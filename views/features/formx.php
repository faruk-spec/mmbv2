<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php $title = 'FormX – Features'; ?>

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
  <div class="fp-badge"><i class="fas fa-wpforms"></i> FormX Module</div>
  <h1>Build Powerful Forms<br>Without Code</h1>
  <p>Drag-and-drop form builder with conditional logic, multi-step flows, payment collection, advanced analytics, and deep integrations.</p>
  <div class="fp-btns">
    <a href="#" class="fp-btn"><i class="fas fa-plus"></i> Create Form</a>
    <a href="#features" class="fp-btn-o"><i class="fas fa-list"></i> See Features</a>
  </div>
  <div class="fp-stats">
  <div class="fp-stat"><div class="fp-sn">20+</div><div class="fp-sl">Field Types</div></div>
  <div class="fp-stat"><div class="fp-sn">Logic</div><div class="fp-sl">Conditional</div></div>
  <div class="fp-stat"><div class="fp-sn">Payment</div><div class="fp-sl">Stripe/PayPal</div></div>
  <div class="fp-stat"><div class="fp-sn">Webhook</div><div class="fp-sl">Integrations</div></div>
  </div>
</section><div class="fp-sec" id="features">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Builder</span>
  <h2>Visual Form Builder</h2>
  
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--cyan);"></span>
    Field Types
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-font"></i></div>
        <h3>Text &amp; Paragraph</h3>
        <p>Single-line text, multi-line textarea with character/word count limits and regex pattern validation.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-check-square"></i></div>
        <h3>Checkboxes &amp; Radio</h3>
        <p>Single/multi-select options. Image-choice mode (show images instead of text labels). Randomize option order.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-caret-down"></i></div>
        <h3>Dropdown &amp; Multi-Select</h3>
        <p>Single or multi-select dropdowns. Searchable for long lists. Option groups. Dynamic options from API.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-star"></i></div>
        <h3>Rating &amp; Scale</h3>
        <p>Star rating (1–10), emoji rating, NPS score (0–10 with labels), and opinion scale.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-calendar"></i></div>
        <h3>Date &amp; Time Picker</h3>
        <p>Date, time, or date-time. Min/max date rules. Blackout specific dates or days of the week.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-upload"></i></div>
        <h3>File Upload</h3>
        <p>Single or multiple files. Configurable size limit, allowed MIME types, and upload-to-S3 option.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-credit-card"></i></div>
        <h3>Payment Field</h3>
        <p>Stripe or PayPal inline checkout. Fixed price, calculated price, or user-entered amount.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-signature"></i></div>
        <h3>Signature Pad</h3>
        <p>Touch/mouse digital signature capture. Saved as PNG. Required or optional.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-map-marker-alt"></i></div>
        <h3>Address &amp; Map</h3>
        <p>Auto-complete via Google Places API. Map pin picker. Show map preview after selection.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-image"></i></div>
        <h3>Image &amp; Video Embed</h3>
        <p>Embed images or YouTube/Vimeo videos as non-field display elements between questions.</p>
      </div>
    </div>
  </div>
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--purple);"></span>
    Logic &amp; Flow
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-code-branch"></i></div>
        <h3>Conditional Logic</h3>
        <p>Show/hide fields and pages based on previous answers. AND/OR condition groups. Nested rules.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-layer-group"></i></div>
        <h3>Multi-Step / Page Forms</h3>
        <p>Paginate long forms into steps with progress bar. Configurable step names and descriptions.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-calculator"></i></div>
        <h3>Calculated Fields</h3>
        <p>Compute values dynamically (total price, score, BMI) using arithmetic and conditional formulas.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-random"></i></div>
        <h3>Skip Logic</h3>
        <p>Jump to a specific page/section based on a single-question answer. Non-linear form navigation.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-check"></i></div>
        <h3>Submission Validation</h3>
        <p>Client-side and server-side validation. Custom error messages per field.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-save"></i></div>
        <h3>Auto-Save &amp; Resume</h3>
        <p>Partial responses saved to local storage. Respondents can return and continue later via unique resume link.</p>
      </div>
    </div>
  </div>
