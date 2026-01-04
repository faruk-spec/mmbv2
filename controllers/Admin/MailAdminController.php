<?php
/**
 * Mail Admin Controller - Platform Super Admin
 * Full system control over all subscribers, users, and settings
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Auth;
use Core\View;
use Core\Database;

class MailAdminController extends BaseController
{
    private $db;
    
    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
        
        $this->db = Database::getInstance();
    }
    
    /**
     * Mail Admin Overview Dashboard
     */
    public function overview()
    {
        // Get system statistics
        $stats = [
            'total_subscribers' => $this->db->fetch("SELECT COUNT(*) as count FROM mail_subscribers")['count'] ?? 0,
            'active_subscriptions' => $this->db->fetch("SELECT COUNT(*) as count FROM mail_subscriptions WHERE status = 'active'")['count'] ?? 0,
            'total_domains' => $this->db->fetch("SELECT COUNT(*) as count FROM mail_domains")['count'] ?? 0,
            'verified_domains' => $this->db->fetch("SELECT COUNT(*) as count FROM mail_domains WHERE is_verified = 1")['count'] ?? 0,
            'total_mailboxes' => $this->db->fetch("SELECT COUNT(*) as count FROM mail_mailboxes")['count'] ?? 0,
            'active_mailboxes' => $this->db->fetch("SELECT COUNT(*) as count FROM mail_mailboxes WHERE is_active = 1")['count'] ?? 0,
            'emails_today' => $this->db->fetch("SELECT COUNT(*) as count FROM mail_logs WHERE DATE(created_at) = CURDATE() AND log_type = 'send'")['count'] ?? 0,
            'emails_this_month' => $this->db->fetch("SELECT COUNT(*) as count FROM mail_logs WHERE MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) AND log_type = 'send'")['count'] ?? 0,
            'revenue_this_month' => $this->db->fetch("SELECT COALESCE(SUM(amount), 0) as total FROM mail_payments WHERE status = 'completed' AND MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE())")['total'] ?? 0,
            'pending_abuse_reports' => $this->db->fetch("SELECT COUNT(*) as count FROM mail_abuse_reports WHERE status = 'pending'")['count'] ?? 0,
        ];
        
        // Get plan distribution
        $planDistribution = $this->db->query(
            "SELECT sp.plan_name, COUNT(sub.id) as count
             FROM mail_subscription_plans sp
             LEFT JOIN mail_subscriptions sub ON sp.id = sub.plan_id AND sub.status = 'active'
             GROUP BY sp.id, sp.plan_name
             ORDER BY sp.sort_order"
        )->fetchAll();
        
        // Get recent subscribers
        $recentSubscribers = $this->db->query(
            "SELECT s.*, 
                    s.account_name as username,
                    s.billing_email as email,
                    sp.plan_name
             FROM mail_subscribers s
             LEFT JOIN mail_subscriptions sub ON s.id = sub.subscriber_id
             LEFT JOIN mail_subscription_plans sp ON sub.plan_id = sp.id
             ORDER BY s.created_at DESC
             LIMIT 10"
        )->fetchAll();
        
        // Get recent abuse reports
        $recentAbuse = $this->db->query(
            "SELECT ar.*, m.email as mailbox_email, d.domain_name
             FROM mail_abuse_reports ar
             LEFT JOIN mail_mailboxes m ON ar.reported_mailbox_id = m.id
             LEFT JOIN mail_domains d ON ar.reported_domain_id = d.id
             WHERE ar.status = 'pending'
             ORDER BY ar.created_at DESC
             LIMIT 5"
        )->fetchAll();
        
        $this->view('admin/mail/overview', [
            'stats' => $stats,
            'planDistribution' => $planDistribution,
            'recentSubscribers' => $recentSubscribers,
            'recentAbuse' => $recentAbuse,
            'title' => 'Mail Server Overview'
        ]);
    }
    
    /**
     * Manage All Subscribers
     */
    public function subscribers()
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Get all subscribers with their subscriptions
        $subscribers = $this->db->query(
            "SELECT s.*, 
                    s.billing_email as email,
                    s.account_name as username,
                    sub.status as subscription_status, 
                    sp.plan_name,
                    COUNT(DISTINCT d.id) as domains_count,
                    COUNT(DISTINCT m.id) as mailboxes_count
             FROM mail_subscribers s
             LEFT JOIN mail_subscriptions sub ON s.id = sub.subscriber_id
             LEFT JOIN mail_subscription_plans sp ON sub.plan_id = sp.id
             LEFT JOIN mail_domains d ON s.id = d.subscriber_id
             LEFT JOIN mail_mailboxes m ON s.id = m.subscriber_id
             GROUP BY s.id
             ORDER BY s.created_at DESC
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        )->fetchAll();
        
        $totalCount = $this->db->fetch("SELECT COUNT(*) as count FROM mail_subscribers")['count'];
        $totalPages = ceil($totalCount / $perPage);
        
        $this->view('admin/mail/subscribers', [
            'subscribers' => $subscribers,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'title' => 'Manage Subscribers'
        ]);
    }
    
    /**
     * View Subscriber Details
     */
    public function subscriberDetails($id)
    {
        $subscriber = $this->db->fetch(
            "SELECT s.*, s.account_name as username, s.billing_email as email, 
                    sub.status as subscription_status, sub.current_period_start, sub.current_period_end,
                    sp.plan_name, sp.max_users, sp.storage_per_user_gb, sp.daily_send_limit
             FROM mail_subscribers s
             LEFT JOIN mail_subscriptions sub ON s.id = sub.subscriber_id
             LEFT JOIN mail_subscription_plans sp ON sub.plan_id = sp.id
             WHERE s.id = ?",
            [$id]
        );
        
        if (!$subscriber) {
            $this->error('Subscriber not found');
            redirect('/admin/projects/mail/subscribers');
            return;
        }
        
        // Get domains
        $domains = $this->db->query(
            "SELECT * FROM mail_domains WHERE subscriber_id = ? ORDER BY created_at DESC",
            [$id]
        )->fetchAll();
        
        // Get mailboxes
        $mailboxes = $this->db->query(
            "SELECT m.*, d.domain_name 
             FROM mail_mailboxes m
             JOIN mail_domains d ON m.domain_id = d.id
             WHERE m.subscriber_id = ?
             ORDER BY m.created_at DESC",
            [$id]
        )->fetchAll();
        
        // Get usage statistics
        $usageStats = $this->db->fetch(
            "SELECT 
                COALESCE(SUM(emails_sent), 0) as total_sent,
                COALESCE(SUM(emails_received), 0) as total_received,
                COALESCE(SUM(storage_used_bytes), 0) as total_storage
             FROM mail_usage_logs
             WHERE subscriber_id = ?",
            [$id]
        );
        
        // Get payment history
        $payments = $this->db->query(
            "SELECT * FROM mail_payments 
             WHERE subscriber_id = ?
             ORDER BY created_at DESC
             LIMIT 10",
            [$id]
        )->fetchAll();
        
        $this->view('admin/mail/subscriber-details', [
            'subscriber' => $subscriber,
            'domains' => $domains,
            'mailboxes' => $mailboxes,
            'usageStats' => $usageStats,
            'payments' => $payments,
            'title' => 'Subscriber Details - ' . $subscriber['account_name']
        ]);
    }
    
    /**
     * Suspend Subscriber
     */
    public function suspendSubscriber()
    {
        $id = $_POST['subscriber_id'] ?? 0;
        $reason = $_POST['reason'] ?? 'Administrative action';
        
        $this->db->query(
            "UPDATE mail_subscribers 
             SET status = 'suspended', suspension_reason = ?, suspended_at = NOW()
             WHERE id = ?",
            [$reason, $id]
        );
        
        // Log admin action
        $this->logAdminAction('suspend_subscriber', 'subscriber', $id, "Suspended subscriber. Reason: $reason");
        
        $this->jsonSuccess('Subscriber suspended successfully');
    }
    
    /**
     * Activate Subscriber
     */
    public function activateSubscriber()
    {
        $id = $_POST['subscriber_id'] ?? 0;
        
        $this->db->query(
            "UPDATE mail_subscribers 
             SET status = 'active', suspension_reason = NULL, suspended_at = NULL
             WHERE id = ?",
            [$id]
        );
        
        // Log admin action
        $this->logAdminAction('activate_subscriber', 'subscriber', $id, "Activated subscriber");
        
        $this->jsonSuccess('Subscriber activated successfully');
    }
    
    /**
     * Override Subscriber Plan
     */
    public function overridePlan()
    {
        $subscriberId = $_POST['subscriber_id'] ?? 0;
        $planId = $_POST['plan_id'] ?? 0;
        $reason = $_POST['reason'] ?? 'Admin override';
        
        // Update subscription
        $this->db->query(
            "UPDATE mail_subscriptions 
             SET plan_id = ?
             WHERE subscriber_id = ?",
            [$planId, $subscriberId]
        );
        
        // Log admin action
        $this->logAdminAction('override_plan', 'subscriber', $subscriberId, "Changed plan to ID $planId. Reason: $reason");
        
        $this->jsonSuccess('Plan updated successfully');
    }
    
    /**
     * Toggle Feature for Subscriber
     */
    public function toggleFeature()
    {
        $subscriberId = $_POST['subscriber_id'] ?? 0;
        $featureKey = $_POST['feature_key'] ?? '';
        $isEnabled = $_POST['is_enabled'] ?? 0;
        $reason = $_POST['reason'] ?? 'Admin override';
        
        // Check if override exists
        $existing = $this->db->fetch(
            "SELECT id FROM mail_feature_access 
             WHERE subscriber_id = ? AND feature_key = ?",
            [$subscriberId, $featureKey]
        );
        
        if ($existing) {
            // Update
            $this->db->query(
                "UPDATE mail_feature_access 
                 SET is_enabled = ?, override_by_admin = 1, override_reason = ?
                 WHERE id = ?",
                [$isEnabled, $reason, $existing['id']]
            );
        } else {
            // Insert
            $this->db->query(
                "INSERT INTO mail_feature_access (subscriber_id, feature_key, is_enabled, override_by_admin, override_reason, created_at)
                 VALUES (?, ?, ?, 1, ?, NOW())",
                [$subscriberId, $featureKey, $isEnabled, $reason]
            );
        }
        
        // Log admin action
        $action = $isEnabled ? 'enabled' : 'disabled';
        $this->logAdminAction('toggle_feature', 'subscriber', $subscriberId, "Feature '$featureKey' $action. Reason: $reason");
        
        $this->jsonSuccess('Feature updated successfully');
    }
    
    /**
     * Manage Subscription Plans
     */
    public function plans()
    {
        $plans = $this->db->query(
            "SELECT sp.*, COUNT(sub.id) as active_subscriptions
             FROM mail_subscription_plans sp
             LEFT JOIN mail_subscriptions sub ON sp.id = sub.plan_id AND sub.status = 'active'
             GROUP BY sp.id
             ORDER BY sp.sort_order"
        )->fetchAll();
        
        $this->view('admin/mail/plans', [
            'plans' => $plans,
            'title' => 'Subscription Plans'
        ]);
    }
    
    /**
     * Edit Plan
     */
    public function editPlan($id)
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->updatePlan($id);
        }
        
        $plan = $this->db->fetch("SELECT * FROM mail_subscription_plans WHERE id = ?", [$id]);
        
        if (!$plan) {
            $this->error('Plan not found');
            redirect('/admin/projects/mail/plans');
            return;
        }
        
        // Get plan features
        $features = $this->db->query(
            "SELECT * FROM mail_plan_features WHERE plan_id = ? ORDER BY feature_key",
            [$id]
        )->fetchAll();
        
        $this->view('admin/mail/edit-plan', [
            'plan' => $plan,
            'features' => $features,
            'title' => 'Edit Plan - ' . $plan['plan_name']
        ]);
    }
    
    /**
     * Update Plan
     */
    private function updatePlan($id)
    {
        $planName = $_POST['plan_name'] ?? '';
        $priceMonthly = $_POST['price_monthly'] ?? 0;
        $priceYearly = $_POST['price_yearly'] ?? 0;
        $currency = $_POST['currency'] ?? 'USD';
        $maxUsers = $_POST['max_users'] ?? 1;
        $storagePerUserGb = $_POST['storage_per_user_gb'] ?? 1;
        $dailySendLimit = $_POST['daily_send_limit'] ?? 100;
        $maxAttachmentSizeMb = $_POST['max_attachment_size_mb'] ?? 10;
        $maxDomains = $_POST['max_domains'] ?? 1;
        $maxAliases = $_POST['max_aliases'] ?? 5;
        $description = $_POST['description'] ?? '';
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        
        $this->db->query(
            "UPDATE mail_subscription_plans 
             SET plan_name = ?, price_monthly = ?, price_yearly = ?, currency = ?, max_users = ?,
                 storage_per_user_gb = ?, daily_send_limit = ?, max_attachment_size_mb = ?,
                 max_domains = ?, max_aliases = ?, description = ?, is_active = ?, updated_at = NOW()
             WHERE id = ?",
            [$planName, $priceMonthly, $priceYearly, $currency, $maxUsers, $storagePerUserGb, 
             $dailySendLimit, $maxAttachmentSizeMb, $maxDomains, $maxAliases, $description, $isActive, $id]
        );
        
        // Update features if provided
        if (isset($_POST['features'])) {
            foreach ($_POST['features'] as $featureKey => $isEnabled) {
                $this->db->query(
                    "UPDATE mail_plan_features 
                     SET is_enabled = ?
                     WHERE plan_id = ? AND feature_key = ?",
                    [$isEnabled ? 1 : 0, $id, $featureKey]
                );
            }
        }
        
        // Log admin action
        $this->logAdminAction('update_plan', 'plan', $id, "Updated plan: $planName");
        
        $this->flash('success', 'Plan updated successfully');
        $this->redirect('/admin/projects/mail/plans');
    }
    
    /**
     * View All Domains
     */
    public function domains()
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $domains = $this->db->query(
            "SELECT d.*, 
                    s.account_name as subscriber_name,
                    s.account_name as username
             FROM mail_domains d
             LEFT JOIN mail_subscribers s ON d.subscriber_id = s.id
             ORDER BY d.created_at DESC
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        )->fetchAll();
        
        $totalCount = $this->db->fetch("SELECT COUNT(*) as count FROM mail_domains")['count'];
        $totalPages = ceil($totalCount / $perPage);
        
        $this->view('admin/mail/domains', [
            'domains' => $domains,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'title' => 'All Domains'
        ]);
    }
    
    /**
     * Edit Domain
     */
    public function editDomain($id)
    {
        $domain = $this->db->fetch("SELECT * FROM mail_domains WHERE id = ?", [$id]);
        
        if (!$domain) {
            $this->flash('error', 'Domain not found');
            $this->redirect('/admin/projects/mail/domains');
        }
        
        $subscriber = $this->db->fetch("SELECT * FROM mail_subscribers WHERE id = ?", [$domain['subscriber_id']]);
        
        $this->view('admin/mail/edit-domain', [
            'domain' => $domain,
            'subscriber' => $subscriber,
            'title' => 'Edit Domain - ' . $domain['domain_name']
        ]);
    }
    
    /**
     * Update Domain
     */
    public function updateDomain($id)
    {
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $catchAllEmail = $_POST['catch_all_email'] ?? null;
        $description = $_POST['description'] ?? '';
        
        $this->db->query(
            "UPDATE mail_domains 
             SET is_active = ?, catch_all_email = ?, description = ?, updated_at = NOW()
             WHERE id = ?",
            [$isActive, $catchAllEmail, $description, $id]
        );
        
        $domain = $this->db->fetch("SELECT domain_name FROM mail_domains WHERE id = ?", [$id]);
        $this->logAdminAction('update_domain', 'domain', $id, "Updated domain: " . $domain['domain_name']);
        
        $this->flash('success', 'Domain updated successfully');
        $this->redirect('/admin/projects/mail/domains');
    }
    
    /**
     * Activate Domain
     */
    public function activateDomain($id)
    {
        $this->db->query("UPDATE mail_domains SET is_active = 1, updated_at = NOW() WHERE id = ?", [$id]);
        
        $domain = $this->db->fetch("SELECT domain_name FROM mail_domains WHERE id = ?", [$id]);
        $this->logAdminAction('activate_domain', 'domain', $id, "Activated domain: " . $domain['domain_name']);
        
        $this->json(['success' => true, 'message' => 'Domain activated successfully']);
    }
    
    /**
     * Suspend Domain
     */
    public function suspendDomain($id)
    {
        $this->db->query("UPDATE mail_domains SET is_active = 0, updated_at = NOW() WHERE id = ?", [$id]);
        
        $domain = $this->db->fetch("SELECT domain_name FROM mail_domains WHERE id = ?", [$id]);
        $this->logAdminAction('suspend_domain', 'domain', $id, "Suspended domain: " . $domain['domain_name']);
        
        $this->json(['success' => true, 'message' => 'Domain suspended successfully']);
    }
    
    /**
     * View Abuse Reports
     */
    public function abuseReports()
    {
        $status = $_GET['status'] ?? 'pending';
        
        // Get statistics
        $stats = [
            'pending' => $this->db->fetch("SELECT COUNT(*) as count FROM mail_abuse_reports WHERE status = 'pending'")['count'] ?? 0,
            'investigating' => $this->db->fetch("SELECT COUNT(*) as count FROM mail_abuse_reports WHERE status = 'investigating'")['count'] ?? 0,
            'resolved' => $this->db->fetch("SELECT COUNT(*) as count FROM mail_abuse_reports WHERE status = 'resolved'")['count'] ?? 0,
            'dismissed' => $this->db->fetch("SELECT COUNT(*) as count FROM mail_abuse_reports WHERE status = 'dismissed'")['count'] ?? 0,
        ];
        
        $reports = $this->db->query(
            "SELECT ar.*, 
                    m.email as mailbox_email,
                    d.domain_name,
                    s.account_name
             FROM mail_abuse_reports ar
             LEFT JOIN mail_mailboxes m ON ar.reported_mailbox_id = m.id
             LEFT JOIN mail_domains d ON ar.reported_domain_id = d.id
             LEFT JOIN mail_subscribers s ON (m.subscriber_id = s.id OR d.subscriber_id = s.id)
             WHERE ar.status = ?
             ORDER BY ar.created_at DESC",
            [$status]
        )->fetchAll();
        
        $this->view('admin/mail/abuse-reports', [
            'reports' => $reports,
            'currentStatus' => $status,
            'stats' => $stats,
            'title' => 'Abuse Reports'
        ]);
    }
    
    /**
     * Handle Abuse Report
     */
    public function handleAbuseReport()
    {
        $reportId = $_POST['report_id'] ?? 0;
        $action = $_POST['action'] ?? 'investigating'; // investigating, resolved, dismissed
        $actionTaken = $_POST['action_taken'] ?? '';
        
        $status = match($action) {
            'investigating' => 'investigating',
            'resolved' => 'resolved',
            'dismissed' => 'dismissed',
            default => 'pending'
        };
        
        $this->db->query(
            "UPDATE mail_abuse_reports 
             SET status = ?, action_taken = ?, handled_by_admin_id = ?, resolved_at = NOW()
             WHERE id = ?",
            [$status, $actionTaken, Auth::id(), $reportId]
        );
        
        // Log admin action
        $this->logAdminAction('handle_abuse', 'abuse_report', $reportId, "Status: $status. Action: $actionTaken");
        
        $this->jsonSuccess('Abuse report updated successfully');
    }
    
    /**
     * System Settings
     */
    public function settings()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->saveSettings();
        }
        
        // Get all settings
        $settings = $this->db->query("SELECT * FROM mail_system_settings ORDER BY setting_key")->fetchAll();
        
        $settingsArray = [];
        foreach ($settings as $setting) {
            $settingsArray[$setting['setting_key']] = $setting['setting_value'];
        }
        
        $this->view('admin/mail/settings', [
            'settings' => $settingsArray,
            'title' => 'Mail Server Settings'
        ]);
    }
    
    /**
     * Save System Settings
     */
    private function saveSettings()
    {
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'setting_') === 0) {
                $settingKey = substr($key, 8); // Remove 'setting_' prefix
                
                // Check if exists
                $existing = $this->db->fetch(
                    "SELECT id FROM mail_system_settings WHERE setting_key = ?",
                    [$settingKey]
                );
                
                if ($existing) {
                    $this->db->query(
                        "UPDATE mail_system_settings SET setting_value = ?, updated_at = NOW() WHERE setting_key = ?",
                        [$value, $settingKey]
                    );
                } else {
                    $this->db->query(
                        "INSERT INTO mail_system_settings (setting_key, setting_value, created_at) VALUES (?, ?, NOW())",
                        [$settingKey, $value]
                    );
                }
            }
        }
        
        // Log admin action
        $this->logAdminAction('update_settings', 'system', 0, "Updated system settings");
        
        $this->flash('success', 'Settings saved successfully');
        $this->redirect('/admin/projects/mail/settings');
    }
    
    /**
     * View Admin Action Logs
     */
    public function logs()
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 50;
        $offset = ($page - 1) * $perPage;
        
        $logs = $this->db->query(
            "SELECT aa.*, 
                    aa.admin_user_id,
                    'Admin' as username
             FROM mail_admin_actions aa
             ORDER BY aa.created_at DESC
             LIMIT ? OFFSET ?",
            [$perPage, $offset]
        )->fetchAll();
        
        $totalCount = $this->db->fetch("SELECT COUNT(*) as count FROM mail_admin_actions")['count'];
        $totalPages = ceil($totalCount / $perPage);
        
        $this->view('admin/mail/logs', [
            'logs' => $logs,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'title' => 'Admin Action Logs'
        ]);
    }
    
    /**
     * Log Admin Action
     */
    private function logAdminAction($actionType, $targetType, $targetId, $description)
    {
        $this->db->query(
            "INSERT INTO mail_admin_actions (admin_user_id, action_type, target_type, target_id, action_description, ip_address, user_agent, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
            [Auth::id(), $actionType, $targetType, $targetId, $description, $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null]
        );
    }
    
    /**
     * Helper methods
     */
    private function success($message)
    {
        $_SESSION['success_message'] = $message;
    }
    
    private function error($message)
    {
        $_SESSION['error_message'] = $message;
    }
    
    private function jsonSuccess($message, $data = [])
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'message' => $message, 'data' => $data]);
        exit;
    }
    
    private function jsonError($message)
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}
