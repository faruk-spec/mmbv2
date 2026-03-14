<?php
/**
 * Shared bill body renderer — included by both view.php and pdf.php.
 *
 * Expected variables (set by the including file):
 *   $bill, $config, $group, $c, $typeLabel, $sym,
 *   $items, $subtotal, $taxPct, $taxAmt, $discount, $total,
 *   $billDate, $td, $tplStyle
 */
?>
<?php if ($group === 'thermal'): ?>
<!-- ============================================================
     THERMAL / POS RECEIPT  (restaurant, mart, stationary, etc.)
     ============================================================ -->
<?php
    $cgstPct  = (float)($td['cgst_pct'] ?? 0);
    $sgstPct  = (float)($td['sgst_pct'] ?? 0);
    $cgstAmt  = round($subtotal * $cgstPct / 100, 2);
    $sgstAmt  = round($subtotal * $sgstPct / 100, 2);
    $tableNo  = $td['table_number'] ?? '';
    $payMode  = $td['payment_mode'] ?? '';
    $billTime = !empty($td['bill_time']) ? $td['bill_time'] : ($bill['created_at'] ? date('H:i', strtotime($bill['created_at'])) : date('H:i'));
?>
<?php if ($tplStyle === '3'): ?>
<!-- Style 3: VT323 retro POS look -->
<div style="font-family:'VT323','Courier New',monospace;background:#fff;width:80mm;max-width:80mm;margin:0 auto;color:#111;letter-spacing:.4px;">
<div style="padding:14px 16px 12px;">
  <div style="text-align:center;margin-bottom:6px;">
    <div style="font-size:24px;font-weight:700;letter-spacing:5px;text-transform:uppercase;">WELCOME!</div>
    <div style="font-size:13px;letter-spacing:3px;text-transform:uppercase;color:#555;">Original Receipt</div>
    <div style="border-top:1px dashed #555;margin:6px 0;"></div>
    <div style="font-size:22px;font-weight:700;letter-spacing:2px;line-height:1.2;text-transform:uppercase;"><?= htmlspecialchars($bill['from_name']) ?></div>
    <?php if ($bill['from_address']): ?><div style="font-size:12px;color:#444;margin-top:3px;"><?= htmlspecialchars(str_replace("\n",' | ',$bill['from_address'])) ?></div><?php endif; ?>
    <?php if ($bill['from_phone']): ?><div style="font-size:12px;color:#444;">Ph: <?= htmlspecialchars($bill['from_phone']) ?></div><?php endif; ?>
    <div style="border-top:1px dashed #555;margin:6px 0;"></div>
  </div>
  <div style="font-size:13px;border-bottom:1px dashed #555;padding-bottom:6px;margin-bottom:6px;">
    <div style="display:flex;justify-content:space-between;"><span>Bill#: <b><?= htmlspecialchars($bill['bill_number']) ?></b></span><span><?= $billDate ?></span></div>
    <div style="display:flex;justify-content:space-between;"><span>Customer: <b><?= htmlspecialchars($bill['to_name']) ?></b></span><span><?= htmlspecialchars($billTime) ?></span></div>
    <?php if ($tableNo): ?><div>Table: <b>#<?= htmlspecialchars($tableNo) ?></b><?= $payMode ? '  |  Pay: <b>'.htmlspecialchars($payMode).'</b>' : '' ?></div><?php endif; ?>
  </div>
  <table style="width:100%;border-collapse:collapse;font-size:13px;border-bottom:1px dashed #555;margin-bottom:6px;">
    <thead><tr style="border-bottom:1px solid #aaa;">
      <th style="text-align:left;padding:3px 2px 3px 0;font-size:12px;">ITEM</th>
      <th style="text-align:right;padding:3px 2px;font-size:12px;">QTY</th>
      <th style="text-align:right;padding:3px 2px;font-size:12px;">RATE</th>
      <th style="text-align:right;padding:3px 0 3px 2px;font-size:12px;">AMT</th>
    </tr></thead>
    <tbody>
    <?php foreach ($items as $item): ?>
    <tr style="border-bottom:1px dotted #ccc;">
      <td style="padding:3px 2px 3px 0;"><?= htmlspecialchars($item['description']??'-') ?></td>
      <td style="padding:3px 2px;text-align:right;"><?= (float)($item['qty']??1) ?></td>
      <td style="padding:3px 2px;text-align:right;"><?= $sym.number_format((float)($item['rate']??0),2) ?></td>
      <td style="padding:3px 0 3px 2px;text-align:right;font-weight:700;"><?= $sym.number_format((float)($item['amount']??0),2) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <div style="font-size:13px;padding-bottom:4px;">
    <div style="display:flex;justify-content:space-between;padding:1px 0;"><span>Sub-Total:</span><span><?= $sym.number_format($subtotal,2) ?></span></div>
    <?php if ($cgstPct > 0): ?><div style="display:flex;justify-content:space-between;padding:1px 0;"><span>CGST <?= $cgstPct ?>%</span><span><?= $sym.number_format($cgstAmt,2) ?></span></div><?php endif; ?>
    <?php if ($sgstPct > 0): ?><div style="display:flex;justify-content:space-between;padding:1px 0;"><span>SGST <?= $sgstPct ?>%</span><span><?= $sym.number_format($sgstAmt,2) ?></span></div><?php endif; ?>
    <?php if ($taxPct > 0 && $cgstPct == 0 && $sgstPct == 0): ?><div style="display:flex;justify-content:space-between;padding:1px 0;"><span>Tax <?= $taxPct ?>%</span><span><?= $sym.number_format($taxAmt,2) ?></span></div><?php endif; ?>
    <?php if ($discount > 0): ?><div style="display:flex;justify-content:space-between;padding:1px 0;"><span>Discount</span><span>-<?= $sym.number_format($discount,2) ?></span></div><?php endif; ?>
  </div>
  <div style="border-top:2px solid #111;padding:5px 0 3px;">
    <div style="display:flex;justify-content:space-between;font-size:21px;font-weight:900;"><span>** TOTAL</span><span><?= $sym.number_format($total,2) ?></span></div>
    <?php if ($payMode && !$tableNo): ?><div style="font-size:13px;color:#555;">Payment: <?= htmlspecialchars($payMode) ?></div><?php endif; ?>
  </div>
  <?php if ($bill['notes']): ?><div style="border-top:1px dashed #555;padding-top:5px;margin-top:4px;font-size:12px;color:#555;text-align:center;"><?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
  <div style="border-top:1px dashed #555;padding-top:7px;margin-top:6px;text-align:center;">
    <div style="font-size:16px;font-weight:700;letter-spacing:2px;">THANK YOU! VISIT AGAIN!</div>
    <div style="font-size:12px;color:#555;margin-top:2px;">** SAVE PAPER ~ SAVE NATURE **</div>
    <div style="font-size:11px;color:#aaa;margin-top:4px;">Powered by BillX</div>
  </div>
</div>
</div>

