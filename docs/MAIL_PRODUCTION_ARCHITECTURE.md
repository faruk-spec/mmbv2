# Production-Level Mail Server Architecture Plan
## Enterprise-Grade Email System (Google/Zoho-like)

### 🎯 Project Overview

**Goal:** Build a production-ready, enterprise-grade email hosting platform comparable to Google Workspace or Zoho Mail, with:
- Multi-tenant architecture
- Scalable infrastructure
- Modern UI/UX
- Complete admin controls
- Professional security

### 📋 System Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     Main Application                         │
│              mymultibranch.com                               │
│                                                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │   Dashboard  │  │    Admin     │  │   Projects   │     │
│  │   /dashboard │  │    /admin    │  │   /projects  │     │
│  └──────────────┘  └──────────────┘  └──────────────┘     │
│         │                 │                  │              │
│         └─────────────────┴──────────────────┘              │
│                          │                                   │
│                   API Gateway                                │
└──────────────────────────┼──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│              Mail Subdomain Application                      │
│              mail.mymultibranch.com                          │
│                                                              │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐  ┌──────────┐  │
│  │ Webmail  │  │ Composer │  │ Settings │  │ Calendar │  │
│  │          │  │          │  │          │  │          │  │
│  └──────────┘  └──────────┘  └──────────┘  └──────────┘  │
│                                                              │
│         Connected via: SSO, API, Shared Sessions             │
└─────────────────────────────────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                   Backend Services                           │
│              projects/mail/                                  │
│                                                              │
│  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐        │
│  │ Mail Server │  │  Database   │  │    Queue    │        │
│  │  (SMTP/     │  │  (MySQL)    │  │  (Redis)    │        │
│  │   IMAP)     │  │             │  │             │        │
│  └─────────────┘  └─────────────┘  └─────────────┘        │
└─────────────────────────────────────────────────────────────┘
```

### 🏗️ Directory Structure

```
mmbv2/
├── projects/mail/                    # Backend system
│   ├── controllers/
│   │   ├── API/                      # REST API Controllers
│   │   │   ├── MailboxAPIController.php
│   │   │   ├── MessageAPIController.php
│   │   │   ├── ContactAPIController.php
│   │   │   └── CalendarAPIController.php
│   │   ├── Admin/
│   │   │   ├── MailAdminController.php
│   │   │   ├── SubscriberManagementController.php
│   │   │   └── SystemSettingsController.php
│   │   └── Subscriber/
│   │       ├── DomainController.php
│   │       ├── MailboxController.php
│   │       └── BillingController.php
│   │
│   ├── models/
│   │   ├── Mailbox.php
│   │   ├── Message.php
│   │   ├── Domain.php
│   │   ├── Subscriber.php
│   │   └── Folder.php
│   │
│   ├── services/
│   │   ├── MailService.php           # Core mail operations
│   │   ├── IMAPService.php           # IMAP integration
│   │   ├── SMTPService.php           # SMTP integration
│   │   ├── QueueService.php          # Email queue
│   │   └── SearchService.php         # Email search
│   │
│   ├── routes/
│   │   ├── api.php                   # API routes
│   │   ├── web.php                   # Web routes
│   │   └── admin.php                 # Admin routes
│   │
│   ├── migrations/                   # Database migrations
│   ├── views/                        # Backend views (admin)
│   └── schema.sql                    # Database schema
│
├── subdomain/                        # Frontend for mail.mymultibranch.com
│   ├── public/
│   │   ├── index.php                 # Entry point
│   │   ├── assets/
│   │   │   ├── css/
│   │   │   │   ├── mail.css          # Mail UI styles
│   │   │   │   └── theme.css         # Theme integration
│   │   │   ├── js/
│   │   │   │   ├── mail-app.js       # Main mail application
│   │   │   │   ├── composer.js       # Email composer
│   │   │   │   ├── inbox.js          # Inbox interface
│   │   │   │   └── api-client.js     # API communication
│   │   │   └── images/
│   │   └── .htaccess
│   │
│   ├── views/
│   │   ├── layouts/
│   │   │   └── mail-app.php          # Main layout
│   │   ├── inbox/
│   │   │   ├── index.php             # Inbox view
│   │   │   ├── compose.php           # Compose email
│   │   │   └── read.php              # Read email
│   │   ├── settings/
│   │   │   ├── account.php
│   │   │   ├── filters.php
│   │   │   └── signature.php
│   │   └── auth/
│   │       ├── login.php             # SSO login
│   │       └── oauth.php             # OAuth callback
│   │
│   ├── config/
│   │   ├── app.php                   # App configuration
│   │   └── api.php                   # API endpoints
│   │
│   └── README.md                     # Deployment instructions
│
└── docs/
    └── mail-architecture.md          # This document
