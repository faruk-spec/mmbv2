<a href="/projects/qr/history" class="back-link">← Back to History</a>

<h1 style="margin-bottom: 30px;">Edit Dynamic QR Code</h1>

<?php if (!($qr['is_dynamic'] ?? false)): ?>
    <div class="card" style="background: rgba(255, 193, 7, 0.1); border: 1px solid #ffc107;">
        <p style="color: #ffc107; margin: 0;">
            ⚠️ This QR code is not dynamic and cannot be edited. Only dynamic QR codes can have their redirect URL changed.
        </p>
    </div>
<?php else: ?>

<div class="grid grid-2">
    <!-- Edit Form -->
    <div class="card">
        <h3 style="margin-bottom: 20px;">Update Redirect URL</h3>
        
        <form method="POST" action="/projects/qr/update/<?= $qr['id'] ?>" id="editForm">
            <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
            
            <div class="form-group">
                <label class="form-label">Redirect URL</label>
                <input type="url" 
                       name="redirect_url" 
                       id="redirectUrl" 
                       class="form-input" 
                       value="<?= htmlspecialchars($qr['redirect_url'] ?? '') ?>" 
                       placeholder="https://example.com"
                       required>
                <small style="color: var(--text-secondary);">
                    The QR code will redirect to this URL when scanned
                </small>
            </div>
            
            <div class="form-group">
                <label class="form-label">Short Code</label>
                <input type="text" 
                       class="form-input" 
                       value="<?= htmlspecialchars($qr['short_code'] ?? '') ?>" 
                       readonly
                       style="background: var(--bg-secondary); cursor: not-allowed;">
                <small style="color: var(--text-secondary);">
                    This is the short URL embedded in your QR code (cannot be changed)
                </small>
            </div>
            
            <div class="form-group">
                <label class="form-label" style="display: flex; align-items: center;">
                    <input type="checkbox" 
                           name="has_password" 
                           id="hasPassword" 
                           value="1" 
                           <?= !empty($qr['password_hash']) ? 'checked' : '' ?>
                           style="margin-right: 10px; width: 20px; height: 20px;">
                    <span>
                        <strong>Password Protection</strong>
                        <small style="display: block; color: var(--text-secondary);">Require password to scan</small>
                    </span>
                </label>
            </div>
            
            <div class="form-group" id="passwordGroup" style="display: <?= !empty($qr['password_hash']) ? 'block' : 'none' ?>;">
                <label class="form-label">New Password (leave empty to keep current)</label>
                <input type="password" name="password" id="qrPassword" class="form-input" placeholder="Enter new password">
            </div>
            
            <div class="form-group">
                <label class="form-label" style="display: flex; align-items: center;">
                    <input type="checkbox" 
                           name="has_expiry" 
                           id="hasExpiry" 
                           value="1" 
                           <?= !empty($qr['expires_at']) ? 'checked' : '' ?>
                           style="margin-right: 10px; width: 20px; height: 20px;">
                    <span>
                        <strong>Set Expiry Date</strong>
                        <small style="display: block; color: var(--text-secondary);">QR code will stop working after this date</small>
                    </span>
                </label>
            </div>
            
            <div class="form-group" id="expiryGroup" style="display: <?= !empty($qr['expires_at']) ? 'block' : 'none' ?>;">
                <label class="form-label">Expires On</label>
                <input type="datetime-local" 
                       name="expires_at" 
                       id="expiresAt" 
                       class="form-input"
                       value="<?= !empty($qr['expires_at']) ? date('Y-m-d\TH:i', strtotime($qr['expires_at'])) : '' ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="active" <?= ($qr['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= ($qr['status'] ?? 'active') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 30px;">
                <button type="submit" class="btn btn-primary" style="flex: 1;">
                    💾 Save Changes
                </button>
                <a href="/projects/qr/view/<?= $qr['id'] ?>" class="btn btn-secondary" style="text-decoration: none;">
                    ❌ Cancel
                </a>
            </div>
        </form>
    </div>
    
    <!-- QR Preview -->
    <div class="card" style="text-align: center;">
        <h3 style="margin-bottom: 20px;">QR Code Preview</h3>
        
        <div style="background: white; padding: 30px; border-radius: 12px; display: inline-block; margin-bottom: 20px;">
            <div id="qrcode"></div>
        </div>
        
        <div style="padding: 15px; background: rgba(0, 123, 255, 0.1); border-radius: 8px; text-align: left;">
            <div style="color: #007bff; font-size: 12px; margin-bottom: 5px; font-weight: 600;">ℹ️ Note</div>
            <div style="font-size: 14px; color: var(--text-secondary);">
                The QR code itself doesn't change. Only the redirect URL is updated. 
                Anyone scanning this code will be redirected to your new URL.
            </div>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: var(--bg-secondary); border-radius: 8px;">
            <div style="color: var(--text-secondary); font-size: 12px; margin-bottom: 5px;">Total Scans</div>
            <div style="font-weight: 600; font-size: 32px; color: var(--purple);"><?= (int)($qr['scan_count'] ?? 0) ?></div>
        </div>
    </div>
</div>

<?php endif; ?>

<!-- QR Code Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
// Generate QR code preview
(function() {
    if (typeof QRCode === 'undefined') {
        console.error('QRCode library not loaded');
        return;
    }
    
    const qrDiv = document.getElementById('qrcode');
    if (!qrDiv) return;
    
    try {
        new QRCode(qrDiv, {
            text: <?= json_encode($qr['content']) ?>,
            width: 250,
            height: 250,
            colorDark: <?= json_encode($qr['foreground_color'] ?? '#000000') ?>,
            colorLight: <?= json_encode($qr['background_color'] ?? '#ffffff') ?>,
            correctLevel: QRCode.CorrectLevel.<?= $qr['error_correction'] ?? 'H' ?>
        });
    } catch (error) {
        console.error('Error generating QR:', error);
    }
})();

// Toggle password field
document.getElementById('hasPassword')?.addEventListener('change', function() {
    document.getElementById('passwordGroup').style.display = this.checked ? 'block' : 'none';
});

// Toggle expiry field
document.getElementById('hasExpiry')?.addEventListener('change', function() {
    document.getElementById('expiryGroup').style.display = this.checked ? 'block' : 'none';
});
</script>