<?php else: ?>
<!-- Style 1 & 2: Clean professional monospace POS receipt -->
<div style="font-family:'Courier New',Courier,monospace;background:#fff;width:80mm;max-width:80mm;margin:0 auto;padding:16px 16px 14px;font-size:11px;color:#111;line-height:1.55;">
  <div style="text-align:center;margin-bottom:6px;">
    <div style="font-size:15px;font-weight:700;letter-spacing:3px;text-transform:uppercase;"><?= htmlspecialchars($bill['from_name']) ?></div>
    <?php if ($bill['from_address']): ?><div style="font-size:9.5px;color:#444;margin-top:2px;line-height:1.4;"><?= htmlspecialchars(str_replace("\n",' | ',$bill['from_address'])) ?></div><?php endif; ?>
    <?php if ($bill['from_phone']): ?><div style="font-size:9.5px;color:#444;">Tel: <?= htmlspecialchars($bill['from_phone']) ?></div><?php endif; ?>
    <?php if ($bill['from_email']): ?><div style="font-size:9.5px;color:#444;"><?= htmlspecialchars($bill['from_email']) ?></div><?php endif; ?>
  </div>
  <div style="border-top:1px dashed #888;margin:6px 0;"></div>
  <div style="text-align:center;font-size:11px;font-weight:700;letter-spacing:5px;margin:4px 0;">RECEIPT</div>
  <div style="border-top:1px dashed #888;margin:6px 0;"></div>
  <div style="font-size:10px;margin-bottom:4px;">
    <div style="display:flex;justify-content:space-between;"><span>Bill #: <b><?= htmlspecialchars($bill['bill_number']) ?></b></span><span><?= $billDate ?></span></div>
    <div style="display:flex;justify-content:space-between;"><span>Cust: <b><?= htmlspecialchars($bill['to_name']) ?></b></span><span><?= htmlspecialchars($billTime) ?></span></div>
    <?php if ($tableNo): ?><div>Table: <b>#<?= htmlspecialchars($tableNo) ?></b><?= $payMode ? '  |  Pay: <b>'.htmlspecialchars($payMode).'</b>' : '' ?></div><?php endif; ?>
  </div>
  <div style="border-top:1px dashed #888;margin:6px 0;"></div>
  <table style="width:100%;border-collapse:collapse;font-size:10px;">
    <thead>
      <tr style="border-bottom:1px solid #888;">
        <th style="text-align:left;padding:2px 2px 2px 0;font-weight:700;">Item</th>
        <th style="text-align:right;padding:2px 2px;font-weight:700;">Qty</th>
        <th style="text-align:right;padding:2px 2px;font-weight:700;">Rate</th>
        <th style="text-align:right;padding:2px 0 2px 2px;font-weight:700;">Amt</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $item): ?>
    <tr style="border-bottom:1px dotted #ccc;">
      <td style="padding:3px 2px 3px 0;font-size:10.5px;"><?= htmlspecialchars($item['description']??'-') ?></td>
      <td style="padding:3px 2px;text-align:right;"><?= (float)($item['qty']??1) ?></td>
      <td style="padding:3px 2px;text-align:right;"><?= $sym.number_format((float)($item['rate']??0),2) ?></td>
      <td style="padding:3px 0 3px 2px;text-align:right;font-weight:700;"><?= $sym.number_format((float)($item['amount']??0),2) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <div style="border-top:1px dashed #888;margin:6px 0;"></div>
  <div style="font-size:10px;">
    <div style="display:flex;justify-content:space-between;padding:1px 0;"><span>Sub-Total</span><span><?= $sym.number_format($subtotal,2) ?></span></div>
    <?php if ($cgstPct > 0): ?><div style="display:flex;justify-content:space-between;padding:1px 0;"><span>CGST <?= $cgstPct ?>%</span><span><?= $sym.number_format($cgstAmt,2) ?></span></div><?php endif; ?>
    <?php if ($sgstPct > 0): ?><div style="display:flex;justify-content:space-between;padding:1px 0;"><span>SGST <?= $sgstPct ?>%</span><span><?= $sym.number_format($sgstAmt,2) ?></span></div><?php endif; ?>
    <?php if ($taxPct > 0 && $cgstPct == 0 && $sgstPct == 0): ?><div style="display:flex;justify-content:space-between;padding:1px 0;"><span>Tax <?= $taxPct ?>%</span><span><?= $sym.number_format($taxAmt,2) ?></span></div><?php endif; ?>
    <?php if ($discount > 0): ?><div style="display:flex;justify-content:space-between;padding:1px 0;"><span>Discount</span><span>-<?= $sym.number_format($discount,2) ?></span></div><?php endif; ?>
  </div>
  <div style="border-top:2px solid #111;margin:5px 0 4px;"></div>
  <div style="display:flex;justify-content:space-between;font-size:14px;font-weight:900;">
    <span>TOTAL</span><span><?= $sym.number_format($total,2) ?></span>
  </div>
  <?php if ($payMode): ?><div style="font-size:9.5px;color:#555;margin-top:2px;">Mode: <b><?= htmlspecialchars($payMode) ?></b></div><?php endif; ?>
  <div style="border-top:1px dashed #888;margin:8px 0 6px;"></div>
  <?php if ($bill['notes']): ?><div style="font-size:9.5px;color:#555;text-align:center;margin-bottom:5px;"><?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
  <div style="text-align:center;font-size:10.5px;font-weight:700;letter-spacing:1px;">-- THANK YOU! VISIT AGAIN --</div>
  <div style="text-align:center;font-size:9px;color:#666;margin-top:2px;">Save Paper. Save Nature.</div>
  <div style="border-top:1px dashed #888;margin:6px 0 4px;"></div>
  <div style="text-align:center;font-size:8.5px;color:#aaa;">Powered by BillX</div>
</div>
<?php endif; // tplStyle === '3' ?>


<?php elseif ($group === 'payslip'): ?>
<!-- ============================================================
     PAYSLIP — professional salary slip
     ============================================================ -->
<?php
    $vehicleNo   = $td['vehicle_number'] ?? '';
    $empName     = $td['employer_name']  ?? $bill['from_name'];
    $salaryMonth = $td['salary_month']   ?? date('F Y', strtotime($bill['bill_date']));
    $designation = $td['designation']    ?? 'Driver';
?>
<div style="font-family:'Inter',Arial,sans-serif;background:#fff;max-width:600px;margin:0 auto;border:1px solid #d0d5dd;font-size:12px;color:#1a1a2e;">
  <div style="background:<?= htmlspecialchars($c) ?>;color:#fff;padding:20px 28px 16px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div style="font-size:20px;font-weight:700;letter-spacing:0.5px;"><?= htmlspecialchars($bill['from_name']) ?></div>
        <?php if ($bill['from_address']): ?><div style="font-size:10px;opacity:.85;margin-top:4px;line-height:1.5;"><?= htmlspecialchars(str_replace("\n",' | ',$bill['from_address'])) ?></div><?php endif; ?>
        <?php if ($bill['from_phone']): ?><div style="font-size:10px;opacity:.8;">Tel: <?= htmlspecialchars($bill['from_phone']) ?></div><?php endif; ?>
        <?php if ($bill['from_email']): ?><div style="font-size:10px;opacity:.8;"><?= htmlspecialchars($bill['from_email']) ?></div><?php endif; ?>
      </div>
      <div style="text-align:right;">
        <div style="font-size:16px;font-weight:700;letter-spacing:2px;text-transform:uppercase;opacity:.9;">SALARY SLIP</div>
        <div style="font-size:11px;opacity:.8;margin-top:4px;"><?= htmlspecialchars($salaryMonth) ?></div>
        <div style="font-size:10px;opacity:.75;">Slip # <?= htmlspecialchars($bill['bill_number']) ?></div>
      </div>
    </div>
  </div>
  <div style="padding:14px 28px;border-bottom:1px solid #e4e7ec;background:#f9fafb;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px 20px;">
      <div>
        <div style="font-size:9.5px;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Employee Name</div>
        <div style="font-weight:600;font-size:13px;"><?= htmlspecialchars($bill['to_name']) ?></div>
        <?php if ($bill['to_phone']): ?><div style="font-size:10px;color:#6b7280;">Tel: <?= htmlspecialchars($bill['to_phone']) ?></div><?php endif; ?>
        <?php if ($bill['to_address']): ?><div style="font-size:10px;color:#6b7280;"><?= htmlspecialchars(str_replace("\n",', ',$bill['to_address'])) ?></div><?php endif; ?>
        <?php if ($bill['to_email']): ?><div style="font-size:10px;color:#6b7280;"><?= htmlspecialchars($bill['to_email']) ?></div><?php endif; ?>
      </div>
      <div>
        <div style="font-size:9.5px;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Designation</div>
        <div style="font-weight:600;"><?= htmlspecialchars($designation) ?></div>
      </div>
      <div>
        <div style="font-size:9.5px;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Employer</div>
        <div style="font-weight:600;"><?= htmlspecialchars($empName) ?></div>
      </div>
      <?php if ($vehicleNo): ?>
      <div>
        <div style="font-size:9.5px;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Vehicle Number</div>
        <div style="font-weight:600;"><?= htmlspecialchars($vehicleNo) ?></div>
      </div>
      <?php endif; ?>
      <div>
        <div style="font-size:9.5px;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Pay Period</div>
        <div style="font-weight:600;"><?= htmlspecialchars($salaryMonth) ?></div>
      </div>
      <div>
        <div style="font-size:9.5px;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Date of Issue</div>
        <div style="font-weight:600;"><?= $billDate ?></div>
      </div>
    </div>
  </div>
  <?php if ($items): ?>
  <div style="padding:14px 28px;border-bottom:1px solid #e4e7ec;">
    <div style="font-size:10.5px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:<?= htmlspecialchars($c) ?>;margin-bottom:8px;">Earnings &amp; Allowances</div>
    <table style="width:100%;border-collapse:collapse;">
      <thead>
        <tr style="background:#f3f4f6;border-bottom:1px solid #e4e7ec;">
          <th style="padding:7px 10px;text-align:left;font-size:10.5px;font-weight:600;color:#374151;">Description</th>
          <th style="padding:7px 10px;text-align:right;font-size:10.5px;font-weight:600;color:#374151;width:120px;">Amount</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach ($items as $item): ?>
      <tr style="border-bottom:1px solid #f3f4f6;">
        <td style="padding:8px 10px;font-size:11.5px;"><?= htmlspecialchars($item['description']??'-') ?></td>
        <td style="padding:8px 10px;text-align:right;font-weight:600;font-size:11.5px;"><?= $sym.number_format((float)($item['amount']??0),2) ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php endif; ?>
  <div style="padding:14px 28px;border-bottom:1px solid #e4e7ec;">
    <div style="display:flex;justify-content:flex-end;">
      <div style="min-width:260px;">
        <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:11.5px;border-bottom:1px solid #e4e7ec;"><span style="color:#6b7280;">Gross Earnings</span><span style="font-weight:600;"><?= $sym.number_format($subtotal,2) ?></span></div>
        <?php if ($taxPct > 0): ?><div style="display:flex;justify-content:space-between;padding:4px 0;font-size:11.5px;border-bottom:1px solid #e4e7ec;"><span style="color:#6b7280;">Deductions / Tax <?= $taxPct ?>%</span><span style="font-weight:600;color:#dc2626;">-<?= $sym.number_format($taxAmt,2) ?></span></div><?php endif; ?>
        <?php if ($discount > 0): ?><div style="display:flex;justify-content:space-between;padding:4px 0;font-size:11.5px;border-bottom:1px solid #e4e7ec;"><span style="color:#6b7280;">Other Deductions</span><span style="font-weight:600;color:#dc2626;">-<?= $sym.number_format($discount,2) ?></span></div><?php endif; ?>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px;background:<?= htmlspecialchars($c) ?>;color:#fff;padding:8px 12px;border-radius:4px;">
          <span style="font-weight:700;font-size:12.5px;letter-spacing:.03em;">NET PAY</span>
          <span style="font-weight:900;font-size:16px;"><?= $sym.number_format($total,2) ?></span>
        </div>
      </div>
    </div>
  </div>
  <div style="padding:12px 28px;border-bottom:1px solid #e4e7ec;font-size:11px;color:#374151;line-height:1.65;">
    <p style="margin:0 0 8px;">This is to certify that the above salary of <b><?= $sym.number_format($total,2) ?></b> has been paid to <b><?= htmlspecialchars($designation) ?> <?= htmlspecialchars($bill['to_name']) ?></b> for the month of <b><?= htmlspecialchars($salaryMonth) ?></b>. The <?= htmlspecialchars(strtolower($designation)) ?> is exclusively utilized for official purposes only.</p>
    <p style="margin:0;">I declare that the above information is correct and true to the best of my knowledge.</p>
  </div>
  <div style="padding:16px 28px 20px;display:flex;justify-content:space-between;align-items:flex-end;">
    <div>
      <div style="width:72px;height:72px;border:1.5px dashed #9ca3af;border-radius:4px;display:flex;align-items:center;justify-content:center;text-align:center;color:#9ca3af;font-size:9px;line-height:1.4;">Revenue<br>Stamp</div>
    </div>
    <div style="text-align:center;">
      <div style="border-top:1px solid #555;width:140px;padding-top:5px;font-size:10.5px;color:#374151;margin-top:32px;">Employee Signature</div>
    </div>
    <div style="text-align:center;">
      <div style="border-top:1px solid #555;width:140px;padding-top:5px;font-size:10.5px;color:#374151;margin-top:32px;">Authorized Signatory</div>
    </div>
  </div>
  <?php if ($bill['notes']): ?><div style="margin:0 28px 14px;background:#f9fafb;border-left:3px solid <?= htmlspecialchars($c) ?>;padding:8px 12px;font-size:10.5px;color:#4b5563;"><?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
  <div style="background:#f9fafb;border-top:1px solid #e4e7ec;padding:7px 28px;text-align:center;font-size:9px;color:#9ca3af;">Computer-generated payslip — no physical signature required &nbsp;|&nbsp; BillX</div>
