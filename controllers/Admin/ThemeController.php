<?php
/**
 * Admin Theme Controller
 * Manages multi-theme system and admin customization panel
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Auth;
use Core\Logger;

class ThemeController extends BaseController
{
    public function __construct()
    {
        $this->requireAdmin();
    }

    /**
     * Theme management panel
     */
    public function index(): void
    {
        $this->requirePermission('settings');

        $db = Database::getInstance();

        // Ensure table exists
        $this->ensureTable($db);

        // Get current theme settings
        $settings = $this->getThemeSettings($db);

        // Parse custom overrides
        $customOverrides = [];
        if (!empty($settings['custom_overrides'])) {
            $customOverrides = json_decode($settings['custom_overrides'], true) ?: [];
        }

        $this->view('admin/settings/theme', [
            'title'              => 'Universal Theme',
            'useUniversalTheme'  => ($settings['use_universal_theme'] ?? 'no') === 'yes',
            'activeTheme'        => $settings['active_theme'] ?? 'default',
            'defaultMode'        => $settings['default_mode'] ?? 'dark',
            'customOverrides'    => $customOverrides,
        ], 'admin');
    }

    /**
     * Update theme settings
     */
    public function update(): void
    {
        $this->requirePermission('settings');

        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid security token.');
            $this->redirect('/admin/settings/theme');
            return;
        }

        $db = Database::getInstance();
        $this->ensureTable($db);

        try {
            $useUniversalTheme = $this->input('use_universal_theme', 'no');
            $activeTheme = $this->input('active_theme', 'default');
            $defaultMode = $this->input('default_mode', 'dark');

            // Validate toggle
            if (!in_array($useUniversalTheme, ['yes', 'no'])) {
                $useUniversalTheme = 'no';
            }

            // Validate theme name
            $allowedThemes = ['default', 'soft', 'corporate', 'neon'];
            if (!in_array($activeTheme, $allowedThemes)) {
                $activeTheme = 'default';
            }

            // Validate mode
            if (!in_array($defaultMode, ['dark', 'light'])) {
                $defaultMode = 'dark';
            }

            $db->beginTransaction();

            // Save settings
            $this->setSetting($db, 'use_universal_theme', $useUniversalTheme);
            $this->setSetting($db, 'active_theme', $activeTheme);
            $this->setSetting($db, 'default_mode', $defaultMode);

            // Process custom overrides
            $overrides = [];
            $overrideKeys = [
                'cyan', 'magenta', 'green', 'orange', 'purple', 'red',
                'bg_primary_dark', 'bg_secondary_dark', 'bg_card_dark',
                'bg_primary_light', 'bg_secondary_light', 'bg_card_light',
                'text_primary_dark', 'text_secondary_dark', 'text_tertiary_dark',
                'text_primary_light', 'text_secondary_light', 'text_tertiary_light',
                'radius_level', 'shadow_intensity',
            ];

            foreach ($overrideKeys as $key) {
                $val = $this->input('override_' . $key);
                if ($val !== null && $val !== '') {
                    $overrides[$key] = $val;
                }
            }

            $this->setSetting($db, 'custom_overrides', !empty($overrides) ? json_encode($overrides) : null);

            $db->commit();

            // Also update the navbar_settings default_theme to keep in sync
            try {
                $db->query(
                    "UPDATE navbar_settings SET default_theme = ? WHERE id = 1",
                    [$defaultMode]
                );
            } catch (\Exception $e) {
                // Table might not exist
            }

            Logger::info('Theme settings updated', [
                'theme' => $activeTheme,
                'mode'  => $defaultMode,
                'admin' => Auth::id(),
            ]);

            $this->flash('success', 'Theme settings updated successfully.');
        } catch (\Exception $e) {
            try { $db->rollback(); } catch (\Exception $re) { /* already rolled back */ }
            Logger::error('Theme update error: ' . $e->getMessage());
            $this->flash('error', 'Failed to update theme settings.');
        }

        $this->redirect('/admin/settings/theme');
    }

    /**
     * API endpoint: get current theme for JS runtime
     */
    public function getThemeApi(): void
    {
        $db = Database::getInstance();
        $this->ensureTable($db);
        $settings = $this->getThemeSettings($db);

        $customOverrides = [];
        if (!empty($settings['custom_overrides'])) {
            $customOverrides = json_decode($settings['custom_overrides'], true) ?: [];
        }

        $this->json([
            'enabled'         => ($settings['use_universal_theme'] ?? 'no') === 'yes',
            'theme'           => $settings['active_theme'] ?? 'default',
            'mode'            => $settings['default_mode'] ?? 'dark',
            'customOverrides' => $customOverrides,
        ]);
    }

    /**
     * Ensure theme_settings table exists
     */
    private function ensureTable(Database $db): void
    {
        try {
            $db->query("SELECT 1 FROM theme_settings LIMIT 1");
        } catch (\Exception $e) {
            // Create table
            $db->query("
                CREATE TABLE IF NOT EXISTS `theme_settings` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `setting_key` VARCHAR(100) NOT NULL UNIQUE,
                    `setting_value` TEXT DEFAULT NULL,
                    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");
            $db->query("
                INSERT IGNORE INTO `theme_settings` (`setting_key`, `setting_value`) VALUES
                    ('use_universal_theme', 'no'),
                    ('active_theme', 'default'),
                    ('default_mode', 'dark'),
                    ('custom_overrides', NULL)
            ");
        }
    }

    /**
     * Get all theme settings as associative array
     */
    private function getThemeSettings(Database $db): array
    {
        $rows = $db->fetchAll("SELECT setting_key, setting_value FROM theme_settings");
        $map  = [];
        foreach ($rows as $row) {
            $map[$row['setting_key']] = $row['setting_value'];
        }
        return $map;
    }

    /**
     * Set a single theme setting (upsert)
     */
    private function setSetting(Database $db, string $key, ?string $value): void
    {
        $existing = $db->fetch("SELECT id FROM theme_settings WHERE setting_key = ?", [$key]);
        if ($existing) {
            $db->query("UPDATE theme_settings SET setting_value = ? WHERE setting_key = ?", [$value, $key]);
        } else {
            $db->query("INSERT INTO theme_settings (setting_key, setting_value) VALUES (?, ?)", [$key, $value]);
        }
    }

    /**
     * Static helper: load theme settings for layouts
     * Returns [ 'enabled' => bool, 'theme' => string, 'mode' => string, 'overrides' => array ]
     * When use_universal_theme is 'no', returns enabled=false so layouts skip applying overrides.
     */
    public static function loadThemeForLayout(): array
    {
        $defaults = ['enabled' => false, 'theme' => 'default', 'mode' => 'dark', 'overrides' => []];
        try {
            $db = Database::getInstance();
            // Check if table exists
            try {
                $rows = $db->fetchAll("SELECT setting_key, setting_value FROM theme_settings");
            } catch (\Exception $e) {
                return $defaults;
            }
            $map = [];
            foreach ($rows as $row) {
                $map[$row['setting_key']] = $row['setting_value'];
            }

            // Check if universal theme is enabled
            $enabled = ($map['use_universal_theme'] ?? 'no') === 'yes';

            $overrides = [];
            if (!empty($map['custom_overrides'])) {
                $overrides = json_decode($map['custom_overrides'], true) ?: [];
            }
            return [
                'enabled'   => $enabled,
                'theme'     => $map['active_theme'] ?? 'default',
                'mode'      => $map['default_mode'] ?? 'dark',
                'overrides' => $overrides,
            ];
        } catch (\Exception $e) {
            return $defaults;
        }
    }

    /**
     * Sanitize a CSS value to prevent injection.
     * Only allows hex colors, named CSS values, and safe CSS primitives.
     */
    public static function sanitizeCssValue(string $value): string
    {
        $value = trim($value);
        // Strip any characters that could break out of CSS value context
        $value = preg_replace('/[^a-zA-Z0-9#.,()%\s\-]/', '', $value);
        // Prevent CSS injection via dangerous constructs
        $lower = strtolower($value);
        $blocked = ['expression', 'url', 'import', 'javascript', 'behavior', 'binding', 'moz-binding'];
        foreach ($blocked as $keyword) {
            if (strpos($lower, $keyword) !== false) {
                return '';
            }
        }
        // Validate hex colors specifically
        if (strpos($value, '#') === 0) {
            if (!preg_match('/^#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?$/', $value)) {
                return '';
            }
        }
        return $value;
    }
}
