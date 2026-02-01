#!/bin/bash
# WhatsApp Bridge Diagnostics Script
# This script helps diagnose connectivity issues with the WhatsApp bridge server

echo "==================================="
echo "WhatsApp Bridge Diagnostics"
echo "==================================="
echo ""

# Check if bridge server is running
echo "1. Checking if bridge server is running on port 3000..."
if lsof -i :3000 > /dev/null 2>&1 || netstat -tuln 2>/dev/null | grep -q ":3000"; then
    echo "   ✓ Port 3000 is in use (bridge server is running)"
else
    echo "   ✗ Port 3000 is NOT in use (bridge server is NOT running)"
    echo "   Start it with: cd projects/whatsapp/whatsapp-bridge && npm start"
    exit 1
fi

echo ""
echo "2. Testing bridge server health endpoint..."
HEALTH_RESPONSE=$(curl -s -w "\nHTTP_CODE:%{http_code}" http://127.0.0.1:3000/api/health 2>&1)
HTTP_CODE=$(echo "$HEALTH_RESPONSE" | grep "HTTP_CODE:" | cut -d: -f2)

if [ "$HTTP_CODE" = "200" ]; then
    echo "   ✓ Bridge server is responding (HTTP 200)"
    echo "   Response: $(echo "$HEALTH_RESPONSE" | grep -v "HTTP_CODE:")"
else
    echo "   ✗ Bridge server is not responding properly (HTTP $HTTP_CODE)"
    echo "   Response: $HEALTH_RESPONSE"
fi

echo ""
echo "3. Testing from PHP context (using php cli)..."
php -r '
    $response = @file_get_contents("http://127.0.0.1:3000/api/health");
    if ($response === false) {
        echo "   ✗ PHP file_get_contents FAILED\n";
        echo "   This might be due to allow_url_fopen=0 or firewall\n";
    } else {
        echo "   ✓ PHP file_get_contents SUCCESS\n";
        echo "   Response: " . $response . "\n";
    }
'

echo ""
echo "4. Testing with PHP curl..."
php -r '
    if (!function_exists("curl_init")) {
        echo "   ✗ cURL extension is NOT installed\n";
        echo "   Install it with: apt-get install php-curl\n";
    } else {
        echo "   ✓ cURL extension is available\n";
        $ch = curl_init("http://127.0.0.1:3000/api/health");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            echo "   ✓ PHP cURL SUCCESS (HTTP $httpCode)\n";
            echo "   Response: $response\n";
        } else {
            echo "   ✗ PHP cURL FAILED (HTTP $httpCode)\n";
        }
    }
'

echo ""
echo "5. Checking PHP configuration..."
php -r '
    $allow_url_fopen = ini_get("allow_url_fopen");
    echo "   allow_url_fopen: " . ($allow_url_fopen ? "✓ ENABLED" : "✗ DISABLED") . "\n";
    
    $curl_available = function_exists("curl_init");
    echo "   curl extension: " . ($curl_available ? "✓ AVAILABLE" : "✗ NOT AVAILABLE") . "\n";
'

echo ""
echo "6. Checking process information..."
ps aux | grep -E "node.*server\.js|whatsapp" | grep -v grep | head -5

echo ""
echo "==================================="
echo "Diagnostics Complete"
echo "==================================="
echo ""
echo "If the bridge server is running but PHP cannot connect:"
echo "1. Check allow_url_fopen in php.ini"
echo "2. Install php-curl if not available"
echo "3. Check firewall rules"
echo "4. Restart the bridge server: pkill -f 'node.*server.js' && cd projects/whatsapp/whatsapp-bridge && npm start &"
echo ""
