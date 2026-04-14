<?php use Core\View; use Core\Helpers; ?>
<?php $pageTitle = ucfirst($folder ?? 'Inbox'); ?>
<?php View::extend('mail'); ?>
<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <div>
        <h2 style="margin:0;font-size:18px;font-weight:600;text-transform:capitalize;"><?= htmlspecialchars($folder === 'inbox' ? 'Inbox' : ucfirst($folder ?? 'Inbox'), ENT_QUOTES, 'UTF-8') ?></h2>
        <p class="text-muted" style="font-size:12px;margin-top:2px;"><?= (int)($total ?? 0) ?> message<?= (int)($total ?? 0) === 1 ? '' : 's' ?></p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;">
        <?php if (!empty($searchQuery ?? null)): ?>
        <span style="font-size:13px;color:#64748b;padding:6px 0;">
            Search: <strong><?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?></strong>
            <a href="/mail" style="margin-left:8px;color:#667eea;"><i class="fas fa-times"></i></a>
        </span>
        <?php endif; ?>
        <a href="/mail/compose" class="btn btn-primary btn-sm"><i class="fas fa-pen"></i> Compose</a>
    </div>
</div>

<?php if (empty($messages)): ?>
<div class="mail-card" style="text-align:center;padding:60px 20px;">
    <i class="fas fa-inbox" style="font-size:48px;color:#334155;margin-bottom:16px;"></i>
    <p style="color:#64748b;font-size:15px;">
        <?= isset($searchQuery) ? 'No results found for "' . htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') . '"' : 'Your ' . htmlspecialchars($folder ?? 'inbox', ENT_QUOTES, 'UTF-8') . ' is empty.' ?>
    </p>
    <?php if (empty($searchQuery)): ?>
    <p style="color:#475569;font-size:13px;margin-top:8px;">Click <i class="fas fa-sync-alt"></i> in the top-bar to sync new messages from your provider.</p>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="mail-card" style="padding:0;overflow:hidden;">
    <table class="mail-table">
        <thead>
            <tr>
                <th style="width:20px;"></th>
                <th>From</th>
                <th>Subject</th>
                <th style="width:140px;">Date</th>
                <th style="width:110px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($messages as $msg): ?>
            <tr class="<?= $msg['is_read'] ? '' : 'unread' ?>" id="mail-row-<?= (int)$msg['id'] ?>">
                <td>
                    <button class="star-btn <?= $msg['is_starred'] ? 'starred' : '' ?>"
                            onclick="mailToggleStar(<?= (int)$msg['id'] ?>, this)"
                            title="<?= $msg['is_starred'] ? 'Unstar' : 'Star' ?>">
                        <i class="fas fa-star"></i>
                    </button>
                </td>
                <td>
                    <a href="/mail/view/<?= (int)$msg['id'] ?>" style="display:block;">
                        <span style="font-weight:<?= $msg['is_read'] ? '400' : '600' ?>;color:<?= $msg['is_read'] ? '#94a3b8' : '#e2e8f0' ?>;">
                            <?= htmlspecialchars($msg['from_name'] ?: $msg['from_email'] ?: '(unknown)', ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <span style="font-size:11px;color:#475569;"><?= htmlspecialchars($msg['from_email'] ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    </a>
                </td>
                <td>
                    <a href="/mail/view/<?= (int)$msg['id'] ?>"
                       style="color:<?= $msg['is_read'] ? '#94a3b8' : '#e2e8f0' ?>;font-weight:<?= $msg['is_read'] ? '400' : '500' ?>;">
                        <?= htmlspecialchars(mb_substr($msg['subject'] ?? '(no subject)', 0, 80), ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </td>
                <td class="text-muted" style="font-size:12px;white-space:nowrap;">
                    <?= $msg['date_sent'] ? date('M j, g:i a', strtotime($msg['date_sent'])) : '—' ?>
                </td>
                <td>
                    <div style="display:flex;gap:4px;">
                        <button class="btn btn-sm btn-secondary"
                                onclick="mailToggleRead(<?= (int)$msg['id'] ?>, <?= $msg['is_read'] ? 0 : 1 ?>, this)"
                                title="<?= $msg['is_read'] ? 'Mark unread' : 'Mark read' ?>">
                            <i class="fas fa-<?= $msg['is_read'] ? 'envelope' : 'envelope-open' ?>"></i>
                        </button>
                        <button class="btn btn-sm btn-secondary" onclick="mailArchiveMsg(<?= (int)$msg['id'] ?>)" title="Archive">
                            <i class="fas fa-archive"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="mailDeleteMsg(<?= (int)$msg['id'] ?>)" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Pagination -->
<?php
$_totalPages = (int)ceil((int)($total ?? 0) / max(1, (int)($perPage ?? 30)));
if ($_totalPages > 1):
?>
<div style="display:flex;justify-content:center;gap:6px;margin-top:16px;flex-wrap:wrap;">
    <?php for ($i = 1; $i <= $_totalPages; $i++): ?>
    <a href="/mail?page=<?= $i ?><?= isset($folder) ? '&folder=' . urlencode($folder) : '' ?>"
       class="btn btn-sm <?= $i === (int)($page ?? 1) ? 'btn-primary' : 'btn-secondary' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<script>
function mailToggleStar(id, btn) {
    const isStarred = btn.classList.contains('starred');
    mailPostAction('/mail/star', {id, state: isStarred ? 0 : 1}, d => {
        if (d.success) btn.classList.toggle('starred');
    });
}

function mailToggleRead(id, newState, btn) {
    mailPostAction('/mail/mark-read', {id, state: newState}, d => {
        if (d.success) {
            const row = document.getElementById('mail-row-' + id);
            if (newState === 0) {
                row.classList.add('unread');
                btn.title = 'Mark read';
                btn.querySelector('i').className = 'fas fa-envelope-open';
            } else {
                row.classList.remove('unread');
                btn.title = 'Mark unread';
                btn.querySelector('i').className = 'fas fa-envelope';
            }
        }
    });
}

function mailArchiveMsg(id) {
    mailPostAction('/mail/archive', {id, state: 1}, d => {
        if (d.success) document.getElementById('mail-row-' + id).remove();
    });
}

function mailDeleteMsg(id) {
    if (!confirm('Move this message to trash?')) return;
    mailPostAction('/mail/delete', {id}, d => {
        if (d.success) document.getElementById('mail-row-' + id).remove();
    });
}
</script>

<?php View::endSection(); ?>
