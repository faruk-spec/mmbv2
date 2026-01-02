# Project URLs Quick Reference

## Development URLs (localhost)

```
# Main Platform
http://localhost/
http://localhost/login
http://localhost/register
http://localhost/dashboard

# CodeXPro - Live Code Editor
http://localhost/projects/codexpro              # Dashboard
http://localhost/projects/codexpro/editor       # Live Editor (Main Feature)
http://localhost/projects/codexpro/editor/new   # New Project
http://localhost/projects/codexpro/projects     # All Projects
http://localhost/projects/codexpro/snippets     # Code Snippets

# ImgTxt - OCR Tool
http://localhost/projects/imgtxt                # Dashboard
http://localhost/projects/imgtxt/upload         # Upload Image (Main Feature)
http://localhost/projects/imgtxt/batch          # Batch Processing
http://localhost/projects/imgtxt/history        # History

# ProShare - File Sharing
http://localhost/projects/proshare              # Dashboard
http://localhost/projects/proshare/upload       # Upload & Share (Main Feature)
http://localhost/projects/proshare/files        # My Files
```

## Production URLs (replace yourdomain.com)

```
# Main Platform
https://yourdomain.com/
https://yourdomain.com/login
https://yourdomain.com/register
https://yourdomain.com/dashboard

# CodeXPro
https://yourdomain.com/projects/codexpro/editor

# ImgTxt
https://yourdomain.com/projects/imgtxt/upload

# ProShare
https://yourdomain.com/projects/proshare/upload
```

## URL Structure

All projects follow this pattern:
```
/{main_route}/projects/{project_name}/{action}

Examples:
/projects/codexpro/editor          # Action: editor
/projects/imgtxt/upload            # Action: upload
/projects/proshare/dashboard       # Action: dashboard
```

## Anonymous Access

ProShare supports anonymous sharing:
```
http://localhost/s/{shortcode}     # Access shared file without login
```

Example: After uploading a file, you'll get a short URL like:
```
http://localhost/s/a1B2c3D4
```

This link can be shared with anyone, even without an account.
