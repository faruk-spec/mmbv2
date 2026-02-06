# Google OAuth Session Tracking Fix

## Issue
Google OAuth logged-in users were unable to visit/access projects after login.

## Root Cause
The `SessionManager::track()` method was attempting to query a `session_timeout_minutes` column from the users table that may not exist in all database installations. When this query failed:

1. An exception was thrown
2. The catch block logged the error but didn't set session metadata
3. Critical session variables were never initialized:
   - `$_SESSION['_last_activity']`
   - `$_SESSION['_expires_at']`
   - `$_SESSION['_timeout_minutes']`

4. When users tried to access projects, `SessionManager::checkExpiration()` couldn't find these variables
5. Session validation would fail or behave unpredictably
6. Users were blocked from accessing projects

## The Fix

### Changes to `core/SessionManager.php`

#### 1. `track()` Method - Set Metadata First
**Before:**
```php
public static function track(int $userId): void
{
    try {
        $db = Database::getInstance();
        
        // Query for session_timeout_minutes (could fail)
        $user = $db->fetch("SELECT session_timeout_minutes...");
        $timeoutMinutes = ...;
        
        // ... database operations ...
        
        // Set session metadata at the END (never reached if exception)
        $_SESSION['_last_activity'] = time();
        $_SESSION['_expires_at'] = time() + ($timeoutMinutes * 60);
        $_SESSION['_timeout_minutes'] = $timeoutMinutes;
        
    } catch (\Exception $e) {
        Logger::error('Session tracking error: ' . $e->getMessage());
        // Session metadata NEVER set!
    }
}
```

**After:**
```php
public static function track(int $userId): void
{
    // Set default timeout first
    $timeoutMinutes = SESSION_LIFETIME;
    
    try {
        $db = Database::getInstance();
        
        // Try to get custom timeout (with individual try-catch)
        try {
            $user = $db->fetch("SELECT session_timeout_minutes...");
            if ($user && isset($user['session_timeout_minutes']) && $user['session_timeout_minutes'] > 0) {
                $timeoutMinutes = $user['session_timeout_minutes'];
            }
        } catch (\Exception $e) {
            // Column doesn't exist, use default
            Logger::info('Could not fetch custom timeout, using default');
        }
        
        // Set session metadata IMMEDIATELY (before DB operations)
        $_SESSION['_last_activity'] = time();
        $_SESSION['_expires_at'] = time() + ($timeoutMinutes * 60);
        $_SESSION['_timeout_minutes'] = $timeoutMinutes;
        
        // ... rest of database operations ...
        
    } catch (\Exception $e) {
        Logger::error('Session tracking error: ' . $e->getMessage());
        
        // Double-check metadata is set even if DB failed
        if (!isset($_SESSION['_last_activity'])) {
            $_SESSION['_last_activity'] = time();
            $_SESSION['_expires_at'] = time() + ($timeoutMinutes * 60);
            $_SESSION['_timeout_minutes'] = $timeoutMinutes;
        }
    }
}
```

#### 2. `checkExpiration()` Method - Auto-Recovery
Added logic to initialize missing session metadata on-the-fly:

```php
public static function checkExpiration(): bool
{
    if (!Auth::check()) {
        return true;
    }
    
    $lastActivity = $_SESSION['_last_activity'] ?? 0;
    $expiresAt = $_SESSION['_expires_at'] ?? 0;
    $timeoutMinutes = $_SESSION['_timeout_minutes'] ?? SESSION_LIFETIME;
    
    // NEW: If metadata is missing, initialize it now
    if ($lastActivity === 0 || $expiresAt === 0) {
        $_SESSION['_last_activity'] = time();
        $_SESSION['_expires_at'] = time() + ($timeoutMinutes * 60);
        $_SESSION['_timeout_minutes'] = $timeoutMinutes;
        Logger::info('Session metadata was missing, initialized for user: ' . Auth::id());
        return true; // Allow this request to proceed
    }
    
    // ... rest of expiration checking ...
}
```

## Benefits

1. **Resilient to Database Issues**: Session tracking won't fail if:
   - `session_timeout_minutes` column doesn't exist
   - Database queries fail
   - Network issues occur

2. **Guaranteed Session Metadata**: Session variables are ALWAYS set:
   - Before database operations
   - Even if exceptions occur
   - With fallback to default timeout

3. **Auto-Recovery**: If metadata is somehow missing:
   - Automatically initialized when needed
   - User session continues without interruption
   - Logged for debugging

4. **Backward Compatible**: Works with or without `session_timeout_minutes` column

## Testing

Comprehensive test suite validates:
- ✅ OAuth login sets session metadata correctly
- ✅ Session expiration checks pass
- ✅ Authentication works
- ✅ Project access succeeds
- ✅ Missing metadata recovers automatically

## Migration Notes

No database migrations required. The fix gracefully handles both scenarios:
- ✅ Database with `session_timeout_minutes` column
- ✅ Database without `session_timeout_minutes` column

## Production Deployment

This fix is safe to deploy immediately:
- No breaking changes
- No database alterations needed
- Improves stability for all users
- Especially critical for OAuth users

## Related Issues

This fix resolves:
- Google OAuth users unable to access projects
- Session validation failing after OAuth login
- Random session timeouts for OAuth users
- "Session expired" errors for valid sessions
