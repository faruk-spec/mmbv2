<?php
/**
 * CodeXPro - Snippets List View
 */
use Core\View;
use Core\Auth;

$user = Auth::user();
$pageTitle = 'Code Snippets';
$currentPage = 'snippets';

ob_start();
?>

<div class="page-header">
    <h1><i class="fas fa-code"></i> Code Snippets</h1>
    <a href="#" onclick="showNewSnippetModal(); return false;" class="btn btn-primary">
        <i class="fas fa-plus"></i> New Snippet
    </a>
</div>

<div class="content-grid">
    <?php if (empty($snippets)): ?>
        <div class="empty-state">
            <i class="fas fa-code" style="font-size: 4rem; color: #4a5568; margin-bottom: 1rem;"></i>
            <h3>No Snippets Yet</h3>
            <p>Create your first code snippet to get started!</p>
            <a href="#" onclick="showNewSnippetModal(); return false;" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create Snippet
            </a>
        </div>
    <?php else: ?>
        <?php foreach ($snippets as $snippet): ?>
            <div class="card">
                <div class="card-header">
                    <h3><?= View::e($snippet['title']) ?></h3>
                    <span class="badge badge-<?= View::e($snippet['language'] ?? 'plaintext') ?>">
                        <?= View::e(strtoupper($snippet['language'] ?? 'plaintext')) ?>
                    </span>
                </div>
                <?php if (!empty($snippet['description'])): ?>
                    <div class="card-body">
                        <p><?= View::e($snippet['description']) ?></p>
                    </div>
                <?php endif; ?>
                <div class="card-footer">
                    <div class="card-meta">
                        <span><i class="fas fa-calendar"></i> <?= date('M j, Y', strtotime($snippet['created_at'])) ?></span>
                        <span class="badge <?= !empty($snippet['is_public']) ? 'badge-success' : 'badge-secondary' ?>">
                            <?= !empty($snippet['is_public']) ? 'Public' : 'Private' ?>
                        </span>
                    </div>
                    <div class="card-actions">
                        <a href="/projects/codexpro/snippets/<?= $snippet['id'] ?>" class="btn btn-sm">
                            <i class="fas fa-eye"></i> View
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- New Snippet Modal -->
<div id="newSnippetModal" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h2><i class="fas fa-plus-circle"></i> Create New Snippet</h2>
            <span class="modal-close" onclick="closeNewSnippetModal()">&times;</span>
        </div>
        <form id="newSnippetForm" onsubmit="createSnippet(event)" style="padding: 24px;">
            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
            <div class="form-group">
                <label for="snippetTitle">Title *</label>
                <input type="text" id="snippetTitle" name="title" required class="form-control" placeholder="Enter snippet title">
            </div>
            <div class="form-group">
                <label for="snippetDescription">Description</label>
                <textarea id="snippetDescription" name="description" class="form-control" rows="2" placeholder="Brief description of the snippet (optional)"></textarea>
            </div>
            <div class="form-group">
                <label for="snippetLanguage">Language *</label>
                <select id="snippetLanguage" name="language" required class="form-control">
                    <option value="">Select a language...</option>
                    <option value="javascript">JavaScript</option>
                    <option value="python">Python</option>
                    <option value="php">PHP</option>
                    <option value="html">HTML</option>
                    <option value="css">CSS</option>
                    <option value="sql">SQL</option>
                    <option value="java">Java</option>
                    <option value="cpp">C++</option>
                    <option value="csharp">C#</option>
                    <option value="ruby">Ruby</option>
                    <option value="go">Go</option>
                    <option value="rust">Rust</option>
                </select>
            </div>
            <div class="form-group">
                <label for="snippetCode">Code *</label>
                <textarea id="snippetCode" name="code" required class="form-control" rows="12" style="font-family: 'Courier New', monospace; font-size: 14px;" placeholder="Paste your code here..."></textarea>
            </div>
            <div class="form-group">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" name="is_public" style="margin-right: 8px; width: 18px; height: 18px;">
                    <span>Make this snippet public</span>
                </label>
                <small style="display: block; margin-top: 4px; color: var(--text-secondary);">Public snippets can be viewed by anyone with the link</small>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeNewSnippetModal()" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create Snippet
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function showNewSnippetModal() {
    document.getElementById('newSnippetModal').style.display = 'flex';
}

function closeNewSnippetModal() {
    document.getElementById('newSnippetModal').style.display = 'none';
    document.getElementById('newSnippetForm').reset();
}

function createSnippet(e) {
    e.preventDefault();
    const formData = new FormData(e.target);
    
    fetch('/projects/codexpro/snippets', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/projects/codexpro/snippets/' + data.snippet_id;
        } else {
            const errorMsg = document.createElement('div');
            errorMsg.textContent = 'Error: ' + (data.error || 'Failed to create snippet');
            errorMsg.style.color = '#ff6b6b';
            errorMsg.style.marginTop = '10px';
            document.getElementById('newSnippetForm').appendChild(errorMsg);
        }
    });
}
</script>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
