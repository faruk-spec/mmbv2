<?php
/**
 * CodeXPro Editor Controller - Live Preview + Collaboration
 *
 * @package MMB\Projects\CodeXPro\Controllers
 */

namespace Projects\CodeXPro\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;
use Core\Security;
use Core\ActivityLogger;

class EditorController
{
    public function index(): void
    {
        $user = Auth::user();
        $db   = Database::getInstance();

        $settings = $this->ensureSettings($db, $user['id']);

        View::render('projects/codexpro/editor', [
            'project'  => null,
            'settings' => $settings,
        ]);
    }

    public function create(): void
    {
        $this->index();
    }

    public function edit(int $id): void
    {
        $user  = Auth::user();
        $db    = Database::getInstance();
        $token = Security::sanitize($_GET['token'] ?? '');

        // Owner access
        $project = $db->fetch(
            "SELECT * FROM codexpro_projects WHERE id = ?",
            [$id]
        );

        if (!$project) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }

        $isOwner = ((int)$project['user_id'] === (int)$user['id']);

        if (!$isOwner) {
            // Check share table (viewer or editor)
            $share = $db->fetch(
                "SELECT can_edit FROM codexpro_project_shares
                 WHERE project_id = ? AND shared_with_user_id = ?",
                [$id, $user['id']]
            );

            // Or collab_token from query string (invite link)
            $tokenShare = ($token !== '' && $token === ($project['collab_token'] ?? ''));

            if (!$share && !$tokenShare) {
                http_response_code(403);
                View::render('errors/403');
                return;
            }

            // Read-only if no can_edit
            $project['_readonly'] = (!$tokenShare && empty($share['can_edit']));
        } else {
            $project['_readonly'] = false;
        }

        $settings = $this->ensureSettings($db, $user['id']);

