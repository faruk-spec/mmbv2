<?php
/**
 * CodeXPro - Edit Snippet View with Admin Theme
 */
use Core\View;
use Core\Auth;
use Core\Security;

$user = Auth::user();
$pageTitle = 'Edit Snippet - CodeXPro';

// Start layout
ob_start();
?>

<div class="page-header">
    <div class="page-header-content">
        <div class="page-title">
            <i class="fas fa-edit"></i>
            <h1>Edit Snippet</h1>
        </div>
        <div class="page-actions">
            <a href="/projects/codexpro/snippets/<?= $snippet['id'] ?>" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancel
            </a>
        </div>
    </div>
</div>

<div class="content-grid">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-code"></i> Snippet Details</h3>
        </div>
        <div class="card-body">
            <form id="editSnippetForm" method="POST">
                <input type="hidden" name="csrf_token" value="<?= Security::generateToken() ?>">
                
                <div class="form-group">
                    <label for="title">
                        <i class="fas fa-heading"></i> Title *
                    </label>
                    <input 
                        type="text" 
                        id="title" 
                        name="title" 
                        value="<?= View::e($snippet['title']) ?>"
                        class="form-control" 
                        required
                        placeholder="Enter snippet title"
                    >
                </div>
                
                <div class="form-group">
                    <label for="description">
                        <i class="fas fa-align-left"></i> Description
                    </label>
                    <textarea 
                        id="description" 
                        name="description" 
                        class="form-control" 
                        rows="3"
                        placeholder="Optional description"
                    ><?= View::e($snippet['description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="language">
                            <i class="fas fa-code"></i> Language *
                        </label>
                        <select id="language" name="language" class="form-control" required>
                            <option value="javascript" <?= ($snippet['language'] ?? '') === 'javascript' ? 'selected' : '' ?>>JavaScript</option>
                            <option value="python" <?= ($snippet['language'] ?? '') === 'python' ? 'selected' : '' ?>>Python</option>
                            <option value="php" <?= ($snippet['language'] ?? '') === 'php' ? 'selected' : '' ?>>PHP</option>
                            <option value="java" <?= ($snippet['language'] ?? '') === 'java' ? 'selected' : '' ?>>Java</option>
                            <option value="cpp" <?= ($snippet['language'] ?? '') === 'cpp' ? 'selected' : '' ?>>C++</option>
                            <option value="csharp" <?= ($snippet['language'] ?? '') === 'csharp' ? 'selected' : '' ?>>C#</option>
                            <option value="ruby" <?= ($snippet['language'] ?? '') === 'ruby' ? 'selected' : '' ?>>Ruby</option>
                            <option value="go" <?= ($snippet['language'] ?? '') === 'go' ? 'selected' : '' ?>>Go</option>
                            <option value="rust" <?= ($snippet['language'] ?? '') === 'rust' ? 'selected' : '' ?>>Rust</option>
                            <option value="typescript" <?= ($snippet['language'] ?? '') === 'typescript' ? 'selected' : '' ?>>TypeScript</option>
                            <option value="sql" <?= ($snippet['language'] ?? '') === 'sql' ? 'selected' : '' ?>>SQL</option>
                            <option value="html" <?= ($snippet['language'] ?? '') === 'html' ? 'selected' : '' ?>>HTML</option>
                            <option value="css" <?= ($snippet['language'] ?? '') === 'css' ? 'selected' : '' ?>>CSS</option>
                            <option value="bash" <?= ($snippet['language'] ?? '') === 'bash' ? 'selected' : '' ?>>Bash</option>
                            <option value="json" <?= ($snippet['language'] ?? '') === 'json' ? 'selected' : '' ?>>JSON</option>
                            <option value="yaml" <?= ($snippet['language'] ?? '') === 'yaml' ? 'selected' : '' ?>>YAML</option>
                            <option value="markdown" <?= ($snippet['language'] ?? '') === 'markdown' ? 'selected' : '' ?>>Markdown</option>
                            <option value="plaintext" <?= ($snippet['language'] ?? '') === 'plaintext' ? 'selected' : '' ?>>Plain Text</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="tags">
                            <i class="fas fa-tags"></i> Tags
                        </label>
                        <input 
                            type="text" 
                            id="tags" 
                            name="tags" 
                            value="<?= View::e($snippet['tags'] ?? '') ?>"
                            class="form-control" 
                            placeholder="tag1, tag2, tag3"
                        >
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="code">
                        <i class="fas fa-file-code"></i> Code *
                    </label>
                    <textarea 
                        id="code" 
                        name="code" 
                        class="form-control code-editor" 
                        rows="15"
                        required
                        placeholder="Paste your code here..."
                    ><?= View::e($snippet['code']) ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input 
                            type="checkbox" 
                            name="is_public" 
                            value="1"
                            <?= !empty($snippet['is_public']) ? 'checked' : '' ?>
                        >
                        <span>
                            <i class="fas fa-globe"></i> Make this snippet public
                        </span>
                    </label>
                    <p class="form-help">Public snippets can be viewed by anyone</p>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Changes
                    </button>
                    <a href="/projects/codexpro/snippets/<?= $snippet['id'] ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('editSnippetForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    
    const formData = new FormData(this);
    
    // Convert FormData to URLSearchParams for proper POST encoding
    const params = new URLSearchParams();
    for (const [key, value] of formData.entries()) {
        params.append(key, value);
    }
    
    fetch('/projects/codexpro/snippets/<?= $snippet['id'] ?>', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: params.toString()
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (typeof showNotification === 'function') {
                showNotification('Snippet updated successfully!', 'success');
            }
            setTimeout(() => {
                window.location.href = '/projects/codexpro/snippets/<?= $snippet['id'] ?>';
            }, 1000);
        } else {
            if (typeof showNotification === 'function') {
                showNotification(data.error || 'Failed to update snippet', 'error');
            } else {
                alert(data.error || 'Failed to update snippet');
            }
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        if (typeof showNotification === 'function') {
            showNotification('An error occurred while saving', 'error');
        } else {
            alert('An error occurred while saving');
        }
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    });
});
</script>

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
        <span>${message}</span>
    `;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
</script>

<style>
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}

.code-editor {
    font-family: 'Fira Code', 'Monaco', 'Courier New', monospace;
    font-size: 0.875rem;
    line-height: 1.6;
    tab-size: 4;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.checkbox-label input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
}

.checkbox-label span {
    display: flex;
    align-items: center;
    gap: 6px;
    font-weight: 500;
}

.form-help {
    margin: 8px 0 0 0;
    font-size: 0.875rem;
    color: #94a3b8;
}

.form-actions {
    display: flex;
    gap: 12px;
    margin-top: 1.5rem;
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
