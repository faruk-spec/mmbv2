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
            // message is a human-readable summary, never raw user data
            'message'       => $this->summarize($statusCode, $responseBody),
        ];

        $this->writeEntry($entry);
    }

    /**
     * Produce a human-readable one-line summary from HTTP status + response body.
     * Never exposes raw user data — only well-known keys (success, message, error).
     */
    private function summarize(int $status, string $body): string
    {
        // First try to get a message from the JSON body
        $decoded = json_decode($body, true);
        if (is_array($decoded)) {
            if (!empty($decoded['message']) && is_string($decoded['message'])) {
                return $this->truncate($decoded['message'], 120);
            }
            if (!empty($decoded['error']) && is_string($decoded['error'])) {
                return $this->truncate($decoded['error'], 120);
            }
            if (isset($decoded['success'])) {
                return $decoded['success'] ? 'Success' : 'Failed';
            }
        }

        // Fall back to HTTP status description
        $descriptions = [
            200 => 'OK', 201 => 'Created', 204 => 'No Content',
            301 => 'Moved Permanently', 302 => 'Found', 304 => 'Not Modified',
            400 => 'Bad Request', 401 => 'Unauthorized', 403 => 'Forbidden',
            404 => 'Not Found', 405 => 'Method Not Allowed', 409 => 'Conflict',
            422 => 'Unprocessable Entity', 429 => 'Too Many Requests',
            500 => 'Internal Server Error', 502 => 'Bad Gateway',
            503 => 'Service Unavailable',
        ];

        return $descriptions[$status] ?? 'HTTP ' . $status;
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
