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
    public const ALL_FEATURES = [
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
        'scan_limit',
        'utm_tracking',
        'qr_label',
        'content_type',
        'design_presets',
        'logo_remove_bg',
    ];

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Resolve the full effective feature map for a user.
     * Returns ['feature_key' => bool, ...]
     *
     * Two modes, controlled by the '_use_plan' flag in qr_user_features:
     *
     *   Plan mode (default / _use_plan = 1):
     *     role features → plan features overlay → per-user overrides SKIPPED
     *
     *   Override mode (_use_plan = 0):
     *     role features → plan SKIPPED → per-user overrides overlay
     *
     * When NO configuration exists at all → all features ALLOWED (permissive
     * default so the system works out-of-the-box before admin configures anything).
     */
    public function getFeatures(int $userId): array
    {
        // Start with defaults (all disabled)
        $features = array_fill_keys(self::ALL_FEATURES, false);
        $hasAnyConfig = false;

        // Layer 1: Role features (always applied as base)
        $role = $this->getUserRole($userId);
        if ($role) {
            $roleFeatures = $this->getRoleFeatures($role);
            if (!empty($roleFeatures)) {
                $hasAnyConfig = true;
                foreach ($roleFeatures as $key => $val) {
                    if (array_key_exists($key, $features)) {
                        $features[$key] = (bool) $val;
                    }
                }
            }
        }

        // Determine mode
        $usePlanMode = $this->getUserPlanMode($userId);

        if ($usePlanMode) {
            // Layer 2: Plan features override role (user overrides skipped)
            $planFeatures = $this->getPlanFeatures($userId);
            if (!empty($planFeatures)) {
                $hasAnyConfig = true;
                foreach ($planFeatures as $key => $val) {
                    if (array_key_exists($key, $features)) {
                        $features[$key] = (bool) $val;
                    }
                }
            }
        } else {
            // Layer 3: Per-user overrides override role (plan skipped)
            $userOverrides = $this->getUserOverrides($userId);
            if (!empty($userOverrides)) {
                $hasAnyConfig = true;
                foreach ($userOverrides as $key => $val) {
                    if (array_key_exists($key, $features)) {
                        $features[$key] = (bool) $val;
                    }
                }
            }
        }

        // If no configuration exists at all, allow everything (permissive default).
        if (!$hasAnyConfig) {
            return array_fill_keys(self::ALL_FEATURES, true);
        }

        return $features;
    }

    /**
     * Check whether a user has access to a specific feature.
     * Unknown feature keys return false (deny by default).
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

            // Fallback: if user has no active subscription, apply the 'free' default plan
            // so that plan-level feature toggles always affect users without subscriptions.
            if (!$row || empty($row['features'])) {
                $row = $this->db->fetch(
                    "SELECT features FROM qr_subscription_plans
                     WHERE slug IN ('free', 'default') AND status = 'active'
                     ORDER BY price ASC LIMIT 1"
                );
            }

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

    /**
     * Returns true when the user is in "plan mode" (default):
     *   - plan features overlay role defaults; per-user overrides are skipped
     * Returns false when the user is in "custom override mode":
     *   - per-user overrides overlay role defaults; plan features are skipped
     *
     * Stored as feature = '_use_plan' in qr_user_features.
     * Missing row = true (default to plan mode).
     */
    private function getUserPlanMode(int $userId): bool
    {
        try {
            $row = $this->db->fetch(
                "SELECT enabled FROM qr_user_features WHERE user_id = ? AND feature = '_use_plan' LIMIT 1",
                [$userId]
            );
            if ($row === null || $row === false) {
                return true; // default: use plan settings
            }
            return (bool) $row['enabled'];
        } catch (\Exception $e) {
            return true; // default to plan mode on error
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
                "SELECT feature, enabled FROM qr_user_features WHERE user_id = ? AND feature != '_use_plan'",
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
