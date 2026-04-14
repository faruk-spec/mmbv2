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

        <div class="form-group">
            <label class="form-label">To <span style="color:#e74c3c;">*</span></label>
            <input type="email" name="to" class="form-input" required placeholder="recipient@example.com"
                   value="<?= htmlspecialchars($_GET['to'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div class="form-group">
                <label class="form-label">CC</label>
                <input type="text" name="cc" class="form-input" placeholder="cc@example.com">
            </div>
            <div class="form-group">
                <label class="form-label">BCC</label>
                <input type="text" name="bcc" class="form-input" placeholder="bcc@example.com">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Subject <span style="color:#e74c3c;">*</span></label>
            <input type="text" name="subject" class="form-input" required placeholder="Email subject"
                   value="<?= htmlspecialchars($_GET['subject'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>

        <div class="form-group">
            <label class="form-label" style="display:flex;justify-content:space-between;align-items:center;">
                Message
                <button type="button" class="btn btn-sm btn-secondary" onclick="mailToggleRich()" id="mailRichToggle">
                    <i class="fas fa-code"></i> Rich HTML
                </button>
            </label>
            <textarea name="body" id="mailBodyPlain" class="form-input" rows="14" placeholder="Write your message…"><?= htmlspecialchars($_GET['body'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
            <div id="mailBodyRich" style="display:none;border:1px solid rgba(255,255,255,.1);border-radius:8px;background:#fff;min-height:280px;"></div>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn btn-primary" id="mailSendBtn">
                <i class="fas fa-paper-plane"></i> Send
            </button>
            <a href="/mail" class="btn btn-secondary">Discard</a>
        </div>
    </form>
</div>

<script>
let mailRichMode = false;
function mailToggleRich() {
    mailRichMode = !mailRichMode;
    document.getElementById('mailBodyPlain').style.display = mailRichMode ? 'none' : '';
    document.getElementById('mailBodyRich').style.display  = mailRichMode ? 'block' : 'none';
    document.getElementById('mailRichToggle').innerHTML    = mailRichMode
        ? '<i class="fas fa-align-left"></i> Plain text'
        : '<i class="fas fa-code"></i> Rich HTML';
}

document.getElementById('mailComposeForm').addEventListener('submit', function() {
    const btn = document.getElementById('mailSendBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending…';
});
</script>

<?php View::endSection(); ?>
