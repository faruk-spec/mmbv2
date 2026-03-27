<?php
/**
 * ResumeX Admin Controller
 *
 * Provides admin-panel management for the ResumeX project:
 *   - Overview statistics
 *   - Custom template upload / delete / listing
 *   - Resume listing across all users
 *
 * @package MMB\Controllers\Admin
 */

namespace Controllers\Admin;

use Controllers\BaseController;
use Core\Auth;
use Core\Database;
use Core\Security;
use Core\Logger;
use Core\ActivityLogger;
use Projects\ResumeX\Models\ResumeModel;
use Projects\ResumeX\Models\TemplateModel;

class ResumeXAdminController extends BaseController
{
    private Database      $db;
    private ResumeModel   $resumeModel;
    private TemplateModel $templateModel;

    public function __construct()
    {
        $this->requireAuth();
        $this->requirePermissionGroup('resumex');
        $this->db            = Database::getInstance();
        $this->resumeModel   = new ResumeModel();
        $this->templateModel = new TemplateModel();
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Overview
    // ──────────────────────────────────────────────────────────────────────────

    public function overview(): void
    {
        $this->requirePermission('resumex');

        $stats    = $this->getStats();
        $recent   = $this->getRecentResumes(10);
        $custom   = $this->templateModel->getAllRows();
        $builtin  = $this->resumeModel->getAllThemePresets();

        $this->view('admin/projects/resumex/overview', [
            'title'           => 'ResumeX Admin — Overview',
            'stats'           => $stats,
            'recentResumes'   => $recent,
            'customTemplates' => $custom,
            'builtinCount'    => count($builtin),
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Template management
    // ──────────────────────────────────────────────────────────────────────────

    public function templates(): void
    {
        $this->requirePermission('resumex.templates');

        $customTemplates  = $this->templateModel->getAllRows();
        $builtinTemplates = $this->resumeModel->getAllThemePresets();
        $csrfToken        = Security::generateCsrfToken();

        $this->view('admin/projects/resumex/templates', [
            'title'            => 'ResumeX — Manage Templates',
            'customTemplates'  => $customTemplates,
            'builtinTemplates' => $builtinTemplates,
            'csrfToken'        => $csrfToken,
            'success'          => $_GET['success'] ?? null,
            'error'            => $_GET['error']   ?? null,
            'uploadedName'     => $_GET['name']    ?? null,
        ]);
    }

    public function uploadTemplate(): void
    {
        $this->requirePermission('resumex.templates');

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->redirectWithError('/admin/projects/resumex/templates', 'Invalid security token.');
        }

        if (empty($_FILES['template_file']) || $_FILES['template_file']['error'] === UPLOAD_ERR_NO_FILE) {
            $this->redirectWithError('/admin/projects/resumex/templates', 'No file was selected for upload.');
        }

        $result = $this->templateModel->upload($_FILES['template_file'], Auth::id());

        if (!$result['success']) {
            $this->redirectWithError('/admin/projects/resumex/templates', $result['error'] ?? 'Upload failed.');
        }

        ActivityLogger::log(Auth::id(), 'resumex_template_upload', [
            'module'        => 'resumex',
            'template_key'  => $result['template']['key']  ?? '',
            'template_name' => $result['template']['name'] ?? '',
        ]);

        $name = urlencode($result['template']['name'] ?? 'Template');
        header("Location: /admin/projects/resumex/templates?success=1&name={$name}");
        exit;
    }

    public function deleteTemplate(): void
    {
        $this->requirePermission('resumex.templates');

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->redirectWithError('/admin/projects/resumex/templates', 'Invalid security token.');
        }

        $id = (int) ($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->redirectWithError('/admin/projects/resumex/templates', 'Invalid template ID.');
        }

        $result = $this->templateModel->delete($id);

        if (!$result['success']) {
            $this->redirectWithError('/admin/projects/resumex/templates', $result['error'] ?? 'Delete failed.');
        }

        ActivityLogger::log(Auth::id(), 'resumex_template_delete', ['module' => 'resumex', 'template_id' => $id]);

        header('Location: /admin/projects/resumex/templates?success=deleted');
        exit;
    }

    /**
     * Handle upload of a full resume template (complete PHP renderer).
     */
    public function uploadFullTemplate(): void
    {
        $this->requirePermission('resumex.templates');

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->redirectWithError('/admin/projects/resumex/templates', 'Invalid security token.');
        }

        if (empty($_FILES['full_template_file']) || $_FILES['full_template_file']['error'] === UPLOAD_ERR_NO_FILE) {
            $this->redirectWithError('/admin/projects/resumex/templates', 'No file was selected for upload.');
        }

        $meta = [
            'key'         => $_POST['tpl_key']      ?? '',
            'name'        => $_POST['tpl_name']     ?? '',
            'category'    => $_POST['tpl_category'] ?? 'custom',
            'display_bg'  => $_POST['tpl_bg']       ?? '#1e1e2e',
            'display_pri' => $_POST['tpl_pri']      ?? '#00f0ff',
        ];

        $result = $this->templateModel->uploadFullTemplate(
            $_FILES['full_template_file'],
            $meta,
            Auth::id()
        );

        if (!$result['success']) {
            $this->redirectWithError('/admin/projects/resumex/templates', $result['error'] ?? 'Upload failed.');
        }

        ActivityLogger::log(Auth::id(), 'resumex_full_template_upload', [
            'module'        => 'resumex',
            'template_key'  => $result['key']  ?? '',
            'template_name' => $result['name'] ?? '',
        ]);

        $name = urlencode($result['name'] ?? 'Template');
        header("Location: /admin/projects/resumex/templates?success=1&name={$name}");
        exit;
    }

    /**
     * Serve the sample FULL template PHP file as a download.
     */
    public function downloadSampleFull(): void
    {
        $this->requirePermission('resumex.templates');

        $path = BASE_PATH . '/projects/resumex/templates/sample-full-template.php';
        if (!file_exists($path)) {
            http_response_code(404);
            echo 'Sample full template not found.';
            exit;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="sample-full-template.php"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    /**
     * Upload a preview image for a custom template.
     */
    public function uploadPreviewImage(): void
    {
        $this->requirePermission('resumex.templates');
        header('Content-Type: application/json');

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid security token.']);
            exit;
        }

        $id = (int) ($_POST['template_id'] ?? 0);
        if ($id <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid template ID.']);
            exit;
        }

        if (empty($_FILES['preview_image']) || $_FILES['preview_image']['error'] === UPLOAD_ERR_NO_FILE) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No file selected.']);
            exit;
        }

        $result = $this->templateModel->uploadPreviewImage($id, $_FILES['preview_image']);
        echo json_encode($result);
        exit;
    }

    /**
     * Serve the sample template PHP file as a download.
     */
    public function downloadSample(): void
    {
        $this->requirePermission('resumex.templates');

        $path = BASE_PATH . '/projects/resumex/templates/sample-template.php';
        if (!file_exists($path)) {
            http_response_code(404);
            echo 'Sample template not found.';
            exit;
        }

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="sample-template.php"');
        header('Content-Length: ' . filesize($path));
        readfile($path);
        exit;
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Visual Template Designer
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Open the visual designer to create a NEW template.
     */
    public function designerNew(): void
    {
        $this->requirePermission('resumex.templates');
        $csrfToken = Security::generateCsrfToken();

        $this->view('admin/projects/resumex/designer', [
            'title'       => 'ResumeX — Template Designer',
            'csrfToken'   => $csrfToken,
            'template'    => null,
            'templateId'  => 0,
            'designJson'  => 'null',
        ]);
    }

    /**
     * Open the visual designer to edit an EXISTING designed template.
     */
    public function designerEdit(int $id): void
    {
        $this->requirePermission('resumex.templates');

        $row = $this->templateModel->getById($id);
        if (!$row || ($row['template_type'] ?? '') !== 'designer') {
            header('Location: /admin/projects/resumex/templates?error=' . urlencode('Template not found.'));
            exit;
        }

        $design = json_decode($row['template_design'] ?? 'null', true);
        $csrfToken = Security::generateCsrfToken();

        $this->view('admin/projects/resumex/designer', [
            'title'      => 'ResumeX — Edit Template: ' . htmlspecialchars($row['name']),
            'csrfToken'  => $csrfToken,
            'template'   => $row,
            'templateId' => $id,
            'designJson' => json_encode($design, JSON_HEX_TAG | JSON_HEX_APOS),
        ]);
    }

    /**
     * AJAX endpoint: save (create or update) a designed template.
     * Accepts JSON body: { _token, id, meta: {...}, design: {...} }
     */
    public function designerSave(): void
    {
        $this->requirePermission('resumex.templates');
        header('Content-Type: application/json');

        $body = file_get_contents('php://input');
        $payload = json_decode($body, true);

        $token = $payload['_token'] ?? ($_POST['_token'] ?? '');
        if (!Security::validateCsrfToken($token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid security token.']);
            exit;
        }

        $id     = (int) ($payload['id'] ?? 0);
        $meta   = $payload['meta']   ?? [];
        $design = $payload['design'] ?? [];

        if (empty($design) || !is_array($design)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Design data is required.']);
            exit;
        }

        if ($id > 0) {
            // Update existing
            $result = $this->templateModel->updateDesignedTemplate($id, $meta, $design);
            if ($result['success']) {
                ActivityLogger::log(Auth::id(), 'resumex_designer_update', ['module' => 'resumex', 'template_id' => $id]);
            }
        } else {
            // Create new
            $result = $this->templateModel->saveDesignedTemplate($meta, $design, Auth::id());
            if ($result['success']) {
                ActivityLogger::log(Auth::id(), 'resumex_designer_create', [
                    'module'       => 'resumex',
                    'template_key' => $result['key'] ?? '',
                    'template_id'  => $result['id']  ?? 0,
                ]);
                $result['redirect'] = '/admin/projects/resumex/designer/' . ($result['id'] ?? 0);
            }
        }

        echo json_encode($result);
        exit;
    }



    // ──────────────────────────────────────────────────────────────────────────
    //  Settings
    // ──────────────────────────────────────────────────────────────────────────

    public function settings(): void
    {
        $this->requirePermission('resumex.settings');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->updateSettings();
            return;
        }

        $settings = $this->getResumeXSettings();

        $this->view('admin/projects/resumex/settings', [
            'title'     => 'ResumeX — Settings',
            'settings'  => $settings,
            'csrfToken' => Security::generateCsrfToken(),
            'success'   => $_SESSION['_flash']['success'] ?? null,
            'error'     => $_SESSION['_flash']['error']   ?? null,
        ]);
        unset($_SESSION['_flash']['success'], $_SESSION['_flash']['error']);
    }

    public function updateSettings(): void
    {
        $this->requirePermission('resumex.settings');

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid security token.';
            $this->redirect('/admin/projects/resumex/settings');
            return;
        }

        $keysToSave = [
            'resumex_hf_api_token',
            'resumex_hf_model_url',
            'resumex_ai_enabled',
        ];

        $oldValues = [];
        $newValues = [];

        foreach ($keysToSave as $key) {
            $row = $this->db->fetch("SELECT value FROM settings WHERE `key` = ?", [$key]);
            $oldValues[$key] = $row ? $row['value'] : null;
        }

        $aiEnabled = isset($_POST['resumex_ai_enabled']) ? '1' : '0';
        $hfToken   = Security::sanitize(trim($_POST['resumex_hf_api_token'] ?? ''));
        $hfModel   = Security::sanitize(trim($_POST['resumex_hf_model_url']  ?? ''));

        // Validate token format: must be empty OR start with 'hf_'
        if (!empty($hfToken) && !str_starts_with($hfToken, 'hf_')) {
            $_SESSION['_flash']['error'] = 'Invalid API token format. Hugging Face tokens start with "hf_".';
            $this->redirect('/admin/projects/resumex/settings');
            return;
        }

        // Validate model URL: must be empty OR a valid Hugging Face inference endpoint
        if (!empty($hfModel)) {
            $allowedPrefix = 'https://api-inference.huggingface.co/';
            if (!str_starts_with($hfModel, $allowedPrefix)) {
                $_SESSION['_flash']['error'] = 'Invalid model URL. The endpoint must start with "' . $allowedPrefix . '".';
                $this->redirect('/admin/projects/resumex/settings');
                return;
            }
        }

        $updates = [
            'resumex_ai_enabled'   => $aiEnabled,
            'resumex_hf_api_token' => $hfToken,
            'resumex_hf_model_url' => $hfModel,
        ];

        foreach ($updates as $key => $value) {
            $existing = $this->db->fetch("SELECT id FROM settings WHERE `key` = ?", [$key]);
            if ($existing) {
                $this->db->update('settings', [
                    'value'      => $value,
                    'updated_at' => date('Y-m-d H:i:s'),
                ], '`key` = ?', [$key]);
            } else {
                $this->db->insert('settings', [
                    'key'        => $key,
                    'value'      => $value,
                    'type'       => 'string',
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            }
            $newValues[$key] = $value;
        }

        ActivityLogger::log(Auth::id(), 'resumex_settings_updated', [
            'module'     => 'resumex',
            'old_values' => $oldValues,
            'new_values' => $newValues,
        ]);

        $_SESSION['_flash']['success'] = 'ResumeX settings saved successfully.';
        $this->redirect('/admin/projects/resumex/settings');
    }

    /**
     * Helper: load all ResumeX settings from the settings table.
     */
    private function getResumeXSettings(): array
    {
        $defaults = [
            'resumex_ai_enabled'   => '1',
            'resumex_hf_api_token' => defined('HUGGING_FACE_API_TOKEN') ? HUGGING_FACE_API_TOKEN : '',
            'resumex_hf_model_url' => 'https://api-inference.huggingface.co/models/mistralai/Mistral-7B-Instruct-v0.1',
        ];

        try {
            $rows = $this->db->fetchAll(
                "SELECT `key`, `value` FROM settings WHERE `key` LIKE 'resumex_%'"
            );
            foreach ($rows as $row) {
                $defaults[$row['key']] = $row['value'];
            }
        } catch (\Exception $e) {
            // Return defaults if settings table unavailable
        }

        return $defaults;
    }

    // ──────────────────────────────────────────────────────────────────────────

    public function resumes(): void
    {
        $this->requirePermission('resumex.resumes');

        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 30;
        $offset  = ($page - 1) * $perPage;
        $search  = trim($_GET['search'] ?? '');

        $rows  = $this->getAllResumes($search, $perPage, $offset);
        $total = $this->countAllResumes($search);
        $pages = (int) ceil($total / $perPage);

        $this->view('admin/projects/resumex/resumes', [
            'title'   => 'ResumeX — All Resumes',
            'resumes' => $rows,
            'total'   => $total,
            'page'    => $page,
            'pages'   => $pages,
            'search'  => $search,
        ]);
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Private helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function getStats(): array
    {
        try {
            $total = (int) ($this->db->fetch(
                "SELECT COUNT(*) AS cnt FROM resumex_resumes"
            )['cnt'] ?? 0);

            $today = (int) ($this->db->fetch(
                "SELECT COUNT(*) AS cnt FROM resumex_resumes WHERE DATE(created_at) = CURDATE()"
            )['cnt'] ?? 0);

            $users = (int) ($this->db->fetch(
                "SELECT COUNT(DISTINCT user_id) AS cnt FROM resumex_resumes"
            )['cnt'] ?? 0);

            $thisMonth = (int) ($this->db->fetch(
                "SELECT COUNT(*) AS cnt FROM resumex_resumes
                 WHERE YEAR(created_at) = YEAR(NOW()) AND MONTH(created_at) = MONTH(NOW())"
            )['cnt'] ?? 0);

            return compact('total', 'today', 'users', 'thisMonth');
        } catch (\Exception $e) {
            Logger::error('ResumeXAdminController::getStats: ' . $e->getMessage());
            return ['total' => 0, 'today' => 0, 'users' => 0, 'thisMonth' => 0];
        }
    }

    private function getRecentResumes(int $limit): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT r.*, u.name AS user_name, u.email AS user_email
                 FROM resumex_resumes r
                 LEFT JOIN users u ON u.id = r.user_id
                 ORDER BY r.created_at DESC
                 LIMIT ?",
                [$limit]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getAllResumes(string $search, int $limit, int $offset): array
    {
        try {
            $where  = '';
            $params = [];
            if ($search !== '') {
                $like   = '%' . $search . '%';
                $where  = "WHERE r.title LIKE ? OR u.name LIKE ? OR u.email LIKE ?";
                $params = [$like, $like, $like];
            }
            $params[] = $limit;
            $params[] = $offset;

            return $this->db->fetchAll(
                "SELECT r.*, u.name AS user_name, u.email AS user_email
                 FROM resumex_resumes r
                 LEFT JOIN users u ON u.id = r.user_id
                 {$where}
                 ORDER BY r.updated_at DESC
                 LIMIT ? OFFSET ?",
                $params
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    private function countAllResumes(string $search): int
    {
        try {
            $where  = '';
            $params = [];
            if ($search !== '') {
                $like   = '%' . $search . '%';
                $where  = "WHERE r.title LIKE ? OR u.name LIKE ? OR u.email LIKE ?";
                $params = [$like, $like, $like];
            }
            return (int) ($this->db->fetch(
                "SELECT COUNT(*) AS cnt
                 FROM resumex_resumes r
                 LEFT JOIN users u ON u.id = r.user_id
                 {$where}",
                $params
            )['cnt'] ?? 0);
        } catch (\Exception $e) {
            return 0;
        }
    }

    private function redirectWithError(string $url, string $message): never
    {
        header('Location: ' . $url . '?error=' . urlencode($message));
        exit;
    }
}
