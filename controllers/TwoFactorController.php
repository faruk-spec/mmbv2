<?php
/**
 * Two-Factor Authentication Controller
 * 
 * Note: This is a placeholder implementation. For production use,
 * integrate a TOTP library like pragmarx/google2fa or spomky-labs/otphp.
 * 
 * @package MMB\Controllers
 */

namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Security;
use Core\Logger;

class TwoFactorController extends BaseController
{
    /**
     * Show 2FA setup page
     */
    public function setup(): void
    {
        $user = Auth::user();
        $twoFactorEnabled = !empty($user['two_factor_secret']);
        
        $this->view('dashboard/2fa-setup', [
            'title' => 'Two-Factor Authentication',
            'twoFactorEnabled' => $twoFactorEnabled
        ]);
    }
    
    /**
     * Enable 2FA
     * 
     * Note: This is a placeholder. In production, you should:
     * 1. Generate a TOTP secret using a library like google2fa
     * 2. Display QR code for the user to scan
     * 3. Verify the code before enabling 2FA
     */
    public function enable(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/2fa/setup');
            return;
        }
        
        $code = $this->input('code');
        
        // Placeholder validation - in production, verify against TOTP secret
        if (empty($code) || strlen($code) !== 6 || !ctype_digit($code)) {
            $this->flash('error', 'Invalid verification code. Please enter a 6-digit code.');
            $this->redirect('/2fa/setup');
            return;
        }
        
        try {
            $db = Database::getInstance();
            $secret = Security::generateToken(32);
            
            $db->update('users', [
                'two_factor_secret' => $secret,
                'two_factor_enabled' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [Auth::id()]);
            
            Logger::activity(Auth::id(), '2fa_enabled');
            
            $this->flash('success', 'Two-factor authentication has been enabled. (Note: This is a demo implementation)');
            
        } catch (\Exception $e) {
            Logger::error('2FA enable error: ' . $e->getMessage());
            $this->flash('error', 'Failed to enable 2FA.');
        }
        
        $this->redirect('/security');
    }
    
    /**
     * Disable 2FA
     */
    public function disable(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/security');
            return;
        }
        
        $password = $this->input('password');
        $user = Auth::user();
        
        if (!Security::verifyPassword($password, $user['password'])) {
            $this->flash('error', 'Incorrect password.');
            $this->redirect('/security');
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            $db->update('users', [
                'two_factor_secret' => null,
                'two_factor_enabled' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [Auth::id()]);
            
            Logger::activity(Auth::id(), '2fa_disabled');
            
            $this->flash('success', 'Two-factor authentication has been disabled.');
            
        } catch (\Exception $e) {
            Logger::error('2FA disable error: ' . $e->getMessage());
            $this->flash('error', 'Failed to disable 2FA.');
        }
        
        $this->redirect('/security');
    }
    
    /**
     * Show 2FA verification page
     */
    public function showVerify(): void
    {
        $this->view('auth/2fa-verify', [
            'title' => 'Verify 2FA'
        ]);
    }
    
    /**
     * Verify 2FA code
     * 
     * Note: This is a placeholder. In production, verify against the user's TOTP secret.
     */
    public function verify(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/2fa/verify');
            return;
        }
        
        $code = $this->input('code');
        
        // Placeholder validation - in production, verify against TOTP
        if (strlen($code) !== 6 || !ctype_digit($code)) {
            $this->flash('error', 'Invalid verification code.');
            $this->redirect('/2fa/verify');
            return;
        }
        
        $_SESSION['2fa_verified'] = true;
        
        $returnUrl = $_GET['return'] ?? '/dashboard';
        $this->redirect($returnUrl);
    }
}
