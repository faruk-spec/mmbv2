<?php

/**
 * WebSocket Server
 * 
 * Simple WebSocket server implementation for real-time features.
 * Handles connections, authentication, room management, and message broadcasting.
 * 
 * Usage:
 *   $server = new WebSocketServer('0.0.0.0', 8080);
 *   $server->run();
 */

class WebSocketServer
{
    private $host;
    private $port;
    private $socket;
    private $clients = [];
    private $rooms = [];
    private $userMap = []; // clientId => userId
    
    /**
     * Constructor
     */
    public function __construct($host = '0.0.0.0', $port = 8080)
    {
        $this->host = $host;
        $this->port = $port;
    }
    
    /**
     * Start the WebSocket server
     */
    public function run()
    {
        // Create socket
        $this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->socket === false) {
            die("Failed to create socket: " . socket_strerror(socket_last_error()) . "\n");
        }
        
        // Set socket options
        socket_set_option($this->socket, SOL_SOCKET, SO_REUSEADDR, 1);
        
        // Bind socket
        if (!socket_bind($this->socket, $this->host, $this->port)) {
            die("Failed to bind socket: " . socket_strerror(socket_last_error()) . "\n");
        }
        
        // Listen on socket
        if (!socket_listen($this->socket, 5)) {
            die("Failed to listen on socket: " . socket_strerror(socket_last_error()) . "\n");
        }
        
        echo "WebSocket server started on {$this->host}:{$this->port}\n";
        
