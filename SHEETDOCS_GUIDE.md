# SheetDocs - Google Sheets & Docs Alternative

## Overview

SheetDocs is a collaborative spreadsheet and document editor integrated into the MMB platform. It provides Google Sheets and Google Docs-like functionality with SSO authentication, tiered feature access (free/paid), and comprehensive admin management.

## Features

### Core Functionality
- ✅ **Document Editor** - Rich text document creation and editing
- ✅ **Spreadsheet Editor** - Collaborative spreadsheet with cells and formulas
- ✅ **SSO Integration** - Uses existing MMB authentication system
- ✅ **Collaboration** - Share documents with view, comment, or edit permissions
- ✅ **Templates** - Pre-built templates for common use cases
- ✅ **Version History** - Track document changes (paid feature)
- ✅ **Export Options** - Export to PDF, DOCX, XLSX, CSV (varies by plan)

### Free Tier Features
- 5 Documents maximum
- 5 Spreadsheets maximum
- 2 Collaborators per document
- 10MB storage limit
- Basic templates
- PDF export only
- Basic formulas

### Premium Tier Features
- Unlimited documents
- Unlimited spreadsheets
- Unlimited collaborators
- 1GB storage
- All premium templates
- Advanced formulas
- Version history
- Export to DOCX, XLSX, CSV
- Priority support
- API access
- 14-day free trial

## Installation

### 1. Database Setup

Create the SheetDocs database:

```bash
mysql -u root -p

CREATE DATABASE sheetdocs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON sheetdocs.* TO 'your_user'@'localhost';
FLUSH PRIVILEGES;
exit;
```

Import the schema:

```bash
mysql -u your_user -p sheetdocs < projects/sheetdocs/schema.sql
```

### 2. Configuration

The database credentials are inherited from the main config. You can customize them in `projects/sheetdocs/config.php`:

```php
'database' => [
    'host' => 'localhost',
    'port' => '3306',
    'database' => 'sheetdocs',
    'username' => 'your_user',
    'password' => 'your_password',
]
```

### 3. Admin Panel Setup

1. Navigate to `/admin/projects/database-setup`
2. Configure SheetDocs database connection
3. Test the connection
4. Import the schema

### 4. Verify Installation

Navigate to `/projects/sheetdocs` to access the dashboard.

## Architecture

### Database Schema

**documents** - Main table for both documents and sheets
- Stores document metadata, content, visibility
- Supports templates and public sharing

**sheets** - Sheet tabs within spreadsheet documents
- Multiple sheets per document
- Order, visibility, row/column counts

**sheet_cells** - Individual cell data
- Cell values, formulas, styling (JSON)
- Efficient storage with unique indexes

**document_shares** - Collaboration and sharing
- User-to-user sharing
- Anonymous sharing via tokens
- Permission levels: view, comment, edit

**document_versions** - Version history (paid feature)
- Complete version tracking
- Change summaries
- Revert capabilities

**comments** - Document comments
- Inline comments with position data
- Threaded discussions
- Resolved status tracking

**user_subscriptions** - Subscription management
- Plan tracking (free/paid)
- Trial periods
- Billing cycles

**usage_stats** - Usage tracking
- Document and sheet counts
- Storage tracking
- API usage monitoring

**activity_logs** - Audit trail
- All user actions logged
- IP and user agent tracking
- GDPR compliant

**templates** - Document templates
- Pre-built templates
- Tier-based access
- Usage tracking

### MVC Structure

```
projects/sheetdocs/
├── config.php                     # Project configuration
├── schema.sql                     # Database schema
├── index.php                      # Entry point
├── controllers/
│   ├── DashboardController.php    # Main dashboard
│   ├── DocumentController.php     # Document CRUD
│   ├── SheetController.php        # Spreadsheet CRUD
│   ├── SubscriptionController.php # Subscription management
│   ├── ShareController.php        # Sharing functionality
│   ├── TemplateController.php     # Templates
│   ├── ExportController.php       # Export functionality
│   ├── ApiController.php          # API endpoints
│   └── SettingsController.php     # User settings
├── views/                         # View templates
└── routes/
    └── web.php                    # Route definitions
```

### Controllers

**DashboardController**
- Dashboard with recent documents/sheets
- Usage statistics
- Subscription status
- Limit checking

**DocumentController**
- Create, read, update, delete documents
- Access control
- Usage limit enforcement
- Activity logging

**SheetController**
- Spreadsheet CRUD operations
- Sheet tab management
- Cell operations
- Formula handling

**SubscriptionController**
- Pricing page
- Upgrade/downgrade functionality
- Trial management
- Feature access control

**ShareController**
- Share with specific users
- Generate public share links
- Manage permissions
- Revoke access

## Usage

### For Users

#### Creating Documents
1. Navigate to SheetDocs dashboard
2. Click "New Document"
3. Choose a template or start blank
4. Start editing

#### Creating Spreadsheets
1. Navigate to SheetDocs dashboard
2. Click "New Spreadsheet"
3. Enter data in cells
4. Use formulas for calculations

