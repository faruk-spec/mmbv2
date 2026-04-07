<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php $title = 'BillX – Features'; ?>

<?php View::section('styles'); ?>
<style>

/* Feature Page – shared styles. Theme-aware via platform CSS variables. */
.fp-wrap{max-width:1100px;margin:0 auto;padding:0 20px 80px;}

/* ── Hero ── */
.fp-hero{padding:48px 0 40px;text-align:center;position:relative;}
.fp-hero::before{content:'';position:absolute;top:-40px;left:50%;transform:translateX(-50%);
  width:700px;height:420px;
  background:radial-gradient(ellipse,rgba(153,69,255,.14) 0%,transparent 70%);
  pointer-events:none;z-index:0;}
.fp-badge{display:inline-flex;align-items:center;gap:7px;
  background:rgba(153,69,255,.12);border:1px solid rgba(153,69,255,.35);
  color:var(--purple);padding:5px 16px;border-radius:50px;
  font-size:.78rem;font-weight:600;text-transform:uppercase;letter-spacing:.06em;
  margin-bottom:18px;position:relative;z-index:1;}
.fp-hero h1{font-size:clamp(1.9rem,5vw,3.4rem);font-weight:800;line-height:1.1;
  margin-bottom:14px;
  background:linear-gradient(135deg,var(--purple),var(--cyan));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
  position:relative;z-index:1;}
.fp-hero p{font-size:1.03rem;color:var(--text-secondary);
  max-width:560px;margin:0 auto 28px;position:relative;z-index:1;}
.fp-btns{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;position:relative;z-index:1;}
.fp-btn{background:linear-gradient(135deg,var(--purple),var(--cyan));
  color:#fff;padding:11px 26px;border-radius:50px;font-weight:600;font-size:.93rem;
  display:inline-flex;align-items:center;gap:7px;transition:var(--transition);}
