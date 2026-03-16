<?php
/**
 * ResumeX Resume Model
 *
 * @package MMB\Projects\ResumeX\Models
 */

namespace Projects\ResumeX\Models;

use Core\Database;

class ResumeModel
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->ensureTable();
    }

    /**
     * Auto-create the resumex_resumes table if it does not yet exist.
     * Runs at most once per PHP request thanks to a static guard.
     */
    private function ensureTable(): void
    {
        static $ran = false;
        if ($ran) {
            return;
        }
        $ran = true;

        try {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `resumex_resumes` (
                    `id`             INT UNSIGNED NOT NULL AUTO_INCREMENT,
                    `user_id`        INT UNSIGNED NOT NULL,
                    `title`          VARCHAR(255) NOT NULL DEFAULT 'My Resume',
                    `template`       VARCHAR(100) NOT NULL DEFAULT 'midnight-pro',
                    `resume_data`    LONGTEXT     NULL,
                    `theme_settings` LONGTEXT     NULL,
                    `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    KEY `idx_user_id` (`user_id`),
                    KEY `idx_updated_at` (`updated_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (\Exception $e) {
            \Core\Logger::error('ResumeModel::ensureTable error: ' . $e->getMessage());
        }
    }

    /**
     * Get all resumes for a user
     */
    public function getAll(int $userId): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM resumex_resumes WHERE user_id = ? ORDER BY updated_at DESC",
                [$userId]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get a single resume by ID and user ID
     */
    public function get(int $id, int $userId): ?array
    {
        try {
            return $this->db->fetch(
                "SELECT * FROM resumex_resumes WHERE id = ? AND user_id = ?",
                [$id, $userId]
            );
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Count resumes for a user
     */
    public function count(int $userId): int
    {
        try {
            $row = $this->db->fetch(
                "SELECT COUNT(*) as cnt FROM resumex_resumes WHERE user_id = ?",
                [$userId]
            );
            return (int) ($row['cnt'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Create a new resume
     */
    public function create(int $userId, string $title, string $template = 'midnight-pro'): int
    {
        try {
            $defaultData   = $this->getDefaultData();
            $defaultTheme  = $this->getThemePreset($template);
            $this->db->query(
                "INSERT INTO resumex_resumes (user_id, title, template, resume_data, theme_settings, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, NOW(), NOW())",
                [
                    $userId,
                    $title,
                    $template,
                    json_encode($defaultData),
                    json_encode($defaultTheme),
                ]
            );
            return (int) $this->db->lastInsertId();
        } catch (\Exception $e) {
            \Core\Logger::error('ResumeModel::create error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Update a resume
     */
    public function update(int $id, int $userId, array $data): bool
    {
        try {
            $fields = [];
            $values = [];

            if (isset($data['title'])) {
                $fields[] = 'title = ?';
                $values[] = $data['title'];
            }
            if (isset($data['template'])) {
                $fields[] = 'template = ?';
                $values[] = $data['template'];
            }
            if (isset($data['resume_data'])) {
                $fields[] = 'resume_data = ?';
                $values[] = is_array($data['resume_data'])
                    ? json_encode($data['resume_data'])
                    : $data['resume_data'];
            }
            if (isset($data['theme_settings'])) {
                $fields[] = 'theme_settings = ?';
                $values[] = is_array($data['theme_settings'])
                    ? json_encode($data['theme_settings'])
                    : $data['theme_settings'];
            }

            if (empty($fields)) {
                return false;
            }

            $fields[]  = 'updated_at = NOW()';
            $values[]  = $id;
            $values[]  = $userId;

            $this->db->query(
                "UPDATE resumex_resumes SET " . implode(', ', $fields) . " WHERE id = ? AND user_id = ?",
                $values
            );
            return true;
        } catch (\Exception $e) {
            \Core\Logger::error('ResumeModel::update error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a resume
     */
    public function delete(int $id, int $userId): bool
    {
        try {
            $this->db->query(
                "DELETE FROM resumex_resumes WHERE id = ? AND user_id = ?",
                [$id, $userId]
            );
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Duplicate a resume
     */
    public function duplicate(int $id, int $userId): int
    {
        try {
            $resume = $this->get($id, $userId);
            if (!$resume) {
                return 0;
            }
            $this->db->query(
                "INSERT INTO resumex_resumes (user_id, title, template, resume_data, theme_settings, created_at, updated_at)
                 VALUES (?, ?, ?, ?, ?, NOW(), NOW())",
                [
                    $userId,
                    $resume['title'] . ' (Copy)',
                    $resume['template'],
                    $resume['resume_data'],
                    $resume['theme_settings'],
                ]
            );
            return (int) $this->db->lastInsertId();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Default empty resume data structure
     */
    public function getDefaultData(): array
    {
        return [
            'contact'        => [
                'name'     => '',
                'email'    => '',
                'phone'    => '',
                'location' => '',
                'website'  => '',
                'linkedin' => '',
                'github'   => '',
                'photo'    => '',
            ],
            'summary'        => '',
            'experience'     => [],
            'education'      => [],
            'skills'         => [],
            'projects'       => [],
            'certifications' => [],
            'awards'         => [],
            'volunteer'      => [],
            'languages'      => [],
            'hobbies'        => [],
            'references'     => [],
            'publications'   => [],
            'section_order'  => [
                'contact', 'summary', 'experience', 'education', 'skills',
                'projects', 'certifications', 'awards', 'volunteer',
                'languages', 'hobbies', 'references', 'publications',
            ],
            'hidden_sections' => [],
        ];
    }

    /**
     * Get a theme preset by key
     */
    public function getThemePreset(string $key): array
    {
        $presets = $this->getAllThemePresets();
        return $presets[$key] ?? $presets['midnight-pro'];
    }

    /**
     * All 12 theme presets
     */
    public function getAllThemePresets(): array
    {
        return [
            'midnight-pro' => [
                'key'             => 'midnight-pro',
                'name'            => 'Midnight Pro',
                'category'        => 'dark',
                'primaryColor'    => '#00f0ff',
                'secondaryColor'  => '#9945ff',
                'backgroundColor' => '#0a0a0f',
                'surfaceColor'    => '#12121e',
                'textColor'       => '#e0e6ff',
                'textMuted'       => '#6b7280',
                'borderColor'     => 'rgba(0,240,255,0.15)',
                'fontFamily'      => 'Poppins',
                'fontSize'        => '14',
                'fontWeight'      => '400',
                'headerStyle'     => 'gradient',
                'buttonStyle'     => 'rounded',
                'cardStyle'       => 'glass',
                'spacing'         => 'comfortable',
                'layoutMode'      => 'single',
                'iconStyle'       => 'filled',
                'accentHighlights' => true,
                'animations'      => true,
            ],
            'arctic-white' => [
                'key'             => 'arctic-white',
                'name'            => 'Arctic White',
                'category'        => 'light',
                'primaryColor'    => '#0369a1',
                'secondaryColor'  => '#7c3aed',
                'backgroundColor' => '#ffffff',
                'surfaceColor'    => '#f8fafc',
                'textColor'       => '#1e293b',
                'textMuted'       => '#64748b',
                'borderColor'     => '#e2e8f0',
                'fontFamily'      => 'Poppins',
                'fontSize'        => '14',
                'fontWeight'      => '400',
                'headerStyle'     => 'solid',
                'buttonStyle'     => 'rounded',
                'cardStyle'       => 'elevated',
                'spacing'         => 'comfortable',
                'layoutMode'      => 'single',
                'iconStyle'       => 'outline',
                'accentHighlights' => true,
                'animations'      => false,
            ],
            'ocean-blue' => [
                'key'             => 'ocean-blue',
                'name'            => 'Ocean Blue',
                'category'        => 'professional',
                'primaryColor'    => '#0ea5e9',
                'secondaryColor'  => '#06b6d4',
                'backgroundColor' => '#0f172a',
                'surfaceColor'    => '#1e293b',
                'textColor'       => '#e2e8f0',
                'textMuted'       => '#94a3b8',
                'borderColor'     => 'rgba(14,165,233,0.2)',
                'fontFamily'      => 'Inter',
                'fontSize'        => '14',
                'fontWeight'      => '400',
                'headerStyle'     => 'gradient',
                'buttonStyle'     => 'pill',
                'cardStyle'       => 'bordered',
                'spacing'         => 'compact',
                'layoutMode'      => 'two-column',
                'iconStyle'       => 'filled',
                'accentHighlights' => true,
                'animations'      => true,
            ],
            'forest-green' => [
                'key'             => 'forest-green',
                'name'            => 'Forest Green',
                'category'        => 'nature',
                'primaryColor'    => '#22c55e',
                'secondaryColor'  => '#16a34a',
                'backgroundColor' => '#052e16',
                'surfaceColor'    => '#14532d',
                'textColor'       => '#dcfce7',
                'textMuted'       => '#86efac',
                'borderColor'     => 'rgba(34,197,94,0.2)',
                'fontFamily'      => 'Georgia',
                'fontSize'        => '14',
                'fontWeight'      => '400',
                'headerStyle'     => 'solid',
                'buttonStyle'     => 'square',
                'cardStyle'       => 'bordered',
                'spacing'         => 'spacious',
                'layoutMode'      => 'single',
                'iconStyle'       => 'filled',
                'accentHighlights' => false,
                'animations'      => false,
            ],
            'royal-purple' => [
                'key'             => 'royal-purple',
                'name'            => 'Royal Purple',
                'category'        => 'creative',
                'primaryColor'    => '#a855f7',
                'secondaryColor'  => '#ec4899',
                'backgroundColor' => '#0f0520',
                'surfaceColor'    => '#1e1035',
                'textColor'       => '#f3e8ff',
                'textMuted'       => '#c4b5fd',
                'borderColor'     => 'rgba(168,85,247,0.25)',
                'fontFamily'      => 'Montserrat',
                'fontSize'        => '14',
                'fontWeight'      => '400',
                'headerStyle'     => 'gradient',
                'buttonStyle'     => 'rounded',
                'cardStyle'       => 'glass',
                'spacing'         => 'comfortable',
                'layoutMode'      => 'single',
                'iconStyle'       => 'filled',
                'accentHighlights' => true,
                'animations'      => true,
            ],
            'neon-cyber' => [
                'key'             => 'neon-cyber',
                'name'            => 'Neon Cyber',
                'category'        => 'tech',
                'primaryColor'    => '#ff2ec4',
                'secondaryColor'  => '#00f0ff',
                'backgroundColor' => '#000000',
                'surfaceColor'    => '#0a0a0a',
                'textColor'       => '#ffffff',
                'textMuted'       => '#888888',
                'borderColor'     => 'rgba(255,46,196,0.3)',
                'fontFamily'      => 'JetBrains Mono',
                'fontSize'        => '13',
                'fontWeight'      => '400',
                'headerStyle'     => 'neon',
                'buttonStyle'     => 'square',
                'cardStyle'       => 'neon',
                'spacing'         => 'compact',
                'layoutMode'      => 'single',
                'iconStyle'       => 'filled',
                'accentHighlights' => true,
                'animations'      => true,
            ],
            'warm-amber' => [
                'key'             => 'warm-amber',
                'name'            => 'Warm Amber',
                'category'        => 'warm',
                'primaryColor'    => '#f59e0b',
                'secondaryColor'  => '#ef4444',
                'backgroundColor' => '#1c1409',
                'surfaceColor'    => '#292010',
                'textColor'       => '#fef3c7',
                'textMuted'       => '#d97706',
                'borderColor'     => 'rgba(245,158,11,0.2)',
                'fontFamily'      => 'Merriweather',
                'fontSize'        => '14',
                'fontWeight'      => '400',
                'headerStyle'     => 'solid',
                'buttonStyle'     => 'rounded',
                'cardStyle'       => 'elevated',
                'spacing'         => 'comfortable',
                'layoutMode'      => 'single',
                'iconStyle'       => 'outline',
                'accentHighlights' => true,
                'animations'      => false,
            ],
            'corporate-gray' => [
                'key'             => 'corporate-gray',
                'name'            => 'Corporate Gray',
                'category'        => 'professional',
                'primaryColor'    => '#6366f1',
                'secondaryColor'  => '#8b5cf6',
                'backgroundColor' => '#ffffff',
                'surfaceColor'    => '#f9fafb',
                'textColor'       => '#111827',
                'textMuted'       => '#6b7280',
                'borderColor'     => '#d1d5db',
                'fontFamily'      => 'Roboto',
                'fontSize'        => '14',
                'fontWeight'      => '400',
                'headerStyle'     => 'minimal',
                'buttonStyle'     => 'square',
                'cardStyle'       => 'flat',
                'spacing'         => 'compact',
                'layoutMode'      => 'two-column',
                'iconStyle'       => 'outline',
                'accentHighlights' => false,
                'animations'      => false,
            ],
            'rose-petal' => [
                'key'             => 'rose-petal',
                'name'            => 'Rose Petal',
                'category'        => 'pastel',
                'primaryColor'    => '#f43f5e',
                'secondaryColor'  => '#fb7185',
                'backgroundColor' => '#fff1f2',
                'surfaceColor'    => '#ffe4e6',
                'textColor'       => '#1e0a0d',
                'textMuted'       => '#9f1239',
                'borderColor'     => '#fecdd3',
                'fontFamily'      => 'Nunito',
                'fontSize'        => '14',
                'fontWeight'      => '400',
                'headerStyle'     => 'gradient',
                'buttonStyle'     => 'pill',
                'cardStyle'       => 'elevated',
                'spacing'         => 'spacious',
                'layoutMode'      => 'single',
                'iconStyle'       => 'filled',
                'accentHighlights' => true,
                'animations'      => true,
            ],
            'vintage-ink' => [
                'key'             => 'vintage-ink',
                'name'            => 'Vintage Ink',
                'category'        => 'classic',
                'primaryColor'    => '#92400e',
                'secondaryColor'  => '#78350f',
                'backgroundColor' => '#fefce8',
                'surfaceColor'    => '#fef9c3',
                'textColor'       => '#1c1917',
                'textMuted'       => '#57534e',
                'borderColor'     => '#d6d3d1',
                'fontFamily'      => 'Playfair Display',
                'fontSize'        => '14',
                'fontWeight'      => '400',
                'headerStyle'     => 'underline',
                'buttonStyle'     => 'square',
                'cardStyle'       => 'flat',
                'spacing'         => 'spacious',
                'layoutMode'      => 'single',
                'iconStyle'       => 'outline',
                'accentHighlights' => false,
                'animations'      => false,
            ],
            'sky-minimal' => [
                'key'             => 'sky-minimal',
                'name'            => 'Sky Minimal',
                'category'        => 'light',
                'primaryColor'    => '#38bdf8',
                'secondaryColor'  => '#0284c7',
                'backgroundColor' => '#f0f9ff',
                'surfaceColor'    => '#e0f2fe',
                'textColor'       => '#0c4a6e',
                'textMuted'       => '#0369a1',
                'borderColor'     => '#bae6fd',
                'fontFamily'      => 'DM Sans',
                'fontSize'        => '14',
                'fontWeight'      => '400',
                'headerStyle'     => 'minimal',
                'buttonStyle'     => 'pill',
                'cardStyle'       => 'flat',
                'spacing'         => 'comfortable',
                'layoutMode'      => 'single',
                'iconStyle'       => 'outline',
                'accentHighlights' => true,
                'animations'      => false,
            ],
            'crimson-elite' => [
                'key'             => 'crimson-elite',
                'name'            => 'Crimson Elite',
                'category'        => 'bold',
                'primaryColor'    => '#dc2626',
                'secondaryColor'  => '#b91c1c',
                'backgroundColor' => '#0a0000',
                'surfaceColor'    => '#1a0505',
                'textColor'       => '#fee2e2',
                'textMuted'       => '#fca5a5',
                'borderColor'     => 'rgba(220,38,38,0.25)',
                'fontFamily'      => 'Bebas Neue',
                'fontSize'        => '14',
                'fontWeight'      => '700',
                'headerStyle'     => 'solid',
                'buttonStyle'     => 'square',
                'cardStyle'       => 'bordered',
                'spacing'         => 'compact',
                'layoutMode'      => 'single',
                'iconStyle'       => 'filled',
                'accentHighlights' => true,
                'animations'      => true,
            ],
        ];
    }
}
