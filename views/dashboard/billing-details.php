<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>
<?php View::section('styles'); ?>
<style>
/* ── Billing Details redesign ──────────────────────────────────────────── */
.bd-wrap { max-width: 660px; margin: 0 auto; }

.bd-steps {
    display: flex; align-items: center; justify-content: center;
    gap: 0; margin-bottom: 32px;
}
.bd-step {
    display: flex; flex-direction: column; align-items: center; gap: 6px;
    position: relative; flex: 1; text-align: center;
}
.bd-step:not(:last-child)::after {
    content: ''; position: absolute; top: 18px; left: calc(50% + 18px);
    width: calc(100% - 36px); height: 2px;
    background: var(--border-color);
}
.bd-step.active:not(:last-child)::after { background: var(--cyan); }
.bd-step-icon {
    width: 36px; height: 36px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: .85rem; font-weight: 700; position: relative; z-index: 1;
    border: 2px solid var(--border-color); background: var(--bg-secondary); color: var(--text-secondary);
}
.bd-step.active   .bd-step-icon { border-color: var(--cyan);   background: rgba(0,240,255,.1); color: var(--cyan); }
.bd-step.done     .bd-step-icon { border-color: var(--green);  background: rgba(0,255,136,.1); color: var(--green); }
.bd-step-label { font-size: .72rem; color: var(--text-secondary); font-weight: 500; }
.bd-step.active .bd-step-label { color: var(--cyan); font-weight: 700; }

.bd-card {
    background: var(--bg-card); border: 1px solid var(--border-color);
    border-radius: 18px; overflow: hidden;
}
.bd-card-head {
    padding: 22px 28px 18px;
    background: linear-gradient(135deg, rgba(0,240,255,.07) 0%, rgba(255,46,196,.05) 100%);
    border-bottom: 1px solid var(--border-color);
    display: flex; align-items: center; gap: 14px;
}
.bd-icon {
    width: 46px; height: 46px; border-radius: 12px;
    background: rgba(0,240,255,.12); display: flex; align-items: center;
    justify-content: center; color: var(--cyan); font-size: 1.2rem; flex-shrink: 0;
}
.bd-card-body { padding: 28px; }

.bd-field-label {
    display: block; font-weight: 600; font-size: .82rem;
    color: var(--text-secondary); margin-bottom: 7px; letter-spacing: .02em;
}
.bd-required { color: var(--red); margin-left: 2px; }
.bd-input {
    width: 100%; padding: 11px 14px;
    background: var(--bg-secondary); border: 1.5px solid var(--border-color);
    border-radius: 10px; color: var(--text-primary); font-size: .9rem;
    font-family: inherit; transition: border-color .2s, box-shadow .2s; box-sizing: border-box;
}
.bd-input:focus { outline: none; border-color: var(--cyan); box-shadow: 0 0 0 3px rgba(0,240,255,.12); }
.bd-input::placeholder { color: var(--text-secondary); opacity: .7; }
select.bd-input { cursor: pointer; }

.bd-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.bd-grid-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; }
.bd-field   { margin-bottom: 18px; }

.bd-divider {
    margin: 20px 0; border: none;
    border-top: 1px solid var(--border-color);
}
.bd-section-label {
    font-size: .72rem; font-weight: 700; color: var(--text-secondary);
    text-transform: uppercase; letter-spacing: .07em; margin-bottom: 16px;
    display: flex; align-items: center; gap: 7px;
}
.bd-section-label i { color: var(--cyan); }

.bd-submit-row {
    display: flex; align-items: center; gap: 16px; margin-top: 28px;
    padding-top: 22px; border-top: 1px solid var(--border-color);
}
.bd-btn-primary {
    flex: 1; padding: 14px 28px; background: linear-gradient(135deg, var(--cyan), var(--magenta));
    color: #06060a; border: none; border-radius: 12px; font-size: .95rem;
    font-weight: 700; cursor: pointer; font-family: inherit;
    transition: opacity .2s, transform .15s;
}
.bd-btn-primary:hover { opacity: .88; transform: translateY(-1px); }
.bd-btn-cancel {
    padding: 14px 20px; background: var(--bg-secondary);
    border: 1.5px solid var(--border-color); border-radius: 12px;
    color: var(--text-secondary); font-size: .88rem; font-weight: 600;
    text-decoration: none; transition: border-color .2s;
}
.bd-btn-cancel:hover { border-color: var(--text-secondary); color: var(--text-primary); }

.bd-info-box {
    display: flex; align-items: flex-start; gap: 10px; padding: 14px 16px;
    background: rgba(0,240,255,.06); border: 1px solid rgba(0,240,255,.15);
    border-radius: 10px; margin-bottom: 24px; font-size: .82rem; color: var(--text-secondary);
}
.bd-info-box i { color: var(--cyan); margin-top: 1px; flex-shrink: 0; }

