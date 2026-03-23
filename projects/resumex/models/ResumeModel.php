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
    public function create(int $userId, string $title, string $template = 'ocean-blue'): int
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
        return $presets[$key] ?? $presets['ocean-blue'];
    }

    /**
     * Two supported theme presets
     */
    public function getAllThemePresets(): array
    {
        return [
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
                'layoutStyle'     => 'sidebar-dark',
                'colorVariants'   => [
                    ['label' => 'Blue',   'primary' => '#0ea5e9', 'secondary' => '#06b6d4'],
                    ['label' => 'Violet', 'primary' => '#8b5cf6', 'secondary' => '#a78bfa'],
                    ['label' => 'Green',  'primary' => '#10b981', 'secondary' => '#34d399'],
                    ['label' => 'Rose',   'primary' => '#f43f5e', 'secondary' => '#fb7185'],
                ],
            ],
            'academic-clean' => [
                'key' => 'academic-clean', 'name' => 'Academic Clean', 'category' => 'academic',
                'primaryColor' => '#1d4ed8', 'secondaryColor' => '#1e40af',
                'backgroundColor' => '#ffffff', 'surfaceColor' => '#eff6ff',
                'textColor' => '#1e3a5f', 'textMuted' => '#3b5998',
                'borderColor' => '#bfdbfe', 'fontFamily' => 'Merriweather', 'fontSize' => '13',
                'fontWeight' => '400', 'headerStyle' => 'underline', 'buttonStyle' => 'square',
                'cardStyle' => 'flat', 'spacing' => 'spacious', 'layoutMode' => 'single',
                'iconStyle' => 'outline', 'accentHighlights' => false, 'animations' => false,
                'layoutStyle'    => 'academic',
                'colorVariants'  => [
                    ['label' => 'Blue',   'primary' => '#1d4ed8', 'secondary' => '#1e40af'],
                    ['label' => 'Teal',   'primary' => '#0d9488', 'secondary' => '#0f766e'],
                    ['label' => 'Purple', 'primary' => '#7c3aed', 'secondary' => '#6d28d9'],
                    ['label' => 'Gray',   'primary' => '#374151', 'secondary' => '#1f2937'],
                ],
            ],
        ];
    }
}
