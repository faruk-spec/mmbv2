<?php
/**
 * ActivityLogger – Centralized Audit Trail
 *
 * A drop-in replacement / extension for Core\Logger::activity() that adds:
 *  • Module / tenant / resource tracking
 *  • Before & after value snapshots
 *  • Human-readable messages
 *  • Device / browser extraction from User-Agent
 *  • User-role tagging
 *  • Request-ID correlation
 *  • Status (success / failure / pending)
 *
 * Usage examples
 * ──────────────
 * // Basic – identical signature to Logger::activity()
 * ActivityLogger::log($userId, 'login');
 *
 * // Rich event
 * ActivityLogger::log($userId, 'update', [
 *     'module'          => 'imgtxt',
 *     'resource_type'   => 'image',
 *     'resource_id'     => 42,
 *     'old_values'      => ['title' => 'Old'],
 *     'new_values'      => ['title' => 'New'],
 *     'readable_message'=> 'Jane updated image #42',
 * ]);
 *
 * // Helpers
 * ActivityLogger::logCreate($userId, 'proshare', 'file', 5, ['name' => 'doc.pdf']);
 * ActivityLogger::logUpdate($userId, 'billx', 'invoice', 12, $before, $after);
 * ActivityLogger::logDelete($userId, 'codexpro', 'snippet', 3);
 * ActivityLogger::logLogin($userId);
 * ActivityLogger::logLogout($userId);
 *
 * @package MMB\Core
 */

namespace Core;

class ActivityLogger
{
    // ------------------------------------------------------------------ //
    // Core log method
    // ------------------------------------------------------------------ //

