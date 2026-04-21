<?php

namespace Core;

class SecureUpload
{
    private const BLOCKED_CONTENT_PATTERN = '/<\?(php|=)?|<script[\s>]|<html[\s>]|eval\s*\(|base64_decode\s*\(/i';

    public static function process(array $file, array $options = []): array
    {
        $cfg = Helpers::config('upload_security', []);
        $mode = self::normalizeMode((string) ($options['mode'] ?? Helpers::config('settings.upload_scan_mode', $cfg['mode'] ?? 'passive')));
        $scanEnabled = (bool) (
            $options['scan_enabled']
            ?? Helpers::config('settings.upload_clamav_enabled', ($cfg['clamav']['enabled'] ?? true) ? '1' : '0')
        );

        $userId = isset($options['user_id']) ? (int) $options['user_id'] : (Auth::id() ?: null);
        $trusted = (bool) ($options['trusted'] ?? false);
        $source = (string) ($options['source'] ?? 'unknown');
        $destinationDir = rtrim((string) ($options['destination_dir'] ?? ''), '/');
        $maxSize = (int) ($options['max_size'] ?? ($cfg['max_file_size'] ?? (50 * 1024 * 1024)));
        if ($destinationDir === '') {
            return self::fail('upload_validation_failed', 'Missing destination directory.', $file, $userId, $source, true);
        }

        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return self::fail('upload_validation_failed', 'Upload failed (error code ' . ($file['error'] ?? UPLOAD_ERR_NO_FILE) . ').', $file, $userId, $source, true);
        }

        if (empty($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
            return self::fail('upload_validation_failed', 'Invalid uploaded file handle.', $file, $userId, $source, true);
        }

        if (($file['size'] ?? 0) <= 0 || ($file['size'] ?? 0) > $maxSize) {
            return self::fail('upload_validation_failed', 'File size is invalid or exceeds allowed limit.', $file, $userId, $source, true);
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) ($finfo->file($file['tmp_name']) ?: 'application/octet-stream');
        $extension = strtolower((string) pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));

        $blockedExtensions = array_map('strtolower', (array) ($cfg['blocked_extensions'] ?? []));
        $blockedMimes = array_map('strtolower', (array) ($cfg['blocked_mime_types'] ?? []));
        if (in_array($extension, $blockedExtensions, true) || in_array(strtolower($mime), $blockedMimes, true)) {
            return self::fail('upload_rejected_validation', 'Blocked file type.', $file, $userId, $source, true, $mime);
        }

        $allowedExtensions = array_map('strtolower', (array) ($options['allowed_extensions'] ?? []));
        if (!empty($allowedExtensions) && !in_array($extension, $allowedExtensions, true)) {
            return self::fail('upload_rejected_validation', 'File extension is not allowed.', $file, $userId, $source, true, $mime);
        }

        $allowedMimes = array_map('strtolower', (array) ($options['allowed_mime_types'] ?? []));
        if (!empty($allowedMimes) && !in_array(strtolower($mime), $allowedMimes, true)) {
            return self::fail('upload_rejected_validation', 'File MIME type is not allowed.', $file, $userId, $source, true, $mime);
        }

        $head = @file_get_contents($file['tmp_name'], false, null, 0, 16384);
        if ($head !== false && !$trusted && preg_match(self::BLOCKED_CONTENT_PATTERN, $head)) {
            return self::fail('upload_rejected_validation', 'Potentially executable or script content detected.', $file, $userId, $source, true, $mime);
        }

        if (str_starts_with($mime, 'image/') && strtolower($mime) !== 'image/svg+xml' && !@getimagesize($file['tmp_name'])) {
            return self::fail('upload_rejected_validation', 'Invalid image content.', $file, $userId, $source, true, $mime);
        }

        if ($scanEnabled) {
            $scan = self::scanWithClamAv($file['tmp_name'], (string) ($cfg['clamav']['command'] ?? 'clamdscan --no-summary --stdout'));
            if (!$scan['success'] && $mode === 'enforce') {
                return self::fail('upload_scan_failed', $scan['reason'], $file, $userId, $source, true, $mime);
            }
            if (!$scan['success'] && $mode === 'passive') {
                UploadSecurityMonitor::logEvent('upload_scan_failed', [
                    'status' => 'failure',
                    'reason' => $scan['reason'],
                    'source' => $source,
                    'user_id' => $userId,
                    'trusted' => $trusted ? 1 : 0,
                    'original_name' => $file['name'] ?? '',
                    'mime_type' => $mime,
                    'size' => (int) ($file['size'] ?? 0),
                    'ip_address' => Security::getClientIp(),
                    'mode' => 'passive',
                ], false);
            }
            if ($scan['success'] && !$scan['clean']) {
                return self::fail('upload_infected_detected', $scan['reason'], $file, $userId, $source, true, $mime);
            }
        }

