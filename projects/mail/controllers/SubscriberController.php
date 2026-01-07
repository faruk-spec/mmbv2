<?php
/**
 * Subscriber Dashboard Controller
 * Handles subscriber owner functionality - managing users, domains, and subscription
 * 
 * @package MMB\Projects\Mail\Controllers
 */

namespace Mail;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\View;
use Mail\MailHelpers;

class SubscriberController extends BaseController
{
    private $db;
    private $subscriberId;
    private $isOwner;
    
    public function __construct()
    {
        parent::__construct();
        
        // Ensure user is authenticated
        if (!Auth::check()) {
            $this->redirect('/login');
        }
        
        $this->db = Database::getInstance();
        
        // Get subscriber info for current user
        $this->loadSubscriberInfo();
    }
    
    /**
     * Load subscriber information for current user
     */
    private function loadSubscriberInfo()
    {
        $userId = Auth::id();
        
        // Check if user is a subscriber owner
        $subscriber = $this->db->fetch(
            "SELECT s.*, sub.plan_id, sp.plan_name, sp.max_users, sp.storage_per_user_gb
             FROM mail_subscribers s
             JOIN mail_subscriptions sub ON s.id = sub.subscriber_id
             JOIN mail_subscription_plans sp ON sub.plan_id = sp.id
             WHERE s.mmb_user_id = ? AND sub.status = 'active'
             LIMIT 1",
            [$userId]
        );
        
        if ($subscriber) {
            $this->subscriberId = $subscriber['id'];
            $this->isOwner = true;
        } else {
            // Check if user is a domain admin or end user
            $role = $this->db->fetch(
                "SELECT subscriber_id, role_type FROM mail_user_roles 
                 WHERE mmb_user_id = ? LIMIT 1",
                [$userId]
            );
            
            if ($role) {
                $this->subscriberId = $role['subscriber_id'];
                $this->isOwner = false;
            }
        }
    }
    
    /**
     * Subscriber Dashboard
     */
    public function dashboard()
    {
        if (!$this->isOwner) {
            $this->flash('error', 'Access denied. Subscriber owner access required.');
            $this->redirect('/projects/mail');
            return;
        }
        
        $userId = Auth::id();
        
        // Get subscriber details with subscription info
        $subscriber = $this->db->fetch(
            "SELECT s.*, sub.plan_id, sub.status as subscription_status, 
                    sp.plan_name, sp.max_users, sp.storage_per_user_gb, sp.max_domains,
                    sp.daily_send_limit, sp.max_aliases
             FROM mail_subscribers s
             JOIN mail_subscriptions sub ON s.id = sub.subscriber_id
             JOIN mail_subscription_plans sp ON sub.plan_id = sp.id
             WHERE s.id = ?",
            [$this->subscriberId]
        );
        
        // Get usage statistics
        $stats = [
            'users_count' => $this->db->fetch("SELECT COUNT(*) as count FROM mail_mailboxes WHERE subscriber_id = ?", [$this->subscriberId])['count'] ?? 0,
            'domains_count' => $this->db->fetch("SELECT COUNT(*) as count FROM mail_domains WHERE subscriber_id = ?", [$this->subscriberId])['count'] ?? 0,
            'aliases_count' => $this->db->fetch("SELECT COUNT(*) as count FROM mail_aliases a JOIN mail_domains d ON a.domain_id = d.id WHERE d.subscriber_id = ?", [$this->subscriberId])['count'] ?? 0,
            'emails_today' => $this->db->fetch("SELECT COUNT(*) as count FROM mail_logs WHERE mailbox_id IN (SELECT id FROM mail_mailboxes WHERE subscriber_id = ?) AND DATE(created_at) = CURDATE() AND log_type = 'send'", [$this->subscriberId])['count'] ?? 0,
        ];
        
        // Get recent users
        $recentUsers = $this->db->query(
            "SELECT m.*, d.domain_name 
             FROM mail_mailboxes m
             JOIN mail_domains d ON m.domain_id = d.id
             WHERE m.subscriber_id = ?
             ORDER BY m.created_at DESC
             LIMIT 5",
            [$this->subscriberId]
        )->fetchAll();
        
        // Get recent domains
        $recentDomains = $this->db->query(
            "SELECT * FROM mail_domains 
             WHERE subscriber_id = ?
             ORDER BY created_at DESC
             LIMIT 5",
            [$this->subscriberId]
        )->fetchAll();
        
        View::render('mail/subscriber/dashboard', [
            'subscriber' => $subscriber,
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentDomains' => $recentDomains,
            'title' => 'Subscriber Dashboard'
        ]);
    }
    
