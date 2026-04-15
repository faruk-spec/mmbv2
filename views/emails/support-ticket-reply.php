<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update on your Support Ticket #<?= (int)$ticketId ?></title>
</head>
<body style="margin:0;padding:0;background-color:#06060a;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
    <table role="presentation" style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="padding:40px 20px;">
                <table role="presentation" style="max-width:600px;margin:0 auto;background:linear-gradient(135deg,#0c0c12 0%,#0f0f18 100%);border-radius:16px;overflow:hidden;border:1px solid rgba(255,255,255,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="padding:36px 40px 20px;text-align:center;background:linear-gradient(135deg,rgba(255,159,67,0.08),rgba(255,107,107,0.08));">
                            <div style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#ff9f43,#ff6b6b);margin-bottom:14px;">
                                <span style="font-size:22px;">💬</span>
                            </div>
                            <h1 style="margin:0;color:#ff9f43;font-size:22px;font-weight:700;">
                                Update on Ticket #<?= (int)$ticketId ?>
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
                                Our support team has replied to your ticket.
                            </p>

                            <!-- Ticket info -->
                            <table role="presentation" style="width:100%;border-collapse:collapse;margin-bottom:20px;">
                                <tr>
                                    <td style="padding:14px 16px;background:rgba(255,159,67,0.06);border:1px solid rgba(255,159,67,0.15);border-radius:8px;">
                                        <div style="color:#8892a6;font-size:12px;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Subject</div>
                                        <div style="color:#e8eefc;font-size:14px;font-weight:500;"><?= htmlspecialchars($subject ?? '') ?></div>
                                    </td>
                                </tr>
                            </table>

                            <!-- Reply -->
                            <p style="margin:0 0 8px;color:#8892a6;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Agent Reply</p>
                            <table role="presentation" style="width:100%;border-collapse:collapse;margin-bottom:20px;">
                                <tr>
                                    <td style="padding:18px;background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.08);border-left:3px solid #ff9f43;border-radius:0 8px 8px 0;color:#c4cad8;font-size:14px;line-height:1.7;">
                                        <?= nl2br(htmlspecialchars($replyMessage ?? '')) ?>
                                    </td>
                                </tr>
                            </table>

                            <!-- Status -->
                            <?php if (!empty($status)): ?>
                            <p style="margin:0 0 20px;color:#8892a6;font-size:14px;">
                                Current status: <strong style="color:#ff9f43;"><?= htmlspecialchars(ucwords(str_replace('_',' ',$status))) ?></strong>
                            </p>
                            <?php endif; ?>

                            <!-- CTA button -->
                            <table role="presentation" style="width:100%;border-collapse:collapse;">
                                <tr>
                                    <td style="text-align:center;">
                                        <a href="<?= htmlspecialchars($ticketUrl ?? '#') ?>" style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#ff9f43,#ff6b6b);border-radius:8px;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;">
                                            View &amp; Reply
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
                                You are receiving this because you have an open support ticket.<br>
                                Log in to reply or close this ticket.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