#### Sharing Documents
1. Open a document
2. Click "Share" button
3. Add user email or generate public link
4. Set permission level
5. Send link to collaborators

#### Upgrading to Premium
1. Navigate to Pricing page
2. Click "Start Free Trial"
3. Enjoy 14 days of premium features
4. Automatic conversion to paid after trial

### For Administrators

#### Admin Panel Access
Navigate to `/admin/projects/sheetdocs` to access:

**Dashboard**
- Total documents and sheets
- Active users
- Subscription statistics
- Storage usage
- Recent activity

**Document Management**
- View all documents
- Search and filter
- Delete inappropriate content
- Monitor sharing activity

**Subscription Management**
- View all user subscriptions
- Monitor trial periods
- Track revenue
- Manage cancellations

**Activity Logs**
- View all user actions
- Filter by user, action, date
- Export for compliance
- Security monitoring

#### Managing Users
```php
// Check user subscription
SELECT * FROM user_subscriptions WHERE user_id = ?;

// View user documents
SELECT * FROM documents WHERE user_id = ?;

// Check usage limits
SELECT * FROM usage_stats WHERE user_id = ?;
```

## API Endpoints

### Public Routes
- `GET /projects/sheetdocs/dashboard` - Dashboard
- `GET /projects/sheetdocs/documents` - List documents
- `GET /projects/sheetdocs/sheets` - List sheets
- `GET /projects/sheetdocs/pricing` - Pricing page
- `GET /sd/{token}` - View shared document

### Authenticated Routes
- `POST /projects/sheetdocs/documents/store` - Create document
- `POST /projects/sheetdocs/documents/{id}/update` - Update document
- `POST /projects/sheetdocs/documents/{id}/delete` - Delete document
- `POST /projects/sheetdocs/sheets/store` - Create sheet
- `POST /projects/sheetdocs/subscription/upgrade` - Upgrade subscription
- `POST /projects/sheetdocs/share/{id}/create` - Share document

### Real-time API
- `POST /projects/sheetdocs/api/cells/update` - Update cell value
- `POST /projects/sheetdocs/api/documents/autosave` - Auto-save document

## Security Features

### Authentication
- Uses Core\Auth system (SSO)
- Session-based authentication
- Automatic redirect for unauthenticated users

### Authorization
- Owner-based access control
- Share-based permissions
- Public/private visibility settings

### Data Protection
- SQL injection prevention (prepared statements)
- XSS sanitization
- CSRF token validation
- Input validation and sanitization

### Activity Logging
- All actions logged with IP and user agent
- Audit trail for compliance
- Security monitoring

## Configuration

### Feature Limits (config.php)

```php
'features' => [
    'free' => [
        'max_documents' => 5,
        'max_sheets' => 5,
        'max_collaborators' => 2,
        'storage_limit' => 10 * 1024 * 1024, // 10MB
    ],
    'paid' => [
        'max_documents' => -1, // unlimited
        'max_sheets' => -1,
        'max_collaborators' => -1,
        'storage_limit' => 1024 * 1024 * 1024, // 1GB
    ]
]
```

### Subscription Pricing

```php
'subscription' => [
    'monthly_price' => 9.99,
    'annual_price' => 99.99,
    'trial_days' => 14,
]
```

## Troubleshooting

### Database Connection Issues
1. Verify credentials in `config.php`
2. Check database exists
3. Ensure user has proper permissions
4. Import schema if tables missing

### Cannot Create Documents
1. Check if user reached free tier limit
2. Verify subscription status
3. Check usage_stats table
4. Clear cache and retry

### Sharing Not Working
1. Verify document_shares table exists
2. Check share token generation
3. Ensure proper permissions set
4. Review activity logs

## Performance Optimization

### Database Indexing
All critical fields are indexed:
- `user_id` for quick user lookups
- `document_id` for relationship queries
- `share_token` for public sharing
- `created_at`/`updated_at` for sorting

### Cell Storage
- Only store non-empty cells
- JSON for style data (compact)
- Bulk cell updates via API

### Caching Recommendations
- Cache user subscriptions
- Cache usage statistics
- Cache template list

## Future Enhancements

- [ ] Real-time collaborative editing (WebSocket)
- [ ] Rich text formatting toolbar
- [ ] Advanced spreadsheet formulas
- [ ] Charts and graphs
- [ ] Mobile apps (iOS/Android)
- [ ] Offline mode
- [ ] Import from Google Docs/Sheets
- [ ] Team workspaces
- [ ] Advanced permissions (folder-level)
- [ ] File attachments in documents

## License

Part of the MyMultiBranch (MMB) platform.

## Support

For issues or questions:
- Check activity logs in admin panel
- Enable `APP_DEBUG` mode for detailed errors
- Review `DATABASE_SETUP_GUIDE.md`
- Check project-specific logs

---

**Version**: 1.0.0  
**Last Updated**: January 2026  
**Status**: Production Ready ✅
