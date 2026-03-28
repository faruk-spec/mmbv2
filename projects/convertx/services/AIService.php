<?php
/**
 * AI Service
 *
 * Abstraction layer over multiple AI providers.
 * Provides OCR, summarization, translation, and classification
 * with intelligent provider routing, fallback, and cost tracking.
 *
 * @package MMB\Projects\ConvertX\Services
 */

namespace Projects\ConvertX\Services;

use Core\Logger;
use Projects\ConvertX\Models\AIProviderModel;

class AIService
{
    private AIProviderModel $providerModel;

    public function __construct()
    {
        $this->providerModel = new AIProviderModel();
    }

    // ------------------------------------------------------------------ //
    //  Public API                                                           //
    // ------------------------------------------------------------------ //

    /**
     * Extract text from an image or scanned document (OCR).
     *
     * @param string $filePath  Absolute path to image/PDF
     * @param string $planTier  User's plan tier ('free'|'pro'|'enterprise')
     * @return array{success: bool, text: string, provider: string, tokens: int, error: string}
     */
    public function ocr(string $filePath, string $planTier = 'free'): array
    {
        $result = $this->dispatch('ocr', $planTier, function (array $provider) use ($filePath): array {
            return $this->callOCR($provider, $filePath);
        });
        if (!$result['success']) {
            return $this->cmdOCR($filePath);
        }
        return $result;
    }

    /**
     * Extract table data from an image using a structured AI prompt.
     *
     * The AI is instructed to return the data as RFC 4180 CSV preserving the
     * original column structure.  Falls back to plain OCR when no vision-capable
     * provider is available.
     *
     * @param string $filePath  Absolute path to image
     * @param string $planTier
     * @return array{success: bool, csv: string, rows: array, provider: string, tokens: int, error: string}
     */
    public function ocrTable(string $filePath, string $planTier = 'free'): array
    {
        $result = $this->dispatch('ocr', $planTier, function (array $provider) use ($filePath): array {
            return $this->callOCRTable($provider, $filePath);
        });
        if (!$result['success']) {
            // No vision provider — fall back to plain cmdOCR and let the caller
            // handle parsing (Tesseract TSV / plain text).
            return ['success' => false, 'csv' => '', 'rows' => [], 'provider' => 'none',
                    'tokens' => 0, 'error' => $result['error'] ?? 'No table-OCR provider available'];
        }
        return $result;
    }

    /**
     * Summarize a piece of text.
     *
     * @param string $text
     * @param string $planTier
     * @param array  $options  Optional: ['max_length' => 200, 'language' => 'en']
     * @return array{success: bool, summary: string, provider: string, tokens: int, error: string}
     */
    public function summarize(string $text, string $planTier = 'free', array $options = []): array
    {
        $result = $this->dispatch('summarization', $planTier, function (array $provider) use ($text, $options): array {
            return $this->callSummarize($provider, $text, $options);
        });
        if (!$result['success']) {
            return $this->phpNativeSummarize($text, $options);
        }
        return $result;
    }

    /**
     * Translate text to a target language.
     *
     * @param string $text
     * @param string $targetLang  ISO-639-1 code, e.g. 'fr', 'de', 'ar'
     * @param string $planTier
     * @return array{success: bool, translated: string, provider: string, tokens: int, error: string}
     */
    public function translate(string $text, string $targetLang, string $planTier = 'pro'): array
    {
        $result = $this->dispatch('translation', $planTier, function (array $provider) use ($text, $targetLang): array {
            return $this->callTranslate($provider, $text, $targetLang);
        });
        if (!$result['success']) {
            return $this->myMemoryTranslate($text, $targetLang);
        }
        return $result;
    }

    /**
     * Classify a document into a category.
     *
     * @param string $text
     * @param string $planTier
     * @return array{success: bool, category: string, confidence: float, provider: string, tokens: int, error: string}
     */
    public function classify(string $text, string $planTier = 'pro'): array
    {
        $result = $this->dispatch('classification', $planTier, function (array $provider) use ($text): array {
            return $this->callClassify($provider, $text);
        });
        if (!$result['success']) {
            return $this->phpNativeClassify($text);
        }
        return $result;
    }

