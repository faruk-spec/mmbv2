<?php
/**
 * Templates View
 */
?>

<a href="/projects/qr" class="back-link">← Back to Dashboard</a>

<h1 style="margin-bottom: 30px; background: linear-gradient(135deg, var(--purple), var(--cyan)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
    <i class="fas fa-palette"></i> Templates
</h1>

<div class="glass-card">
    <div class="empty-state" style="padding: 60px 20px;">
        <div class="empty-icon">
            <i class="fas fa-palette"></i>
        </div>
        <h2 style="font-size: 24px; margin-bottom: 15px; color: var(--text-primary);">QR Code Templates</h2>
        <p style="color: var(--text-secondary); margin-bottom: 30px;">Save and reuse your favorite QR code designs.</p>
        
        <div class="feature-list" style="text-align: left; max-width: 500px; margin: 0 auto;">
            <div class="feature-item">
                <i class="fas fa-check-circle" style="color: var(--cyan);"></i>
                <span>Save design as template</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check-circle" style="color: var(--cyan);"></i>
                <span>Reuse templates for new QR codes</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check-circle" style="color: var(--cyan);"></i>
                <span>Share templates with team</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check-circle" style="color: var(--cyan);"></i>
                <span>Browse template gallery</span>
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
