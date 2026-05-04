<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>
<?php View::section('content'); ?>

<div style="max-width:700px;margin:0 auto;">
    <div style="margin-bottom:20px;">
        <h1 style="font-size:1.4rem;font-weight:700;margin:0 0 6px;display:flex;align-items:center;gap:10px;">
            <i class="fas fa-file-invoice" style="color:var(--cyan);"></i> Billing Details
        </h1>
        <p style="color:var(--text-secondary);font-size:.9rem;margin:0;">This information will appear on your invoices and is required before subscribing to a plan.</p>
    </div>

    <?php if (Helpers::hasFlash('error')): ?>
        <div class="alert alert-error" style="margin-bottom:16px;"><?= View::e(Helpers::getFlash('error')) ?></div>
    <?php endif; ?>
    <?php if (Helpers::hasFlash('success')): ?>
        <div class="alert alert-success" style="margin-bottom:16px;"><?= View::e(Helpers::getFlash('success')) ?></div>
    <?php endif; ?>

    <div class="card" style="border-radius:14px;overflow:hidden;border:1px solid var(--border-color);">
        <div style="padding:20px 24px;border-bottom:1px solid var(--border-color);background:linear-gradient(135deg,rgba(0,240,255,.07),rgba(255,46,196,.05));">
            <h3 style="font-size:.9rem;font-weight:700;margin:0;color:var(--cyan);text-transform:uppercase;letter-spacing:.05em;">
                <i class="fas fa-user-tag"></i> Contact &amp; Address
            </h3>
        </div>
        <div style="padding:24px;">
            <form method="POST" action="/billing-details">
                <?= \Core\Security::csrfField() ?>
                <input type="hidden" name="next" value="<?= View::e($next) ?>">

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div>
                        <label style="display:block;font-weight:600;font-size:.875rem;margin-bottom:6px;">Full Name <span style="color:var(--red);">*</span></label>
                        <input type="text" name="full_name" class="form-input" required
                               value="<?= View::e($billing['full_name'] ?? '') ?>"
                               placeholder="John Doe"
                               style="width:100%;padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.9rem;">
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;font-size:.875rem;margin-bottom:6px;">Email <span style="color:var(--red);">*</span></label>
                        <input type="email" name="billing_email" class="form-input" required
                               value="<?= View::e($billing['email'] ?? '') ?>"
                               placeholder="you@example.com"
                               style="width:100%;padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.9rem;">
                    </div>
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-weight:600;font-size:.875rem;margin-bottom:6px;">Phone Number <span style="color:var(--red);">*</span></label>
                    <input type="tel" name="billing_phone" class="form-input" required
                           value="<?= View::e($billing['phone'] ?? '') ?>"
                           placeholder="+91 9876543210"
                           style="width:100%;padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.9rem;">
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-weight:600;font-size:.875rem;margin-bottom:6px;">Address Line 1 <span style="color:var(--red);">*</span></label>
                    <input type="text" name="address_line1" class="form-input" required
                           value="<?= View::e($billing['address_line1'] ?? '') ?>"
                           placeholder="Street address, P.O. box"
                           style="width:100%;padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.9rem;">
                </div>

                <div style="margin-bottom:16px;">
                    <label style="display:block;font-weight:600;font-size:.875rem;margin-bottom:6px;">Address Line 2 <span style="color:var(--text-secondary);font-size:.8em;">(optional)</span></label>
                    <input type="text" name="address_line2" class="form-input"
                           value="<?= View::e($billing['address_line2'] ?? '') ?>"
                           placeholder="Apt, suite, unit, floor, etc."
                           style="width:100%;padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.9rem;">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
                    <div>
                        <label style="display:block;font-weight:600;font-size:.875rem;margin-bottom:6px;">City <span style="color:var(--red);">*</span></label>
                        <input type="text" name="city" class="form-input" required
                               value="<?= View::e($billing['city'] ?? '') ?>"
                               placeholder="Mumbai"
                               style="width:100%;padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.9rem;">
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;font-size:.875rem;margin-bottom:6px;">State / Province <span style="color:var(--red);">*</span></label>
                        <input type="text" name="state" class="form-input" required
                               value="<?= View::e($billing['state'] ?? '') ?>"
                               placeholder="Maharashtra"
                               style="width:100%;padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.9rem;">
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:24px;">
                    <div>
                        <label style="display:block;font-weight:600;font-size:.875rem;margin-bottom:6px;">Postal Code <span style="color:var(--red);">*</span></label>
                        <input type="text" name="postal_code" class="form-input" required
                               value="<?= View::e($billing['postal_code'] ?? '') ?>"
                               placeholder="400001"
                               style="width:100%;padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.9rem;">
                    </div>
                    <div>
                        <label style="display:block;font-weight:600;font-size:.875rem;margin-bottom:6px;">Country <span style="color:var(--red);">*</span></label>
                        <select name="country" required
                                style="width:100%;padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.9rem;">
                            <option value="">Select country...</option>
                            <?php
                            $countries = ['India','United States','United Kingdom','Canada','Australia','Germany','France','Singapore','UAE','Saudi Arabia','Bangladesh','Pakistan','Nigeria','Brazil','Mexico'];
                            $selected = $billing['country'] ?? '';
                            foreach ($countries as $c): ?>
                            <option value="<?= View::e($c) ?>" <?= $selected === $c ? 'selected' : '' ?>><?= View::e($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div style="display:flex;gap:12px;align-items:center;">
                    <button type="submit" class="btn btn-primary" style="padding:12px 28px;font-weight:600;background:linear-gradient(135deg,var(--cyan),var(--magenta));border:none;border-radius:10px;color:#06060a;cursor:pointer;font-size:.9rem;">
                        <i class="fas fa-save" style="margin-right:6px;"></i> Save &amp; Continue
                    </button>
                    <a href="<?= View::e($next) ?>" style="color:var(--text-secondary);font-size:.875rem;text-decoration:none;">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php View::endSection(); ?>
