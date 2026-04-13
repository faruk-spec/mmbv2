<?php
/**
 * Admin Network Inspector Controller
 *
 * Displays logged API request/response data for debugging.
 * Only available when APP_DEBUG is true and the user is super_admin.
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Auth;

class NetworkInspectorController extends BaseController
{
    private string $logFile;

    public function __construct()
    {
        $this->logFile = dirname(__DIR__, 2) . '/storage/logs/network_inspector.json';
    }

    /**
     * GET /admin/network-inspector
     */
    public function index(): void
    {
        $user = Auth::user();

        // Only super_admin may access this page; also require APP_DEBUG
        if (!$user || $user['role'] !== 'super_admin') {
            $this->flash('error', 'Access denied.');
            $this->redirect('/admin');
            return;
        }

        if (!defined('APP_DEBUG') || !APP_DEBUG) {
            $this->flash('error', 'Network inspector is only available in debug mode.');
            $this->redirect('/admin');
            return;
        }

        $entries = [];
        if (file_exists($this->logFile)) {
            $raw     = file_get_contents($this->logFile);
            $entries = json_decode($raw ?: '[]', true) ?? [];
            // Newest first
            $entries = array_reverse($entries);
        }

        $this->view('admin/network-inspector', [
            'title'   => 'Network Inspector',
            'entries' => $entries,
        ]);
    }

    /**
     * POST /admin/network-inspector/clear
     * Clears all logged entries.
     */
    public function clear(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/network-inspector');
            return;
        }

        $user = Auth::user();
        if (!$user || $user['role'] !== 'super_admin') {
            $this->flash('error', 'Access denied.');
            $this->redirect('/admin');
            return;
        }

        if (file_exists($this->logFile)) {
            file_put_contents($this->logFile, '[]');
        }

        $this->flash('success', 'Network inspector log cleared.');
        $this->redirect('/admin/network-inspector');
    }
}
