<?php $pageTitle = ucfirst($folder ?? 'Inbox'); ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
    <div>
        <h2 style="margin:0;font-size:18px;font-weight:600;text-transform:capitalize;"><?= htmlspecialchars($folder === 'inbox' ? 'Inbox' : ucfirst($folder ?? 'Inbox')) ?></h2>
        <p class="text-muted" style="font-size:12px;margin-top:2px;"><?= (int)($total ?? 0) ?> message<?= $total == 1 ? '' : 's' ?></p>
    </div>
    <div style="display:flex;gap:8px;">
        <?php if (!empty($searchQuery ?? null)): ?>
        <span style="font-size:13px;color:#64748b;padding:6px 0;">
            Search: <strong><?= htmlspecialchars($searchQuery, ENT_QUOTES) ?></strong>
            <a href="/" style="margin-left:8px;color:#667eea;"><i class="fas fa-times"></i></a>
        </span>
        <?php endif; ?>
        <a href="/compose" class="btn btn-primary btn-sm"><i class="fas fa-pen"></i> Compose</a>
    </div>
</div>

<?php if (empty($messages)): ?>
<div class="card" style="text-align:center;padding:60px 20px;">
    <i class="fas fa-inbox" style="font-size:48px;color:#334155;margin-bottom:16px;"></i>
    <p style="color:#64748b;font-size:15px;">
        <?= isset($searchQuery) ? 'No results found for "' . htmlspecialchars($searchQuery, ENT_QUOTES) . '"' : 'Your ' . htmlspecialchars($folder ?? 'inbox') . ' is empty.' ?>
    </p>
    <?php if (empty($searchQuery)): ?>
    <p style="color:#475569;font-size:13px;margin-top:8px;">Click <em>Sync</em> to fetch new messages from your mail provider.</p>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="card" style="padding:0;overflow:hidden;">
    <table class="table">
        <thead>
            <tr>
                <th style="width:20px;"></th>
                <th>From</th>
                <th>Subject</th>
                <th style="width:130px;">Date</th>
                <th style="width:100px;">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($messages as $msg): ?>
            <tr class="<?= $msg['is_read'] ? '' : 'unread' ?>" id="row-<?= $msg['id'] ?>">
                <td>
                    <button class="star-btn <?= $msg['is_starred'] ? 'starred' : '' ?>"
                            onclick="toggleStar(<?= $msg['id'] ?>, this)"
                            title="<?= $msg['is_starred'] ? 'Unstar' : 'Star' ?>">
                        <i class="fas fa-star"></i>
                    </button>
                </td>
                <td>
                    <a href="/view/<?= $msg['id'] ?>" style="display:block;">
                        <span style="font-weight:<?= $msg['is_read'] ? '400' : '600' ?>;color:<?= $msg['is_read'] ? '#94a3b8' : '#e2e8f0' ?>;">
                            <?= htmlspecialchars($msg['from_name'] ?: $msg['from_email'] ?: '(unknown)', ENT_QUOTES) ?>
                        </span>
                        <span style="font-size:11px;color:#475569;"><?= htmlspecialchars($msg['from_email'] ?? '', ENT_QUOTES) ?></span>
                    </a>
                </td>
                <td>
                    <a href="/view/<?= $msg['id'] ?>" style="color:<?= $msg['is_read'] ? '#94a3b8' : '#e2e8f0' ?>;font-weight:<?= $msg['is_read'] ? '400' : '500' ?>;">
                        <?= htmlspecialchars(mb_substr($msg['subject'] ?? '(no subject)', 0, 80), ENT_QUOTES) ?>
                    </a>
                </td>
                <td class="text-muted" style="font-size:12px;white-space:nowrap;">
                    <?= $msg['date_sent'] ? date('M j, g:i a', strtotime($msg['date_sent'])) : '—' ?>
                </td>
                <td>
                    <div style="display:flex;gap:4px;">
                        <button class="btn btn-sm btn-secondary" onclick="toggleRead(<?= $msg['id'] ?>, <?= $msg['is_read'] ? 0 : 1 ?>, this)"
                                title="<?= $msg['is_read'] ? 'Mark unread' : 'Mark read' ?>">
                            <i class="fas fa-<?= $msg['is_read'] ? 'envelope' : 'envelope-open' ?>"></i>
                        </button>
                        <button class="btn btn-sm btn-secondary" onclick="archiveMsg(<?= $msg['id'] ?>, this)" title="Archive">
                            <i class="fas fa-archive"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="deleteMsg(<?= $msg['id'] ?>, this)" title="Delete">
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
$totalPages = (int)ceil(($total ?? 0) / max(1, (int)($perPage ?? 30)));
if ($totalPages > 1):
?>
<div style="display:flex;justify-content:center;gap:6px;margin-top:16px;flex-wrap:wrap;">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
    <a href="?page=<?= $i ?><?= isset($folder) ? '&folder=' . urlencode($folder) : '' ?>"
       class="btn btn-sm <?= $i === (int)($page ?? 1) ? 'btn-primary' : 'btn-secondary' ?>"><?= $i ?></a>
    <?php endfor; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<script>
function toggleStar(id, btn) {
    const isStarred = btn.classList.contains('starred');
    postAction('/star', {id: id, state: isStarred ? 0 : 1}, d => {
        if (d.success) btn.classList.toggle('starred');
    });
}

function toggleRead(id, newState, btn) {
    postAction('/mark-read', {id: id, state: newState}, d => {
        if (d.success) {
            const row = document.getElementById('row-' + id);
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

function archiveMsg(id, btn) {
    postAction('/archive', {id: id, state: 1}, d => {
        if (d.success) document.getElementById('row-' + id).remove();
    });
}

function deleteMsg(id, btn) {
    if (!confirm('Move this message to trash?')) return;
    postAction('/delete', {id: id}, d => {
        if (d.success) document.getElementById('row-' + id).remove();
    });
}
</script>
