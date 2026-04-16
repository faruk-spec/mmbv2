<?php
/**
 * Lightweight SSE Notification Endpoint
 *
 * "Fire-and-close" pattern: check once for new notifications, emit events,
 * then exit immediately. The browser's EventSource reconnects automatically
 * using the `retry:` interval. This avoids holding PHP-FPM workers open.
 *
 * @package MMB\Controllers
 */

namespace Controllers;

use Core\Auth;
use Core\Database;
use Core\Notification;

class NotificationStreamController extends BaseController
{
    /**
     * GET /notifications/stream
     *
     * Checks once for notifications newer than last_id, emits them as SSE
     * events, then exits. The browser reconnects every POLL_INTERVAL_MS ms.
     */
    public function stream(): void
    {
        // Auth check — 401 instead of a redirect so EventSource onerror fires
        if (!Auth::check()) {
            http_response_code(401);
            exit;
        }

        // Disable output buffering so events reach the client immediately
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');   // nginx: disable proxy buffering
        header('Connection: close');        // fire-and-close: no keep-alive

        // Reconnect interval sent to the browser (15 seconds)
        $pollMs = 15000;
        echo "retry: {$pollMs}\n\n";

        $userId = Auth::id();
        $lastId = max(0, (int) ($_GET['last_id'] ?? 0));

        try {
            $db   = Database::getInstance();
            $rows = $db->fetchAll(
                "SELECT * FROM notifications WHERE user_id = ? AND id > ? AND is_read = 0 ORDER BY id ASC LIMIT 10",
                [$userId, $lastId]
            );

            if (!empty($rows)) {
                $unreadCount = Notification::getUnreadCount($userId);

                foreach ($rows as $row) {
                    $rowId       = (int) $row['id'];
                    $row['data'] = !empty($row['data']) ? json_decode($row['data'], true) : null;

                    $payload = json_encode([
                        'type'         => 'notification',
                        'notification' => $row,
                        'unread_count' => $unreadCount,
                    ]);

                    echo "id: {$rowId}\ndata: {$payload}\n\n";
                }
            }
        } catch (\Exception $e) {
            // Non-fatal — the browser will reconnect on next poll
        }

        // SSE comment keeps proxies happy on empty responses
        echo ": ok\n\n";
        flush();
        exit;
    }
}