    // ------------------------------------------------------------------ //
    //  Provider routing & fallback                                          //
    // ------------------------------------------------------------------ //

    /**
     * Dispatch an AI task to the best available provider with fallback logic.
     *
     * @param string   $capability
     * @param string   $planTier
     * @param callable $handler   Receives the provider array, returns result array
     */
    private function dispatch(string $capability, string $planTier, callable $handler): array
    {
        $providers = $this->providerModel->getActive();

        foreach ($providers as $provider) {
            $capabilities = json_decode($provider['capabilities'] ?? '[]', true);
            $allowedTiers = json_decode($provider['allowed_tiers'] ?? '["free","pro","enterprise"]', true);

            if (!in_array($capability, $capabilities, true)
                || !in_array($planTier, $allowedTiers, true)
                || !($provider['is_healthy'] ?? true)
            ) {
                continue;
            }

            try {
                $result = $handler($provider);

                // Record usage & cost
                if (!empty($result['tokens'])) {
                    $cost = $this->estimateCost($provider, (int) $result['tokens']);
                    $this->providerModel->recordUsage((int) $provider['id'], (int) $result['tokens'], $cost);
                }

                if ($result['success']) {
                    $result['provider'] = $provider['slug'];
                    return $result;
                }

                // Mark provider unhealthy after failure
                $this->providerModel->setHealth((int) $provider['id'], false);
                Logger::warning("AIService: provider {$provider['slug']} failed for {$capability}, trying next.");

            } catch (\Exception $e) {
                $this->providerModel->setHealth((int) $provider['id'], false);
                Logger::error("AIService: provider {$provider['slug']} threw exception: " . $e->getMessage());
            }
        }

        return [
            'success'  => false,
            'provider' => 'none',
            'tokens'   => 0,
            'error'    => "No available provider for capability: {$capability}",
        ];
    }

    // ------------------------------------------------------------------ //
    //  Provider-specific call implementations                               //
    // ------------------------------------------------------------------ //

    private function callOCR(array $provider, string $filePath): array
    {
        switch ($provider['slug']) {
            case 'openai':
                return $this->openaiVisionOCR($provider, $filePath);

            case 'tesseract':
                return $this->tesseractOCR($filePath);

            default:
                return ['success' => false, 'text' => '', 'tokens' => 0, 'error' => 'Unknown OCR provider'];
        }
    }

    private function callOCRTable(array $provider, string $filePath): array
    {
        switch ($provider['slug']) {
            case 'openai':
                return $this->openaiVisionOCRTable($provider, $filePath);

            default:
                // Non-vision providers cannot do table OCR
                return ['success' => false, 'csv' => '', 'rows' => [], 'tokens' => 0,
                        'error' => 'Provider does not support table OCR'];
        }
    }

    private function callSummarize(array $provider, string $text, array $options): array
    {
        switch ($provider['slug']) {
            case 'openai':
                return $this->openaiSummarize($provider, $text, $options);

            case 'huggingface':
                return $this->huggingfaceSummarize($provider, $text, $options);

            default:
                return ['success' => false, 'summary' => '', 'tokens' => 0, 'error' => 'Unknown summarization provider'];
        }
    }

    private function callTranslate(array $provider, string $text, string $targetLang): array
    {
        switch ($provider['slug']) {
            case 'openai':
                return $this->openaiTranslate($provider, $text, $targetLang);

            default:
                return ['success' => false, 'translated' => '', 'tokens' => 0, 'error' => 'Unknown translation provider'];
        }
    }

    private function callClassify(array $provider, string $text): array
    {
        switch ($provider['slug']) {
            case 'openai':
                return $this->openaiClassify($provider, $text);

            case 'huggingface':
                return $this->huggingfaceClassify($provider, $text);

            default:
                return ['success' => false, 'category' => '', 'confidence' => 0.0, 'tokens' => 0, 'error' => 'Unknown classification provider'];
        }
    }

    // ------------------------------------------------------------------ //
    //  OpenAI implementations                                              //
    // ------------------------------------------------------------------ //

