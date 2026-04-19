<?php
/**
 * CodeXPro Collaboration Controller
 *
 * Handles real-time collaboration via Server-Sent Events (SSE),
 * presence heartbeats, change push, and invite/revoke.
 *
 * @package MMB\Projects\CodeXPro\Controllers
 */

namespace Projects\CodeXPro\Controllers;

use Core\Database;
use Core\Auth;
use Core\Security;

class CollaborationController
{
    /** Palette of per-user avatar colours (CSS hex) */
    private const COLORS = [
        '#00f0ff','#ff6b9d','#a78bfa','#34d399','#fb923c',
        '#60a5fa','#f9a8d4','#6ee7b7','#fcd34d','#f87171',
    ];

    /** How many seconds a session is considered "active" */
    private const PRESENCE_TTL = 30;

    /** Max SSE stream duration (seconds) before client must reconnect */
    private const SSE_MAX_TIME = 55;

    /** SSE heartbeat interval (seconds) */
    private const SSE_HEARTBEAT = 8;

    /** Seconds to keep change rows in the buffer */
    private const CHANGE_BUFFER_TTL = 900; // 15 min

    // ──────────────────────────────────────────────────────────────────
    // SSE Stream  –  GET /projects/codexpro/collab/{id}/stream?since=N
    // ──────────────────────────────────────────────────────────────────
    public function stream(int $projectId): void
    {
        $user = Auth::user();
        $db   = Database::getInstance();

        if (!$this->canAccess($db, $projectId, $user['id'])) {
            http_response_code(403);
            echo "data: " . json_encode(['error' => 'Forbidden']) . "\n\n";
            return;
        }

        // Register / refresh presence
        $this->upsertSession($db, $projectId, $user['id']);

        // SSE headers
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache, no-store');
        header('X-Accel-Buffering: no'); // Nginx: disable buffering
        header('Connection: keep-alive');

        // Disable output buffering at every level
        while (ob_get_level()) { ob_end_clean(); }
        @ini_set('output_buffering', 'off');
        @ini_set('zlib.output_compression', 0);

        $since      = max(0, (int)($_GET['since'] ?? 0));
        $start      = time();
        $lastHb     = $start;

        // Send initial presence snapshot
        $this->ssePresence($db, $projectId, $user['id']);

        while (true) {
            if (connection_aborted()) break;
            $now = time();
            if ($now - $start >= self::SSE_MAX_TIME) break;

            // Poll changes newer than $since
            $rows = $db->fetchAll(
                "SELECT id, user_id, type, payload, seq
                 FROM codexpro_collab_changes
                 WHERE project_id = ?
                   AND user_id   != ?
                   AND seq        > ?
                 ORDER BY seq ASC
                 LIMIT 30",
                [$projectId, $user['id'], $since]
            );

            foreach ($rows as $row) {
                $payload = json_decode($row['payload'], true) ?? [];
                $payload['_user_id'] = (int)$row['user_id'];
                $payload['_type']    = $row['type'];
                $payload['_seq']     = (int)$row['seq'];
                $since = max($since, (int)$row['seq']);
                $this->sseEmit('change', $payload);
            }

            // Heartbeat + presence refresh every SSE_HEARTBEAT seconds
            if ($now - $lastHb >= self::SSE_HEARTBEAT) {
                $lastHb = $now;
                $this->upsertSession($db, $projectId, $user['id']);
                $this->ssePresence($db, $projectId, $user['id']);
                $this->purgeOldChanges($db, $projectId);
            }

            $this->sseEmit('ping', ['t' => $now, 'since' => $since]);

            flush();
            usleep(800000); // 0.8 s poll interval
        }

        // Final: remove stale session
        $this->removeSession($db, $projectId, $user['id']);
        $this->sseEmit('disconnect', ['user_id' => $user['id']]);
        flush();
    }

