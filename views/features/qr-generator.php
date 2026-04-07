<?php use Core\View; use Core\Auth; ?>
<?php View::extend('main'); ?>
<?php $title = 'QR Generator – Features'; ?>

<?php View::section('styles'); ?>
<style>
@import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700;800&display=swap');
:root{--gradient-primary:linear-gradient(135deg,#9945ff,#00f0ff);--font-heading:'Space Grotesk',sans-serif;--radius-full:9999px;--radius-lg:16px;--radius-xl:24px;--glow-cyan:0 0 24px rgba(0,240,255,.3);--glow-purple:0 0 24px rgba(153,69,255,.3);}
.fp-wrap{max-width:1100px;margin:0 auto;padding:0 20px 80px;}
.fp-hero{padding:64px 0 48px;text-align:center;position:relative;overflow:hidden;}
.fp-hero::before{content:'';position:absolute;top:-60px;left:50%;transform:translateX(-50%);width:800px;height:500px;background:radial-gradient(ellipse,rgba(153,69,255,.18) 0%,rgba(0,240,255,.08) 50%,transparent 70%);pointer-events:none;z-index:0;animation:heroBlob 8s ease-in-out infinite alternate;}
@keyframes heroBlob{from{transform:translateX(-50%) scale(1);}to{transform:translateX(-50%) scale(1.12);}}
.fp-badge{display:inline-flex;align-items:center;gap:8px;background:rgba(153,69,255,.12);border:1px solid rgba(153,69,255,.4);color:var(--purple);padding:6px 18px;border-radius:var(--radius-full);font-size:.74rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:22px;position:relative;z-index:1;animation:badgePulse 3s ease-in-out infinite;}
@keyframes badgePulse{0%,100%{box-shadow:0 0 0 0 rgba(153,69,255,.35);}50%{box-shadow:0 0 0 8px rgba(153,69,255,0);}}
.fp-hero h1{font-size:clamp(2.3rem,6vw,3.9rem);font-weight:800;line-height:1.08;margin-bottom:18px;font-family:var(--font-heading);background:var(--gradient-primary);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;position:relative;z-index:1;}
.fp-hero p{font-size:1.08rem;color:var(--text-secondary);max-width:600px;margin:0 auto 32px;line-height:1.7;position:relative;z-index:1;}
.fp-btns{display:flex;gap:12px;justify-content:center;flex-wrap:wrap;position:relative;z-index:1;margin-bottom:36px;}
.fp-btn-primary{display:inline-flex;align-items:center;gap:8px;background:var(--gradient-primary);color:#fff;font-weight:700;padding:13px 30px;border-radius:var(--radius-full);text-decoration:none;font-size:.96rem;transition:var(--transition);box-shadow:var(--glow-purple);}
.fp-btn-primary:hover{transform:translateY(-2px);box-shadow:var(--glow-cyan);}
.fp-btn-outline{display:inline-flex;align-items:center;gap:8px;background:transparent;color:var(--text-primary);font-weight:600;padding:12px 28px;border-radius:var(--radius-full);text-decoration:none;font-size:.96rem;border:1.5px solid var(--border-color);transition:var(--transition);}
.fp-btn-outline:hover{border-color:var(--cyan);color:var(--cyan);transform:translateY(-2px);}
.fp-stats{display:flex;gap:8px;justify-content:center;flex-wrap:wrap;position:relative;z-index:1;}
.fp-stat{background:rgba(255,255,255,.04);border:1px solid var(--border-color);border-radius:var(--radius-full);padding:6px 16px;font-size:.8rem;color:var(--text-secondary);font-weight:500;}
.fp-stat strong{color:var(--cyan);font-weight:700;}
.fp-section{margin-top:68px;}
.fp-lbl{display:inline-flex;align-items:center;gap:6px;background:rgba(0,240,255,.1);border:1px solid rgba(0,240,255,.3);color:var(--cyan);padding:4px 14px;border-radius:var(--radius-full);font-size:.71rem;font-weight:700;text-transform:uppercase;letter-spacing:.08em;margin-bottom:10px;}
.fp-section h2{font-family:var(--font-heading);font-size:clamp(1.55rem,3vw,2.25rem);font-weight:800;margin-bottom:8px;color:var(--text-primary);}
.fp-sub{color:var(--text-secondary);font-size:.97rem;margin-bottom:32px;max-width:600px;line-height:1.6;}
.fp-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px;}
.fp-card{background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-lg);padding:26px;transition:var(--transition);position:relative;overflow:hidden;}
.fp-card::after{content:'';position:absolute;inset:0;border-radius:var(--radius-lg);background:var(--gradient-primary);opacity:0;transition:opacity .3s;pointer-events:none;}
.fp-card:hover{transform:translateY(-5px);border-color:rgba(0,240,255,.4);box-shadow:0 10px 36px rgba(0,240,255,.14);}
.fp-card:hover::after{opacity:.035;}
.fp-card-icon{width:50px;height:50px;border-radius:13px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;font-size:1.3rem;flex-shrink:0;}
.fp-card-icon.c-cyan{background:rgba(0,240,255,.12);color:var(--cyan);}
.fp-card-icon.c-purple{background:rgba(153,69,255,.12);color:var(--purple);}
.fp-card-icon.c-green{background:rgba(39,174,96,.12);color:var(--green);}
.fp-card-icon.c-orange{background:rgba(243,156,18,.12);color:var(--orange);}
.fp-card-icon.c-magenta{background:rgba(236,64,122,.12);color:#ec407a;}
.fp-card h3{font-family:var(--font-heading);font-size:1.02rem;font-weight:700;margin-bottom:12px;color:var(--text-primary);}
.fp-card ul{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:5px;}
.fp-card ul li{font-size:.84rem;color:var(--text-secondary);display:flex;align-items:center;gap:8px;line-height:1.4;}
.fp-card ul li::before{content:'';width:5px;height:5px;border-radius:50%;flex-shrink:0;}
.dot-cyan::before{background:var(--cyan)!important;}
.dot-purple::before{background:var(--purple)!important;}
.dot-green::before{background:var(--green)!important;}
.dot-orange::before{background:var(--orange)!important;}
.dot-magenta::before{background:#ec407a!important;}
.fp-steps{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:4px;margin-top:32px;position:relative;}
.fp-steps::before{content:'';position:absolute;top:27px;left:10%;width:80%;height:2px;background:linear-gradient(90deg,transparent,rgba(153,69,255,.5),rgba(0,240,255,.5),transparent);z-index:0;}
.fp-step{text-align:center;padding:24px 16px;position:relative;z-index:1;}
.fp-step-num{width:56px;height:56px;border-radius:50%;background:var(--bg-secondary);border:2px solid rgba(153,69,255,.5);display:flex;align-items:center;justify-content:center;margin:0 auto 14px;font-family:var(--font-heading);font-size:1.25rem;font-weight:800;color:var(--purple);transition:var(--transition);}
.fp-step:hover .fp-step-num{border-color:var(--cyan);color:var(--cyan);box-shadow:var(--glow-cyan);background:rgba(0,240,255,.06);}
.fp-step h4{font-family:var(--font-heading);font-size:.95rem;font-weight:700;color:var(--text-primary);margin-bottom:6px;}
.fp-step p{font-size:.83rem;color:var(--text-secondary);line-height:1.55;}
.fp-api{background:var(--bg-card);border:1px solid var(--border-color);border-radius:var(--radius-xl);padding:28px 32px;margin-top:32px;}
.fp-api h3{font-family:var(--font-heading);font-size:1.1rem;font-weight:700;margin-bottom:18px;color:var(--text-primary);display:flex;align-items:center;gap:8px;}
.fp-endpoint{display:flex;align-items:center;gap:10px;padding:11px 16px;background:rgba(0,0,0,.25);border-radius:10px;margin-bottom:10px;font-family:'Courier New',monospace;font-size:.86rem;border:1px solid rgba(255,255,255,.05);}
.fp-method{padding:3px 10px;border-radius:6px;font-weight:700;font-size:.72rem;text-transform:uppercase;letter-spacing:.04em;flex-shrink:0;}
.fp-method.get{background:rgba(39,174,96,.2);color:var(--green);}
.fp-method.post{background:rgba(0,240,255,.15);color:var(--cyan);}
.fp-endpoint code{color:var(--text-primary);flex:1;}
.ep-desc{color:var(--text-secondary);font-size:.78rem;font-family:inherit;margin-left:auto;white-space:nowrap;}
.fp-t-wrap{overflow-x:auto;border-radius:var(--radius-lg);margin-top:32px;border:1px solid var(--border-color);}
.fp-t{width:100%;border-collapse:collapse;}
.fp-t thead tr{background:rgba(153,69,255,.08);}
.fp-t thead th{padding:14px 16px;font-family:var(--font-heading);font-weight:700;font-size:.84rem;text-align:center;color:var(--text-primary);}
.fp-t thead th:first-child{text-align:left;}
.fp-t thead th.pro{background:rgba(0,240,255,.08);color:var(--cyan);}
.fp-t thead th.pro .badge-pop{display:inline-block;font-size:.58rem;background:var(--gradient-primary);color:#fff;padding:2px 8px;border-radius:var(--radius-full);margin-left:6px;vertical-align:middle;font-weight:700;}
.fp-t tbody tr{border-top:1px solid var(--border-color);transition:background .15s;}
.fp-t tbody tr:hover{background:rgba(255,255,255,.025);}
.fp-t tbody td{padding:11px 16px;font-size:.87rem;text-align:center;color:var(--text-secondary);}
.fp-t tbody td:first-child{text-align:left;font-weight:600;color:var(--text-primary);}
.fp-t tbody td.pro{background:rgba(0,240,255,.03);}
.fp-t .chk{color:var(--cyan);}
.fp-t .x{color:rgba(255,255,255,.18);}
.fp-hl{margin-top:68px;border-radius:var(--radius-xl);padding:52px 40px;text-align:center;background:var(--bg-card);border:1px solid var(--border-color);position:relative;overflow:hidden;}
.fp-hl::before{content:'';position:absolute;inset:0;background:var(--gradient-primary);opacity:.05;pointer-events:none;}
.fp-hl::after{content:'';position:absolute;top:-80px;right:-80px;width:320px;height:320px;border-radius:50%;background:radial-gradient(circle,rgba(153,69,255,.15),transparent 70%);pointer-events:none;}
.fp-hl-inner{position:relative;z-index:1;}
.fp-hl h2{font-family:var(--font-heading);font-size:clamp(1.7rem,3.5vw,2.5rem);font-weight:800;background:var(--gradient-primary);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:14px;}
.fp-hl p{color:var(--text-secondary);font-size:1rem;max-width:550px;margin:0 auto 24px;line-height:1.7;}
.fp-hl-tags{display:flex;gap:8px;justify-content:center;flex-wrap:wrap;margin-top:20px;}
.fp-hl-tag{padding:5px 14px;border-radius:var(--radius-full);font-size:.78rem;font-weight:600;background:rgba(0,240,255,.1);border:1px solid rgba(0,240,255,.25);color:var(--cyan);}
.fp-cta{margin-top:68px;border-radius:var(--radius-xl);padding:60px 40px;text-align:center;position:relative;overflow:hidden;background:var(--bg-card);border:1.5px solid rgba(153,69,255,.3);}
.fp-cta::before{content:'';position:absolute;top:-120px;left:-80px;width:360px;height:360px;border-radius:50%;background:radial-gradient(circle,rgba(153,69,255,.12),transparent 70%);pointer-events:none;}
.fp-cta::after{content:'';position:absolute;bottom:-100px;right:-60px;width:300px;height:300px;border-radius:50%;background:radial-gradient(circle,rgba(0,240,255,.1),transparent 70%);pointer-events:none;}
.fp-cta h2{font-family:var(--font-heading);font-size:clamp(1.7rem,3vw,2.3rem);font-weight:800;color:var(--text-primary);margin-bottom:10px;position:relative;z-index:1;}
.fp-cta>p{color:var(--text-secondary);margin-bottom:28px;font-size:1.02rem;position:relative;z-index:1;}
.fp-cta .fp-btns{position:relative;z-index:1;margin:0;}
.fp-anim{opacity:0;transform:translateY(28px);transition:opacity .55s ease,transform .55s ease;}
.fp-anim.vis{opacity:1;transform:none;}
.fp-anim[data-d="1"]{transition-delay:.1s;}
.fp-anim[data-d="2"]{transition-delay:.2s;}
.fp-anim[data-d="3"]{transition-delay:.3s;}
@media(max-width:768px){.fp-steps::before{display:none;}.fp-steps{grid-template-columns:1fr 1fr;}.fp-hl,.fp-cta{padding:32px 24px;}.fp-api{padding:20px;}.fp-section h2{font-size:1.5rem;}}
@media(max-width:480px){.fp-steps{grid-template-columns:1fr;}.fp-btns{flex-direction:column;align-items:stretch;}.fp-btn-primary,.fp-btn-outline{justify-content:center;}}
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div class="fp-wrap">
  <section class="fp-hero">
    <div class="fp-badge fp-anim"><i class="fas fa-qrcode"></i>&nbsp; QR Generator Module</div>
    <h1 class="fp-anim" data-d="1">Smart QR Codes<br>That Do More</h1>
    <p class="fp-anim" data-d="2">Generate, customize, track, and manage QR codes at scale &mdash; from simple URLs to geo-targeted dynamic campaigns.</p>
    <div class="fp-btns fp-anim" data-d="3">
    <?php if(Auth::check()): ?>
        <a href="/dashboard" class="fp-btn-primary"><i class="fas fa-rocket"></i> Go to Dashboard</a>
      <?php else: ?>
        <a href="/register" class="fp-btn-primary"><i class="fas fa-rocket"></i> Get Started Free</a>
        <a href="/login" class="fp-btn-outline"><i class="fas fa-sign-in-alt"></i> Sign In</a>
      <?php endif; ?>
    </div>
    <div class="fp-stats fp-anim" data-d="3">
      <span class="fp-stat"><strong>25+</strong> Types</span>
      <span class="fp-stat"><strong>Dynamic</strong> Real-Time Edit</span>
      <span class="fp-stat"><strong>Bulk</strong> CSV Import</span>
      <span class="fp-stat"><strong>REST</strong> API</span>
    </div>
  </section>

  <section class="fp-section fp-anim">
    <div class="fp-lbl"><i class="fas fa-layer-group"></i> Feature Categories</div>
    <h2>Everything You Need to QR</h2>
    <p class="fp-sub">From raw content creation to real-time analytics &mdash; the complete QR toolkit for teams of all sizes.</p>
    <div class="fp-grid">

      <div class="fp-card">
        <div class="fp-card-icon c-cyan">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="3" height="3"/><rect x="18" y="14" width="3" height="3"/><rect x="14" y="18" width="3" height="3"/><rect x="18" y="18" width="3" height="3"/></svg>
        </div>
        <h3>QR Content Types</h3>
        <ul>
          <li class="dot-cyan">URL &amp; website links</li>
          <li class="dot-cyan">vCard contact sharing</li>
          <li class="dot-cyan">WiFi network credentials</li>
          <li class="dot-cyan">SMS &amp; Email compose</li>
          <li class="dot-cyan">App Store / Play Store</li>
          <li class="dot-cyan">Social media profiles</li>
          <li class="dot-cyan">Crypto payment address</li>
          <li class="dot-cyan">Plain text / Calendar event</li>
        </ul>
      </div>

      <div class="fp-card">
        <div class="fp-card-icon c-purple">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
        </div>
        <h3>Dynamic QR Codes</h3>
        <ul>
          <li class="dot-purple">Real-time destination editing</li>
          <li class="dot-purple">Password protection</li>
          <li class="dot-purple">Expiry dates &amp; scan limits</li>
          <li class="dot-purple">A/B testing destinations</li>
          <li class="dot-purple">Geo-targeted redirects</li>
          <li class="dot-purple">Device-based routing</li>
          <li class="dot-purple">Scheduled activation windows</li>
          <li class="dot-purple">Campaign versioning</li>
        </ul>
      </div>

      <div class="fp-card">
        <div class="fp-card-icon c-green">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/><line x1="2" y1="20" x2="22" y2="20"/></svg>
        </div>
        <h3>Scan Analytics</h3>
        <ul>
          <li class="dot-green">Total &amp; unique scan counts</li>
          <li class="dot-green">Geographic heatmap</li>
          <li class="dot-green">Device &amp; OS breakdown</li>
          <li class="dot-green">Time-based scan charts</li>
          <li class="dot-green">Referrer source tracking</li>
          <li class="dot-green">Campaign UTM support</li>
          <li class="dot-green">CSV / JSON export</li>
          <li class="dot-green">Real-time live dashboard</li>
        </ul>
      </div>

      <div class="fp-card">
        <div class="fp-card-icon c-orange">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="13.5" cy="6.5" r="1"/><circle cx="17.5" cy="10.5" r="1"/><circle cx="8.5" cy="7.5" r="1"/><circle cx="6.5" cy="12.5" r="1"/><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10c.926 0 1.648-.746 1.648-1.688 0-.437-.18-.835-.437-1.125-.29-.289-.438-.652-.438-1.125A1.64 1.64 0 0 1 14.441 18h1.996c3.051 0 5.555-2.503 5.555-5.555C21.992 6.014 17.461 2 12 2z"/></svg>
        </div>
        <h3>Design &amp; Branding</h3>
        <ul>
          <li class="dot-orange">Custom foreground &amp; background</li>
          <li class="dot-orange">Logo &amp; image embedding</li>
          <li class="dot-orange">Dot, square &amp; rounded shapes</li>
          <li class="dot-orange">SVG / PNG / PDF export</li>
          <li class="dot-orange">High-DPI print quality</li>
          <li class="dot-orange">Corner frame styles</li>
          <li class="dot-orange">Gradient fill support</li>
          <li class="dot-orange">White-label watermark removal</li>
        </ul>
      </div>

    </div>
  </section>

  <section class="fp-section fp-anim">
    <div class="fp-lbl"><i class="fas fa-route"></i> Workflow</div>
    <h2>How It Works</h2>
    <p class="fp-sub">Four simple steps from idea to deployed, trackable QR code.</p>
    <div class="fp-steps">
      <div class="fp-step">
        <div class="fp-step-num">1</div>
        <h4>Create</h4>
        <p>Choose a content type and enter your URL, contact info, or any data payload.</p>
      </div>
      <div class="fp-step">
        <div class="fp-step-num">2</div>
        <h4>Customize</h4>
        <p>Style the QR code with colors, logo, shape, and choose your export format.</p>
      </div>
      <div class="fp-step">
        <div class="fp-step-num">3</div>
        <h4>Deploy</h4>
        <p>Download, embed, or share via a short trackable link instantly.</p>
      </div>
      <div class="fp-step">
        <div class="fp-step-num">4</div>
        <h4>Track</h4>
        <p>Monitor scans, geo, devices, and conversion data in real time.</p>
      </div>
    </div>
  </section>

  <section class="fp-section fp-anim">
    <div class="fp-lbl"><i class="fas fa-code"></i> Developer API</div>
    <h2>REST API Access</h2>
    <p class="fp-sub">Integrate QR generation and management directly into your product with our clean REST API.</p>
    <div class="fp-api">
      <h3><i class="fas fa-terminal"></i> Endpoints</h3>
      <div class="fp-endpoint"><span class="fp-method get">GET</span><code>/api/v1/qr</code><span class="ep-desc">List all QR codes</span></div>
      <div class="fp-endpoint"><span class="fp-method post">POST</span><code>/api/v1/qr</code><span class="ep-desc">Create a new QR code</span></div>
      <div class="fp-endpoint"><span class="fp-method get">GET</span><code>/api/v1/qr/{id}</code><span class="ep-desc">Get a specific QR code</span></div>
      <div class="fp-endpoint"><span class="fp-method post">POST</span><code>/api/v1/qr/{id}/update</code><span class="ep-desc">Update dynamic QR destination</span></div>
    </div>
  </section>

  <section class="fp-section fp-anim">
    <div class="fp-lbl"><i class="fas fa-table"></i> Plans</div>
    <h2>Compare Plans</h2>
    <p class="fp-sub">Find the right QR Generator plan for your workflow and scale.</p>
    <div class="fp-t-wrap">
      <table class="fp-t">
        <thead><tr>
          <th>Feature</th><th>Free</th><th>Starter</th>
          <th class="pro">Pro <span class="badge-pop">Popular</span></th><th>Enterprise</th>
        </tr></thead>
        <tbody>
          <tr><td>Static QR Codes</td><td><i class="fas fa-check chk"></i></td><td><i class="fas fa-check chk"></i></td><td class="pro"><i class="fas fa-check chk"></i></td><td><i class="fas fa-check chk"></i></td></tr>
          <tr><td>Dynamic QR Codes</td><td><i class="fas fa-times x"></i></td><td><i class="fas fa-check chk"></i></td><td class="pro"><i class="fas fa-check chk"></i></td><td><i class="fas fa-check chk"></i></td></tr>
          <tr><td>Scan Analytics</td><td><i class="fas fa-times x"></i></td><td>Basic</td><td class="pro">Full</td><td>Full + Export</td></tr>
          <tr><td>Bulk Generation</td><td><i class="fas fa-times x"></i></td><td><i class="fas fa-times x"></i></td><td class="pro"><i class="fas fa-check chk"></i></td><td><i class="fas fa-check chk"></i></td></tr>
          <tr><td>A/B Testing</td><td><i class="fas fa-times x"></i></td><td><i class="fas fa-times x"></i></td><td class="pro"><i class="fas fa-check chk"></i></td><td><i class="fas fa-check chk"></i></td></tr>
          <tr><td>API Access</td><td><i class="fas fa-times x"></i></td><td><i class="fas fa-times x"></i></td><td class="pro"><i class="fas fa-check chk"></i></td><td><i class="fas fa-check chk"></i></td></tr>
          <tr><td>Custom Domain</td><td><i class="fas fa-times x"></i></td><td><i class="fas fa-times x"></i></td><td class="pro"><i class="fas fa-check chk"></i></td><td><i class="fas fa-check chk"></i></td></tr>
          <tr><td>White-Label</td><td><i class="fas fa-times x"></i></td><td><i class="fas fa-times x"></i></td><td class="pro"><i class="fas fa-times x"></i></td><td><i class="fas fa-check chk"></i></td></tr>
        </tbody>
      </table>
    </div>
  </section>

  <div class="fp-hl fp-anim">
    <div class="fp-hl-inner">
      <h2>Built for Scale</h2>
      <p>Whether you're managing 10 QR codes or 10 million, our infrastructure scales seamlessly &mdash; with sub-100ms generation and 99.9% uptime SLA.</p>
      <div class="fp-hl-tags">
        <span class="fp-hl-tag">Serverless Generation</span>
        <span class="fp-hl-tag">Edge CDN</span>
        <span class="fp-hl-tag">99.9% Uptime</span>
        <span class="fp-hl-tag">Sub-100ms</span>
        <span class="fp-hl-tag">SOC 2 Ready</span>
      </div>
    </div>
  </div>

  <div class="fp-cta fp-anim">
    <h2>Start Creating QR Codes Today</h2>
    <p>Join thousands of businesses using our QR Generator to power their physical-to-digital experiences.</p>
    <div class="fp-btns">
    <?php if(Auth::check()): ?>
        <a href="/dashboard" class="fp-btn-primary"><i class="fas fa-rocket"></i> Go to Dashboard</a>
      <?php else: ?>
        <a href="/register" class="fp-btn-primary"><i class="fas fa-rocket"></i> Get Started Free</a>
        <a href="/login" class="fp-btn-outline"><i class="fas fa-sign-in-alt"></i> Sign In</a>
      <?php endif; ?>
    </div>
  </div>
</div>
<script>
(function(){
  var els=document.querySelectorAll('.fp-anim');
  if(!els.length)return;
  var io=new IntersectionObserver(function(entries){
    entries.forEach(function(e){if(e.isIntersecting){e.target.classList.add('vis');io.unobserve(e.target);}});
  },{threshold:.12});
  els.forEach(function(el){io.observe(el);});
})();
</script>
<?php View::endSection(); ?>
