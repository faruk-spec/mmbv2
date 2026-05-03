<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<?php if (Helpers::hasFlash('success')): ?>
<div style="background:rgba(0,255,136,.1);border:1px solid var(--green);color:var(--green);padding:12px 16px;border-radius:8px;margin-bottom:20px;">
    <i class="fas fa-check-circle"></i> <?= View::e(Helpers::getFlash('success')) ?>
</div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
<div style="background:rgba(255,107,107,.1);border:1px solid var(--red);color:var(--red);padding:12px 16px;border-radius:8px;margin-bottom:20px;">
    <i class="fas fa-exclamation-circle"></i> <?= View::e(Helpers::getFlash('error')) ?>
</div>
<?php endif; ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1 style="font-size:1.4rem;font-weight:700;margin-bottom:4px;">Invoice Settings</h1>
        <p style="color:var(--text-secondary);font-size:.875rem;">Configure shared invoice branding, company details, and footer content.</p>
    </div>
</div>

<form method="POST" action="/admin/invoice-settings" enctype="multipart/form-data" style="max-width:920px;">
    <?= \Core\Security::csrfField() ?>
    <input type="hidden" name="current_invoice_logo" value="<?= View::e($settings['invoice_logo'] ?? '') ?>">

    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:22px;margin-bottom:20px;">
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;">
            <div>
                <label>Company Name</label>
                <input type="text" name="invoice_company_name" class="form-control" value="<?= View::e($settings['invoice_company_name'] ?? '') ?>">
            </div>
            <div>
                <label>Company Email</label>
                <input type="email" name="invoice_company_email" class="form-control" value="<?= View::e($settings['invoice_company_email'] ?? '') ?>">
            </div>
            <div>
                <label>Company Phone</label>
                <input type="text" name="invoice_company_phone" class="form-control" value="<?= View::e($settings['invoice_company_phone'] ?? '') ?>">
            </div>
            <div>
                <label>Invoice Prefix</label>
                <input type="text" name="invoice_prefix" class="form-control" maxlength="10" value="<?= View::e($settings['invoice_prefix'] ?? 'INV') ?>">
            </div>
            <div>
                <label>Accent Color</label>
                <input type="color" name="invoice_accent_color" class="form-control" style="height:44px;" value="<?= View::e($settings['invoice_accent_color'] ?? '#0077cc') ?>">
            </div>
            <div>
                <label>Invoice Logo</label>
                <input type="file" name="invoice_logo" class="form-control" accept=".png,.jpg,.jpeg,.webp,.svg">
                <?php if (!empty($settings['invoice_logo'])): ?>
                <div style="margin-top:10px;display:flex;align-items:center;gap:12px;">
                    <img src="<?= View::e($settings['invoice_logo']) ?>" alt="Invoice logo" style="max-width:120px;max-height:56px;border-radius:6px;background:#fff;padding:4px;">
                    <label style="display:flex;align-items:center;gap:6px;font-size:.85rem;">
                        <input type="checkbox" name="remove_invoice_logo" value="1"> Remove logo
                    </label>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div style="margin-top:16px;">
            <label>Company Address</label>
            <textarea name="invoice_company_address" class="form-control" rows="3"><?= View::e($settings['invoice_company_address'] ?? '') ?></textarea>
        </div>
    </div>

    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:22px;margin-bottom:20px;">
        <div style="margin-bottom:16px;">
            <label>Footer Note</label>
            <textarea name="invoice_footer_note" class="form-control" rows="3"><?= View::e($settings['invoice_footer_note'] ?? '') ?></textarea>
        </div>
        <div>
            <label>Terms / Legal Note</label>
            <textarea name="invoice_terms" class="form-control" rows="4"><?= View::e($settings['invoice_terms'] ?? '') ?></textarea>
        </div>
    </div>

    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Invoice Settings</button>
</form>

<?php View::endSection(); ?>
