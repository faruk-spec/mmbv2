<?php
/** @var array $config @var array $user @var string|null $error */
$csrfToken = \Core\Security::generateCsrfToken();
$selectedType = htmlspecialchars($_GET['type'] ?? 'general');
if (!array_key_exists($selectedType, $config['bill_types'])) $selectedType = 'general';
$billNumber = 'BILL-' . strtoupper(date('Ymd')) . '-' . rand(100, 999);
?>

<a href="/projects/billx" class="back-link"><i class="fas fa-arrow-left"></i> Dashboard</a>

<div style="margin-bottom:20px;">
    <h2 style="font-size:1.6rem;font-weight:700;background:linear-gradient(135deg,#f59e0b,#00f0ff);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
        <i class="fas fa-file-invoice" style="-webkit-text-fill-color:#f59e0b;"></i> Generate Bill
    </h2>
    <p style="color:var(--text-secondary);margin-top:4px;">Fill in the details and see a live preview of your bill</p>
</div>

<?php if (!empty($error)): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" action="/projects/billx/generate" id="billForm">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;align-items:start;">

        <!-- ====== LEFT PANEL: Form ====== -->
        <div style="display:flex;flex-direction:column;gap:16px;">

            <!-- Bill Type & Number -->
            <div class="card">
                <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:12px;color:var(--amber);">
                    <i class="fas fa-tag"></i> Bill Details
                </h4>
                <div class="grid grid-2" style="gap:12px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Bill Type</label>
                        <select name="bill_type" id="bill_type" class="form-select">
                            <?php foreach ($config['bill_types'] as $key => $label): ?>
                            <option value="<?= htmlspecialchars($key) ?>" <?= $key === $selectedType ? 'selected' : '' ?>>
                                <?= htmlspecialchars($label) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Bill Number</label>
                        <input type="text" name="bill_number" id="bill_number" class="form-input"
                               value="<?= htmlspecialchars($billNumber) ?>" required>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Bill Date</label>
                        <input type="date" name="bill_date" id="bill_date" class="form-input"
                               value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Currency</label>
                        <select name="currency" id="currency" class="form-select">
                            <option value="INR">INR ₹</option>
                            <option value="USD">USD $</option>
                            <option value="EUR">EUR €</option>
                            <option value="GBP">GBP £</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- From / To -->
            <div class="card">
                <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:12px;color:var(--amber);">
                    <i class="fas fa-users"></i> Parties
                </h4>
                <div class="grid grid-2" style="gap:12px;">
                    <div>
                        <p style="font-size:0.78rem;font-weight:600;color:var(--text-secondary);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em;">From (Issuer)</p>
                        <div class="form-group" style="margin-bottom:8px;">
                            <label class="form-label">Name *</label>
                            <input type="text" name="from_name" id="from_name" class="form-input" placeholder="Your name / company" required>
                        </div>
                        <div class="form-group" style="margin-bottom:8px;">
                            <label class="form-label">Address</label>
                            <textarea name="from_address" id="from_address" class="form-textarea" rows="2" placeholder="Address"></textarea>
                        </div>
                        <div class="form-group" style="margin-bottom:8px;">
                            <label class="form-label">Phone</label>
                            <input type="text" name="from_phone" id="from_phone" class="form-input" placeholder="+91 XXXXX XXXXX">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Email</label>
                            <input type="email" name="from_email" id="from_email" class="form-input" placeholder="email@example.com">
                        </div>
                    </div>
                    <div>
                        <p style="font-size:0.78rem;font-weight:600;color:var(--text-secondary);margin-bottom:8px;text-transform:uppercase;letter-spacing:0.05em;">To (Recipient)</p>
                        <div class="form-group" style="margin-bottom:8px;">
                            <label class="form-label">Name *</label>
                            <input type="text" name="to_name" id="to_name" class="form-input" placeholder="Recipient name" required>
                        </div>
                        <div class="form-group" style="margin-bottom:8px;">
                            <label class="form-label">Address</label>
                            <textarea name="to_address" id="to_address" class="form-textarea" rows="2" placeholder="Address"></textarea>
                        </div>
                        <div class="form-group" style="margin-bottom:8px;">
                            <label class="form-label">Phone</label>
                            <input type="text" name="to_phone" id="to_phone" class="form-input" placeholder="+91 XXXXX XXXXX">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label class="form-label">Email</label>
                            <input type="email" name="to_email" id="to_email" class="form-input" placeholder="email@example.com">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items -->
            <div class="card">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                    <h4 style="font-size:0.9rem;font-weight:600;color:var(--amber);">
                        <i class="fas fa-list-ul"></i> Items
                    </h4>
                    <button type="button" onclick="addItem()" class="btn btn-secondary btn-sm">
                        <i class="fas fa-plus"></i> Add Row
                    </button>
                </div>
                <div style="overflow-x:auto;">
                    <table style="width:100%;border-collapse:collapse;font-size:0.82rem;" id="itemsTable">
                        <thead>
                            <tr style="border-bottom:1px solid var(--border-color);">
                                <th style="text-align:left;padding:6px 8px;color:var(--text-secondary);font-weight:600;">Description</th>
                                <th style="text-align:right;padding:6px 8px;color:var(--text-secondary);font-weight:600;width:70px;">Qty</th>
                                <th style="text-align:right;padding:6px 8px;color:var(--text-secondary);font-weight:600;width:100px;">Rate</th>
                                <th style="text-align:right;padding:6px 8px;color:var(--text-secondary);font-weight:600;width:100px;">Amount</th>
                                <th style="width:32px;"></th>
                            </tr>
                        </thead>
                        <tbody id="itemsBody">
                            <!-- Populated by JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Totals & Notes -->
            <div class="card">
                <h4 style="font-size:0.9rem;font-weight:600;margin-bottom:12px;color:var(--amber);">
                    <i class="fas fa-calculator"></i> Totals & Notes
                </h4>
                <div class="grid grid-2" style="gap:12px;margin-bottom:12px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Tax (%)</label>
                        <input type="number" name="tax_percent" id="tax_percent" class="form-input"
                               value="0" min="0" max="100" step="0.01">
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label">Discount (amount)</label>
                        <input type="number" name="discount_amount" id="discount_amount" class="form-input"
                               value="0" min="0" step="0.01">
                    </div>
                </div>
                <div class="form-group" style="margin:0;">
                    <label class="form-label">Notes / Terms</label>
                    <textarea name="notes" id="notes" class="form-textarea" rows="2"
                              placeholder="Payment terms, thank you note, etc."></textarea>
                </div>
            </div>

            <!-- Submit -->
            <div class="form-actions" style="justify-content:flex-start;">
                <button type="submit" class="btn btn-primary" style="font-size:1rem;padding:12px 28px;">
                    <i class="fas fa-save"></i> Save &amp; View Bill
                </button>
                <a href="/projects/billx" class="btn btn-secondary">Cancel</a>
            </div>
        </div>

        <!-- ====== RIGHT PANEL: Live Preview ====== -->
        <div style="position:sticky;top:80px;">
            <div class="card" style="padding:0;overflow:hidden;">
                <div style="background:var(--bg-secondary);padding:12px 16px;border-bottom:1px solid var(--border-color);display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:0.85rem;font-weight:600;color:var(--text-secondary);">
                        <i class="fas fa-eye"></i> Live Preview
                    </span>
                    <span id="previewTypeBadge" style="font-size:0.7rem;padding:3px 10px;border-radius:12px;background:#f59e0b;color:white;font-weight:600;"></span>
                </div>
                <div style="padding:16px;background:#f5f5f5;min-height:600px;" id="billPreviewWrapper">
                    <div id="billPreview" style="background:white;max-width:520px;margin:0 auto;font-family:'Poppins',sans-serif;font-size:13px;color:#333;box-shadow:0 2px 16px rgba(0,0,0,0.12);border-radius:4px;overflow:hidden;">
                        <!-- Preview rendered by JS -->
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /grid -->
</form>

