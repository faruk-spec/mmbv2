<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #<?= (int)$ticketId ?> Status Update</title>
</head>
<body style="margin:0;padding:0;background-color:#06060a;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
    <table role="presentation" style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="padding:40px 20px;">
                <table role="presentation" style="max-width:600px;margin:0 auto;background:linear-gradient(135deg,#0c0c12 0%,#0f0f18 100%);border-radius:16px;overflow:hidden;border:1px solid rgba(255,255,255,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="padding:36px 40px 20px;text-align:center;background:linear-gradient(135deg,rgba(100,210,255,0.08),rgba(255,200,80,0.08));">
                            <div style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#64d2ff,#ffc850);margin-bottom:14px;">
                                <span style="font-size:22px;">🔔</span>
                            </div>
                            <h1 style="margin:0;color:#64d2ff;font-size:22px;font-weight:700;">
                                Ticket #<?= (int)$ticketId ?> Status Updated
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
                                The status of your support ticket has been updated by our team.
                            </p>

                            <!-- Ticket info -->
                            <table role="presentation" style="width:100%;border-collapse:collapse;margin-bottom:20px;">
                                <tr>
                                    <td style="padding:18px;background:rgba(100,210,255,0.06);border:1px solid rgba(100,210,255,0.15);border-radius:10px;">
                                        <div style="margin-bottom:12px;">
                                            <div style="color:#8892a6;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">Ticket ID</div>
                                            <div style="color:#64d2ff;font-size:16px;font-weight:700;">#<?= (int)$ticketId ?></div>
                                        </div>
                                        <div style="margin-bottom:12px;">
                                            <div style="color:#8892a6;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">Subject</div>
                                            <div style="color:#e8eefc;font-size:14px;font-weight:500;"><?= htmlspecialchars($subject ?? '') ?></div>
                                        </div>
                                        <div>
                                            <div style="color:#8892a6;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;margin-bottom:6px;">New Status</div>
                                            <span style="display:inline-block;padding:4px 12px;background:rgba(100,210,255,0.15);border:1px solid rgba(100,210,255,0.3);border-radius:20px;color:#64d2ff;font-size:13px;font-weight:600;">
                                                <?= htmlspecialchars(ucwords(str_replace('_', ' ', $status ?? ''))) ?>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <?php if (!empty($note)): ?>
                            <p style="margin:0 0 8px;color:#8892a6;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Note from Support</p>
                            <table role="presentation" style="width:100%;border-collapse:collapse;margin-bottom:26px;">
                                <tr>
                                    <td style="padding:16px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-left:3px solid #64d2ff;border-radius:0 8px 8px 0;color:#c4cad8;font-size:14px;line-height:1.7;">
                                        <?= nl2br(htmlspecialchars($note)) ?>
                                    </td>
                                </tr>
                            </table>
                            <?php endif; ?>

                            <!-- CTA button -->
                            <table role="presentation" style="width:100%;border-collapse:collapse;">
                                <tr>
                                    <td style="text-align:center;">
                                        <a href="<?= htmlspecialchars($ticketUrl ?? '#') ?>" style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#64d2ff,#ffc850);border-radius:8px;color:#06060a;font-size:15px;font-weight:700;text-decoration:none;letter-spacing:.02em;">
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
                                You are receiving this because you have an open support ticket.<br>
                                Log in to view your ticket and reply.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
