<?php
/**
 * Generic OAuth 2.0 Provider Integration
 *
 * @package MMB\Core
 */

namespace Core;

class OAuthProvider
{
    private const SUPPORTED_PROVIDERS = ['google', 'github', 'apple'];

    private const DEFAULT_CONFIG = [
        'google' => [
            'auth_url' => 'https://accounts.google.com/o/oauth2/v2/auth',
            'token_url' => 'https://oauth2.googleapis.com/token',
            'userinfo_url' => 'https://www.googleapis.com/oauth2/v2/userinfo',
            'scopes' => 'openid email profile'
        ],
        'github' => [
            'auth_url' => 'https://github.com/login/oauth/authorize',
            'token_url' => 'https://github.com/login/oauth/access_token',
            'userinfo_url' => 'https://api.github.com/user',
            'userinfo_email_url' => 'https://api.github.com/user/emails',
            'scopes' => 'read:user user:email'
        ],
        'apple' => [
            'auth_url' => 'https://appleid.apple.com/auth/authorize',
            'token_url' => 'https://appleid.apple.com/auth/token',
            'userinfo_url' => '',
            'scopes' => 'name email'
        ]
    ];

    private static array $config = [];
    private static array $usersTableColumns = [];

    private static function normalizeProvider(string $provider): string
    {
        return strtolower(trim($provider));
    }

    public static function isSupportedProvider(string $provider): bool
    {
        return in_array(self::normalizeProvider($provider), self::SUPPORTED_PROVIDERS, true);
    }

    private static function init(string $provider): void
    {
        $provider = self::normalizeProvider($provider);

        if (isset(self::$config[$provider])) {
            return;
        }

        if (!self::isSupportedProvider($provider)) {
            self::$config[$provider] = [];
            return;
        }

        try {
            $db = Database::getInstance();
            $providerData = $db->fetch(
                "SELECT * FROM oauth_providers WHERE name = ? AND is_enabled = 1",
                [$provider]
            );

            if (!$providerData) {
                self::$config[$provider] = [];
                return;
            }

            $providerConfig = json_decode($providerData['config'] ?? '{}', true) ?: [];
            $defaults = self::DEFAULT_CONFIG[$provider] ?? [];

            self::$config[$provider] = [
                'provider' => $provider,
                'display_name' => $providerData['display_name'] ?: ucfirst($provider),
                'client_id' => $providerData['client_id'],
                'client_secret' => $providerData['client_secret'],
                'redirect_uri' => $providerData['redirect_uri'] ?: (APP_URL . '/auth/' . $provider . '/callback'),
                'auth_url' => $providerConfig['auth_url'] ?? ($defaults['auth_url'] ?? ''),
                'token_url' => $providerConfig['token_url'] ?? ($defaults['token_url'] ?? ''),
                'userinfo_url' => $providerConfig['userinfo_url'] ?? ($defaults['userinfo_url'] ?? ''),
                'userinfo_email_url' => $providerConfig['userinfo_email_url'] ?? ($defaults['userinfo_email_url'] ?? ''),
                'scopes' => explode(' ', trim($providerData['scopes'] ?: ($defaults['scopes'] ?? 'openid email profile'))),
                'provider_id' => $providerData['id']
            ];
        } catch (\Exception $e) {
            Logger::error('OAuth init error (' . $provider . '): ' . $e->getMessage());
            self::$config[$provider] = [];
        }
    }

    public static function isEnabled(string $provider): bool
    {
        self::init($provider);
        $provider = self::normalizeProvider($provider);
        return !empty(self::$config[$provider]) && !empty(self::$config[$provider]['client_id']);
    }

    public static function getEnabledProviders(): array
    {
        try {
            $db = Database::getInstance();
            return $db->fetchAll(
                "SELECT name, display_name FROM oauth_providers WHERE is_enabled = 1 AND name IN ('google', 'github', 'apple') ORDER BY FIELD(name, 'google', 'github', 'apple')",
                []
            );
        } catch (\Exception $e) {
            Logger::error('Failed to load enabled OAuth providers: ' . $e->getMessage());
            return [];
        }
    }

