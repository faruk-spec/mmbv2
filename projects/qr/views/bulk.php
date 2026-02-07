<?php
/**
 * Bulk Generate View
 */
?>

<div class="glass-card">
    <div class="empty-state" style="padding: 60px 20px;">
        <div class="empty-icon">
            <i class="fas fa-layer-group"></i>
        </div>
        <h2 style="font-size: 24px; margin-bottom: 15px; color: var(--text-primary);">Bulk QR Code Generation</h2>
        <p style="color: var(--text-secondary); margin-bottom: 30px;">Generate hundreds of QR codes at once from CSV or Excel files.</p>
        
        <div class="feature-list" style="text-align: left; max-width: 500px; margin: 0 auto;">
            <div class="feature-item">
                <i class="fas fa-check-circle" style="color: var(--cyan);"></i>
                <span>Upload CSV/Excel files</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check-circle" style="color: var(--cyan);"></i>
                <span>Generate multiple QR codes at once</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check-circle" style="color: var(--cyan);"></i>
                <span>Apply templates to bulk generation</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check-circle" style="color: var(--cyan);"></i>
                <span>Download as ZIP archive</span>
            </div>
        </div>
        
        <div style="margin-top: 40px;">
            <p style="color: var(--text-secondary); font-size: 14px;">
                <i class="fas fa-info-circle"></i> Feature coming soon...
            </p>
        </div>
    </div>
</div>

<style>
.feature-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 8px;
    color: var(--text-primary);
}

.feature-item i {
    font-size: 20px;
}
</style>
