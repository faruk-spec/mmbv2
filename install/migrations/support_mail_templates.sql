-- Support Ticket Mail Templates Migration
-- Inserts four support-ticket email templates into mail_notification_templates
-- so admins can edit them via /admin/mail/templates.
--
-- Variables available in each template (use {{variable_name}} syntax):
--   All:          {{ticket_id}}, {{subject}}, {{user_name}}, {{ticket_url}}
--   Created:      {{description}}
--   Reply:        {{reply_message}}, {{status}}
--   Closed:       {{resolution}}
--   Status-update:{{status}}, {{note}}

INSERT IGNORE INTO `mail_notification_templates`
    (`slug`, `name`, `subject`, `body`, `variables`, `is_enabled`)
VALUES

-- ── Ticket created ──────────────────────────────────────────────────────────
('support-ticket-created',
 'Support: Ticket Created',
 'Support Ticket #{{ticket_id}} Created: {{subject}}',
'<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background-color:#06060a;font-family:Segoe UI,Tahoma,Geneva,Verdana,sans-serif;">
  <table role="presentation" style="width:100%;border-collapse:collapse;">
    <tr><td style="padding:40px 20px;">
      <table role="presentation" style="max-width:600px;margin:0 auto;background:linear-gradient(135deg,#0c0c12 0%,#0f0f18 100%);border-radius:16px;overflow:hidden;border:1px solid rgba(255,255,255,0.1);">
        <tr><td style="padding:36px 40px 20px;text-align:center;background:linear-gradient(135deg,rgba(0,240,255,0.08),rgba(255,46,196,0.08));">
          <h1 style="margin:0;color:#00f0ff;font-size:22px;font-weight:700;">&#127915; Support Ticket #{{ticket_id}} Created</h1>
        </td></tr>
        <tr><td style="padding:36px 40px;">
          <h2 style="margin:0 0 16px;color:#e8eefc;font-size:20px;font-weight:600;">Hello, {{user_name}}!</h2>
          <p style="margin:0 0 18px;color:#8892a6;font-size:15px;line-height:1.7;">
            Your support ticket has been received. Our team will review it and get back to you as soon as possible.
          </p>
          <table role="presentation" style="width:100%;border-collapse:collapse;margin-bottom:26px;">
            <tr><td style="padding:20px;background:rgba(0,240,255,0.06);border:1px solid rgba(0,240,255,0.15);border-radius:10px;">
              <div style="margin-bottom:10px;">
                <span style="color:#8892a6;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Ticket ID</span>
                <div style="color:#00f0ff;font-size:16px;font-weight:700;margin-top:2px;">#{{ticket_id}}</div>
              </div>
              <div>
                <span style="color:#8892a6;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Subject</span>
                <div style="color:#e8eefc;font-size:15px;font-weight:500;margin-top:2px;">{{subject}}</div>
              </div>
            </td></tr>
          </table>
          <table role="presentation" style="width:100%;border-collapse:collapse;">
            <tr><td style="text-align:center;">
              <a href="{{ticket_url}}" style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#00f0ff,#ff2ec4);border-radius:8px;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;">View Your Ticket</a>
            </td></tr>
          </table>
        </td></tr>
        <tr><td style="padding:20px 40px;text-align:center;border-top:1px solid rgba(255,255,255,0.06);">
          <p style="margin:0;color:#5c6478;font-size:12px;line-height:1.6;">You are receiving this because you submitted a support ticket.</p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>',
'["ticket_id","subject","user_name","ticket_url","description"]', 1),

-- ── Agent/user reply ─────────────────────────────────────────────────────────
('support-ticket-reply',
 'Support: New Reply on Ticket',
 'Update on your Support Ticket #{{ticket_id}}',
'<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background-color:#06060a;font-family:Segoe UI,Tahoma,Geneva,Verdana,sans-serif;">
  <table role="presentation" style="width:100%;border-collapse:collapse;">
    <tr><td style="padding:40px 20px;">
      <table role="presentation" style="max-width:600px;margin:0 auto;background:linear-gradient(135deg,#0c0c12 0%,#0f0f18 100%);border-radius:16px;overflow:hidden;border:1px solid rgba(255,255,255,0.1);">
        <tr><td style="padding:36px 40px 20px;text-align:center;background:linear-gradient(135deg,rgba(0,240,255,0.08),rgba(255,46,196,0.08));">
          <h1 style="margin:0;color:#00f0ff;font-size:22px;font-weight:700;">&#128172; New Reply on Ticket #{{ticket_id}}</h1>
        </td></tr>
        <tr><td style="padding:36px 40px;">
          <h2 style="margin:0 0 16px;color:#e8eefc;font-size:20px;font-weight:600;">Hello, {{user_name}}!</h2>
          <p style="margin:0 0 8px;color:#8892a6;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Subject</p>
          <p style="margin:0 0 18px;color:#e8eefc;font-size:15px;">{{subject}}</p>
          <p style="margin:0 0 8px;color:#8892a6;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Reply</p>
          <table role="presentation" style="width:100%;border-collapse:collapse;margin-bottom:26px;">
            <tr><td style="padding:16px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.07);border-radius:8px;color:#c4cad8;font-size:14px;line-height:1.6;">{{reply_message}}</td></tr>
          </table>
          <table role="presentation" style="width:100%;border-collapse:collapse;">
            <tr><td style="text-align:center;">
              <a href="{{ticket_url}}" style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#00f0ff,#ff2ec4);border-radius:8px;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;">View Ticket</a>
            </td></tr>
          </table>
        </td></tr>
        <tr><td style="padding:20px 40px;text-align:center;border-top:1px solid rgba(255,255,255,0.06);">
          <p style="margin:0;color:#5c6478;font-size:12px;line-height:1.6;">You are receiving this because there is an update on your support ticket.</p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>',
'["ticket_id","subject","user_name","ticket_url","reply_message","status"]', 1),