    private static function sanitizeReturnPath(?string $returnUrl): ?string
    {
        if ($returnUrl === null || $returnUrl === '') {
            return null;
        }

        $returnUrl = trim($returnUrl);
        if ($returnUrl === '' || str_starts_with($returnUrl, '//') || str_contains($returnUrl, '\\')) {
            return null;
        }

        $parts = parse_url($returnUrl);
        if ($parts === false) {
            return null;
        }

        if (!empty($parts['scheme']) || !empty($parts['host'])) {
            return null;
        }

        if (!str_starts_with($returnUrl, '/')) {
            return null;
        }

        return $returnUrl;
    }

    public static function getAuthUrl(string $provider, ?string $returnUrl = null): string
    {
        self::init($provider);
        $provider = self::normalizeProvider($provider);

        if (!self::isEnabled($provider)) {
            return '/login';
        }

        $state = Security::generateToken();
        $_SESSION['oauth_state_' . $provider] = $state;

        $safeReturnPath = self::sanitizeReturnPath($returnUrl);
        if ($safeReturnPath !== null) {
            $_SESSION['oauth_return_url_' . $provider] = $safeReturnPath;
        }

        $params = [
            'client_id' => self::$config[$provider]['client_id'],
            'redirect_uri' => self::$config[$provider]['redirect_uri'],
            'response_type' => 'code',
            'scope' => implode(' ', self::$config[$provider]['scopes']),
            'state' => $state
        ];

        if ($provider === 'google') {
            $params['access_type'] = 'offline';
            $params['prompt'] = 'consent';
        }

        return self::$config[$provider]['auth_url'] . '?' . http_build_query($params);
    }

    public static function getLoginMethodName(string $provider): string
    {
        return self::normalizeProvider($provider) . '_oauth';
    }

    public static function getDisplayName(string $provider): string
    {
        $provider = self::normalizeProvider($provider);
        self::init($provider);
        return self::$config[$provider]['display_name'] ?? ucfirst($provider);
    }

    public static function getProviderUserColumn(string $provider): string
    {
        return self::normalizeProvider($provider) . '_id';
    }

    private static function usersColumnExists(string $column): bool
    {
        if (array_key_exists($column, self::$usersTableColumns)) {
            return self::$usersTableColumns[$column];
        }

        try {
            $db = Database::getInstance();
            $row = $db->fetch("SHOW COLUMNS FROM users LIKE ?", [$column]);
            self::$usersTableColumns[$column] = (bool) $row;
        } catch (\Exception $e) {
            self::$usersTableColumns[$column] = false;
        }

        return self::$usersTableColumns[$column];
    }

    public static function handleCallback(string $provider, string $code, string $state): array|false
    {
        self::init($provider);
        $provider = self::normalizeProvider($provider);

        if (!self::isEnabled($provider)) {
            return false;
        }

        $stateKey = 'oauth_state_' . $provider;
        if (!isset($_SESSION[$stateKey]) || $state !== $_SESSION[$stateKey]) {
            Logger::warning('OAuth state mismatch for provider: ' . $provider);
            return false;
        }

        unset($_SESSION[$stateKey]);

        try {
            $tokenData = self::exchangeCodeForToken($provider, $code);
            if (!$tokenData || empty($tokenData['access_token'])) {
                return false;
            }

            $userInfo = self::getUserInfo($provider, $tokenData['access_token'], $tokenData['id_token'] ?? null);
            if (!$userInfo || empty($userInfo['provider_user_id'])) {
                return false;
            }

            if (empty($userInfo['email']) && $provider === 'apple') {
                Logger::warning('Apple OAuth callback did not include an email address.');
                return false;
            }

            return [
                'provider' => $provider,
                'provider_user_id' => (string) $userInfo['provider_user_id'],
                'email' => (string) ($userInfo['email'] ?? ''),
                'name' => (string) ($userInfo['name'] ?? ''),
                'avatar' => $userInfo['avatar'] ?? null,
                'access_token' => $tokenData['access_token'],
                'refresh_token' => $tokenData['refresh_token'] ?? null,
                'expires_in' => $tokenData['expires_in'] ?? 3600,
                'email_verified' => (bool) ($userInfo['email_verified'] ?? false)
            ];
        } catch (\Exception $e) {
            Logger::error('OAuth callback error (' . $provider . '): ' . $e->getMessage());
            return false;
        }
    }

