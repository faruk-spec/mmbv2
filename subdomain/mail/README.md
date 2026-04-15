# Mail Module – Subdomain Bootstrap

The mail system is now served at the **main domain** under `/mail`.

- **Current URL:** `https://yourdomain.in/mail`
- **Future URL:** `https://mail.yourdomain.in` (when subdomain is configured)

## Current Architecture

All mail routes, controllers and views live inside the main platform:

```
controllers/
└── MailController.php        # All mail actions (inbox, compose, reply, …)
views/
├── layouts/
│   └── mail.php              # Webmail HTML layout (dark theme)
└── mail/
    ├── inbox.php             # Inbox / folder listing
    ├── view.php              # Single message + reply / forward
    ├── compose.php           # Compose new email
    └── settings.php          # User settings + sync trigger
routes/web.php                # /mail/* routes registered here
```

## This Directory (`subdomain/mail/`)

The `index.php` in this folder is a **redirect bootstrap** that currently
sends visitors to the main domain's `/mail` path.  When you are ready to
point `mail.yourdomain.in` to its own web root, follow the steps below.

## Upgrading to a True Subdomain

1. **Set up DNS** – add an `A` (or `CNAME`) record for `mail.yourdomain.in`
   pointing to your server.
2. **Configure the virtual host** – set its `DocumentRoot` (or `root`) to
   this `subdomain/mail/` directory.
3. **Edit `index.php`** in this directory:
   - Remove the redirect block.
   - Uncomment the "Standalone mode" block at the bottom of the file.
   - Set the `MAIN_APP_PATH` environment variable (or update `getenv()` call)
     to the absolute path of the main platform installation.
4. Ensure both the main domain and the subdomain share:
   - The same database
   - The same session cookie domain (e.g. `.yourdomain.in`)

### Example Apache VirtualHost

```apache
<VirtualHost *:443>
    ServerName mail.yourdomain.in
    DocumentRoot /var/www/mmbv2/subdomain/mail
    SetEnv MAIN_APP_PATH /var/www/mmbv2
    SetEnv APP_URL https://yourdomain.in

    <Directory /var/www/mmbv2/subdomain/mail>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

### Example Nginx Config

```nginx
server {
    listen 443 ssl;
    server_name mail.yourdomain.in;
    root /var/www/mmbv2/subdomain/mail;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_param MAIN_APP_PATH /var/www/mmbv2;
        fastcgi_param APP_URL https://yourdomain.in;
        include fastcgi_params;
    }
}
```

## Mail Configuration

Admin panel → **Mail Config**: `https://yourdomain.in/admin/mail/config`

## Requirements

- PHP 8.0+ with `imap` extension (for IMAP sync)
- Active mail provider configured in the admin panel
