<?php use Core\View; use Core\Security; ?>
<?php $pageTitle = 'View Message'; ?>
<?php View::extend('mail'); ?>
<?php View::section('content'); ?>

<div style="margin-bottom:16px;">
    <a href="/mail" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Inbox</a>
</div>

<div class="mail-card">
    <!-- Message header -->
    <div style="border-bottom:1px solid rgba(255,255,255,.07);padding-bottom:16px;margin-bottom:16px;">
        <h2 style="font-size:18px;font-weight:600;margin-bottom:12px;line-height:1.4;">
            <?= htmlspecialchars($msg['subject'] ?? '(no subject)', ENT_QUOTES, 'UTF-8') ?>
        </h2>
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;">
            <div>
                <div style="font-size:14px;color:#e2e8f0;font-weight:500;">
                    <?= htmlspecialchars($msg['from_name'] ?: ($msg['from_email'] ?? ''), ENT_QUOTES, 'UTF-8') ?>
                    <?php if ($msg['from_name']): ?>
                    <span style="color:#64748b;font-weight:400;">&lt;<?= htmlspecialchars($msg['from_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>&gt;</span>
                    <?php endif; ?>
                </div>
                <div style="font-size:12px;color:#64748b;margin-top:2px;">
                    To: <?= htmlspecialchars($msg['to_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    <?php if ($msg['cc_email']): ?>
                    &nbsp;· CC: <?= htmlspecialchars($msg['cc_email'], ENT_QUOTES, 'UTF-8') ?>
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
        <button class="btn btn-secondary btn-sm" onclick="document.getElementById('mailReplyForm').style.display='block';document.getElementById('mailForwardForm').style.display='none';document.getElementById('mailReplyForm').scrollIntoView({behavior:'smooth'})">
            <i class="fas fa-reply"></i> Reply
        </button>
        <button class="btn btn-secondary btn-sm" onclick="document.getElementById('mailForwardForm').style.display='block';document.getElementById('mailReplyForm').style.display='none';document.getElementById('mailForwardForm').scrollIntoView({behavior:'smooth'})">
            <i class="fas fa-share"></i> Forward
        </button>
        <button class="btn btn-secondary btn-sm" onclick="mailArchiveView(<?= (int)$msg['id'] ?>)">
            <i class="fas fa-archive"></i> Archive
        </button>
        <button class="btn btn-danger btn-sm" onclick="mailDeleteView(<?= (int)$msg['id'] ?>)">
            <i class="fas fa-trash"></i> Delete
        </button>
    </div>

    <!-- Message body -->
    <div class="email-body-wrap">
        <?php if (!empty($msg['body_html'])): ?>
        <iframe id="mailEmailFrame" sandbox="allow-same-origin" style="width:100%;min-height:400px;border:none;"></iframe>
        <script>
        (function(){
            const frame = document.getElementById('mailEmailFrame');
            const doc = frame.contentDocument || frame.contentWindow.document;
            doc.open();
            doc.write(<?= json_encode($msg['body_html']) ?>);
            doc.close();
            setTimeout(() => { frame.style.height = (doc.documentElement.scrollHeight + 20) + 'px'; }, 200);
        })();
        </script>
        <?php else: ?>
        <div style="padding:20px;white-space:pre-wrap;font-family:monospace;font-size:13px;color:#e2e8f0;background:#0d0d14;">
            <?= htmlspecialchars($msg['body_text'] ?? '(empty body)', ENT_QUOTES, 'UTF-8') ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Reply form (hidden by default) -->
<div id="mailReplyForm" style="display:none;" class="mail-card">
    <h3 style="margin-bottom:16px;font-size:15px;"><i class="fas fa-reply" style="color:#667eea;"></i> Reply</h3>
    <form method="POST" action="/mail/reply">
        <?= Security::csrfField() ?>
        <input type="hidden" name="orig_id" value="<?= (int)$msg['id'] ?>">
        <div class="form-group">
            <label class="form-label">To</label>
            <input type="email" name="to" class="form-input" value="<?= htmlspecialchars($msg['from_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Subject</label>
            <input type="text" name="subject" class="form-input" value="Re: <?= htmlspecialchars($msg['subject'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Message</label>
            <textarea name="body" class="form-input" rows="8" placeholder="Write your reply…"></textarea>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Reply</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('mailReplyForm').style.display='none'">Cancel</button>
        </div>
    </form>
</div>

<!-- Forward form (hidden by default) -->
<div id="mailForwardForm" style="display:none;" class="mail-card">
    <h3 style="margin-bottom:16px;font-size:15px;"><i class="fas fa-share" style="color:#667eea;"></i> Forward</h3>
    <form method="POST" action="/mail/forward">
        <?= Security::csrfField() ?>
        <input type="hidden" name="orig_id" value="<?= (int)$msg['id'] ?>">
        <div class="form-group">
            <label class="form-label">To</label>
            <input type="email" name="to" class="form-input" placeholder="recipient@example.com">
        </div>
        <div class="form-group">
            <label class="form-label">Subject</label>
            <input type="text" name="subject" class="form-input" value="Fwd: <?= htmlspecialchars($msg['subject'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Message</label>
            <textarea name="body" class="form-input" rows="8">---------- Forwarded Message ----------
From: <?= htmlspecialchars($msg['from_email'] ?? '', ENT_QUOTES, 'UTF-8') ?>

Date: <?= $msg['date_sent'] ? date('D, M j Y g:i a', strtotime($msg['date_sent'])) : '—' ?>

Subject: <?= htmlspecialchars($msg['subject'] ?? '', ENT_QUOTES, 'UTF-8') ?>


<?= htmlspecialchars($msg['body_text'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Forward</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('mailForwardForm').style.display='none'">Cancel</button>
        </div>
    </form>
</div>

<script>
function mailArchiveView(id) {
    mailPostAction('/mail/archive', {id, state: 1}, d => {
        if (d.success) window.location.href = '/mail';
    });
}
function mailDeleteView(id) {
    if (!confirm('Delete this message?')) return;
    mailPostAction('/mail/delete', {id}, d => {
        if (d.success) window.location.href = '/mail';
    });
}
</script>

<?php View::endSection(); ?>
