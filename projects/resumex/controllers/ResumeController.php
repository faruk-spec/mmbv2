<?php
/**
 * ResumeX Resume Controller
 *
 * @package MMB\Projects\ResumeX\Controllers
 */

namespace Projects\ResumeX\Controllers;

use Core\Auth;
use Core\View;
use Core\Security;
use Projects\ResumeX\Models\ResumeModel;

class ResumeController
{
    private ResumeModel $resumeModel;

    public function __construct()
    {
        $this->resumeModel = new ResumeModel();
    }

    /**
     * Show create form (template picker)
     */
    public function create(): void
    {
        $allThemes = $this->resumeModel->getAllThemePresets();

        View::render('projects/resumex/create', [
            'title'     => 'Create New Resume',
            'user'      => Auth::user(),
            'allThemes' => $allThemes,
        ]);
    }

    /**
     * Store a new resume
     */
    public function store(): void
    {
        Security::validateCsrfToken($_POST['_token'] ?? '');

        $userId   = Auth::id();
        $title    = trim($_POST['title'] ?? 'My Resume');
        $template = trim($_POST['template'] ?? 'midnight-pro');

        if (empty($title)) {
            $title = 'My Resume';
        }

        $id = $this->resumeModel->create($userId, $title, $template);

        if ($id) {
            header("Location: /projects/resumex/edit/{$id}?new=1");
        } else {
            header("Location: /projects/resumex/create?error=1");
        }
        exit;
    }

    /**
     * Show editor for a resume
     */
    public function edit(int $id): void
    {
        $userId = Auth::id();
        $resume = $this->resumeModel->get($id, $userId);

        if (!$resume) {
            http_response_code(404);
            echo '<h2>Resume not found.</h2>';
            return;
        }

        $resumeData   = json_decode($resume['resume_data']   ?? '{}', true) ?: $this->resumeModel->getDefaultData();
        $themeSettings = json_decode($resume['theme_settings'] ?? '{}', true) ?: $this->resumeModel->getThemePreset($resume['template']);
        $allThemes    = $this->resumeModel->getAllThemePresets();

        View::render('projects/resumex/editor', [
            'title'         => 'Edit Resume - ' . htmlspecialchars($resume['title']),
            'user'          => Auth::user(),
            'resume'        => $resume,
            'resumeData'    => $resumeData,
            'themeSettings' => $themeSettings,
            'allThemes'     => $allThemes,
            'csrfToken'     => Security::generateCsrfToken(),
        ]);
    }

    /**
     * Save resume data (AJAX / POST)
     */
    public function save(int $id): void
    {
        $userId = Auth::id();

        // Support JSON body (AJAX)
        $body = file_get_contents('php://input');
        if (!empty($body)) {
            $payload = json_decode($body, true);
            $token   = $payload['_token'] ?? ($_POST['_token'] ?? '');
        } else {
            $payload = $_POST;
            $token   = $_POST['_token'] ?? '';
        }

        Security::validateCsrfToken($token);

        $data = [];
        if (isset($payload['title'])) {
            $data['title'] = trim($payload['title']);
        }
        if (isset($payload['template'])) {
            $data['template'] = $payload['template'];
        }
        if (isset($payload['resume_data'])) {
            $rd = $payload['resume_data'];
            $data['resume_data'] = is_array($rd) ? $rd : json_decode($rd, true);
        }
        if (isset($payload['theme_settings'])) {
            $ts = $payload['theme_settings'];
            $data['theme_settings'] = is_array($ts) ? $ts : json_decode($ts, true);
        }

        $success = $this->resumeModel->update($id, $userId, $data);

        if (!empty($body)) {
            header('Content-Type: application/json');
            echo json_encode(['success' => $success, 'message' => $success ? 'Saved successfully' : 'Save failed']);
        } else {
            header("Location: /projects/resumex/edit/{$id}?saved=1");
        }
        exit;
    }

    /**
     * Preview a resume
     */
    public function preview(int $id): void
    {
        $userId = Auth::id();
        $resume = $this->resumeModel->get($id, $userId);

        if (!$resume) {
            http_response_code(404);
            echo '<h2>Resume not found.</h2>';
            return;
        }

        $resumeData   = json_decode($resume['resume_data']   ?? '{}', true) ?: $this->resumeModel->getDefaultData();
        $themeSettings = json_decode($resume['theme_settings'] ?? '{}', true) ?: $this->resumeModel->getThemePreset($resume['template']);

        View::render('projects/resumex/preview', [
            'title'         => 'Preview - ' . htmlspecialchars($resume['title']),
            'user'          => Auth::user(),
            'resume'        => $resume,
            'resumeData'    => $resumeData,
            'themeSettings' => $themeSettings,
        ]);
    }

    /**
     * Delete a resume
     */
    public function delete(): void
    {
        Security::validateCsrfToken($_POST['_token'] ?? '');

        $userId = Auth::id();
        $id     = (int) ($_POST['id'] ?? 0);

        if ($id) {
            $this->resumeModel->delete($id, $userId);
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true]);
        exit;
    }

    /**
     * Duplicate a resume
     */
    public function duplicate(): void
    {
        Security::validateCsrfToken($_POST['_token'] ?? '');

        $userId = Auth::id();
        $id     = (int) ($_POST['id'] ?? 0);

        $newId = $id ? $this->resumeModel->duplicate($id, $userId) : 0;

        header('Content-Type: application/json');
        echo json_encode(['success' => (bool) $newId, 'id' => $newId]);
        exit;
    }

    /**
     * Download resume as HTML (print-friendly)
     */
    public function download(int $id): void
    {
        $userId = Auth::id();
        $resume = $this->resumeModel->get($id, $userId);

        if (!$resume) {
            http_response_code(404);
            echo '<h2>Resume not found.</h2>';
            return;
        }

        $resumeData   = json_decode($resume['resume_data']   ?? '{}', true) ?: $this->resumeModel->getDefaultData();
        $themeSettings = json_decode($resume['theme_settings'] ?? '{}', true) ?: $this->resumeModel->getThemePreset($resume['template']);

        // Render a print-optimised view
        View::render('projects/resumex/print', [
            'title'         => htmlspecialchars($resume['title']),
            'resume'        => $resume,
            'resumeData'    => $resumeData,
            'themeSettings' => $themeSettings,
        ]);
    }
}
