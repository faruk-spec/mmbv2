<?php
/**
 * ConvertX OCR Controller
 *
 * Handles both local (Tesseract) and AI-assisted OCR text extraction.
 *
 * @package MMB\Projects\ConvertX\Controllers
 */

namespace Projects\ConvertX\Controllers;

use Core\Auth;
use Core\Database;
use Core\Security;

class OcrController
{
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'application/pdf'];
    private const MAX_FILE_SIZE = 10485760; // 10 MB

    // ------------------------------------------------------------------ //
    //  Local OCR (Tesseract)                                               //
    // ------------------------------------------------------------------ //

    public function show(): void
    {
        $this->render('ocr', [
            'title' => 'OCR – Extract Text',
            'user'  => Auth::user(),
        ]);
    }

    public function process(): void
    {
        while (ob_get_level() > 0) ob_end_clean();
        header('Content-Type: application/json');

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            echo json_encode(['success' => false, 'error' => 'Invalid request token.']);
            return;
        }
        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required.']);
            return;
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => 'Please upload a valid image or PDF file.']);
            return;
        }

        $file = $_FILES['file'];
        if ($file['size'] > self::MAX_FILE_SIZE) {
            echo json_encode(['success' => false, 'error' => 'File exceeds 10 MB limit.']);
            return;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, self::ALLOWED_TYPES, true)) {
            echo json_encode(['success' => false, 'error' => 'Unsupported file type. Please upload JPEG, PNG, GIF, WebP, or PDF.']);
            return;
        }

        $lang     = preg_replace('/[^a-z+]/', '', strtolower($_POST['lang'] ?? 'eng'));
        $lang     = $lang ?: 'eng';
        $tmpPath  = $file['tmp_name'];
        $outBase  = sys_get_temp_dir() . '/cx_ocr_' . uniqid();

        // Tesseract CLI
        $cmd = sprintf(
            'tesseract %s %s -l %s --oem 1 --psm 3 2>/dev/null',
            escapeshellarg($tmpPath),
            escapeshellarg($outBase),
            escapeshellarg($lang)
        );
        exec($cmd, $output, $returnCode);

        $txtFile = $outBase . '.txt';
        if ($returnCode !== 0 || !file_exists($txtFile)) {
            // Tesseract not available or failed
            echo json_encode(['success' => false, 'error' => 'OCR processing failed. Please ensure Tesseract is installed on the server.']);
            return;
        }

        $text = file_get_contents($txtFile);
        @unlink($txtFile);

        // Clean up whitespace
        $text = preg_replace('/\r\n/', "\n", $text);
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        $text = trim($text);

        echo json_encode(['success' => true, 'text' => $text, 'chars' => mb_strlen($text)]);
    }

    // ------------------------------------------------------------------ //
    //  AI OCR (OpenAI Vision)                                              //
    // ------------------------------------------------------------------ //

    public function showAi(): void
    {
        $provider   = $this->getOpenAIProvider();
        $configured = ($provider !== null);
        $this->render('ocr-ai', [
            'title'      => 'AI OCR – Extract Text',
            'user'       => Auth::user(),
            'configured' => $configured,
        ]);
    }

    public function processAi(): void
    {
        while (ob_get_level() > 0) ob_end_clean();
        header('Content-Type: application/json');

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            echo json_encode(['success' => false, 'error' => 'Invalid request token.']);
            return;
        }
        if (!Auth::check()) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Authentication required.']);
            return;
        }

        $provider = $this->getOpenAIProvider();
        if (!$provider) {
            echo json_encode(['success' => false, 'error' => 'AI OCR is not configured. An admin must enable the OpenAI provider in ConvertX Settings.']);
            return;
        }

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['success' => false, 'error' => 'Please upload a valid image file.']);
            return;
        }

        $file = $_FILES['file'];
        if ($file['size'] > self::MAX_FILE_SIZE) {
            echo json_encode(['success' => false, 'error' => 'File exceeds 10 MB limit.']);
            return;
        }

        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mime     = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        $imgMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

        if (!in_array($mime, $imgMimes, true)) {
            echo json_encode(['success' => false, 'error' => 'AI OCR supports image files only (JPEG, PNG, GIF, WebP).']);
            return;
        }

        // Encode image as base64 for OpenAI vision
        $imageData = base64_encode(file_get_contents($file['tmp_name']));
        $prompt    = trim($_POST['prompt'] ?? 'Extract all text from this image exactly as written, preserving layout as much as possible.');

        $payload = json_encode([
            'model'      => $provider['model'] ?? 'gpt-4o',
            'max_tokens' => 4096,
            'messages'   => [[
                'role'    => 'user',
                'content' => [
                    ['type' => 'text',       'text'      => $prompt],
                    ['type' => 'image_url',  'image_url' => ['url' => "data:{$mime};base64,{$imageData}"]],
                ],
            ]],
        ]);

        $ch = curl_init('https://api.openai.com/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $provider['api_key'],
                'Content-Type: application/json',
            ],
        ]);
        $response  = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode !== 200) {
            $err = 'AI request failed.';
            if ($response) {
                $decoded = json_decode($response, true);
                $err = $decoded['error']['message'] ?? $err;
            }
            echo json_encode(['success' => false, 'error' => $err]);
            return;
        }

        $decoded = json_decode($response, true);
        $text    = $decoded['choices'][0]['message']['content'] ?? '';
        echo json_encode(['success' => true, 'text' => $text, 'chars' => mb_strlen($text)]);
    }

    // ------------------------------------------------------------------ //
    //  Helpers                                                             //
    // ------------------------------------------------------------------ //

    private function getOpenAIProvider(): ?array
    {
        try {
            $db  = Database::getInstance();
            $row = $db->fetch(
                "SELECT * FROM convertx_ai_providers WHERE slug = 'openai' AND is_active = 1 AND api_key != '' LIMIT 1"
            );
            return $row ?: null;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require PROJECT_PATH . '/views/layout.php';
    }
}