    /**
     * Manage Users - List all users under subscription
     */
    public function manageUsers()
    {
        if (!$this->isOwner) {
            $this->flash('error', 'Access denied. Subscriber owner access required.');
            $this->redirect('/projects/mail');
            return;
        }
        
        // Get plan limits
        $plan = $this->db->fetch(
            "SELECT sp.max_users, s.users_count
             FROM mail_subscribers s
             JOIN mail_subscriptions sub ON s.id = sub.subscriber_id
             JOIN mail_subscription_plans sp ON sub.plan_id = sp.id
             WHERE s.id = ?",
            [$this->subscriberId]
        );
        
        // Get all users
        $users = $this->db->query(
            "SELECT m.*, d.domain_name, 
                    CASE WHEN m.mmb_user_id IS NOT NULL THEN 'Linked' ELSE 'Mail Only' END as user_type
             FROM mail_mailboxes m
             JOIN mail_domains d ON m.domain_id = d.id
             WHERE m.subscriber_id = ?
             ORDER BY m.created_at DESC",
            [$this->subscriberId]
        )->fetchAll();
        
        View::render('mail/subscriber/manage-users', [
            'users' => $users,
            'plan' => $plan,
            'canAddMore' => ($plan['users_count'] < $plan['max_users']),
            'title' => 'Manage Users'
        ]);
    }
    
    /**
     * Add New User
     */
    public function addUser()
    {
        if (!$this->isOwner) {
            $this->flash('error', 'Access denied. Subscriber owner access required.');
            $this->redirect('/projects/mail');
            return;
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            return $this->storeUser();
        }
        
        // Get plan limits
        $plan = $this->db->fetch(
            "SELECT sp.max_users, s.users_count
             FROM mail_subscribers s
             JOIN mail_subscriptions sub ON s.id = sub.subscriber_id
             JOIN mail_subscription_plans sp ON sub.plan_id = sp.id
             WHERE s.id = ?",
            [$this->subscriberId]
        );
        
        // Check if can add more users
        if ($plan['users_count'] >= $plan['max_users']) {
            $this->flash('error', 'User limit reached. Please upgrade your plan to add more users.');
            $this->redirect('/projects/mail/subscriber/users');
            return;
        }
        
        // Get domains for dropdown
        $domains = $this->db->query(
            "SELECT * FROM mail_domains 
             WHERE subscriber_id = ? AND is_verified = 1 AND is_active = 1
             ORDER BY domain_name",
            [$this->subscriberId]
        )->fetchAll();
        
        View::render('mail/subscriber/add-user', [
            'domains' => $domains,
            'title' => 'Add New User'
        ]);
    }
    
