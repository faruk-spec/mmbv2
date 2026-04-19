<?php
/**
 * ConvertX Feature Service
 *
 * Resolves effective per-user feature/page access:
 * 1) Plan defaults from convertx_subscription_plans.features (or free fallback)
 * 2) Per-user overrides from convertx_user_features
 */
namespace Projects\ConvertX\Services;

use Core\Database;

class FeatureService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getFeatures(int $userId): array
    {
        $features = $this->getPlanFeatures($userId);
        if (empty($features)) {
            $features = $this->getDefaultFeatures();
        }

        try {
            $rows = $this->db->fetchAll(
                "SELECT feature, enabled FROM convertx_user_features WHERE user_id = :uid",
                ['uid' => $userId]
            ) ?: [];
            foreach ($rows as $row) {
                $key = (string) ($row['feature'] ?? '');
                if ($key !== '' && array_key_exists($key, $features)) {
                    $features[$key] = (bool) $row['enabled'];
                }
            }
        } catch (\Exception $e) {
            // keep plan defaults
        }

        return $features;
    }

    public function can(int $userId, string $featureKey): bool
    {
        $features = $this->getFeatures($userId);
        return (bool) ($features[$featureKey] ?? true);
    }

    private function getPlanFeatures(int $userId): array
    {
        $allKeys = array_keys($this->getDefaultFeatures());
        $features = [];

        try {
            // Prefer explicit ConvertX subscription assignment
            $row = $this->db->fetch(
                "SELECT p.features
                 FROM convertx_user_subscriptions s
                 JOIN convertx_subscription_plans p ON p.id = s.plan_id
                 WHERE s.user_id = :uid AND s.status = 'active'
                 ORDER BY s.started_at DESC
                 LIMIT 1",
                ['uid' => $userId]
            );
            if ($row && !empty($row['features'])) {
                $features = json_decode((string) $row['features'], true) ?: [];
            }
        } catch (\Exception $e) {
            // ignore
        }

        if (empty($features)) {
            try {
                // Fallback from platform plan slug mapping
                $row = $this->db->fetch(
                    "SELECT p.features
                     FROM user_plans up
                     JOIN convertx_subscription_plans p ON p.slug = up.plan_slug
                     WHERE up.user_id = :uid
                     LIMIT 1",
                    ['uid' => $userId]
                );
                if ($row && !empty($row['features'])) {
                    $features = json_decode((string) $row['features'], true) ?: [];
                }
            } catch (\Exception $e) {
                // ignore
            }
        }

        if (empty($features)) {
            try {
                $row = $this->db->fetch(
                    "SELECT features FROM convertx_subscription_plans
                     WHERE slug = 'free' AND status = 'active'
                     LIMIT 1"
                );
                if ($row && !empty($row['features'])) {
                    $features = json_decode((string) $row['features'], true) ?: [];
                }
            } catch (\Exception $e) {
                // ignore
            }
        }

        $merged = $this->getDefaultFeatures();
        foreach ($allKeys as $k) {
            if (array_key_exists($k, $features)) {
                $merged[$k] = (bool) $features[$k];
            }
        }
        return $merged;
    }

    private function getDefaultFeatures(): array
    {
        return [
            'page_dashboard'    => true,
            'page_convert'      => true,
            'page_ai_process'   => true,
            'page_batch'        => true,
            'page_history'      => true,
            'page_ocr'          => true,
            'page_ocr_ai'       => true,
            'page_pdf_merge'    => true,
            'page_pdf_split'    => true,
            'page_pdf_compress' => true,
            'page_img_compress' => true,
            'page_img_resize'   => true,
            'page_img_crop'     => true,
            'page_img_watermark'=> true,
            'page_img_rotate'   => true,
            'page_img_meme'     => true,
            'page_img_editor'   => true,
            'page_img_upscale'  => true,
            'page_img_remove_bg'=> true,
            'page_docs'         => true,
            'page_apikeys'      => true,
            'page_plan'         => true,
            'page_settings'     => true,
        ];
    }
}

