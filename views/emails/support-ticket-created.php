<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Ticket #<?= (int)$ticketId ?> Created</title>
</head>
<body style="margin:0;padding:0;background:#f0f4f8;font-family:'Segoe UI',Arial,sans-serif;">
<table role="presentation" style="width:100%;border-collapse:collapse;">
<tr><td style="padding:32px 16px;">
<table role="presentation" style="max-width:600px;margin:0 auto;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.10);">

    <!-- Header -->
    <tr><td style="background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);padding:36px 40px 28px;text-align:center;">
        <div style="display:inline-block;width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#00c6ff,#a855f7);text-align:center;line-height:52px;font-size:24px;margin-bottom:14px;">🎫</div>
        <h1 style="margin:0;color:#fff;font-size:22px;font-weight:700;letter-spacing:-.3px;">Ticket #<?= (int)$ticketId ?> Created</h1>
        <p style="margin:8px 0 0;color:rgba(255,255,255,.65);font-size:13px;">Support</p>
    </td></tr>

    <!-- Body -->
    <tr><td style="background:#fff;padding:32px 40px;">
        <p style="margin:0 0 6px;color:#374151;font-size:16px;font-weight:600;">Hi <?= htmlspecialchars($userName ?? 'User') ?>,</p>
        <p style="margin:0 0 24px;color:#6b7280;font-size:14px;line-height:1.7;">Your support ticket has been received. Our team will review it and get back to you shortly.</p>

        <!-- Ticket details -->
        <table role="presentation" style="width:100%;border-collapse:collapse;border-radius:8px;overflow:hidden;margin-bottom:24px;">
        <tr><td style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;padding:20px;">
            <table style="width:100%;border-collapse:collapse;">
            <tr><td style="padding:0 0 12px;border-bottom:1px solid #e2e8f0;">
                <div style="color:#9ca3af;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;">Ticket ID</div>
                <div style="color:#00c6ff;font-size:20px;font-weight:800;">#<?= (int)$ticketId ?></div>
            </td></tr>
            <tr><td style="padding:12px 0 0;">
                <div style="color:#9ca3af;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;margin-bottom:4px;">Subject</div>
                <div style="color:#1f2937;font-size:14px;font-weight:600;"><?= htmlspecialchars($subject ?? '') ?></div>
            </td></tr>
            </table>
        </td></tr>
        </table>

        <?php if (!empty($description)): ?>
        <div style="margin-bottom:24px;">
            <div style="color:#9ca3af;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;margin-bottom:6px;">Your Message</div>
            <div style="padding:14px 16px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;color:#374151;font-size:14px;line-height:1.7;">
                <?= nl2br(htmlspecialchars(substr($description, 0, 500))) ?><?= strlen($description ?? '') > 500 ? '…' : '' ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- CTA -->
        <table role="presentation" style="width:100%;border-collapse:collapse;">
        <tr><td style="text-align:center;padding:4px 0 8px;">
            <a href="<?= htmlspecialchars($ticketUrl ?? '#') ?>" style="display:inline-block;padding:13px 36px;background:linear-gradient(135deg,#00c6ff,#a855f7);border-radius:8px;color:#fff;font-size:15px;font-weight:700;text-decoration:none;letter-spacing:.02em;">View Your Ticket</a>
        </td></tr>
        </table>
    </td></tr>

    <!-- Footer -->
    <tr><td style="background:#f8fafc;padding:18px 40px;text-align:center;border-top:1px solid #e2e8f0;">
        <p style="margin:0;color:#9ca3af;font-size:12px;line-height:1.6;">You received this email because you opened a support ticket.<br>If this wasn't you, please ignore this message.</p>
    </td></tr>

</table>
</td></tr>
</table>
</body>
</html>
