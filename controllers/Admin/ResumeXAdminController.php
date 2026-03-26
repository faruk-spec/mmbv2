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

        ActivityLogger::log('resumex_full_template_upload', 'resumex', [
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
