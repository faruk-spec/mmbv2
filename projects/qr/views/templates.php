<?php
/**
 * Templates View
 */
?>

<div class="glass-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h3 class="section-title">
            <i class="fas fa-palette"></i> QR Templates
        </h3>
    </div>
    
    <p style="color: var(--text-secondary); margin-bottom: 30px;">
        Save your favorite QR code designs as templates and reuse them instantly.
        Templates save colors, styles, frames, logos, and all customization settings.
    </p>
    
    <?php if (empty($templates)): ?>
        <div class="empty-state" style="padding: 60px 20px;">
            <div class="empty-icon">
                <i class="fas fa-palette"></i>
            </div>
            <h2 style="font-size: 24px; margin-bottom: 15px; color: var(--text-primary);">No Templates Yet</h2>
            <p style="color: var(--text-secondary); margin-bottom: 30px;">
                Create your first template from the QR Generator page by clicking "Save as Template".
            </p>
            <a href="/projects/qr/generate" class="btn-primary">
                <i class="fas fa-plus"></i> Go to Generator
            </a>
        </div>
    <?php else: ?>
        <div class="templates-grid">
            <?php foreach ($templates as $template): ?>
                <div class="template-card glass-card">
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
                                <i class="fas fa-trash"></i>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="template-info-box" style="margin-top: 30px;">
        <h4 style="margin-bottom: 15px; color: var(--text-primary);">
            <i class="fas fa-info-circle"></i> How to Create Templates
        </h4>
        <ol style="color: var(--text-secondary); line-height: 1.8;">
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
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.template-card {
    padding: 0;
    overflow: hidden;
    display: flex;
    flex-direction: column;
}

.template-preview {
    background: linear-gradient(135deg, rgba(87, 96, 255, 0.1), rgba(26, 188, 254, 0.1));
    padding: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    min-height: 150px;
}

.qr-preview-placeholder {
    font-size: 60px;
    color: var(--purple);
    opacity: 0.5;
}

.template-info {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.template-info h4 {
    margin: 0;
    color: var(--text-primary);
    font-size: 16px;
}

.template-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
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
    gap: 10px;
    font-size: 12px;
}

.setting-item {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 8px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 8px;
    color: var(--text-secondary);
}

.color-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.template-date {
    color: var(--text-secondary);
    font-size: 12px;
}

.template-actions {
    padding: 15px 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    gap: 10px;
}

.template-actions .btn-sm {
    flex: 1;
}

.template-info-box {
    background: rgba(255, 255, 255, 0.03);
    border-radius: 12px;
    padding: 20px;
}

.template-info-box ol {
    margin: 0;
    padding-left: 20px;
}

.template-info-box a {
    color: var(--cyan);
    text-decoration: none;
}

.template-info-box a:hover {
    text-decoration: underline;
}
</style>

<script>
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