    private function openaiRequest(array $provider, array $payload): array
    {
        $apiKey  = $provider['api_key'] ?? '';
        $baseUrl = rtrim($provider['base_url'] ?? 'https://api.openai.com', '/');

        $ch = curl_init($baseUrl . '/v1/chat/completions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_TIMEOUT        => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return ['success' => false, 'error' => "AI service request failed (HTTP {$httpCode})"];
        }

        $data = json_decode($response, true);
        return ['success' => true, 'data' => $data];
    }

    private function openaiVisionOCR(array $provider, string $filePath): array
    {
        $imageData = base64_encode(file_get_contents($filePath));
        $mimeType  = mime_content_type($filePath) ?: 'image/png';

        $payload = [
            'model'    => $provider['model'] ?? 'gpt-4-vision-preview',
            'messages' => [[
                'role'    => 'user',
                'content' => [
                    ['type' => 'text',      'text'      => 'Extract all text from this image verbatim.'],
                    ['type' => 'image_url', 'image_url' => ['url' => "data:{$mimeType};base64,{$imageData}"]],
                ],
            ]],
            'max_tokens' => 4096,
        ];

        $res = $this->openaiRequest($provider, $payload);
        if (!$res['success']) {
            return ['success' => false, 'text' => '', 'tokens' => 0, 'error' => $res['error']];
        }

        $content = $res['data']['choices'][0]['message']['content'] ?? '';
        $tokens  = $res['data']['usage']['total_tokens'] ?? 0;
        return ['success' => true, 'text' => $content, 'tokens' => $tokens, 'error' => ''];
    }

    /**
     * Table-aware OCR: ask the vision model to return the image data as RFC 4180
     * CSV so that column structure is preserved exactly.  The response is parsed
     * into a 2-D rows array ready for writeXlsxFromRows() / writeOdsCalcFromRows().
     */
    private function openaiVisionOCRTable(array $provider, string $filePath): array
    {
        $imageData = base64_encode(file_get_contents($filePath));
        $mimeType  = mime_content_type($filePath) ?: 'image/png';

        $prompt =
            "This image contains a table or spreadsheet. "
            . "Extract ALL data from it and return ONLY valid RFC 4180 CSV. "
            . "Rules:\n"
            . "- First row must be the header row (column names).\n"
            . "- Each subsequent row is one data row.\n"
            . "- Use a comma as the delimiter.\n"
            . "- Wrap any cell that contains a comma or double-quote in double quotes.\n"
            . "- Escape literal double-quotes inside cells by doubling them (\"\").\n"
            . "- Preserve numeric values exactly as shown (keep currency symbols, decimal points, commas within numbers must be inside quoted cells).\n"
            . "- Do NOT add any explanation, markdown fences, or extra text — output ONLY the CSV.";

        $payload = [
            'model'    => $provider['model'] ?? 'gpt-4o',
            'messages' => [[
                'role'    => 'user',
                'content' => [
                    ['type' => 'text',      'text'      => $prompt],
                    ['type' => 'image_url', 'image_url' => ['url' => "data:{$mimeType};base64,{$imageData}"]],
                ],
            ]],
            'max_tokens' => 4096,
        ];

        $res = $this->openaiRequest($provider, $payload);
        if (!$res['success']) {
            return ['success' => false, 'csv' => '', 'rows' => [], 'tokens' => 0, 'error' => $res['error']];
        }

        $raw    = trim($res['data']['choices'][0]['message']['content'] ?? '');
        $tokens = $res['data']['usage']['total_tokens'] ?? 0;

        // Strip markdown code fences if the model wrapped the CSV anyway
        $raw = preg_replace('/^```[^\n]*\n?/m', '', $raw);
        $raw = preg_replace('/^```$/m', '', $raw);
        $raw = trim((string) $raw);

        // Parse CSV into 2-D rows array
        $rows = $this->parseCsvString($raw);

        if (empty($rows)) {
            return ['success' => false, 'csv' => $raw, 'rows' => [], 'tokens' => $tokens,
                    'error'   => 'AI returned no parseable CSV data'];
        }

        return ['success' => true, 'csv' => $raw, 'rows' => $rows,
                'tokens'  => $tokens, 'provider' => 'openai', 'error' => ''];
    }

