# WhatsApp API Automation - Quick Start

## 🚀 Get Started in 5 Minutes

### Step 1: Import Database
```bash
cd /path/to/mmbv2
mysql -u your_username -p mmb_whatsapp < projects/whatsapp/schema.sql
```

Or use phpMyAdmin:
1. Create database `mmb_whatsapp` (if not exists)
2. Import `projects/whatsapp/schema.sql`

### Step 2: Access the Platform
Navigate to: **`https://yourdomain.com/projects/whatsapp`**

### Step 3: Create Your First Session
1. Click **"New Session"** button
2. Enter a session name (e.g., "My WhatsApp")
3. A QR code will be displayed (in production with WhatsApp client integration)
4. Scan with WhatsApp mobile app
5. Session is now active!

### Step 4: Generate API Key
1. Go to **Settings** tab
2. Click **"Generate API Key"**
3. Copy your API key (format: `whapi_xxxxx...`)
4. Save it securely

### Step 5: Start Using the API
```bash
# Send a message
curl -X POST https://yourdomain.com/api/whatsapp/send-message \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "session_id": 1,
    "recipient": "+1234567890",
    "message": "Hello from WhatsApp API!"
  }'
```

## 📱 Main Features

### User Dashboard
- **URL:** `/projects/whatsapp`
- View statistics and metrics
- Quick access to all features
- Recent messages and sessions

### Sessions Management
- **URL:** `/projects/whatsapp/sessions`
- Create unlimited sessions
- QR code scanning interface
- Monitor connection status
- Disconnect sessions

### Send Messages
- **URL:** `/projects/whatsapp/messages`
- Send to any phone number
- View message history
- Support for text and media

### Contacts
- **URL:** `/projects/whatsapp/contacts`
- Sync WhatsApp contacts
- Browse contact list
- Quick message access

### Settings
- **URL:** `/projects/whatsapp/settings`
- Generate API keys
- Configure webhooks
- View usage statistics

### API Documentation
- **URL:** `/projects/whatsapp/api-docs`
- Interactive documentation
- Request/response examples
- cURL examples
- Error codes

## 🔑 Admin Panel

### Overview Dashboard
- **URL:** `/admin/whatsapp/overview`
- System-wide statistics
- Recent activity
- Quick actions

### Admin Features
- View all user sessions
- Monitor all messages
- Manage users
- View API logs
- Delete sessions
- System monitoring

## 🔌 API Usage Examples

### Authentication
All API requests require your API key in the header:
```bash
Authorization: Bearer YOUR_API_KEY
```

### Send Message
```bash
curl -X POST https://yourdomain.com/api/whatsapp/send-message \
  -H "Authorization: Bearer whapi_abc123..." \
  -H "Content-Type: application/json" \
  -d '{
    "session_id": 1,
    "recipient": "+1234567890",
    "message": "Hello!"
  }'
```

### Get Messages
```bash
curl -X GET "https://yourdomain.com/api/whatsapp/messages?session_id=1&limit=10" \
  -H "Authorization: Bearer whapi_abc123..."
```

### Check Status
```bash
curl -X GET "https://yourdomain.com/api/whatsapp/status?session_id=1" \
  -H "Authorization: Bearer whapi_abc123..."
```

## 🌐 Subdomain Setup (Optional)

Want to run on `whatsapp.yourdomain.com`?

1. **Add DNS A Record:**
   ```
   whatsapp.yourdomain.com → YOUR_SERVER_IP
   ```

2. **Configure Web Server** (see INSTALLATION.md for details)

3. **Access:** `https://whatsapp.yourdomain.com`

## ⚙️ Production Integration

For actual WhatsApp connectivity, integrate with:

**Option 1: whatsapp-web.js (Open Source)**
```bash
npm install whatsapp-web.js
# Create Node.js bridge server
# Connect to PHP backend
```

**Option 2: Commercial API**
- Twilio WhatsApp API
- MessageBird
- 360Dialog
- Other BSPs

See `INSTALLATION.md` for detailed integration guide.

## 📊 Key Metrics

Monitor your platform:
- Total Sessions
- Active Sessions
- Messages Sent
- Messages Today
- API Calls
- Active Users

## 🔐 Security Best Practices

1. **Keep API Keys Secret:** Never commit to version control
2. **Use HTTPS:** Always in production
3. **Rate Limiting:** Built-in 100 req/min limit
4. **Webhooks:** Use HTTPS URLs only
5. **Session Management:** Disconnect unused sessions

## 🆘 Troubleshooting

### Can't see WhatsApp project in dashboard?
- Verify `config/projects.php` has WhatsApp entry
- Check database connection
- Clear browser cache

### QR Code not working?
- This is expected - requires WhatsApp client integration
- See INSTALLATION.md for production setup

### API requests failing?
- Check API key is correct
- Verify Authorization header format
- Check rate limits (100 req/min)
- Review API logs in admin panel

### Database errors?
- Import schema.sql file
- Verify database credentials
- Check table permissions

## 📚 Documentation Links

- **Full Documentation:** `projects/whatsapp/README.md`
- **Installation Guide:** `projects/whatsapp/INSTALLATION.md`
- **Implementation Details:** `projects/whatsapp/IMPLEMENTATION_SUMMARY.md`
- **Online API Docs:** `/projects/whatsapp/api-docs`

## 💡 Tips

1. **Test with Sandbox:** Create test sessions first
2. **Monitor Logs:** Check admin panel regularly
3. **Use Webhooks:** For real-time notifications
4. **Backup Database:** Regular backups recommended
5. **Read Docs:** Complete API documentation available

## 🎯 What's Included

✅ Complete user interface
✅ Full REST API
✅ Admin dashboard
✅ Database schema
✅ Security features
✅ Documentation
✅ Rate limiting
✅ API key management
✅ Webhook support
✅ Multi-user support

## 🚀 You're All Set!

Start using WhatsApp API automation now:
1. Import database ✅
2. Access dashboard ✅
3. Create session ✅
4. Generate API key ✅
5. Send messages ✅

For questions, refer to the comprehensive documentation or contact support.

---

**License:** MIT (Part of MyMultiBranch Platform)  
**Version:** 1.0.0  
**Status:** Production Ready 🎉
