<?php
/**
 * CodeXPro - Templates View
 */
use Core\View;
use Core\Auth;

$user = Auth::user();
$pageTitle = 'Templates';
$currentPage = 'templates';

ob_start();
?>

<div class="page-header">
    <h1><i class="fas fa-file-code"></i> Code Templates</h1>
</div>

<div class="content-grid">
    <?php if (empty($templates)): ?>
        <div class="empty-state">
            <i class="fas fa-file-code" style="font-size: 4rem; color: #4a5568; margin-bottom: 1rem;"></i>
            <h3>No Templates Available</h3>
            <p>Templates will be added soon to help you get started faster.</p>
        </div>
    <?php else: ?>
        <?php
        $categories = [];
        foreach ($templates as $template) {
            $cat = $template['category'] ?? 'Other';
            if (!isset($categories[$cat])) {
                $categories[$cat] = [];
            }
            $categories[$cat][] = $template;
        }
        ?>
        
        <?php foreach ($categories as $category => $catTemplates): ?>
            <div class="category-section">
                <h2 class="category-title"><?= View::e($category) ?></h2>
                <div class="templates-grid">
                    <?php foreach ($catTemplates as $template): ?>
                        <div class="card template-card">
                            <div class="card-header">
                                <h3><?= View::e($template['name']) ?></h3>
                                <?php if (!empty($template['language'])): ?>
                                    <span class="badge badge-<?= View::e($template['language']) ?>">
                                        <?= View::e(strtoupper($template['language'])) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if (!empty($template['description'])): ?>
                                <div class="card-body">
                                    <p><?= View::e($template['description']) ?></p>
                                </div>
                            <?php endif; ?>
                            <div class="card-footer">
                                <?php if (!empty($template['is_starter'])): ?>
                                    <button onclick="useStarterTemplate('<?= View::e($template['id']) ?>')" class="btn btn-primary btn-block">
                                        <i class="fas fa-check"></i> Use Template
                                    </button>
                                <?php else: ?>
                                    <button onclick="useTemplate(<?= (int)$template['id'] ?>)" class="btn btn-primary btn-block">
                                        <i class="fas fa-check"></i> Use Template
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
function useTemplate(templateId) {
    fetch('/projects/codexpro/templates/' + templateId)
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                // Store template data and redirect to editor
                sessionStorage.setItem('template_data', JSON.stringify(data.template));
                window.location.href = '/projects/codexpro/editor/new';
            } else {
                alert('Error loading template: ' + (data.error || 'Unknown error'));
            }
        });
}

function useStarterTemplate(templateKey) {
    fetch('/projects/codexpro/api/create-from-template', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({ template: templateKey })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.href = '/projects/codexpro/editor/' + data.project_id;
        } else {
            alert('Error creating project from template: ' + (data.error || 'Unknown error'));
        }
    })
    .catch(err => {
        alert('Error: ' + err.message);
    });
}
</script>

<style>
.category-section {
    margin-bottom: 3rem;
}

.category-title {
    color: #06b6d4;
    font-size: 1.5rem;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid rgba(6, 182, 212, 0.3);
}

.templates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1.5rem;
}

.template-card {
    transition: all 0.3s;
}

.template-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(6, 182, 212, 0.3);
}

.badge-html { background: #e34c26; }
.badge-css { background: #264de4; }
.badge-javascript { background: #f7df1e; color: #000; }
.badge-python { background: #3776ab; }
.badge-php { background: #777bb4; }
.badge-java { background: #007396; }
</style>

<?php
$content = ob_get_clean();
require __DIR__ . '/layout.php';
?>
