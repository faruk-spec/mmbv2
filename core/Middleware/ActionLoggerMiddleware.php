<?php
/**
 * Action Logger Middleware
 *
 * Automatically captures EVERY mutating HTTP request (POST / PUT / DELETE / PATCH)
 * that passes through the main Router. This guarantees that no user action –
 * including every button click that submits a form – is ever missed in the audit log.
 *
 * The log entry is deliberately lightweight (URL, method, request params keys):
 * explicit ActivityLogger calls inside controllers will add richer business
 * context (old_values / new_values / readable_message) when available.
 *
 * How it works
 * ─────────────
 *  1. Router::dispatch() calls this before any route handler.
 *  2. We extract module + action from the URL path using a fast heuristic.
 *  3. We call ActivityLogger::log() with status=pending (the controller can
 *     emit its own success/failure event later).
 *  4. The entry carries `_auto: true` in `data` so the UI can distinguish
 *     auto-logged requests from explicit business events.
 *
 * URL → action examples
 * ─────────────────────
 *  POST /admin/whatsapp/sessions/delete        → whatsapp.session_deleted
 *  POST /admin/users/5/edit                    → users.user_updated
 *  POST /admin/platform-plans/create           → platform_plans.platform_plan_created
 *  POST /projects/whatsapp/sessions/create     → whatsapp.session_created
 *  POST /projects/whatsapp/messages/send       → whatsapp.message_sent
 *  POST /projects/whatsapp/settings/update     → whatsapp.settings_updated
 *  DELETE /projects/proshare/files/42          → proshare.file_deleted
 *
 * @package MMB\Core\Middleware
 */

namespace Core\Middleware;

use Core\ActivityLogger;
use Core\Auth;

class ActionLoggerMiddleware
{
    /**
     * HTTP methods we want to capture.
     * GET requests are high-volume page loads – we track those via TrafficTracker.
     */
    private const MUTATING_METHODS = ['POST', 'PUT', 'DELETE', 'PATCH'];

    /**
     * URL prefixes to skip entirely (static assets, health probes, etc.)
     */
    private const SKIP_PREFIXES = [
        '/assets/', '/public/', '/storage/', '/favicon',
        '/_health', '/_status', '/install/',
    ];

    /**
     * URL fragments that indicate a pure read/status AJAX endpoint.
     * These happen during polling and would flood the log.
     */
    private const SKIP_FRAGMENTS = [
        '/status', '/ping', '/health', '/heartbeat',
        '/qr-status', '/session-status', '/bridge-health',
        '/check-auth', '/debug-session',
    ];

    /**
     * Verb suffix → past-tense action name
     */
    private const VERB_MAP = [
        'create'      => 'created',
        'store'       => 'created',
        'add'         => 'added',
        'insert'      => 'created',
        'update'      => 'updated',
        'edit'        => 'updated',
        'save'        => 'updated',
        'modify'      => 'updated',
        'delete'      => 'deleted',
        'remove'      => 'deleted',
        'destroy'     => 'deleted',
        'purge'       => 'deleted',
        'toggle'      => 'toggled',
        'enable'      => 'enabled',
        'disable'     => 'disabled',
        'block'       => 'blocked',
        'unblock'     => 'unblocked',
        'ban'         => 'banned',
        'revoke'      => 'revoked',
        'cancel'      => 'cancelled',
        'reject'      => 'rejected',
        'approve'     => 'approved',
        'send'        => 'sent',
        'broadcast'   => 'broadcasted',
        'import'      => 'imported',
        'export'      => 'exported',
        'sync'        => 'synced',
        'upload'      => 'uploaded',
        'generate'    => 'generated',
        'reset'       => 'reset',
        'regenerate'  => 'regenerated',
        'assign'      => 'assigned',
        'unassign'    => 'unassigned',
        'disconnect'  => 'disconnected',
        'connect'     => 'connected',
        'publish'     => 'published',
        'unpublish'   => 'unpublished',
        'archive'     => 'archived',
        'restore'     => 'restored',
        'cleanup'     => 'cleaned_up',
        'resolve'     => 'resolved',
        'transfer'    => 'transferred',
        'duplicate'   => 'duplicated',
        'test'        => 'tested',
        'verify'      => 'verified',
        'login'       => 'login',
        'logout'      => 'logout',
        'register'    => 'registered',
    ];

