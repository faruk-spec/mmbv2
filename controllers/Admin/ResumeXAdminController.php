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

        ActivityLogger::log('resumex_template_upload', 'resumex', [
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

        ActivityLogger::log('resumex_template_delete', 'resumex', ['template_id' => $id]);

        header('Location: /admin/projects/resumex/templates?success=deleted');
        exit;
    }

    /**
     * Show the visual Template Designer form.
     * Pass ?prefill=<built-in-key> to pre-populate from an existing built-in template.
     */
    public function createTemplate(): void
    {
        $this->requirePermission('resumex.templates');

        $allTemplates = $this->resumeModel->getAllThemePresets();
        $prefillKey   = trim($_GET['prefill'] ?? '');
        $prefill      = null;
        $isOverride   = false;

        $builtinTemplates = $this->resumeModel->getAllThemePresets();
        $customRows       = $this->templateModel->getAllRows();
        $customKeys       = array_column($customRows, 'key');

        if ($prefillKey !== '' && isset($allTemplates[$prefillKey])) {
            $prefill    = $allTemplates[$prefillKey];
            $isOverride = !in_array($prefillKey, $customKeys, true);  // only built-ins are flagged as override
        }

        $this->view('admin/projects/resumex/template_create', [
            'title'            => 'ResumeX — Template Designer',
            'csrfToken'        => Security::generateCsrfToken(),
            'prefill'          => $prefill,
            'isOverride'       => $isOverride,
            'builtinKeys'      => array_keys($this->resumeModel->getAllThemePresets()),
            'error'            => $_GET['error'] ?? null,
        ]);
    }

    /**
     * Handle form submission from the Template Designer.
     */
    public function saveTemplate(): void
    {
        $this->requirePermission('resumex.templates');

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->redirectWithError('/admin/projects/resumex/templates/create', 'Invalid security token.');
        }

        // Build preset array from POST data
        $data = [
            'key'              => strtolower(trim($_POST['key']         ?? '')),
            'name'             => trim($_POST['name']                   ?? ''),
            'category'         => trim($_POST['category']               ?? 'custom'),
            'primaryColor'     => trim($_POST['primaryColor']           ?? '#000000'),
            'secondaryColor'   => trim($_POST['secondaryColor']         ?? '#000000'),
            'backgroundColor'  => trim($_POST['backgroundColor']        ?? '#ffffff'),
            'surfaceColor'     => trim($_POST['surfaceColor']           ?? '#ffffff'),
            'textColor'        => trim($_POST['textColor']              ?? '#000000'),
            'textMuted'        => trim($_POST['textMuted']              ?? '#666666'),
            'borderColor'      => trim($_POST['borderColor']            ?? '#e2e8f0'),
            'fontFamily'       => trim($_POST['fontFamily']             ?? 'Inter'),
            'fontSize'         => trim($_POST['fontSize']               ?? '14'),
            'fontWeight'       => trim($_POST['fontWeight']             ?? '400'),
            'headerStyle'      => trim($_POST['headerStyle']            ?? 'minimal'),
            'buttonStyle'      => trim($_POST['buttonStyle']            ?? 'pill'),
            'cardStyle'        => trim($_POST['cardStyle']              ?? 'bordered'),
            'spacing'          => trim($_POST['spacing']                ?? 'normal'),
            'layoutMode'       => trim($_POST['layoutMode']             ?? 'two-column'),
            'iconStyle'        => trim($_POST['iconStyle']              ?? 'filled'),
            'accentHighlights' => !empty($_POST['accentHighlights']),
            'animations'       => !empty($_POST['animations']),
            'layoutStyle'      => trim($_POST['layoutStyle']            ?? 'minimal'),
            'colorVariants'    => [],
        ];

        // Parse color variants (up to 4)
        $variantLabels    = $_POST['variant_label']    ?? [];
        $variantPrimaries = $_POST['variant_primary']  ?? [];
        $variantSeconds   = $_POST['variant_secondary'] ?? [];
        for ($i = 0; $i < min(4, count($variantLabels)); $i++) {
            $label = trim($variantLabels[$i] ?? '');
            $prim  = trim($variantPrimaries[$i] ?? '');
            $sec   = trim($variantSeconds[$i]   ?? '');
            if ($label !== '' && $prim !== '' && $sec !== '') {
                $data['colorVariants'][] = ['label' => $label, 'primary' => $prim, 'secondary' => $sec];
            }
        }

        $isOverride = !empty($_POST['is_override']);
        $result     = $this->templateModel->createFromData($data, Auth::id(), $isOverride);

        if (!$result['success']) {
            $this->redirectWithError('/admin/projects/resumex/templates/create', $result['error'] ?? 'Failed to save template.');
        }

        ActivityLogger::log('resumex_template_create', 'resumex', [
            'template_key'  => $data['key'],
            'template_name' => $data['name'],
            'is_override'   => $isOverride ? 1 : 0,
        ]);

        $name = urlencode($data['name']);
        header("Location: /admin/projects/resumex/templates?success=1&name={$name}");
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
    //  All resumes (read-only listing)
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
