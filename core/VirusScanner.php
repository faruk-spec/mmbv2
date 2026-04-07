<?php
namespace Core;

class VirusScanner
{
    private static array $blockedSchemes = ['javascript', 'data', 'vbscript', 'file', 'ftp'];
    private static array $blockedDomains = [
        'malware.com','phishing.com','xvideos.com','pornhub.com',
        '0.0.0.0','localhost',
    ];

    public static function isSafeUrl(string $url): bool
    {
        $url = trim($url);
        if (empty($url)) return true;

        $parsed = parse_url(strtolower($url));
        if ($parsed === false) return false;

        $scheme = $parsed['scheme'] ?? '';
        if (in_array($scheme, self::$blockedSchemes, true)) return false;

        $host = $parsed['host'] ?? '';
        if (empty($host)) return false;

        // Block private/loopback IPs
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            if (!filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return false;
            }
        }

        foreach (self::$blockedDomains as $blocked) {
            if ($host === $blocked || str_ends_with($host, '.' . $blocked)) return false;
        }

        return true;
    }

    public static function scanUrl(string $url): array
    {
        $safe = self::isSafeUrl($url);
        if (!$safe) {
            return ['safe' => false, 'reason' => 'URL contains a blocked or unsafe target.'];
        }

        $apiKey = defined('VIRUSTOTAL_API_KEY') ? VIRUSTOTAL_API_KEY : (\Core\Helpers::config('virus_scan.virustotal_api_key') ?? '');
        if (!empty($apiKey)) {
            $result = self::checkVirusTotal($url, $apiKey);
            if ($result !== null) return $result;
        }

        return ['safe' => true, 'reason' => ''];
    }

    public static function scanFile(string $filePath): array
    {
        if (!file_exists($filePath)) {
            return ['safe' => false, 'reason' => 'File not found.'];
        }

        $maxSize = 50 * 1024 * 1024; // 50 MB
        if (filesize($filePath) > $maxSize) {
            return ['safe' => false, 'reason' => 'File exceeds maximum allowed size.'];
        }

        // Check for PHP code inside files (even in images)
        $content = file_get_contents($filePath, false, null, 0, 8192);
        if ($content !== false) {
            if (preg_match('/<\?php|<\?=/i', $content)) {
                return ['safe' => false, 'reason' => 'File contains potentially dangerous PHP code.'];
            }
            if (preg_match('/<script[\s>]/i', $content)) {
                return ['safe' => false, 'reason' => 'File contains script tags.'];
            }
        }

        // MIME type mismatch check
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $detectedMime = $finfo->file($filePath);
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $allowedMimeMap = [
            'jpg'  => ['image/jpeg'],
            'jpeg' => ['image/jpeg'],
            'png'  => ['image/png'],
            'gif'  => ['image/gif'],
            'webp' => ['image/webp'],
            'pdf'  => ['application/pdf'],
            'zip'  => ['application/zip', 'application/x-zip-compressed'],
            'txt'  => ['text/plain'],
            'csv'  => ['text/plain', 'text/csv', 'application/csv'],
        ];
        if (isset($allowedMimeMap[$ext])) {
            if (!in_array($detectedMime, $allowedMimeMap[$ext], true)) {
                return ['safe' => false, 'reason' => "File MIME type mismatch: detected {$detectedMime} for .{$ext} file."];
            }
        }

        return ['safe' => true, 'reason' => ''];
    }

    private static function checkVirusTotal(string $url, string $apiKey): ?array
    {
        try {
            $encodedUrl = rtrim(strtr(base64_encode($url), '+/', '-_'), '=');
            $ch = curl_init("https://www.virustotal.com/api/v3/urls/{$encodedUrl}");
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_HTTPHEADER     => ["x-apikey: {$apiKey}"],
            ]);
            $response = curl_exec($ch);
            $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                $stats = $data['data']['attributes']['last_analysis_stats'] ?? [];
                $malicious = ($stats['malicious'] ?? 0) + ($stats['suspicious'] ?? 0);
                if ($malicious > 0) {
                    return ['safe' => false, 'reason' => "URL flagged by {$malicious} security vendor(s)."];
                }
                return ['safe' => true, 'reason' => ''];
            }
        } catch (\Exception $e) {
            Logger::error('VirusTotal check failed: ' . $e->getMessage());
        }
        return null;
    }
}
