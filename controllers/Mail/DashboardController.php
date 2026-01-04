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
            "SELECT s.*, sub.status as subscription_status
             FROM mail_subscribers s
             LEFT JOIN mail_subscriptions sub ON s.id = sub.subscriber_id
             WHERE s.mmb_user_id = ?
             LIMIT 1",
            [$userId]
        );
        
        if ($subscriber) {
            // User is a subscriber - redirect to subscriber dashboard
            $this->redirect('/projects/mail/subscriber/dashboard');
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
        
        // User has no mail access - show getting started page
        $this->view('mail/getting-started', [
            'pageTitle' => 'Mail Hosting - Getting Started'
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
