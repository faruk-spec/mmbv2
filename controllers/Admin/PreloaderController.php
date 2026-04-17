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
            'preloader_text_color'  => $this->validateHexColor($this->input('preloader_text_color'), '#00f0ff'),
            'preloader_bg_color'    => $this->validateHexColor($this->input('preloader_bg_color'),   '#06060a'),
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

        // Re-check MIME to prevent bypasses (skip for SVG which may return text/plain on some systems)
        if ($ext !== 'svg') {
            $mime = mime_content_type($file['tmp_name']);
            if (!in_array($mime, $allowedTypes)) {
                return false;
            }
        } else {
            // For SVG files: parse the XML and strip any script tags / event handlers
            // to prevent stored XSS through an uploaded SVG.
            $svgContent = file_get_contents($file['tmp_name']);
            if ($svgContent === false) {
                return false;
            }
            $svgContent = $this->sanitizeSvg($svgContent);
            if ($svgContent === false) {
                return false; // Rejected (e.g. not valid XML)
            }
            // Write sanitized SVG back to a temp file before moving
            file_put_contents($file['tmp_name'], $svgContent);
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

    /**
     * Sanitize an SVG string by stripping dangerous elements and attributes.
     * Returns the sanitized string or false if the input is not valid XML.
     */
    private function sanitizeSvg(string $svg): string|false
    {
        // Must be parseable XML
        $prev = libxml_use_internal_errors(true);
        $doc  = new \DOMDocument();
        if (!$doc->loadXML($svg, LIBXML_NONET)) {
            libxml_use_internal_errors($prev);
            return false;
        }
        libxml_use_internal_errors($prev);

        // Elements that must be removed entirely
        $forbiddenTags = ['script', 'iframe', 'object', 'embed', 'link', 'meta', 'style'];
        foreach ($forbiddenTags as $tag) {
            foreach (iterator_to_array($doc->getElementsByTagName($tag)) as $node) {
                $node->parentNode?->removeChild($node);
            }
        }

        // Remove event-handler attributes (onclick, onload, etc.) and dangerous hrefs
        $xpath = new \DOMXPath($doc);
        foreach ($xpath->query('//@*') as $attr) {
            $name = strtolower($attr->nodeName);
            if (str_starts_with($name, 'on') || $name === 'xlink:href' || $name === 'href') {
                // Allow href only if it's a safe data:image or relative fragment reference
                if (($name === 'href' || $name === 'xlink:href') &&
                    preg_match('/^(#|data:image\/)/i', $attr->value)) {
                    continue;
                }
                $attr->ownerElement?->removeAttributeNode($attr);
            }
        }

        return $doc->saveXML($doc->documentElement) ?: false;
    }

    /**
     * Validate a CSS hex colour value, returning $default if invalid.
     */
    private function validateHexColor(?string $input, string $default): string
    {
        if ($input !== null && preg_match('/^#[0-9a-fA-F]{3,8}$/', $input)) {
            return $input;
        }
        return $default;
    }
}