        // Main server loop
        while (true) {
            $read = array_merge([$this->socket], $this->clients);
            $write = null;
            $except = null;
            
            if (socket_select($read, $write, $except, 0, 10) < 1) {
                continue;
            }
            
            // Check for new connections
            if (in_array($this->socket, $read)) {
                $newClient = socket_accept($this->socket);
                if ($newClient !== false) {
                    $this->clients[] = $newClient;
                    echo "New connection: " . $this->getClientId($newClient) . "\n";
                }
                
                $key = array_search($this->socket, $read);
                unset($read[$key]);
            }
            
            // Handle client messages
            foreach ($read as $client) {
                $data = @socket_read($client, 4096);
                
                if ($data === false || $data === '') {
                    $this->disconnect($client);
                    continue;
                }
                
                // Check if handshake is needed
                if (!isset($this->clients[array_search($client, $this->clients)]['handshake'])) {
                    $this->handshake($client, $data);
                } else {
                    $this->handleMessage($client, $data);
                }
            }
        }
    }
    
    /**
     * Perform WebSocket handshake
     */
    private function handshake($client, $headers)
    {
        if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $headers, $match)) {
            $key = trim($match[1]);
            $acceptKey = base64_encode(sha1($key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11', true));
            
            $response = "HTTP/1.1 101 Switching Protocols\r\n";
            $response .= "Upgrade: websocket\r\n";
            $response .= "Connection: Upgrade\r\n";
            $response .= "Sec-WebSocket-Accept: $acceptKey\r\n\r\n";
            
            socket_write($client, $response, strlen($response));
            
            $clientId = $this->getClientId($client);
            $index = array_search($client, $this->clients);
            $this->clients[$index] = ['socket' => $client, 'handshake' => true];
            
            echo "Handshake completed for client: $clientId\n";
        }
    }
    
    /**
     * Handle incoming WebSocket message
     */
    private function handleMessage($client, $data)
    {
        $message = $this->decode($data);
        
        if ($message === false) {
            return;
        }
        
        $payload = json_decode($message, true);
        
        if (!$payload || !isset($payload['type'])) {
            return;
        }
        
        $clientId = $this->getClientId($client);
        
        switch ($payload['type']) {
            case 'auth':
                $this->handleAuth($client, $payload);
                break;
                
            case 'join':
                $this->handleJoin($client, $payload);
                break;
                
            case 'leave':
                $this->handleLeave($client, $payload);
                break;
                
            case 'message':
                $this->handleChatMessage($client, $payload);
                break;
                
            case 'cursor':
                $this->handleCursorUpdate($client, $payload);
                break;
                
            case 'edit':
                $this->handleEdit($client, $payload);
                break;
                
            case 'ping':
                $this->send($client, ['type' => 'pong']);
                break;
                
            default:
                echo "Unknown message type: {$payload['type']}\n";
        }
    }
    
    /**
     * Handle authentication
     */
    private function handleAuth($client, $payload)
    {
        $token = $payload['token'] ?? null;
        
        if (!$token) {
            $this->send($client, [
                'type' => 'error',
                'message' => 'Token required'
            ]);
            return;
        }
        
        // Validate token (implement your own validation)
        $userId = $this->validateToken($token);
        
        if ($userId) {
            $clientId = $this->getClientId($client);
            $this->userMap[$clientId] = $userId;
            
            $this->send($client, [
                'type' => 'auth_success',
                'user_id' => $userId
            ]);
            
            echo "Client $clientId authenticated as user $userId\n";
        } else {
            $this->send($client, [
                'type' => 'error',
                'message' => 'Invalid token'
            ]);
        }
    }
    
    /**
     * Handle join room
     */
    private function handleJoin($client, $payload)
    {
        $room = $payload['room'] ?? null;
        
        if (!$room) {
            return;
        }
        
        $clientId = $this->getClientId($client);
        
        if (!isset($this->rooms[$room])) {
            $this->rooms[$room] = [];
        }
        
        $this->rooms[$room][$clientId] = $client;
        
        // Notify others in room
        $this->broadcast($room, [
            'type' => 'user_joined',
            'user_id' => $this->userMap[$clientId] ?? null,
            'client_id' => $clientId
        ], $client);
        
        // Send current room members to new user
        $members = [];
        foreach ($this->rooms[$room] as $cId => $c) {
            if ($cId !== $clientId) {
                $members[] = [
                    'client_id' => $cId,
                    'user_id' => $this->userMap[$cId] ?? null
                ];
            }
        }
        
        $this->send($client, [
            'type' => 'room_joined',
            'room' => $room,
            'members' => $members
        ]);
        
        echo "Client $clientId joined room: $room\n";
    }
    
    /**
     * Handle leave room
     */
    private function handleLeave($client, $payload)
    {
        $room = $payload['room'] ?? null;
        
        if (!$room || !isset($this->rooms[$room])) {
            return;
        }
        
        $clientId = $this->getClientId($client);
        unset($this->rooms[$room][$clientId]);
        
        // Notify others
        $this->broadcast($room, [
            'type' => 'user_left',
            'user_id' => $this->userMap[$clientId] ?? null,
            'client_id' => $clientId
        ]);
        
        // Clean up empty rooms
        if (empty($this->rooms[$room])) {
            unset($this->rooms[$room]);
        }
        
        echo "Client $clientId left room: $room\n";
    }
    
    /**
     * Handle chat message
     */
    private function handleChatMessage($client, $payload)
    {
        $room = $payload['room'] ?? null;
        $message = $payload['message'] ?? null;
        
        if (!$room || !$message) {
            return;
        }
        
        $clientId = $this->getClientId($client);
        
        $this->broadcast($room, [
            'type' => 'message',
            'user_id' => $this->userMap[$clientId] ?? null,
            'client_id' => $clientId,
            'message' => $message,
            'timestamp' => time()
        ]);
    }
    
    /**
     * Handle cursor update (for collaboration)
     */
    private function handleCursorUpdate($client, $payload)
    {
        $room = $payload['room'] ?? null;
        $position = $payload['position'] ?? null;
        
        if (!$room || $position === null) {
            return;
        }
        
        $clientId = $this->getClientId($client);
        
        $this->broadcast($room, [
            'type' => 'cursor',
            'user_id' => $this->userMap[$clientId] ?? null,
            'client_id' => $clientId,
            'position' => $position
        ], $client);
    }
    
    /**
     * Handle code/text edit
     */
    private function handleEdit($client, $payload)
    {
        $room = $payload['room'] ?? null;
        $changes = $payload['changes'] ?? null;
        
        if (!$room || !$changes) {
            return;
        }
        
        $clientId = $this->getClientId($client);
        
        $this->broadcast($room, [
            'type' => 'edit',
            'user_id' => $this->userMap[$clientId] ?? null,
            'client_id' => $clientId,
            'changes' => $changes,
            'timestamp' => microtime(true)
        ], $client);
    }
    
    /**
     * Broadcast message to all clients in a room
     */
    private function broadcast($room, $data, $except = null)
    {
        if (!isset($this->rooms[$room])) {
            return;
        }
        
        foreach ($this->rooms[$room] as $client) {
            if ($except && $client === $except) {
                continue;
            }
            
            $this->send($client, $data);
        }
    }
    
    /**
     * Send message to a specific client
     */
    private function send($client, $data)
    {
        $message = json_encode($data);
        $encoded = $this->encode($message);
        @socket_write($client, $encoded, strlen($encoded));
    }
    
    /**
     * Disconnect a client
     */
    private function disconnect($client)
    {
        $clientId = $this->getClientId($client);
        
        // Remove from all rooms
        foreach ($this->rooms as $room => $clients) {
            if (isset($clients[$clientId])) {
                $this->broadcast($room, [
                    'type' => 'user_left',
                    'user_id' => $this->userMap[$clientId] ?? null,
                    'client_id' => $clientId
                ]);
                
                unset($this->rooms[$room][$clientId]);
                
                if (empty($this->rooms[$room])) {
                    unset($this->rooms[$room]);
                }
            }
        }
        
        // Remove from user map
        unset($this->userMap[$clientId]);
        
        // Close socket
        $key = array_search($client, $this->clients);
        if ($key !== false) {
            unset($this->clients[$key]);
        }
        
        @socket_close($client);
        
        echo "Client disconnected: $clientId\n";
    }
    
    /**
     * Get client ID
     */
    private function getClientId($client)
    {
        return (int)$client;
    }
    
    /**
     * Validate authentication token
     */
    private function validateToken($token)
    {
        // Implement your own token validation
        // This is a placeholder that should be replaced with real validation
        
        // For example, you could:
        // 1. Decode JWT token
        // 2. Check session in database
        // 3. Verify API key
        
        // For now, return a fake user ID for testing
        return substr($token, 0, 8);
    }
    
    /**
     * Encode message for WebSocket
     */
    private function encode($message)
    {
        $length = strlen($message);
        $header = chr(129); // Text frame, FIN bit set
        
        if ($length <= 125) {
            $header .= chr($length);
        } elseif ($length <= 65535) {
            $header .= chr(126) . pack('n', $length);
        } else {
            $header .= chr(127) . pack('J', $length);
        }
        
        return $header . $message;
    }
    
    /**
     * Decode message from WebSocket
     */
    private function decode($data)
    {
        $length = ord($data[1]) & 127;
        
        if ($length == 126) {
            $masks = substr($data, 4, 4);
            $payload = substr($data, 8);
        } elseif ($length == 127) {
            $masks = substr($data, 10, 4);
            $payload = substr($data, 14);
        } else {
            $masks = substr($data, 2, 4);
            $payload = substr($data, 6);
        }
        
        $text = '';
        for ($i = 0; $i < strlen($payload); $i++) {
            $text .= $payload[$i] ^ $masks[$i % 4];
        }
        
        return $text;
    }
}
