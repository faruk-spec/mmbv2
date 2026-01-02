# MMB Projects - Access Guide

## Quick Start URLs

After setting up the platform, you can access the projects at these URLs:

### Main Platform
- **Homepage**: `http://yourdomain.com/`
- **Login**: `http://yourdomain.com/login`
- **Register**: `http://yourdomain.com/register`
- **Dashboard**: `http://yourdomain.com/dashboard`

### Project URLs

#### 1. CodeXPro - Live Code Editor
- **Dashboard**: `http://yourdomain.com/projects/codexpro`
- **Live Editor**: `http://yourdomain.com/projects/codexpro/editor`
- **New Project**: `http://yourdomain.com/projects/codexpro/editor/new`
- **Projects List**: `http://yourdomain.com/projects/codexpro/projects`
- **Snippets**: `http://yourdomain.com/projects/codexpro/snippets`

#### 2. ImgTxt - OCR Image to Text
- **Dashboard**: `http://yourdomain.com/projects/imgtxt`
- **Upload**: `http://yourdomain.com/projects/imgtxt/upload`
- **Batch Processing**: `http://yourdomain.com/projects/imgtxt/batch`
- **History**: `http://yourdomain.com/projects/imgtxt/history`

#### 3. ProShare - Secure File Sharing
- **Dashboard**: `http://yourdomain.com/projects/proshare`
- **Upload/Share**: `http://yourdomain.com/projects/proshare/upload`
- **My Files**: `http://yourdomain.com/projects/proshare/files`
- **Anonymous Share**: `http://yourdomain.com/s/{short_code}` (after uploading)

## Local Development Setup

### For Local Testing (e.g., localhost)

1. **Set up your web server** (Apache/Nginx)
   ```
   DocumentRoot: /path/to/mmb/public
   OR
   DocumentRoot: /path/to/mmb (with .htaccess)
   ```

2. **Access via localhost**:
   - If using port 80: `http://localhost/`
   - If using custom port: `http://localhost:8080/`
   - With virtual host: `http://mmb.local/`

3. **Example URLs for localhost**:
   ```
   http://localhost/projects/codexpro/editor
   http://localhost/projects/imgtxt/upload
   http://localhost/projects/proshare/upload
   ```

## Database Setup Required

Before accessing the projects, you need to:

1. **Run the installation** (if not done):
   ```
   http://yourdomain.com/install/
   ```

2. **Create project databases**:
   ```sql
   CREATE DATABASE mmb_codexpro;
   CREATE DATABASE mmb_imgtxt;
   CREATE DATABASE mmb_proshare;
   ```

3. **Import schemas**:
   ```bash
   mysql -u root -p mmb_codexpro < projects/codexpro/schema.sql
   mysql -u root -p mmb_imgtxt < projects/imgtxt/schema.sql
   mysql -u root -p mmb_proshare < projects/proshare/schema.sql
   ```

## Testing the Projects

### CodeXPro Test Flow:
1. Navigate to `http://yourdomain.com/projects/codexpro/editor`
2. Write HTML in the first tab
3. Write CSS in the second tab
4. Write JavaScript in the third tab
5. See live preview on the right panel
6. Click "Save" to save the project
7. Click "Export" to download as HTML file

### ImgTxt Test Flow:
1. Navigate to `http://yourdomain.com/projects/imgtxt/upload`
2. Drag and drop an image or PDF
3. Select language (English, Spanish, etc.)
4. Click "Extract Text"
5. View extracted text
6. Download or copy to clipboard

### ProShare Test Flow:
1. Navigate to `http://yourdomain.com/projects/proshare/upload`
2. Drag and drop a file
3. Set options (expiry, password, max downloads, etc.)
4. Click "Upload & Share"
5. Copy the share link
6. Test the link in incognito/private window (works without login)

## Mobile Testing

Test responsive design by:
1. Opening DevTools (F12)
2. Toggle device toolbar (Ctrl+Shift+M)
3. Select different devices (iPhone, iPad, etc.)
4. Test all features on mobile view

## Project Features Summary

### CodeXPro Features:
- ✅ Split-pane editor with live preview
- ✅ HTML/CSS/JavaScript support
- ✅ Auto-save every 3 seconds
- ✅ Export to HTML file
- ✅ Keyboard shortcuts (Ctrl+S to save)
- ✅ Mobile responsive

### ImgTxt Features:
- ✅ Drag & drop upload
- ✅ OCR processing (requires Tesseract)
- ✅ Multi-language support (10+ languages)
- ✅ Batch processing
- ✅ Download as TXT
- ✅ Copy to clipboard
- ✅ Mobile responsive

### ProShare Features:
- ✅ Anonymous sharing (no login required)
- ✅ Drag & drop upload
- ✅ Password protection
- ✅ Link expiry (1 hour to 30 days)
- ✅ Max downloads limit
- ✅ Self-destruct option
- ✅ Progress tracking
- ✅ Mobile responsive
- ✅ 20+ file types supported

## Troubleshooting

### If projects don't load:
1. Check database connection in `config/database.php`
2. Ensure project databases are created
3. Verify schemas are imported
4. Check web server error logs
5. Enable `APP_DEBUG` in `config/app.php`

### For ImgTxt OCR:
If OCR doesn't work, install Tesseract:
```bash
# Ubuntu/Debian
sudo apt-get install tesseract-ocr tesseract-ocr-eng

# macOS
brew install tesseract

# Verify
tesseract --version
```

### For file uploads:
Check PHP upload limits in `php.ini`:
```ini
upload_max_filesize = 500M
post_max_size = 500M
max_execution_time = 300
```

## Next Phase Ideas

Suggested next features to implement:

### For CodeXPro:
- Templates library (Bootstrap, React, Vue starter templates)
- Code collaboration (real-time editing with multiple users)
- GitHub integration (push/pull code)
- Package manager integration (npm, bower)
- Browser console output
- Responsive design preview (different screen sizes)

### For ImgTxt:
- PDF multi-page batch processing
- Advanced image preprocessing (rotation, contrast, brightness)
- Custom training for specific fonts
- Table recognition and export to CSV/Excel
- Handwriting recognition
- Real-time camera OCR (via webcam)

### For ProShare:
- Complete chat/messaging implementation
- Group file sharing
- QR code generation for shares
- Email notifications for downloads
- Analytics dashboard
- CDN integration for faster downloads
- Virus scanning integration
- File versioning

### Admin Panel Integration:
- Feature toggles for each project
- Usage statistics and analytics
- User management per project
- Storage quota management
- Rate limiting configuration
- Custom branding per project
