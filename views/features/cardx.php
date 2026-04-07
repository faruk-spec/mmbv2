<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php $title = 'CardX – Features'; ?>

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
  <div class="fp-badge"><i class="fas fa-id-card"></i> CardX Module</div>
  <h1>Digital Business Cards<br>That Impress</h1>
  <p>Create stunning digital business cards, ID cards, and badges. Share via QR, NFC, or link. Track who viewed and saved your card.</p>
  <div class="fp-btns">
    <a href="#" class="fp-btn"><i class="fas fa-plus"></i> Create Card</a>
    <a href="#features" class="fp-btn-o"><i class="fas fa-list"></i> See Features</a>
  </div>
  <div class="fp-stats">
  <div class="fp-stat"><div class="fp-sn">NFC</div><div class="fp-sl">Share</div></div>
  <div class="fp-stat"><div class="fp-sn">QR</div><div class="fp-sl">Built-in</div></div>
  <div class="fp-stat"><div class="fp-sn">Analytics</div><div class="fp-sl">Views &amp; Saves</div></div>
  <div class="fp-stat"><div class="fp-sn">Bulk</div><div class="fp-sl">Generation</div></div>
  </div>
</section><div class="fp-sec" id="features">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Core Features</span>
  <h2>Cards for Every Use Case</h2>
  
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--cyan);"></span>
    Card Types
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-user-tie"></i></div>
        <h3>Digital Business Card</h3>
        <p>Professional vCard with photo, name, title, company, phone, email, website, and social links. Live-updated.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-id-badge"></i></div>
        <h3>Employee ID Card</h3>
        <p>Photo, name, department, employee ID, barcode/QR, and manager contact. Printable front/back design.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-ticket-alt"></i></div>
        <h3>Event Badge</h3>
        <p>Attendee name, ticket type, session access, QR check-in code, and schedule QR links.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-graduation-cap"></i></div>
        <h3>Student ID</h3>
        <p>Student photo, name, course, institution logo, student ID, and academic year. Expiry date field.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-store"></i></div>
        <h3>Membership Card</h3>
        <p>Club/loyalty card with member name, ID, tier level, barcode, and expiry date.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-credit-card"></i></div>
        <h3>Custom Card</h3>
        <p>Fully custom card for any use case. Free-form field editor with 20+ field types.</p>
      </div>
    </div>
  </div>
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--purple);"></span>
    Design &amp; Customization
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-palette"></i></div>
        <h3>Visual Card Editor</h3>
        <p>Drag-and-drop canvas editor. Resize, reposition, and style every element freely.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-image"></i></div>
        <h3>Background Options</h3>
        <p>Solid color, gradient, image background, or pattern. Per-face (front/back) backgrounds.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-font"></i></div>
        <h3>Typography Control</h3>
        <p>Font family (Google Fonts), size, weight, color, and text shadow per text element.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-shapes"></i></div>
        <h3>Shapes &amp; Icons</h3>
        <p>Add icons from Font Awesome, upload SVGs, or draw simple shapes (rectangles, circles, lines).</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-th"></i></div>
        <h3>Layout Templates</h3>
        <p>50+ pre-built card layouts. One-click template swap preserving your data.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-print"></i></div>
        <h3>Print-Ready Export</h3>
        <p>Export CMYK PDF with bleed marks at 300 DPI. Predefined sizes: standard, ISO, credit card, badge.</p>
      </div>
    </div>
  </div>
