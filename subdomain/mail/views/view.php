<?php $pageTitle = 'View Message'; ?>
<div style="margin-bottom:16px;">
    <a href="/" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Inbox</a>
</div>

<div class="card">
    <!-- Message header -->
    <div style="border-bottom:1px solid rgba(255,255,255,.07);padding-bottom:16px;margin-bottom:16px;">
        <h2 style="font-size:18px;font-weight:600;margin-bottom:12px;line-height:1.4;">
            <?= htmlspecialchars($msg['subject'] ?? '(no subject)', ENT_QUOTES) ?>
        </h2>
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;">
            <div>
                <div style="font-size:14px;color:#e2e8f0;font-weight:500;">
                    <?= htmlspecialchars($msg['from_name'] ?: $msg['from_email'] ?? '', ENT_QUOTES) ?>
                    <?php if ($msg['from_name']): ?>
                    <span style="color:#64748b;font-weight:400;">&lt;<?= htmlspecialchars($msg['from_email'] ?? '', ENT_QUOTES) ?>&gt;</span>
                    <?php endif; ?>
                </div>
                <div style="font-size:12px;color:#64748b;margin-top:2px;">
                    To: <?= htmlspecialchars($msg['to_email'] ?? '', ENT_QUOTES) ?>
                    <?php if ($msg['cc_email']): ?>
                     &nbsp;· CC: <?= htmlspecialchars($msg['cc_email'], ENT_QUOTES) ?>
                    <?php endif; ?>
                </div>
            </div>
            <div style="font-size:12px;color:#64748b;white-space:nowrap;">
                <?= $msg['date_sent'] ? date('D, M j Y g:i a', strtotime($msg['date_sent'])) : '—' ?>
            </div>
        </div>
    </div>

    <!-- Action bar -->
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
        <button class="btn btn-secondary btn-sm" onclick="showReplyForm()">
            <i class="fas fa-reply"></i> Reply
        </button>
        <button class="btn btn-secondary btn-sm" onclick="showForwardForm()">
            <i class="fas fa-share"></i> Forward
        </button>
        <button class="btn btn-secondary btn-sm" onclick="archiveMsg(<?= $msg['id'] ?>)">
            <i class="fas fa-archive"></i> Archive
        </button>
        <button class="btn btn-danger btn-sm" onclick="deleteMsg(<?= $msg['id'] ?>)">
            <i class="fas fa-trash"></i> Delete
        </button>
    </div>

    <!-- Message body -->
    <div class="email-body-wrap">
        <?php if (!empty($msg['body_html'])): ?>
        <iframe id="emailFrame" sandbox="allow-same-origin" style="width:100%;min-height:400px;border:none;"></iframe>
        <script>
        (function(){
            const frame = document.getElementById('emailFrame');
            const doc = frame.contentDocument || frame.contentWindow.document;
            doc.open();
            doc.write(<?= json_encode($msg['body_html']) ?>);
            doc.close();
            // Auto-resize
            setTimeout(() => {
                frame.style.height = (doc.documentElement.scrollHeight + 20) + 'px';
            }, 200);
        })();
        </script>
        <?php else: ?>
        <div style="padding:20px;white-space:pre-wrap;font-family:monospace;font-size:13px;color:#e2e8f0;background:#0d0d14;">
            <?= htmlspecialchars($msg['body_text'] ?? '(empty body)', ENT_QUOTES) ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Reply form (hidden) -->
<div id="replyForm" style="display:none;" class="card">
    <h3 style="margin-bottom:16px;font-size:15px;"><i class="fas fa-reply" style="color:#667eea;"></i> Reply</h3>
    <form method="POST" action="/reply">
        <?= \Core\Security::csrfField() ?>
        <input type="hidden" name="orig_id" value="<?= (int)$msg['id'] ?>">
        <div class="form-group">
            <label class="form-label">To</label>
            <input type="email" name="to" class="form-input" value="<?= htmlspecialchars($msg['from_email'] ?? '', ENT_QUOTES) ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Subject</label>
            <input type="text" name="subject" class="form-input" value="Re: <?= htmlspecialchars($msg['subject'] ?? '', ENT_QUOTES) ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Message</label>
            <textarea name="body" class="form-input" rows="8" placeholder="Write your reply…"></textarea>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Reply</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('replyForm').style.display='none'">Cancel</button>
        </div>
    </form>
</div>

<!-- Forward form (hidden) -->
<div id="forwardForm" style="display:none;" class="card">
    <h3 style="margin-bottom:16px;font-size:15px;"><i class="fas fa-share" style="color:#667eea;"></i> Forward</h3>
    <form method="POST" action="/forward">
        <?= \Core\Security::csrfField() ?>
        <input type="hidden" name="orig_id" value="<?= (int)$msg['id'] ?>">
        <div class="form-group">
            <label class="form-label">To</label>
            <input type="email" name="to" class="form-input" placeholder="recipient@example.com">
        </div>
        <div class="form-group">
            <label class="form-label">Subject</label>
            <input type="text" name="subject" class="form-input" value="Fwd: <?= htmlspecialchars($msg['subject'] ?? '', ENT_QUOTES) ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Message</label>
            <textarea name="body" class="form-input" rows="8">---------- Forwarded Message ----------
From: <?= htmlspecialchars($msg['from_email'] ?? '', ENT_QUOTES) ?>
Date: <?= $msg['date_sent'] ? date('D, M j Y g:i a', strtotime($msg['date_sent'])) : '—' ?>
Subject: <?= htmlspecialchars($msg['subject'] ?? '', ENT_QUOTES) ?>

<?= htmlspecialchars($msg['body_text'] ?? '', ENT_QUOTES) ?></textarea>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Forward</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('forwardForm').style.display='none'">Cancel</button>
        </div>
    </form>
</div>

<script>
function showReplyForm() {
    document.getElementById('replyForm').style.display = 'block';
    document.getElementById('forwardForm').style.display = 'none';
    document.getElementById('replyForm').scrollIntoView({behavior:'smooth'});
}
function showForwardForm() {
    document.getElementById('forwardForm').style.display = 'block';
    document.getElementById('replyForm').style.display = 'none';
    document.getElementById('forwardForm').scrollIntoView({behavior:'smooth'});
}
function archiveMsg(id) {
    postAction('/archive', {id: id, state: 1}, d => {
        if (d.success) window.location.href = '/';
    });
}
function deleteMsg(id) {
    if (!confirm('Delete this message?')) return;
    postAction('/delete', {id: id}, d => {
        if (d.success) window.location.href = '/';
    });
}
</script>
