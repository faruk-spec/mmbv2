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

        .auth-security-note {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin: 0 auto 20px;
            padding: 7px 12px;
            border-radius: 999px;
            border: 1px solid var(--auth-border);
            background: var(--auth-primary-soft);
            color: var(--auth-text);
            font-size: 0.78rem;
            font-weight: 600;
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

        /* Animated particles canvas */
        #auth-particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }

        .auth-shell {
            position: relative;
            z-index: 1;
        }
    </style>
<?php if ($extraStyles !== ''): ?>
    <style nonce="<?= View::e($nonce) ?>">
<?= $extraStyles ?>
    </style>
<?php endif; ?>
</head>
<body>
    <canvas id="auth-particles"></canvas>
    <?php include BASE_PATH . '/views/layouts/navbar.php'; ?>
    <div class="auth-shell">
        <?php View::yield('content'); ?>
    </div>
<?php if ($extraScripts !== ''): ?>
    <script nonce="<?= View::e($nonce) ?>">
<?= $extraScripts ?>
    </script>
<?php endif; ?>
<script nonce="<?= View::e($nonce) ?>">
(function () {
    var canvas = document.getElementById('auth-particles');
    if (!canvas) return;
    var ctx = canvas.getContext('2d');
    var nodes = [], packets = [], raf, time = 0;
    var CONNECT_DIST = 140;
    var MAX_PACKETS  = 30;

    function resize() { canvas.width = window.innerWidth; canvas.height = window.innerHeight; }
    function rnd(a, b) { return a + Math.random() * (b - a); }
    function globalHue() { return 185 + 85 * (0.5 + 0.5 * Math.sin(time * 0.025)); }

    function createNode() {
        var depth = Math.floor(Math.random() * 3);
        var sf = 1 - depth * 0.3;
        return {
            x: rnd(0, canvas.width), y: rnd(0, canvas.height),
            depth: depth, r: rnd(1.0, 2.5) + (2 - depth) * 0.6,
            dx: rnd(-0.25, 0.25) * sf, dy: rnd(-0.25, 0.25) * sf,
            baseAlpha: rnd(0.4, 0.75) + (2 - depth) * 0.1,
            phase: rnd(0, Math.PI * 2), pulse: 0
        };
    }

    function init() {
        nodes = []; packets = [];
        var count = Math.min(70, Math.floor(canvas.width * canvas.height / 12000));
        for (var i = 0; i < count; i++) nodes.push(createNode());
    }

    function gradLine(x1, y1, h1, x2, y2, h2, alpha, lineW) {
        var g = ctx.createLinearGradient(x1, y1, x2, y2);
        g.addColorStop(0, 'hsla(' + h1 + ',100%,65%,' + alpha + ')');
        g.addColorStop(1, 'hsla(' + h2 + ',100%,65%,' + alpha + ')');
        ctx.lineWidth = lineW; ctx.strokeStyle = g;
        ctx.beginPath(); ctx.moveTo(x1, y1); ctx.lineTo(x2, y2); ctx.stroke();
    }

    function draw() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        time += 0.016;
        var gHue = globalHue();
        var edges = [];

        for (var ii = 0; ii < nodes.length - 1; ii++) {
            for (var jj = ii + 1; jj < nodes.length; jj++) {
                var na = nodes[ii], nb = nodes[jj];
                var ddx = na.x - nb.x, ddy = na.y - nb.y;
                var dist = Math.sqrt(ddx * ddx + ddy * ddy);
                if (dist < CONNECT_DIST) {
                    var dDepth = Math.abs(na.depth - nb.depth);
                    var lineA = (1 - dist / CONNECT_DIST) * 0.38 * (1 - dDepth * 0.2);
                    var pulseBoost = 1 + Math.max(na.pulse, nb.pulse) * 2;
                    lineA = Math.min(lineA * pulseBoost, 0.75);
                    var lineW = (0.75 - dDepth * 0.12) * (1 + Math.max(na.pulse, nb.pulse) * 0.6);
                    var h1 = gHue + (ii % 7 - 3) * 12, h2 = gHue + (jj % 7 - 3) * 12;
                    gradLine(na.x, na.y, h1, nb.x, nb.y, h2, lineA, lineW);
                    edges.push(ii, jj);
                }
            }
        }

        if (packets.length < MAX_PACKETS && edges.length > 0 && Math.random() < 0.08) {
            var pick = Math.floor(Math.random() * (edges.length / 2)) * 2;
            packets.push({ ai: edges[pick], bi: edges[pick + 1], t: 0,
                speed: rnd(0.012, 0.028), hue: gHue + rnd(-45, 45), trail: [] });
            if (nodes[edges[pick]]) nodes[edges[pick]].pulse = 1;
        }

        for (var k = packets.length - 1; k >= 0; k--) {
            var pk = packets[k];
            var pna = nodes[pk.ai], pnb = nodes[pk.bi];
            if (!pna || !pnb) { packets.splice(k, 1); continue; }
            pk.t += pk.speed;
            if (pk.t >= 1) { if (pnb) pnb.pulse = 1; packets.splice(k, 1); continue; }
            var px = pna.x + (pnb.x - pna.x) * pk.t;
            var py = pna.y + (pnb.y - pna.y) * pk.t;
            pk.trail.push({ x: px, y: py });
            if (pk.trail.length > 8) pk.trail.shift();
            for (var ti = 0; ti < pk.trail.length; ti++) {
                var tf = (ti + 1) / pk.trail.length;
                ctx.save(); ctx.globalAlpha = tf * 0.55;
                ctx.beginPath(); ctx.arc(pk.trail[ti].x, pk.trail[ti].y, 1.4 * tf, 0, Math.PI * 2);
                ctx.fillStyle = 'hsla(' + pk.hue + ',100%,82%,1)'; ctx.fill(); ctx.restore();
            }
            ctx.save(); ctx.shadowBlur = 14;
            ctx.shadowColor = 'hsla(' + pk.hue + ',100%,85%,1)';
            ctx.beginPath(); ctx.arc(px, py, 2.6, 0, Math.PI * 2);
            ctx.fillStyle = 'hsla(' + pk.hue + ',100%,92%,1)'; ctx.fill(); ctx.restore();
        }

        for (var ni = 0; ni < nodes.length; ni++) {
            var p = nodes[ni];
            var tw = p.baseAlpha * (0.55 + 0.45 * Math.sin(time * 1.4 + p.phase));
            var nHue = gHue + (p.depth - 1) * 28;
            var glowMult = 1 + p.pulse * 3.5;
            ctx.save(); ctx.shadowBlur = p.r * 9 * glowMult;
            ctx.shadowColor = 'hsla(' + nHue + ',100%,72%,' + Math.min(0.9 * glowMult, 1) + ')';
            ctx.beginPath(); ctx.arc(p.x, p.y, p.r * (1 + p.pulse * 0.6), 0, Math.PI * 2);
            ctx.fillStyle = 'hsla(' + nHue + ',100%,80%,' + Math.min(tw + p.pulse * 0.45, 1) + ')';
            ctx.fill(); ctx.restore();
            if (p.pulse > 0) p.pulse = Math.max(0, p.pulse - 0.035);
            p.x += p.dx; p.y += p.dy;
            if (p.x < -10) p.x = canvas.width + 10;
            if (p.x > canvas.width + 10) p.x = -10;
            if (p.y < -10) p.y = canvas.height + 10;
            if (p.y > canvas.height + 10) p.y = -10;
        }

        raf = requestAnimationFrame(draw);
    }

    resize(); init(); draw();
    window.addEventListener('resize', function () { cancelAnimationFrame(raf); resize(); init(); draw(); });
}());
</script>
</body>
</html>