</div>


<?php elseif ($group === 'fuel'): ?>
<!-- ============================================================
     FUEL RECEIPT
     ============================================================ -->
<?php
    $vehicleNo   = $td['vehicle_number'] ?? '';
    $vehicleType = $td['vehicle_type']   ?? '';
    $fuelType    = $td['fuel_type']      ?? 'Petrol';
    $payMode     = $td['payment_mode']   ?? '';
    $billTime    = $bill['created_at'] ? date('H:i', strtotime($bill['created_at'])) : date('H:i');
?>
<div style="font-family:'Inter',Arial,sans-serif;background:#fff;max-width:560px;margin:0 auto;border:1px solid #d0d5dd;font-size:12px;color:#1a1a2e;">
  <div style="background:<?= htmlspecialchars($c) ?>;color:#fff;padding:18px 24px 14px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div style="font-size:9.5px;letter-spacing:3px;text-transform:uppercase;opacity:.8;margin-bottom:4px;">FUEL RECEIPT</div>
        <div style="font-size:20px;font-weight:700;"><?= htmlspecialchars($bill['from_name']) ?></div>
        <?php if ($bill['from_address']): ?><div style="font-size:10px;opacity:.8;margin-top:3px;line-height:1.5;"><?= htmlspecialchars(str_replace("\n",', ',$bill['from_address'])) ?></div><?php endif; ?>
        <?php if ($bill['from_phone']): ?><div style="font-size:10px;opacity:.8;">Tel: <?= htmlspecialchars($bill['from_phone']) ?></div><?php endif; ?>
        <?php if ($bill['from_email']): ?><div style="font-size:10px;opacity:.8;"><?= htmlspecialchars($bill['from_email']) ?></div><?php endif; ?>
      </div>
      <div style="text-align:right;">
        <div style="font-size:11px;opacity:.8;">Receipt #</div>
        <div style="font-size:16px;font-weight:700;"><?= htmlspecialchars($bill['bill_number']) ?></div>
        <div style="font-size:10px;opacity:.8;margin-top:4px;"><?= $billDate ?></div>
        <div style="font-size:10px;opacity:.8;"><?= $billTime ?></div>
      </div>
    </div>
  </div>
  <div style="padding:12px 24px;background:#f8f9fa;border-bottom:1px solid #e4e7ec;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px 16px;">
      <div>
        <div style="font-size:9.5px;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Customer</div>
        <div style="font-weight:600;font-size:13px;"><?= htmlspecialchars($bill['to_name']) ?></div>
        <?php if ($bill['to_phone']): ?><div style="font-size:10px;color:#555;">Tel: <?= htmlspecialchars($bill['to_phone']) ?></div><?php endif; ?>
        <?php if ($bill['to_address']): ?><div style="font-size:10px;color:#555;"><?= htmlspecialchars(str_replace("\n",', ',$bill['to_address'])) ?></div><?php endif; ?>
        <?php if ($bill['to_email']): ?><div style="font-size:10px;color:#555;"><?= htmlspecialchars($bill['to_email']) ?></div><?php endif; ?>
      </div>
      <div>
        <div style="font-size:9.5px;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Vehicle</div>
        <?php if ($vehicleNo): ?><div style="font-weight:600;font-size:13px;"><?= htmlspecialchars($vehicleNo) ?></div><?php endif; ?>
        <?php if ($vehicleType): ?><div style="font-size:10px;color:#555;"><?= htmlspecialchars($vehicleType) ?></div><?php endif; ?>
      </div>
      <div>
        <div style="font-size:9.5px;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Fuel Type</div>
        <div style="font-weight:600;"><?= htmlspecialchars($fuelType) ?></div>
      </div>
      <?php if ($payMode): ?>
      <div>
        <div style="font-size:9.5px;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Payment Mode</div>
        <div style="font-weight:600;"><?= htmlspecialchars($payMode) ?></div>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr style="background:#f3f4f6;border-bottom:2px solid <?= htmlspecialchars($c) ?>;">
        <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:600;color:#374151;">Description</th>
        <th style="padding:9px 12px;text-align:center;font-size:10.5px;font-weight:600;color:#374151;width:80px;">Qty (L)</th>
        <th style="padding:9px 12px;text-align:right;font-size:10.5px;font-weight:600;color:#374151;width:90px;">Rate/L</th>
        <th style="padding:9px 16px;text-align:right;font-size:10.5px;font-weight:600;color:#374151;width:100px;">Amount</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $item): ?>
    <tr style="border-bottom:1px solid #f3f4f6;">
      <td style="padding:10px 16px;font-size:12px;"><?= htmlspecialchars($item['description']??$fuelType) ?></td>
      <td style="padding:10px 12px;text-align:center;font-size:12px;"><?= number_format((float)($item['qty']??1),2) ?></td>
      <td style="padding:10px 12px;text-align:right;font-size:12px;"><?= $sym.number_format((float)($item['rate']??0),2) ?></td>
      <td style="padding:10px 16px;text-align:right;font-weight:700;font-size:12px;"><?= $sym.number_format((float)($item['amount']??0),2) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <div style="padding:10px 24px 12px;background:#f9fafb;border-top:1px solid #e4e7ec;">
    <div style="display:flex;justify-content:flex-end;">
      <div style="min-width:220px;">
        <?php if ($subtotal !== $total): ?><div style="display:flex;justify-content:space-between;padding:3px 0;font-size:11px;color:#6b7280;"><span>Subtotal</span><span><?= $sym.number_format($subtotal,2) ?></span></div><?php endif; ?>
        <?php if ($taxPct > 0): ?><div style="display:flex;justify-content:space-between;padding:3px 0;font-size:11px;color:#6b7280;"><span>Tax <?= $taxPct ?>%</span><span><?= $sym.number_format($taxAmt,2) ?></span></div><?php endif; ?>
        <?php if ($discount > 0): ?><div style="display:flex;justify-content:space-between;padding:3px 0;font-size:11px;color:#6b7280;"><span>Discount</span><span>-<?= $sym.number_format($discount,2) ?></span></div><?php endif; ?>
        <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:800;border-top:2px solid <?= htmlspecialchars($c) ?>;padding-top:7px;margin-top:5px;color:<?= htmlspecialchars($c) ?>;">
          <span>TOTAL</span><span><?= $sym.number_format($total,2) ?></span>
        </div>
      </div>
    </div>
  </div>
  <?php if ($bill['notes']): ?><div style="padding:8px 24px;font-size:10.5px;color:#555;border-top:1px dashed #e4e7ec;"><?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
  <div style="background:<?= htmlspecialchars($c) ?>;color:#fff;padding:10px 24px;text-align:center;">
    <div style="font-size:11px;font-weight:600;margin-bottom:2px;">THANK YOU FOR FUELLING WITH US!</div>
    <div style="font-size:9.5px;opacity:.8;">Save Fuel, Secure the Future &nbsp;|&nbsp; BillX</div>
  </div>
