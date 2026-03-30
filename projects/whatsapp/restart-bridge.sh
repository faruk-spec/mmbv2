#!/bin/bash
# Restart WhatsApp Bridge Server Script
# This script safely stops and restarts the bridge server
# and automatically loads environment variables from .env

echo "==================================="
echo "WhatsApp Bridge Server Restart"
echo "==================================="
echo ""

# Get the directory of this script
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
BRIDGE_DIR="$SCRIPT_DIR/whatsapp-bridge"

cd "$BRIDGE_DIR" || exit 1

# ── Portable port helpers (lsof is not installed on many VPS) ────────────────
# port_in_use <port>  → returns 0 (true) if something is listening on that port
port_in_use() {
    local port="$1"
    # Try ss first (iproute2, available on all modern Linux)
    if ss -tlnp "sport = :$port" 2>/dev/null | grep -q ":$port"; then
        return 0
    fi
    # Fallback: netstat
    if netstat -tlnp 2>/dev/null | grep -q ":$port "; then
        return 0
    fi
    # Fallback: fuser
    if fuser "${port}/tcp" > /dev/null 2>&1; then
        return 0
    fi
    # Fallback: curl health endpoint
    if curl -s --max-time 1 "http://127.0.0.1:$port/api/health" > /dev/null 2>&1; then
        return 0
    fi
    return 1
}

# get_pid <port>  → print the PID listening on that port (best effort)
get_pid() {
    local port="$1"
    local pid=""
    # ss with process info
    pid=$(ss -tlnp "sport = :$port" 2>/dev/null | grep -oP 'pid=\K[0-9]+' | head -1)
    [ -n "$pid" ] && echo "$pid" && return
    # netstat
    pid=$(netstat -tlnp 2>/dev/null | grep ":$port " | awk '{print $7}' | cut -d/ -f1 | head -1)
    [ -n "$pid" ] && echo "$pid" && return
    # fuser
    pid=$(fuser "${port}/tcp" 2>/dev/null | awk '{print $1}')
    [ -n "$pid" ] && echo "$pid" && return
    # pgrep for node server.js
    pgrep -f "node server.js" 2>/dev/null | head -1
}

