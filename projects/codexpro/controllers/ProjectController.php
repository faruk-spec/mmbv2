<?php
/**
 * CodeXPro Project Controller
 *
 * @package MMB\Projects\CodeXPro\Controllers
 */

namespace Projects\CodeXPro\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;
use Core\Security;
use Core\Helpers;
use Core\ActivityLogger;

class ProjectController
{
    public function index(): void
    {
        $user = Auth::user();
        $db   = Database::getInstance();

        $projects = $db->fetchAll(
            "SELECT * FROM codexpro_projects WHERE user_id = ? ORDER BY updated_at DESC, created_at DESC",
            [$user['id']]
        );

        View::render('projects/codexpro/projects', [
            'projects' => $projects,
        ]);
    }

    public function store(): void
    {
        header('Content-Type: application/json');

        try {
            $user = Auth::user();
            $db   = Database::getInstance();

            $name        = Security::sanitize($_POST['name'] ?? '');
            $description = Security::sanitize($_POST['description'] ?? '');
            $language    = Security::sanitize($_POST['language'] ?? 'html');
            $visibility  = Security::sanitize($_POST['visibility'] ?? 'private');

            if ($name === '') {
                echo json_encode(['success' => false, 'error' => 'Project name is required']);
                return;
            }

            $projectId = $db->insert('codexpro_projects', [
                'user_id' => $user['id'],
                'name' => $name,
                'description' => $description,
                'language' => $language,
                'html_content' => '',
                'css_content' => '',
                'js_content' => '',
                'visibility' => in_array($visibility, ['private', 'public'], true) ? $visibility : 'private',
            ]);

            try {
                ActivityLogger::logCreate($user['id'], 'codexpro', 'project', $projectId, ['name' => $name, 'language' => $language]);
            } catch (\Throwable $_) {
            }

            try {
                \Core\Notification::send($user['id'], 'codexpro_project_created', 'Project "' . $name . '" created in CodeXPro.', ['project' => 'codexpro', 'project_id' => $projectId]);
            } catch (\Throwable $_) {
            }

            echo json_encode(['success' => true, 'project_id' => $projectId]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function show(int $id): void
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

        Helpers::redirect('/projects/codexpro/editor/' . $id);
    }

    public function update(int $id): void
    {
        header('Content-Type: application/json');

        try {
            $user = Auth::user();
            $db   = Database::getInstance();

            $project = $db->fetch(
                "SELECT * FROM codexpro_projects WHERE id = ? AND user_id = ?",
                [$id, $user['id']]
            );

            if (!$project) {
                echo json_encode(['success' => false, 'error' => 'Project not found']);
                return;
            }

            $name        = Security::sanitize($_POST['name'] ?? $project['name']);
            $description = Security::sanitize($_POST['description'] ?? ($project['description'] ?? ''));
            $visibility  = Security::sanitize($_POST['visibility'] ?? ($project['visibility'] ?? 'private'));

            $db->update('codexpro_projects', [
                'name' => $name,
                'description' => $description,
                'visibility' => in_array($visibility, ['private', 'public'], true) ? $visibility : 'private',
            ], 'id = ? AND user_id = ?', [$id, $user['id']]);

            try {
                ActivityLogger::logUpdate($user['id'], 'codexpro', 'project', $id, [], ['name' => $name, 'description' => $description, 'visibility' => $visibility]);
            } catch (\Throwable $_) {
            }

            echo json_encode(['success' => true]);
        } catch (\Throwable $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function delete(int $id): void
    {
        header('Content-Type: application/json');

        try {
            $user = Auth::user();
            $db   = Database::getInstance();

            $project = $db->fetch(
                "SELECT id FROM codexpro_projects WHERE id = ? AND user_id = ?",
                [$id, $user['id']]
            );

            if (!$project) {
                echo json_encode(['success' => false, 'error' => 'Project not found or you do not have permission to delete it']);
                return;
            }

            $deleted = $db->delete('codexpro_projects', 'id = ? AND user_id = ?', [$id, $user['id']]);

            if ($deleted > 0) {
                try {
                    ActivityLogger::logDelete($user['id'], 'codexpro', 'project', $id, ['id' => $id]);
                } catch (\Throwable $_) {
                }

                try {
                    \Core\Notification::send($user['id'], 'codexpro_project_deleted', 'Project deleted in CodeXPro.', ['project' => 'codexpro', 'project_id' => $id]);
                } catch (\Throwable $_) {
                }

                echo json_encode(['success' => true, 'message' => 'Project deleted successfully']);
                return;
            }

            echo json_encode(['success' => false, 'error' => 'Failed to delete project']);
        } catch (\Throwable $e) {
            try {
                ActivityLogger::logFailure($user['id'] ?? 0, 'delete_project', $e->getMessage());
            } catch (\Throwable $_) {
            }
            echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        }
    }

    public function quickUpdate(int $id): void
    {
        header('Content-Type: application/json');

        try {
            $user = Auth::user();
            $db   = Database::getInstance();

            $project = $db->fetch(
                "SELECT id FROM codexpro_projects WHERE id = ? AND user_id = ?",
                [$id, $user['id']]
            );

            if (!$project) {
                echo json_encode(['success' => false, 'error' => 'Project not found or you do not have permission to edit it']);
                return;
            }

            $input = json_decode(file_get_contents('php://input'), true) ?: [];

            $updateData = [];
            if (array_key_exists('name', $input)) {
                $name = Security::sanitize((string)$input['name']);
                if ($name === '') {
                    echo json_encode(['success' => false, 'error' => 'Project name cannot be empty']);
                    return;
                }
                $updateData['name'] = $name;
            }
            if (array_key_exists('description', $input)) {
                $updateData['description'] = Security::sanitize((string)$input['description']);
            }
            if (array_key_exists('visibility', $input)) {
                $visibility = Security::sanitize((string)$input['visibility']);
                $updateData['visibility'] = in_array($visibility, ['private', 'public'], true) ? $visibility : 'private';
            }

            if (empty($updateData)) {
                echo json_encode(['success' => false, 'error' => 'No fields to update']);
                return;
            }

            $db->update('codexpro_projects', $updateData, 'id = ? AND user_id = ?', [$id, $user['id']]);

            try {
                ActivityLogger::logUpdate($user['id'], 'codexpro', 'project', $id, [], $updateData);
            } catch (\Throwable $_) {
            }

            echo json_encode(['success' => true, 'data' => $updateData, 'message' => 'Project updated successfully']);
        } catch (\Throwable $e) {
            try {
                ActivityLogger::logFailure($user['id'] ?? 0, 'quick_update_project', $e->getMessage());
            } catch (\Throwable $_) {
            }
            echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        }
    }
}