</div></div>
<div class="fp-alt">
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Sharing &amp; Distribution</span>
  <h2>Share Everywhere</h2>
  
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--green);"></span>
    Digital Sharing
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-qrcode"></i></div>
        <h3>Built-in QR Code</h3>
        <p>Auto-generated QR pointing to your live card. Customizable QR style to match card brand.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-wifi"></i></div>
        <h3>NFC Tag Support</h3>
        <p>Write card data to any NFC tag/sticker. Step-by-step NFC write wizard via Web NFC API.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-link"></i></div>
        <h3>Public Card URL</h3>
        <p>Shareable link (e.g., card.example.com/john-doe) with custom slug and password-protect option.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-mobile-alt"></i></div>
        <h3>Save to Contacts</h3>
        <p>One-tap "Save to Contacts" button generates and downloads a .vcf file for any smartphone.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-share-alt"></i></div>
        <h3>Social Share</h3>
        <p>Share card link via WhatsApp, Telegram, LinkedIn, email, or copy-to-clipboard.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-envelope"></i></div>
        <h3>Email Signature Block</h3>
        <p>Generate an HTML email signature block from your card. Copy-paste into Gmail, Outlook, Apple Mail.</p>
      </div>
    </div>
  </div>
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--orange);"></span>
    Bulk Generation &amp; Management
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-file-csv"></i></div>
        <h3>CSV Bulk Import</h3>
        <p>Upload employee/attendee CSV — each row generates a unique personalized card. Template-driven.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-download"></i></div>
        <h3>Batch PDF/PNG Export</h3>
        <p>Download all cards as a ZIP of individual PDFs or PNGs. Optimized for print-on-demand services.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-folder"></i></div>
        <h3>Card Collections</h3>
        <p>Organize cards into groups (departments, events, teams). Bulk-share, bulk-expire, bulk-export.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-clock"></i></div>
        <h3>Expiry &amp; Archiving</h3>
        <p>Set expiry date per card. Expired cards show a customizable "expired" overlay. Auto-archive after grace period.</p>
      </div>
    </div>
  </div>
</div></div>
</div>
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Analytics &amp; Tracking</span>
  <h2>See Who Engages With Your Card</h2>
  
</div><div class="fp-grid fp-anim">
      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-eye"></i></div>
        <h3>View &amp; Save Count</h3>
        <p>Total views, unique viewers, and "Save to Contacts" click count. Daily trend chart.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-map-marker-alt"></i></div>
        <h3>Geographic Breakdown</h3>
        <p>Countries and cities where your card was viewed. Heatmap visualization.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-mobile"></i></div>
        <h3>Device &amp; Source</h3>
        <p>iOS vs Android vs Desktop. Referrer tracking (QR scan vs direct link vs NFC).</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-mouse-pointer"></i></div>
        <h3>Link Click Tracking</h3>
        <p>Track clicks on each link in your card (phone, email, website, social). Per-link click counts.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-bell"></i></div>
        <h3>Lead Notifications</h3>
        <p>Real-time email alert when someone saves your contact. Optional with daily digest option.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-file-export"></i></div>
        <h3>Export Viewer Data</h3>
        <p>Download CSV of viewers who saved your contact (name, email if collected, timestamp).</p>
      </div></div>
</div>
<div class="fp-alt">
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">User Journey</span>
  <h2>Card Live in 4 Steps</h2>
  
</div><div class="fp-steps fp-anim">
  <div class="fp-step"><div class="fp-step-n">1</div><h4>Choose Type</h4><p>Business card, ID, badge, or custom</p></div>
  <div class="fp-step"><div class="fp-step-n">2</div><h4>Design</h4><p>Use template or custom editor</p></div>
  <div class="fp-step"><div class="fp-step-n">3</div><h4>Add Data</h4><p>Fill fields or CSV import</p></div>
  <div class="fp-step"><div class="fp-step-n">4</div><h4>Share</h4><p>QR, NFC, link, or print</p></div>
</div></div>
</div>
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Developer API</span>
  <h2>Integrate CardX</h2>
  
</div><div class="fp-api fp-anim">
  <div class="fp-api-h"><span class="fp-m fp-m-post">POST</span><span class="fp-api-p">/api/v1/cards/bulk</span></div>
  <div class="fp-api-b">{
  "template_id": "tpl_corp_blue",
  "items": [
    { "name": "Jane Doe", "title": "CTO", "email": "jane@company.com", "photo_url": "https://..." },
    { "name": "John Smith", "title": "CEO", "email": "john@company.com", "photo_url": "https://..." }
  ],
  "output": "pdf",
  "async": true
}</div>
</div></div>
<div class="fp-tw fp-anim">
  <table class="fp-t">
    <thead><tr><th>Feature</th><th>Free</th><th>Pro</th><th>Business</th></tr></thead>
    <tbody><tr><td>Cards</td><td>5</td><td>Unlimited</td><td>Unlimited</td></tr>
<tr><td>NFC Support</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>Analytics</td><td><i class="fas fa-minus pt"></i> Basic</td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>Bulk Generation</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>Custom Domain</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>API Access</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>White-Label</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td></tr>
</tbody>
  </table>
</div><div class="fp-hl fp-anim">
  <h3><i class="fas fa-magic"></i> Live Cards, Always Up-to-Date</h3>
  <p>Unlike printed cards, CardX digital cards update instantly. Change your phone number or title today — everyone who saved your QR or NFC link gets the new information automatically.</p>
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