# ── Load .env file if it exists ──────────────────────────────
ENV_FILE="$BRIDGE_DIR/.env"
if [ -f "$ENV_FILE" ]; then
    echo "Loading configuration from .env..."
    # Export each non-blank, non-comment line as an environment variable
    while IFS= read -r line; do
        # Skip blank lines and comment lines
        [[ -z "$line" || "$line" == \#* ]] && continue
        export "$line"
    done < "$ENV_FILE"
    echo "   ✓ Configuration loaded"
    if [ -n "$WHATSAPP_PROXY_URL" ]; then
        # Redact credentials in the log output
        SAFE_PROXY=$(echo "$WHATSAPP_PROXY_URL" | sed 's|://[^:@]*:[^@]*@|://***:***@|')
        echo "   Proxy: $SAFE_PROXY"
    else
        echo "   No proxy configured (using direct connection)"
    fi
else
    echo "   (No .env file found — using system environment)"
    if [ -f "$BRIDGE_DIR/.env.example" ]; then
        echo "   Tip: cp $BRIDGE_DIR/.env.example $ENV_FILE"
    else
        echo "   Tip: git pull  (to get the .env.example template)"
        echo "        Then: cp $BRIDGE_DIR/.env.example $ENV_FILE"
    fi
fi
echo ""

# ── Check if the server is already running ───────────────────
echo "1. Checking current status..."
if port_in_use 3000; then
    echo "   Bridge server is currently running on port 3000"

    PID=$(get_pid 3000)
    if [ -n "$PID" ]; then
        echo "   Process ID: $PID"
    fi

    echo ""
    echo "2. Stopping the bridge server..."
    if [ -n "$PID" ]; then
        kill "$PID" 2>/dev/null
    else
        # No PID found — kill all node server.js processes
        pkill -f "node server.js" 2>/dev/null || true
    fi

    # Wait for the port to be released (up to 5 s)
    for i in 1 2 3 4 5; do
        sleep 1
        port_in_use 3000 || break
    done

    if port_in_use 3000; then
        echo "   Process still running, forcing shutdown..."
        if [ -n "$PID" ]; then
            kill -9 "$PID" 2>/dev/null
        fi
        pkill -9 -f "node server.js" 2>/dev/null || true
        sleep 1
    fi

    echo "   ✓ Bridge server stopped"
else
    echo "   No bridge server currently running on port 3000"
fi

echo ""
echo "3. Starting bridge server..."

# Check if node_modules exists
if [ ! -d "node_modules" ]; then
    echo "   Installing dependencies first..."
    npm install
fi

# Start the server in background with output logging
echo "   Starting server..."
nohup npm start > bridge-server.log 2>&1 &
NEW_PID=$!

# Wait for the health endpoint to respond (up to 10 s)
echo "   Waiting for server to respond..."
STARTED=0
for i in 1 2 3 4 5 6 7 8 9 10; do
    sleep 1
    if curl -s --max-time 1 http://127.0.0.1:3000/api/health > /dev/null 2>&1; then
        STARTED=1
        break
    fi
done

if [ "$STARTED" = "1" ]; then
    echo "   ✓ Bridge server started successfully (PID: $NEW_PID)"
    echo "   Log file: $BRIDGE_DIR/bridge-server.log"

    # Test the health endpoint
    echo ""
    echo "4. Testing health endpoint..."
    HEALTH_CHECK=$(curl -s http://127.0.0.1:3000/api/health)
    echo "   Response: $HEALTH_CHECK"

    # Run connectivity test to check WhatsApp reachability
    echo ""
    echo "5. Testing WhatsApp connectivity..."
    CONN_CHECK=$(curl -s http://127.0.0.1:3000/api/connectivity-test)
    # Try to extract the 'success' and 'message' fields portably
    CONN_OK=$(echo "$CONN_CHECK" | grep -o '"success":[a-z]*' | head -1 | cut -d: -f2)
    CONN_MSG=$(echo "$CONN_CHECK" | grep -o '"message":"[^"]*"' | head -1 | sed 's/"message":"//;s/"//')
    if [ "$CONN_OK" = "true" ]; then
        echo "   ✓ $CONN_MSG"
    else
        echo "   ✗ ${CONN_MSG:-WhatsApp is not reachable (SSL/TLS blocked)}"
        echo ""
        echo "   ════════════════════════════════════════════════"
        echo "   WhatsApp is NOT reachable from this server."
        echo "   This is why QR codes time out."
        echo ""
        echo "   HOW TO FIX:"
        echo "   1. Get a SOCKS5 proxy (e.g. webshare.io — free"
        echo "      tier works, or buy a cheap US/EU VPS)"
        echo "   2. Edit $ENV_FILE"
        echo "      Set: WHATSAPP_PROXY_URL=socks5://user:pass@host:1080"
        echo "   3. Re-run: bash $0"
        echo ""
        echo "   Full guide: cat $SCRIPT_DIR/FIX_SSL_WHATSAPP.md"
        echo "   ════════════════════════════════════════════════"
    fi
else
    echo "   ✗ Bridge server did not respond after 10 seconds"
    echo ""
    echo "   Last log lines:"
    tail -20 bridge-server.log 2>/dev/null | sed 's/^/   /'
    exit 1
fi

echo ""
echo "==================================="
echo "Bridge Server Restart Complete"
echo "==================================="
echo ""
echo "Server is running on http://0.0.0.0:3000"
echo "Health check: http://127.0.0.1:3000/api/health"
echo "Connectivity: http://127.0.0.1:3000/api/connectivity-test"
echo "Logs: $BRIDGE_DIR/bridge-server.log"
echo ""
echo "To view logs: tail -f $BRIDGE_DIR/bridge-server.log"
echo "To stop:      kill $NEW_PID"
echo ""

