<?php
/**
 * Google OAuth Controller
 * Handles Google SSO authentication
 * 
 * @package MMB\Controllers
 */

namespace Controllers;

use Core\Auth;
use Core\GoogleOAuth;
use Core\Security;
use Core\SessionManager;
use Core\Logger;
use Core\Database;

class GoogleOAuthController extends BaseController
{
    /**
     * Redirect to Google OAuth
     */
    public function redirect(): void
    {
        if (!GoogleOAuth::isEnabled()) {
            $this->flash('error', 'Google Sign-In is not configured.');
            $this->redirect('/login');
            return;
        }
        
        $returnUrl = $_GET['return'] ?? $_GET['redirect'] ?? null;
        $authUrl = GoogleOAuth::getAuthUrl($returnUrl);
        
        header('Location: ' . $authUrl);
        exit;
    }
    
    /**
     * Handle Google OAuth callback
     */
    public function callback(): void
    {
        if (!GoogleOAuth::isEnabled()) {
            $this->flash('error', 'Google Sign-In is not configured.');
            $this->redirect('/login');
            return;
        }
        
        $code = $_GET['code'] ?? null;
        $state = $_GET['state'] ?? null;
        $error = $_GET['error'] ?? null;
        
        // Handle user cancellation
        if ($error === 'access_denied') {
            $this->flash('info', 'Google Sign-In was cancelled.');
            $this->redirect('/login');
            return;
        }
        
        if (!$code || !$state) {
            $this->flash('error', 'Invalid OAuth response.');
            $this->redirect('/login');
            return;
        }
        
        // Handle the callback
        $oauthData = GoogleOAuth::handleCallback($code, $state);
        
        if (!$oauthData) {
            $this->flash('error', 'Failed to authenticate with Google. Please try again.');
            $this->redirect('/login');
            return;
        }
        
        // Find or create user
        $userId = GoogleOAuth::findOrCreateUser($oauthData);
        
        if (!$userId) {
            $this->flash('error', 'Failed to create or link account.');
            $this->redirect('/login');
            return;
        }
        
        // Log the user in
        try {
            $db = Database::getInstance();
            $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
            
            if (!$user || $user['status'] !== 'active') {
                $this->flash('error', 'Account is inactive or banned.');
                $this->redirect('/login');
                return;
            }
            
            // Set user session
            session_regenerate_id(true);
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['_created'] = time();
            
            // Track session
            SessionManager::track($userId);
            
            // Log login
            $this->logLogin($userId, $oauthData['email'], 'google_oauth', 'success');
            
            // Update last login
            $db->update('users', [
                'last_login_at' => date('Y-m-d H:i:s'),
                'last_login_ip' => Security::getClientIp()
            ], 'id = ?', [$userId]);
            
            // Log activity
            Logger::activity($userId, 'login', [
                'method' => 'google_oauth',
                'email' => $oauthData['email']
            ]);
            
            // Get return URL
            $returnUrl = $_SESSION['oauth_return_url'] ?? '/dashboard';
            unset($_SESSION['oauth_return_url']);
            
            $this->flash('success', 'Successfully signed in with Google!');
            $this->redirect($returnUrl);
            
        } catch (\Exception $e) {
            Logger::error('Google OAuth login error: ' . $e->getMessage());
            $this->flash('error', 'An error occurred during sign-in.');
            $this->redirect('/login');
        }
    }
    
    /**
     * Link Google account to existing user
     */
    public function link(): void
    {
        $this->requireAuth();
        
        if (!GoogleOAuth::isEnabled()) {
            $this->flash('error', 'Google Sign-In is not configured.');
            $this->redirect('/security');
            return;
        }
        
        $userId = Auth::id();
        $_SESSION['oauth_linking'] = true;
        $_SESSION['oauth_link_user_id'] = $userId;
        
        $authUrl = GoogleOAuth::getAuthUrl('/security');
        header('Location: ' . $authUrl);
        exit;
    }
    
    /**
     * Unlink Google account
     */
    public function unlink(): void
    {
        $this->requireAuth();
        
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/security');
            return;
        }
        
        $userId = Auth::id();
        
        if (GoogleOAuth::revokeConnection($userId)) {
            $this->flash('success', 'Google account unlinked successfully.');
        } else {
            $this->flash('error', 'Failed to unlink Google account.');
        }
        
        $this->redirect('/security');
    }
    
    /**
     * Log login attempt
     */
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
