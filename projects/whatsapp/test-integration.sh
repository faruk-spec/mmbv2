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

# Check if bridge server is running
echo -n "1. Checking if bridge server is running... "
if curl -s -X POST http://127.0.0.1:3000/api/generate-qr \
    -H "Content-Type: application/json" \
    -d '{"sessionId":"test","userId":1}' > /dev/null 2>&1; then
    echo -e "${GREEN}✓ Bridge server is running${NC}"
    BRIDGE_RUNNING=1
else
    echo -e "${YELLOW}✗ Bridge server is NOT running${NC}"
    echo "  To start: cd projects/whatsapp/whatsapp-bridge && node server.js"
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
if [ -f "projects/whatsapp/whatsapp-bridge/package.json" ]; then
    if [ -d "projects/whatsapp/whatsapp-bridge/node_modules" ]; then
        echo -e "${GREEN}✓ npm packages installed${NC}"
    else
        echo -e "${YELLOW}✗ npm packages not installed${NC}"
        echo "  Run: cd projects/whatsapp/whatsapp-bridge && npm install"
    fi
else
    echo -e "${RED}✗ package.json not found${NC}"
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
    
    # Test generate-qr endpoint
    echo -n "   - Testing /api/generate-qr... "
    RESPONSE=$(curl -s -X POST http://127.0.0.1:3000/api/generate-qr \
        -H "Content-Type: application/json" \
        -d '{"sessionId":"test-'$(date +%s)'","userId":1}' \
        --max-time 20)
    
    if echo "$RESPONSE" | grep -q "success"; then
        if echo "$RESPONSE" | grep -q '"success":true'; then
            echo -e "${GREEN}✓ Endpoint working${NC}"
            echo "     QR code generated successfully"
        else
            echo -e "${YELLOW}✓ Endpoint responding${NC}"
            echo "     Response: $RESPONSE"
        fi
    else
        echo -e "${RED}✗ Endpoint failed${NC}"
        echo "     Response: $RESPONSE"
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
echo "- projects/whatsapp/TROUBLESHOOTING.md"
echo "- projects/whatsapp/QUICK_START.md"
echo ""
