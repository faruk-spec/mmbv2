<?php
/** @var array $config @var array $user @var string|null $error */
$csrfToken = \Core\Security::generateCsrfToken();
$selectedType = htmlspecialchars($_GET['type'] ?? 'general');
if (!array_key_exists($selectedType, $config['bill_types'])) $selectedType = 'general';
$billNumber = 'BILL-' . strtoupper(date('Ymd')) . '-' . substr(strtoupper(bin2hex(random_bytes(3))), 0, 6);
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
// ─── Helpers ──────────────────────────────────────────────────────────────────
function escHtml(s){return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');}
const CURRENCY_SYM = {INR:'₹',USD:'$',EUR:'€',GBP:'£'};
function fmtAmt(n,sym){return sym+(+n||0).toFixed(2);}
function fmtDate(d){if(!d)return '';const p=d.split('-');const m=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];return p[2]+' '+m[parseInt(p[1])-1]+' '+p[0];}

// ─── Bill group mapping ───────────────────────────────────────────────────────
const BILL_GROUPS = {
  thermal:   ['restaurant','recharge','mart','newspaper'],
  payslip:   ['driver','helper'],
  fuel:      ['fuel'],
  cab:       ['cab'],
  official:  ['rent','lta'],
  medical:   ['medical'],
  hotel:     ['hotel'],
  gym:       ['gym'],
  invoice:   ['book','internet','ecom','general','stationary'],
};
function getGroup(type){for(const[g,arr] of Object.entries(BILL_GROUPS))if(arr.includes(type))return g;return 'invoice';}

const TYPE_LABELS = {
  fuel:'Fuel Bill',driver:'Driver Salary',helper:'Daily Helper Bill',rent:'Rent Receipt',
  book:'Book Invoice',internet:'Internet Invoice',restaurant:'Restaurant Bill',lta:'LTA Receipt',
  ecom:'E-Com Invoice',general:'General Bill',recharge:'Recharge Receipt',medical:'Medical Bill',
  stationary:'Stationary Bill',cab:'Cab & Travel Bill',mart:'Mart Bill',gym:'Gym Bill',
  hotel:'Hotel Bill',newspaper:'Newspaper Bill',
};
const TYPE_COLORS = {
  fuel:'#e65000',driver:'#1565c0',helper:'#546e7a',rent:'#6a1b9a',book:'#5d4037',
  internet:'#0277bd',restaurant:'#c62828',lta:'#2e7d32',ecom:'#1565c0',general:'#37474f',
  recharge:'#00838f',medical:'#0077b6',stationary:'#bf360c',cab:'#e65100',mart:'#1b5e20',
  gym:'#212121',hotel:'#7d5a00',newspaper:'#1a1a1a',
};

// ─── Items management ─────────────────────────────────────────────────────────
let itemCount=0;
function addItem(desc='',qty=1,rate=0){
    const tbody=document.getElementById('itemsBody');
    const amount=(qty*rate).toFixed(2);
    const tr=document.createElement('tr');tr.className='item-row';
    tr.innerHTML=`<td><input type="text" name="item_description[]" placeholder="Description" value="${escHtml(desc)}" oninput="updatePreview()"></td><td><input type="number" name="item_qty[]" value="${qty}" min="0" step="any" style="text-align:right;" oninput="calcRow(this)"></td><td><input type="number" name="item_rate[]" value="${rate}" min="0" step="any" style="text-align:right;" oninput="calcRow(this)"></td><td style="text-align:right;font-weight:600;padding:4px 8px;" class="row-amount">${amount}</td><td><button type="button" class="remove-item-btn" onclick="removeItem(this)" title="Remove"><i class="fas fa-times"></i></button></td>`;
    tbody.appendChild(tr);updatePreview();
}
function removeItem(btn){btn.closest('tr').remove();updatePreview();}
function calcRow(input){const tr=input.closest('tr');const qty=parseFloat(tr.querySelector('[name="item_qty[]"]').value)||0;const rate=parseFloat(tr.querySelector('[name="item_rate[]"]').value)||0;tr.querySelector('.row-amount').textContent=(qty*rate).toFixed(2);updatePreview();}
function getItems(){return Array.from(document.querySelectorAll('#itemsBody .item-row')).map(tr=>({description:tr.querySelector('[name="item_description[]"]').value,qty:parseFloat(tr.querySelector('[name="item_qty[]"]').value)||0,rate:parseFloat(tr.querySelector('[name="item_rate[]"]').value)||0,amount:parseFloat(tr.querySelector('.row-amount').textContent)||0}));}
function collectData(){
    const type=document.getElementById('bill_type').value;
    const currency=document.getElementById('currency').value;
    const sym=CURRENCY_SYM[currency]||currency+' ';
    const items=getItems();
    const subtotal=items.reduce((s,it)=>s+it.amount,0);
    const taxPct=parseFloat(document.getElementById('tax_percent').value)||0;
    const discount=parseFloat(document.getElementById('discount_amount').value)||0;
    const taxAmt=subtotal*taxPct/100;
    const total=subtotal+taxAmt-discount;
    return{type,sym,currency,billNo:document.getElementById('bill_number').value||'-',billDate:document.getElementById('bill_date').value||'',fromName:document.getElementById('from_name').value||'Issuer',fromAddr:document.getElementById('from_address').value||'',fromPhone:document.getElementById('from_phone').value||'',fromEmail:document.getElementById('from_email').value||'',toName:document.getElementById('to_name').value||'Customer',toAddr:document.getElementById('to_address').value||'',toPhone:document.getElementById('to_phone').value||'',toEmail:document.getElementById('to_email').value||'',taxPct,discount,taxAmt,subtotal,total,notes:document.getElementById('notes').value||'',items};
}

