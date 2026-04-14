<?php use Core\View; use Core\Security; use Core\MailService; use Core\Helpers; ?>
<?php $pageTitle = 'Sent Message'; ?>
<?php View::extend('mail'); ?>
<?php View::section('content'); ?>

<?php $providers = MailService::getAllProviders(); ?>

<?php if (Helpers::hasFlash('success')): ?>
<div class="mail-alert mail-alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars(Helpers::getFlash('success'), ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
<div class="mail-alert mail-alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars(Helpers::getFlash('error'), ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div style="margin-bottom:16px;display:flex;gap:8px;align-items:center;">
    <a href="/mail/sent" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Sent</a>
</div>

<!-- ── Main message card ─────────────────────────────────────────────── -->
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
                <?php if (($msg['status'] ?? '') === 'sent'): ?>
                <span style="color:#6ee7b7;"><i class="fas fa-check-circle"></i> Sent</span>
                <?php else: ?>
                <span style="color:#fca5a5;" title="<?= htmlspecialchars($msg['error_message'] ?? '', ENT_QUOTES, 'UTF-8') ?>"><i class="fas fa-exclamation-circle"></i> Failed</span>
                <?php endif; ?>
                &nbsp;·&nbsp;
                <?= !empty($msg['sent_at']) ? date('D, M j Y g:i a', strtotime($msg['sent_at'])) : '—' ?>
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
    <?php elseif (!empty($msg['body_text'])): ?>
    <div style="background:#1a1a26;border-radius:8px;padding:20px;color:#e2e8f0;font-size:13px;line-height:1.7;overflow-x:auto;max-height:600px;overflow-y:auto;white-space:pre-wrap;font-family:monospace;">
        <?= htmlspecialchars($msg['body_text'], ENT_QUOTES, 'UTF-8') ?>
    </div>
    <?php else: ?>
    <div style="color:#94a3b8;font-style:italic;font-size:13px;padding:20px 0;">
        <i class="fas fa-info-circle"></i> Message body is not stored. Future emails will be saved automatically.
    </div>
    <?php endif; ?>
</div>

<!-- ── Reply thread ──────────────────────────────────────────────────── -->
<?php if (!empty($thread)): ?>
<div style="margin-bottom:4px;font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.5px;padding-left:4px;">
    <i class="fas fa-code-branch"></i> Conversation — <?= count($thread) ?> related message<?= count($thread) === 1 ? '' : 's' ?>
</div>
<div style="border:1px solid rgba(255,255,255,.07);border-radius:8px;overflow:hidden;margin-bottom:16px;">
<?php foreach ($thread as $t): ?>
<div class="sv-thread-item" style="border-bottom:1px solid rgba(255,255,255,.05);">
    <div onclick="toggleSvThread(this)" style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;cursor:pointer;background:rgba(255,255,255,.02);user-select:none;">
        <div style="display:flex;align-items:center;gap:10px;min-width:0;">
            <span style="font-size:11px;color:<?= ($t['status'] ?? '') === 'sent' ? '#6ee7b7' : '#fca5a5' ?>;">
                <i class="fas <?= ($t['status'] ?? '') === 'sent' ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
            </span>
            <span style="font-size:13px;font-weight:500;color:#e2e8f0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:340px;">
                To: <?= htmlspecialchars($t['recipient'] ?? '', ENT_QUOTES, 'UTF-8') ?>
            </span>
            <span style="font-size:12px;color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;">
                <?= htmlspecialchars(mb_substr(strip_tags($t['body_html'] ?? $t['body_text'] ?? ''), 0, 80), ENT_QUOTES, 'UTF-8') ?>
            </span>
        </div>
        <div style="display:flex;align-items:center;gap:8px;flex-shrink:0;">
            <span style="font-size:12px;color:#64748b;white-space:nowrap;"><?= !empty($t['sent_at']) ? date('M j, g:i a', strtotime($t['sent_at'])) : '' ?></span>
            <i class="fas fa-chevron-down sv-chevron" style="font-size:11px;color:#64748b;transition:transform .2s;"></i>
        </div>
    </div>
    <div class="sv-thread-body" style="display:none;padding:0 16px 16px;">
        <?php if (!empty($t['body_html'])): ?>
        <div style="background:#fff;border-radius:6px;padding:14px;color:#222;font-size:13px;line-height:1.6;overflow-x:auto;max-height:200px;overflow-y:auto;"><?= $t['body_html'] ?></div>
        <?php elseif (!empty($t['body_text'])): ?>
        <div style="background:#0d0d14;border-radius:6px;padding:14px;color:#94a3b8;font-size:12px;line-height:1.6;overflow-x:auto;max-height:200px;overflow-y:auto;white-space:pre-wrap;font-family:monospace;"><?= htmlspecialchars(mb_substr($t['body_text'], 0, 500), ENT_QUOTES, 'UTF-8') ?><?= mb_strlen($t['body_text'] ?? '') > 500 ? '…' : '' ?></div>
        <?php endif; ?>
        <?php if (!empty($t['id'])): ?>
        <div style="margin-top:10px;"><a href="/mail/sent/view/<?= (int)$t['id'] ?>" class="btn btn-sm btn-secondary" style="font-size:11px;">Open in Sent</a></div>
        <?php endif; ?>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>

<!-- ── Reply form ────────────────────────────────────────────────────── -->
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
            <input type="text" name="subject" class="form-input" value="Re: <?= htmlspecialchars(ltrim(preg_replace('/^(Re:\s*)+/i', '', $msg['subject'] ?? ''), ' '), ENT_QUOTES, 'UTF-8') ?>">
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

<!-- ── Forward form ──────────────────────────────────────────────────── -->
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
            <?php
            $origBody = !empty($msg['body_html']) ? strip_tags($msg['body_html']) : ($msg['body_text'] ?? '');
            $fwdHeader = "\n\n---------- Forwarded Message ----------\n"
                . "To: " . ($msg['recipient'] ?? '') . "\n"
                . "Date: " . (!empty($msg['sent_at']) ? date('D, M j Y g:i a', strtotime($msg['sent_at'])) : '') . "\n"
                . "Subject: " . ($msg['subject'] ?? '') . "\n\n"
                . $origBody;
            ?>
            <textarea name="body" class="form-input" rows="10"><?= htmlspecialchars($fwdHeader, ENT_QUOTES, 'UTF-8') ?></textarea>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Forward</button>
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('svForwardForm').style.display='none'">Cancel</button>
        </div>
    </form>
</div>


<script>
function toggleSvThread(header) {
    const body = header.nextElementSibling;
    const icon = header.querySelector('.sv-chevron');
    const open = body.style.display !== 'none';
    body.style.display = open ? 'none' : 'block';
    if (icon) icon.style.transform = open ? '' : 'rotate(180deg)';
}
</script>

<?php View::endSection(); ?>
