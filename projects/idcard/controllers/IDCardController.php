<?php
/**
 * CardX – ID Card Controller
 * Handles generate / view / download / delete / history actions.
 *
 * @package MMB\Projects\IDCard\Controllers
 */

namespace Projects\IDCard\Controllers;

use Core\Auth;
use Core\Security;
use Core\Logger;
use Projects\IDCard\Models\IDCardModel;

class IDCardController
{
    private IDCardModel $model;
    /** @var array */
    private array $config;

    public function __construct()
    {
        $this->model  = new IDCardModel();
        $this->config = require PROJECT_PATH . '/config.php';
    }

    // ------------------------------------------------------------------ //
    //  Generator form                                                      //
    // ------------------------------------------------------------------ //

    public function showForm(): void
    {
        $template = $_GET['template'] ?? 'corporate';
        $templates = $this->config['templates'];
        if (!isset($templates[$template])) {
            $template = 'corporate';
        }

        $this->render('generate', [
            'title'        => 'Generate ID Card',
            'user'         => Auth::user(),
            'templates'    => $templates,
            'selectedTpl'  => $template,
            'tplConfig'    => $templates[$template],
            'field_labels' => $this->config['field_labels'],
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Generate (POST)                                                     //
    // ------------------------------------------------------------------ //

    public function generate(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->jsonError('Invalid security token.');
            return;
        }

        $userId      = Auth::id();
        $templateKey = $this->sanitize($_POST['template_key'] ?? 'corporate');
        $templates   = $this->config['templates'];

        if (!isset($templates[$templateKey])) {
            $templateKey = 'corporate';
        }

        $tplConfig = $templates[$templateKey];
        $cardData  = [];
        foreach ($tplConfig['fields'] as $field) {
            if ($field === 'photo') {
                continue;
            }
            $cardData[$field] = $this->sanitize($_POST[$field] ?? '');
        }

        // Design overrides (colours, fonts, layout style)
        $allowedStyles = ['classic', 'sidebar', 'wave', 'bold_header', 'diagonal',
                          'gradient_pro', 'neon', 'executive', 'stripe', 'metro',
                          'glass', 'zigzag', 'ribbon',
                          'v_sharp', 'v_curve', 'v_hex', 'v_circle', 'v_split',
                          'v_ribbon', 'v_arch', 'v_diamond', 'v_corner', 'v_dual',
                          'v_stripe', 'v_badge'];
        $rawStyle = $this->sanitize($_POST['design_style'] ?? 'classic');
        $isPortrait = ($tplConfig['orientation'] ?? 'landscape') === 'portrait';
        $defaultStyle = $isPortrait ? 'v_sharp' : 'classic';
        $designStyle = in_array($rawStyle, $allowedStyles, true) ? $rawStyle : $defaultStyle;

        $design = [
            'primary_color'  => $this->sanitizeColor($_POST['primary_color']  ?? $tplConfig['color']),
            'accent_color'   => $this->sanitizeColor($_POST['accent_color']   ?? $tplConfig['accent']),
            'bg_color'       => $this->sanitizeColor($_POST['bg_color']       ?? $tplConfig['bg']),
            'text_color'     => $this->sanitizeColor($_POST['text_color']     ?? $tplConfig['text']),
            'font_family'    => $this->sanitizeFont($_POST['font_family']     ?? 'Poppins'),
            'design_style'   => $designStyle,
            'show_qr'        => !empty($_POST['show_qr']),
            'qr_size'        => max(36, min(90, (int)($_POST['qr_size'] ?? 54))),
            'card_width'     => 'standard',
            'profile_shape'  => in_array($this->sanitize($_POST['profile_shape'] ?? 'circle'), ['circle','oval','square'], true) ? $this->sanitize($_POST['profile_shape'] ?? 'circle') : 'circle',
        ];

        // Handle photo upload
        $photoPath = null;
        if (!empty($_FILES['photo']['tmp_name'])) {
            $photoPath = $this->handleUpload($_FILES['photo'], 'photos');
        }

        // Handle logo upload
        $logoPath = null;
        if (!empty($_FILES['logo']['tmp_name'])) {
            $logoPath = $this->handleUpload($_FILES['logo'], 'logos');
        }

        // AI suggestions (rule-based)
        $aiPrompt       = $this->sanitize($_POST['ai_prompt'] ?? '');
        $aiSuggestions  = $this->generateAISuggestions($templateKey, $cardData, $aiPrompt);

        $cardId = $this->model->create([
            'user_id'        => $userId,
            'template_key'   => $templateKey,
            'card_data'      => $cardData,
            'design'         => $design,
            'photo_path'     => $photoPath,
            'logo_path'      => $logoPath,
            'ai_prompt'      => $aiPrompt,
            'ai_suggestions' => $aiSuggestions,
            'status'         => 'generated',
        ]);

        Logger::activity($userId, 'idcard_generated', ['card_id' => $cardId, 'template' => $templateKey]);

        if ($this->isAjax()) {
            echo json_encode(['success' => true, 'card_id' => $cardId, 'redirect' => '/projects/idcard/view/' . $cardId]);
            exit;
        }
        header('Location: /projects/idcard/view/' . $cardId);
        exit;
    }

    // ------------------------------------------------------------------ //
    //  View                                                                //
    // ------------------------------------------------------------------ //

    public function view(int $id): void
    {
        $userId = Auth::id();
        $card   = $this->model->findById($id, $userId);

        if (!$card) {
            http_response_code(404);
            echo "ID card not found.";
            return;
        }

        $templates = $this->config['templates'];
        $tplConfig = $templates[$card['template_key']] ?? $templates['corporate'];

        $this->render('view', [
            'title'        => 'View ID Card',
            'user'         => Auth::user(),
            'card'         => $card,
            'tplConfig'    => $tplConfig,
            'field_labels' => $this->config['field_labels'],
        ]);
    }

    // ------------------------------------------------------------------ //
    //  History                                                             //
    // ------------------------------------------------------------------ //

    public function history(): void
    {
        $userId  = Auth::id();
        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 12;
        $offset  = ($page - 1) * $perPage;

        $cards = $this->model->getByUser($userId, $perPage, $offset);
        $total = $this->model->countByUser($userId);
        $pages = max(1, (int) ceil($total / $perPage));

        $this->render('history', [
            'title'     => 'My ID Cards',
            'user'      => Auth::user(),
            'cards'     => $cards,
            'total'     => $total,
            'page'      => $page,
            'pages'     => $pages,
            'templates' => $this->config['templates'],
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Download (PNG via browser print / canvas)                          //
    // ------------------------------------------------------------------ //

    public function download(int $id): void
    {
        $userId = Auth::id();
        $card   = $this->model->findById($id, $userId);

        if (!$card) {
            http_response_code(404);
            echo "ID card not found.";
            return;
        }

        // Serve the card view in a print-optimised layout
        $templates = $this->config['templates'];
        $tplConfig = $templates[$card['template_key']] ?? $templates['corporate'];

        $this->render('view', [
            'title'        => 'Download ID Card',
            'user'         => Auth::user(),
            'card'         => $card,
            'tplConfig'    => $tplConfig,
            'field_labels' => $this->config['field_labels'],
            'printMode'    => true,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Delete                                                              //
    // ------------------------------------------------------------------ //

    public function delete(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->jsonError('Invalid security token.');
            return;
        }

        $userId = Auth::id();
        $id     = (int) ($_POST['id'] ?? 0);

        if ($id && $this->model->delete($id, $userId)) {
            Logger::activity($userId, 'idcard_deleted', ['card_id' => $id]);
            if ($this->isAjax()) {
                echo json_encode(['success' => true]);
                exit;
            }
            header('Location: /projects/idcard/history?deleted=1');
        } else {
            if ($this->isAjax()) {
                $this->jsonError('Could not delete card.');
                return;
            }
            header('Location: /projects/idcard/history?error=1');
        }
        exit;
    }

    // ------------------------------------------------------------------ //
    //  AI Suggestions (AJAX)                                              //
    // ------------------------------------------------------------------ //

    public function aiSuggest(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->jsonError('Invalid security token.');
            return;
        }

        $templateKey = $this->sanitize($_POST['template_key'] ?? 'corporate');
        $cardData    = $_POST['card_data'] ?? [];
        $prompt      = $this->sanitize($_POST['prompt'] ?? '');

        // Sanitise each card_data field
        $sanitised = [];
        foreach ($cardData as $k => $v) {
            $sanitised[$this->sanitize($k)] = $this->sanitize($v);
        }

        $suggestions = $this->generateAISuggestions($templateKey, $sanitised, $prompt);
        echo json_encode(['success' => true, 'suggestions' => $suggestions]);
        exit;
    }

    // ------------------------------------------------------------------ //
    //  Internal helpers                                                    //
    // ------------------------------------------------------------------ //

    /**
     * Rule-based AI suggestions (no external API required).
     * Falls back gracefully when HUGGING_FACE_API_TOKEN is not set.
     */
    private function generateAISuggestions(string $tpl, array $data, string $prompt): array
    {
        $suggestions = [];
        $templates   = $this->config['templates'];
        $tplConfig   = $templates[$tpl] ?? $templates['corporate'];

        // --- Design recommendations ---
        $suggestions['design_tips'] = [
            "Use high contrast between background ({$tplConfig['bg']}) and text ({$tplConfig['text']}) for readability.",
            "Keep the primary colour ({$tplConfig['color']}) consistent with your organisation branding.",
            "Ensure the photo is square-cropped and at least 300×300 px for best print quality.",
        ];

        // --- Content recommendations ---
        $missing = [];
        foreach ($tplConfig['fields'] as $field) {
            if ($field !== 'photo' && empty($data[$field])) {
                $missing[] = $this->config['field_labels'][$field] ?? $field;
            }
        }
        if ($missing) {
            $suggestions['missing_fields'] = 'Consider filling in: ' . implode(', ', $missing) . '.';
        }

        // --- Template-specific tips ---
        $tips = [
            'corporate' => 'Corporate ID cards look best with a company logo and a clear employee photo.',
            'student'   => 'Include the academic year and course code for easy identification.',
            'event'     => 'Add a QR code linking to the event schedule for a professional touch.',
            'visitor'   => 'Always include the visit date and host name for security purposes.',
            'medical'   => 'Display the blood group prominently — it can be critical in emergencies.',
            'minimal'   => 'Minimalist dark cards photograph well under any lighting conditions.',
        ];
        $suggestions['template_tip'] = $tips[$tpl] ?? 'Choose the template that best matches your organisation style.';

        // --- Prompt-driven hint ---
        if ($prompt) {
            $suggestions['prompt_hint'] = 'Based on your request "' . $prompt . '": consider adjusting the accent colour or adding a QR code for a more modern look.';
        }

        // --- Try Hugging Face API for richer text (optional) ---
        $apiToken = defined('HUGGING_FACE_API_TOKEN') ? HUGGING_FACE_API_TOKEN : '';
        if ($apiToken && $prompt) {
            try {
                $hfSuggestion = $this->callHuggingFace($prompt, $tpl, $data);
                if ($hfSuggestion) {
                    $suggestions['ai_text'] = $hfSuggestion;
                }
            } catch (\Exception $e) {
                // silently ignore; rule-based suggestions are still returned
            }
        }

        return $suggestions;
    }

    /**
     * Call Hugging Face Inference API for richer AI suggestions.
     */
    private function callHuggingFace(string $prompt, string $template, array $data): ?string
    {
        if (!defined('HUGGING_FACE_API_TOKEN') || !HUGGING_FACE_API_TOKEN) {
            return null;
        }
        $apiToken = HUGGING_FACE_API_TOKEN;
        $model    = 'mistralai/Mistral-7B-Instruct-v0.1';

        $systemPrompt = "You are a professional ID card design assistant. Provide brief, practical design tips (2–3 sentences max) for an ID card.";
        $userMessage  = "Template: {$template}. User request: {$prompt}. Give a short design suggestion.";

        $payload = json_encode([
            'inputs'     => "<s>[INST] {$systemPrompt}\n{$userMessage} [/INST]",
            'parameters' => ['max_new_tokens' => 120, 'temperature' => 0.6],
        ]);

        $ch = curl_init("https://api-inference.huggingface.co/models/{$model}");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiToken,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 8,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        if (!$response) {
            return null;
        }

        $decoded = json_decode($response, true);
        $text    = $decoded[0]['generated_text'] ?? null;

        if (!$text) {
            return null;
        }

        // Strip the prompt echo from Mistral's output
        $parts = explode('[/INST]', $text);
        $clean = trim(end($parts));
        return $clean ?: null;
    }

    private function handleUpload(array $file, string $subdir): ?string
    {
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $allowedExts  = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxBytes     = 5 * 1024 * 1024; // 5 MB

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }
        if ($file['size'] > $maxBytes) {
            return null;
        }

        // Validate actual file content (not just the reported MIME type)
        $imageInfo = @getimagesize($file['tmp_name']);
        if (!$imageInfo || !in_array($imageInfo['mime'], $allowedMimes, true)) {
            return null;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $ext = preg_replace('/[^a-z0-9]/', '', $ext);
        if (!in_array($ext, $allowedExts, true)) {
            // Use extension inferred from real MIME type
            $mimeToExt = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp'];
            $ext = $mimeToExt[$imageInfo['mime']] ?? 'jpg';
        }

        $uploadDir = BASE_PATH . '/storage/idcard/' . $subdir . '/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $destPath = $uploadDir . $filename;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            return 'storage/idcard/' . $subdir . '/' . $filename;
        }
        return null;
    }

    private function sanitize(mixed $value): string
    {
        return htmlspecialchars(strip_tags((string) $value), ENT_QUOTES, 'UTF-8');
    }

    private function sanitizeColor(string $color): string
    {
        $color = trim($color);
        if (preg_match('/^#[0-9A-Fa-f]{3,8}$/', $color)) {
            return $color;
        }
        return '#000000';
    }

    private function sanitizeFont(string $font): string
    {
        $allowed = ['Poppins', 'Inter', 'Roboto', 'Lato', 'Open Sans', 'Montserrat', 'Raleway'];
        return in_array($font, $allowed, true) ? $font : 'Poppins';
    }

    private function isAjax(): bool
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    private function jsonError(string $message): void
    {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }

    protected function render(string $view, array $data = []): void
    {
        extract($data);
        ob_start();
        include PROJECT_PATH . '/views/' . $view . '.php';
        $content = ob_get_clean();
        include PROJECT_PATH . '/views/layout.php';
    }
}