// ─── RENDERER 1: Thermal Receipt ─────────────────────────────────────────────
// (restaurant, recharge, mart, newspaper)
function renderThermal(d){
    const c=TYPE_COLORS[d.type]||'#333';
    const label=TYPE_LABELS[d.type]||d.type;
    const dash='<div style="border-top:1px dashed #999;margin:8px 0;"></div>';
    const items=d.items.map(it=>`<div style="display:flex;justify-content:space-between;padding:2px 0;font-size:12px;"><span style="flex:1;">${escHtml(it.description||'-')}</span><span style="white-space:nowrap;margin-left:8px;">${it.qty}x${d.sym}${it.rate.toFixed(2)}</span><span style="white-space:nowrap;margin-left:8px;font-weight:700;">${d.sym}${it.amount.toFixed(2)}</span></div>`).join('');
    return `<div style="font-family:'Courier New',Courier,monospace;background:#fff;max-width:320px;margin:0 auto;padding:16px 20px;font-size:12px;color:#111;border:1px solid #ddd;box-shadow:2px 2px 8px rgba(0,0,0,.15);">
<div style="text-align:center;font-size:16px;font-weight:900;letter-spacing:1px;text-transform:uppercase;">${escHtml(d.fromName)}</div>
${d.fromAddr?`<div style="text-align:center;font-size:10px;color:#444;margin-top:2px;">${escHtml(d.fromAddr).replace(/\n/g,', ')}</div>`:''}
${d.fromPhone?`<div style="text-align:center;font-size:10px;">Tel: ${escHtml(d.fromPhone)}</div>`:''}
<div style="text-align:center;font-size:11px;font-weight:700;background:${c};color:#fff;padding:3px 0;margin:8px 0;letter-spacing:2px;">${label.toUpperCase()}</div>
${dash}
<div style="display:flex;justify-content:space-between;font-size:11px;"><span>Bill#: <b>${escHtml(d.billNo)}</b></span><span>Date: ${fmtDate(d.billDate)}</span></div>
<div style="font-size:11px;">Customer: <b>${escHtml(d.toName)}</b></div>
${d.toPhone?`<div style="font-size:10px;">Ph: ${escHtml(d.toPhone)}</div>`:''}
${dash}
<div style="display:flex;justify-content:space-between;font-size:10px;font-weight:700;padding:2px 0;border-bottom:1px solid #333;margin-bottom:4px;"><span style="flex:1;">Item</span><span>Qty×Rate</span><span style="margin-left:8px;">Amt</span></div>
${items||'<div style="color:#aaa;text-align:center;padding:8px;">No items</div>'}
${dash}
<div style="display:flex;justify-content:space-between;font-size:11px;"><span>Subtotal</span><span>${d.sym}${d.subtotal.toFixed(2)}</span></div>
${d.taxPct>0?`<div style="display:flex;justify-content:space-between;font-size:11px;"><span>Tax ${d.taxPct}%</span><span>${d.sym}${d.taxAmt.toFixed(2)}</span></div>`:''}
${d.discount>0?`<div style="display:flex;justify-content:space-between;font-size:11px;"><span>Discount</span><span>-${d.sym}${d.discount.toFixed(2)}</span></div>`:''}
<div style="display:flex;justify-content:space-between;font-size:14px;font-weight:900;border-top:2px solid #111;margin-top:4px;padding-top:4px;"><span>TOTAL</span><span>${d.sym}${d.total.toFixed(2)}</span></div>
${dash}
${d.notes?`<div style="font-size:10px;color:#555;text-align:center;margin-bottom:4px;">${escHtml(d.notes)}</div>`:''}
<div style="text-align:center;font-size:11px;margin-top:8px;font-weight:700;">** Thank You! Visit Again **</div>
<div style="text-align:center;font-size:9px;color:#888;margin-top:4px;">Powered by BillX</div>
</div>`;
}

