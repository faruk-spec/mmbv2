# Home Page Customization - Migration Guide

This guide explains how to apply the home page customization feature to an existing MyMultiBranch installation.

## Overview

The home page customization feature allows administrators to:
- Customize hero section (title, subtitle, description, banner image)
- Change the projects section title with a lightning icon
- Add images to project cards
- Enable/disable projects
- Customize project colors, icons, and descriptions

All customization is done through the admin panel at `/admin/home-content`.

## Database Migration

### For New Installations

The schema is already included in `/install/schema.sql`. No additional action needed.

### For Existing Installations

Run the migration SQL script:

```bash
mysql -u your_username -p your_database_name < install/migrations/home_page_customization.sql
```

Or manually execute the SQL from `install/migrations/home_page_customization.sql` in your database management tool (phpMyAdmin, MySQL Workbench, etc.).

## What Gets Created

### Tables

1. **home_content** - Stores hero section and projects section title
   - Includes fields for title, subtitle, description, image_url
   
2. **home_projects** - Stores project information with images
   - Extends the existing projects configuration from `/config/projects.php`
   - Includes project_key, name, description, image_url, icon, color, and more

### Default Data

The migration automatically populates:
- Default hero section content
- Projects section title: "Explore Our Super Fast Products"
- All 6 projects (CodeXPro, DevZone, ImgTxt, ProShare, QR Generator, ResumeX)

## Features

### 1. Hero Section Customization
- **Title**: Main heading with gradient effect
- **Subtitle**: Tagline displayed below title
- **Description**: Detailed platform description
- **Banner Image**: Right-side banner image (supports JPEG, PNG, GIF, WebP up to 5MB)

### 2. Projects Section
- **Section Title**: Customizable with animated lightning icon ⚡
- **Project Cards**: Each project can have:
  - Custom image (top of card)
  - Name and description
  - Color theme
  - Icon
  - Enable/disable status

### 3. Image Upload
- Upload directory: `/storage/uploads/home/`
- Supported formats: JPEG, PNG, GIF, WebP
- Max file size: 5MB
- Automatic image optimization recommended

## Admin Panel Access

1. Log in as an administrator
2. Navigate to **Settings** → **Home Page** in the sidebar
3. Or go directly to `/admin/home-content`

## File Structure

```
/controllers/Admin/HomeContentController.php  - Admin controller for home page management
/views/admin/home/index.php                   - Admin interface for customization
/views/home.php                               - Updated home page view (uses database)
/storage/uploads/home/                        - Image uploads directory
/install/migrations/home_page_customization.sql - Migration script
```

## Backwards Compatibility

The system maintains backwards compatibility:
- If database tables don't exist, falls back to `/config/projects.php`
- Default hero content uses APP_NAME constant if no database entry exists
- Existing installations will see default content until customization is applied

## Security

- All user input is sanitized using `Security::sanitize()`
- File uploads are validated (type, size)
- Images are stored in protected directory with .htaccess
- CSRF protection on all forms
- Admin authentication required

## Responsive Design

The new hero section layout:
- Desktop: Two-column layout (content | image)
- Mobile: Stacked layout (content above image)
- Uses CSS Grid for responsive behavior

## Next Steps

After migration:
1. Visit `/admin/home-content` to customize your home page
2. Upload a hero banner image
3. Upload images for each project
4. Adjust colors, descriptions as needed
5. Preview changes at `/`

## Troubleshooting

**Images not displaying?**
- Check file permissions on `/storage/uploads/home/` (755)
- Verify .htaccess allows image files
- Check image paths in database

**Cannot access admin panel?**
- Ensure you're logged in as admin or super_admin
- Check routes in `/routes/admin.php`

**Migration fails?**
- Check for duplicate entries if re-running
- Ensure database user has CREATE, INSERT permissions
- Check MySQL/MariaDB version compatibility (requires 5.7+)

## Support

For issues or questions, check the main README.md or open an issue in the repository.
