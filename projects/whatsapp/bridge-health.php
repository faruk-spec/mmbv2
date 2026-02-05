<?php
/**
 * WhatsApp Bridge Health Check
 * Access this via: https://yourdomain.com/projects/whatsapp/bridge-health.php
 * 
 * This script helps diagnose connectivity issues between PHP and the bridge server
 */

// Prevent direct access from web (optional - remove this if you want web access)
// Uncomment the next 3 lines to restrict access
// if (!isset($_SERVER['argv'])) {
//     die('This script should be run from command line');
// }

header('Content-Type: application/json');

$results = [
    'timestamp' => date('Y-m-d H:i:s'),
    'bridge_url' => 'http://127.0.0.1:3000',
    'tests' => []
];

// Test 1: Check if curl is available
$results['tests']['curl_available'] = function_exists('curl_init');

// Test 2: Check allow_url_fopen
$results['tests']['allow_url_fopen'] = ini_get('allow_url_fopen') == 1;

// Test 3: Try to connect with curl
if ($results['tests']['curl_available']) {
    try {
        $ch = curl_init('http://127.0.0.1:3000/api/health');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        $results['tests']['curl_test'] = [
            'success' => $httpCode === 200,
            'http_code' => $httpCode,
            'response' => $response ? json_decode($response, true) : null,
            'error' => $curlError ?: null
        ];
    } catch (Exception $e) {
        $results['tests']['curl_test'] = [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Test 4: Try with file_get_contents
if ($results['tests']['allow_url_fopen']) {
    try {
        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'ignore_errors' => true
            ]
        ]);
        
        $response = @file_get_contents('http://127.0.0.1:3000/api/health', false, $context);
        
        $results['tests']['file_get_contents_test'] = [
            'success' => $response !== false,
            'response' => $response ? json_decode($response, true) : null,
            'http_response_header' => isset($http_response_header) ? $http_response_header : null
        ];
    } catch (Exception $e) {
        $results['tests']['file_get_contents_test'] = [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Test 5: Check if port 3000 is reachable
$results['tests']['port_reachable'] = @fsockopen('127.0.0.1', 3000, $errno, $errstr, 2) !== false;

// Test 6: Try alternative localhost addresses
$alternativeHosts = ['localhost', '127.0.0.1', '::1'];
foreach ($alternativeHosts as $host) {
    if ($results['tests']['curl_available']) {
        $ch = curl_init("http://$host:3000/api/health");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $results['tests']['alternative_hosts'][$host] = [
            'reachable' => $httpCode === 200,
            'http_code' => $httpCode
        ];
    }
}

// Determine overall status
$overallSuccess = false;
if (isset($results['tests']['curl_test']) && $results['tests']['curl_test']['success']) {
    $overallSuccess = true;
} elseif (isset($results['tests']['file_get_contents_test']) && $results['tests']['file_get_contents_test']['success']) {
    $overallSuccess = true;
}

$results['overall_status'] = $overallSuccess ? 'SUCCESS' : 'FAILED';

// Recommendations
$results['recommendations'] = [];

if (!$results['tests']['curl_available']) {
    $results['recommendations'][] = 'Install PHP cURL extension: apt-get install php-curl && service php-fpm restart';
}

if (!$results['tests']['allow_url_fopen']) {
    $results['recommendations'][] = 'Enable allow_url_fopen in php.ini';
}

if (!$results['tests']['port_reachable']) {
    $results['recommendations'][] = 'Bridge server is not running on port 3000. Start it with: cd projects/whatsapp/whatsapp-bridge && npm start';
}

if (!$overallSuccess && $results['tests']['port_reachable']) {
    $results['recommendations'][] = 'Port 3000 is open but server is not responding. Check bridge server logs and restart it.';
}

// Output results
echo json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
