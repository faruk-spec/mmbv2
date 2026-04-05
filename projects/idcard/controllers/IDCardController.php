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

        $editCardId = (int)($_POST['edit_card_id'] ?? 0);

        if ($editCardId > 0) {
            // ── UPDATE existing card ──────────────────────────────────────
            $existing = $this->model->findById($editCardId, $userId);
            if (!$existing) {
                $this->jsonError('Card not found or access denied.');
                return;
            }
            // Preserve existing photo/logo when user updates card without uploading new files
            if ($photoPath === null) {
                $photoPath = $existing['photo_path'] ?? null;
            }
            if ($logoPath === null) {
                $logoPath = $existing['logo_path'] ?? null;
            }
            $this->model->update($editCardId, $userId, [
                'template_key'   => $templateKey,
                'card_data'      => $cardData,
                'design'         => $design,
                'photo_path'     => $photoPath,
                'logo_path'      => $logoPath,
                'ai_prompt'      => $aiPrompt,
                'ai_suggestions' => $aiSuggestions,
            ]);
            Logger::activity($userId, 'idcard_updated', ['card_id' => $editCardId, 'template' => $templateKey]);
            if ($this->isAjax()) {
                echo json_encode(['success' => true, 'card_id' => $editCardId, 'redirect' => '/projects/idcard/view/' . $editCardId]);
                exit;
            }
            header('Location: /projects/idcard/view/' . $editCardId);
            exit;
        }

        // ── CREATE new card ───────────────────────────────────────────────
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
    //  Edit (GET — pre-filled form)                                       //
    // ------------------------------------------------------------------ //

    public function edit(int $id): void
    {
        $userId = Auth::id();
        $card   = $this->model->findById($id, $userId);

        if (!$card) {
            http_response_code(404);
            echo "ID card not found.";
            return;
        }

        $templates   = $this->config['templates'];
        $templateKey = $card['template_key'];
        if (!isset($templates[$templateKey])) {
            $templateKey = 'corporate';
        }

        $this->render('generate', [
            'title'        => 'Edit ID Card',
            'user'         => Auth::user(),
            'templates'    => $templates,
            'selectedTpl'  => $templateKey,
            'tplConfig'    => $templates[$templateKey],
            'field_labels' => $this->config['field_labels'],
            'editCardId'   => $id,
            'editCardData' => $card['card_data'] ?? [],
            'editDesign'   => $card['design'] ?? [],
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

        // Redirect to view page with auto-download trigger
        $format = $this->sanitize($_GET['format'] ?? 'jpg');
        if (!in_array($format, ['jpg', 'pdf'], true)) {
            $format = 'jpg';
        }
        header('Location: /projects/idcard/view/' . $id . '?dl=' . $format);
        exit;
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
    //  AI Generate page                                                    //
    // ------------------------------------------------------------------ //

    public function showAIGenerate(): void
    {
        $template = $_GET['template'] ?? 'corporate';
        $templates = $this->config['templates'];
        if (!isset($templates[$template])) {
            $template = 'corporate';
        }

        $this->render('ai_generate', [
            'title'        => 'Generate with AI',
            'user'         => Auth::user(),
            'templates'    => $templates,
            'selectedTpl'  => $template,
            'tplConfig'    => $templates[$template],
            'field_labels' => $this->config['field_labels'],
        ]);
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
     * Generate AI design suggestions.
     * Tries OpenAI first (when configured), then Hugging Face, then falls back to rule-based.
     */
    private function generateAISuggestions(string $tpl, array $data, string $prompt): array
    {
        $suggestions = [];
        $templates   = $this->config['templates'];
        $tplConfig   = $templates[$tpl] ?? $templates['corporate'];

        // --- Try OpenAI for richer, context-aware suggestions ---
        $openAISuggestion = $this->callOpenAI($tpl, $data, $prompt, $tplConfig);
        if ($openAISuggestion !== null) {
            $suggestions['ai_powered']        = true;
            $suggestions['design_tips']        = $openAISuggestion['design_tips']        ?? [];
            $suggestions['template_tip']       = $openAISuggestion['template_tip']       ?? '';
            $suggestions['ai_text']            = $openAISuggestion['ai_text']            ?? '';
            $suggestions['field_suggestions']  = $openAISuggestion['field_suggestions']  ?? [];
            $suggestions['color_suggestions']  = $openAISuggestion['color_suggestions']  ?? [];
            if ($prompt) {
                $suggestions['prompt_hint'] = $openAISuggestion['prompt_hint'] ?? '';
            }
            return $suggestions;
        }

        // --- Rule-based fallback ---
        $suggestions['ai_powered']  = false;
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
     * Call OpenAI API for ID card design suggestions.
     * Returns null if AI is disabled, not configured, or the call fails.
     */
    private function callOpenAI(string $tpl, array $data, string $prompt, array $tplConfig): ?array
    {
        // Load settings from DB
        $settingKeys = ['idcard_ai_enabled', 'idcard_openai_api_key', 'idcard_openai_model', 'idcard_ai_daily_limit'];
        $dbSettings  = [];
        foreach ($settingKeys as $k) {
            $row = \Core\Database::getInstance()->fetch(
                "SELECT setting_value FROM idcard_settings WHERE setting_key = ?", [$k]
            );
            $dbSettings[$k] = $row ? $row['setting_value'] : null;
        }

        // Check if AI is enabled
        if (($dbSettings['idcard_ai_enabled'] ?? '1') !== '1') {
            return null;
        }

        // Per-user daily rate limit
        $dailyLimit = (int)($dbSettings['idcard_ai_daily_limit'] ?? 0);
        if ($dailyLimit > 0 && !$this->checkAndIncrementAIRateLimit($dailyLimit)) {
            return null;
        }

        // Resolve API key: DB setting → env constant
        $apiKey = $dbSettings['idcard_openai_api_key'] ?? '';
        if (empty($apiKey)) {
            $apiKey = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';
        }
        if (empty($apiKey)) {
            return null;
        }

        $model = $dbSettings['idcard_openai_model'] ?? 'gpt-4o-mini';
        if (empty($model)) {
            $model = 'gpt-4o-mini';
        }

        // Sanitize inputs to prevent prompt injection
        // Strip control characters, cap length, and remove common instruction-override patterns
        $safeTpl    = $this->sanitizeInput($tpl,    50);
        $safePrompt = $this->sanitizeInput($prompt, 200);
        // Reject prompts that attempt to override system instructions
        $injectionPatterns = [
            '/ignore\s+(previous|all|prior)\s+instructions/i',
            '/you\s+are\s+now/i',
            '/act\s+as\s+(?:a\s+)?(?:DAN|jailbreak|unrestricted)/i',
            '/forget\s+(your\s+)?(previous|all|prior)/i',
            '/disregard\s+(previous|all|prior)/i',
        ];
        foreach ($injectionPatterns as $pattern) {
            if (preg_match($pattern, $safePrompt)) {
                $safePrompt = '';
                break;
            }
        }

        $fieldSummary = [];
        foreach ($data as $k => $v) {
            if (!empty($v)) {
                $cleanKey = $this->sanitizeInput($k, 30);
                $cleanVal = $this->sanitizeInput($v, 60);
                $fieldSummary[] = "{$cleanKey}: {$cleanVal}";
            }
        }
        $fieldStr = implode(', ', array_slice($fieldSummary, 0, 10));

        // Build the list of form fields the template uses (exclude 'photo')
        $templateFields = array_values(array_filter($tplConfig['fields'] ?? [], fn($f) => $f !== 'photo'));
        $fieldsJson = json_encode($templateFields);

        $systemPrompt = 'You are an expert ID card designer and content generator. '
            . 'Always respond with a valid JSON object only — no markdown, no extra text. '
            . 'The JSON must have exactly these keys: '
            . '"design_tips" (array of 3 practical design tips as strings), '
            . '"template_tip" (string, one specific tip for this template type), '
            . '"ai_text" (string, a 2-3 sentence design recommendation based on the card data and user request), '
            . '"prompt_hint" (string, direct response to the user\'s design request, or empty string if no request), '
            . '"field_suggestions" (object: for each field in the provided fields list, suggest a realistic sample value appropriate for the template and user request — only include fields that can be meaningfully filled from the prompt/context; leave out fields you cannot determine), '
            . '"color_suggestions" (object with keys "primary_color" and "accent_color" as hex values that suit the template type and user request).';

        $userPrompt = "Template type: {$safeTpl}. ";
        $userPrompt .= "Template fields: {$fieldsJson}. ";
        if ($fieldStr) {
            $userPrompt .= "Existing card data: {$fieldStr}. ";
        }
        if ($safePrompt) {
            $userPrompt .= "User request: {$safePrompt}. ";
        }
        $userPrompt .= "Current colours — Primary: {$tplConfig['color']}, Accent: {$tplConfig['accent']}, Background: {$tplConfig['bg']}.";

        $payload = json_encode([
            'model'           => $model,
            'messages'        => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userPrompt],
            ],
            'temperature'     => 0.7,
            'max_tokens'      => 700,
            'response_format' => ['type' => 'json_object'],
        ]);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $apiKey,
                'Content-Type: application/json',
            ],
        ]);

        $raw      = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlErr  = curl_error($ch);
        curl_close($ch);

        if ($raw === false || !empty($curlErr) || $httpCode !== 200) {
            Logger::error('CardX OpenAI API error: HTTP ' . $httpCode . ($curlErr ? ': ' . $curlErr : ''));
            return null;
        }

        $response = json_decode($raw, true);
        $text     = $response['choices'][0]['message']['content'] ?? '';

        if (empty($text)) {
            return null;
        }

        // Extract and validate JSON from response
        if (preg_match('/\{.*\}/s', $text, $m)) {
            $parsed = json_decode($m[0], true);
            if (
                is_array($parsed)
                && isset($parsed['design_tips'], $parsed['template_tip'], $parsed['ai_text'])
                && is_array($parsed['design_tips'])
            ) {
                // Sanitize field_suggestions: only allow known template fields, string values ≤ 120 chars
                if (!empty($parsed['field_suggestions']) && is_array($parsed['field_suggestions'])) {
                    $clean = [];
                    foreach ($parsed['field_suggestions'] as $fk => $fv) {
                        if (in_array($fk, $templateFields, true) && is_string($fv)) {
                            $clean[$fk] = mb_substr(strip_tags($fv), 0, 120);
                        }
                    }
                    $parsed['field_suggestions'] = $clean;
                } else {
                    $parsed['field_suggestions'] = [];
                }

                // Sanitize color_suggestions: validate hex values
                $cs = $parsed['color_suggestions'] ?? [];
                $parsed['color_suggestions'] = [
                    'primary_color' => $this->sanitizeColor($cs['primary_color'] ?? ''),
                    'accent_color'  => $this->sanitizeColor($cs['accent_color']  ?? ''),
                ];

                return $parsed;
            }
        }

        return null;
    }

    /**
     * Check and increment the per-user daily AI rate limit.
     * Uses a temporary counter stored in idcard_settings with a date suffix.
     */
    private function checkAndIncrementAIRateLimit(int $limit): bool
    {
        if ($limit <= 0) {
            return true;
        }

        $userId  = \Core\Auth::id();
        $today   = date('Y-m-d');
        $key     = "ai_rate_{$userId}_{$today}";

        try {
            $db  = \Core\Database::getInstance();
            $row = $db->fetch("SELECT setting_value FROM idcard_settings WHERE setting_key = ?", [$key]);
            $count = (int)($row['setting_value'] ?? 0);

            if ($count >= $limit) {
                return false;
            }

            $db->query(
                "INSERT INTO idcard_settings (setting_key, setting_value)
                 VALUES (?, ?)
                 ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()",
                [$key, (string)($count + 1)]
            );
        } catch (\Exception $e) {
            // Allow request on DB error
        }

        return true;
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

    /**
     * Strip ASCII control characters and cap the string length.
     */
    private function sanitizeInput(string $value, int $maxLen): string
    {
        return mb_substr(preg_replace('/[\x00-\x1F\x7F]/u', '', $value), 0, $maxLen);
    }
    private function sanitizeColor(string $color): string
    {
        $color = trim($color);
        if (preg_match('/^#([0-9A-Fa-f]{3}|[0-9A-Fa-f]{4}|[0-9A-Fa-f]{6}|[0-9A-Fa-f]{8})$/', $color)) {
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
