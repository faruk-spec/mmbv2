<a href="/projects/proshare" class="back-link">← Back to Dashboard</a>

<h1 style="margin-bottom: 30px;">My Files</h1>

<div class="card">
    <?php if (empty($files)): ?>
        <div style="text-align: center; padding: 60px 20px; color: var(--text-secondary);">
            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="opacity: 0.5; margin-bottom: 15px;">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14,2 14,8 20,8"/>
            </svg>
            <h3 style="margin-bottom: 10px;">No Files Yet</h3>
            <p style="margin-bottom: 20px;">Upload your first file to get started.</p>
            <a href="/projects/proshare/upload" class="btn btn-primary">Upload File</a>
        </div>
    <?php else: ?>
        <?php foreach ($files as $file): ?>
            <div class="file-item">
                <div class="file-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="var(--orange)" stroke-width="2">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14,2 14,8 20,8"/>
                    </svg>
                </div>
                <div class="file-info">
                    <div class="file-name"><?= htmlspecialchars($file['original_name']) ?></div>
                    <div class="file-meta">
                        <?= round($file['size'] / 1024, 2) ?> KB • 
                        <?= $file['downloads'] ?> downloads • 
                        <?= date('M d, Y', strtotime($file['created_at'])) ?>
                    </div>
                </div>
                <div style="display: flex; gap: 8px;">
                    <a href="/projects/proshare/share/<?= htmlspecialchars($file['short_code']) ?>" 
                       class="btn btn-secondary" style="padding: 8px 16px;">Share</a>
                    <a href="/projects/proshare/download/<?= htmlspecialchars($file['short_code']) ?>" 
                       class="btn btn-secondary" style="padding: 8px 16px;">Download</a>
                    <form method="POST" action="/projects/proshare/delete/<?= htmlspecialchars($file['short_code']) ?>" 
                          style="display: inline;" onsubmit="return confirm('Delete this file?');">
                        <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
                        <button type="submit" class="btn btn-danger" style="padding: 8px 16px;">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