</div>


<?php elseif ($group === 'cab'): ?>
<!-- ============================================================
     CAB & TRAVEL RECEIPT
     ============================================================ -->
<div style="font-family:'Inter',Arial,sans-serif;background:#fff;max-width:480px;margin:0 auto;border:1px solid #d0d5dd;font-size:12px;color:#1a1a2e;">
  <div style="background:<?= htmlspecialchars($c) ?>;color:#fff;padding:18px 22px 14px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div style="font-size:9.5px;letter-spacing:3px;text-transform:uppercase;opacity:.8;margin-bottom:4px;">CAB &amp; TRAVEL RECEIPT</div>
        <div style="font-size:20px;font-weight:700;"><?= htmlspecialchars($bill['from_name']) ?></div>
        <?php if ($bill['from_address']): ?><div style="font-size:10px;opacity:.8;margin-top:3px;"><?= htmlspecialchars(str_replace("\n",' | ',$bill['from_address'])) ?></div><?php endif; ?>
        <?php if ($bill['from_phone']): ?><div style="font-size:10px;opacity:.8;">Tel: <?= htmlspecialchars($bill['from_phone']) ?></div><?php endif; ?>
        <?php if ($bill['from_email']): ?><div style="font-size:10px;opacity:.8;"><?= htmlspecialchars($bill['from_email']) ?></div><?php endif; ?>
      </div>
      <div style="text-align:right;">
        <div style="font-size:10px;opacity:.8;">Receipt #</div>
        <div style="font-size:15px;font-weight:700;"><?= htmlspecialchars($bill['bill_number']) ?></div>
        <div style="font-size:10px;opacity:.8;margin-top:4px;"><?= $billDate ?></div>
      </div>
    </div>
  </div>
  <div style="padding:14px 22px;border-bottom:1px solid #e4e7ec;background:#f8f9fa;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px 16px;">
      <div>
        <div style="font-size:9.5px;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Passenger</div>
        <div style="font-weight:600;font-size:13px;"><?= htmlspecialchars($bill['to_name']) ?></div>
        <?php if ($bill['to_phone']): ?><div style="font-size:10px;color:#555;">Tel: <?= htmlspecialchars($bill['to_phone']) ?></div><?php endif; ?>
        <?php if ($bill['to_address']): ?><div style="font-size:10px;color:#555;"><?= htmlspecialchars(str_replace("\n",', ',$bill['to_address'])) ?></div><?php endif; ?>
        <?php if ($bill['to_email']): ?><div style="font-size:10px;color:#555;"><?= htmlspecialchars($bill['to_email']) ?></div><?php endif; ?>
      </div>
      <?php if (!empty($td['vehicle_number'])): ?>
      <div>
        <div style="font-size:9.5px;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Cab / Vehicle</div>
        <div style="font-weight:600;"><?= htmlspecialchars($td['vehicle_number']) ?></div>
        <?php if (!empty($td['driver_name'])): ?><div style="font-size:10px;color:#555;">Driver: <?= htmlspecialchars($td['driver_name']) ?></div><?php endif; ?>
      </div>
      <?php endif; ?>
      <?php if (!empty($td['pickup'])): ?>
      <div>
        <div style="font-size:9.5px;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Pickup</div>
        <div style="font-weight:600;"><?= htmlspecialchars($td['pickup']) ?></div>
      </div>
      <?php endif; ?>
      <?php if (!empty($td['drop'])): ?>
      <div>
        <div style="font-size:9.5px;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Drop</div>
        <div style="font-weight:600;"><?= htmlspecialchars($td['drop']) ?></div>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr style="background:#f3f4f6;border-bottom:2px solid <?= htmlspecialchars($c) ?>;">
        <th style="padding:8px 16px;text-align:left;font-size:10.5px;font-weight:600;color:#374151;">Description</th>
        <th style="padding:8px 16px;text-align:right;font-size:10.5px;font-weight:600;color:#374151;width:110px;">Amount</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $item): ?>
    <tr style="border-bottom:1px solid #f3f4f6;">
      <td style="padding:9px 16px;font-size:12px;"><?= htmlspecialchars($item['description']??'-') ?></td>
      <td style="padding:9px 16px;text-align:right;font-weight:600;font-size:12px;"><?= $sym.number_format((float)($item['amount']??0),2) ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if ($taxPct > 0): ?>
    <tr style="border-bottom:1px solid #f3f4f6;">
      <td style="padding:7px 16px;font-size:11px;color:#6b7280;">Tax <?= $taxPct ?>%</td>
      <td style="padding:7px 16px;text-align:right;font-size:11px;color:#6b7280;"><?= $sym.number_format($taxAmt,2) ?></td>
    </tr>
    <?php endif; ?>
    <?php if ($discount > 0): ?>
    <tr style="border-bottom:1px solid #f3f4f6;">
      <td style="padding:7px 16px;font-size:11px;color:#16a34a;">Discount</td>
      <td style="padding:7px 16px;text-align:right;font-size:11px;color:#16a34a;">-<?= $sym.number_format($discount,2) ?></td>
    </tr>
    <?php endif; ?>
    </tbody>
  </table>
  <div style="background:<?= htmlspecialchars($c) ?>;color:#fff;padding:12px 22px;display:flex;justify-content:space-between;align-items:center;">
    <span style="font-weight:700;font-size:13px;letter-spacing:.5px;">FARE TOTAL</span>
    <span style="font-weight:900;font-size:18px;"><?= $sym.number_format($total,2) ?></span>
  </div>
  <?php if ($bill['notes']): ?><div style="padding:8px 22px;font-size:10.5px;color:#555;border-top:1px dashed #e4e7ec;"><?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
  <div style="padding:7px 22px;text-align:center;font-size:9.5px;color:#9ca3af;border-top:1px solid #e4e7ec;">Safe Journey! &nbsp;|&nbsp; BillX</div>
</div>


<?php elseif ($group === 'official'): ?>
<!-- ============================================================
     OFFICIAL RECEIPT  (rent, LTA)
     ============================================================ -->
