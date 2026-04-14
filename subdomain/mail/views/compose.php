<?php $pageTitle = 'Compose'; ?>
<div style="margin-bottom:16px;display:flex;justify-content:space-between;align-items:center;">
    <div>
        <h2 style="margin:0;font-size:18px;font-weight:600;">Compose New Email</h2>
    </div>
    <a href="/" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Discard</a>
</div>

<div class="card">
    <form method="POST" action="/compose" id="composeForm">
        <?= \Core\Security::csrfField() ?>

        <div class="form-group">
            <label class="form-label">To <span style="color:#e74c3c;">*</span></label>
            <input type="email" name="to" class="form-input" required placeholder="recipient@example.com"
                   value="<?= htmlspecialchars($_GET['to'] ?? '', ENT_QUOTES) ?>">
        </div>

        <div class="grid-row" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
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
                   value="<?= htmlspecialchars($_GET['subject'] ?? '', ENT_QUOTES) ?>">
        </div>

        <div class="form-group">
            <label class="form-label" style="display:flex;justify-content:space-between;">
                Message
                <span style="font-weight:400;color:#64748b;">
                    <button type="button" class="btn btn-sm btn-secondary" onclick="toggleRich()" id="richToggle">
                        <i class="fas fa-code"></i> Rich HTML
                    </button>
                </span>
            </label>
            <textarea name="body" id="bodyPlain" class="form-input" rows="14" placeholder="Write your message…"><?= htmlspecialchars($_GET['body'] ?? '', ENT_QUOTES) ?></textarea>
            <!-- Rich editor placeholder: swap to a TinyMCE / Quill if desired -->
            <div id="bodyRich" style="display:none;border:1px solid rgba(255,255,255,.1);border-radius:8px;background:#fff;min-height:280px;"></div>
        </div>

        <div style="display:flex;gap:10px;">
            <button type="submit" class="btn btn-primary" id="sendBtn">
                <i class="fas fa-paper-plane"></i> Send
            </button>
            <a href="/" class="btn btn-secondary">Discard</a>
        </div>
    </form>
</div>

<script>
let richMode = false;
function toggleRich() {
    richMode = !richMode;
    document.getElementById('bodyPlain').style.display = richMode ? 'none' : '';
    document.getElementById('bodyRich').style.display  = richMode ? 'block' : 'none';
    document.getElementById('richToggle').innerHTML = richMode
        ? '<i class="fas fa-align-left"></i> Plain text'
        : '<i class="fas fa-code"></i> Rich HTML';
}

document.getElementById('composeForm').addEventListener('submit', function() {
    document.getElementById('sendBtn').disabled = true;
    document.getElementById('sendBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending…';
});
</script>
