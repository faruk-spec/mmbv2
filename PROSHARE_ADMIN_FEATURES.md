# ProShare Admin Panel - Logging & Monitoring Features

## Overview
This document describes the new logging and monitoring features added to the ProShare admin panel. The features are organized into two separate dropdown menus for better usability.

## Menu Structure

### 1. ProShare User Dashboard (User-facing features)
Access path: `/admin/projects/proshare/user-*`

#### Features:
- **User Dashboard** (`/admin/projects/proshare/user-dashboard`)
  - Overview of all ProShare users
  - Quick access to user files and activities
  
- **User Files** (`/admin/projects/proshare/user-files`)
  - View all files uploaded by a specific user
  - Filter by user
  - Pagination support
  
- **User Activity** (`/admin/projects/proshare/user-activity`)
  - View all activity logs for a specific user
  - Track file uploads, downloads, and other actions
  - Filter by user

### 2. ProShare Admin (Admin-facing monitoring features)
Access path: `/admin/projects/proshare/*`

#### Features:

##### User Activity Logs
- **User Activity Logs** (`/admin/projects/proshare/user-logs`)
  - Complete log of all user activities
  - Filter by user
  - IP address tracking
  - User agent information
  - Pagination support

- **Session History** (`/admin/projects/proshare/sessions`)
  - Active and inactive user sessions
  - Device information (browser, platform, device name)
  - IP address tracking
  - Last active timestamp
  - Online/offline status

##### File & Folder Activity
- **All Files** (`/admin/projects/proshare/files`)
  - Complete list of all uploaded files
  - File status (active/expired)
  - User information
  - Download counts

- **File Activity Logs** (`/admin/projects/proshare/file-activity`)
  - Detailed activity logs for file operations
  - Statistics: uploads, downloads, deletes, shares
  - Filter by action type
  - IP address and timestamp tracking

##### Security Monitoring
- **Security Monitoring** (`/admin/projects/proshare/security`)
  - Failed login attempts (24-hour and historical)
  - Blocked IP addresses
  - Suspicious activity detection
  - Unique attacker tracking (7-day period)
  - Security statistics dashboard

- **Server Health** (`/admin/projects/proshare/server-health`)
  - CPU usage monitoring
  - Memory usage (used/total/percentage)
  - Disk usage (used/total/percentage)
  - System uptime
  - Load averages (1, 5, 15 minutes)
  - Database performance metrics
  - Error logs (last 100 entries)

##### Storage Monitoring
- **Storage Monitoring** (`/admin/projects/proshare/storage`)
  - Total storage usage
  - Storage per user
  - File count per user
  - Storage growth trends (last 30 days)
  - Average file size statistics
  - Visual charts for storage trends

##### Audit Trail
- **Audit Trail** (`/admin/projects/proshare/audit-trail`)
  - Complete audit log of all admin actions
  - User information for each action
  - Resource type and ID tracking
  - IP address and user agent logging
  - Pagination support
  - Export functionality (CSV/JSON)

- **Export Audit Trail** (`/admin/projects/proshare/audit-trail/export`)
  - Export to CSV format
  - Export to JSON format
  - Includes all audit log data

##### Analytics & Insights
- **Analytics & Insights** (`/admin/projects/proshare/analytics`)
  - Active users (last 30 days)
  - Total downloads and uploads
  - Average downloads per file
  - Traffic overview chart (last 30 days)
  - Most downloaded files (top 10)
  - Most active users (top 10)
  - Active users list with activity counts

## Database Schema

The features utilize the following existing tables from the ProShare database:

### Project Database Tables (proshare)
- `files` - File storage and metadata
- `file_downloads` - Download tracking
- `activity_logs` - User activity logs
- `audit_logs` - Admin action audit trail
- `notifications` - System notifications

### Main Database Tables
- `users` - User accounts
- `user_devices` - Session and device tracking
- `failed_logins` - Failed login attempts
- `blocked_ips` - Blocked IP addresses

## Features Not Implemented

Some features from the original requirements were not implemented due to complexity or lack of supporting infrastructure:

1. **File Version History** - Would require additional database schema changes
2. **Brute Force Detection** - Basic tracking exists, advanced detection not implemented
3. **Real-time Server Monitoring** - Static snapshots only, no real-time updates
4. **Database Performance Metrics** - Basic status queries only
5. **Notification Alerts** - Viewing only, automatic alert generation not implemented

These features can be added in future iterations if needed.

## Security Considerations

- All routes require authentication (`auth` middleware)
- All routes require admin role (`admin` middleware)
- CSRF protection on all POST requests
- SQL injection protection through parameterized queries
- XSS protection through output escaping
- User input validation

## Usage Notes

1. **Database Connection**: The controller uses `Database::projectConnection('proshare')` to access the ProShare database, following MMB's multi-database architecture.

2. **Cross-Database Queries**: Some features require joining data from both the main database and the ProShare database. This is handled by executing separate queries and merging results.

3. **Performance**: For large datasets, pagination is implemented to prevent memory issues and improve load times.

4. **Server Health Monitoring**: 
   - Some metrics (CPU, memory, disk) may not be available on Windows systems
   - Shell commands are used for system metrics on Unix-like systems
   - Error logs are read from `storage/logs/error.log`

5. **Export Functionality**: Audit trail exports include all logs (no pagination) for complete audit records.

## Future Enhancements

Potential improvements for future versions:

1. Real-time monitoring with WebSocket integration
2. Advanced brute force detection algorithms
3. Automated alert system for suspicious activities
4. File version history tracking
5. Advanced analytics with predictive insights
6. Customizable dashboards
7. Scheduled report generation
8. Advanced search and filtering options
9. API endpoints for external monitoring tools
10. Mobile-responsive improvements

## Testing

To test the features:

1. Ensure ProShare database is set up and populated with test data
2. Log in as an admin user
3. Navigate to the ProShare dropdowns in the admin sidebar
4. Test each feature endpoint
5. Verify data displays correctly
6. Test pagination, filtering, and export functionality

## Support

For issues or questions about these features:
1. Check the MMB documentation
2. Review the controller code in `controllers/Admin/ProShareAdminController.php`
3. Inspect view files in `views/admin/projects/proshare/`
4. Verify database schema in `projects/proshare/schema.sql`
