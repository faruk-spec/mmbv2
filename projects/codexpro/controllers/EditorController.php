<?php
/**
 * CodeXPro Editor Controller - Live Preview
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

        $settings = $db->fetch(
            "SELECT * FROM codexpro_user_settings WHERE user_id = ?",
            [$user['id']]
        );

        if (!$settings) {
            $db->insert('codexpro_user_settings', [
                'user_id'          => $user['id'],
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
                [$user['id']]
            );
        }

        View::render('projects/codexpro/editor', [
            'project' => null,
            'settings' => $settings,
        ]);
    }

    public function create(): void
    {
        $this->index();
    }

    public function edit(int $id): void
    {
        $user = Auth::user();
        $db   = Database::getInstance();

        $project = $db->fetch(
            "SELECT * FROM codexpro_projects WHERE id = ? AND user_id = ?",
            [$id, $user['id']]
        );

        if (!$project) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }

        $settings = $db->fetch(
            "SELECT * FROM codexpro_user_settings WHERE user_id = ?",
            [$user['id']]
        );

        View::render('projects/codexpro/editor', [
            'project' => $project,
            'settings' => $settings,
        ]);
    }

    public function save(): void
    {
        header('Content-Type: application/json');

        try {
            $user = Auth::user();
            $db   = Database::getInstance();

            $projectId   = (int)($_POST['project_id'] ?? 0);
            $name        = Security::sanitize($_POST['name'] ?? 'Untitled Project');
            $description = Security::sanitize($_POST['description'] ?? '');
            $htmlContent = $_POST['html_content'] ?? '';
            $cssContent  = $_POST['css_content'] ?? '';
            $jsContent   = $_POST['js_content'] ?? '';
            $visibility  = Security::sanitize($_POST['visibility'] ?? 'private');

            if ($projectId >= 1) {
                $project = $db->fetch(
                    "SELECT id FROM codexpro_projects WHERE id = ? AND user_id = ?",
                    [$projectId, $user['id']]
                );

                if (!$project) {
                    echo json_encode(['success' => false, 'error' => 'Project not found']);
                    return;
                }

                $db->update('codexpro_projects', [
                    'name' => $name,
                    'description' => $description,
                    'html_content' => $htmlContent,
                    'css_content' => $cssContent,
                    'js_content' => $jsContent,
                    'visibility' => in_array($visibility, ['private', 'public'], true) ? $visibility : 'private',
                ], 'id = ? AND user_id = ?', [$projectId, $user['id']]);

                try {
                    ActivityLogger::logUpdate($user['id'], 'codexpro', 'file', $projectId, [], ['name' => $name, 'action' => 'file_saved']);
                } catch (\Throwable $_) {
                }

                echo json_encode(['success' => true, 'project_id' => $projectId]);
                return;
            }

            $newProjectId = $db->insert('codexpro_projects', [
                'user_id' => $user['id'],
                'name' => $name,
                'description' => $description,
                'language' => 'html',
                'html_content' => $htmlContent,
                'css_content' => $cssContent,
                'js_content' => $jsContent,
                'visibility' => in_array($visibility, ['private', 'public'], true) ? $visibility : 'private',
            ]);

            if ($newProjectId > 0) {
                try {
                    ActivityLogger::logCreate($user['id'], 'codexpro', 'file', $newProjectId, ['name' => $name, 'action' => 'file_saved']);
                } catch (\Throwable $_) {
                }

                echo json_encode(['success' => true, 'project_id' => $newProjectId]);
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
                "SELECT id FROM codexpro_projects WHERE id = ? AND user_id = ?",
                [$projectId, $user['id']]
            );

            if (!$project) {
                echo json_encode(['success' => false, 'error' => 'Project not found']);
                return;
            }

            $db->update('codexpro_projects', [
                'html_content' => $_POST['html_content'] ?? '',
                'css_content' => $_POST['css_content'] ?? '',
                'js_content' => $_POST['js_content'] ?? '',
            ], 'id = ? AND user_id = ?', [$projectId, $user['id']]);

            try {
                ActivityLogger::logUpdate($user['id'], 'codexpro', 'file', $projectId, [], ['action' => 'file_saved']);
            } catch (\Throwable $_) {
            }

            echo json_encode(['success' => true]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }
}
