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
.toggle-row { display:flex; align-items:center; gap:12px; }
.toggle-switch { position:relative; width:48px; height:26px; flex-shrink:0; }
.toggle-switch input { opacity:0; width:0; height:0; }
.toggle-switch .slider { position:absolute; inset:0; background:var(--bg-secondary); border:1px solid var(--border-color); border-radius:26px; cursor:pointer; transition:.25s; }
.toggle-switch .slider::before { content:''; position:absolute; width:18px; height:18px; left:3px; top:3px; background:var(--text-secondary); border-radius:50%; transition:.25s; }
.toggle-switch input:checked + .slider { background:var(--cyan); border-color:var(--cyan); }
.toggle-switch input:checked + .slider::before { transform:translateX(22px); background:#fff; }
.alert-success { background:rgba(0,255,136,.1); color:var(--green); border:1px solid var(--green); padding:12px 16px; border-radius:8px; margin-bottom:20px; }
.alert-danger  { background:rgba(255,107,107,.1); color:var(--red); border:1px solid var(--red); padding:12px 16px; border-radius:8px; margin-bottom:20px; }
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

<form method="POST" action="/admin/payment-settings">
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
            <input type="text" id="payment_cashfree_secret" name="payment_cashfree_secret"
                   value="<?= htmlspecialchars($settings['payment_cashfree_secret']??'') ?>"
                   placeholder="Your Cashfree Secret Key">
            <small>&#9888; Stored as plain text in the database. Ensure your server is secured.</small>
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
    </div>

    <div style="display:flex;justify-content:flex-end;">
        <button type="submit" style="background:linear-gradient(135deg,var(--cyan),var(--magenta));color:#fff;border:none;border-radius:8px;padding:10px 28px;font-size:.9rem;font-weight:600;cursor:pointer;">
            <i class="fas fa-save"></i> Save Payment Settings
        </button>
    </div>
</form>
<?php View::endSection(); ?>
