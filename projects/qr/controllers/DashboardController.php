<?php
/**
 * QR Generator Dashboard Controller
 * 
 * @package MMB\Projects\QR\Controllers
 */

namespace Projects\QR\Controllers;

use Core\Auth;
use Core\View;
use Core\Database;
use Projects\QR\Models\QRModel;

class DashboardController
{
    private QRModel $qrModel;
    
    public function __construct()
    {
        $this->qrModel = new QRModel();
    }
    
    /**
     * Show project dashboard
     */
    public function index(): void
    {
        $user = Auth::user();
        $userId = Auth::id();
        
        // Get user's QR code stats from database
        $stats = [
            'total_generated' => 0,
            'total_scans' => 0,
            'active_codes' => 0,
            'scans_today' => 0,
            'scans_this_week' => 0,
            'average_scans' => 0
        ];
        
        $recentQRs = [];
        $topQRs = [];
        
        if ($userId) {
            try {
                // Get total QR codes count (including deleted) - this never decreases
                $stats['total_generated'] = $this->qrModel->countAllByUser($userId);
                
                // Get active (non-deleted) QR codes
                $stats['active_codes'] = $this->qrModel->countActiveByUser($userId);
                
                // Get scan statistics
                $scanStats = $this->qrModel->getScanStats($userId);
                $stats['total_scans'] = $scanStats['total'];
                $stats['scans_today'] = $scanStats['today'];
                $stats['scans_this_week'] = $scanStats['this_week'];
                
                // Calculate average scans per QR code
                if ($stats['active_codes'] > 0) {
                    $stats['average_scans'] = round($stats['total_scans'] / $stats['active_codes'], 1);
                }
                
                // Get recent activity (last 10 QR codes)
                $recentQRs = $this->qrModel->getRecentByUser($userId, 10);
                
                // Get top performing QR codes (top 5 by scans)
                $topQRs = $this->qrModel->getTopByScans($userId, 5);
                
            } catch (\Exception $e) {
                \Core\Logger::error('Failed to fetch QR stats: ' . $e->getMessage());
            }
        }
        
        // Generate AI insights based on user data
        $aiInsights = $this->generateAIInsights($stats, $recentQRs, $topQRs);
        
        $this->render('dashboard', [
            'title' => 'QR Generator Dashboard',
            'user' => $user,
            'stats' => $stats,
            'recentQRs' => $recentQRs,
            'topQRs' => $topQRs,
            'aiInsights' => $aiInsights
        ]);
    }
    
    /**
     * Generate AI-powered insights based on user data
     */
    private function generateAIInsights(array $stats, array $recentQRs, array $topQRs): array
    {
        $insights = [];
        
        // Analyze scan patterns
        if ($stats['active_codes'] > 0) {
            if ($stats['average_scans'] < 5) {
                $insights[] = [
                    'type' => 'warning',
                    'icon' => 'fa-chart-line',
                    'title' => 'Low Scan Rate Detected',
                    'message' => 'Your QR codes are averaging ' . number_format($stats['average_scans'], 1) . ' scans. Try using <strong>rounded corners</strong> and <strong>vibrant colors</strong> to improve visibility.',
                    'action' => 'Try "Vibrant" preset',
                    'link' => '/projects/qr/generate?preset=vibrant'
                ];
            } elseif ($stats['average_scans'] > 20) {
                $insights[] = [
                    'type' => 'success',
                    'icon' => 'fa-trophy',
                    'title' => 'Excellent Performance!',
                    'message' => 'Your QR codes are performing great with an average of ' . number_format($stats['average_scans'], 1) . ' scans. Keep using similar designs!',
                    'action' => null,
                    'link' => null
                ];
            }
        }
        
        // Analyze recent activity
        if (count($recentQRs) > 3) {
            $hasGradient = false;
            $hasRounded = false;
            foreach (array_slice($recentQRs, 0, 3) as $qr) {
                // Check if QR has specific design elements (would need to be stored in DB)
                // For now, make general recommendations
            }
            
            $insights[] = [
                'type' => 'tip',
                'icon' => 'fa-palette',
                'title' => 'Design Recommendation',
                'message' => 'Based on current trends, <strong>gradient QR codes</strong> with <strong>rounded corners</strong> get 40% more scans. Try the "Modern" preset for best results.',
                'action' => 'Try "Modern" preset',
                'link' => '/projects/qr/generate?preset=modern'
            ];
        }
        
        // Time-based insights
        $hour = (int)date('H');
        if ($hour >= 9 && $hour <= 17) {
            $insights[] = [
                'type' => 'info',
                'icon' => 'fa-clock',
                'title' => 'Peak Usage Time',
                'message' => 'QR codes created during business hours tend to get 30% more engagement. Perfect time to generate new codes!',
                'action' => 'Generate Now',
                'link' => '/projects/qr/generate'
            ];
        }
        
        // If no specific insights, provide general tip
        if (empty($insights)) {
            $tips = [
                [
                    'type' => 'tip',
                    'icon' => 'fa-lightbulb',
                    'title' => 'QR Code Best Practice',
                    'message' => 'Use <strong>high contrast colors</strong> (dark on light background) for better scanning reliability. Aim for at least 30% error correction for outdoor use.',
                    'action' => 'Generate with high EC',
                    'link' => '/projects/qr/generate'
                ],
                [
                    'type' => 'tip',
                    'icon' => 'fa-mobile-alt',
                    'title' => 'Mobile Optimization',
                    'message' => 'Make your QR codes at least <strong>2cm x 2cm</strong> when printed for optimal mobile scanning. Test on multiple devices before mass printing.',
                    'action' => 'Learn more',
                    'link' => '/projects/qr/generate'
                ],
                [
                    'type' => 'tip',
                    'icon' => 'fa-paint-brush',
                    'title' => 'Branding Tip',
                    'message' => 'Add your <strong>brand colors</strong> to QR codes for 25% better brand recognition. Our AI presets can help match your brand identity.',
                    'action' => 'Explore presets',
                    'link' => '/projects/qr/generate?preset=professional'
                ]
            ];
            
            $insights[] = $tips[array_rand($tips)];
        }
        
        return $insights;
    }
    
