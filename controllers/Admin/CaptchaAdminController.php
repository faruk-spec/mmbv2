<?php
/**
 * Admin Captcha Settings Controller
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Database;
use Core\Security;

class CaptchaAdminController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requirePermissionGroup('settings');
    }

    /**
     * GET /admin/settings/captcha
     */
    public function index(): void
    {
        $this->requirePermission('settings');
        $db = Database::getInstance();

        $keys = ['captcha_enabled', 'captcha_on_login', 'captcha_on_register'];
        $settings = [];
        foreach ($keys as $key) {
            $row = $db->fetch("SELECT value FROM settings WHERE `key` = ?", [$key]);
            $settings[$key] = $row ? $row['value'] : '0';
        }

        $this->view('admin/settings/captcha', [
            'title'    => 'Captcha Settings',
            'settings' => $settings,
        ]);
    }

    /**
     * POST /admin/settings/captcha
     */
    public function update(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/settings/captcha');
            return;
        }

        $this->requirePermission('settings');
        $db = Database::getInstance();

        $fields = [
            'captcha_enabled'     => $this->input('captcha_enabled') ? '1' : '0',
            'captcha_on_login'    => $this->input('captcha_on_login') ? '1' : '0',
            'captcha_on_register' => $this->input('captcha_on_register') ? '1' : '0',
        ];

        foreach ($fields as $key => $value) {
            $exists = $db->fetch("SELECT id FROM settings WHERE `key` = ?", [$key]);
            if ($exists) {
                $db->update('settings', ['value' => $value, 'updated_at' => date('Y-m-d H:i:s')], '`key` = ?', [$key]);
            } else {
                $db->insert('settings', ['key' => $key, 'value' => $value, 'created_at' => date('Y-m-d H:i:s')]);
            }
        }

        $this->flash('success', 'Captcha settings saved.');
        $this->redirect('/admin/settings/captcha');
    }
}