<?php $pan = $td['pan_number'] ?? ''; $prop = $td['property_info'] ?? ''; ?>
<div style="font-family:'Inter',Georgia,serif;background:#fff;max-width:600px;margin:0 auto;border:2px solid <?= htmlspecialchars($c) ?>;font-size:12px;color:#1a1a2e;position:relative;overflow:hidden;">
  <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-35deg);font-size:80px;font-weight:900;color:<?= htmlspecialchars($c) ?>;opacity:.04;pointer-events:none;user-select:none;white-space:nowrap;z-index:0;">ORIGINAL</div>
  <div style="position:relative;z-index:1;border-bottom:3px double <?= htmlspecialchars($c) ?>;padding:18px 28px 14px;text-align:center;">
    <div style="font-size:10px;letter-spacing:4px;text-transform:uppercase;color:<?= htmlspecialchars($c) ?>;font-weight:600;margin-bottom:4px;">— Official Document —</div>
    <div style="font-size:22px;font-weight:700;color:<?= htmlspecialchars($c) ?>;letter-spacing:.5px;"><?= htmlspecialchars($bill['from_name']) ?></div>
    <?php if ($bill['from_address']): ?><div style="font-size:10.5px;color:#555;margin-top:4px;"><?= htmlspecialchars(str_replace("\n",' | ',$bill['from_address'])) ?></div><?php endif; ?>
    <?php if ($bill['from_phone']): ?><div style="font-size:10.5px;color:#555;">Tel: <?= htmlspecialchars($bill['from_phone']) ?></div><?php endif; ?>
    <?php if ($bill['from_email']): ?><div style="font-size:10.5px;color:#555;"><?= htmlspecialchars($bill['from_email']) ?></div><?php endif; ?>
    <?php if ($pan): ?><div style="font-size:10.5px;color:#555;">PAN: <b><?= htmlspecialchars($pan) ?></b></div><?php endif; ?>
  </div>
  <div style="position:relative;z-index:1;padding:10px 28px;background:#f8f9fa;border-bottom:1px solid #e4e7ec;">
    <div style="display:flex;justify-content:space-between;font-size:11.5px;">
      <span>Receipt No.: <b><?= htmlspecialchars($bill['bill_number']) ?></b></span>
      <span><b><?= htmlspecialchars(strtoupper($typeLabel)) ?></b></span>
      <span>Date: <b><?= $billDate ?></b></span>
    </div>
  </div>
  <div style="position:relative;z-index:1;padding:14px 28px;border-bottom:1px solid #e4e7ec;">
    <div style="background:#f0f7f0;border-left:4px solid <?= htmlspecialchars($c) ?>;padding:10px 14px;border-radius:0 4px 4px 0;">
      <div style="font-size:10px;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Received with thanks from</div>
      <div style="font-size:15px;font-weight:700;color:#111;"><?= htmlspecialchars($bill['to_name']) ?></div>
      <?php if ($bill['to_address']): ?><div style="font-size:10.5px;color:#555;margin-top:2px;"><?= htmlspecialchars(str_replace("\n",', ',$bill['to_address'])) ?></div><?php endif; ?>
      <?php if ($bill['to_phone']): ?><div style="font-size:10.5px;color:#555;">Tel: <?= htmlspecialchars($bill['to_phone']) ?></div><?php endif; ?>
      <?php if ($bill['to_email']): ?><div style="font-size:10.5px;color:#555;"><?= htmlspecialchars($bill['to_email']) ?></div><?php endif; ?>
    </div>
    <?php if ($prop): ?><div style="font-size:11px;color:#555;margin-top:8px;">Property / Reference: <b><?= htmlspecialchars($prop) ?></b></div><?php endif; ?>
  </div>
  <div style="position:relative;z-index:1;padding:14px 28px;border-bottom:1px solid #e4e7ec;">
    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:<?= htmlspecialchars($c) ?>;margin-bottom:8px;">Particulars</div>
    <table style="width:100%;border-collapse:collapse;">
      <?php foreach ($items as $item): ?>
      <tr style="border-bottom:1px dotted #d1d5db;">
        <td style="padding:6px 0;font-size:12px;"><?= htmlspecialchars($item['description']??'-') ?></td>
        <td style="padding:6px 0;text-align:right;font-weight:600;font-size:12px;width:120px;"><?= $sym.number_format((float)($item['amount']??0),2) ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
    <div style="margin-top:10px;border-top:1px solid #d1d5db;padding-top:8px;">
      <?php if ($taxPct > 0): ?><div style="display:flex;justify-content:space-between;font-size:11px;color:#6b7280;padding:2px 0;"><span>Tax <?= $taxPct ?>%</span><span><?= $sym.number_format($taxAmt,2) ?></span></div><?php endif; ?>
      <?php if ($discount > 0): ?><div style="display:flex;justify-content:space-between;font-size:11px;color:#6b7280;padding:2px 0;"><span>Discount</span><span>-<?= $sym.number_format($discount,2) ?></span></div><?php endif; ?>
      <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800;border-top:2px solid <?= htmlspecialchars($c) ?>;padding-top:7px;margin-top:5px;color:<?= htmlspecialchars($c) ?>;">
        <span>Total Amount Received</span><span><?= $sym.number_format($total,2) ?></span>
      </div>
    </div>
  </div>
  <?php if ($bill['notes']): ?><div style="position:relative;z-index:1;padding:10px 28px;border-bottom:1px solid #e4e7ec;font-size:11px;color:#4b5563;font-style:italic;"><b>Note:</b> <?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
  <div style="position:relative;z-index:1;padding:18px 28px 22px;display:flex;justify-content:space-between;">
    <div style="text-align:center;">
      <div style="height:36px;"></div>
      <div style="border-top:1px solid #555;width:150px;padding-top:5px;font-size:10.5px;color:#374151;">Receiver's Signature</div>
    </div>
    <div style="text-align:center;">
      <div style="height:36px;"></div>
      <div style="border-top:1px solid #555;width:150px;padding-top:5px;font-size:10.5px;color:#374151;">Issuer's Signature</div>
    </div>
  </div>
  <div style="position:relative;z-index:1;background:#f8f9fa;border-top:1px solid #e4e7ec;padding:7px 28px;text-align:center;font-size:9px;color:#9ca3af;">Computer-generated receipt &nbsp;|&nbsp; BillX</div>
</div>


<?php elseif ($group === 'medical'): ?>
<!-- ============================================================
     MEDICAL BILL — clinical invoice
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
    $netAmt    = $subtotal + $cgstAmt + $sgstAmt + ($cgstPct === 0.0 && $sgstPct === 0.0 ? $taxAmt : 0.0) - $discount;
    $billTime  = $bill['created_at'] ? date('H:i', strtotime($bill['created_at'])) : date('H:i');
?>
<div style="font-family:'Inter',Arial,sans-serif;background:#fff;max-width:620px;margin:0 auto;border:1px solid #d0d5dd;font-size:12px;color:#1a1a2e;">
  <div style="background:<?= htmlspecialchars($c) ?>;color:#fff;padding:18px 24px 14px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div style="font-size:9.5px;letter-spacing:3px;text-transform:uppercase;opacity:.8;margin-bottom:4px;">MEDICAL INVOICE</div>
        <div style="font-size:20px;font-weight:700;"><?= htmlspecialchars($bill['from_name']) ?></div>
        <?php if ($bill['from_address']): ?><div style="font-size:10px;opacity:.8;margin-top:3px;line-height:1.5;"><?= htmlspecialchars(str_replace("\n",' | ',$bill['from_address'])) ?></div><?php endif; ?>
        <?php if ($bill['from_phone']): ?><div style="font-size:10px;opacity:.8;">Tel: <?= htmlspecialchars($bill['from_phone']) ?></div><?php endif; ?>
        <?php if ($bill['from_email']): ?><div style="font-size:10px;opacity:.8;"><?= htmlspecialchars($bill['from_email']) ?></div><?php endif; ?>
      </div>
      <div style="text-align:right;">
        <div style="font-size:10px;opacity:.8;">Invoice No.</div>
        <div style="font-size:15px;font-weight:700;"><?= htmlspecialchars($bill['bill_number']) ?></div>
        <div style="font-size:10px;opacity:.8;margin-top:6px;">Date: <?= $billDate ?></div>
        <div style="font-size:10px;opacity:.8;">Time: <?= $billTime ?></div>
      </div>
    </div>
  </div>
  <div style="padding:12px 24px;border-bottom:1px solid #e4e7ec;background:#f8f9fa;">
    <div style="font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:<?= htmlspecialchars($c) ?>;margin-bottom:8px;">Patient Information</div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:7px 12px;">
      <div><div style="font-size:9px;color:#6b7280;text-transform:uppercase;margin-bottom:1px;">Patient Name</div><div style="font-weight:600;"><?= htmlspecialchars($bill['to_name']) ?></div></div>
      <?php if ($patAge): ?><div><div style="font-size:9px;color:#6b7280;text-transform:uppercase;margin-bottom:1px;">Age</div><div style="font-weight:600;"><?= htmlspecialchars($patAge) ?></div></div><?php endif; ?>
      <?php if ($patId): ?><div><div style="font-size:9px;color:#6b7280;text-transform:uppercase;margin-bottom:1px;">Patient ID</div><div style="font-weight:600;"><?= htmlspecialchars($patId) ?></div></div><?php endif; ?>
      <?php if ($guardian): ?><div><div style="font-size:9px;color:#6b7280;text-transform:uppercase;margin-bottom:1px;">Guardian</div><div style="font-weight:600;"><?= htmlspecialchars($guardian) ?></div></div><?php endif; ?>
      <?php if ($issue): ?><div><div style="font-size:9px;color:#6b7280;text-transform:uppercase;margin-bottom:1px;">Diagnosis / Issue</div><div style="font-weight:600;"><?= htmlspecialchars($issue) ?></div></div><?php endif; ?>
      <?php if ($doctor): ?><div><div style="font-size:9px;color:#6b7280;text-transform:uppercase;margin-bottom:1px;">Consultant</div><div style="font-weight:600;"><?= htmlspecialchars($doctor) ?></div></div><?php endif; ?>
      <?php if ($admitDate): ?><div><div style="font-size:9px;color:#6b7280;text-transform:uppercase;margin-bottom:1px;">Admit Date</div><div style="font-weight:600;"><?= $admitDate ?></div></div><?php endif; ?>
      <div><div style="font-size:9px;color:#6b7280;text-transform:uppercase;margin-bottom:1px;">Discharge / Date</div><div style="font-weight:600;"><?= $billDate ?></div></div>
      <div><div style="font-size:9px;color:#6b7280;text-transform:uppercase;margin-bottom:1px;">Room Category</div><div style="font-weight:600;"><?= htmlspecialchars($roomCat) ?></div></div>
      <div><div style="font-size:9px;color:#6b7280;text-transform:uppercase;margin-bottom:1px;">Insurance</div><div style="font-weight:600;"><?= htmlspecialchars($insurance) ?></div></div>
      <?php if ($bill['to_phone']): ?><div><div style="font-size:9px;color:#6b7280;text-transform:uppercase;margin-bottom:1px;">Contact</div><div style="font-weight:600;"><?= htmlspecialchars($bill['to_phone']) ?></div></div><?php endif; ?>
      <?php if ($bill['to_email']): ?><div><div style="font-size:9px;color:#6b7280;text-transform:uppercase;margin-bottom:1px;">Email</div><div style="font-weight:600;"><?= htmlspecialchars($bill['to_email']) ?></div></div><?php endif; ?>
      <?php if ($bill['to_address']): ?><div style="grid-column:span 2;"><div style="font-size:9px;color:#6b7280;text-transform:uppercase;margin-bottom:1px;">Address</div><div style="font-weight:600;"><?= htmlspecialchars($bill['to_address']) ?></div></div><?php endif; ?>
    </div>
  </div>
  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr style="background:#f3f4f6;border-bottom:2px solid <?= htmlspecialchars($c) ?>;">
        <th style="padding:8px 16px;text-align:left;font-size:10.5px;font-weight:600;color:#374151;">#</th>
        <th style="padding:8px 12px;text-align:left;font-size:10.5px;font-weight:600;color:#374151;">Treatment / Description</th>
        <th style="padding:8px 12px;text-align:right;font-size:10.5px;font-weight:600;color:#374151;width:90px;">Rate</th>
        <th style="padding:8px 16px;text-align:right;font-size:10.5px;font-weight:600;color:#374151;width:100px;">Amount</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $i => $item): ?>
    <tr style="border-bottom:1px solid #f3f4f6;background:<?= $i%2===0?'#fff':'#fafafa' ?>;">
      <td style="padding:8px 16px;font-size:11px;color:#9ca3af;"><?= $i+1 ?></td>
      <td style="padding:8px 12px;font-size:12px;"><?= htmlspecialchars($item['description']??'-') ?></td>
      <td style="padding:8px 12px;text-align:right;font-size:12px;"><?= $sym.number_format((float)($item['rate']??0),2) ?></td>
      <td style="padding:8px 16px;text-align:right;font-weight:600;font-size:12px;"><?= $sym.number_format((float)($item['amount']??0),2) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <div style="padding:12px 24px;background:#f9fafb;border-top:1px solid #e4e7ec;">
    <div style="display:flex;justify-content:flex-end;">
      <div style="min-width:260px;">
        <div style="display:flex;justify-content:space-between;padding:3px 0;font-size:11px;color:#6b7280;border-bottom:1px solid #e4e7ec;"><span>Subtotal (Taxable Amount)</span><span><?= $sym.number_format($subtotal,2) ?></span></div>
        <?php if ($cgstPct > 0): ?><div style="display:flex;justify-content:space-between;padding:3px 0;font-size:11px;color:#6b7280;border-bottom:1px solid #e4e7ec;"><span>CGST @ <?= $cgstPct ?>%</span><span><?= $sym.number_format($cgstAmt,2) ?></span></div><?php endif; ?>
        <?php if ($sgstPct > 0): ?><div style="display:flex;justify-content:space-between;padding:3px 0;font-size:11px;color:#6b7280;border-bottom:1px solid #e4e7ec;"><span>SGST @ <?= $sgstPct ?>%</span><span><?= $sym.number_format($sgstAmt,2) ?></span></div><?php endif; ?>
        <?php if ($taxPct > 0 && $cgstPct == 0 && $sgstPct == 0): ?><div style="display:flex;justify-content:space-between;padding:3px 0;font-size:11px;color:#6b7280;border-bottom:1px solid #e4e7ec;"><span>Tax <?= $taxPct ?>%</span><span><?= $sym.number_format($taxAmt,2) ?></span></div><?php endif; ?>
        <?php if ($discount > 0): ?><div style="display:flex;justify-content:space-between;padding:3px 0;font-size:11px;color:#16a34a;border-bottom:1px solid #e4e7ec;"><span>Discount</span><span>-<?= $sym.number_format($discount,2) ?></span></div><?php endif; ?>
        <div style="display:flex;justify-content:space-between;font-size:15px;font-weight:800;border-top:2px solid <?= htmlspecialchars($c) ?>;padding-top:7px;margin-top:5px;color:<?= htmlspecialchars($c) ?>;">
          <span>Net Amount</span><span><?= $sym.number_format($total,2) ?></span>
        </div>
      </div>
    </div>
  </div>
  <?php if ($bill['notes']): ?><div style="padding:8px 24px;border-top:1px dashed #e4e7ec;font-size:10.5px;color:#555;"><b>Remark:</b> <?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
  <div style="background:#f8f9fa;border-top:1px solid #e4e7ec;padding:7px 24px;text-align:center;font-size:9px;color:#9ca3af;">Computer-generated invoice — signature not required &nbsp;|&nbsp; BillX</div>
