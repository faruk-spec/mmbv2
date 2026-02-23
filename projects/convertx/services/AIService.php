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
        return $this->dispatch('ocr', $planTier, function (array $provider) use ($filePath): array {
            return $this->callOCR($provider, $filePath);
        });
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
        return $this->dispatch('summarization', $planTier, function (array $provider) use ($text, $options): array {
            return $this->callSummarize($provider, $text, $options);
        });
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
        return $this->dispatch('translation', $planTier, function (array $provider) use ($text, $targetLang): array {
            return $this->callTranslate($provider, $text, $targetLang);
        });
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
        return $this->dispatch('classification', $planTier, function (array $provider) use ($text): array {
            return $this->callClassify($provider, $text);
        });
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
            return ['success' => false, 'error' => "OpenAI HTTP {$httpCode}"];
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
            return ['success' => false, 'error' => "HuggingFace HTTP {$httpCode}"];
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
}
