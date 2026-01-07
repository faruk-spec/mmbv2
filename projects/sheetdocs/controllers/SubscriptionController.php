<?php
/**
 * SheetDocs Subscription Controller
 * 
 * @package MMB\Projects\SheetDocs\Controllers
 */

namespace Projects\SheetDocs\Controllers;

use Core\Auth;
use Core\Database;
use Core\View;
use Core\Security;
use Core\Helpers;

class SubscriptionController
{
    private $db;
    private $projectConfig;
    
    public function __construct()
    {
        if (!Auth::check()) {
            Helpers::redirect('/login?redirect=' . urlencode($_SERVER['REQUEST_URI']));
            exit;
        }
        
        $this->projectConfig = require dirname(__DIR__) . '/config.php';
        $this->db = Database::getProjectConnection('sheetdocs', $this->projectConfig['database']);
    }
    
    /**
     * Show pricing page
     */
    public function pricing(): void
    {
        $userId = Auth::id();
        
        // Get current subscription
        $subscription = $this->getUserSubscription($userId);
        
        // Get usage stats
        $stmt = $this->db->prepare("
            SELECT * FROM usage_stats WHERE user_id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);
        $usageStats = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if (!$usageStats) {
            $usageStats = [
                'document_count' => 0,
                'sheet_count' => 0,
                'storage_used' => 0
            ];
        }
        
        $freeFe atures = $this->projectConfig['features']['free'];
        $paidFeatures = $this->projectConfig['features']['paid'];
        $pricing = $this->projectConfig['subscription'];
        
        View::render('projects/sheetdocs/pricing', [
            'subscription' => $subscription,
            'usageStats' => $usageStats,
            'freeFeatures' => $freeFeatures,
            'paidFeatures' => $paidFeatures,
            'pricing' => $pricing
        ]);
    }
    
    /**
     * Upgrade subscription
     */
    public function upgrade(): void
    {
        Security::validateCsrfToken();
        $userId = Auth::id();
        
        $billingCycle = in_array($_POST['billing_cycle'] ?? '', ['monthly', 'annual']) 
            ? $_POST['billing_cycle'] : 'monthly';
        
        // Get or create subscription
        $subscription = $this->getUserSubscription($userId);
        
        // Calculate trial and billing dates
        $now = new \DateTime();
        $trialEnds = clone $now;
        $trialEnds->modify('+' . $this->projectConfig['subscription']['trial_days'] . ' days');
        
        $periodEnd = clone $now;
        if ($billingCycle === 'annual') {
            $periodEnd->modify('+1 year');
        } else {
            $periodEnd->modify('+1 month');
        }
        
        if ($subscription) {
            // Update existing subscription
            $stmt = $this->db->prepare("
                UPDATE user_subscriptions 
                SET plan = 'paid',
                    status = 'trial',
                    billing_cycle = :billing_cycle,
                    trial_ends_at = :trial_ends_at,
                    current_period_start = NOW(),
                    current_period_end = :period_end,
                    updated_at = NOW()
                WHERE user_id = :user_id
            ");
        } else {
            // Create new subscription
            $stmt = $this->db->prepare("
                INSERT INTO user_subscriptions 
                (user_id, plan, status, billing_cycle, trial_ends_at, current_period_start, current_period_end)
                VALUES (:user_id, 'paid', 'trial', :billing_cycle, :trial_ends_at, NOW(), :period_end)
            ");
        }
        
        $stmt->execute([
            'user_id' => $userId,
            'billing_cycle' => $billingCycle,
            'trial_ends_at' => $trialEnds->format('Y-m-d H:i:s'),
            'period_end' => $periodEnd->format('Y-m-d H:i:s')
        ]);
        
        // Log activity
        $this->logActivity($userId, null, 'upgrade', [
            'plan' => 'paid',
            'billing_cycle' => $billingCycle,
            'trial' => true
        ]);
        
        Helpers::setFlash('success', 'Congratulations! You now have access to all premium features for ' . 
            $this->projectConfig['subscription']['trial_days'] . ' days trial. Enjoy unlimited documents, sheets, and advanced features!');
        Helpers::redirect('/projects/sheetdocs/dashboard');
    }
    
    /**
     * Cancel subscription
     */
    public function cancel(): void
    {
        Security::validateCsrfToken();
        $userId = Auth::id();
        
        $subscription = $this->getUserSubscription($userId);
        
        if (!$subscription || $subscription['plan'] === 'free') {
            Helpers::setFlash('error', 'You do not have an active paid subscription.');
            Helpers::redirect('/projects/sheetdocs/pricing');
            exit;
        }
        
        // Update subscription to cancelled
        $stmt = $this->db->prepare("
            UPDATE user_subscriptions 
            SET status = 'cancelled',
                cancelled_at = NOW(),
                updated_at = NOW()
            WHERE user_id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);
        
        // Log activity
        $this->logActivity($userId, null, 'cancel_subscription', [
            'plan' => $subscription['plan']
        ]);
        
        Helpers::setFlash('success', 'Your subscription has been cancelled. You will continue to have access until the end of your current billing period.');
        Helpers::redirect('/projects/sheetdocs/pricing');
    }
    
    /**
     * Get user subscription
     */
    private function getUserSubscription(int $userId): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM user_subscriptions WHERE user_id = :user_id
        ");
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Log activity
     */
    private function logActivity(int $userId, ?int $documentId, string $action, array $details = []): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO activity_logs (user_id, document_id, action, details, ip_address, user_agent)
            VALUES (:user_id, :document_id, :action, :details, :ip, :user_agent)
        ");
        
        $stmt->execute([
            'user_id' => $userId,
            'document_id' => $documentId,
            'action' => $action,
            'details' => json_encode($details),
            'ip' => Security::getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
    }
}
