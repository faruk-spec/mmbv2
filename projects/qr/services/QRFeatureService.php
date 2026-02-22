<?php
/**
 * QR Feature Service
 *
 * Resolves the effective feature set for a given user by layering:
 *   1. Default (deny all)
 *   2. Plan features (qr_subscription_plans.features JSON)
 *   3. Role features (qr_role_features table)
 *   4. Per-user feature overrides (qr_user_features table)
 *
 * Also exposes plan limits (max QR counts) for enforcement in controllers.
 *
 * @package MMB\Projects\QR\Services
 */

namespace Projects\QR\Services;

use Core\Database;
use Core\Logger;

class QRFeatureService
{
    private Database $db;

    // All known feature keys (matches QRAdminController::getAllFeatures())
    private const ALL_FEATURES = [
        'static_qr',
        'dynamic_qr',
        'analytics',
        'bulk_generation',
        'ai_design',
        'password_protection',
        'expiry_date',
        'campaigns',
        'api_access',
        'whitelabel',
        'team_roles',
        'download_png',
        'download_svg',
        'download_pdf',
        'custom_logo',
        'custom_colors',
        'frame_styles',
        'priority_support',
        'export_data',
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Resolve the full effective feature map for a user.
     * Returns ['feature_key' => bool, ...]
     */
    public function getFeatures(int $userId): array
    {
        // Start with defaults (all disabled)
        $features = array_fill_keys(self::ALL_FEATURES, false);

        // Layer 1: Plan features
        $planFeatures = $this->getPlanFeatures($userId);
        foreach ($planFeatures as $key => $val) {
            if (array_key_exists($key, $features)) {
                $features[$key] = (bool) $val;
            }
        }

        // Layer 2: Role features
        $role = $this->getUserRole($userId);
        if ($role) {
            $roleFeatures = $this->getRoleFeatures($role);
            foreach ($roleFeatures as $key => $val) {
                if (array_key_exists($key, $features)) {
                    $features[$key] = (bool) $val;
                }
            }
        }

        // Layer 3: Per-user overrides (highest priority)
        $userOverrides = $this->getUserOverrides($userId);
        foreach ($userOverrides as $key => $val) {
            if (array_key_exists($key, $features)) {
                $features[$key] = (bool) $val;
            }
        }

        return $features;
    }

    /**
     * Check whether a user has access to a specific feature.
     */
    public function can(int $userId, string $feature): bool
    {
        $features = $this->getFeatures($userId);
        return (bool) ($features[$feature] ?? false);
    }

    /**
     * Return the active plan limits for the user.
     * Keys: max_static_qr, max_dynamic_qr, max_scans_per_month, max_bulk_generation
     * A value of -1 means unlimited.
     * Returns null when no plan is found (treat as free / no limits enforced).
     */
    public function getPlanLimits(int $userId): ?array
    {
        try {
            $row = $this->db->fetch(
                "SELECT p.max_static_qr, p.max_dynamic_qr,
                        p.max_scans_per_month, p.max_bulk_generation
                 FROM qr_user_subscriptions s
                 JOIN qr_subscription_plans p ON p.id = s.plan_id
                 WHERE s.user_id = ? AND s.status = 'active'
                 ORDER BY s.started_at DESC
                 LIMIT 1",
                [$userId]
            );
            return $row ?: null;
        } catch (\Exception $e) {
            Logger::error('QRFeatureService::getPlanLimits error: ' . $e->getMessage());
            return null;
        }
    }

    // ------------------------------------------------------------------
    // Private helpers
    // ------------------------------------------------------------------

    private function getPlanFeatures(int $userId): array
    {
        try {
            $row = $this->db->fetch(
                "SELECT p.features
                 FROM qr_user_subscriptions s
                 JOIN qr_subscription_plans p ON p.id = s.plan_id
                 WHERE s.user_id = ? AND s.status = 'active'
                 ORDER BY s.started_at DESC
                 LIMIT 1",
                [$userId]
            );
            if (!$row || empty($row['features'])) {
                return [];
            }
            $decoded = json_decode($row['features'], true);
            if (!is_array($decoded)) {
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Logger::error('QRFeatureService: corrupt plan features JSON — ' . json_last_error_msg());
                }
                return [];
            }
            return $decoded;
        } catch (\Exception $e) {
            Logger::error('QRFeatureService::getPlanFeatures error: ' . $e->getMessage());
            return [];
        }
    }

    private function getUserRole(int $userId): ?string
    {
        try {
            $row = $this->db->fetch("SELECT role FROM users WHERE id = ?", [$userId]);
            return $row ? ($row['role'] ?? null) : null;
        } catch (\Exception $e) {
            Logger::error('QRFeatureService::getUserRole error: ' . $e->getMessage());
            return null;
        }
    }

    private function getRoleFeatures(string $role): array
    {
        try {
            $rows = $this->db->fetchAll(
                "SELECT feature, enabled FROM qr_role_features WHERE role = ?",
                [$role]
            );
            $result = [];
            foreach ($rows as $r) {
                $result[$r['feature']] = (bool) $r['enabled'];
            }
            return $result;
        } catch (\Exception $e) {
            Logger::error('QRFeatureService::getRoleFeatures error: ' . $e->getMessage());
            return [];
        }
    }

    private function getUserOverrides(int $userId): array
    {
        try {
            $rows = $this->db->fetchAll(
                "SELECT feature, enabled FROM qr_user_features WHERE user_id = ?",
                [$userId]
            );
            $result = [];
            foreach ($rows as $r) {
                $result[$r['feature']] = (bool) $r['enabled'];
            }
            return $result;
        } catch (\Exception $e) {
            Logger::error('QRFeatureService::getUserOverrides error: ' . $e->getMessage());
            return [];
        }
    }
}
