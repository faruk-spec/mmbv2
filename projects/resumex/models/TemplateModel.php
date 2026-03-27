<?php
/**
 * ResumeX Template Model
 *
 * Manages two kinds of custom resume templates:
 *
 *  preset  - a PHP file that returns a theme-property array (colors/fonts/layout).
 *            Uploaded via the "Upload Theme Preset" form. Merged with built-in
 *            presets and applied by preview.php / print.php.
 *
 *  full    - a complete PHP renderer that outputs a standalone HTML page.
 *            Uploaded via the "Upload Full Resume Template" form. Bypasses
 *            preview.php / print.php entirely; the file is included directly with
 *            $resumeData, $resume, $themeSettings, $isEmbed, $isPdf available.
 *
 * @package MMB\Projects\ResumeX\Models
 */

namespace Projects\ResumeX\Models;

use Core\Database;
use Core\Logger;

class TemplateModel
{
    private Database $db;

    /** Absolute path to the directory that holds uploaded preset PHP files. */
    private string $storageDir;

    /** Absolute path to the directory that holds uploaded full-template PHP files. */
    private string $fullTemplateDir;

    /** Required top-level keys that every PRESET template definition must provide. */
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

    /** Regex pattern for validating a CSS hex color (#rgb or #rrggbb). */
    private const HEX_COLOR_PATTERN = '/^#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?$/';

    public function __construct()
    {
        $this->db              = Database::getInstance();
        $this->storageDir      = BASE_PATH . '/storage/uploads/resumex/templates';
        $this->fullTemplateDir = BASE_PATH . '/storage/uploads/resumex/full-templates';
        $this->ensureTable();
        $this->ensureStorageDir();
        $this->ensureFullTemplateDir();
    }