.fp-btn:hover{opacity:.85;transform:translateY(-2px);color:#fff;}
.fp-btn-o{border:1px solid rgba(0,240,255,.35);color:var(--cyan);
  padding:11px 26px;border-radius:50px;font-weight:600;font-size:.93rem;
  display:inline-flex;align-items:center;gap:7px;transition:var(--transition);}
.fp-btn-o:hover{background:rgba(0,240,255,.08);border-color:var(--cyan);}

/* ── Stats ── */
.fp-stats{display:flex;gap:32px;justify-content:center;flex-wrap:wrap;
  margin-top:36px;padding:20px 0;
  border-top:1px solid var(--border-color);border-bottom:1px solid var(--border-color);}
.fp-stat{text-align:center;}
.fp-sn{font-size:1.7rem;font-weight:800;
  background:linear-gradient(135deg,var(--purple),var(--cyan));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.fp-sl{font-size:.7rem;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.07em;}

/* ── Section ── */
.fp-sec{padding:56px 0;}
.fp-sec-hd{text-align:center;margin-bottom:40px;}
.fp-lbl{display:inline-block;
  background:rgba(0,240,255,.08);border:1px solid rgba(0,240,255,.2);
  color:var(--cyan);padding:4px 13px;border-radius:50px;
  font-size:.71rem;font-weight:600;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;}
.fp-sec-hd h2{font-size:clamp(1.45rem,3.4vw,2.2rem);font-weight:700;
  margin-bottom:9px;color:var(--text-primary);}
.fp-sec-hd p{color:var(--text-secondary);max-width:500px;margin:0 auto;}
.fp-alt{background:var(--bg-secondary);border-radius:16px;padding:56px 28px;margin:0 -10px;}
@media(max-width:600px){.fp-alt{padding:40px 16px;margin:0;}}

/* ── Collapsible Category ── */
.fp-cat{margin-bottom:34px;}
.fp-cat-btn{display:flex;align-items:center;gap:10px;width:100%;background:none;
  border:none;border-bottom:1px solid var(--border-color);
  padding-bottom:11px;margin-bottom:20px;text-align:left;
  cursor:pointer;font-family:inherit;color:var(--text-primary);
  font-size:.98rem;font-weight:700;transition:var(--transition);}
.fp-cat-btn:hover{color:var(--cyan);}
.fp-dot{width:9px;height:9px;border-radius:50%;flex-shrink:0;}
.fp-arr{margin-left:auto;font-size:.78rem;color:var(--text-secondary);
  transition:transform .24s ease;display:none;}
.fp-cat-btn[aria-expanded="false"] .fp-arr{transform:rotate(-90deg);}
@media(max-width:768px){
  .fp-arr{display:inline-block;}
  .fp-cat-body.is-closed{display:none;}
}

/* ── Feature Cards Grid ── */
.fp-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:17px;}
@media(max-width:900px){.fp-grid{grid-template-columns:repeat(2,1fr);}}
@media(max-width:560px){.fp-grid{grid-template-columns:1fr;}}

.fp-card{background:var(--bg-card);border:1px solid var(--border-color);
  border-radius:13px;padding:22px;transition:var(--transition);}
.fp-card:hover{border-color:rgba(0,240,255,.3);transform:translateY(-3px);box-shadow:var(--shadow);}
.fp-ic{width:46px;height:46px;border-radius:11px;display:flex;align-items:center;
  justify-content:center;font-size:1.15rem;margin-bottom:14px;}
.ic-c{background:rgba(0,240,255,.1);color:var(--cyan);}
.ic-p{background:rgba(153,69,255,.12);color:var(--purple);}
.ic-g{background:rgba(0,255,136,.08);color:var(--green);}
.ic-o{background:rgba(255,170,0,.1);color:var(--orange);}
.ic-m{background:rgba(255,46,196,.1);color:var(--magenta);}
.fp-card h3{font-size:.95rem;font-weight:600;margin-bottom:7px;color:var(--text-primary);}
.fp-card p{color:var(--text-secondary);font-size:.83rem;line-height:1.63;}
.fp-tag{display:inline-block;margin-top:10px;
  background:rgba(153,69,255,.14);color:var(--purple);
  font-size:.67rem;font-weight:600;padding:2px 9px;border-radius:50px;}

/* ── API Block ── */
.fp-api{background:var(--bg-card);border:1px solid var(--border-color);
  border-radius:13px;overflow:hidden;margin-bottom:18px;}
.fp-api-h{display:flex;align-items:center;gap:9px;padding:11px 16px;
  background:rgba(153,69,255,.07);border-bottom:1px solid var(--border-color);}
.fp-m{padding:3px 9px;border-radius:5px;font-size:.71rem;font-weight:700;}
.fp-m-get{background:rgba(0,255,136,.12);color:var(--green);}
.fp-m-post{background:rgba(0,240,255,.12);color:var(--cyan);}
.fp-m-patch{background:rgba(255,170,0,.12);color:var(--orange);}
.fp-api-p{font-family:'Courier New',monospace;font-size:.81rem;color:var(--text-primary);}
.fp-api-b{padding:13px 16px;font-family:'Courier New',monospace;
  font-size:.79rem;color:var(--text-secondary);line-height:1.8;overflow-x:auto;}

/* ── Steps ── */
.fp-steps{display:grid;grid-template-columns:repeat(auto-fill,minmax(155px,1fr));gap:14px;}
.fp-step{background:var(--bg-card);border:1px solid var(--border-color);
  border-radius:13px;padding:20px 14px;text-align:center;}
.fp-step-n{width:32px;height:32px;border-radius:50%;
  background:linear-gradient(135deg,var(--purple),var(--cyan));
  display:flex;align-items:center;justify-content:center;
  font-weight:700;font-size:.86rem;margin:0 auto 11px;}
.fp-step h4{font-size:.86rem;font-weight:600;margin-bottom:5px;color:var(--text-primary);}
.fp-step p{font-size:.77rem;color:var(--text-secondary);}

/* ── Table ── */
.fp-tw{overflow-x:auto;border-radius:13px;border:1px solid var(--border-color);}
.fp-t{width:100%;border-collapse:collapse;background:var(--bg-card);}
.fp-t th{background:rgba(153,69,255,.08);color:var(--text-primary);
  padding:12px 15px;text-align:left;font-size:.8rem;font-weight:600;
  border-bottom:1px solid var(--border-color);}
.fp-t td{padding:10px 15px;color:var(--text-secondary);font-size:.8rem;
  border-bottom:1px solid var(--border-color);}
.fp-t tr:last-child td{border-bottom:none;}
.fp-t tr:hover td{background:rgba(0,240,255,.03);color:var(--text-primary);}
.ck{color:var(--green);}.no{color:var(--red);}.pt{color:var(--orange);}

/* ── Highlight box ── */
.fp-hl{background:linear-gradient(135deg,rgba(153,69,255,.1),rgba(0,240,255,.06));
  border:1px solid rgba(153,69,255,.22);border-radius:13px;
  padding:30px;text-align:center;margin:36px 0;}
.fp-hl h3{font-size:1.25rem;font-weight:700;margin-bottom:9px;
  background:linear-gradient(135deg,var(--purple),var(--cyan));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
.fp-hl p{color:var(--text-secondary);max-width:520px;margin:0 auto;}

/* ── Format badges ── */
.fp-fmts{display:grid;grid-template-columns:repeat(auto-fill,minmax(185px,1fr));gap:9px;}
.fp-fmt{background:var(--bg-card);border:1px solid var(--border-color);
  border-radius:9px;padding:11px 14px;display:flex;align-items:center;gap:8px;
  font-size:.8rem;font-weight:500;color:var(--text-secondary);transition:var(--transition);}
.fp-fmt:hover{border-color:var(--cyan);color:var(--cyan);}

/* Tabs */
.fp-tabs{display:flex;gap:5px;border-bottom:1px solid var(--border-color);
  margin-bottom:22px;flex-wrap:wrap;}
.fp-tab-btn{background:none;border:none;border-bottom:2px solid transparent;
  color:var(--text-secondary);padding:8px 16px;font-family:inherit;
  font-size:.82rem;font-weight:500;cursor:pointer;transition:var(--transition);margin-bottom:-1px;}
.fp-tab-btn.active{color:var(--cyan);border-bottom-color:var(--cyan);}
.fp-tab-btn:hover{color:var(--text-primary);}
.fp-tab-p{display:none;}.fp-tab-p.active{display:block;}

/* Scroll-in animation */
.fp-anim{opacity:0;transform:translateY(16px);transition:opacity .42s ease,transform .42s ease;}
.fp-anim.vis{opacity:1;transform:translateY(0);}

</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div class="fp-wrap" style="padding-top:30px;">
<section class="fp-hero fp-anim">
  <div class="fp-badge"><i class="fas fa-file-invoice-dollar"></i> BillX Module</div>
  <h1>Invoicing &amp; Billing<br>Done Right</h1>
  <p>End-to-end billing platform: invoices, recurring subscriptions, expense tracking, multi-currency, and deep payment gateway integrations.</p>
  <div class="fp-btns">
    <a href="#" class="fp-btn"><i class="fas fa-plus"></i> Create Invoice</a>
    <a href="#features" class="fp-btn-o"><i class="fas fa-list"></i> See Features</a>
  </div>
  <div class="fp-stats">
  <div class="fp-stat"><div class="fp-sn">Multi</div><div class="fp-sl">Currency</div></div>
  <div class="fp-stat"><div class="fp-sn">Auto</div><div class="fp-sl">Recurring</div></div>
  <div class="fp-stat"><div class="fp-sn">Stripe</div><div class="fp-sl">Integrated</div></div>
  <div class="fp-stat"><div class="fp-sn">PDF</div><div class="fp-sl">Export</div></div>
  </div>
</section><div class="fp-sec" id="features">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Core Features</span>
  <h2>Complete Billing Toolkit</h2>
  
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--cyan);"></span>
    Invoice Creation
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-address-book"></i></div>
        <h3>Client &amp; Vendor Management</h3>
        <p>Full CRM-lite: client profiles, billing address, contacts, payment terms, credit limit, and notes.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-list"></i></div>
        <h3>Line Items &amp; Products</h3>
        <p>Product/service catalogue with descriptions, unit prices, and categories. SKU support. Quick-add from catalogue.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-percent"></i></div>
        <h3>Tax Rates &amp; Rules</h3>
        <p>Multiple tax rates per line item (VAT, GST, HST). Compound tax support. Tax-exempt client flags.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-tags"></i></div>
        <h3>Discounts</h3>
        <p>Percentage or fixed discounts at line-item or invoice level. Promo code support.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-globe"></i></div>
        <h3>Multi-Currency</h3>
        <p>150+ currencies. Real-time exchange rates via Open Exchange Rates API. Display in client currency, report in base currency.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-palette"></i></div>
        <h3>Branded Templates</h3>
        <p>Logo, brand color, custom footer text, digital signature block, and payment instructions per template.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-calendar-alt"></i></div>
        <h3>Payment Terms</h3>
        <p>Net 7/15/30/60/90, due on receipt, or custom date. Automatic due-date calculation.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-sticky-note"></i></div>
        <h3>Notes &amp; Terms</h3>
        <p>Private internal notes and customer-visible terms/conditions per invoice. Rich-text editor.</p>
      </div>
    </div>
  </div>
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--purple);"></span>
    Payment &amp; Collection
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-credit-card"></i></div>
        <h3>Stripe &amp; PayPal Integration</h3>
        <p>Collect online payments via credit card, debit card, Apple Pay, Google Pay, and PayPal.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-link"></i></div>
        <h3>Payment Links</h3>
        <p>Generate a one-click payment page for any invoice. Shareable via email, SMS, or QR code.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-hand-holding-usd"></i></div>
        <h3>Partial Payments</h3>
        <p>Allow clients to pay in installments. Track outstanding balance and payment history.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-bell"></i></div>
        <h3>Auto-Reminders</h3>
        <p>Scheduled reminder emails at configurable intervals before and after due date. Customizable templates.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-exclamation-circle"></i></div>
        <h3>Late Fees</h3>
        <p>Automatically apply fixed or percentage late fees after configurable overdue period.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-receipt"></i></div>
        <h3>Payment Receipts</h3>
        <p>Auto-generate and email PDF receipt immediately upon payment. Includes transaction ID.</p>
      </div>
    </div>
  </div>
