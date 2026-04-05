<?php use Core\View; use Core\Security; ?>
<?php View::extend('notex:app'); ?>
<?php View::section('content'); ?>

<div style="max-width:800px;">
    <div class="card mb-3">
        <div class="card-header">
            <div class="card-title" style="gap:10px;">
                <div style="width:12px;height:12px;border-radius:50%;background:<?= View::e($note['color'] ?? '#ffd700') ?>;"></div>
                <?= View::e($note['title']) ?>
                <?php if ($note['is_pinned']): ?><i class="fas fa-thumbtack pin-icon"></i><?php endif; ?>
            </div>
            <div style="display:flex;gap:8px;">
                <a href="/projects/notex/notes/<?= $note['id'] ?>/edit" class="btn btn-secondary btn-sm"><i class="fas fa-edit"></i> Edit</a>
                <a href="/projects/notex/notes" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
            </div>
        </div>

        <?php if (!empty($note['folder_name'])): ?>
        <div style="margin-bottom:14px;font-size:13px;color:var(--text-secondary);">
            <i class="fas fa-folder" style="margin-right:5px;"></i><?= View::e($note['folder_name']) ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($tags)): ?>
        <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px;">
            <?php foreach ($tags as $tag): ?>
            <span style="background:rgba(255,255,255,0.06);border-radius:20px;padding:3px 10px;font-size:12px;color:<?= View::e($tag['color']) ?>;">
                <?= View::e($tag['name']) ?>
            </span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <div style="background:var(--bg-secondary);border-radius:8px;padding:20px;line-height:1.8;font-size:15px;white-space:pre-wrap;word-break:break-word;">
            <?= View::e($note['content'] ?? '') ?>
        </div>

        <div style="margin-top:14px;font-size:12px;color:var(--text-secondary);">
            Updated <?= date('M d, Y H:i', strtotime($note['updated_at'] ?? $note['created_at'])) ?>
        </div>
    </div>
</div>

<?php View::end(); ?>
