<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<?php
$layoutLabels = [
    'bill_to' => 'Bill To Section',
    'subscription_details' => 'Subscription Details Section',
    'line_items' => 'Line Items Table',
    'footer_notes' => 'Footer Notes Block',
];
$layoutOrder = json_decode($settings['invoice_layout_blocks'] ?? '', true);
if (!is_array($layoutOrder)) {
    $layoutOrder = array_keys($layoutLabels);
}
$normalizedLayoutOrder = [];
foreach ($layoutOrder as $item) {
    $key = (string) $item;
    if (isset($layoutLabels[$key])) {
        $normalizedLayoutOrder[] = $key;
    }
}
$layoutOrder = array_values(array_unique($normalizedLayoutOrder));
foreach (array_keys($layoutLabels) as $key) {
    if (!in_array($key, $layoutOrder, true)) {
        $layoutOrder[] = $key;
    }
}
?>

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
        <div style="margin-bottom:16px;font-weight:700;font-size:.9rem;">Tax Settings</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;align-items:end;">
            <div>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;">
                    <input type="checkbox" name="invoice_tax_enabled" value="1" <?= ($settings['invoice_tax_enabled'] ?? '0') === '1' ? 'checked' : '' ?> id="taxEnabledChk">
                    <span>Enable Tax on Invoices</span>
                </label>
            </div>
            <div>
                <label>Tax Label (e.g. GST, VAT)</label>
                <input type="text" name="invoice_tax_label" class="form-control" value="<?= View::e($settings['invoice_tax_label'] ?? 'Tax') ?>">
            </div>
            <div>
                <label>Tax Rate (%)</label>
                <input type="number" name="invoice_tax_rate" class="form-control" min="0" max="100" step="0.01" value="<?= View::e($settings['invoice_tax_rate'] ?? '0') ?>">
            </div>
        </div>
        <p style="color:var(--text-secondary);font-size:.78rem;margin-top:12px;">When enabled, tax will be shown as a line item on all invoices. The total will include the tax amount.</p>
    </div>

    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:22px;margin-bottom:20px;">
        <div style="margin-bottom:16px;font-weight:700;font-size:.9rem;">Content Customization</div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px;align-items:end;">
            <div>
                <label>Invoice Title</label>
                <input type="text" name="invoice_title" class="form-control" value="<?= View::e($settings['invoice_title'] ?? 'Subscription Invoice') ?>">
            </div>
            <div>
                <label>Invoice Subtitle</label>
                <input type="text" name="invoice_subtitle" class="form-control" value="<?= View::e($settings['invoice_subtitle'] ?? 'Secure payment receipt') ?>">
            </div>
            <div>
                <label>Line Item Label</label>
                <input type="text" name="invoice_item_label" class="form-control" value="<?= View::e($settings['invoice_item_label'] ?? 'Subscription') ?>">
            </div>
            <div>
                <label>Total Label</label>
                <input type="text" name="invoice_total_label" class="form-control" value="<?= View::e($settings['invoice_total_label'] ?? 'Total') ?>">
            </div>
        </div>
    </div>

    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:22px;margin-bottom:20px;">
        <div style="margin-bottom:8px;font-weight:700;font-size:.9rem;">Invoice Layout Builder (Drag &amp; Drop)</div>
        <p style="color:var(--text-secondary);font-size:.8rem;margin-bottom:12px;">Drag sections to customize invoice section order.</p>
        <input type="hidden" name="invoice_layout_blocks" id="invoiceLayoutBlocksInput" value="<?= htmlspecialchars((string) json_encode($layoutOrder), ENT_QUOTES, 'UTF-8') ?>">
        <ul id="invoiceLayoutList" style="list-style:none;padding:0;margin:0;display:grid;gap:10px;">
            <?php foreach ($layoutOrder as $blockKey): ?>
            <li draggable="true"
                data-layout-key="<?= View::e($blockKey) ?>"
                style="display:flex;align-items:center;gap:10px;padding:12px;border:1px dashed var(--border-color);border-radius:10px;background:var(--bg-secondary);cursor:move;">
                <i class="fas fa-grip-vertical" style="color:var(--text-secondary);"></i>
                <span style="font-size:.86rem;font-weight:600;"><?= View::e($layoutLabels[$blockKey] ?? $blockKey) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
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

<?php View::section('scripts'); ?>
<script>
(function () {
    const list = document.getElementById('invoiceLayoutList');
    const hidden = document.getElementById('invoiceLayoutBlocksInput');
    if (!list || !hidden) return;
    let dragged = null;

    function updateValue() {
        const order = Array.from(list.querySelectorAll('li[data-layout-key]')).map((item) => item.dataset.layoutKey);
        hidden.value = JSON.stringify(order);
    }

    list.querySelectorAll('li[data-layout-key]').forEach((item) => {
        item.addEventListener('dragstart', function () {
            dragged = this;
            this.style.opacity = '0.5';
        });
        item.addEventListener('dragend', function () {
            this.style.opacity = '1';
            dragged = null;
            updateValue();
        });
        item.addEventListener('dragover', function (e) {
            e.preventDefault();
        });
        item.addEventListener('drop', function (e) {
            e.preventDefault();
            if (!dragged || dragged === this) return;
            const rect = this.getBoundingClientRect();
            const after = e.clientY > rect.top + rect.height / 2;
            if (after) {
                this.parentNode.insertBefore(dragged, this.nextSibling);
            } else {
                this.parentNode.insertBefore(dragged, this);
            }
        });
    });

    updateValue();
})();
</script>
<?php View::endSection(); ?>
