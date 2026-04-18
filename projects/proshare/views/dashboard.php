<?php use Core\View; ?>
<?php View::extend('proshare:app'); ?>

<?php View::section('content'); ?>

<!-- Quick Actions Banner -->
<div class="ps-grid ps-grid-2 mb-3">
    <a href="/projects/proshare/upload" class="ps-action-card" style="text-decoration: none; display: flex; align-items: center; gap: 18px; padding: 22px 24px; background: linear-gradient(135deg, rgba(255,170,0,0.12), rgba(255,46,196,0.08)); border: 1px solid rgba(255,170,0,0.2); border-radius: 14px; transition: all 0.2s;">
        <div style="width: 52px; height: 52px; background: linear-gradient(135deg, var(--ps-primary), var(--ps-secondary)); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="fas fa-cloud-upload-alt" style="color: #06060a; font-size: 1.3rem;"></i>
        </div>
        <div>
            <div style="font-weight: 700; font-size: 1rem; color: var(--text-primary);">Upload Files</div>
            <div style="font-size: 0.8rem; color: var(--text-secondary);">Share up to 500 MB securely</div>
        </div>
        <i class="fas fa-arrow-right" style="margin-left: auto; color: var(--ps-primary); font-size: 0.9rem;"></i>
    </a>
    
    <a href="/projects/proshare/text" class="ps-action-card" style="text-decoration: none; display: flex; align-items: center; gap: 18px; padding: 22px 24px; background: linear-gradient(135deg, rgba(0,240,255,0.08), rgba(0,240,255,0.03)); border: 1px solid rgba(0,240,255,0.15); border-radius: 14px; transition: all 0.2s;">
        <div style="width: 52px; height: 52px; background: linear-gradient(135deg, var(--ps-accent), rgba(0,240,255,0.5)); border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <i class="fas fa-file-alt" style="color: #06060a; font-size: 1.3rem;"></i>
        </div>
        <div>
            <div style="font-weight: 700; font-size: 1rem; color: var(--text-primary);">Share Text</div>
            <div style="font-size: 0.8rem; color: var(--text-secondary);">Code snippets, notes & more</div>
        </div>
        <i class="fas fa-arrow-right" style="margin-left: auto; color: var(--ps-accent); font-size: 0.9rem;"></i>
    </a>
</div>

<!-- Statistics Grid -->
<div class="ps-grid ps-grid-4 mb-3">
    <div class="stat-card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
            <div style="width: 42px; height: 42px; background: rgba(0,240,255,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-file" style="color: var(--cyan);"></i>
            </div>
            <i class="fas fa-arrow-up" style="color: var(--cyan); font-size: 0.75rem; opacity: 0.6;" aria-hidden="true"></i>
        </div>
        <div class="stat-value" style="color: var(--cyan);"><?= number_format($stats['total_files'] ?? 0) ?></div>
        <div class="stat-label">Total Files</div>
    </div>
    
    <div class="stat-card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
            <div style="width: 42px; height: 42px; background: rgba(255,46,196,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-file-alt" style="color: var(--magenta);"></i>
            </div>
            <i class="fas fa-arrow-up" style="color: var(--magenta); font-size: 0.75rem; opacity: 0.6;" aria-hidden="true"></i>
        </div>
        <div class="stat-value" style="color: var(--magenta);"><?= number_format($stats['total_texts'] ?? 0) ?></div>
        <div class="stat-label">Text Shares</div>
    </div>
    
    <div class="stat-card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
            <div style="width: 42px; height: 42px; background: rgba(0,255,136,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-download" style="color: var(--green);"></i>
            </div>
            <i class="fas fa-arrow-up" style="color: var(--green); font-size: 0.75rem; opacity: 0.6;" aria-hidden="true"></i>
        </div>
        <div class="stat-value" style="color: var(--green);"><?= number_format($stats['total_downloads'] ?? 0) ?></div>
        <div class="stat-label">Total Downloads</div>
    </div>
    
    <div class="stat-card">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
            <div style="width: 42px; height: 42px; background: rgba(255,170,0,0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-share-alt" style="color: var(--orange);"></i>
            </div>
            <i class="fas fa-circle" style="color: var(--green); font-size: 0.5rem;" aria-hidden="true"></i>
        </div>
        <div class="stat-value" style="color: var(--orange);"><?= number_format($stats['active_shares'] ?? 0) ?></div>
        <div class="stat-label">Active Shares</div>
    </div>
</div>

