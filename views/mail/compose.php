<?php
// Pre-designed mail templates
$_mailTemplates = [
    ['name' => 'Blank', 'subject' => '', 'body' => ''],
    ['name' => 'Professional Letter', 'subject' => 'Important Update',
     'body' => '<p>Dear {Name},</p><p>I hope this email finds you well. I wanted to reach out regarding…</p><p>Please let me know if you have any questions or concerns. I look forward to your response.</p><p>Best regards,<br>{Your Name}<br>{Your Title}</p>'],
    ['name' => 'Newsletter', 'subject' => 'Our Latest Updates',
     'body' => '<table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;font-family:Arial,sans-serif;"><tr><td style="background:#667eea;padding:32px;text-align:center;"><h1 style="color:#fff;margin:0;font-size:24px;">Newsletter</h1></td></tr><tr><td style="padding:32px;background:#ffffff;"><h2 style="color:#333;font-size:18px;">Hello {Name},</h2><p style="color:#555;line-height:1.6;">Here are the latest updates from us…</p><p style="text-align:center;margin-top:28px;"><a href="#" style="background:#667eea;color:#fff;padding:12px 28px;text-decoration:none;border-radius:6px;font-weight:600;">Read More</a></p></td></tr><tr><td style="padding:16px;background:#f9f9f9;text-align:center;font-size:12px;color:#999;">You received this email because you subscribed to our newsletter. <a href="#" style="color:#667eea;">Unsubscribe</a></td></tr></table>'],
    ['name' => 'Meeting Invitation', 'subject' => 'Meeting Invitation: {Topic}',
     'body' => '<p>Hi {Name},</p><p>You are invited to a meeting:</p><table style="border-collapse:collapse;width:100%;max-width:500px;margin:16px 0;"><tr><td style="padding:10px;border:1px solid #ddd;font-weight:600;background:#f5f5f5;width:140px;">Topic</td><td style="padding:10px;border:1px solid #ddd;">{Topic}</td></tr><tr><td style="padding:10px;border:1px solid #ddd;font-weight:600;background:#f5f5f5;">Date &amp; Time</td><td style="padding:10px;border:1px solid #ddd;">{DateTime}</td></tr><tr><td style="padding:10px;border:1px solid #ddd;font-weight:600;background:#f5f5f5;">Location</td><td style="padding:10px;border:1px solid #ddd;">{Location}</td></tr></table><p>Please confirm your attendance by replying to this email.</p><p>Best regards,<br>{Your Name}</p>'],
    ['name' => 'Follow-Up', 'subject' => 'Following up on our conversation',
     'body' => '<p>Hi {Name},</p><p>I wanted to follow up on our previous conversation regarding {Topic}.</p><p>Could you please let me know the status? I would appreciate an update at your earliest convenience.</p><p>Thank you for your time.</p><p>Best regards,<br>{Your Name}</p>'],
    ['name' => 'Invoice / Payment', 'subject' => 'Invoice #{InvoiceNo} – Payment Due',
     'body' => '<p>Dear {Name},</p><p>Please find below your invoice for <strong>{Amount}</strong>, due on <strong>{DueDate}</strong>.</p><table style="width:100%;border-collapse:collapse;margin:20px 0;"><tr style="background:#f5f5f5;"><th style="padding:10px;border:1px solid #ddd;text-align:left;">Description</th><th style="padding:10px;border:1px solid #ddd;text-align:right;">Amount</th></tr><tr><td style="padding:10px;border:1px solid #ddd;">{ServiceDescription}</td><td style="padding:10px;border:1px solid #ddd;text-align:right;">{Amount}</td></tr></table><p>Questions? Reply to this email.</p>'],
];
?>
<?php use Core\View; use Core\Security; ?>
<?php $pageTitle = 'Compose'; ?>
<?php View::extend('mail'); ?>
<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <h2 style="margin:0;font-size:18px;font-weight:600;">Compose New Email</h2>
    <a href="/mail" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Discard</a>
</div>

