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
        $template = trim($_POST['template'] ?? 'ocean-blue');

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

        if ($newId) {
            header("Location: /projects/resumex/edit/{$newId}?duplicated=1");
        } else {
            header('Location: /projects/resumex?error=duplicate_failed');
        }
        exit;
    }

    /**
     * Download resume — generates and streams a PDF file directly to the browser.
     * Uses Chromium headless for server-side PDF generation.
     * Falls back to the browser print dialog if Chromium is unavailable.
     */
    public function download(int $id): void
    {
        $userId = Auth::id();

        if (!$userId) {
            // Not logged in — redirect to login page
            header('Location: /login?redirect=' . urlencode('/projects/resumex/download/' . $id));
            exit;
        }

        $resume = $this->resumeModel->get($id, $userId);

        if (!$resume) {
            http_response_code(404);
            View::render('projects/resumex/download_notfound', [
                'title' => 'Resume Not Found',
                'user'  => Auth::user(),
                'id'    => $id,
            ]);
            return;
        }

        $resumeData    = json_decode($resume['resume_data']   ?? '{}', true) ?: $this->resumeModel->getDefaultData();
        $themeSettings = json_decode($resume['theme_settings'] ?? '{}', true) ?: $this->resumeModel->getThemePreset($resume['template']);

        // Try server-side PDF generation with Chromium headless
        $chromium = $this->findChromiumBinary();
        if ($chromium !== null) {
            $html = $this->renderPrintHtml($resume, $resumeData, $themeSettings);
            $pdf  = $this->generatePdfWithChromium($chromium, $html);

            if ($pdf !== null) {
                $filename = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $resume['title'] ?: 'resume');
                // Encode filename per RFC 6266 to prevent header injection
                $safeFilename = rawurlencode($filename) . '.pdf';
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . $filename . '.pdf"; filename*=UTF-8\'\'' . $safeFilename);
                header('Content-Length: ' . strlen($pdf));
                header('Cache-Control: private, max-age=0, must-revalidate');
                header('Pragma: private');
                echo $pdf;
                exit;
            }
        }

        // Fallback: render the print view with auto-print dialog
        View::render('projects/resumex/print', [
            'title'         => htmlspecialchars($resume['title']),
            'resume'        => $resume,
            'resumeData'    => $resumeData,
            'themeSettings' => $themeSettings,
            'autoPrint'     => true,
        ]);
    }

    /**
     * Find an available Chromium or Google Chrome binary on the system.
     */
    private function findChromiumBinary(): ?string
    {
        $candidates = [
            '/usr/bin/chromium',
            '/usr/bin/chromium-browser',
            '/usr/bin/google-chrome',
            '/usr/bin/google-chrome-stable',
        ];

        foreach ($candidates as $bin) {
            if (is_executable($bin)) {
                return $bin;
            }
        }

        return null;
    }

    /**
     * Render the print view to an HTML string for PDF generation.
     */
    private function renderPrintHtml(array $resume, array $resumeData, array $themeSettings): string
    {
        $autoPrint = false;
        ob_start();
        extract([
            'resume'        => $resume,
            'resumeData'    => $resumeData,
            'themeSettings' => $themeSettings,
            'autoPrint'     => $autoPrint,
        ]);
        include dirname(__DIR__) . '/views/print.php';
        $html = ob_get_clean() ?: '';

        // Remove the Google Fonts @import since file:// rendering cannot load external resources.
        // Chromium will fall back to the system font (Arial) specified as the CSS fallback.
        $html = preg_replace('/@import\s+url\([^)]*fonts\.googleapis\.com[^)]*\)\s*;/', '', $html);

        return $html;
    }

    /**
     * Use Chromium headless to convert HTML to a PDF byte string.
     * Returns null on failure so the caller can fall back gracefully.
     */
    private function generatePdfWithChromium(string $chromium, string $html): ?string
    {
        $tmpHtml = tempnam(sys_get_temp_dir(), 'resumex_') . '.html';
        $tmpPdf  = tempnam(sys_get_temp_dir(), 'resumex_') . '.pdf';

        try {
            if (file_put_contents($tmpHtml, $html) === false) {
                return null;
            }

            // --no-sandbox is required when running as root (e.g. in Docker/container environments).
            $noSandbox = (function_exists('posix_getuid') && posix_getuid() === 0) ? ' --no-sandbox' : '';

            $cmd = escapeshellarg($chromium)
                . ' --headless=new'
                . ' --disable-gpu'
                . $noSandbox
                . ' --disable-dev-shm-usage'
                . ' --run-all-compositor-stages-before-draw'
                . ' --print-to-pdf=' . escapeshellarg($tmpPdf)
                . ' --print-to-pdf-no-header'
                . ' ' . escapeshellarg('file://' . $tmpHtml)
                . ' 2>/dev/null';

            exec($cmd, $output, $returnCode);

            if ($returnCode === 0 && file_exists($tmpPdf) && filesize($tmpPdf) > 0) {
                return file_get_contents($tmpPdf) ?: null;
            }
        } finally {
            if (file_exists($tmpHtml)) {
                @unlink($tmpHtml);
            }
            if (file_exists($tmpPdf)) {
                @unlink($tmpPdf);
            }
        }

        return null;
    }
}
