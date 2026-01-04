<?php
/**
 * Dashboard Controller
 * Main entry point for mail project
 * 
 * @package MMB\Controllers\Mail
 */

namespace Controllers\Mail;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;

class DashboardController extends BaseController
{
    private $db;
    
    public function __construct()
    {
        $this->requireAuth();
        $this->db = Database::getInstance();
    }
    
    /**
     * Main dashboard / landing page
     * Redirects to appropriate interface based on user role
     */
    public function index()
    {
        $userId = Auth::id();
        
        // Check if user is a subscriber owner
        $subscriber = $this->db->fetch(
            "SELECT s.*, sub.status as subscription_status, sub.plan_id,
                    sp.plan_name, sp.price_monthly, sp.max_users, sp.storage_per_user_gb
             FROM mail_subscribers s
             LEFT JOIN mail_subscriptions sub ON s.id = sub.subscriber_id
             LEFT JOIN mail_subscription_plans sp ON sub.plan_id = sp.id
             WHERE s.mmb_user_id = ?
             LIMIT 1",
            [$userId]
        );
        
        if ($subscriber) {
            // User is a subscriber - show upgrade page with current plan
            $currentPlanId = $subscriber['plan_id'];
            $allPlans = $this->db->fetchAll(
                "SELECT * FROM mail_subscription_plans WHERE is_active = 1 ORDER BY price_monthly ASC"
            );
            
            $this->view('mail/subscriber-dashboard', [
                'pageTitle' => 'Mail Hosting Dashboard',
                'subscriber' => $subscriber,
                'currentPlan' => $subscriber,
                'plans' => $allPlans
            ]);
            return;
        }
        
        // Check if user has a mailbox (regular user added by subscriber)
        $mailbox = $this->db->fetch(
            "SELECT * FROM mail_mailboxes WHERE mmb_user_id = ? LIMIT 1",
            [$userId]
        );
        
        if ($mailbox) {
            // User has a mailbox - redirect to webmail
            $this->redirect('/projects/mail/webmail');
            return;
        }
        
        // User has no mail access - show getting started page with plans
        $plans = $this->db->fetchAll(
            "SELECT * FROM mail_subscription_plans WHERE is_active = 1 ORDER BY price_monthly ASC"
        );
        
        $this->view('mail/getting-started', [
            'pageTitle' => 'Mail Hosting - Getting Started',
            'plans' => $plans
        ]);
    }
    
    /**
     * Getting started page for new users
     */
    public function gettingStarted()
    {
        $this->view('mail/getting-started', [
            'pageTitle' => 'Mail Hosting - Getting Started',
            'plans' => $this->db->fetchAll(
                "SELECT * FROM mail_subscription_plans WHERE is_active = 1 ORDER BY monthly_price ASC"
            )
        ]);
    }
}
