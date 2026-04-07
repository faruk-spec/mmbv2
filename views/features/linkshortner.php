<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php $title = 'LinkShortner – Features'; ?>

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
  <div class="fp-badge"><i class="fas fa-link"></i> LinkShortner Module</div>
  <h1>Short Links That<br>Work Harder</h1>
  <p>Branded link shortening with deep analytics, smart redirects, retargeting pixels, A/B testing, and enterprise-grade API access.</p>
  <div class="fp-btns">
    <a href="#" class="fp-btn"><i class="fas fa-plus"></i> Shorten Link</a>
    <a href="#features" class="fp-btn-o"><i class="fas fa-list"></i> See Features</a>
  </div>
  <div class="fp-stats">
  <div class="fp-stat"><div class="fp-sn">Custom</div><div class="fp-sl">Domains</div></div>
  <div class="fp-stat"><div class="fp-sn">Deep</div><div class="fp-sl">Analytics</div></div>
  <div class="fp-stat"><div class="fp-sn">Smart</div><div class="fp-sl">Redirects</div></div>
  <div class="fp-stat"><div class="fp-sn">REST</div><div class="fp-sl">API</div></div>
  </div>
</section><div class="fp-sec" id="features">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Core Features</span>
  <h2>The Complete Link Management Platform</h2>
  
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--cyan);"></span>
    Link Creation &amp; Management
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-link"></i></div>
        <h3>One-Click Shortening</h3>
        <p>Paste any URL — get a short link instantly. Browser extension, bookmarklet, and mobile app support.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-pencil-alt"></i></div>
        <h3>Custom Alias / Slug</h3>
        <p>Choose a memorable back-half (e.g., brand.com/summer-sale). Availability check in real-time.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-globe"></i></div>
        <h3>Custom Domain</h3>
        <p>Use your own domain for branded short links. Multi-domain support. HTTPS auto-provisioned via Let's Encrypt.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-folder"></i></div>
        <h3>Link Collections</h3>
        <p>Organize links into campaigns, folders, or workspaces. Color labels and tag system.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-clock"></i></div>
        <h3>Link Expiry</h3>
        <p>Set expiry date/time. Expired links redirect to a configurable fallback URL or show an expired page.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-lock"></i></div>
        <h3>Password-Protected Links</h3>
        <p>Require a password before redirect. Optional email-gate to collect leads at click time.</p>
      </div>
    </div>
  </div>
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--purple);"></span>
    Smart Redirect Engine
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-code-branch"></i></div>
        <h3>A/B Split Testing</h3>
        <p>Split traffic between multiple destinations at configurable percentages. Built-in conversion tracking.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-globe"></i></div>
        <h3>Geo-Targeted Redirect</h3>
        <p>Route visitors to country/region-specific URLs using MaxMind GeoIP2 with 99.8% accuracy.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-mobile-alt"></i></div>
        <h3>Device-Type Routing</h3>
        <p>Separate URLs for iOS (App Store), Android (Play Store), and Desktop. Language-based routing too.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-calendar"></i></div>
        <h3>Scheduled Redirects</h3>
        <p>Time-based destination switching. Redirect to sale page during campaign, then back to product page.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-ban"></i></div>
        <h3>Click Limit</h3>
        <p>Cap total or per-IP clicks. Ideal for limited-offer campaigns, single-use coupons, event tickets.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-code"></i></div>
        <h3>UTM Auto-Builder</h3>
        <p>Visually build UTM parameters (source, medium, campaign, content, term) and auto-append to destination.</p>
      </div>
    </div>
  </div>
</div></div>
<div class="fp-alt">
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Analytics &amp; Intelligence</span>
  <h2>Every Click Tells a Story</h2>
  
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--green);"></span>
    Click Analytics
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-chart-line"></i></div>
        <h3>Real-Time Click Counter</h3>
        <p>Live click count. Updates every 30 s. Total vs unique clicks prominently displayed.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-map"></i></div>
        <h3>Geographic Heatmap</h3>
        <p>World map with click density by country/city. Drill down to city level. Export as GeoJSON.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-mobile"></i></div>
        <h3>Device &amp; Browser Breakdown</h3>
        <p>iOS vs Android vs Desktop. Browser family, OS version, and screen resolution buckets.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-calendar-week"></i></div>
        <h3>Time-Series Charts</h3>
        <p>Hourly/daily/weekly/monthly trend charts. Peak click hours heatmap (24h x 7day grid).</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-external-link-alt"></i></div>
        <h3>Referrer Tracking</h3>
        <p>Top referring domains, social platforms, and direct traffic. Referrer path drill-down.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-users"></i></div>
        <h3>Unique vs Repeat Clicks</h3>
        <p>IP + fingerprint distinction. Bot click filtering with configurable suspicion threshold.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-file-export"></i></div>
        <h3>Export Reports</h3>
        <p>Scheduled PDF/CSV reports. Custom date range. Branded reports for agency clients.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-bell"></i></div>
        <h3>Click Milestone Alerts</h3>
        <p>Email/webhook when link hits 100, 500, 1K, or custom milestone.</p>
      </div>
    </div>
  </div>
</div></div>
</div>
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Retargeting &amp; Pixels</span>
  <h2>Turn Every Click Into a Lead</h2>
  
