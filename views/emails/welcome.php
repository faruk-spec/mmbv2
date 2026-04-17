<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to <?= APP_NAME ?></title>
</head>
<body style="margin: 0; padding: 0; background-color: #09090b; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background: #18181b; border-radius: 16px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 40px 40px 20px; text-align: center; background: rgba(59,130,246,0.06);">
                            <h1 style="margin: 0; color: #22c55e; font-size: 28px; font-weight: 600;">
                                Welcome to <?= APP_NAME ?>! 🎉
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <h2 style="margin: 0 0 20px; color: #e8eefc; font-size: 24px; font-weight: 600;">
                                Hello, <?= htmlspecialchars($name) ?>!
                            </h2>
                            
                            <p style="margin: 0 0 20px; color: #8892a6; font-size: 16px; line-height: 1.6;">
                                Your account has been created successfully. We're excited to have you on board!
                            </p>
                            
                            <p style="margin: 0 0 30px; color: #8892a6; font-size: 16px; line-height: 1.6;">
                                With <?= APP_NAME ?>, you have access to multiple powerful tools:
                            </p>
                            
                            <table role="presentation" style="width: 100%; margin-bottom: 30px;">
                                <tr>
                                    <td style="padding: 15px; background: rgba(59,130,246,0.1); border-radius: 8px; margin-bottom: 10px;">
                                        <strong style="color: #3b82f6;">QR Generator</strong>
                                        <p style="margin: 5px 0 0; color: #8892a6; font-size: 14px;">Create custom QR codes instantly</p>
                                    </td>
                                </tr>
                                <tr><td style="height: 10px;"></td></tr>
                                <tr>
                                    <td style="padding: 15px; background: rgba(139,92,246,0.1); border-radius: 8px;">
                                        <strong style="color: #8b5cf6;">File Sharing</strong>
                                        <p style="margin: 5px 0 0; color: #8892a6; font-size: 14px;">Secure file sharing platform</p>
                                    </td>
                                </tr>
                                <tr><td style="height: 10px;"></td></tr>
                                <tr>
                                    <td style="padding: 15px; background: rgba(34,197,94,0.1); border-radius: 8px;">
                                        <strong style="color: #22c55e;">And More...</strong>
                                        <p style="margin: 5px 0 0; color: #8892a6; font-size: 14px;">Explore all our tools in your dashboard</p>
                                    </td>
                                </tr>
                            </table>
                            
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="<?= htmlspecialchars($login_url) ?>" 
                                   style="display: inline-block; padding: 16px 40px; background: #3b82f6; color: #ffffff; text-decoration: none; font-weight: 600; font-size: 16px; border-radius: 8px;">
                                    Go to Dashboard
                                </a>
                            </div>
                            
                            <p style="margin: 30px 0 0; color: #8892a6; font-size: 14px; line-height: 1.6;">
                                If you have any questions, feel free to reach out to our support team.
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
