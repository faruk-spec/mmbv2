-- Update email_verification template to include OTP code in the email body
UPDATE `mail_notification_templates`
SET
    `subject`   = 'Verify your email address — {{app_name}}',
    `body`      = '<h2>Hi {{name}},</h2><p>Thanks for registering! Please confirm your email address by entering the verification code below:</p><div style="text-align:center;margin:24px 0;"><span style="display:inline-block;font-size:36px;font-weight:700;letter-spacing:10px;background:#f4f7fb;border:2px dashed #667eea;border-radius:10px;padding:16px 32px;color:#333;">{{otp}}</span></div><p>This code expires in 24 hours. If you did not create an account, please ignore this email.</p>',
    `variables` = '["name","otp","verify_url","app_name"]'
WHERE `slug` = 'email_verification';

-- Update login_alert template variable names to match what the code sends
UPDATE `mail_notification_templates`
SET
    `body`      = '<h2>Hi {{name}},</h2><p>A new login was detected on your account.</p><table style="border-collapse:collapse;width:100%;margin:16px 0;"><tr><td style="padding:8px;border:1px solid #ddd;"><strong>IP Address</strong></td><td style="padding:8px;border:1px solid #ddd;">{{ip_address}}</td></tr><tr><td style="padding:8px;border:1px solid #ddd;"><strong>Time</strong></td><td style="padding:8px;border:1px solid #ddd;">{{login_time}}</td></tr></table><p>If this was not you, please <a href="{{reset_url}}">reset your password</a> immediately.</p>',
    `variables` = '["name","ip_address","login_time","reset_url","app_name"]',
    `is_enabled` = 1
WHERE `slug` = 'login_alert';

-- Update password_changed template variable names to match what the code sends
UPDATE `mail_notification_templates`
SET
    `body`      = '<h2>Hi {{name}},</h2><p>Your password was successfully changed on {{changed_at}}.</p><p>If you did not make this change, please <a href="{{reset_url}}">reset your password</a> immediately and contact support.</p>',
    `variables` = '["name","changed_at","reset_url","app_name"]'
WHERE `slug` = 'password_changed';

