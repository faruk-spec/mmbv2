<?php
/**
 * Support API Controller
 *
 * Public + admin REST endpoints for the dynamic ticket system.
 *
 * Public (auth required):
 *   GET  /api/support/groups
 *   GET  /api/support/categories?group_id=
 *   GET  /api/support/template?category_id=
 *   POST /api/support/tickets
 *
 * Admin (admin role required):
 *   GET    /api/admin/support/groups
 *   POST   /api/admin/support/groups
 *   PUT    /api/admin/support/groups/{id}
 *   DELETE /api/admin/support/groups/{id}
 *
 *   GET    /api/admin/support/categories?group_id=
 *   POST   /api/admin/support/categories
 *   PUT    /api/admin/support/categories/{id}
 *   DELETE /api/admin/support/categories/{id}
 *
 *   GET    /api/admin/support/template/{category_id}
 *   POST   /api/admin/support/template/{category_id}
 *   GET    /api/admin/support/template/{category_id}/history
 *   GET    /api/admin/support/template/version/{id}
 *
 * @package Controllers\Api
 */

namespace Controllers\Api;

use Controllers\BaseController;
use Core\Auth;
use Core\Helpers;
use Core\TemplateValidator;
use Models\SupportTemplateModel;
use Core\Notification;
use Core\Database;

class SupportApiController extends BaseController
{
    private SupportTemplateModel $model;

    public function __construct()
    {
        header('Content-Type: application/json; charset=utf-8');
        $this->model = new SupportTemplateModel();
    }

    // =========================================================================
    // User-facing public endpoints
    // =========================================================================

    /** GET /api/support/groups */
    public function getGroups(): void
    {
        if (!Auth::check()) {
            $this->jsonError('Unauthenticated.', 401);
            return;
        }
        $groups = $this->model->getAllGroups(true);
        $this->jsonOk($groups);
    }

    /** GET /api/support/categories?group_id= */
    public function getCategories(): void
    {
        if (!Auth::check()) {
            $this->jsonError('Unauthenticated.', 401);
            return;
        }
        $groupId = (int) ($_GET['group_id'] ?? 0);
        if ($groupId <= 0) {
            $this->jsonError('group_id is required.', 422);
            return;
        }
        $cats = $this->model->getCategoriesByGroup($groupId, true);
        $this->jsonOk($cats);
    }

    /** GET /api/support/template?category_id= */
    public function getTemplate(): void
    {
        if (!Auth::check()) {
            $this->jsonError('Unauthenticated.', 401);
            return;
        }
        $categoryId = (int) ($_GET['category_id'] ?? 0);
        if ($categoryId <= 0) {
            $this->jsonError('category_id is required.', 422);
            return;
        }
        $tpl = $this->model->getActiveTemplate($categoryId);
        if (!$tpl) {
            $this->jsonError('No active template found for this category.', 404);
            return;
        }
        $schema = json_decode($tpl['schema_json'], true);
        $this->jsonOk([
            'template_id'   => (int) $tpl['id'],
            'version'        => (int) $tpl['version'],
            'category_id'    => (int) $tpl['category_id'],
            'category_name'  => $tpl['category_name'] ?? '',
            'group_name'     => $tpl['group_name']    ?? '',
            'schema'         => $schema,
        ]);
    }

    /** POST /api/support/tickets */
    public function submitTicket(): void
    {
        if (!Auth::check()) {
            $this->jsonError('Unauthenticated.', 401);
            return;
        }

        $body = $this->jsonBody();

        $templateId = (int) ($body['template_id'] ?? 0);
        $subject    = trim($body['subject'] ?? '');
        $priority   = $body['priority'] ?? 'medium';
        $data       = $body['data'] ?? [];

        if ($templateId <= 0) {
            $this->jsonError('template_id is required.', 422);
            return;
        }
        if ($subject === '' || strlen($subject) > 255) {
            $this->jsonError('Subject is required and must not exceed 255 characters.', 422);
            return;
        }
        if (!in_array($priority, ['low', 'medium', 'high', 'urgent'], true)) {
            $priority = 'medium';
        }

        $tpl = $this->model->getTemplateById($templateId);
        if (!$tpl) {
            $this->jsonError('Template not found.', 404);
            return;
        }
        $schema = json_decode($tpl['schema_json'], true) ?: [];

        // Server-side validation
        $validator = new TemplateValidator($schema);
        $errors    = $validator->validate($data, $_FILES ?? []);
        if (!empty($errors)) {
            $this->jsonError('Validation failed.', 422, ['errors' => $errors]);
            return;
        }

        // Handle file uploads
        $category   = $this->model->getCategoryById((int) $tpl['category_id']);
        $groupId    = $category ? (int) $category['group_id'] : 0;
        $categoryId = (int) $tpl['category_id'];

        // Create the ticket first (to get ID)
        $ticketId = $this->model->createTicket([
            'user_id'        => Auth::id(),
            'template_id'    => $templateId,
            'group_id'       => $groupId,
            'category_id'    => $categoryId,
            'subject'        => $subject,
            'submitted_data' => $data,
            'priority'       => $priority,
        ]);

        // Process file fields
        $this->processFileUploads($ticketId, $schema, $data);

        // Initial message = serialised form data (human-readable)
        $textSummary = $this->buildTextSummary($schema, $data);
        $this->model->addTicketMessage($ticketId, 'user', Auth::id(), $textSummary);

        // Notify admins
        $this->notifyAdmins(
            'support_ticket',
            "New support ticket #{$ticketId}: {$subject}",
            ['ticket_id' => $ticketId, 'url' => '/admin/support/tickets/' . $ticketId]
        );

        $this->jsonOk(['ticket_id' => $ticketId], 201);
    }

