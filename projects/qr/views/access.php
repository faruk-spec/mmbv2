<?php
/**
 * QR Code Access Form
 * Password protection page for QR codes
 */
?>

<div class="access-container">
    <div class="glass-card access-card">
        <div class="access-header">
            <div class="lock-icon">
                <i class="fas fa-lock"></i>
            </div>
            <h2>Protected QR Code</h2>
            <p>This QR code is password protected. Please enter the password to continue.</p>
        </div>
        
        <form method="POST" action="/projects/qr/access/<?= htmlspecialchars($code) ?>" class="access-form">
            <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
            
            <?php if (!empty($error)): ?>
                <div class="alert alert-error" style="margin-bottom:16px;">
                    <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-key"></i> Password
                </label>
                <input type="password" name="password" class="form-input" placeholder="Enter password" required autofocus>
            </div>
            
            <button type="submit" class="btn btn-primary btn-large">
                <i class="fas fa-unlock"></i> Unlock & Access
                <span class="btn-shine"></span>
            </button>
        </form>
        
        <div class="access-footer">
            <p><i class="fas fa-shield-alt"></i> Your password is encrypted and secure</p>
        </div>
    </div>
</div>

<style>
.alert {
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.alert-error {
    background: rgba(255, 71, 87, 0.1);
    color: #ff4757;
    border: 1px solid rgba(255, 71, 87, 0.3);
}
.access-container {
    min-height: 80vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
}

.access-card {
    max-width: 500px;
    width: 100%;
    text-align: center;
}

.access-header {
    margin-bottom: 30px;
}

.lock-icon {
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

.access-header h2 {
    font-size: 28px;
    font-weight: 700;
    margin-bottom: 10px;
    background: linear-gradient(135deg, var(--purple), var(--cyan));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.access-header p {
    color: var(--text-secondary);
    font-size: 15px;
}

.access-form {
    margin: 30px 0;
}

.access-footer {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.access-footer p {
    color: var(--text-secondary);
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.access-footer i {
    color: var(--cyan);
}
</style>
