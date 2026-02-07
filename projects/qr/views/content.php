<?php
/**
 * QR Code Content Display
 * Shows QR code content for non-URL types
 */
?>

<div class="content-container">
    <div class="glass-card content-card">
        <div class="content-header">
            <div class="content-icon">
                <i class="fas fa-qrcode"></i>
            </div>
            <h2>QR Code Content</h2>
            <p>Scanned successfully!</p>
        </div>
        
        <div class="content-body">
            <div class="content-type">
                <span class="type-badge"><?= htmlspecialchars($qr['type'] ?? 'text') ?></span>
            </div>
            
            <div class="content-display">
                <?php if ($qr['type'] === 'text' || $qr['type'] === 'url'): ?>
                    <pre><?= htmlspecialchars($content) ?></pre>
                <?php elseif ($qr['type'] === 'email'): ?>
                    <div class="email-content">
                        <i class="fas fa-envelope"></i>
                        <a href="<?= htmlspecialchars($content) ?>"><?= htmlspecialchars(str_replace('mailto:', '', $content)) ?></a>
                    </div>
                <?php elseif ($qr['type'] === 'phone'): ?>
                    <div class="phone-content">
                        <i class="fas fa-phone"></i>
                        <a href="<?= htmlspecialchars($content) ?>"><?= htmlspecialchars(str_replace('tel:', '', $content)) ?></a>
                    </div>
                <?php elseif ($qr['type'] === 'vcard'): ?>
                    <div class="vcard-content">
                        <i class="fas fa-address-card"></i>
                        <pre><?= htmlspecialchars($content) ?></pre>
                    </div>
                <?php elseif ($qr['type'] === 'wifi'): ?>
                    <div class="wifi-content">
                        <i class="fas fa-wifi"></i>
                        <pre><?= htmlspecialchars($content) ?></pre>
                    </div>
                <?php else: ?>
                    <pre><?= htmlspecialchars($content) ?></pre>
                <?php endif; ?>
            </div>
            
            <div class="content-actions">
                <button onclick="copyContent()" class="btn btn-secondary">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
        </div>
        
        <div class="content-footer">
            <p><i class="fas fa-check-circle"></i> Scanned at <?= date('Y-m-d H:i:s') ?></p>
        </div>
    </div>
</div>

<script>
function copyContent() {
    const content = <?= json_encode($content) ?>;
    navigator.clipboard.writeText(content).then(() => {
        alert('Content copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy:', err);
    });
}
</script>

<style>
.content-container {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.content-card {
    max-width: 600px;
    width: 100%;
    text-align: center;
}

.content-header {
    margin-bottom: 30px;
}

.content-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto 20px;
    background: linear-gradient(135deg, var(--purple), var(--cyan));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    color: white;
    box-shadow: 0 8px 20px rgba(153, 69, 255, 0.4);
}

.content-header h2 {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 10px;
    background: linear-gradient(135deg, var(--purple), var(--cyan));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.content-header p {
    color: var(--text-secondary);
    font-size: 15px;
}

.content-body {
    margin: 30px 0;
}

.content-type {
    margin-bottom: 20px;
}

.type-badge {
    display: inline-block;
    padding: 6px 16px;
    background: linear-gradient(135deg, var(--purple), var(--cyan));
    color: white;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.content-display {
    padding: 20px;
    background: rgba(255, 255, 255, 0.03);
    border-radius: 12px;
    margin-bottom: 20px;
    text-align: left;
}

.content-display pre {
    color: var(--text-primary);
    font-family: 'Courier New', monospace;
    font-size: 14px;
    white-space: pre-wrap;
    word-wrap: break-word;
    margin: 0;
}

.email-content,
.phone-content,
.vcard-content,
.wifi-content {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 16px;
}

.email-content i,
.phone-content i,
.vcard-content i,
.wifi-content i {
    color: var(--cyan);
    font-size: 24px;
}

.email-content a,
.phone-content a {
    color: var(--purple);
    text-decoration: none;
    font-weight: 600;
}

.email-content a:hover,
.phone-content a:hover {
    text-decoration: underline;
}

.content-actions {
    display: flex;
    justify-content: center;
    gap: 10px;
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.15);
}

.content-footer {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.content-footer p {
    color: var(--text-secondary);
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.content-footer i {
    color: #4CAF50;
}
</style>
