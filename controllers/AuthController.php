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
        
        if (Auth::attempt($email, $password, $remember)) {
            // Track login
            TrafficTracker::trackLogin(Auth::id());
            
            // Generate SSO token
            $ssoToken = SSO::generateToken(Auth::id());
            SSO::storeToken($ssoToken);
            
            // Set SSO cookie for projects
            setcookie('sso_token', $ssoToken, time() + 3600, '/', '', true, true);
            
            $returnUrl = $_GET['return'] ?? '/dashboard';
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
        SSO::clearToken();
        setcookie('sso_token', '', time() - 3600, '/', '', true, true);
        Auth::logout();
        $this->redirect('/');
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
            $this->flash('success', 'Email verified successfully!');
        } else {
            $this->flash('error', 'Invalid verification token.');
        }
        
        $this->redirect('/login');
    }
}
