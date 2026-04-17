<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Sign-In to <?= APP_NAME ?></title>
</head>
<body style="margin: 0; padding: 0; background-color: #06060a; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background: linear-gradient(135deg, #0c0c12 0%, #0f0f18 100%); border-radius: 16px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 40px 40px 20px; text-align: center; background: linear-gradient(135deg, rgba(0,240,255,0.1), rgba(100,100,255,0.1));">
                            <h1 style="margin: 0; color: #00f0ff; font-size: 28px; font-weight: 600;">
                                New Sign-In Detected 🔐
                            </h1>
                        </td>
                    </tr>

                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <h2 style="margin: 0 0 20px; color: #e8eefc; font-size: 22px; font-weight: 600;">
                                Hello, <?= htmlspecialchars($name ?? '') ?>!
                            </h2>

                            <p style="margin: 0 0 20px; color: #8892a6; font-size: 16px; line-height: 1.6;">
                                We detected a new sign-in to your <?= APP_NAME ?> account.
                            </p>

                            <table role="presentation" style="width: 100%; margin-bottom: 30px; border: 1px solid rgba(255,255,255,0.08); border-radius: 8px; overflow: hidden;">
                                <tr>
                                    <td style="padding: 14px 20px; background: rgba(0,0,0,0.3); color: #8892a6; font-size: 13px; border-bottom: 1px solid rgba(255,255,255,0.06);">
                                        <strong style="color: #e8eefc;">IP Address:</strong>&nbsp; <?= htmlspecialchars($ip ?? 'Unknown') ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 14px 20px; background: rgba(0,0,0,0.3); color: #8892a6; font-size: 13px;">
                                        <strong style="color: #e8eefc;">Time:</strong>&nbsp; <?= htmlspecialchars($time ?? date('Y-m-d H:i:s')) ?>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin: 0 0 20px; color: #8892a6; font-size: 15px; line-height: 1.6;">
                                If this was you, no action is needed. If you did not sign in, please
                                <strong style="color: #ff4444;">change your password immediately</strong> and contact support.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding: 30px 40px; background: rgba(0,0,0,0.3); border-top: 1px solid rgba(255,255,255,0.1);">
                            <p style="margin: 0; color: #8892a6; font-size: 13px; text-align: center;">
                                &copy; <?= date('Y') ?> <?= APP_NAME ?>. All rights reserved.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
