<?php use Core\View; use Core\Security; ?>
<?php View::extend('linkshortner:app'); ?>
<?php View::section('content'); ?>

<div class="card">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-list" style="color:var(--accent);"></i> My Links</div>
        <a href="/projects/linkshortner/create" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> New Link</a>
    </div>

    <?php if (!empty($links)): ?>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Short Link</th>
                    <th>Destination</th>
                    <th>Clicks</th>
                    <th>Expires</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($links as $link): ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:8px;">
                        <a href="/l/<?= View::e($link['code']) ?>" target="_blank" style="color:var(--accent);font-weight:600;">/l/<?= View::e($link['code']) ?></a>
                        <button class="copy-btn" onclick="copyText('<?= View::e((defined('APP_URL') ? APP_URL : '') . '/l/' . $link['code']) ?>')" title="Copy">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <?php if ($link['title']): ?>
                        <div style="color:var(--text-secondary);font-size:12px;margin-top:3px;"><?= View::e($link['title']) ?></div>
                    <?php endif; ?>
                </td>
                <td style="max-width:220px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                    <a href="<?= View::e($link['original_url']) ?>" target="_blank" style="color:var(--text-secondary);font-size:13px;"><?= View::e($link['original_url']) ?></a>
                </td>
                <td>
                    <a href="/projects/linkshortner/analytics/<?= View::e($link['code']) ?>" style="color:var(--orange);font-weight:600;">
                        <?= number_format($link['total_clicks']) ?>
                    </a>
                    <?php if ($link['click_limit']): ?>
                        <span style="color:var(--text-secondary);font-size:12px;">/ <?= $link['click_limit'] ?></span>
                    <?php endif; ?>
                </td>
                <td style="font-size:13px;">
                    <?php if ($link['expires_at']): ?>
                        <?= date('M d, Y', strtotime($link['expires_at'])) ?>
                    <?php else: ?>
                        <span style="color:var(--text-secondary);">Never</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($link['status'] === 'active'): ?>
                        <span class="badge badge-success">Active</span>
                    <?php elseif ($link['status'] === 'expired'): ?>
                        <span class="badge badge-danger">Expired</span>
                    <?php else: ?>
                        <span class="badge badge-warning">Disabled</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div style="display:flex;gap:6px;flex-wrap:wrap;">
                        <a href="/projects/linkshortner/analytics/<?= View::e($link['code']) ?>" class="btn btn-secondary btn-sm" title="Analytics">
                            <i class="fas fa-chart-bar"></i>
                        </a>
                        <button type="button"
                                class="btn btn-secondary btn-sm"
                                title="Generate QR"
                                onclick="ecoQrOpen('<?= View::e((defined('APP_URL') ? APP_URL : '') . '/l/' . $link['code']) ?>')">
                            <i class="fas fa-qrcode" style="color:#00f0ff;"></i>
                        </button>
                        <a href="/projects/linkshortner/links/<?= $link['id'] ?>/edit" class="btn btn-secondary btn-sm" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form method="POST" action="/projects/linkshortner/links/<?= $link['id'] ?>/delete" onsubmit="return confirm('Delete this link?');" style="display:inline;">
                            <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
                            <button type="submit" class="btn btn-danger btn-sm" title="Delete"><i class="fas fa-trash"></i></button>
                        </form>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if (($totalPages ?? 1) > 1): ?>
    <div style="display:flex;gap:8px;justify-content:center;margin-top:20px;">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>" class="btn btn-sm <?= $i == $page ? 'btn-primary' : 'btn-secondary' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div style="text-align:center;padding:50px 0;color:var(--text-secondary);">
        <i class="fas fa-link" style="font-size:3rem;opacity:0.3;display:block;margin-bottom:16px;"></i>
        <p>No links yet.</p>
        <a href="/projects/linkshortner/create" class="btn btn-primary" style="margin-top:16px;">
            <i class="fas fa-plus"></i> Create your first link
        </a>
    </div>
    <?php endif; ?>
</div>

<script>
function copyText(text) {
    navigator.clipboard.writeText(text).then(() => {
        const msg = document.createElement('div');
        msg.textContent = 'Copied!';
        Object.assign(msg.style, {position:'fixed',bottom:'20px',right:'20px',background:'rgba(0,212,255,0.9)',color:'#000',padding:'10px 18px',borderRadius:'8px',fontFamily:'inherit',fontWeight:'600',zIndex:9999,transition:'opacity 0.3s'});
        document.body.appendChild(msg);
        setTimeout(() => { msg.style.opacity = '0'; setTimeout(() => msg.remove(), 300); }, 1800);
    });
}
</script>
<?php View::end(); ?>
<?php require BASE_PATH . '/views/partials/eco-qr-modal.php'; ?>
