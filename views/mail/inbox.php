<?php use Core\View; use Core\Helpers; ?>
<?php $pageTitle = ucfirst($folder ?? 'Inbox'); ?>
<?php View::extend('mail'); ?>
<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
    <div>
        <h2 style="margin:0;font-size:18px;font-weight:600;text-transform:capitalize;">
            <?php
            $folderLabels = ['inbox'=>'Inbox','starred'=>'Starred','archived'=>'Archived','trash'=>'Trash'];
            echo htmlspecialchars($folderLabels[$folder ?? 'inbox'] ?? ucfirst($folder ?? 'Inbox'), ENT_QUOTES, 'UTF-8');
            ?>
        </h2>
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
    <?php
    $folderIcons = ['inbox'=>'fa-inbox','starred'=>'fa-star','archived'=>'fa-archive','trash'=>'fa-trash'];
    $fi = $folderIcons[$folder ?? 'inbox'] ?? 'fa-inbox';
    ?>
    <i class="fas <?= $fi ?>" style="font-size:48px;color:var(--text-tertiary);margin-bottom:16px;"></i>
    <p style="color:#64748b;font-size:15px;">
        <?= isset($searchQuery) ? 'No results found for "' . htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') . '"'
                                 : 'Your ' . htmlspecialchars($folderLabels[$folder ?? 'inbox'] ?? ucfirst($folder ?? 'inbox'), ENT_QUOTES, 'UTF-8') . ' is empty.' ?>
    </p>
    <?php if (empty($searchQuery) && ($folder ?? 'inbox') === 'inbox'): ?>
    <p style="color:#475569;font-size:13px;margin-top:8px;">
        Click <i class="fas fa-sync-alt"></i> in the top-bar to sync new messages from your provider.
    </p>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="mail-card" style="padding:0;overflow:hidden;">
    <table class="mail-table" id="mailInboxTable">
        <thead>
            <tr>
                <th style="width:22px;"></th>
                <th style="width:220px;">From</th>
                <th>Subject &amp; Preview</th>
                <th style="width:120px;">Date</th>
                <th style="width:120px;text-align:right;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($messages as $idx => $msg): ?>
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
                    <a href="/mail/view/<?= (int)$msg['id'] ?>" style="display:block;">
                        <span style="color:<?= $msg['is_read'] ? '#94a3b8' : '#e2e8f0' ?>;font-weight:<?= $msg['is_read'] ? '400' : '500' ?>;">
                            <?= htmlspecialchars(mb_substr($msg['subject'] ?? '(no subject)', 0, 70), ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <?php
                        $preview = mb_substr(strip_tags($msg['body_text'] ?? $msg['body_html'] ?? ''), 0, 90);
                        if ($preview):
                        ?>
                        <span style="font-size:12px;color:#475569;margin-left:6px;">
                            – <?= htmlspecialchars($preview, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                        <?php endif; ?>
                    </a>
                </td>
                <td class="text-muted" style="font-size:12px;white-space:nowrap;">
                    <?= $msg['date_sent'] ? date('M j, g:i a', strtotime($msg['date_sent'])) : '—' ?>
                </td>
                <td>
                    <div class="mail-row-actions" style="justify-content:flex-end;">
                        <button class="btn-icon"
                                onclick="mailToggleRead(<?= (int)$msg['id'] ?>, <?= $msg['is_read'] ? 0 : 1 ?>, this)"
                                title="<?= $msg['is_read'] ? 'Mark unread' : 'Mark read' ?>">
                            <i class="fas fa-<?= $msg['is_read'] ? 'envelope' : 'envelope-open' ?>"></i>
                        </button>
                        <?php if (($folder ?? 'inbox') === 'trash'): ?>
                        <button class="btn-icon" onclick="mailRestoreMsg(<?= (int)$msg['id'] ?>)" title="Restore from trash"
                                style="color:#6ee7b7;">
                            <i class="fas fa-undo"></i>
                        </button>
                        <button class="btn-icon" onclick="mailDeletePermanent(<?= (int)$msg['id'] ?>)" title="Delete permanently"
                                style="color:#fca5a5;">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                        <?php else: ?>
                        <button class="btn-icon" onclick="mailArchiveMsg(<?= (int)$msg['id'] ?>)" title="Archive (e)">
                            <i class="fas fa-archive"></i>
                        </button>
                        <button class="btn-icon" onclick="mailDeleteMsg(<?= (int)$msg['id'] ?>)" title="Delete (#)"
                                style="color:#fca5a5;">
                            <i class="fas fa-trash"></i>
                        </button>
                        <?php endif; ?>
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
        if (d.success) {
            btn.classList.toggle('starred');
            mailToast(isStarred ? 'Unstarred' : 'Starred', {icon: isStarred ? 'fa-star' : 'fa-star', color:'#f59e0b', duration:1800});
        }
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
        if (d.success) {
            const row = document.getElementById('mail-row-' + id);
            row.style.opacity = '0'; row.style.transition = 'opacity .25s';
            setTimeout(() => row.remove(), 250);
            mailToast('Archived', {icon:'fa-archive', color:'#6ee7b7', duration:2500});
        }
    });
}

function mailDeleteMsg(id) {
    mailPostAction('/mail/delete', {id}, d => {
        if (d.success) {
            const row = document.getElementById('mail-row-' + id);
            row.style.opacity = '0'; row.style.transition = 'opacity .25s';
            setTimeout(() => row.remove(), 250);
            mailToast('Moved to Trash', {icon:'fa-trash', color:'#fca5a5', duration:2500});
        }
    });
}

function mailRestoreMsg(id) {
    mailPostAction('/mail/archive', {id, state: 0}, d => {
        if (d.success) {
            const row = document.getElementById('mail-row-' + id);
            row.style.opacity = '0'; row.style.transition = 'opacity .25s';
            setTimeout(() => row.remove(), 250);
            mailToast('Restored to Inbox', {icon:'fa-undo', color:'#6ee7b7', duration:2500});
        }
    });
}

function mailDeletePermanent(id) {
    if (!confirm('Permanently delete this message? This cannot be undone.')) return;
    mailPostAction('/mail/delete', {id, permanent: 1}, d => {
        if (d.success) {
            const row = document.getElementById('mail-row-' + id);
            row.style.opacity = '0'; row.style.transition = 'opacity .25s';
            setTimeout(() => row.remove(), 250);
            mailToast('Deleted permanently', {icon:'fa-trash-alt', color:'#fca5a5', duration:2500});
        }
    });
}
</script>

<?php View::endSection(); ?>