```

### 🔧 Technology Stack

**Backend (projects/mail/):**
- **Language:** PHP 8.1+
- **Framework:** Custom MVC
- **Database:** MySQL 8.0+ / MariaDB 10.6+
- **Cache:** Redis 6.0+
- **Queue:** Redis Queue / RabbitMQ
- **Mail Server:** Postfix + Dovecot (SMTP/IMAP)
- **Search:** ElasticSearch (optional, for advanced search)

**Frontend (mail.mymultibranch.com):**
- **UI Framework:** Vue.js 3 / React 18
- **CSS:** Tailwind CSS + Custom components
- **Icons:** Font Awesome 6 / Heroicons
- **Build Tool:** Vite / Webpack
- **API Client:** Axios
- **State Management:** Pinia / Redux

**Infrastructure:**
- **Web Server:** Nginx + PHP-FPM
- **SSL:** Let's Encrypt (Certbot)
- **Monitoring:** Prometheus + Grafana
- **Logging:** ELK Stack (Elasticsearch, Logstash, Kibana)

### 📊 Database Schema (Enhanced)

```sql
-- Core Tables
mail_subscribers             # Tenant accounts
mail_subscriptions          # Active subscriptions
mail_subscription_plans     # Available plans
mail_domains                # Custom domains
mail_mailboxes              # User mailboxes
mail_aliases                # Email aliases
mail_forwarding_rules       # Email forwarding

-- Email Storage
mail_folders                # Mailbox folders (Inbox, Sent, etc.)
mail_messages               # Email messages
mail_attachments            # File attachments
mail_message_recipients     # Message recipients
mail_message_flags          # Read, starred, etc.

-- Features
mail_contacts               # Address book
mail_contact_groups         # Contact groups
mail_calendars              # Calendar events
mail_filters                # Email filters/rules
mail_signatures             # Email signatures
mail_templates              # Email templates
mail_auto_responders        # Auto-reply rules

-- Security & Monitoring
mail_login_attempts         # Track login attempts
mail_access_logs            # Access logging
mail_audit_logs             # Audit trail
mail_quarantine             # Spam/suspicious emails
mail_blacklist              # Blocked senders
mail_whitelist              # Allowed senders

-- Queue & Processing
mail_queue                  # Outgoing email queue
mail_queue_failed           # Failed emails
mail_delivery_logs          # Delivery tracking

-- Billing
mail_billing_history        # Transaction history
mail_invoices               # Generated invoices
mail_payment_methods        # Stored payment methods
mail_usage_metrics          # Usage tracking
```

### 🎨 UI/UX Design Principles

**Following Google Gmail / Zoho Mail patterns:**

1. **Clean Interface:**
   - Minimalist design
   - White space utilization
   - Clear typography

2. **Three-Pane Layout:**
   ```
   ┌─────────┬──────────────┬────────────────────┐
   │ Sidebar │ Message List │  Message Preview   │
   │         │              │                    │
   │ Folders │   Inbox      │  Email Content     │
   │ Labels  │   Messages   │                    │
   │         │              │                    │
   └─────────┴──────────────┴────────────────────┘
   ```

3. **Responsive Design:**
   - Mobile-first approach
   - Adaptive layouts
   - Touch-friendly

4. **Dark Mode:**
   - Integrated with navbar theme toggle
   - Smooth transitions
   - Consistent colors

5. **Performance:**
   - Lazy loading
   - Virtual scrolling for long lists
   - Progressive Web App (PWA)

### 🔐 Security Features

1. **Authentication:**
   - SSO (Single Sign-On) with main app
   - OAuth 2.0 / OpenID Connect
   - 2FA (Two-Factor Authentication)
   - Session management

2. **Authorization:**
   - Role-based access control (RBAC)
   - Permission levels
   - API key management

3. **Data Protection:**
   - End-to-end encryption (optional)
   - TLS/SSL for transport
   - Database encryption at rest
   - GDPR compliance

4. **Email Security:**
   - SPF, DKIM, DMARC
   - Spam filtering (SpamAssassin)
   - Virus scanning (ClamAV)
   - Rate limiting

### 🚀 API Design

**RESTful API Endpoints:**

```
Authentication:
POST   /api/auth/login
POST   /api/auth/logout
POST   /api/auth/refresh
GET    /api/auth/user

