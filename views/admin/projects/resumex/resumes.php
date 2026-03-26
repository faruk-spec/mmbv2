<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1><i class="fas fa-file-alt" style="color:var(--cyan);"></i> ResumeX — All Resumes</h1>
        <p style="color:var(--text-secondary);"><?= number_format($total) ?> resume<?= $total !== 1 ? 's' : '' ?> across all users</p>
    </div>
    <a href="/admin/projects/resumex" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Overview</a>
</div>

<!-- Search -->
<form method="GET" style="margin-bottom:20px;display:flex;gap:10px;">
    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
           placeholder="Search by title, user name or email…"
           style="flex:1;padding:9px 14px;border-radius:8px;border:1px solid var(--border-color);background:var(--bg-card);color:var(--text-primary);font-size:0.875rem;">
    <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Search</button>
    <?php if ($search !== ''): ?>
        <a href="/admin/projects/resumex/resumes" class="btn btn-secondary">Clear</a>
    <?php endif; ?>
</form>

<div class="card">
    <?php if (empty($resumes)): ?>
        <p style="text-align:center;color:var(--text-secondary);padding:40px 20px;">No resumes found.</p>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>User</th>
                    <th>Template</th>
                    <th>Created</th>
                    <th>Updated</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($resumes as $r): ?>
                <tr>
                    <td style="color:var(--text-secondary);font-size:0.8rem;"><?= (int)$r['id'] ?></td>
                    <td><strong><?= htmlspecialchars($r['title']) ?></strong></td>
                    <td style="font-size:0.82rem;">
                        <?= htmlspecialchars($r['user_name'] ?? '') ?>
                        <?php if (!empty($r['user_email'])): ?>
                            <br><span style="color:var(--text-secondary);font-size:0.75rem;"><?= htmlspecialchars($r['user_email']) ?></span>
                        <?php endif; ?>
                    </td>
                    <td><code style="font-size:0.78rem;color:var(--cyan);"><?= htmlspecialchars($r['template']) ?></code></td>
                    <td style="font-size:0.8rem;color:var(--text-secondary);"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                    <td style="font-size:0.8rem;color:var(--text-secondary);"><?= date('d M Y H:i', strtotime($r['updated_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
    <div style="display:flex;gap:6px;padding:16px;flex-wrap:wrap;">
        <?php for ($p = 1; $p <= $pages; $p++): ?>
            <a href="?page=<?= $p ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>"
               style="padding:6px 12px;border-radius:6px;font-size:0.8rem;border:1px solid var(--border-color);color:<?= $p === $page ? 'var(--cyan)' : 'var(--text-secondary)' ?>;background:<?= $p === $page ? 'rgba(0,240,255,0.1)' : 'transparent' ?>;text-decoration:none;">
                <?= $p ?>
            </a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>

    <?php endif; ?>
</div>

<?php View::endSection(); ?>