    // ──────────────────────────────────────────────────────────────────
    // Push Change  –  POST /projects/codexpro/collab/{id}/push
    // Body: { type, payload, seq_hint }
    // ──────────────────────────────────────────────────────────────────
    public function push(int $projectId): void
    {
        header('Content-Type: application/json');

        $user = Auth::user();
        $db   = Database::getInstance();

        if (!$this->validateCsrf()) {
            echo json_encode(['success' => false, 'error' => 'CSRF']);
            return;
        }
        if (!$this->canAccess($db, $projectId, $user['id'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Forbidden']);
            return;
        }

        $raw  = file_get_contents('php://input');
        $body = json_decode($raw, true) ?? [];

        $allowed = ['html', 'css', 'js', 'cursor', 'meta'];
        $type    = in_array($body['type'] ?? '', $allowed, true) ? $body['type'] : 'meta';
        $payload = $body['payload'] ?? [];

        // Sanitise payload: allow only specific keys per type
        $clean = $this->sanitizePayload($type, $payload);

        // Get next sequence number for this project
        $maxSeq = (int)($db->fetch(
            "SELECT COALESCE(MAX(seq),0) as m FROM codexpro_collab_changes WHERE project_id = ?",
            [$projectId]
        )['m'] ?? 0);
        $seq = $maxSeq + 1;

        $db->insert('codexpro_collab_changes', [
            'project_id' => $projectId,
            'user_id'    => $user['id'],
            'type'       => $type,
            'payload'    => json_encode($clean),
            'seq'        => $seq,
        ]);

        // Update cursor in session if it's a cursor event
        if ($type === 'cursor' && isset($clean['line'], $clean['ch'], $clean['tab'])) {
            $db->query(
                "UPDATE codexpro_collab_sessions
                 SET cursor_line = ?, cursor_ch = ?, active_tab = ?, last_seen = NOW()
                 WHERE project_id = ? AND user_id = ?",
                [(int)$clean['line'], (int)$clean['ch'], $clean['tab'], $projectId, $user['id']]
            );
        } else {
            $this->upsertSession($db, $projectId, $user['id']);
        }

        echo json_encode(['success' => true, 'seq' => $seq]);
    }

    // ──────────────────────────────────────────────────────────────────
    // Presence heartbeat  –  POST /projects/codexpro/collab/{id}/heartbeat
    // ──────────────────────────────────────────────────────────────────
    public function heartbeat(int $projectId): void
    {
        header('Content-Type: application/json');

        $user = Auth::user();
        $db   = Database::getInstance();

        if (!$this->canAccess($db, $projectId, $user['id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $this->upsertSession($db, $projectId, $user['id']);

        $collaborators = $this->getActiveCollaborators($db, $projectId, $user['id']);

        echo json_encode(['success' => true, 'collaborators' => $collaborators]);
    }

    // ──────────────────────────────────────────────────────────────────
    // Invite collaborator  –  POST /projects/codexpro/collab/{id}/invite
    // Body: { email, can_edit }
    // ──────────────────────────────────────────────────────────────────
    public function invite(int $projectId): void
    {
        header('Content-Type: application/json');

        $user = Auth::user();
        $db   = Database::getInstance();

        if (!$this->validateCsrf()) {
            echo json_encode(['success' => false, 'error' => 'CSRF']);
            return;
        }

        // Must be owner
        $project = $db->fetch(
            "SELECT id, name, collab_token FROM codexpro_projects WHERE id = ? AND user_id = ?",
            [$projectId, $user['id']]
        );
        if (!$project) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Only the project owner can invite collaborators']);
            return;
        }

        $raw    = file_get_contents('php://input');
        $body   = json_decode($raw, true) ?? [];
        $email  = trim($body['email'] ?? '');
        $canEdit = !empty($body['can_edit']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Invalid email address']);
            return;
        }

        // Find the invitee in the main users table
        $mainDb = \Core\Database::getInstance();
        $invitee = $mainDb->fetch("SELECT id, name FROM users WHERE email = ?", [$email]);

        if (!$invitee) {
            echo json_encode(['success' => false, 'error' => 'No user found with that email']);
            return;
        }
        if ((int)$invitee['id'] === (int)$user['id']) {
            echo json_encode(['success' => false, 'error' => 'You cannot invite yourself']);
            return;
        }

        // Upsert in project_shares
        $existing = $db->fetch(
            "SELECT id FROM codexpro_project_shares WHERE project_id = ? AND shared_with_user_id = ?",
            [$projectId, $invitee['id']]
        );
        if ($existing) {
            $db->query(
                "UPDATE codexpro_project_shares SET can_edit = ? WHERE id = ?",
                [$canEdit ? 1 : 0, $existing['id']]
            );
        } else {
            $db->insert('codexpro_project_shares', [
                'project_id'          => $projectId,
                'shared_with_user_id' => $invitee['id'],
                'can_edit'            => $canEdit ? 1 : 0,
                'created_at'          => date('Y-m-d H:i:s'),
            ]);
        }

        // Ensure a collab_token exists on the project (for shareable link)
        if (empty($project['collab_token'])) {
            $token = bin2hex(random_bytes(24));
            $db->query(
                "UPDATE codexpro_projects SET collab_token = ? WHERE id = ?",
                [$token, $projectId]
            );
            $project['collab_token'] = $token;
        }

        echo json_encode([
            'success'       => true,
            'invitee'       => ['id' => $invitee['id'], 'name' => $invitee['name']],
            'collab_link'   => '/projects/codexpro/editor/' . $projectId . '?token=' . $project['collab_token'],
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // Revoke collaborator  –  POST /projects/codexpro/collab/{id}/revoke
    // Body: { user_id }
    // ──────────────────────────────────────────────────────────────────
    public function revoke(int $projectId): void
    {
        header('Content-Type: application/json');

        $user = Auth::user();
        $db   = Database::getInstance();

        if (!$this->validateCsrf()) {
            echo json_encode(['success' => false, 'error' => 'CSRF']);
            return;
        }

        // Must be owner
        $project = $db->fetch(
            "SELECT id FROM codexpro_projects WHERE id = ? AND user_id = ?",
            [$projectId, $user['id']]
        );
        if (!$project) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Forbidden']);
            return;
        }

        $raw      = file_get_contents('php://input');
        $body     = json_decode($raw, true) ?? [];
        $revokeId = (int)($body['user_id'] ?? 0);

        if ($revokeId < 1) {
            echo json_encode(['success' => false, 'error' => 'Invalid user_id']);
            return;
        }

        $db->query(
            "DELETE FROM codexpro_project_shares WHERE project_id = ? AND shared_with_user_id = ?",
            [$projectId, $revokeId]
        );
        $db->query(
            "DELETE FROM codexpro_collab_sessions WHERE project_id = ? AND user_id = ?",
            [$projectId, $revokeId]
        );

        echo json_encode(['success' => true]);
    }

    // ──────────────────────────────────────────────────────────────────
    // List collaborators  –  GET /projects/codexpro/collab/{id}/members
    // ──────────────────────────────────────────────────────────────────
    public function members(int $projectId): void
    {
        header('Content-Type: application/json');

        $user = Auth::user();
        $db   = Database::getInstance();

        if (!$this->canAccess($db, $projectId, $user['id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        // Shares list (invited members)
        $shares = $db->fetchAll(
            "SELECT s.shared_with_user_id AS user_id, s.can_edit, s.created_at
             FROM codexpro_project_shares s
             WHERE s.project_id = ? AND s.shared_with_user_id IS NOT NULL",
            [$projectId]
        );

        $result = [];
        if (!empty($shares)) {
            $ids = array_column($shares, 'user_id');
            $ph  = implode(',', array_fill(0, count($ids), '?'));
            $users = \Core\Database::getInstance()->fetchAll(
                "SELECT id, name, email FROM users WHERE id IN ($ph)",
                $ids
            );
            $umap = [];
            foreach ($users as $u) { $umap[$u['id']] = $u; }

            foreach ($shares as $s) {
                $uid = (int)$s['user_id'];
                $result[] = [
                    'user_id'  => $uid,
                    'name'     => $umap[$uid]['name']  ?? 'Unknown',
                    'email'    => $umap[$uid]['email'] ?? '',
                    'can_edit' => (bool)$s['can_edit'],
                    'online'   => $this->isOnline($db, $projectId, $uid),
                ];
            }
        }

        echo json_encode(['success' => true, 'members' => $result]);
    }

    // ══════════════════════════════════════════════════════════════════
    // Private helpers
    // ══════════════════════════════════════════════════════════════════

    /** Check if a user can access a project (owner OR share) */
    private function canAccess(Database $db, int $projectId, int $userId): bool
    {
        $owner = $db->fetch(
            "SELECT id FROM codexpro_projects WHERE id = ? AND user_id = ?",
            [$projectId, $userId]
        );
        if ($owner) return true;

        // Check via collab_token query param (public invite link)
        $token = Security::sanitize($_GET['token'] ?? '');
        if ($token !== '') {
            $t = $db->fetch(
                "SELECT id FROM codexpro_projects WHERE id = ? AND collab_token = ?",
                [$projectId, $token]
            );
            if ($t) return true;
        }

        $share = $db->fetch(
            "SELECT id FROM codexpro_project_shares WHERE project_id = ? AND shared_with_user_id = ?",
            [$projectId, $userId]
        );
        return (bool)$share;
    }

    private function upsertSession(Database $db, int $projectId, int $userId): void
    {
        $colorIdx = $userId % count(self::COLORS);
        $color    = self::COLORS[$colorIdx];
        $db->query(
            "INSERT INTO codexpro_collab_sessions (project_id, user_id, color, last_seen)
             VALUES (?, ?, ?, NOW())
             ON DUPLICATE KEY UPDATE color = ?, last_seen = NOW()",
            [$projectId, $userId, $color, $color]
        );
    }

    private function removeSession(Database $db, int $projectId, int $userId): void
    {
        $db->query(
            "DELETE FROM codexpro_collab_sessions WHERE project_id = ? AND user_id = ?",
            [$projectId, $userId]
        );
    }

    private function getActiveCollaborators(Database $db, int $projectId, int $selfId): array
    {
        $ttl  = self::PRESENCE_TTL;
        $rows = $db->fetchAll(
            "SELECT cs.user_id, cs.color, cs.cursor_line, cs.cursor_ch, cs.active_tab
             FROM codexpro_collab_sessions cs
             WHERE cs.project_id = ?
               AND cs.user_id   != ?
               AND cs.last_seen  > DATE_SUB(NOW(), INTERVAL ? SECOND)",
            [$projectId, $selfId, $ttl]
        );
        if (empty($rows)) return [];

        $ids = array_column($rows, 'user_id');
        $ph  = implode(',', array_fill(0, count($ids), '?'));
        $users = \Core\Database::getInstance()->fetchAll(
            "SELECT id, name FROM users WHERE id IN ($ph)",
            $ids
        );
        $umap = [];
        foreach ($users as $u) { $umap[(int)$u['id']] = $u['name']; }

        $result = [];
        foreach ($rows as $r) {
            $uid = (int)$r['user_id'];
            $result[] = [
                'user_id'     => $uid,
                'name'        => $umap[$uid] ?? 'Unknown',
                'color'       => $r['color'],
                'cursor_line' => (int)$r['cursor_line'],
                'cursor_ch'   => (int)$r['cursor_ch'],
                'active_tab'  => $r['active_tab'],
            ];
        }
        return $result;
    }

    private function isOnline(Database $db, int $projectId, int $userId): bool
    {
        $ttl = self::PRESENCE_TTL;
        $row = $db->fetch(
            "SELECT id FROM codexpro_collab_sessions
             WHERE project_id = ? AND user_id = ?
               AND last_seen > DATE_SUB(NOW(), INTERVAL ? SECOND)",
            [$projectId, $userId, $ttl]
        );
        return (bool)$row;
    }

    private function ssePresence(Database $db, int $projectId, int $selfId): void
    {
        $collaborators = $this->getActiveCollaborators($db, $projectId, $selfId);
        $this->sseEmit('presence', ['collaborators' => $collaborators]);
    }

    /** @param mixed $data */
    private function sseEmit(string $event, $data): void
    {
        echo "event: " . $event . "\n";
        echo "data: "  . json_encode($data) . "\n\n";
    }

    private function purgeOldChanges(Database $db, int $projectId): void
    {
        $ttl = self::CHANGE_BUFFER_TTL;
        $db->query(
            "DELETE FROM codexpro_collab_changes
             WHERE project_id = ? AND created_at < DATE_SUB(NOW(), INTERVAL ? SECOND)",
            [$projectId, $ttl]
        );
    }

    private function sanitizePayload(string $type, array $payload): array
    {
        switch ($type) {
            case 'html':
            case 'css':
            case 'js':
                return ['content' => (string)($payload['content'] ?? '')];
            case 'cursor':
                return [
                    'line' => max(0, (int)($payload['line'] ?? 0)),
                    'ch'   => max(0, (int)($payload['ch']   ?? 0)),
                    'tab'  => in_array($payload['tab'] ?? '', ['html','css','js'], true)
                              ? $payload['tab'] : 'html',
                ];
            case 'meta':
                return ['name' => substr(Security::sanitize($payload['name'] ?? ''), 0, 120)];
            default:
                return [];
        }
    }

    private function validateCsrf(): bool
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN']
               ?? $_POST['_csrf_token']
               ?? '';
        return Security::verifyCsrfToken($token);
    }
}
