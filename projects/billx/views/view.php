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
?>

<a href="/projects/billx/history" class="back-link"><i class="fas fa-arrow-left"></i> Bill History</a>

<!-- Action bar -->
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <h2 style="font-size:1.4rem;font-weight:700;">
        <span style="color:var(--amber);"><?= htmlspecialchars($typeLabel) ?></span>
        &nbsp;—&nbsp;#<?= htmlspecialchars($bill['bill_number']) ?>
    </h2>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="/projects/billx/download/<?= (int)$bill['id'] ?>" class="btn btn-secondary">
            <i class="fas fa-download"></i> Download HTML
        </a>
        <button type="button" class="btn btn-secondary" onclick="window.print()">
            <i class="fas fa-print"></i> Print
        </button>
        <button type="button" class="btn btn-danger btn-sm"
                onclick="document.getElementById('deleteModal').style.display='flex'">
            <i class="fas fa-trash"></i> Delete
        </button>
    </div>
</div>

<!-- Bill document wrapper -->
<div style="background:#f0f0f0;padding:24px;border-radius:12px;" id="billDocWrapper">
<div id="billDocument" style="max-width:700px;margin:0 auto;">

<?php if ($group === 'thermal'): ?>
<!-- ============================================================
     THERMAL RECEIPT  (restaurant, recharge, mart, newspaper)
     ============================================================ -->
<div style="font-family:'Courier New',Courier,monospace;background:#fff;max-width:340px;margin:0 auto;padding:18px 22px;font-size:12px;color:#111;border:1px solid #ccc;box-shadow:2px 2px 10px rgba(0,0,0,.2);">
    <div style="text-align:center;font-size:17px;font-weight:900;letter-spacing:1px;text-transform:uppercase;"><?= htmlspecialchars($bill['from_name']) ?></div>
    <?php if ($bill['from_address']): ?>
    <div style="text-align:center;font-size:10px;color:#444;margin-top:2px;"><?= htmlspecialchars(str_replace("\n",', ',$bill['from_address'])) ?></div>
    <?php endif; ?>
    <?php if ($bill['from_phone']): ?>
    <div style="text-align:center;font-size:10px;">Tel: <?= htmlspecialchars($bill['from_phone']) ?></div>
    <?php endif; ?>
    <?php if ($bill['from_email']): ?>
    <div style="text-align:center;font-size:10px;"><?= htmlspecialchars($bill['from_email']) ?></div>
    <?php endif; ?>
    <div style="text-align:center;font-size:11px;font-weight:700;background:<?= htmlspecialchars($c) ?>;color:#fff;padding:3px 0;margin:8px 0;letter-spacing:2px;"><?= strtoupper(htmlspecialchars($typeLabel)) ?></div>
    <div style="border-top:1px dashed #999;margin:8px 0;"></div>
    <div style="display:flex;justify-content:space-between;font-size:11px;"><span>Bill#: <b><?= htmlspecialchars($bill['bill_number']) ?></b></span><span>Date: <?= $billDate ?></span></div>
    <div style="font-size:11px;">Customer: <b><?= htmlspecialchars($bill['to_name']) ?></b></div>
    <?php if ($bill['to_phone']): ?><div style="font-size:10px;">Ph: <?= htmlspecialchars($bill['to_phone']) ?></div><?php endif; ?>
    <div style="border-top:1px dashed #999;margin:8px 0;"></div>
    <div style="display:flex;justify-content:space-between;font-size:10px;font-weight:700;padding:2px 0;border-bottom:1px solid #333;margin-bottom:4px;"><span style="flex:1;">Item</span><span>Qty×Rate</span><span style="margin-left:8px;">Amt</span></div>
    <?php foreach ($items as $item): ?>
    <div style="display:flex;justify-content:space-between;padding:2px 0;font-size:12px;">
        <span style="flex:1;"><?= htmlspecialchars($item['description'] ?? '-') ?></span>
        <span style="white-space:nowrap;margin-left:8px;"><?= (float)($item['qty']??1) ?>×<?= $sym ?><?= number_format((float)($item['rate']??0),2) ?></span>
        <span style="white-space:nowrap;margin-left:8px;font-weight:700;"><?= $sym ?><?= number_format((float)($item['amount']??0),2) ?></span>
    </div>
    <?php endforeach; ?>
    <div style="border-top:1px dashed #999;margin:8px 0;"></div>
    <div style="display:flex;justify-content:space-between;font-size:11px;"><span>Subtotal</span><span><?= $sym ?><?= number_format($subtotal,2) ?></span></div>
    <?php if ($taxPct > 0): ?>
    <div style="display:flex;justify-content:space-between;font-size:11px;"><span>Tax <?= $taxPct ?>%</span><span><?= $sym ?><?= number_format($taxAmt,2) ?></span></div>
    <?php endif; ?>
    <?php if ($discount > 0): ?>
    <div style="display:flex;justify-content:space-between;font-size:11px;"><span>Discount</span><span>-<?= $sym ?><?= number_format($discount,2) ?></span></div>
    <?php endif; ?>
    <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:900;border-top:2px solid #111;margin-top:4px;padding-top:4px;"><span>TOTAL</span><span><?= $sym ?><?= number_format($total,2) ?></span></div>
    <div style="border-top:1px dashed #999;margin:8px 0;"></div>
    <?php if ($bill['notes']): ?><div style="font-size:10px;color:#555;text-align:center;margin-bottom:4px;"><?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
    <div style="text-align:center;font-size:11px;margin-top:8px;font-weight:700;">** Thank You! Visit Again **</div>
    <div style="text-align:center;font-size:9px;color:#888;margin-top:4px;">Powered by BillX</div>
