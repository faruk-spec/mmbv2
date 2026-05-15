<?php use Core\Security; use Core\View; ?>
<?php
$nonce = Security::getCspNonce();
$pageTitle = trim(($title ?? '') !== '' ? ($title . ' - ' . APP_NAME) : APP_NAME);
ob_start();
View::yield('styles');
$extraStyles = trim(ob_get_clean());
ob_start();
View::yield('scripts');
$extraScripts = trim(ob_get_clean());
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::e($pageTitle) ?></title>
    <style nonce="<?= View::e($nonce) ?>">
        :root {
            color-scheme: dark;
            --auth-bg: #07111f;
            --auth-panel: rgba(10, 21, 38, 0.94);
            --auth-panel-strong: #0d1b2f;
            --auth-border: rgba(120, 157, 214, 0.22);
            --auth-border-strong: rgba(87, 179, 255, 0.34);
            --auth-text: #f4f8ff;
            --auth-muted: #9cb2d1;
            --auth-primary: #59d0ff;
            --auth-primary-dark: #07111f;
            --auth-primary-soft: rgba(89, 208, 255, 0.12);
            --auth-danger: #ff7b7b;
            --auth-success: #2ddb8d;
            --auth-warning: #ffcf66;
            --auth-shadow: 0 30px 70px rgba(0, 0, 0, 0.42);
            --auth-radius: 20px;
            --auth-radius-sm: 12px;
            --auth-transition: 0.2s ease;
            --bg-card: var(--auth-panel);
            --bg-secondary: rgba(8, 18, 33, 0.92);
            --border-color: var(--auth-border);
            --text-primary: var(--auth-text);
            --text-secondary: var(--auth-muted);
            --cyan: var(--auth-primary);
            --magenta: #c06dff;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Inter, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            background:
                radial-gradient(circle at top left, rgba(89, 208, 255, 0.16), transparent 30%),
                radial-gradient(circle at top right, rgba(170, 95, 255, 0.12), transparent 28%),
                linear-gradient(180deg, #08121f 0%, #050b14 100%);
            color: var(--auth-text);
        }

        a {
            color: var(--auth-primary);
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        .auth-shell {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 32px 16px;
        }

        .auth-page-wrap,
        .auth-narrow {
            width: 100%;
            max-width: 440px;
            margin: 0 auto;
        }

        .auth-card,
        .auth-simple-card,
        .tfa-card,
        .otp-card {
            background: var(--auth-panel);
            border: 1px solid var(--auth-border);
            border-radius: var(--auth-radius);
            box-shadow: var(--auth-shadow);
            backdrop-filter: blur(18px);
        }

        .auth-card {
            padding: 36px 30px 30px;
        }

        .auth-simple-card,
        .otp-card {
            padding: 32px 28px;
        }

        .auth-logo-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 20px;
        }

        .auth-logo-img,
        .merchant-logo {
            width: 72px;
            height: 72px;
            object-fit: contain;
            border-radius: 18px;
            background: #fff;
            border: 1px solid var(--auth-border);
            padding: 8px;
        }

        .auth-logo-icon {
            width: 72px;
            height: 72px;
            border-radius: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #59d0ff, #c06dff);
            color: #06111f;
            font-size: 2rem;
            font-weight: 800;
        }

        .auth-tagline,
        .auth-subtext,
        .auth-footer-copy,
        .auth-help-text {
            color: var(--auth-muted);
        }

        .auth-tagline,
        .auth-subtext {
            margin: 0 0 24px;
            text-align: center;
            font-size: 0.92rem;
            line-height: 1.6;
        }

        .auth-divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 20px 0;
            color: var(--auth-muted);
            font-size: 0.82rem;
        }

        .auth-divider::before,
        .auth-divider::after {
            content: "";
            flex: 1;
            height: 1px;
            background: var(--auth-border);
        }

        .alert {
            border-radius: var(--auth-radius-sm);
            padding: 12px 14px;
            font-size: 0.9rem;
            margin-bottom: 16px;
            border: 1px solid transparent;
        }

        .alert-error {
            background: rgba(255, 123, 123, 0.12);
            color: #ffd1d1;
            border-color: rgba(255, 123, 123, 0.3);
        }

        .alert-success {
            background: rgba(45, 219, 141, 0.12);
            color: #cbffe2;
            border-color: rgba(45, 219, 141, 0.28);
        }

        .alert-info {
            background: rgba(89, 208, 255, 0.12);
            color: #d8f7ff;
            border-color: rgba(89, 208, 255, 0.24);
        }

        .form-group {
            margin-bottom: 18px;
        }

        .form-label,
        .auth-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--auth-text);
            font-size: 0.93rem;
        }

        .form-label-note,
        .form-help {
            color: var(--auth-muted);
            font-size: 0.8rem;
            font-weight: 500;
        }

        .form-input,
        .form-control,
        .tfa-backup-input,
        .auth-code-input {
            width: 100%;
            background: rgba(8, 18, 33, 0.92);
            border: 1px solid var(--auth-border);
            color: var(--auth-text);
            border-radius: 12px;
            padding: 12px 14px;
            font: inherit;
            transition: border-color var(--auth-transition), box-shadow var(--auth-transition), background var(--auth-transition);
        }

        .form-input:focus,
        .form-control:focus,
        .tfa-backup-input:focus,
        .auth-code-input:focus {
            outline: none;
            border-color: var(--auth-border-strong);
            box-shadow: 0 0 0 3px rgba(89, 208, 255, 0.12);
            background: #0a1729;
        }

        .form-error {
            margin-top: 8px;
            color: #ffd1d1;
            font-size: 0.82rem;
        }

        .form-checkbox {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--auth-muted);
            font-size: 0.9rem;
        }

        .auth-inline-row,
        .auth-actions-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border-radius: 12px;
            border: 1px solid transparent;
            padding: 12px 16px;
            font: inherit;
            font-weight: 700;
            cursor: pointer;
            transition: transform var(--auth-transition), opacity var(--auth-transition), border-color var(--auth-transition), background var(--auth-transition);
            text-decoration: none;
        }

        .btn:hover {
            transform: translateY(-1px);
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #59d0ff, #87f3ff);
            color: var(--auth-primary-dark);
        }

        .btn-secondary {
            background: rgba(89, 208, 255, 0.08);
            border-color: var(--auth-border);
            color: var(--auth-text);
        }

        .auth-btn-block {
            width: 100%;
        }

        .auth-footer-copy {
            margin-top: 20px;
            text-align: center;
            font-size: 0.9rem;
        }

        .auth-resend-wrap {
            margin-top: 20px;
        }

        .auth-link-strong {
            font-weight: 700;
        }

        .oauth-group {
            margin-bottom: 16px;
        }

        .oauth-label {
            text-align: center;
            color: var(--auth-muted);
            font-size: 0.85rem;
            margin: 0 0 10px;
        }

        .oauth-row {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .oauth-button {
            width: 46px;
            height: 46px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
            transition: border-color var(--auth-transition), box-shadow var(--auth-transition), transform var(--auth-transition);
        }

        .oauth-button:hover,
        .oauth-button:focus-visible {
            border-color: #cbd5e1;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.12);
            transform: translateY(-1px);
            outline: none;
        }

        .captcha-row {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
            flex-wrap: wrap;
        }

        .captcha-image {
            border-radius: 8px;
            border: 1px solid var(--auth-border);
            cursor: pointer;
        }

        .captcha-refresh {
            background: none;
            border: none;
            color: var(--auth-primary);
            cursor: pointer;
            font: inherit;
            padding: 0;
        }

        .auth-code-input {
            text-align: center;
            font-size: 1.35rem;
            letter-spacing: 6px;
            padding: 14px;
        }

        .auth-code-note {
            display: block;
            margin-top: 8px;
            color: var(--auth-muted);
            font-size: 0.82rem;
            line-height: 1.5;
        }

        .auth-stack-sm {
            display: grid;
            gap: 14px;
        }

        .auth-center {
            text-align: center;
        }

        .auth-hero-icon {
            font-size: 2.5rem;
            margin-bottom: 12px;
        }

        .auth-title {
            margin: 0 0 8px;
            font-size: 1.6rem;
        }

        .auth-back-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-top: 18px;
            color: var(--auth-muted);
            font-size: 0.85rem;
        }

        @media (max-width: 520px) {
            .auth-shell { padding: 20px 12px; }
            .auth-card,
            .auth-simple-card,
            .otp-card,
            .tfa-card { padding: 24px 20px; }
            .auth-inline-row,
            .auth-actions-row { align-items: flex-start; }
        }
    </style>
<?php if ($extraStyles !== ''): ?>
    <style nonce="<?= View::e($nonce) ?>">
<?= $extraStyles ?>
    </style>
<?php endif; ?>
</head>
<body>
    <div class="auth-shell">
        <?php View::yield('content'); ?>
    </div>
<?php if ($extraScripts !== ''): ?>
    <script nonce="<?= View::e($nonce) ?>">
<?= $extraScripts ?>
    </script>
<?php endif; ?>
</body>
</html>
