<?php use Core\View; ?>
<?php $pageTitle = 'Sent'; ?>
<?php View::extend('mail'); ?>
<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <div>
        <h2 style="margin:0;font-size:18px;font-weight:600;">Sent Mail</h2>
        <p class="text-muted" style="font-size:12px;margin-top:2px;"><?= (int)($total ?? 0) ?> message<?= (int)($total ?? 0) === 1 ? '' : 's' ?></p>
    </div>
    <a href="/mail/compose" class="btn btn-primary btn-sm"><i class="fas fa-pen"></i> Compose</a>
</div>

<?php if (empty($messages)): ?>
<div class="mail-card" style="text-align:center;padding:60px 20px;">
    <i class="fas fa-paper-plane" style="font-size:48px;color:#334155;margin-bottom:16px;"></i>
    <p style="color:#64748b;font-size:15px;">No emails sent yet.</p>
</div>
<?php else: ?>
<div class="mail-card" style="padding:0;overflow:hidden;">
    <table class="mail-table">
        <thead>
            <tr>
                <th>To</th>
                <th>Subject</th>
                <th>Provider</th>
                <th style="width:100px;">Status</th>
                <th style="width:150px;">Sent At</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($messages as $msg): ?>
            <tr style="cursor:pointer;" onclick="window.location='/mail/sent/view/<?= (int)$msg['id'] ?>'">
                <td style="font-size:13px;"><?= htmlspecialchars($msg['to_email'], ENT_QUOTES, 'UTF-8') ?></td>
                <td style="font-size:13px;color:#e2e8f0;"><?= htmlspecialchars(mb_substr($msg['subject'] ?? '(no subject)', 0, 80), ENT_QUOTES, 'UTF-8') ?></td>
                <td style="font-size:12px;color:#64748b;"><?= $msg['provider_config_id'] ? '#' . (int)$msg['provider_config_id'] : '—' ?></td>
                <td>
                    <?php if ($msg['status'] === 'sent'): ?>
                    <span style="color:#6ee7b7;font-size:12px;"><i class="fas fa-check-circle"></i> Sent</span>
                    <?php else: ?>
                    <span style="color:#fca5a5;font-size:12px;" title="<?= htmlspecialchars($msg['error_message'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                        <i class="fas fa-exclamation-circle"></i> Failed
                    </span>
                    <?php endif; ?>
                </td>
                <td class="text-muted" style="font-size:12px;white-space:nowrap;">
                    <?= $msg['sent_at'] ? date('M j, Y g:i a', strtotime($msg['sent_at'])) : '—' ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php
$_totalPages = (int)ceil((int)($total ?? 0) / max(1, (int)($perPage ?? 30)));
if ($_totalPages > 1):
?>
<div style="display:flex;justify-content:center;gap:6px;margin-top:16px;flex-wrap:wrap;">
    <?php for ($i = 1; $i <= $_totalPages; $i++): ?>
    <a href="/mail/sent?page=<?= $i ?>"
       class="btn btn-sm <?= $i === (int)($page ?? 1) ? 'btn-primary' : 'btn-secondary' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<?php View::endSection(); ?>
