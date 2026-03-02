<?php
/** @var array $bill @var array $config @var array $user */
$csrfToken = \Core\Security::generateCsrfToken();

$typeLabel = $config['bill_types'][$bill['bill_type']] ?? ucfirst($bill['bill_type']);
$sym = ['INR' => '₹', 'USD' => '$', 'EUR' => '€', 'GBP' => '£'][$bill['currency']] ?? $bill['currency'] . ' ';

// Theme colours per bill type (mirrors JS themes in generate.php)
$themes = [
    'fuel'       => ['#e65c00', '#f9d423'],
    'driver'     => ['#1565c0', '#42a5f5'],
    'helper'     => ['#546e7a', '#90a4ae'],
    'rent'       => ['#6a1b9a', '#ab47bc'],
    'book'       => ['#5d4037', '#a1887f'],
    'internet'   => ['#0277bd', '#29b6f6'],
    'restaurant' => ['#c62828', '#ef9a9a'],
    'lta'        => ['#2e7d32', '#66bb6a'],
    'ecom'       => ['#1565c0', '#64b5f6'],
    'general'    => ['#37474f', '#78909c'],
    'recharge'   => ['#00838f', '#26c6da'],
    'medical'    => ['#01579b', '#4fc3f7'],
    'stationary' => ['#bf360c', '#ff8a65'],
    'cab'        => ['#f57f17', '#ffd54f'],
    'mart'       => ['#1b5e20', '#69f0ae'],
    'gym'        => ['#212121', '#ff6f00'],
    'hotel'      => ['#bf8c00', '#ffd700'],
    'newspaper'  => ['#1a1a1a', '#555555'],
];
[$primaryColor, $secondaryColor] = $themes[$bill['bill_type']] ?? ['#37474f', '#78909c'];
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

