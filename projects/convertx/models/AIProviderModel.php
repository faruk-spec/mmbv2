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
