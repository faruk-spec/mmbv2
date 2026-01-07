# MyMultiBranch Platform

A modular, multi-project PHP platform with centralized authentication, unified admin panel, secure architecture, and future-proof structure.

## Features

- **Unified Authentication (SSO)**: Single login/register/logout for main site and all projects
- **Global Admin Panel**: Manage main site and all projects from a single dashboard
- **Secure by Design**: Argon2id password hashing, CSRF protection, XSS sanitization, rate limiting
- **Modular Architecture**: MVC structure with PSR-4 autoloading
- **Project Management**: Support for multiple sub-projects with independent databases
- **Dark Neon UI Theme**: Futuristic design with clean edges and neon accents

## Requirements

- PHP 8.0 or higher
- MySQL 5.7+ / MariaDB 10.3+
- Apache with mod_rewrite OR Nginx
- Required PHP extensions: pdo, pdo_mysql, json, mbstring, openssl, session

## Installation

1. Clone or download the repository to your web server
2. Point your web server's document root to the `/public` directory (recommended) or the project root
3. Configure your web server:

   **For Apache**: The `.htaccess` files are already included. Ensure `mod_rewrite` is enabled and `AllowOverride All` is set.
   
   **For Nginx**: Copy the URL rewrite rules from `nginx.conf.example` to your server configuration or BT Panel's URL Rewrite section:
   ```nginx
   location / {
       try_files $uri $uri/ /index.php?url=$uri&$query_string;
   }
   ```

4. Navigate to `/install/` in your browser
5. Follow the installation wizard to:
   - Check system requirements
   - Configure database connection
   - Create database schema
   - Set up admin account
   - Generate configuration files

## Folder Structure

```
/public/        → Web root with index.php entry point
/core/          → Shared framework modules
  - App.php     → Main application class
  - Auth.php    → Authentication system
  - Database.php → Database connection manager
  - Router.php  → URL routing
  - Security.php → Security utilities
  - SSO.php     → Single Sign-On system
  - View.php    → Template engine
  - Logger.php  → Logging system
  - Helpers.php → Helper functions
  - Middleware/ → Request middleware
/config/        → Configuration files
/controllers/   → Request controllers
/views/         → View templates
/routes/        → Route definitions
/projects/      → Sub-projects (codexpro, devzone, imgtxt, proshare, qr, resumex)
/admin/         → Global admin panel
/install/       → Installation wizard
/storage/       → Protected storage (logs, cache, uploads)
```

## Security Features

- **Argon2id Password Hashing**: Industry-standard secure password hashing
- **CSRF Protection**: Token-based protection on all forms
- **XSS Sanitization**: Input sanitization to prevent cross-site scripting
- **PDO Prepared Statements**: SQL injection prevention
- **Rate Limiting**: Protection against brute-force attacks
- **Session Fingerprinting**: Session hijacking prevention
- **Secure Cookies**: HttpOnly and Secure cookie flags
- **Storage Protection**: PHP execution blocked in /storage

## User Roles

- `super_admin`: Full system access
- `admin`: Administrative access to all features
- `project_admin`: Project-specific administrative access
- `user`: Standard user access

## Available Projects

- **CodeXPro**: Advanced code editor and IDE platform
- **DevZone**: Developer collaboration and project management
- **ImgTxt**: Image to text converter and OCR tool
- **ProShare**: Secure file sharing platform
- **QR Generator**: QR code generation and management
- **ResumeX**: Professional resume builder
- **SheetDocs**: Collaborative spreadsheet and document editor (Google Sheets/Docs alternative)

## Configuration

### Database (config/database.php)
```php
return [
    'host' => 'localhost',
    'port' => '3306',
    'database' => 'mmb_main',
    'username' => 'your_username',
    'password' => 'your_password',
];
```

### Application (config/app.php)
```php
define('APP_NAME', 'MyMultiBranch');
define('APP_URL', 'https://yourdomain.com');
define('APP_DEBUG', false);
```

## License

MIT License