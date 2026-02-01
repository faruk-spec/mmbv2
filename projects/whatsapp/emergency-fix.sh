#!/bin/bash
# Emergency Fix-All Script
# Run this if you've made changes but nothing is working

set -e  # Exit on any error

echo "=========================================="
echo "WhatsApp Platform Emergency Fix"
echo "=========================================="
echo ""
echo "This script will:"
echo "1. Stop all related processes"
echo "2. Install missing dependencies"
echo "3. Restart everything properly"
echo "4. Verify it's working"
echo ""
read -p "Press Enter to continue or Ctrl+C to cancel..."
echo ""

# Get script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

echo "=== Step 1: Stopping all processes ==="
echo "Killing any running bridge servers..."
pkill -9 -f "node.*server.js" 2>/dev/null || echo "No bridge server was running"
sleep 2
echo "✓ Processes stopped"
echo ""

echo "=== Step 2: Checking dependencies ==="

# Check if Node.js is installed
if ! command -v node >/dev/null 2>&1; then
    echo "✗ Node.js is not installed!"
    echo "Install it with: curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && apt-get install -y nodejs"
    exit 1
fi
echo "✓ Node.js is installed ($(node --version))"

# Check if npm packages are installed
cd whatsapp-bridge
if [ ! -d "node_modules" ]; then
    echo "Installing Node.js dependencies..."
    npm install
    echo "✓ Dependencies installed"
else
    echo "✓ Node.js dependencies already installed"
fi
cd ..

# Check PHP
if ! command -v php >/dev/null 2>&1; then
    echo "✗ PHP is not installed!"
    exit 1
fi
echo "✓ PHP is installed ($(php -r 'echo PHP_VERSION;'))"

# Check PHP curl
if ! php -m | grep -q curl; then
    echo "⚠ PHP cURL extension not found"
    echo "Installing PHP cURL..."
    if command -v apt-get >/dev/null 2>&1; then
        apt-get update -qq
        apt-get install -y php-curl
    elif command -v yum >/dev/null 2>&1; then
        yum install -y php-curl
    fi
    echo "✓ PHP cURL installed"
else
    echo "✓ PHP cURL is installed"
fi

echo ""
echo "=== Step 3: Restarting services ==="

# Restart PHP-FPM
echo "Restarting PHP-FPM..."
if command -v systemctl >/dev/null 2>&1; then
    systemctl restart php-fpm 2>/dev/null || service php-fpm restart 2>/dev/null || echo "Could not restart php-fpm (might be ok)"
else
    service php-fpm restart 2>/dev/null || echo "Could not restart php-fpm (might be ok)"
fi
echo "✓ PHP-FPM restarted"

# Start bridge server
echo "Starting bridge server..."
cd whatsapp-bridge
nohup npm start > bridge-server.log 2>&1 &
BRIDGE_PID=$!
echo "Bridge server starting (PID: $BRIDGE_PID)..."
cd ..

# Wait for bridge to start
echo "Waiting for bridge server to initialize (15 seconds)..."
sleep 15

echo ""
echo "=== Step 4: Verifying everything is working ==="
echo ""

# Check if port is in use
if lsof -i :3000 >/dev/null 2>&1; then
    echo "✓ Bridge server is running on port 3000"
else
    echo "✗ Bridge server is NOT running on port 3000"
    echo "Check the logs: cat whatsapp-bridge/bridge-server.log"
    exit 1
fi

# Test health endpoint
echo "Testing bridge server health endpoint..."
HEALTH_RESPONSE=$(curl -s -m 10 http://127.0.0.1:3000/api/health 2>&1)
if echo "$HEALTH_RESPONSE" | grep -q "success"; then
    echo "✓ Bridge server health check passed"
    echo "  Response: $HEALTH_RESPONSE"
else
    echo "✗ Bridge server health check failed"
    echo "  Response: $HEALTH_RESPONSE"
    exit 1
fi

# Test PHP connectivity
echo ""
echo "Testing PHP connectivity..."
php -r '
    $ch = curl_init("http://127.0.0.1:3000/api/health");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200 && strpos($response, "success") !== false) {
        echo "✓ PHP can connect to bridge server\n";
        echo "  Response: $response\n";
        exit(0);
    } else {
        echo "✗ PHP cannot connect to bridge server\n";
        echo "  HTTP Code: $httpCode\n";
        echo "  Response: $response\n";
        exit(1);
    }
'

if [ $? -eq 0 ]; then
    echo ""
    echo "=========================================="
    echo "✓✓✓ ALL SYSTEMS WORKING! ✓✓✓"
    echo "=========================================="
    echo ""
    echo "Your WhatsApp platform is now operational."
    echo ""
    echo "Next steps:"
    echo "1. Go to: https://your-domain.com/projects/whatsapp/sessions"
    echo "2. Click 'New Session' to create a test session"
    echo "3. Click 'Scan QR' to generate a QR code"
    echo ""
    echo "Bridge server logs: $SCRIPT_DIR/whatsapp-bridge/bridge-server.log"
    echo "View logs with: tail -f $SCRIPT_DIR/whatsapp-bridge/bridge-server.log"
    echo ""
else
    echo ""
    echo "=========================================="
    echo "⚠ PARTIAL SUCCESS"
    echo "=========================================="
    echo ""
    echo "Bridge server is running but PHP can't connect."
    echo ""
    echo "Additional fixes needed:"
    echo "1. Check if allow_url_fopen is enabled in php.ini"
    echo "2. Check firewall rules"
    echo "3. Run complete diagnostics: ./complete-diagnostics.sh"
    echo ""
fi

echo "For detailed troubleshooting, see: STILL_NOT_WORKING.md"
echo ""
