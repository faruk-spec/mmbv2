<?php

/**
 * WebSocket Client Helper
 * 
 * Helper class for JavaScript WebSocket client integration.
 * Generates client-side code for connecting to WebSocket server.
 */

class WebSocketClient
{
    /**
     * Generate JavaScript code for WebSocket client
     */
    public static function getClientScript($wsUrl, $options = [])
    {
        $authToken = $options['token'] ?? '';
        $reconnect = $options['reconnect'] ?? true;
        $reconnectDelay = $options['reconnectDelay'] ?? 3000;
        
        $reconnectStr = $reconnect ? 'true' : 'false';
        
        return <<<JS
<script>
class WebSocketManager {
    constructor(url, token) {
        this.url = url;
        this.token = token;
        this.ws = null;
        this.reconnect = {$reconnectStr};
        this.reconnectDelay = {$reconnectDelay};
        this.reconnecting = false;
        this.currentRoom = null;
        this.listeners = {};
        
        this.connect();
    }
    
    connect() {
        console.log('Connecting to WebSocket server...');
        
        this.ws = new WebSocket(this.url);
        
        this.ws.onopen = () => {
            console.log('WebSocket connected');
            this.reconnecting = false;
            
            // Authenticate
            this.send({
                type: 'auth',
                token: this.token
            });
            
            // Rejoin room if previously connected
            if (this.currentRoom) {
                this.joinRoom(this.currentRoom);
            }
            
            this.emit('connected');
        };
        
        this.ws.onmessage = (event) => {
            try {
                const data = JSON.parse(event.data);
                this.handleMessage(data);
            } catch (e) {
                console.error('Failed to parse message:', e);
            }
        };
        
        this.ws.onerror = (error) => {
            console.error('WebSocket error:', error);
            this.emit('error', error);
        };
        
        this.ws.onclose = () => {
            console.log('WebSocket disconnected');
            this.emit('disconnected');
            
            if (this.reconnect && !this.reconnecting) {
                this.reconnecting = true;
                setTimeout(() => this.connect(), this.reconnectDelay);
            }
        };
    }
    
    handleMessage(data) {
        switch (data.type) {
            case 'auth_success':
                console.log('Authenticated as user:', data.user_id);
                this.emit('authenticated', data);
                break;
                
            case 'room_joined':
                console.log('Joined room:', data.room);
                this.currentRoom = data.room;
                this.emit('room_joined', data);
                break;
                
            case 'user_joined':
                console.log('User joined:', data.user_id);
                this.emit('user_joined', data);
                break;
                
            case 'user_left':
                console.log('User left:', data.user_id);
                this.emit('user_left', data);
                break;
                
            case 'message':
                this.emit('message', data);
                break;
                
            case 'cursor':
                this.emit('cursor', data);
                break;
                
            case 'edit':
                this.emit('edit', data);
                break;
                
            case 'pong':
                this.emit('pong');
                break;
                
            case 'error':
                console.error('Server error:', data.message);
                this.emit('error', data);
                break;
                
            default:
                this.emit(data.type, data);
        }
    }
    
    send(data) {
        if (this.ws && this.ws.readyState === WebSocket.OPEN) {
            this.ws.send(JSON.stringify(data));
        } else {
            console.warn('WebSocket not connected');
        }
    }
    
    joinRoom(room) {
        this.send({
            type: 'join',
            room: room
        });
    }
    
    leaveRoom(room) {
        this.send({
            type: 'leave',
            room: room
        });
        this.currentRoom = null;
    }
    
    sendMessage(room, message) {
        this.send({
            type: 'message',
            room: room,
            message: message
        });
    }
    
    sendCursor(room, position) {
        this.send({
            type: 'cursor',
            room: room,
            position: position
        });
    }
    
    sendEdit(room, changes) {
        this.send({
            type: 'edit',
            room: room,
            changes: changes
        });
    }
    
    ping() {
        this.send({ type: 'ping' });
    }
    
    on(event, callback) {
        if (!this.listeners[event]) {
            this.listeners[event] = [];
        }
        this.listeners[event].push(callback);
    }
    
    off(event, callback) {
        if (!this.listeners[event]) return;
        
        if (callback) {
            this.listeners[event] = this.listeners[event].filter(cb => cb !== callback);
        } else {
            delete this.listeners[event];
        }
    }
    
    emit(event, data) {
        if (!this.listeners[event]) return;
        
        this.listeners[event].forEach(callback => {
            try {
                callback(data);
            } catch (e) {
                console.error('Error in event handler:', e);
            }
        });
    }
    
    disconnect() {
        this.reconnect = false;
        if (this.ws) {
            this.ws.close();
        }
    }
}

// Initialize WebSocket
const ws = new WebSocketManager('{$wsUrl}', '{$authToken}');

// Export for global use
window.wsManager = ws;
</script>
JS;
    }
    
    /**
     * Generate token for WebSocket authentication
     */
    public static function generateToken($userId, $expiresIn = 3600)
    {
        $data = [
            'user_id' => $userId,
            'expires' => time() + $expiresIn,
            'random' => bin2hex(random_bytes(16))
        ];
        
        $json = json_encode($data);
        $signature = hash_hmac('sha256', $json, self::getSecret());
        
        return base64_encode($json . '.' . $signature);
    }
    
    /**
     * Validate WebSocket token
     */
    public static function validateToken($token)
    {
        try {
            $decoded = base64_decode($token);
            $parts = explode('.', $decoded);
            
            if (count($parts) !== 2) {
                return false;
            }
            
            list($json, $signature) = $parts;
            
            $expectedSignature = hash_hmac('sha256', $json, self::getSecret());
            
            if (!hash_equals($expectedSignature, $signature)) {
                return false;
            }
            
            $data = json_decode($json, true);
            
            if (!$data || !isset($data['user_id']) || !isset($data['expires'])) {
                return false;
            }
            
            if ($data['expires'] < time()) {
                return false;
            }
            
            return $data['user_id'];
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Get secret key for token signing
     */
    private static function getSecret()
    {
        // Use application secret or generate one
        if (file_exists(__DIR__ . '/../../config/app.php')) {
            $config = require __DIR__ . '/../../config/app.php';
            return $config['secret'] ?? 'default-secret-change-me';
        }
        
        return 'default-secret-change-me';
    }
}
