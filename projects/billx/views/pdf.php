<?php
/** @var array $bill @var array $config */
$typeLabel = $config['bill_types'][$bill['bill_type']] ?? ucfirst($bill['bill_type']);
$sym = ['INR'=>'₹','USD'=>'$','EUR'=>'€','GBP'=>'£'][$bill['currency']] ?? $bill['currency'].' ';
$group = $config['bill_groups'][$bill['bill_type']] ?? 'invoice';
$c     = $config['bill_colors'][$bill['bill_type']] ?? '#37474f';
$items    = $bill['items'];
$subtotal = (float)$bill['subtotal'];
$taxPct   = (float)$bill['tax_percent'];
$taxAmt   = (float)$bill['tax_amount'];
$discount = (float)$bill['discount_amount'];
$total    = (float)$bill['total_amount'];
$billDate = $bill['bill_date'] ? date('d M Y', strtotime($bill['bill_date'])) : '';
$td = json_decode($bill['template_data'] ?? '{}', true) ?: [];
?><!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($typeLabel) ?> - <?= htmlspecialchars($bill['bill_number']) ?></title>
<style>
@page { size: <?= in_array($group,['thermal']) ? '80mm auto' : 'A4 portrait' ?>; margin: <?= $group==='thermal' ? '4mm 3mm' : '15mm' ?>; }
* { box-sizing: border-box; }
body { margin: 0; padding: 0; background: #fff; }
@media screen { body { background: #f5f5f5; padding: 20px; } }
.print-btn { position: fixed; top: 12px; right: 12px; padding: 8px 20px; background: #f59e0b; color: #fff; border: none; border-radius: 6px; font-size: 14px; cursor: pointer; font-weight: 600; }
@media print { .print-btn { display: none; } }
</style>
</head>
<body>
<button class="print-btn" onclick="window.print()">🖨 Save as PDF</button>
<div id="billDocument" style="max-width:<?= $group==='thermal'?'340px':'700px' ?>;margin:0 auto;">
<?php if ($group === 'thermal'): ?>
<!-- ============================================================
     THERMAL / POS RECEIPT  (restaurant, recharge, mart, newspaper)
     ============================================================ -->
<?php
    $cgstPct  = (float)($td['cgst_pct'] ?? 0);
    $sgstPct  = (float)($td['sgst_pct'] ?? 0);
    $cgstAmt  = round($subtotal * $cgstPct / 100, 2);
    $sgstAmt  = round($subtotal * $sgstPct / 100, 2);
    $tableNo  = $td['table_number'] ?? '';
    $payMode  = $td['payment_mode'] ?? '';
    $billTime = $bill['created_at'] ? date('H:i', strtotime($bill['created_at'])) : date('H:i');
?>
<div style="font-family:'Courier New',Courier,monospace;background:#fff;max-width:340px;margin:0 auto;padding:14px 18px;font-size:11px;color:#111;border:1px solid #ccc;box-shadow:1px 2px 8px rgba(0,0,0,.15);">
    <div style="border-top:1px dashed #888;margin:4px 0;"></div>
    <div style="text-align:center;letter-spacing:6px;font-size:11px;font-weight:700;margin:2px 0;">RECEIPT</div>
    <div style="border-top:1px dashed #888;margin:4px 0;"></div>
    <div style="display:flex;justify-content:space-between;margin-bottom:2px;">
        <span>Name: <b><?= htmlspecialchars($bill['to_name']) ?></b></span>
        <span>Invoice No: <b><?= htmlspecialchars($bill['bill_number']) ?></b></span>
    </div>
    <?php if ($tableNo): ?>
    <div style="display:flex;justify-content:space-between;">
        <span>Table: <b>#<?= htmlspecialchars($tableNo) ?></b></span>
        <span>Date: <?= $billDate ?></span>
    </div>
    <?php else: ?><div style="font-size:11px;">Date: <?= $billDate ?></div><?php endif; ?>
    <div style="font-size:10px;margin-top:2px;"><?= htmlspecialchars($bill['from_name']) ?><?= $bill['from_phone'] ? ' | ' . htmlspecialchars($bill['from_phone']) : '' ?></div>
    <div style="border-top:1px dashed #888;margin:6px 0;"></div>
    <table style="width:100%;border-collapse:collapse;">
        <thead><tr style="border-bottom:1px dashed #555;">
            <th style="padding:2px 3px;text-align:left;font-size:10px;">Item</th>
            <th style="padding:2px 3px;text-align:right;font-size:10px;">Price</th>
            <th style="padding:2px 3px;text-align:center;font-size:10px;">Qty</th>
            <th style="padding:2px 3px;text-align:right;font-size:10px;">Total</th>
        </tr></thead>
        <tbody>
        <?php foreach ($items as $item): ?>
        <tr>
            <td style="padding:2px 3px;font-size:11px;"><?= htmlspecialchars($item['description'] ?? '-') ?></td>
            <td style="padding:2px 3px;text-align:right;font-size:11px;"><?= $sym ?><?= number_format((float)($item['rate']??0),2) ?></td>
            <td style="padding:2px 3px;text-align:center;font-size:11px;"><?= (float)($item['qty']??1) ?></td>
            <td style="padding:2px 3px;text-align:right;font-weight:700;font-size:11px;"><?= $sym ?><?= number_format((float)($item['amount']??0),2) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div style="border-top:1px dashed #888;margin:6px 0;"></div>
    <div style="display:flex;justify-content:space-between;padding:1px 0;"><span>Sub-Total:</span><span><?= $sym ?><?= number_format($subtotal,2) ?></span></div>
    <?php if ($cgstPct > 0): ?>
    <div style="display:flex;justify-content:space-between;padding:1px 0;"><span>CGST: <?= $cgstPct ?>%</span><span><?= $sym ?><?= number_format($cgstAmt,2) ?></span></div>
    <?php endif; ?>
    <?php if ($sgstPct > 0): ?>
    <div style="display:flex;justify-content:space-between;padding:1px 0;"><span>SGST: <?= $sgstPct ?>%</span><span><?= $sym ?><?= number_format($sgstAmt,2) ?></span></div>
    <?php endif; ?>
    <?php if ($taxPct > 0 && $cgstPct == 0 && $sgstPct == 0): ?>
    <div style="display:flex;justify-content:space-between;padding:1px 0;"><span>Tax <?= $taxPct ?>%</span><span><?= $sym ?><?= number_format($taxAmt,2) ?></span></div>
    <?php endif; ?>
    <?php if ($discount > 0): ?>
    <div style="display:flex;justify-content:space-between;padding:1px 0;"><span>Discount</span><span>-<?= $sym ?><?= number_format($discount,2) ?></span></div>
    <?php endif; ?>
    <div style="border-top:1px dashed #888;margin:6px 0;"></div>
    <div style="display:flex;justify-content:space-between;align-items:center;">
        <span>Mode: <b><?= htmlspecialchars($payMode ?: '-') ?></b></span>
        <span style="font-size:13px;font-weight:900;">Total: <?= $sym ?><?= number_format($total,2) ?></span>
    </div>
    <div style="border-top:1px dashed #888;margin:6px 0;"></div>
    <?php if ($bill['notes']): ?><div style="font-size:10px;color:#555;text-align:center;margin-bottom:3px;"><?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
    <div style="text-align:center;font-size:11px;font-weight:700;margin-top:5px;">** SAVE PAPER SAVE NATURE !!</div>
    <div style="text-align:center;font-size:10px;color:#666;margin-top:2px;">Time: <?= $billTime ?></div>
    <div style="border-top:1px dashed #888;margin:6px 0;"></div>
    <div style="text-align:center;font-size:9px;color:#888;">Powered by BillX</div>
</div>

<?php elseif ($group === 'payslip'): ?>
<!-- ============================================================
     DRIVER SALARY / HELPER — formal letter style
     ============================================================ -->
<?php
    $vehicleNo   = $td['vehicle_number'] ?? '';
    $empName     = $td['employer_name']  ?? $bill['from_name'];
    $salaryMonth = $td['salary_month']   ?? date('F Y', strtotime($bill['bill_date']));
    $designation = $td['designation']    ?? 'Driver';
?>
<div style="font-family:Arial,sans-serif;background:#fff;padding:24px 28px;font-size:12px;color:#111;border:1px solid #ccc;max-width:560px;margin:0 auto;line-height:1.7;">
    <div style="text-align:right;font-size:12px;color:#333;margin-bottom:8px;">Date: <?= $billDate ?></div>
    <div style="text-align:center;font-size:14px;font-weight:700;text-decoration:underline;margin-bottom:14px;"><?= htmlspecialchars($typeLabel) ?></div>
    <p style="margin:0 0 12px;text-align:justify;">This is to certify that Mr./Ms. <b><?= htmlspecialchars($empName) ?></b> have paid <b><?= $sym.number_format($total,2) ?></b> to <?= htmlspecialchars($designation) ?> Mr/Ms <b><?= htmlspecialchars($bill['to_name']) ?></b> towards salary of the month of <b><?= htmlspecialchars($salaryMonth) ?></b> (Acknowledged receipt enclosed). I also declare that the <?= htmlspecialchars(strtolower($designation)) ?> is exclusively utilized for official purpose only</p>
    <p style="margin:0 0 16px;text-align:justify;">Please reimburse the above amount. I further declare that what is stated above is correct and true.</p>
    <?php if ($items): ?>
    <table style="width:100%;border-collapse:collapse;margin-bottom:14px;font-size:11px;">
        <thead><tr style="border-bottom:1px solid #ccc;"><th style="padding:4px 6px;text-align:left;">Description</th><th style="padding:4px 6px;text-align:right;">Amount</th></tr></thead>
        <tbody>
        <?php foreach ($items as $item): ?>
        <tr style="border-bottom:1px solid #eee;">
            <td style="padding:4px 6px;"><?= htmlspecialchars($item['description']??'-') ?></td>
            <td style="padding:4px 6px;text-align:right;font-weight:600;"><?= $sym.number_format((float)($item['amount']??0),2) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
    <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
        <div><b>Vehicle Number:</b> <?= htmlspecialchars($vehicleNo ?: '__________') ?></div>
        <div><b>Date:</b> <?= $billDate ?></div>
    </div>
    <div style="display:flex;justify-content:space-between;margin-bottom:18px;">
        <div><b><?= htmlspecialchars($designation) ?> Name:</b> <?= htmlspecialchars($bill['to_name']) ?></div>
        <div><b>Employee Name:</b> <?= htmlspecialchars($empName) ?></div>
    </div>
    <div style="font-weight:700;margin-bottom:4px;">Revenue Stamp</div>
    <div style="width:64px;height:64px;border:1px dashed #aaa;display:flex;align-items:center;justify-content:center;font-size:9px;color:#aaa;text-align:center;padding:4px;">Revenue<br>Stamp</div>
    <?php if ($bill['notes']): ?><div style="margin-top:12px;font-size:11px;color:#555;border-top:1px dashed #ddd;padding-top:8px;"><?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
    <div style="text-align:center;font-size:9px;color:#aaa;margin-top:16px;border-top:1px dashed #ddd;padding-top:6px;">* This is a computer-generated receipt | BillX</div>
</div>

<?php elseif ($group === 'fuel'): ?>
<!-- ============================================================
     FUEL RECEIPT — formal two-column layout
     ============================================================ -->
<?php
    $vehicleNo   = $td['vehicle_number'] ?? '';
    $vehicleType = $td['vehicle_type']   ?? '';
    $fuelType    = $td['fuel_type']      ?? 'Petrol';
    $payMode     = $td['payment_mode']   ?? '';
    $billTime    = $bill['created_at'] ? date('H:i', strtotime($bill['created_at'])) : date('H:i');
?>
<div style="font-family:Arial,sans-serif;background:#fff;padding:24px 28px;font-size:12px;color:#111;border:1px solid #ddd;max-width:560px;margin:0 auto;box-shadow:0 2px 8px rgba(0,0,0,.08);">
    <h2 style="font-size:22px;font-weight:700;margin:0 0 16px;border-bottom:2px solid #333;padding-bottom:8px;">Fuel Receipt</h2>
    <div style="display:flex;justify-content:space-between;margin-bottom:14px;">
        <div></div>
        <div style="text-align:right;">
            <div style="font-weight:700;font-size:12px;margin-bottom:4px;">Receipt Details</div>
            <div>Receipt Number: <b><?= htmlspecialchars($bill['bill_number']) ?></b></div>
            <div>Date: <?= $billDate ?></div>
            <div>Time: <?= $billTime ?></div>
        </div>
    </div>
    <div style="display:flex;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:10px;">
        <div>
            <div style="font-weight:700;margin-bottom:6px;font-size:12px;">Billed To</div>
            <div>Customer Name: <b><?= htmlspecialchars($bill['to_name']) ?></b></div>
            <div>Vehicle Number: <b><?= htmlspecialchars($vehicleNo) ?></b></div>
            <div>Vehicle Type: <b><?= htmlspecialchars($vehicleType) ?></b></div>
        </div>
        <div style="text-align:right;">
            <div style="font-weight:700;margin-bottom:6px;font-size:12px;">Fuel Station Details</div>
            <div>Fuel Station Name: <b><?= htmlspecialchars($bill['from_name']) ?></b></div>
            <?php if ($bill['from_address']): ?><div>Fuel Station Address: <?= htmlspecialchars(str_replace("\n",', ',$bill['from_address'])) ?></div><?php endif; ?>
            <?php if ($bill['from_phone']): ?><div>📞 <?= htmlspecialchars($bill['from_phone']) ?></div><?php endif; ?>
        </div>
    </div>
    <div style="text-align:right;font-weight:700;margin-bottom:10px;">Payment Method<?= $payMode ? ': '.htmlspecialchars($payMode) : '' ?></div>
    <div style="border:1px solid #ddd;border-radius:4px;overflow:hidden;margin-bottom:12px;">
        <div style="background:#f5f5f5;padding:6px 8px;font-weight:700;font-size:12px;border-bottom:1px solid #ddd;">Receipt Summary</div>
        <table style="width:100%;border-collapse:collapse;">
            <thead><tr style="background:#fafafa;border-bottom:1px solid #ddd;"><th style="padding:6px 8px;text-align:left;font-size:11px;">Fuel Rate</th><th style="padding:6px 8px;text-align:center;font-size:11px;">Quantity</th><th style="padding:6px 8px;text-align:right;font-size:11px;">Total Amount</th></tr></thead>
            <tbody>
            <?php foreach ($items as $item): ?>
            <tr style="border-bottom:1px solid #e0e0e0;">
                <td style="padding:6px 8px;font-size:12px;"><?= $sym ?><?= number_format((float)($item['rate']??0),2) ?></td>
                <td style="padding:6px 8px;text-align:center;font-size:12px;"><?= (float)($item['qty']??1) ?> lt.</td>
                <td style="padding:6px 8px;text-align:right;font-weight:700;font-size:12px;"><?= $sym ?><?= number_format((float)($item['amount']??0),2) ?></td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($taxPct > 0): ?><div style="text-align:right;font-size:11px;color:#666;margin-bottom:4px;">Tax <?= $taxPct ?>%: <?= $sym.number_format($taxAmt,2) ?></div><?php endif; ?>
    <div style="text-align:right;font-weight:700;font-size:13px;border-top:2px solid #333;padding-top:6px;margin-bottom:14px;">Total: <?= $sym.number_format($total,2) ?></div>
    <div style="text-align:center;border-top:1px solid #ddd;padding-top:12px;">
        <div style="font-weight:700;font-size:12px;margin-bottom:4px;">THANK YOU ! FOR FUELLING WITH US !</div>
        <div style="font-size:11px;color:#555;margin-bottom:4px;">FOR ANY QUERIES AND COMPLAINTS VISIT OUR CUSTOMER CARE</div>
        <div style="font-size:11px;font-weight:600;margin-bottom:4px;">SAVE FUEL, SECURE THE FUTURE!</div>
        <div style="font-size:11px;color:#888;">TIME: <?= $billTime ?></div>
    </div>
    <?php if ($bill['notes']): ?><div style="margin-top:10px;font-size:11px;color:#555;border-top:1px dashed #ddd;padding-top:8px;"><?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
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
        <?php if (!empty($td['pickup'])): ?><div style="font-size:11px;color:#aaa;">From: <?= htmlspecialchars($td['pickup']) ?></div><?php endif; ?>
        <?php if (!empty($td['drop'])): ?><div style="font-size:11px;color:#aaa;">To: <?= htmlspecialchars($td['drop']) ?></div><?php endif; ?>
        <?php if (!empty($td['vehicle_number'])): ?><div style="font-size:11px;color:#aaa;">Cab#: <?= htmlspecialchars($td['vehicle_number']) ?><?= !empty($td['driver_name']) ? ' | Driver: '.htmlspecialchars($td['driver_name']) : '' ?></div><?php endif; ?>
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
<?php $pan = $td['pan_number'] ?? ''; $prop = $td['property_info'] ?? ''; ?>
<div style="font-family:Georgia,serif;background:#fff;padding:24px 28px;font-size:12px;color:#222;border:2px solid <?= htmlspecialchars($c) ?>;max-width:600px;margin:0 auto;position:relative;box-shadow:0 2px 12px rgba(0,0,0,.1);">
    <div style="position:absolute;top:10px;right:12px;font-size:64px;color:<?= htmlspecialchars($c) ?>;opacity:.06;font-weight:900;pointer-events:none;user-select:none;">ORIGINAL</div>
    <div style="text-align:center;margin-bottom:12px;">
        <div style="font-size:11px;letter-spacing:3px;text-transform:uppercase;color:<?= htmlspecialchars($c) ?>;font-weight:700;">— Official —</div>
        <div style="font-size:22px;font-weight:900;letter-spacing:1px;color:<?= htmlspecialchars($c) ?>;"><?= strtoupper(htmlspecialchars($typeLabel)) ?></div>
        <div style="font-size:16px;font-weight:700;"><?= htmlspecialchars($bill['from_name']) ?></div>
        <?php if ($bill['from_address']): ?><div style="font-size:11px;color:#555;"><?= htmlspecialchars(str_replace("\n",' | ',$bill['from_address'])) ?></div><?php endif; ?>
        <?php if ($bill['from_phone']): ?><div style="font-size:11px;color:#555;">📞 <?= htmlspecialchars($bill['from_phone']) ?></div><?php endif; ?>
        <?php if ($pan): ?><div style="font-size:11px;color:#555;">PAN: <b><?= htmlspecialchars($pan) ?></b></div><?php endif; ?>
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
    <?php if ($prop): ?><div style="font-size:11px;color:#555;margin-bottom:8px;">Property / Office: <b><?= htmlspecialchars($prop) ?></b></div><?php endif; ?>
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
    <div style="text-align:center;font-size:9px;color:#aaa;margin-top:12px;border-top:1px dashed #ddd;padding-top:6px;">Generated by BillX | Computer-generated receipt</div>
</div>

<?php elseif ($group === 'medical'): ?>
<!-- ============================================================
     MEDICAL BILL — hospital invoice style
     ============================================================ -->
<?php
    $doctor    = $td['doctor_name']   ?? '';
    $patId     = $td['patient_id']    ?? '';
    $issue     = $td['patient_issue'] ?? '';
    $guardian  = $td['guardian_name'] ?? '';
    $admitDate = !empty($td['admit_date']) ? date('d M Y', strtotime($td['admit_date'])) : '';
    $roomCat   = $td['room_category'] ?? 'Single';
    $patAge    = $td['patient_age']   ?? '';
    $insurance = $td['insurance']     ?? 'Yes';
    $cgstPct   = (float)($td['cgst_pct'] ?? 0);
    $sgstPct   = (float)($td['sgst_pct'] ?? 0);
    $cgstAmt   = round($subtotal * $cgstPct / 100, 2);
    $sgstAmt   = round($subtotal * $sgstPct / 100, 2);
    $taxable   = $subtotal;
    $netAmt    = $subtotal + $cgstAmt + $sgstAmt - $discount;
    $billTime  = $bill['created_at'] ? date('H:i', strtotime($bill['created_at'])) : date('H:i');
?>
<div style="font-family:Arial,sans-serif;background:#fff;font-size:12px;color:#111;border:1px solid #ccc;max-width:600px;margin:0 auto;">
    <div style="padding:10px 14px;border-bottom:1px solid #ddd;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <div style="font-size:16px;font-weight:700;"><?= htmlspecialchars($bill['from_name']) ?></div>
                <?php if ($bill['from_address']): ?><div style="font-size:11px;color:#555;"><?= htmlspecialchars(str_replace("\n",' | ',$bill['from_address'])) ?></div><?php endif; ?>
            </div>
            <div style="text-align:right;"><div style="font-size:11px;font-weight:600;">Invoice No: <b><?= htmlspecialchars($bill['bill_number']) ?></b></div></div>
        </div>
    </div>
    <div style="padding:8px 14px;border-bottom:1px solid #ddd;">
        <div style="font-weight:700;margin-bottom:4px;">Hospital details:</div>
        <?php if ($bill['from_phone']): ?><div style="font-size:11px;">Contact Details: <?= htmlspecialchars($bill['from_phone']) ?></div><?php endif; ?>
        <div style="font-size:11px;">Discharge Date:</div>
        <div style="font-size:11px;"><?= $billDate ?></div>
    </div>
    <div style="padding:8px 14px;border-bottom:1px solid #ddd;">
        <div style="font-weight:700;margin-bottom:6px;">Patient Information</div>
        <table style="width:100%;border-collapse:collapse;font-size:11px;">
            <tr><td style="padding:3px 6px;"><b>Patient Name:</b> <?= htmlspecialchars($bill['to_name']) ?></td><td style="padding:3px 6px;"><b>Patient Issue:</b> <?= htmlspecialchars($issue) ?></td><td style="padding:3px 6px;"><b>Address:</b> <?= htmlspecialchars($bill['to_address'] ?? '') ?></td></tr>
            <tr><td style="padding:3px 6px;"><b>Guardian Name:</b> <?= htmlspecialchars($guardian) ?></td><td style="padding:3px 6px;"><b>Admit Date:</b><br><?= $admitDate ?></td><td style="padding:3px 6px;"><b>Mobile:</b> <?= htmlspecialchars($bill['to_phone'] ?? '') ?></td></tr>
            <tr><td style="padding:3px 6px;"><b>Insurance Avl:</b><br><?= htmlspecialchars($insurance) ?></td><td style="padding:3px 6px;"><b>Age:</b> <?= htmlspecialchars($patAge) ?></td><td></td></tr>
            <tr><td style="padding:3px 6px;"><b>Consultant:</b> <?= htmlspecialchars($doctor) ?></td><td style="padding:3px 6px;"><b>Room Category:</b><br><?= htmlspecialchars($roomCat) ?></td><td style="padding:3px 6px;"><?= $patId ? '<b>Patient ID:</b> '.htmlspecialchars($patId) : '' ?></td></tr>
        </table>
    </div>
    <table style="width:100%;border-collapse:collapse;font-size:12px;">
        <thead><tr style="background:#f5f5f5;border-bottom:1px solid #ccc;border-top:1px solid #ccc;"><th style="padding:6px 8px;text-align:left;">Details</th><th style="padding:6px 8px;text-align:right;width:80px;">Price</th><th style="padding:6px 8px;text-align:right;width:80px;">Amount</th></tr></thead>
        <tbody>
        <?php foreach ($items as $item): ?>
        <tr style="border-bottom:1px solid #eee;">
            <td style="padding:5px 8px;"><?= htmlspecialchars($item['description']??'-') ?></td>
            <td style="padding:5px 8px;text-align:right;"><?= $sym.number_format((float)($item['rate']??0),2) ?></td>
            <td style="padding:5px 8px;text-align:right;font-weight:600;"><?= $sym.number_format((float)($item['amount']??0),2) ?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <div style="padding:8px 14px;border-top:1px solid #ddd;display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;">
        <div>
            <div style="font-weight:700;margin-bottom:4px;">Pay By</div>
            <div style="font-size:11px;">Amount: <?= $sym.number_format($total,2) ?></div>
        </div>
        <div style="text-align:right;font-size:11px;">
            <div>Tax: <?= $taxPct ?>%</div>
            <div>CGST: <?= $cgstPct ?>% - <?= $sym.number_format($cgstAmt,2) ?></div>
            <div>SGST: <?= $sgstPct ?>% - <?= $sym.number_format($sgstAmt,2) ?></div>
            <div><b>Taxable Amount: <?= $sym.number_format($taxable,2) ?></b></div>
            <div><b>Net Amount: <?= number_format($netAmt,2) ?></b></div>
            <div><b>Total Amount: <?= $sym.number_format($total,2) ?></b></div>
        </div>
    </div>
    <?php if ($bill['notes']): ?><div style="padding:8px 14px;border-top:1px solid #ddd;font-size:11px;"><b>Remark:</b><br><?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
    <div style="padding:8px 14px;border-top:1px solid #ccc;font-size:10px;color:#555;font-style:italic;">* This is a computer-generated invoice. Signature not required. Created on <?= $billDate ?> at <?= $billTime ?>.</div>
</div>

<?php elseif ($group === 'hotel'): ?>
<!-- ============================================================
     HOTEL FOLIO
     ============================================================ -->
<?php
    $roomNo   = $td['room_number']   ?? '';
    $gstin    = $td['gstin']         ?? '';
    $checkin  = !empty($td['checkin_date'])  ? date('d M Y', strtotime($td['checkin_date']))  : '';
    $checkout = !empty($td['checkout_date']) ? date('d M Y', strtotime($td['checkout_date'])) : '';
?>
<div style="font-family:Georgia,serif;background:#fffdf5;font-size:12px;color:#333;border:1px solid #c9a84c;box-shadow:0 4px 20px rgba(0,0,0,.15);">
    <div style="background:linear-gradient(135deg,#7d5a00,#c9a84c);color:#fff;padding:20px 24px;text-align:center;">
        <div style="font-size:10px;letter-spacing:4px;text-transform:uppercase;opacity:.8;margin-bottom:4px;">★ ★ ★</div>
        <div style="font-size:22px;font-weight:700;letter-spacing:1px;"><?= htmlspecialchars($bill['from_name']) ?></div>
        <?php if ($bill['from_address']): ?><div style="font-size:10px;opacity:.85;margin-top:4px;"><?= htmlspecialchars(str_replace("\n",' | ',$bill['from_address'])) ?></div><?php endif; ?>
        <?php if ($bill['from_phone']): ?><div style="font-size:10px;opacity:.85;">📞 <?= htmlspecialchars($bill['from_phone']) ?></div><?php endif; ?>
        <?php if ($gstin): ?><div style="font-size:10px;opacity:.85;">GSTIN: <?= htmlspecialchars($gstin) ?></div><?php endif; ?>
        <div style="font-size:11px;letter-spacing:3px;text-transform:uppercase;margin-top:8px;opacity:.9;">— Hotel Folio —</div>
    </div>
    <div style="background:#fdf0c0;padding:10px 24px;border-bottom:2px solid #c9a84c;display:flex;justify-content:space-between;flex-wrap:wrap;gap:8px;">
        <div>
            <span style="font-size:10px;color:#7d5a00;display:block;text-transform:uppercase;">Guest Name</span>
            <span style="font-size:14px;font-weight:700;"><?= htmlspecialchars($bill['to_name']) ?></span>
            <?php if ($bill['to_phone']): ?><span style="font-size:10px;color:#7d5a00;display:block;">📞 <?= htmlspecialchars($bill['to_phone']) ?></span><?php endif; ?>
        </div>
        <div style="text-align:right;">
            <span style="font-size:10px;color:#7d5a00;display:block;text-transform:uppercase;">Folio Details</span>
            <span style="font-size:12px;display:block;">Folio #: <b><?= htmlspecialchars($bill['bill_number']) ?></b></span>
            <span style="font-size:12px;">Date: <?= $billDate ?></span>
            <?php if ($roomNo): ?><span style="font-size:11px;display:block;">Room: <b><?= htmlspecialchars($roomNo) ?></b></span><?php endif; ?>
            <?php if ($checkin): ?><span style="font-size:11px;display:block;">In: <?= $checkin ?><?= $checkout ? ' → '.$checkout : '' ?></span><?php endif; ?>
        </div>
    </div>
    <table style="width:100%;border-collapse:collapse;">
        <thead><tr style="background:#c9a84c;color:#fff;"><th style="padding:8px 12px;text-align:left;font-size:11px;">Description</th><th style="padding:8px 12px;text-align:center;font-size:11px;width:70px;">Nights/Qty</th><th style="padding:8px 12px;text-align:right;font-size:11px;width:80px;">Rate</th><th style="padding:8px 12px;text-align:right;font-size:11px;width:90px;">Amount</th></tr></thead>
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
    <div style="padding:10px 24px;background:#fdf8ee;border-top:1px solid #e8d89a;"><div style="display:flex;justify-content:flex-end;"><div style="min-width:220px;">
        <?php if ($subtotal !== $total): ?><div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-bottom:1px solid #e8d89a;"><span style="color:#777;">Subtotal</span><span><?= $sym.number_format($subtotal,2) ?></span></div><?php endif; ?>
        <?php if ($taxPct > 0): ?><div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-bottom:1px solid #e8d89a;"><span style="color:#777;">Tax/Service <?= $taxPct ?>%</span><span><?= $sym.number_format($taxAmt,2) ?></span></div><?php endif; ?>
        <?php if ($discount > 0): ?><div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-bottom:1px solid #e8d89a;"><span style="color:#777;">Discount</span><span style="color:#e53935;">-<?= $sym.number_format($discount,2) ?></span></div><?php endif; ?>
        <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;border-top:2px solid #c9a84c;padding-top:6px;margin-top:4px;color:#7d5a00;"><span>Total Amount</span><span><?= $sym.number_format($total,2) ?></span></div>
    </div></div></div>
    <?php if ($bill['notes']): ?><div style="padding:8px 24px;font-size:11px;color:#7d5a00;font-style:italic;border-top:1px solid #e8d89a;"><?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
    <div style="background:linear-gradient(135deg,#7d5a00,#c9a84c);color:#fff;padding:10px 24px;display:flex;justify-content:space-between;font-size:10px;"><span>Thank you for your stay! 🌟</span><span>BillX</span></div>
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
        <?php if (!empty($td['member_id'])): ?><span style="font-size:11px;color:#aaa;display:block;">Member ID: <?= htmlspecialchars($td['member_id']) ?></span><?php endif; ?>
        <?php if (!empty($td['plan_name'])): ?><span style="font-size:11px;color:#ff6f00;display:block;">Plan: <?= htmlspecialchars($td['plan_name']) ?></span><?php endif; ?>
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
     STANDARD PROFESSIONAL INVOICE WITH GST  (book, internet, ecom, general, stationary)
     ============================================================ -->
<?php
    $gstin   = $td['gstin']          ?? '';
    $hsnCode = $td['hsn_code']       ?? '';
    $cgstPct = (float)($td['cgst_pct'] ?? 0);
    $sgstPct = (float)($td['sgst_pct'] ?? 0);
    $pos     = $td['place_of_supply'] ?? '';
    $cgstAmt = round($subtotal * $cgstPct / 100, 2);
    $sgstAmt = round($subtotal * $sgstPct / 100, 2);
?>
<div style="font-family:Arial,sans-serif;background:#fff;font-size:12px;color:#222;border:1px solid #ddd;box-shadow:0 2px 12px rgba(0,0,0,.1);">
    <div style="background:<?= htmlspecialchars($c) ?>;color:#fff;padding:20px 24px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <div style="font-size:22px;font-weight:700;"><?= htmlspecialchars($bill['from_name']) ?></div>
                <?php if ($bill['from_address']): ?><div style="font-size:10px;opacity:.85;margin-top:4px;"><?= htmlspecialchars(str_replace("\n",' | ',$bill['from_address'])) ?></div><?php endif; ?>
                <div style="font-size:10px;opacity:.85;"><?= $bill['from_phone']?'📞 '.htmlspecialchars($bill['from_phone']).'  ':'' ?><?= $bill['from_email']?'✉ '.htmlspecialchars($bill['from_email']):'' ?></div>
                <?php if ($gstin): ?><div style="font-size:10px;opacity:.85;">GSTIN: <?= htmlspecialchars($gstin) ?></div><?php endif; ?>
            </div>
            <div style="text-align:right;">
                <div style="font-size:26px;font-weight:900;letter-spacing:-1px;opacity:.9;">INVOICE</div>
                <div style="font-size:11px;opacity:.85;"># <?= htmlspecialchars($bill['bill_number']) ?></div>
                <div style="font-size:11px;opacity:.85;">Date: <?= $billDate ?></div>
                <?php if ($pos): ?><div style="font-size:10px;opacity:.85;">Supply: <?= htmlspecialchars($pos) ?></div><?php endif; ?>
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
        <thead><tr style="background:<?= htmlspecialchars($c) ?>;color:#fff;">
            <th style="padding:8px 10px;text-align:center;font-size:11px;width:30px;">#</th>
            <th style="padding:8px 10px;text-align:left;font-size:11px;">Description</th>
            <?php if ($hsnCode): ?><th style="padding:8px 10px;text-align:center;font-size:11px;width:70px;">HSN/SAC</th><?php endif; ?>
            <th style="padding:8px 10px;text-align:center;font-size:11px;width:50px;">Qty</th>
            <th style="padding:8px 10px;text-align:right;font-size:11px;width:80px;">Rate</th>
            <th style="padding:8px 10px;text-align:right;font-size:11px;width:90px;">Amount</th>
        </tr></thead>
        <tbody>
            <?php foreach ($items as $i => $item): ?>
            <tr style="background:<?= $i%2===0?'#fafafa':'#fff' ?>;">
                <td style="padding:7px 10px;font-size:12px;border-bottom:1px solid #eee;text-align:center;"><?= $i+1 ?></td>
                <td style="padding:7px 10px;font-size:12px;border-bottom:1px solid #eee;"><?= htmlspecialchars($item['description']??'-') ?></td>
                <?php if ($hsnCode): ?><td style="padding:7px 10px;font-size:11px;border-bottom:1px solid #eee;text-align:center;"><?= htmlspecialchars($hsnCode) ?></td><?php endif; ?>
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
            <?php if ($cgstPct > 0): ?><div style="display:flex;justify-content:space-between;padding:4px 0;font-size:12px;border-bottom:1px solid #eee;"><span style="color:#666;">CGST @ <?= $cgstPct ?>%</span><span style="font-weight:600;"><?= $sym.number_format($cgstAmt,2) ?></span></div><?php endif; ?>
            <?php if ($sgstPct > 0): ?><div style="display:flex;justify-content:space-between;padding:4px 0;font-size:12px;border-bottom:1px solid #eee;"><span style="color:#666;">SGST @ <?= $sgstPct ?>%</span><span style="font-weight:600;"><?= $sym.number_format($sgstAmt,2) ?></span></div><?php endif; ?>
            <?php if ($taxPct > 0 && $cgstPct == 0 && $sgstPct == 0): ?><div style="display:flex;justify-content:space-between;padding:4px 0;font-size:12px;border-bottom:1px solid #eee;"><span style="color:#666;">Tax <?= $taxPct ?>%</span><span style="font-weight:600;"><?= $sym.number_format($taxAmt,2) ?></span></div><?php endif; ?>
            <?php if ($discount > 0): ?><div style="display:flex;justify-content:space-between;padding:4px 0;font-size:12px;border-bottom:1px solid #eee;"><span style="color:#666;">Discount</span><span style="font-weight:600;color:#e53935;">-<?= $sym.number_format($discount,2) ?></span></div><?php endif; ?>
            <div style="display:flex;justify-content:space-between;padding:8px 0 4px;font-size:16px;font-weight:900;border-top:2px solid <?= htmlspecialchars($c) ?>;color:<?= htmlspecialchars($c) ?>;"><span>Total</span><span><?= $sym.number_format($total,2) ?></span></div>
        </div>
    </div>
    <?php if ($bill['notes']): ?><div style="margin:0 24px 16px;background:#f5f5f5;border-radius:4px;padding:10px;font-size:11px;color:#555;border-left:3px solid <?= htmlspecialchars($c) ?>;"><b>Terms & Notes:</b> <?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
    <div style="background:<?= htmlspecialchars($c) ?>;color:rgba(255,255,255,.7);padding:8px 24px;text-align:center;font-size:10px;">Thank you for your business! | BillX</div>
</div>

<?php endif; ?>

</div><!-- /billDocument -->
<script>
window.addEventListener('load', function() {
    // Wait for fonts to fully render before opening print dialog
    var trigger = function(){ setTimeout(function(){ window.print(); }, 300); };
    if (document.fonts && document.fonts.ready) {
        document.fonts.ready.then(trigger);
    } else {
        setTimeout(trigger, 800);
    }
});
</script>
</body>
</html>
