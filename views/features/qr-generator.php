<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php $title = 'QR Generator – Features'; ?>

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
  <div class="fp-badge"><i class="fas fa-qrcode"></i> QR Generator Module</div>
  <h1>Smart QR Codes<br>That Do More</h1>
  <p>Generate, customize, track, and manage QR codes at scale — from simple URLs to geo-targeted dynamic campaigns.</p>
  <div class="fp-btns">
    <a href="#" class="fp-btn"><i class="fas fa-rocket"></i> Start Free</a>
    <a href="#core" class="fp-btn-o"><i class="fas fa-list"></i> See All Features</a>
  </div>
  <div class="fp-stats">
  <div class="fp-stat"><div class="fp-sn">25+</div><div class="fp-sl">QR Types</div></div>
  <div class="fp-stat"><div class="fp-sn">Dynamic</div><div class="fp-sl">Real-Time Edit</div></div>
  <div class="fp-stat"><div class="fp-sn">Bulk</div><div class="fp-sl">CSV Import</div></div>
  <div class="fp-stat"><div class="fp-sn">REST</div><div class="fp-sl">API</div></div>
  </div>
</section>
<div class="fp-sec" id="core">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Core Features</span>
  <h2>Everything You Need</h2>
  
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--cyan);"></span>
    QR Content Types
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-link"></i></div>
        <h3>URL / Website Link</h3>
        <p>Encode any URL — static or dynamic with redirect tracking. Supports HTTP, HTTPS, and custom deep-link schemes.</p>
      <span class="fp-tag">Most Used</span>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-id-card"></i></div>
        <h3>vCard / Contact</h3>
        <p>Full vCard 3.0 &amp; 4.0: name, phone, email, address, photo URL, company, and social handles.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-wifi"></i></div>
        <h3>Wi-Fi Credentials</h3>
        <p>SSID, password, encryption (WPA/WEP/None). Hidden-network support. One-tap connect for guests.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-envelope"></i></div>
        <h3>Email / SMS</h3>
        <p>Pre-fill recipient, subject, and body for email; pre-fill number and message body for SMS.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-map-marker-alt"></i></div>
        <h3>GPS Location</h3>
        <p>Latitude/longitude with altitude, Google Maps URL fallback, and Apple Maps support.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-phone"></i></div>
        <h3>Phone / WhatsApp</h3>
        <p>Direct dial, WhatsApp chat pre-fill, FaceTime audio/video — all in one scannable code.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-calendar"></i></div>
        <h3>Calendar Event (vCal)</h3>
        <p>iCal events with title, date/time range, location, description, organizer, and attendees.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-file-pdf"></i></div>
        <h3>PDF / File Link</h3>
        <p>Point to any file (PDF, DOCX, MP4) with optional inline viewer, download prompt, and expiry.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fab fa-bitcoin"></i></div>
        <h3>Crypto Payment</h3>
        <p>Bitcoin, Ethereum, major altcoins — encode wallet address, amount, label, and memo field.</p>
      </div>
    </div>
  </div>
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--purple);"></span>
    Customization Engine
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-palette"></i></div>
        <h3>Full Color Control</h3>
        <p>Foreground, background, gradient fills (linear/radial), per-module color. Hex/RGB/HSL input with eye dropper.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-shapes"></i></div>
        <h3>Module Shapes</h3>
        <p>Square, rounded, dots, classy, classy-rounded, extra-rounded — data cells and corner finders independently.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-image"></i></div>
        <h3>Center Logo Embed</h3>
        <p>Upload SVG/PNG/JPEG logo. Adjustable size, padding, background shape, and auto error-correction boost to H (30%).</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-border-all"></i></div>
        <h3>Frame &amp; CTA Labels</h3>
        <p>Decorative frames with call-to-action text ("Scan Me"), icon overlays, and border styles.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-sliders-h"></i></div>
        <h3>Error Correction Levels</h3>
        <p>L (7%), M (15%), Q (25%), H (30%) — auto-upgraded when logo embed is detected.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-ruler"></i></div>
        <h3>Size &amp; Quiet Zone</h3>
        <p>Output from 64 px to 4096 px. Quiet zone modules configurable from 0 to 10.</p>
      </div>
    </div>
  </div>
