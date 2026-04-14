# Mail Subdomain Module

A standalone webmail application that can be deployed at `mail.yourdomain.in`.

## Directory Structure

```
subdomain/mail/
├── index.php              # Entry point + mini-router
├── .htaccess              # URL rewriting
├── controllers/
│   └── InboxController.php
└── views/
    ├── layout.php         # Base HTML layout (dark theme)
    ├── inbox.php          # Inbox/folder listing
    ├── view.php           # Single message view + reply/forward
    ├── compose.php        # Compose new email
    └── settings.php       # User settings + sync trigger
```

## Deployment

1. Copy the entire `subdomain/mail/` directory to your web server under the `mail.yourdomain.in` virtual host.
2. Set `DocumentRoot` to the copied directory.
3. Make sure `.htaccess` is enabled (`AllowOverride All`).
4. The app automatically locates the main platform two directories up (`dirname(__DIR__, 2)`).
   - Adjust the `$mainRoot` path in `index.php` if your directory structure is different.

### Example Apache VirtualHost

```apache
<VirtualHost *:80>
    ServerName mail.yourdomain.in
    DocumentRoot /var/www/mail
    <Directory /var/www/mail>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Example Nginx config

```nginx
server {
    listen 80;
    server_name mail.yourdomain.in;
    root /var/www/mail;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

## Features

| Feature | Status |
|---------|--------|
| Inbox listing with pagination | ✅ |
| Starred messages folder | ✅ |
| Archived messages folder | ✅ |
| View email (HTML + plaintext) | ✅ |
| Compose new email | ✅ |
| Reply to email | ✅ |
| Forward email | ✅ |
| Mark read/unread | ✅ |
| Star / unstar | ✅ |
| Archive / delete | ✅ |
| Full-text search | ✅ |
| IMAP inbox sync | ✅ (requires PHP `imap` extension) |
| User settings page | ✅ |
| Responsive dark-theme UI | ✅ |

## Requirements

- PHP 8.0+
- Main platform must be accessible (shared DB and session)
- PHP `imap` extension (for IMAP sync)
- Active mail provider configured in Admin → Mail Config

## Authentication

The app reuses the main platform session. If the user is not logged in, they are redirected to `APP_URL/login`.

## Mail Configuration

All SMTP/IMAP credentials are managed in the main platform admin panel at:

```
https://yourdomain.in/admin/mail/config
```
