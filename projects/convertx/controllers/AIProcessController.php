<?php
/**
 * AI Process Controller
 *
 * Accepts any file upload + free-form remarks, sends the file content
 * (or a base64-encoded image) to OpenAI, and streams the response back
 * as JSON.  No format conversion is performed — this is a pure
 * "chat with your file" endpoint.
 *
 * @package MMB\Projects\ConvertX\Controllers
 */

namespace Projects\ConvertX\Controllers;

use Core\Auth;
use Core\Security;
use Core\Logger;
use Core\Database;

class AIProcessController
{
    /** Maximum text size to send to OpenAI (characters). */
    private const MAX_TEXT_CHARS = 60000;

    /** Image extensions that can be sent as vision input. */
    private const IMAGE_EXTS = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

    /** Extensions whose raw bytes can be read as text. */
    private const TEXT_EXTS = ['txt', 'md', 'csv', 'tsv', 'html', 'htm', 'xml', 'rst', 'json', 'yaml', 'yml'];

    private function getOpenAIProvider(): ?array
    {
        try {
            $db  = Database::getInstance();
            $row = $db->fetch(
                "SELECT * FROM convertx_ai_providers
                  WHERE slug = 'openai' AND is_active = 1 AND api_key != '' LIMIT 1"
            );
            return $row ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    // ------------------------------------------------------------------ //
    //  Show the AI Process page                                            //
    // ------------------------------------------------------------------ //

    public function show(): void
    {
        $provider   = $this->getOpenAIProvider();
        $configured = ($provider !== null);
        $this->render('ai-process', [
            'title'      => 'AI File Processing',
            'user'       => Auth::user(),
            'configured' => $configured,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Process: upload + remarks → OpenAI → JSON response                 //
    // ------------------------------------------------------------------ //

    public function process(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
        header('Content-Type: application/json');

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            echo json_encode(['success' => false, 'error' => 'Invalid request token']);
            return;
        }

        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required']);
            return;
        }

        $provider = $this->getOpenAIProvider();
        if (!$provider) {
            echo json_encode([
                'success' => false,
                'error'   => 'OpenAI is not configured. Ask an admin to add the OpenAI API key in ConvertX → Settings.',
            ]);
            return;
        }

        $remarks = trim($_POST['remarks'] ?? '');
        if ($remarks === '') {
            echo json_encode(['success' => false, 'error' => 'Please enter instructions / remarks.']);
            return;
        }

        // ── File upload (optional — user may send remarks-only) ──────────
        $filePath    = null;
        $ext         = '';
        $originalName = '';
        if (!empty($_FILES['file']) && (int) $_FILES['file']['error'] === UPLOAD_ERR_OK) {
            $originalName = basename($_FILES['file']['name']);
            $ext          = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            $userId   = Auth::id();
            $dir      = BASE_PATH . '/storage/uploads/convertx/' . $userId;
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
            $filePath = $dir . '/aiproc_' . bin2hex(random_bytes(8)) . '.' . $ext;
            if (!move_uploaded_file($_FILES['file']['tmp_name'], $filePath)) {
                echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file.']);
                return;
            }
        }

        // ── Build OpenAI message content ─────────────────────────────────
        @set_time_limit(120);

        $model     = $provider['model'] ?: 'gpt-4o';
        $apiKey    = $provider['api_key'];
        $baseUrl   = rtrim($provider['base_url'] ?: 'https://api.openai.com', '/');

        try {
            $content = $this->buildMessageContent($filePath, $ext, $originalName, $remarks);
        } catch (\Exception $e) {
            if ($filePath) {
                @unlink($filePath);
            }
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            return;
        }

        $payload = [
            'model'      => $model,
            'messages'   => [['role' => 'user', 'content' => $content]],
            'max_tokens' => 4096,
        ];

        // ── Call OpenAI ──────────────────────────────────────────────────
        $ch = curl_init($baseUrl . '/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_TIMEOUT        => 90,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
        ]);
        $body     = (string) curl_exec($ch);
        $curlErr  = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Clean up uploaded file
        if ($filePath) {
            @unlink($filePath);
        }

        if ($curlErr) {
            echo json_encode(['success' => false, 'error' => 'Connection error: ' . $curlErr]);
            return;
        }

        $data = json_decode($body, true);

        if ($httpCode !== 200) {
            $errMsg = $data['error']['message'] ?? "OpenAI returned HTTP {$httpCode}";
            Logger::error("AIProcessController: {$errMsg}");
            echo json_encode(['success' => false, 'error' => $errMsg]);
            return;
        }

        $output = $data['choices'][0]['message']['content'] ?? '';
        $tokens = $data['usage']['total_tokens'] ?? 0;

        // Record usage
        try {
            $db = Database::getInstance();
            $db->query(
                "UPDATE convertx_ai_providers
                    SET total_tokens_used = total_tokens_used + :t,
                        total_cost_usd    = total_cost_usd + :c
                  WHERE id = :id",
                [
                    't'  => $tokens,
                    'c'  => round($tokens * (float) ($provider['cost_per_1k_tokens'] ?? 0.00015) / 1000, 8),
                    'id' => (int) $provider['id'],
                ]
            );
        } catch (\Exception $e) {
            Logger::warning('AIProcessController: usage record failed — ' . $e->getMessage());
        }

        echo json_encode([
            'success'      => true,
            'output'       => $output,
            'tokens_used'  => $tokens,
            'model'        => $model,
            'filename'     => $originalName,
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Build the message content array for OpenAI                         //
    // ------------------------------------------------------------------ //

    private function buildMessageContent(
        ?string $filePath,
        string  $ext,
        string  $originalName,
        string  $remarks
    ): array|string {
        if ($filePath === null) {
            // No file — plain text prompt
            return $remarks;
        }

        // Image → vision
        if (in_array($ext, self::IMAGE_EXTS, true)) {
            $mime      = mime_content_type($filePath) ?: 'image/jpeg';
            $b64       = base64_encode((string) file_get_contents($filePath));
            return [
                ['type' => 'text',
                 'text' => "File: {$originalName}\n\nInstructions: {$remarks}"],
                ['type' => 'image_url',
                 'image_url' => ['url' => "data:{$mime};base64,{$b64}", 'detail' => 'high']],
            ];
        }

        // Readable text formats
        $fileText = $this->extractText($filePath, $ext);
        if ($fileText === '') {
            throw new \RuntimeException(
                "Could not extract text from '{$originalName}'. "
                . "Supported text formats: " . implode(', ', array_merge(self::TEXT_EXTS, ['pdf', 'docx', 'xlsx', 'pptx', 'odt']))
                . ". For images, use JPG/PNG/WebP."
            );
        }

        if (mb_strlen($fileText) > self::MAX_TEXT_CHARS) {
            $fileText = mb_substr($fileText, 0, self::MAX_TEXT_CHARS)
                      . "\n\n[... content truncated to " . number_format(self::MAX_TEXT_CHARS) . " characters ...]";
        }

        return "File: {$originalName}\n\n"
             . "Instructions: {$remarks}\n\n"
             . "--- File Content ---\n{$fileText}";
    }

    // ------------------------------------------------------------------ //
    //  Text extraction helpers                                             //
    // ------------------------------------------------------------------ //

    private function extractText(string $path, string $ext): string
    {
        // Plain text / markup
        if (in_array($ext, self::TEXT_EXTS, true)) {
            return (string) file_get_contents($path);
        }

        // PDF → pdftotext (poppler)
        if ($ext === 'pdf') {
            $bin = $this->findBinary('pdftotext');
            if ($bin) {
                $tmp = sys_get_temp_dir() . '/cx_ptt_' . bin2hex(random_bytes(8));
                $proc = proc_open(
                    [$bin, $path, $tmp],
                    [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']],
                    $pipes
                );
                if (is_resource($proc)) {
                    fclose($pipes[0]);
                    fclose($pipes[1]);
                    fclose($pipes[2]);
                    proc_close($proc);
                }
                if (file_exists($tmp)) {
                    $text = (string) file_get_contents($tmp);
                    @unlink($tmp);
                    if (trim($text) !== '') {
                        return $text;
                    }
                }
            }
        }

        // Office / document — LibreOffice --cat
        $loExts = ['docx', 'doc', 'odt', 'rtf', 'xlsx', 'xls', 'ods', 'pptx', 'ppt', 'odp', 'pdf'];
        if (in_array($ext, $loExts, true)) {
            $lo = $this->findBinary('libreoffice') ?: $this->findBinary('soffice');
            if ($lo) {
                $pid = bin2hex(random_bytes(4));
                $proc = proc_open(
                    [$lo, '--headless', '--cat',
                     '-env:UserInstallation=file:///tmp/lo-' . $pid,
                     $path],
                    [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']],
                    $pipes,
                    null,
                    ['DISPLAY' => '', 'HOME' => '/tmp']
                );
                $text = '';
                if (is_resource($proc)) {
                    fclose($pipes[0]);
                    fclose($pipes[2]);
                    $text = (string) stream_get_contents($pipes[1]);
                    fclose($pipes[1]);
                    proc_close($proc);
                }
                if (trim($text) !== '') {
                    return $text;
                }
            }
        }

        return '';
    }

    /**
     * Locate a binary by name, restricting to trusted system directories.
     * Returns the absolute path or null if not found / not in a safe location.
     */
    private function findBinary(string $name): ?string
    {
        static $cache = [];
        if (isset($cache[$name])) {
            return $cache[$name];
        }
        // Only accept binaries in well-known system paths
        $safeDirs = ['/usr/bin', '/usr/local/bin', '/bin', '/usr/sbin', '/opt/libreoffice/program'];
        foreach ($safeDirs as $dir) {
            $full = $dir . '/' . $name;
            if (is_executable($full)) {
                return $cache[$name] = $full;
            }
        }
        return $cache[$name] = null;
    }

    // ------------------------------------------------------------------ //
    //  Helpers                                                             //
    // ------------------------------------------------------------------ //

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require PROJECT_PATH . '/views/layout.php';
    }
}