Mailbox:
GET    /api/mailbox/folders
GET    /api/mailbox/messages
GET    /api/mailbox/message/{id}
POST   /api/mailbox/send
PUT    /api/mailbox/message/{id}
DELETE /api/mailbox/message/{id}
POST   /api/mailbox/message/{id}/flag
POST   /api/mailbox/message/{id}/move

Compose:
POST   /api/compose/send
POST   /api/compose/draft
POST   /api/compose/attachment
DELETE /api/compose/attachment/{id}

Contacts:
GET    /api/contacts
POST   /api/contacts
PUT    /api/contacts/{id}
DELETE /api/contacts/{id}

Settings:
GET    /api/settings/account
PUT    /api/settings/account
GET    /api/settings/filters
POST   /api/settings/filters
PUT    /api/settings/signature

Admin (Main App):
GET    /api/admin/subscribers
GET    /api/admin/subscriber/{id}
PUT    /api/admin/subscriber/{id}
POST   /api/admin/subscriber/{id}/suspend
GET    /api/admin/usage-stats
```

### 📦 Deployment Strategy

**For Subdomain (mail.mymultibranch.com):**

1. **DNS Configuration:**
   ```
   A     mail    YOUR_SERVER_IP
   MX    @       mail.mymultibranch.com   10
   TXT   @       v=spf1 mx ~all
   TXT   default._domainkey   v=DKIM1; k=rsa; p=PUBLIC_KEY
   ```

2. **Nginx Configuration:**
   ```nginx
   server {
       listen 80;
       listen 443 ssl http2;
       server_name mail.mymultibranch.com;
       
       ssl_certificate /etc/letsencrypt/live/mail.mymultibranch.com/fullchain.pem;
       ssl_certificate_key /etc/letsencrypt/live/mail.mymultibranch.com/privkey.pem;
       
       root /var/www/mail.mymultibranch.com/public;
       index index.php index.html;
       
       # API proxy to main app
       location /api/ {
           proxy_pass http://mymultibranch.com/projects/mail/api/;
           proxy_set_header Host $host;
           proxy_set_header X-Real-IP $remote_addr;
       }
       
       # Static files
       location / {
           try_files $uri $uri/ /index.php?$query_string;
       }
       
       # PHP-FPM
       location ~ \.php$ {
           fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
           fastcgi_index index.php;
           include fastcgi_params;
       }
   }
   ```

3. **Deployment Script:**
   ```bash
   #!/bin/bash
   # Deploy to subdomain
   
   echo "Deploying mail subdomain..."
   
   # Copy subdomain files
   rsync -av --delete subdomain/ /var/www/mail.mymultibranch.com/
   
   # Set permissions
   chown -R www-data:www-data /var/www/mail.mymultibranch.com
   
   # Build frontend assets
   cd /var/www/mail.mymultibranch.com
   npm install
   npm run build
   
   # Reload services
   systemctl reload nginx
   
   echo "Deployment complete!"
   ```

### 🔄 Integration Points

**1. Main Dashboard → Mail:**
- Link to mail.mymultibranch.com
- Show unread count
- Quick compose button

**2. Main Admin → Mail Admin:**
- Subscriber management
- Usage statistics
- Billing integration
- Support tickets

**3. SSO Flow:**
```
User on mymultibranch.com
  ↓
Click "Mail" button
  ↓
Redirect to mail.mymultibranch.com with auth token
  ↓
Validate token via API
  ↓
Create session on mail subdomain
  ↓
