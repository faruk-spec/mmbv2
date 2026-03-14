<?php
/** @var array $bill @var array $config @var array $user */
$csrfToken = \Core\Security::generateCsrfToken();

$typeLabel = $config['bill_types'][$bill['bill_type']] ?? ucfirst($bill['bill_type']);
$sym = ['INR' => '₹', 'USD' => '$', 'EUR' => '€', 'GBP' => '£'][$bill['currency']] ?? $bill['currency'] . ' ';

// Layout group and accent colour come from config (bill_groups / bill_colors).
$group = $config['bill_groups'][$bill['bill_type']] ?? 'invoice';
$c     = $config['bill_colors'][$bill['bill_type']] ?? '#37474f';

$items    = $bill['items'];
$subtotal = (float)$bill['subtotal'];
$taxPct   = (float)$bill['tax_percent'];
$taxAmt   = (float)$bill['tax_amount'];
$discount = (float)$bill['discount_amount'];
$total    = (float)$bill['total_amount'];
$billDate = $bill['bill_date'] ? date('d M Y', strtotime($bill['bill_date'])) : '';

// Template-data extras (CGST, SGST, vehicle, table, etc.)
$td = json_decode($bill['template_data'] ?? '{}', true) ?: [];
$tplStyle = $td['template_style'] ?? '1';
$autoprint = !empty($_GET['autoprint']);
?>

<a href="/projects/billx/history" class="back-link"><i class="fas fa-arrow-left"></i> Bill History</a>

<!-- Action bar -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <h2 style="font-size:1.4rem;font-weight:700;">
        <span style="color:var(--amber);"><?= htmlspecialchars($typeLabel) ?></span>
        &nbsp;—&nbsp;#<?= htmlspecialchars($bill['bill_number']) ?>
    </h2>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="/projects/billx/generate" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> New Bill
        </a>
        <button type="button" class="btn btn-secondary btn-sm" id="crambleBtn" onclick="toggleCrambled()">
            <i class="fas fa-scroll"></i> Crumpled View
        </button>
        <a href="/projects/billx/pdf/<?= (int)$bill['id'] ?>?autoprint=1" target="_blank" class="btn btn-secondary btn-sm">
            <i class="fas fa-print"></i> Print
        </a>
        <a href="/projects/billx/pdf/<?= (int)$bill['id'] ?>?download=1" target="_blank" class="btn btn-primary btn-sm">
            <i class="fas fa-download"></i> Download PDF
        </a>
        <button type="button" class="btn btn-danger btn-sm"
                onclick="document.getElementById('deleteModal').style.display='flex'">
            <i class="fas fa-trash"></i> Delete
        </button>
    </div>
</div>

<!-- Bill document wrapper -->
<div style="background:#f0f0f0;padding:24px;border-radius:12px;" id="billDocWrapper">

<div id="billDocument" style="<?= $group==='thermal' ? 'width:80mm;max-width:80mm;' : 'max-width:700px;' ?> margin:0 auto;">

<?php include __DIR__ . '/_bill_render.php'; ?>

</div><!-- /billDocument -->
</div><!-- /wrapper -->

<!-- Delete modal -->
<div id="deleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:1000;align-items:center;justify-content:center;">
    <div class="card" style="max-width:400px;width:90%;padding:28px;">
        <h3 style="margin-bottom:12px;"><i class="fas fa-exclamation-triangle" style="color:#ff6b6b;"></i> Delete Bill</h3>
        <p style="color:var(--text-secondary);margin-bottom:20px;">
            Delete bill <strong>#<?= htmlspecialchars($bill['bill_number']) ?></strong>? This cannot be undone.
        </p>
        <form method="POST" action="/projects/billx/delete">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <input type="hidden" name="id" value="<?= (int)$bill['id'] ?>">
            <div class="form-actions">
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
                <button type="button" class="btn btn-secondary"
                        onclick="document.getElementById('deleteModal').style.display='none'">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
/* ── Crumpled paper effect ─────────────────────────────────────────── */
#billDocWrapper.crambled {
    background-color: #9e9b96 !important;
    background-image:
        repeating-linear-gradient(
            157deg,
            transparent 0, transparent 88px,
            rgba(0,0,0,.065) 88px, rgba(255,255,255,.55) 89px,
            rgba(0,0,0,.025) 90px, transparent 91px
        ),
        repeating-linear-gradient(
            -53deg,
            transparent 0, transparent 110px,
            rgba(0,0,0,.05) 110px, rgba(255,255,255,.45) 111px,
            rgba(0,0,0,.02) 112px, transparent 113px
        ),
        repeating-linear-gradient(
            73deg,
            transparent 0, transparent 148px,
            rgba(0,0,0,.04) 148px, rgba(255,255,255,.38) 149px,
            transparent 150px
        ),
        repeating-linear-gradient(
            180deg,
            rgba(255,255,255,.018) 0, rgba(255,255,255,.018) 1px,
            transparent 1px, transparent 3px
        ) !important;
}
#billDocWrapper.crambled #billDocument {
    position: relative;
    background: #fdfcfa;
    box-shadow:
        -6px 4px 22px rgba(0,0,0,.22),
        6px -3px 16px rgba(0,0,0,.15),
        0 12px 40px rgba(0,0,0,.28);
    transform: rotate(0.45deg) skew(-0.12deg, 0.1deg);
    filter: brightness(0.99) contrast(1.02);
}
#billDocWrapper.crambled #billDocument::before {
    content: '';
    position: absolute;
    inset: 0;
    pointer-events: none;
    z-index: 9999;
    background:
        linear-gradient(148deg, transparent 37%, rgba(0,0,0,.042) 37.3%, rgba(255,255,255,.82) 37.7%, rgba(0,0,0,.018) 38%, transparent 38.4%),
        linear-gradient(-46deg, transparent 44%, rgba(0,0,0,.035) 44.3%, rgba(255,255,255,.7) 44.7%, rgba(0,0,0,.015) 45%, transparent 45.4%),
        linear-gradient(71deg, transparent 23%, rgba(0,0,0,.028) 23.3%, rgba(255,255,255,.6) 23.7%, transparent 24.1%),
        radial-gradient(ellipse 35% 30% at 0% 0%, rgba(0,0,0,.08) 0%, transparent 100%),
        radial-gradient(ellipse 30% 25% at 100% 100%, rgba(0,0,0,.06) 0%, transparent 100%);
    mix-blend-mode: multiply;
}
#crambleBtn.active {
    background: var(--amber) !important;
    color: #fff !important;
    border-color: var(--amber) !important;
}
@media print {
    .back-link, .btn, #deleteModal, .billx-sidebar, .sidebar-toggle, nav { display: none !important; }
    .billx-main { margin-left: 0 !important; padding: 0 !important; }
    #billDocWrapper { background: white !important; padding: 0 !important; }
}
</style>

<script>
function toggleCrambled(){
    const w = document.getElementById('billDocWrapper');
    const btn = document.getElementById('crambleBtn');
    w.classList.toggle('crambled');
    btn.classList.toggle('active');
}
<?php if ($autoprint): ?>
window.addEventListener('load', function(){ setTimeout(function(){ window.print(); }, 600); });
<?php endif; ?>
</script>