<div class="mail-card">
    <form method="POST" action="/mail/compose" id="mailComposeForm">
        <?= Security::csrfField() ?>

        <!-- From (provider selector) -->
        <?php if (!empty($providers)): ?>
        <div class="form-group">
            <label class="form-label">From</label>
            <select name="provider_id" class="form-input" style="cursor:pointer;">
                <?php foreach ($providers as $p): ?>
                <option value="<?= (int)$p['id'] ?>" <?= $p['is_active'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars(
                        ($p['from_name'] ? $p['from_name'] . ' <' . $p['from_email'] . '>' : $p['from_email'])
                        . ' [' . strtoupper($p['provider_type']) . ']'
                        . ($p['is_active'] ? ' ✓' : ''),
                        ENT_QUOTES, 'UTF-8'
                    ) ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>

        <!-- To (multi-tag input) -->
        <div class="form-group" style="position:relative;">
            <label class="form-label">To <span style="color:#e74c3c;">*</span>
                <span style="font-weight:400;text-transform:none;font-size:11px;color:#64748b;">(comma-separated for multiple)</span>
            </label>
            <div id="mailTagWrap" style="display:flex;flex-wrap:wrap;gap:6px;align-items:center;padding:8px 12px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:8px;min-height:42px;cursor:text;position:relative;" onclick="document.getElementById('mailToInput').focus()">
                <div id="mailToTags" style="display:contents;"></div>
                <input type="text" id="mailToInput" placeholder="recipient@example.com"
                       style="flex:1;min-width:180px;background:none;border:none;outline:none;color:#e2e8f0;font-size:13px;padding:2px 0;"
                       value="<?= htmlspecialchars($_GET['to'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                       autocomplete="off">
            </div>
            <div id="mailToSuggest" style="display:none;position:absolute;background:#1e1e2e;border:1px solid rgba(255,255,255,.12);border-radius:8px;width:100%;z-index:999;box-shadow:0 8px 24px rgba(0,0,0,.4);overflow:hidden;top:calc(100% + 4px);left:0;"></div>
            <input type="hidden" name="to" id="mailToHidden" value="<?= htmlspecialchars($_GET['to'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group">
                <label class="form-label">CC</label>
                <input type="text" name="cc" class="form-input" placeholder="cc@example.com, cc2@example.com">
            </div>
            <div class="form-group">
                <label class="form-label">BCC</label>
                <input type="text" name="bcc" class="form-input" placeholder="bcc@example.com">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Subject <span style="color:#e74c3c;">*</span></label>
            <input type="text" name="subject" id="mailSubject" class="form-input" required placeholder="Email subject"
                   value="<?= htmlspecialchars($_GET['subject'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <!-- Template picker -->
        <div class="form-group">
            <label class="form-label">Template</label>
            <select id="mailTemplateSelect" class="form-input" style="cursor:pointer;" onchange="mailApplyTemplate(this.value)">
                <?php foreach ($_mailTemplates as $i => $tpl): ?>
                <option value="<?= $i ?>"><?= htmlspecialchars($tpl['name'], ENT_QUOTES, 'UTF-8') ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label class="form-label" style="display:flex;justify-content:space-between;align-items:center;">
                Message
                <button type="button" class="btn btn-sm btn-secondary" onclick="mailToggleRich()" id="mailRichToggle">
                    <i class="fas fa-code"></i> Rich HTML
                </button>
            </label>
            <textarea name="body" id="mailBodyPlain" class="form-input" rows="16"
                      placeholder="Write your message…"><?= htmlspecialchars($_GET['body'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            <div id="mailBodyRich" style="display:none;border:1px solid rgba(255,255,255,.1);border-radius:8px;background:#fff;min-height:320px;padding:16px;outline:none;color:#222;line-height:1.6;font-family:Arial,sans-serif;font-size:14px;" contenteditable="false"></div>
        </div>

        <!-- Priority -->
        <div style="display:flex;gap:16px;align-items:center;margin-bottom:16px;">
            <div style="display:flex;align-items:center;gap:8px;">
                <label style="font-size:12px;color:#94a3b8;">Priority</label>
                <select name="priority" class="form-input" style="padding:5px 10px;font-size:12px;width:auto;">
                    <option value="3">High</option>
                    <option value="5" selected>Normal</option>
                    <option value="7">Low</option>
                </select>
            </div>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn btn-primary" id="mailSendBtn">
                <i class="fas fa-paper-plane"></i> Send
            </button>
            <a href="/mail" class="btn btn-secondary">Discard</a>
        </div>
    </form>
</div>

<style>
.mail-tag{display:inline-flex;align-items:center;gap:5px;background:rgba(102,126,234,.2);color:#a5b4fc;border:1px solid rgba(102,126,234,.3);border-radius:20px;padding:3px 10px;font-size:12px;}
.mail-tag button{background:none;border:none;color:#94a3b8;cursor:pointer;padding:0;line-height:1;font-size:14px;display:flex;align-items:center;}
.mail-tag button:hover{color:#fca5a5;}
</style>

<script>
/* ── Template data ── */
const mailTemplates = <?= json_encode($_mailTemplates) ?>;

function mailApplyTemplate(idx) {
    const tpl = mailTemplates[idx];
    if (!tpl) return;
    if (tpl.subject) document.getElementById('mailSubject').value = tpl.subject;
    document.getElementById('mailBodyPlain').value = tpl.body;
    if (mailRichMode) document.getElementById('mailBodyRich').innerHTML = tpl.body;
}

/* ── Rich-text toggle ── */
let mailRichMode = false;
function mailToggleRich() {
    mailRichMode = !mailRichMode;
    const plain = document.getElementById('mailBodyPlain');
    const rich  = document.getElementById('mailBodyRich');
    if (mailRichMode) {
        rich.innerHTML       = plain.value;
        rich.contentEditable = 'true';
        plain.style.display  = 'none';
        rich.style.display   = 'block';
        document.getElementById('mailRichToggle').innerHTML = '<i class="fas fa-align-left"></i> Plain text';
    } else {
        plain.value          = rich.innerHTML;
        rich.contentEditable = 'false';
        plain.style.display  = '';
        rich.style.display   = 'none';
        document.getElementById('mailRichToggle').innerHTML = '<i class="fas fa-code"></i> Rich HTML';
    }
}

/* ── Multi-recipient tag input ── */
const mailToTags  = [];
const mailTagsContainer = document.getElementById('mailToTags');
const mailToInput = document.getElementById('mailToInput');

function mailAddTag(email) {
    email = email.trim();
    if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) return false;
    if (mailToTags.includes(email)) return false;
    mailToTags.push(email);
    const tag = document.createElement('span');
    tag.className = 'mail-tag';
    tag.dataset.email = email;
    tag.innerHTML = htmlEnc(email)
        + '<button type="button" onclick="mailRemoveTag(this)" title="Remove"><i class="fas fa-times-circle"></i></button>';
    mailTagsContainer.insertBefore(tag, null);
    document.getElementById('mailTagWrap').insertBefore(tag, mailToInput);
    updateHidden();
    return true;
}

function mailRemoveTag(btn) {
    const tag   = btn.closest('.mail-tag');
    const email = tag.dataset.email;
    const idx   = mailToTags.indexOf(email);
    if (idx > -1) mailToTags.splice(idx, 1);
    tag.remove();
    updateHidden();
}

function updateHidden() {
    document.getElementById('mailToHidden').value = mailToTags.join(',');
}

function htmlEnc(s) {
    return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escJs(s) {
    return s.replace(/\\/g,'\\\\').replace(/'/g,"\\'");
}

// Pre-seed from URL param
(function() {
    const pre = mailToInput.value.trim();
    if (pre) { pre.split(',').forEach(e => mailAddTag(e.trim())); mailToInput.value = ''; }
})();

mailToInput.addEventListener('keydown', e => {
    if (['Enter',',' ,' ','Tab'].includes(e.key)) {
        const v = mailToInput.value.trim().replace(/,$/, '');
        if (v) { e.preventDefault(); mailAddTag(v); mailToInput.value = ''; hideSuggest(); }
    } else if (e.key === 'Backspace' && mailToInput.value === '' && mailToTags.length) {
        const last = document.querySelector('#mailTagWrap .mail-tag:last-of-type');
        if (last) { mailRemoveTag(last.querySelector('button')); }
    }
});
mailToInput.addEventListener('blur', () => {
    setTimeout(() => {
        if (mailToInput.value.trim()) { mailAddTag(mailToInput.value.trim()); mailToInput.value = ''; }
        hideSuggest();
    }, 200);
});

/* ── Recipient autocomplete ── */
let suggestTimer = null;
const suggestBox = document.getElementById('mailToSuggest');

mailToInput.addEventListener('input', () => {
    clearTimeout(suggestTimer);
    const q = mailToInput.value.trim();
    if (q.length < 2) { hideSuggest(); return; }
    suggestTimer = setTimeout(() => {
        fetch('/mail/suggest-recipients?q=' + encodeURIComponent(q))
            .then(r => r.json())
            .then(list => {
                const filtered = list.filter(e => !mailToTags.includes(e));
                if (!filtered.length) { hideSuggest(); return; }
                suggestBox.innerHTML = filtered.map(email =>
                    '<div class="suggest-item" data-email="' + htmlEnc(email) + '">'
                    + '<i class="fas fa-user" style="color:#667eea;margin-right:8px;font-size:11px;"></i>'
                    + htmlEnc(email) + '</div>'
                ).join('');
                suggestBox.querySelectorAll('.suggest-item').forEach(el => {
                    el.addEventListener('mousedown', () => {
                        mailAddTag(el.dataset.email);
                        mailToInput.value = '';
                        hideSuggest();
                    });
                });
                suggestBox.style.display = 'block';
            }).catch(() => hideSuggest());
    }, 250);
});

function hideSuggest() { suggestBox.style.display = 'none'; }

/* ── Form submit ── */
document.getElementById('mailComposeForm').addEventListener('submit', function(e) {
    const pending = mailToInput.value.trim();
    if (pending) { mailAddTag(pending); mailToInput.value = ''; }
    updateHidden();
    if (!mailToTags.length) {
        e.preventDefault();
        alert('Please add at least one recipient.');
        mailToInput.focus();
        return;
    }
    if (mailRichMode) {
        const plain = document.getElementById('mailBodyPlain');
        plain.value = document.getElementById('mailBodyRich').innerHTML;
    }
    const btn = document.getElementById('mailSendBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending…';
});
</script>

<style>
.suggest-item{padding:9px 14px;cursor:pointer;font-size:13px;color:#e2e8f0;border-bottom:1px solid rgba(255,255,255,.04);}
.suggest-item:hover{background:rgba(102,126,234,.15);}
.suggest-item:last-child{border-bottom:none;}
</style>

<?php View::endSection(); ?>
