<?php
/**
 * Multi-Provider OAuth Controller
 * Handles Google, GitHub, and Apple SSO authentication
 *
 * @package MMB\Controllers
 */

namespace Controllers;

use Core\Auth;
use Core\OAuthProvider;
use Core\Security;
use Core\SessionManager;
use Core\Logger;
use Core\Database;
use Core\Notification;
use Core\MailService;

class SocialOAuthController extends BaseController
{
    public function redirectToProvider(string $provider): void
    {
        $provider = strtolower(trim($provider));
        if (!OAuthProvider::isSupportedProvider($provider) || !OAuthProvider::isEnabled($provider)) {
            $this->flash('error', ucfirst($provider) . ' Sign-In is not configured.');
            $this->redirect('/login');
            return;
        }

        $returnUrl = $_GET['return'] ?? $_GET['redirect'] ?? null;
        $authUrl = OAuthProvider::getAuthUrl($provider, $returnUrl);

        header('Location: ' . $authUrl);
        exit;
    }

    public function callback(string $provider): void
    {
        $provider = strtolower(trim($provider));
        $providerName = OAuthProvider::getDisplayName($provider);

        if (!OAuthProvider::isSupportedProvider($provider) || !OAuthProvider::isEnabled($provider)) {
            $this->flash('error', $providerName . ' Sign-In is not configured.');
            $this->redirect('/login');
            return;
        }

        $code = $_GET['code'] ?? null;
        $state = $_GET['state'] ?? null;
        $error = $_GET['error'] ?? null;

        if ($error === 'access_denied') {
            $this->flash('info', $providerName . ' Sign-In was cancelled.');
            $this->redirect('/login');
            return;
        }

        if (!$code || !$state) {
            $this->flash('error', 'Invalid OAuth response.');
            $this->redirect('/login');
            return;
        }

        $oauthData = OAuthProvider::handleCallback($provider, $code, $state);
        if (!$oauthData) {
            $this->flash('error', 'Failed to authenticate with ' . $providerName . '. Please try again.');
            $this->redirect('/login');
            return;
        }

        $userId = OAuthProvider::findOrCreateUser($provider, $oauthData);
        if (!$userId) {
            $this->flash('error', 'Failed to create or link account.');
            $this->redirect('/login');
            return;
        }

        try {
            $db = Database::getInstance();
            $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);

            if (!$user || $user['status'] !== 'active') {
                $this->flash('error', 'Account is inactive or banned.');
                $this->redirect('/login');
                return;
            }

            $isNewUser = empty($user['last_login_at']);
            $loginMethod = OAuthProvider::getLoginMethodName($provider);

            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['_created'] = time();

            SessionManager::track($userId);
            $this->logLogin($userId, $oauthData['email'], $loginMethod, 'success');

            $db->update('users', [
                'last_login_at' => date('Y-m-d H:i:s'),
                'last_login_ip' => Security::getClientIp()
            ], 'id = ?', [$userId]);

            Logger::activity($userId, 'login', [
                'method' => $loginMethod,
                'email' => $oauthData['email']
            ]);

            try {
                Notification::send($userId, 'user_login', 'You signed in with ' . $providerName . '.', [
                    'method' => $loginMethod,
                    'ip' => Security::getClientIp()
                ]);
            } catch (\Exception $e) {
            }

            if ($isNewUser) {
                try {
                    MailService::sendNotification($user['email'], 'welcome', [
                        'name' => $user['name'],
                        'login_url' => (defined('APP_URL') ? APP_URL : '') . '/dashboard',
                    ], false);
                } catch (\Throwable $e) {
                    Logger::error($providerName . ' SSO welcome email failed: ' . $e->getMessage());
                }
            } else {
                try {
                    MailService::sendNotification($user['email'], 'login_alert', [
                        'name' => $user['name'],
                        'ip_address' => Security::getClientIp(),
                        'login_time' => date('Y-m-d H:i:s'),
                        'reset_url' => (defined('APP_URL') ? APP_URL : '') . '/forgot-password',
                    ], false);
                } catch (\Throwable $e) {
                    Logger::error($providerName . ' SSO login alert email failed: ' . $e->getMessage());
                }
            }

            $returnUrl = OAuthProvider::consumeReturnUrl($provider);
            $this->flash('success', 'Successfully signed in with ' . $providerName . '!');
            $this->redirect($returnUrl);
        } catch (\Exception $e) {
            Logger::error($providerName . ' OAuth login error: ' . $e->getMessage());
            $this->flash('error', 'An error occurred during sign-in.');
            $this->redirect('/login');
        }
    }

    public function link(string $provider): void
    {
        $this->requireAuth();
        $provider = strtolower(trim($provider));

        if (!OAuthProvider::isSupportedProvider($provider) || !OAuthProvider::isEnabled($provider)) {
            $this->flash('error', OAuthProvider::getDisplayName($provider) . ' Sign-In is not configured.');
            $this->redirect('/security');
            return;
        }

        $_SESSION['oauth_linking'] = true;
        $_SESSION['oauth_link_user_id'] = Auth::id();

        $authUrl = OAuthProvider::getAuthUrl($provider, '/security');
        header('Location: ' . $authUrl);
        exit;
    }

    public function unlink(string $provider): void
    {
        $this->requireAuth();
        $provider = strtolower(trim($provider));
        $providerName = OAuthProvider::getDisplayName($provider);

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/security');
            return;
        }

        $userId = Auth::id();

        $db = Database::getInstance();
        $userCheck = $db->fetch("SELECT oauth_only FROM users WHERE id = ?", [$userId]);
        if ($userCheck && ($userCheck['oauth_only'] ?? 0) == 1) {
            $this->flash('error', 'You must set a password before unlinking your ' . $providerName . ' account. Go to Security Settings and change your password first.');
            $this->redirect('/security');
            return;
        }

        if (OAuthProvider::revokeConnection($provider, $userId)) {
            try {
                Notification::send($userId, $provider . '_unlinked', 'Your ' . $providerName . ' account has been unlinked.', []);
            } catch (\Exception $e) {
            }
            $this->flash('success', $providerName . ' account unlinked successfully.');
        } else {
            $this->flash('error', 'Failed to unlink ' . $providerName . ' account. Please ensure you have a password set.');
        }

        $this->redirect('/security');
    }

    private function logLogin(int $userId, string $email, string $method, string $status, ?string $failureReason = null): void
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
