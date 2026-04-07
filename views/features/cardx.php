<?php use Core\View; use Core\Auth; ?>
<?php View::extend('main'); ?>
<?php $title = 'CardX – Features'; ?>

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
    <div class="fp-badge fp-anim"><i class="fas fa-id-card"></i>&nbsp; CardX Module</div>
    <h1 class="fp-anim" data-d="1">Digital ID Cards &amp;<br>Business Profiles</h1>
    <p class="fp-anim" data-d="2">Design, print, and share professional ID cards and business cards with NFC, QR code sharing, and custom templates.</p>
    <div class="fp-btns fp-anim" data-d="3">
      <?php if(Auth::check()): ?>
        <a href="/dashboard" class="fp-btn-primary"><i class="fas fa-rocket"></i> Go to Dashboard</a>
      <?php else: ?>
        <a href="/register" class="fp-btn-primary"><i class="fas fa-rocket"></i> Get Started Free</a>
        <a href="/login" class="fp-btn-outline"><i class="fas fa-sign-in-alt"></i> Sign In</a>
      <?php endif; ?>
    </div>
    <div class="fp-stats fp-anim" data-d="3">
      <span class="fp-stat"><strong>NFC</strong> Ready</span>
      <span class="fp-stat"><strong>QR</strong> Built-In</span>
      <span class="fp-stat"><strong>Print</strong>-Ready</span>
      <span class="fp-stat"><strong>PDF</strong> Export</span>
    </div>
  </section>

  <section class="fp-section fp-anim">
    <div class="fp-lbl"><i class="fas fa-layer-group"></i> Feature Categories</div>
    <h2>Cards That Make an Impression</h2>
    <p class="fp-sub">Design once, share everywhere &mdash; NFC tap, QR scan, printed card, or digital link.</p>
    <div class="fp-grid">

      <div class="fp-card">
        <div class="fp-card-icon c-magenta">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg>
        </div>
        <h3>Card Design</h3>
        <ul>
          <li class="dot-magenta">Drag-and-drop card editor</li>
          <li class="dot-magenta">10+ professional templates</li>
          <li class="dot-magenta">Custom fonts &amp; colors</li>
          <li class="dot-magenta">Photo &amp; logo upload</li>
          <li class="dot-magenta">Background patterns &amp; gradients</li>
          <li class="dot-magenta">QR code auto-embed</li>
          <li class="dot-magenta">Front &amp; back design</li>
          <li class="dot-magenta">Real-time preview</li>
        </ul>
      </div>

      <div class="fp-card">
        <div class="fp-card-icon c-cyan">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 12v8a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-8"/><polyline points="16 6 12 2 8 6"/><line x1="12" y1="2" x2="12" y2="15"/></svg>
        </div>
        <h3>Digital Sharing</h3>
        <ul>
          <li class="dot-cyan">NFC tap-to-share</li>
          <li class="dot-cyan">QR code embed &amp; scan</li>
          <li class="dot-cyan">Shareable public link</li>
          <li class="dot-cyan">vCard (.vcf) download</li>
          <li class="dot-cyan">Apple Wallet / Google Wallet</li>
          <li class="dot-cyan">Email signature embed</li>
          <li class="dot-cyan">Custom short URL alias</li>
          <li class="dot-cyan">One-tap contact save</li>
        </ul>
      </div>

      <div class="fp-card">
        <div class="fp-card-icon c-green">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
        </div>
        <h3>Print &amp; Export</h3>
        <ul>
          <li class="dot-green">Print-ready PDF at 300 DPI</li>
          <li class="dot-green">Standard business card size</li>
          <li class="dot-green">A4 / A5 sheet layouts</li>
          <li class="dot-green">Bulk export (ZIP)</li>
          <li class="dot-green">PNG &amp; SVG export</li>
          <li class="dot-green">Bleed &amp; crop marks</li>
          <li class="dot-green">CMYK-ready color profiles</li>
          <li class="dot-green">Print provider integration</li>
        </ul>
      </div>

      <div class="fp-card">
        <div class="fp-card-icon c-purple">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>
        </div>
        <h3>Organization</h3>
        <ul>
          <li class="dot-purple">Card collections &amp; folders</li>
          <li class="dot-purple">Department grouping</li>
          <li class="dot-purple">Expiry date controls</li>
          <li class="dot-purple">Role-based access control</li>
          <li class="dot-purple">Team card templates</li>
          <li class="dot-purple">Bulk card generation (CSV)</li>
          <li class="dot-purple">Card version history</li>
          <li class="dot-purple">Usage analytics per card</li>
        </ul>
      </div>

    </div>
  </section>

  <section class="fp-section fp-anim">
    <div class="fp-lbl"><i class="fas fa-route"></i> Workflow</div>
    <h2>How It Works</h2>
    <p class="fp-sub">Four simple steps from concept to a shareable, professional card.</p>
    <div class="fp-steps">
      <div class="fp-step"><div class="fp-step-num">1</div><h4>Design</h4><p>Use the drag-and-drop editor to build your card with templates, photos, and branding.</p></div>
      <div class="fp-step"><div class="fp-step-num">2</div><h4>Configure</h4><p>Set NFC, QR options, expiry, privacy, and sharing permissions for each card.</p></div>
      <div class="fp-step"><div class="fp-step-num">3</div><h4>Share</h4><p>Tap via NFC, scan a QR, or send a link &mdash; contacts saved instantly.</p></div>
      <div class="fp-step"><div class="fp-step-num">4</div><h4>Manage</h4><p>Update content anytime, track views, and organise cards by team or department.</p></div>
    </div>
  </section>

  <section class="fp-section fp-anim">
    <div class="fp-lbl"><i class="fas fa-code"></i> Developer API</div>
    <h2>REST API Access</h2>
    <p class="fp-sub">Programmatically create, update, and manage cards at scale for enterprise deployments.</p>
    <div class="fp-api">
      <h3><i class="fas fa-terminal"></i> Endpoints</h3>
      <div class="fp-endpoint"><span class="fp-method get">GET</span><code>/api/v1/cards</code><span class="ep-desc">List all cards</span></div>
      <div class="fp-endpoint"><span class="fp-method post">POST</span><code>/api/v1/cards</code><span class="ep-desc">Create a card</span></div>
      <div class="fp-endpoint"><span class="fp-method get">GET</span><code>/api/v1/cards/{id}</code><span class="ep-desc">Get card details</span></div>
      <div class="fp-endpoint"><span class="fp-method post">POST</span><code>/api/v1/cards/bulk</code><span class="ep-desc">Bulk generate from CSV</span></div>
    </div>
  </section>

  <section class="fp-section fp-anim">
    <div class="fp-lbl"><i class="fas fa-table"></i> Plans</div>
    <h2>Compare Plans</h2>
    <p class="fp-sub">Find the CardX plan that fits your team size and design needs.</p>
    <div class="fp-t-wrap">
      <table class="fp-t">
        <thead><tr>
          <th>Feature</th><th>Free</th><th>Starter</th>
          <th class="pro">Pro <span class="badge-pop">Popular</span></th><th>Enterprise</th>
        </tr></thead>
        <tbody>
          <tr><td>Card Designer</td><td><i class="fas fa-check chk"></i></td><td><i class="fas fa-check chk"></i></td><td class="pro"><i class="fas fa-check chk"></i></td><td><i class="fas fa-check chk"></i></td></tr>
          <tr><td>Templates</td><td>3 Free</td><td><i class="fas fa-check chk"></i></td><td class="pro">All 10+</td><td>Custom</td></tr>
          <tr><td>NFC Sharing</td><td><i class="fas fa-times x"></i></td><td><i class="fas fa-check chk"></i></td><td class="pro"><i class="fas fa-check chk"></i></td><td><i class="fas fa-check chk"></i></td></tr>
          <tr><td>Bulk Export</td><td><i class="fas fa-times x"></i></td><td><i class="fas fa-times x"></i></td><td class="pro"><i class="fas fa-check chk"></i></td><td><i class="fas fa-check chk"></i></td></tr>
          <tr><td>Custom Domain</td><td><i class="fas fa-times x"></i></td><td><i class="fas fa-times x"></i></td><td class="pro"><i class="fas fa-check chk"></i></td><td><i class="fas fa-check chk"></i></td></tr>
          <tr><td>API Access</td><td><i class="fas fa-times x"></i></td><td><i class="fas fa-times x"></i></td><td class="pro"><i class="fas fa-check chk"></i></td><td><i class="fas fa-check chk"></i></td></tr>
          <tr><td>White-Label</td><td><i class="fas fa-times x"></i></td><td><i class="fas fa-times x"></i></td><td class="pro"><i class="fas fa-times x"></i></td><td><i class="fas fa-check chk"></i></td></tr>
          <tr><td>Priority Support</td><td><i class="fas fa-times x"></i></td><td><i class="fas fa-times x"></i></td><td class="pro"><i class="fas fa-check chk"></i></td><td><i class="fas fa-check chk"></i></td></tr>
        </tbody>
      </table>
    </div>
  </section>

  <div class="fp-hl fp-anim">
    <div class="fp-hl-inner">
      <h2>Make Every First Impression Count</h2>
      <p>Your business card is often the first thing people remember. Make it unforgettable with NFC, dynamic content, and pixel-perfect design.</p>
      <div class="fp-hl-tags">
        <span class="fp-hl-tag">NFC Tap Sharing</span>
        <span class="fp-hl-tag">QR Built-In</span>
        <span class="fp-hl-tag">Print-Ready</span>
        <span class="fp-hl-tag">10+ Templates</span>
        <span class="fp-hl-tag">vCard Export</span>
      </div>
    </div>
  </div>

  <div class="fp-cta fp-anim">
    <h2>Create Your First Card Today</h2>
    <p>Join professionals and businesses using CardX to share their identity instantly &mdash; digital-first, always up-to-date.</p>
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