        View::render('projects/codexpro/editor', [
            'project'  => $project,
            'settings' => $settings,
        ]);
    }

    public function save(): void
    {
        header('Content-Type: application/json');

        try {
            $user = Auth::user();
            $db   = Database::getInstance();

            $projectId   = (int)($_POST['project_id']   ?? 0);
            $name        = Security::sanitize($_POST['name']        ?? 'Untitled Project');
            $description = Security::sanitize($_POST['description'] ?? '');
            $htmlContent = $_POST['html_content'] ?? '';
            $cssContent  = $_POST['css_content']  ?? '';
            $jsContent   = $_POST['js_content']   ?? '';
            $visibility  = Security::sanitize($_POST['visibility']  ?? 'private');
            $clientVer   = (int)($_POST['version'] ?? 0);

            $visibility  = in_array($visibility, ['private', 'public'], true) ? $visibility : 'private';

            if ($projectId >= 1) {
                // Allow owner or editor-share to save
                $project = $db->fetch(
                    "SELECT id, user_id, version FROM codexpro_projects
                     WHERE id = ?",
                    [$projectId]
                );

                if (!$project) {
                    echo json_encode(['success' => false, 'error' => 'Project not found']);
                    return;
                }

                $isOwner = ((int)$project['user_id'] === (int)$user['id']);
                if (!$isOwner) {
                    $share = $db->fetch(
                        "SELECT can_edit FROM codexpro_project_shares
                         WHERE project_id = ? AND shared_with_user_id = ? AND can_edit = 1",
                        [$projectId, $user['id']]
                    );
                    if (!$share) {
                        echo json_encode(['success' => false, 'error' => 'No write permission']);
                        return;
                    }
                }

                $serverVer = (int)($project['version'] ?? 1);

                // Conflict detection: if client version is behind server, warn (don't block)
                $conflicted = ($clientVer > 0 && $clientVer < $serverVer);

                // Snapshot current state before overwriting
                require_once __DIR__ . '/VersionController.php';
                VersionController::snapshotStatic(
                    $db, $projectId, $user['id'],
                    $htmlContent, $cssContent, $jsContent
                );

                $db->update('codexpro_projects', [
                    'name'         => $name,
                    'description'  => $description,
                    'html_content' => $htmlContent,
                    'css_content'  => $cssContent,
                    'js_content'   => $jsContent,
                    'visibility'   => $visibility,
                    'version'      => $serverVer + 1,
                ], 'id = ?', [$projectId]);

                try {
                    ActivityLogger::logUpdate(
                        $user['id'], 'codexpro', 'file', $projectId,
                        [], ['name' => $name, 'action' => 'file_saved']
                    );
                } catch (\Throwable $_) {}

                echo json_encode([
                    'success'    => true,
                    'project_id' => $projectId,
                    'version'    => $serverVer + 1,
                    'conflict'   => $conflicted,
                ]);
                return;
            }

            // Create new project
            $newProjectId = $db->insert('codexpro_projects', [
                'user_id'      => $user['id'],
                'name'         => $name,
                'description'  => $description,
                'language'     => 'html',
                'html_content' => $htmlContent,
                'css_content'  => $cssContent,
                'js_content'   => $jsContent,
                'visibility'   => $visibility,
                'version'      => 1,
            ]);

            if ($newProjectId > 0) {
                // Initial snapshot
                require_once __DIR__ . '/VersionController.php';
                VersionController::snapshotStatic(
                    $db, $newProjectId, $user['id'],
                    $htmlContent, $cssContent, $jsContent
                );

                try {
                    ActivityLogger::logCreate(
                        $user['id'], 'codexpro', 'file', $newProjectId,
                        ['name' => $name, 'action' => 'file_created']
                    );
                } catch (\Throwable $_) {}

                echo json_encode([
                    'success'    => true,
                    'project_id' => $newProjectId,
                    'version'    => 1,
                    'conflict'   => false,
                ]);
                return;
            }

            echo json_encode(['success' => false, 'error' => 'Failed to create project']);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function autosave(): void
    {
        header('Content-Type: application/json');

        try {
            $user = Auth::user();
            $db   = Database::getInstance();

            $projectId = (int)($_POST['project_id'] ?? 0);
            if ($projectId < 1) {
                echo json_encode(['success' => false, 'error' => 'Project ID required']);
                return;
            }

            $project = $db->fetch(
                "SELECT id, user_id, version FROM codexpro_projects WHERE id = ?",
                [$projectId]
            );

            if (!$project) {
                echo json_encode(['success' => false, 'error' => 'Project not found']);
                return;
            }

            $isOwner = ((int)$project['user_id'] === (int)$user['id']);
            if (!$isOwner) {
                $share = $db->fetch(
                    "SELECT can_edit FROM codexpro_project_shares
                     WHERE project_id = ? AND shared_with_user_id = ? AND can_edit = 1",
                    [$projectId, $user['id']]
                );
                if (!$share) {
                    echo json_encode(['success' => false, 'error' => 'No write permission']);
                    return;
                }
            }

            $serverVer = (int)($project['version'] ?? 1);

            $db->update('codexpro_projects', [
                'html_content' => $_POST['html_content'] ?? '',
                'css_content'  => $_POST['css_content']  ?? '',
                'js_content'   => $_POST['js_content']   ?? '',
                'version'      => $serverVer + 1,
            ], 'id = ?', [$projectId]);

            try {
                ActivityLogger::logUpdate(
                    $user['id'], 'codexpro', 'file', $projectId,
                    [], ['action' => 'autosaved']
                );
            } catch (\Throwable $_) {}

            echo json_encode(['success' => true, 'version' => $serverVer + 1]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    // ══════════════════════════════════════════════════════════════════
    // Private helpers
    // ══════════════════════════════════════════════════════════════════

    private function ensureSettings(Database $db, int $userId): array
    {
        $settings = $db->fetch(
            "SELECT * FROM codexpro_user_settings WHERE user_id = ?",
            [$userId]
        );

        if (!$settings) {
            $db->insert('codexpro_user_settings', [
                'user_id'          => $userId,
                'theme'            => 'dark',
                'font_size'        => 14,
                'font_family'      => 'JetBrains Mono',
                'tab_size'         => 2,
                'auto_save'        => 1,
                'auto_preview'     => 1,
                'key_bindings'     => 'default',
                'word_wrap'        => 0,
                'line_numbers'     => 1,
                'bracket_matching' => 1,
                'auto_indent'      => 1,
                'indent_guides'    => 1,
                'highlight_line'   => 1,
                'show_minimap'     => 0,
            ]);
            $settings = $db->fetch(
                "SELECT * FROM codexpro_user_settings WHERE user_id = ?",
                [$userId]
            );
        }

        return $settings ?? [];
    }
}