<style>
.item-row td { padding: 4px 6px; }
.item-row input[type="text"],
.item-row input[type="number"] {
    width: 100%;
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 4px;
    color: var(--text-primary);
    padding: 5px 8px;
    font-family: inherit;
    font-size: 0.8rem;
}
.item-row input:focus { outline: none; border-color: var(--amber); }
.remove-item-btn {
    background: none; border: none; color: #ff6b6b; cursor: pointer;
    font-size: 0.9rem; padding: 4px; line-height: 1;
}
.remove-item-btn:hover { color: #ff4757; }
@media (max-width: 900px) {
    form > div[style*="grid-template-columns:1fr 1fr"] {
        grid-template-columns: 1fr !important;
    }
    form > div > div:last-child { position: static !important; }
}
</style>

<script>
// ─── Bill type themes ─────────────────────────────────────────────────────────
const BILL_THEMES = {
    fuel:       { primary:'#e65c00', secondary:'#f9d423', bg:'#fff8f0', badge:'⛽', label:'Fuel Bill' },
    driver:     { primary:'#1565c0', secondary:'#42a5f5', bg:'#f0f6ff', badge:'🚗', label:'Driver Salary' },
    helper:     { primary:'#546e7a', secondary:'#90a4ae', bg:'#f5f7f8', badge:'🔧', label:'Helper Bill' },
    rent:       { primary:'#6a1b9a', secondary:'#ab47bc', bg:'#faf0ff', badge:'🏠', label:'Rent Receipt' },
    book:       { primary:'#5d4037', secondary:'#a1887f', bg:'#fdf6f0', badge:'📚', label:'Book Invoice' },
    internet:   { primary:'#0277bd', secondary:'#29b6f6', bg:'#f0f8ff', badge:'🌐', label:'Internet Invoice' },
    restaurant: { primary:'#c62828', secondary:'#ef9a9a', bg:'#fff5f5', badge:'🍽️', label:'Restaurant Bill' },
    lta:        { primary:'#2e7d32', secondary:'#66bb6a', bg:'#f0fff4', badge:'✈️', label:'LTA Receipt' },
    ecom:       { primary:'#1565c0', secondary:'#64b5f6', bg:'#f0f6ff', badge:'🛒', label:'E-Com Invoice' },
    general:    { primary:'#37474f', secondary:'#78909c', bg:'#fafafa', badge:'📄', label:'General Bill' },
    recharge:   { primary:'#00838f', secondary:'#26c6da', bg:'#f0fdff', badge:'📱', label:'Recharge Receipt' },
    medical:    { primary:'#01579b', secondary:'#4fc3f7', bg:'#f0f8ff', badge:'🏥', label:'Medical Bill' },
    stationary: { primary:'#bf360c', secondary:'#ff8a65', bg:'#fff8f5', badge:'✏️', label:'Stationary Bill' },
    cab:        { primary:'#f57f17', secondary:'#ffd54f', bg:'#fffde7', badge:'🚕', label:'Cab & Travel' },
    mart:       { primary:'#1b5e20', secondary:'#69f0ae', bg:'#f0fff4', badge:'🛍️', label:'Mart Bill' },
    gym:        { primary:'#212121', secondary:'#ff6f00', bg:'#f5f5f5', badge:'💪', label:'Gym Bill' },
    hotel:      { primary:'#bf8c00', secondary:'#ffd700', bg:'#fffdf0', badge:'🏨', label:'Hotel Bill' },
    newspaper:  { primary:'#1a1a1a', secondary:'#555',    bg:'#f9f9f9', badge:'📰', label:'Newspaper Bill' },
};

// ─── Items management ─────────────────────────────────────────────────────────
let itemCount = 0;

function addItem(desc='', qty=1, rate=0) {
    const tbody = document.getElementById('itemsBody');
    const idx   = itemCount++;
    const amount = (qty * rate).toFixed(2);
    const tr = document.createElement('tr');
    tr.className = 'item-row';
    tr.innerHTML = `
        <td><input type="text"   name="item_description[]" placeholder="Description" value="${escHtml(desc)}" oninput="updatePreview()"></td>
        <td><input type="number" name="item_qty[]"         value="${qty}"  min="0" step="any" style="text-align:right;" oninput="calcRow(this)"></td>
        <td><input type="number" name="item_rate[]"        value="${rate}" min="0" step="any" style="text-align:right;" oninput="calcRow(this)"></td>
        <td style="text-align:right;font-weight:600;padding:4px 8px;" class="row-amount">${amount}</td>
        <td><button type="button" class="remove-item-btn" onclick="removeItem(this)" title="Remove"><i class="fas fa-times"></i></button></td>
    `;
    tbody.appendChild(tr);
    updatePreview();
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function removeItem(btn) {
    btn.closest('tr').remove();
    updatePreview();
}

function calcRow(input) {
    const tr  = input.closest('tr');
    const qty  = parseFloat(tr.querySelector('[name="item_qty[]"]').value)  || 0;
    const rate = parseFloat(tr.querySelector('[name="item_rate[]"]').value) || 0;
    tr.querySelector('.row-amount').textContent = (qty * rate).toFixed(2);
    updatePreview();
}

function getItems() {
    const rows = document.querySelectorAll('#itemsBody .item-row');
    return Array.from(rows).map(tr => ({
        description: tr.querySelector('[name="item_description[]"]').value,
        qty:   parseFloat(tr.querySelector('[name="item_qty[]"]').value)  || 0,
        rate:  parseFloat(tr.querySelector('[name="item_rate[]"]').value) || 0,
        amount: parseFloat(tr.querySelector('.row-amount').textContent)   || 0,
    }));
}

// ─── Currency symbols ─────────────────────────────────────────────────────────
const CURRENCY_SYM = { INR:'₹', USD:'$', EUR:'€', GBP:'£' };

// ─── Live preview renderer ────────────────────────────────────────────────────
function updatePreview() {
    const type      = document.getElementById('bill_type').value;
    const theme     = BILL_THEMES[type] || BILL_THEMES.general;
    const currency  = document.getElementById('currency').value;
    const sym       = CURRENCY_SYM[currency] || currency + ' ';
    const billNo    = document.getElementById('bill_number').value || '-';
    const billDate  = document.getElementById('bill_date').value   || '';
    const fromName  = document.getElementById('from_name').value   || 'Issuer Name';
    const fromAddr  = document.getElementById('from_address').value|| '';
    const fromPhone = document.getElementById('from_phone').value  || '';
    const fromEmail = document.getElementById('from_email').value  || '';
    const toName    = document.getElementById('to_name').value     || 'Recipient Name';
    const toAddr    = document.getElementById('to_address').value  || '';
    const toPhone   = document.getElementById('to_phone').value    || '';
    const toEmail   = document.getElementById('to_email').value    || '';
    const taxPct    = parseFloat(document.getElementById('tax_percent').value)    || 0;
    const discount  = parseFloat(document.getElementById('discount_amount').value)|| 0;
    const notes     = document.getElementById('notes').value       || '';
    const items     = getItems();

    // Update badge
    document.getElementById('previewTypeBadge').textContent = theme.badge + ' ' + theme.label;
    document.getElementById('previewTypeBadge').style.background = theme.primary;

    let subtotal = items.reduce((s, it) => s + it.amount, 0);
    let taxAmt   = subtotal * taxPct / 100;
    let total    = subtotal + taxAmt - discount;

    // Build items rows HTML
    let itemsHtml = items.map(it => `
        <tr>
            <td style="padding:8px 10px;border-bottom:1px solid #eee;">${escHtml(it.description || '–')}</td>
            <td style="padding:8px 10px;border-bottom:1px solid #eee;text-align:right;">${it.qty}</td>
            <td style="padding:8px 10px;border-bottom:1px solid #eee;text-align:right;">${sym}${it.rate.toFixed(2)}</td>
            <td style="padding:8px 10px;border-bottom:1px solid #eee;text-align:right;font-weight:600;">${sym}${it.amount.toFixed(2)}</td>
        </tr>`).join('');

    if (!itemsHtml) itemsHtml = `<tr><td colspan="4" style="padding:12px;text-align:center;color:#aaa;">No items added</td></tr>`;

    const formatDate = d => {
        if (!d) return '';
        const parts = d.split('-');
        const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
        return `${parts[2]} ${months[parseInt(parts[1])-1]} ${parts[0]}`;
    };

    const html = `
    <div style="background:${theme.primary};color:white;padding:24px 24px 16px;">
        <div style="display:flex;justify-content:space-between;align-items:flex-start;">
            <div>
                <div style="font-size:22px;font-weight:700;letter-spacing:-0.5px;">${theme.badge} ${escHtml(fromName)}</div>
                ${fromAddr  ? `<div style="font-size:11px;opacity:0.85;margin-top:4px;">${escHtml(fromAddr).replace(/\n/g,'<br>')}</div>` : ''}
                ${fromPhone ? `<div style="font-size:11px;opacity:0.85;">📞 ${escHtml(fromPhone)}</div>` : ''}
                ${fromEmail ? `<div style="font-size:11px;opacity:0.85;">✉️ ${escHtml(fromEmail)}</div>` : ''}
            </div>
            <div style="text-align:right;">
                <div style="font-size:16px;font-weight:700;background:rgba(255,255,255,0.2);padding:6px 14px;border-radius:6px;">${escHtml(theme.label).toUpperCase()}</div>
                <div style="font-size:12px;margin-top:8px;opacity:0.9;">Bill # <strong>${escHtml(billNo)}</strong></div>
                <div style="font-size:12px;opacity:0.9;">Date: <strong>${formatDate(billDate)}</strong></div>
            </div>
        </div>
    </div>

    <div style="background:${theme.secondary}22;border-left:4px solid ${theme.secondary};padding:12px 24px;display:flex;justify-content:space-between;align-items:center;">
        <div>
            <div style="font-size:10px;font-weight:700;color:#888;text-transform:uppercase;letter-spacing:0.05em;">Bill To</div>
            <div style="font-size:14px;font-weight:700;color:#222;">${escHtml(toName)}</div>
            ${toAddr  ? `<div style="font-size:11px;color:#555;">${escHtml(toAddr).replace(/\n/g,'<br>')}</div>` : ''}
            ${toPhone ? `<div style="font-size:11px;color:#555;">📞 ${escHtml(toPhone)}</div>` : ''}
            ${toEmail ? `<div style="font-size:11px;color:#555;">✉️ ${escHtml(toEmail)}</div>` : ''}
        </div>
    </div>

    <div style="padding:16px 24px;">
        <table style="width:100%;border-collapse:collapse;font-size:12px;">
            <thead>
                <tr style="background:${theme.primary};color:white;">
                    <th style="padding:8px 10px;text-align:left;">Description</th>
                    <th style="padding:8px 10px;text-align:right;width:50px;">Qty</th>
                    <th style="padding:8px 10px;text-align:right;width:80px;">Rate</th>
                    <th style="padding:8px 10px;text-align:right;width:80px;">Amount</th>
                </tr>
            </thead>
            <tbody style="color:#333;">
                ${itemsHtml}
            </tbody>
        </table>
    </div>

    <div style="padding:0 24px 16px;display:flex;justify-content:flex-end;">
        <div style="min-width:220px;">
            <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:12px;border-bottom:1px solid #eee;">
                <span style="color:#666;">Subtotal</span>
                <span style="font-weight:600;">${sym}${subtotal.toFixed(2)}</span>
            </div>
            ${taxPct > 0 ? `
            <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:12px;border-bottom:1px solid #eee;">
                <span style="color:#666;">Tax (${taxPct}%)</span>
                <span style="font-weight:600;">${sym}${taxAmt.toFixed(2)}</span>
            </div>` : ''}
            ${discount > 0 ? `
            <div style="display:flex;justify-content:space-between;padding:5px 0;font-size:12px;border-bottom:1px solid #eee;">
                <span style="color:#666;">Discount</span>
                <span style="font-weight:600;color:#e53935;">- ${sym}${discount.toFixed(2)}</span>
            </div>` : ''}
            <div style="display:flex;justify-content:space-between;padding:10px 0 5px;font-size:15px;font-weight:700;border-top:2px solid ${theme.primary};">
                <span>Total</span>
                <span style="color:${theme.primary};">${sym}${total.toFixed(2)}</span>
            </div>
        </div>
    </div>

    ${notes ? `
    <div style="margin:0 24px 16px;background:#f9f9f9;border-radius:6px;padding:10px 14px;font-size:11px;color:#555;border-left:3px solid ${theme.secondary};">
        <strong style="display:block;margin-bottom:4px;color:#333;">Notes</strong>
        ${escHtml(notes).replace(/\n/g,'<br>')}
    </div>` : ''}

    <div style="background:${theme.primary};color:white;padding:10px 24px;text-align:center;font-size:11px;opacity:0.9;">
        Generated with BillX • MyMultiBranch
    </div>`;

    document.getElementById('billPreview').innerHTML = html;
}

// ─── Wire up all live inputs ──────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // Add default 1 item row
    addItem('', 1, 0);

    const liveInputs = [
        'bill_type','bill_number','bill_date','currency',
        'from_name','from_address','from_phone','from_email',
        'to_name','to_address','to_phone','to_email',
        'tax_percent','discount_amount','notes'
    ];
    liveInputs.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('input', updatePreview);
    });
    updatePreview();
});
</script>
