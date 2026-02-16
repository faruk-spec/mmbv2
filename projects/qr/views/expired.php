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
        
        <!-- Promotional Section -->
        <div class="promo-section">
            <div class="promo-header">
                <h3>Create Your Own QR Codes</h3>
                <p>Generate dynamic, trackable QR codes with advanced features</p>
            </div>
            
            <div class="promo-features">
                <div class="feature-item">
                    <i class="fas fa-lock"></i>
                    <span>Password Protection</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-clock"></i>
                    <span>Expiry Dates</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-chart-line"></i>
                    <span>Analytics & Tracking</span>
                </div>
                <div class="feature-item">
                    <i class="fas fa-palette"></i>
                    <span>Custom Designs</span>
                </div>
            </div>
            
            <a href="https://mmbtech.online/projects/qr/generate" class="btn-promo">
                <i class="fas fa-qrcode"></i> Start Creating Free
                <span class="btn-shine"></span>
            </a>
            
            <p class="promo-note">
                Powered by <strong>MMB Tech</strong> - Professional QR Code Solutions
            </p>
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

/* Promotional Section */
.promo-section {
    margin-top: 40px;
    padding: 30px;
    background: linear-gradient(135deg, rgba(153, 69, 255, 0.1), rgba(20, 241, 149, 0.1));
    border-radius: 16px;
    border: 1px solid rgba(153, 69, 255, 0.2);
}

.promo-header {
    margin-bottom: 25px;
}

.promo-header h3 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: 8px;
    background: linear-gradient(135deg, var(--purple), var(--cyan));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.promo-header p {
    color: var(--text-secondary);
    font-size: 14px;
}

.promo-features {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: 15px;
    margin: 25px 0;
}

.feature-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    padding: 15px;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 10px;
    transition: all 0.3s ease;
}

.feature-item:hover {
    background: rgba(255, 255, 255, 0.08);
    transform: translateY(-2px);
}

.feature-item i {
    font-size: 24px;
    background: linear-gradient(135deg, var(--purple), var(--cyan));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.feature-item span {
    font-size: 13px;
    color: var(--text-primary);
    font-weight: 500;
    text-align: center;
}

.btn-promo {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    padding: 14px 32px;
    background: linear-gradient(135deg, var(--purple), var(--cyan));
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    margin: 20px 0;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(153, 69, 255, 0.4);
}

.btn-promo:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(153, 69, 255, 0.5);
}

.btn-shine {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-promo:hover .btn-shine {
    left: 100%;
}

.promo-note {
    margin-top: 15px;
    font-size: 13px;
    color: var(--text-secondary);
}

.promo-note strong {
    background: linear-gradient(135deg, var(--purple), var(--cyan));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    font-weight: 700;
}
</style>
