<?php
/**
 * Google OAuth 2.0 Integration
 * 
 * @package MMB\Core
 */

namespace Core;

class GoogleOAuth
{
    private static ?array $config = null;
    
    /**
     * Initialize Google OAuth configuration
     */
    private static function init(): void
    {
        if (self::$config !== null) {
            return;
        }
        
        try {
            $db = Database::getInstance();
            $provider = $db->fetch(
                "SELECT * FROM oauth_providers WHERE name = 'google' AND is_enabled = 1",
                []
            );
            
            if (!$provider) {
                self::$config = [];
                return;
            }
            
            $config = json_decode($provider['config'], true);
            
            self::$config = [
                'client_id' => $provider['client_id'],
                'client_secret' => $provider['client_secret'],
                'redirect_uri' => $provider['redirect_uri'] ?? (APP_URL . '/auth/google/callback'),
                'auth_url' => $config['auth_url'] ?? 'https://accounts.google.com/o/oauth2/v2/auth',
                'token_url' => $config['token_url'] ?? 'https://oauth2.googleapis.com/token',
                'userinfo_url' => $config['userinfo_url'] ?? 'https://www.googleapis.com/oauth2/v2/userinfo',
                'scopes' => explode(' ', $provider['scopes'] ?? 'openid email profile'),
                'provider_id' => $provider['id']
            ];
        } catch (\Exception $e) {
            Logger::error('Google OAuth init error: ' . $e->getMessage());
            self::$config = [];
        }
    }
    
    /**
     * Check if Google OAuth is enabled
     */
    public static function isEnabled(): bool
    {
        self::init();
        return !empty(self::$config) && !empty(self::$config['client_id']);
    }
    
    /**
     * Get authorization URL for Google
     */
    public static function getAuthUrl(?string $returnUrl = null): string
    {
        self::init();
        
        if (!self::isEnabled()) {
            return '/login';
        }
        
        $state = Security::generateToken();
        $_SESSION['oauth_state'] = $state;
        
        if ($returnUrl) {
            $_SESSION['oauth_return_url'] = $returnUrl;
        }
        
        $params = [
            'client_id' => self::$config['client_id'],
            'redirect_uri' => self::$config['redirect_uri'],
            'response_type' => 'code',
            'scope' => implode(' ', self::$config['scopes']),
            'state' => $state,
            'access_type' => 'offline',
            'prompt' => 'consent'
        ];
        
        return self::$config['auth_url'] . '?' . http_build_query($params);
    }
    