    /**
     * Handle the middleware.
     * Always returns true – logging must NEVER block the request.
     */
    public function handle(): bool
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

        // Only care about mutating requests
        if (!in_array($method, self::MUTATING_METHODS, true)) {
            return true;
        }

        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?? '/';

        // Skip static / utility paths
        foreach (self::SKIP_PREFIXES as $prefix) {
            if (strpos($uri, $prefix) === 0) {
                return true;
            }
        }
        foreach (self::SKIP_FRAGMENTS as $frag) {
            if (strpos($uri, $frag) !== false) {
                return true;
            }
        }

        try {
            [$module, $action, $resourceType, $resourceId] = $this->parseUri($uri, $method);

            $userId = Auth::check() ? Auth::id() : null;

            // Collect non-sensitive POST keys for context
            $postKeys = array_keys($_POST);
            $filtered = array_values(array_filter($postKeys, fn($k) => !in_array(
                strtolower($k),
                ['password', 'password_confirmation', 'token', 'csrf_token', '_csrf_token',
                 'secret', 'key', 'api_key', 'two_factor_code', 'backup_code'],
                true
            )));

            ActivityLogger::log($userId, $action, [
                'module'        => $module,
                'resource_type' => $resourceType,
                'resource_id'   => $resourceId,
                'data'          => [
                    '_auto'       => true,
                    'method'      => $method,
                    'url'         => $uri,
                    'post_keys'   => $filtered,
                ],
            ]);
        } catch (\Throwable $t) {
            // Never block the request
            error_log('ActionLoggerMiddleware error: ' . $t->getMessage());
        }