</div></div>
<div class="fp-alt">
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Integrations &amp; Automation</span>
  <h2>Connect to Everything</h2>
  
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--green);"></span>
    Notifications &amp; Webhooks
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-envelope"></i></div>
        <h3>Email Notifications</h3>
        <p>Customizable confirmation email to respondent and notification email to admins. HTML template editor.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-webhook"></i></div>
        <h3>Webhook on Submit</h3>
        <p>POST submission data to any URL. JSON payload with all field values, metadata, and file URLs.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-plug"></i></div>
        <h3>Zapier / Make.com</h3>
        <p>Native Zapier trigger and Make.com module. Connect to 5000+ apps without writing code.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-table"></i></div>
        <h3>Google Sheets Sync</h3>
        <p>Auto-append each submission as a new row in a connected Google Sheet. Real-time sync.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-slack"></i></div>
        <h3>Slack Notification</h3>
        <p>Post a formatted message to any Slack channel on each new submission.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-database"></i></div>
        <h3>CRM Push</h3>
        <p>Push submissions directly to HubSpot, Salesforce, or ActiveCampaign as contacts or leads.</p>
      </div>
    </div>
  </div>
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--orange);"></span>
    Appearance &amp; Embedding
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-paint-brush"></i></div>
        <h3>Visual Theme Editor</h3>
        <p>Brand colors, font, button styles, card background, and custom CSS. Mobile preview mode.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-code"></i></div>
        <h3>Embed Anywhere</h3>
        <p>Inline iframe, pop-up modal, side-slider, and full-page embed modes. JS snippet or WordPress plugin.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-language"></i></div>
        <h3>Multi-Language</h3>
        <p>Form labels and messages in multiple languages. RTL support for Arabic/Hebrew/Persian.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-clock"></i></div>
        <h3>Submission Scheduling</h3>
        <p>Open/close form at set dates. Max submission limit. Show countdown timer to close date.</p>
      </div>
    </div>
  </div>
</div></div>
</div>
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Analytics &amp; Data</span>
  <h2>Understand Your Responses</h2>
  
</div><div class="fp-grid fp-anim">
      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-chart-bar"></i></div>
        <h3>Response Summary Charts</h3>
        <p>Auto-generated bar/pie charts for each question. Share analytics page publicly.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-filter"></i></div>
        <h3>Response Filtering</h3>
        <p>Filter submissions by any field value, date range, or keyword. Save filter presets.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-file-excel"></i></div>
        <h3>CSV / Excel Export</h3>
        <p>Export all submissions with metadata (timestamp, IP, duration). Filtered exports supported.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-percentage"></i></div>
        <h3>Completion Rate</h3>
        <p>Track form view-to-submit conversion. Per-step drop-off analysis for multi-step forms.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-hourglass"></i></div>
        <h3>Avg Completion Time</h3>
        <p>Average time to complete the form. Per-field time heatmap to find friction points.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-map"></i></div>
        <h3>Geographic Insights</h3>
        <p>Country/city breakdown of respondents based on IP. Useful for location-sensitive campaigns.</p>
      </div></div>
</div>
<div class="fp-alt">
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">User Journey</span>
  <h2>Form Live in 5 Steps</h2>
  
</div><div class="fp-steps fp-anim">
  <div class="fp-step"><div class="fp-step-n">1</div><h4>Drag Fields</h4><p>Add fields from sidebar panel</p></div>
  <div class="fp-step"><div class="fp-step-n">2</div><h4>Set Logic</h4><p>Conditional show/hide rules</p></div>
  <div class="fp-step"><div class="fp-step-n">3</div><h4>Style</h4><p>Brand colors and custom CSS</p></div>
  <div class="fp-step"><div class="fp-step-n">4</div><h4>Publish</h4><p>Embed, share link, or QR code</p></div>
  <div class="fp-step"><div class="fp-step-n">5</div><h4>Analyze</h4><p>Real-time response dashboard</p></div>
</div></div>
</div>
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Developer API</span>
  <h2>Programmatic Form Management</h2>
  
</div><div class="fp-api fp-anim">
  <div class="fp-api-h"><span class="fp-m fp-m-post">POST</span><span class="fp-api-p">/api/v1/forms/{id}/submissions</span></div>
  <div class="fp-api-b">{
  "fields": {
    "name": "Jane Doe",
    "email": "jane@example.com",
    "message": "Hello, I would like to enquire..."
  },
  "meta": { "referrer": "https://campaign-page.com", "user_agent": "Mozilla/5.0..." }
}</div>
</div><div class="fp-api fp-anim">
  <div class="fp-api-h"><span class="fp-m fp-m-get">GET</span><span class="fp-api-p">/api/v1/forms/{id}/submissions?from=2025-01-01&amp;limit=100</span></div>
  <div class="fp-api-b">{ "total": 842, "data": [{ "id": "sub_001", "submitted_at": "2025-01-10T14:22:11Z",
  "fields": { "name": "Jane Doe", "email": "jane@example.com" } }] }</div>
</div></div>
<div class="fp-tw fp-anim">
  <table class="fp-t">
    <thead><tr><th>Feature</th><th>Free</th><th>Pro</th><th>Enterprise</th></tr></thead>
    <tbody><tr><td>Forms</td><td>3</td><td>Unlimited</td><td>Unlimited</td></tr>
<tr><td>Submissions/mo</td><td>100</td><td>Unlimited</td><td>Unlimited</td></tr>
<tr><td>File Upload</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>Conditional Logic</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>Payment Fields</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>Webhooks</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>API Access</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>Remove Branding</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td></tr>
</tbody>
  </table>
</div><div class="fp-hl fp-anim">
  <h3><i class="fas fa-lock"></i> GDPR &amp; HIPAA Ready</h3>
  <p>FormX supports data residency selection, consent fields, IP anonymization, automatic data-retention policies, and DPA agreements for regulated industries.</p>
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