<!-- Bill document -->
<div style="background:#f5f5f5;padding:24px;border-radius:12px;">
    <div id="billDocument" style="background:white;max-width:680px;margin:0 auto;font-family:'Poppins',sans-serif;font-size:13px;color:#333;box-shadow:0 4px 20px rgba(0,0,0,0.1);border-radius:4px;overflow:hidden;">

        <!-- Header -->
        <div style="background:<?= htmlspecialchars($primaryColor) ?>;color:white;padding:28px 28px 20px;">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:12px;">
                <div>
                    <div style="font-size:22px;font-weight:700;"><?= htmlspecialchars($bill['from_name']) ?></div>
                    <?php if ($bill['from_address']): ?>
                    <div style="font-size:11px;opacity:0.85;margin-top:4px;"><?= nl2br(htmlspecialchars($bill['from_address'])) ?></div>
                    <?php endif; ?>
                    <?php if ($bill['from_phone']): ?>
                    <div style="font-size:11px;opacity:0.85;">📞 <?= htmlspecialchars($bill['from_phone']) ?></div>
                    <?php endif; ?>
                    <?php if ($bill['from_email']): ?>
                    <div style="font-size:11px;opacity:0.85;">✉️ <?= htmlspecialchars($bill['from_email']) ?></div>
                    <?php endif; ?>
                </div>
                <div style="text-align:right;">
                    <div style="font-size:16px;font-weight:700;background:rgba(255,255,255,0.2);padding:6px 14px;border-radius:6px;">
                        <?= strtoupper(htmlspecialchars($typeLabel)) ?>
                    </div>
                    <div style="font-size:12px;margin-top:8px;opacity:0.9;">
                        Bill # <strong><?= htmlspecialchars($bill['bill_number']) ?></strong>
                    </div>
                    <div style="font-size:12px;opacity:0.9;">
                        Date: <strong><?= date('d M Y', strtotime($bill['bill_date'])) ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bill To -->
        <div style="background:<?= htmlspecialchars($secondaryColor) ?>22;border-left:4px solid <?= htmlspecialchars($secondaryColor) ?>;padding:14px 28px;">
            <div style="font-size:10px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:0.05em;">Bill To</div>
            <div style="font-size:15px;font-weight:700;color:#222;"><?= htmlspecialchars($bill['to_name']) ?></div>
            <?php if ($bill['to_address']): ?>
            <div style="font-size:11px;color:#555;"><?= nl2br(htmlspecialchars($bill['to_address'])) ?></div>
            <?php endif; ?>
            <?php if ($bill['to_phone']): ?>
            <div style="font-size:11px;color:#555;">📞 <?= htmlspecialchars($bill['to_phone']) ?></div>
            <?php endif; ?>
            <?php if ($bill['to_email']): ?>
            <div style="font-size:11px;color:#555;">✉️ <?= htmlspecialchars($bill['to_email']) ?></div>
            <?php endif; ?>
        </div>

        <!-- Items table -->
        <div style="padding:20px 28px;">
            <table style="width:100%;border-collapse:collapse;font-size:12px;">
                <thead>
                    <tr style="background:<?= htmlspecialchars($primaryColor) ?>;color:white;">
                        <th style="padding:9px 10px;text-align:left;">Description</th>
                        <th style="padding:9px 10px;text-align:right;width:60px;">Qty</th>
                        <th style="padding:9px 10px;text-align:right;width:90px;">Rate</th>
                        <th style="padding:9px 10px;text-align:right;width:90px;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bill['items'] as $item): ?>
                    <tr>
                        <td style="padding:9px 10px;border-bottom:1px solid #eee;"><?= htmlspecialchars($item['description'] ?? '') ?></td>
                        <td style="padding:9px 10px;border-bottom:1px solid #eee;text-align:right;"><?= htmlspecialchars($item['qty']) ?></td>
                        <td style="padding:9px 10px;border-bottom:1px solid #eee;text-align:right;"><?= $sym ?><?= number_format((float)($item['rate'] ?? 0), 2) ?></td>
                        <td style="padding:9px 10px;border-bottom:1px solid #eee;text-align:right;font-weight:600;"><?= $sym ?><?= number_format((float)($item['amount'] ?? 0), 2) ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($bill['items'])): ?>
                    <tr><td colspan="4" style="padding:12px;text-align:center;color:#aaa;">No items</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Totals -->
        <div style="padding:0 28px 20px;display:flex;justify-content:flex-end;">
            <div style="min-width:240px;">
                <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:12px;border-bottom:1px solid #eee;">
                    <span style="color:#666;">Subtotal</span>
                    <span style="font-weight:600;"><?= $sym ?><?= number_format((float)$bill['subtotal'], 2) ?></span>
                </div>
                <?php if ((float)$bill['tax_percent'] > 0): ?>
                <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:12px;border-bottom:1px solid #eee;">
                    <span style="color:#666;">Tax (<?= (float)$bill['tax_percent'] ?>%)</span>
                    <span style="font-weight:600;"><?= $sym ?><?= number_format((float)$bill['tax_amount'], 2) ?></span>
                </div>
                <?php endif; ?>
                <?php if ((float)$bill['discount_amount'] > 0): ?>
                <div style="display:flex;justify-content:space-between;padding:6px 0;font-size:12px;border-bottom:1px solid #eee;">
                    <span style="color:#666;">Discount</span>
                    <span style="font-weight:600;color:#e53935;">- <?= $sym ?><?= number_format((float)$bill['discount_amount'], 2) ?></span>
                </div>
                <?php endif; ?>
                <div style="display:flex;justify-content:space-between;padding:10px 0 5px;font-size:16px;font-weight:700;border-top:2px solid <?= htmlspecialchars($primaryColor) ?>;">
                    <span>Total</span>
                    <span style="color:<?= htmlspecialchars($primaryColor) ?>;"><?= $sym ?><?= number_format((float)$bill['total_amount'], 2) ?></span>
                </div>
            </div>
        </div>

        <?php if ($bill['notes']): ?>
        <div style="margin:0 28px 20px;background:#f9f9f9;border-radius:6px;padding:12px 16px;font-size:11px;color:#555;border-left:3px solid <?= htmlspecialchars($secondaryColor) ?>;">
            <strong style="display:block;margin-bottom:4px;color:#333;">Notes</strong>
            <?= nl2br(htmlspecialchars($bill['notes'])) ?>
        </div>
        <?php endif; ?>

        <!-- Footer -->
        <div style="background:<?= htmlspecialchars($primaryColor) ?>;color:white;padding:12px 28px;text-align:center;font-size:11px;opacity:0.9;">
            Generated with BillX • MyMultiBranch
        </div>
    </div>
</div>

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
    #billDocument { box-shadow: none !important; }
}
</style>
