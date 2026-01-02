# Admin Panel - Complete Guide

## Overview

The admin panel has been redesigned with a professional left sidebar navigation system featuring dropdown menus for all sections. All implemented features (CodeXPro, ImgTxt, and ProShare) are now fully integrated and manageable through the admin interface.

## Features

### Left Sidebar Navigation

#### Main Sections:
1. **Dashboard** - System overview with statistics
2. **Projects Management** - Centralized project control
3. **User Management** - User administration
4. **Security Center** - Security monitoring and controls
5. **Activity Logs** - System and user activity tracking
6. **Settings** - System configuration

### Project Integration

#### CodeXPro (Live Code Editor)
**Dropdown Menu Items:**
- **Overview** - Statistics and activity
  - Total projects created
  - Code snippets saved
  - Active users
  - Recent activity
- **Settings** - Project configuration
  - Enable/disable features
  - Set resource limits
  - Configure auto-save intervals
- **Users** - User management
  - View users
  - Manage permissions
  - Track user projects
- **Templates** - Template library
  - Manage code templates
  - Add new templates
  - Category organization

#### ImgTxt (OCR Tool)
**Dropdown Menu Items:**
- **Overview** - Statistics and performance
  - Total OCR jobs
  - Completed jobs
  - Active users
  - Success rate
- **Settings** - OCR configuration
  - Enable/disable features
  - Set file size limits
  - Configure Tesseract options
- **OCR Jobs** - Job monitoring
  - View all jobs
  - Monitor progress
  - Failed jobs
  - Batch operations
- **Languages** - Language management
  - Supported languages
  - Add new languages
  - Language packs
  - Default language settings

#### ProShare (Secure File Sharing)
**Dropdown Menu Items:**
- **Overview** - Statistics and activity
  - Total files shared
  - Text shares
  - Active shares
  - Download statistics
- **Settings** - Security and limits
  - Max file size
  - Default expiry
  - Encryption settings
  - Anonymous sharing options
- **Files** - File management
  - View all shared files
  - Monitor downloads
  - Remove files
  - Expired files cleanup
- **Text Shares** - Text management
  - View text shares
  - Monitor views
  - Content moderation
- **Notifications** - Notification center
  - User notifications
  - System alerts
  - Email notifications

### Security Center
**Dropdown Menu Items:**
- **Overview** - Security dashboard
- **Blocked IPs** - IP blacklist management
- **Failed Logins** - Login attempt monitoring

### Activity Logs
**Dropdown Menu Items:**
- **All Logs** - Complete activity log
- **User Activity** - User action tracking
- **System Logs** - System events

### Settings
**Dropdown Menu Items:**
- **General** - System settings
- **Maintenance** - Maintenance mode

## Dashboard Enhancements

### Project Statistics Cards
The dashboard now displays real-time statistics for all three new projects:

**CodeXPro Card:**
- Total projects count
- Code snippets saved
- Quick access button

**ImgTxt Card:**
- Total OCR jobs
- Completed jobs
- Quick access button

**ProShare Card:**
- Files shared
- Text shares
- Quick access button

### Visual Design
- Gradient backgrounds for each project
- Icon indicators
- Color-coded statistics
- Hover effects
- Responsive grid layout

## Mobile Responsive Features

### Sidebar Behavior:
- **Desktop** (>768px): Fixed sidebar always visible
- **Mobile** (<768px): Collapsible sidebar with overlay
  - Hamburger menu button in top bar
  - Slide-in animation
  - Click outside to close
  - Touch-friendly navigation

### Dropdown Menus:
- **Desktop**: Smooth expand/collapse animation
- **Mobile**: Optimized for touch
- **Auto-open**: Active page's dropdown automatically expands
- **Single-open**: Only one dropdown open at a time

## URL Structure

### Admin Base:
```
/admin/dashboard
```

### Projects:
```
/admin/projects                          # All projects overview
/admin/projects/{project_name}           # Project overview
/admin/projects/{project_name}/settings  # Project settings
```

### CodeXPro URLs:
```
/admin/projects/codexpro                 # Overview
/admin/projects/codexpro/settings        # Settings
/admin/projects/codexpro/users           # Users
/admin/projects/codexpro/templates       # Templates
```

### ImgTxt URLs:
```
/admin/projects/imgtxt                   # Overview
/admin/projects/imgtxt/settings          # Settings
/admin/projects/imgtxt/jobs              # OCR Jobs
/admin/projects/imgtxt/languages         # Languages
```

### ProShare URLs:
```
/admin/projects/proshare                 # Overview
/admin/projects/proshare/settings        # Settings
/admin/projects/proshare/files           # Files
/admin/projects/proshare/texts           # Text Shares
/admin/projects/proshare/notifications   # Notifications
```

## Layout System

### Admin Layout (`views/layouts/admin.php`)
The new admin layout includes:
- Fixed left sidebar with navigation
- Top bar with page title and user menu
- Content area with automatic padding
- Mobile overlay and menu toggle
- JavaScript for dropdown functionality

### Using the Admin Layout:
```php
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<!-- Your content here -->
<?php View::endSection(); ?>
```

## Styling and Theme

### Color Scheme:
- **Primary**: Cyan (#00f0ff)
- **Secondary**: Magenta (#ff2ec4)
- **Success**: Green (#00ff88)
- **Warning**: Orange (#ffaa00)
- **Error**: Red (#ff6b6b)
- **Background**: Dark (#06060a)

### Sidebar:
- Dark translucent background
- Backdrop blur effect
- Border separation
- Smooth scrolling

### Menu Items:
- Icon + text layout
- Hover effects
- Active state highlighting
- Badge support for notifications

### Dropdown Behavior:
- Chevron icon rotation
- Max-height transition
- Background highlighting
- Nested indentation

## JavaScript Functionality

### Mobile Menu:
```javascript
// Toggle sidebar on mobile
mobileMenuBtn.addEventListener('click', () => {
    sidebar.classList.toggle('active');
    sidebarOverlay.classList.toggle('active');
});
```

### Dropdown Menus:
```javascript
// Toggle dropdown
toggle.addEventListener('click', function() {
    const dropdown = this.parentElement;
    dropdown.classList.toggle('open');
});
```

### Auto-expand:
```javascript
// Auto-open dropdown if current page is in it
if (link.classList.contains('active')) {
    link.parentElement.parentElement.classList.add('open');
}
```

## Best Practices

### Adding New Projects:
1. Add project routes in `routes/admin.php`
2. Create project controllers
3. Add dropdown menu in `views/layouts/admin.php`
4. Add statistics to dashboard
5. Create project-specific admin views

### Menu Structure:
- Keep main sections at top level
- Use dropdowns for sub-sections
- Limit dropdown depth to 2 levels
- Use clear, descriptive labels
- Add icons for visual identification

### Mobile Optimization:
- Test all menu interactions on mobile
- Ensure touch targets are at least 44px
- Verify overlay closes properly
- Check dropdown functionality

## Troubleshooting

### Sidebar Not Showing:
- Check if admin layout is being used
- Verify user has admin role
- Clear browser cache

### Dropdowns Not Working:
- Check JavaScript is loaded
- Verify menu structure
- Check for JavaScript errors

### Mobile Menu Issues:
- Verify media queries
- Check mobile menu button
- Test overlay functionality

## Future Enhancements

Potential additions:
- Search functionality in sidebar
- Collapsible/expandable sidebar
- User preference for sidebar state
- Notifications badge in sidebar
- Quick actions menu
- Keyboard shortcuts
- Dark/light theme toggle
- Custom sidebar order
- Favorite/pinned sections
- Recent pages history
