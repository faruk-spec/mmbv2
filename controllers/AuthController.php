<?php
/**
 * Authentication Controller
 * 
 * @package MMB\Controllers
 */

namespace Controllers;

use Core\Auth;
use Core\Security;
use Core\SSO;
use Core\Helpers;
use Core\View;
use Core\TrafficTracker;
use Core\Logger;
use Core\Notification;
use Core\MailService;

class AuthController extends BaseController
{
    /**
     * Show login form
     */
    public function showLogin(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }
        
        // Check if session expired
        if (isset($_GET['expired']) && $_GET['expired'] == '1') {
            $this->flash('info', 'Your session has expired. Please sign in again.');
        }
        
        $this->view('auth/login', [
            'title' => 'Login'
        ]);
    }
    
    /**
     * Process login
     */
    public function login(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request. Please try again.');
            $this->redirect('/login');
            return;
        }
        
        $errors = $this->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
        
        if (!empty($errors)) {
            $this->redirect('/login');
            return;
        }
        
        $email = $this->input('email');
        $password = $this->input('password');
        $remember = $this->input('remember') === 'on';
        
        // Attempt authentication (but don't complete login yet if 2FA is enabled)
        $user = Auth::attemptCredentials($email, $password);
        
        if ($user) {
            // Check if 2FA is enabled
            if (!empty($user['two_factor_secret']) && $user['two_factor_enabled']) {
                // Store user ID in session for 2FA verification
                $_SESSION['pending_2fa_user_id'] = $user['id'];
                $_SESSION['pending_2fa_remember'] = $remember;
                
                // Redirect to 2FA verification
                $this->redirect('/2fa/verify');
                return;
            }
            
            // No 2FA, complete login normally
            Auth::loginUser($user['id'], $remember);
            
            // Track login
            TrafficTracker::trackLogin($user['id']);
            
            // Log activity
            Logger::activity($user['id'], 'login', [
                'method' => 'email_password',
                'remember' => $remember
            ]);
            try { Notification::send($user['id'], 'user_login', 'You logged in successfully.', ['method' => 'email_password', 'ip' => Security::getClientIp()]); } catch (\Exception $e) {}
            
            // Generate SSO token
            $ssoToken = SSO::generateToken($user['id']);
            SSO::storeToken($ssoToken);
            
            // Set SSO cookie for projects
            setcookie('sso_token', $ssoToken, time() + 3600, '/', '', true, true);
            
            $returnUrl = $_GET['redirect'] ?? $_GET['return'] ?? '/dashboard';
            $this->redirect($returnUrl);
        } else {
            $this->flash('error', 'Invalid credentials or account is locked.');
            View::flashOldInput(['email' => $email]);
            $this->redirect('/login');
        }
    }
    
    /**
     * Show registration form
     */
    public function showRegister(): void
    {
        if (Auth::check()) {
            $this->redirect('/dashboard');
        }
        
        $this->view('auth/register', [
            'title' => 'Register'
        ]);
    }
    
    /**
     * Process registration
     */
    public function register(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request. Please try again.');
            $this->redirect('/register');
            return;
        }
        
        $errors = $this->validate([
            'name' => 'required|min:2|max:50',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed'
        ]);
        
        if (!empty($errors)) {
            $this->redirect('/register');
            return;
        }
        
        $userId = Auth::register([
            'name' => Security::sanitize($this->input('name')),
            'email' => $this->input('email'),
            'password' => $this->input('password')
        ]);
        
        if ($userId) {
            // Track registration
            TrafficTracker::trackRegistration($userId);
            
            // Log activity
            Logger::activity($userId, 'registration', [
                'name' => Security::sanitize($this->input('name')),
                'email' => $this->input('email')
            ]);
            try { Notification::send($userId, 'user_registered', 'Welcome! Your account has been created.', ['email' => $this->input('email')]); } catch (\Exception $e) {}
            
            // Send welcome notification email
            try {
                MailService::sendNotification($this->input('email'), 'welcome', [
                    'name'      => Security::sanitize($this->input('name')),
                    'login_url' => APP_URL . '/login',
                ]);
            } catch (\Exception $e) {
                Logger::error('Welcome email failed: ' . $e->getMessage());
            }
            
            $this->flash('success', 'Registration successful! Please login.');
            $this->redirect('/login');
        } else {
            $this->flash('error', 'Registration failed. Email may already exist.');
            $this->redirect('/register');
        }
    }
    
    /**
     * Logout user
     */
    public function logout(): void
    {
        // Log activity before logout
        if (Auth::check()) {
            Logger::activity(Auth::id(), 'logout');
        }
        
        SSO::clearToken();
        setcookie('sso_token', '', time() - 3600, '/', '', true, true);
        Auth::logout();
        $this->redirect('/?logged_out=1');
    }
    
    /**
     * Show forgot password form
     */
    public function showForgotPassword(): void
    {
        $this->view('auth/forgot-password', [
            'title' => 'Forgot Password'
        ]);
    }
    
    /**
     * Process forgot password
     */
    public function forgotPassword(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/forgot-password');
            return;
        }
        
        $email = $this->input('email');
        
        if (Auth::sendPasswordReset($email)) {
            $this->flash('success', 'If that email exists, a reset link has been sent.');
        }
        
        $this->redirect('/forgot-password');
    }
    
    /**
     * Show reset password form
     */
    public function showResetPassword(string $token): void
    {
        $db          = \Core\Database::getInstance();
        $hashedToken = hash('sha256', $token);

        // Check token exists and is within 5-minute window
        $reset = $db->fetch(
            "SELECT * FROM password_resets WHERE token = ? AND created_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)",
            [$hashedToken]
        );

        if (!$reset) {
            $this->flash('error', 'This password reset link has expired or is invalid. Please request a new one.');
            $this->redirect('/forgot-password');
            return;
        }

        // Reject if already visited (one-time link)
        if (!empty($reset['visited_at'])) {
            $this->flash('error', 'This reset link has already been used. Please request a new one.');
            $this->redirect('/forgot-password');
            return;
        }

        // Mark as visited so it cannot be reopened
        $db->update('password_resets', ['visited_at' => date('Y-m-d H:i:s')], 'token = ?', [$hashedToken]);

        $this->view('auth/reset-password', [
            'title' => 'Reset Password',
            'token' => $token
        ]);
    }
    
    /**
     * Process password reset
     */
    public function resetPassword(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/login');
            return;
        }
        
        $errors = $this->validate([
            'password' => 'required|min:8|confirmed'
        ]);
        
        if (!empty($errors)) {
            $this->redirect('/reset-password/' . $this->input('token'));
            return;
        }
        
        $token = $this->input('token');
        $password = $this->input('password');
        
        if (Auth::resetPassword($token, $password)) {
            // Get user ID from token before logging
            $db = \Core\Database::getInstance();
            $resetRecord = $db->fetch("SELECT user_id FROM password_resets WHERE token = ?", [$token]);
            if ($resetRecord) {
                Logger::activity($resetRecord['user_id'], 'password_reset', [
                    'method' => 'email_link'
                ]);
                try { Notification::send($resetRecord['user_id'], 'password_reset', 'Your password was reset successfully.', ['method' => 'email_link', 'ip' => Security::getClientIp()]); } catch (\Exception $e) {}
            }
            
            $this->flash('success', 'Password has been reset. Please login.');
            $this->redirect('/login');
        } else {
            $this->flash('error', 'Invalid or expired reset token.');
            $this->redirect('/forgot-password');
        }
    }
    
    /**
     * Verify email
     */
    public function verifyEmail(string $token): void
    {
        if (Auth::verifyEmail($token)) {
            // Get user ID and log activity
            $db = \Core\Database::getInstance();
            $user = $db->fetch("SELECT id FROM users WHERE email_verified = 1 ORDER BY updated_at DESC LIMIT 1");
            if ($user) {
                Logger::activity($user['id'], 'email_verified');
            }
            
            $this->flash('success', 'Email verified successfully!');
        } else {
            $this->flash('error', 'Invalid verification token.');
        }
        
        $this->redirect('/login');
    }
}