// ─── RENDERER 2: Payslip ──────────────────────────────────────────────────────
// (driver, helper)
// Convention: even-indexed items (0,2,4…) are earnings; odd-indexed (1,3,5…) are deductions.
function renderPayslip(d){
    const c=TYPE_COLORS[d.type]||'#1565c0';
    const label=TYPE_LABELS[d.type]||d.type;
    // Split items into two halves for earnings/deductions table appearance
    const earnings=d.items.filter((_,i)=>i%2===0);
    const deductions=d.items.filter((_,i)=>i%2!==0);
    const maxRows=Math.max(earnings.length,deductions.length,1);
    let rows='';
    for(let i=0;i<maxRows;i++){
        const e=earnings[i];const de=deductions[i];
        rows+=`<tr style="border-bottom:1px solid #eee;">
<td style="padding:5px 8px;font-size:12px;">${e?escHtml(e.description||''):''}</td>
<td style="padding:5px 8px;text-align:right;font-size:12px;font-weight:600;">${e?d.sym+e.amount.toFixed(2):''}</td>
<td style="padding:5px 8px;font-size:12px;border-left:2px solid #ddd;">${de?escHtml(de.description||''):''}</td>
<td style="padding:5px 8px;text-align:right;font-size:12px;font-weight:600;color:#e53935;">${de?'-'+d.sym+de.amount.toFixed(2):''}</td>
</tr>`;
    }
    return `<div style="font-family:Arial,sans-serif;background:#fff;padding:0;font-size:12px;color:#222;border:1px solid #ccc;box-shadow:0 2px 12px rgba(0,0,0,.1);">
<div style="background:${c};color:#fff;padding:16px 20px;display:flex;justify-content:space-between;align-items:center;">
<div><div style="font-size:18px;font-weight:700;">${escHtml(d.fromName)}</div>${d.fromAddr?`<div style="font-size:10px;opacity:.85;">${escHtml(d.fromAddr).replace(/\n/g,', ')}</div>`:''}</div>
<div style="text-align:right;"><div style="font-size:15px;font-weight:900;letter-spacing:1px;">${label.toUpperCase()}</div><div style="font-size:10px;opacity:.9;">Slip # ${escHtml(d.billNo)}</div><div style="font-size:10px;opacity:.9;">Period: ${fmtDate(d.billDate)}</div></div>
</div>
<div style="background:#f3f3f3;padding:10px 20px;display:flex;justify-content:space-between;border-bottom:2px solid ${c};">
<div><span style="font-size:10px;color:#666;display:block;text-transform:uppercase;letter-spacing:.05em;">Employee Name</span><span style="font-size:14px;font-weight:700;">${escHtml(d.toName)}</span>${d.toPhone?`<span style="font-size:10px;color:#555;display:block;">Ph: ${escHtml(d.toPhone)}</span>`:''}</div>
<div style="text-align:right;"><span style="font-size:10px;color:#666;display:block;text-transform:uppercase;letter-spacing:.05em;">Pay Date</span><span style="font-size:13px;font-weight:600;">${fmtDate(d.billDate)}</span></div>
</div>
<table style="width:100%;border-collapse:collapse;">
<thead><tr style="background:#e8eaf6;"><th style="padding:7px 8px;text-align:left;font-size:11px;text-transform:uppercase;color:${c};">Earnings</th><th style="padding:7px 8px;text-align:right;font-size:11px;color:${c};">Amount</th><th style="padding:7px 8px;text-align:left;font-size:11px;text-transform:uppercase;color:#e53935;border-left:2px solid #ddd;">Deductions</th><th style="padding:7px 8px;text-align:right;font-size:11px;color:#e53935;">Amount</th></tr></thead>
<tbody>${rows}</tbody>
</table>
<div style="padding:10px 20px;background:#f8f8f8;border-top:2px solid #ddd;display:flex;justify-content:space-between;align-items:center;">
${d.taxPct>0?`<div style="font-size:11px;color:#666;">Tax ${d.taxPct}% = <b>${d.sym}${d.taxAmt.toFixed(2)}</b></div>`:'<div></div>'}
<div><span style="font-size:11px;color:#555;margin-right:8px;">Total Deductions: <b style="color:#e53935;">${d.sym}${(d.items.filter((_,i)=>i%2!==0).reduce((s,it)=>s+it.amount,0)).toFixed(2)}</b></span></div>
</div>
<div style="background:${c};color:#fff;padding:12px 20px;display:flex;justify-content:space-between;align-items:center;">
<span style="font-size:13px;font-weight:700;">NET SALARY</span>
<span style="font-size:20px;font-weight:900;">${d.sym}${d.total.toFixed(2)}</span>
</div>
${d.notes?`<div style="padding:8px 20px;font-size:10px;color:#666;border-top:1px solid #eee;">${escHtml(d.notes)}</div>`:''}
<div style="padding:14px 20px;display:flex;justify-content:space-between;font-size:11px;border-top:1px solid #eee;">
<div><div style="border-top:1px solid #555;width:120px;padding-top:4px;margin-top:24px;">Employee Signature</div></div>
<div><div style="border-top:1px solid #555;width:120px;padding-top:4px;margin-top:24px;">Authorised Signatory</div></div>
</div>
</div>`;
}