    // ----------------------------------------------------------------------
    //  Schema bootstrap
    // ----------------------------------------------------------------------

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
                    `id`              INT UNSIGNED     NOT NULL AUTO_INCREMENT,
                    `key`             VARCHAR(100)     NOT NULL,
                    `name`            VARCHAR(255)     NOT NULL,
                    `category`        VARCHAR(100)     NOT NULL DEFAULT 'custom',
                    `template_type`   VARCHAR(10)      NOT NULL DEFAULT 'preset',
                    `file_name`       VARCHAR(255)     NOT NULL DEFAULT '',
                    `template_design` LONGTEXT         DEFAULT NULL,
                    `uploaded_by`     INT UNSIGNED     NOT NULL,
                    `is_active`       TINYINT(1)       NOT NULL DEFAULT 1,
                    `is_override`     TINYINT(1)       NOT NULL DEFAULT 0,
                    `preview_image`   VARCHAR(500)     DEFAULT NULL,
                    `display_bg`      VARCHAR(20)      DEFAULT NULL,
                    `display_pri`     VARCHAR(20)      DEFAULT NULL,
                    `created_at`      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    `updated_at`      DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP
                                                               ON UPDATE CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uk_key` (`key`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $cols = [
                'is_override'     => "TINYINT(1) NOT NULL DEFAULT 0",
                'preview_image'   => "VARCHAR(500) DEFAULT NULL",
                'template_type'   => "VARCHAR(10) NOT NULL DEFAULT 'preset'",
                'display_bg'      => "VARCHAR(20) DEFAULT NULL",
                'display_pri'     => "VARCHAR(20) DEFAULT NULL",
                'template_design' => "LONGTEXT DEFAULT NULL",
            ];
            foreach ($cols as $col => $def) {
                $this->addColumnIfMissing('resumex_templates', $col, $def);
            }
        } catch (\Exception $e) {
            Logger::error('TemplateModel::ensureTable error: ' . $e->getMessage());
        }
    }

    private function addColumnIfMissing(string $table, string $column, string $definition): void
    {
        try {
            // SHOW COLUMNS is simpler and doesn't require information_schema access
            $row = $this->db->fetch("SHOW COLUMNS FROM `{$table}` LIKE ?", [$column]);
            if (!$row) {
                $this->db->query("ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$definition}");
            }
        } catch (\Exception $e) {
            Logger::error("TemplateModel::addColumnIfMissing({$table}.{$column}): " . $e->getMessage());
        }
    }

    private function ensureStorageDir(): void
    {
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0775, true);
        }
    }

    private function ensureFullTemplateDir(): void
    {
        if (!is_dir($this->fullTemplateDir)) {
            mkdir($this->fullTemplateDir, 0775, true);
        }
    }

    // ----------------------------------------------------------------------
    //  Public API
    // ----------------------------------------------------------------------

    /**
     * Return all active custom templates as theme-preset arrays, keyed by their
     * template key.
     *
     *  preset templates:   evaluated to load the array; skipped when file is missing.
     *  full   templates:   a minimal stub array is returned with '_full_template' => true.
     *
     * @return array<string, array>
     */
    public function getAllCustomTemplates(): array
    {
        $rows = $this->fetchActiveRows();
        $out  = [];

        foreach ($rows as $row) {
            $type = $row['template_type'] ?? 'preset';

            if ($type === 'full') {
                $stub = $this->buildFullTemplateStub($row);
                $out[$stub['key']] = $stub;
            } elseif ($type === 'designer') {
                $stub = $this->buildDesignerTemplateStub($row);
                if ($stub !== null) {
                    $out[$stub['key']] = $stub;
                }
            } else {
                $preset = $this->loadTemplateFile($row['file_name']);
                if ($preset !== null) {
                    $preset['_is_override']   = (bool) ($row['is_override'] ?? false);
                    $preset['_preview_image'] = $row['preview_image'] ?? null;
                    $preset['_db_id']         = (int) $row['id'];
                    $out[$preset['key']]      = $preset;
                }
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
                "SELECT * FROM resumex_templates ORDER BY template_type ASC, created_at DESC"
            );
        } catch (\Exception $e) {
            Logger::error('TemplateModel::getAllRows error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Fetch one template row by ID.
     */
    public function getById(int $id): ?array
    {
        try {
            $row = $this->db->fetch("SELECT * FROM resumex_templates WHERE id = ?", [$id]);
            return ($row && $row !== false) ? $row : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * If the given template key belongs to an active full template, return the
     * absolute path to the rendering PHP file. Returns null otherwise.
     */
    public function getFullTemplateFile(string $key): ?string
    {
        try {
            $row = $this->db->fetch(
                "SELECT file_name FROM resumex_templates
                 WHERE `key` = ? AND template_type = 'full' AND is_active = 1",
                [$key]
            );
            if (!$row || empty($row['file_name'])) {
                return null;
            }
            $path = $this->fullTemplateDir . '/' . basename($row['file_name']);
            if (!file_exists($path)) {
                return null;
            }
            // Verify the resolved path stays within the intended directory (prevents symlink escapes)
            $realPath = realpath($path);
            $realDir  = realpath($this->fullTemplateDir);
            if ($realPath === false || $realDir === false || strncmp($realPath, $realDir . DIRECTORY_SEPARATOR, strlen($realDir) + 1) !== 0) {
                return null;
            }
            return $realPath;
        } catch (\Exception $e) {
            Logger::error('TemplateModel::getFullTemplateFile error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * If the given template key belongs to an active DESIGNER template, return
     * the parsed design array (layout JSON). Returns null otherwise.
     *
     * @return array|null  The design definition, or null if not a designer template.
     */
    public function getDesignedTemplateDesign(string $key): ?array
    {
        try {
            $row = $this->db->fetch(
                "SELECT template_design FROM resumex_templates
                 WHERE `key` = ? AND template_type = 'designer' AND is_active = 1",
                [$key]
            );
            if (!$row || empty($row['template_design'])) {
                return null;
            }
            $design = json_decode($row['template_design'], true);
            return is_array($design) ? $design : null;
        } catch (\Exception $e) {
            Logger::error('TemplateModel::getDesignedTemplateDesign error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Create a new designer template record.
     *
     * @param  array  $meta        Keys: key, name, category, display_bg, display_pri
     * @param  array  $design      The full design JSON structure
     * @param  int    $uploadedBy  Admin user ID
     * @return array{success: bool, id?: int, key?: string, error?: string}
     */
    public function saveDesignedTemplate(array $meta, array $design, int $uploadedBy): array
    {
        $key      = strtolower(trim($meta['key']      ?? ''));
        $name     = trim($meta['name']                ?? '');
        $category = trim($meta['category']            ?? 'custom');
        $dispBg   = trim($meta['display_bg']          ?? '#ffffff');
        $dispPri  = trim($meta['display_pri']         ?? '#007bff');

        if (!preg_match('/^[a-z0-9\-]+$/', $key) || strlen($key) > 100) {
            return ['success' => false, 'error' => 'Key must contain only lowercase letters, digits, and hyphens (max 100 chars).'];
        }
        if ($name === '' || strlen($name) > 255) {
            return ['success' => false, 'error' => 'Name is required (max 255 chars).'];
        }
        if ($this->keyExists($key)) {
            return ['success' => false, 'error' => "A template with key \"{$key}\" already exists."];
        }

        try {
            $this->db->query(
                "INSERT INTO resumex_templates
                    (`key`, `name`, `category`, `template_type`, `template_design`, `file_name`, `uploaded_by`, `display_bg`, `display_pri`)
                 VALUES (?, ?, ?, 'designer', ?, '', ?, ?, ?)",
                [$key, $name, $category, json_encode($design), $uploadedBy, $dispBg, $dispPri]
            );
            $id = $this->db->lastInsertId();
            return ['success' => true, 'id' => $id, 'key' => $key];
        } catch (\Exception $e) {
            Logger::error('TemplateModel::saveDesignedTemplate DB error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error while saving template.'];
        }
    }

    /**
     * Update an existing designer template.
     *
     * @param  int    $id     DB record ID
     * @param  array  $meta   Fields to update: name, category, display_bg, display_pri
     * @param  array  $design The full design JSON structure
     * @return array{success: bool, error?: string}
     */
    public function updateDesignedTemplate(int $id, array $meta, array $design): array
    {
        try {
            $name     = trim($meta['name']       ?? '');
            $category = trim($meta['category']   ?? 'custom');
            $dispBg   = trim($meta['display_bg'] ?? '#ffffff');
            $dispPri  = trim($meta['display_pri']?? '#007bff');

            if ($name === '') {
                return ['success' => false, 'error' => 'Name is required.'];
            }

            $this->db->query(
                "UPDATE resumex_templates
                    SET `name` = ?, `category` = ?, `display_bg` = ?, `display_pri` = ?,
                        `template_design` = ?
                  WHERE `id` = ? AND template_type = 'designer'",
                [$name, $category, $dispBg, $dispPri, json_encode($design), $id]
            );
            return ['success' => true];
        } catch (\Exception $e) {
            Logger::error('TemplateModel::updateDesignedTemplate error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error.'];
        }
    }


    /**
     * Validate and store an uploaded PRESET template file.
     *
     * @param  array  $file        Entry from $_FILES
     * @param  int    $uploadedBy  User ID of the uploader
     * @param  bool   $isOverride  Whether to allow replacing an existing key
     * @return array{success: bool, error?: string, template?: array}
     */
    public function upload(array $file, int $uploadedBy, bool $isOverride = false): array
    {
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

        $preset = $this->evalTemplateFile($file['tmp_name']);
        if ($preset === null) {
            return ['success' => false, 'error' => 'Template file must return an array. Check the sample template for the required format.'];
        }

        $validationError = $this->validatePreset($preset);
        if ($validationError !== null) {
            return ['success' => false, 'error' => $validationError];
        }

        if (!$isOverride && $this->keyExists($preset['key'])) {
            return ['success' => false, 'error' => "A template with key \"{$preset['key']}\" already exists. Use a unique key."];
        }
        if ($isOverride && $this->keyExists($preset['key'])) {
            $this->deleteByKey($preset['key']);
        }

        $fileName = $preset['key'] . '_' . time() . '.php';
        $dest     = $this->storageDir . '/' . $fileName;

        if (!$this->moveFile($file['tmp_name'], $dest)) {
            return ['success' => false, 'error' => 'Could not save the template file to disk.'];
        }

        try {
            $this->db->query(
                "INSERT INTO resumex_templates
                    (`key`, `name`, `category`, `template_type`, `file_name`, `uploaded_by`, `is_override`)
                 VALUES (?, ?, ?, 'preset', ?, ?, ?)",
                [$preset['key'], $preset['name'], $preset['category'], $fileName, $uploadedBy, $isOverride ? 1 : 0]
            );
        } catch (\Exception $e) {
            @unlink($dest);
            Logger::error('TemplateModel::upload DB error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error while saving template.'];
        }

        return ['success' => true, 'template' => $preset];
    }

    /**
     * Upload a FULL resume template PHP file (a complete renderer).
     *
     * The uploaded file receives $resumeData, $resume, $themeSettings,
     * $isEmbed, $isPdf when included, and should output a complete HTML page.
     *
     * @param  array  $file       Entry from $_FILES
     * @param  array  $meta       Keys: key, name, category, display_bg, display_pri
     * @param  int    $uploadedBy User ID of the uploader
     * @return array{success: bool, error?: string, key?: string, name?: string}
     */
    public function uploadFullTemplate(array $file, array $meta, int $uploadedBy): array
    {
        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'File upload failed (code ' . ($file['error'] ?? '?') . ').'];
        }

        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        if ($ext !== 'php') {
            return ['success' => false, 'error' => 'Only .php template files are accepted.'];
        }

        if ($file['size'] > 2 * 1024 * 1024) {
            return ['success' => false, 'error' => 'Full template file must be smaller than 2 MB.'];
        }

        $key      = strtolower(trim($meta['key']      ?? ''));
        $name     = trim($meta['name']                ?? '');
        $category = trim($meta['category']            ?? 'custom');
        $dispBg   = trim($meta['display_bg']          ?? '#1e1e2e');
        $dispPri  = trim($meta['display_pri']         ?? '#00f0ff');

        if (!preg_match('/^[a-z0-9\-]+$/', $key) || strlen($key) > 100) {
            return ['success' => false, 'error' => 'Key must contain only lowercase letters, digits, and hyphens (max 100 chars).'];
        }
        if ($name === '' || strlen($name) > 255) {
            return ['success' => false, 'error' => 'Name must be a non-empty string of at most 255 characters.'];
        }
        if ($this->keyExists($key)) {
            return ['success' => false, 'error' => "A template with key \"{$key}\" already exists. Use a unique key."];
        }

        $fileName = $key . '_' . time() . '.php';
        $dest     = $this->fullTemplateDir . '/' . $fileName;

        if (!$this->moveFile($file['tmp_name'], $dest)) {
            return ['success' => false, 'error' => 'Could not save the template file to disk.'];
        }

        try {
            $this->db->query(
                "INSERT INTO resumex_templates
                    (`key`, `name`, `category`, `template_type`, `file_name`, `uploaded_by`, `display_bg`, `display_pri`)
                 VALUES (?, ?, ?, 'full', ?, ?, ?, ?)",
                [$key, $name, $category, $fileName, $uploadedBy, $dispBg, $dispPri]
            );
        } catch (\Exception $e) {
            @unlink($dest);
            Logger::error('TemplateModel::uploadFullTemplate DB error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error while saving template.'];
        }

        return ['success' => true, 'key' => $key, 'name' => $name];
    }

    /**
     * Delete a custom template by database ID.
     *
     * @return array{success: bool, error?: string}
     */
    public function delete(int $id): array
    {
        try {
            $row = $this->db->fetch("SELECT * FROM resumex_templates WHERE id = ?", [$id]);

            if (!$row) {
                return ['success' => false, 'error' => 'Template not found.'];
            }

            $type = $row['template_type'] ?? 'preset';
            $dir  = $type === 'full' ? $this->fullTemplateDir : $this->storageDir;
            $path = $dir . '/' . basename($row['file_name'] ?? '');
            if ($path && file_exists($path)) {
                // Verify path stays within intended directory before deletion
                $realPath = realpath($path);
                $realDir  = realpath($dir);
                if ($realPath !== false && $realDir !== false
                    && strncmp($realPath, $realDir . DIRECTORY_SEPARATOR, strlen($realDir) + 1) === 0) {
                    @unlink($realPath);
                }
            }

            $this->db->query("DELETE FROM resumex_templates WHERE id = ?", [$id]);

            return ['success' => true];
        } catch (\Exception $e) {
            Logger::error('TemplateModel::delete error: ' . $e->getMessage());
            return ['success' => false, 'error' => 'Database error while deleting template.'];
        }
    }

    /**
     * Upload a preview image for a given template DB row.
     */
    public function uploadPreviewImage(int $templateId, array $file): array
    {
        $previewDir = BASE_PATH . '/storage/uploads/resumex/previews';
        if (!is_dir($previewDir)) {
            mkdir($previewDir, 0775, true);
        }

        if (!isset($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Upload error (code ' . ($file['error'] ?? '?') . ').'];
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            return ['success' => false, 'error' => 'Preview image must be smaller than 2 MB.'];
        }

        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
        $finfo   = finfo_open(FILEINFO_MIME_TYPE);
        $mime    = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!array_key_exists($mime, $allowed)) {
            return ['success' => false, 'error' => 'Only JPEG, PNG, GIF, and WebP images are accepted.'];
        }

        $ext      = $allowed[$mime];
        $fileName = "preview_{$templateId}_" . time() . ".{$ext}";
        $dest     = $previewDir . '/' . $fileName;

        if (!$this->moveFile($file['tmp_name'], $dest)) {
            return ['success' => false, 'error' => 'Could not save the preview image.'];
        }

        $url = '/storage/uploads/resumex/previews/' . $fileName;

        try {
            $this->db->query(
                "UPDATE resumex_templates SET preview_image = ? WHERE id = ?",
                [$url, $templateId]
            );
        } catch (\Exception $e) {
            Logger::error('TemplateModel::uploadPreviewImage DB error: ' . $e->getMessage());
        }

        return ['success' => true, 'url' => $url];
    }

    // ----------------------------------------------------------------------
    //  Private helpers
    // ----------------------------------------------------------------------

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
     * Build a minimal preset-like stub for a full template so it can appear
     * in the template picker card grid.
     */
    private function buildFullTemplateStub(array $row): array
    {
        $bg  = $row['display_bg']  ?? '#1e1e2e';
        $pri = $row['display_pri'] ?? '#00f0ff';

        return [
            'key'              => $row['key'],
            'name'             => $row['name'],
            'category'         => $row['category'] ?? 'custom',
            'primaryColor'     => $pri,
            'secondaryColor'   => $pri,
            'backgroundColor'  => $bg,
            'surfaceColor'     => $bg,
            'textColor'        => '#ffffff',
            'textMuted'        => '#aaaaaa',
            'borderColor'      => 'rgba(255,255,255,0.1)',
            'fontFamily'       => 'Inter',
            'fontSize'         => '14',
            'fontWeight'       => '400',
            'headerStyle'      => 'solid',
            'buttonStyle'      => 'pill',
            'cardStyle'        => 'bordered',
            'spacing'          => 'normal',
            'layoutMode'       => 'single',
            'iconStyle'        => 'filled',
            'accentHighlights' => false,
            'animations'       => false,
            'layoutStyle'      => 'full',      // special value — signals full template
            'colorVariants'    => [],           // full templates own their colors
            '_full_template'   => true,
            '_preview_image'   => $row['preview_image'] ?? null,
            '_db_id'           => (int) $row['id'],
            '_is_override'     => false,
        ];
    }

    /**
     * Build a theme-stub array from a designer template DB row.
     * Returns null if the design JSON is invalid or missing.
     */
    private function buildDesignerTemplateStub(array $row): ?array
    {
        $design = null;
        if (!empty($row['template_design'])) {
            $design = json_decode($row['template_design'], true);
        }
        if (!is_array($design)) {
            return null;
        }

        $bg  = $row['display_bg']  ?? ($design['canvas']['background'] ?? '#ffffff');
        $pri = $row['display_pri'] ?? ($design['colorVariants'][0]['primary'] ?? '#007bff');
        $sec = $design['colorVariants'][0]['secondary'] ?? $pri;

        // Extract color variants from design
        $colorVariants = [];
        if (!empty($design['colorVariants']) && is_array($design['colorVariants'])) {
            $colorVariants = $design['colorVariants'];
        }

        return [
            'key'              => $row['key'],
            'name'             => $row['name'],
            'category'         => $row['category'] ?? 'custom',
            'primaryColor'     => $pri,
            'secondaryColor'   => $sec,
            'backgroundColor'  => $bg,
            'surfaceColor'     => $bg,
            'textColor'        => $design['canvas']['textColor'] ?? '#111111',
            'textMuted'        => '#555555',
            'borderColor'      => '#dddddd',
            'fontFamily'       => $design['canvas']['fontFamily'] ?? 'Inter',
            'fontSize'         => '14',
            'fontWeight'       => '400',
            'headerStyle'      => 'solid',
            'buttonStyle'      => 'pill',
            'cardStyle'        => 'flat',
            'spacing'          => 'normal',
            'layoutMode'       => 'single',
            'iconStyle'        => 'filled',
            'accentHighlights' => false,
            'animations'       => false,
            'layoutStyle'      => 'designer',
            'colorVariants'    => $colorVariants,
            '_designer_template' => true,
            '_design'          => $design,
            '_preview_image'   => $row['preview_image'] ?? null,
            '_db_id'           => (int) $row['id'],
            '_is_override'     => false,
        ];
    }

    private function evalTemplateFile(string $filePath): ?array
    {
        try {
            $result = (static function (string $p) {
                return include $p;
            })($filePath);

            return is_array($result) ? $result : null;
        } catch (\Throwable $e) {
            Logger::error('TemplateModel::evalTemplateFile error: ' . $e->getMessage());
            return null;
        }
    }

    private function loadTemplateFile(string $fileName): ?array
    {
        if ($fileName === '') {
            return null;
        }
        $path = $this->storageDir . '/' . basename($fileName);
        if (!file_exists($path)) {
            return null;
        }
        return $this->evalTemplateFile($path);
    }

    private function validatePreset(array $preset): ?string
    {
        foreach (self::REQUIRED_KEYS as $reqKey) {
            if (!array_key_exists($reqKey, $preset)) {
                return "Missing required field: \"{$reqKey}\". See the sample template for the complete list.";
            }
        }

        if (!preg_match('/^[a-z0-9\-]+$/', $preset['key']) || strlen($preset['key']) > 100) {
            return 'The "key" field must contain only lowercase letters, digits, and hyphens, and be at most 100 characters.';
        }

        if (!is_string($preset['name']) || trim($preset['name']) === '' || strlen($preset['name']) > 255) {
            return 'The "name" field must be a non-empty string of at most 255 characters.';
        }

        $colorFields = ['primaryColor', 'secondaryColor', 'backgroundColor', 'surfaceColor', 'textColor', 'textMuted'];
        foreach ($colorFields as $field) {
            $val = $preset[$field] ?? '';
            if (!is_string($val) || !preg_match(self::HEX_COLOR_PATTERN, $val)) {
                return "Field \"{$field}\" must be a valid hex color (e.g. \"#1e40af\").";
            }
        }

        $variants = $preset['colorVariants'];
        if (!is_array($variants) || count($variants) < 1 || count($variants) > 4) {
            return '"colorVariants" must be an array of 1 to 4 items.';
        }
        foreach ($variants as $i => $v) {
            if (!is_array($v) || empty($v['label']) || empty($v['primary']) || empty($v['secondary'])) {
                return "colorVariants[{$i}] must have \"label\", \"primary\", and \"secondary\" fields.";
            }
            if (!preg_match(self::HEX_COLOR_PATTERN, $v['primary'])
                || !preg_match(self::HEX_COLOR_PATTERN, $v['secondary'])) {
                return "colorVariants[{$i}]: \"primary\" and \"secondary\" must be valid hex colors.";
            }
        }

        return null;
    }

    private function keyExists(string $key): bool
    {
        try {
            $row = $this->db->fetch("SELECT id FROM resumex_templates WHERE `key` = ?", [$key]);
            return $row !== null && $row !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    private function deleteByKey(string $key): void
    {
        try {
            $row = $this->db->fetch("SELECT * FROM resumex_templates WHERE `key` = ?", [$key]);
            if ($row) {
                $type = $row['template_type'] ?? 'preset';
                $dir  = $type === 'full' ? $this->fullTemplateDir : $this->storageDir;
                $path = $dir . '/' . basename($row['file_name'] ?? '');
                if ($path && file_exists($path)) {
                    @unlink($path);
                }
                $this->db->query("DELETE FROM resumex_templates WHERE `key` = ?", [$key]);
            }
        } catch (\Exception $e) {
            Logger::error('TemplateModel::deleteByKey error: ' . $e->getMessage());
        }
    }

    /**
     * Move an uploaded file with fallbacks for cross-mount-point environments.
     */
    private function moveFile(string $tmpPath, string $dest): bool
    {
        if (move_uploaded_file($tmpPath, $dest)) {
            return true;
        }
        Logger::error('TemplateModel::moveFile move_uploaded_file failed for ' . basename($dest) . ', trying copy() fallback.');
        if (@copy($tmpPath, $dest)) {
            @unlink($tmpPath);
            return true;
        }
        $data = @file_get_contents($tmpPath);
        if ($data !== false && @file_put_contents($dest, $data) !== false) {
            @unlink($tmpPath);
            return true;
        }
        return false;
    }
}
