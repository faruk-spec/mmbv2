<?php

namespace Core;

class UploadSecurityMonitor
{
    public static function logEvent(string $action, array $context = [], bool $notifyAdmins = false): void
    {
        $userId = isset($context['user_id']) ? (int) $context['user_id'] : (Auth::id() ?: null);
        $status = $context['status'] ?? 'failure';

        ActivityLogger::log($userId, $action, [
            'module' => 'upload_security',
            'resource_type' => 'upload',
            'status' => $status,
            'readable_message' => $context['message'] ?? null,
            'data' => $context,
        ]);

        $logLevel = $status === 'failure' ? 'warning' : 'info';
        Logger::{$logLevel}('Upload security event: ' . $action, $context);

        if ($status === 'failure') {
            self::appendFail2BanLog($action, $context);
        }

        if ($notifyAdmins) {
            self::notifyAdmins($action, $context);
        }
    }

    private static function appendFail2BanLog(string $action, array $context): void
    {
        $logDir = BASE_PATH . '/storage/logs';
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $ip = $context['ip_address'] ?? Security::getClientIp();
        $line = sprintf(
            "[%s] action=%s ip=%s user_id=%s reason=%s file=%s\n",
            date('Y-m-d H:i:s'),
            preg_replace('/[^a-z0-9_:-]/i', '', $action),
            preg_replace('/[^a-f0-9\.:]/i', '', (string) $ip),
            isset($context['user_id']) ? (int) $context['user_id'] : 0,
            str_replace(["\r", "\n"], ' ', (string) ($context['reason'] ?? '')),
            str_replace(["\r", "\n"], ' ', (string) ($context['original_name'] ?? ''))
        );

        @file_put_contents($logDir . '/upload-security-fail2ban.log', $line, FILE_APPEND | LOCK_EX);
    }

    private static function notifyAdmins(string $action, array $context): void
    {
        try {
            $db = Database::getInstance();
            $admins = $db->fetchAll("SELECT id, email FROM users WHERE role LIKE '%admin%'");
            if (empty($admins)) {
                return;
            }

            $subject = 'Security upload alert: ' . str_replace('_', ' ', $action);
            $message = nl2br(htmlspecialchars(
                "Upload security event: {$action}\n" .
                "Reason: " . ($context['reason'] ?? 'N/A') . "\n" .
                "IP: " . ($context['ip_address'] ?? Security::getClientIp()) . "\n" .
                "File: " . ($context['original_name'] ?? 'N/A')
            ));

            foreach ($admins as $admin) {
                Notification::send((int) $admin['id'], 'security_alert', 'Upload security event detected', $context, ['database']);
            }

            $settingsRow = $db->fetch("SELECT value FROM settings WHERE `key` = 'security_alert_emails' LIMIT 1");
            $emails = [];
            if (!empty($settingsRow['value'])) {
                $emails = array_filter(array_map('trim', explode(',', $settingsRow['value'])));
            }

            if (empty($emails)) {
                foreach ($admins as $admin) {
                    if (!empty($admin['email'])) {
                        $emails[] = $admin['email'];
                    }
                }
            }

            $emails = array_values(array_unique(array_filter($emails, static fn($email) => filter_var($email, FILTER_VALIDATE_EMAIL))));
            foreach ($emails as $email) {
                Mailer::send($email, $subject, $message);
            }
        } catch (\Throwable $e) {
            Logger::error('Upload security notifyAdmins failed: ' . $e->getMessage());
        }
    }
}