    /**
     * Record an activity event.
     *
     * @param int|null $userId     Null for anonymous / system events
     * @param string   $action     Machine-readable action (e.g. 'file_upload')
     * @param array    $context    Optional rich context – see keys below
     *
     * Context keys (all optional):
     *   module           string  – originating app/module
     *   tenant_id        int     – tenant / company ID
     *   resource_type    string  – entity type ('file', 'invoice', …)
     *   resource_id      string  – entity ID
     *   user_role        string  – 'admin' | 'user' | 'system'
     *   old_values       array   – state before the change
     *   new_values       array   – state after the change
     *   readable_message string  – pre-built human-readable description
     *   status           string  – 'success' | 'failure' | 'pending'
     *   data             array   – arbitrary extra metadata (stored in `data` column)
     */
    public static function log(?int $userId, string $action, array $context = []): void
    {
        try {
            $db = Database::getInstance();

            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            [$device, $browser] = self::parseUserAgent($userAgent);

            $module        = $context['module']        ?? null;
            $tenantId      = $context['tenant_id']     ?? null;
            $resourceType  = $context['resource_type'] ?? null;
            $resourceId    = isset($context['resource_id']) ? (string)$context['resource_id'] : null;
            $userRole      = $context['user_role']     ?? self::detectRole($userId);
            $oldValues     = isset($context['old_values'])  ? json_encode($context['old_values'])  : null;
            $newValues     = isset($context['new_values'])  ? json_encode($context['new_values'])  : null;
            $status        = $context['status']        ?? 'success';
            $requestId     = $context['request_id']    ?? self::currentRequestId();
            $readable      = $context['readable_message']
                             ?? self::buildReadableMessage($userId, $action, $context);

            // Merge remaining context into the legacy `data` JSON column
            $extraData = $context['data'] ?? [];
            foreach (['module','tenant_id','resource_type','resource_id','user_role',
                       'old_values','new_values','readable_message','status',
                       'request_id','data'] as $reserved) {
                unset($context[$reserved]);
            }
            $dataPayload = array_merge($context, $extraData);

            $db->insert('activity_logs', [
                'user_id'          => $userId,
                'action'           => $action,
                'module'           => $module,
                'tenant_id'        => $tenantId,
                'resource_type'    => $resourceType,
                'resource_id'      => $resourceId,
                'user_role'        => $userRole,
                'old_values'       => $oldValues,
                'new_values'       => $newValues,
                'readable_message' => $readable,
                'request_id'       => $requestId,
                'device'           => $device,
                'browser'          => $browser,
                'status'           => in_array($status, ['success','failure','pending']) ? $status : 'success',
                'ip_address'       => Security::getClientIp(),
                'user_agent'       => $userAgent,
                'data'             => !empty($dataPayload) ? json_encode($dataPayload) : null,
                'created_at'       => date('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            Logger::error('ActivityLogger: failed to persist event – ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    // Convenience helpers
    // ------------------------------------------------------------------ //

    public static function logCreate(?int $userId, string $module, string $resourceType, $resourceId, array $newValues = [], array $extra = []): void
    {
        self::log($userId, $resourceType . '_created', array_merge([
            'module'        => $module,
            'resource_type' => $resourceType,
            'resource_id'   => $resourceId,
            'new_values'    => $newValues,
        ], $extra));
    }

    public static function logUpdate(?int $userId, string $module, string $resourceType, $resourceId, array $oldValues, array $newValues, array $extra = []): void
    {
        self::log($userId, $resourceType . '_updated', array_merge([
            'module'        => $module,
            'resource_type' => $resourceType,
            'resource_id'   => $resourceId,
            'old_values'    => $oldValues,
            'new_values'    => $newValues,
        ], $extra));
    }

    public static function logDelete(?int $userId, string $module, string $resourceType, $resourceId, array $oldValues = [], array $extra = []): void
    {
        self::log($userId, $resourceType . '_deleted', array_merge([
            'module'        => $module,
            'resource_type' => $resourceType,
            'resource_id'   => $resourceId,
            'old_values'    => $oldValues,
        ], $extra));
    }

    public static function logLogin(?int $userId, array $extra = []): void
    {
        self::log($userId, 'login', array_merge(['module' => 'auth'], $extra));
    }

    public static function logLogout(?int $userId, array $extra = []): void
    {
        self::log($userId, 'logout', array_merge(['module' => 'auth'], $extra));
    }

    public static function logPermissionChange(?int $adminId, string $module, $targetUserId, array $oldPerms, array $newPerms, array $extra = []): void
    {
        self::log($adminId, 'permission_changed', array_merge([
            'module'        => $module,
            'resource_type' => 'user',
            'resource_id'   => $targetUserId,
            'old_values'    => $oldPerms,
            'new_values'    => $newPerms,
            'user_role'     => 'admin',
        ], $extra));
    }

    public static function logFailure(?int $userId, string $action, string $reason, array $extra = []): void
    {
        self::log($userId, $action, array_merge(['status' => 'failure', 'reason' => $reason], $extra));
    }

    // ------------------------------------------------------------------ //
    // Internal helpers
    // ------------------------------------------------------------------ //

    /**
     * Request-ID for correlation across log entries in the same HTTP request.
     */
    private static ?string $currentRequestId = null;

    /**
     * Parse device type and browser name from a User-Agent string.
     *
     * @return array [device, browser]
     */
    private static function parseUserAgent(string $ua): array
    {
        if (empty($ua)) {
            return ['Unknown', 'Unknown'];
        }

        // Device detection (order matters – tablets before phones)
        $device = 'Desktop';
        if (preg_match('/tablet|ipad/i', $ua)) {
            $device = 'Tablet';
        } elseif (preg_match('/mobile|android|iphone|ipod|blackberry|windows phone/i', $ua)) {
            $device = 'Mobile';
        } elseif (preg_match('/bot|crawler|spider|curl|wget/i', $ua)) {
            $device = 'Bot';
        }

        // Browser detection
        $browser = 'Unknown';
        $patterns = [
            'Edge'    => '/Edg\//i',
            'Chrome'  => '/Chrome\/[\d.]+/i',
            'Firefox' => '/Firefox\/[\d.]+/i',
            'Safari'  => '/Version\/[\d.]+ Safari/i',
            'Opera'   => '/OPR\/|Opera\//i',
            'IE'      => '/MSIE |Trident\//i',
        ];
        foreach ($patterns as $name => $pattern) {
            if (preg_match($pattern, $ua)) {
                $browser = $name;
                break;
            }
        }

        return [$device, $browser];
    }

    /**
     * Detect the role of the current user from session or defaults.
     */
    private static function detectRole(?int $userId): string
    {
        if ($userId === null) {
            return 'system';
        }
        try {
            $authUser = Auth::user();
            if ($authUser && isset($authUser['role'])) {
                return $authUser['role'];
            }
            if ($authUser && !empty($authUser['is_admin'])) {
                return 'admin';
            }
        } catch (\Throwable $t) {
            // silently fall through
        }
        return 'user';
    }

    /**
     * Return (or generate) a per-request correlation ID stored in a static
     * property so every log entry from the same PHP request shares it.
     */
    private static function currentRequestId(): string
    {
        if (self::$currentRequestId === null) {
            self::$currentRequestId = bin2hex(random_bytes(16));
        }
        return self::$currentRequestId;
    }

    /**
     * Build a human-readable sentence when none is provided.
     *
     * Examples:
     *   "User #5 performed login"
     *   "User #3 created file #42 in proshare"
     *   "User #3 updated webhook in whatsapp (webhook_url: 'https://old.com' → 'https://new.com')"
     */
    private static function buildReadableMessage(?int $userId, string $action, array $context): string
    {
        // Resolve a display name if possible
        $actor = $userId ? 'User #' . $userId : 'System';

        $resourceType = $context['resource_type'] ?? null;
        $resourceId   = $context['resource_id']   ?? null;
        $module       = $context['module']         ?? null;
        $oldValues    = is_array($context['old_values'] ?? null) ? ($context['old_values'] ?? []) : [];
        $newValues    = is_array($context['new_values'] ?? null) ? ($context['new_values'] ?? []) : [];

        // Convert snake_case action to a readable verb phrase
        $verb = ucwords(str_replace('_', ' ', $action));

        $msg = "{$actor} {$verb}";

        if ($resourceType && $resourceId) {
            $msg .= " {$resourceType} #{$resourceId}";
        } elseif ($resourceType) {
            $msg .= " {$resourceType}";
        }

        if ($module) {
            $msg .= " in {$module}";
        }

        // Append a concise field-change summary when old AND new values are available
        if (!empty($oldValues) && !empty($newValues)) {
            $changes = [];
            foreach ($newValues as $key => $newVal) {
                $oldVal = $oldValues[$key] ?? null;
                // Skip internal/noise keys
                if (in_array($key, ['action', '_auto', 'csrf_token'], true)) {
                    continue;
                }
                $newStr = is_array($newVal) ? json_encode($newVal) : (string)$newVal;
                if ($oldVal === null) {
                    $changes[] = "{$key}: '{$newStr}'";
                } elseif ((string)$oldVal !== (string)$newVal) {
                    $oldStr = is_array($oldVal) ? json_encode($oldVal) : (string)$oldVal;
                    // Truncate long values for readability
                    if (strlen($oldStr) > 60) { $oldStr = substr($oldStr, 0, 57) . '…'; }
                    if (strlen($newStr) > 60) { $newStr = substr($newStr, 0, 57) . '…'; }
                    $changes[] = "{$key}: '{$oldStr}' → '{$newStr}'";
                }
            }
            if (!empty($changes)) {
                $msg .= ' (' . implode(', ', array_slice($changes, 0, 5)) . ')';
            }
        }

        return $msg;
    }
}