</div>


<?php elseif ($group === 'hotel'): ?>
<!-- ============================================================
     HOTEL FOLIO — elegant guest bill
     ============================================================ -->
<?php
    $roomNo   = $td['room_number']   ?? '';
    $gstin    = $td['gstin']         ?? '';
    $checkin  = !empty($td['checkin_date'])  ? date('d M Y', strtotime($td['checkin_date']))  : '';
    $checkout = !empty($td['checkout_date']) ? date('d M Y', strtotime($td['checkout_date'])) : '';
?>
<div style="font-family:'Inter',Georgia,serif;background:#fffdf5;font-size:12px;color:#2d2508;border:1px solid #c9a84c;">
  <div style="background:linear-gradient(135deg,#6b4c00,#c9a84c);color:#fff;padding:22px 28px 18px;text-align:center;">
    <div style="font-size:11px;letter-spacing:5px;text-transform:uppercase;opacity:.75;margin-bottom:6px;">&#9733; &#9733; &#9733;</div>
    <div style="font-size:24px;font-weight:700;letter-spacing:.5px;font-family:'Playfair Display','Georgia',serif;"><?= htmlspecialchars($bill['from_name']) ?></div>
    <?php if ($bill['from_address']): ?><div style="font-size:10px;opacity:.8;margin-top:5px;line-height:1.6;"><?= htmlspecialchars(str_replace("\n",' | ',$bill['from_address'])) ?></div><?php endif; ?>
    <?php if ($bill['from_phone']): ?><div style="font-size:10px;opacity:.8;">Tel: <?= htmlspecialchars($bill['from_phone']) ?></div><?php endif; ?>
    <?php if ($bill['from_email']): ?><div style="font-size:10px;opacity:.8;"><?= htmlspecialchars($bill['from_email']) ?></div><?php endif; ?>
    <?php if ($gstin): ?><div style="font-size:10px;opacity:.8;">GSTIN: <?= htmlspecialchars($gstin) ?></div><?php endif; ?>
    <div style="font-size:11px;letter-spacing:4px;text-transform:uppercase;margin-top:10px;opacity:.85;">— <?= htmlspecialchars($typeLabel) ?> —</div>
  </div>
  <div style="background:#fdf0c0;padding:12px 28px;border-bottom:2px solid #c9a84c;display:flex;justify-content:space-between;flex-wrap:wrap;gap:10px;">
    <div>
      <div style="font-size:9px;color:#7d5a00;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Guest Name</div>
      <div style="font-size:15px;font-weight:700;color:#2d2508;"><?= htmlspecialchars($bill['to_name']) ?></div>
      <?php if ($bill['to_phone']): ?><div style="font-size:10px;color:#7d5a00;margin-top:2px;">Tel: <?= htmlspecialchars($bill['to_phone']) ?></div><?php endif; ?>
      <?php if ($bill['to_address']): ?><div style="font-size:10px;color:#7d5a00;"><?= htmlspecialchars(str_replace("\n",', ',$bill['to_address'])) ?></div><?php endif; ?>
      <?php if ($bill['to_email']): ?><div style="font-size:10px;color:#7d5a00;"><?= htmlspecialchars($bill['to_email']) ?></div><?php endif; ?>
    </div>
    <div style="text-align:right;">
      <div style="font-size:9px;color:#7d5a00;text-transform:uppercase;letter-spacing:.06em;margin-bottom:3px;">Bill Details</div>
      <div style="font-size:13px;font-weight:700;">Bill #: <?= htmlspecialchars($bill['bill_number']) ?></div>
      <div style="font-size:11px;color:#7d5a00;margin-top:3px;">Date: <?= $billDate ?></div>
      <?php if ($roomNo): ?><div style="font-size:11px;color:#7d5a00;">Room: <b><?= htmlspecialchars($roomNo) ?></b></div><?php endif; ?>
      <?php if ($checkin): ?><div style="font-size:11px;color:#7d5a00;">Check-in: <?= $checkin ?></div><?php endif; ?>
      <?php if ($checkout): ?><div style="font-size:11px;color:#7d5a00;">Check-out: <?= $checkout ?></div><?php endif; ?>
    </div>
  </div>
  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr style="background:#c9a84c;color:#fff;">
        <th style="padding:9px 16px;text-align:left;font-size:10.5px;font-weight:600;">Description</th>
        <th style="padding:9px 10px;text-align:center;font-size:10.5px;font-weight:600;width:80px;">Nights/Qty</th>
        <th style="padding:9px 10px;text-align:right;font-size:10.5px;font-weight:600;width:90px;">Rate</th>
        <th style="padding:9px 16px;text-align:right;font-size:10.5px;font-weight:600;width:100px;">Amount</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $i => $item): ?>
    <tr style="background:<?= $i%2===0?'#fdf8ee':'#fff' ?>;border-bottom:1px solid #e8d89a;">
      <td style="padding:9px 16px;font-size:12px;"><?= htmlspecialchars($item['description']??'-') ?></td>
      <td style="padding:9px 10px;text-align:center;font-size:12px;"><?= (float)($item['qty']??1) ?></td>
      <td style="padding:9px 10px;text-align:right;font-size:12px;"><?= $sym.number_format((float)($item['rate']??0),2) ?></td>
      <td style="padding:9px 16px;text-align:right;font-weight:700;font-size:12px;"><?= $sym.number_format((float)($item['amount']??0),2) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <div style="padding:12px 28px;background:#fdf8ee;border-top:1px solid #e8d89a;">
    <div style="display:flex;justify-content:flex-end;">
      <div style="min-width:240px;">
        <?php if ($subtotal !== $total): ?><div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-bottom:1px solid #e8d89a;color:#7d5a00;"><span>Subtotal</span><span><?= $sym.number_format($subtotal,2) ?></span></div><?php endif; ?>
        <?php if ($taxPct > 0): ?><div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-bottom:1px solid #e8d89a;color:#7d5a00;"><span>Tax / Service <?= $taxPct ?>%</span><span><?= $sym.number_format($taxAmt,2) ?></span></div><?php endif; ?>
        <?php if ($discount > 0): ?><div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-bottom:1px solid #e8d89a;color:#16a34a;"><span>Discount</span><span>-<?= $sym.number_format($discount,2) ?></span></div><?php endif; ?>
        <div style="display:flex;justify-content:space-between;font-size:17px;font-weight:800;border-top:2px solid #c9a84c;padding-top:7px;margin-top:5px;color:#7d5a00;">
          <span>Total Amount</span><span><?= $sym.number_format($total,2) ?></span>
        </div>
      </div>
    </div>
  </div>
  <?php if ($bill['notes']): ?><div style="padding:8px 28px;font-size:10.5px;color:#7d5a00;font-style:italic;border-top:1px solid #e8d89a;"><?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
  <div style="background:linear-gradient(135deg,#6b4c00,#c9a84c);color:#fff;padding:10px 28px;display:flex;justify-content:space-between;align-items:center;font-size:10.5px;">
    <span>Thank you for your stay! &#9733;</span>
    <span style="opacity:.75;">BillX</span>
  </div>
