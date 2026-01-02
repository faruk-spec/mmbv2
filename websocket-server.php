#!/usr/bin/env php
<?php

/**
 * WebSocket Server Launcher
 * 
 * Start the WebSocket server for real-time features.
 * 
 * Usage:
 *   php websocket-server.php [host] [port]
 * 
 * Example:
 *   php websocket-server.php 0.0.0.0 8080
 */

require_once __DIR__ . '/core/WebSocket/WebSocketServer.php';

// Get host and port from command line or use defaults
$host = $argv[1] ?? '0.0.0.0';
$port = $argv[2] ?? 8080;

echo "==========================================\n";
echo "  MMB WebSocket Server\n";
echo "==========================================\n";
echo "Host: $host\n";
echo "Port: $port\n";
echo "==========================================\n\n";

// Create and run server
$server = new WebSocketServer($host, $port);
$server->run();