</div></div>
<div class="fp-alt">
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Advanced Billing</span>
  <h2>Subscriptions &amp; Automation</h2>
  
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--green);"></span>
    Recurring Billing
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-redo"></i></div>
        <h3>Subscription Plans</h3>
        <p>Define monthly/annual/custom billing plans. Attach clients to plans for automatic invoice generation.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-robot"></i></div>
        <h3>Auto-Invoice Generation</h3>
        <p>Invoices created and sent automatically on billing cycle date. Zero manual intervention.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-calculator"></i></div>
        <h3>Proration</h3>
        <p>Automatically prorate charges when clients upgrade/downgrade mid-cycle.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-times-circle"></i></div>
        <h3>Dunning Management</h3>
        <p>Smart retry logic for failed payments. Configurable retry schedule with automatic subscription pause.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-gift"></i></div>
        <h3>Trial Periods</h3>
        <p>Free trial with automatic conversion to paid plan. Trial-end notification emails 3 days before.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-pause"></i></div>
        <h3>Pause &amp; Resume</h3>
        <p>Pause subscriptions (e.g., seasonal businesses) with automatic future resume date.</p>
      </div>
    </div>
  </div>
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--orange);"></span>
    Expense Tracking
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-receipt"></i></div>
        <h3>Receipt Upload &amp; OCR</h3>
        <p>Upload photos of receipts — OCR auto-extracts vendor, date, and amount. 99%+ accuracy.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-folder"></i></div>
        <h3>Expense Categories</h3>
        <p>Customizable categories (travel, equipment, meals). Hierarchical sub-categories supported.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-chart-pie"></i></div>
        <h3>Expense Reports</h3>
        <p>Auto-generated monthly expense reports. Filter by category, date range, project, or employee.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-user-check"></i></div>
        <h3>Reimbursement Workflow</h3>
        <p>Employee expense submission with manager approval flow. Batch reimbursement export for payroll.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-bullseye"></i></div>
        <h3>Budget Limits</h3>
        <p>Set monthly/annual budget limits per category. Over-budget alerts via email and dashboard.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-building"></i></div>
        <h3>Vendor Tracking</h3>
        <p>Link expenses to vendors. Track total spend per vendor. Vendor payment history and contact info.</p>
      </div>
    </div>
  </div>