    /**
     * Parse an RFC 4180 CSV string into a 2-D array of string cells.
     *
     * @param string $csv
     * @return array<int, array<int, string>>
     */
    private function parseCsvString(string $csv): array
    {
        if (empty(trim($csv))) {
            return [];
        }

        // Normalise line endings
        $csv  = str_replace(["\r\n", "\r"], "\n", $csv);
        $rows = [];

        // Use a temp stream so fgetcsv handles RFC 4180 quoting correctly
        $handle = fopen('php://memory', 'r+');
        if ($handle === false) {
            return [];
        }
        fwrite($handle, $csv);
        rewind($handle);

        while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            // Skip completely empty rows that may appear at end of AI output
            if ($row === [null]) {
                continue;
            }
            $rows[] = array_map('strval', $row);
        }
        fclose($handle);

        return $rows;
    }

    private function openaiSummarize(array $provider, string $text, array $options): array
    {
        $maxLen  = (int) ($options['max_length'] ?? 200);
        $payload = [
            'model'    => $provider['model'] ?? 'gpt-4o-mini',
            'messages' => [[
                'role'    => 'user',
                'content' => "Summarize the following text in at most {$maxLen} words:\n\n{$text}",
            ]],
            'max_tokens' => 512,
        ];

        $res = $this->openaiRequest($provider, $payload);
        if (!$res['success']) {
            return ['success' => false, 'summary' => '', 'tokens' => 0, 'error' => $res['error']];
        }

        $summary = $res['data']['choices'][0]['message']['content'] ?? '';
        $tokens  = $res['data']['usage']['total_tokens'] ?? 0;
        return ['success' => true, 'summary' => $summary, 'tokens' => $tokens, 'error' => ''];
    }

    private function openaiTranslate(array $provider, string $text, string $targetLang): array
    {
        $payload = [
            'model'    => $provider['model'] ?? 'gpt-4o-mini',
            'messages' => [[
                'role'    => 'user',
                'content' => "Translate the following text to {$targetLang}. Return only the translation:\n\n{$text}",
            ]],
            'max_tokens' => 2048,
        ];

        $res = $this->openaiRequest($provider, $payload);
        if (!$res['success']) {
            return ['success' => false, 'translated' => '', 'tokens' => 0, 'error' => $res['error']];
        }

        $translated = $res['data']['choices'][0]['message']['content'] ?? '';
        $tokens     = $res['data']['usage']['total_tokens'] ?? 0;
        return ['success' => true, 'translated' => $translated, 'tokens' => $tokens, 'error' => ''];
    }

    private function openaiClassify(array $provider, string $text): array
    {
        $categories = 'invoice, contract, report, letter, form, other';
        $payload    = [
            'model'    => $provider['model'] ?? 'gpt-4o-mini',
            'messages' => [[
                'role'    => 'user',
                'content' => "Classify this document into exactly one of: {$categories}. "
                           . "Reply as JSON: {\"category\": \"...\", \"confidence\": 0.0-1.0}\n\n{$text}",
            ]],
            'max_tokens' => 64,
        ];

        $res = $this->openaiRequest($provider, $payload);
        if (!$res['success']) {
            return ['success' => false, 'category' => '', 'confidence' => 0.0, 'tokens' => 0, 'error' => $res['error']];
        }

        $raw     = $res['data']['choices'][0]['message']['content'] ?? '{}';
        $parsed  = json_decode($raw, true) ?? [];
        $tokens  = $res['data']['usage']['total_tokens'] ?? 0;

        return [
            'success'    => true,
            'category'   => $parsed['category']   ?? 'other',
            'confidence' => (float) ($parsed['confidence'] ?? 0.0),
            'tokens'     => $tokens,
            'error'      => '',
        ];
    }

    // ------------------------------------------------------------------ //
    //  Tesseract (local OCR)                                               //
    // ------------------------------------------------------------------ //

    private function tesseractOCR(string $filePath): array
    {
        $in  = escapeshellarg($filePath);
        $out = tempnam(sys_get_temp_dir(), 'tess_');
        exec("tesseract {$in} {$out} 2>&1", $lines, $code);

        $txtFile = $out . '.txt';
        if ($code !== 0 || !file_exists($txtFile)) {
            return ['success' => false, 'text' => '', 'tokens' => 0, 'error' => implode(' ', $lines)];
        }

        $text = file_get_contents($txtFile);
        @unlink($txtFile);
        @unlink($out);

        return ['success' => true, 'text' => $text, 'tokens' => 0, 'error' => ''];
    }

    // ------------------------------------------------------------------ //
    //  HuggingFace implementations                                         //
    // ------------------------------------------------------------------ //

    private function huggingfaceRequest(array $provider, string $modelPath, array $payload): array
    {
        $apiKey  = $provider['api_key'] ?? '';
        $baseUrl = rtrim($provider['base_url'] ?? 'https://api-inference.huggingface.co', '/');
        $url     = $baseUrl . '/models/' . $modelPath;

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $apiKey,
            ],
            CURLOPT_TIMEOUT        => 60,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $httpCode !== 200) {
            return ['success' => false, 'error' => "AI service request failed (HTTP {$httpCode})"];
        }

        return ['success' => true, 'data' => json_decode($response, true)];
    }

    private function huggingfaceSummarize(array $provider, string $text, array $options): array
    {
        $model = $provider['model'] ?? 'facebook/bart-large-cnn';
        $res   = $this->huggingfaceRequest($provider, $model, ['inputs' => $text]);

        if (!$res['success']) {
            return ['success' => false, 'summary' => '', 'tokens' => 0, 'error' => $res['error']];
        }

        $summary = $res['data'][0]['summary_text'] ?? '';
        return ['success' => true, 'summary' => $summary, 'tokens' => 0, 'error' => ''];
    }

    private function huggingfaceClassify(array $provider, string $text): array
    {
        $model  = $provider['model'] ?? 'facebook/bart-large-mnli';
        $labels = ['invoice', 'contract', 'report', 'letter', 'form', 'other'];
        $res    = $this->huggingfaceRequest($provider, $model, [
            'inputs'     => $text,
            'parameters' => ['candidate_labels' => $labels],
        ]);

        if (!$res['success']) {
            return ['success' => false, 'category' => '', 'confidence' => 0.0, 'tokens' => 0, 'error' => $res['error']];
        }

        $category   = $res['data']['labels'][0]  ?? 'other';
        $confidence = $res['data']['scores'][0]  ?? 0.0;
        return ['success' => true, 'category' => $category, 'confidence' => (float) $confidence, 'tokens' => 0, 'error' => ''];
    }

    // ------------------------------------------------------------------ //
    //  Cost estimation                                                      //
    // ------------------------------------------------------------------ //

    /**
     * Estimate the USD cost for a given number of tokens on a provider.
     *
     * @param array $provider  Provider row from DB (contains cost_per_1k_tokens)
     * @param int   $tokens
     * @return float
     */
    public function estimateCost(array $provider, int $tokens): float
    {
        $ratePer1k = (float) ($provider['cost_per_1k_tokens'] ?? 0.002);
        return round($tokens * $ratePer1k / 1000, 6);
    }

    /**
     * Estimate token count from plain text (rough approximation).
     *
     * Rule of thumb: ~4 characters per token.
     *
     * @param string $text
     * @return int
     */
    public function estimateTokens(string $text): int
    {
        return (int) ceil(mb_strlen($text) / 4);
    }

    // ------------------------------------------------------------------ //
    //  PHP-native AI fallbacks (zero-config, no API key required)          //
    // ------------------------------------------------------------------ //

    /**
     * Extractive summarization: extract the leading sentences up to $maxWords.
     * Always available — no network, no API key, no external binary needed.
     *
     * Note: sentence splitting is based on `.!?` punctuation and will break on
     * abbreviations like "Dr." or "U.S.A." — this is acceptable for a zero-config
     * fallback; configure an API-backed provider for higher-quality summaries.
     */
    private function phpNativeSummarize(string $text, array $options = []): array
    {
        if (empty(trim($text))) {
            return ['success' => false, 'summary' => '', 'tokens' => 0, 'provider' => 'php_native',
                    'error' => 'No text to summarize'];
        }
        $maxWords  = max(10, (int) ($options['max_length'] ?? 200));
        $sentences = preg_split('/(?<=[.!?])\s+/u', trim($text), -1, PREG_SPLIT_NO_EMPTY) ?: [trim($text)];
        $summary   = '';
        $wordCount = 0;
        foreach ($sentences as $s) {
            $cnt = str_word_count($s);
            if ($wordCount > 0 && $wordCount + $cnt > $maxWords) {
                break;
            }
            $summary   .= ($summary !== '' ? ' ' : '') . trim($s);
            $wordCount += $cnt;
        }
        if ($summary === '') {
            $summary = implode(' ', array_slice(preg_split('/\s+/u', trim($text)) ?: [], 0, $maxWords));
        }
        // Safety net: if a single very long "sentence" was added in full,
        // truncate the summary to maxWords.
        $allWords = preg_split('/\s+/u', $summary) ?: [];
        if (count($allWords) > $maxWords) {
            $summary = implode(' ', array_slice($allWords, 0, $maxWords));
        }
        return ['success' => true, 'summary' => $summary, 'tokens' => 0, 'provider' => 'php_native', 'error' => ''];
    }

    /**
     * Keyword-based document classification.
     * Always available — no network, no API key, no external binary needed.
     */
    private function phpNativeClassify(string $text): array
    {
        if (empty(trim($text))) {
            return ['success' => true, 'category' => 'other', 'confidence' => 1.0,
                    'tokens' => 0, 'provider' => 'php_native', 'error' => ''];
        }
        $lower    = mb_strtolower($text);
        $keywords = [
            'invoice'  => ['invoice', 'bill to', 'amount due', 'subtotal', 'payment terms', 'due date', 'receipt'],
            'contract' => ['agreement', 'this agreement', 'parties', 'whereas', 'hereby', 'terms and conditions'],
            'report'   => ['executive summary', 'findings', 'conclusion', 'analysis', 'quarterly', 'annual report'],
            'letter'   => ['dear ', 'sincerely', 'best regards', 'to whom it may concern', 'yours faithfully'],
            'form'     => ['please fill', 'signature:', 'date of birth', 'full name:', 'check one'],
        ];
        $scores = ['other' => 1];
        foreach ($keywords as $cat => $terms) {
            $score = 0;
            foreach ($terms as $term) {
                $score += substr_count($lower, $term);
            }
            $scores[$cat] = $score;
        }
        arsort($scores);
        $category   = (string) array_key_first($scores);
        $total      = max(1, array_sum($scores));
        $confidence = (float) min(1.0, round($scores[$category] / $total, 2));
        return ['success' => true, 'category' => $category, 'confidence' => $confidence,
                'tokens' => 0, 'provider' => 'php_native', 'error' => ''];
    }

    /**
     * Free translation via the MyMemory API (no API key for low volume).
     * Limit: ~5 000 chars/request, ~100 requests/day on the anonymous tier.
     */
    private function myMemoryTranslate(string $text, string $targetLang): array
    {
        if (empty(trim($text))) {
            return ['success' => false, 'translated' => '', 'tokens' => 0, 'provider' => 'mymemory',
                    'error' => 'No text to translate'];
        }
        $chunk = mb_substr($text, 0, 4000);
        $url   = 'https://api.mymemory.translated.net/get?q=' . rawurlencode($chunk)
               . '&langpair=en|' . rawurlencode($targetLang);
        $ctx   = stream_context_create(['http' => [
            'timeout' => 15, 'method' => 'GET',
            'header'  => "User-Agent: ConvertX/1.0\r\n",
        ]]);
        $resp = @file_get_contents($url, false, $ctx);
        if ($resp === false) {
            return ['success' => false, 'translated' => '', 'tokens' => 0, 'provider' => 'mymemory',
                    'error' => 'Translation API unreachable — check server outbound HTTP access'];
        }
        $data = json_decode($resp, true);
        $translated = $data['responseData']['translatedText'] ?? '';
        if (($data['responseStatus'] ?? 0) !== 200 || empty($translated)) {
            return ['success' => false, 'translated' => '', 'tokens' => 0, 'provider' => 'mymemory',
                    'error' => $data['responseDetails'] ?? 'Translation failed'];
        }
        return ['success' => true, 'translated' => $translated, 'tokens' => 0, 'provider' => 'mymemory', 'error' => ''];
    }

    /**
     * Command-line OCR fallback.
     *
     * Tries (in order):
     *   1. Tesseract — real OCR for raster images and scanned PDFs
     *   2. pdftotext (poppler-utils) — fast text extraction for digital PDFs
     *   3. LibreOffice --cat — extracts plain text from office documents only
     *                          (explicitly skipped for image files to avoid returning
     *                           binary metadata / C2PA certificate garbage)
     */
    private function cmdOCR(string $filePath): array
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Image file extensions that must NOT go through LibreOffice --cat
        $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'tif', 'svg', 'ico'];
        $isImage   = in_array($ext, $imageExts, true);

        // 1. Tesseract — proper OCR for raster images and scanned PDFs
        $tess = trim((string) shell_exec('which tesseract 2>/dev/null'));
        if ($tess) {
            // Use a cryptographically unique path so Tesseract can write <base>.txt
            // without a pre-created placeholder file (no tempnam race condition).
            $tmpBase = sys_get_temp_dir() . '/cx_tess_' . getmypid() . '_' . bin2hex(random_bytes(8));
            exec(
                escapeshellarg($tess) . ' ' . escapeshellarg($filePath)
                . ' ' . escapeshellarg($tmpBase) . ' -l eng 2>/dev/null',
                $_lines, $tessCode
            );
            $tessOut = $tmpBase . '.txt';
            if ($tessCode === 0 && file_exists($tessOut)) {
                $text = (string) file_get_contents($tessOut);
                @unlink($tessOut);
                if (!empty(trim($text))) {
                    return ['success' => true, 'text' => $text, 'tokens' => 0,
                            'provider' => 'tesseract', 'error' => ''];
                }
            }
        }

        // 2. pdftotext — best for digital (non-scanned) PDFs
        if ($ext === 'pdf') {
            $pdftotext = trim((string) shell_exec('which pdftotext 2>/dev/null'));
            if ($pdftotext) {
                $tmp = tempnam(sys_get_temp_dir(), 'cx_ptt_');
                exec(escapeshellarg($pdftotext) . ' ' . escapeshellarg($filePath)
                     . ' ' . escapeshellarg($tmp) . ' 2>/dev/null', $_, $code);
                if ($code === 0 && file_exists($tmp)) {
                    $text = (string) file_get_contents($tmp);
                    @unlink($tmp);
                    if (!empty(trim($text))) {
                        return ['success' => true, 'text' => $text, 'tokens' => 0,
                                'provider' => 'pdftotext', 'error' => ''];
                    }
                }
            }
        }

        // 3. LibreOffice --cat — works on office documents only.
        //    NEVER run on image files: LO outputs raw binary metadata (XMP, C2PA
        //    certificates, EXIF) which is useless garbage, not OCR'd text.
        $lo = trim((string) shell_exec('which libreoffice 2>/dev/null'))
           ?: trim((string) shell_exec('which soffice 2>/dev/null'));
        if ($lo && file_exists($filePath) && !$isImage) {
            $pid  = getmypid();
            $cmd  = 'DISPLAY= HOME=/tmp ' . escapeshellarg($lo) . ' --headless --cat '
                  . '-env:UserInstallation=file:///tmp/lo-' . $pid . ' '
                  . escapeshellarg($filePath) . ' 2>/dev/null';
            exec($cmd, $lines, $code);
            $text = trim(implode("\n", $lines));
            if (!empty($text)) {
                return ['success' => true, 'text' => $text, 'tokens' => 0,
                        'provider' => 'libreoffice_cat', 'error' => ''];
            }
        }

        $hint = $isImage
            ? 'Install Tesseract for image OCR: apt-get install tesseract-ocr'
            : 'OCR requires Tesseract, pdftotext (poppler-utils), or an OpenAI API key. '
              . 'Install with: apt-get install tesseract-ocr poppler-utils';

        return [
            'success'  => false,
            'text'     => '',
            'tokens'   => 0,
            'provider' => 'none',
            'error'    => $hint,
        ];
    }
}
