<?php
/**
 * Dashboard Controller
 * Main entry point for mail project
 * 
 * @package MMB\Projects\Mail\Controllers
 */

namespace Mail;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;

class DashboardController extends BaseController
{
    private $db;
    
    public function __construct()
    {
        // Check authentication
        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }
        
        // Initialize database with error handling
        try {
            $this->db = Database::getInstance();
        } catch (\Throwable $e) {
            error_log('Warning: Database initialization failed in DashboardController: ' . $e->getMessage());
            $this->db = null;
        }
    }
    
    /**
     * Ensure database is available
     */
    private function ensureDatabase()
    {
        if ($this->db === null) {
            try {
                $this->db = Database::getInstance();
            } catch (\Throwable $e) {
                error_log('Failed to initialize database in DashboardController: ' . $e->getMessage());
                throw new \RuntimeException('Database is not available. Please try again later.');
            }
        }
        return $this->db;
    }
    
    /**
     * Main dashboard / landing page
     * Redirects to appropriate interface based on user role
     */
    public function index()
    {
        // Ensure database is available
        $this->ensureDatabase();
        
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