</div>

<?php elseif ($group === 'payslip'): ?>
<!-- ============================================================
     PAYSLIP  (driver, helper)
     ============================================================ -->
<?php
    // Convention: even-indexed items (0,2,4…) are earnings; odd-indexed items (1,3,5…) are deductions.
    // Users should add items alternating: earning, deduction, earning, deduction, etc.
    $earnings   = array_values(array_filter($items, fn($k) => $k % 2 === 0, ARRAY_FILTER_USE_KEY));
    $deductions = array_values(array_filter($items, fn($k) => $k % 2 !== 0, ARRAY_FILTER_USE_KEY));
    $maxRows    = max(count($earnings), count($deductions), 1);
    $totalDeductions = array_sum(array_column($deductions, 'amount'));
?>
<div style="font-family:Arial,sans-serif;background:#fff;font-size:12px;color:#222;border:1px solid #ccc;box-shadow:0 2px 12px rgba(0,0,0,.1);">
    <div style="background:<?= htmlspecialchars($c) ?>;color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
        <div>
            <div style="font-size:18px;font-weight:700;"><?= htmlspecialchars($bill['from_name']) ?></div>
            <?php if ($bill['from_address']): ?><div style="font-size:10px;opacity:.85;"><?= htmlspecialchars(str_replace("\n",', ',$bill['from_address'])) ?></div><?php endif; ?>
        </div>
        <div style="text-align:right;">
            <div style="font-size:15px;font-weight:900;letter-spacing:1px;"><?= strtoupper(htmlspecialchars($typeLabel)) ?></div>
            <div style="font-size:10px;opacity:.9;">Slip # <?= htmlspecialchars($bill['bill_number']) ?></div>
            <div style="font-size:10px;opacity:.9;">Period: <?= $billDate ?></div>
        </div>
    </div>
    <div style="background:#f3f3f3;padding:10px 20px;display:flex;justify-content:space-between;border-bottom:2px solid <?= htmlspecialchars($c) ?>;">
        <div>
            <span style="font-size:10px;color:#666;display:block;text-transform:uppercase;letter-spacing:.05em;">Employee Name</span>
            <span style="font-size:14px;font-weight:700;"><?= htmlspecialchars($bill['to_name']) ?></span>
            <?php if ($bill['to_phone']): ?><span style="font-size:10px;color:#555;display:block;">Ph: <?= htmlspecialchars($bill['to_phone']) ?></span><?php endif; ?>
        </div>
        <div style="text-align:right;">
            <span style="font-size:10px;color:#666;display:block;text-transform:uppercase;letter-spacing:.05em;">Pay Date</span>
            <span style="font-size:13px;font-weight:600;"><?= $billDate ?></span>
        </div>
    </div>
    <table style="width:100%;border-collapse:collapse;">
        <thead>
            <tr style="background:#e8eaf6;">
                <th style="padding:7px 8px;text-align:left;font-size:11px;text-transform:uppercase;color:<?= htmlspecialchars($c) ?>;">Earnings</th>
                <th style="padding:7px 8px;text-align:right;font-size:11px;color:<?= htmlspecialchars($c) ?>;">Amount</th>
                <th style="padding:7px 8px;text-align:left;font-size:11px;text-transform:uppercase;color:#e53935;border-left:2px solid #ddd;">Deductions</th>
                <th style="padding:7px 8px;text-align:right;font-size:11px;color:#e53935;">Amount</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($i = 0; $i < $maxRows; $i++): ?>
            <tr style="border-bottom:1px solid #eee;">
                <td style="padding:5px 8px;font-size:12px;"><?= isset($earnings[$i]) ? htmlspecialchars($earnings[$i]['description']??'') : '' ?></td>
                <td style="padding:5px 8px;text-align:right;font-size:12px;font-weight:600;"><?= isset($earnings[$i]) ? $sym.number_format((float)$earnings[$i]['amount'],2) : '' ?></td>
                <td style="padding:5px 8px;font-size:12px;border-left:2px solid #ddd;"><?= isset($deductions[$i]) ? htmlspecialchars($deductions[$i]['description']??'') : '' ?></td>
                <td style="padding:5px 8px;text-align:right;font-size:12px;font-weight:600;color:#e53935;"><?= isset($deductions[$i]) ? '-'.$sym.number_format((float)$deductions[$i]['amount'],2) : '' ?></td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>
    <div style="padding:10px 20px;background:#f8f8f8;border-top:2px solid #ddd;display:flex;justify-content:space-between;align-items:center;">
        <?php if ($taxPct > 0): ?><div style="font-size:11px;color:#666;">Tax <?= $taxPct ?>% = <b><?= $sym.number_format($taxAmt,2) ?></b></div><?php else: ?><div></div><?php endif; ?>
        <div><span style="font-size:11px;color:#555;">Total Deductions: <b style="color:#e53935;"><?= $sym.number_format($totalDeductions,2) ?></b></span></div>
    </div>
    <div style="background:<?= htmlspecialchars($c) ?>;color:#fff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;">
        <span style="font-size:13px;font-weight:700;">NET SALARY</span>
        <span style="font-size:20px;font-weight:900;"><?= $sym.number_format($total,2) ?></span>
    </div>
    <?php if ($bill['notes']): ?><div style="padding:8px 20px;font-size:10px;color:#666;border-top:1px solid #eee;"><?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
    <div style="padding:14px 20px;display:flex;justify-content:space-between;font-size:11px;border-top:1px solid #eee;">
        <div><div style="border-top:1px solid #555;width:120px;padding-top:4px;margin-top:24px;">Employee Signature</div></div>
        <div><div style="border-top:1px solid #555;width:120px;padding-top:4px;margin-top:24px;">Authorised Signatory</div></div>
    </div>