<!-- Recent Files & Text Shares in two columns -->
<div class="ps-grid ps-grid-2 mb-3">

    <!-- Recent Files -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file" style="color: var(--cyan);"></i> Recent Files
            </h3>
            <a href="/projects/proshare/files" class="ps-btn ps-btn-secondary" style="padding: 4px 12px; font-size: 0.8rem; text-decoration: none;">
                View All <i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i>
            </a>
        </div>
        
        <?php if (!empty($recentFiles)): ?>
            <div style="padding: 0;">
                <?php foreach ($recentFiles as $file): ?>
                    <div style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-bottom: 1px solid var(--border-color);">
                        <div style="width: 36px; height: 36px; background: rgba(0,240,255,0.08); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-file" style="color: var(--cyan); font-size: 0.875rem;"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 0.875rem; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text-primary);">
                                <?= View::e($file['original_name']) ?>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-secondary);">
                                <?= number_format($file['size'] / 1024 / 1024, 1) ?> MB &middot; <?= $file['downloads'] ?> downloads
                            </div>
                        </div>
                        <div style="flex-shrink: 0;">
                            <?php if ($file['status'] === 'active'): ?>
                                <span class="badge badge-success" style="font-size: 0.7rem;">Active</span>
                            <?php else: ?>
                                <span class="badge badge-danger" style="font-size: 0.7rem;"><?= ucfirst($file['status']) ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="/projects/proshare/preview/<?= View::e($file['short_code']) ?>" style="color: var(--text-secondary); font-size: 0.875rem; text-decoration: none; flex-shrink: 0;">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="padding: 2rem; text-align: center; color: var(--text-secondary); font-size: 0.875rem;">
                <i class="fas fa-folder-open" style="font-size: 2rem; display: block; margin-bottom: 10px; opacity: 0.4;"></i>
                No files yet &mdash; <a href="/projects/proshare/upload" style="color: var(--cyan);">upload one</a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Recent Text Shares -->
    <div class="card" style="margin-bottom: 0;">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-file-alt" style="color: var(--magenta);"></i> Recent Texts
            </h3>
            <a href="/projects/proshare/files" class="ps-btn ps-btn-secondary" style="padding: 4px 12px; font-size: 0.8rem; text-decoration: none;">
                View All <i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i>
            </a>
        </div>
        
        <?php if (!empty($recentTexts)): ?>
            <div style="padding: 0;">
                <?php foreach ($recentTexts as $text): ?>
                    <div style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-bottom: 1px solid var(--border-color);">
                        <div style="width: 36px; height: 36px; background: rgba(255,46,196,0.08); border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <i class="fas fa-file-alt" style="color: var(--magenta); font-size: 0.875rem;"></i>
                        </div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-size: 0.875rem; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; color: var(--text-primary);">
                                <?= View::e($text['title'] ?: 'Untitled') ?>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--text-secondary);">
                                <?= $text['views'] ?> views &middot; <?= date('M d', strtotime($text['created_at'])) ?>
                            </div>
                        </div>
                        <div style="flex-shrink: 0;">
                            <?php if ($text['status'] === 'active'): ?>
                                <span class="badge badge-success" style="font-size: 0.7rem;">Active</span>
                            <?php else: ?>
                                <span class="badge badge-danger" style="font-size: 0.7rem;"><?= ucfirst($text['status']) ?></span>
                            <?php endif; ?>
                        </div>
                        <a href="/t/<?= View::e($text['short_code']) ?>" target="_blank" style="color: var(--text-secondary); font-size: 0.875rem; text-decoration: none; flex-shrink: 0;">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div style="padding: 2rem; text-align: center; color: var(--text-secondary); font-size: 0.875rem;">
                <i class="fas fa-file-alt" style="font-size: 2rem; display: block; margin-bottom: 10px; opacity: 0.4;"></i>
                No text shares yet &mdash; <a href="/projects/proshare/text" style="color: var(--cyan);">create one</a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Recent Notifications -->
<?php if (!empty($notifications)): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-bell" style="color: var(--orange);"></i> Recent Notifications
            <span class="badge" style="background: rgba(255,170,0,0.15); color: var(--orange); font-size: 0.7rem; padding: 2px 8px; border-radius: 1rem; margin-left: 6px;"><?= count($notifications) ?></span>
        </h3>
        <a href="/projects/proshare/notifications" class="ps-btn ps-btn-secondary" style="padding: 4px 12px; font-size: 0.8rem; text-decoration: none;">
            View All <i class="fas fa-chevron-right" style="font-size: 0.7rem;"></i>
        </a>
    </div>
    
    <div style="padding: 0;">
        <?php foreach ($notifications as $notification): ?>
            <div style="padding: 14px 20px; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 12px;">
                <div style="width: 36px; height: 36px; background: rgba(0, 240, 255, 0.1); border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <?php if ($notification['type'] === 'download'): ?>
                        <i class="fas fa-download" style="color: var(--cyan); font-size: 0.85rem;"></i>
                    <?php elseif ($notification['type'] === 'expiry_warning'): ?>
                        <i class="fas fa-clock" style="color: var(--orange); font-size: 0.85rem;"></i>
                    <?php elseif ($notification['type'] === 'security_alert'): ?>
                        <i class="fas fa-shield-alt" style="color: var(--ps-danger); font-size: 0.85rem;"></i>
                    <?php else: ?>
                        <i class="fas fa-bell" style="color: var(--cyan); font-size: 0.85rem;"></i>
                    <?php endif; ?>
                </div>
                <div style="flex: 1; min-width: 0;">
                    <div style="font-size: 0.875rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= View::e($notification['message']) ?></div>
                    <div class="text-muted" style="font-size: 0.75rem; margin-top: 2px;">
                        <?= date('M d, H:i', strtotime($notification['created_at'])) ?>
                    </div>
                </div>
                <span style="width: 8px; height: 8px; background: var(--cyan); border-radius: 50%; flex-shrink: 0;"></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php View::endSection(); ?>

<?php View::section('styles'); ?>
<style>
    .stat-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 14px;
        padding: 20px 22px;
        transition: transform 0.15s, box-shadow 0.15s;
    }
    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }
    .stat-value {
        font-size: 2rem;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 4px;
    }
    .stat-label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: var(--text-secondary);
    }
    .ps-action-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }
    /* Responsive stat grid */
    @media (max-width: 1024px) {
        .ps-grid-4 { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 640px) {
        .ps-grid-4,
        .ps-grid-2 { grid-template-columns: 1fr; }
        .stat-value { font-size: 1.6rem; }
    }
</style>
<?php View::endSection(); ?>
