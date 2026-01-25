# WhatsApp API Automation - Installation & Setup Guide

## Quick Start Guide

### 1. Database Setup

Run the database schema to create the required tables:

```bash
cd /path/to/mmbv2
mysql -u your_username -p mmb_whatsapp < projects/whatsapp/schema.sql
```

Or use phpMyAdmin:
1. Select the `mmb_whatsapp` database (create if it doesn't exist)
2. Import the `projects/whatsapp/schema.sql` file

### 2. Configuration

The WhatsApp project is automatically configured in `config/projects.php`:

```php
'whatsapp' => [
    'name' => 'WhatsApp API',
    'description' => 'WhatsApp API automation and messaging platform',
    'icon' => 'message-circle',
    'color' => '#25D366',
    'enabled' => true,
    'database' => 'mmb_whatsapp',
    'url' => '/projects/whatsapp'
]
```

### 3. Access the Platform

**User Dashboard:**
- Navigate to: `https://yourdomain.com/projects/whatsapp`
- Or click on "WhatsApp API" from the main dashboard

**Admin Panel:**
- Navigate to: `https://yourdomain.com/admin/whatsapp/overview`
- Access via Admin Panel → Projects → WhatsApp API

### 4. Create Your First Session

1. Go to `/projects/whatsapp/sessions`
2. Click "New Session"
3. Enter a session name
4. Scan the QR code with WhatsApp mobile app
5. Start sending messages!

### 5. Generate API Key

1. Go to `/projects/whatsapp/settings`
2. Click "Generate API Key"
3. Copy and save your API key securely
4. Use it to authenticate API requests

## Production Integration

**Note:** This is a framework implementation. For production use with actual WhatsApp integration:

### Option 1: Using whatsapp-web.js (Recommended)

1. **Install Node.js dependencies:**
```bash
npm install whatsapp-web.js qrcode-terminal
```

2. **Create a bridge server** (`whatsapp-bridge.js`):
```javascript
const { Client, LocalAuth } = require('whatsapp-web.js');
const express = require('express');
const app = express();

const client = new Client({
    authStrategy: new LocalAuth()
});

client.on('qr', (qr) => {
    // Send QR to PHP backend
});

client.on('ready', () => {
    console.log('Client is ready!');
});

client.on('message', async (msg) => {
    // Forward to PHP webhook
});

app.post('/send-message', async (req, res) => {
    const { recipient, message } = req.body;
    await client.sendMessage(recipient, message);
    res.json({ success: true });
});

app.listen(3000);
client.initialize();
```

3. **Update PHP controllers** to communicate with Node.js bridge via HTTP/WebSocket

### Option 2: Using Commercial API

Integrate with commercial WhatsApp Business API providers:
- Twilio WhatsApp API
- MessageBird
- 360Dialog
- Or other WhatsApp Business Solution Providers (BSPs)

## API Usage Examples

### Send Message

```bash
curl -X POST https://yourdomain.com/api/whatsapp/send-message \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "session_id": 1,
    "recipient": "+1234567890",
    "message": "Hello from WhatsApp API!"
  }'
```

### Get Session Status

```bash
curl -X GET https://yourdomain.com/api/whatsapp/status?session_id=1 \
  -H "Authorization: Bearer YOUR_API_KEY"
```

### Get Messages

```bash
curl -X GET https://yourdomain.com/api/whatsapp/messages?session_id=1&limit=10 \
  -H "Authorization: Bearer YOUR_API_KEY"
```

## Features

✅ **Session Management**
- Create multiple WhatsApp sessions
- QR code authentication
- Session status monitoring
- Automatic reconnection

✅ **Message Automation**
- Send text messages
- Send media (images, videos, documents)
- Message history and tracking
- Incoming message handling

✅ **Contact Management**
- Sync WhatsApp contacts
- Contact database
- Search and filter contacts

✅ **REST API**
- Full REST API for automation
- API key authentication
- Rate limiting (100 req/min)
- Comprehensive documentation

✅ **Webhooks**
- Real-time event notifications
- Configurable webhook URLs
- Message received events
- Session status events

✅ **Admin Dashboard**
- Monitor all sessions
- View all messages
- User management
- API usage logs
- Statistics and analytics

## Security Features

- ✅ API key authentication
- ✅ Rate limiting
- ✅ CSRF protection
- ✅ SQL injection prevention
- ✅ Session fingerprinting
- ✅ Secure password hashing

## Subdomain Setup

To run WhatsApp API on a subdomain (e.g., `whatsapp.yourdomain.com`):

### 1. DNS Configuration

Add A record:
```
whatsapp.yourdomain.com → YOUR_SERVER_IP
```

### 2. Apache VirtualHost

```apache
<VirtualHost *:80>
    ServerName whatsapp.yourdomain.com
    DocumentRoot /path/to/mmbv2/public
    
    <Directory /path/to/mmbv2/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Rewrite to WhatsApp project
    RewriteEngine On
    RewriteRule ^/(.*)$ /projects/whatsapp/$1 [L,QSA]
</VirtualHost>
```

### 3. Nginx Configuration

```nginx
server {
    listen 80;
    server_name whatsapp.yourdomain.com;
    root /path/to/mmbv2/public;
    
    location / {
        try_files $uri $uri/ /projects/whatsapp/index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        include fastcgi_params;
    }
}
```

## Troubleshooting

### Database Connection Issues
- Verify database credentials in `config/database.php`
- Ensure `mmb_whatsapp` database exists
- Check user permissions

### QR Code Not Generating
- This is expected - integrate with whatsapp-web.js for actual QR codes
- See "Production Integration" section above

### API Requests Failing
- Verify API key is correct
- Check rate limits (100 req/min)
- Ensure Authorization header is set correctly

### Session Not Connecting
- Requires WhatsApp Web client integration (see production setup)
- Check Node.js bridge is running (if using whatsapp-web.js)

## Support & Documentation

- **User Dashboard**: `/projects/whatsapp`
- **API Documentation**: `/projects/whatsapp/api-docs`
- **Settings**: `/projects/whatsapp/settings`
- **Admin Panel**: `/admin/whatsapp/overview`

## License

Part of MyMultiBranch Platform - MIT License

---

For questions or support, refer to the main project documentation or contact the system administrator.
