# ProShare - Secure File Sharing Platform

## Overview

ProShare is a production-ready, secure file sharing platform with modern UI/UX, comprehensive security features, and admin panel integration capabilities.

## Features

### Core Functionality
- ✅ **Anonymous File Sharing** - No account required
- ✅ **Text Sharing** - Share code snippets, notes, or any text content
- ✅ **Password Protection** - Secure files with Argon2id hashing
- ✅ **Link Expiry** - Auto-delete after specified time (1 hour to 30 days)
- ✅ **Download Limits** - Control max downloads per file
- ✅ **Self-Destruct** - One-time access for sensitive files
- ✅ **File Compression** - Optional compression to save space
- ✅ **File Integrity** - SHA-256 checksums for verification

### Security Features
- ✅ **Argon2id Password Hashing** - Industry-standard secure hashing
- ✅ **CSRF Protection** - Token-based protection on all forms
- ✅ **Input Sanitization** - XSS prevention
- ✅ **SQL Injection Prevention** - Prepared statements
- ✅ **File Type Validation** - MIME type verification
- ✅ **Size Limits** - Configurable max file size
- ✅ **Audit Logging** - Complete activity trail
- ✅ **Rate Limiting Ready** - Hooks for rate limiting

### UI/UX Features
- ✅ **Modern Dark Theme** - Matching admin panel design
- ✅ **Responsive Design** - Works on all devices (320px - 1920px+)
- ✅ **Sidebar Navigation** - Consistent with admin panel
- ✅ **Drag & Drop Upload** - Intuitive file upload
- ✅ **Progress Indicators** - Real-time upload feedback
- ✅ **Empty States** - Helpful guidance when no data
- ✅ **Notifications System** - In-app notifications
- ✅ **Icon Library** - Font Awesome 6.4.0 integration

## Architecture

### Separate Database
ProShare uses its own dedicated database (`proshare`) separate from the main MMB database. This ensures:
- **Data Isolation** - Project data is independent
- **Easier Scaling** - Can move to separate server if needed
- **Better Organization** - Clear separation of concerns
- **Flexible Deployment** - Can be deployed independently

### Database Tables
1. **files** - File metadata with encryption and security fields
2. **file_downloads** - Download tracking with IP and user agent
3. **folders** - Collection/folder support
4. **file_folders** - Many-to-many file-folder relationship
5. **text_shares** - Text sharing with same security as files
6. **messages** - Secure messaging (schema ready)
7. **chat_rooms** - Group messaging (schema ready)
8. **link_access** - Email-based access control
9. **notifications** - In-app notification system
10. **audit_logs** - Complete audit trail (GDPR compliant)
11. **user_settings** - User preferences
12. **backups** - Automatic backup metadata
13. **settings** - Project-level configuration

### MVC Structure
```
projects/proshare/
├── config.php                  # Database and project config
├── schema.sql                  # Database schema
├── ProShareHelpers.php        # Common helper functions
├── controllers/
│   ├── DashboardController.php
│   ├── UploadController.php
│   ├── DownloadController.php
│   ├── TextShareController.php
│   ├── FileController.php
│   ├── SettingsController.php
│   └── NotificationController.php
├── views/
│   ├── layouts/
│   │   └── app.php            # Main layout with sidebar
│   ├── dashboard.modern.php
│   ├── upload.modern.php
│   ├── text-share.modern.php
│   ├── files-list.modern.php
│   ├── settings.modern.php
│   ├── notifications.modern.php
│   ├── password-form.php
│   └── text-view.php
└── routes/
    └── web.php                # Route definitions
```

## Installation

### 1. Database Setup

Create the ProShare database:

```bash
# Access MySQL
mysql -u root -p

# Create database
CREATE DATABASE proshare CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

# Grant permissions (adjust username as needed)
GRANT ALL PRIVILEGES ON proshare.* TO 'your_user'@'localhost';
FLUSH PRIVILEGES;

# Exit
exit;
```

Import the schema:

```bash
mysql -u your_user -p proshare < projects/proshare/schema.sql
```

### 2. Configuration

The database credentials are automatically inherited from the main config, but you can customize them in `projects/proshare/config.php`:

```php
'database' => [
    'host' => 'localhost',
    'port' => '3306',
    'database' => 'proshare',
    'username' => 'your_user',
    'password' => 'your_password',
]
```

### 3. Storage Directory

Ensure the storage directory exists and is writable:

```bash
mkdir -p storage/uploads/proshare
chmod 755 storage/uploads/proshare
```

### 4. Verify Installation

Navigate to `/projects/proshare` in your browser to access the dashboard.

## Usage

### For Users

#### Upload Files
1. Navigate to **Upload Files** from the sidebar
2. Drag & drop files or click to browse
3. Configure options:
   - Set expiry time (1 hour to 30 days or never)
   - Set download limit (1 to unlimited)
   - Add password protection (optional)
   - Enable self-destruct (optional)
   - Enable compression (recommended)
4. Click **Upload Files**
5. Copy and share the generated link