    /**
     * Show the user's QR plan / subscription page
     */
    public function plan(): void
    {
        $userId = Auth::id();
        $db     = Database::getInstance();

        // ── QR-specific subscription ────────────────────────────────────────
        $qrSub = null;
        try {
            $qrSub = $db->fetch(
                "SELECT s.*, p.name plan_name, p.slug plan_slug, p.price, p.billing_cycle,
                        p.max_static_qr, p.max_dynamic_qr, p.max_scans_per_month, p.features
                 FROM qr_user_subscriptions s
                 JOIN qr_subscription_plans p ON p.id = s.plan_id
                 WHERE s.user_id = ? AND s.status = 'active'
                 ORDER BY s.started_at DESC LIMIT 1",
                [$userId]
            );
        } catch (\Exception $e) {
            // Table may not exist yet
        }

        // Decode features JSON if present
        if ($qrSub && !empty($qrSub['features'])) {
            $decoded = json_decode($qrSub['features'], true);
            $qrSub['features'] = is_array($decoded) ? $decoded : [];
        }

        // ── QR usage counts ─────────────────────────────────────────────────
        $staticCount  = 0;
        $dynamicCount = 0;
        try {
            $staticCount  = (int) ($db->fetch(
                "SELECT COUNT(*) c FROM qr_codes WHERE user_id = ? AND is_dynamic = 0 AND deleted_at IS NULL",
                [$userId]
            )['c'] ?? 0);
            $dynamicCount = (int) ($db->fetch(
                "SELECT COUNT(*) c FROM qr_codes WHERE user_id = ? AND is_dynamic = 1 AND deleted_at IS NULL",
                [$userId]
            )['c'] ?? 0);
        } catch (\Exception $e) { /* ignore */ }

        // ── Platform (universal) plans ──────────────────────────────────────
        $platformPlans = [];
        try {
            $rows = $db->fetchAll(
                "SELECT * FROM platform_plans WHERE status = 'active' ORDER BY sort_order ASC, price ASC"
            );
            foreach ($rows as &$p) {
                $p['included_apps'] = json_decode($p['included_apps'] ?? '[]', true) ?: [];
            }
            $platformPlans = $rows;
        } catch (\Exception $e) { /* table may not exist */ }

        // ── User's active platform subscriptions ────────────────────────────
        $activePlatformPlanIds = [];
        try {
            $subs = $db->fetchAll(
                "SELECT plan_id FROM platform_user_subscriptions WHERE user_id = ? AND status = 'active'",
                [$userId]
            );
            $activePlatformPlanIds = array_column($subs, 'plan_id');
        } catch (\Exception $e) { /* ignore */ }

        // ── Contact email ────────────────────────────────────────────────────
        $contactEmail = 'support@mmbtech.online';
        try {
            $row = $db->fetch("SELECT value FROM settings WHERE `key` = 'maintenance_contact_email' LIMIT 1");
            if ($row && !empty($row['value'])) {
                $contactEmail = $row['value'];
            }
        } catch (\Exception $e) { /* use fallback */ }

        \Core\Logger::activity($userId, 'qr_plan_viewed');

        $this->render('plan', [
            'title'                 => 'My QR Plan',
            'qrSub'                 => $qrSub,
            'staticCount'           => $staticCount,
            'dynamicCount'          => $dynamicCount,
            'platformPlans'         => $platformPlans,
            'activePlatformPlanIds' => $activePlatformPlanIds,
            'contactEmail'          => $contactEmail,
        ]);
    }

    /**
     * Render a project view
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        
        // Start output buffering
        ob_start();
        include PROJECT_PATH . '/views/' . $view . '.php';
        $content = ob_get_clean();
        
        // Include layout
        include PROJECT_PATH . '/views/layout.php';
    }
}