-- ── Ticket closed / resolved ─────────────────────────────────────────────────
('support-ticket-closed',
 'Support: Ticket Resolved/Closed',
 'Support Ticket #{{ticket_id}} Has Been Resolved',
'<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background-color:#06060a;font-family:Segoe UI,Tahoma,Geneva,Verdana,sans-serif;">
  <table role="presentation" style="width:100%;border-collapse:collapse;">
    <tr><td style="padding:40px 20px;">
      <table role="presentation" style="max-width:600px;margin:0 auto;background:linear-gradient(135deg,#0c0c12 0%,#0f0f18 100%);border-radius:16px;overflow:hidden;border:1px solid rgba(255,255,255,0.1);">
        <tr><td style="padding:36px 40px 20px;text-align:center;background:linear-gradient(135deg,rgba(0,240,255,0.08),rgba(22,163,74,0.08));">
          <h1 style="margin:0;color:#16a34a;font-size:22px;font-weight:700;">&#9989; Ticket #{{ticket_id}} Resolved</h1>
        </td></tr>
        <tr><td style="padding:36px 40px;">
          <h2 style="margin:0 0 16px;color:#e8eefc;font-size:20px;font-weight:600;">Hello, {{user_name}}!</h2>
          <p style="margin:0 0 18px;color:#8892a6;font-size:15px;line-height:1.7;">
            Your support ticket has been marked as resolved. We hope your issue was addressed.
          </p>
          <p style="margin:0 0 8px;color:#8892a6;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Subject</p>
          <p style="margin:0 0 18px;color:#e8eefc;font-size:15px;">{{subject}}</p>
          <p style="margin:0 0 8px;color:#8892a6;font-size:13px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Resolution</p>
          <table role="presentation" style="width:100%;border-collapse:collapse;margin-bottom:26px;">
            <tr><td style="padding:16px;background:rgba(22,163,74,0.06);border:1px solid rgba(22,163,74,0.2);border-radius:8px;color:#c4cad8;font-size:14px;line-height:1.6;">{{resolution}}</td></tr>
          </table>
          <table role="presentation" style="width:100%;border-collapse:collapse;">
            <tr><td style="text-align:center;">
              <a href="{{ticket_url}}" style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#00f0ff,#ff2ec4);border-radius:8px;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;">View Ticket</a>
            </td></tr>
          </table>
        </td></tr>
        <tr><td style="padding:20px 40px;text-align:center;border-top:1px solid rgba(255,255,255,0.06);">
          <p style="margin:0;color:#5c6478;font-size:12px;line-height:1.6;">If your issue has not been resolved, please re-open the ticket from the link above.</p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>',
'["ticket_id","subject","user_name","ticket_url","resolution"]', 1),

-- ── Status update (non-closed) ───────────────────────────────────────────────
('support-ticket-status-update',
 'Support: Ticket Status Updated',
 'Support Ticket #{{ticket_id}} Status Updated: {{status}}',
'<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background-color:#06060a;font-family:Segoe UI,Tahoma,Geneva,Verdana,sans-serif;">
  <table role="presentation" style="width:100%;border-collapse:collapse;">
    <tr><td style="padding:40px 20px;">
      <table role="presentation" style="max-width:600px;margin:0 auto;background:linear-gradient(135deg,#0c0c12 0%,#0f0f18 100%);border-radius:16px;overflow:hidden;border:1px solid rgba(255,255,255,0.1);">
        <tr><td style="padding:36px 40px 20px;text-align:center;background:linear-gradient(135deg,rgba(0,240,255,0.08),rgba(217,119,6,0.08));">
          <h1 style="margin:0;color:#d97706;font-size:22px;font-weight:700;">&#128260; Ticket #{{ticket_id}} Status Changed</h1>
        </td></tr>
        <tr><td style="padding:36px 40px;">
          <h2 style="margin:0 0 16px;color:#e8eefc;font-size:20px;font-weight:600;">Hello, {{user_name}}!</h2>
          <p style="margin:0 0 18px;color:#8892a6;font-size:15px;line-height:1.7;">
            The status of your support ticket has been updated.
          </p>
          <table role="presentation" style="width:100%;border-collapse:collapse;margin-bottom:26px;">
            <tr><td style="padding:20px;background:rgba(0,240,255,0.06);border:1px solid rgba(0,240,255,0.15);border-radius:10px;">
              <div style="margin-bottom:10px;">
                <span style="color:#8892a6;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Subject</span>
                <div style="color:#e8eefc;font-size:15px;font-weight:500;margin-top:2px;">{{subject}}</div>
              </div>
              <div>
                <span style="color:#8892a6;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">New Status</span>
                <div style="color:#d97706;font-size:16px;font-weight:700;margin-top:2px;">{{status}}</div>
              </div>
            </td></tr>
          </table>
          <table role="presentation" style="width:100%;border-collapse:collapse;">
            <tr><td style="text-align:center;">
              <a href="{{ticket_url}}" style="display:inline-block;padding:14px 32px;background:linear-gradient(135deg,#00f0ff,#ff2ec4);border-radius:8px;color:#ffffff;font-size:15px;font-weight:700;text-decoration:none;">View Ticket</a>
            </td></tr>
          </table>
        </td></tr>
        <tr><td style="padding:20px 40px;text-align:center;border-top:1px solid rgba(255,255,255,0.06);">
          <p style="margin:0;color:#5c6478;font-size:12px;line-height:1.6;">You are receiving this because the status of your support ticket changed.</p>
        </td></tr>
      </table>
    </td></tr>
  </table>
</body>
</html>',
'["ticket_id","subject","user_name","ticket_url","status","note"]', 1);
