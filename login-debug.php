<?php
/**
 * Login Debug Helper
 * Add this to troubleshoot login issues
 */

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check what was posted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>POST Request Received</h3>";
    echo "<pre>";
    echo "POST Data:\n";
    print_r($_POST);
    echo "\n\nSession Data:\n";
    print_r($_SESSION);
    echo "\n\nCookies:\n";
    print_r($_COOKIE);
    echo "</pre>";
    
    // Check CSRF
    if (isset($_POST['_csrf_token'])) {
        echo "<p>CSRF Token submitted: " . htmlspecialchars($_POST['_csrf_token']) . "</p>";
        echo "<p>Session CSRF Token: " . (isset($_SESSION['csrf_token']) ? htmlspecialchars($_SESSION['csrf_token']) : 'NOT SET') . "</p>";
        echo "<p>Match: " . (isset($_SESSION['csrf_token']) && $_POST['_csrf_token'] === $_SESSION['csrf_token'] ? 'YES' : 'NO') . "</p>";
    } else {
        echo "<p style='color: red;'>NO CSRF TOKEN IN POST DATA</p>";
    }
    
    // Check email/password
    if (isset($_POST['email']) && isset($_POST['password'])) {
        echo "<p>Email provided: " . htmlspecialchars($_POST['email']) . "</p>";
        echo "<p>Password provided: " . (empty($_POST['password']) ? 'EMPTY' : 'NOT EMPTY (length: ' . strlen($_POST['password']) . ')') . "</p>";
    }
    
    echo "<hr>";
}

// Check current session state
echo "<h3>Current Session State</h3>";
echo "<pre>";
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? 'ACTIVE' : 'INACTIVE') . "\n";
echo "Session ID: " . session_id() . "\n";
echo "User ID in session: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'NOT SET') . "\n";
echo "</pre>";

// Check server variables
echo "<h3>Server Variables</h3>";
echo "<pre>";
echo "REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD'] . "\n";
echo "REQUEST_URI: " . $_SERVER['REQUEST_URI'] . "\n";
echo "HTTP_HOST: " . $_SERVER['HTTP_HOST'] . "\n";
echo "HTTPS: " . (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'ON' : 'OFF') . "\n";
echo "</pre>";

echo "<p><a href='/login'>Go to Login Page</a></p>";
