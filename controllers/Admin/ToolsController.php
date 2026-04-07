<?php
namespace Controllers\Admin;

use Controllers\BaseController;
use Core\VirusScanner;

class ToolsController extends BaseController
{
    public function __construct()
    {
        $this->requireAuth();
        $this->requireRoleAdmin();
    }

    public function scanner(): void
    {
        $this->view('admin/tools/scanner', ['title' => 'URL/Virus Scanner']);
    }

    public function scanUrl(): void
    {
        if (!$this->validateCsrf()) {
            $this->flash('error', 'Invalid request.');
            $this->redirect('/admin/tools/scanner');
            return;
        }

        $url = trim($this->input('scan_url', ''));
        if (empty($url)) {
            $this->flash('error', 'Please provide a URL to scan.');
            $this->redirect('/admin/tools/scanner');
            return;
        }

        $result = VirusScanner::scanUrl($url);

        $this->view('admin/tools/scanner', [
            'title'      => 'URL/Virus Scanner',
            'scanResult' => $result,
            'scannedUrl' => $url,
        ]);
    }
}
