# WhatsApp API Automation

A SaaS-based WhatsApp API automation platform that allows users to scan and login to their WhatsApp accounts and use automation features through REST APIs.

## Features

- **QR Code Authentication**: Scan QR code to connect WhatsApp Web
- **Session Management**: Create and manage multiple WhatsApp sessions
- **Message Automation**: Send and receive WhatsApp messages via API
- **Contact Management**: Sync and manage WhatsApp contacts
- **API Integration**: RESTful API for third-party integration
- **Webhook Support**: Real-time notifications via webhooks
- **Rate Limiting**: Built-in API rate limiting (100 requests/minute)
- **Security**: API key authentication with secure session management

## Installation

1. **Database Setup**
   ```bash
   mysql -u your_username -p mmb_whatsapp < schema.sql
   ```

2. **Configuration**
   - The project is automatically configured when you visit the dashboard
   - Generate your API key from Settings page

3. **Usage**
   - Create a WhatsApp session from the Sessions page
   - Scan the QR code with your WhatsApp mobile app
   - Once connected, use the API endpoints to send messages

## API Endpoints

### Authentication
All API requests require an API key in the Authorization header:
```
Authorization: Bearer YOUR_API_KEY
```

### Send Message
```http
POST /api/whatsapp/send-message
Content-Type: application/json

{
  "session_id": 1,
  "recipient": "+1234567890",
  "message": "Hello from WhatsApp API!"
}
```

### Get Messages
```http
GET /api/whatsapp/messages?session_id=1
Authorization: Bearer YOUR_API_KEY
```

### Get Session Status
```http
GET /api/whatsapp/status?session_id=1
Authorization: Bearer YOUR_API_KEY
```

For complete API documentation, visit `/projects/whatsapp/api-docs`

## Architecture

### Database Schema
- `whatsapp_sessions`: Store WhatsApp session information
- `whatsapp_messages`: Store sent and received messages
- `whatsapp_contacts`: Store synced contacts
- `whatsapp_api_keys`: Store user API keys
- `whatsapp_user_settings`: Store user preferences and webhook URLs
- `whatsapp_api_logs`: Store API request logs

### Integration Requirements

**Note**: This is a framework implementation. For production use, you need to integrate with a WhatsApp Web client library such as:

- [whatsapp-web.js](https://github.com/pedroslopez/whatsapp-web.js) (Node.js)
- [Baileys](https://github.com/WhiskeySockets/Baileys) (Node.js)
- Or use a commercial WhatsApp Business API provider

The integration typically involves:
1. Setting up a Node.js WebSocket server running the WhatsApp client
2. Creating a bridge between PHP and Node.js (via HTTP/WebSocket)
3. Handling QR code generation and session management
4. Implementing message sending/receiving callbacks

## Security Features

- API key authentication
- Rate limiting (100 requests/minute)
- CSRF protection on web forms
- Session fingerprinting
- Secure password hashing for user accounts
- SQL injection prevention via prepared statements

## Rate Limits

- **API Calls**: 100 requests per minute per API key
- **Sessions**: Maximum 5 active sessions per user
- **Messages**: Subject to WhatsApp's official limits

## Webhook Events

Configure a webhook URL in Settings to receive real-time events:

```json
{
  "event": "message.received",
  "session_id": 1,
  "data": {
    "from": "+1234567890",
    "message": "Hello!",
    "timestamp": "2026-01-25T10:30:00Z"
  }
}
```

## License

Part of the MyMultiBranch Platform - MIT License

## Support

For API documentation and support, visit:
- Dashboard: `/projects/whatsapp`
- API Docs: `/projects/whatsapp/api-docs`
- Settings: `/projects/whatsapp/settings`
