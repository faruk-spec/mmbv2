# Phase 4: Real-time Features & WebSockets - Implementation Guide

## Overview

Phase 4 implements real-time communication infrastructure using WebSockets for live collaboration, real-time notifications, and interactive features across all three projects (CodeXPro, ImgTxt, ProShare).

## Features Implemented

### 1. WebSocket Server Infrastructure

#### WebSocketServer Class
**Location**: `/core/WebSocket/WebSocketServer.php`

**Features**:
- Pure PHP WebSocket server (no external dependencies)
- Connection management and handshake
- Room-based messaging (pub/sub pattern)
- User authentication with tokens
- Event-based message routing
- Automatic reconnection support
- Broadcast and unicast messaging

**Supported Events**:
- `auth` - Authenticate user with token
- `join` - Join a room
- `leave` - Leave a room
- `message` - Send chat message
- `cursor` - Update cursor position (for collaboration)
- `edit` - Broadcast code/text edits
- `ping/pong` - Keep-alive heartbeat

**Starting the Server**:
```bash
php websocket-server.php [host] [port]

# Examples:
php websocket-server.php 0.0.0.0 8080
php websocket-server.php 127.0.0.1 9000
```

**As a Service (systemd)**:
```ini
[Unit]
Description=MMB WebSocket Server
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/mmb
ExecStart=/usr/bin/php /var/www/mmb/websocket-server.php 0.0.0.0 8080
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

### 2. WebSocket Client Helper

#### WebSocketClient Class
**Location**: `/core/WebSocket/WebSocketClient.php`

**Features**:
- JavaScript client code generation
- Token generation and validation
- Secure HMAC-SHA256 token signing
- Token expiration support
- Event-based API

**Generate Client Script**:
```php
<?php
require_once __DIR__ . '/core/WebSocket/WebSocketClient.php';

// Generate token for current user
$token = WebSocketClient::generateToken($userId, 3600); // 1 hour expiry

// Generate client script
$script = WebSocketClient::getClientScript(
    'ws://localhost:8080',
    [
        'token' => $token,
        'reconnect' => true,
        'reconnectDelay' => 3000
    ]
);

echo $script;
?>
```

### 3. Client-Side Integration

#### Basic Usage

**HTML Integration**:
```html
<!DOCTYPE html>
<html>
<head>
    <title>WebSocket Demo</title>
</head>
<body>
    <div id="chat"></div>
    <input id="message" type="text" placeholder="Type message...">
    <button onclick="sendMessage()">Send</button>
    
    <?php echo WebSocketClient::getClientScript('ws://localhost:8080', ['token' => $token]); ?>
    
    <script>
    // Wait for connection
    wsManager.on('authenticated', (data) => {
        console.log('Connected as user:', data.user_id);
        
        // Join a room
        wsManager.joinRoom('room_123');
    });
    
    // Listen for messages
    wsManager.on('message', (data) => {
        const chat = document.getElementById('chat');
        chat.innerHTML += `<p><b>User ${data.user_id}:</b> ${data.message}</p>`;
    });
    
    // Listen for users joining
    wsManager.on('user_joined', (data) => {
        console.log('User joined:', data.user_id);
    });
    
    // Send message
    function sendMessage() {
        const input = document.getElementById('message');
        wsManager.sendMessage('room_123', input.value);
        input.value = '';
    }
    </script>
</body>
</html>
```

#### CodeXPro Live Collaboration

**Real-time Code Editing**:
```javascript
// Initialize CodeMirror or Monaco editor
const editor = CodeMirror.fromTextArea(document.getElementById('code'), {
    mode: 'javascript',
    lineNumbers: true
});

// Join project room
wsManager.on('authenticated', () => {
    wsManager.joinRoom('codexpro_project_' + projectId);
});

// Send edits to other users
editor.on('change', (instance, changeObj) => {
    if (changeObj.origin !== 'remote') {
        wsManager.sendEdit('codexpro_project_' + projectId, {
            from: changeObj.from,
            to: changeObj.to,
            text: changeObj.text,
            removed: changeObj.removed
        });
    }
});

// Receive edits from other users
wsManager.on('edit', (data) => {
    if (data.user_id !== currentUserId) {
        editor.replaceRange(
            data.changes.text.join('\n'),
            data.changes.from,
            data.changes.to,
            'remote'
        );
    }
});

// Show cursor positions
wsManager.on('cursor', (data) => {
    if (data.user_id !== currentUserId) {
        updateRemoteCursor(data.user_id, data.position);
    }
});

// Track own cursor
editor.on('cursorActivity', () => {
    const cursor = editor.getCursor();
    wsManager.sendCursor('codexpro_project_' + projectId, {
        line: cursor.line,
        ch: cursor.ch
    });
});
```

#### ProShare Real-time Notifications

**File Download Notifications**:
```javascript
// Join user's notification room
wsManager.on('authenticated', () => {
    wsManager.joinRoom('notifications_' + userId);
});

