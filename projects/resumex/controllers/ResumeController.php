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

        // Enforce max resumes limit
        $maxResumesFree = (int) $this->getAdminSetting('resumex_max_resumes_free', '3');
        $maxResumesPro  = (int) $this->getAdminSetting('resumex_max_resumes_pro', '0');
        if ($maxResumesFree > 0 || $maxResumesPro > 0) {
            $currentCount = count($this->resumeModel->getAll($userId));
            $isPro = $this->userHasPro($userId);
            $limit = $isPro ? $maxResumesPro : $maxResumesFree;
            if ($limit > 0 && $currentCount >= $limit) {
                header("Location: /projects/resumex/create?error=limit_reached");
                exit;
            }
        }

        // Collect optional colour-variant overrides from the picker
        $colorOverride = [];
        $rawPri = trim($_POST['color_primary'] ?? '');
        $rawSec = trim($_POST['color_secondary'] ?? '');
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $rawPri)) {
            $colorOverride['primaryColor'] = $rawPri;
        }
        if (preg_match('/^#[0-9a-fA-F]{6}$/', $rawSec)) {
            $colorOverride['secondaryColor'] = $rawSec;
        }

        $id = $this->resumeModel->create($userId, $title, $template, $colorOverride);

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
            View::render('projects/resumex/resume_notfound', [
                'title'     => 'Resume Not Found',
                'user'      => Auth::user(),
                'id'        => $id,
                'allThemes' => $this->resumeModel->getAllThemePresets(),
            ]);
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
     * Preview a resume.
     * If the resume uses a full custom template, includes it directly.
     * If the resume uses a designer template, renders via designed_template.php.
     */
    public function preview(int $id): void
    {
        $userId = Auth::id();
        $resume = $this->resumeModel->get($id, $userId);

        if (!$resume) {
            http_response_code(404);
            View::render('projects/resumex/resume_notfound', [
                'title'     => 'Resume Not Found',
                'user'      => Auth::user(),
                'id'        => $id,
                'allThemes' => $this->resumeModel->getAllThemePresets(),
            ]);
            return;
        }

        $resumeData    = json_decode($resume['resume_data']   ?? '{}', true) ?: $this->resumeModel->getDefaultData();
        $themeSettings = json_decode($resume['theme_settings'] ?? '{}', true) ?: $this->resumeModel->getThemePreset($resume['template']);

        // Check if this template is a designer template
        $designerDesign = $this->resumeModel->getDesignedTemplateDesign($resume['template']);
        if ($designerDesign !== null) {
            $isEmbed = isset($_GET['embed']);
            $isPdf   = false;
            $title   = htmlspecialchars($resume['title'] ?? 'Resume', ENT_QUOTES, 'UTF-8');
            extract(compact('resumeData', 'themeSettings', 'resume', 'isEmbed', 'isPdf', 'title', 'designerDesign'));
            include dirname(__DIR__) . '/views/designed_template.php';
            return;
        }

        // Check if this template is a full custom renderer
        $fullTplFile = $this->resumeModel->getFullTemplateFile($resume['template']);
        if ($fullTplFile !== null) {
            $isEmbed    = isset($_GET['embed']);
            $isPdf      = isset($_GET['pdf']);
            $title      = htmlspecialchars($resume['title'] ?? 'Resume', ENT_QUOTES, 'UTF-8');
            extract(compact('resumeData', 'themeSettings', 'resume', 'isEmbed', 'isPdf', 'title'));
            include $fullTplFile;
            return;
        }

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

            // Apply watermark for free users if enabled
            $addWatermark = ($this->getAdminSetting('resumex_pdf_watermark_free', '0') === '1' && !$this->userHasPro($userId));
            if ($addWatermark) {
                $watermarkText = $this->getAdminSetting('resumex_pdf_watermark_text', 'ResumeX Free');
                // Strip any characters that could break out of CSS content string
                $watermarkText = preg_replace('/[^a-zA-Z0-9\s\-\_\.\/\!\@\#\$\%\^\&\*\(\)\+\=\[\]\{\}]/', '', $watermarkText);
                $watermarkText = str_replace(['"', "'", '\\'], '', $watermarkText);
                if (empty($watermarkText)) {
                    $watermarkText = 'ResumeX Free';
                }
                $watermarkStyle = '<style>body::after{content:"' . $watermarkText . '";position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-30deg);font-size:5rem;font-weight:900;color:rgba(0,0,0,0.07);z-index:9999;pointer-events:none;white-space:nowrap;letter-spacing:0.2em;}</style>';
                if (strpos($html, '</head>') !== false) {
                    $html = str_replace('</head>', $watermarkStyle . '</head>', $html);
                } else {
                    $html = $watermarkStyle . $html;
                }
            }

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
        $designerDesign = $this->resumeModel->getDesignedTemplateDesign($resume['template']);
        if ($designerDesign !== null) {
            $isEmbed = false;
            $isPdf   = false;
            $title   = htmlspecialchars($resume['title'] ?? 'Resume', ENT_QUOTES, 'UTF-8');
            extract(compact('resumeData', 'themeSettings', 'resume', 'isEmbed', 'isPdf', 'title', 'designerDesign'));
            include dirname(__DIR__) . '/views/designed_template.php';
            return;
        }

        $fullTplFile = $this->resumeModel->getFullTemplateFile($resume['template']);
        if ($fullTplFile !== null) {
            $isEmbed = false;
            $isPdf   = false;
            $title   = htmlspecialchars($resume['title'] ?? 'Resume', ENT_QUOTES, 'UTF-8');
            extract(compact('resumeData', 'themeSettings', 'resume', 'isEmbed', 'isPdf', 'title'));
            include $fullTplFile;
            return;
        }

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
     * If the resume uses a full custom template, that file is used instead of print.php.
     */
    private function renderPrintHtml(array $resume, array $resumeData, array $themeSettings): string
    {
        $autoPrint   = false;
        $isEmbed     = false;
        $isPdf       = true;
        $title       = htmlspecialchars($resume['title'] ?? 'Resume', ENT_QUOTES, 'UTF-8');

        ob_start();

        // Check designer template first
        $designerDesign = $this->resumeModel->getDesignedTemplateDesign($resume['template']);
        if ($designerDesign !== null) {
            extract(compact('resumeData', 'themeSettings', 'resume', 'isEmbed', 'isPdf', 'title', 'designerDesign'));
            include dirname(__DIR__) . '/views/designed_template.php';
        } else {
            $fullTplFile = $this->resumeModel->getFullTemplateFile($resume['template']);
            if ($fullTplFile !== null) {
                extract(compact('resumeData', 'themeSettings', 'resume', 'isEmbed', 'isPdf', 'title'));
                include $fullTplFile;
            } else {
                extract([
                    'resume'        => $resume,
                    'resumeData'    => $resumeData,
                    'themeSettings' => $themeSettings,
                    'autoPrint'     => $autoPrint,
                ]);
                include dirname(__DIR__) . '/views/print.php';
            }
        }

        $html = ob_get_clean() ?: '';

        // Remove Google Fonts @import since file:// rendering cannot load external resources.
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

    private function getAdminSetting(string $key, string $default = ''): string
    {
        try {
            $row = \Core\Database::getInstance()->fetch("SELECT value FROM settings WHERE `key` = ?", [$key]);
            return $row ? (string) $row['value'] : $default;
        } catch (\Exception $e) {
            return $default;
        }
    }

    private function userHasPro(int $userId): bool
    {
        try {
            $db  = \Core\Database::getInstance();
            $sub = $db->fetch(
                "SELECT pus.id FROM platform_user_subscriptions pus
                 JOIN platform_plans pp ON pp.id = pus.plan_id
                 WHERE pus.user_id = ? AND pus.status = 'active'
                 AND (pus.expires_at IS NULL OR pus.expires_at > NOW())
                 AND JSON_CONTAINS(pp.included_apps, '\"resumex\"')",
                [$userId]
            );
            return $sub !== null && $sub !== false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Show import resume form
     */
    public function importForm(): void
    {
        View::render('projects/resumex/import', [
            'title'     => 'Import Resume',
            'user'      => Auth::user(),
            'allThemes' => $this->resumeModel->getAllThemePresets(),
            'csrfToken' => Security::generateCsrfToken(),
        ]);
    }

    /**
     * Process uploaded resume file, parse it, then redirect to editor
     */
    public function storeImport(): void
    {
        if (!\Core\Security::validateCsrfToken($_POST['_token'] ?? '')) {
            header('Location: /projects/resumex/import?error=token');
            exit;
        }

        $userId = Auth::id();
        if (!$userId) {
            header('Location: /login');
            exit;
        }

        $template = trim($_POST['template'] ?? 'ocean-blue');
        $title    = trim($_POST['title'] ?? 'My Imported Resume');
        if (empty($title)) {
            $title = 'My Imported Resume';
        }

        $resumeData = null;

        if (!empty($_POST['resume_json'])) {
            $parsed = json_decode(trim($_POST['resume_json']), true);
            if (is_array($parsed)) {
                $resumeData = $parsed;
            }
        }

        if ($resumeData === null && isset($_FILES['resume_file']) && $_FILES['resume_file']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['resume_file']['name'], PATHINFO_EXTENSION));
            if ($ext === 'json') {
                $content = file_get_contents($_FILES['resume_file']['tmp_name']);
                $parsed  = json_decode($content, true);
                if (is_array($parsed)) {
                    $resumeData = $parsed;
                }
            }
        }

        if ($resumeData === null) {
            $resumeData = $this->resumeModel->getDefaultData();
        }

        $id = $this->resumeModel->create($userId, $title, $template, [], $resumeData);

        if ($id) {
            header("Location: /projects/resumex/edit/{$id}?imported=1");
        } else {
            header("Location: /projects/resumex/import?error=1");
        }
        exit;
    }

    /**
     * Generate or toggle a public share link for a resume
     */
    public function generateShareLink(): void
    {
        if (!\Core\Security::validateCsrfToken($_POST['_token'] ?? '')) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false]);
            exit;
        }

        $userId = Auth::id();
        $id     = (int) ($_POST['id'] ?? 0);

        $resume = $this->resumeModel->get($id, $userId);
        if (!$resume) {
            header('Content-Type: application/json');
            http_response_code(404);
            echo json_encode(['success' => false]);
            exit;
        }

        $sharingEnabled = $this->getAdminSetting('resumex_public_resumes', '1');
        if ($sharingEnabled !== '1') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Public sharing is disabled.']);
            exit;
        }

        $token = $resume['share_token'] ?? '';
        try {
            $db = \Core\Database::getInstance();
            if (empty($token)) {
                $token = bin2hex(random_bytes(32));
                $db->update(
                    'resumex_resumes',
                    ['share_token' => $token, 'is_public' => 1, 'updated_at' => date('Y-m-d H:i:s')],
                    'id = ? AND user_id = ?',
                    [$id, $userId]
                );
            } else {
                $newPublic = ($resume['is_public'] ?? 0) ? 0 : 1;
                $db->update(
                    'resumex_resumes',
                    ['is_public' => $newPublic, 'updated_at' => date('Y-m-d H:i:s')],
                    'id = ? AND user_id = ?',
                    [$id, $userId]
                );
            }
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'error' => 'Database error.']);
            exit;
        }

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'token' => $token, 'url' => '/projects/resumex/share/' . $token]);
        exit;
    }

    /**
     * Display a publicly shared resume
     */
    public function publicView(string $token): void
    {
        if (empty($token)) {
            http_response_code(404);
            echo '<h1>Not Found</h1>';
            exit;
        }

        try {
            $db     = \Core\Database::getInstance();
            $resume = $db->fetch(
                "SELECT * FROM resumex_resumes WHERE share_token = ? AND is_public = 1",
                [$token]
            );
        } catch (\Exception $e) {
            $resume = null;
        }

        if (!$resume) {
            http_response_code(404);
            View::render('projects/resumex/resume_notfound', [
                'title'     => 'Resume Not Found',
                'user'      => Auth::user(),
                'id'        => 0,
                'allThemes' => $this->resumeModel->getAllThemePresets(),
            ]);
            return;
        }

        $resumeData    = json_decode($resume['resume_data']    ?? '{}', true) ?: $this->resumeModel->getDefaultData();
        $themeSettings = json_decode($resume['theme_settings'] ?? '{}', true) ?: $this->resumeModel->getThemePreset($resume['template']);

        View::render('projects/resumex/preview', [
            'title'         => htmlspecialchars($resume['title']) . ' - Public Resume',
            'user'          => Auth::user(),
            'resume'        => $resume,
            'resumeData'    => $resumeData,
            'themeSettings' => $themeSettings,
            'isPublic'      => true,
        ]);
    }
}
