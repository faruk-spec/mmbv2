<?php
/**
 * Dashboard Controller
 * 
 * @package MMB\Controllers
 */

namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Security;
use Core\Helpers;
use Core\Logger;

class DashboardController extends BaseController
{
    /**
     * Dashboard home
     */
    public function index(): void
    {
        $db = Database::getInstance();
        
        // Get projects from database or config
        try {
            $projects = $db->fetchAll("SELECT * FROM home_projects WHERE is_enabled = 1 ORDER BY sort_order ASC");
            
            // Convert to associative array format
            $projectsList = [];
            foreach ($projects as $project) {
                $projectsList[$project['project_key']] = $project;
            }
            $projects = $projectsList;
        } catch (\Exception $e) {
            // Fallback to config if database query fails
            $projects = require BASE_PATH . '/config/projects.php';
            // Filter only enabled projects
            $projects = array_filter($projects, function($project) {
                return $project['enabled'] ?? true;
            });
        }
        
        // Get user activity
        $activity = $db->fetchAll(
            "SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 5",
            [Auth::id()]
        );
        
        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'projects' => $projects,
            'activity' => $activity
        ]);
    }
    
    /**
     * Profile page
     */
    public function profile(): void
    {
        $this->view('dashboard/profile', [
            'title' => 'Profile'
        ]);
    }
    
    /**
     * Update profile
     */
    public function updateProfile(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/profile');
            return;
        }
        
        $errors = $this->validate([
            'name' => 'required|min:2|max:50'
        ]);
        
        if (!empty($errors)) {
            $this->redirect('/profile');
            return;
        }
        
        try {
            $db = Database::getInstance();
            
            // Update user
            $db->update('users', [
                'name' => Security::sanitize($this->input('name')),
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [Auth::id()]);
            
            // Update profile
            $profileData = [
                'bio' => Security::sanitize($this->input('bio', '')),
                'phone' => Security::sanitize($this->input('phone', '')),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // Handle avatar upload
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                $avatar = Helpers::uploadFile(
                    $_FILES['avatar'],
                    BASE_PATH . '/storage/uploads/avatars',
                    ['jpg', 'jpeg', 'png', 'gif']
                );
                
                if ($avatar) {
                    $profileData['avatar'] = $avatar;
                }
            }
            
            $db->update('user_profiles', $profileData, 'user_id = ?', [Auth::id()]);
            
            Logger::activity(Auth::id(), 'profile_updated');
            
            $this->flash('success', 'Profile updated successfully.');
            
        } catch (\Exception $e) {
            Logger::error('Profile update error: ' . $e->getMessage());
            $this->flash('error', 'Failed to update profile.');
        }
        
        $this->redirect('/profile');
    }
    
    /**
     * Security settings page
     */
    public function security(): void
    {
        $db = Database::getInstance();
        
        // Get user devices
        $devices = $db->fetchAll(
            "SELECT * FROM user_remember_tokens WHERE user_id = ? ORDER BY created_at DESC",
            [Auth::id()]
        );
        
        // Get user info for 2FA status
        $user = Auth::user();
        
        $this->view('dashboard/security', [
            'title' => 'Security Settings',
            'devices' => $devices,
            'twoFactorEnabled' => !empty($user['two_factor_secret'])
        ]);
    }
    
    /**
     * Update password
     */
    public function updatePassword(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/security');
            return;
        }
        
        $errors = $this->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed'
        ]);
        
        if (!empty($errors)) {
            $this->redirect('/security');
            return;
        }
        
        $user = Auth::user();
        
        if (!Security::verifyPassword($this->input('current_password'), $user['password'])) {
            $this->flash('error', 'Current password is incorrect.');
            $this->redirect('/security');
            return;
        }
        
        try {
            $db = Database::getInstance();
            $db->update('users', [
                'password' => Security::hashPassword($this->input('password')),
                'updated_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [Auth::id()]);
            
            // Invalidate other sessions
            $db->delete('user_remember_tokens', 'user_id = ?', [Auth::id()]);
            
            Logger::activity(Auth::id(), 'password_changed');
            
            $this->flash('success', 'Password updated successfully.');
            
        } catch (\Exception $e) {
            Logger::error('Password update error: ' . $e->getMessage());
            $this->flash('error', 'Failed to update password.');
        }
        
        $this->redirect('/security');
    }
    
    /**
     * Activity log page
     */
    public function activity(): void
    {
        $db = Database::getInstance();
        
        $page = max(1, (int) $this->input('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        $activity = $db->fetchAll(
            "SELECT * FROM activity_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [Auth::id(), $perPage, $offset]
        );
        
        $total = $db->fetch(
            "SELECT COUNT(*) as count FROM activity_logs WHERE user_id = ?",
            [Auth::id()]
        );
        
        $this->view('dashboard/activity', [
            'title' => 'Activity Log',
            'activity' => $activity,
            'pagination' => [
                'current' => $page,
                'total' => ceil($total['count'] / $perPage),
                'perPage' => $perPage
            ]
        ]);
    }
    
    /**
     * Settings page
     */
    public function settings(): void
    {
        $this->view('dashboard/settings', [
            'title' => 'Settings'
        ]);
    }
    
    /**
     * Update settings
     */
    public function updateSettings(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/settings');
            return;
        }
        
        $settingType = $this->input('setting_type', 'theme');
        
        try {
            $db = Database::getInstance();
            
            // Handle different setting types
            switch ($settingType) {
                case 'theme':
                    $theme = $this->input('theme', 'dark');
                    if (!in_array($theme, ['dark', 'light'])) {
                        $theme = 'dark';
                    }
                    
                    // Update user preferences
                    $db->update('user_profiles', [
                        'theme_preference' => $theme,
                        'updated_at' => date('Y-m-d H:i:s')
                    ], 'user_id = ?', [Auth::id()]);
                    
                    Logger::activity(Auth::id(), 'theme_changed', $theme);
                    $this->flash('success', 'Theme preference updated successfully.');
                    break;
                    
                case 'notifications':
                    $emailNotifications = $this->input('email_notifications', 0);
                    $securityAlerts = $this->input('security_alerts', 0);
                    $productUpdates = $this->input('product_updates', 0);
                    
                    $db->update('user_profiles', [
                        'email_notifications' => $emailNotifications ? 1 : 0,
                        'security_alerts' => $securityAlerts ? 1 : 0,
                        'product_updates' => $productUpdates ? 1 : 0,
                        'updated_at' => date('Y-m-d H:i:s')
                    ], 'user_id = ?', [Auth::id()]);
                    
                    Logger::activity(Auth::id(), 'notification_preferences_updated');
                    $this->flash('success', 'Notification preferences updated successfully.');
                    break;
                    
                case 'display':
                    $itemsPerPage = max(10, min(100, (int) $this->input('items_per_page', 20)));
                    $dateFormat = Security::sanitize($this->input('date_format', 'M d, Y'));
                    
                    $db->update('user_profiles', [
                        'items_per_page' => $itemsPerPage,
                        'date_format' => $dateFormat,
                        'updated_at' => date('Y-m-d H:i:s')
                    ], 'user_id = ?', [Auth::id()]);
                    
                    Logger::activity(Auth::id(), 'display_settings_updated');
                    $this->flash('success', 'Display settings updated successfully.');
                    break;
                    
                default:
                    $this->flash('error', 'Invalid setting type.');
            }
            
        } catch (\Exception $e) {
            Logger::error('Settings update error: ' . $e->getMessage());
            $this->flash('error', 'Failed to update settings.');
        }
        
        $this->redirect('/settings');
    }
}
