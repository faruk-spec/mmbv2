<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
.pay-card { background:var(--bg-card); border:1px solid var(--border-color); border-radius:12px; padding:28px 32px; margin-bottom:24px; }
.pay-card h3 { color:var(--cyan); font-size:.9rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; margin:0 0 20px; padding-bottom:12px; border-bottom:1px solid var(--border-color); }
.form-group { display:flex; flex-direction:column; gap:6px; margin-bottom:16px; }
.form-group label { font-size:.875rem; font-weight:600; }
.form-group small { color:var(--text-secondary); font-size:.78rem; }
.form-group input[type="text"], .form-group select {
    background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:8px;
    color:var(--text-primary); padding:9px 12px; font-size:.9rem; width:100%; max-width:500px; box-sizing:border-box;
}
.form-group input:focus, .form-group select:focus { outline:none; border-color:var(--cyan); }
.form-group input[type="file"] { max-width:500px; }
.toggle-row { display:flex; align-items:center; gap:12px; }
.toggle-switch { position:relative; width:48px; height:26px; flex-shrink:0; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-switch .slider { position:absolute; inset:0; background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:26px; cursor:pointer; transition:.25s; }
.toggle-switch .slider::before { content:''; position:absolute; width:18px; height:18px; left:3px; top:3px; background:var(--text-secondary); border-radius:50%; transition:.25s; }
.toggle-switch input:checked + .slider { background:var(--cyan); border-color:var(--cyan); }
.toggle-switch input:checked + .slider::before { transform:translateX(22px); background:#fff; }
.alert-success { background:rgba(0,255,136,.1); color:var(--green); border:1px solid var(--green); padding:12px 16px; border-radius:8px; margin-bottom:20px; }
.alert-danger  { background:rgba(255,107,107,.1); color:var(--red); border:1px solid var(--red); padding:12px 16px; border-radius:8px; margin-bottom:20px; }
.gateway-logo-preview-wrap { display:flex; align-items:center; gap:10px; }
.gateway-logo-preview { width:62px; height:62px; border-radius:10px; border:1px solid var(--border-color); background:#fff; object-fit:contain; padding:6px; }
.gateway-remove { display:inline-flex; align-items:center; gap:8px; font-size:.84rem; color:var(--text-secondary); }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1><i class="fas fa-credit-card" style="color:var(--cyan);"></i> Payment Settings</h1>
        <p style="color:var(--text-secondary);">Configure payment gateways for plan subscriptions</p>
    </div>
</div>

<?php if (!empty($success)): ?>
<div class="alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
<div class="alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" action="/admin/payment-settings" enctype="multipart/form-data">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf) ?>">

    <!-- General -->
    <div class="pay-card">
        <h3><i class="fas fa-sliders-h"></i> General</h3>
        <div class="form-group">
            <label>Default Payment Method</label>
            <select name="payment_method">
                <option value="request" <?= ($settings['payment_method']??'request')==='request'?'selected':'' ?>>Request Admin (Manual)</option>
                <option value="upi"     <?= ($settings['payment_method']??'')==='upi'    ?'selected':'' ?>>UPI / PhonePe QR</option>
                <option value="cashfree"<?= ($settings['payment_method']??'')==='cashfree'?'selected':'' ?>>Cashfree</option>
            </select>
            <small>This determines what payment option is shown to users when subscribing to a paid plan.</small>
        </div>
        <div class="form-group">
            <label>Default Currency</label>
            <select name="payment_currency">
                <?php foreach (['INR','USD','EUR','GBP','AED','SAR','BDT','PKR','NGN','BRL','MXN','CAD','AUD','JPY'] as $c): ?>
                <option value="<?= $c ?>" <?= ($settings['payment_currency']??'INR')===$c?'selected':'' ?>><?= $c ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Manual Review Option</label>
            <div class="toggle-row">
                <label class="toggle-switch">
                    <input type="checkbox" name="payment_manual_review_enabled" value="1"
                           <?= ($settings['payment_manual_review_enabled']??'1')==='1'?'checked':'' ?>>
                    <span class="slider"></span>
                </label>
                <span style="font-size:.9rem;">Allow users to select "Manual Review" as a payment method</span>
            </div>
            <small>When disabled, the Manual Review option is hidden from the payment method selector. Users will only see the enabled payment gateways (UPI / Cashfree).</small>
        </div>
        <div class="form-group">
            <label>Mobile Verification Required</label>
            <div class="toggle-row">
                <label class="toggle-switch">
                    <input type="checkbox" name="require_mobile_verification" value="1"
                           <?= ($settings['require_mobile_verification']??'0')==='1'?'checked':'' ?>>
                    <span class="slider"></span>
                </label>
                <span style="font-size:.9rem;">Require users to verify their phone number before subscribing</span>
            </div>
            <small>When enabled, users must verify their phone via OTP before accessing any subscription plan.</small>
        </div>
        <div class="form-group">
            <label for="payment_default_refund_days">Default Refund Window (days)</label>
            <input type="number" id="payment_default_refund_days" name="payment_default_refund_days"
                   value="<?= (int) ($settings['payment_default_refund_days'] ?? 7) ?>"
                   min="0" max="365" style="max-width:120px;">
            <small>Number of days from payment date within which users can request a refund. When a refund is requested, the subscription remains active while the request is pending admin review. If admin approves, the subscription is cancelled and refund processed. If admin rejects with "keep plan active", the user retains access. Set to 0 to disable refunds by default.</small>
        </div>
        <div class="form-group">
            <label for="payment_default_cancel_days">Default Cancellation Window (days)</label>
            <input type="number" id="payment_default_cancel_days" name="payment_default_cancel_days"
                   value="<?= (int) ($settings['payment_default_cancel_days'] ?? 0) ?>"
                   min="0" max="365" style="max-width:120px;">
            <small>Number of days from payment date within which users can cancel their subscription. Set to 0 for no time restriction (cancel any time).</small>
        </div>
    </div>

    <!-- UPI / PhonePe -->
    <div class="pay-card">
        <h3><i class="fas fa-qrcode"></i> UPI / PhonePe Manual QR</h3>
        <p style="color:var(--text-secondary);font-size:.85rem;margin-bottom:16px;">Users will see a QR code with your UPI ID pre-filled for the exact plan amount. They scan, pay, and click confirm &mdash; you activate manually.</p>
        <div class="form-group">
            <label for="payment_upi_id">UPI ID (e.g. yourname@phonepe or yourname@ybl)</label>
            <input type="text" id="payment_upi_id" name="payment_upi_id"
                   value="<?= htmlspecialchars($settings['payment_upi_id']??'') ?>"
                   placeholder="yourname@phonepe" style="max-width:340px;">
            <small>This UPI ID is used to generate the payment QR code for each plan amount.</small>
        </div>
        <div class="form-group">
            <label for="payment_upi_logo">Gateway Logo URL (optional)</label>
            <input type="text" id="payment_upi_logo" name="payment_upi_logo"
                   value="<?= htmlspecialchars($settings['payment_upi_logo'] ?? '') ?>"
                   placeholder="/uploads/payment-gateways/upi-logo.png or https://...">
            <small>Optional custom logo shown in plan checkout for UPI payment method.</small>
        </div>
        <div class="form-group">
            <label for="payment_upi_logo_file">Upload UPI Logo (optional)</label>
            <input type="file" id="payment_upi_logo_file" name="payment_upi_logo_file" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">
            <?php if (!empty($settings['payment_upi_logo'])): ?>
            <div class="gateway-logo-preview-wrap">
                <img src="<?= htmlspecialchars($settings['payment_upi_logo']) ?>" alt="UPI logo" class="gateway-logo-preview">
                <label class="gateway-remove"><input type="checkbox" name="remove_payment_upi_logo" value="1"> Remove current logo</label>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Cashfree -->
    <div class="pay-card">
        <h3><i class="fas fa-university"></i> Cashfree Payment Gateway</h3>
        <p style="color:var(--text-secondary);font-size:.85rem;margin-bottom:16px;">Integrate Cashfree for automatic payment processing. Get your API credentials from the <a href="https://merchant.cashfree.com" target="_blank" rel="noopener" style="color:var(--cyan);">Cashfree Merchant Dashboard</a>.</p>
        <div class="form-group">
            <label>Enable Cashfree</label>
            <div class="toggle-row">
                <label class="toggle-switch">
                    <input type="checkbox" name="payment_cashfree_enabled" value="1"
                           <?= ($settings['payment_cashfree_enabled']??'0')==='1'?'checked':'' ?>>
                    <span class="slider"></span>
                </label>
                <span style="font-size:.9rem;">Enable Cashfree payment gateway</span>
            </div>
        </div>
        <div class="form-group">
            <label for="payment_cashfree_app_id">App ID</label>
            <input type="text" id="payment_cashfree_app_id" name="payment_cashfree_app_id"
                   value="<?= htmlspecialchars($settings['payment_cashfree_app_id']??'') ?>"
                   placeholder="Your Cashfree App ID">
        </div>
        <div class="form-group">
            <label for="payment_cashfree_secret">Secret Key</label>
            <?php $secretPlaceholder = !empty($settings['payment_cashfree_secret_set']) ? 'Leave blank to keep existing secret' : 'Your Cashfree Secret Key'; ?>
            <input type="text" id="payment_cashfree_secret" name="payment_cashfree_secret"
                   value=""
                   placeholder="<?= htmlspecialchars($secretPlaceholder) ?>">
            <small>Secrets are stored encrypted. Leave blank to keep the current secret unchanged.</small>
        </div>
        <div class="form-group">
            <label>Sandbox / Test Mode</label>
            <div class="toggle-row">
                <label class="toggle-switch">
                    <input type="checkbox" name="payment_cashfree_sandbox" value="1"
                           <?= ($settings['payment_cashfree_sandbox']??'1')==='1'?'checked':'' ?>>
                    <span class="slider"></span>
                </label>
                <span style="font-size:.9rem;">Use sandbox (test) environment</span>
            </div>
            <small>Disable sandbox when you are ready for live payments.</small>
        </div>
        <div class="form-group">
            <label for="payment_cashfree_logo">Gateway Logo URL (optional)</label>
            <input type="text" id="payment_cashfree_logo" name="payment_cashfree_logo"
                   value="<?= htmlspecialchars($settings['payment_cashfree_logo'] ?? '') ?>"
                   placeholder="/uploads/payment-gateways/cashfree-logo.png or https://...">
            <small>Optional custom logo shown in plan checkout for Cashfree.</small>
        </div>
        <div class="form-group">
            <label for="payment_cashfree_logo_file">Upload Cashfree Logo (optional)</label>
            <input type="file" id="payment_cashfree_logo_file" name="payment_cashfree_logo_file" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">
            <?php if (!empty($settings['payment_cashfree_logo'])): ?>
            <div class="gateway-logo-preview-wrap">
                <img src="<?= htmlspecialchars($settings['payment_cashfree_logo']) ?>" alt="Cashfree logo" class="gateway-logo-preview">
                <label class="gateway-remove"><input type="checkbox" name="remove_payment_cashfree_logo" value="1"> Remove current logo</label>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="pay-card">
        <h3><i class="fas fa-clipboard-check"></i> Manual Review</h3>
        <p style="color:var(--text-secondary);font-size:.85rem;margin-bottom:16px;">Optional branding for the Manual Review payment method tile.</p>
        <div class="form-group">
            <label for="payment_manual_review_logo">Gateway Logo URL (optional)</label>
            <input type="text" id="payment_manual_review_logo" name="payment_manual_review_logo"
                   value="<?= htmlspecialchars($settings['payment_manual_review_logo'] ?? '') ?>"
                   placeholder="/uploads/payment-gateways/manual-review-logo.png or https://...">
        </div>
        <div class="form-group">
            <label for="payment_manual_review_logo_file">Upload Manual Review Logo (optional)</label>
            <input type="file" id="payment_manual_review_logo_file" name="payment_manual_review_logo_file" accept=".png,.jpg,.jpeg,.webp,image/png,image/jpeg,image/webp">
            <?php if (!empty($settings['payment_manual_review_logo'])): ?>
            <div class="gateway-logo-preview-wrap">
                <img src="<?= htmlspecialchars($settings['payment_manual_review_logo']) ?>" alt="Manual review logo" class="gateway-logo-preview">
                <label class="gateway-remove"><input type="checkbox" name="remove_payment_manual_review_logo" value="1"> Remove current logo</label>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div style="display:flex;justify-content:flex-end;">
        <button type="submit" style="background:linear-gradient(135deg,var(--cyan),var(--magenta));color:#fff;border:none;border-radius:8px;padding:10px 28px;font-size:.9rem;font-weight:600;cursor:pointer;">
            <i class="fas fa-save"></i> Save Payment Settings
        </button>
    </div>
</form>
<?php View::endSection(); ?>
