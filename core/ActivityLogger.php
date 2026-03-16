<?php
/**
 * ActivityLogger – Centralized Audit Trail
 *
 * A drop-in replacement / extension for Core\Logger::activity() that adds:
 *  • Module / tenant / resource tracking
 *  • Before & after value snapshots
 *  • Field-level change detection (changes column)
 *  • Entity context (entity_name)
 *  • Denormalized user name (user_name)
 *  • Human-readable messages using real names
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
 *     'entity_name'     => 'My Photo',
 *     'old_values'      => ['title' => 'Old'],
 *     'new_values'      => ['title' => 'New'],
 *     'readable_message'=> 'Jane updated image #42',
 * ]);
 *
 * // Helpers
 * ActivityLogger::logCreate($userId, 'proshare', 'file', 5, ['name' => 'doc.pdf'], 'doc.pdf');
 * ActivityLogger::logUpdate($userId, 'billx', 'invoice', 12, $before, $after, 'Invoice #12');
 * ActivityLogger::logDelete($userId, 'codexpro', 'snippet', 3, [], 'My Snippet');
 * ActivityLogger::logEnable($userId, 'admin', 'project', 7, 'Payment API');
 * ActivityLogger::logDisable($userId, 'admin', 'project', 7, 'Payment API');
 * ActivityLogger::logLogin($userId);
 * ActivityLogger::logLogout($userId);
 * ActivityLogger::logApiKeyCreated($userId, $keyId, 'My Key');
 * ActivityLogger::logApiKeyRevoked($userId, $keyId, 'My Key');
 * ActivityLogger::logSettingsUpdated($userId, 'whatsapp', $before, $after);
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
     *   entity_name      string  – human-readable name of the entity
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
            $entityName    = $context['entity_name']   ?? null;
            $userRole      = $context['user_role']     ?? self::detectRole($userId);
            $oldValues     = isset($context['old_values'])  ? $context['old_values']  : null;
            $newValues     = isset($context['new_values'])  ? $context['new_values']  : null;
            $status        = $context['status']        ?? 'success';
            $requestId     = $context['request_id']    ?? self::currentRequestId();

            // Compute field-level changes when both snapshots are available
            $changes = null;
            if (is_array($oldValues) && is_array($newValues)) {
                $changes = self::computeChanges($oldValues, $newValues);
            }

            // Resolve and store the user's display name for denormalization
            $userName = $context['user_name'] ?? self::resolveUserName($userId);

            $readable = $context['readable_message']
                        ?? self::buildReadableMessage($userId, $userName, $action, $context, $changes);

            // Merge remaining context into the legacy `data` JSON column
            $extraData = $context['data'] ?? [];
            foreach (['module','tenant_id','resource_type','resource_id','entity_name',
                       'user_role','old_values','new_values','readable_message','status',
                       'request_id','data','user_name'] as $reserved) {
                unset($context[$reserved]);
            }
            $dataPayload = array_merge($context, $extraData);

            $db->insert('activity_logs', [
                'user_id'          => $userId,
                'user_name'        => $userName,
                'action'           => $action,
                'module'           => $module,
                'tenant_id'        => $tenantId,
                'resource_type'    => $resourceType,
                'resource_id'      => $resourceId,
                'entity_name'      => $entityName,
                'user_role'        => $userRole,
                'old_values'       => $oldValues !== null ? json_encode($oldValues) : null,
                'new_values'       => $newValues !== null ? json_encode($newValues) : null,
                'changes'          => $changes !== null ? json_encode($changes) : null,
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

    public static function logCreate(?int $userId, string $module, string $resourceType, $resourceId, array $newValues = [], string $entityName = '', array $extra = []): void
    {
        self::log($userId, $resourceType . '_created', array_merge([
            'module'        => $module,
            'resource_type' => $resourceType,
            'resource_id'   => $resourceId,
            'entity_name'   => $entityName ?: null,
            'new_values'    => $newValues,
        ], $extra));
    }

    public static function logUpdate(?int $userId, string $module, string $resourceType, $resourceId, array $oldValues, array $newValues, string $entityName = '', array $extra = []): void
    {
        self::log($userId, $resourceType . '_updated', array_merge([
            'module'        => $module,
            'resource_type' => $resourceType,
            'resource_id'   => $resourceId,
            'entity_name'   => $entityName ?: null,
            'old_values'    => $oldValues,
            'new_values'    => $newValues,
        ], $extra));
    }

    public static function logDelete(?int $userId, string $module, string $resourceType, $resourceId, array $oldValues = [], string $entityName = '', array $extra = []): void
    {
        self::log($userId, $resourceType . '_deleted', array_merge([
            'module'        => $module,
            'resource_type' => $resourceType,
            'resource_id'   => $resourceId,
            'entity_name'   => $entityName ?: null,
            'old_values'    => $oldValues,
        ], $extra));
    }

    public static function logEnable(?int $userId, string $module, string $resourceType, $resourceId, string $entityName = '', array $extra = []): void
    {
        self::log($userId, $resourceType . '_enabled', array_merge([
            'module'        => $module,
            'resource_type' => $resourceType,
            'resource_id'   => $resourceId,
            'entity_name'   => $entityName ?: null,
        ], $extra));
    }

    public static function logDisable(?int $userId, string $module, string $resourceType, $resourceId, string $entityName = '', array $extra = []): void
    {
        self::log($userId, $resourceType . '_disabled', array_merge([
            'module'        => $module,
            'resource_type' => $resourceType,
            'resource_id'   => $resourceId,
            'entity_name'   => $entityName ?: null,
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

    public static function logApiKeyCreated(?int $userId, $keyId, string $keyName = '', array $extra = []): void
    {
        self::log($userId, 'api_key_created', array_merge([
            'module'        => 'api',
            'resource_type' => 'api_key',
            'resource_id'   => $keyId,
            'entity_name'   => $keyName ?: null,
        ], $extra));
    }

    public static function logApiKeyRevoked(?int $userId, $keyId, string $keyName = '', array $extra = []): void
    {
        self::log($userId, 'api_key_revoked', array_merge([
            'module'        => 'api',
            'resource_type' => 'api_key',
            'resource_id'   => $keyId,
            'entity_name'   => $keyName ?: null,
        ], $extra));
    }

    public static function logSettingsUpdated(?int $userId, string $module, array $oldValues, array $newValues, array $extra = []): void
    {
        self::log($userId, 'settings_updated', array_merge([
            'module'        => $module,
            'resource_type' => 'settings',
            'old_values'    => $oldValues,
            'new_values'    => $newValues,
        ], $extra));
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
    // Field-level change detection
    // ------------------------------------------------------------------ //

    /**
     * Compute a field-level diff between two state snapshots.
     *
     * Returns only the fields that changed, in the format:
     *   ["field" => ["old" => $oldVal, "new" => $newVal]]
     *
     * Fields that are identical (after string-casting) are omitted.
     * Keys listed in $ignoreKeys are always skipped.
     *
     * @param array $oldValues State before the change
     * @param array $newValues State after the change
     * @param array $ignoreKeys Fields to always exclude from the diff
     * @return array|null Associative diff array, or null if nothing changed
     */
    public static function computeChanges(array $oldValues, array $newValues, array $ignoreKeys = []): ?array
    {
        $skip = array_merge(
            ['updated_at', 'created_at', 'password', 'remember_token', '_token', 'csrf_token'],
            $ignoreKeys
        );

        $changes = [];
        // Detect modified and new fields
        foreach ($newValues as $key => $newVal) {
            if (in_array($key, $skip, true)) {
                continue;
            }
            $oldVal = $oldValues[$key] ?? null;
            $newStr = is_array($newVal) ? json_encode($newVal) : (string)$newVal;
            $oldStr = $oldVal !== null ? (is_array($oldVal) ? json_encode($oldVal) : (string)$oldVal) : null;

            if ($oldStr !== $newStr) {
                $changes[$key] = ['old' => $oldVal, 'new' => $newVal];
            }
        }
        // Detect removed fields
        foreach ($oldValues as $key => $oldVal) {
            if (in_array($key, $skip, true) || array_key_exists($key, $newValues)) {
                continue;
            }
            $changes[$key] = ['old' => $oldVal, 'new' => null];
        }

        return empty($changes) ? null : $changes;
    }

    // ------------------------------------------------------------------ //
    // Internal helpers
    // ------------------------------------------------------------------ //

    /**
     * Request-ID for correlation across log entries in the same HTTP request.
     */
    private static ?string $currentRequestId = null;

    /**
     * Cache of resolved user names within the same request.
     */
    private static array $userNameCache = [];

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
     * Resolve the display name for a user ID.
     * Checks Auth session first, then database. Results are cached per-request.
     */
    private static function resolveUserName(?int $userId): ?string
    {
        if ($userId === null) {
            return null;
        }

        if (isset(self::$userNameCache[$userId])) {
            return self::$userNameCache[$userId];
        }

        // Try session first (zero DB cost on most requests)
        try {
            $authUser = Auth::user();
            if ($authUser && (int)($authUser['id'] ?? 0) === $userId && !empty($authUser['name'])) {
                self::$userNameCache[$userId] = $authUser['name'];
                return $authUser['name'];
            }
        } catch (\Throwable $t) {
            // silently fall through
        }

        // Fallback to DB lookup
        try {
            $db  = Database::getInstance();
            $row = $db->fetch("SELECT name FROM users WHERE id = ? LIMIT 1", [$userId]);
            $name = $row['name'] ?? null;
            self::$userNameCache[$userId] = $name;
            return $name;
        } catch (\Throwable $t) {
            return null;
        }
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
     * Uses the actual user name (if resolved) and entity name (if available).
     *
     * Examples:
     *   "Faruque Ahmed created project 'Payment API'"
     *   "Faruque Ahmed changed project name from 'pink' to 'red'"
     *   "Faruque Ahmed disabled project 'Sheet API'"
     *   "System performed login"
     */
    private static function buildReadableMessage(?int $userId, ?string $userName, string $action, array $context, ?array $changes): string
    {
        // Actor display
        if ($userName) {
            $actor = $userName;
        } elseif ($userId) {
            $actor = 'User #' . $userId;
        } else {
            $actor = 'System';
        }

        $resourceType = $context['resource_type'] ?? null;
        $resourceId   = $context['resource_id']   ?? null;
        $entityName   = $context['entity_name']   ?? null;
        $module       = $context['module']         ?? null;

        // Map known action patterns to friendly verbs/messages
        $lowerAction = strtolower($action);

        // Handle structured action types
        if ($lowerAction === 'login') {
            return "{$actor} logged in";
        }
        if ($lowerAction === 'logout') {
            return "{$actor} logged out";
        }
        if ($lowerAction === 'api_key_created') {
            $name = $entityName ? "'{$entityName}'" : ($resourceId ? "#{$resourceId}" : 'an API key');
            return "{$actor} created API key {$name}";
        }
        if ($lowerAction === 'api_key_revoked') {
            $name = $entityName ? "'{$entityName}'" : ($resourceId ? "#{$resourceId}" : 'an API key');
            return "{$actor} revoked API key {$name}";
        }
        if ($lowerAction === 'settings_updated') {
            $where = $module ? " in {$module}" : '';
            return "{$actor} updated settings{$where}";
        }

        // Detect action verb from suffix patterns
        $verbMap = [
            '_created'  => 'created',
            '_updated'  => 'updated',
            '_deleted'  => 'deleted',
            '_enabled'  => 'enabled',
            '_disabled' => 'disabled',
        ];
        $verb = null;
        $detectedType = $resourceType;
        foreach ($verbMap as $suffix => $v) {
            if (str_ends_with($lowerAction, $suffix)) {
                $verb = $v;
                // Derive resource type from action prefix if not in context
                if (!$detectedType) {
                    $detectedType = str_replace($suffix, '', $lowerAction);
                    $detectedType = str_replace('_', ' ', $detectedType);
                }
                break;
            }
        }

        // Build entity reference string
        $entityRef = self::buildEntityRef($detectedType, $resourceId, $entityName);

        if ($verb && $entityRef) {
            $msg = "{$actor} {$verb} {$entityRef}";
        } elseif ($verb) {
            $msg = "{$actor} {$verb}";
            if ($module) {
                $msg .= " in {$module}";
            }
        } else {
            // Fallback: convert snake_case action to readable phrase
            $readable = ucwords(str_replace('_', ' ', $action));
            $msg = "{$actor} performed {$readable}";
            if ($entityRef) {
                $msg .= " on {$entityRef}";
            }
        }

        // Append field-change summary for updated events (max 3 fields for brevity)
        if ($verb === 'updated' && !empty($changes)) {
            $parts = [];
            $count = 0;
            foreach ($changes as $field => $diff) {
                if ($count >= 3) {
                    $parts[] = '…';
                    break;
                }
                $oldStr = self::truncate(is_array($diff['old']) ? json_encode($diff['old']) : (string)($diff['old'] ?? ''));
                $newStr = self::truncate(is_array($diff['new']) ? json_encode($diff['new']) : (string)($diff['new'] ?? ''));
                $label  = str_replace('_', ' ', $field);
                $parts[] = "{$label} from '{$oldStr}' to '{$newStr}'";
                $count++;
            }
            if (!empty($parts)) {
                // Replace generic "updated X" with a specific "changed X [fields]"
                $msg = "{$actor} changed {$entityRef} " . implode(', ', $parts);
            }
        }

        return $msg;
    }

    /**
     * Build a human-readable entity reference string.
     * e.g. "project 'Payment API'" or "user #42"
     */
    private static function buildEntityRef(?string $type, $id, ?string $name): string
    {
        if (!$type) {
            return '';
        }
        $label = str_replace('_', ' ', $type);
        if ($name) {
            return "{$label} '{$name}'";
        }
        if ($id !== null && $id !== '') {
            return "{$label} #{$id}";
        }
        return $label;
    }

    /**
     * Truncate a string for display in readable messages.
     */
    private static function truncate(string $str, int $max = 50): string
    {
        return mb_strlen($str) > $max ? mb_substr($str, 0, $max - 1) . '…' : $str;
    }
}