    private static function exchangeCodeForToken(string $provider, string $code): array|false
    {
        $provider = self::normalizeProvider($provider);
        $data = [
            'code' => $code,
            'client_id' => self::$config[$provider]['client_id'],
            'client_secret' => self::$config[$provider]['client_secret'],
            'redirect_uri' => self::$config[$provider]['redirect_uri'],
            'grant_type' => 'authorization_code'
        ];

        $headers = [];
        if ($provider === 'github') {
            $headers[] = 'Accept: application/json';
        }

        return self::makeRequest(self::$config[$provider]['token_url'], 'POST', $data, $headers);
    }

    private static function getUserInfo(string $provider, string $accessToken, ?string $idToken = null): array|false
    {
        $provider = self::normalizeProvider($provider);

        if ($provider === 'google') {
            $response = self::makeRequest(
                self::$config[$provider]['userinfo_url'],
                'GET',
                [],
                ['Authorization: Bearer ' . $accessToken]
            );
            if (!$response) {
                return false;
            }
            return [
                'provider_user_id' => $response['id'] ?? null,
                'email' => $response['email'] ?? null,
                'name' => $response['name'] ?? '',
                'avatar' => $response['picture'] ?? null,
                'email_verified' => $response['verified_email'] ?? false
            ];
        }

        if ($provider === 'github') {
            $headers = [
                'Authorization: Bearer ' . $accessToken,
                'User-Agent: MyMultiBranch OAuth'
            ];
            $profile = self::makeRequest(self::$config[$provider]['userinfo_url'], 'GET', [], $headers);
            if (!$profile) {
                return false;
            }

            $email = $profile['email'] ?? null;
            $emailVerified = false;
            if (empty($email) && !empty(self::$config[$provider]['userinfo_email_url'])) {
                $emails = self::makeRequest(self::$config[$provider]['userinfo_email_url'], 'GET', [], $headers);
                if (is_array($emails)) {
                    foreach ($emails as $emailEntry) {
                        if (!empty($emailEntry['primary']) && !empty($emailEntry['email'])) {
                            $email = $emailEntry['email'];
                            $emailVerified = !empty($emailEntry['verified']);
                            break;
                        }
                    }
                }
            } else {
                $emailVerified = true;
            }

            return [
                'provider_user_id' => (string) ($profile['id'] ?? ''),
                'email' => $email,
                'name' => $profile['name'] ?: ($profile['login'] ?? ''),
                'avatar' => $profile['avatar_url'] ?? null,
                'email_verified' => $emailVerified
            ];
        }

        if ($provider === 'apple') {
            if (empty($idToken)) {
                return false;
            }

            $jwtParts = explode('.', $idToken);
            if (count($jwtParts) < 2) {
                return false;
            }

            $payload = json_decode(base64_decode(strtr($jwtParts[1], '-_', '+/')), true);
            if (!is_array($payload)) {
                return false;
            }

            return [
                'provider_user_id' => $payload['sub'] ?? null,
                'email' => $payload['email'] ?? null,
                'name' => $payload['email'] ?? 'Apple User',
                'avatar' => null,
                'email_verified' => !empty($payload['email_verified'])
            ];
        }

        return false;
    }

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
            Logger::error('OAuth cURL error: ' . $error);
            return false;
        }

        if ($statusCode >= 400) {
            Logger::error('OAuth API error: ' . $response);
            return false;
        }

        return json_decode($response, true) ?: false;
    }

    public static function linkAccount(string $provider, int $userId, array $oauthData): bool
    {
        self::init($provider);
        $provider = self::normalizeProvider($provider);

        if (!self::isEnabled($provider)) {
            return false;
        }

        try {
            $db = Database::getInstance();

            $existing = $db->fetch(
                "SELECT id, user_id FROM oauth_user_connections WHERE provider_id = ? AND provider_user_id = ?",
                [self::$config[$provider]['provider_id'], $oauthData['provider_user_id']]
            );

            if ($existing && $existing['user_id'] != $userId) {
                return false;
            }

            $expiresAt = $oauthData['expires_in'] ? date('Y-m-d H:i:s', time() + (int)$oauthData['expires_in']) : null;

            if ($existing) {
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
                $db->insert('oauth_user_connections', [
                    'user_id' => $userId,
                    'provider_id' => self::$config[$provider]['provider_id'],
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

                $providerColumn = self::getProviderUserColumn($provider);
                if (self::usersColumnExists($providerColumn)) {
                    $db->update('users', [
                        $providerColumn => $oauthData['provider_user_id']
                    ], 'id = ?', [$userId]);
                }
            }

            return true;
        } catch (\Exception $e) {
            Logger::error('OAuth link account error (' . $provider . '): ' . $e->getMessage());
            return false;
        }
    }

    public static function findOrCreateUser(string $provider, array $oauthData): int|false
    {
        self::init($provider);
        $provider = self::normalizeProvider($provider);

        if (!self::isEnabled($provider)) {
            return false;
        }

        if (empty($oauthData['email'])) {
            Logger::warning('OAuth email missing for provider: ' . $provider);
            return false;
        }

        try {
            $db = Database::getInstance();

            $connection = $db->fetch(
                "SELECT user_id FROM oauth_user_connections WHERE provider_id = ? AND provider_user_id = ?",
                [self::$config[$provider]['provider_id'], $oauthData['provider_user_id']]
            );

            if ($connection) {
                self::linkAccount($provider, (int)$connection['user_id'], $oauthData);
                return (int)$connection['user_id'];
            }

            $user = $db->fetch("SELECT id FROM users WHERE email = ?", [$oauthData['email']]);
            if ($user) {
                self::linkAccount($provider, (int)$user['id'], $oauthData);
                return (int)$user['id'];
            }

            $insertUserData = [
                'name' => Security::sanitize($oauthData['name'] ?: $oauthData['email']),
                'email' => $oauthData['email'],
                'password' => Security::hashPassword(bin2hex(random_bytes(16))),
                'oauth_only' => 1,
                'user_unique_id' => \Core\Auth::generateUuidV4(),
                'email_verified_at' => !empty($oauthData['email_verified']) ? date('Y-m-d H:i:s') : null,
                'status' => 'active',
                'role' => 'user',
                'created_at' => date('Y-m-d H:i:s')
            ];

            $providerColumn = self::getProviderUserColumn($provider);
            if (self::usersColumnExists($providerColumn)) {
                $insertUserData[$providerColumn] = $oauthData['provider_user_id'];
            }

            $userId = $db->insert('users', $insertUserData);

            $db->insert('user_profiles', [
                'user_id' => $userId,
                'avatar' => $oauthData['avatar'],
                'created_at' => date('Y-m-d H:i:s')
            ]);

            self::linkAccount($provider, (int)$userId, $oauthData);

            Logger::activity((int)$userId, 'registration', [
                'method' => self::getLoginMethodName($provider),
                'email' => $oauthData['email']
            ]);

            return (int)$userId;
        } catch (\Exception $e) {
            Logger::error('OAuth find/create user error (' . $provider . '): ' . $e->getMessage());
            return false;
        }
    }

    public static function revokeConnection(string $provider, int $userId): bool
    {
        self::init($provider);
        $provider = self::normalizeProvider($provider);

        if (!self::isEnabled($provider)) {
            return false;
        }

        try {
            $db = Database::getInstance();
            $user = $db->fetch("SELECT oauth_only FROM users WHERE id = ?", [$userId]);

            if (!$user || ($user['oauth_only'] ?? 0) == 1) {
                Logger::error('Cannot unlink OAuth account: User is OAuth-only');
                return false;
            }

            $db->delete('oauth_user_connections', 'user_id = ? AND provider_id = ?', [$userId, self::$config[$provider]['provider_id']]);

            $providerColumn = self::getProviderUserColumn($provider);
            if (self::usersColumnExists($providerColumn)) {
                $db->update('users', [
                    $providerColumn => null
                ], 'id = ?', [$userId]);
            }

            Logger::activity($userId, $provider . '_oauth_revoked', []);
            return true;
        } catch (\Exception $e) {
            Logger::error('OAuth revoke connection error (' . $provider . '): ' . $e->getMessage());
            return false;
        }
    }

    public static function consumeReturnUrl(string $provider): string
    {
        $provider = self::normalizeProvider($provider);
        $key = 'oauth_return_url_' . $provider;
        $returnUrl = $_SESSION[$key] ?? '/dashboard';
        unset($_SESSION[$key]);

        return self::sanitizeReturnPath($returnUrl) ?? '/dashboard';
    }
}
