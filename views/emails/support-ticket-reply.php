<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Reply on Ticket #<?= (int)$ticketId ?></title>
</head>
<body style="margin:0;padding:0;background:#f0f4f8;font-family:'Segoe UI',Arial,sans-serif;">
<table role="presentation" style="width:100%;border-collapse:collapse;">
<tr><td style="padding:32px 16px;">
<table role="presentation" style="max-width:600px;margin:0 auto;border-radius:12px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.10);">

    <!-- Header -->
    <tr><td style="background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%);padding:36px 40px 28px;text-align:center;">
        <div style="display:inline-block;width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#f59e0b,#ef4444);text-align:center;line-height:52px;font-size:24px;margin-bottom:14px;">💬</div>
        <h1 style="margin:0;color:#fff;font-size:22px;font-weight:700;letter-spacing:-.3px;">New Reply on Ticket #<?= (int)$ticketId ?></h1>
        <p style="margin:8px 0 0;color:rgba(255,255,255,.65);font-size:13px;">Support</p>
    </td></tr>

    <!-- Body -->
    <tr><td style="background:#fff;padding:32px 40px;">
        <p style="margin:0 0 6px;color:#374151;font-size:16px;font-weight:600;">Hi <?= htmlspecialchars($userName ?? 'User') ?>,</p>
        <p style="margin:0 0 20px;color:#6b7280;font-size:14px;line-height:1.7;">A support agent has replied to your ticket. See the message below.</p>

        <!-- Subject -->
        <div style="margin-bottom:16px;">
            <div style="color:#9ca3af;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;margin-bottom:6px;">Subject</div>
            <div style="color:#1f2937;font-size:14px;font-weight:600;padding:10px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;"><?= htmlspecialchars($subject ?? '') ?></div>
        </div>

        <!-- Reply -->
        <div style="margin-bottom:24px;">
            <div style="color:#9ca3af;font-size:11px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;margin-bottom:6px;">Agent Reply</div>
            <div style="padding:16px 18px;background:#fffbeb;border:1px solid #fde68a;border-left:4px solid #f59e0b;border-radius:0 8px 8px 0;color:#374151;font-size:14px;line-height:1.7;">
                <?= nl2br(htmlspecialchars($replyMessage ?? '')) ?>
            </div>
        </div>

        <?php if (!empty($status)): ?>
        <p style="margin:0 0 20px;color:#6b7280;font-size:14px;">
            Current status: <strong style="color:#f59e0b;"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $status))) ?></strong>
        </p>
        <?php endif; ?>

        <!-- CTA -->
        <table role="presentation" style="width:100%;border-collapse:collapse;">
        <tr><td style="text-align:center;padding:4px 0 8px;">
            <a href="<?= htmlspecialchars($ticketUrl ?? '#') ?>" style="display:inline-block;padding:13px 36px;background:linear-gradient(135deg,#f59e0b,#ef4444);border-radius:8px;color:#fff;font-size:15px;font-weight:700;text-decoration:none;">View &amp; Reply</a>
        </td></tr>
        </table>
    </td></tr>

    <!-- Footer -->
    <tr><td style="background:#f8fafc;padding:18px 40px;text-align:center;border-top:1px solid #e2e8f0;">
        <p style="margin:0;color:#9ca3af;font-size:12px;line-height:1.6;">You received this because there is an update on your support ticket.<br>Log in to view the full thread and reply.</p>
    </td></tr>

</table>
</td></tr>
</table>
</body>
</html>
