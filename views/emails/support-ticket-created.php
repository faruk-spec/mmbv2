<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Ticket #<?= (int)$ticketId ?> Created</title>
</head>
<body style="margin:0;padding:0;background-color:#06060a;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
    <table role="presentation" style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="padding:40px 20px;">
                <table role="presentation" style="max-width:600px;margin:0 auto;background:linear-gradient(135deg,#0c0c12 0%,#0f0f18 100%);border-radius:16px;overflow:hidden;border:1px solid rgba(255,255,255,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="padding:36px 40px 20px;text-align:center;background:linear-gradient(135deg,rgba(0,240,255,0.08),rgba(255,46,196,0.08));">
                            <div style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#00f0ff,#ff2ec4);margin-bottom:14px;">
                                <span style="font-size:22px;">🎫</span>
                            </div>
                            <h1 style="margin:0;color:#00f0ff;font-size:22px;font-weight:700;">
                                Support Ticket #<?= (int)$ticketId ?> Created
                            </h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding:36px 40px;">
                            <h2 style="margin:0 0 16px;color:#e8eefc;font-size:20px;font-weight:600;">
                                Hello, <?= htmlspecialchars($userName ?? 'User') ?>!
                            </h2>

                            <p style="margin:0 0 18px;color:#8892a6;font-size:15px;line-height:1.7;">
                                Your support ticket has been received. Our team will review it and get back to you as soon as possible.
                            </p>

                            <!-- Ticket details box -->
                            <table role="presentation" style="width:100%;border-collapse:collapse;margin-bottom:26px;">
                                <tr>
                                    <td style="padding:20px;background:rgba(0,240,255,0.06);border:1px solid rgba(0,240,255,0.15);border-radius:10px;">
                                        <div style="margin-bottom:10px;">
                                            <span style="color:#8892a6;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Ticket ID</span>
                                            <div style="color:#00f0ff;font-size:16px;font-weight:700;margin-top:2px;">#<?= (int)$ticketId ?></div>
                                        </div>
                                        <div>
                                            <span style="color:#8892a6;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Subject</span>
                                            <div style="color:#e8eefc;font-size:15px;font-weight:500;margin-top:2px;"><?= htmlspecialchars($subject ?? '') ?></div>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <?php if (!empty($description)): ?>
                            <p style="margin:0 0 8px;color:#8892a6;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Your Message</p>
                            <table role="presentation" style="width:100%;border-collapse:collapse;margin-bottom:26px;">
                                <tr>
                                    <td style="padding:16px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:8px;color:#c4cad8;font-size:14px;line-height:1.6;">
                                        <?= nl2br(htmlspecialchars(substr($description, 0, 500))) ?><?= strlen($description ?? '') > 500 ? '...' : '' ?>
                                    </td>
                                </tr>
                            </table>
                            <?php endif; ?>

                            <!-- CTA button -->
                            <table role="presentation" style="width:100%;border-collapse:collapse;">
                                <tr>
                                    <td style="text-align:center;">
                                        <a href="<?= htmlspecialchars($ticketUrl ?? '#') ?>" style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#00f0ff,#ff2ec4);border-radius:8px;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;letter-spacing:.02em;">
                                            View Your Ticket
                                        </a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:20px 40px;text-align:center;border-top:1px solid rgba(255,255,255,0.06);">
                            <p style="margin:0;color:#5c6478;font-size:12px;line-height:1.6;">
                                You are receiving this because you submitted a support ticket.<br>
                                If you did not submit this ticket, please contact us immediately.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