    // =========================================================================
    // Admin endpoints — Groups
    // =========================================================================

    /** GET /api/admin/support/groups */
    public function adminGetGroups(): void
    {
        if (!$this->checkAdmin()) return;
        $this->jsonOk($this->model->getAllGroups());
    }

    /** POST /api/admin/support/groups */
    public function adminCreateGroup(): void
    {
        if (!$this->checkAdmin()) return;
        $body = $this->jsonBody();
        $name = trim($body['name'] ?? '');
        if ($name === '') { $this->jsonError('name is required.', 422); return; }
        $id = $this->model->createGroup([
            'name'        => $name,
            'description' => trim($body['description'] ?? ''),
            'icon'        => trim($body['icon'] ?? 'users'),
            'color'       => trim($body['color'] ?? '#00f0ff'),
            'sort_order'  => (int) ($body['sort_order'] ?? 0),
        ]);
        $this->jsonOk(['id' => $id], 201);
    }

    /** PUT /api/admin/support/groups/{id} */
    public function adminUpdateGroup(int $id): void
    {
        if (!$this->checkAdmin()) return;
        $body = $this->jsonBody();
        $this->model->updateGroup($id, $body);
        $this->jsonOk(['updated' => true]);
    }

    /** DELETE /api/admin/support/groups/{id} */
    public function adminDeleteGroup(int $id): void
    {
        if (!$this->checkAdmin()) return;
        $this->model->deleteGroup($id);
        $this->jsonOk(['deleted' => true]);
    }

    // =========================================================================
    // Admin endpoints — Categories
    // =========================================================================

    /** GET /api/admin/support/categories?group_id= */
    public function adminGetCategories(): void
    {
        if (!$this->checkAdmin()) return;
        $groupId = (int) ($_GET['group_id'] ?? 0);
        $cats    = $groupId > 0
            ? $this->model->getCategoriesByGroup($groupId)
            : $this->model->getAllCategories();
        $this->jsonOk($cats);
    }

    /** POST /api/admin/support/categories */
    public function adminCreateCategory(): void
    {
        if (!$this->checkAdmin()) return;
        $body    = $this->jsonBody();
        $groupId = (int) ($body['group_id'] ?? 0);
        $name    = trim($body['name'] ?? '');
        if ($groupId <= 0 || $name === '') {
            $this->jsonError('group_id and name are required.', 422);
            return;
        }
        $id = $this->model->createCategory([
            'group_id'    => $groupId,
            'name'        => $name,
            'description' => trim($body['description'] ?? ''),
            'icon'        => trim($body['icon'] ?? 'tag'),
            'sort_order'  => (int) ($body['sort_order'] ?? 0),
        ]);
        $this->jsonOk(['id' => $id], 201);
    }

    /** PUT /api/admin/support/categories/{id} */
    public function adminUpdateCategory(int $id): void
    {
        if (!$this->checkAdmin()) return;
        $body = $this->jsonBody();
        $this->model->updateCategory($id, $body);
        $this->jsonOk(['updated' => true]);
    }

    /** DELETE /api/admin/support/categories/{id} */
    public function adminDeleteCategory(int $id): void
    {
        if (!$this->checkAdmin()) return;
        $this->model->deleteCategory($id);
        $this->jsonOk(['deleted' => true]);
    }

    // =========================================================================
    // Admin endpoints — Templates
    // =========================================================================

    /** GET /api/admin/support/template/{category_id} */
    public function adminGetTemplate(int $categoryId): void
    {
        if (!$this->checkAdmin()) return;
        $tpl = $this->model->getActiveTemplate($categoryId);
        if (!$tpl) {
            $this->jsonOk(null); // no template yet — builder starts fresh
            return;
        }
        $this->jsonOk([
            'id'          => (int) $tpl['id'],
            'version'     => (int) $tpl['version'],
            'schema'      => json_decode($tpl['schema_json'], true),
            'created_at'  => $tpl['created_at'],
        ]);
    }

    /** POST /api/admin/support/template/{category_id} — save new version */
    public function adminSaveTemplate(int $categoryId): void
    {
        if (!$this->checkAdmin()) return;

        $cat = $this->model->getCategoryById($categoryId);
        if (!$cat) {
            $this->jsonError('Category not found.', 404);
            return;
        }

        $body   = $this->jsonBody();
        $schema = $body['schema'] ?? $body;

        if (empty($schema['sections'])) {
            $this->jsonError('Schema must contain at least one section.', 422);
            return;
        }

        $id = $this->model->saveTemplate($categoryId, $schema, Auth::id());
        $this->jsonOk(['template_id' => $id, 'version' => $schema['version'] ?? 1], 201);
    }

