<?php
/**
 * LinkShortner Link Controller
 *
 * @package MMB\Projects\LinkShortner\Controllers
 */

namespace Projects\LinkShortner\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;
use Core\Security;

class LinkController
{
    private function generateCode(Database $db, int $length = 6): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $chars[random_int(0, strlen($chars) - 1)];
            }
        } while ($db->fetchColumn("SELECT COUNT(*) FROM short_links WHERE code = ?", [$code]) > 0);
        return $code;
    }

    public function index(): void
    {
        $user  = Auth::user();
        $db    = Database::projectConnection('linkshortner');
        $page  = max(1, (int) ($_GET['page'] ?? 1));
        $limit = 20;
        $offset = ($page - 1) * $limit;

        $total = (int) $db->fetchColumn("SELECT COUNT(*) FROM short_links WHERE user_id = ?", [$user['id']]);
        $links = $db->fetchAll(
            "SELECT * FROM short_links WHERE user_id = ? ORDER BY created_at DESC LIMIT ? OFFSET ?",
            [$user['id'], $limit, $offset]
        );

        View::render('projects/linkshortner/links', [
            'title'       => 'My Links',
            'subtitle'    => 'Manage your short links',
            'links'       => $links,
            'total'       => $total,
            'page'        => $page,
            'totalPages'  => (int) ceil($total / $limit),
        ]);
    }

    public function create(): void
    {
        View::render('projects/linkshortner/create', [
            'title'    => 'Create Short Link',
            'subtitle' => 'Shorten a URL',
        ]);
    }

    public function store(): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /projects/linkshortner/create');
            exit;
        }

        $user = Auth::user();
        $db   = Database::projectConnection('linkshortner');

        $originalUrl = trim($_POST['original_url'] ?? '');
        if (empty($originalUrl) || !filter_var($originalUrl, FILTER_VALIDATE_URL)) {
            $_SESSION['error'] = 'Please enter a valid URL.';
            header('Location: /projects/linkshortner/create');
            exit;
        }

        $customCode = trim($_POST['custom_code'] ?? '');
        if ($customCode !== '') {
            if (!preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $customCode)) {
                $_SESSION['error'] = 'Custom code must be 3–20 alphanumeric characters.';
                header('Location: /projects/linkshortner/create');
                exit;
            }
            if ($db->fetchColumn("SELECT COUNT(*) FROM short_links WHERE code = ?", [$customCode]) > 0) {
                $_SESSION['error'] = 'That custom code is already taken.';
                header('Location: /projects/linkshortner/create');
                exit;
            }
            $code = $customCode;
        } else {
            $code = $this->generateCode($db);
        }

        $title      = trim($_POST['title'] ?? '') ?: null;
        $password   = !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_BCRYPT) : null;
        $expiresAt  = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
        $clickLimit = !empty($_POST['click_limit']) ? (int) $_POST['click_limit'] : null;
        $utmSource  = trim($_POST['utm_source'] ?? '') ?: null;
        $utmMedium  = trim($_POST['utm_medium'] ?? '') ?: null;
        $utmCampaign = trim($_POST['utm_campaign'] ?? '') ?: null;

        $db->query(
            "INSERT INTO short_links (user_id, code, original_url, title, password, expires_at, click_limit, utm_source, utm_medium, utm_campaign) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$user['id'], $code, $originalUrl, $title, $password, $expiresAt, $clickLimit, $utmSource, $utmMedium, $utmCampaign]
        );

        $_SESSION['success'] = 'Short link created! Code: ' . $code;
        header('Location: /projects/linkshortner/links');
        exit;
    }

    public function edit(int $id): void
    {
        $user = Auth::user();
        $db   = Database::projectConnection('linkshortner');
        $link = $db->fetch("SELECT * FROM short_links WHERE id = ? AND user_id = ?", [$id, $user['id']]);
        if (!$link) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }
        View::render('projects/linkshortner/edit', [
            'title'    => 'Edit Link',
            'subtitle' => 'Update your short link',
            'link'     => $link,
        ]);
    }

    public function update(int $id): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid security token.';
            header("Location: /projects/linkshortner/links/{$id}/edit");
            exit;
        }

        $user = Auth::user();
        $db   = Database::projectConnection('linkshortner');
        $link = $db->fetch("SELECT * FROM short_links WHERE id = ? AND user_id = ?", [$id, $user['id']]);
        if (!$link) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }

        $originalUrl = trim($_POST['original_url'] ?? '');
        if (empty($originalUrl) || !filter_var($originalUrl, FILTER_VALIDATE_URL)) {
            $_SESSION['error'] = 'Please enter a valid URL.';
            header("Location: /projects/linkshortner/links/{$id}/edit");
            exit;
        }

        $title      = trim($_POST['title'] ?? '') ?: null;
        $expiresAt  = !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
        $clickLimit = !empty($_POST['click_limit']) ? (int) $_POST['click_limit'] : null;
        $utmSource  = trim($_POST['utm_source'] ?? '') ?: null;
        $utmMedium  = trim($_POST['utm_medium'] ?? '') ?: null;
        $utmCampaign = trim($_POST['utm_campaign'] ?? '') ?: null;

        $db->query(
            "UPDATE short_links SET original_url = ?, title = ?, expires_at = ?, click_limit = ?, utm_source = ?, utm_medium = ?, utm_campaign = ?, updated_at = NOW() WHERE id = ? AND user_id = ?",
            [$originalUrl, $title, $expiresAt, $clickLimit, $utmSource, $utmMedium, $utmCampaign, $id, $user['id']]
        );

        $_SESSION['success'] = 'Link updated successfully.';
        header('Location: /projects/linkshortner/links');
        exit;
    }

    public function delete(int $id): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $_SESSION['error'] = 'Invalid security token.';
            header('Location: /projects/linkshortner/links');
            exit;
        }

        $user = Auth::user();
        $db   = Database::projectConnection('linkshortner');
        $db->query("DELETE FROM short_links WHERE id = ? AND user_id = ?", [$id, $user['id']]);

        $_SESSION['success'] = 'Link deleted.';
        header('Location: /projects/linkshortner/links');
        exit;
    }

    public function toggle(int $id): void
    {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid token']);
            exit;
        }

        $user = Auth::user();
        $db   = Database::projectConnection('linkshortner');
        $link = $db->fetch("SELECT id, status FROM short_links WHERE id = ? AND user_id = ?", [$id, $user['id']]);
        if (!$link) {
            http_response_code(404);
            echo json_encode(['error' => 'Not found']);
            exit;
        }

        $newStatus = $link['status'] === 'active' ? 'disabled' : 'active';
        $db->query("UPDATE short_links SET status = ?, updated_at = NOW() WHERE id = ?", [$newStatus, $id]);

        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'status' => $newStatus]);
        exit;
    }
}