Show inbox
```

### 📈 Performance Targets

**Response Times:**
- Page load: < 2 seconds
- API response: < 100ms
- Email send: < 500ms
- Search: < 200ms

**Scalability:**
- Support 10,000+ concurrent users
- Handle 1M+ emails/day
- 99.9% uptime

**Storage:**
- Efficient compression
- Attachment deduplication
- Archive old emails

### 🧪 Testing Strategy

1. **Unit Tests:**
   - PHPUnit for backend
   - Jest for frontend

2. **Integration Tests:**
   - API endpoint testing
   - Database integration
   - Mail server integration

3. **E2E Tests:**
   - Cypress / Playwright
   - User workflows
   - Cross-browser testing

4. **Load Testing:**
   - Apache JMeter
   - 1000+ concurrent users
   - Stress testing

### 📚 Documentation

1. **User Documentation:**
   - Getting started guide
   - Feature tutorials
   - FAQ section

2. **Admin Documentation:**
   - Installation guide
   - Configuration options
   - Troubleshooting

3. **Developer Documentation:**
   - API reference
   - Architecture overview
   - Contributing guide

4. **Deployment Guide:**
   - Server requirements
   - Installation steps
   - Configuration

### 🎯 Implementation Phases

**Phase 1: Foundation (Weeks 1-2)**
- ✅ Database schema design
- ✅ Basic CRUD operations
- ✅ Authentication system
- ✅ API structure

**Phase 2: Core Features (Weeks 3-4)**
- ✅ Email sending/receiving
- ✅ Mailbox management
- ✅ Folder organization
- ✅ Search functionality

**Phase 3: UI Development (Weeks 5-6)**
- ✅ Inbox interface
- ✅ Compose email
- ✅ Settings panel
- ✅ Mobile responsive

**Phase 4: Advanced Features (Weeks 7-8)**
- ✅ Contacts management
- ✅ Calendar integration
- ✅ Filters and rules
- ✅ Templates

**Phase 5: Integration (Weeks 9-10)**
- ✅ Main dashboard integration
- ✅ Admin panel integration
- ✅ SSO implementation
- ✅ Billing integration

**Phase 6: Polish & Launch (Weeks 11-12)**
- ✅ Performance optimization
- ✅ Security audit
- ✅ User testing
- ✅ Production deployment

### 🛠️ Development Workflow

**For Each Pull Request:**

1. **Development:**
   ```bash
   # Work on feature branch
   git checkout -b feature/mail-composer
   
   # Make changes in projects/mail/
   # Test locally
   
   # Update subdomain/ folder
   ./scripts/sync-subdomain.sh
   ```

2. **Testing:**
   ```bash
   # Run tests
   vendor/bin/phpunit tests/
   
   # Check syntax
   find . -name "*.php" -exec php -l {} \;
   
   # Code quality
   vendor/bin/phpstan analyze
   ```

3. **Commit:**
   ```bash
   git add .
   git commit -m "feat: add email composer with attachments"
   git push origin feature/mail-composer
   ```

4. **Deploy Subdomain:**
   ```bash
   # After PR approval
   # Manually copy subdomain/ to mail.mymultibranch.com
   rsync -av subdomain/ user@server:/var/www/mail.mymultibranch.com/
   ```

### 🎁 Deliverables

In the next commit, I will create:

1. ✅ **Enhanced project structure** with proper MVC
2. ✅ **Subdomain folder** with complete frontend
3. ✅ **API layer** for communication
4. ✅ **Modern UI components** (Gmail-like)
5. ✅ **Integration guides** for dashboard/admin
6. ✅ **Deployment scripts** for subdomain
7. ✅ **Complete documentation**

### 📋 Checklist for Production

- [ ] SSL certificate installed
- [ ] DNS records configured
- [ ] Mail server (Postfix/Dovecot) configured
- [ ] SPF/DKIM/DMARC setup
- [ ] Database indexes optimized
- [ ] Redis cache configured
- [ ] Backup strategy implemented
- [ ] Monitoring setup (Grafana)
- [ ] Log aggregation (ELK)
- [ ] Security audit completed
- [ ] Load testing passed
- [ ] Documentation complete
- [ ] User training provided

---

**Ready to build this?** This is a production-grade architecture that will give you a professional email system comparable to Google Workspace or Zoho Mail. 

The next commit will include the complete structure with working code, modern UI, and easy deployment to subdomain.
