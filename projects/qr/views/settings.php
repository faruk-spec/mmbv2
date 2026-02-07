<?php
/**
 * Settings View
 */
?>

<div class="glass-card">
    <h3 class="section-title">
        <i class="fas fa-sliders-h"></i> Default Settings
    </h3>
    
    <div style="padding: 20px;">
        <div class="feature-list" style="text-align: left; max-width: 600px;">
            <div class="feature-item">
                <i class="fas fa-check-circle" style="color: var(--cyan);"></i>
                <span>Default QR code size and error correction</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check-circle" style="color: var(--cyan);"></i>
                <span>Default colors and styles</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check-circle" style="color: var(--cyan);"></i>
                <span>Auto-save generated QR codes</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check-circle" style="color: var(--cyan);"></i>
                <span>Download format preferences (PNG/SVG/PDF)</span>
            </div>
            <div class="feature-item">
                <i class="fas fa-check-circle" style="color: var(--cyan);"></i>
                <span>Email notifications for scans</span>
            </div>
        </div>
        
        <div style="margin-top: 40px; text-align: center;">
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
