<?php
/**
 * Authentication System
 * 
 * @package MMB\Core
 */

namespace Core;

class Auth
{
    private static ?array $user = null;
    
    /**
     * Attempt login
     */
    public static function attempt(string $email, string $password, bool $remember = false): bool
    {
        $user = self::attemptCredentials($email, $password);
        
        if ($user) {
            // Check if 2FA is enabled
            if (!empty($user['two_factor_secret']) && $user['two_factor_enabled']) {
                // For 2FA users, don't complete login here
                // Caller should redirect to 2FA verification
                return false;
            }
            
            // Complete login for non-2FA users
            return self::loginUser($user['id'], $remember);
        }
        
        return false;
    }
    
    /**
     * Attempt to authenticate credentials without logging in
     * Returns user array if valid, null if invalid
     */
    public static function attemptCredentials(string $email, string $password): ?array
    {
        // Rate limiting
        $ip = Security::getClientIp();
        if (!Security::checkRateLimit("login_{$ip}", 5, 15)) {
            self::logLogin(null, $email, 'email_password', 'blocked', 'Rate limit exceeded');
            return null;
        }
        
        try {
            $db = Database::getInstance();
            $user = $db->fetch(
                "SELECT * FROM users WHERE email = ? AND status = 'active'",
                [$email]
            );
            
            if (!$user || !Security::verifyPassword($password, $user['password'])) {
                Security::logFailedLogin($email, $ip);
                self::logLogin($user['id'] ?? null, $email, 'email_password', 'failed', 'Invalid credentials');
                return null;
            }
            
            // Check email verification if required
            if (defined('REQUIRE_EMAIL_VERIFICATION') && REQUIRE_EMAIL_VERIFICATION) {
                if (!$user['email_verified_at']) {
                    self::logLogin($user['id'], $email, 'email_password', 'failed', 'Email not verified');
                    return null;
                }
            }
            
            // Clear rate limit on successful authentication
            Security::clearRateLimit("login_{$ip}");
            
            return $user;
            
        } catch (\Exception $e) {
            Logger::error('Authentication error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Complete user login after authentication
     */
    public static function loginUser(int $userId, bool $remember = false): bool
    {
        try {
            $db = Database::getInstance();
            $user = $db->fetch("SELECT * FROM users WHERE id = ? AND status = 'active'", [$userId]);
            
            if (!$user) {
                return false;
            }
            
            $ip = Security::getClientIp();
            
            // Set session
            self::setUserSession($user);
            $_SESSION['_login_time'] = time();

            // Check for existing active sessions (concurrent session detection)
            try {
                $activeSessions = $db->fetchAll(
                    "SELECT id FROM user_sessions WHERE user_id = ? AND is_active = 1 AND expires_at > NOW()",
                    [$userId]
                );
                if (count($activeSessions) > 0) {
                    $_SESSION['_concurrent_session_warning'] = count($activeSessions);
                    // Notify those existing sessions about this new login
                    $db->query(
                        "INSERT INTO settings (`key`, `value`, `type`, `updated_at`) 
                         VALUES (?, ?, 'json', NOW())
                         ON DUPLICATE KEY UPDATE `value` = ?, `updated_at` = NOW()",
                        [
                            'new_login_notify_' . $userId,
                            json_encode(['time' => time(), 'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown', 'ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 100)]),
                            json_encode(['time' => time(), 'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown', 'ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 100)])
                        ]
                    );
                }
            } catch (\Exception $e) {
                // non-fatal
            }

            // Track session
            SessionManager::track($user['id']);
            
            // Handle remember me
            if ($remember) {
                self::createRememberToken($user['id']);
            }
            
            // Log activity
            Logger::activity($user['id'], 'login', ['ip' => $ip]);
            
            // Log login history
            self::logLogin($user['id'], $user['email'], 'email_password', 'success');
            
            // Update last login
            $db->update('users', [
                'last_login_at' => date('Y-m-d H:i:s'),
                'last_login_ip' => $ip
            ], 'id = ?', [$user['id']]);
            
            return true;
            
        } catch (\Exception $e) {
            Logger::error('Login completion error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Register new user
     */
    public static function register(array $data): int|false
    {
        try {
            $db = Database::getInstance();
            
            // Check if email exists
            $existing = $db->fetch("SELECT id FROM users WHERE email = ?", [$data['email']]);
            if ($existing) {
                return false;
            }
            
            $name = $data['name'];
            $email = $data['email'];
            
            // Hash password
            $data['password'] = Security::hashPassword($data['password']);
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['status'] = 'active';
            $data['role'] = 'user';
            $data['user_unique_id'] = self::generateUuidV4();
            
            // Generate verification token
            $verificationToken = null;
            if (defined('REQUIRE_EMAIL_VERIFICATION') && REQUIRE_EMAIL_VERIFICATION) {
                $verificationToken = Security::generateToken();
                $data['email_verification_token'] = $verificationToken;
            }
            
            $userId = $db->insert('users', $data);
            
            // Create user profile
            $db->insert('user_profiles', [
                'user_id' => $userId,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Send verification email if required
            if ($verificationToken) {
                Mailer::sendVerificationEmail($email, $name, $verificationToken);
            } else {
                // Send welcome email
                Mailer::sendWelcomeEmail($email, $name);
            }
            
            // Log activity
            Logger::activity($userId, 'register', ['email' => $email]);
            
            return $userId;
            
        } catch (\Exception $e) {
            Logger::error('Registration error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Logout user
     */
    public static function logout(): void
    {
        if (self::check()) {
            Logger::activity(self::id(), 'logout');
        }
        
        // Terminate session tracking
        SessionManager::terminateSession('logout');
        
        // Clear remember token cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
            
            try {
                $db = Database::getInstance();
                $db->delete('user_remember_tokens', 'token = ?', [$_COOKIE['remember_token']]);
            } catch (\Exception $e) {
                // Ignore
            }
        }
        
        // Destroy session
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();
        
        self::$user = null;
    }

    
    /**
     * Check if user is logged in
     */
    public static function check(): bool
    {
        if (isset($_SESSION['user_id'])) {
            return true;
        }
        
        // Check remember token
        if (isset($_COOKIE['remember_token'])) {
            return self::loginWithRememberToken($_COOKIE['remember_token']);
        }
        
        return false;
    }
    
    /**
     * Check if user is guest
     */
    public static function guest(): bool
    {
        return !self::check();
    }
    
    /**
     * Get current user
     */
    public static function user(): ?array
    {
        if (!self::check()) {
            return null;
        }
        
        if (self::$user === null) {
            try {
                $db = Database::getInstance();
                self::$user = $db->fetch(
                    "SELECT u.*, up.avatar, up.bio, up.phone FROM users u 
                     LEFT JOIN user_profiles up ON u.id = up.user_id 
                     WHERE u.id = ?",
                    [$_SESSION['user_id']]
                );
            } catch (\Exception $e) {
                Logger::error('Error fetching user: ' . $e->getMessage());
                return null;
            }
        }
        
        return self::$user;
    }
    
    /**
     * Get user ID
     */
    public static function id(): ?int
    {
        return $_SESSION['user_id'] ?? null;
    }

    /**
     * Parse a potentially comma-separated role string into an array of slugs.
     * Supports both legacy single-role values ("admin") and multi-role
     * values stored as comma-separated slugs ("admin,editor_role").
     */
    public static function getRoles(?array $user = null): array
    {
        if ($user === null) {
            $user = self::user();
        }
        if (!$user || empty($user['role'])) {
            return ['user'];
        }
        return array_values(array_filter(array_map('trim', explode(',', $user['role']))));
    }

    /**
     * Check user role
     */
    public static function hasRole(string $role): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }

        $roles = self::getRoles($user);

        // Super admin has all roles
        if (in_array('super_admin', $roles, true)) {
            return true;
        }

        // Admin has project_admin, audit_viewer and user roles
        if (in_array('admin', $roles, true) && in_array($role, ['admin', 'project_admin', 'audit_viewer', 'user'], true)) {
            return true;
        }

        return in_array($role, $roles, true);
    }
    
    /**
     * Check if user is admin
     */
    public static function isAdmin(): bool
    {
        return self::hasRole('admin') || self::hasRole('super_admin');
    }

    /**
     * Check if user can access the Audit Explorer
     * (super_admin, admin, or audit_viewer role)
     */
    public static function canAccessAudit(): bool
    {
        return self::isAdmin() || self::hasRole('audit_viewer') || self::hasPermission('audit');
    }

    /**
     * Check if the current user has a specific granular admin permission.
     *
     * Returns true for super_admin / admin (implicit full access) or when:
     *  1. the user has an explicit row in admin_user_permissions for $key, OR
     *  2. the user's role has $key in user_role_permissions.
     *
     * Individual user permissions always take precedence (override) over
     * role-level defaults.
     */
    public static function hasPermission(string $key): bool
    {
        if (self::isAdmin()) {
            return true;
        }

        $userId = self::id();
        if (!$userId) {
            return false;
        }

        try {
            $db = Database::getInstance();

            // 1. Explicit per-user permission
            if ($db->fetch(
                "SELECT id FROM admin_user_permissions WHERE user_id = ? AND permission_key = ?",
                [$userId, $key]
            ) !== null) {
                return true;
            }

            // 2. Role-based permission — check ALL roles the user holds
            $user = self::user();
            if ($user) {
                foreach (self::getRoles($user) as $slug) {
                    $roleRow = $db->fetch(
                        "SELECT r.id FROM user_roles r WHERE r.slug = ? AND r.status = 'active'",
                        [$slug]
                    );
                    if ($roleRow && $db->fetch(
                        "SELECT id FROM user_role_permissions WHERE role_id = ? AND permission_key = ?",
                        [$roleRow['id'], $key]
                    ) !== null) {
                        return true;
                    }
                }
            }
        } catch (\Exception $e) {
            // user_roles / user_role_permissions may not exist yet
        }

        return false;
    }

    /**
     * Check if the current user has any permission whose key equals $prefix
     * OR starts with "$prefix." (i.e. any permission in that group).
     *
     * Used by the admin sidebar to decide whether to show a whole section.
     * Super_admin / admin always return true.
     */
    public static function hasPermissionGroup(string $prefix): bool
    {
        if (self::isAdmin()) {
            return true;
        }

        $userId = self::id();
        if (!$userId) {
            return false;
        }

        try {
            $db = Database::getInstance();

            // 1. Direct user permissions
            if ((int) $db->fetchColumn(
                "SELECT COUNT(*) FROM admin_user_permissions
                 WHERE user_id = ? AND (permission_key = ? OR permission_key LIKE ?)",
                [$userId, $prefix, $prefix . '.%']
            ) > 0) {
                return true;
            }

            // 2. Role-based permissions — check ALL roles the user holds
            $user = self::user();
            if ($user) {
                foreach (self::getRoles($user) as $slug) {
                    $roleRow = $db->fetch(
                        "SELECT r.id FROM user_roles r WHERE r.slug = ? AND r.status = 'active'",
                        [$slug]
                    );
                    if ($roleRow && (int) $db->fetchColumn(
                        "SELECT COUNT(*) FROM user_role_permissions
                         WHERE role_id = ? AND (permission_key = ? OR permission_key LIKE ?)",
                        [$roleRow['id'], $prefix, $prefix . '.%']
                    ) > 0) {
                        return true;
                    }
                }
            }
        } catch (\Exception $e) {
            // Tables may not exist yet
        }

        return false;
    }

    /**
     * Check if the current user has at least one entry in admin_user_permissions
     * OR has at least one permission granted via their role.
     *
     * Used as a gateway check: super_admin / admin always return true;
     * non-admin users are allowed through only when an administrator has
     * explicitly granted them at least one admin-panel permission.
     */
    public static function hasAnyAdminPermission(): bool
    {
        if (self::isAdmin()) {
            return true;
        }

        $userId = self::id();
        if (!$userId) {
            return false;
        }

        try {
            $db = Database::getInstance();

            // 1. Direct user permissions
            if ((int) $db->fetchColumn(
                "SELECT COUNT(*) FROM admin_user_permissions WHERE user_id = ?",
                [$userId]
            ) > 0) {
                return true;
            }

            // 2. Role-based permissions — check ALL roles the user holds
            $user = self::user();
            if ($user) {
                foreach (self::getRoles($user) as $slug) {
                    $roleRow = $db->fetch(
                        "SELECT r.id FROM user_roles r WHERE r.slug = ? AND r.status = 'active'",
                        [$slug]
                    );
                    if ($roleRow && (int) $db->fetchColumn(
                        "SELECT COUNT(*) FROM user_role_permissions WHERE role_id = ?",
                        [$roleRow['id']]
                    ) > 0) {
                        return true;
                    }
                }
            }
        } catch (\Exception $e) {
            // Tables may not exist yet
        }

        return false;
    }
    
    /**
     * Set user session
     */
    private static function setUserSession(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_unique_id'] = $user['user_unique_id'] ?? null;
        $_SESSION['_created'] = time();
    }

    /**
     * Generate a Version 4 (random) UUID string.
     *
     * The returned string is in the standard 8-4-4-4-12 hexadecimal format, e.g.:
     *   "550e8400-e29b-41d4-a716-446655440000"
     *
     * Version and variant bits are set in accordance with RFC 4122 §4.4:
     *   - Bits 12-15 of time_hi_and_version are set to 0100 (version 4)
     *   - Bits 6-7 of clock_seq_hi_and_reserved are set to 10 (variant 1)
     *
     * Uses a single random_bytes(16) call for efficiency (one OS call).
     *
     * @return string UUID v4 string (36 characters including hyphens)
     */
    public static function generateUuidV4(): string
    {
        $bytes = random_bytes(16);

        // Set version bits (4) in byte 6
        $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40);
        // Set variant bits (RFC 4122) in byte 8
        $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);

        return sprintf(
            '%08s-%04s-%04s-%04s-%12s',
            bin2hex(substr($bytes, 0, 4)),
            bin2hex(substr($bytes, 4, 2)),
            bin2hex(substr($bytes, 6, 2)),
            bin2hex(substr($bytes, 8, 2)),
            bin2hex(substr($bytes, 10, 6))
        );
    }
    
    /**
     * Create remember token
     */
    private static function createRememberToken(int $userId): void
    {
        $token = Security::generateToken();
        $hashedToken = hash('sha256', $token);
        
        try {
            $db = Database::getInstance();
            
            // Store device info
            $device = [
                'browser' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
                'ip' => Security::getClientIp()
            ];
            
            $db->insert('user_remember_tokens', [
                'user_id' => $userId,
                'token' => $hashedToken,
                'device_info' => json_encode($device),
                'expires_at' => date('Y-m-d H:i:s', time() + (30 * 24 * 60 * 60)),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Set cookie
            setcookie(
                'remember_token',
                $token,
                time() + (30 * 24 * 60 * 60),
                '/',
                '',
                true,
                true
            );
            
        } catch (\Exception $e) {
            Logger::error('Error creating remember token: ' . $e->getMessage());
        }
    }
    
    /**
     * Login with remember token
     */
    private static function loginWithRememberToken(string $token): bool
    {
        $hashedToken = hash('sha256', $token);
        
        try {
            $db = Database::getInstance();
            $tokenData = $db->fetch(
                "SELECT * FROM user_remember_tokens WHERE token = ? AND expires_at > NOW()",
                [$hashedToken]
            );
            
            if (!$tokenData) {
                setcookie('remember_token', '', time() - 3600, '/', '', true, true);
                return false;
            }
            
            $user = $db->fetch(
                "SELECT * FROM users WHERE id = ? AND status = 'active'",
                [$tokenData['user_id']]
            );
            
            if (!$user) {
                return false;
            }
            
            self::setUserSession($user);
            
            // Rotate token for security
            self::createRememberToken($user['id']);
            $db->delete('user_remember_tokens', 'id = ?', [$tokenData['id']]);
            
            return true;
            
        } catch (\Exception $e) {
            Logger::error('Remember token login error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Send password reset email
     */
    public static function sendPasswordReset(string $email): bool
    {
        try {
            $db = Database::getInstance();
            $user = $db->fetch("SELECT id, name FROM users WHERE email = ?", [$email]);
            
            if (!$user) {
                return true; // Don't reveal if email exists
            }
            
            $token = Security::generateToken();
            
            // Delete old tokens
            $db->delete('password_resets', 'email = ?', [$email]);
            
            // Create new reset
            $db->insert('password_resets', [
                'email'      => $email,
                'token'      => hash('sha256', $token),
                'created_at' => date('Y-m-d H:i:s'),
                'visited_at' => null,
            ]);
            
            // Send password reset email immediately (not queued — user needs the link now)
            try {
                $sent = MailService::sendNotification($email, 'password_reset', [
                    'name'      => $user['name'],
                    'reset_url' => APP_URL . '/reset-password/' . $token,
                ], false); // false = bypass queue, send synchronously
                if (!$sent) {
                    Mailer::sendPasswordResetEmail($email, $user['name'], $token);
                }
            } catch (\Exception $me) {
                Mailer::sendPasswordResetEmail($email, $user['name'], $token);
            }
            
            Logger::info("Password reset requested for {$email}");
            
            return true;
            
        } catch (\Exception $e) {
            Logger::error('Password reset error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Reset password with token
     */
    public static function resetPassword(string $token, string $password): bool
    {
        try {
            $db = Database::getInstance();
            $hashedToken = hash('sha256', $token);
            
            $reset = $db->fetch(
                "SELECT * FROM password_resets WHERE token = ? AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)",
                [$hashedToken]
            );
            
            if (!$reset) {
                return false;
            }
            
            $user = $db->fetch("SELECT id FROM users WHERE email = ?", [$reset['email']]);
            if (!$user) {
                return false;
            }
            
            // Update password
            $db->update('users', [
                'password' => Security::hashPassword($password)
            ], 'id = ?', [$user['id']]);
            
            // Delete reset token
            $db->delete('password_resets', 'email = ?', [$reset['email']]);
            
            // Invalidate all remember tokens
            $db->delete('user_remember_tokens', 'user_id = ?', [$user['id']]);
            
            Logger::activity($user['id'], 'password_reset');
            
            return true;
            
        } catch (\Exception $e) {
            Logger::error('Password reset error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Verify email
     */
    public static function verifyEmail(string $token): bool
    {
        try {
            $db = Database::getInstance();
            $user = $db->fetch(
                "SELECT id FROM users WHERE email_verification_token = ?",
                [$token]
            );
            
            if (!$user) {
                return false;
            }
            
            $db->update('users', [
                'email_verified_at' => date('Y-m-d H:i:s'),
                'email_verification_token' => null
            ], 'id = ?', [$user['id']]);
            
            Logger::activity($user['id'], 'email_verified');
            
            return true;
            
        } catch (\Exception $e) {
            Logger::error('Email verification error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Log login attempt to database
     */
    private static function logLogin(?int $userId, string $email, string $method, string $status, ?string $failureReason = null): void
    {
        try {
            $db = Database::getInstance();
            $db->insert('login_history', [
                'user_id' => $userId,
                'email' => $email,
                'login_method' => $method,
                'ip_address' => Security::getClientIp(),
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                'status' => $status,
                'failure_reason' => $failureReason,
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            Logger::error('Log login error: ' . $e->getMessage());
        }
    }
}