</div></div>
</div>
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Reports &amp; Analytics</span>
  <h2>Financial Clarity at a Glance</h2>
  
</div><div class="fp-grid fp-anim">
      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-chart-bar"></i></div>
        <h3>Revenue by Client / Period</h3>
        <p>Monthly/quarterly/annual revenue breakdown. Client lifetime value ranking. YoY comparison.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-exclamation"></i></div>
        <h3>Outstanding AR Aging</h3>
        <p>Receivables aged 0–30, 31–60, 61–90, 90+ days. Prioritized collection list with one-click reminders.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-balance-scale"></i></div>
        <h3>Profit &amp; Loss Summary</h3>
        <p>Income vs expense summary. Exportable to accountant-ready format (CSV, PDF).</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-file-invoice"></i></div>
        <h3>Tax Summary Report</h3>
        <p>VAT/GST collected by rate. Ready for quarterly tax filings. Jurisdiction breakdown.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-clock"></i></div>
        <h3>Invoice Lifecycle Stats</h3>
        <p>Average days to payment per client. Overdue rate, payment method distribution.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-download"></i></div>
        <h3>Scheduled Reports</h3>
        <p>Auto-email PDF reports weekly/monthly to owners or accountants.</p>
      </div></div>
</div>
<div class="fp-alt">
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">User Journey</span>
  <h2>Invoice Sent in 5 Steps</h2>
  
