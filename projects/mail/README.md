# Mail Hosting Server SaaS Platform

A complete, industry-standard email hosting SaaS platform similar to Zoho Mail, built with subscription management, multi-tenant architecture, and comprehensive feature gating.

## Overview

This is a production-ready mail hosting platform that provides:
- **Multi-tenant architecture** with subscriber-based isolation
- **Subscription management** with free and paid plans
- **Feature-based access control** (SMTP, IMAP, API)
- **Custom domain support** with DNS verification
- **Webmail interface** for accessing emails
- **Complete admin control** for managing subscribers and system

## Features

### User Hierarchy
1. **Platform Super Admin** - Full control over entire platform, all subscribers, and system settings
2. **Subscriber (Account Owner)** - User who purchases a subscription. Acts as super admin for their own account:
   - Can add users up to plan limits
   - Assign roles (Domain Admin or End User)
   - Manage all domains and mailboxes within their subscription
   - Control billing and subscription settings
   - Full access to their subscription scope
3. **Domain Admin** - Manages specific domains within the subscriber's account:
   - Add/edit mailboxes for assigned domains
   - Manage aliases and forwarding
   - View domain statistics
4. **End User** - Basic mailbox access only:
   - Send and receive emails
   - Manage personal settings
   - Access webmail interface

### Subscription Plans

| Feature | Free | Starter | Business | Developer |
|---------|------|---------|----------|-----------|
| **Price** | $0/month | $9.99/month | $29.99/month | $49.99/month |
| **Max Users** | 1 | 5 | 25 | 100 |
| **Storage/User** | 1 GB | 5 GB | 25 GB | 50 GB |
| **Daily Send Limit** | 50 | 500 | 2,000 | 10,000 |
| **Max Attachment** | 5 MB | 25 MB | 50 MB | 100 MB |
| **Custom Domains** | 1 | 3 | 10 | 50 |
| **Email Aliases** | 3 | 25 | 100 | 500 |
| **Webmail Access** | ✅ | ✅ | ✅ | ✅ |
| **SMTP Access** | ❌ | ✅ | ✅ | ✅ |
| **IMAP/POP3** | ❌ | ✅ | ✅ | ✅ |
| **API Access** | ❌ | ❌ | ✅ | ✅ |
| **2FA** | ❌ | ✅ | ✅ | ✅ |
| **Threaded Conversations** | ❌ | ✅ | ✅ | ✅ |
| **Scheduled Send** | ❌ | ✅ | ✅ | ✅ |
| **Read Receipts** | ❌ | ✅ | ✅ | ✅ |

### Core Features

#### Webmail Interface
- Modern 3-column layout (folders, email list, preview)
- Inbox, Sent, Drafts, Trash, Spam, Archive folders
- Custom folder creation
- Rich text email composer with attachments
- Email search and filtering
- Bulk actions (move, delete, mark as read/spam)
- Threaded conversations (paid plans)
- Drag-and-drop attachments

#### Custom Domain Management
- Add unlimited custom domains (based on plan)
- DNS verification with automatic record generation
- SPF, DKIM, DMARC record support
- MX record configuration
- Domain verification status tracking
- SSL/TLS certificate integration
- Catch-all address configuration

#### Mailbox Management
- Create and manage email accounts
- Quota management per user
- Daily send limit enforcement
- Storage usage tracking
- Suspend/resume mailboxes
- Email aliases and forwarding
- Auto-responders and filters

#### SMTP/IMAP/POP3 Access (Paid Plans)
- Credential generation for external clients
- TLS/SSL encryption enforcement
- Rate limiting per plan
- Connection logging and monitoring
- Configuration guides for popular clients

#### REST API (Business & Developer Plans)
- Complete REST API for mail operations
- API key generation and management
- Rate limiting (60/min, 10,000/day)
- Endpoints:
  - Send email
  - Read mailbox
  - Manage folders
  - Search emails
  - Manage contacts
- Webhook support for events
- API usage tracking and analytics

