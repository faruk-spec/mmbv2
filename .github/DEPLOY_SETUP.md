# Deployment Setup Guide

## Prerequisites

Before running the workflow for the first time you must complete the one-time
server-side setup described below.

---

## 1. Required GitHub Repository Secrets

Go to **Settings → Secrets and variables → Actions** and add:

| Secret name       | Description                                        | Example                       |
|-------------------|----------------------------------------------------|-------------------------------|
| `AAPANEL_HOST`    | Server IP or hostname                              | `123.45.67.89`                |
| `AAPANEL_USER`    | SSH user with write access to wwwroot              | `root` or `deploy`            |
| `AAPANEL_PASSWORD`| SSH password for the above user                   | *(your password)*             |
| `SITE_NAME`       | Site folder name inside `/www/wwwroot/`            | `mmbtech.online`              |

---

## 2. One-time Server Setup

SSH into your server and run:

```bash
# Replace mmbtech.online with your actual SITE_NAME
SITE=mmbtech.online
APP=/www/wwwroot/$SITE

# Create the directory structure
mkdir -p "$APP/releases"
mkdir -p "$APP/shared/storage/logs"
mkdir -p "$APP/shared/storage/cache"
mkdir -p "$APP/shared/storage/uploads"
mkdir -p "$APP/shared/storage/idcard/logos"
mkdir -p "$APP/shared/storage/idcard/photos"
mkdir -p "$APP/shared/config"

# Place production config files in the shared area (they persist across deploys)
# Copy from your existing installation or create from scratch:
cp /path/to/existing/config/database.php "$APP/shared/config/database.php"
cp /path/to/existing/config/mail.php     "$APP/shared/config/mail.php"

# Point aaPanel's site root at the 'current' symlink
# In aaPanel → Website → Root Directory, set it to:
#   /www/wwwroot/$SITE/current
# (The 'current' symlink is created automatically on first deploy.)
```

---

## 3. aaPanel Website Root Directory

In the aaPanel control panel, set the **Root Directory** for your site to:

```
/www/wwwroot/mmbtech.online/current
```

This ensures every deploy is zero-downtime (the symlink switch is atomic).

---

## 4. How Releases Work

```
/www/wwwroot/mmbtech.online/
├── releases/
│   ├── 42/            ← deploy run #42
│   ├── 43/            ← deploy run #43 (current)
│   └── ...
├── shared/
│   ├── config/
│   │   ├── database.php   ← never overwritten
│   │   └── mail.php       ← never overwritten
│   └── storage/
│       ├── logs/          ← persists across releases
│       ├── cache/         ← persists across releases
│       └── uploads/       ← persists across releases
└── current -> releases/43/   ← symlink switched on each deploy
```

Old releases are pruned automatically, keeping the last **5** releases for quick rollback.

---

## 5. Running a Deployment

1. Go to **Actions → 🚀 Deploy to aaPanel → Run workflow**
2. Choose **Deploy Latest**
3. Leave *Version* empty
4. Click **Run workflow**

---

## 6. Rolling Back

1. Go to **Actions → 🚀 Deploy to aaPanel → Run workflow**
2. Choose **Rollback**
3. Leave *Version* empty to roll back to the previous release, **or** enter a
   specific run number (e.g. `42`) to roll back to that exact release
4. Click **Run workflow**

---

## 7. Production Config Template

If you do not have a `shared/config/database.php` yet, create it:

```php
<?php
return [
    'host'      => 'localhost',
    'database'  => 'your_db_name',
    'username'  => 'your_db_user',
    'password'  => 'your_db_password',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];
```

And `shared/config/mail.php`:

```php
<?php
return [
    'driver'     => 'smtp',
    'host'       => 'smtp.example.com',
    'port'       => 587,
    'username'   => 'no-reply@example.com',
    'password'   => 'your_smtp_password',
    'encryption' => 'tls',
    'from'       => [
        'address' => 'no-reply@example.com',
        'name'    => 'MMB Platform',
    ],
];
```
