<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Ticket #<?= (int)$ticketId ?> Closed</title>
</head>
<body style="margin:0;padding:0;background-color:#09090b;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif;">
    <table role="presentation" style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="padding:40px 20px;">
                <table role="presentation" style="max-width:600px;margin:0 auto;background:#18181b;border-radius:16px;overflow:hidden;border:1px solid rgba(255,255,255,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="padding:36px 40px 20px;text-align:center;background:linear-gradient(135deg,rgba(34,197,94,0.08),rgba(59,130,246,0.08));">
                            <div style="display:inline-flex;align-items:center;justify-content:center;width:56px;height:56px;border-radius:50%;background:linear-gradient(135deg,#22c55e,#3b82f6);margin-bottom:14px;">
                                <span style="font-size:22px;">✅</span>
                            </div>
                            <h1 style="margin:0;color:#22c55e;font-size:22px;font-weight:700;">
                                Ticket #<?= (int)$ticketId ?> Closed
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
                                Your support ticket has been resolved and closed. Thank you for contacting us!
                            </p>

                            <!-- Ticket info -->
                            <table role="presentation" style="width:100%;border-collapse:collapse;margin-bottom:20px;">
                                <tr>
                                    <td style="padding:16px;background:rgba(34,197,94,0.05);border:1px solid rgba(34,197,94,0.15);border-radius:8px;">
                                        <div style="margin-bottom:10px;">
                                            <div style="color:#8892a6;font-size:12px;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">Ticket</div>
                                            <div style="color:#22c55e;font-size:16px;font-weight:700;">#<?= (int)$ticketId ?></div>
                                        </div>
                                        <div>
                                            <div style="color:#8892a6;font-size:12px;text-transform:uppercase;letter-spacing:.05em;margin-bottom:3px;">Subject</div>
                                            <div style="color:#e8eefc;font-size:14px;font-weight:500;"><?= htmlspecialchars($subject ?? '') ?></div>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <?php if (!empty($resolution)): ?>
                            <p style="margin:0 0 8px;color:#8892a6;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Resolution</p>
                            <table role="presentation" style="width:100%;border-collapse:collapse;margin-bottom:26px;">
                                <tr>
                                    <td style="padding:16px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-left:3px solid #22c55e;border-radius:0 8px 8px 0;color:#c4cad8;font-size:14px;line-height:1.7;">
                                        <?= nl2br(htmlspecialchars($resolution)) ?>
                                    </td>
                                </tr>
                            </table>
                            <?php endif; ?>

                            <p style="margin:0 0 24px;color:#8892a6;font-size:14px;line-height:1.6;">
                                If you still have questions or the issue recurs, please don't hesitate to open a new ticket.
                            </p>

                            <!-- CTA button -->
                            <table role="presentation" style="width:100%;border-collapse:collapse;">
                                <tr>
                                    <td style="text-align:center;">
                                        <a href="<?= htmlspecialchars($ticketUrl ?? '#') ?>" style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#22c55e,#3b82f6);border-radius:8px;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;">
                                            View Closed Ticket
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
                                This ticket has been permanently closed. Thank you for using our support system.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