#### Security Features
- Spam filtering (SpamAssassin/Rspamd integration)
- Antivirus scanning (ClamAV)
- SPF validation
- DKIM signing
- DMARC policy enforcement
- Rate limiting (login, sending, API)
- Brute-force protection
- Two-factor authentication (paid plans)
- TLS/SSL encryption everywhere
- Audit logging for all actions

#### Billing System
- Stripe integration for payments
- Subscription management (upgrade/downgrade)
- Automatic renewal
- Invoice generation and history
- Payment method management
- Refund handling
- Grace period support
- Usage-based billing alerts

### Super Admin Capabilities

Super Admin has complete control over the system:
- Create/edit/delete subscribers
- Manually create users for any subscriber
- Assign or override subscription plans
- Enable/disable any feature for specific subscribers
- Suspend accounts instantly with reason tracking
- Override billing status
- View all logs (admin actions, API usage, mail logs)
- Manage abuse reports and spam
- Configure global system limits
- Impersonate users (read-only mode)
- View system analytics and statistics

## Technology Stack

### Application Layer
- **Language**: PHP 8.2+
- **Framework**: Custom MVC (MMB Framework)
- **Frontend**: HTML5, CSS3, JavaScript (Modern UI)
- **API**: REST (JSON responses)
- **Web Server**: Apache/Nginx

### Mail Infrastructure
- **SMTP Server**: Postfix
- **IMAP/POP3 Server**: Dovecot
- **Spam Protection**: SpamAssassin / Rspamd
- **Antivirus**: ClamAV
- **Authentication**: SPF, DKIM, DMARC
- **Encryption**: TLS 1.2+

### Data & Infrastructure
- **Database**: MySQL 5.7+ / MariaDB 10.3+
- **Cache & Queue**: Redis
- **Mail Storage**: Maildir format
- **Attachment Storage**: Local / S3-compatible
- **OS**: Linux (Ubuntu recommended)

## Database Schema

### Core Tables
- `subscription_plans` - Define available plans
- `plan_features` - Feature gating per plan
- `subscribers` - Account owners (references MMB users)
- `subscriptions` - Active subscriptions
- `payments` - Payment history
- `invoices` - Billing invoices
- `domains` - Custom domains
- `dns_records` - DNS configuration
- `mailboxes` - Email accounts
- `mail_user_roles` - Role assignments
- `feature_access` - Feature overrides

### Mail Tables
- `mail_folders` - Folder structure
- `mail_messages` - Email storage
- `mail_attachments` - File attachments
- `mail_filters` - Auto-rules and filters
- `auto_responders` - Vacation messages
- `aliases` - Email aliases
- `contacts` - Address book
- `email_templates` - Reusable templates

### API & Monitoring
- `smtp_credentials` - SMTP access credentials
- `api_keys` - API authentication keys
- `api_usage_logs` - API call tracking
- `usage_logs` - Quota tracking
- `mail_logs` - Send/receive logs
- `mail_statistics` - Analytics data
- `admin_actions` - Audit trail
- `abuse_reports` - Spam/abuse tracking
- `webhooks` - Webhook configurations
- `webhook_deliveries` - Delivery tracking

## Installation

### Prerequisites
```bash
- PHP 8.2 or higher
- MySQL 5.7+ / MariaDB 10.3+
- Redis server
- Postfix mail server
- Dovecot IMAP/POP3 server
- SpamAssassin
- ClamAV (optional)
```

### Setup Steps

1. **Database Setup**
```bash
mysql -u root -p
CREATE DATABASE mail_server CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mail_server;
SOURCE /path/to/projects/mail/schema.sql;
```

2. **Configuration**
Edit `projects/mail/config.php` with your settings:
- Database credentials
- SMTP/IMAP server details
- Storage paths
- Feature toggles

3. **Mail Server Configuration**
Configure Postfix and Dovecot to authenticate against the database.

4. **Stripe Setup** (for payments)
- Create Stripe account
- Add API keys to config
- Configure webhook endpoints

5. **Admin Panel Access**
Access the mail admin panel at:
```
https://yourdomain.com/admin/projects/mail
```

