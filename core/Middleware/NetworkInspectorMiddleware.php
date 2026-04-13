<?php
/**
 * Network Inspector Middleware
 *
 * Logs API request/response metadata for debugging purposes.
 * Only activates when APP_DEBUG is true and the current user is super_admin.
 * Redacts sensitive fields to prevent credential leaks.
 *
 * @package MMB\Core\Middleware
 */

namespace Core\Middleware;

use Core\Auth;
use Core\Logger;

class NetworkInspectorMiddleware
{
    /** Fields whose values must be redacted in logs */
    private const SENSITIVE_KEYS = [
        'password', 'token', 'secret', 'key', 'authorization',
        'cookie', 'access_token', 'refresh_token', 'api_key', 'client_secret',
    ];

    /** Maximum entries kept in the log file */
    private const MAX_ENTRIES = 100;

    private string $logFile;

    public function __construct()
    {
        $this->logFile = dirname(__DIR__, 2) . '/storage/logs/network_inspector.json';
    }

    /**
     * Log the current request.
     * Call this after the response has been generated (e.g. in a shutdown handler).
     */
    public function log(
        string $method,
        string $url,
        int    $statusCode,
        float  $responseTime,
        array  $requestBody  = [],
        string $responseBody = ''
    ): void {
        if (!defined('APP_DEBUG') || !APP_DEBUG) {
            return;
        }

        // Only log for super_admin users
        try {
            $user = Auth::user();
            if (!$user || $user['role'] !== 'super_admin') {
                return;
            }
        } catch (\Exception $e) {
            return;
        }

        $entry = [
            'timestamp'     => date('Y-m-d H:i:s'),
            'method'        => strtoupper($method),
            'url'           => $url,
            'status'        => $statusCode,
            'response_time' => round($responseTime, 3),
            'request_body'  => $this->redact($requestBody),
            'response_body' => $this->truncate($this->redactString($responseBody), 300),
        ];

        $this->writeEntry($entry);
    }

    /**
     * Redact sensitive keys from an associative array (recursive).
     */
    private function redact(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array(strtolower((string) $key), self::SENSITIVE_KEYS, true)) {
                $data[$key] = '[REDACTED]';
            } elseif (is_array($value)) {
                $data[$key] = $this->redact($value);
            }
        }
        return $data;
    }

    /**
     * Redact sensitive keys from a raw JSON/text string.
     * Handles both string values ("key":"value") and non-string values ("key":123 / true / null).
     */
    private function redactString(string $body): string
    {
        foreach (self::SENSITIVE_KEYS as $key) {
            $escaped = preg_quote($key, '/');
            // Match quoted string values: "key": "..."
            $body = preg_replace(
                '/"' . $escaped . '"\s*:\s*"[^"]*"/i',
                '"' . $key . '":"[REDACTED]"',
                $body
            );
            // Match non-string values (numbers, booleans, null): "key": 123 / true / false / null
            $body = preg_replace(
                '/"' . $escaped . '"\s*:\s*(?!")[^\s,}\]]+/i',
                '"' . $key . '":"[REDACTED]"',
                $body
            );
        }
        return $body;
    }

    private function truncate(string $s, int $max): string
    {
        return mb_strlen($s) > $max ? mb_substr($s, 0, $max) . '…' : $s;
    }

    /**
     * Atomically append one entry to the log file, keeping at most MAX_ENTRIES.
     */
    private function writeEntry(array $entry): void
    {
        try {
            // Ensure directory exists
            $dir = dirname($this->logFile);
            if (!is_dir($dir)) {
                mkdir($dir, 0750, true);
            }

            $fh = fopen($this->logFile, 'c+');
            if (!$fh) {
                return;
            }

            flock($fh, LOCK_EX);

            $contents = stream_get_contents($fh);
            $entries  = json_decode($contents ?: '[]', true) ?? [];

            $entries[] = $entry;

            // Rotate: keep only the most recent MAX_ENTRIES
            if (count($entries) > self::MAX_ENTRIES) {
                $entries = array_slice($entries, -self::MAX_ENTRIES);
            }

            rewind($fh);
            ftruncate($fh, 0);
            fwrite($fh, json_encode($entries, JSON_PRETTY_PRINT));
            fflush($fh);
            flock($fh, LOCK_UN);
            fclose($fh);
        } catch (\Exception $e) {
            Logger::error('NetworkInspectorMiddleware write error: ' . $e->getMessage());
        }
    }
}
