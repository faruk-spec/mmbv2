<?php use Core\View; use Core\Auth; ?>
<?php View::extend('main'); ?>
<?php $title = 'ConvertX – Features'; ?>

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
    <div class="fp-badge fp-anim"><i class="fas fa-exchange-alt"></i>&nbsp; ConvertX Module</div>
    <h1 class="fp-anim" data-d="1">Convert Any File<br>Format Instantly</h1>
    <p class="fp-anim" data-d="2">Powerful file conversion for images, documents, audio, and video. Batch process hundreds of files with a single click.</p>
    <div class="fp-btns fp-anim" data-d="3">
      <?php if(Auth::check()): ?>
        <a href="/dashboard" class="fp-btn-primary"><i class="fas fa-rocket"></i> Go to Dashboard</a>
      <?php else: ?>
        <a href="/register" class="fp-btn-primary"><i class="fas fa-rocket"></i> Get Started Free</a>
        <a href="/login" class="fp-btn-outline"><i class="fas fa-sign-in-alt"></i> Sign In</a>
      <?php endif; ?>
    </div>
    <div class="fp-stats fp-anim" data-d="3">
      <span class="fp-stat"><strong>50+</strong> Formats</span>
      <span class="fp-stat"><strong>Batch</strong> Convert</span>
      <span class="fp-stat"><strong>OCR</strong> Ready</span>
      <span class="fp-stat"><strong>REST</strong> API</span>
    </div>
  </section>

  <section class="fp-section fp-anim">
    <div class="fp-lbl"><i class="fas fa-layer-group"></i> Feature Categories</div>
    <h2>Convert Everything, Instantly</h2>
    <p class="fp-sub">Images, documents, compression &mdash; one unified pipeline with blazing-fast performance.</p>
    <div class="fp-grid">

      <div class="fp-card">
        <div class="fp-card-icon c-cyan">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
        </div>
        <h3>Image Conversion</h3>
        <ul>
          <li class="dot-cyan">JPG / PNG / WebP / AVIF</li>
          <li class="dot-cyan">SVG / TIFF / BMP / GIF</li>
          <li class="dot-cyan">Resize &amp; crop</li>
          <li class="dot-cyan">Smart compression</li>
          <li class="dot-cyan">Background removal (AI)</li>
          <li class="dot-cyan">Color space conversion</li>
          <li class="dot-cyan">Watermark overlay</li>
          <li class="dot-cyan">Batch rename on export</li>
        </ul>
      </div>

      <div class="fp-card">
        <div class="fp-card-icon c-purple">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
        </div>
        <h3>Document Conversion</h3>
        <ul>
          <li class="dot-purple">PDF &harr; Word / Excel / PPT</li>
          <li class="dot-purple">HTML &rarr; PDF rendering</li>
          <li class="dot-purple">Markdown &rarr; PDF / HTML</li>
          <li class="dot-purple">OCR text extraction</li>
          <li class="dot-purple">Image &rarr; searchable PDF</li>
          <li class="dot-purple">Merge &amp; split PDFs</li>
          <li class="dot-purple">Password protect PDFs</li>
          <li class="dot-purple">Form field extraction</li>
        </ul>
      </div>

      <div class="fp-card">
        <div class="fp-card-icon c-green">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        </div>
        <h3>Compression</h3>
        <ul>
          <li class="dot-green">Smart auto-compression</li>
          <li class="dot-green">Quality slider control</li>
          <li class="dot-green">Lossless &amp; lossy modes</li>
          <li class="dot-green">Before/after size preview</li>
          <li class="dot-green">Target file size mode</li>
          <li class="dot-green">PDF compression</li>
          <li class="dot-green">ZIP archive packing</li>
          <li class="dot-green">Compression ratio report</li>
        </ul>
      </div>

      <div class="fp-card">
        <div class="fp-card-icon c-orange">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="21 8 21 21 3 21 3 8"/><rect x="1" y="3" width="22" height="5"/><line x1="10" y1="12" x2="14" y2="12"/></svg>
        </div>
        <h3>Batch Processing</h3>
        <ul>
          <li class="dot-orange">Multi-file drag &amp; drop upload</li>
          <li class="dot-orange">ZIP output download</li>
          <li class="dot-orange">Live progress tracking</li>
          <li class="dot-orange">Queue management</li>
          <li class="dot-orange">Error file reporting</li>
          <li class="dot-orange">Folder structure preserve</li>
          <li class="dot-orange">Scheduled batch jobs</li>
          <li class="dot-orange">Email on completion</li>
        </ul>
      </div>

    </div>
  </section>

  <section class="fp-section fp-anim">
    <div class="fp-lbl"><i class="fas fa-route"></i> Workflow</div>
    <h2>How It Works</h2>
    <p class="fp-sub">Upload, configure, convert, and download in four frictionless steps.</p>
    <div class="fp-steps">
      <div class="fp-step"><div class="fp-step-num">1</div><h4>Upload</h4><p>Drag and drop single files or entire ZIP archives into the conversion panel.</p></div>
      <div class="fp-step"><div class="fp-step-num">2</div><h4>Select Format</h4><p>Choose your target format, quality, resolution, and any advanced options.</p></div>
      <div class="fp-step"><div class="fp-step-num">3</div><h4>Convert</h4><p>Our pipeline processes files in parallel &mdash; most conversions complete in seconds.</p></div>
      <div class="fp-step"><div class="fp-step-num">4</div><h4>Download</h4><p>Download converted files individually or as a single ZIP archive.</p></div>
    </div>
  </section>

  <section class="fp-section fp-anim">
    <div class="fp-lbl"><i class="fas fa-code"></i> Developer API</div>
    <h2>REST API Access</h2>
    <p class="fp-sub">Integrate file conversion directly into your app &mdash; submit jobs and poll for results asynchronously.</p>
    <div class="fp-api">
      <h3><i class="fas fa-terminal"></i> Endpoints</h3>
      <div class="fp-endpoint"><span class="fp-method post">POST</span><code>/api/v1/convert</code><span class="ep-desc">Submit a conversion job</span></div>
      <div class="fp-endpoint"><span class="fp-method get">GET</span><code>/api/v1/jobs/{id}</code><span class="ep-desc">Poll job status</span></div>
      <div class="fp-endpoint"><span class="fp-method get">GET</span><code>/api/v1/jobs/{id}/download</code><span class="ep-desc">Download result file</span></div>
      <div class="fp-endpoint"><span class="fp-method post">POST</span><code>/api/v1/convert/batch</code><span class="ep-desc">Submit a batch job</span></div>
    </div>
  </section>

  <section class="fp-section fp-anim">
    <div class="fp-lbl"><i class="fas fa-table"></i> Plans</div>
    <h2>Compare Plans</h2>
    <p class="fp-sub">Choose the ConvertX plan that matches your volume and format requirements.</p>
    <div class="fp-t-wrap">
      <table class="fp-t">
        <thead><tr>
          <th>Feature</th><th>Free</th><th>Starter</th>
          <th class="pro">Pro <span class="badge-pop">Popular</span></th><th>Enterprise</th>
        </tr></thead>
        <tbody>
          <tr><td>Image Convert</td><td><i class="fas fa-check chk"></i></td><td><i class="fas fa-check chk"></i></td><td class="pro"><i class="fas fa-check chk"></i></td><td><i class="fas fa-check chk"></i></td></tr>
          <tr><td>Document Convert</td><td><i class="fas fa-times x"></i></td><td><i class="fas fa-check chk"></i></td><td class="pro"><i class="fas fa-check chk"></i></td><td><i class="fas fa-check chk"></i></td></tr>
          <tr><td>Video Convert</td><td><i class="fas fa-times x"></i></td><td><i class="fas fa-times x"></i></td><td class="pro"><i class="fas fa-check chk"></i></td><td><i class="fas fa-check chk"></i></td></tr>
          <tr><td>Batch Processing</td><td><i class="fas fa-times x"></i></td><td>10 files</td><td class="pro">Unlimited</td><td>Unlimited</td></tr>
          <tr><td>OCR</td><td><i class="fas fa-times x"></i></td><td><i class="fas fa-times x"></i></td><td class="pro"><i class="fas fa-check chk"></i></td><td><i class="fas fa-check chk"></i></td></tr>
          <tr><td>API Access</td><td><i class="fas fa-times x"></i></td><td><i class="fas fa-times x"></i></td><td class="pro"><i class="fas fa-check chk"></i></td><td><i class="fas fa-check chk"></i></td></tr>
          <tr><td>Priority Queue</td><td><i class="fas fa-times x"></i></td><td><i class="fas fa-times x"></i></td><td class="pro"><i class="fas fa-check chk"></i></td><td><i class="fas fa-check chk"></i></td></tr>
          <tr><td>White-Label</td><td><i class="fas fa-times x"></i></td><td><i class="fas fa-times x"></i></td><td class="pro"><i class="fas fa-times x"></i></td><td><i class="fas fa-check chk"></i></td></tr>
        </tbody>
      </table>
    </div>
  </section>

  <div class="fp-hl fp-anim">
    <div class="fp-hl-inner">
      <h2>Blazing Fast Conversion Pipeline</h2>
      <p>Powered by an async parallel-processing engine, ConvertX handles thousands of concurrent jobs &mdash; images in &lt;200ms, documents in seconds.</p>
      <div class="fp-hl-tags">
        <span class="fp-hl-tag">50+ Formats</span>
        <span class="fp-hl-tag">Async Jobs</span>
        <span class="fp-hl-tag">OCR Engine</span>
        <span class="fp-hl-tag">ZIP Output</span>
        <span class="fp-hl-tag">Priority Queue</span>
      </div>
    </div>
  </div>

  <div class="fp-cta fp-anim">
    <h2>Convert Your First File Free</h2>
    <p>No sign-up required for basic conversions. Go Pro for batch processing, OCR, and full API access.</p>
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
