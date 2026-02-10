<?php
/**
 * Templates View
 */
?>

<div class="glass-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl); flex-wrap: wrap; gap: var(--space-md);">
        <h3 class="section-title" style="margin-bottom: 0;">
            <i class="fas fa-palette"></i> QR Templates
        </h3>
    </div>
    
    <p style="color: var(--text-secondary); margin-bottom: var(--space-xl); font-size: var(--font-sm);">
        Save your favorite QR code designs as templates and reuse them instantly.
        Templates save colors, styles, frames, logos, and all customization settings.
    </p>
    
    <!-- Search and Filter Section -->
    <?php if (!empty($templates)): ?>
    <div style="display: flex; gap: var(--space-md); margin-bottom: var(--space-xl); flex-wrap: wrap;">
        <div style="flex: 1; min-width: 15rem;">
            <input type="text" 
                   id="searchTemplates" 
                   class="form-control" 
                   placeholder="Search templates..." 
                   onkeyup="filterTemplates()"
                   style="padding: 0.625rem 1rem; font-size: var(--font-sm);">
        </div>
        <select id="filterVisibility" 
                class="form-select" 
                onchange="filterTemplates()" 
                style="width: auto; min-width: 10rem; padding: 0.625rem 1rem; font-size: var(--font-sm);">
            <option value="">All Templates</option>
            <option value="private">Private Only</option>
            <option value="public">Public Only</option>
        </select>
        <select id="sortTemplates" 
                class="form-select" 
                onchange="sortTemplates()" 
                style="width: auto; min-width: 10rem; padding: 0.625rem 1rem; font-size: var(--font-sm);">
            <option value="recent">Most Recent</option>
            <option value="name">Name (A-Z)</option>
            <option value="oldest">Oldest First</option>
        </select>
    </div>
    <?php endif; ?>
    
    <?php if (empty($templates)): ?>
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-palette"></i>
            </div>
            <h2 style="font-size: var(--font-2xl); margin-bottom: var(--space-md); color: var(--text-primary);">No Templates Yet</h2>
            <p style="color: var(--text-secondary); margin-bottom: var(--space-xl); font-size: var(--font-sm);">
                Create your first template from the QR Generator page by clicking "Save as Template".
            </p>
            <a href="/projects/qr/generate" class="btn-primary">
                <i class="fas fa-plus"></i> Go to Generator
            </a>
        </div>
    <?php else: ?>
        <div class="templates-grid" id="templatesGrid">
            <?php foreach ($templates as $template): ?>
                <div class="template-card glass-card"
                     data-name="<?= strtolower(htmlspecialchars($template['name'])) ?>"
                     data-visibility="<?= $template['is_public'] ? 'public' : 'private' ?>"
                     data-date="<?= strtotime($template['created_at']) ?>">
                    <div class="template-preview">
                        <div class="qr-preview-placeholder">
                            <i class="fas fa-qrcode"></i>
                        </div>
                    </div>
                    
                    <div class="template-info">
                        <h4><?= htmlspecialchars($template['name']) ?></h4>
                        
                        <?php if ($template['is_public']): ?>
                            <span class="template-badge public">
                                <i class="fas fa-globe"></i> Public
                            </span>
                        <?php else: ?>
                            <span class="template-badge private">
                                <i class="fas fa-lock"></i> Private
                            </span>
                        <?php endif; ?>
                        
                        <div class="template-settings">
                            <?php 
                            $settings = $template['settings'];
                            if (!empty($settings['foreground_color'])): 
                            ?>
                                <span class="setting-item">
                                    <span class="color-dot" style="background: <?= $settings['foreground_color'] ?>"></span>
                                    Foreground
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($settings['background_color'])): ?>
                                <span class="setting-item">
                                    <span class="color-dot" style="background: <?= $settings['background_color'] ?>"></span>
                                    Background
                                </span>
                            <?php endif; ?>
                            
                            <?php if (!empty($settings['frame_style'])): ?>
                                <span class="setting-item">
                                    <i class="fas fa-border-style"></i>
                                    <?= ucfirst($settings['frame_style']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="template-date">
                            <i class="fas fa-clock"></i>
                            <?= date('M j, Y', strtotime($template['created_at'])) ?>
                        </div>
                    </div>
                    
                    <div class="template-actions">
                        <button class="btn-primary btn-sm" onclick="applyTemplate(<?= $template['id'] ?>)">
                            <i class="fas fa-check"></i> Use Template
                        </button>
                        <?php if ($template['user_id'] == $user['id']): ?>
                            <button class="btn-danger btn-sm" onclick="deleteTemplate(<?= $template['id'] ?>)">
                                <i class="fas fa-trash"></i> Delete
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div id="noTemplatesResult" style="display: none; text-align: center; padding: var(--space-2xl); color: var(--text-secondary);">
            <i class="fas fa-search" style="font-size: 3rem; opacity: 0.5; margin-bottom: var(--space-md);"></i>
            <p>No templates found matching your criteria.</p>
        </div>
    <?php endif; ?>
    
    <div class="template-info-box" style="margin-top: var(--space-xl);">
        <h4 style="margin-bottom: var(--space-md); color: var(--text-primary); font-size: var(--font-lg);">
            <i class="fas fa-info-circle"></i> How to Create Templates
        </h4>
        <ol style="color: var(--text-secondary); line-height: 1.8; font-size: var(--font-sm);">
            <li>Go to the <a href="/projects/qr/generate">QR Generator</a> page</li>
            <li>Customize your QR code with colors, styles, frame, logo, etc.</li>
            <li>Look for the "Save as Template" button (coming soon to generator page)</li>
            <li>Your template will appear here and can be reused for new QR codes</li>
        </ol>
    </div>
</div>

<style>
.templates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(17.5rem, 1fr)); /* 280px to rem */
    gap: var(--space-lg);
}

