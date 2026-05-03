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
    //  Plans (Pro Feature Controls)
    // ──────────────────────────────────────────────────────────────────────────

    public function plans(): void
    {
        $this->requirePermission('resumex.settings');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->savePlans();
            return;
        }

        $settings = $this->getResumeXSettings();

        $this->view('admin/projects/resumex/plans', [
            'title'     => 'ResumeX — Plans & Pro Features',
            'settings'  => $settings,
            'csrfToken' => Security::generateCsrfToken(),
            'success'   => $_SESSION['_flash']['success'] ?? null,
            'error'     => $_SESSION['_flash']['error']   ?? null,
        ]);
        unset($_SESSION['_flash']['success'], $_SESSION['_flash']['error']);
    }

    public function savePlans(): void
    {
        $this->requirePermission('resumex.settings');

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['_flash']['error'] = 'Invalid security token.';
            $this->redirect('/admin/projects/resumex/plans');
            return;
        }

        $maxResumesFree    = max(0, (int) ($_POST['resumex_max_resumes_free'] ?? 3));
        $maxResumesPro     = max(0, (int) ($_POST['resumex_max_resumes_pro'] ?? 0));
        $pdfWatermarkFree  = isset($_POST['resumex_pdf_watermark_free']) ? '1' : '0';
        $pdfWatermarkText  = Security::sanitize(trim($_POST['resumex_pdf_watermark_text'] ?? 'ResumeX Free'));
        $proTemplatesOnly  = isset($_POST['resumex_pro_templates_only']) ? '1' : '0';
        $linkedinImport    = isset($_POST['resumex_linkedin_import']) ? '1' : '0';
        $publicResumes     = isset($_POST['resumex_public_resumes']) ? '1' : '0';
        $customDomain      = isset($_POST['resumex_custom_domain']) ? '1' : '0';

        $updates = [
            'resumex_max_resumes_free'    => (string) $maxResumesFree,
            'resumex_max_resumes_pro'     => (string) $maxResumesPro,
            'resumex_pdf_watermark_free'  => $pdfWatermarkFree,
            'resumex_pdf_watermark_text'  => $pdfWatermarkText !== '' ? $pdfWatermarkText : 'ResumeX Free',
            'resumex_pro_templates_only'  => $proTemplatesOnly,
            'resumex_linkedin_import'     => $linkedinImport,
            'resumex_public_resumes'      => $publicResumes,
            'resumex_custom_domain'       => $customDomain,
        ];

        foreach ($updates as $key => $value) {
            $existing = $this->db->fetch("SELECT id FROM settings WHERE `key` = ?", [$key]);
            if ($existing) {
                $this->db->update('settings', ['value' => $value, 'updated_at' => date('Y-m-d H:i:s')], '`key` = ?', [$key]);
            } else {
                $this->db->insert('settings', ['key' => $key, 'value' => $value, 'type' => 'string', 'created_at' => date('Y-m-d H:i:s')]);
            }
        }

        ActivityLogger::log(Auth::id(), 'resumex_plans_updated', ['module' => 'resumex']);
        $_SESSION['_flash']['success'] = 'ResumeX plan settings saved successfully.';
        $this->redirect('/admin/projects/resumex/plans');
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
            'resumex_openai_api_key',
            'resumex_openai_model',
            'resumex_ai_enabled',
            'resumex_ai_daily_limit',
        ];

        $oldValues = [];
        $newValues = [];

        foreach ($keysToSave as $key) {
            $row = $this->db->fetch("SELECT value FROM settings WHERE `key` = ?", [$key]);
            $oldValues[$key] = $row ? $row['value'] : null;
        }

        $aiEnabled         = isset($_POST['resumex_ai_enabled']) ? '1' : '0';
        $openaiKey         = Security::sanitize(trim($_POST['resumex_openai_api_key'] ?? ''));
        $openaiModel       = Security::sanitize(trim($_POST['resumex_openai_model'] ?? ''));
        $dailyLimit        = max(0, (int) ($_POST['resumex_ai_daily_limit'] ?? 0));

        // Validate key format: must be empty OR start with 'sk-'
        if (!empty($openaiKey) && !str_starts_with($openaiKey, 'sk-')) {
            $_SESSION['_flash']['error'] = 'Invalid API key format. OpenAI keys start with "sk-".';
            $this->redirect('/admin/projects/resumex/settings');
            return;
        }

        // Validate model: allow only known safe model names (alphanumeric + dash + dot)
        if (!empty($openaiModel) && !preg_match('/^[a-zA-Z0-9\-\.]+$/', $openaiModel)) {
            $_SESSION['_flash']['error'] = 'Invalid model name format.';
            $this->redirect('/admin/projects/resumex/settings');
            return;
        }

        // Validate daily limit: 0–9999
        if ($dailyLimit > 9999) {
            $_SESSION['_flash']['error'] = 'Daily limit must be between 0 and 9999.';
            $this->redirect('/admin/projects/resumex/settings');
            return;
        }

        $updates = [
            'resumex_ai_enabled'          => $aiEnabled,
            'resumex_openai_api_key'      => $openaiKey,
            'resumex_openai_model'        => $openaiModel,
            'resumex_ai_daily_limit'      => (string) $dailyLimit,
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

    // ──────────────────────────────────────────────────────────────────────────
    //  Analytics
    // ──────────────────────────────────────────────────────────────────────────

    public function analytics(): void
    {
        $this->requirePermission('resumex');

        $stats       = $this->getStats();
        $daily       = $this->getDailyCreations(30);
        $byTemplate  = $this->getResumesByTemplate(10);
        $topUsers    = $this->getTopUsers(10);
        $aiUsage     = $this->getAiUsageSummary();

        $this->view('admin/projects/resumex/analytics', [
            'title'      => 'ResumeX — Analytics',
            'stats'      => $stats,
            'daily'      => $daily,
            'byTemplate' => $byTemplate,
            'topUsers'   => $topUsers,
            'aiUsage'    => $aiUsage,
        ]);
    }

    private function getDailyCreations(int $days): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT DATE(created_at) AS day, COUNT(*) AS cnt
                 FROM resumex_resumes
                 WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
                 GROUP BY DATE(created_at)
                 ORDER BY day ASC",
                [$days]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getResumesByTemplate(int $limit): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT template, COUNT(*) AS cnt
                 FROM resumex_resumes
                 GROUP BY template
                 ORDER BY cnt DESC
                 LIMIT ?",
                [$limit]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getTopUsers(int $limit): array
    {
        try {
            return $this->db->fetchAll(
                "SELECT r.user_id, u.name AS user_name, u.email AS user_email,
                        COUNT(*) AS resume_count,
                        MAX(r.updated_at) AS last_active
                 FROM resumex_resumes r
                 LEFT JOIN users u ON u.id = r.user_id
                 GROUP BY r.user_id, u.name, u.email
                 ORDER BY resume_count DESC
                 LIMIT ?",
                [$limit]
            );
        } catch (\Exception $e) {
            return [];
        }
    }

    private function getAiUsageSummary(): array
    {
        try {
            $today = (int) ($this->db->fetch(
                "SELECT COUNT(*) AS cnt FROM activity_logs
                 WHERE module = 'resumex' AND action LIKE '%ai%' AND DATE(created_at) = CURDATE()"
            )['cnt'] ?? 0);
            $week = (int) ($this->db->fetch(
                "SELECT COUNT(*) AS cnt FROM activity_logs
                 WHERE module = 'resumex' AND action LIKE '%ai%'
                 AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
            )['cnt'] ?? 0);
            $month = (int) ($this->db->fetch(
                "SELECT COUNT(*) AS cnt FROM activity_logs
                 WHERE module = 'resumex' AND action LIKE '%ai%'
                 AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)"
            )['cnt'] ?? 0);
            return compact('today', 'week', 'month');
        } catch (\Exception $e) {
            return ['today' => 0, 'week' => 0, 'month' => 0];
        }
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Pro Feature Settings helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Extended list of all ResumeX settings including pro-feature controls.
     */
    private function getResumeXSettings(): array
    {
        $defaults = [
            'resumex_ai_enabled'           => '1',
            'resumex_openai_api_key'       => defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '',
            'resumex_openai_model'         => 'gpt-4o-mini',
            'resumex_ai_daily_limit'       => '0',
            'resumex_max_resumes_free'     => '3',
            'resumex_max_resumes_pro'      => '0',
            'resumex_pdf_watermark_free'   => '0',
            'resumex_pdf_watermark_text'   => 'ResumeX Free',
            'resumex_pro_templates_only'   => '0',
            'resumex_linkedin_import'      => '1',
            'resumex_public_resumes'       => '1',
            'resumex_custom_domain'        => '0',
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

    private function redirectWithError(string $url, string $message): never
    {
        header('Location: ' . $url . '?error=' . urlencode($message));
        exit;
    }

    public function toggleTemplatePro(): void
    {
        $this->requirePermission('resumex.templates');
        header('Content-Type: application/json');

        $token = $_POST['_token'] ?? '';
        if (!\Core\Security::validateCsrfToken($token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid security token.']);
            exit;
        }

        $id  = (int) ($_POST['id'] ?? 0);
        $val = ($_POST['is_pro'] ?? '0') === '1' ? 1 : 0;

        if (!$id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid template ID.']);
            exit;
        }

        try {
            $this->db->update('resumex_templates', ['is_pro' => $val, 'updated_at' => date('Y-m-d H:i:s')], 'id = ?', [$id]);
            \Core\ActivityLogger::log(\Core\Auth::id(), 'resumex_template_pro_toggle', [
                'module' => 'resumex', 'template_id' => $id, 'is_pro' => $val,
            ]);
            echo json_encode(['success' => true, 'is_pro' => $val]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Database error.']);
        }
        exit;
    }

    /**
     * Toggle PRO status for a built-in (hardcoded) template.
     * Stores the list of pro built-in template keys in settings.
     */
    public function toggleBuiltinTemplatePro(): void
    {
        $this->requirePermission('resumex.templates');
        header('Content-Type: application/json');

        $token = $_POST['_token'] ?? '';
        if (!\Core\Security::validateCsrfToken($token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid security token.']);
            exit;
        }

        $key = trim($_POST['key'] ?? '');
        $val = ($_POST['is_pro'] ?? '0') === '1' ? 1 : 0;

        if ($key === '' || !preg_match('/^[a-z0-9_\-]+$/', $key)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid template key.']);
            exit;
        }

        try {
            $row = $this->db->fetch("SELECT value FROM settings WHERE `key` = 'resumex_builtin_pro_templates'");
            $proKeys = [];
            if ($row && !empty($row['value'])) {
                $proKeys = json_decode($row['value'], true) ?: [];
            }

            if ($val) {
                if (!in_array($key, $proKeys, true)) {
                    $proKeys[] = $key;
                }
            } else {
                $proKeys = array_values(array_filter($proKeys, fn($k) => $k !== $key));
            }

            $json = json_encode(array_values($proKeys));
            $existing = $this->db->fetch("SELECT id FROM settings WHERE `key` = 'resumex_builtin_pro_templates'");
            if ($existing) {
                $this->db->update('settings', ['value' => $json, 'updated_at' => date('Y-m-d H:i:s')], '`key` = ?', ['resumex_builtin_pro_templates']);
            } else {
                $this->db->query("INSERT INTO settings (`key`, `value`, `created_at`, `updated_at`) VALUES (?, ?, NOW(), NOW())", ['resumex_builtin_pro_templates', $json]);
            }

            \Core\ActivityLogger::log(\Core\Auth::id(), 'resumex_builtin_template_pro_toggle', [
                'module' => 'resumex', 'template_key' => $key, 'is_pro' => $val,
            ]);
            echo json_encode(['success' => true, 'is_pro' => $val]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Database error.']);
        }
        exit;
    }
}