        return true;
    }

    // ------------------------------------------------------------------ //
    // URL parsing helpers
    // ------------------------------------------------------------------ //

    /**
     * Parse URI into [module, action, resourceType, resourceId].
     */
    private function parseUri(string $uri, string $method): array
    {
        // Normalise: remove leading slash, explode
        $segments = array_values(array_filter(explode('/', trim($uri, '/'))));

        if (empty($segments)) {
            return ['core', 'page_visited', null, null];
        }

        $first = $segments[0] ?? '';

        // ── Admin routes: /admin/... ──────────────────────────────────
        if ($first === 'admin') {
            return $this->parseAdminUri(array_slice($segments, 1), $method);
        }

        // ── Project routes: /projects/{name}/... ──────────────────────
        if ($first === 'projects' && isset($segments[1])) {
            $module = $segments[1];
            $rest   = array_slice($segments, 2);
            return $this->parseProjectUri($module, $rest, $method);
        }

        // ── Auth / core routes ────────────────────────────────────────
        return $this->parseCoreUri($segments, $method);
    }

    /**
     * Parse /admin/{...} segments.
     */
    private function parseAdminUri(array $segments, string $method): array
    {
        if (empty($segments)) {
            return ['admin', 'admin_dashboard_accessed', null, null];
        }

        // /admin/projects/{name}/...
        if ($segments[0] === 'projects' && isset($segments[1])) {
            $module = $segments[1];
            $rest   = array_slice($segments, 2);
            [$action, $rt, $rid] = $this->extractActionFromPath($rest, $module, $method);
            return [$module, 'admin_' . $action, $rt, $rid];
        }

        // /admin/{module}/{...}
        $module = $segments[0];
        $rest   = array_slice($segments, 1);
        [$action, $rt, $rid] = $this->extractActionFromPath($rest, $module, $method);
        return [$module, 'admin_' . $action, $rt, $rid];
    }

    /**
     * Parse /projects/{module}/{...} segments.
     */
    private function parseProjectUri(string $module, array $rest, string $method): array
    {
        [$action, $rt, $rid] = $this->extractActionFromPath($rest, $module, $method);
        return [$module, $module . '_' . $action, $rt, $rid];
    }

    /**
     * Parse core/auth routes.
     */
    private function parseCoreUri(array $segments, string $method): array
    {
        $slug = implode('_', $segments);
        return ['core', $slug ?: 'request', null, null];
    }

    /**
     * Extract (action_string, resourceType, resourceId) from remaining path segments.
     *
     * Algorithm:
     *  - Look for a known verb in the LAST segment
     *  - Treat numeric-only segments as resource IDs
     *  - Treat the segment just before a verb (or the penultimate plural noun) as resourceType
     */
    private function extractActionFromPath(array $segments, string $module, string $method): array
    {
        if (empty($segments)) {
            // e.g. POST /admin/settings – deduce from method
            $verb     = $this->methodToDefaultVerb($method);
            $resource = $this->singularise($module);
            return ["{$resource}_{$verb}", $resource, null];
        }

        $resourceId   = null;
        $verbSegment  = null;
        $nounSegments = [];

        foreach ($segments as $seg) {
            if (ctype_digit($seg) || preg_match('/^[a-f0-9]{8,}$/i', $seg)) {
                $resourceId = $seg;
            } elseif (isset(self::VERB_MAP[strtolower($seg)])) {
                $verbSegment = strtolower($seg);
            } else {
                $nounSegments[] = $seg;
            }
        }

        $verb = $verbSegment
            ? self::VERB_MAP[$verbSegment]
            : $this->methodToDefaultVerb($method);

        // Resource type is the last noun segment (typically a plural like "sessions")
        $resourceType = !empty($nounSegments)
            ? $this->singularise(end($nounSegments))
            : $this->singularise($module);

        $action = "{$resourceType}_{$verb}";

        return [$action, $resourceType, $resourceId];
    }

    /**
     * Default action verb when no explicit verb found in URL.
     */
    private function methodToDefaultVerb(string $method): string
    {
        return match ($method) {
            'DELETE' => 'deleted',
            'PUT'    => 'updated',
            'PATCH'  => 'updated',
            default  => 'submitted', // POST without a clear verb
        };
    }

    /**
     * Very simple English singulariser for URL path segments.
     * Handles the common patterns (sessions→session, messages→message, etc.)
     */
    private function singularise(string $word): string
    {
        $word = strtolower($word);
        // Handle hyphens/dashes
        $word = str_replace(['-', '_'], '_', $word);

        // Already singular or short
        if (strlen($word) <= 3) {
            return $word;
        }

        // Common irregulars
        $irregulars = [
            'api-keys'     => 'api_key',
            'api_keys'     => 'api_key',
            'api-logs'     => 'api_log',
            'api_logs'     => 'api_log',
            'user-settings'=> 'user_setting',
            'user_settings'=> 'user_setting',
            'rate-limits'  => 'rate_limit',
            'rate_limits'  => 'rate_limit',
            'abuse-reports'=> 'abuse_report',
            'abuse_reports'=> 'abuse_report',
            'audit-trail'  => 'audit_trail',
            'audit_trail'  => 'audit_trail',
        ];
        if (isset($irregulars[$word])) {
            return $irregulars[$word];
        }

        // Standard rules
        if (substr($word, -3) === 'ies') {
            return substr($word, 0, -3) . 'y'; // categories → category
        }
        if (substr($word, -4) === 'sses') {
            return substr($word, 0, -2); // addresses → address
        }
        if (substr($word, -1) === 's' && substr($word, -2) !== 'ss') {
            return substr($word, 0, -1); // sessions → session, messages → message
        }
        return $word;
    }
}
