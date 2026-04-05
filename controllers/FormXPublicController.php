<?php
/**
 * FormX Public Controller
 *
 * Renders public-facing form pages and handles form submissions
 * without requiring authentication.
 *
 * @package MMB\Controllers
 */

namespace Controllers;

use Controllers\BaseController;
use Core\Database;
use Core\Security;
use Core\Helpers;

class FormXPublicController extends BaseController
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // -------------------------------------------------------------------------
    // Public form page
    // -------------------------------------------------------------------------

    public function show(): void
    {
        $slug = Helpers::input('slug', '');
        $form = $this->db->fetch(
            "SELECT * FROM formx_forms WHERE slug = ? AND status = 'active'",
            [$slug]
        );

        if (!$form) {
            http_response_code(404);
            \Core\View::render('errors/404');
            exit;
        }

        $form['fields']   = json_decode($form['fields']   ?? '[]', true) ?: [];
        $form['settings'] = json_decode($form['settings'] ?? '{}', true) ?: [];

        \Core\View::render('formx/public-form', [
            'title' => htmlspecialchars($form['title']),
            'form'  => $form,
        ]);
    }

    // -------------------------------------------------------------------------
    // Handle public form submission
    // -------------------------------------------------------------------------

    public function submit(): void
    {
        $slug = Helpers::input('slug', '');
        $form = $this->db->fetch(
            "SELECT * FROM formx_forms WHERE slug = ? AND status = 'active'",
            [$slug]
        );

        if (!$form) {
            http_response_code(404);
            exit;
        }

        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Security::verifyCsrfToken($token)) {
            Helpers::flash('error', 'Invalid request. Please try again.');
            Helpers::redirect('/forms/' . $slug);
            return;
        }

        $fields   = json_decode($form['fields']   ?? '[]', true) ?: [];
        $settings = json_decode($form['settings'] ?? '{}', true) ?: [];

        // Collect and validate submitted data
        $submittedData = [];
        $errors = [];
        foreach ($fields as $field) {
            $name  = $field['name'] ?? '';
            $label = $field['label'] ?? $name;
            $type  = $field['type'] ?? 'text';

            if (!$name || in_array($type, ['heading', 'paragraph', 'divider'])) {
                continue;
            }

            if ($type === 'checkbox') {
                $value = array_map('strval', (array) ($_POST[$name] ?? []));
            } else {
                $value = trim((string) ($_POST[$name] ?? ''));
            }

            if (!empty($field['required'])) {
                $empty = is_array($value) ? empty($value) : ($value === '');
                if ($empty) {
                    $errors[] = $label . ' is required.';
                }
            }

            $submittedData[$name] = $value;
        }

        if ($errors) {
            Helpers::flash('error', implode(' ', $errors));
            Helpers::redirect('/forms/' . $slug);
            return;
        }

        $ip        = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        $this->db->query(
            "INSERT INTO formx_submissions (form_id, data, ip_address, user_agent) VALUES (?, ?, ?, ?)",
            [$form['id'], json_encode($submittedData), $ip, $userAgent]
        );

        $this->db->query(
            "UPDATE formx_forms SET submissions_count = submissions_count + 1 WHERE id = ?",
            [$form['id']]
        );

        // Optional email notification
        $notifyEmail = $settings['notify_email'] ?? '';
        if ($notifyEmail && filter_var($notifyEmail, FILTER_VALIDATE_EMAIL)) {
            try {
                $subject = 'New FormX submission: ' . $form['title'];
                $body    = "A new submission was received for form \"{$form['title']}\".\n\n";
                foreach ($submittedData as $k => $v) {
                    $body .= "{$k}: " . (is_array($v) ? implode(', ', $v) : $v) . "\n";
                }
                mail($notifyEmail, $subject, $body);
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $successMsg  = $settings['success_message'] ?? 'Thank you! Your response has been submitted.';
        $redirectUrl = $settings['redirect_url'] ?? '';

        if ($redirectUrl) {
            Helpers::flash('success', $successMsg);
            Helpers::redirect($redirectUrl);
            return;
        }

        $form['fields']   = $fields;
        $form['settings'] = $settings;

        \Core\View::render('formx/public-form', [
            'title'   => htmlspecialchars($form['title']),
            'form'    => $form,
            'success' => $successMsg,
        ]);
    }
}
