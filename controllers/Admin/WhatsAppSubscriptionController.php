<?php
/**
 * WhatsApp Subscription Admin Controller
 * 
 * Manages subscription plans and user subscriptions
 * 
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Core\Auth;
use Core\View;
use Core\Database;
use Core\Security;

class WhatsAppSubscriptionController
{
    private $db;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        
        // Check if user is admin
        if (!Auth::isAdmin()) {
            header('Location: /dashboard');
            exit;
        }
    }
    
    /**
     * List all subscription plans
     */
    public function plans()
    {
        $plans = $this->db->fetchAll("
            SELECT * FROM whatsapp_subscription_plans 
            ORDER BY price ASC
        ");
        
        $stats = [
            'totalPlans' => count($plans),
            'activePlans' => count(array_filter($plans, fn($p) => $p['is_active'] == 1)),
            'totalSubscriptions' => $this->db->fetchColumn("SELECT COUNT(*) FROM whatsapp_subscriptions"),
            'activeSubscriptions' => $this->db->fetchColumn("SELECT COUNT(*) FROM whatsapp_subscriptions WHERE status = 'active'")
        ];
        
        View::render('admin/projects/whatsapp/subscription-plans', [
            'plans' => $plans,
            'stats' => $stats,
            'pageTitle' => 'Subscription Plans - WhatsApp Admin'
        ]);
    }
    
    /**
     * Show create plan form
     */
    public function createPlanForm()
    {
        View::render('admin/projects/whatsapp/subscription-plan-form', [
            'plan' => null,
            'pageTitle' => 'Create Subscription Plan - WhatsApp Admin'
        ]);
    }
    
    /**
     * Create new subscription plan
     */
    public function createPlan()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/whatsapp/subscription-plans');
            exit;
        }
        
        // Verify CSRF token
        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid security token';
            header('Location: /admin/whatsapp/subscription-plans/create');
            exit;
        }
        
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $currency = trim($_POST['currency'] ?? 'USD');
        $messages_limit = intval($_POST['messages_limit'] ?? 0);
        $sessions_limit = intval($_POST['sessions_limit'] ?? 0);
        $api_calls_limit = intval($_POST['api_calls_limit'] ?? 0);
        $duration_days = intval($_POST['duration_days'] ?? 30);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Validation
        if (empty($name)) {
            $_SESSION['error'] = 'Plan name is required';
            header('Location: /admin/whatsapp/subscription-plans/create');
            exit;
        }
        
        // Insert plan
        $this->db->query("
            INSERT INTO whatsapp_subscription_plans 
            (name, description, price, currency, messages_limit, sessions_limit, api_calls_limit, duration_days, is_active)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ", [$name, $description, $price, $currency, $messages_limit, $sessions_limit, $api_calls_limit, $duration_days, $is_active]);
        
        $_SESSION['success'] = 'Subscription plan created successfully';
        header('Location: /admin/whatsapp/subscription-plans');
        exit;
    }
    
    /**
     * Show edit plan form
     */
    public function editPlanForm($id)
    {
        $plan = $this->db->fetch("SELECT * FROM whatsapp_subscription_plans WHERE id = ?", [$id]);
        
        if (!$plan) {
            $_SESSION['error'] = 'Plan not found';
            header('Location: /admin/whatsapp/subscription-plans');
            exit;
        }
        
        View::render('admin/projects/whatsapp/subscription-plan-form', [
            'plan' => $plan,
            'pageTitle' => 'Edit Subscription Plan - WhatsApp Admin'
        ]);
    }
    
    /**
     * Update subscription plan
     */
    public function updatePlan($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/whatsapp/subscription-plans');
            exit;
        }
        
        // Verify CSRF token
        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid security token';
            header('Location: /admin/whatsapp/subscription-plans/edit/' . $id);
            exit;
        }
        
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        $currency = trim($_POST['currency'] ?? 'USD');
        $messages_limit = intval($_POST['messages_limit'] ?? 0);
        $sessions_limit = intval($_POST['sessions_limit'] ?? 0);
        $api_calls_limit = intval($_POST['api_calls_limit'] ?? 0);
        $duration_days = intval($_POST['duration_days'] ?? 30);
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        
        // Validation
        if (empty($name)) {
            $_SESSION['error'] = 'Plan name is required';
            header('Location: /admin/whatsapp/subscription-plans/edit/' . $id);
            exit;
        }
        
        // Update plan
        $this->db->query("
            UPDATE whatsapp_subscription_plans 
            SET name = ?, description = ?, price = ?, currency = ?, 
                messages_limit = ?, sessions_limit = ?, api_calls_limit = ?, 
                duration_days = ?, is_active = ?
            WHERE id = ?
        ", [$name, $description, $price, $currency, $messages_limit, $sessions_limit, $api_calls_limit, $duration_days, $is_active, $id]);
        
        $_SESSION['success'] = 'Subscription plan updated successfully';
        header('Location: /admin/whatsapp/subscription-plans');
        exit;
    }
    
    /**
     * Delete subscription plan
     */
    public function deletePlan($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/whatsapp/subscription-plans');
            exit;
        }
        
        // Verify CSRF token
        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid security token';
            header('Location: /admin/whatsapp/subscription-plans');
            exit;
        }
        
        // Check if plan is in use
        $inUse = $this->db->fetchColumn("
            SELECT COUNT(*) FROM whatsapp_subscriptions WHERE plan_type = (
                SELECT LOWER(REPLACE(name, ' Plan', '')) FROM whatsapp_subscription_plans WHERE id = ?
            )
        ", [$id]);
        
        if ($inUse > 0) {
            $_SESSION['error'] = 'Cannot delete plan - it is currently in use by ' . $inUse . ' subscription(s)';
            header('Location: /admin/whatsapp/subscription-plans');
            exit;
        }
        
        $this->db->query("DELETE FROM whatsapp_subscription_plans WHERE id = ?", [$id]);
        
        $_SESSION['success'] = 'Subscription plan deleted successfully';
        header('Location: /admin/whatsapp/subscription-plans');
        exit;
    }
    
    /**
     * List all user subscriptions
     */
    public function subscriptions()
    {
        $page = $_GET['page'] ?? 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Build filter query
        $where = [];
        $params = [];
        
        if (!empty($_GET['status'])) {
            $where[] = "status = ?";
            $params[] = $_GET['status'];
        }
        
        if (!empty($_GET['plan_type'])) {
            $where[] = "plan_type = ?";
            $params[] = $_GET['plan_type'];
        }
        
        if (!empty($_GET['user_id'])) {
            $where[] = "user_id = ?";
            $params[] = $_GET['user_id'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Get subscriptions
        $subscriptions = $this->db->fetchAll("
            SELECT * FROM whatsapp_subscription_details
            $whereClause
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ", array_merge($params, [$perPage, $offset]));
        
        // Get total count
        $totalSubscriptions = $this->db->fetchColumn("
            SELECT COUNT(*) FROM whatsapp_subscriptions $whereClause
        ", $params);
        
        View::render('admin/projects/whatsapp/user-subscriptions', [
            'subscriptions' => $subscriptions,
            'currentPage' => $page,
            'totalPages' => ceil($totalSubscriptions / $perPage),
            'filters' => $_GET,
            'pageTitle' => 'User Subscriptions - WhatsApp Admin'
        ]);
    }
    
    /**
     * Show assign subscription form
     */
    public function assignSubscriptionForm()
    {
        $users = $this->db->fetchAll("
            SELECT id, name, email FROM users 
            ORDER BY name ASC
        ");
        
        $plans = $this->db->fetchAll("
            SELECT * FROM whatsapp_subscription_plans 
            WHERE is_active = 1 
            ORDER BY price ASC
        ");
        
        View::render('admin/projects/whatsapp/assign-subscription', [
            'users' => $users,
            'plans' => $plans,
            'pageTitle' => 'Assign Subscription - WhatsApp Admin'
        ]);
    }
    
    /**
     * Assign subscription to user
     */
    public function assignSubscription()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/whatsapp/user-subscriptions');
            exit;
        }
        
        // Verify CSRF token
        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid security token';
            header('Location: /admin/whatsapp/user-subscriptions/assign');
            exit;
        }
        
        $user_id = intval($_POST['user_id'] ?? 0);
        $plan_id = intval($_POST['plan_id'] ?? 0);
        $duration_days = intval($_POST['duration_days'] ?? 30);
        
        // Validate user
        $user = $this->db->fetch("SELECT id FROM users WHERE id = ?", [$user_id]);
        if (!$user) {
            $_SESSION['error'] = 'User not found';
            header('Location: /admin/whatsapp/user-subscriptions/assign');
            exit;
        }
        
        // Get plan details
        $plan = $this->db->fetch("SELECT * FROM whatsapp_subscription_plans WHERE id = ?", [$plan_id]);
        if (!$plan) {
            $_SESSION['error'] = 'Plan not found';
            header('Location: /admin/whatsapp/user-subscriptions/assign');
            exit;
        }
        
        // Determine plan type from plan name
        $planType = strtolower(str_replace(' Plan', '', $plan['name']));
        
        // Check if user already has an active subscription
        $existingSubscription = $this->db->fetch("
            SELECT id FROM whatsapp_subscriptions 
            WHERE user_id = ? AND status = 'active'
        ", [$user_id]);
        
        if ($existingSubscription) {
            // Deactivate existing subscription
            $this->db->query("
                UPDATE whatsapp_subscriptions 
                SET status = 'inactive' 
                WHERE id = ?
            ", [$existingSubscription['id']]);
        }
        
        // Create new subscription
        $start_date = date('Y-m-d H:i:s');
        $end_date = date('Y-m-d H:i:s', strtotime("+{$duration_days} days"));
        
        $this->db->query("
            INSERT INTO whatsapp_subscriptions 
            (user_id, plan_type, status, start_date, end_date, messages_limit, sessions_limit, api_calls_limit)
            VALUES (?, ?, 'active', ?, ?, ?, ?, ?)
        ", [
            $user_id, 
            $planType, 
            $start_date, 
            $end_date, 
            $plan['messages_limit'], 
            $plan['sessions_limit'], 
            $plan['api_calls_limit']
        ]);
        
        $_SESSION['success'] = 'Subscription assigned successfully';
        header('Location: /admin/whatsapp/user-subscriptions');
        exit;
    }
    
    /**
     * Update user subscription
     */
    public function updateSubscription($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/whatsapp/user-subscriptions');
            exit;
        }
        
        // Verify CSRF token
        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid security token';
            header('Location: /admin/whatsapp/user-subscriptions');
            exit;
        }
        
        $action = $_POST['action'] ?? '';
        
        if ($action === 'extend') {
            $days = intval($_POST['days'] ?? 30);
            $this->db->query("
                UPDATE whatsapp_subscriptions 
                SET end_date = DATE_ADD(end_date, INTERVAL ? DAY)
                WHERE id = ?
            ", [$days, $id]);
            $_SESSION['success'] = "Subscription extended by {$days} days";
        } elseif ($action === 'reset_usage') {
            $this->db->query("
                UPDATE whatsapp_subscriptions 
                SET messages_used = 0, sessions_used = 0, api_calls_used = 0
                WHERE id = ?
            ", [$id]);
            $_SESSION['success'] = 'Usage statistics reset successfully';
        } elseif ($action === 'change_status') {
            $status = $_POST['status'] ?? 'active';
            $this->db->query("
                UPDATE whatsapp_subscriptions 
                SET status = ?
                WHERE id = ?
            ", [$status, $id]);
            $_SESSION['success'] = 'Subscription status updated';
        }
        
        header('Location: /admin/whatsapp/user-subscriptions');
        exit;
    }
    
    /**
     * Cancel user subscription
     */
    public function cancelSubscription($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: /admin/whatsapp/user-subscriptions');
            exit;
        }
        
        // Verify CSRF token
        if (!Security::verifyCsrfToken($_POST['csrf_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid security token';
            header('Location: /admin/whatsapp/user-subscriptions');
            exit;
        }
        
        $this->db->query("
            UPDATE whatsapp_subscriptions 
            SET status = 'cancelled'
            WHERE id = ?
        ", [$id]);
        
        $_SESSION['success'] = 'Subscription cancelled successfully';
        header('Location: /admin/whatsapp/user-subscriptions');
        exit;
    }
}