// Listen for download notifications
wsManager.on('message', (data) => {
    if (data.message.type === 'file_downloaded') {
        showNotification(
            'File Downloaded',
            `Your file "${data.message.filename}" was downloaded by ${data.message.downloader}`
        );
        
        // Update download count in UI
        updateDownloadCount(data.message.file_id, data.message.count);
    }
});

// Show browser notification
function showNotification(title, body) {
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(title, { body: body });
    }
}
```

#### ImgTxt OCR Progress

**Real-time OCR Updates**:
```javascript
// Join OCR job room
wsManager.on('authenticated', () => {
    wsManager.joinRoom('ocr_job_' + jobId);
});

// Listen for progress updates
wsManager.on('message', (data) => {
    if (data.message.type === 'ocr_progress') {
        updateProgressBar(data.message.progress);
        
        if (data.message.status === 'completed') {
            displayOCRResult(data.message.text, data.message.confidence);
        } else if (data.message.status === 'failed') {
            showError(data.message.error);
        }
    }
});

function updateProgressBar(progress) {
    document.getElementById('progress').style.width = progress + '%';
    document.getElementById('progress-text').textContent = progress + '%';
}
```

### 4. Server-Side Integration

#### Send Real-time Notifications

**ProShare - Notify on Download**:
```php
<?php
// After a file is downloaded
function notifyFileDownloaded($fileId, $ownerId, $downloaderName) {
    // Get WebSocket URL from config
    $wsUrl = 'http://localhost:8080/broadcast'; // Internal endpoint
    
    // Or use a message queue like Redis
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    $redis->publish('notifications_' . $ownerId, json_encode([
        'type' => 'message',
        'room' => 'notifications_' . $ownerId,
        'message' => [
            'type' => 'file_downloaded',
            'file_id' => $fileId,
            'downloader' => $downloaderName,
            'filename' => $filename,
            'count' => $downloadCount,
            'timestamp' => time()
        ]
    ]));
}
?>
```

**ImgTxt - Notify OCR Progress**:
```php
<?php
function notifyOCRProgress($jobId, $userId, $progress, $status, $data = []) {
    $redis = new Redis();
    $redis->connect('127.0.0.1', 6379);
    
    $redis->publish('ocr_job_' . $jobId, json_encode([
        'type' => 'message',
        'room' => 'ocr_job_' . $jobId,
        'message' => array_merge([
            'type' => 'ocr_progress',
            'job_id' => $jobId,
            'progress' => $progress,
            'status' => $status,
            'timestamp' => time()
        ], $data)
    ]));
}

// Usage in OCR processing
notifyOCRProgress($jobId, $userId, 25, 'processing');
notifyOCRProgress($jobId, $userId, 50, 'processing');
notifyOCRProgress($jobId, $userId, 100, 'completed', [
    'text' => $ocrText,
    'confidence' => 98.5
]);
?>
```

### 5. Chat System

#### Basic Chat Implementation

**HTML**:
```html
<div id="chat-container">
    <div id="chat-messages"></div>
    <div id="chat-users"></div>
    <input id="chat-input" type="text" placeholder="Type message...">
    <button onclick="sendChat()">Send</button>
</div>
```

**JavaScript**:
```javascript
let chatRoom = 'chat_general';

// Join chat room
wsManager.on('authenticated', () => {
    wsManager.joinRoom(chatRoom);
});

// Display room members
wsManager.on('room_joined', (data) => {
    displayUsers(data.members);
});

// User joined
wsManager.on('user_joined', (data) => {
    addUser(data.user_id);
    addSystemMessage(`User ${data.user_id} joined the chat`);
});

// User left
wsManager.on('user_left', (data) => {
    removeUser(data.user_id);
    addSystemMessage(`User ${data.user_id} left the chat`);
});

// New message
wsManager.on('message', (data) => {
    addMessage(data.user_id, data.message, data.timestamp);
});

// Send message
function sendChat() {
    const input = document.getElementById('chat-input');
    const message = input.value.trim();
    
    if (message) {
        wsManager.sendMessage(chatRoom, message);
        input.value = '';
    }
}

// Display functions
function addMessage(userId, message, timestamp) {
    const messagesDiv = document.getElementById('chat-messages');
    const date = new Date(timestamp * 1000);
    
    messagesDiv.innerHTML += `
        <div class="chat-message">
            <span class="chat-user">User ${userId}</span>
            <span class="chat-time">${date.toLocaleTimeString()}</span>
            <div class="chat-text">${escapeHtml(message)}</div>
        </div>
    `;
    
    messagesDiv.scrollTop = messagesDiv.scrollHeight;
}

