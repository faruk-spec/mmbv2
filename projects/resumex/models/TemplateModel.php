<?php
/**
 * ResumeX Template Model
 *
 * Manages custom (user-uploaded) resume templates stored on disk and tracked in
 * the database.  Built-in presets defined in ResumeModel::getAllThemePresets()
 * are not touched by this model.
 *
 * @package MMB\Projects\ResumeX\Models
 */

namespace Projects\ResumeX\Models;

use Core\Database;
use Core\Logger;

class TemplateModel
{
    private Database $db;

    /** Absolute path to the directory that holds uploaded template PHP files. */
    private string $storageDir;

    /** Required top-level keys that every template definition must provide. */
    private const REQUIRED_KEYS = [
        'key', 'name', 'category',
        'primaryColor', 'secondaryColor', 'backgroundColor',
        'surfaceColor', 'textColor', 'textMuted', 'borderColor',
        'fontFamily', 'fontSize', 'fontWeight',
        'headerStyle', 'buttonStyle', 'cardStyle',
        'spacing', 'layoutMode', 'iconStyle',
        'accentHighlights', 'animations', 'layoutStyle',
        'colorVariants',
    ];

    public function __construct()
    {
        $this->db         = Database::getInstance();
        $this->storageDir = BASE_PATH . '/storage/uploads/resumex/templates';
        $this->ensureTable();
        $this->ensureStorageDir();
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Schema bootstrap
    // ──────────────────────────────────────────────────────────────────────────

    private function ensureTable(): void
    {
        static $ran = false;
        if ($ran) {
            return;
        }
        $ran = true;

        try {
            $this->db->query("
                CREATE TABLE IF NOT EXISTS `resumex_templates` (
                    `id`          INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                    `key`         VARCHAR(100)     NOT NULL,
                    `name`        VARCHAR(255)     NOT NULL,
                    `category`    VARCHAR(100)     NOT NULL DEFAULT 'custom',
                    `file_name`   VARCHAR(255)     NOT NULL,
                    `uploaded_by` INT UNSIGNED     NOT NULL,
                    `is_active`   TINYINT(1)       NOT NULL DEFAULT 1,
                    `created_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at`  DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP
                                                            ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uk_key` (`key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
        } catch (\Exception $e) {
            Logger::error('TemplateModel::ensureTable error: ' . $e->getMessage());
        }
    }

    private function ensureStorageDir(): void
    {
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Public API
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Return all active custom templates as theme-preset arrays, keyed by their
     * template key.  Templates whose backing file is missing are skipped.
     *
     * @return array<string, array>
     */
    public function getAllCustomTemplates(): array
    {
        $rows = $this->fetchActiveRows();
        $out  = [];

        foreach ($rows as $row) {
            $preset = $this->loadTemplateFile($row['file_name']);
            if ($preset !== null) {
                $out[$preset['key']] = $preset;
            }
        }

        return $out;
    }

    /**
     * Return all custom template rows from the database (for the admin listing).
     *
     * @return array[]
     */
    public function getAllRows(): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM resumex_templates ORDER BY created_at DESC"
            );
        } catch (\Exception $e) {
            Logger::error('TemplateModel::getAllRows error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Validate and store an uploaded template file.
     *
     * @param  array  $file        Entry from $_FILES (e.g. $_FILES['template_file'])
     * @param  int    $uploadedBy  User ID of the uploader
     * @return array{success: bool, error?: string, template?: array}
     */
    public function upload(array $file, int $uploadedBy): array
    {
        // ── Basic upload checks ──────────────────────────────────────────────
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'File upload failed (code ' . ($file['error'] ?? '?') . ').'];
        }

        $originalName = $file['name'] ?? '';
        if (strtolower(pathinfo($originalName, PATHINFO_EXTENSION)) !== 'php') {
            return ['success' => false, 'error' => 'Only .php template files are accepted.'];
        }

        if ($file['size'] > 512 * 1024) {
            return ['success' => false, 'error' => 'Template file must be smaller than 512 KB.'];
        }

        // ── Load and validate the template definition ────────────────────────
        $preset = $this->evalTemplateFile($file['tmp_name']);
        if ($preset === null) {
            return ['success' => false, 'error' => 'Template file must return an array. Check the sample template for the required format.'];
        }

        $validationError = $this->validatePreset($preset);
        if ($validationError !== null) {
            return ['success' => false, 'error' => $validationError];
        }

        // ── Reject duplicate keys ────────────────────────────────────────────
        if ($this->keyExists($preset['key'])) {
            return ['success' => false, 'error' => "A template with key \"{$preset['key']}\" already exists. Use a unique key."];
        }

        // ── Persist the file ─────────────────────────────────────────────────
        $fileName = $preset['key'] . '_' . time() . '.php';
        $dest     = $this->storageDir . '/' . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return ['success' => false, 'error' => 'Could not save the template file to disk.'];
        }

        // ── Write DB record ──────────────────────────────────────────────────
        try {
            $this->db->query(
                "INSERT INTO resumex_templates (`key`, `name`, `category`, `file_name`, `uploaded_by`)
                 VALUES (?, ?, ?, ?, ?)",
                [
                    $preset['key'],
                    $preset['name'],
                    $preset['category'],
                    $fileName,
                    $uploadedBy,
                ]
            );
        } catch (\Exception $e) {
            // Roll back the file
            @unlink($dest);
            Logger::error('TemplateModel::upload DB error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error while saving template.'];
        }

        return ['success' => true, 'template' => $preset];
    }

    /**
     * Delete a custom template by database ID.
     *
     * @return array{success: bool, error?: string}
     */
    public function delete(int $id): array
    {
        try {
            $row = $this->db->fetch(
                "SELECT * FROM resumex_templates WHERE id = ?",
                [$id]
            );

            if (!$row) {
                return ['success' => false, 'error' => 'Template not found.'];
            }

            // Remove file
            $path = $this->storageDir . '/' . $row['file_name'];
            if (file_exists($path)) {
                @unlink($path);
            }

            $this->db->query("DELETE FROM resumex_templates WHERE id = ?", [$id]);

            return ['success' => true];
        } catch (\Exception $e) {
            Logger::error('TemplateModel::delete error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error while deleting template.'];
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Private helpers
    // ──────────────────────────────────────────────────────────────────────────

    /** @return array[] */
    private function fetchActiveRows(): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT * FROM resumex_templates WHERE is_active = 1 ORDER BY created_at ASC"
            );
        } catch (\Exception $e) {
            Logger::error('TemplateModel::fetchActiveRows error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Safely evaluate a template PHP file in an isolated scope and return the
     * returned value.  Returns null if the file does not return an array.
     */
    private function evalTemplateFile(string $filePath): ?array
    {
        try {
            // Execute in a clean scope so the template cannot access $this or
            // any variables from this method.
            $result = (static function (string $p) {
                return include $p;
            })($filePath);

            return is_array($result) ? $result : null;
        } catch (\Throwable $e) {
            Logger::error('TemplateModel::evalTemplateFile error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Load a stored template file by file name and return its preset array,
     * or null if the file is missing or invalid.
     */
    private function loadTemplateFile(string $fileName): ?array
    {
        $path = $this->storageDir . '/' . $fileName;
        if (!file_exists($path)) {
            return null;
        }
        return $this->evalTemplateFile($path);
    }

    /**
     * Validate the structure of a template preset array.
     * Returns null on success, or an error message string on failure.
     */
    private function validatePreset(array $preset): ?string
    {
        // Check required keys
        foreach (self::REQUIRED_KEYS as $reqKey) {
            if (!array_key_exists($reqKey, $preset)) {
                return "Missing required field: \"{$reqKey}\". See the sample template for the complete list.";
            }
        }

        // Validate `key`
        if (!preg_match('/^[a-z0-9\-]+$/', $preset['key']) || strlen($preset['key']) > 100) {
            return 'The "key" field must contain only lowercase letters, digits, and hyphens, and be at most 100 characters.';
        }

        // Validate `name`
        if (!is_string($preset['name']) || trim($preset['name']) === '' || strlen($preset['name']) > 255) {
            return 'The "name" field must be a non-empty string of at most 255 characters.';
        }

        // Validate hex color fields
        $colorFields = [
            'primaryColor', 'secondaryColor', 'backgroundColor',
            'surfaceColor', 'textColor', 'textMuted',
        ];
        foreach ($colorFields as $field) {
            $val = $preset[$field] ?? '';
            if (!is_string($val) || !preg_match('/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', $val)) {
                return "Field \"{$field}\" must be a valid hex color (e.g. \"#1e40af\").";
            }
        }

        // Validate colorVariants
        $variants = $preset['colorVariants'];
        if (!is_array($variants) || count($variants) < 1 || count($variants) > 4) {
            return '"colorVariants" must be an array of 1 to 4 items.';
        }
        foreach ($variants as $i => $v) {
            if (!is_array($v) || empty($v['label']) || empty($v['primary']) || empty($v['secondary'])) {
                return "colorVariants[{$i}] must have \"label\", \"primary\", and \"secondary\" fields.";
            }
            if (!preg_match('/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', $v['primary'])
                || !preg_match('/^#[0-9a-fA-F]{3}([0-9a-fA-F]{3})?$/', $v['secondary'])) {
                return "colorVariants[{$i}]: \"primary\" and \"secondary\" must be valid hex colors.";
            }
        }

        return null;
    }

    /** Check if a template key already exists in the database. */
    private function keyExists(string $key): bool
    {
        try {
            $row = $this->db->fetch(
                "SELECT id FROM resumex_templates WHERE `key` = ?",
                [$key]
            );
            return $row !== null && $row !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
}