</div></div>
<div class="fp-alt">
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Advanced Capabilities</span>
  <h2>Dynamic QR Intelligence</h2>
  <p>Enterprise features that transform QR codes into smart marketing tools.</p>
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--green);"></span>
    Dynamic QR Codes
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-sync-alt"></i></div>
        <h3>Real-Time Destination Edit</h3>
        <p>Change the QR destination without reprinting. Redirect rules updated instantly — zero downtime for printed materials.</p>
      <span class="fp-tag">Dynamic</span>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-code-branch"></i></div>
        <h3>A/B Redirect Split Testing</h3>
        <p>Route scan traffic between multiple URLs at configurable percentages with built-in conversion tracking per variant.</p>
      <span class="fp-tag">Pro</span>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-globe"></i></div>
        <h3>Geo-Targeted Routing</h3>
        <p>Redirect scanners to country/language-specific pages automatically using MaxMind GeoIP2.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-mobile-alt"></i></div>
        <h3>Device-Type Routing</h3>
        <p>Separate destinations for iOS, Android, Desktop. Deep-link to App Store / Play Store automatically.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-clock"></i></div>
        <h3>Scheduled Redirects</h3>
        <p>Active windows with start/end timestamps. Auto-expire to fallback URL after campaign ends.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-hashtag"></i></div>
        <h3>Scan Limit &amp; One-Time QR</h3>
        <p>Hard cap on total or unique-IP scans. Perfect for gated content, event tickets, single-use vouchers.</p>
      </div>
    </div>
  </div>
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--orange);"></span>
    Bulk &amp; Automation
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-file-csv"></i></div>
        <h3>CSV / Excel Bulk Import</h3>
        <p>Upload thousands of rows — each becomes an individual QR code. Column-mapping wizard with validation.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-download"></i></div>
        <h3>Batch Export (ZIP)</h3>
        <p>Download bulk QR codes as a ZIP of SVG/PNG/PDF files. Named by row data or custom filename template.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-folder-open"></i></div>
        <h3>Campaign Organizer</h3>
        <p>Group QR codes by campaign, event, or department. Nested folders with color labels.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-clone"></i></div>
        <h3>Template Library</h3>
        <p>Save style presets (colors, logo, frame) as reusable templates. Share templates across team members.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-robot"></i></div>
        <h3>Webhook Callbacks</h3>
        <p>POST real-time scan events to any URL. Payload includes scan metadata, geo data, and QR ID.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-print"></i></div>
        <h3>Print-Ready PDF Export</h3>
        <p>CMYK-optimized print PDFs with bleed/trim marks, Avery label sheet layouts, and custom DPI.</p>
      </div>
    </div>
  </div>
</div></div>
</div>
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">User Journey</span>
  <h2>From Idea to Scan in 60 Seconds</h2>
  
</div><div class="fp-steps fp-anim">
  <div class="fp-step"><div class="fp-step-n">1</div><h4>Choose Type</h4><p>Select from 25+ QR content types</p></div>
  <div class="fp-step"><div class="fp-step-n">2</div><h4>Enter Data</h4><p>Fill form with smart validation</p></div>
  <div class="fp-step"><div class="fp-step-n">3</div><h4>Customize</h4><p>Style colors, shapes, logo &amp; frame</p></div>
  <div class="fp-step"><div class="fp-step-n">4</div><h4>Live Preview</h4><p>Real-time rendering + scan test</p></div>
  <div class="fp-step"><div class="fp-step-n">5</div><h4>Download</h4><p>SVG, PNG, PDF or via API</p></div>
  <div class="fp-step"><div class="fp-step-n">6</div><h4>Track Scans</h4><p>Analytics go live instantly</p></div>
</div></div>
<div class="fp-alt">
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Analytics &amp; Insights</span>
  <h2>Know Every Scan</h2>
  <p>Rich analytics capturing device, location, time, and behavior for every scan event.</p>
