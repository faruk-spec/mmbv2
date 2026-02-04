# Debugging 500 and 400 Errors

## Current Status

Based on diagnostics output:
- ✅ Bridge server running (PID 1162352)
- ✅ Health endpoint responding (HTTP 200)
- ✅ PHP connectivity working (both methods)
- ✅ PHP configuration correct
- ✅ Bridge configured correctly (0.0.0.0)
- ✅ SessionController has cURL support

**But:**
- ❌ Session creation fails with 500 error
- ❌ QR code loading fails with 400 error
- ❌ Database check shows no output (likely failing silently)

## Issues Fixed

### 1. Diagnostic Script Database Check
**Problem:** Database check output was suppressed by `2>/dev/null`, causing silent failures

**Fix:** Removed `2>/dev/null` from line 215 so errors are visible

**Now:** Database errors will be shown, helping identify real issues

## Debugging Steps

### Step 1: Run Debug Script

```bash
cd /www/wwwroot/mmbtech.online/projects/whatsapp
php debug-session-creation.php
```

This script checks:
1. ✓ Core files exist
2. ✓ Database connection works
3. ✓ whatsapp_sessions table exists and structure
4. ✓ User authentication status
5. ✓ Existing sessions can be queried
6. ✓ INSERT query syntax is correct

**Look for:**
- Missing files
- Database connection errors
- Table structure issues
- Authentication problems

### Step 2: Check PHP Error Logs

```bash
# For PHP-FPM
tail -50 /var/log/php-fpm/error.log

# For Apache
tail -50 /var/log/apache2/error.log

# For Nginx with PHP-FPM
tail -50 /var/log/nginx/error.log
```

**Look for:**
- Fatal errors
- Database connection errors
- Missing class/file errors
- Permission errors

### Step 3: Test Database Directly

```bash
cd /www/wwwroot/mmbtech.online
php -r '
require_once "core/Database.php";
require_once "config/database.php";
$db = Core\Database::getInstance();
echo "Database connected\n";
$result = $db->query("SHOW TABLES LIKE \"whatsapp%\"");
print_r($result);
'
```

### Step 4: Run Updated Diagnostics

```bash
cd /www/wwwroot/mmbtech.online/projects/whatsapp
./complete-diagnostics.sh
```

**Now shows database errors** instead of hiding them.

## Common Causes of 500 Error

### Cause 1: Database Table Missing
```sql
-- Check if table exists
SHOW TABLES LIKE 'whatsapp_sessions';

-- If missing, create it:
CREATE TABLE whatsapp_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    session_id VARCHAR(255) NOT NULL UNIQUE,
    session_name VARCHAR(255) NOT NULL,
    status VARCHAR(50) DEFAULT 'initializing',
    phone_number VARCHAR(50) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    disconnected_at TIMESTAMP NULL,
    last_activity TIMESTAMP NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_session_id (session_id)
);
```

### Cause 2: User Not Authenticated
Session creation requires authenticated user. If session expired:
1. Log out and log back in
2. Check if Auth system is working
3. Verify session cookies are being set

### Cause 3: Database Query Method Issue
The code uses `$this->db->query()` method. If Database class doesn't have this method:
- Check `core/Database.php` for correct method names
- Might need `execute()` or `exec()` instead

### Cause 4: Missing CSRF Token
Frontend must send valid CSRF token. Check:
```javascript
// In sessions.php, check if this line exists:
body: 'session_name=' + encodeURIComponent(sessionName) + '&csrf_token=<?= Security::generateCsrfToken() ?>'
```

### Cause 5: lastInsertId() Method Missing
After INSERT, code calls `$this->db->lastInsertId()`. If method doesn't exist:
- Check Database class implementation
- Might need different method name

## QR Code 400 Error

**Cause:** Cascade from session creation failure

**Why:** If session wasn't created (500 error), trying to load QR for non-existent session returns 400

**Fix:** Once session creation works, QR codes will work

**Independent check:**
```bash
# Test QR endpoint with existing session
curl http://127.0.0.1:3000/api/health
# Should return: {"success":true,"status":"running"}

# Test PHP can reach bridge
php -r 'echo file_get_contents("http://127.0.0.1:3000/api/health");'
# Should return: {"success":true...}
```

## Navbar CSS 404

**Issue:** `/assets/css/navbar.css` not found

**Not Critical** - Won't cause 500/400 errors, just missing styles

**To Fix:**
```bash
# Find where navbar.css should be
find /www/wwwroot/mmbtech.online -name "navbar.css" -type f

# Check if it's in a different path
# Common locations:
# - /www/wwwroot/mmbtech.online/public/assets/css/navbar.css
# - /www/wwwroot/mmbtech.online/assets/css/navbar.css
# - /www/wwwroot/mmbtech.online/views/assets/css/navbar.css
```

## Next Steps

1. **Run debug script** to identify exact error
2. **Check PHP logs** for fatal errors
3. **Run updated diagnostics** to see database issues
4. **Create missing table** if needed
5. **Test session creation** again

## Quick Test

After fixing issues, test session creation via command line:

```bash
cd /www/wwwroot/mmbtech.online

# Simulate session creation (as authenticated user)
php -r '
session_start();
$_SESSION["user"] = ["id" => 1, "username" => "test"];
$_SERVER["REQUEST_METHOD"] = "POST";
$_POST["session_name"] = "Test Session";
$_POST["csrf_token"] = "test";

require_once "projects/whatsapp/controllers/SessionController.php";
$controller = new Projects\WhatsApp\Controllers\SessionController();
// This will show the actual error
'
```

## Expected Results

**When Fixed:**
- Session creation returns HTTP 200 with JSON
- QR code loading works
- No 500 or 400 errors
- Diagnostics show 0 issues

**Current:**
- Session creation: 500 error
- QR code: 400 error  
- Diagnostics: 1 issue (database check failed)

**Run debug script first to identify the root cause!**
