<?php
/**
 * CodeXPro - Single Snippet View with Admin Theme and Quick Edit
 */
use Core\View;
use Core\Auth;

$user = Auth::user();
$pageTitle = View::e($snippet['title']) . ' - CodeXPro';
$isOwner = $snippet['user_id'] == $user['id'];

// Start layout
ob_start();
?>

<div class="page-header">
    <div class="page-header-content">
        <div class="page-title">
            <i class="fas fa-code"></i>
            <h1 id="snippetTitle"><?= View::e($snippet['title']) ?></h1>
        </div>
        <div class="page-actions">
            <?php if ($isOwner): ?>
                <button onclick="toggleQuickEdit()" class="btn btn-secondary" id="quickEditBtn">
                    <i class="fas fa-sliders-h"></i> Quick Config
                </button>
                <a href="/projects/codexpro/snippets/<?= $snippet['id'] ?>/edit" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Full Edit
                </a>
                <button onclick="deleteSnippet(<?= $snippet['id'] ?>)" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete
                </button>
            <?php endif; ?>
            <?php if ($snippet['is_public']): ?>
                <button onclick="shareSnippet()" class="btn btn-success">
                    <i class="fas fa-share-alt"></i> Share
                </button>
            <?php endif; ?>
            <button onclick="copyCode()" class="btn btn-secondary">
                <i class="fas fa-copy"></i> Copy Code
            </button>
        </div>
    </div>
</div>

<?php if ($isOwner): ?>
<!-- Quick Edit Panel -->
<div id="quickEditPanel" class="quick-edit-panel" style="display:none;" onclick="event.stopPropagation();">
    <div class="quick-edit-content">
        <h3><i class="fas fa-sliders-h"></i> Quick Configuration</h3>
        <div class="quick-edit-form">
            <div class="form-group">
                <label for="quickTitle">Title</label>
                <input type="text" id="quickTitle" class="form-control" value="<?= View::e($snippet['title']) ?>" onclick="event.stopPropagation();">
            </div>
            <div class="form-group">
                <label for="quickDescription">Description</label>
                <textarea id="quickDescription" class="form-control" rows="2" onclick="event.stopPropagation();"><?= View::e($snippet['description']) ?></textarea>
            </div>
            <div class="form-group">
                <label class="toggle-label" onclick="event.stopPropagation();">
                    <input type="checkbox" id="quickPublic" <?= $snippet['is_public'] ? 'checked' : '' ?> onclick="event.stopPropagation();">
                    <span class="toggle-slider"></span>
                    <span class="toggle-text">Public Snippet</span>
                </label>
            </div>
            <div class="form-actions">
                <button onclick="saveQuickEdit()" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <button onclick="toggleQuickEdit()" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </button>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="content-grid">
    <div class="card">
        <div class="card-header">
            <div class="snippet-meta">
                <span class="badge badge-language"><?= View::e($snippet['language'] ?? 'plaintext') ?></span>
                <span class="badge <?= $snippet['is_public'] ? 'badge-success' : 'badge-secondary' ?>" id="visibilityBadge">
                    <i class="fas fa-<?= $snippet['is_public'] ? 'globe' : 'lock' ?>"></i>
                    <span id="visibilityText"><?= $snippet['is_public'] ? 'Public' : 'Private' ?></span>
                </span>
                <span class="snippet-date" title="<?= date('F j, Y g:i A', strtotime($snippet['created_at'])) ?>">
                    <i class="fas fa-calendar"></i>
                    <span class="relative-time" data-time="<?= strtotime($snippet['created_at']) ?>"></span>
                </span>
                <?php if (isset($snippet['updated_at']) && $snippet['updated_at'] != $snippet['created_at']): ?>
                    <span class="snippet-date" title="<?= date('F j, Y g:i A', strtotime($snippet['updated_at'])) ?>">
                        <i class="fas fa-edit"></i>
                        Edited <span class="relative-time" data-time="<?= strtotime($snippet['updated_at']) ?>"></span>
                    </span>
                <?php endif; ?>
                <?php if (isset($snippet['views'])): ?>
                    <span class="snippet-views">
                        <i class="fas fa-eye"></i>
                        <?= number_format($snippet['views']) ?> views
                    </span>
                <?php endif; ?>
            </div>
        </div>
        
        <?php if (!empty($snippet['description'])): ?>
            <div class="card-body">
                <p class="snippet-description" id="snippetDescription"><?= View::e($snippet['description']) ?></p>
            </div>
        <?php endif; ?>
        
        <div class="code-container">
            <pre><code class="language-<?= View::e($snippet['language'] ?? 'plaintext') ?>"><?= View::e($snippet['code']) ?></code></pre>
        </div>
        
        <?php if (!empty($snippet['tags'])): ?>
            <div class="card-footer">
                <div class="snippet-tags">
                    <i class="fas fa-tags"></i>
                    <?php foreach (explode(',', $snippet['tags']) as $tag): ?>
                        <span class="tag"><?= View::e(trim($tag)) ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/styles/atom-one-dark.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.8.0/highlight.min.js"></script>
<script>
    // Initialize syntax highlighting
    hljs.highlightAll();
    
    // Expose snippet ID globally for layout.php functions
    window.snippetId = <?= $snippet['id'] ?>;
    window.isOwner = <?= $isOwner ? 'true' : 'false' ?>;
</script>

<style>
/* Existing styles */
.snippet-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.snippet-meta span {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 0.875rem;
    color: #94a3b8;
}

.badge-language {
    background: linear-gradient(135deg, #06b6d4 0%, #3b82f6 100%);
    color: white;
    font-weight: 600;
}

.snippet-description {
    font-size: 1rem;
    color: #cbd5e1;
    line-height: 1.6;
    margin: 0;
}

.code-container {
    background: #1e293b;
    border-radius: 8px;
    overflow: hidden;
    margin: 0;
}

.code-container pre {
    margin: 0;
    padding: 1.5rem;
    overflow-x: auto;
}

.code-container code {
    font-family: 'Fira Code', 'Monaco', 'Courier New', monospace;
    font-size: 0.875rem;
    line-height: 1.6;
}

.snippet-tags {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.snippet-tags i {
    color: #94a3b8;
}

.tag {
    background: rgba(59, 130, 246, 0.1);
    color: #3b82f6;
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

.notification {
    position: fixed;
    top: 80px;
    right: 20px;
    background: #1e293b;
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
    display: flex;
    align-items: center;
    gap: 10px;
    opacity: 0;
    transform: translateX(100%);
    transition: all 0.3s ease;
    z-index: 10000;
}

.notification.show {
    opacity: 1;
    transform: translateX(0);
}

.notification-success {
    border-left: 4px solid #10b981;
}

.notification-error {
    border-left: 4px solid #ef4444;
}

.notification-info {
    border-left: 4px solid #3b82f6;
}
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
