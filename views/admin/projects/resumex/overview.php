<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1><i class="fas fa-file-alt" style="color:var(--cyan);"></i> ResumeX — Overview</h1>
        <p style="color:var(--text-secondary);">Resume builder statistics and template management</p>
    </div>
    <div style="display:flex;gap:8px;">
        <a href="/admin/projects/resumex/templates" class="btn btn-secondary"><i class="fas fa-layer-group"></i> Templates</a>
        <a href="/admin/projects/resumex/resumes" class="btn btn-secondary"><i class="fas fa-list"></i> All Resumes</a>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-4" style="margin-bottom:24px;">
    <div class="stat-card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:var(--cyan);"><?= number_format((int)($stats['total'] ?? 0)) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Total Resumes</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:#34d399;"><?= number_format((int)($stats['today'] ?? 0)) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Created Today</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:#a78bfa;"><?= number_format((int)($stats['users'] ?? 0)) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Active Users</div>
    </div>
    <div class="stat-card" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:#f59e0b;"><?= number_format((int)($stats['thisMonth'] ?? 0)) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">This Month</div>
    </div>
</div>

<div class="grid grid-2" style="gap:20px;">

    <!-- Recent Resumes -->
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <h3 class="card-title"><i class="fas fa-clock"></i> Recent Resumes</h3>
            <a href="/admin/projects/resumex/resumes" style="font-size:0.8rem;color:var(--cyan);">View all →</a>
        </div>
        <?php if (empty($recentResumes)): ?>
            <p style="color:var(--text-secondary);padding:20px;text-align:center;">No resumes yet.</p>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>User</th>
                        <th>Template</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentResumes as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['title']) ?></td>
                        <td style="font-size:0.8rem;color:var(--text-secondary);"><?= htmlspecialchars($r['user_name'] ?? $r['user_email'] ?? '—') ?></td>
                        <td><code style="font-size:0.75rem;"><?= htmlspecialchars($r['template']) ?></code></td>
                        <td style="font-size:0.8rem;color:var(--text-secondary);"><?= date('d M Y', strtotime($r['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

    <!-- Template summary -->
    <div class="card">
        <div class="card-header" style="display:flex;justify-content:space-between;align-items:center;">
            <h3 class="card-title"><i class="fas fa-layer-group"></i> Templates</h3>
            <a href="/admin/projects/resumex/templates" style="font-size:0.8rem;color:var(--cyan);">Manage →</a>
        </div>
        <div style="padding:16px;display:flex;flex-direction:column;gap:12px;">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px;background:rgba(59,130,246,0.06);border:1px solid rgba(59,130,246,0.15);border-radius:8px;">
                <div>
                    <div style="font-size:1.4rem;font-weight:700;color:var(--cyan);"><?= (int)$builtinCount ?></div>
                    <div style="font-size:0.8rem;color:var(--text-secondary);">Built-in Templates</div>
                </div>
                <i class="fas fa-cubes" style="font-size:1.5rem;color:var(--cyan);opacity:0.4;"></i>
            </div>
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px;background:rgba(139,92,246,0.06);border:1px solid rgba(139,92,246,0.15);border-radius:8px;">
                <div>
                    <div style="font-size:1.4rem;font-weight:700;color:#a78bfa;"><?= count($customTemplates) ?></div>
                    <div style="font-size:0.8rem;color:var(--text-secondary);">Custom Uploaded Templates</div>
                </div>
                <i class="fas fa-upload" style="font-size:1.5rem;color:#a78bfa;opacity:0.4;"></i>
            </div>
            <a href="/admin/projects/resumex/templates" class="btn btn-primary" style="width:100%;text-align:center;justify-content:center;">
                <i class="fas fa-plus"></i> Upload New Template
            </a>
            <a href="/admin/projects/resumex/templates/sample-download" style="display:block;text-align:center;font-size:0.82rem;color:var(--cyan);margin-top:4px;">
                <i class="fas fa-download"></i> Download sample-template.php
            </a>
        </div>
    </div>

</div>

<?php View::endSection(); ?>