</div>


<?php elseif ($group === 'gym'): ?>
<!-- ============================================================
     GYM INVOICE — modern fitness receipt
     ============================================================ -->
<div style="font-family:'Inter',Arial,sans-serif;background:#fff;max-width:520px;margin:0 auto;border:1px solid #d0d5dd;font-size:12px;color:#1a1a2e;">
  <div style="background:<?= htmlspecialchars($c) ?>;color:#fff;padding:18px 24px 14px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div style="font-size:9.5px;letter-spacing:3px;text-transform:uppercase;opacity:.8;margin-bottom:4px;">FITNESS INVOICE</div>
        <div style="font-size:22px;font-weight:800;letter-spacing:-.3px;"><?= htmlspecialchars($bill['from_name']) ?></div>
        <?php if ($bill['from_address']): ?><div style="font-size:10px;opacity:.8;margin-top:3px;"><?= htmlspecialchars(str_replace("\n",' | ',$bill['from_address'])) ?></div><?php endif; ?>
        <?php if ($bill['from_phone']): ?><div style="font-size:10px;opacity:.8;">Tel: <?= htmlspecialchars($bill['from_phone']) ?></div><?php endif; ?>
        <?php if ($bill['from_email']): ?><div style="font-size:10px;opacity:.8;"><?= htmlspecialchars($bill['from_email']) ?></div><?php endif; ?>
      </div>
      <div style="text-align:right;">
        <div style="font-size:10px;opacity:.8;">Invoice #</div>
        <div style="font-size:15px;font-weight:700;"><?= htmlspecialchars($bill['bill_number']) ?></div>
        <div style="font-size:10px;opacity:.8;margin-top:4px;"><?= $billDate ?></div>
      </div>
    </div>
  </div>
  <div style="padding:14px 24px;border-bottom:1px solid #e4e7ec;background:#f8f9fa;">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px 16px;">
      <div>
        <div style="font-size:9.5px;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Member Name</div>
        <div style="font-weight:700;font-size:14px;"><?= htmlspecialchars($bill['to_name']) ?></div>
        <?php if ($bill['to_phone']): ?><div style="font-size:10px;color:#555;">Tel: <?= htmlspecialchars($bill['to_phone']) ?></div><?php endif; ?>
        <?php if ($bill['to_address']): ?><div style="font-size:10px;color:#555;"><?= htmlspecialchars(str_replace("\n",', ',$bill['to_address'])) ?></div><?php endif; ?>
        <?php if ($bill['to_email']): ?><div style="font-size:10px;color:#555;"><?= htmlspecialchars($bill['to_email']) ?></div><?php endif; ?>
      </div>
      <?php if (!empty($td['member_id'])): ?>
      <div>
        <div style="font-size:9.5px;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Member ID</div>
        <div style="font-weight:600;"><?= htmlspecialchars($td['member_id']) ?></div>
      </div>
      <?php endif; ?>
      <?php if (!empty($td['plan_name'])): ?>
      <div>
        <div style="font-size:9.5px;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Membership Plan</div>
        <div style="font-weight:600;color:<?= htmlspecialchars($c) ?>;"><?= htmlspecialchars($td['plan_name']) ?></div>
      </div>
      <?php endif; ?>
      <div>
        <div style="font-size:9.5px;color:#6b7280;text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;">Invoice Date</div>
        <div style="font-weight:600;"><?= $billDate ?></div>
      </div>
    </div>
  </div>
  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr style="background:#f3f4f6;border-bottom:2px solid <?= htmlspecialchars($c) ?>;">
        <th style="padding:8px 16px;text-align:left;font-size:10.5px;font-weight:600;color:#374151;">Service / Plan</th>
        <th style="padding:8px 12px;text-align:center;font-size:10.5px;font-weight:600;color:#374151;width:60px;">Qty</th>
        <th style="padding:8px 12px;text-align:right;font-size:10.5px;font-weight:600;color:#374151;width:90px;">Rate</th>
        <th style="padding:8px 16px;text-align:right;font-size:10.5px;font-weight:600;color:#374151;width:100px;">Amount</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $item): ?>
    <tr style="border-bottom:1px solid #f3f4f6;">
      <td style="padding:9px 16px;font-size:12px;"><?= htmlspecialchars($item['description']??'-') ?></td>
      <td style="padding:9px 12px;text-align:center;font-size:12px;"><?= (float)($item['qty']??1) ?></td>
      <td style="padding:9px 12px;text-align:right;font-size:12px;"><?= $sym.number_format((float)($item['rate']??0),2) ?></td>
      <td style="padding:9px 16px;text-align:right;font-weight:700;font-size:12px;"><?= $sym.number_format((float)($item['amount']??0),2) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <div style="padding:12px 24px;background:#f9fafb;border-top:1px solid #e4e7ec;">
    <div style="display:flex;justify-content:flex-end;">
      <div style="min-width:240px;">
        <?php if ($subtotal !== $total): ?><div style="display:flex;justify-content:space-between;padding:3px 0;font-size:11px;color:#6b7280;border-bottom:1px solid #e4e7ec;"><span>Subtotal</span><span><?= $sym.number_format($subtotal,2) ?></span></div><?php endif; ?>
        <?php if ($taxPct > 0): ?><div style="display:flex;justify-content:space-between;padding:3px 0;font-size:11px;color:#6b7280;border-bottom:1px solid #e4e7ec;"><span>Tax <?= $taxPct ?>%</span><span><?= $sym.number_format($taxAmt,2) ?></span></div><?php endif; ?>
        <?php if ($discount > 0): ?><div style="display:flex;justify-content:space-between;padding:3px 0;font-size:11px;color:#16a34a;border-bottom:1px solid #e4e7ec;"><span>Discount</span><span>-<?= $sym.number_format($discount,2) ?></span></div><?php endif; ?>
        <div style="display:flex;justify-content:space-between;font-size:16px;font-weight:800;border-top:2px solid <?= htmlspecialchars($c) ?>;padding-top:7px;margin-top:5px;color:<?= htmlspecialchars($c) ?>;">
          <span>TOTAL DUE</span><span><?= $sym.number_format($total,2) ?></span>
        </div>
      </div>
    </div>
  </div>
  <?php if ($bill['notes']): ?><div style="padding:8px 24px;font-size:10.5px;color:#555;border-top:1px dashed #e4e7ec;"><?= htmlspecialchars($bill['notes']) ?></div><?php endif; ?>
  <div style="background:<?= htmlspecialchars($c) ?>;color:#fff;padding:9px 24px;text-align:center;">
    <div style="font-size:11px;font-weight:600;">Stay Strong. Keep Pushing!</div>
    <div style="font-size:9.5px;opacity:.8;margin-top:2px;">BillX</div>
  </div>
</div>


