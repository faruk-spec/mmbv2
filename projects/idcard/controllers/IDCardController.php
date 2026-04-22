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
use Core\SecureUpload;
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
                          'v_stripe', 'v_badge', 'ai_generated'];
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

        // Accept AI image path (DALL-E generated) or legacy AI HTML card
        $aiCardHtml = null;
        if (!empty($_POST['ai_image_path'])) {
            // Validate path is within the expected storage location before storing
            $rawPath = preg_replace('/[^\w\/.+-]/', '', (string)$_POST['ai_image_path']);
            if (preg_match('/^storage\/idcard\/ai_cards\/[a-f0-9]{32}\.png$/', $rawPath)) {
                $aiCardHtml = '__AI_IMG__:' . $rawPath;
                $design['design_style'] = 'ai_generated';
            }
        } elseif (!empty($_POST['ai_card_html'])) {
            $aiCardHtml = $this->sanitizeCardHtml((string) $_POST['ai_card_html']);
            // Store 'ai_generated' as design_style so view.php knows to use the AI HTML
            $design['design_style'] = 'ai_generated';
        }

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
                'ai_card_html'   => $aiCardHtml,
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
            'ai_card_html'   => $aiCardHtml,
            'status'         => 'generated',
        ]);

        Logger::activity($userId, 'idcard_generated', ['card_id' => $cardId, 'template' => $templateKey]);
        try { \Core\Notification::send($userId, 'idcard_created', 'ID Card generated in IDCard (template: ' . $templateKey . ').', ['project' => 'idcard', 'card_id' => $cardId]); } catch (\Exception $e) {}

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
            try { \Core\Notification::send($userId, 'idcard_deleted', 'ID Card #' . $id . ' deleted.', ['project' => 'idcard', 'card_id' => $id]); } catch (\Exception $e) {}
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
        // Check if admin has disabled the AI Generate page
        $adminConfig = $this->model->getSetting('admin_config', []);
        if (is_array($adminConfig) && array_key_exists('ai_generate_page_enabled', $adminConfig) && !$adminConfig['ai_generate_page_enabled']) {
            header('Location: /projects/idcard/generate');
            exit;
        }

        $template = $_GET['template'] ?? 'corporate';
        $templates = $this->config['templates'];
        if (!isset($templates[$template])) {
            $template = 'corporate';
        }

        // Check if AI is properly configured so the view can show a warning if not
        $aiEnabled    = $this->model->getSetting('idcard_ai_enabled', '1');
        $dbKey        = $this->model->getSetting('idcard_openai_api_key', '');
        $aiKeySet     = !empty($dbKey) || (defined('OPENAI_API_KEY') && !empty(OPENAI_API_KEY));
        $aiConfigured = ($aiEnabled === '1') && $aiKeySet;

        $this->render('ai_generate', [
            'title'        => 'Generate with AI',
            'user'         => Auth::user(),
            'templates'    => $templates,
            'selectedTpl'  => $template,
            'tplConfig'    => $templates[$template],
            'field_labels' => $this->config['field_labels'],
            'aiConfigured' => $aiConfigured,
            'aiEnabled'    => $aiEnabled === '1',
            'aiKeySet'     => $aiKeySet,
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
        $generateHtml = !empty($_POST['generate_html']);

        // Sanitise each card_data field
        $sanitised = [];
        foreach ($cardData as $k => $v) {
            $sanitised[$this->sanitize($k)] = $this->sanitize($v);
        }

        $suggestions = $this->generateAISuggestions($templateKey, $sanitised, $prompt);

        // When the AI Generate page requests an AI image card, generate it via DALL-E
        if ($generateHtml) {
            $templates   = $this->config['templates'];
            $tplConfig   = $templates[$templateKey] ?? $templates['corporate'];
            $aiImagePath = $this->callOpenAIImage($templateKey, $sanitised, $prompt, $tplConfig);
            if ($aiImagePath !== null) {
                $suggestions['ai_image_path'] = $aiImagePath;
                $suggestions['ai_powered']    = true;
            }
        }

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
        $fallbackReason   = '';
        $openAISuggestion = $this->callOpenAI($tpl, $data, $prompt, $tplConfig, $fallbackReason);
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
        $suggestions['ai_powered']       = false;
        $suggestions['fallback_reason']  = $fallbackReason;
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
     * Sets $fallbackReason to a human-readable explanation when returning null.
     */
    private function callOpenAI(string $tpl, array $data, string $prompt, array $tplConfig, string &$fallbackReason = ''): ?array
    {
        // Load settings via model (consistent, correct typing)
        $aiEnabled   = $this->model->getSetting('idcard_ai_enabled', '1');
        $dbApiKey    = $this->model->getSetting('idcard_openai_api_key', '');
        $model       = $this->model->getSetting('idcard_openai_model', 'gpt-4o-mini');
        $dailyLimit  = (int) $this->model->getSetting('idcard_ai_daily_limit', '0');

        // Check if AI is enabled
        if ($aiEnabled !== '1') {
            $fallbackReason = 'ai_disabled';
            Logger::warning('CardX AI: disabled via admin settings (idcard_ai_enabled=' . $aiEnabled . ')');
            return null;
        }

        // Per-user daily rate limit
        if ($dailyLimit > 0 && !$this->checkAndIncrementAIRateLimit($dailyLimit)) {
            $fallbackReason = 'rate_limited';
            Logger::warning('CardX AI: daily rate limit reached for user ' . \Core\Auth::id());
            return null;
        }

        // Resolve API key: DB setting → env constant
        $apiKey = !empty($dbApiKey) ? $dbApiKey : (defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '');
        if (empty($apiKey)) {
            $fallbackReason = 'no_api_key';
            Logger::warning('CardX AI: no API key configured (set idcard_openai_api_key in admin or define OPENAI_API_KEY)');
            return null;
        }

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
            $fallbackReason = 'api_error';
            $body = is_string($raw) ? substr($raw, 0, 300) : '';
            Logger::error('CardX OpenAI API error: HTTP ' . $httpCode . ($curlErr ? ' curl: ' . $curlErr : '') . ($body ? ' body: ' . $body : ''));
            return null;
        }

        $response = json_decode($raw, true);
        $text     = $response['choices'][0]['message']['content'] ?? '';

        if (empty($text)) {
            $fallbackReason = 'api_error';
            Logger::error('CardX OpenAI: empty content in response');
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

        $fallbackReason = 'api_error';
        Logger::error('CardX OpenAI: failed to parse response JSON — ' . substr($text, 0, 200));
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

    /**
     * Call OpenAI to generate a complete, self-contained HTML card design.
     * Returns sanitized HTML string or null on failure.
     */
    private function callOpenAICardHTML(string $tpl, array $data, string $prompt, array $tplConfig): ?string
    {
        $aiEnabled  = $this->model->getSetting('idcard_ai_enabled', '1');
        $dbApiKey   = $this->model->getSetting('idcard_openai_api_key', '');

        if ($aiEnabled !== '1') {
            return null;
        }

        $apiKey = !empty($dbApiKey) ? $dbApiKey : (defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '');
        if (empty($apiKey)) {
            return null;
        }

        // Use the admin-configured model; prefer gpt-4o for HTML generation quality
        // but fall back to whatever is configured if it's already a capable model
        $model = $this->model->getSetting('idcard_openai_model', 'gpt-4o');
        // Note: HTML card generation needs a model that handles complex code well.
        // If admin set gpt-4o-mini or gpt-3.5-turbo, silently upgrade to gpt-4o
        // so the AI card design quality is sufficient.
        if (in_array($model, ['gpt-4o-mini', 'gpt-3.5-turbo'], true)) {
            $model = 'gpt-4o';
        }

        $isPortrait  = ($tplConfig['orientation'] ?? 'landscape') === 'portrait';
        $orientation = $isPortrait ? 'PORTRAIT' : 'LANDSCAPE';
        $cardW       = $isPortrait ? '54mm' : '85.6mm';
        $cardH       = $isPortrait ? '85.6mm' : '54mm';

        // Build per-field name→value pairs with clean labels
        $labelMap  = $this->config['field_labels'] ?? [];
        $fieldPairs = [];  // [ ['label'=>..., 'key'=>..., 'value'=>...], ... ]
        foreach ($data as $k => $v) {
            if ($v !== '' && $v !== null) {
                $label = $labelMap[$k] ?? ucwords(str_replace('_', ' ', $k));
                $fieldPairs[] = [
                    'label' => $label,
                    'key'   => $k,
                    'value' => mb_substr(strip_tags((string)$v), 0, 100),
                ];
            }
        }

        // Identify the most important fields for card layout
        $nameVal = $data['name'] ?? '';
        $orgVal  = $data['company_name'] ?? $data['school_name'] ?? $data['organization'] ?? ($tplConfig['name'] ?? '');
        $roleVal = $data['designation'] ?? $data['title'] ?? $data['course'] ?? $data['event_name'] ?? '';

        // Remaining fields (not name/org/role) — these must also appear on the card
        $extraFields = [];
        $skipKeys = ['name', 'company_name', 'school_name', 'organization',
                     'designation', 'title', 'course', 'event_name'];
        foreach ($fieldPairs as $fp) {
            if (!in_array($fp['key'], $skipKeys, true)) {
                $extraFields[] = $fp['label'] . ': ' . $fp['value'];
            }
        }

        // Build the complete field listing string (what the AI must print on the card)
        $allFieldsStr = '';
        foreach ($fieldPairs as $fp) {
            $allFieldsStr .= '  • ' . $fp['label'] . ' = "' . $fp['value'] . '"' . "\n";
        }

        $extraFieldsBlock = implode("\n", array_map(fn($f) => "  • $f", $extraFields));

        $pri = $tplConfig['color']  ?? '#1e40af';
        $acc = $tplConfig['accent'] ?? '#3b82f6';

        $safePrompt   = mb_substr(strip_tags($prompt), 0, 300);
        $designHint   = $safePrompt ?: 'professional modern corporate design';

        // Orientation-specific structural layout guidance
        if ($isPortrait) {
            $layoutGuide = <<<LAYOUT
PORTRAIT LAYOUT STRUCTURE (follow this layer stack from top to bottom):
Layer 1 — HEADER BAND (top 38% of card, full width):
  • Fill with a rich gradient using the primary colour (e.g. linear-gradient from primary to a darker shade or accent).
  • Add an SVG diagonal/angular cut at the bottom edge of this band (skew the band bottom edge by 8–12 degrees using a polygon or path).
  • Place the organisation name "{$orgVal}" in bold white text inside this band (top area, font ≈ 0.75rem).
  • Add a small rectangular "logo placeholder" div (white, 28×16px, top-left corner, border-radius:3px) to represent a logo.
  • Add decorative SVG shapes inside this band: e.g. semi-transparent circles, angular polygons, or wave path in accent colour.

Layer 2 — PHOTO ZONE (centred, overlapping header/body boundary):
  • Large circular photo frame: width ≈ 27% of card width, aspect-ratio 1/1, border-radius 50%.
  • Give it a 3–4px solid white border and a drop shadow (box-shadow:0 4px 14px rgba(0,0,0,0.35)).
  • Position it so roughly 50% sits in the header and 50% in the body (use position:absolute, top ≈ 28%, left:50%, transform:translateX(-50%)).
  • Inside the circle use <!--CX_PHOTO_SLOT-->.

Layer 3 — BODY (remaining 62% below header, white or very light neutral background):
  • Add top padding equal to ~half the photo circle height to clear the photo.
  • Centre-align the person name in large bold dark text (1.0–1.15rem, font-weight:800, colour: dark version of primary).
  • Below name: job title/designation in medium weight, primary colour, smaller size (0.72rem).
  • Below title: a thin horizontal accent-colour divider line (width:55%, height:2px, margin:auto).
  • Below divider: left-aligned field rows (padding-left:8%), each row as:
      <div style="display:flex;gap:4px;margin:1.5px 0;">
        <span style="font-weight:700;color:{$pri};min-width:35%;font-size:0.58rem;">LABEL:</span>
        <span style="color:#444;font-size:0.58rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">VALUE</span>
      </div>
  • Fields to show in rows (in this order): Department, Employee ID, Date of Birth, Phone, Email, Address.

Layer 4 — FOOTER STRIP (bottom 7% of card, full width):
  • Solid accent colour background.
  • Optionally show a small barcode-like SVG pattern (thin vertical lines) or repeat org name in white.
LAYOUT;
        } else {
            $layoutGuide = <<<LAYOUT
LANDSCAPE LAYOUT STRUCTURE (follow this two-panel layout):
LEFT PANEL (leftmost 36% of card width, full height):
  • Rich gradient background using primary colour (vertical or diagonal gradient).
  • Add decorative SVG angular shapes or curves in accent colour inside this panel.
  • At the top: organisation name "{$orgVal}" in small bold white text (≈0.62rem), with a small rectangular "logo placeholder" div (white, 26×14px, border-radius:2px) above it.
  • Centre of left panel: Large circular photo frame, width ≈ 55% of panel width, aspect-ratio:1/1, border-radius:50%, border:3px solid white, box-shadow:0 4px 12px rgba(0,0,0,0.3).
    Inside: <!--CX_PHOTO_SLOT-->.
  • Below photo: person name in bold white, font-size ≈ 0.78rem, text-align:center, font-weight:800.
  • Below name: job title in accent/light colour, font-size ≈ 0.6rem, text-align:center.

RIGHT PANEL (remaining 64% of card width, full height):
  • White or very light (#f8f9fa) background.
  • Add a thin vertical accent-colour bar (width:4px) at the left edge of this panel.
  • At the top: a large bold heading of the person name again in primary colour (1.0rem, font-weight:800).
  • Below: job title in medium weight, darker accent, 0.7rem.
  • Thin horizontal divider (primary colour, 50% width, 2px height, margin:4px 0).
  • Below: left-aligned field rows, each:
      <div style="display:flex;gap:4px;margin:2px 0;padding-left:8px;">
        <span style="font-weight:700;color:{$pri};min-width:38%;font-size:0.58rem;">LABEL:</span>
        <span style="color:#555;font-size:0.58rem;overflow:hidden;white-space:nowrap;text-overflow:ellipsis;">VALUE</span>
      </div>
  • Show in rows: Department, Employee ID, Date of Birth, Phone, Email (in order).
  • At very bottom: a small accent-colour footer strip (height:12%, full width of right panel) with any remaining text or decorative barcode SVG.
LAYOUT;
        }

        // ── SYSTEM PROMPT ────────────────────────────────────────────────────────
        $systemPrompt = <<<SYSPROMPT
You are an expert HTML/CSS ID card designer. You produce professional ID cards that look like real-world printed employee/staff ID cards — NOT simple blue boxes with text. Your cards have distinct visual zones, decorative elements, and a polished layered structure like a premium printed card.

═══ OUTPUT FORMAT (NON-NEGOTIABLE) ═══
• Output ONLY the raw <div>…</div> element — no markdown code fences (no ```), no explanations, no text before or after the div.
• Root element: <div style="width:100%;height:100%;position:relative;overflow:hidden;font-family:'Segoe UI',Arial,sans-serif;">
• Use ONLY inline CSS (style="…" attributes). Zero <style> tags, zero class attributes, zero external references.
• Allowed tags: div, span, svg, path, polygon, polyline, rect, circle, ellipse, line, defs, linearGradient, radialGradient, stop, g, text, tspan.
• FORBIDDEN (stripped server-side): script, style, link, iframe, img, object, embed, form, input, foreignObject, on* event handlers, javascript:, expression().

═══ PHOTO PLACEHOLDER ═══
• Photo placeholder MUST be: <!--CX_PHOTO_SLOT-->
• Always wrap it in a <div> that defines the photo frame: size, border-radius, border, overflow:hidden, display:flex, align-items:center, justify-content:center.
• Example: <div style="width:27%;aspect-ratio:1/1;border-radius:50%;overflow:hidden;border:3px solid #fff;box-shadow:0 4px 14px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;background:rgba(255,255,255,0.15);"><!--CX_PHOTO_SLOT--></div>

═══ DATA RULES (HIGHEST PRIORITY) ═══
• EVERY field value listed in the user message MUST appear as readable printed text on the card — no exceptions.
• Use exact values — do not invent, abbreviate, or omit any field.
• Prominent: Full Name (largest text, bold), Organisation (header area), Job Title (subtitle below name).
• Standard fields: Department, Employee ID, Date of Birth, Phone, Email, Address — shown as labelled rows.
• Font sizes: name 0.9–1.1rem bold; title 0.65–0.75rem; field labels 0.55–0.62rem bold; field values 0.55–0.62rem.
• Every text span: overflow:hidden;white-space:nowrap;text-overflow:ellipsis — prevents text spill outside card.
• Use clamp() where possible: e.g. font-size:clamp(0.55rem,1.3vw,0.72rem).

═══ DESIGN STRUCTURE (MANDATORY) ═══
The card MUST have these visual zones — NOT a single flat background with all text on it:
1. A HEADER/ACCENT ZONE with rich gradient + decorative SVG elements (diagonal edge, geometric shapes, curves).
2. A PHOTO FRAME — large circular frame positioned prominently, good border and shadow.
3. A BODY ZONE — contrasting background (white or light) where name, title, and field rows appear.
4. A FOOTER STRIP — solid accent colour at the bottom edge.
The diagonal/angular cut between header and body is essential — use SVG polygon or CSS skewY transform on a positioned div.
Add at least two decorative SVG elements in the accent zone (circles, polygons, curved paths).
SYSPROMPT;

        // ── USER PROMPT ──────────────────────────────────────────────────────────
        $userPrompt = <<<USERPROMPT
Generate a {$orientation} professional ID card ({$cardW} × {$cardH}).

━━━ ALL CARD DATA (print EVERY item on the card — no exceptions) ━━━
{$allFieldsStr}
━━━ PRIMARY FIELDS (most prominent) ━━━
• Full Name → "{$nameVal}"   (largest text, bold, prominent)
• Organisation → "{$orgVal}"  (header zone)
• Job Title → "{$roleVal}"    (subtitle below name)
• Photo → <!--CX_PHOTO_SLOT--> in large circular frame

━━━ SECONDARY FIELDS (show as labelled rows) ━━━
{$extraFieldsBlock}

━━━ LAYOUT SPECIFICATION ━━━
{$layoutGuide}

━━━ COLOURS ━━━
Primary: {$pri}   Accent: {$acc}

━━━ STYLE ━━━
{$designHint}

━━━ FINAL CHECKLIST (verify before outputting) ━━━
☑ Root <div> uses width:100%;height:100%;position:relative;overflow:hidden
☑ Header/accent zone with gradient + angular/diagonal SVG cut edge
☑ At least 2 decorative SVG shapes in the accent zone
☑ Large circular photo frame with <!--CX_PHOTO_SLOT-->
☑ Person name in large bold text
☑ Job title as subtitle
☑ All secondary fields as labelled rows (label bold: value)
☑ Footer accent strip at bottom
☑ Output is raw <div>…</div> only — no fences, no explanation
USERPROMPT;

        $payload = json_encode([
            'model'       => $model,
            'messages'    => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user',   'content' => $userPrompt],
            ],
            'temperature' => 0.65,   // balanced: creative design with reliable data inclusion
            'max_tokens'  => 5000,   // enough for full rich layered card HTML
        ]);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 50,
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
            Logger::error('CardX AI card HTML: HTTP ' . $httpCode . ($curlErr ? ' — ' . $curlErr : ''));
            return null;
        }

        $response = json_decode($raw, true);
        $html     = trim($response['choices'][0]['message']['content'] ?? '');

        if (empty($html)) {
            return null;
        }

        // Strip markdown code fences that some models add despite instructions
        $html = preg_replace('/^```(?:html|xml|HTML)?\s*/i', '', $html);
        $html = preg_replace('/\s*```\s*$/i', '', $html);
        $html = trim($html);

        // Must start with a <div — basic sanity check
        if (!preg_match('/^\s*<div\b/i', $html)) {
            Logger::error('CardX AI card HTML: unexpected response (not a div): ' . mb_substr($html, 0, 100));
            return null;
        }

        return $this->sanitizeCardHtml($html);
    }

    /**
     * Call DALL-E 3 to generate a professional ID card IMAGE.
     * Downloads the image to local storage and returns the relative path,
     * or null on failure.
     */
    private function callOpenAIImage(string $tpl, array $data, string $prompt, array $tplConfig): ?string
    {
        $aiEnabled = $this->model->getSetting('idcard_ai_enabled', '1');
        $dbApiKey  = $this->model->getSetting('idcard_openai_api_key', '');

        if ($aiEnabled !== '1') {
            return null;
        }

        $apiKey = !empty($dbApiKey) ? $dbApiKey : (defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '');
        if (empty($apiKey)) {
            return null;
        }

        $isPortrait  = ($tplConfig['orientation'] ?? 'landscape') === 'portrait';
        $imgSize     = $isPortrait ? '1024x1792' : '1792x1024';
        $orientation = $isPortrait ? 'vertical portrait' : 'horizontal landscape';

        // Key field values
        $labelMap = $this->config['field_labels'] ?? [];
        $nameVal  = $data['name'] ?? '';
        $orgVal   = $data['company_name'] ?? $data['school_name'] ?? $data['organization'] ?? ($tplConfig['name'] ?? '');
        $roleVal  = $data['designation'] ?? $data['title'] ?? $data['course'] ?? $data['event_name'] ?? '';

        // Secondary fields (not the primary headline fields)
        $skipKeys   = ['name', 'company_name', 'school_name', 'organization',
                       'designation', 'title', 'course', 'event_name'];
        $fieldLines = [];
        foreach ($data as $k => $v) {
            if ($v !== '' && $v !== null && !in_array($k, $skipKeys, true)) {
                $label        = $labelMap[$k] ?? ucwords(str_replace('_', ' ', $k));
                $fieldLines[] = $label . ': ' . mb_substr(strip_tags((string)$v), 0, 60);
            }
        }

        $pri        = $tplConfig['color']  ?? '#1e40af';
        $acc        = $tplConfig['accent'] ?? '#3b82f6';
        $safePrompt = mb_substr(strip_tags($prompt), 0, 200);

        $fieldText = !empty($fieldLines)
            ? 'Include these details as legible text labels on the card: ' . implode(' | ', $fieldLines) . '. '
            : '';

        $dallePrompt =
            "Professional printed ID card design, {$orientation} orientation, high-quality flat graphic design, white background, card centered. "
            . "Card layout: (1) Bold colored header band at top in color {$pri}, diagonal angled bottom edge using CSS clip-path style, "
            . "header contains organization name '{$orgVal}' in white bold text and a small rectangular logo placeholder box top-left. "
            . "(2) Large circular photo frame with white border and drop-shadow, avatar silhouette inside, overlapping the header at center. "
            . "(3) White body section below with '{$nameVal}' as the largest bold dark text, "
            . ($roleVal ? "'{$roleVal}' as a subtitle line in a muted color, " : '')
            . "a thin horizontal divider line, then neatly formatted label-value field rows in small text. "
            . $fieldText
            . "(4) Solid {$acc} colored footer strip at bottom edge of card. "
            . "Thin barcode graphic near the bottom. "
            . "Clean modern corporate style, crisp typography, the card fills the frame, no outer border or extra white space, "
            . "professional print-quality ID card design"
            . ($safePrompt ? ", {$safePrompt}" : '') . '.';

        // Cap to DALL-E's prompt character limit
        $dallePrompt = mb_substr($dallePrompt, 0, 3800);

        $payload = json_encode([
            'model'           => 'dall-e-3',
            'prompt'          => $dallePrompt,
            'n'               => 1,
            'size'            => $imgSize,
            'quality'         => 'standard',
            'response_format' => 'b64_json',
        ]);

        $ch = curl_init('https://api.openai.com/v1/images/generations');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 90,
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
            Logger::error('CardX DALL-E image: HTTP ' . $httpCode . ($curlErr ? ' — ' . $curlErr : '') . ' body: ' . mb_substr((string)$raw, 0, 300));
            return null;
        }

        $response = json_decode($raw, true);
        $b64      = $response['data'][0]['b64_json'] ?? '';

        if (empty($b64)) {
            Logger::error('CardX DALL-E image: empty b64_json in response');
            return null;
        }

        $imgData = base64_decode($b64, true);
        if ($imgData === false || strlen($imgData) < 1000) {
            Logger::error('CardX DALL-E image: base64 decode failed or image too small');
            return null;
        }

        $saveDir = BASE_PATH . '/storage/idcard/ai_cards/';
        if (!is_dir($saveDir)) {
            mkdir($saveDir, 0755, true);
        }

        $filename = bin2hex(random_bytes(16)) . '.png';
        $savePath = $saveDir . $filename;

        if (file_put_contents($savePath, $imgData) === false) {
            Logger::error('CardX DALL-E image: failed to write image to ' . $savePath);
            return null;
        }

        return 'storage/idcard/ai_cards/' . $filename;
    }

    /**
     * Sanitize AI-generated card HTML to remove any dangerous content.
     * Strips scripts, event handlers, forbidden tags, and javascript: URLs.
     * Uses a multi-pass regex approach suitable for SVG/HTML mixed content
     * (DOMDocument would mangle SVG elements).
     */
    private function sanitizeCardHtml(string $html): string
    {
        // Remove forbidden tags and their content entirely (multi-pass for nesting)
        $forbidden = ['script', 'style', 'link', 'iframe', 'object', 'embed', 'meta',
                      'base', 'form', 'input', 'button', 'select', 'textarea', 'foreignObject'];
        // Two passes to handle nested same-tag structures in malformed HTML
        for ($pass = 0; $pass < 2; $pass++) {
            foreach ($forbidden as $tag) {
                $html = preg_replace('/<' . $tag . '\b[^>]*>.*?<\/' . $tag . '>/is', '', $html);
                $html = preg_replace('/<' . $tag . '\b[^>]*\/?>/is', '', $html);
            }
        }

        // Remove all on* event handlers (onclick, onerror, onload, etc.)
        $html = preg_replace('/\s+on[a-z]+\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]*)/i', '', $html);

        // Remove javascript: and vbscript: protocols
        $html = preg_replace('/\b(javascript|vbscript)\s*:/i', 'blocked:', $html);

        // Remove CSS expression()
        $html = preg_replace('/\bexpression\s*\(/i', 'blocked(', $html);

        // Remove img tags (no external image loading)
        $html = preg_replace('/<img\b[^>]*\/?>/is', '', $html);

        // Remove any data: URLs (could be used for XSS in certain browsers)
        $html = preg_replace('/\bdata\s*:\s*(?!image\/(?:png|jpeg|gif|webp|svg\+xml))[^;\'")>\s]+/i', 'blocked:', $html);

        // Cap at 80KB — generous but prevents runaway responses
        if (strlen($html) > 80000) {
            $html = mb_substr($html, 0, 80000) . '</div>';
        }

        return $html;
    }

    private function handleUpload(array $file, string $subdir): ?string    {
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

        $uploadDir = BASE_PATH . '/storage/idcard/' . $subdir . '/';

        $result = SecureUpload::process($file, [
            'destination_dir'    => $uploadDir,
            'allowed_extensions' => $allowedExts,
            'allowed_mime_types' => $allowedMimes,
            'max_size'           => $maxBytes,
            'source'             => 'idcard.' . $subdir . '_upload',
            'user_id'            => Auth::id(),
        ]);

        if (empty($result['success'])) {
            return null;
        }

        return 'storage/idcard/' . $subdir . '/' . $result['filename'];
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
