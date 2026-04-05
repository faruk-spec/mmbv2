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
        // Fetch regardless of status so we can show a branded "unavailable" page
        $form = $this->db->fetch(
            "SELECT * FROM formx_forms WHERE slug = ?",
            [$slug]
        );

        if (!$form) {
            http_response_code(404);
            \Core\View::render('formx/public-form', [
                'title'       => 'Form Not Found',
                'form'        => null,
                'unavailable' => 'notfound',
                'gateOpen'    => false,
                'gateError'   => false,
                'isExpired'   => false,
            ]);
            exit;
        }

        if ($form['status'] !== 'active') {
            \Core\View::render('formx/public-form', [
                'title'       => htmlspecialchars($form['title']),
                'form'        => $form,
                'unavailable' => $form['status'],
                'gateOpen'    => false,
                'gateError'   => false,
                'isExpired'   => false,
            ]);
            return;
        }

        $form['fields']   = json_decode($form['fields']   ?? '[]', true) ?: [];
        $form['settings'] = json_decode($form['settings'] ?? '{}', true) ?: [];

        // ── Expiry check ────────────────────────────────────────────────────
        $isExpired = false;
        if (!empty($form['expires_at'])) {
            $isExpired = strtotime($form['expires_at']) < time();
        }

        // ── Password-gate handling ──────────────────────────────────────────
        $gateOpen  = true;
        $gateError = false;
        $gateMode  = 'public'; // 'public' | 'password' | 'login'
        $accessMode     = $form['settings']['access_mode']     ?? 'public';
        $accessPasswordHash = $form['settings']['access_password'] ?? '';

        if ($accessMode === 'login') {
            $gateMode = 'login';
            if (\Core\Auth::check()) {
                $gateOpen = true;
            } else {
                $gateOpen = false;
            }
        } elseif ($accessMode === 'password' && $accessPasswordHash !== '') {
            $gateMode = 'password';
            $sessionKey = 'formx_gate_' . (int)$form['id'];
            if (!empty($_SESSION[$sessionKey])) {
                $gateOpen = true;
            } elseif (isset($_POST['_gate_password'])) {
                $submitted = $_POST['_gate_password'] ?? '';
                if (password_verify($submitted, $accessPasswordHash)) {
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
            'title'      => htmlspecialchars($form['title']),
            'form'       => $form,
            'gateOpen'   => $gateOpen,
            'gateError'  => $gateError,
            'gateMode'   => $gateMode,
            'isExpired'  => $isExpired,
            'success'    => Helpers::hasFlash('formx_success') ? Helpers::getFlash('formx_success') : null,
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
            // Show branded unavailable page instead of bare 404
            $anyForm = $this->db->fetch("SELECT id, title, status FROM formx_forms WHERE slug = ?", [$slug]);
            if ($anyForm && $anyForm['status'] !== 'active') {
                \Core\View::render('formx/public-form', [
                    'title'       => htmlspecialchars($anyForm['title']),
                    'form'        => $anyForm,
                    'unavailable' => $anyForm['status'],
                    'gateOpen'    => false,
                    'gateError'   => false,
                    'isExpired'   => false,
                ]);
            } else {
                http_response_code(404);
                \Core\View::render('formx/public-form', [
                    'title'       => 'Form Not Found',
                    'form'        => null,
                    'unavailable' => 'notfound',
                    'gateOpen'    => false,
                    'gateError'   => false,
                    'isExpired'   => false,
                ]);
            }
            return;
        }

        // Check expiry
        if (!empty($form['expires_at']) && strtotime($form['expires_at']) < time()) {
            Helpers::flash('error', 'This form has expired and is no longer accepting submissions.');
            Helpers::redirect('/forms/' . $slug);
            return;
        }

        $token = $_POST['_csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Security::verifyCsrfToken($token)) {
            Helpers::flash('error', 'Invalid request. Please try again.');
            Helpers::redirect('/forms/' . $slug);
            return;
        }

        $fields   = json_decode($form['fields']   ?? '[]', true) ?: [];
        $settings = json_decode($form['settings'] ?? '{}', true) ?: [];

        // Enforce access gate on direct POST submissions
        $accessMode = $settings['access_mode'] ?? 'public';
        if ($accessMode === 'login' && !\Core\Auth::check()) {
            Helpers::redirect('/forms/' . $slug);
            return;
        }
        if ($accessMode === 'password') {
            $sessionKey = 'formx_gate_' . (int)$form['id'];
            if (empty($_SESSION[$sessionKey])) {
                Helpers::redirect('/forms/' . $slug);
                return;
            }
        }

        // Resolve IP address (used for per-IP limit checks)
        $rawIp   = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;
        $firstIp = $rawIp ? trim(explode(',', $rawIp)[0]) : null;
        $ip      = ($firstIp && filter_var($firstIp, FILTER_VALIDATE_IP)) ? $firstIp : ($_SERVER['REMOTE_ADDR'] ?? null);

        // Check overall submission limit
        $maxSubmissions = (int)($settings['max_submissions'] ?? 0);
        if ($maxSubmissions > 0) {
            $totalCount = (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM formx_submissions WHERE form_id = ?",
                [$form['id']]
            );
            if ($totalCount >= $maxSubmissions) {
                Helpers::flash('error', 'This form has reached its maximum number of submissions and is no longer accepting responses.');
                Helpers::redirect('/forms/' . $slug);
                return;
            }
        }

        // Check per-IP submission limit
        $maxPerIp = (int)($settings['max_submissions_per_ip'] ?? 0);
        if ($maxPerIp > 0 && $ip) {
            $ipCount = (int)$this->db->fetchColumn(
                "SELECT COUNT(*) FROM formx_submissions WHERE form_id = ? AND ip_address = ?",
                [$form['id'], $ip]
            );
            if ($ipCount >= $maxPerIp) {
                Helpers::flash('error', 'You have already submitted this form the maximum number of times allowed.');
                Helpers::redirect('/forms/' . $slug);
                return;
            }
        }

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

        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        // Detect device type and browser from user-agent
        $device  = 'Desktop';
        $browser = 'Unknown';
        if ($userAgent) {
            if (preg_match('/iPhone|Android.*Mobile|BlackBerry|Windows Phone/i', $userAgent)) {
                $device = 'Mobile';
            } elseif (preg_match('/iPad|Android(?!.*Mobile)|Tablet/i', $userAgent)) {
                $device = 'Tablet';
            }
            if      (str_contains($userAgent, 'Firefox'))            $browser = 'Firefox';
            elseif  (str_contains($userAgent, 'Edg/'))               $browser = 'Edge';
            elseif  (str_contains($userAgent, 'Chrome'))             $browser = 'Chrome';
            elseif  (str_contains($userAgent, 'Safari'))             $browser = 'Safari';
            elseif  (str_contains($userAgent, 'Opera') || str_contains($userAgent, 'OPR/')) $browser = 'Opera';
        }

        try {
            $this->db->query(
                "INSERT INTO formx_submissions (form_id, data, ip_address, user_agent, device, browser) VALUES (?, ?, ?, ?, ?, ?)",
                [$form['id'], json_encode($submittedData), $ip, $userAgent, $device, $browser]
            );
        } catch (\Exception $e) {
            // Fallback if device/browser columns don't exist yet (old schema)
            $this->db->query(
                "INSERT INTO formx_submissions (form_id, data, ip_address, user_agent) VALUES (?, ?, ?, ?)",
                [$form['id'], json_encode($submittedData), $ip, $userAgent]
            );
        }

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

        $successMsg  = !empty($settings['success_message']) ? $settings['success_message'] : 'Thank you! Your response has been submitted.';
        $redirectUrl = $settings['redirect_url'] ?? '';

        if ($redirectUrl) {
            Helpers::flash('success', $successMsg);
            Helpers::redirect($redirectUrl);
            return;
        }

        Helpers::flash('formx_success', $successMsg);
        Helpers::redirect('/forms/' . $slug);
    }
}
