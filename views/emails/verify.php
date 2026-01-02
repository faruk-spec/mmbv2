<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email</title>
</head>
<body style="margin: 0; padding: 0; background-color: #06060a; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
    <table role="presentation" style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" style="max-width: 600px; margin: 0 auto; background: linear-gradient(135deg, #0c0c12 0%, #0f0f18 100%); border-radius: 16px; overflow: hidden; border: 1px solid rgba(255,255,255,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="padding: 40px 40px 20px; text-align: center; background: linear-gradient(135deg, rgba(0,240,255,0.1), rgba(255,46,196,0.1));">
                            <h1 style="margin: 0; color: #00f0ff; font-size: 28px; font-weight: 600;">
                                <?= APP_NAME ?>
                            </h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 40px;">
                            <h2 style="margin: 0 0 20px; color: #e8eefc; font-size: 24px; font-weight: 600;">
                                Verify Your Email Address
                            </h2>
                            
                            <p style="margin: 0 0 20px; color: #8892a6; font-size: 16px; line-height: 1.6;">
                                Hi <?= htmlspecialchars($name) ?>,
                            </p>
                            
                            <p style="margin: 0 0 30px; color: #8892a6; font-size: 16px; line-height: 1.6;">
                                Thank you for registering with <?= APP_NAME ?>. Please click the button below to verify your email address and activate your account.
                            </p>
                            
                            <div style="text-align: center; margin: 30px 0;">
                                <a href="<?= htmlspecialchars($verify_url) ?>" 
                                   style="display: inline-block; padding: 16px 40px; background: linear-gradient(135deg, #00f0ff, #ff2ec4); color: #06060a; text-decoration: none; font-weight: 600; font-size: 16px; border-radius: 8px;">
                                    Verify Email Address
                                </a>
                            </div>
                            
                            <p style="margin: 30px 0 0; color: #8892a6; font-size: 14px; line-height: 1.6;">
                                If you didn't create an account, you can safely ignore this email.
                            </p>
                            
                            <p style="margin: 20px 0 0; color: #8892a6; font-size: 14px; line-height: 1.6;">
                                If the button doesn't work, copy and paste this link into your browser:<br>
                                <a href="<?= htmlspecialchars($verify_url) ?>" style="color: #00f0ff; word-break: break-all;">
                                    <?= htmlspecialchars($verify_url) ?>
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
