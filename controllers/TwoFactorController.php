<?php
/**
 * Two-Factor Authentication Controller
 * 
 * Implements TOTP-based 2FA compatible with Google Authenticator
 * 
 * @package MMB\Controllers
 */

namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Security;
use Core\Logger;
use Core\TOTP;

class TwoFactorController extends BaseController
{
    /**
     * Show 2FA setup page
     */
    public function setup(): void
    {
        $user = Auth::user();
        $twoFactorEnabled = !empty($user['two_factor_secret']) && $user['two_factor_enabled'];
        
        $secret = null;
        $qrCodeUrl = null;
        $provisioningUri = null;
        
        // If 2FA is not yet enabled, generate a new secret for setup
        if (!$twoFactorEnabled) {
            $secret = TOTP::generateSecret();
            $accountName = $user['email'];
            $issuer = defined('APP_NAME') ? APP_NAME : 'MyMultiBranch';
            $provisioningUri = TOTP::getProvisioningUri($secret, $accountName, $issuer);
            $qrCodeUrl = TOTP::getQRCodeUrl($provisioningUri);
            
            // Store secret in session temporarily until verified
            $_SESSION['pending_2fa_secret'] = $secret;
        }
        
        $this->view('dashboard/2fa-setup', [
            'title' => 'Two-Factor Authentication',
            'twoFactorEnabled' => $twoFactorEnabled,
            'secret' => $secret,
            'qrCodeUrl' => $qrCodeUrl,
            'provisioningUri' => $provisioningUri
        ]);
    }
    
    /**
     * Enable 2FA after verification
     */
    public function enable(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/2fa/setup');
            return;
        }
        
        $code = $this->input('code');
        $secret = $_SESSION['pending_2fa_secret'] ?? null;
        
        if (!$secret) {
            $this->flash('error', 'No pending 2FA setup found. Please try again.');
            $this->redirect('/2fa/setup');
            return;
        }
        
        // Verify the code
        if (!TOTP::verifyCode($secret, $code)) {
            $this->flash('error', 'Invalid verification code. Please try again.');
            $this->redirect('/2fa/setup');
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            // Generate backup codes
            $backupCodes = TOTP::generateBackupCodes();
            $hashedBackupCodes = array_map(function($code) {
                return password_hash($code, PASSWORD_DEFAULT);
            }, $backupCodes);
            
            // Enable 2FA
            $db->update('users', [
                'two_factor_secret' => $secret,
                'two_factor_enabled' => 1,
                'two_factor_backup_codes' => json_encode($hashedBackupCodes),
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [Auth::id()]);
            
            // Clear pending secret
            unset($_SESSION['pending_2fa_secret']);
            
            // Store backup codes in session to display once
            $_SESSION['2fa_backup_codes'] = $backupCodes;
            
            Logger::activity(Auth::id(), '2fa_enabled');
            
            $this->flash('success', 'Two-factor authentication enabled successfully!');
            $this->redirect('/2fa/backup-codes');
            
        } catch (\Exception $e) {
            Logger::error('2FA enable error: ' . $e->getMessage());
            $this->flash('error', 'Failed to enable 2FA.');
            $this->redirect('/2fa/setup');
        }
    }
    
    /**
     * Show backup codes (one-time display after enabling)
     */
    public function showBackupCodes(): void
    {
        $backupCodes = $_SESSION['2fa_backup_codes'] ?? null;
        
        if (!$backupCodes) {
            $this->redirect('/security');
            return;
        }
        
        // Clear from session after displaying
        unset($_SESSION['2fa_backup_codes']);
        
        $this->view('dashboard/2fa-backup-codes', [
            'title' => 'Backup Codes',
            'backupCodes' => $backupCodes
        ]);
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
                'two_factor_backup_codes' => null,
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
     * Show 2FA verification page (during login)
     */
    public function showVerify(): void
    {
        // Must have pending 2FA verification
        if (empty($_SESSION['pending_2fa_user_id'])) {
            $this->redirect('/login');
            return;
        }
        
        $this->view('auth/2fa-verify', [
            'title' => 'Verify 2FA Code'
        ]);
    }
    
    /**
     * Verify 2FA code during login
     */
    public function verify(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/2fa/verify');
            return;
        }
        
        $userId = $_SESSION['pending_2fa_user_id'] ?? null;
        
        if (!$userId) {
            $this->flash('error', 'No pending authentication.');
            $this->redirect('/login');
            return;
        }
        
        try {
            $db = Database::getInstance();
            $user = $db->fetch("SELECT * FROM users WHERE id = ?", [$userId]);
            
            if (!$user || !$user['two_factor_enabled']) {
                $this->flash('error', 'Invalid authentication state.');
                $this->redirect('/login');
                return;
            }
            
            $code = $this->input('code');
            $useBackup = $this->input('use_backup') === '1';
            
            $verified = false;
            
            if ($useBackup) {
                // Verify backup code
                $backupCodes = json_decode($user['two_factor_backup_codes'] ?? '[]', true);
                
                foreach ($backupCodes as $index => $hashedCode) {
                    if (password_verify($code, $hashedCode)) {
                        // Remove used backup code
                        unset($backupCodes[$index]);
                        $db->update('users', [
                            'two_factor_backup_codes' => json_encode(array_values($backupCodes))
                        ], 'id = ?', [$userId]);
                        
                        $verified = true;
                        Logger::activity($userId, '2fa_backup_code_used');
                        break;
                    }
                }
            } else {
                // Verify TOTP code
                $verified = TOTP::verifyCode($user['two_factor_secret'], $code, 1);
            }
            
            if (!$verified) {
                $this->flash('error', 'Invalid verification code.');
                $this->redirect('/2fa/verify');
                return;
            }
            
            // Mark as verified and complete login
            unset($_SESSION['pending_2fa_user_id']);
            $_SESSION['2fa_verified'] = true;
            $_SESSION['user_id'] = $userId;
            
            Logger::activity($userId, '2fa_verified');
            
            $returnUrl = $_SESSION['return_url'] ?? '/dashboard';
            unset($_SESSION['return_url']);
            
            $this->redirect($returnUrl);
            
        } catch (\Exception $e) {
            Logger::error('2FA verification error: ' . $e->getMessage());
            $this->flash('error', 'Verification failed.');
            $this->redirect('/2fa/verify');
        }
    }
}
