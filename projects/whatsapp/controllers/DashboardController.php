<?php
/**
 * WhatsApp Dashboard Controller
 *
 * @package MMB\Projects\WhatsApp\Controllers
 */

namespace Projects\WhatsApp\Controllers;

use Core\Auth;
use Core\View;
use Core\Database;
use Core\Logger;
use Core\SubscriptionService;

class DashboardController
{
    private $db;
    private $user;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->user = Auth::user();

        if (!Auth::check()) {
            header('Location: /login');
            exit;
        }
    }

    /**
     * Display dashboard
     */
    public function index()
    {
        try {
            $stats = $this->getDashboardStats();
        } catch (\Throwable $e) {
            Logger::warning('WhatsApp dashboard stats fallback: ' . $e->getMessage(), ['user_id' => $this->user['id'] ?? null]);
            $stats = [
                'totalSessions' => 0,
                'activeSessions' => 0,
                'messagesToday' => 0,
                'apiCallsToday' => 0,
            ];
        }

        try {
            $recentSessions = $this->db->fetchAll("SELECT * FROM whatsapp_sessions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5", [$this->user['id']]);
        } catch (\Throwable $e) {
            Logger::warning('WhatsApp dashboard recentSessions fallback: ' . $e->getMessage(), ['user_id' => $this->user['id'] ?? null]);
            $recentSessions = [];
        }

        try {
            $recentMessages = $this->db->fetchAll("SELECT * FROM whatsapp_messages WHERE user_id = ? ORDER BY created_at DESC LIMIT 10", [$this->user['id']]);
        } catch (\Throwable $e) {
            Logger::warning('WhatsApp dashboard recentMessages fallback: ' . $e->getMessage(), ['user_id' => $this->user['id'] ?? null]);
            $recentMessages = [];
        }

        View::render('whatsapp/dashboard', [
            'user' => $this->user,
            'stats' => $stats,
            'recentSessions' => $recentSessions,
            'recentMessages' => $recentMessages,
            'pageTitle' => 'WhatsApp Dashboard'
        ]);
    }

    /**
     * Display documentation
     */
    public function documentation()
    {
        View::render('whatsapp/documentation', [
            'user' => $this->user,
            'pageTitle' => 'WhatsApp API Documentation'
        ]);
    }

    /**
     * Display chat interface
     */
    public function chat()
    {
        $sessions = $this->db->fetchAll("SELECT * FROM whatsapp_sessions WHERE user_id = ? ORDER BY updated_at DESC", [$this->user['id']]);

        View::render('whatsapp/chat', [
            'user' => $this->user,
            'sessions' => $sessions,
            'pageTitle' => 'WhatsApp Chat'
        ]);
    }

    /**
     * Get dashboard statistics
     */
    private function getDashboardStats()
    {
        $totalSessions = 0;
        $activeSessions = 0;
        $messagesToday = 0;
        $apiCallsToday = 0;

        try {
            $totalSessions = $this->db->fetchColumn("SELECT COUNT(*) FROM whatsapp_sessions WHERE user_id = ?", [$this->user['id']]) ?? 0;
            $activeSessions = $this->db->fetchColumn("SELECT COUNT(*) FROM whatsapp_sessions WHERE user_id = ? AND status = 'active'", [$this->user['id']]) ?? 0;
        } catch (\Throwable $e) {
            Logger::warning('WhatsApp dashboard sessions stats fallback: ' . $e->getMessage(), ['user_id' => $this->user['id'] ?? null]);
        }

        try {
            $messagesToday = $this->db->fetchColumn("
                SELECT COUNT(*) FROM whatsapp_messages
                WHERE user_id = ? AND DATE(created_at) = CURDATE()
            ", [$this->user['id']]) ?? 0;
        } catch (\Throwable $e) {
            Logger::warning('WhatsApp dashboard message stats fallback: ' . $e->getMessage(), ['user_id' => $this->user['id'] ?? null]);
        }

        try {
            $apiCallsToday = $this->db->fetchColumn("
                SELECT COUNT(*) FROM whatsapp_api_logs
                WHERE user_id = ? AND DATE(created_at) = CURDATE()
            ", [$this->user['id']]) ?? 0;
        } catch (\Throwable $e) {
            Logger::warning('WhatsApp dashboard API stats fallback: ' . $e->getMessage(), ['user_id' => $this->user['id'] ?? null]);
        }

        return [
            'totalSessions' => $totalSessions,
            'activeSessions' => $activeSessions,
            'messagesToday' => $messagesToday,
            'apiCallsToday' => $apiCallsToday
        ];
    }

    /**
     * Display user's subscription page
     */
    public function subscription()
    {
        $subscriptionService = new SubscriptionService($this->db);
        $subscriptionService->ensureInfrastructure();

        $userId = (int) ($this->user['id'] ?? 0);
        $subscription = $subscriptionService->getCurrentSubscription('whatsapp', $userId);
        $plans = $subscriptionService->getActivePlans('whatsapp');
        $history = $subscriptionService->getSubscriptionHistory('whatsapp', $userId);
        $paymentHistory = $subscriptionService->getUserPayments($userId, 'whatsapp');

        View::render('whatsapp/subscription', [
            'user' => $this->user,
            'subscription' => $subscription,
            'plans' => $plans,
            'history' => $history,
            'paymentHistory' => $paymentHistory,
            'pageTitle' => 'My Subscription - WhatsApp API'
        ]);
    }
}
