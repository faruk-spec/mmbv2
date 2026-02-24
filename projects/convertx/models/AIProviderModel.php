<?php
/**
 * AI Provider Model
 *
 * Stores and retrieves AI provider configurations for the ConvertX platform.
 * Supports provider routing, cost tracking, and health monitoring.
 *
 * @package MMB\Projects\ConvertX\Models
 */

namespace Projects\ConvertX\Models;

use Core\Database;

class AIProviderModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureSchema();
    }

    /**
     * Create the AI provider tables if they do not yet exist, then seed defaults.
     * Runs silently so a missing table never crashes the first request.
     */
    private function ensureSchema(): void
    {
        try {
            $this->db->query(
                "CREATE TABLE IF NOT EXISTS convertx_ai_providers (
                    id                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    name                VARCHAR(100)  NOT NULL,
                    slug                VARCHAR(50)   NOT NULL,
                    base_url            VARCHAR(512)  NULL,
                    api_key             VARCHAR(512)  NULL,
                    model               VARCHAR(100)  NULL,
                    capabilities        TEXT          NOT NULL DEFAULT '[]',
                    allowed_tiers       TEXT          NOT NULL DEFAULT '[\"free\",\"pro\",\"enterprise\"]',
                    priority            TINYINT       NOT NULL DEFAULT 10,
                    cost_per_1k_tokens  DECIMAL(10,6) NOT NULL DEFAULT 0.002000,
                    is_active           TINYINT(1)    NOT NULL DEFAULT 1,
                    is_healthy          TINYINT(1)    NOT NULL DEFAULT 1,
                    total_tokens_used   BIGINT        NOT NULL DEFAULT 0,
                    total_cost_usd      DECIMAL(12,4) NOT NULL DEFAULT 0,
                    last_used_at        DATETIME      NULL,
                    health_checked_at   DATETIME      NULL,
                    created_at          DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    UNIQUE KEY uq_slug (slug),
                    INDEX idx_active (is_active, priority)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
            $this->db->query(
                "CREATE TABLE IF NOT EXISTS convertx_provider_usage (
                    id          BIGINT AUTO_INCREMENT PRIMARY KEY,
                    provider_id INT UNSIGNED    NOT NULL,
                    tokens_used INT             NOT NULL DEFAULT 0,
                    cost_usd    DECIMAL(10,6)   NOT NULL DEFAULT 0,
                    recorded_at DATETIME        NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_provider (provider_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
            );
            $this->seedDefaultProviders();
        } catch (\Exception $e) {
            // Log at warning level — schema creation failures are recoverable
            // (tables may already exist, or DB may be temporarily unavailable).
            Logger::warning('AIProviderModel::ensureSchema - ' . $e->getMessage());
        }
    }

    /**
     * Seed the three default providers (OpenAI, HuggingFace, Tesseract) via
     * INSERT IGNORE — idempotent; does nothing if the rows already exist.
     * Tesseract is seeded as active only when the binary is present on disk.
     */
    private function seedDefaultProviders(): void
    {
        // Use a static flag so we only shell_exec once per request
        static $seeded = false;
        if ($seeded) {
            return;
        }
        $seeded = true;

        $tessActive = !empty(trim((string) shell_exec('which tesseract 2>/dev/null'))) ? 1 : 0;

        $this->db->query(
            "INSERT IGNORE INTO convertx_ai_providers
                 (name, slug, base_url, model, capabilities, allowed_tiers, priority, cost_per_1k_tokens, is_active)
             VALUES
                 ('OpenAI',           'openai',      'https://api.openai.com',
                  'gpt-4o-mini',
                  '[\"ocr\",\"summarization\",\"translation\",\"classification\"]',
                  '[\"pro\",\"enterprise\"]', 1, 0.000150, 1),
                 ('HuggingFace',      'huggingface', 'https://api-inference.huggingface.co',
                  'facebook/bart-large-cnn',
                  '[\"summarization\",\"classification\"]',
                  '[\"free\",\"pro\",\"enterprise\"]', 5, 0.000010, 1),
                 ('Tesseract (Local)','tesseract',   NULL, NULL,
                  '[\"ocr\"]',
                  '[\"free\",\"pro\",\"enterprise\"]', 3, 0.000000, :tessActive)",
            ['tessActive' => $tessActive]
        );
    }

    /**
     * Return all active providers ordered by priority (lower = higher priority).
     */
    public function getActive(): array
    {
        return $this->db->fetchAll(
            "SELECT * FROM convertx_ai_providers
             WHERE is_active = 1
             ORDER BY priority ASC"
        ) ?: [];
    }

    /**
     * Find the best provider for a given capability and plan tier.
     *
     * Selection criteria (in order):
     *   1. Provider supports the requested capability
     *   2. Provider is enabled for the plan tier
     *   3. Lowest priority value (highest precedence)
     *
     * @param string $capability  e.g. 'ocr', 'summarization', 'translation'
     * @param string $planTier    e.g. 'free', 'pro', 'enterprise'
     */
    public function selectProvider(string $capability, string $planTier = 'free'): ?array
    {
        $providers = $this->getActive();
        foreach ($providers as $provider) {
            $capabilities = json_decode($provider['capabilities'] ?? '[]', true);
            $allowedTiers = json_decode($provider['allowed_tiers'] ?? '["free","pro","enterprise"]', true);

            if (in_array($capability, $capabilities, true)
                && in_array($planTier, $allowedTiers, true)
            ) {
                return $provider;
            }
        }
        return null;
    }

    /**
     * Record a usage event (tokens + cost) for a provider.
     */
    public function recordUsage(int $providerId, int $tokens, float $cost): void
    {
        $this->db->query(
            "INSERT INTO convertx_provider_usage
                (provider_id, tokens_used, cost_usd, recorded_at)
             VALUES
                (:pid, :tokens, :cost, NOW())",
            ['pid' => $providerId, 'tokens' => $tokens, 'cost' => $cost]
        );

        // Update aggregated totals on the provider row
        $this->db->query(
            "UPDATE convertx_ai_providers
             SET total_tokens_used = total_tokens_used + :tokens,
                 total_cost_usd    = total_cost_usd    + :cost,
                 last_used_at      = NOW()
             WHERE id = :id",
            ['id' => $providerId, 'tokens' => $tokens, 'cost' => $cost]
        );
    }

    /**
     * Mark a provider healthy or unhealthy.
     */
    public function setHealth(int $providerId, bool $healthy): void
    {
        $this->db->query(
            "UPDATE convertx_ai_providers
             SET is_healthy = :healthy, health_checked_at = NOW()
             WHERE id = :id",
            ['id' => $providerId, 'healthy' => (int) $healthy]
        );
    }

    /**
     * Find a provider by slug (e.g. 'openai', 'huggingface').
     */
    public function findBySlug(string $slug): ?array
    {
        $row = $this->db->fetch(
            "SELECT * FROM convertx_ai_providers WHERE slug = :slug LIMIT 1",
            ['slug' => $slug]
        );
        return $row ?: null;
    }

    /**
     * Aggregate cost per provider for a date range (admin dashboard).
     */
    public function getCostReport(string $from, string $to): array
    {
        return $this->db->fetchAll(
            "SELECT p.name, p.slug,
                    COALESCE(SUM(u.tokens_used), 0) AS tokens,
                    COALESCE(SUM(u.cost_usd), 0)    AS cost_usd
             FROM convertx_ai_providers p
             LEFT JOIN convertx_provider_usage u
                    ON u.provider_id = p.id
                   AND DATE(u.recorded_at) BETWEEN :from AND :to
             GROUP BY p.id
             ORDER BY cost_usd DESC",
            ['from' => $from, 'to' => $to]
        ) ?: [];
    }
}