// ─── RENDERER 3: Fuel Slip ────────────────────────────────────────────────────
function renderFuel(d){
    const items=d.items.map(it=>`<tr style="border-bottom:1px solid #ffe0a0;"><td style="padding:5px 8px;font-size:12px;">${escHtml(it.description||'-')}</td><td style="padding:5px 8px;text-align:center;font-size:12px;">${it.qty} L</td><td style="padding:5px 8px;text-align:right;font-size:12px;">${d.sym}${it.rate.toFixed(2)}/L</td><td style="padding:5px 8px;text-align:right;font-weight:700;font-size:12px;">${d.sym}${it.amount.toFixed(2)}</td></tr>`).join('');
    return `<div style="font-family:Arial,sans-serif;background:#fff;max-width:400px;border:2px solid #e65000;border-radius:6px;overflow:hidden;font-size:12px;box-shadow:0 2px 12px rgba(0,0,0,.1);">
<div style="background:#e65000;color:#fff;padding:14px 16px;">
<div style="font-size:20px;font-weight:900;letter-spacing:-0.5px;">⛽ ${escHtml(d.fromName)}</div>
${d.fromAddr?`<div style="font-size:10px;opacity:.85;margin-top:2px;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}
${d.fromPhone?`<div style="font-size:10px;opacity:.85;">📞 ${escHtml(d.fromPhone)}</div>`:''}
</div>
<div style="background:#fff3e0;padding:8px 16px;display:flex;justify-content:space-between;border-bottom:1px dashed #e65000;">
<span style="font-size:11px;font-weight:700;color:#e65000;">FUEL RECEIPT</span>
<span style="font-size:11px;">Bill#: <b>${escHtml(d.billNo)}</b></span>
<span style="font-size:11px;">${fmtDate(d.billDate)}</span>
</div>
<div style="padding:8px 16px;background:#fafafa;border-bottom:1px solid #ffe0b2;">
<div style="font-size:11px;color:#666;text-transform:uppercase;letter-spacing:.05em;">Vehicle / Customer</div>
<div style="font-size:14px;font-weight:700;">${escHtml(d.toName)}</div>
${d.toPhone?`<div style="font-size:11px;color:#555;">📞 ${escHtml(d.toPhone)}</div>`:''}
${d.toAddr?`<div style="font-size:11px;color:#555;">${escHtml(d.toAddr).replace(/\n/g,', ')}</div>`:''}
</div>
<table style="width:100%;border-collapse:collapse;">
<thead><tr style="background:#fff3e0;"><th style="padding:6px 8px;text-align:left;font-size:11px;color:#e65000;">Fuel / Product</th><th style="padding:6px 8px;text-align:center;font-size:11px;color:#e65000;">Qty</th><th style="padding:6px 8px;text-align:right;font-size:11px;color:#e65000;">Rate</th><th style="padding:6px 8px;text-align:right;font-size:11px;color:#e65000;">Amount</th></tr></thead>
<tbody>${items||'<tr><td colspan="4" style="text-align:center;padding:10px;color:#aaa;">No items</td></tr>'}</tbody>
</table>
${d.taxPct>0?`<div style="padding:4px 16px;display:flex;justify-content:space-between;font-size:11px;background:#fff8f0;border-top:1px solid #ffe0b2;"><span>Tax ${d.taxPct}%</span><span>${d.sym}${d.taxAmt.toFixed(2)}</span></div>`:''}
<div style="background:#e65000;color:#fff;padding:10px 16px;display:flex;justify-content:space-between;font-size:15px;font-weight:900;margin-top:0;">
<span>TOTAL AMOUNT</span><span>${d.sym}${d.total.toFixed(2)}</span>
</div>
${d.notes?`<div style="padding:8px 16px;font-size:10px;color:#666;">${escHtml(d.notes)}</div>`:''}
<div style="text-align:center;padding:8px;font-size:10px;color:#999;background:#fff8f0;">Thank you for fueling with us | Powered by BillX</div>
</div>`;
}

// ─── RENDERER 4: Cab / Travel ─────────────────────────────────────────────────
function renderCab(d){
    const items=d.items.map(it=>`<div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #333;font-size:12px;"><span>${escHtml(it.description||'-')}</span><span style="font-weight:700;">${d.sym}${it.amount.toFixed(2)}</span></div>`).join('');
    return `<div style="font-family:Arial,sans-serif;background:#1a1a1a;color:#fff;max-width:360px;border-radius:8px;overflow:hidden;font-size:12px;box-shadow:0 4px 16px rgba(0,0,0,.3);">
<div style="background:#f5a623;padding:14px 16px;color:#1a1a1a;">
<div style="font-size:20px;font-weight:900;">🚕 ${escHtml(d.fromName)}</div>
${d.fromPhone?`<div style="font-size:11px;">📞 ${escHtml(d.fromPhone)}</div>`:''}
${d.fromAddr?`<div style="font-size:10px;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}
</div>
<div style="padding:10px 16px;background:#2a2a2a;border-bottom:1px solid #444;">
<div style="display:flex;justify-content:space-between;"><span style="font-size:11px;color:#f5a623;font-weight:700;">CAB & TRAVEL RECEIPT</span><span style="font-size:11px;color:#aaa;">${fmtDate(d.billDate)}</span></div>
<div style="font-size:11px;color:#aaa;">Receipt#: <b style="color:#fff;">${escHtml(d.billNo)}</b></div>
</div>
<div style="padding:10px 16px;background:#222;border-bottom:1px solid #444;">
<div style="font-size:10px;color:#f5a623;text-transform:uppercase;letter-spacing:.1em;">Passenger</div>
<div style="font-size:14px;font-weight:700;">${escHtml(d.toName)}</div>
${d.toPhone?`<div style="font-size:11px;color:#aaa;">📞 ${escHtml(d.toPhone)}</div>`:''}
${d.toAddr?`<div style="font-size:10px;color:#aaa;">From: ${escHtml(d.toAddr).replace(/\n/g,' ')}</div>`:''}
</div>
<div style="padding:10px 16px;">
${items||'<div style="color:#666;text-align:center;">No items</div>'}
${d.taxPct>0?`<div style="display:flex;justify-content:space-between;padding:4px 0;font-size:11px;color:#aaa;"><span>Tax ${d.taxPct}%</span><span>${d.sym}${d.taxAmt.toFixed(2)}</span></div>`:''}
${d.discount>0?`<div style="display:flex;justify-content:space-between;padding:4px 0;font-size:11px;color:#aaa;"><span>Discount</span><span>-${d.sym}${d.discount.toFixed(2)}</span></div>`:''}
</div>
<div style="background:#f5a623;color:#1a1a1a;padding:12px 16px;display:flex;justify-content:space-between;font-size:16px;font-weight:900;">
<span>FARE TOTAL</span><span>${d.sym}${d.total.toFixed(2)}</span>
</div>
${d.notes?`<div style="padding:8px 16px;font-size:10px;color:#aaa;border-top:1px solid #444;">${escHtml(d.notes)}</div>`:''}
<div style="text-align:center;padding:8px;font-size:9px;color:#666;">Safe Journey! | BillX</div>
</div>`;
}

// ─── RENDERER 5: Official Receipt ─────────────────────────────────────────────
// (rent, lta)
function renderOfficial(d){
    const c=TYPE_COLORS[d.type]||'#2e7d32';
    const label=TYPE_LABELS[d.type]||'Receipt';
    const items=d.items.map(it=>`<div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px dotted #bbb;font-size:12px;"><span>${escHtml(it.description||'-')}</span><span style="font-weight:600;">${d.sym}${it.amount.toFixed(2)}</span></div>`).join('');
    return `<div style="font-family:Georgia,serif;background:#fff;padding:24px 28px;font-size:12px;color:#222;border:2px solid ${c};position:relative;box-shadow:0 2px 12px rgba(0,0,0,.1);">
<div style="position:absolute;top:10px;right:12px;font-size:64px;color:${c};opacity:.06;font-weight:900;pointer-events:none;user-select:none;">ORIGINAL</div>
<div style="text-align:center;margin-bottom:12px;">
<div style="font-size:11px;letter-spacing:3px;text-transform:uppercase;color:${c};font-weight:700;">— Official —</div>
<div style="font-size:22px;font-weight:900;letter-spacing:1px;color:${c};">${label.toUpperCase()}</div>
<div style="font-size:16px;font-weight:700;">${escHtml(d.fromName)}</div>
${d.fromAddr?`<div style="font-size:11px;color:#555;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}
${d.fromPhone?`<div style="font-size:11px;color:#555;">📞 ${escHtml(d.fromPhone)}</div>`:''}
</div>
<div style="border-top:3px double ${c};border-bottom:3px double ${c};padding:8px 0;margin-bottom:12px;">
<div style="display:flex;justify-content:space-between;">
<span>Receipt No.: <b>${escHtml(d.billNo)}</b></span>
<span>Date: <b>${fmtDate(d.billDate)}</b></span>
</div>
</div>
<div style="margin-bottom:12px;padding:10px;background:#f9fdf9;border-left:4px solid ${c};">
<div style="font-size:11px;color:#666;margin-bottom:4px;">Received with thanks from:</div>
<div style="font-size:15px;font-weight:700;">${escHtml(d.toName)}</div>
${d.toAddr?`<div style="font-size:11px;color:#555;">${escHtml(d.toAddr).replace(/\n/g,', ')}</div>`:''}
${d.toPhone?`<div style="font-size:11px;color:#555;">Contact: ${escHtml(d.toPhone)}</div>`:''}
</div>
<div style="margin-bottom:8px;font-size:11px;color:#555;text-transform:uppercase;letter-spacing:.05em;font-weight:600;">Particulars</div>
${items||`<div style="padding:6px 0;border-bottom:1px dotted #bbb;font-size:12px;color:#aaa;">No items specified</div>`}
<div style="margin-top:12px;border-top:1px solid #ccc;padding-top:8px;">
${d.taxPct>0?`<div style="display:flex;justify-content:space-between;font-size:11px;color:#666;margin-bottom:2px;"><span>Tax ${d.taxPct}%</span><span>${d.sym}${d.taxAmt.toFixed(2)}</span></div>`:''}
${d.discount>0?`<div style="display:flex;justify-content:space-between;font-size:11px;color:#666;margin-bottom:2px;"><span>Discount</span><span>-${d.sym}${d.discount.toFixed(2)}</span></div>`:''}
<div style="display:flex;justify-content:space-between;font-size:16px;font-weight:900;color:${c};border-top:2px solid ${c};padding-top:6px;margin-top:4px;">
<span>Total Amount Received</span><span>${d.sym}${d.total.toFixed(2)}</span>
</div>
</div>
${d.notes?`<div style="margin-top:10px;font-size:11px;color:#555;font-style:italic;border-top:1px dashed #ccc;padding-top:8px;">Note: ${escHtml(d.notes)}</div>`:''}
<div style="margin-top:20px;display:flex;justify-content:space-between;font-size:11px;">
<div><div style="border-top:1px solid #555;width:130px;padding-top:4px;margin-top:30px;">Receiver's Signature</div></div>
<div style="text-align:right;"><div style="border-top:1px solid #555;width:130px;padding-top:4px;margin-top:30px;">Issuer's Signature</div></div>
</div>
<div style="text-align:center;font-size:9px;color:#aaa;margin-top:12px;border-top:1px dashed #ddd;padding-top:6px;">Generated by BillX | This is a computer-generated receipt</div>
</div>`;
}

// ─── RENDERER 6: Medical Bill ─────────────────────────────────────────────────
function renderMedical(d){
    const items=d.items.map((it,i)=>`<tr style="background:${i%2===0?'#f0f7ff':'#fff'};"><td style="padding:6px 10px;font-size:12px;">${escHtml(it.description||'-')}</td><td style="padding:6px 10px;text-align:center;font-size:12px;">${it.qty}</td><td style="padding:6px 10px;text-align:right;font-size:12px;">${d.sym}${it.rate.toFixed(2)}</td><td style="padding:6px 10px;text-align:right;font-weight:700;font-size:12px;">${d.sym}${it.amount.toFixed(2)}</td></tr>`).join('');
    return `<div style="font-family:Arial,sans-serif;background:#fff;font-size:12px;color:#222;border:1px solid #c8e6ff;box-shadow:0 2px 12px rgba(0,0,0,.1);">
<div style="background:#0077b6;color:#fff;padding:14px 20px;display:flex;justify-content:space-between;align-items:center;">
<div><div style="font-size:20px;font-weight:900;">🏥 ${escHtml(d.fromName)}</div>${d.fromAddr?`<div style="font-size:10px;opacity:.85;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}<div style="font-size:10px;opacity:.85;">${d.fromPhone?'📞 '+escHtml(d.fromPhone)+'  ':''}${d.fromEmail?'✉ '+escHtml(d.fromEmail):''}</div></div>
<div style="text-align:center;"><div style="width:48px;height:48px;background:#fff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 4px;"><span style="color:#0077b6;font-size:24px;font-weight:900;">✚</span></div><div style="font-size:9px;opacity:.85;letter-spacing:1px;">MEDICAL BILL</div></div>
</div>
<div style="background:#e3f2fd;padding:10px 20px;display:flex;justify-content:space-between;border-bottom:2px solid #0077b6;">
<div><span style="font-size:10px;color:#0077b6;display:block;text-transform:uppercase;letter-spacing:.05em;">Patient Name</span><span style="font-size:14px;font-weight:700;">${escHtml(d.toName)}</span>${d.toPhone?`<span style="font-size:10px;color:#555;display:block;">📞 ${escHtml(d.toPhone)}</span>`:''}</div>
<div style="text-align:right;"><span style="font-size:10px;color:#0077b6;display:block;text-transform:uppercase;letter-spacing:.05em;">Bill Info</span><span style="font-size:12px;font-weight:600;display:block;">Bill # ${escHtml(d.billNo)}</span><span style="font-size:12px;">Date: ${fmtDate(d.billDate)}</span></div>
</div>
<table style="width:100%;border-collapse:collapse;">
<thead><tr style="background:#0077b6;color:#fff;"><th style="padding:7px 10px;text-align:left;font-size:11px;">Service / Medication</th><th style="padding:7px 10px;text-align:center;font-size:11px;width:50px;">Qty</th><th style="padding:7px 10px;text-align:right;font-size:11px;width:80px;">Rate</th><th style="padding:7px 10px;text-align:right;font-size:11px;width:80px;">Amount</th></tr></thead>
<tbody>${items||'<tr><td colspan="4" style="padding:12px;text-align:center;color:#aaa;">No services</td></tr>'}</tbody>
</table>
<div style="padding:10px 20px;background:#f5f5f5;border-top:1px solid #c8e6ff;">
<div style="display:flex;justify-content:flex-end;"><div style="min-width:220px;">
${d.subtotal!==d.total?`<div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;"><span style="color:#666;">Subtotal</span><span>${d.sym}${d.subtotal.toFixed(2)}</span></div>`:''}
${d.taxPct>0?`<div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;"><span style="color:#666;">Tax ${d.taxPct}%</span><span>${d.sym}${d.taxAmt.toFixed(2)}</span></div>`:''}
${d.discount>0?`<div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;"><span style="color:#666;">Discount</span><span style="color:#e53935;">-${d.sym}${d.discount.toFixed(2)}</span></div>`:''}
<div style="display:flex;justify-content:space-between;font-size:15px;font-weight:900;border-top:2px solid #0077b6;padding-top:6px;margin-top:4px;color:#0077b6;"><span>Total</span><span>${d.sym}${d.total.toFixed(2)}</span></div>
</div></div>
</div>
${d.notes?`<div style="padding:8px 20px;font-size:11px;color:#555;border-top:1px solid #e0e0e0;background:#fafafa;">${escHtml(d.notes)}</div>`:''}
<div style="background:#0077b6;color:#fff;padding:8px 20px;display:flex;justify-content:space-between;font-size:10px;"><span>Get well soon! 🙏</span><span>Powered by BillX</span></div>
</div>`;
}

// ─── RENDERER 7: Hotel Folio ──────────────────────────────────────────────────
function renderHotel(d){
    const items=d.items.map((it,i)=>`<tr style="background:${i%2===0?'#fdf8ee':'#fff'};border-bottom:1px solid #e8d89a;"><td style="padding:8px 12px;font-size:12px;">${escHtml(it.description||'-')}</td><td style="padding:8px 12px;text-align:center;font-size:12px;">${it.qty}</td><td style="padding:8px 12px;text-align:right;font-size:12px;">${d.sym}${it.rate.toFixed(2)}</td><td style="padding:8px 12px;text-align:right;font-weight:700;font-size:12px;">${d.sym}${it.amount.toFixed(2)}</td></tr>`).join('');
    return `<div style="font-family:Georgia,serif;background:#fffdf5;font-size:12px;color:#333;border:1px solid #c9a84c;box-shadow:0 4px 20px rgba(0,0,0,.15);">
<div style="background:linear-gradient(135deg,#7d5a00,#c9a84c);color:#fff;padding:20px 24px;text-align:center;">
<div style="font-size:10px;letter-spacing:4px;text-transform:uppercase;opacity:.8;margin-bottom:4px;">★ ★ ★</div>
<div style="font-size:22px;font-weight:700;letter-spacing:1px;">${escHtml(d.fromName)}</div>
${d.fromAddr?`<div style="font-size:10px;opacity:.85;margin-top:4px;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}
${d.fromPhone?`<div style="font-size:10px;opacity:.85;">📞 ${escHtml(d.fromPhone)}</div>`:''}
<div style="font-size:11px;letter-spacing:3px;text-transform:uppercase;margin-top:8px;opacity:.9;">— Hotel Folio —</div>
</div>
<div style="background:#fdf0c0;padding:10px 24px;border-bottom:2px solid #c9a84c;display:flex;justify-content:space-between;">
<div><span style="font-size:10px;color:#7d5a00;display:block;text-transform:uppercase;letter-spacing:.05em;">Guest Name</span><span style="font-size:14px;font-weight:700;">${escHtml(d.toName)}</span>${d.toPhone?`<span style="font-size:10px;color:#7d5a00;display:block;">📞 ${escHtml(d.toPhone)}</span>`:''}</div>
<div style="text-align:right;"><span style="font-size:10px;color:#7d5a00;display:block;text-transform:uppercase;letter-spacing:.05em;">Folio Details</span><span style="font-size:12px;display:block;">Folio #: <b>${escHtml(d.billNo)}</b></span><span style="font-size:12px;">Date: ${fmtDate(d.billDate)}</span></div>
</div>
<table style="width:100%;border-collapse:collapse;">
<thead><tr style="background:#c9a84c;color:#fff;"><th style="padding:8px 12px;text-align:left;font-size:11px;">Description / Service</th><th style="padding:8px 12px;text-align:center;font-size:11px;width:60px;">Nights/Qty</th><th style="padding:8px 12px;text-align:right;font-size:11px;width:80px;">Rate</th><th style="padding:8px 12px;text-align:right;font-size:11px;width:90px;">Amount</th></tr></thead>
<tbody>${items||'<tr><td colspan="4" style="padding:12px;text-align:center;color:#aaa;">No charges</td></tr>'}</tbody>
</table>
<div style="padding:10px 24px;background:#fdf8ee;border-top:1px solid #e8d89a;">
<div style="display:flex;justify-content:flex-end;"><div style="min-width:220px;">
${d.subtotal!==d.total?`<div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-bottom:1px solid #e8d89a;"><span style="color:#777;">Subtotal</span><span>${d.sym}${d.subtotal.toFixed(2)}</span></div>`:''}
${d.taxPct>0?`<div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-bottom:1px solid #e8d89a;"><span style="color:#777;">Tax/Service ${d.taxPct}%</span><span>${d.sym}${d.taxAmt.toFixed(2)}</span></div>`:''}
${d.discount>0?`<div style="display:flex;justify-content:space-between;font-size:11px;padding:3px 0;border-bottom:1px solid #e8d89a;"><span style="color:#777;">Discount</span><span style="color:#e53935;">-${d.sym}${d.discount.toFixed(2)}</span></div>`:''}
<div style="display:flex;justify-content:space-between;font-size:16px;font-weight:700;border-top:2px solid #c9a84c;padding-top:6px;margin-top:4px;color:#7d5a00;"><span>Total Amount</span><span>${d.sym}${d.total.toFixed(2)}</span></div>
</div></div>
</div>
${d.notes?`<div style="padding:8px 24px;font-size:11px;color:#7d5a00;font-style:italic;border-top:1px solid #e8d89a;">${escHtml(d.notes)}</div>`:''}
<div style="background:linear-gradient(135deg,#7d5a00,#c9a84c);color:#fff;padding:10px 24px;display:flex;justify-content:space-between;font-size:10px;opacity:.9;"><span>Thank you for your stay! 🌟</span><span>Powered by BillX</span></div>
</div>`;
}

// ─── RENDERER 8: Gym Invoice ──────────────────────────────────────────────────
function renderGym(d){
    const items=d.items.map(it=>`<div style="display:flex;justify-content:space-between;padding:6px 0;border-bottom:1px solid #333;font-size:12px;"><span>${escHtml(it.description||'-')}</span><span style="color:#ff6f00;font-weight:700;">${d.sym}${it.amount.toFixed(2)}</span></div>`).join('');
    return `<div style="font-family:Arial,sans-serif;background:#181818;color:#f0f0f0;font-size:12px;box-shadow:0 4px 20px rgba(0,0,0,.4);">
<div style="background:linear-gradient(135deg,#212121,#333);padding:16px 20px;border-bottom:3px solid #ff6f00;">
<div style="display:flex;justify-content:space-between;align-items:center;">
<div><div style="font-size:22px;font-weight:900;letter-spacing:-0.5px;">💪 ${escHtml(d.fromName)}</div>${d.fromAddr?`<div style="font-size:10px;color:#aaa;margin-top:2px;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}<div style="font-size:10px;color:#aaa;">${d.fromPhone?'📞 '+escHtml(d.fromPhone):''}</div></div>
<div style="text-align:right;"><div style="font-size:13px;font-weight:900;color:#ff6f00;letter-spacing:2px;">GYM INVOICE</div><div style="font-size:10px;color:#aaa;">Inv # ${escHtml(d.billNo)}</div><div style="font-size:10px;color:#aaa;">${fmtDate(d.billDate)}</div></div>
</div>
</div>
<div style="padding:10px 20px;background:#242424;border-bottom:1px solid #444;">
<span style="font-size:10px;color:#ff6f00;display:block;text-transform:uppercase;letter-spacing:.1em;">Member</span>
<span style="font-size:14px;font-weight:700;">${escHtml(d.toName)}</span>
${d.toPhone?`<span style="font-size:11px;color:#aaa;display:block;">📞 ${escHtml(d.toPhone)}</span>`:''}
</div>
<div style="padding:12px 20px;border-bottom:1px solid #444;">
${items||'<div style="color:#666;text-align:center;padding:8px;">No items</div>'}
</div>
<div style="padding:10px 20px;background:#242424;">
${d.taxPct>0?`<div style="display:flex;justify-content:space-between;font-size:11px;color:#aaa;padding:2px 0;"><span>Tax ${d.taxPct}%</span><span>${d.sym}${d.taxAmt.toFixed(2)}</span></div>`:''}
${d.discount>0?`<div style="display:flex;justify-content:space-between;font-size:11px;color:#aaa;padding:2px 0;"><span>Discount</span><span>-${d.sym}${d.discount.toFixed(2)}</span></div>`:''}
</div>
<div style="background:#ff6f00;color:#fff;padding:12px 20px;display:flex;justify-content:space-between;font-size:16px;font-weight:900;letter-spacing:-0.5px;">
<span>TOTAL DUE</span><span>${d.sym}${d.total.toFixed(2)}</span>
</div>
${d.notes?`<div style="padding:8px 20px;font-size:10px;color:#aaa;border-top:1px solid #444;">${escHtml(d.notes)}</div>`:''}
<div style="text-align:center;padding:8px;font-size:9px;color:#555;background:#111;">Stay strong! 💪 | BillX</div>
</div>`;
}

// ─── RENDERER 9: Standard Professional Invoice ────────────────────────────────
// (book, internet, ecom, general, stationary)
function renderInvoice(d){
    const c=TYPE_COLORS[d.type]||'#37474f';
    const label=TYPE_LABELS[d.type]||'Invoice';
    const items=d.items.map((it,i)=>`<tr style="background:${i%2===0?'#fafafa':'#fff'};"><td style="padding:7px 10px;font-size:12px;border-bottom:1px solid #eee;">${i+1}</td><td style="padding:7px 10px;font-size:12px;border-bottom:1px solid #eee;">${escHtml(it.description||'-')}</td><td style="padding:7px 10px;text-align:center;font-size:12px;border-bottom:1px solid #eee;">${it.qty}</td><td style="padding:7px 10px;text-align:right;font-size:12px;border-bottom:1px solid #eee;">${d.sym}${it.rate.toFixed(2)}</td><td style="padding:7px 10px;text-align:right;font-weight:700;font-size:12px;border-bottom:1px solid #eee;">${d.sym}${it.amount.toFixed(2)}</td></tr>`).join('');
    return `<div style="font-family:Arial,sans-serif;background:#fff;font-size:12px;color:#222;border:1px solid #ddd;box-shadow:0 2px 12px rgba(0,0,0,.1);">
<div style="background:${c};color:#fff;padding:20px 24px;">
<div style="display:flex;justify-content:space-between;align-items:flex-start;">
<div><div style="font-size:22px;font-weight:700;">${escHtml(d.fromName)}</div>${d.fromAddr?`<div style="font-size:10px;opacity:.85;margin-top:4px;">${escHtml(d.fromAddr).replace(/\n/g,' | ')}</div>`:''}<div style="font-size:10px;opacity:.85;">${d.fromPhone?'📞 '+escHtml(d.fromPhone)+'  ':''}${d.fromEmail?'✉ '+escHtml(d.fromEmail):''}</div></div>
<div style="text-align:right;"><div style="font-size:26px;font-weight:900;letter-spacing:-1px;opacity:.9;">INVOICE</div><div style="font-size:11px;opacity:.85;"># ${escHtml(d.billNo)}</div><div style="font-size:11px;opacity:.85;">Date: ${fmtDate(d.billDate)}</div><div style="font-size:10px;margin-top:4px;background:rgba(255,255,255,.2);padding:2px 8px;border-radius:4px;">${escHtml(label)}</div></div>
</div>
</div>
<div style="padding:12px 24px;background:#f7f7f7;border-bottom:2px solid ${c};display:flex;justify-content:space-between;">
<div><span style="font-size:10px;color:#888;text-transform:uppercase;letter-spacing:.05em;display:block;">Bill To</span><span style="font-size:14px;font-weight:700;">${escHtml(d.toName)}</span>${d.toAddr?`<span style="font-size:11px;color:#555;display:block;">${escHtml(d.toAddr).replace(/\n/g,' | ')}</span>`:''}${d.toPhone?`<span style="font-size:11px;color:#555;">📞 ${escHtml(d.toPhone)}</span>`:''}</div>
<div style="text-align:right;">${d.toEmail?`<span style="font-size:11px;color:#555;">✉ ${escHtml(d.toEmail)}</span>`:''}</div>
</div>
<table style="width:100%;border-collapse:collapse;">
<thead><tr style="background:${c};color:#fff;"><th style="padding:8px 10px;text-align:center;font-size:11px;width:30px;">#</th><th style="padding:8px 10px;text-align:left;font-size:11px;">Description</th><th style="padding:8px 10px;text-align:center;font-size:11px;width:50px;">Qty</th><th style="padding:8px 10px;text-align:right;font-size:11px;width:80px;">Unit Price</th><th style="padding:8px 10px;text-align:right;font-size:11px;width:90px;">Amount</th></tr></thead>
<tbody>${items||'<tr><td colspan="5" style="padding:14px;text-align:center;color:#aaa;">No items added</td></tr>'}</tbody>
</table>
<div style="padding:12px 24px 16px;display:flex;justify-content:flex-end;background:#fafafa;border-top:1px solid #eee;">
<div style="min-width:240px;">
<div style="display:flex;justify-content:space-between;padding:4px 0;font-size:12px;border-bottom:1px solid #eee;"><span style="color:#666;">Subtotal</span><span style="font-weight:600;">${d.sym}${d.subtotal.toFixed(2)}</span></div>
${d.taxPct>0?`<div style="display:flex;justify-content:space-between;padding:4px 0;font-size:12px;border-bottom:1px solid #eee;"><span style="color:#666;">Tax ${d.taxPct}%</span><span style="font-weight:600;">${d.sym}${d.taxAmt.toFixed(2)}</span></div>`:''}
${d.discount>0?`<div style="display:flex;justify-content:space-between;padding:4px 0;font-size:12px;border-bottom:1px solid #eee;"><span style="color:#666;">Discount</span><span style="font-weight:600;color:#e53935;">-${d.sym}${d.discount.toFixed(2)}</span></div>`:''}
<div style="display:flex;justify-content:space-between;padding:8px 0 4px;font-size:16px;font-weight:900;border-top:2px solid ${c};color:${c};"><span>Total</span><span>${d.sym}${d.total.toFixed(2)}</span></div>
</div>
</div>
${d.notes?`<div style="margin:0 24px 16px;background:#f5f5f5;border-radius:4px;padding:10px;font-size:11px;color:#555;border-left:3px solid ${c};"><b>Terms & Notes:</b> ${escHtml(d.notes)}</div>`:''}
<div style="background:${c};color:rgba(255,255,255,.7);padding:8px 24px;text-align:center;font-size:10px;">Thank you for your business! | Generated by BillX</div>
</div>`;
}

// ─── Main dispatcher ──────────────────────────────────────────────────────────
function updatePreview(){
    const d=collectData();
    const group=getGroup(d.type);
    const label=TYPE_LABELS[d.type]||d.type;
    const c=TYPE_COLORS[d.type]||'#333';
    document.getElementById('previewTypeBadge').textContent=label;
    document.getElementById('previewTypeBadge').style.background=c;
    let html='';
    switch(group){
        case 'thermal':  html=renderThermal(d); break;
        case 'payslip':  html=renderPayslip(d); break;
        case 'fuel':     html=renderFuel(d);    break;
        case 'cab':      html=renderCab(d);     break;
        case 'official': html=renderOfficial(d);break;
        case 'medical':  html=renderMedical(d); break;
        case 'hotel':    html=renderHotel(d);   break;
        case 'gym':      html=renderGym(d);     break;
        default:         html=renderInvoice(d); break;
    }
    const wrapper=document.getElementById('billPreview');
    wrapper.style.background='transparent';
    wrapper.style.borderRadius='0';
    wrapper.style.boxShadow='none';
    wrapper.innerHTML=html;
}

// ─── Wire up all live inputs ──────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
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
