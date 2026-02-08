<?php
/**
 * QR Code Expired View
 * Shown when QR code has expired
 */
?>

<div class="expired-container">
    <div class="glass-card expired-card">
        <div class="expired-header">
            <div class="expired-icon">
                <i class="fas fa-clock"></i>
            </div>
            <h2>QR Code Expired</h2>
            <p>This QR code has expired and is no longer accessible.</p>
        </div>
        
        <div class="expired-details">
            <div class="detail-row">
                <span class="detail-label">Type:</span>
                <span class="detail-value"><?= htmlspecialchars($qr['type'] ?? 'Unknown') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Expired on:</span>
                <span class="detail-value"><?= htmlspecialchars($qr['expires_at'] ?? 'N/A') ?></span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Created on:</span>
                <span class="detail-value"><?= htmlspecialchars($qr['created_at'] ?? 'N/A') ?></span>
            </div>
        </div>
        
        <div class="expired-footer">
            <p><i class="fas fa-info-circle"></i> If you believe this is an error, please contact the QR code owner.</p>
        </div>
    </div>
</div>

<style>
.expired-container {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.expired-card {
    max-width: 500px;
    width: 100%;
    text-align: center;
}

.expired-header {
    margin-bottom: 30px;
}

.expired-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    color: white;
    box-shadow: 0 8px 20px rgba(255, 107, 107, 0.4);
}

.expired-header h2 {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 10px;
    background: linear-gradient(135deg, #ff6b6b, #ee5a6f);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.expired-header p {
    color: var(--text-secondary);
    font-size: 15px;
}

.expired-details {
    margin: 30px 0;
    padding: 20px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 12px;
    text-align: left;
}

.detail-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
}

.detail-row:last-child {
    border-bottom: none;
}

.detail-label {
    color: var(--text-secondary);
    font-size: 14px;
}

.detail-value {
    color: var(--text-primary);
    font-weight: 600;
    font-size: 14px;
}

.expired-footer {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.expired-footer p {
    color: var(--text-secondary);
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.expired-footer i {
    color: #ff6b6b;
}
</style>