    /**
     * Store New User
     */
    private function storeUser()
    {
        // Validate input
        $email = $_POST['email'] ?? '';
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $displayName = $_POST['display_name'] ?? '';
        $domainId = $_POST['domain_id'] ?? 0;
        $roleType = $_POST['role_type'] ?? 'end_user';
        $storageQuota = $_POST['storage_quota'] ?? 1073741824; // 1GB default
        
        // Validate
        if (!MailHelpers::isValidEmail($email)) {
            $this->flash('error', 'Invalid email address');
            $this->redirect('/projects/mail/subscriber/users/add');
            return;
        }
        
        // Check plan limits
        $plan = $this->db->fetch(
            "SELECT sp.max_users, s.users_count
             FROM mail_subscribers s
             JOIN mail_subscriptions sub ON s.id = sub.subscriber_id
             JOIN mail_subscription_plans sp ON sub.plan_id = sp.id
             WHERE s.id = ?",
            [$this->subscriberId]
        );
        
        if ($plan['users_count'] >= $plan['max_users']) {
            $this->flash('error', 'User limit reached for your plan');
            $this->redirect('/projects/mail/subscriber/users/add');
            return;
        }
        
        // Check if email already exists
        $existing = $this->db->fetch("SELECT id FROM mail_mailboxes WHERE email = ?", [$email]);
        if ($existing) {
            $this->flash('error', 'Email address already exists');
            $this->redirect('/projects/mail/subscriber/users/add');
            return;
        }
        
        // Verify domain belongs to subscriber
        $domain = $this->db->fetch(
            "SELECT id FROM mail_domains WHERE id = ? AND subscriber_id = ?",
            [$domainId, $this->subscriberId]
        );
        
        if (!$domain) {
            $this->flash('error', 'Invalid domain selected');
            $this->redirect('/projects/mail/subscriber/users/add');
            return;
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_ARGON2ID);
        
        // Insert mailbox
        try {
            $this->db->query(
                "INSERT INTO mail_mailboxes (subscriber_id, domain_id, email, username, password, display_name, role_type, added_by_user_id, storage_quota, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [$this->subscriberId, $domainId, $email, $username, $hashedPassword, $displayName, $roleType, Auth::id(), $storageQuota]
            );
            
            // Update users count
            $this->db->query(
                "UPDATE mail_subscribers SET users_count = users_count + 1 WHERE id = ?",
                [$this->subscriberId]
            );
            
            // Create default folders for the new mailbox
            $mailboxId = $this->db->getConnection()->lastInsertId();
            $this->createDefaultFolders($mailboxId);
            
            $this->flash('success', 'User created successfully');
            $this->redirect('/projects/mail/subscriber/users');
            
        } catch (\Exception $e) {
            $this->flash('error', 'Failed to create user: ' . $e->getMessage());
            $this->redirect('/projects/mail/subscriber/users/add');
        }
    }
    
    /**
     * Create default folders for mailbox
     */
    private function createDefaultFolders($mailboxId)
    {
        $folders = [
            ['inbox', 'Inbox', 1],
            ['sent', 'Sent', 2],
            ['drafts', 'Drafts', 3],
            ['trash', 'Trash', 4],
            ['spam', 'Spam', 5],
            ['archive', 'Archive', 6]
        ];
        
        foreach ($folders as $folder) {
            $this->db->query(
                "INSERT INTO mail_folders (mailbox_id, folder_name, folder_type, sort_order, created_at)
                 VALUES (?, ?, ?, ?, NOW())",
                [$mailboxId, $folder[1], $folder[0], $folder[2]]
            );
        }
    }
    
    /**
     * Assign Role to User
     */
    public function assignRole()
    {
        if (!$this->isOwner) {
            $this->jsonError('Access denied');
            return;
        }
        
        $mailboxId = $_POST['mailbox_id'] ?? 0;
        $roleType = $_POST['role_type'] ?? 'end_user';
        
        // Verify mailbox belongs to subscriber
        $mailbox = $this->db->fetch(
            "SELECT id FROM mail_mailboxes WHERE id = ? AND subscriber_id = ?",
            [$mailboxId, $this->subscriberId]
        );
        
        if (!$mailbox) {
            $this->jsonError('Invalid mailbox');
            return;
        }
        
        // Update role
        $this->db->query(
            "UPDATE mail_mailboxes SET role_type = ? WHERE id = ?",
            [$roleType, $mailboxId]
        );
        
        $this->jsonSuccess('Role updated successfully');
    }
    
    /**
     * Delete User
     */
    public function deleteUser()
    {
        if (!$this->isOwner) {
            $this->jsonError('Access denied');
            return;
        }
        
        $mailboxId = $_POST['mailbox_id'] ?? 0;
        
        // Verify mailbox belongs to subscriber
        $mailbox = $this->db->fetch(
            "SELECT id, role_type FROM mail_mailboxes WHERE id = ? AND subscriber_id = ?",
            [$mailboxId, $this->subscriberId]
        );
        
        if (!$mailbox) {
            $this->jsonError('Invalid mailbox');
            return;
        }
        
        // Don't allow deleting subscriber owner's own mailbox
        if ($mailbox['role_type'] === 'subscriber_owner') {
            $this->jsonError('Cannot delete subscriber owner mailbox');
            return;
        }
        
        // Delete mailbox (cascades to folders, messages, etc.)
        $this->db->query("DELETE FROM mail_mailboxes WHERE id = ?", [$mailboxId]);
        
        // Update users count
        $this->db->query(
            "UPDATE mail_subscribers SET users_count = users_count - 1 WHERE id = ?",
            [$this->subscriberId]
        );
        
        $this->jsonSuccess('User deleted successfully');
    }
    