</div><div class="fp-grid fp-anim">
      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-chart-line"></i></div>
        <h3>Real-Time Scan Counter</h3>
        <p>Live scan count updates every 30 s. Configurable refresh with WebSocket push option.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-map"></i></div>
        <h3>Geographic Heatmap</h3>
        <p>World/country/city level heatmap. Drill-down to city resolution. Exportable as GeoJSON or CSV.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-mobile"></i></div>
        <h3>Device &amp; OS Breakdown</h3>
        <p>iOS vs Android vs Desktop. Browser family, screen size buckets, OS version distribution.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-calendar-week"></i></div>
        <h3>Time-Series &amp; Peak Hours</h3>
        <p>Hourly/daily/weekly/monthly trend charts. Identify peak and low-engagement windows.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-users"></i></div>
        <h3>Unique vs Repeat Scans</h3>
        <p>IP + user-agent fingerprinting to distinguish unique visitors from returning scanners.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-file-export"></i></div>
        <h3>Export Reports</h3>
        <p>Scheduled email reports, on-demand CSV dump, and branded PDF analytics for clients.</p>
      </div></div>
</div>
</div>
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Developer API</span>
  <h2>Build QR Into Anything</h2>
  <p>RESTful JSON API with SDK support. All features accessible programmatically.</p>
</div><div class="fp-api fp-anim">
  <div class="fp-api-h"><span class="fp-m fp-m-post">POST</span><span class="fp-api-p">/api/v1/qr/generate</span></div>
  <div class="fp-api-b">{
  "type": "url",
  "data": { "url": "https://example.com" },
  "style": { "foreground": "#7c3aed", "shape": "rounded", "size": 512, "logo_url": "https://cdn.example.com/logo.png" },
  "dynamic": true,
  "campaign_id": "camp_abc123"
}</div>
</div><div class="fp-api fp-anim">
  <div class="fp-api-h"><span class="fp-m fp-m-patch">PATCH</span><span class="fp-api-p">/api/v1/qr/{id}/redirect</span></div>
  <div class="fp-api-b">{
  "destination_url": "https://new-landing.com",
  "active_until": "2025-12-31T23:59:59Z",
  "geo_rules": [
    { "country": "US", "url": "https://us.example.com" },
    { "country": "DE", "url": "https://de.example.com" }
  ]
}</div>
</div><div class="fp-api fp-anim">
  <div class="fp-api-h"><span class="fp-m fp-m-get">GET</span><span class="fp-api-p">/api/v1/qr/{id}/analytics?from=2025-01-01&amp;to=2025-01-31&amp;group=day</span></div>
  <div class="fp-api-b">{ "total_scans": 4821, "unique_scans": 3210,
  "by_country": [{ "code": "US", "scans": 1822 }],
  "by_device": { "ios": 2410, "android": 1876, "desktop": 535 } }</div>
</div></div>
<div class="fp-alt">
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Plan Comparison</span>
  <h2>Features by Plan</h2>
  
</div><div class="fp-tw fp-anim">
  <table class="fp-t">
    <thead><tr><th>Feature</th><th>Free</th><th>Starter</th><th>Pro</th><th>Enterprise</th></tr></thead>
    <tbody><tr><td>Static QR Codes</td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>Dynamic QR Codes</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>Scan Analytics</td><td><i class="fas fa-minus pt"></i> Basic</td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>Bulk Generation</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>A/B Testing</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>API Access</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-minus pt"></i> 1K/mo</td><td><i class="fas fa-minus pt"></i> 100K/mo</td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>Custom Domain</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>White-Label</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td></tr>
</tbody>
  </table>
</div></div>
</div>
<div class="fp-hl fp-anim">
  <h3><i class="fas fa-star"></i> Built for Scale</h3>
  <p>QR Generator handles millions of scans per day with edge-cached redirects, CDN-distributed QR images, and horizontally scalable job queues for bulk generation.</p>
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
