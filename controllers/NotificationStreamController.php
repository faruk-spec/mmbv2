<?php
/**
 * Server-Sent Events (SSE) Notification Stream Controller
 *
 * Streams new notifications to the client in real-time using SSE.
 * The client reconnects automatically after ~55 seconds so PHP workers
 * are never held indefinitely.
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
     * Streams SSE events with new notifications for the logged-in user.
     * Query param: last_id (int) – only return notifications newer than this ID.
     */
    public function stream(): void
    {
        // Auth check — respond with 401 instead of redirecting (SSE clients handle this via onerror)
        if (!Auth::check()) {
            http_response_code(401);
            exit;
        }

        // Disable every layer of output buffering so events flush immediately
        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');   // Disable nginx buffering
        header('Connection: keep-alive');

        $userId = Auth::id();
        $lastId = max(0, (int) ($_GET['last_id'] ?? 0));
        $start  = time();
        $maxAge = 55; // seconds — ask client to reconnect after this

        // Tell the client to retry in 3 seconds on disconnection
        echo "retry: 3000\n\n";
        flush();

        while (true) {
            // Stop if the connection was closed by the client
            if (connection_aborted()) {
                break;
            }

            // Stop and let the client reconnect after maxAge
            if ((time() - $start) >= $maxAge) {
                echo "event: reconnect\ndata: {\"type\":\"reconnect\"}\n\n";
                flush();
                break;
            }

            try {
                $db    = Database::getInstance();
                $rows  = $db->fetchAll(
                    "SELECT * FROM notifications WHERE user_id = ? AND id > ? ORDER BY id ASC LIMIT 10",
                    [$userId, $lastId]
                );

                foreach ($rows as $row) {
                    $lastId = (int) $row['id'];
                    $row['data'] = !empty($row['data']) ? json_decode($row['data'], true) : null;

                    $unreadCount = Notification::getUnreadCount($userId);

                    $payload = json_encode([
                        'type'         => 'notification',
                        'notification' => $row,
                        'unread_count' => $unreadCount,
                    ]);

                    echo "id: {$lastId}\ndata: {$payload}\n\n";
                    flush();
                }
            } catch (\Exception $e) {
                // Non-fatal — just skip this tick
            }

            // SSE comment lines (starting with ':') act as keep-alive messages.
            // They carry no data but prevent proxies and load balancers from closing
            // what appears to be an idle TCP connection, while using minimal bandwidth.
            echo ": heartbeat\n\n";
            flush();

            sleep(5);
        }

        exit;
    }
}