</div><div class="fp-grid fp-anim">
      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fab fa-facebook"></i></div>
        <h3>Meta Pixel</h3>
        <p>Attach a Facebook/Meta pixel to any link. Every click fires the pixel — even before the destination page loads.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fab fa-google"></i></div>
        <h3>Google Tag</h3>
        <p>Inject Google Analytics GA4 event and Google Ads conversion tag at click time.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fab fa-twitter"></i></div>
        <h3>Twitter Pixel</h3>
        <p>Twitter/X website tag attached to the redirect. Builds retargeting audience from link clicks.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-code"></i></div>
        <h3>Custom Script Injection</h3>
        <p>Add arbitrary JavaScript snippet to the redirect interstitial page for any pixel/tag.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-layer-group"></i></div>
        <h3>Multiple Pixels Per Link</h3>
        <p>Attach up to 10 different pixels/tags per link simultaneously.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-shield-alt"></i></div>
        <h3>Consent-Mode Aware</h3>
        <p>Respects user consent signals. GDPR-mode fires pixels only after cookie consent is granted.</p>
      </div></div>
</div>
<div class="fp-alt">
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Security &amp; Trust</span>
  <h2>Safe Links at Every Step</h2>
  
</div><div class="fp-grid fp-anim">
      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-shield-alt"></i></div>
        <h3>Malicious URL Scanning</h3>
        <p>All destination URLs checked against Google Safe Browsing and VirusTotal before activation.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-ban"></i></div>
        <h3>Abuse Reporting</h3>
        <p>Users can flag suspicious links. Admin review queue with one-click block and takedown.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-eye-slash"></i></div>
        <h3>Link Cloaking</h3>
        <p>Hide the destination URL from appearing in browser status bars. Intermediate redirect page option.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-user-check"></i></div>
        <h3>Link Ownership Verification</h3>
        <p>Verify you own the destination domain via DNS TXT record to prevent brand impersonation.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-file-alt"></i></div>
        <h3>Audit Trail</h3>
        <p>Every link create/edit/delete logged with user, timestamp, IP, and before/after diff.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-chart-area"></i></div>
        <h3>Fraud Click Filtering</h3>
        <p>Auto-detect and filter bot traffic, datacenter IPs, and click-farm patterns from analytics.</p>
      </div></div>
</div>
</div>
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">User Journey</span>
  <h2>Short Link Live in Seconds</h2>
  
</div><div class="fp-steps fp-anim">
  <div class="fp-step"><div class="fp-step-n">1</div><h4>Paste URL</h4><p>Any URL, any length</p></div>
  <div class="fp-step"><div class="fp-step-n">2</div><h4>Customize Alias</h4><p>Custom slug or auto-generated</p></div>
  <div class="fp-step"><div class="fp-step-n">3</div><h4>Add Redirects</h4><p>Geo, device, schedule rules</p></div>
  <div class="fp-step"><div class="fp-step-n">4</div><h4>Attach Pixels</h4><p>Retargeting tags in one click</p></div>
  <div class="fp-step"><div class="fp-step-n">5</div><h4>Share</h4><p>Email, social, QR code, print</p></div>
  <div class="fp-step"><div class="fp-step-n">6</div><h4>Analyze</h4><p>Real-time analytics dashboard</p></div>
</div></div>
<div class="fp-alt">
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Developer API</span>
  <h2>Programmatic Link Management</h2>
  
</div><div class="fp-api fp-anim">
  <div class="fp-api-h"><span class="fp-m fp-m-post">POST</span><span class="fp-api-p">/api/v1/links</span></div>
  <div class="fp-api-b">{
  "long_url": "https://example.com/very/long/path?utm_source=newsletter",
  "custom_alias": "summer25",
  "domain": "go.yourbrand.com",
  "expires_at": "2025-08-31T23:59:59Z",
  "geo_rules": [{ "country": "US", "url": "https://us.example.com" }],
  "pixels": ["px_meta_123", "px_ga_456"]
}</div>
</div><div class="fp-api fp-anim">
  <div class="fp-api-h"><span class="fp-m fp-m-get">GET</span><span class="fp-api-p">/api/v1/links/{id}/analytics?from=2025-01-01&amp;group=day</span></div>
  <div class="fp-api-b">{ "total_clicks": 8421, "unique_clicks": 5033,
  "by_day": [{ "date": "2025-01-01", "clicks": 312 }],
  "by_country": [{ "code": "US", "clicks": 3210 }],
  "by_device": { "mobile": 6100, "desktop": 2321 } }</div>
</div></div>
</div>
<div class="fp-sec">
<div class="fp-tw fp-anim">
  <table class="fp-t">
    <thead><tr><th>Feature</th><th>Free</th><th>Pro</th><th>Business</th><th>Enterprise</th></tr></thead>
    <tbody><tr><td>Links/mo</td><td>50</td><td>Unlimited</td><td>Unlimited</td><td>Unlimited</td></tr>
<tr><td>Custom Domain</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>Analytics Retention</td><td>7 days</td><td>1 year</td><td>2 years</td><td>Unlimited</td></tr>
<tr><td>Retargeting Pixels</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>A/B Testing</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>Geo Routing</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>API Access</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-minus pt"></i> 10K/mo</td><td><i class="fas fa-minus pt"></i> 1M/mo</td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>Team Workspaces</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
</tbody>
  </table>
</div></div>
<div class="fp-hl fp-anim">
  <h3><i class="fas fa-tachometer-alt"></i> Sub-10 ms Global Redirects</h3>
  <p>LinkShortner's redirect engine runs on edge nodes in 35+ global regions. Every click reaches its destination in under 10 ms regardless of where the scanner is in the world.</p>
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