#### Share Text
1. Navigate to **Share Text** from the sidebar
2. Enter optional title
3. Paste or type your content
4. Configure security options (same as file upload)
5. Click **Create Share Link**
6. Copy and share the generated link

#### Manage Files
1. Navigate to **My Files** from the sidebar
2. View all your uploaded files
3. Actions available:
   - **View** - Open file link
   - **Copy** - Copy link to clipboard
   - **Delete** - Remove file permanently

#### Settings
1. Navigate to **Settings** from the sidebar
2. Configure:
   - Email/SMS notifications
   - Default expiry time
   - Max file size
   - Compression preferences
   - Auto-delete settings

### For Administrators

#### Admin Panel Integration
ProShare is designed for seamless admin panel integration:

1. **Activity Logs** - All actions logged to `audit_logs` table
2. **Usage Statistics** - Track files, downloads, storage
3. **User Management** - View per-user statistics
4. **Security Monitoring** - Track failed password attempts
5. **Storage Management** - Monitor disk usage

#### Cron Jobs
Set up a cron job to clean expired files:

```bash
# Add to crontab (run every hour)
0 * * * * cd /path/to/project && php -r "require 'vendor/autoload.php'; \$controller = new Projects\ProShare\Controllers\MaintenanceController(); \$controller->cleanExpiredFiles();"
```

## API Endpoints

### Public Routes (Anonymous Access)
- `GET /s/{shortcode}` - Download file
- `GET /t/{shortcode}` - View text share
- `POST /projects/proshare/verify-password` - Verify file password
- `POST /projects/proshare/text/verify-password` - Verify text password

### Authenticated Routes
- `GET /projects/proshare/dashboard` - Dashboard
- `GET /projects/proshare/upload` - Upload page
- `POST /projects/proshare/upload` - Process upload
- `GET /projects/proshare/text` - Text share form
- `POST /projects/proshare/text/create` - Create text share
- `GET /projects/proshare/files` - My files list
- `GET /projects/proshare/settings` - Settings page
- `POST /projects/proshare/settings` - Update settings
- `GET /projects/proshare/notifications` - Notifications
- `POST /projects/proshare/notifications/mark-read` - Mark as read

## Security Considerations

### File Upload Security
1. **File Type Validation** - Only allowed MIME types
2. **Size Limits** - Enforced server-side
3. **Unique Filenames** - Prevent overwrites
4. **Separate Storage** - Outside web root
5. **Integrity Checks** - SHA-256 verification

### Password Protection
1. **Argon2id Hashing** - Best practice password hashing
2. **Session-based Auth** - Passwords verified server-side
3. **Failed Attempt Logging** - Track brute force attempts
4. **Rate Limiting Ready** - Infrastructure for rate limiting

### Data Privacy (GDPR Compliant)
1. **Anonymous Uploads** - No required user accounts
2. **Auto-Deletion** - Configurable expiry
3. **Complete Audit Trail** - All actions logged
4. **User Control** - Users can delete anytime
5. **Data Minimization** - Only essential data collected

## Supported File Types

- **Images**: JPEG, PNG, GIF, WebP
- **Documents**: PDF, DOC, DOCX, XLS, XLSX
- **Archives**: ZIP, RAR
- **Text**: TXT, CSV
- **Media**: MP4, MPEG, MP3, WAV

## Configuration

### Maximum File Size
Default: 500MB (524,288,000 bytes)

Adjust in `controllers/UploadController.php`:
```php
private const MAX_FILE_SIZE = 524288000; // 500MB
```

### Allowed File Types
Modify the `ALLOWED_TYPES` array in `controllers/UploadController.php`

### Default Settings
User defaults can be configured in the `user_settings` table.

## Troubleshooting

### Upload Fails
1. Check PHP `upload_max_filesize` and `post_max_size`
2. Verify storage directory permissions
3. Check disk space
4. Review error logs

### Database Connection Errors
1. Verify database credentials in `config.php`
2. Ensure database exists
3. Check user permissions
4. Import schema if tables missing

### Files Not Downloading
1. Check file exists in storage directory
2. Verify file permissions
3. Check expiry and download limits
4. Review audit logs for errors

## Performance Optimization

1. **Enable Compression** - Reduces storage and transfer time
2. **CDN Integration** - For faster global downloads
3. **Database Indexing** - All key fields indexed
4. **Caching** - Implement caching for statistics
5. **Async Processing** - For large file operations

## Future Enhancements

- [ ] Real-time chat integration
- [ ] Email/SMS notifications
- [ ] QR code generation for links
- [ ] Advanced analytics dashboard
- [ ] CDN integration
- [ ] Virus scanning (ClamAV)
- [ ] File versioning
- [ ] RESTful API
- [ ] Mobile apps (iOS/Android)
- [ ] End-to-end encryption

## License

Part of the MyMultiBranch (MMB) platform.

## Support

For issues or questions:
- Check audit logs in the database
- Enable `APP_DEBUG` mode for detailed errors
- Review the `DATABASE_SETUP_GUIDE.md`
- Check `PROJECTS_ACCESS_GUIDE.md`

---

**Version**: 1.0.0  
**Last Updated**: December 2025  
**Status**: Production Ready ✅
