#!/bin/bash
# Test script for WhatsApp Platform
# This script tests both session creation and bridge integration

echo "==================================="
echo "WhatsApp Platform Integration Test"
echo "==================================="
echo ""

# Colors for output
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Detect current directory and set paths accordingly
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

# Determine bridge path relative to script location
if [ -d "whatsapp-bridge" ]; then
    BRIDGE_PATH="whatsapp-bridge"
elif [ -d "projects/whatsapp/whatsapp-bridge" ]; then
    BRIDGE_PATH="projects/whatsapp/whatsapp-bridge"
else
    BRIDGE_PATH=""
fi

echo "Working directory: $(pwd)"
echo "Bridge path: ${BRIDGE_PATH:-Not found}"
echo ""

# Check if bridge server is running
echo -n "1. Checking if bridge server is running... "
if curl -s -X POST http://127.0.0.1:3000/api/generate-qr \
    -H "Content-Type: application/json" \
    -d '{"sessionId":"test","userId":1}' > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Bridge server is running${NC}"
    BRIDGE_RUNNING=1
else
    echo -e "${YELLOW}✗ Bridge server is NOT running${NC}"
    if [ -n "$BRIDGE_PATH" ]; then
        echo "  To start: cd $BRIDGE_PATH && node server.js"
    else
        echo "  To start: cd whatsapp-bridge && node server.js"
    fi
    BRIDGE_RUNNING=0
fi
echo ""

# Check Node.js installation
echo -n "2. Checking Node.js installation... "
if command -v node &> /dev/null; then
    NODE_VERSION=$(node -v)
    echo -e "${GREEN}✓ Node.js $NODE_VERSION installed${NC}"
else
    echo -e "${RED}✗ Node.js not found${NC}"
    echo "  Install Node.js 14+ to use the bridge server"
fi
echo ""

# Check npm packages
echo -n "3. Checking npm packages... "
if [ -n "$BRIDGE_PATH" ] && [ -f "$BRIDGE_PATH/package.json" ]; then
    echo -e "${GREEN}✓ package.json found${NC}"
    if [ -d "$BRIDGE_PATH/node_modules" ]; then
        echo -e "  ${GREEN}✓ npm packages installed${NC}"
    else
        echo -e "  ${YELLOW}✗ npm packages not installed${NC}"
        echo "  Run: cd $BRIDGE_PATH && npm install"
    fi
else
    echo -e "${RED}✗ package.json not found${NC}"
    echo "  Expected at: $BRIDGE_PATH/package.json"
    if [ -z "$BRIDGE_PATH" ]; then
        echo "  Bridge directory not found!"
    fi
fi
echo ""

# Check PHP configuration
echo -n "4. Checking PHP configuration... "
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n 1)
    echo -e "${GREEN}✓ $PHP_VERSION${NC}"
    
    # Check output buffering settings
    OB_STATUS=$(php -r "echo ini_get('output_buffering');")
    echo "  Output buffering: $OB_STATUS"
else
    echo -e "${RED}✗ PHP not found${NC}"
fi
echo ""

# Check database
echo -n "5. Checking database tables... "
# You'll need to customize this with your database credentials
# For now, we'll skip this check
echo -e "${YELLOW}⊘ Skipped (manual check required)${NC}"
echo "  Verify tables exist: whatsapp_sessions, whatsapp_subscriptions"
echo ""

# Test bridge API if running
if [ $BRIDGE_RUNNING -eq 1 ]; then
    echo "6. Testing bridge API endpoints..."
    
    # Test health endpoint first
    echo -n "   - Testing /api/health... "
    HEALTH_RESPONSE=$(curl -s -X GET http://127.0.0.1:3000/api/health --max-time 5)
    
    if echo "$HEALTH_RESPONSE" | grep -q '"success":true'; then
        echo -e "${GREEN}✓ Health check passed${NC}"
    else
        echo -e "${YELLOW}⚠ Health check failed${NC}"
        echo "     Response: $HEALTH_RESPONSE"
    fi
    
    # Test generate-qr endpoint
    echo -n "   - Testing /api/generate-qr... "
    RESPONSE=$(curl -s -X POST http://127.0.0.1:3000/api/generate-qr \
        -H "Content-Type: application/json" \
        -d "{\"sessionId\":\"test-$(date +%s)\",\"userId\":1}" \
        --max-time 20 2>&1)
    
    CURL_EXIT=$?
    
    if [ $CURL_EXIT -ne 0 ]; then
        echo -e "${RED}✗ Connection failed${NC}"
        echo "     Error: curl returned exit code $CURL_EXIT"
        echo "     This usually means the server is not responding or timed out"
    elif [ -z "$RESPONSE" ]; then
        echo -e "${RED}✗ Empty response${NC}"
        echo "     The server didn't return any data"
        echo "     Check server.js for errors: cd $BRIDGE_PATH && node server.js"
    elif echo "$RESPONSE" | grep -q "success"; then
        if echo "$RESPONSE" | grep -q '"success":true'; then
            echo -e "${GREEN}✓ Endpoint working${NC}"
            echo "     QR code generated successfully"
        else
            echo -e "${YELLOW}✓ Endpoint responding but with error${NC}"
            
            # Check for specific Chrome/Puppeteer errors
            if echo "$RESPONSE" | grep -q -i "launch.*browser\|libatk\|libgbm\|shared.*libraries\|Chrome.*dependencies"; then
                echo -e "     ${YELLOW}⚠ Chrome/Puppeteer dependencies missing${NC}"
                echo ""
                echo "     Fix: Install Chrome dependencies"
                if [ -n "$BRIDGE_PATH" ]; then
                    echo "     1. cd $BRIDGE_PATH"
                else
                    echo "     1. cd whatsapp-bridge"
                fi
                echo "     2. sudo ./install-chrome-deps.sh"
                echo "     3. See CHROME_SETUP.md for detailed instructions"
                echo ""
            fi
            
            # Show first 300 chars of response
            TRIMMED=$(echo "$RESPONSE" | cut -c1-300)
            echo "     Error: $TRIMMED..."
        fi
    else
        echo -e "${RED}✗ Invalid response${NC}"
        echo "     Response doesn't contain expected JSON"
        TRIMMED=$(echo "$RESPONSE" | cut -c1-200)
        echo "     Data: $TRIMMED"
    fi
    echo ""
fi

# Summary
echo "==================================="
echo "Test Summary"
echo "==================================="
echo ""

if [ $BRIDGE_RUNNING -eq 1 ]; then
    echo -e "${GREEN}✓ Bridge server is operational${NC}"
    echo ""
    echo "Next steps:"
    echo "1. Go to /projects/whatsapp/sessions"
    echo "2. Create a new session"
    echo "3. Click 'View QR' on the session"
    echo "4. You should see a real WhatsApp QR code"
    echo "5. Scan with WhatsApp mobile app to connect"
else
    echo -e "${YELLOW}⚠ Bridge server is not running${NC}"
    echo ""
    echo "To start the bridge server:"
    echo "1. cd projects/whatsapp/whatsapp-bridge"
    echo "2. npm install (if not done)"
    echo "3. node server.js"
    echo ""
    echo "After starting:"
    echo "- Platform will automatically detect the bridge"
    echo "- Real QR codes will be displayed"
    echo "- Placeholder QR codes will be shown when bridge is down"
fi

echo ""
echo "For more help, see:"
echo "- projects/whatsapp/CHROME_SETUP.md (Chrome/Puppeteer issues)"
echo "- projects/whatsapp/TROUBLESHOOTING.md (General troubleshooting)"
echo "- projects/whatsapp/QUICK_START.md (Initial setup)"
echo ""
