<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password</title>
</head>
<body style="margin: 0; padding: 0; background-color: #09090b; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background: #18181b; border-radius: 16px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 40px 40px 20px; text-align: center; background: linear-gradient(135deg, rgba(245,158,11,0.1), rgba(139,92,246,0.1));">
                            <h1 style="margin: 0; color: #f59e0b; font-size: 28px; font-weight: 600;">
                                <?= APP_NAME ?>
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <h2 style="margin: 0 0 20px; color: #e8eefc; font-size: 24px; font-weight: 600;">
                                Reset Your Password
                            </h2>
                            
                            <p style="margin: 0 0 20px; color: #8892a6; font-size: 16px; line-height: 1.6;">
                                Hi <?= htmlspecialchars($name) ?>,
                            </p>
                            
                            <p style="margin: 0 0 30px; color: #8892a6; font-size: 16px; line-height: 1.6;">
                                We received a request to reset your password. Click the button below to create a new password. This link will expire in 1 hour.
                            </p>
                            
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="<?= htmlspecialchars($reset_url) ?>" 
                                   style="display: inline-block; padding: 16px 40px; background: linear-gradient(135deg, #f59e0b, #8b5cf6); color: #ffffff; text-decoration: none; font-weight: 600; font-size: 16px; border-radius: 8px;">
                                    Reset Password
                                </a>
                            </div>
                            
                            <p style="margin: 30px 0 0; color: #8892a6; font-size: 14px; line-height: 1.6;">
                                If you didn't request a password reset, please ignore this email or contact support if you have concerns.
                            </p>
                            
                            <p style="margin: 20px 0 0; color: #8892a6; font-size: 14px; line-height: 1.6;">
                                If the button doesn't work, copy and paste this link into your browser:<br>
                                <a href="<?= htmlspecialchars($reset_url) ?>" style="color: #f59e0b; word-break: break-all;">
                                    <?= htmlspecialchars($reset_url) ?>
                                </a>
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