        if (!is_dir($destinationDir) && !@mkdir($destinationDir, 0755, true) && !is_dir($destinationDir)) {
            return self::fail('upload_storage_error', 'Could not create destination directory.', $file, $userId, $source, true, $mime);
        }

        $prefix = preg_replace('/[^a-z0-9_-]/i', '', (string) ($options['filename_prefix'] ?? ''));
        $safeExt = $extension !== '' ? $extension : self::extensionFromMime($mime);
        $safeName = ($prefix !== '' ? $prefix . '_' : '') . bin2hex(random_bytes(16)) . ($safeExt ? '.' . $safeExt : '');
        $destinationPath = $destinationDir . '/' . $safeName;

        if (!@move_uploaded_file($file['tmp_name'], $destinationPath)) {
            return self::fail('upload_storage_error', 'Could not move uploaded file to destination.', $file, $userId, $source, true, $mime);
        }

        @chmod($destinationPath, 0644);
        UploadSecurityMonitor::logEvent('upload_accepted', [
            'status' => 'success',
            'source' => $source,
            'user_id' => $userId,
            'trusted' => $trusted ? 1 : 0,
            'original_name' => $file['name'] ?? '',
            'stored_name' => $safeName,
            'mime_type' => $mime,
            'size' => (int) ($file['size'] ?? 0),
            'ip_address' => Security::getClientIp(),
        ], false);

        return [
            'success' => true,
            'filename' => $safeName,
            'path' => $destinationPath,
            'mime_type' => $mime,
            'size' => (int) ($file['size'] ?? 0),
            'original_name' => (string) ($file['name'] ?? ''),
        ];
    }

    private static function fail(string $action, string $reason, array $file, ?int $userId, string $source, bool $notifyAdmins, ?string $mime = null): array
    {
        UploadSecurityMonitor::logEvent($action, [
            'status' => 'failure',
            'reason' => $reason,
            'source' => $source,
            'user_id' => $userId,
            'original_name' => (string) ($file['name'] ?? ''),
            'mime_type' => $mime ?: null,
            'size' => (int) ($file['size'] ?? 0),
            'ip_address' => Security::getClientIp(),
        ], $notifyAdmins);

        return [
            'success' => false,
            'error' => $reason,
            'action' => $action,
        ];
    }

    private static function normalizeMode(string $mode): string
    {
        $mode = strtolower(trim($mode));
        return in_array($mode, ['passive', 'enforce'], true) ? $mode : 'passive';
    }

    private static function scanWithClamAv(string $filePath, string $baseCommand): array
    {
        if (!is_file($filePath)) {
            return ['success' => false, 'clean' => false, 'reason' => 'File for antivirus scan is missing.'];
        }

        if (!function_exists('shell_exec')) {
            return ['success' => false, 'clean' => false, 'reason' => 'shell_exec is disabled; antivirus scan unavailable.'];
        }

        $command = trim($baseCommand) . ' ' . escapeshellarg($filePath) . ' 2>&1';
        $output = shell_exec($command);

        if ($output === null) {
            return ['success' => false, 'clean' => false, 'reason' => 'ClamAV command failed to execute.'];
        }

        $normalized = strtoupper($output);
        if (str_contains($normalized, 'FOUND')) {
            return ['success' => true, 'clean' => false, 'reason' => trim($output)];
        }

        if (str_contains($normalized, 'OK')) {
            return ['success' => true, 'clean' => true, 'reason' => 'Clean'];
        }

        return ['success' => false, 'clean' => false, 'reason' => trim($output) ?: 'Unknown ClamAV response.'];
    }

    private static function extensionFromMime(string $mime): string
    {
        $map = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'image/svg+xml' => 'svg',
            'application/pdf' => 'pdf',
            'text/plain' => 'txt',
            'text/csv' => 'csv',
            'application/zip' => 'zip',
            'application/x-rar-compressed' => 'rar',
            'audio/mpeg' => 'mp3',
            'audio/wav' => 'wav',
            'video/mp4' => 'mp4',
        ];

        return $map[strtolower($mime)] ?? 'bin';
    }
}
