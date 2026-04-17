<?php
/**
 * Admin Preloader Settings Controller
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Security;

class PreloaderController extends BaseController
{
    private const UPLOAD_DIR = '/public/uploads/preloader/';

    public function __construct()
    {
        $this->requireAuth();
        $this->requirePermissionGroup('settings');
    }

    /**
     * GET /admin/settings/preloader
     */
    public function index(): void
    {
        $this->requirePermission('settings');
        $db = Database::getInstance();

        $keys = [
            'preloader_enabled',
            'preloader_type',         // 'text' | 'image'
            'preloader_text',
            'preloader_text_color',
            'preloader_bg_color',
            'preloader_animation',    // 'wave' | 'pulse' | 'spin' | 'bounce'
            'preloader_speed',        // milliseconds, e.g. 800
            'preloader_image_path',
            'skeleton_enabled',
            'action_loader_enabled',
        ];

        $settings = [];
        foreach ($keys as $key) {
            $row = $db->fetch("SELECT value FROM settings WHERE `key` = ?", [$key]);
            $settings[$key] = $row ? $row['value'] : null;
        }

        // Defaults
        $settings['preloader_type']      = $settings['preloader_type']      ?? 'text';
        $settings['preloader_text']      = $settings['preloader_text']      ?? 'Loading…';
        $settings['preloader_text_color']= $settings['preloader_text_color']?? '#00f0ff';
        $settings['preloader_bg_color']  = $settings['preloader_bg_color']  ?? '#06060a';
        $settings['preloader_animation'] = $settings['preloader_animation'] ?? 'wave';
        $settings['preloader_speed']     = $settings['preloader_speed']     ?? '800';

        $this->view('admin/settings/preloader', [
            'title'    => 'Preloader Settings',
            'settings' => $settings,
        ]);
    }

    /**
     * POST /admin/settings/preloader
     */
    public function update(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/settings/preloader');
            return;
        }

        $this->requirePermission('settings');
        $db = Database::getInstance();

        $fields = [
            'preloader_enabled'     => $this->input('preloader_enabled')      ? '1' : '0',
            'preloader_type'        => in_array($this->input('preloader_type'), ['text', 'image']) ? $this->input('preloader_type') : 'text',
            'preloader_text'        => Security::sanitize($this->input('preloader_text', 'Loading…')),
            'preloader_text_color'  => preg_match('/^#[0-9a-fA-F]{3,8}$/', $this->input('preloader_text_color', '#00f0ff')) ? $this->input('preloader_text_color') : '#00f0ff',
            'preloader_bg_color'    => preg_match('/^#[0-9a-fA-F]{3,8}$/', $this->input('preloader_bg_color', '#06060a'))   ? $this->input('preloader_bg_color')   : '#06060a',
            'preloader_animation'   => in_array($this->input('preloader_animation'), ['wave', 'pulse', 'spin', 'bounce']) ? $this->input('preloader_animation') : 'wave',
            'preloader_speed'       => max(200, min(3000, (int)$this->input('preloader_speed', 800))),
            'skeleton_enabled'      => $this->input('skeleton_enabled')        ? '1' : '0',
            'action_loader_enabled' => $this->input('action_loader_enabled')   ? '1' : '0',
        ];

        // Handle optional image upload
        if (!empty($_FILES['preloader_image']['name'])) {
            $uploaded = $this->handleUpload($_FILES['preloader_image']);
            if ($uploaded) {
                $fields['preloader_image_path'] = $uploaded;
            } else {
                $this->flash('error', 'Image upload failed. Allowed types: png, gif, svg, webp, jpg.');
                $this->redirect('/admin/settings/preloader');
                return;
            }
        }

        foreach ($fields as $key => $value) {
            $exists = $db->fetch("SELECT id FROM settings WHERE `key` = ?", [$key]);
            if ($exists) {
                $db->update('settings', ['value' => $value, 'updated_at' => date('Y-m-d H:i:s')], '`key` = ?', [$key]);
            } else {
                $db->insert('settings', ['key' => $key, 'value' => $value, 'created_at' => date('Y-m-d H:i:s')]);
            }
        }

        $this->flash('success', 'Preloader settings saved.');
        $this->redirect('/admin/settings/preloader');
    }

    /**
     * Upload and validate the preloader image.
     * Returns the public path on success, or false on failure.
     */
    private function handleUpload(array $file): string|false
    {
        $allowedTypes = ['image/png', 'image/gif', 'image/svg+xml', 'image/webp', 'image/jpeg'];
        $allowedExts  = ['png', 'gif', 'svg', 'webp', 'jpg', 'jpeg'];

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts)) {
            return false;
        }

        // Re-check MIME to prevent bypasses (skip for SVG which returns text/plain on some systems)
        if ($ext !== 'svg') {
            $mime = mime_content_type($file['tmp_name']);
            if (!in_array($mime, $allowedTypes)) {
                return false;
            }
        }

        $dir = BASE_PATH . self::UPLOAD_DIR;
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = 'preloader_' . bin2hex(random_bytes(8)) . '.' . $ext;
        $dest     = $dir . $filename;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            return false;
        }

        return '/public/uploads/preloader/' . $filename;
    }
}
