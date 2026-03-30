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
    echo "   Tip: cp $BRIDGE_DIR/.env.example $ENV_FILE"
fi
echo ""

# ── Check if the server is running ───────────────────────────
echo "1. Checking current status..."
if lsof -i :3000 > /dev/null 2>&1; then
    echo "   Bridge server is currently running on port 3000"
    
    # Get the PID
    PID=$(lsof -t -i:3000)
    echo "   Process ID: $PID"
    
    # Stop the server
    echo ""
    echo "2. Stopping the bridge server..."
    kill $PID 2>/dev/null
    
    # Wait for it to stop
    sleep 2
    
    # Force kill if still running
    if lsof -i :3000 > /dev/null 2>&1; then
        echo "   Process still running, forcing shutdown..."
        kill -9 $PID 2>/dev/null
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

# Wait a moment for server to start
sleep 3

# Check if it started successfully
if lsof -i :3000 > /dev/null 2>&1; then
    echo "   ✓ Bridge server started successfully (PID: $NEW_PID)"
    echo "   Log file: $BRIDGE_DIR/bridge-server.log"
    
    # Test the health endpoint
    echo ""
    echo "4. Testing health endpoint..."
    sleep 2
    HEALTH_CHECK=$(curl -s http://127.0.0.1:3000/api/health)
    echo "   Response: $HEALTH_CHECK"

    # Run connectivity test to check WhatsApp reachability
    echo ""
    echo "5. Testing WhatsApp connectivity..."
    sleep 1
    CONN_CHECK=$(curl -s http://127.0.0.1:3000/api/connectivity-test)
    # Try to extract the 'success' and 'message' fields portably
    CONN_OK=$(echo "$CONN_CHECK" | grep -o '"success":[a-z]*' | head -1 | cut -d: -f2)
    CONN_MSG=$(echo "$CONN_CHECK" | grep -o '"message":"[^"]*"' | head -1 | sed 's/"message":"//;s/"//')
    if [ "$CONN_OK" = "true" ]; then
        echo "   ✓ $CONN_MSG"
    else
        echo "   ✗ $CONN_MSG"
        echo ""
        echo "   ════════════════════════════════════════════════"
        echo "   WhatsApp is NOT reachable from this server."
        echo "   This is why QR codes time out."
        echo ""
        echo "   HOW TO FIX:"
        echo "   1. Get a SOCKS5 proxy (e.g. webshare.io or buy"
        echo "      a cheap VPS in the US/EU and run dante-server)"
        echo "   2. Edit $ENV_FILE"
        echo "      Set: WHATSAPP_PROXY_URL=socks5://user:pass@host:1080"
        echo "   3. Re-run: bash $SCRIPT_DIR/restart-bridge.sh"
        echo ""
        echo "   Full guide: cat $SCRIPT_DIR/FIX_SSL_WHATSAPP.md"
        echo "   ════════════════════════════════════════════════"
    fi
else
    echo "   ✗ Failed to start bridge server"
    echo "   Check the log file: $BRIDGE_DIR/bridge-server.log"
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