.template-card {
    padding: 0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.template-card:hover {
    transform: translateY(-0.125rem);
}

.template-preview {
    background: linear-gradient(135deg, rgba(87, 96, 255, 0.1), rgba(26, 188, 254, 0.1));
    padding: var(--space-xl);
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 9.375rem; /* 150px to rem */
}

.qr-preview-placeholder {
    font-size: 3.75rem; /* 60px to rem */
    color: var(--purple);
    opacity: 0.5;
}

.template-info {
    padding: var(--space-lg);
    display: flex;
    flex-direction: column;
    gap: var(--space-sm);
}

.template-info h4 {
    margin: 0;
    color: var(--text-primary);
    font-size: var(--font-md);
    font-weight: 600;
}

.template-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.3125rem; /* 5px to rem */
    padding: 0.25rem 0.625rem; /* 4px 10px to rem */
    border-radius: 0.75rem; /* 12px to rem */
    font-size: 0.6875rem; /* 11px to rem */
    font-weight: 600;
    width: fit-content;
}

.template-badge.public {
    background: rgba(46, 213, 115, 0.2);
    color: #2ed573;
}

.template-badge.private {
    background: rgba(136, 136, 136, 0.2);
    color: #888;
}

.template-settings {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-sm);
    font-size: var(--font-xs);
}

.setting-item {
    display: inline-flex;
    align-items: center;
    gap: 0.3125rem;
    padding: 0.25rem 0.5rem; /* 4px 8px to rem */
    background: rgba(255, 255, 255, 0.05);
    border-radius: 0.5rem; /* 8px to rem */
    color: var(--text-secondary);
}

.color-dot {
    width: 0.75rem; /* 12px to rem */
    height: 0.75rem;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.template-date {
    color: var(--text-secondary);
    font-size: var(--font-xs);
}

.template-actions {
    padding: var(--space-md) var(--space-lg);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    gap: var(--space-sm);
}

.template-actions .btn-sm {
    flex: 1;
}

.template-info-box {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 0.75rem; /* 12px to rem */
    padding: var(--space-lg);
}

.template-info-box ol {
    margin: 0;
    padding-left: var(--space-lg);
}

.template-info-box a {
    color: var(--cyan);
    text-decoration: none;
}

.template-info-box a:hover {
    text-decoration: underline;
}

/* Responsive Styles */
@media (max-width: 48rem) { /* 768px to rem */
    .templates-grid {
        grid-template-columns: repeat(auto-fill, minmax(15.625rem, 1fr)); /* 250px to rem */
    }
    
    .template-preview {
        min-height: 9.375rem; /* 150px to rem */
    }
    
    .template-info {
        padding: var(--space-md);
    }
    
    .template-actions {
        padding: var(--space-sm) var(--space-md);
        flex-direction: column;
    }
}
    
    .template-settings {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media (max-width: 480px) {
    .templates-grid {
        grid-template-columns: 1fr;
    }
    
    .template-card {
        padding: 15px;
    }
    
    .template-actions {
        flex-direction: column;
    }
    
    .template-actions .btn-sm {
        width: 100%;
    }
}
</style>

<script>
function filterTemplates() {
    const searchTerm = document.getElementById('searchTemplates')?.value.toLowerCase() || '';
    const visibilityFilter = document.getElementById('filterVisibility')?.value.toLowerCase() || '';
    const cards = document.querySelectorAll('.template-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const name = card.getAttribute('data-name') || '';
        const visibility = card.getAttribute('data-visibility') || '';
        
        const matchesSearch = name.includes(searchTerm);
        const matchesVisibility = !visibilityFilter || visibility === visibilityFilter;
        
        if (matchesSearch && matchesVisibility) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Show/hide no results message
    const noResults = document.getElementById('noTemplatesResult');
    const grid = document.getElementById('templatesGrid');
    if (noResults && grid) {
        if (visibleCount === 0) {
            grid.style.display = 'none';
            noResults.style.display = 'block';
        } else {
            grid.style.display = 'grid';
            noResults.style.display = 'none';
        }
    }
}

function sortTemplates() {
    const sortBy = document.getElementById('sortTemplates')?.value || 'recent';
    const grid = document.getElementById('templatesGrid');
    if (!grid) return;
    
    const cards = Array.from(grid.querySelectorAll('.template-card'));
    
    cards.sort((a, b) => {
        switch(sortBy) {
            case 'name':
                return (a.getAttribute('data-name') || '').localeCompare(b.getAttribute('data-name') || '');
            case 'oldest':
                return parseInt(a.getAttribute('data-date') || '0') - parseInt(b.getAttribute('data-date') || '0');
            case 'recent':
            default:
                return parseInt(b.getAttribute('data-date') || '0') - parseInt(a.getAttribute('data-date') || '0');
        }
    });
    
    // Re-append sorted cards
    cards.forEach(card => grid.appendChild(card));
}

function applyTemplate(templateId) {
    // Store template ID in localStorage and redirect to generator
    localStorage.setItem('applyTemplateId', templateId);
    window.location.href = '/projects/qr/generate';
}

function deleteTemplate(templateId) {
    if (!confirm('Are you sure you want to delete this template?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('id', templateId);
    
    fetch('/projects/qr/templates/delete', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to delete template');
        }
    })
    .catch(error => {
        alert('Error deleting template');
        console.error(error);
    });
}

// Check if we need to apply a template (coming from generator)
window.addEventListener('load', function() {
    const templateId = localStorage.getItem('applyTemplateId');
    if (templateId) {
        localStorage.removeItem('applyTemplateId');
        // Template would be applied in the generator page
    }
});
</script>