</div><div class="fp-steps fp-anim">
  <div class="fp-step"><div class="fp-step-n">1</div><h4>Add Client</h4><p>Create or select client profile</p></div>
  <div class="fp-step"><div class="fp-step-n">2</div><h4>Add Line Items</h4><p>Products, services, or recurring</p></div>
  <div class="fp-step"><div class="fp-step-n">3</div><h4>Set Terms &amp; Tax</h4><p>Currency, due date, tax rates</p></div>
  <div class="fp-step"><div class="fp-step-n">4</div><h4>Preview &amp; Send</h4><p>PDF preview, email or link</p></div>
  <div class="fp-step"><div class="fp-step-n">5</div><h4>Track Payment</h4><p>Auto-reminder, mark paid, receipt</p></div>
</div></div>
</div>
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Developer API</span>
  <h2>Automate Your Billing</h2>
  
</div><div class="fp-api fp-anim">
  <div class="fp-api-h"><span class="fp-m fp-m-post">POST</span><span class="fp-api-p">/api/v1/invoices</span></div>
  <div class="fp-api-b">{
  "client_id": "cli_abc123",
  "line_items": [
    { "description": "Web Design", "quantity": 1, "unit_price": 1500.00, "tax_rate": "VAT_20" }
  ],
  "currency": "USD",
  "due_date": "2025-02-15",
  "send_email": true
}</div>
</div><div class="fp-api fp-anim">
  <div class="fp-api-h"><span class="fp-m fp-m-get">GET</span><span class="fp-api-p">/api/v1/invoices?status=overdue&amp;client_id=cli_abc123</span></div>
  <div class="fp-api-b">{ "data": [{ "id": "inv_xyz", "total": 1800.00, "due_date": "2025-01-15", "days_overdue": 12 }],
  "meta": { "total_outstanding": 18450.00 } }</div>
</div></div>
<div class="fp-tw fp-anim">
  <table class="fp-t">
    <thead><tr><th>Feature</th><th>Free</th><th>Starter</th><th>Pro</th><th>Enterprise</th></tr></thead>
    <tbody><tr><td>Invoices/mo</td><td>5</td><td>Unlimited</td><td>Unlimited</td><td>Unlimited</td></tr>
<tr><td>Recurring Billing</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>Payment Gateway</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>Expense Tracking</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>Multi-Currency</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>API Access</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>White-Label PDF</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td></tr>
</tbody>
  </table>
</div><div class="fp-hl fp-anim">
  <h3><i class="fas fa-shield-alt"></i> PCI DSS Compliant Payments</h3>
  <p>BillX never stores raw card data. All payments processed via Stripe's PCI-certified infrastructure. Full audit trail for every financial transaction.</p>
</div>
</div>
<script>

// Scroll-in animations
(function(){
  var obs = new IntersectionObserver(function(entries){
    entries.forEach(function(e){ if(e.isIntersecting) e.target.classList.add('vis'); });
  },{threshold:0.1});
  document.querySelectorAll('.fp-anim').forEach(function(el){ obs.observe(el); });
})();

// Mobile accordions
(function(){
  document.querySelectorAll('.fp-cat-btn').forEach(function(btn){
    btn.addEventListener('click',function(){
      if(window.innerWidth > 768) return;
      var expanded = btn.getAttribute('aria-expanded') === 'true';
      btn.setAttribute('aria-expanded', expanded ? 'false' : 'true');
      var body = btn.nextElementSibling;
      if(body) body.classList.toggle('is-closed', expanded);
    });
    // Start collapsed on mobile
    if(window.innerWidth <= 768){
      btn.setAttribute('aria-expanded','false');
      var body = btn.nextElementSibling;
      if(body) body.classList.add('is-closed');
    }
  });
})();

// Tabs
(function(){
  document.querySelectorAll('.fp-tab-btn').forEach(function(btn){
    btn.addEventListener('click',function(){
      var panel = btn.dataset.tab;
      var wrap = btn.closest('.fp-tab-wrap');
      wrap.querySelectorAll('.fp-tab-btn').forEach(function(b){ b.classList.remove('active'); });
      wrap.querySelectorAll('.fp-tab-p').forEach(function(p){ p.classList.remove('active'); });
      btn.classList.add('active');
      var target = wrap.querySelector('#'+panel);
      if(target) target.classList.add('active');
    });
  });
})();

</script>
<?php View::endSection(); ?>