<?php else: ?>
<!-- ============================================================
     PROFESSIONAL GST INVOICE  (general, book, internet, ecom, recharge, newspaper)
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
<div style="font-family:'Inter',Arial,sans-serif;background:#fff;font-size:12px;color:#1a1a2e;border:1px solid #d0d5dd;">
  <div style="background:<?= htmlspecialchars($c) ?>;color:#fff;padding:22px 28px 18px;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;">
      <div>
        <div style="font-size:22px;font-weight:700;letter-spacing:-.3px;"><?= htmlspecialchars($bill['from_name']) ?></div>
        <?php if ($bill['from_address']): ?><div style="font-size:10px;opacity:.8;margin-top:5px;line-height:1.6;"><?= htmlspecialchars(str_replace("\n",' | ',$bill['from_address'])) ?></div><?php endif; ?>
        <div style="font-size:10px;opacity:.8;margin-top:2px;"><?= $bill['from_phone']?'Tel: '.htmlspecialchars($bill['from_phone']).'  ':'' ?><?= $bill['from_email']?'Email: '.htmlspecialchars($bill['from_email']):'' ?></div>
        <?php if ($gstin): ?><div style="font-size:10px;opacity:.8;margin-top:2px;">GSTIN: <?= htmlspecialchars($gstin) ?></div><?php endif; ?>
      </div>
      <div style="text-align:right;">
        <div style="font-size:28px;font-weight:900;letter-spacing:-1px;opacity:.92;line-height:1;">INVOICE</div>
        <div style="font-size:10px;opacity:.8;margin-top:5px;"># <?= htmlspecialchars($bill['bill_number']) ?></div>
        <div style="font-size:10px;opacity:.8;">Date: <?= $billDate ?></div>
        <?php if ($pos): ?><div style="font-size:10px;opacity:.8;">Place of Supply: <?= htmlspecialchars($pos) ?></div><?php endif; ?>
        <div style="display:inline-block;margin-top:6px;background:rgba(255,255,255,.2);padding:3px 10px;border-radius:20px;font-size:10px;letter-spacing:.03em;"><?= htmlspecialchars($typeLabel) ?></div>
      </div>
    </div>
  </div>
  <div style="display:flex;border-bottom:2px solid <?= htmlspecialchars($c) ?>;">
    <div style="flex:1;padding:14px 28px;border-right:1px solid #e4e7ec;">
      <div style="font-size:9.5px;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;">Bill From</div>
      <div style="font-weight:700;font-size:13px;"><?= htmlspecialchars($bill['from_name']) ?></div>
      <?php if ($bill['from_address']): ?><div style="font-size:10.5px;color:#4b5563;margin-top:2px;"><?= htmlspecialchars(str_replace("\n",' | ',$bill['from_address'])) ?></div><?php endif; ?>
      <?php if ($bill['from_phone']): ?><div style="font-size:10.5px;color:#4b5563;">Tel: <?= htmlspecialchars($bill['from_phone']) ?></div><?php endif; ?>
      <?php if ($bill['from_email']): ?><div style="font-size:10.5px;color:#4b5563;"><?= htmlspecialchars($bill['from_email']) ?></div><?php endif; ?>
    </div>
    <div style="flex:1;padding:14px 28px;background:#f8f9fa;">
      <div style="font-size:9.5px;color:#6b7280;text-transform:uppercase;letter-spacing:.07em;margin-bottom:5px;">Bill To</div>
      <div style="font-weight:700;font-size:13px;"><?= htmlspecialchars($bill['to_name']) ?></div>
      <?php if ($bill['to_address']): ?><div style="font-size:10.5px;color:#4b5563;margin-top:2px;"><?= htmlspecialchars(str_replace("\n",' | ',$bill['to_address'])) ?></div><?php endif; ?>
      <?php if ($bill['to_phone']): ?><div style="font-size:10.5px;color:#4b5563;">Tel: <?= htmlspecialchars($bill['to_phone']) ?></div><?php endif; ?>
      <?php if ($bill['to_email']): ?><div style="font-size:10.5px;color:#4b5563;"><?= htmlspecialchars($bill['to_email']) ?></div><?php endif; ?>
    </div>
  </div>
  <table style="width:100%;border-collapse:collapse;">
    <thead>
      <tr style="background:<?= htmlspecialchars($c) ?>;color:#fff;">
        <th style="padding:9px 12px;text-align:center;font-size:10.5px;font-weight:600;width:32px;">#</th>
        <th style="padding:9px 12px;text-align:left;font-size:10.5px;font-weight:600;">Description</th>
        <?php if ($hsnCode): ?><th style="padding:9px 10px;text-align:center;font-size:10.5px;font-weight:600;width:70px;">HSN/SAC</th><?php endif; ?>
        <th style="padding:9px 10px;text-align:center;font-size:10.5px;font-weight:600;width:55px;">Qty</th>
        <th style="padding:9px 12px;text-align:right;font-size:10.5px;font-weight:600;width:90px;">Rate</th>
        <th style="padding:9px 16px;text-align:right;font-size:10.5px;font-weight:600;width:100px;">Amount</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $i => $item): ?>
    <tr style="background:<?= $i%2===0?'#fafafa':'#fff' ?>;border-bottom:1px solid #e4e7ec;">
      <td style="padding:8px 12px;font-size:11px;text-align:center;color:#9ca3af;"><?= $i+1 ?></td>
      <td style="padding:8px 12px;font-size:12px;"><?= htmlspecialchars($item['description']??'-') ?></td>
      <?php if ($hsnCode): ?><td style="padding:8px 10px;font-size:11px;text-align:center;"><?= htmlspecialchars($hsnCode) ?></td><?php endif; ?>
      <td style="padding:8px 10px;text-align:center;font-size:12px;"><?= (float)($item['qty']??1) ?></td>
      <td style="padding:8px 12px;text-align:right;font-size:12px;"><?= $sym.number_format((float)($item['rate']??0),2) ?></td>
      <td style="padding:8px 16px;text-align:right;font-weight:600;font-size:12px;"><?= $sym.number_format((float)($item['amount']??0),2) ?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  <div style="padding:14px 28px 16px;display:flex;justify-content:space-between;align-items:flex-start;background:#f9fafb;border-top:1px solid #e4e7ec;gap:20px;">
    <?php if ($bill['notes']): ?>
    <div style="flex:1;background:#fff;border-left:3px solid <?= htmlspecialchars($c) ?>;padding:9px 12px;font-size:11px;color:#4b5563;border-radius:0 4px 4px 0;align-self:flex-start;"><b>Terms &amp; Notes:</b><br><?= htmlspecialchars($bill['notes']) ?></div>
    <?php else: ?><div style="flex:1;"></div><?php endif; ?>
    <div style="min-width:260px;">
      <div style="display:flex;justify-content:space-between;padding:4px 0;font-size:11.5px;border-bottom:1px solid #e4e7ec;"><span style="color:#6b7280;">Subtotal</span><span style="font-weight:600;"><?= $sym.number_format($subtotal,2) ?></span></div>
      <?php if ($cgstPct > 0): ?><div style="display:flex;justify-content:space-between;padding:4px 0;font-size:11.5px;border-bottom:1px solid #e4e7ec;"><span style="color:#6b7280;">CGST @ <?= $cgstPct ?>%</span><span style="font-weight:600;"><?= $sym.number_format($cgstAmt,2) ?></span></div><?php endif; ?>
      <?php if ($sgstPct > 0): ?><div style="display:flex;justify-content:space-between;padding:4px 0;font-size:11.5px;border-bottom:1px solid #e4e7ec;"><span style="color:#6b7280;">SGST @ <?= $sgstPct ?>%</span><span style="font-weight:600;"><?= $sym.number_format($sgstAmt,2) ?></span></div><?php endif; ?>
      <?php if ($taxPct > 0 && $cgstPct == 0 && $sgstPct == 0): ?><div style="display:flex;justify-content:space-between;padding:4px 0;font-size:11.5px;border-bottom:1px solid #e4e7ec;"><span style="color:#6b7280;">Tax <?= $taxPct ?>%</span><span style="font-weight:600;"><?= $sym.number_format($taxAmt,2) ?></span></div><?php endif; ?>
      <?php if ($discount > 0): ?><div style="display:flex;justify-content:space-between;padding:4px 0;font-size:11.5px;border-bottom:1px solid #e4e7ec;"><span style="color:#6b7280;">Discount</span><span style="font-weight:600;color:#16a34a;">-<?= $sym.number_format($discount,2) ?></span></div><?php endif; ?>
      <div style="display:flex;justify-content:space-between;padding:8px 0 4px;font-size:17px;font-weight:900;border-top:2px solid <?= htmlspecialchars($c) ?>;color:<?= htmlspecialchars($c) ?>;"><span>Total</span><span><?= $sym.number_format($total,2) ?></span></div>
    </div>
  </div>
  <div style="padding:14px 28px 18px;border-top:1px solid #e4e7ec;display:flex;justify-content:flex-end;">
    <div style="text-align:center;min-width:180px;">
      <div style="height:32px;"></div>
      <div style="border-top:1px solid #555;padding-top:5px;font-size:10.5px;color:#4b5563;">Authorized Signatory</div>
      <div style="font-size:10px;color:#9ca3af;margin-top:2px;"><?= htmlspecialchars($bill['from_name']) ?></div>
    </div>
  </div>
  <div style="background:<?= htmlspecialchars($c) ?>;color:rgba(255,255,255,.8);padding:9px 28px;text-align:center;font-size:10px;">Thank you for your business! &nbsp;|&nbsp; BillX</div>
</div>

<?php endif; ?>