    /**
     * Handle OAuth callback and exchange code for token
     */
    public static function handleCallback(string $code, string $state): array|false
    {
        self::init();
        
        if (!self::isEnabled()) {
            return false;
        }
        
        // Verify state to prevent CSRF
        if (!isset($_SESSION['oauth_state']) || $state !== $_SESSION['oauth_state']) {
            Logger::warning('OAuth state mismatch');
            return false;
        }
        
        unset($_SESSION['oauth_state']);
        
        try {
            // Exchange code for access token
            $tokenData = self::exchangeCodeForToken($code);
            
            if (!$tokenData) {
                return false;
            }
            
            // Get user info from Google
            $userInfo = self::getUserInfo($tokenData['access_token']);
            
            if (!$userInfo) {
                return false;
            }
            
            return [
                'provider_user_id' => $userInfo['id'],
                'email' => $userInfo['email'],
                'name' => $userInfo['name'] ?? '',
                'avatar' => $userInfo['picture'] ?? null,
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'] ?? null,
                'expires_in' => $tokenData['expires_in'] ?? 3600,
                'email_verified' => $userInfo['verified_email'] ?? false
            ];
            
        } catch (\Exception $e) {
            Logger::error('Google OAuth callback error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Exchange authorization code for access token
     */
    private static function exchangeCodeForToken(string $code): array|false
    {
        $data = [
            'code' => $code,
            'client_id' => self::$config['client_id'],
            'client_secret' => self::$config['client_secret'],
            'redirect_uri' => self::$config['redirect_uri'],
            'grant_type' => 'authorization_code'
        ];
        
        $response = self::makeRequest(self::$config['token_url'], 'POST', $data);
        
        return $response;
    }
    
    /**
     * Get user info from Google
     */
    private static function getUserInfo(string $accessToken): array|false
    {
        $response = self::makeRequest(
            self::$config['userinfo_url'],
            'GET',
            [],
            ['Authorization: Bearer ' . $accessToken]
        );
        
        return $response;
    }
    
    /**
     * Make HTTP request
     */
    private static function makeRequest(string $url, string $method = 'GET', array $data = [], array $headers = []): array|false
    {
        $ch = curl_init();
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        } elseif ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }
        
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
            'Accept: application/json',
            'Content-Type: application/x-www-form-urlencoded'
        ], $headers));
        
        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        if ($error) {
            Logger::error('cURL error: ' . $error);
            return false;
        }
        
        if ($statusCode >= 400) {
            Logger::error('OAuth API error: ' . $response);
            return false;
        }
        
        return json_decode($response, true) ?: false;
    }
    
    /**
     * Link Google account to existing user
     */
    public static function linkAccount(int $userId, array $oauthData): bool
    {
        self::init();
        
        if (!self::isEnabled()) {
            return false;
        }
        
        try {
            $db = Database::getInstance();
            
            // Check if this Google account is already linked
            $existing = $db->fetch(
                "SELECT user_id FROM oauth_user_connections WHERE provider_id = ? AND provider_user_id = ?",
                [self::$config['provider_id'], $oauthData['provider_user_id']]
            );
            
            if ($existing && $existing['user_id'] != $userId) {
                return false; // Already linked to another account
            }
            
            $expiresAt = $oauthData['expires_in'] ? date('Y-m-d H:i:s', time() + $oauthData['expires_in']) : null;
            
            if ($existing) {
                // Update existing connection
                $db->update('oauth_user_connections', [
                    'provider_email' => $oauthData['email'],
                    'provider_name' => $oauthData['name'],
                    'provider_avatar' => $oauthData['avatar'],
                    'access_token' => $oauthData['access_token'],
                    'refresh_token' => $oauthData['refresh_token'],
                    'token_expires_at' => $expiresAt,
                    'last_used_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ], 'id = ?', [$existing['id']]);
            } else {
                // Create new connection
                $db->insert('oauth_user_connections', [
                    'user_id' => $userId,
                    'provider_id' => self::$config['provider_id'],
                    'provider_user_id' => $oauthData['provider_user_id'],
                    'provider_email' => $oauthData['email'],
                    'provider_name' => $oauthData['name'],
                    'provider_avatar' => $oauthData['avatar'],
                    'access_token' => $oauthData['access_token'],
                    'refresh_token' => $oauthData['refresh_token'],
                    'token_expires_at' => $expiresAt,
                    'last_used_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s')
                ]);
                
                // Update user's google_id
                $db->update('users', [
                    'google_id' => $oauthData['provider_user_id']
                ], 'id = ?', [$userId]);
            }
            
            return true;
            
        } catch (\Exception $e) {
            Logger::error('Link Google account error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Find or create user from Google OAuth data
     */
    public static function findOrCreateUser(array $oauthData): int|false
    {
        self::init();
        
        if (!self::isEnabled()) {
            return false;
        }
        
        try {
            $db = Database::getInstance();
            
            // Check if OAuth connection exists
            $connection = $db->fetch(
                "SELECT user_id FROM oauth_user_connections WHERE provider_id = ? AND provider_user_id = ?",
                [self::$config['provider_id'], $oauthData['provider_user_id']]
            );
            
            if ($connection) {
                // Update connection info
                self::linkAccount($connection['user_id'], $oauthData);
                return $connection['user_id'];
            }
            
            // Check if user exists by email
            $user = $db->fetch("SELECT id FROM users WHERE email = ?", [$oauthData['email']]);
            
            if ($user) {
                // Link Google account to existing user
                self::linkAccount($user['id'], $oauthData);
                return $user['id'];
            }
            
            // Create new user
            $userId = $db->insert('users', [
                'name' => Security::sanitize($oauthData['name']),
                'email' => $oauthData['email'],
                'password' => Security::hashPassword(bin2hex(random_bytes(16))), // Random password
                'google_id' => $oauthData['provider_user_id'],
                'email_verified_at' => $oauthData['email_verified'] ? date('Y-m-d H:i:s') : null,
                'status' => 'active',
                'role' => 'user',
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Create user profile
            $db->insert('user_profiles', [
                'user_id' => $userId,
                'avatar' => $oauthData['avatar'],
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            // Link Google account
            self::linkAccount($userId, $oauthData);
            
            Logger::activity($userId, 'registration', [
                'method' => 'google_oauth',
                'email' => $oauthData['email']
            ]);
            
            return $userId;
            
        } catch (\Exception $e) {
            Logger::error('Find or create user error: ' . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Revoke Google OAuth connection for user
     */
    public static function revokeConnection(int $userId): bool
    {
        self::init();
        
        try {
            $db = Database::getInstance();
            
            $db->delete('oauth_user_connections', 'user_id = ? AND provider_id = ?', [$userId, self::$config['provider_id']]);
            
            $db->update('users', [
                'google_id' => null
            ], 'id = ?', [$userId]);
            
            Logger::activity($userId, 'google_oauth_revoked');
            
            return true;
            
        } catch (\Exception $e) {
            Logger::error('Revoke Google connection error: ' . $e->getMessage());
            return false;
        }
    }
}