    /** GET /api/admin/support/template/{category_id}/history */
    public function adminGetTemplateHistory(int $categoryId): void
    {
        if (!$this->checkAdmin()) return;
        $history = $this->model->getTemplateHistory($categoryId);
        $this->jsonOk($history);
    }

    /** GET /api/admin/support/template/version/{id} */
    public function adminGetTemplateVersion(int $id): void
    {
        if (!$this->checkAdmin()) return;
        $tpl = $this->model->getTemplateById($id);
        if (!$tpl) {
            $this->jsonError('Template version not found.', 404);
            return;
        }
        $this->jsonOk([
            'id'         => (int) $tpl['id'],
            'version'    => (int) $tpl['version'],
            'is_active'  => (bool) $tpl['is_active'],
            'schema'     => json_decode($tpl['schema_json'], true),
            'created_at' => $tpl['created_at'],
        ]);
    }

    // =========================================================================
    // Private helpers
    // =========================================================================

    private function checkAdmin(): bool
    {
        if (!Auth::check()) {
            $this->jsonError('Unauthenticated.', 401);
            return false;
        }
        if (!Auth::hasAnyAdminPermission()) {
            $this->jsonError('Forbidden.', 403);
            return false;
        }
        return true;
    }

    private function jsonBody(): array
    {
        $raw = file_get_contents('php://input');
        if ($raw === false || $raw === '') {
            return $_POST ?: [];
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : ($_POST ?: []);
    }

    private function jsonOk(mixed $data, int $code = 200): void
    {
        http_response_code($code);
        echo json_encode(['ok' => true, 'data' => $data]);
        exit;
    }

    private function jsonError(string $message, int $code = 400, array $extra = []): void
    {
        http_response_code($code);
        echo json_encode(array_merge(['ok' => false, 'error' => $message], $extra));
        exit;
    }

    private function notifyAdmins(string $type, string $message, array $data = []): void
    {
        $db     = Database::getInstance();
        $admins = $db->fetchAll("SELECT id FROM users WHERE role LIKE '%admin%'") ?: [];
        foreach ($admins as $admin) {
            Notification::send((int) $admin['id'], $type, $message, $data);
        }
    }

    private function processFileUploads(int $ticketId, array $schema, array &$data): void
    {
        foreach ($schema['sections'] ?? [] as $section) {
            foreach ($section['fields'] ?? [] as $field) {
                if (($field['type'] ?? '') !== 'file') continue;
                $fieldName = $field['name'] ?? '';
                if ($fieldName === '' || empty($_FILES[$fieldName])) continue;

                $f = $_FILES[$fieldName];
                if ((int) ($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;

                $ext       = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
                $allowed   = ['pdf','png','jpg','jpeg','gif','webp','mp4','txt','zip','doc','docx','xlsx','csv'];
                if (!in_array($ext, $allowed, true)) continue;

                $dir = BASE_PATH . '/storage/uploads/support/tickets/' . $ticketId . '/' . preg_replace('/[^a-z0-9_-]/', '', $fieldName);
                if (!is_dir($dir)) {
                    @mkdir($dir, 0755, true);
                }
                try {
                    $safeName = 'att_' . bin2hex(random_bytes(12)) . '.' . $ext;
                } catch (\Throwable $e) {
                    continue;
                }
                $target = $dir . '/' . $safeName;
                if (!@move_uploaded_file($f['tmp_name'], $target)) continue;

                $mime     = mime_content_type($target) ?: ($f['type'] ?? 'application/octet-stream');
                $relPath  = 'support/tickets/' . $ticketId . '/' . preg_replace('/[^a-z0-9_-]/', '', $fieldName) . '/' . $safeName;

                $attId = $this->model->saveAttachment($ticketId, $fieldName, basename($f['name']), $relPath, $mime, (int) $f['size']);
                $data[$fieldName] = ['attachment_id' => $attId, 'file_name' => basename($f['name'])];
            }
        }
    }

    private function buildTextSummary(array $schema, array $data): string
    {
        $lines = [];
        foreach ($schema['sections'] ?? [] as $section) {
            if (!empty($section['title'])) {
                $lines[] = '### ' . $section['title'];
            }
            foreach ($section['fields'] ?? [] as $field) {
                $name  = $field['name']  ?? '';
                $label = $field['label'] ?? $name;
                $val   = $data[$name]    ?? '';
                if (is_array($val)) {
                    $val = isset($val['file_name']) ? '[Attachment: ' . $val['file_name'] . ']' : implode(', ', $val);
                }
                if ($val !== '') {
                    $lines[] = "{$label}: {$val}";
                }
            }
        }
        return implode("\n", $lines);
    }
}
