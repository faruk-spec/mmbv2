<?php
/**
 * ResumeX Template Upload Controller
 *
 * Admin-only controller for uploading and managing custom resume templates.
 *
 * @package MMB\Projects\ResumeX\Controllers
 */

namespace Projects\ResumeX\Controllers;

use Core\Auth;
use Core\View;
use Core\Security;
use Projects\ResumeX\Models\TemplateModel;
use Projects\ResumeX\Models\ResumeModel;

class TemplateUploadController
{
    private TemplateModel $templateModel;
    private ResumeModel   $resumeModel;

    public function __construct()
    {
        $this->requireAdmin();
        $this->templateModel = new TemplateModel();
        $this->resumeModel   = new ResumeModel();
    }

    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Show the template upload/management page.
     */
    public function index(): void
    {
        $customTemplates = $this->templateModel->getAllRows();
        $builtinTemplates = $this->resumeModel->getAllThemePresets();

        View::render('projects/resumex/template_upload', [
            'title'            => 'Manage Resume Templates',
            'user'             => Auth::user(),
            'customTemplates'  => $customTemplates,
            'builtinTemplates' => $builtinTemplates,
            'csrfToken'        => Security::generateCsrfToken(),
            'success'          => $_GET['success'] ?? null,
            'error'            => $_GET['error']   ?? null,
            'uploadedName'     => $_GET['name']    ?? null,
        ]);
    }

    /**
     * Handle template file upload (POST).
     */
    public function upload(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->redirectWithError('Invalid security token. Please try again.');
        }

        if (empty($_FILES['template_file']) || $_FILES['template_file']['error'] === UPLOAD_ERR_NO_FILE) {
            $this->redirectWithError('No file was selected for upload.');
        }

        $result = $this->templateModel->upload($_FILES['template_file'], Auth::id());

        if (!$result['success']) {
            $this->redirectWithError($result['error'] ?? 'Upload failed.');
        }

        $name = urlencode($result['template']['name'] ?? 'Template');
        header("Location: /projects/resumex/templates/upload?success=1&name={$name}");
        exit;
    }

    /**
     * Delete a custom template (POST).
     */
    public function delete(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->redirectWithError('Invalid security token. Please try again.');
        }

        $id = (int) ($_POST['id'] ?? 0);

        if ($id <= 0) {
            $this->redirectWithError('Invalid template ID.');
        }

        $result = $this->templateModel->delete($id);

        if (!$result['success']) {
            $this->redirectWithError($result['error'] ?? 'Delete failed.');
        }

        header('Location: /projects/resumex/templates/upload?success=deleted');
        exit;
    }

    // ──────────────────────────────────────────────────────────────────────────
    //  Private helpers
    // ──────────────────────────────────────────────────────────────────────────

    private function requireAdmin(): void
    {
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo '<h1>403 — Access Denied</h1><p>Admin access required.</p>';
            exit;
        }
    }

    private function redirectWithError(string $message): never
    {
        header('Location: /projects/resumex/templates/upload?error=' . urlencode($message));
        exit;
    }
}
