<?php
/**
 * Admin QR Management Controller
 *
 * Manages all QR code admin features: listing, analytics, blocked links,
 * storage usage, subscription plans, abuse reports and role/user permissions.
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Security;
use Core\Auth;
use Core\Logger;

class QRAdminController extends BaseController
{
    private Database $db;

    public function __construct()
    {
        $this->requireAuth();
        $this->requireAdmin();
        $this->db = Database::getInstance();
    }

    // -------------------------------------------------------------------------
    // All QR Codes
    // -------------------------------------------------------------------------

    /**
     * List all QR codes across all users
     */
    public function index(): void
    {
        $page    = max(1, (int) $this->input('page', 1));
        $perPage = 20;
        $offset  = ($page - 1) * $perPage;

        $search = $this->input('search', '');
        $type   = $this->input('type', '');
        $status = $this->input('status', '');

        $where  = '1=1';
        $params = [];

        if ($search) {
            $where   .= ' AND (q.content LIKE ? OR u.name LIKE ? OR u.email LIKE ?)';
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
            $params[] = "%{$search}%";
        }
        if ($type) {
            $where   .= ' AND q.type = ?';
            $params[] = $type;
        }
        if ($status) {
            $where   .= ' AND q.status = ?';
            $params[] = $status;
        }

        try {
            $qrCodes = $this->db->fetchAll(
                "SELECT q.*, u.name AS user_name, u.email AS user_email
                 FROM qr_codes q
                 LEFT JOIN users u ON u.id = q.user_id
                 WHERE {$where}
                 ORDER BY q.created_at DESC
                 LIMIT ? OFFSET ?",
                array_merge($params, [$perPage, $offset])
            );

            $totalRow = $this->db->fetch(
                "SELECT COUNT(*) AS cnt
                 FROM qr_codes q
                 LEFT JOIN users u ON u.id = q.user_id
                 WHERE {$where}",
                $params
            );

            $stats = $this->db->fetch(
                "SELECT
                     COUNT(*) AS total,
                     SUM(is_dynamic) AS dynamic,
                     SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active,
                     SUM(CASE WHEN status = 'blocked' THEN 1 ELSE 0 END) AS blocked,
                     SUM(scan_count) AS total_scans
                 FROM qr_codes"
            );
        } catch (\Exception $e) {
            $qrCodes  = [];
            $totalRow = ['cnt' => 0];
            $stats    = ['total' => 0, 'dynamic' => 0, 'active' => 0, 'blocked' => 0, 'total_scans' => 0];
        }

        $this->view('admin/qr/index', [
            'title'      => 'QR Code Management',
            'subtitle'   => 'View and manage all QR codes across the platform',
            'qrCodes'    => $qrCodes,
            'stats'      => $stats,
            'search'     => $search,
            'type'       => $type,
            'status'     => $status,
            'pagination' => [
                'current' => $page,
                'total'   => ceil(($totalRow['cnt'] ?? 0) / $perPage),
                'perPage' => $perPage,
            ],
        ]);
    }

    /**
     * Block a QR code
     */
    public function blockQR(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/qr');
            return;
        }

        try {
            $this->db->query(
                "UPDATE qr_codes SET status = 'blocked', updated_at = NOW() WHERE id = ?",
                [(int) $id]
            );
            Logger::activity(Auth::id(), 'admin_qr_blocked', ['qr_id' => (int) $id]);
            $this->flash('success', 'QR code has been blocked.');
        } catch (\Exception $e) {
            Logger::error('QR block error: ' . $e->getMessage());
            $this->flash('error', 'Failed to block QR code.');
        }

        $this->redirect('/admin/qr');
    }

    /**
     * Unblock a QR code
     */
    public function unblockQR(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/qr');
            return;
        }

        try {
            $this->db->query(
                "UPDATE qr_codes SET status = 'active', updated_at = NOW() WHERE id = ?",
                [(int) $id]
            );
            Logger::activity(Auth::id(), 'admin_qr_unblocked', ['qr_id' => (int) $id]);
            $this->flash('success', 'QR code has been unblocked.');
        } catch (\Exception $e) {
            Logger::error('QR unblock error: ' . $e->getMessage());
            $this->flash('error', 'Failed to unblock QR code.');
        }

        $this->redirect('/admin/qr');
    }

    // -------------------------------------------------------------------------
    // Scan Traffic Analytics
    // -------------------------------------------------------------------------

    /**
     * Scan traffic analytics dashboard
     */
    public function analytics(): void
    {
        try {
            // Total scans per day (last 30 days)
            $dailyScans = $this->db->fetchAll(
                "SELECT DATE(scanned_at) AS date, COUNT(*) AS scans
                 FROM qr_scans
                 WHERE scanned_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                 GROUP BY DATE(scanned_at)
                 ORDER BY date ASC"
            );

            // Top QR codes by scans
            $topQR = $this->db->fetchAll(
                "SELECT q.id, q.content, q.type, q.scan_count, u.name AS user_name
                 FROM qr_codes q
                 LEFT JOIN users u ON u.id = q.user_id
                 ORDER BY q.scan_count DESC
                 LIMIT 10"
            );

            // Scans by country
            $byCountry = $this->db->fetchAll(
                "SELECT country, COUNT(*) AS scans
                 FROM qr_scans
                 WHERE country IS NOT NULL AND country != ''
                 GROUP BY country
                 ORDER BY scans DESC
                 LIMIT 10"
            );

            // Scans by device type
            $byDevice = $this->db->fetchAll(
                "SELECT device_type, COUNT(*) AS scans
                 FROM qr_scans
                 WHERE device_type IS NOT NULL
                 GROUP BY device_type
                 ORDER BY scans DESC"
            );

            // Overall stats
            $overallStats = $this->db->fetch(
                "SELECT
                     COUNT(*) AS total_scans,
                     COUNT(DISTINCT qr_id) AS unique_qr_scanned,
                     COUNT(DISTINCT ip_address) AS unique_visitors
                 FROM qr_scans"
            );
        } catch (\Exception $e) {
            $dailyScans   = [];
            $topQR        = [];
            $byCountry    = [];
            $byDevice     = [];
            $overallStats = ['total_scans' => 0, 'unique_qr_scanned' => 0, 'unique_visitors' => 0];
        }

        Logger::activity(Auth::id(), 'admin_qr_analytics_viewed');

        $this->view('admin/qr/analytics', [
            'title'        => 'QR Scan Analytics',
            'subtitle'     => 'Traffic and scan statistics across all QR codes',
            'dailyScans'   => $dailyScans,
            'topQR'        => $topQR,
            'byCountry'    => $byCountry,
            'byDevice'     => $byDevice,
            'overallStats' => $overallStats,
        ]);
    }

    // -------------------------------------------------------------------------
    // Blocked / Malicious Links
    // -------------------------------------------------------------------------

    /**
     * Manage blocked URL patterns
     */
    public function blockedLinks(): void
    {
        try {
            $blockedLinks = $this->db->fetchAll(
                "SELECT bl.*, u.name AS blocked_by_name
                 FROM qr_blocked_links bl
                 LEFT JOIN users u ON u.id = bl.blocked_by
                 ORDER BY bl.created_at DESC"
            );
        } catch (\Exception $e) {
            $blockedLinks = [];
        }

        $this->view('admin/qr/blocked-links', [
            'title'        => 'Blocked Links',
            'subtitle'     => 'Manage blocked URL patterns to prevent malicious QR codes',
            'blockedLinks' => $blockedLinks,
        ]);
    }

    /**
     * Block a URL pattern
     */
    public function blockLink(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/qr/blocked-links');
            return;
        }

        $pattern = trim($this->input('url_pattern', ''));
        $reason  = trim($this->input('reason', ''));

        if (empty($pattern)) {
            $this->flash('error', 'URL pattern is required.');
            $this->redirect('/admin/qr/blocked-links');
            return;
        }

        try {
            $this->db->query(
                "INSERT INTO qr_blocked_links (url_pattern, reason, blocked_by, created_at)
                 VALUES (?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE reason = VALUES(reason), blocked_by = VALUES(blocked_by)",
                [$pattern, $reason, Auth::id()]
            );

            // Also block all existing QR codes matching the pattern
            $this->db->query(
                "UPDATE qr_codes SET status = 'blocked', updated_at = NOW()
                 WHERE content LIKE ? OR redirect_url LIKE ?",
                ["%{$pattern}%", "%{$pattern}%"]
            );

            Logger::activity(Auth::id(), 'admin_qr_link_blocked', ['pattern' => $pattern, 'reason' => $reason]);
            $this->flash('success', 'URL pattern has been blocked and matching QR codes deactivated.');
        } catch (\Exception $e) {
            Logger::error('Block link error: ' . $e->getMessage());
            $this->flash('error', 'Failed to block URL pattern.');
        }

        $this->redirect('/admin/qr/blocked-links');
    }

    /**
     * Unblock a URL pattern
     */
    public function unblockLink(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/qr/blocked-links');
            return;
        }

        try {
            $link = $this->db->fetch("SELECT * FROM qr_blocked_links WHERE id = ?", [(int) $id]);
            if ($link) {
                $this->db->query("DELETE FROM qr_blocked_links WHERE id = ?", [(int) $id]);
                Logger::activity(Auth::id(), 'admin_qr_link_unblocked', ['pattern' => $link['url_pattern']]);
                $this->flash('success', 'URL pattern has been unblocked.');
            } else {
                $this->flash('error', 'Blocked link not found.');
            }
        } catch (\Exception $e) {
            Logger::error('Unblock link error: ' . $e->getMessage());
            $this->flash('error', 'Failed to unblock URL pattern.');
        }

        $this->redirect('/admin/qr/blocked-links');
    }

    // -------------------------------------------------------------------------
    // Storage Usage Monitor
    // -------------------------------------------------------------------------

    /**
     * Storage usage monitor
     */
    public function storage(): void
    {
        try {
            // Per-user storage usage
            $userStorage = $this->db->fetchAll(
                "SELECT u.id, u.name, u.email,
                     COUNT(q.id) AS qr_count,
                     SUM(CASE WHEN q.logo_path IS NOT NULL THEN 1 ELSE 0 END) AS qr_with_logo,
                     MAX(q.created_at) AS last_qr_at
                 FROM users u
                 LEFT JOIN qr_codes q ON q.user_id = u.id
                 GROUP BY u.id
                 HAVING qr_count > 0
                 ORDER BY qr_count DESC
                 LIMIT 50"
            );

            // Overall stats
            $storageStats = $this->db->fetch(
                "SELECT
                     COUNT(*) AS total_qr,
                     SUM(CASE WHEN is_dynamic = 1 THEN 1 ELSE 0 END) AS dynamic_qr,
                     SUM(CASE WHEN logo_path IS NOT NULL THEN 1 ELSE 0 END) AS with_logo,
                     SUM(scan_count) AS total_scans
                 FROM qr_codes"
            );

            // Disk usage of logo uploads
            $logoDir  = BASE_PATH . '/storage/qr/logos';
            $diskUsed = 0;
            $fileCount = 0;
            if (is_dir($logoDir)) {
                foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($logoDir)) as $file) {
                    if ($file->isFile()) {
                        $diskUsed += $file->getSize();
                        $fileCount++;
                    }
                }
            }
        } catch (\Exception $e) {
            $userStorage  = [];
            $storageStats = ['total_qr' => 0, 'dynamic_qr' => 0, 'with_logo' => 0, 'total_scans' => 0];
            $diskUsed     = 0;
            $fileCount    = 0;
        }

        Logger::activity(Auth::id(), 'admin_qr_storage_viewed');

        $this->view('admin/qr/storage', [
            'title'        => 'Storage Usage Monitor',
            'subtitle'     => 'QR code and asset storage usage across the platform',
            'userStorage'  => $userStorage,
            'storageStats' => $storageStats,
            'diskUsed'     => $diskUsed,
            'fileCount'    => $fileCount,
        ]);
    }

    // -------------------------------------------------------------------------
    // Plan Management
    // -------------------------------------------------------------------------

    /**
     * List subscription plans
     */
    public function plans(): void
    {
        try {
            $plans = $this->db->fetchAll(
                "SELECT p.*,
                     COUNT(s.id) AS subscriber_count
                 FROM qr_subscription_plans p
                 LEFT JOIN qr_user_subscriptions s ON s.plan_id = p.id AND s.status = 'active'
                 GROUP BY p.id
                 ORDER BY p.price ASC"
            );
        } catch (\Exception $e) {
            $plans = [];
        }

        $this->view('admin/qr/plans', [
            'title'    => 'QR Subscription Plans',
            'subtitle' => 'Manage plan limits and feature access for QR generator',
            'plans'    => $plans,
        ]);
    }

    /**
     * Update a plan (limits + features)
     */
    public function updatePlan(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/qr/plans');
            return;
        }

        $planId = (int) $id;
        try {
            $plan = $this->db->fetch("SELECT * FROM qr_subscription_plans WHERE id = ?", [$planId]);
            if (!$plan) {
                $this->flash('error', 'Plan not found.');
                $this->redirect('/admin/qr/plans');
                return;
            }

            $features = [
                'downloads'           => array_filter(explode(',', $this->input('feature_downloads', ''))),
                'analytics'           => (bool) $this->input('feature_analytics', false),
                'bulk'                => (bool) $this->input('feature_bulk', false),
                'ai'                  => (bool) $this->input('feature_ai', false),
                'password_protection' => (bool) $this->input('feature_password', false),
                'campaigns'           => (bool) $this->input('feature_campaigns', false),
                'api'                 => (bool) $this->input('feature_api', false),
                'whitelabel'          => (bool) $this->input('feature_whitelabel', false),
                'priority_support'    => (bool) $this->input('feature_priority_support', false),
                'team_roles'          => (bool) $this->input('feature_team_roles', false),
            ];

            $this->db->query(
                "UPDATE qr_subscription_plans SET
                     name                 = ?,
                     max_static_qr        = ?,
                     max_dynamic_qr       = ?,
                     max_scans_per_month  = ?,
                     max_bulk_generation  = ?,
                     features             = ?,
                     status               = ?,
                     updated_at           = NOW()
                 WHERE id = ?",
                [
                    Security::sanitize($this->input('name', $plan['name'])),
                    (int) $this->input('max_static_qr', $plan['max_static_qr']),
                    (int) $this->input('max_dynamic_qr', $plan['max_dynamic_qr']),
                    (int) $this->input('max_scans_per_month', $plan['max_scans_per_month']),
                    (int) $this->input('max_bulk_generation', $plan['max_bulk_generation']),
                    json_encode($features),
                    $this->input('status', 'active'),
                    $planId,
                ]
            );

            Logger::activity(Auth::id(), 'admin_qr_plan_updated', ['plan_id' => $planId, 'plan_name' => $plan['name']]);
            $this->flash('success', 'Plan updated successfully.');
        } catch (\Exception $e) {
            Logger::error('Plan update error: ' . $e->getMessage());
            $this->flash('error', 'Failed to update plan.');
        }

        $this->redirect('/admin/qr/plans');
    }

    /**
     * Toggle a single feature flag in a plan
     */
    public function togglePlanFeature(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.'], 403);
            return;
        }

        $planId  = (int) $id;
        $feature = $this->input('feature', '');

        $allowed = ['analytics', 'bulk', 'ai', 'password_protection', 'campaigns', 'api', 'whitelabel', 'priority_support', 'team_roles'];
        if (!in_array($feature, $allowed, true)) {
            $this->json(['success' => false, 'message' => 'Invalid feature.'], 400);
            return;
        }

        try {
            $plan = $this->db->fetch("SELECT * FROM qr_subscription_plans WHERE id = ?", [$planId]);
            if (!$plan) {
                $this->json(['success' => false, 'message' => 'Plan not found.'], 404);
                return;
            }

            $features          = json_decode($plan['features'] ?? '{}', true) ?: [];
            $current           = (bool) ($features[$feature] ?? false);
            $features[$feature] = !$current;

            $this->db->query(
                "UPDATE qr_subscription_plans SET features = ?, updated_at = NOW() WHERE id = ?",
                [json_encode($features), $planId]
            );

            Logger::activity(Auth::id(), 'admin_qr_plan_feature_toggled', [
                'plan_id' => $planId,
                'feature' => $feature,
                'enabled' => !$current,
            ]);

            $this->json(['success' => true, 'enabled' => !$current]);
        } catch (\Exception $e) {
            Logger::error('Toggle plan feature error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    // -------------------------------------------------------------------------
    // Abuse Reports
    // -------------------------------------------------------------------------

    /**
     * List abuse reports
     */
    public function abuseReports(): void
    {
        try {
            $reports = $this->db->fetchAll(
                "SELECT r.*, q.content AS qr_content, q.type AS qr_type, q.status AS qr_status,
                     u.name AS reporter_name, u.email AS reporter_email
                 FROM qr_abuse_reports r
                 LEFT JOIN qr_codes q ON q.id = r.qr_id
                 LEFT JOIN users u ON u.id = r.reporter_id
                 ORDER BY r.created_at DESC"
            );

            $stats = $this->db->fetch(
                "SELECT
                     COUNT(*) AS total,
                     SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                     SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) AS resolved,
                     SUM(CASE WHEN status = 'dismissed' THEN 1 ELSE 0 END) AS dismissed
                 FROM qr_abuse_reports"
            );
        } catch (\Exception $e) {
            $reports = [];
            $stats   = ['total' => 0, 'pending' => 0, 'resolved' => 0, 'dismissed' => 0];
        }

        $this->view('admin/qr/abuse-reports', [
            'title'    => 'Abuse Reports',
            'subtitle' => 'Review and manage reported QR codes',
            'reports'  => $reports,
            'stats'    => $stats,
        ]);
    }

    /**
     * Resolve an abuse report (block QR + mark resolved)
     */
    public function resolveAbuse(string $id): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/qr/abuse-reports');
            return;
        }

        $action = $this->input('action', 'resolve'); // resolve | dismiss

        try {
            $report = $this->db->fetch("SELECT * FROM qr_abuse_reports WHERE id = ?", [(int) $id]);
            if (!$report) {
                $this->flash('error', 'Report not found.');
                $this->redirect('/admin/qr/abuse-reports');
                return;
            }

            $newStatus = $action === 'dismiss' ? 'dismissed' : 'resolved';

            $this->db->query(
                "UPDATE qr_abuse_reports SET status = ?, resolved_by = ?, resolved_at = NOW() WHERE id = ?",
                [$newStatus, Auth::id(), (int) $id]
            );

            if ($action === 'resolve') {
                $this->db->query(
                    "UPDATE qr_codes SET status = 'blocked', updated_at = NOW() WHERE id = ?",
                    [$report['qr_id']]
                );
            }

            Logger::activity(Auth::id(), 'admin_qr_abuse_' . $newStatus, ['report_id' => (int) $id, 'qr_id' => $report['qr_id']]);
            $this->flash('success', 'Abuse report has been ' . $newStatus . '.');
        } catch (\Exception $e) {
            Logger::error('Resolve abuse error: ' . $e->getMessage());
            $this->flash('error', 'Failed to process report.');
        }

        $this->redirect('/admin/qr/abuse-reports');
    }

    // -------------------------------------------------------------------------
    // Role & User Feature Management
    // -------------------------------------------------------------------------

    /**
     * Role & user feature management page
     */
    public function roles(): void
    {
        try {
            // Available features
            $allFeatures = $this->getAllFeatures();

            // Role features
            $roleFeatureRows = $this->db->fetchAll("SELECT * FROM qr_role_features");
            $roleFeatures    = [];
            foreach ($roleFeatureRows as $row) {
                $roleFeatures[$row['role']][$row['feature']] = (bool) $row['enabled'];
            }

            // Users with custom feature overrides
            $userFeatureRows = $this->db->fetchAll(
                "SELECT uf.*, u.name AS user_name, u.email AS user_email
                 FROM qr_user_features uf
                 JOIN users u ON u.id = uf.user_id
                 ORDER BY u.name, uf.feature"
            );

            $userFeatures = [];
            foreach ($userFeatureRows as $row) {
                $userFeatures[$row['user_id']] = $userFeatures[$row['user_id']] ?? [
                    'user_name'  => $row['user_name'],
                    'user_email' => $row['user_email'],
                    'features'   => [],
                ];
                $userFeatures[$row['user_id']]['features'][$row['feature']] = (bool) $row['enabled'];
            }

            // All users for the select box
            $users = $this->db->fetchAll("SELECT id, name, email, role FROM users ORDER BY name");

            // Plans for user plan assignment
            $plans = $this->db->fetchAll("SELECT id, name, slug FROM qr_subscription_plans WHERE status = 'active' ORDER BY price ASC");

            // User plan subscriptions
            $userPlans = $this->db->fetchAll(
                "SELECT s.user_id, p.name AS plan_name, p.slug, s.status AS sub_status
                 FROM qr_user_subscriptions s
                 JOIN qr_subscription_plans p ON p.id = s.plan_id
                 WHERE s.status = 'active'"
            );
            $userPlanMap = [];
            foreach ($userPlans as $up) {
                $userPlanMap[$up['user_id']] = $up;
            }
        } catch (\Exception $e) {
            $allFeatures  = $this->getAllFeatures();
            $roleFeatures = [];
            $userFeatures = [];
            $users        = [];
            $plans        = [];
            $userPlanMap  = [];
        }

        $roles = ['user', 'manager', 'owner', 'admin'];

        $this->view('admin/qr/roles', [
            'title'       => 'Roles & Feature Permissions',
            'subtitle'    => 'Set feature access by role or individual user',
            'allFeatures' => $allFeatures,
            'roles'       => $roles,
            'roleFeatures'=> $roleFeatures,
            'userFeatures'=> $userFeatures,
            'users'       => $users,
            'plans'       => $plans,
            'userPlanMap' => $userPlanMap,
        ]);
    }

    /**
     * Set a feature for a role
     */
    public function setRoleFeature(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.'], 403);
            return;
        }

        $role    = $this->input('role', '');
        $feature = $this->input('feature', '');
        $enabled = (bool) $this->input('enabled', false);

        $validRoles    = ['user', 'manager', 'owner', 'admin'];
        $validFeatures = array_keys($this->getAllFeatures());

        if (!in_array($role, $validRoles, true) || !in_array($feature, $validFeatures, true)) {
            $this->json(['success' => false, 'message' => 'Invalid role or feature.'], 400);
            return;
        }

        try {
            $this->db->query(
                "INSERT INTO qr_role_features (role, feature, enabled, updated_at)
                 VALUES (?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE enabled = VALUES(enabled), updated_at = NOW()",
                [$role, $feature, $enabled ? 1 : 0]
            );

            Logger::activity(Auth::id(), 'admin_qr_role_feature_set', [
                'role'    => $role,
                'feature' => $feature,
                'enabled' => $enabled,
            ]);

            $this->json(['success' => true]);
        } catch (\Exception $e) {
            Logger::error('Set role feature error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    /**
     * Set a feature override for a specific user
     */
    public function setUserFeature(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.'], 403);
            return;
        }

        $userId  = (int) $this->input('user_id', 0);
        $feature = $this->input('feature', '');
        $enabled = (bool) $this->input('enabled', false);

        $validFeatures = array_keys($this->getAllFeatures());

        if (!$userId || !in_array($feature, $validFeatures, true)) {
            $this->json(['success' => false, 'message' => 'Invalid user or feature.'], 400);
            return;
        }

        try {
            $this->db->query(
                "INSERT INTO qr_user_features (user_id, feature, enabled, updated_at)
                 VALUES (?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE enabled = VALUES(enabled), updated_at = NOW()",
                [$userId, $feature, $enabled ? 1 : 0]
            );

            Logger::activity(Auth::id(), 'admin_qr_user_feature_set', [
                'user_id' => $userId,
                'feature' => $feature,
                'enabled' => $enabled,
            ]);

            $this->json(['success' => true]);
        } catch (\Exception $e) {
            Logger::error('Set user feature error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    /**
     * Remove all feature overrides for a user
     */
    public function removeUserFeatures(): void
    {
        if (!$this->validateCsrf()) {
            $this->json(['success' => false, 'message' => 'Invalid request.'], 403);
            return;
        }

        $userId = (int) $this->input('user_id', 0);
        if (!$userId) {
            $this->json(['success' => false, 'message' => 'Invalid user.'], 400);
            return;
        }

        try {
            $this->db->query("DELETE FROM qr_user_features WHERE user_id = ?", [$userId]);
            Logger::activity(Auth::id(), 'admin_qr_user_features_removed', ['user_id' => $userId]);
            $this->json(['success' => true]);
        } catch (\Exception $e) {
            Logger::error('Remove user features error: ' . $e->getMessage());
            $this->json(['success' => false, 'message' => 'Server error.'], 500);
        }
    }

    /**
     * Assign / change a user's QR subscription plan
     */
    public function assignUserPlan(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/qr/roles');
            return;
        }

        $userId = (int) $this->input('user_id', 0);
        $planId = (int) $this->input('plan_id', 0);

        if (!$userId || !$planId) {
            $this->flash('error', 'User and plan are required.');
            $this->redirect('/admin/qr/roles');
            return;
        }

        try {
            // Cancel any existing subscription
            $this->db->query(
                "UPDATE qr_user_subscriptions SET status = 'cancelled', updated_at = NOW() WHERE user_id = ? AND status = 'active'",
                [$userId]
            );

            // Insert new subscription
            $this->db->query(
                "INSERT INTO qr_user_subscriptions (user_id, plan_id, status, started_at)
                 VALUES (?, ?, 'active', NOW())",
                [$userId, $planId]
            );

            $plan = $this->db->fetch("SELECT name FROM qr_subscription_plans WHERE id = ?", [$planId]);

            Logger::activity(Auth::id(), 'admin_qr_user_plan_assigned', [
                'user_id'   => $userId,
                'plan_id'   => $planId,
                'plan_name' => $plan['name'] ?? '',
            ]);

            $this->flash('success', 'Plan assigned successfully.');
        } catch (\Exception $e) {
            Logger::error('Assign user plan error: ' . $e->getMessage());
            $this->flash('error', 'Failed to assign plan.');
        }

        $this->redirect('/admin/qr/roles');
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Returns the full list of QR features with human-readable labels
     */
    private function getAllFeatures(): array
    {
        return [
            'static_qr'           => 'Static QR Codes',
            'dynamic_qr'          => 'Dynamic QR Codes',
            'analytics'           => 'Scan Analytics',
            'bulk_generation'     => 'Bulk QR Generation',
            'ai_design'           => 'AI-Powered Design',
            'password_protection' => 'Password Protection',
            'expiry_date'         => 'Expiry Date',
            'campaigns'           => 'Campaign Management',
            'api_access'          => 'API Access',
            'whitelabel'          => 'White-Label / Custom Domain',
            'team_roles'          => 'Team Roles (Manager/Owner)',
            'download_png'        => 'Download PNG',
            'download_svg'        => 'Download SVG',
            'download_pdf'        => 'Download PDF',
            'custom_logo'         => 'Custom Logo / Branding',
            'custom_colors'       => 'Custom Colors',
            'frame_styles'        => 'Frame Styles',
            'priority_support'    => 'Priority Support',
            'export_data'         => 'Export Scan Data',
        ];
    }
}
