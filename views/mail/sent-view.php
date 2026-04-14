<?php use Core\View; use Core\Security; use Core\MailService; ?>
<?php $pageTitle = 'Sent Message'; ?>
<?php View::extend('mail'); ?>
<?php View::section('content'); ?>

<?php $providers = MailService::getAllProviders(); ?>

<div style="margin-bottom:16px;display:flex;gap:8px;align-items:center;">
    <a href="/mail/sent" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Sent</a>
    <a href="/mail/compose?to=<?= urlencode($msg['recipient']) ?>&subject=<?= urlencode('Re: ' . ($msg['subject'] ?? '')) ?>" class="btn btn-secondary btn-sm">
        <i class="fas fa-reply"></i> Reply (compose)
    </a>
</div>

<div class="mail-card">
    <!-- Header -->
    <div style="border-bottom:1px solid rgba(255,255,255,.07);padding-bottom:16px;margin-bottom:16px;">
        <h2 style="font-size:18px;font-weight:600;margin-bottom:12px;line-height:1.4;">
            <?= htmlspecialchars($msg['subject'] ?? '(no subject)', ENT_QUOTES, 'UTF-8') ?>
        </h2>
        <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:8px;">
            <div>
                <div style="font-size:14px;color:#e2e8f0;font-weight:500;">
                    To: <?= htmlspecialchars($msg['recipient'], ENT_QUOTES, 'UTF-8') ?>
                </div>
                <?php if (!empty($msg['cc_email'])): ?>
                <div style="font-size:12px;color:#64748b;margin-top:2px;">CC: <?= htmlspecialchars($msg['cc_email'], ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
            </div>
            <div style="font-size:12px;color:#64748b;white-space:nowrap;">
                <?php if ($msg['status'] === 'sent'): ?>
                <span style="color:#6ee7b7;"><i class="fas fa-check-circle"></i> Sent</span>
                <?php else: ?>
                <span style="color:#fca5a5;" title="<?= htmlspecialchars($msg['error_message'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><i class="fas fa-exclamation-circle"></i> Failed</span>
                <?php endif; ?>
                &nbsp;·&nbsp;
                <?= $msg['sent_at'] ? date('D, M j Y g:i a', strtotime($msg['sent_at'])) : '—' ?>
            </div>
        </div>
    </div>

    <!-- Action bar -->
    <div style="display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;">
        <button class="btn btn-secondary btn-sm"
            onclick="document.getElementById('svReplyForm').style.display='block';document.getElementById('svForwardForm').style.display='none';document.getElementById('svReplyForm').scrollIntoView({behavior:'smooth'})">
            <i class="fas fa-reply"></i> Reply
        </button>
        <button class="btn btn-secondary btn-sm"
            onclick="document.getElementById('svForwardForm').style.display='block';document.getElementById('svReplyForm').style.display='none';document.getElementById('svForwardForm').scrollIntoView({behavior:'smooth'})">
            <i class="fas fa-share"></i> Forward
        </button>
    </div>

    <!-- Body -->
    <?php if (!empty($msg['body_html'])): ?>
    <div style="background:#fff;border-radius:8px;padding:20px;color:#222;font-size:14px;line-height:1.7;overflow-x:auto;max-height:600px;overflow-y:auto;">
        <?= $msg['body_html'] ?>
    </div>
    <?php else: ?>
    <div style="color:#94a3b8;font-style:italic;font-size:13px;padding:20px 0;">
        <i class="fas fa-info-circle"></i> Message body is not available (sent before body storage was enabled).
    </div>
    <?php endif; ?>
</div>

<!-- Reply form -->
<div id="svReplyForm" style="display:none;" class="mail-card">
    <h3 style="font-size:15px;font-weight:600;margin-bottom:16px;"><i class="fas fa-reply" style="color:#667eea;margin-right:8px;"></i> Reply</h3>
    <form method="POST" action="/mail/sent/reply">
        <?= Security::csrfField() ?>
        <input type="hidden" name="orig_id" value="<?= (int)$msg['id'] ?>">
        <input type="hidden" name="to" value="<?= htmlspecialchars($msg['recipient'], ENT_QUOTES, 'UTF-8') ?>">
        <?php if (!empty($providers)): ?>
        <div class="form-group">
            <label class="form-label">From</label>
            <select name="provider_id" class="form-input" style="cursor:pointer;">
                <?php foreach ($providers as $p): ?>
                <option value="<?= (int)$p['id'] ?>" <?= $p['is_active'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars(($p['from_name'] ? $p['from_name'] . ' <' . $p['from_email'] . '>' : $p['from_email']) . ' [' . strtoupper($p['provider_type']) . ']' . ($p['is_active'] ? ' ✓' : ''), ENT_QUOTES, 'UTF-8') ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="form-group">
            <label class="form-label">To</label>
            <input type="text" class="form-input" value="<?= htmlspecialchars($msg['recipient'], ENT_QUOTES, 'UTF-8') ?>" readonly style="color:#94a3b8;">
        </div>
        <div class="form-group">
            <label class="form-label">Subject</label>
            <input type="text" name="subject" class="form-input" value="Re: <?= htmlspecialchars($msg['subject'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Message</label>
            <textarea name="body" class="form-input" rows="10" placeholder="Write your reply…"></textarea>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Send Reply</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('svReplyForm').style.display='none'">Cancel</button>
        </div>
    </form>
</div>

<!-- Forward form -->
<div id="svForwardForm" style="display:none;" class="mail-card">
    <h3 style="font-size:15px;font-weight:600;margin-bottom:16px;"><i class="fas fa-share" style="color:#667eea;margin-right:8px;"></i> Forward</h3>
    <form method="POST" action="/mail/sent/reply">
        <?= Security::csrfField() ?>
        <input type="hidden" name="orig_id" value="<?= (int)$msg['id'] ?>">
        <?php if (!empty($providers)): ?>
        <div class="form-group">
            <label class="form-label">From</label>
            <select name="provider_id" class="form-input" style="cursor:pointer;">
                <?php foreach ($providers as $p): ?>
                <option value="<?= (int)$p['id'] ?>" <?= $p['is_active'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars(($p['from_name'] ? $p['from_name'] . ' <' . $p['from_email'] . '>' : $p['from_email']) . ' [' . strtoupper($p['provider_type']) . ']' . ($p['is_active'] ? ' ✓' : ''), ENT_QUOTES, 'UTF-8') ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        <div class="form-group">
            <label class="form-label">To</label>
            <input type="text" name="to" class="form-input" placeholder="forward-to@example.com">
        </div>
        <div class="form-group">
            <label class="form-label">Subject</label>
            <input type="text" name="subject" class="form-input" value="Fwd: <?= htmlspecialchars($msg['subject'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="form-group">
            <label class="form-label">Message</label>
            <textarea name="body" class="form-input" rows="10"><?php
$origBody = strip_tags($msg['body_html'] ?? '');
$fwdHeader = "\n\n---------- Forwarded Message ----------\n"
    . "To: " . ($msg['recipient'] ?? '') . "\n"
    . "Date: " . ($msg['sent_at'] ? date('D, M j Y g:i a', strtotime($msg['sent_at'])) : '') . "\n"
    . "Subject: " . ($msg['subject'] ?? '') . "\n\n"
    . $origBody;
echo htmlspecialchars($fwdHeader, ENT_QUOTES, 'UTF-8');
?></textarea>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Forward</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('svForwardForm').style.display='none'">Cancel</button>
        </div>
    </form>
</div>

<?php View::endSection(); ?>
