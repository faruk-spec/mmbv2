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
        // Rate limiting
        $ip = Security::getClientIp();
        if (!Security::checkRateLimit("login_{$ip}", 5, 15)) {
            self::logLogin(null, $email, 'email_password', 'blocked', 'Rate limit exceeded');
            return false;
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
                return false;
            }
            
            // Check email verification if required
            if (defined('REQUIRE_EMAIL_VERIFICATION') && REQUIRE_EMAIL_VERIFICATION) {
                if (!$user['email_verified_at']) {
                    self::logLogin($user['id'], $email, 'email_password', 'failed', 'Email not verified');
                    return false;
                }
            }
            
            // Clear rate limit on successful login
            Security::clearRateLimit("login_{$ip}");
            
            // Set session
            self::setUserSession($user);
            
            // Track session
            SessionManager::track($user['id']);
            
            // Handle remember me
            if ($remember) {
                self::createRememberToken($user['id']);
            }
            
            // Log activity
            Logger::activity($user['id'], 'login', ['ip' => $ip]);
            
            // Log login history
            self::logLogin($user['id'], $email, 'email_password', 'success');
            
            // Update last login
            $db->update('users', [
                'last_login_at' => date('Y-m-d H:i:s'),
                'last_login_ip' => $ip
            ], 'id = ?', [$user['id']]);
            
            return true;
            
        } catch (\Exception $e) {
            Logger::error('Login error: ' . $e->getMessage());
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
     * Check user role
     */
    public static function hasRole(string $role): bool
    {
        $user = self::user();
        if (!$user) {
            return false;
        }
        
        // Super admin has all roles
        if ($user['role'] === 'super_admin') {
            return true;
        }
        
        // Admin has project_admin and user roles
        if ($user['role'] === 'admin' && in_array($role, ['admin', 'project_admin', 'user'])) {
            return true;
        }
        
        return $user['role'] === $role;
    }
    
    /**
     * Check if user is admin
     */
    public static function isAdmin(): bool
    {
        return self::hasRole('admin') || self::hasRole('super_admin');
    }
    
    /**
     * Set user session
     */
    private static function setUserSession(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['_created'] = time();
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
                'email' => $email,
                'token' => hash('sha256', $token),
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Send password reset email
            Mailer::sendPasswordResetEmail($email, $user['name'], $token);
            
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
                "SELECT * FROM password_resets WHERE token = ? AND created_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)",
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
