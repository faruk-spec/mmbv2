#!/bin/bash
# Restart WhatsApp Bridge Server Script
# This script safely stops and restarts the bridge server

echo "==================================="
echo "WhatsApp Bridge Server Restart"
echo "==================================="
echo ""

# Get the directory of this script
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
BRIDGE_DIR="$SCRIPT_DIR/whatsapp-bridge"

cd "$BRIDGE_DIR" || exit 1

# Check if the server is running
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
echo "Logs: $BRIDGE_DIR/bridge-server.log"
echo ""
echo "To view logs: tail -f $BRIDGE_DIR/bridge-server.log"
echo "To stop: kill $NEW_PID"
echo ""