</div>

<?php elseif ($group === 'fuel'): ?>
<!-- ============================================================
     FUEL SLIP
     ============================================================ -->
<div style="font-family:Arial,sans-serif;background:#fff;max-width:420px;margin:0 auto;border:2px solid #e65000;border-radius:6px;overflow:hidden;font-size:12px;box-shadow:0 2px 12px rgba(0,0,0,.1);">
    <div style="background:#e65000;color:#fff;padding:14px 16px;">
        <div style="font-size:20px;font-weight:900;">⛽ <?= htmlspecialchars($bill['from_name']) ?></div>
        <?php if ($bill['from_address']): ?><div style="font-size:10px;opacity:.85;"><?= htmlspecialchars(str_replace("\n",' | ',$bill['from_address'])) ?></div><?php endif; ?>
        <?php if ($bill['from_phone']): ?><div style="font-size:10px;opacity:.85;">📞 <?= htmlspecialchars($bill['from_phone']) ?></div><?php endif; ?>
    </div>
    <div style="background:#fff3e0;padding:8px 16px;display:flex;justify-content:space-between;border-bottom:1px dashed #e65000;">
        <span style="font-size:11px;font-weight:700;color:#e65000;">FUEL RECEIPT</span>
        <span style="font-size:11px;">Bill#: <b><?= htmlspecialchars($bill['bill_number']) ?></b></span>
        <span style="font-size:11px;"><?= $billDate ?></span>
    </div>
    <div style="padding:8px 16px;background:#fafafa;border-bottom:1px solid #ffe0b2;">
        <div style="font-size:11px;color:#666;text-transform:uppercase;letter-spacing:.05em;">Vehicle / Customer</div>
        <div style="font-size:14px;font-weight:700;"><?= htmlspecialchars($bill['to_name']) ?></div>
        <?php if ($bill['to_phone']): ?><div style="font-size:11px;color:#555;">📞 <?= htmlspecialchars($bill['to_phone']) ?></div><?php endif; ?>
        <?php if ($bill['to_address']): ?><div style="font-size:11px;color:#555;"><?= htmlspecialchars(str_replace("\n",', ',$bill['to_address'])) ?></div><?php endif; ?>
    </div>
    <table style="width:100%;border-collapse:collapse;">
        <thead><tr style="background:#fff3e0;"><th style="padding:6px 8px;text-align:left;font-size:11px;color:#e65000;">Fuel / Product</th><th style="padding:6px 8px;text-align:center;font-size:11px;color:#e65000;">Qty (L)</th><th style="padding:6px 8px;text-align:right;font-size:11px;color:#e65000;">Rate/L</th><th style="padding:6px 8px;text-align:right;font-size:11px;color:#e65000;">Amount</th></tr></thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr style="border-bottom:1px solid #ffe0a0;">
                <td style="padding:5px 8px;font-size:12px;"><?= htmlspecialchars($item['description']??'-') ?></td>
                <td style="padding:5px 8px;text-align:center;font-size:12px;"><?= (float)($item['qty']??1) ?> L</td>
                <td style="padding:5px 8px;text-align:right;font-size:12px;"><?= $sym ?><?= number_format((float)($item['rate']??0),2) ?>/L</td>
                <td style="padding:5px 8px;text-align:right;font-weight:700;font-size:12px;"><?= $sym ?><?= number_format((float)($item['amount']??0),2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php if ($taxPct > 0): ?><div style="padding:4px 16px;display:flex;justify-content:space-between;font-size:11px;background:#fff8f0;border-top:1px solid #ffe0b2;"><span>Tax <?= $taxPct ?>%</span><span><?= $sym.number_format($taxAmt,2) ?></span></div><?php endif; ?>
    <div style="background:#e65000;color:#fff;padding:10px 16px;display:flex;justify-content:space-between;font-size:15px;font-weight:900;">
        <span>TOTAL AMOUNT</span><span><?= $sym.number_format($total,2) ?></span>
    </div>
    <?php if ($bill['notes']): ?><div style="padding:8px 16px;font-size:10px;color:#666;"><?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
    <div style="text-align:center;padding:8px;font-size:10px;color:#999;background:#fff8f0;">Thank you for fueling with us | Powered by BillX</div>
</div>

<?php elseif ($group === 'cab'): ?>
<!-- ============================================================
     CAB & TRAVEL
     ============================================================ -->
<div style="font-family:Arial,sans-serif;background:#1a1a1a;color:#fff;max-width:380px;margin:0 auto;border-radius:8px;overflow:hidden;font-size:12px;box-shadow:0 4px 16px rgba(0,0,0,.4);">
    <div style="background:#f5a623;padding:14px 16px;color:#1a1a1a;">
        <div style="font-size:20px;font-weight:900;">🚕 <?= htmlspecialchars($bill['from_name']) ?></div>
        <?php if ($bill['from_phone']): ?><div style="font-size:11px;">📞 <?= htmlspecialchars($bill['from_phone']) ?></div><?php endif; ?>
        <?php if ($bill['from_address']): ?><div style="font-size:10px;"><?= htmlspecialchars(str_replace("\n",' | ',$bill['from_address'])) ?></div><?php endif; ?>
    </div>
    <div style="padding:10px 16px;background:#2a2a2a;border-bottom:1px solid #444;">
        <div style="display:flex;justify-content:space-between;"><span style="font-size:11px;color:#f5a623;font-weight:700;">CAB & TRAVEL RECEIPT</span><span style="font-size:11px;color:#aaa;"><?= $billDate ?></span></div>
        <div style="font-size:11px;color:#aaa;">Receipt#: <b style="color:#fff;"><?= htmlspecialchars($bill['bill_number']) ?></b></div>
    </div>
    <div style="padding:10px 16px;background:#222;border-bottom:1px solid #444;">
        <div style="font-size:10px;color:#f5a623;text-transform:uppercase;letter-spacing:.1em;">Passenger</div>
        <div style="font-size:14px;font-weight:700;"><?= htmlspecialchars($bill['to_name']) ?></div>
        <?php if ($bill['to_phone']): ?><div style="font-size:11px;color:#aaa;">📞 <?= htmlspecialchars($bill['to_phone']) ?></div><?php endif; ?>
        <?php if ($bill['to_address']): ?><div style="font-size:10px;color:#aaa;">From: <?= htmlspecialchars(str_replace("\n",' ',$bill['to_address'])) ?></div><?php endif; ?>
    </div>
    <div style="padding:10px 16px;">
        <?php foreach ($items as $item): ?>
        <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #333;font-size:12px;">
            <span><?= htmlspecialchars($item['description']??'-') ?></span>
            <span style="font-weight:700;"><?= $sym.number_format((float)($item['amount']??0),2) ?></span>
        </div>
        <?php endforeach; ?>
        <?php if ($taxPct > 0): ?><div style="display:flex;justify-content:space-between;padding:4px 0;font-size:11px;color:#aaa;"><span>Tax <?= $taxPct ?>%</span><span><?= $sym.number_format($taxAmt,2) ?></span></div><?php endif; ?>
        <?php if ($discount > 0): ?><div style="display:flex;justify-content:space-between;padding:4px 0;font-size:11px;color:#aaa;"><span>Discount</span><span>-<?= $sym.number_format($discount,2) ?></span></div><?php endif; ?>
    </div>
    <div style="background:#f5a623;color:#1a1a1a;padding:12px 16px;display:flex;justify-content:space-between;font-size:16px;font-weight:900;">
        <span>FARE TOTAL</span><span><?= $sym.number_format($total,2) ?></span>
    </div>
    <?php if ($bill['notes']): ?><div style="padding:8px 16px;font-size:10px;color:#aaa;border-top:1px solid #444;"><?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
    <div style="text-align:center;padding:8px;font-size:9px;color:#666;">Safe Journey! | BillX</div>
</div>

<?php elseif ($group === 'official'): ?>
<!-- ============================================================
     OFFICIAL RECEIPT  (rent, lta)
     ============================================================ -->
<div style="font-family:Georgia,serif;background:#fff;padding:24px 28px;font-size:12px;color:#222;border:2px solid <?= htmlspecialchars($c) ?>;max-width:600px;margin:0 auto;position:relative;box-shadow:0 2px 12px rgba(0,0,0,.1);">
    <div style="position:absolute;top:10px;right:12px;font-size:64px;color:<?= htmlspecialchars($c) ?>;opacity:.06;font-weight:900;pointer-events:none;user-select:none;">ORIGINAL</div>
    <div style="text-align:center;margin-bottom:12px;">
        <div style="font-size:11px;letter-spacing:3px;text-transform:uppercase;color:<?= htmlspecialchars($c) ?>;font-weight:700;">— Official —</div>
        <div style="font-size:22px;font-weight:900;letter-spacing:1px;color:<?= htmlspecialchars($c) ?>;"><?= strtoupper(htmlspecialchars($typeLabel)) ?></div>
        <div style="font-size:16px;font-weight:700;"><?= htmlspecialchars($bill['from_name']) ?></div>
        <?php if ($bill['from_address']): ?><div style="font-size:11px;color:#555;"><?= htmlspecialchars(str_replace("\n",' | ',$bill['from_address'])) ?></div><?php endif; ?>
        <?php if ($bill['from_phone']): ?><div style="font-size:11px;color:#555;">📞 <?= htmlspecialchars($bill['from_phone']) ?></div><?php endif; ?>
    </div>
    <div style="border-top:3px double <?= htmlspecialchars($c) ?>;border-bottom:3px double <?= htmlspecialchars($c) ?>;padding:8px 0;margin-bottom:12px;">
        <div style="display:flex;justify-content:space-between;"><span>Receipt No.: <b><?= htmlspecialchars($bill['bill_number']) ?></b></span><span>Date: <b><?= $billDate ?></b></span></div>
    </div>
    <div style="margin-bottom:12px;padding:10px;background:#f9fdf9;border-left:4px solid <?= htmlspecialchars($c) ?>;">
        <div style="font-size:11px;color:#666;margin-bottom:4px;">Received with thanks from:</div>
        <div style="font-size:15px;font-weight:700;"><?= htmlspecialchars($bill['to_name']) ?></div>
        <?php if ($bill['to_address']): ?><div style="font-size:11px;color:#555;"><?= htmlspecialchars(str_replace("\n",', ',$bill['to_address'])) ?></div><?php endif; ?>
        <?php if ($bill['to_phone']): ?><div style="font-size:11px;color:#555;">Contact: <?= htmlspecialchars($bill['to_phone']) ?></div><?php endif; ?>
    </div>
    <div style="margin-bottom:8px;font-size:11px;color:#555;text-transform:uppercase;letter-spacing:.05em;font-weight:600;">Particulars</div>
    <?php foreach ($items as $item): ?>
    <div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px dotted #bbb;font-size:12px;">
        <span><?= htmlspecialchars($item['description']??'-') ?></span>
        <span style="font-weight:600;"><?= $sym.number_format((float)($item['amount']??0),2) ?></span>
    </div>
    <?php endforeach; ?>
    <div style="margin-top:12px;border-top:1px solid #ccc;padding-top:8px;">
        <?php if ($taxPct > 0): ?><div style="display:flex;justify-content:space-between;font-size:11px;color:#666;margin-bottom:2px;"><span>Tax <?= $taxPct ?>%</span><span><?= $sym.number_format($taxAmt,2) ?></span></div><?php endif; ?>
        <?php if ($discount > 0): ?><div style="display:flex;justify-content:space-between;font-size:11px;color:#666;margin-bottom:2px;"><span>Discount</span><span>-<?= $sym.number_format($discount,2) ?></span></div><?php endif; ?>
        <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:900;color:<?= htmlspecialchars($c) ?>;border-top:2px solid <?= htmlspecialchars($c) ?>;padding-top:6px;margin-top:4px;">
            <span>Total Amount Received</span><span><?= $sym.number_format($total,2) ?></span>
        </div>
    </div>
    <?php if ($bill['notes']): ?><div style="margin-top:10px;font-size:11px;color:#555;font-style:italic;border-top:1px dashed #ccc;padding-top:8px;">Note: <?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
    <div style="margin-top:20px;display:flex;justify-content:space-between;font-size:11px;">
        <div><div style="border-top:1px solid #555;width:130px;padding-top:4px;margin-top:30px;">Receiver's Signature</div></div>
        <div style="text-align:right;"><div style="border-top:1px solid #555;width:130px;padding-top:4px;margin-top:30px;">Issuer's Signature</div></div>
    </div>
    <div style="text-align:center;font-size:9px;color:#aaa;margin-top:12px;border-top:1px dashed #ddd;padding-top:6px;">Generated by BillX | This is a computer-generated receipt</div>
</div>

<?php elseif ($group === 'medical'): ?>
<!-- ============================================================
     MEDICAL BILL
     ============================================================ -->
<div style="font-family:Arial,sans-serif;background:#fff;font-size:12px;color:#222;border:1px solid #c8e6ff;box-shadow:0 2px 12px rgba(0,0,0,.1);">
    <div style="background:#0077b6;color:#fff;padding:14px 20px;display:flex;justify-content:space-between;align-items:center;">
        <div>
            <div style="font-size:20px;font-weight:900;">🏥 <?= htmlspecialchars($bill['from_name']) ?></div>
            <?php if ($bill['from_address']): ?><div style="font-size:10px;opacity:.85;"><?= htmlspecialchars(str_replace("\n",' | ',$bill['from_address'])) ?></div><?php endif; ?>
            <div style="font-size:10px;opacity:.85;"><?= $bill['from_phone']?'📞 '.htmlspecialchars($bill['from_phone']).'  ':'' ?><?= $bill['from_email']?'✉ '.htmlspecialchars($bill['from_email']):'' ?></div>
        </div>
        <div style="text-align:center;">
            <div style="width:48px;height:48px;background:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 4px;">
                <span style="color:#0077b6;font-size:24px;font-weight:900;">✚</span>
            </div>
            <div style="font-size:9px;opacity:.85;letter-spacing:1px;">MEDICAL BILL</div>
        </div>
    </div>
    <div style="background:#e3f2fd;padding:10px 20px;display:flex;justify-content:space-between;border-bottom:2px solid #0077b6;">
        <div>
            <span style="font-size:10px;color:#0077b6;display:block;text-transform:uppercase;letter-spacing:.05em;">Patient Name</span>
            <span style="font-size:14px;font-weight:700;"><?= htmlspecialchars($bill['to_name']) ?></span>
            <?php if ($bill['to_phone']): ?><span style="font-size:10px;color:#555;display:block;">📞 <?= htmlspecialchars($bill['to_phone']) ?></span><?php endif; ?>
        </div>
        <div style="text-align:right;">
            <span style="font-size:10px;color:#0077b6;display:block;text-transform:uppercase;letter-spacing:.05em;">Bill Info</span>
            <span style="font-size:12px;font-weight:600;display:block;">Bill # <?= htmlspecialchars($bill['bill_number']) ?></span>
            <span style="font-size:12px;">Date: <?= $billDate ?></span>
        </div>
    </div>
    <table style="width:100%;border-collapse:collapse;">
        <thead><tr style="background:#0077b6;color:#fff;"><th style="padding:7px 10px;text-align:left;font-size:11px;">Service / Medication</th><th style="padding:7px 10px;text-align:center;font-size:11px;width:50px;">Qty</th><th style="padding:7px 10px;text-align:right;font-size:11px;width:80px;">Rate</th><th style="padding:7px 10px;text-align:right;font-size:11px;width:80px;">Amount</th></tr></thead>
        <tbody>
            <?php foreach ($items as $i => $item): ?>
            <tr style="background:<?= $i%2===0?'#f0f7ff':'#fff' ?>;">
                <td style="padding:6px 10px;font-size:12px;"><?= htmlspecialchars($item['description']??'-') ?></td>
                <td style="padding:6px 10px;text-align:center;font-size:12px;"><?= (float)($item['qty']??1) ?></td>
                <td style="padding:6px 10px;text-align:right;font-size:12px;"><?= $sym.number_format((float)($item['rate']??0),2) ?></td>
                <td style="padding:6px 10px;text-align:right;font-weight:700;font-size:12px;"><?= $sym.number_format((float)($item['amount']??0),2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div style="padding:10px 20px;background:#f5f5f5;border-top:1px solid #c8e6ff;">
        <div style="display:flex;justify-content:flex-end;"><div style="min-width:220px;">
            <?php if ($subtotal !== $total): ?><div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;"><span style="color:#666;">Subtotal</span><span><?= $sym.number_format($subtotal,2) ?></span></div><?php endif; ?>
            <?php if ($taxPct > 0): ?><div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;"><span style="color:#666;">Tax <?= $taxPct ?>%</span><span><?= $sym.number_format($taxAmt,2) ?></span></div><?php endif; ?>
            <?php if ($discount > 0): ?><div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;"><span style="color:#666;">Discount</span><span style="color:#e53935;">-<?= $sym.number_format($discount,2) ?></span></div><?php endif; ?>
            <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:900;border-top:2px solid #0077b6;padding-top:6px;margin-top:4px;color:#0077b6;"><span>Total</span><span><?= $sym.number_format($total,2) ?></span></div>
        </div></div>
    </div>
    <?php if ($bill['notes']): ?><div style="padding:8px 20px;font-size:11px;color:#555;border-top:1px solid #e0e0e0;background:#fafafa;"><?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
    <div style="background:#0077b6;color:#fff;padding:8px 20px;display:flex;justify-content:space-between;font-size:10px;"><span>Get well soon! 🙏</span><span>Powered by BillX</span></div>
</div>

<?php elseif ($group === 'hotel'): ?>
<!-- ============================================================
     HOTEL FOLIO
     ============================================================ -->
<div style="font-family:Georgia,serif;background:#fffdf5;font-size:12px;color:#333;border:1px solid #c9a84c;box-shadow:0 4px 20px rgba(0,0,0,.15);">
    <div style="background:linear-gradient(135deg,#7d5a00,#c9a84c);color:#fff;padding:20px 24px;text-align:center;">
        <div style="font-size:10px;letter-spacing:4px;text-transform:uppercase;opacity:.8;margin-bottom:4px;">★ ★ ★</div>
        <div style="font-size:22px;font-weight:700;letter-spacing:1px;"><?= htmlspecialchars($bill['from_name']) ?></div>
        <?php if ($bill['from_address']): ?><div style="font-size:10px;opacity:.85;margin-top:4px;"><?= htmlspecialchars(str_replace("\n",' | ',$bill['from_address'])) ?></div><?php endif; ?>
        <?php if ($bill['from_phone']): ?><div style="font-size:10px;opacity:.85;">📞 <?= htmlspecialchars($bill['from_phone']) ?></div><?php endif; ?>
        <div style="font-size:11px;letter-spacing:3px;text-transform:uppercase;margin-top:8px;opacity:.9;">— Hotel Folio —</div>
    </div>
    <div style="background:#fdf0c0;padding:10px 24px;border-bottom:2px solid #c9a84c;display:flex;justify-content:space-between;">
        <div>
            <span style="font-size:10px;color:#7d5a00;display:block;text-transform:uppercase;letter-spacing:.05em;">Guest Name</span>
            <span style="font-size:14px;font-weight:700;"><?= htmlspecialchars($bill['to_name']) ?></span>
            <?php if ($bill['to_phone']): ?><span style="font-size:10px;color:#7d5a00;display:block;">📞 <?= htmlspecialchars($bill['to_phone']) ?></span><?php endif; ?>
        </div>
        <div style="text-align:right;">
            <span style="font-size:10px;color:#7d5a00;display:block;text-transform:uppercase;letter-spacing:.05em;">Folio Details</span>
            <span style="font-size:12px;display:block;">Folio #: <b><?= htmlspecialchars($bill['bill_number']) ?></b></span>
            <span style="font-size:12px;">Date: <?= $billDate ?></span>
        </div>
    </div>
    <table style="width:100%;border-collapse:collapse;">
        <thead><tr style="background:#c9a84c;color:#fff;"><th style="padding:8px 12px;text-align:left;font-size:11px;">Description / Service</th><th style="padding:8px 12px;text-align:center;font-size:11px;width:70px;">Nights/Qty</th><th style="padding:8px 12px;text-align:right;font-size:11px;width:80px;">Rate</th><th style="padding:8px 12px;text-align:right;font-size:11px;width:90px;">Amount</th></tr></thead>
        <tbody>
            <?php foreach ($items as $i => $item): ?>
            <tr style="background:<?= $i%2===0?'#fdf8ee':'#fff' ?>;border-bottom:1px solid #e8d89a;">
                <td style="padding:8px 12px;font-size:12px;"><?= htmlspecialchars($item['description']??'-') ?></td>
                <td style="padding:8px 12px;text-align:center;font-size:12px;"><?= (float)($item['qty']??1) ?></td>
                <td style="padding:8px 12px;text-align:right;font-size:12px;"><?= $sym.number_format((float)($item['rate']??0),2) ?></td>
                <td style="padding:8px 12px;text-align:right;font-weight:700;font-size:12px;"><?= $sym.number_format((float)($item['amount']??0),2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div style="padding:10px 24px;background:#fdf8ee;border-top:1px solid #e8d89a;">
        <div style="display:flex;justify-content:flex-end;"><div style="min-width:220px;">
            <?php if ($subtotal !== $total): ?><div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-bottom:1px solid #e8d89a;"><span style="color:#777;">Subtotal</span><span><?= $sym.number_format($subtotal,2) ?></span></div><?php endif; ?>
            <?php if ($taxPct > 0): ?><div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-bottom:1px solid #e8d89a;"><span style="color:#777;">Tax/Service <?= $taxPct ?>%</span><span><?= $sym.number_format($taxAmt,2) ?></span></div><?php endif; ?>
            <?php if ($discount > 0): ?><div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-bottom:1px solid #e8d89a;"><span style="color:#777;">Discount</span><span style="color:#e53935;">-<?= $sym.number_format($discount,2) ?></span></div><?php endif; ?>
            <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;border-top:2px solid #c9a84c;padding-top:6px;margin-top:4px;color:#7d5a00;"><span>Total Amount</span><span><?= $sym.number_format($total,2) ?></span></div>
        </div></div>
    </div>
    <?php if ($bill['notes']): ?><div style="padding:8px 24px;font-size:11px;color:#7d5a00;font-style:italic;border-top:1px solid #e8d89a;"><?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
    <div style="background:linear-gradient(135deg,#7d5a00,#c9a84c);color:#fff;padding:10px 24px;display:flex;justify-content:space-between;font-size:10px;opacity:.9;"><span>Thank you for your stay! 🌟</span><span>Powered by BillX</span></div>
</div>

<?php elseif ($group === 'gym'): ?>
<!-- ============================================================
     GYM INVOICE
     ============================================================ -->
<div style="font-family:Arial,sans-serif;background:#181818;color:#f0f0f0;font-size:12px;box-shadow:0 4px 20px rgba(0,0,0,.4);">
    <div style="background:linear-gradient(135deg,#212121,#333);padding:16px 20px;border-bottom:3px solid #ff6f00;">
        <div style="display:flex;justify-content:space-between;align-items:center;">
            <div>
                <div style="font-size:22px;font-weight:900;letter-spacing:-0.5px;">💪 <?= htmlspecialchars($bill['from_name']) ?></div>
                <?php if ($bill['from_address']): ?><div style="font-size:10px;color:#aaa;margin-top:2px;"><?= htmlspecialchars(str_replace("\n",' | ',$bill['from_address'])) ?></div><?php endif; ?>
                <?php if ($bill['from_phone']): ?><div style="font-size:10px;color:#aaa;">📞 <?= htmlspecialchars($bill['from_phone']) ?></div><?php endif; ?>
            </div>
            <div style="text-align:right;">
                <div style="font-size:13px;font-weight:900;color:#ff6f00;letter-spacing:2px;">GYM INVOICE</div>
                <div style="font-size:10px;color:#aaa;">Inv # <?= htmlspecialchars($bill['bill_number']) ?></div>
                <div style="font-size:10px;color:#aaa;"><?= $billDate ?></div>
            </div>
        </div>
    </div>
    <div style="padding:10px 20px;background:#242424;border-bottom:1px solid #444;">
        <span style="font-size:10px;color:#ff6f00;display:block;text-transform:uppercase;letter-spacing:.1em;">Member</span>
        <span style="font-size:14px;font-weight:700;"><?= htmlspecialchars($bill['to_name']) ?></span>
        <?php if ($bill['to_phone']): ?><span style="font-size:11px;color:#aaa;display:block;">📞 <?= htmlspecialchars($bill['to_phone']) ?></span><?php endif; ?>
    </div>
    <div style="padding:12px 20px;border-bottom:1px solid #444;">
        <?php foreach ($items as $item): ?>
        <div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #333;font-size:12px;">
            <span><?= htmlspecialchars($item['description']??'-') ?></span>
            <span style="color:#ff6f00;font-weight:700;"><?= $sym.number_format((float)($item['amount']??0),2) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <div style="padding:10px 20px;background:#242424;">
        <?php if ($taxPct > 0): ?><div style="display:flex;justify-content:space-between;font-size:11px;color:#aaa;padding:2px 0;"><span>Tax <?= $taxPct ?>%</span><span><?= $sym.number_format($taxAmt,2) ?></span></div><?php endif; ?>
        <?php if ($discount > 0): ?><div style="display:flex;justify-content:space-between;font-size:11px;color:#aaa;padding:2px 0;"><span>Discount</span><span>-<?= $sym.number_format($discount,2) ?></span></div><?php endif; ?>
    </div>
    <div style="background:#ff6f00;color:#fff;padding:12px 20px;display:flex;justify-content:space-between;font-size:16px;font-weight:900;letter-spacing:-0.5px;">
        <span>TOTAL DUE</span><span><?= $sym.number_format($total,2) ?></span>
    </div>
    <?php if ($bill['notes']): ?><div style="padding:8px 20px;font-size:10px;color:#aaa;border-top:1px solid #444;"><?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
    <div style="text-align:center;padding:8px;font-size:9px;color:#555;background:#111;">Stay strong! 💪 | BillX</div>
</div>

<?php else: ?>
<!-- ============================================================
     STANDARD PROFESSIONAL INVOICE  (book, internet, ecom, general, stationary)
     ============================================================ -->
<div style="font-family:Arial,sans-serif;background:#fff;font-size:12px;color:#222;border:1px solid #ddd;box-shadow:0 2px 12px rgba(0,0,0,.1);">
    <div style="background:<?= htmlspecialchars($c) ?>;color:#fff;padding:20px 24px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <div style="font-size:22px;font-weight:700;"><?= htmlspecialchars($bill['from_name']) ?></div>
                <?php if ($bill['from_address']): ?><div style="font-size:10px;opacity:.85;margin-top:4px;"><?= htmlspecialchars(str_replace("\n",' | ',$bill['from_address'])) ?></div><?php endif; ?>
                <div style="font-size:10px;opacity:.85;"><?= $bill['from_phone']?'📞 '.htmlspecialchars($bill['from_phone']).'  ':'' ?><?= $bill['from_email']?'✉ '.htmlspecialchars($bill['from_email']):'' ?></div>
            </div>
            <div style="text-align:right;">
                <div style="font-size:26px;font-weight:900;letter-spacing:-1px;opacity:.9;">INVOICE</div>
                <div style="font-size:11px;opacity:.85;"># <?= htmlspecialchars($bill['bill_number']) ?></div>
                <div style="font-size:11px;opacity:.85;">Date: <?= $billDate ?></div>
                <div style="font-size:10px;margin-top:4px;background:rgba(255,255,255,.2);padding:2px 8px;border-radius:4px;"><?= htmlspecialchars($typeLabel) ?></div>
            </div>
        </div>
    </div>
    <div style="padding:12px 24px;background:#f7f7f7;border-bottom:2px solid <?= htmlspecialchars($c) ?>;display:flex;justify-content:space-between;">
        <div>
            <span style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:.05em;display:block;">Bill To</span>
            <span style="font-size:14px;font-weight:700;"><?= htmlspecialchars($bill['to_name']) ?></span>
            <?php if ($bill['to_address']): ?><span style="font-size:11px;color:#555;display:block;"><?= htmlspecialchars(str_replace("\n",' | ',$bill['to_address'])) ?></span><?php endif; ?>
            <?php if ($bill['to_phone']): ?><span style="font-size:11px;color:#555;">📞 <?= htmlspecialchars($bill['to_phone']) ?></span><?php endif; ?>
        </div>
        <?php if ($bill['to_email']): ?><div style="text-align:right;"><span style="font-size:11px;color:#555;">✉ <?= htmlspecialchars($bill['to_email']) ?></span></div><?php endif; ?>
    </div>
    <table style="width:100%;border-collapse:collapse;">
        <thead><tr style="background:<?= htmlspecialchars($c) ?>;color:#fff;"><th style="padding:8px 10px;text-align:center;font-size:11px;width:30px;">#</th><th style="padding:8px 10px;text-align:left;font-size:11px;">Description</th><th style="padding:8px 10px;text-align:center;font-size:11px;width:50px;">Qty</th><th style="padding:8px 10px;text-align:right;font-size:11px;width:80px;">Unit Price</th><th style="padding:8px 10px;text-align:right;font-size:11px;width:90px;">Amount</th></tr></thead>
        <tbody>
            <?php foreach ($items as $i => $item): ?>
            <tr style="background:<?= $i%2===0?'#fafafa':'#fff' ?>;">
                <td style="padding:7px 10px;font-size:12px;border-bottom:1px solid #eee;text-align:center;"><?= $i+1 ?></td>
                <td style="padding:7px 10px;font-size:12px;border-bottom:1px solid #eee;"><?= htmlspecialchars($item['description']??'-') ?></td>
                <td style="padding:7px 10px;text-align:center;font-size:12px;border-bottom:1px solid #eee;"><?= (float)($item['qty']??1) ?></td>
                <td style="padding:7px 10px;text-align:right;font-size:12px;border-bottom:1px solid #eee;"><?= $sym.number_format((float)($item['rate']??0),2) ?></td>
                <td style="padding:7px 10px;text-align:right;font-weight:700;font-size:12px;border-bottom:1px solid #eee;"><?= $sym.number_format((float)($item['amount']??0),2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div style="padding:12px 24px 16px;display:flex;justify-content:flex-end;background:#fafafa;border-top:1px solid #eee;">
        <div style="min-width:240px;">
            <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:12px;border-bottom:1px solid #eee;"><span style="color:#666;">Subtotal</span><span style="font-weight:600;"><?= $sym.number_format($subtotal,2) ?></span></div>
            <?php if ($taxPct > 0): ?><div style="display:flex;justify-content:space-between;padding:4px 0;font-size:12px;border-bottom:1px solid #eee;"><span style="color:#666;">Tax <?= $taxPct ?>%</span><span style="font-weight:600;"><?= $sym.number_format($taxAmt,2) ?></span></div><?php endif; ?>
            <?php if ($discount > 0): ?><div style="display:flex;justify-content:space-between;padding:4px 0;font-size:12px;border-bottom:1px solid #eee;"><span style="color:#666;">Discount</span><span style="font-weight:600;color:#e53935;">-<?= $sym.number_format($discount,2) ?></span></div><?php endif; ?>
            <div style="display:flex;justify-content:space-between;padding:8px 0 4px;font-size:16px;font-weight:900;border-top:2px solid <?= htmlspecialchars($c) ?>;color:<?= htmlspecialchars($c) ?>;"><span>Total</span><span><?= $sym.number_format($total,2) ?></span></div>
        </div>
    </div>
    <?php if ($bill['notes']): ?><div style="margin:0 24px 16px;background:#f5f5f5;border-radius:4px;padding:10px;font-size:11px;color:#555;border-left:3px solid <?= htmlspecialchars($c) ?>;"><b>Terms & Notes:</b> <?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
    <div style="background:<?= htmlspecialchars($c) ?>;color:rgba(255,255,255,.7);padding:8px 24px;text-align:center;font-size:10px;">Thank you for your business! | Generated by BillX</div>
</div>

<?php endif; ?>

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
@media print {
    .back-link, .btn, #deleteModal, .billx-sidebar, .sidebar-toggle, nav { display: none !important; }
    .billx-main { margin-left: 0 !important; padding: 0 !important; }
    #billDocWrapper { background: white !important; padding: 0 !important; }
}
</style>
