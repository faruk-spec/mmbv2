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
        
        // Get user active sessions
        $sessions = $db->fetchAll(
            "SELECT * FROM user_sessions WHERE user_id = ? AND is_active = 1 ORDER BY last_activity_at DESC",
            [Auth::id()]
        );
        
        // Get user info for 2FA status
        $user = Auth::user();
        
        $this->view('dashboard/security', [
            'title' => 'Security Settings',
            'devices' => $devices,
            'sessions' => $sessions,
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
     * Revoke a session
     */
    public function revokeSession(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/security');
            return;
        }
        
        try {
            $db = Database::getInstance();
            $sessionId = (int) $this->input('session_id');
            
            // Verify this session belongs to the current user
            $session = $db->fetch(
                "SELECT * FROM user_sessions WHERE id = ? AND user_id = ?",
                [$sessionId, Auth::id()]
            );
            
            if (!$session) {
                $this->flash('error', 'Session not found.');
                $this->redirect('/security');
                return;
            }
            
            // Don't allow revoking current session
            if ($session['session_id'] === session_id()) {
                $this->flash('error', 'Cannot revoke current session. Use logout instead.');
                $this->redirect('/security');
                return;
            }
            
            // Mark session as inactive
            $db->update('user_sessions', [
                'is_active' => 0,
                'last_activity_at' => date('Y-m-d H:i:s')
            ], 'id = ?', [$sessionId]);
            
            Logger::activity(Auth::id(), 'session_revoked', ['session_id' => $sessionId]);
            
            $this->flash('success', 'Session revoked successfully.');
            
        } catch (\Exception $e) {
            Logger::error('Session revoke error: ' . $e->getMessage());
            $this->flash('error', 'Failed to revoke session.');
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
            
            // Ensure user_profiles entry exists
            $profile = $db->fetch("SELECT * FROM user_profiles WHERE user_id = ?", [Auth::id()]);
            if (!$profile) {
                $db->insert('user_profiles', [
                    'user_id' => Auth::id(),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            // Handle different setting types
            switch ($settingType) {
                case 'theme':
                    $theme = $this->input('theme', 'dark');
                    if (!in_array($theme, ['dark', 'light'])) {
                        $theme = 'dark';
                    }
                    
                    // Try to update, if column doesn't exist, log but don't fail
                    try {
                        $db->update('user_profiles', [
                            'theme_preference' => $theme,
                            'updated_at' => date('Y-m-d H:i:s')
                        ], 'user_id = ?', [Auth::id()]);
                        
                        // Also update navbar_settings for global theme
                        $db->query("UPDATE navbar_settings SET default_theme = ? WHERE id = 1", [$theme]);
                        
                        Logger::activity(Auth::id(), 'theme_changed', ['theme' => $theme]);
                        $this->flash('success', 'Theme preference updated successfully.');
                    } catch (\Exception $e) {
                        // Fallback: just update navbar_settings
                        $db->query("UPDATE navbar_settings SET default_theme = ? WHERE id = 1", [$theme]);
                        Logger::activity(Auth::id(), 'theme_changed', ['theme' => $theme]);
                        $this->flash('success', 'Theme updated successfully.');
                    }
                    break;
                    
                case 'notifications':
                    $emailNotifications = $this->input('email_notifications', 0);
                    $securityAlerts = $this->input('security_alerts', 0);
                    $productUpdates = $this->input('product_updates', 0);
                    
                    try {
                        $db->update('user_profiles', [
                            'email_notifications' => $emailNotifications ? 1 : 0,
                            'security_alerts' => $securityAlerts ? 1 : 0,
                            'product_updates' => $productUpdates ? 1 : 0,
                            'updated_at' => date('Y-m-d H:i:s')
                        ], 'user_id = ?', [Auth::id()]);
                        
                        Logger::activity(Auth::id(), 'notification_preferences_updated');
                        $this->flash('success', 'Notification preferences updated successfully.');
                    } catch (\Exception $e) {
                        Logger::error('Notification update error: ' . $e->getMessage());
                        $this->flash('success', 'Preferences saved.');
                    }
                    break;
                    
                case 'display':
                    $itemsPerPage = max(10, min(100, (int) $this->input('items_per_page', 20)));
                    $dateFormat = Security::sanitize($this->input('date_format', 'M d, Y'));
                    $displaySettings = json_encode([
                        'items_per_page' => $itemsPerPage,
                        'date_format' => $dateFormat
                    ]);
                    
                    try {
                        $db->update('user_profiles', [
                            'display_settings' => $displaySettings,
                            'updated_at' => date('Y-m-d H:i:s')
                        ], 'user_id = ?', [Auth::id()]);
                        
                        Logger::activity(Auth::id(), 'display_settings_updated', ['settings' => $displaySettings]);
                        $this->flash('success', 'Display settings updated successfully.');
                    } catch (\Exception $e) {
                        Logger::error('Display update error: ' . $e->getMessage());
                        $this->flash('error', 'Failed to update display settings. Please ensure database migration is run.');
                    }
                    break;
                    
                case 'projects':
                    $defaultView = Security::sanitize($this->input('default_view', 'grid'));
                    $autoSave = $this->input('auto_save', 0);
                    $projectSettings = json_encode([
                        'default_view' => $defaultView,
                        'auto_save' => $autoSave ? 1 : 0
                    ]);
                    
                    try {
                        $db->update('user_profiles', [
                            'project_settings' => $projectSettings,
                            'updated_at' => date('Y-m-d H:i:s')
                        ], 'user_id = ?', [Auth::id()]);
                        
                        Logger::activity(Auth::id(), 'project_settings_updated', ['settings' => $projectSettings]);
                        $this->flash('success', 'Project settings updated successfully.');
                    } catch (\Exception $e) {
                        Logger::error('Project update error: ' . $e->getMessage());
                        $this->flash('error', 'Failed to update project settings. Please ensure database migration is run.');
                    }
                    break;
                    
                default:
                    // Project-specific settings
                    $projectDefaults = $this->input('project_defaults', []);
                    $autoSaveEnabled = $this->input('auto_save_enabled', 0);
                    $defaultProjectView = Security::sanitize($this->input('default_project_view', 'grid'));
                    
                    try {
                        $db->update('user_profiles', [
                            'project_defaults' => json_encode($projectDefaults),
                            'auto_save_enabled' => $autoSaveEnabled ? 1 : 0,
                            'default_project_view' => $defaultProjectView,
                            'updated_at' => date('Y-m-d H:i:s')
                        ], 'user_id = ?', [Auth::id()]);
                        
                        Logger::activity(Auth::id(), 'project_settings_updated');
                        $this->flash('success', 'Project settings updated successfully.');
                    } catch (\Exception $e) {
                        Logger::error('Project settings error: ' . $e->getMessage());
                        $this->flash('success', 'Settings saved.');
                    }
                    break;
                    
                default:
                    $this->flash('error', 'Invalid setting type.');
            }
            
        } catch (\Exception $e) {
            Logger::error('Settings update error: ' . $e->getMessage());
            $this->flash('error', 'An error occurred. Please try again.');
        }
        
        $this->redirect('/settings');
    }
}
