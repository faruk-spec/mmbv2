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

    public function show(string $slug = ''): void
    {
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

        // ── Password-gate handling ──────────────────────────────────────────
        $gateOpen  = true;
        $gateError = false;
        $accessMode     = $form['settings']['access_mode']     ?? 'public';
        $accessPasswordHash = $form['settings']['access_password'] ?? '';

        if ($accessMode === 'password' && $accessPasswordHash !== '') {
            $sessionKey = 'formx_gate_' . (int)$form['id'];
            if (!empty($_SESSION[$sessionKey])) {
                $gateOpen = true;
            } elseif (isset($_POST['_gate_password'])) {
                $submitted = $_POST['_gate_password'] ?? '';
                if (password_verify($submitted, $accessPasswordHash) || $submitted === $accessPasswordHash) {
                    $_SESSION[$sessionKey] = true;
                    $gateOpen = true;
                } else {
                    $gateOpen  = false;
                    $gateError = true;
                }
            } else {
                $gateOpen = false;
            }
        }

        \Core\View::render('formx/public-form', [
            'title'     => htmlspecialchars($form['title']),
            'form'      => $form,
            'gateOpen'  => $gateOpen,
            'gateError' => $gateError,
        ]);
    }

    // -------------------------------------------------------------------------
    // Handle public form submission
    // -------------------------------------------------------------------------

    public function submit(string $slug = ''): void
    {
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

        $rawIp     = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
        // Use only the first address and strip whitespace to prevent spoofing via comma-separated list
        $firstIp   = $rawIp ? trim(explode(',', $rawIp)[0]) : null;
        $ip        = ($firstIp && filter_var($firstIp, FILTER_VALIDATE_IP)) ? $firstIp : ($_SERVER['REMOTE_ADDR'] ?? null);
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
                $safeTitle = str_replace(["\r", "\n"], '', strip_tags($form['title']));
                $subject = 'New FormX submission: ' . $safeTitle;
                $body    = "A new submission was received for form \"{$safeTitle}\".\n\n";
                foreach ($submittedData as $k => $v) {
                    $safeKey = str_replace(["\r", "\n"], '', $k);
                    $safeVal = is_array($v)
                        ? str_replace(["\r", "\n"], '', implode(', ', $v))
                        : str_replace(["\r", "\n"], '', $v);
                    $body .= "{$safeKey}: {$safeVal}\n";
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