## API Documentation

### Authentication
```http
POST /api/auth
Content-Type: application/json

{
  "api_key": "your_api_key",
  "api_secret": "your_api_secret"
}
```

### Send Email
```http
POST /api/email/send
Authorization: Bearer {token}

{
  "from": "sender@domain.com",
  "to": ["recipient@domain.com"],
  "subject": "Test Email",
  "body_html": "<p>Hello World</p>",
  "body_text": "Hello World"
}
```

### Read Mailbox
```http
GET /api/mailbox/inbox?page=1&per_page=50
Authorization: Bearer {token}
```

## URL Structure

### Public Website
```
/                           # Homepage
/pricing                    # Pricing plans
/features                   # Feature showcase
/signup                     # Registration
/login                      # User login
```

### Subscriber Dashboard
```
/projects/mail/dashboard    # Main dashboard
/projects/mail/domains      # Domain management
/projects/mail/accounts     # Mailbox management
/projects/mail/aliases      # Email aliases
/projects/mail/settings     # Account settings
```

### Webmail Interface
```
/projects/mail/mailbox/inbox    # Inbox
/projects/mail/mailbox/sent     # Sent emails
/projects/mail/email/compose    # Compose email
/projects/mail/email/read/{id}  # Read email
```

### Super Admin Panel
```
/admin/projects/mail                   # Overview
/admin/projects/mail/subscribers       # Subscriber management
/admin/projects/mail/plans             # Plan management
/admin/projects/mail/domains           # All domains
/admin/projects/mail/mailboxes         # All mailboxes
/admin/projects/mail/billing           # Billing reports
/admin/projects/mail/abuse             # Abuse reports
/admin/projects/mail/logs              # System logs
/admin/projects/mail/settings          # System settings
```

## Security Best Practices

1. **Always use TLS/SSL** for all mail connections
2. **Enable DKIM signing** for all outgoing emails
3. **Configure SPF and DMARC** records properly
4. **Regular security audits** of user accounts
5. **Monitor abuse reports** and take action quickly
6. **Rate limiting** on all endpoints
7. **Strong password policies** enforcement
8. **2FA requirement** for admin accounts
9. **Regular backups** of mail data
10. **Keep software updated** (PHP, Postfix, Dovecot)

## Scalability

The platform is designed for horizontal scaling:
- Stateless PHP application servers
- Redis for session and queue management
- Multiple mail servers behind load balancer
- S3-compatible storage for attachments
- Database read replicas for queries
- Queue-based email processing

## Support & Documentation

- User Guide: `/docs/user-guide.md`
- Admin Guide: `/docs/admin-guide.md`
- API Documentation: `/docs/api.md`
- Mail Server Setup: `/docs/mail-server-setup.md`
- Deployment Guide: `/docs/deployment.md`

## Implementation Status

### ✅ Completed Phases
- **Phase 1:** Core Infrastructure (Database, Config, Helpers)
- **Phase 2:** Admin & Subscriber Views (8 views implemented)
- **Phase 3:** Domain Management (DNS configuration interface)

### 🔨 Pending Phases
For a detailed breakdown of all pending implementation phases, see [PENDING_PHASES.md](./PENDING_PHASES.md).

**Critical Priority:**
- Phase 4: Backend Integration (DNS verification, DKIM, authentication)
- Phase 6: Webmail Interface (3-column layout, email composer)

**High Priority:**
- Phase 5: Billing System (Stripe integration)
- Phase 11: Testing & QA
- Phase 12: Documentation

**Medium Priority:**
- Phase 7: SMTP/IMAP Integration
- Phase 8: Email Processing
- Phase 9: REST API

**Low Priority:**
- Phase 10: Advanced Features

## License

Copyright © 2026 MyMultiBranch Platform. All rights reserved.

## Version

Current Version: 0.3.0 (Phases 1-3 Complete)
- Version 0.3.0 - Phases 1-3 Complete (January 2026)
- Version 1.0.0 - Will be released when all phases complete
- Uses Semantic Versioning: Major.Minor.Patch
