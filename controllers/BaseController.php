<?php
/**
 * Base Controller
 * 
 * @package MMB\Controllers
 */

namespace Controllers;

use Core\View;
use Core\Auth;
use Core\Security;
use Core\Helpers;

abstract class BaseController
{
    /**
     * Render a view
     */
    protected function view(string $view, array $data = [], ?string $layout = null): void
    {
        // Add common data
        $data['user'] = Auth::user();
        $data['isLoggedIn'] = Auth::check();
        $data['csrf_token'] = Security::generateCsrfToken();
        
        // Set layout if provided
        if ($layout) {
            View::extend($layout);
        }
        
        View::render($view, $data);
    }
    
    /**
     * Return JSON response
     */
    protected function json($data, int $code = 200): void
    {
        Helpers::json($data, $code);
    }
    
    /**
     * Redirect to URL
     */
    protected function redirect(string $url, int $code = 302): void
    {
        Helpers::redirect($url, $code);
    }
    
    /**
     * Validate CSRF token
     */
    protected function validateCsrf(): bool
    {
        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        return Security::verifyCsrfToken($token);
    }
    
    /**
     * Get input value
     */
    protected function input(string $key, $default = null): mixed
    {
        return Helpers::input($key, $default);
    }
    
    /**
     * Get all input
     */
    protected function all(): array
    {
        return Helpers::all();
    }
    
    /**
     * Validate input
     */
    protected function validate(array $rules): array
    {
        $errors = Helpers::validate($_POST, $rules);
        if (!empty($errors)) {
            View::setErrors($errors);
            View::flashOldInput($_POST);
        }
        return $errors;
    }
    
    /**
     * Flash message
     */
    protected function flash(string $type, string $message): void
    {
        Helpers::flash($type, $message);
    }
    
    /**
     * Check if user is authenticated
     */
    protected function requireAuth(): void
    {
        if (!Auth::check()) {
            $this->redirect('/login?return=' . urlencode($_SERVER['REQUEST_URI']));
        }
    }
    
    /**
     * Check if user is admin
     */
    protected function requireAdmin(): void
    {
        if (!Auth::hasAnyAdminPermission()) {
            http_response_code(403);
            View::render('errors/403');
            exit;
        }
    }

    /**
     * Require a specific granular admin permission.
     *
     * Checks Auth::hasPermission($key) which resolves via:
     *  1. Role (admin / super_admin) → always pass
     *  2. Explicit row in admin_user_permissions for this user
     *  3. Row in user_role_permissions for the user's role
     *
     * On failure, flashes an error and redirects the already-authenticated
     * admin user to the dashboard (avoids hard 403 within the admin panel).
     */
    protected function requirePermission(string $key): void
    {
        if (!Auth::hasPermission($key)) {
            $this->flash('error', 'You do not have permission to access that section.');
            $this->redirect('/dashboard');
            exit;
        }
    }

    /**
     * Gate a controller whose features are split across several sub-permission
     * keys (e.g. 'qr', 'qr.analytics', 'qr.blocked_links' …).
     *
     * Passes if the user holds the exact key OR any key that starts with
     * "$prefix." – i.e. they have at least one permission in the group.
     * Individual action methods should then call requirePermission('qr.analytics')
     * etc. to enforce fine-grained access.
     */
    protected function requirePermissionGroup(string $prefix): void
    {
        if (!Auth::hasPermissionGroup($prefix)) {
            $this->flash('error', 'You do not have permission to access that section.');
            $this->redirect('/dashboard');
            exit;
        }
    }

    /**
     * Require the user to be a true role-based admin (admin / super_admin).
     *
     * Use this inside actions that must be restricted to real admins even if
     * the user happens to have some entries in admin_user_permissions
     * (e.g. managing permissions themselves, security settings, etc.).
     */
    protected function requireRoleAdmin(): void
    {
        if (!Auth::isAdmin()) {
            http_response_code(403);
            View::render('errors/403');
            exit;
        }
    }

    /**
     * Check if user can access Audit Explorer (admin or audit_viewer role)
     */
    protected function requireAuditAccess(): void
    {
        if (!Auth::canAccessAudit()) {
            http_response_code(403);
            View::render('errors/403');
            exit;
        }
    }

    /**
     * Render a support email template.
     *
     * Checks the mail_notification_templates DB table first (so admins can edit
     * templates via /admin/mail/templates). Falls back to views/emails/{slug}.php
     * if no active DB template is found.
     *
     * Returns ['subject' => string|null, 'body' => string] or null on failure.
     * subject is null when the PHP-file fallback is used (caller keeps its own subject).
     */
    protected function renderSupportEmail(string $slug, array $vars): ?array
    {
        // Build snake_case placeholder map from camelCase vars
        $placeholders = [];
        foreach ($vars as $key => $value) {
            $snake = strtolower(preg_replace('/([A-Z])/', '_$1', $key));
            $placeholders[$snake]  = (string) $value;
            $placeholders[$key]    = (string) $value; // keep original key too
        }

        // Try DB template first
        try {
            $db  = \Core\Database::getInstance();
            $tpl = $db->fetch(
                "SELECT subject, body FROM mail_notification_templates WHERE slug = ? AND is_enabled = 1 LIMIT 1",
                [$slug]
            );
            if ($tpl && !empty($tpl['body'])) {
                $subject = $tpl['subject'] ?? '';
                $body    = $tpl['body'];
                foreach ($placeholders as $k => $v) {
                    $body    = str_replace('{{' . $k . '}}', $v, $body);
                    $subject = str_replace('{{' . $k . '}}', $v, $subject);
                }
                return ['subject' => $subject ?: null, 'body' => $body];
            }
        } catch (\Throwable $e) {
            // DB unavailable or table not created yet — fall through
        }

        // Fall back to PHP file template
        $file = defined('BASE_PATH') ? BASE_PATH . '/views/emails/' . $slug . '.php' : null;
        if ($file === null || !file_exists($file)) {
            return null;
        }
        ob_start();
        extract($vars, EXTR_SKIP);
        include $file;
        $body = ob_get_clean() ?: null;
        return $body !== null ? ['subject' => null, 'body' => $body] : null;
    }
}