@media (max-width: 600px) {
    .bd-grid-2 { grid-template-columns: 1fr; }
    .bd-grid-3 { grid-template-columns: 1fr 1fr; }
}
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div class="bd-wrap">

    <!-- Progress steps -->
    <div class="bd-steps">
        <div class="bd-step active">
            <div class="bd-step-icon"><i class="fas fa-file-invoice"></i></div>
            <span class="bd-step-label">Billing Info</span>
        </div>
        <div class="bd-step">
            <div class="bd-step-icon"><i class="fas fa-credit-card"></i></div>
            <span class="bd-step-label">Payment</span>
        </div>
        <div class="bd-step">
            <div class="bd-step-icon"><i class="fas fa-check-circle"></i></div>
            <span class="bd-step-label">Confirm</span>
        </div>
    </div>

    <?php if (Helpers::hasFlash('error')): ?>
        <div class="alert alert-error" style="margin-bottom:18px;"><?= View::e(Helpers::getFlash('error')) ?></div>
    <?php endif; ?>
    <?php if (Helpers::hasFlash('success')): ?>
        <div class="alert alert-success" style="margin-bottom:18px;"><?= View::e(Helpers::getFlash('success')) ?></div>
    <?php endif; ?>

    <div class="bd-info-box">
        <i class="fas fa-info-circle"></i>
        <span>This information will appear on your invoices and receipts. Please ensure your details are accurate.</span>
    </div>

    <div class="bd-card">
        <div class="bd-card-head">
            <div class="bd-icon"><i class="fas fa-user-tag"></i></div>
            <div>
                <div style="font-weight:700;font-size:1.05rem;">Billing Information</div>
                <div style="font-size:.78rem;color:var(--text-secondary);">Contact &amp; address for invoicing</div>
            </div>
        </div>
        <div class="bd-card-body">
            <form method="POST" action="/billing-details">
                <?= \Core\Security::csrfField() ?>
                <input type="hidden" name="next" value="<?= View::e($next) ?>">

                <!-- Contact -->
                <div class="bd-section-label"><i class="fas fa-id-card"></i> Contact</div>
                <div class="bd-grid-2">
                    <div class="bd-field">
                        <label class="bd-field-label">Full Name <span class="bd-required">*</span></label>
                        <input type="text" name="full_name" class="bd-input" required
                               value="<?= View::e($billing['full_name'] ?? '') ?>" placeholder="John Doe">
                    </div>
                    <div class="bd-field">
                        <label class="bd-field-label">Email <span class="bd-required">*</span></label>
                        <input type="email" name="billing_email" class="bd-input" required
                               value="<?= View::e($billing['email'] ?? '') ?>" placeholder="you@example.com">
                    </div>
                </div>
                <div class="bd-field">
                    <label class="bd-field-label">Phone Number <span class="bd-required">*</span></label>
                    <input type="tel" name="billing_phone" class="bd-input" required
                           value="<?= View::e($billing['phone'] ?? '') ?>" placeholder="+91 9876543210">
                </div>

                <hr class="bd-divider">

                <!-- Address -->
                <div class="bd-section-label"><i class="fas fa-map-marker-alt"></i> Address</div>
                <div class="bd-field">
                    <label class="bd-field-label">Address Line 1 <span class="bd-required">*</span></label>
                    <input type="text" name="address_line1" class="bd-input" required
                           value="<?= View::e($billing['address_line1'] ?? '') ?>" placeholder="Street address, P.O. box">
                </div>
                <div class="bd-field">
                    <label class="bd-field-label">Address Line 2 <span style="font-size:.75em;color:var(--text-secondary);font-weight:400;">(optional)</span></label>
                    <input type="text" name="address_line2" class="bd-input"
                           value="<?= View::e($billing['address_line2'] ?? '') ?>" placeholder="Apt, suite, unit, floor, etc.">
                </div>
                <div class="bd-grid-3">
                    <div class="bd-field">
                        <label class="bd-field-label">City <span class="bd-required">*</span></label>
                        <input type="text" name="city" class="bd-input" required
                               value="<?= View::e($billing['city'] ?? '') ?>" placeholder="Mumbai">
                    </div>
                    <div class="bd-field">
                        <label class="bd-field-label">State <span class="bd-required">*</span></label>
                        <input type="text" name="state" class="bd-input" required
                               value="<?= View::e($billing['state'] ?? '') ?>" placeholder="Maharashtra">
                    </div>
                    <div class="bd-field">
                        <label class="bd-field-label">Postal Code <span class="bd-required">*</span></label>
                        <input type="text" name="postal_code" class="bd-input" required
                               value="<?= View::e($billing['postal_code'] ?? '') ?>" placeholder="400001">
                    </div>
                </div>
                <div class="bd-field">
                    <label class="bd-field-label">Country <span class="bd-required">*</span></label>
                    <select name="country" class="bd-input" required>
                        <option value="">Select country…</option>
                        <?php
                        $countries = ['India','United States','United Kingdom','Canada','Australia','Germany','France','Singapore','UAE','Saudi Arabia','Bangladesh','Pakistan','Nigeria','Brazil','Mexico'];
                        $selected = $billing['country'] ?? '';
                        foreach ($countries as $c): ?>
                        <option value="<?= View::e($c) ?>" <?= $selected === $c ? 'selected' : '' ?>><?= View::e($c) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="bd-submit-row">
                    <a href="<?= View::e($next) ?>" class="bd-btn-cancel">
                        <i class="fas fa-times" style="margin-right:5px;"></i>Cancel
                    </a>
                    <button type="submit" class="bd-btn-primary">
                        <i class="fas fa-arrow-right" style="margin-right:7px;"></i>Save &amp; Continue to Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php View::endSection(); ?>