    /**
     * Show subscription page (for new users to subscribe)
     */
    public function subscribe()
    {
        // Get all available plans
        $plans = $this->db->fetchAll(
            "SELECT * FROM mail_subscription_plans WHERE is_active = 1 ORDER BY sort_order ASC"
        );
        
        View::render('mail/subscribe', [
            'plans' => $plans,
            'title' => 'Subscribe to Mail Hosting'
        ]);
    }
    
    /**
     * Process new subscription
     */
    public function processSubscription()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/projects/mail/subscribe');
            return;
        }
        
        $planId = intval($_POST['plan_id'] ?? 0);
        $billingCycle = $_POST['billing_cycle'] ?? 'monthly';
        
        // Validate plan
        $plan = $this->db->fetch(
            "SELECT * FROM mail_subscription_plans WHERE id = ? AND is_active = 1",
            [$planId]
        );
        
        if (!$plan) {
            $this->flash('error', 'Invalid plan selected');
            $this->redirect('/projects/mail/subscribe');
            return;
        }
        
        $userId = Auth::id();
        
        // Check if user already has a subscription
        $existing = $this->db->fetch(
            "SELECT id FROM mail_subscribers WHERE mmb_user_id = ?",
            [$userId]
        );
        
        if ($existing) {
            $this->flash('error', 'You already have an active subscription');
            $this->redirect('/projects/mail/subscriber/dashboard');
            return;
        }
        
        // Create subscriber
        $this->db->query(
            "INSERT INTO mail_subscribers (mmb_user_id, account_name, status, created_at)
             VALUES (?, ?, 'active', NOW())",
            [$userId, Auth::user()->name ?? 'Account']
        );
        
        $subscriberId = $this->db->getConnection()->lastInsertId();
        
        // Create subscription
        $this->db->query(
            "INSERT INTO mail_subscriptions (subscriber_id, plan_id, status, billing_cycle, 
                                            current_period_start, current_period_end, created_at)
             VALUES (?, ?, 'active', ?, NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH), NOW())",
            [$subscriberId, $planId, $billingCycle]
        );
        
        $this->flash('success', 'Subscription created successfully!');
        $this->redirect('/projects/mail/subscriber/dashboard');
    }
    
    /**
     * Show subscription details
     */
    public function subscription()
    {
        if (!$this->isOwner) {
            $this->flash('error', 'Access denied. Subscriber owner access required.');
            $this->redirect('/projects/mail');
            return;
        }
        
        // Get subscription details
        $subscription = $this->db->fetch(
            "SELECT s.*, sub.*, sp.plan_name, sp.price_monthly, sp.price_yearly
             FROM mail_subscribers s
             JOIN mail_subscriptions sub ON s.id = sub.subscriber_id
             JOIN mail_subscription_plans sp ON sub.plan_id = sp.id
             WHERE s.id = ?",
            [$this->subscriberId]
        );
        
        // Get all available plans for upgrade/downgrade
        $plans = $this->db->fetchAll(
            "SELECT * FROM mail_subscription_plans WHERE is_active = 1 ORDER BY sort_order ASC"
        );
        
        View::render('mail/subscriber/subscription', [
            'subscription' => $subscription,
            'plans' => $plans,
            'title' => 'Subscription Details'
        ]);
    }
    
    /**
     * Show billing history
     */
    public function billing()
    {
        if (!$this->isOwner) {
            $this->flash('error', 'Access denied. Subscriber owner access required.');
            $this->redirect('/projects/mail');
            return;
        }
        
        // Get billing history
        $billingHistory = $this->db->fetchAll(
            "SELECT * FROM mail_billing_history 
             WHERE subscriber_id = ? 
             ORDER BY created_at DESC 
             LIMIT 50",
            [$this->subscriberId]
        );
        
        // Get subscription details
        $subscription = $this->db->fetch(
            "SELECT s.*, sub.*, sp.plan_name, sp.price_monthly, sp.price_yearly
             FROM mail_subscribers s
             JOIN mail_subscriptions sub ON s.id = sub.subscriber_id
             JOIN mail_subscription_plans sp ON sub.plan_id = sp.id
             WHERE s.id = ?",
            [$this->subscriberId]
        );
        
        View::render('mail/subscriber/billing', [
            'billingHistory' => $billingHistory,
            'subscription' => $subscription,
            'title' => 'Billing History'
        ]);
    }
    
    /**
     * Show upgrade plan page
     */
    public function showUpgrade()
    {
        if (!$this->isOwner) {
            $this->flash('error', 'Access denied. Subscriber owner access required.');
            $this->redirect('/projects/mail');
            return;
        }
        
        // Check if plan parameter is provided (direct upgrade link)
        $planParam = isset($_GET['plan']) ? intval($_GET['plan']) : 0;
        
        if ($planParam > 0) {
            // Direct upgrade - process immediately
            return $this->processDirectUpgrade($planParam);
        }
        
        // Get current subscription
        $subscription = $this->db->fetch(
            "SELECT s.*, sub.*, sp.plan_name, sp.price_monthly, sp.price_yearly, sp.sort_order
             FROM mail_subscribers s
             JOIN mail_subscriptions sub ON s.id = sub.subscriber_id
             JOIN mail_subscription_plans sp ON sub.plan_id = sp.id
             WHERE s.id = ?",
            [$this->subscriberId]
        );
        
        // Get available upgrade plans (plans with higher sort_order)
        $plans = $this->db->fetchAll(
            "SELECT * FROM mail_subscription_plans 
             WHERE is_active = 1 AND sort_order > ? 
             ORDER BY sort_order ASC",
            [$subscription['sort_order'] ?? 0]
        );
        
        View::render('mail/subscriber/upgrade', [
            'currentSubscription' => $subscription,
            'plans' => $plans,
            'title' => 'Upgrade Plan'
        ]);
    }
    
    /**
     * Process direct upgrade from GET parameter
     */
    private function processDirectUpgrade($planId)
    {
        // Validate plan
        $plan = $this->db->fetch(
            "SELECT * FROM mail_subscription_plans WHERE id = ? AND is_active = 1",
            [$planId]
        );
        
        if (!$plan) {
            $this->flash('error', 'Invalid plan selected');
            $this->redirect('/projects/mail/subscriber/upgrade');
            return;
        }
        
        // Get current subscription
        $currentSubscription = $this->db->fetch(
            "SELECT * FROM mail_subscriptions WHERE subscriber_id = ? AND status = 'active'",
            [$this->subscriberId]
        );
        
        if (!$currentSubscription) {
            $this->flash('error', 'No active subscription found');
            $this->redirect('/projects/mail/subscribe');
            return;
        }
        
        // Update subscription
        $this->db->query(
            "UPDATE mail_subscriptions SET plan_id = ?, updated_at = NOW() WHERE id = ?",
            [$planId, $currentSubscription['id']]
        );
        
        // Log the upgrade
        try {
            $this->db->query(
                "INSERT INTO mail_billing_history (subscriber_id, subscription_id, transaction_type, 
                                                   amount, description, created_at)
                 VALUES (?, ?, 'upgrade', ?, ?, NOW())",
                [
                    $this->subscriberId,
                    $currentSubscription['id'],
                    $plan['price_monthly'],
                    "Upgraded to {$plan['plan_name']} plan"
                ]
            );
        } catch (\Exception $e) {
            // If billing history insert fails, log but continue
            error_log("Failed to log upgrade to billing history: " . $e->getMessage());
        }
        
        $this->flash('success', "Successfully upgraded to {$plan['plan_name']} plan!");
        $this->redirect('/projects/mail/subscriber/dashboard');
    }
    
    /**
     * Process plan upgrade
     */
    public function upgradePlan()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/projects/mail/subscriber/upgrade');
            return;
        }
        
        if (!$this->isOwner) {
            $this->flash('error', 'Access denied. Subscriber owner access required.');
            $this->redirect('/projects/mail');
            return;
        }
        
        $newPlanId = intval($_POST['plan_id'] ?? 0);
        
        // Validate plan
        $plan = $this->db->fetch(
            "SELECT * FROM mail_subscription_plans WHERE id = ? AND is_active = 1",
            [$newPlanId]
        );
        
        if (!$plan) {
            $this->flash('error', 'Invalid plan selected');
            $this->redirect('/projects/mail/subscriber/upgrade');
            return;
        }
        
        // Get current subscription
        $currentSubscription = $this->db->fetch(
            "SELECT * FROM mail_subscriptions WHERE subscriber_id = ? AND status = 'active'",
            [$this->subscriberId]
        );
        
        if (!$currentSubscription) {
            $this->flash('error', 'No active subscription found');
            $this->redirect('/projects/mail/subscribe');
            return;
        }
        
        // Update subscription
        $this->db->query(
            "UPDATE mail_subscriptions SET plan_id = ?, updated_at = NOW() WHERE id = ?",
            [$newPlanId, $currentSubscription['id']]
        );
        
        // Log the upgrade
        try {
            $this->db->query(
                "INSERT INTO mail_billing_history (subscriber_id, subscription_id, transaction_type, 
                                                   amount, description, created_at)
                 VALUES (?, ?, 'upgrade', ?, ?, NOW())",
                [
                    $this->subscriberId,
                    $currentSubscription['id'],
                    $plan['price_monthly'],
                    "Upgraded to {$plan['plan_name']} plan"
                ]
            );
        } catch (\Exception $e) {
            // If billing history insert fails, log but continue
            error_log("Failed to log upgrade to billing history: " . $e->getMessage());
        }
        
        $this->flash('success', "Successfully upgraded to {$plan['plan_name']} plan!");
        $this->redirect('/projects/mail/subscriber/dashboard');
    }
    
    /**
     * Process plan downgrade
     */
    public function downgradePlan()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/projects/mail/subscriber/subscription');
            return;
        }
        
        if (!$this->isOwner) {
            $this->flash('error', 'Access denied. Subscriber owner access required.');
            $this->redirect('/projects/mail');
            return;
        }
        
        $newPlanId = intval($_POST['plan_id'] ?? 0);
        
        // Validate plan
        $plan = $this->db->fetch(
            "SELECT * FROM mail_subscription_plans WHERE id = ? AND is_active = 1",
            [$newPlanId]
        );
        
        if (!$plan) {
            $this->flash('error', 'Invalid plan selected');
            $this->redirect('/projects/mail/subscriber/subscription');
            return;
        }
        
        // Get current subscription
        $currentSubscription = $this->db->fetch(
            "SELECT * FROM mail_subscriptions WHERE subscriber_id = ? AND status = 'active'",
            [$this->subscriberId]
        );
        
        if (!$currentSubscription) {
            $this->flash('error', 'No active subscription found');
            $this->redirect('/projects/mail/subscribe');
            return;
        }
        
        // Update subscription
        $this->db->query(
            "UPDATE mail_subscriptions SET plan_id = ?, updated_at = NOW() WHERE id = ?",
            [$newPlanId, $currentSubscription['id']]
        );
        
        // Log the downgrade
        try {
            $this->db->query(
                "INSERT INTO mail_billing_history (subscriber_id, subscription_id, transaction_type, 
                                                   amount, description, created_at)
                 VALUES (?, ?, 'downgrade', ?, ?, NOW())",
                [
                    $this->subscriberId,
                    $currentSubscription['id'],
                    $plan['price_monthly'],
                    "Downgraded to {$plan['plan_name']} plan"
                ]
            );
        } catch (\Exception $e) {
            // If billing history insert fails, log but continue
            error_log("Failed to log downgrade to billing history: " . $e->getMessage());
        }
        
        $this->flash('success', "Successfully downgraded to {$plan['plan_name']} plan!");
        $this->redirect('/projects/mail/subscriber/dashboard');
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