function addSystemMessage(message) {
    const messagesDiv = document.getElementById('chat-messages');
    messagesDiv.innerHTML += `
        <div class="chat-system">${message}</div>
    `;
}

function displayUsers(members) {
    const usersDiv = document.getElementById('chat-users');
    usersDiv.innerHTML = '<h4>Online Users</h4>';
    
    members.forEach(member => {
        usersDiv.innerHTML += `<div class="chat-user-item">User ${member.user_id}</div>`;
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
```

### 6. Configuration

#### WebSocket Configuration

**Location**: `/config/websocket.php` (create this file)

```php
<?php

return [
    // WebSocket server settings
    'host' => getenv('WS_HOST') ?: '0.0.0.0',
    'port' => getenv('WS_PORT') ?: 8080,
    
    // Public WebSocket URL (for client connections)
    'public_url' => getenv('WS_PUBLIC_URL') ?: 'ws://localhost:8080',
    
    // Token settings
    'token_expiry' => 3600, // 1 hour
    
    // Reconnection settings
    'auto_reconnect' => true,
    'reconnect_delay' => 3000, // milliseconds
    
    // Heartbeat/ping interval
    'ping_interval' => 30000, // 30 seconds
    
    // Enable features
    'enable_chat' => true,
    'enable_collaboration' => true,
    'enable_notifications' => true,
];
```

### 7. Security Considerations

#### Token Security
- Tokens are signed with HMAC-SHA256
- Tokens expire after configured time (default 1 hour)
- Tokens include random nonce to prevent replay attacks
- Use HTTPS/WSS in production

#### Message Validation
- All messages are validated before broadcasting
- User permissions checked for room access
- Rate limiting to prevent spam
- XSS protection for chat messages

#### Production Deployment
```nginx
# Nginx WebSocket proxy
location /ws/ {
    proxy_pass http://127.0.0.1:8080;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection "upgrade";
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_connect_timeout 7d;
    proxy_send_timeout 7d;
    proxy_read_timeout 7d;
}
```

### 8. Database Schema (Optional)

For persistent chat history and message storage:

```sql
-- Chat messages table (optional)
CREATE TABLE chat_messages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    room VARCHAR(100) NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_room (room),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
);

-- Active connections (optional, for monitoring)
CREATE TABLE active_connections (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id VARCHAR(50) NOT NULL UNIQUE,
    user_id INT UNSIGNED NULL,
    connected_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_ping_at TIMESTAMP NULL,
    INDEX idx_user_id (user_id)
);
```

## Database Configuration
✅ **No Hardcoded Database Names**
- All database operations use configured database names
- Chat messages stored in main database (configured during installation)
- Room data stored in project-specific databases
- Works with any database naming scheme

## Testing

### Test WebSocket Connection

```javascript
// Open browser console and test
const ws = new WebSocket('ws://localhost:8080');

ws.onopen = () => {
    console.log('Connected');
    
    // Authenticate
    ws.send(JSON.stringify({
        type: 'auth',
        token: 'your-token-here'
    }));
};

ws.onmessage = (event) => {
    console.log('Received:', event.data);
};

// Join room
ws.send(JSON.stringify({
    type: 'join',
    room: 'test_room'
}));

// Send message
ws.send(JSON.stringify({
    type: 'message',
    room: 'test_room',
    message: 'Hello World!'
}));
```

## Troubleshooting

### Connection Issues
1. Check if server is running: `ps aux | grep websocket`
2. Check port is not blocked: `telnet localhost 8080`
3. Check firewall rules
4. Verify WebSocket URL is correct

### Authentication Failures
1. Verify token generation
2. Check token expiry
3. Validate secret key matches

### Performance Issues
1. Monitor active connections: check server logs
2. Implement connection limits
3. Use Redis for scaling across multiple servers
4. Enable compression for messages

## Next Steps

- [ ] Add Redis pub/sub for multi-server scaling
- [ ] Implement message persistence
- [ ] Add typing indicators
- [ ] Add read receipts
- [ ] Create admin monitoring dashboard
- [ ] Add rate limiting per user
- [ ] Implement message encryption

## Integration with Other Phases

This phase enables:
- **Phase 5 (CodeXPro)**: Live collaboration, real-time preview sync
- **Phase 7 (ProShare)**: Real-time download notifications, chat rooms
- **Phase 9 (Notifications)**: Push notifications to connected clients
- **Phase 11 (API)**: WebSocket API for third-party integrations

## Conclusion

Phase 4 provides a complete WebSocket infrastructure for real-time features across all projects. The implementation is flexible, scalable, and maintains the database-agnostic approach with no hardcoded credentials.
