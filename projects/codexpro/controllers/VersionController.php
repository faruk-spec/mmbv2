<?php
/**
 * CodeXPro Version Controller
 *
 * Manages project version history – list, view, restore, and label.
 *
 * @package MMB\Projects\CodeXPro\Controllers
 */

namespace Projects\CodeXPro\Controllers;

use Core\Database;
use Core\Auth;
use Core\Security;

class VersionController
{
    /** Maximum snapshots kept per project */
    private const MAX_VERSIONS = 50;

    // ──────────────────────────────────────────────────────────────────
    // List versions  –  GET /projects/codexpro/versions/{id}
    // ──────────────────────────────────────────────────────────────────
    public function index(int $projectId): void
    {
        header('Content-Type: application/json');

        $user = Auth::user();
        $db   = Database::getInstance();

        if (!$this->canAccess($db, $projectId, $user['id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $versions = $db->fetchAll(
            "SELECT v.id, v.version_num, v.label, v.created_at,
                    u.name AS author
             FROM codexpro_project_versions v
             LEFT JOIN users u ON u.id = v.user_id
             WHERE v.project_id = ?
             ORDER BY v.version_num DESC
             LIMIT 50",
            [$projectId]
        );

        echo json_encode(['success' => true, 'versions' => $versions]);
    }

    // ──────────────────────────────────────────────────────────────────
    // Get a single version's code  –  GET /projects/codexpro/versions/{id}/get?v=N
    // ──────────────────────────────────────────────────────────────────
    public function get(int $projectId): void
    {
        header('Content-Type: application/json');

        $user = Auth::user();
        $db   = Database::getInstance();

        if (!$this->canAccess($db, $projectId, $user['id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $versionId = (int)($_GET['v'] ?? 0);
        $row = $db->fetch(
            "SELECT id, version_num, label, html_content, css_content, js_content, created_at
             FROM codexpro_project_versions
             WHERE project_id = ? AND id = ?",
            [$projectId, $versionId]
        );

        if (!$row) {
            echo json_encode(['success' => false, 'error' => 'Version not found']);
            return;
        }

        echo json_encode(['success' => true, 'version' => $row]);
    }

    // ──────────────────────────────────────────────────────────────────
    // Restore a version  –  POST /projects/codexpro/versions/{id}/restore
    // Body: { version_id }
    // ──────────────────────────────────────────────────────────────────
    public function restore(int $projectId): void
    {
        header('Content-Type: application/json');

        $user = Auth::user();
        $db   = Database::getInstance();

        if (!$this->validateCsrf()) {
            echo json_encode(['success' => false, 'error' => 'CSRF']);
            return;
        }
        if (!$this->isOwnerOrEditor($db, $projectId, $user['id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $raw       = file_get_contents('php://input');
        $body      = json_decode($raw, true) ?? [];
        $versionId = (int)($body['version_id'] ?? 0);

        $snap = $db->fetch(
            "SELECT html_content, css_content, js_content, version_num
             FROM codexpro_project_versions
             WHERE project_id = ? AND id = ?",
            [$projectId, $versionId]
        );

        if (!$snap) {
            echo json_encode(['success' => false, 'error' => 'Version not found']);
            return;
        }

        // Snapshot current state before restoring (so it's not lost)
        $this->snapshot($db, $projectId, $user['id']);

        // Restore
        $db->query(
            "UPDATE codexpro_projects
             SET html_content = ?, css_content = ?, js_content = ?,
                 version = version + 1
             WHERE id = ? AND user_id = ?",
            [
                $snap['html_content'],
                $snap['css_content'],
                $snap['js_content'],
                $projectId,
                $user['id'],
            ]
        );

        echo json_encode([
            'success'      => true,
            'html_content' => $snap['html_content'],
            'css_content'  => $snap['css_content'],
            'js_content'   => $snap['js_content'],
            'message'      => 'Restored to version ' . $snap['version_num'],
        ]);
    }

    // ──────────────────────────────────────────────────────────────────
    // Label a version  –  POST /projects/codexpro/versions/{id}/label
    // Body: { version_id, label }
    // ──────────────────────────────────────────────────────────────────
    public function label(int $projectId): void
    {
        header('Content-Type: application/json');

        $user = Auth::user();
        $db   = Database::getInstance();

        if (!$this->validateCsrf()) {
            echo json_encode(['success' => false, 'error' => 'CSRF']);
            return;
        }
        if (!$this->isOwnerOrEditor($db, $projectId, $user['id'])) {
            http_response_code(403);
            echo json_encode(['error' => 'Forbidden']);
            return;
        }

        $raw       = file_get_contents('php://input');
        $body      = json_decode($raw, true) ?? [];
        $versionId = (int)($body['version_id'] ?? 0);
        $labelText = substr(Security::sanitize($body['label'] ?? ''), 0, 120);

        $db->query(
            "UPDATE codexpro_project_versions
             SET label = ?
             WHERE project_id = ? AND id = ?",
            [$labelText ?: null, $projectId, $versionId]
        );

        echo json_encode(['success' => true]);
    }

    // ══════════════════════════════════════════════════════════════════
    // Public static helper — called from EditorController on save
    // ══════════════════════════════════════════════════════════════════

    /**
     * Create a version snapshot.  Called by EditorController after every save.
     * Prunes old snapshots to MAX_VERSIONS.
     */
    public static function snapshotStatic(
        Database $db,
        int $projectId,
        int $userId,
        string $html,
        string $css,
        string $js
    ): void {
        // Next version number
        $maxNum = (int)($db->fetch(
            "SELECT COALESCE(MAX(version_num),0) AS m FROM codexpro_project_versions WHERE project_id = ?",
            [$projectId]
        )['m'] ?? 0);

        $db->insert('codexpro_project_versions', [
            'project_id'   => $projectId,
            'user_id'      => $userId,
            'version_num'  => $maxNum + 1,
            'html_content' => $html,
            'css_content'  => $css,
            'js_content'   => $js,
        ]);

        // Prune: keep only the most recent MAX_VERSIONS
        $db->query(
            "DELETE FROM codexpro_project_versions
             WHERE project_id = ?
               AND id NOT IN (
                   SELECT id FROM (
                       SELECT id FROM codexpro_project_versions
                       WHERE project_id = ?
                       ORDER BY version_num DESC
                       LIMIT " . self::MAX_VERSIONS . "
                   ) AS keep
               )",
            [$projectId, $projectId]
        );
    }

    // ══════════════════════════════════════════════════════════════════
    // Private helpers
    // ══════════════════════════════════════════════════════════════════

    private function snapshot(Database $db, int $projectId, int $userId): void
    {
        $current = $db->fetch(
            "SELECT html_content, css_content, js_content FROM codexpro_projects WHERE id = ?",
            [$projectId]
        );
        if ($current) {
            self::snapshotStatic(
                $db, $projectId, $userId,
                $current['html_content'] ?? '',
                $current['css_content']  ?? '',
                $current['js_content']   ?? ''
            );
        }
    }

    private function canAccess(Database $db, int $projectId, int $userId): bool
    {
        $owner = $db->fetch(
            "SELECT id FROM codexpro_projects WHERE id = ? AND user_id = ?",
            [$projectId, $userId]
        );
        if ($owner) return true;

        $share = $db->fetch(
            "SELECT id FROM codexpro_project_shares WHERE project_id = ? AND shared_with_user_id = ?",
            [$projectId, $userId]
        );
        return (bool)$share;
    }

    private function isOwnerOrEditor(Database $db, int $projectId, int $userId): bool
    {
        $owner = $db->fetch(
            "SELECT id FROM codexpro_projects WHERE id = ? AND user_id = ?",
            [$projectId, $userId]
        );
        if ($owner) return true;

        $share = $db->fetch(
            "SELECT id FROM codexpro_project_shares
             WHERE project_id = ? AND shared_with_user_id = ? AND can_edit = 1",
            [$projectId, $userId]
        );
        return (bool)$share;
    }

    private function validateCsrf(): bool
    {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN']
               ?? $_POST['_csrf_token']
               ?? '';
        return Security::verifyCsrfToken($token);
    }
}
