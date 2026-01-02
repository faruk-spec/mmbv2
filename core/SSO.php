<?php
/**
 * SSO (Single Sign-On) System
 * 
 * @package MMB\Core
 */

namespace Core;

class SSO
{
    private static ?string $secretKey = null;
    
    /**
     * Initialize SSO
     */
    public static function init(): void
    {
        self::$secretKey = defined('SSO_SECRET_KEY') ? SSO_SECRET_KEY : 'default_sso_key';
    }
    
    /**
     * Generate SSO token for user
     */
    public static function generateToken(int $userId): string
    {
        self::init();
        
        $payload = [
            'user_id' => $userId,
            'issued_at' => time(),
            'expires_at' => time() + 3600, // 1 hour
            'fingerprint' => Security::generateSessionFingerprint()
        ];
        
        $encodedPayload = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', $encodedPayload, self::$secretKey);
        
        return $encodedPayload . '.' . $signature;
    }
    
    /**
     * Validate SSO token
     */
    public static function validateToken(string $token): array|false
    {
        self::init();
        
        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            return false;
        }
        
        [$encodedPayload, $signature] = $parts;
        
        // Verify signature
        $expectedSignature = hash_hmac('sha256', $encodedPayload, self::$secretKey);
        if (!hash_equals($expectedSignature, $signature)) {
            Logger::warning('Invalid SSO token signature');
            return false;
        }
        
        // Decode payload
        $payload = json_decode(base64_decode($encodedPayload), true);
        if (!$payload) {
            return false;
        }
        
        // Check expiration
        if ($payload['expires_at'] < time()) {
            Logger::info('SSO token expired');
            return false;
        }
        
        // Verify fingerprint
        if ($payload['fingerprint'] !== Security::generateSessionFingerprint()) {
            Logger::warning('SSO token fingerprint mismatch');
            return false;
        }
        
        return $payload;
    }
    
    /**
     * Get user from SSO token
     */
    public static function getUserFromToken(string $token): ?array
    {
        $payload = self::validateToken($token);
        if (!$payload) {
            return null;
        }
        
        try {
            $db = Database::getInstance();
            return $db->fetch(
                "SELECT id, name, email, role, status FROM users WHERE id = ? AND status = 'active'",
                [$payload['user_id']]
            );
        } catch (\Exception $e) {
            Logger::error('SSO user lookup error: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Store SSO token in session
     */
    public static function storeToken(string $token): void
    {
        $_SESSION['sso_token'] = $token;
    }
    
    /**
     * Get stored SSO token
     */
    public static function getStoredToken(): ?string
    {
        return $_SESSION['sso_token'] ?? null;
    }
    
    /**
     * Clear SSO token
     */
    public static function clearToken(): void
    {
        unset($_SESSION['sso_token']);
    }
    
    /**
     * Validate request for project
     */
    public static function validateProjectRequest(string $projectName): bool
    {
        $userId = null;
        $isAuthenticated = false;
        
        // Check if user is authenticated via Auth system (from middleware)
        if (Auth::check()) {
            $userId = Auth::id();
            $isAuthenticated = true;
        } else {
            // If not authenticated, try SSO token
            $token = $_COOKIE['sso_token'] ?? $_SERVER['HTTP_X_SSO_TOKEN'] ?? null;
            
            if ($token) {
                $user = self::getUserFromToken($token);
                if ($user) {
                    // Set up session for this user
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    $userId = $user['id'];
                    $isAuthenticated = true;
                }
            }
        }
        
        // If not authenticated at all, return false (will redirect to login)
        if (!$isAuthenticated) {
            return false;
        }
        
        // Check project access permissions
        $hasAccess = self::hasProjectAccess($userId, $projectName);
        
        // If authenticated but no access, show access denied instead of redirect loop
        if (!$hasAccess) {
            self::showAccessDenied($projectName);
            exit;
        }
        
        return true;
    }
    
    /**
     * Check if user has access to project
     */
    public static function hasProjectAccess(int $userId, string $projectName): bool
    {
        try {
            $db = Database::getInstance();
            $user = $db->fetch("SELECT role FROM users WHERE id = ?", [$userId]);
            
            if (!$user) {
                return false;
            }
            
            // Check if project is enabled using the Helpers method (checks database first, then config)
            if (!Helpers::isProjectEnabled($projectName)) {
                return false;
            }
            
            // Admins have access to all projects
            if (in_array($user['role'], ['super_admin', 'admin'])) {
                return true;
            }
            
            // Check for explicit deny in project_permissions table
            $permission = $db->fetch(
                "SELECT has_access FROM project_permissions WHERE user_id = ? AND project_name = ?",
                [$userId, $projectName]
            );
            
            // If explicit permission exists and it's denied, deny access
            if ($permission !== null && !$permission['has_access']) {
                return false;
            }
            
            // All authenticated users have access to projects by default
            // unless explicitly denied in project_permissions table
            return true;
            
        } catch (\Exception $e) {
            Logger::error('Project access check error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Generate SSO login URL for project
     */
    public static function getLoginUrl(string $returnUrl = ''): string
    {
        $baseUrl = defined('APP_URL') ? APP_URL : '';
        $params = $returnUrl ? '?return=' . urlencode($returnUrl) : '';
        return $baseUrl . '/login' . $params;
    }
    
    /**
     * Handle SSO callback from main site
     */
    public static function handleCallback(): bool
    {
        $token = $_GET['sso_token'] ?? null;
        
        if (!$token) {
            return false;
        }
        
        $user = self::getUserFromToken($token);
        if (!$user) {
            return false;
        }
        
        // Set up session
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['sso_token'] = $token;
        
        // Set cookie for cross-project access
        setcookie('sso_token', $token, time() + 3600, '/', '', true, true);
        
        return true;
    }
    
    /**
     * Redirect to main login
     */
    public static function redirectToLogin(string $returnUrl = ''): void
    {
        header('Location: ' . self::getLoginUrl($returnUrl));
        exit;
    }
    
    /**
     * Show access denied page for authenticated users without project access
     */
    private static function showAccessDenied(string $projectName): void
    {
        http_response_code(403);
        $user = Auth::user();
        
        // Render access denied view
        View::render('errors/project-access-denied', [
            'project' => $projectName,
            'user' => $user
        ]);
    }
}
