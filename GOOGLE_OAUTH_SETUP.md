# Google OAuth Setup Guide

This guide explains how to configure Google OAuth 2.0 authentication for your MMB application.

## Prerequisites

1. A Google Cloud Platform (GCP) account
2. Access to the admin panel of your MMB application
3. Your application's URL (e.g., `https://yourdomain.com`)

## Step 1: Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project or select an existing one
3. Note your project name and ID

## Step 2: Enable Google+ API

1. In your Google Cloud Project, go to **APIs & Services** → **Library**
2. Search for "Google+ API"
3. Click on it and enable it for your project

## Step 3: Configure OAuth Consent Screen

1. Go to **APIs & Services** → **OAuth consent screen**
2. Choose **External** (or Internal if you're using Google Workspace)
3. Fill in the required information:
   - App name: Your Application Name
   - User support email: Your email
   - Developer contact email: Your email
4. Add scopes:
   - `openid`
   - `email`
   - `profile`
5. Save and continue

## Step 4: Create OAuth 2.0 Credentials

1. Go to **APIs & Services** → **Credentials**
2. Click **Create Credentials** → **OAuth 2.0 Client ID**
3. Choose **Web application** as application type
4. Configure:
   - **Name**: MMB OAuth Client
   - **Authorized JavaScript origins**: Add your application URL
     ```
     https://yourdomain.com
     ```
   - **Authorized redirect URIs**: Add your callback URL
     ```
     https://yourdomain.com/auth/google/callback
     ```
5. Click **Create**
6. **Important**: Copy the **Client ID** and **Client Secret** - you'll need these!

## Step 5: Configure in MMB Admin Panel

1. Log in to your MMB admin panel
2. Navigate to **Admin** → **OAuth Providers** (or `/admin/oauth`)
3. Click **Configure** for Google
4. Fill in the form:
   - **Client ID**: Paste the Client ID from Google
   - **Client Secret**: Paste the Client Secret from Google
   - **Redirect URI**: Leave blank to use default, or specify custom URL
   - **Scopes**: Default is `openid email profile` (recommended)
   - **Enable checkbox**: Check this to activate Google OAuth
5. Click **Save Changes**

## Step 6: Run Database Migration

If you haven't already, run the OAuth database migration:

```bash
# Connect to your database and run:
mysql -u your_user -p your_database < install/migrations/add_oauth_and_session_management.sql
```

Or import through phpMyAdmin or your database management tool.

## Step 7: Test the Integration

1. Log out of your account (if logged in)
2. Go to the login page
3. You should see a **"Sign in with Google"** button
4. Click it and authorize with your Google account
5. You should be logged in successfully

## Troubleshooting

### "Google Sign-In is not configured" error
- Make sure you've enabled Google OAuth in the admin panel
- Verify the Client ID and Client Secret are correct
- Check that the redirect URI matches exactly

### "redirect_uri_mismatch" error
- The redirect URI in Google Cloud Console must exactly match your application's callback URL
- Common mistake: Missing or extra trailing slashes, http vs https

### "Access denied" error
- Check that the user's email is allowed (if you're using Internal consent screen)
- Verify OAuth consent screen is published

### Users can't link Google account
- Make sure the feature is enabled in admin panel
- Check database tables exist (oauth_providers, oauth_user_connections)

## Session Management

The system now includes automatic session management:

- **Session Timeout**: Default 120 minutes (configurable)
- **Auto Logout**: Users are automatically logged out after timeout
- **Session Tracking**: All active sessions are tracked in the database

### Admin Features

Admins can:
- View all active sessions at `/admin/sessions`
- Revoke specific sessions
- View login history at `/admin/sessions/login-history`
- Manage OAuth connections at `/admin/oauth/connections`

## Security Best Practices

1. **Keep secrets secure**: Never commit Client Secret to version control
2. **Use HTTPS**: OAuth requires secure connections in production
3. **Regular audits**: Review OAuth connections and sessions regularly
4. **Rotate secrets**: Change Client Secret periodically
5. **Monitor logs**: Check login history for suspicious activity

## User Features

Users can:
- Link/unlink Google account from `/security` page
- View active sessions
- Sign in with Google from login page

## Configuration Options

### Session Timeout
Edit `config/app.php`:
```php
define('SESSION_LIFETIME', 120); // minutes
```

### OAuth Scopes
Default scopes are:
- `openid`: Basic OpenID Connect
- `email`: User's email address
- `profile`: User's name and profile picture

You can add more scopes in the admin panel if needed.

## API Endpoints

- `GET /auth/google` - Initiates OAuth flow
- `GET /auth/google/callback` - OAuth callback handler
- `GET /auth/google/link` - Link Google to existing account (authenticated)
- `POST /auth/google/unlink` - Unlink Google account (authenticated)

## Database Tables

The OAuth feature uses these tables:
- `oauth_providers` - OAuth provider configurations
- `oauth_user_connections` - User-provider connections
- `user_sessions` - Active user sessions
- `login_history` - Login attempt audit log

## Support

For issues or questions:
1. Check the troubleshooting section above
2. Review application logs in `storage/logs/`
3. Check Google Cloud Console for OAuth errors
4. Verify database tables were created correctly
