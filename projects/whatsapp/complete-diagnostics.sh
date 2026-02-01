#!/bin/bash
# Complete WhatsApp Platform Troubleshooting Script
# This script checks all components and identifies what's still not working

echo "=========================================="
echo "WhatsApp Platform Complete Diagnostics"
echo "=========================================="
echo ""
echo "Timestamp: $(date)"
echo ""

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Track overall issues
ISSUES_FOUND=0

# Function to print status
print_status() {
    if [ $1 -eq 0 ]; then
        echo -e "${GREEN}✓${NC} $2"
    else
        echo -e "${RED}✗${NC} $2"
        ISSUES_FOUND=$((ISSUES_FOUND + 1))
    fi
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

echo "=== 1. CHECKING BRIDGE SERVER ==="
echo ""

# Check if port 3000 is in use
if lsof -i :3000 > /dev/null 2>&1 || netstat -tuln 2>/dev/null | grep -q ":3000"; then
    print_status 0 "Port 3000 is in use (bridge server process exists)"
    
    # Get process details
    PID=$(lsof -t -i:3000 2>/dev/null)
    if [ ! -z "$PID" ]; then
        echo "  Process ID: $PID"
        ps -p $PID -o pid,ppid,cmd,etime 2>/dev/null | tail -1 | sed 's/^/  /'
    fi
else
    print_status 1 "Port 3000 is NOT in use - bridge server is NOT running"
    echo "  Fix: cd projects/whatsapp/whatsapp-bridge && npm start &"
    echo ""
fi

echo ""
echo "=== 2. TESTING BRIDGE SERVER HEALTH ==="
echo ""

# Test health endpoint with curl
if command -v curl >/dev/null 2>&1; then
    HEALTH_RESPONSE=$(curl -s -m 5 http://127.0.0.1:3000/api/health 2>&1)
    HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" -m 5 http://127.0.0.1:3000/api/health 2>&1)
    
    if [ "$HTTP_CODE" = "200" ]; then
        print_status 0 "Bridge server health endpoint responding (HTTP 200)"
        echo "  Response: $HEALTH_RESPONSE"
    else
        print_status 1 "Bridge server health endpoint NOT responding (HTTP $HTTP_CODE)"
        echo "  Response: $HEALTH_RESPONSE"
    fi
else
    print_warning "curl not found, skipping HTTP test"
fi

echo ""
echo "=== 3. CHECKING PHP CONNECTIVITY ==="
echo ""

# Check PHP version
if command -v php >/dev/null 2>&1; then
    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    print_status 0 "PHP is installed (version $PHP_VERSION)"
else
    print_status 1 "PHP is NOT installed"
fi

# Test PHP file_get_contents
echo ""
echo "Testing file_get_contents..."
php -r '
    $result = @file_get_contents("http://127.0.0.1:3000/api/health");
    if ($result === false) {
        echo "✗ PHP file_get_contents FAILED\n";
        $error = error_get_last();
        echo "  Error: " . ($error["message"] ?? "Unknown error") . "\n";
        exit(1);
    } else {
        echo "✓ PHP file_get_contents SUCCESS\n";
        echo "  Response: " . $result . "\n";
        exit(0);
    }
'
FILE_GET_STATUS=$?
if [ $FILE_GET_STATUS -ne 0 ]; then
    ISSUES_FOUND=$((ISSUES_FOUND + 1))
fi

# Test PHP curl
echo ""
echo "Testing PHP cURL..."
php -r '
    if (!function_exists("curl_init")) {
        echo "✗ cURL extension is NOT installed\n";
        echo "  Install: apt-get install php-curl && service php-fpm restart\n";
        exit(1);
    }
    
    $ch = curl_init("http://127.0.0.1:3000/api/health");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "✓ PHP cURL SUCCESS (HTTP $httpCode)\n";
        echo "  Response: $response\n";
        exit(0);
    } else {
        echo "✗ PHP cURL FAILED (HTTP $httpCode)\n";
        echo "  Error: $error\n";
        exit(1);
    }
'
CURL_STATUS=$?
if [ $CURL_STATUS -ne 0 ]; then
    ISSUES_FOUND=$((ISSUES_FOUND + 1))
fi

echo ""
echo "=== 4. CHECKING PHP CONFIGURATION ==="
echo ""

php -r '
    $allow_url_fopen = ini_get("allow_url_fopen");
    $curl_available = function_exists("curl_init");
    
    if ($allow_url_fopen) {
        echo "✓ allow_url_fopen: ENABLED\n";
    } else {
        echo "✗ allow_url_fopen: DISABLED\n";
        echo "  Fix: Set allow_url_fopen = On in php.ini\n";
    }
    
    if ($curl_available) {
        echo "✓ cURL extension: AVAILABLE\n";
    } else {
        echo "✗ cURL extension: NOT AVAILABLE\n";
        echo "  Fix: apt-get install php-curl && service php-fpm restart\n";
    }
    
    exit((!$allow_url_fopen || !$curl_available) ? 1 : 0);
'
PHP_CONFIG_STATUS=$?
if [ $PHP_CONFIG_STATUS -ne 0 ]; then
    ISSUES_FOUND=$((ISSUES_FOUND + 1))
fi

echo ""
echo "=== 5. CHECKING DATABASE CONNECTION ==="
echo ""

# Test if database queries work
php -r '
    require_once "core/Database.php";
    require_once "config/database.php";
    
    try {
        $db = Core\Database::getInstance();
        echo "✓ Database connection successful\n";
        
        // Test whatsapp_sessions table
        $result = $db->query("SELECT COUNT(*) as count FROM whatsapp_sessions LIMIT 1");
        echo "✓ whatsapp_sessions table exists\n";
        
        // Test whatsapp_subscriptions table (might not exist)
        try {
            $result = $db->query("SELECT COUNT(*) as count FROM whatsapp_subscriptions LIMIT 1");
            echo "✓ whatsapp_subscriptions table exists\n";
        } catch (Exception $e) {
            echo "⚠ whatsapp_subscriptions table does NOT exist (will use defaults)\n";
        }
        
        exit(0);
    } catch (Exception $e) {
        echo "✗ Database connection FAILED\n";
        echo "  Error: " . $e->getMessage() . "\n";
        exit(1);
    }
' 2>&1
DB_STATUS=$?
if [ $DB_STATUS -ne 0 ]; then
    ISSUES_FOUND=$((ISSUES_FOUND + 1))
fi

echo ""
echo "=== 6. CHECKING SERVER CONFIGURATION ==="
echo ""

# Check if server.js has correct config
if [ -f "projects/whatsapp/whatsapp-bridge/server.js" ]; then
    if grep -q "0.0.0.0" projects/whatsapp/whatsapp-bridge/server.js; then
        print_status 0 "Bridge server configured to listen on 0.0.0.0"
    else
        print_status 1 "Bridge server NOT configured for 0.0.0.0"
        echo "  Fix: Update server.js to listen on 0.0.0.0"
    fi
else
    print_status 1 "server.js file not found"
fi

echo ""
echo "=== 7. CHECKING SESSION CONTROLLER ==="
echo ""

# Check if SessionController has curl support
if [ -f "projects/whatsapp/controllers/SessionController.php" ]; then
    if grep -q "curl_init" projects/whatsapp/controllers/SessionController.php; then
        print_status 0 "SessionController has cURL support"
    else
        print_status 1 "SessionController missing cURL support"
        echo "  Fix: Update SessionController.php with dual connection method"
    fi
else
    print_status 1 "SessionController.php file not found"
fi

echo ""
echo "=========================================="
echo "SUMMARY"
echo "=========================================="
echo ""

if [ $ISSUES_FOUND -eq 0 ]; then
    echo -e "${GREEN}✓ ALL CHECKS PASSED${NC}"
    echo ""
    echo "System appears to be configured correctly."
    echo ""
    echo "If you're still experiencing issues:"
    echo "1. Restart the bridge server: ./projects/whatsapp/restart-bridge.sh"
    echo "2. Check application logs for specific errors"
    echo "3. Try creating a test session and check browser console"
    echo "4. Access: https://your-domain.com/projects/whatsapp/bridge-health.php"
else
    echo -e "${RED}✗ FOUND $ISSUES_FOUND ISSUE(S)${NC}"
    echo ""
    echo "Please fix the issues listed above and run this script again."
    echo ""
    echo "Quick fixes:"
    echo "1. Bridge not running: cd projects/whatsapp/whatsapp-bridge && npm start &"
    echo "2. Missing cURL: apt-get install php-curl && service php-fpm restart"
    echo "3. Database issues: Check database credentials and tables"
    echo "4. Configuration issues: Review PRODUCTION_DEPLOYMENT.md"
fi

echo ""
echo "For detailed help, see: projects/whatsapp/PRODUCTION_DEPLOYMENT.md"
echo ""

exit $ISSUES_FOUND
