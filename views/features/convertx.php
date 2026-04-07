<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php $title = 'ConvertX – Features'; ?>

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
  <div class="fp-badge"><i class="fas fa-exchange-alt"></i> ConvertX Module</div>
  <h1>Convert Any File<br>To Anything</h1>
  <p>200+ format conversions across documents, images, audio, video, and data files. Fast, secure, API-first cloud conversion engine.</p>
  <div class="fp-btns">
    <a href="#" class="fp-btn"><i class="fas fa-upload"></i> Upload &amp; Convert</a>
    <a href="#formats" class="fp-btn-o"><i class="fas fa-list"></i> View All Formats</a>
  </div>
  <div class="fp-stats">
  <div class="fp-stat"><div class="fp-sn">200+</div><div class="fp-sl">Formats</div></div>
  <div class="fp-stat"><div class="fp-sn">OCR</div><div class="fp-sl">Text Extraction</div></div>
  <div class="fp-stat"><div class="fp-sn">Batch</div><div class="fp-sl">Processing</div></div>
  <div class="fp-stat"><div class="fp-sn">API</div><div class="fp-sl">First</div></div>
  </div>
</section><div class="fp-sec" id="formats">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Supported Formats</span>
  <h2>200+ Conversions</h2>
  <p>Documents, images, audio, video, spreadsheets, and data — all in one engine.</p>
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true"><span class="fp-dot" style="background:var(--cyan);"></span>Document Formats<i class="fas fa-chevron-down fp-arr"></i></button>
  <div class="fp-cat-body"><div class="fp-fmts">
    <div class="fp-fmt"><i class="fas fa-file-alt" style="color:var(--cyan);"></i> PDF &rarr; DOCX / HTML / TXT</div>
    <div class="fp-fmt"><i class="fas fa-file-alt" style="color:var(--cyan);"></i> DOCX &rarr; PDF / HTML</div>
    <div class="fp-fmt"><i class="fas fa-file-alt" style="color:var(--cyan);"></i> PPTX &rarr; PDF / Images</div>
    <div class="fp-fmt"><i class="fas fa-file-alt" style="color:var(--cyan);"></i> XLSX &rarr; CSV / PDF</div>
    <div class="fp-fmt"><i class="fas fa-file-alt" style="color:var(--cyan);"></i> TXT &rarr; PDF / DOCX</div>
    <div class="fp-fmt"><i class="fas fa-file-alt" style="color:var(--cyan);"></i> HTML &rarr; PDF / DOCX</div>
    <div class="fp-fmt"><i class="fas fa-file-alt" style="color:var(--cyan);"></i> ODT / ODS / ODP</div>
    <div class="fp-fmt"><i class="fas fa-file-alt" style="color:var(--cyan);"></i> RTF &#8596; DOCX</div>
    <div class="fp-fmt"><i class="fas fa-file-alt" style="color:var(--cyan);"></i> EPUB &rarr; PDF / MOBI</div>
    <div class="fp-fmt"><i class="fas fa-file-alt" style="color:var(--cyan);"></i> Markdown &rarr; PDF / HTML</div>
  </div></div>
</div>
<div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true"><span class="fp-dot" style="background:var(--green);"></span>Image Formats<i class="fas fa-chevron-down fp-arr"></i></button>
  <div class="fp-cat-body"><div class="fp-fmts">
    <div class="fp-fmt"><i class="fas fa-file-image" style="color:var(--green);"></i> JPEG &#8596; PNG / WEBP / AVIF</div>
    <div class="fp-fmt"><i class="fas fa-file-image" style="color:var(--green);"></i> SVG &rarr; PNG / PDF</div>
    <div class="fp-fmt"><i class="fas fa-file-image" style="color:var(--green);"></i> HEIC &rarr; JPEG / PNG</div>
    <div class="fp-fmt"><i class="fas fa-file-image" style="color:var(--green);"></i> TIFF &rarr; JPEG / PNG</div>
    <div class="fp-fmt"><i class="fas fa-file-image" style="color:var(--green);"></i> BMP &rarr; Any Format</div>
    <div class="fp-fmt"><i class="fas fa-file-image" style="color:var(--green);"></i> GIF &rarr; MP4 / WebP</div>
    <div class="fp-fmt"><i class="fas fa-file-image" style="color:var(--green);"></i> RAW (CR2 / NEF / ARW)</div>
    <div class="fp-fmt"><i class="fas fa-file-image" style="color:var(--green);"></i> PSD &rarr; PNG / JPEG</div>
    <div class="fp-fmt"><i class="fas fa-file-image" style="color:var(--green);"></i> ICO &rarr; PNG</div>
    <div class="fp-fmt"><i class="fas fa-file-image" style="color:var(--green);"></i> WebP &rarr; Any Format</div>
  </div></div>
</div>
<div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true"><span class="fp-dot" style="background:var(--orange);"></span>Audio &amp; Video<i class="fas fa-chevron-down fp-arr"></i></button>
  <div class="fp-cat-body"><div class="fp-fmts">
    <div class="fp-fmt"><i class="fas fa-film" style="color:var(--orange);"></i> MP3 &#8596; WAV / FLAC / OGG</div>
    <div class="fp-fmt"><i class="fas fa-film" style="color:var(--orange);"></i> AAC &rarr; MP3 / WAV</div>
    <div class="fp-fmt"><i class="fas fa-film" style="color:var(--orange);"></i> M4A &#8596; MP3</div>
    <div class="fp-fmt"><i class="fas fa-film" style="color:var(--orange);"></i> MP4 &#8596; AVI / MKV / MOV</div>
    <div class="fp-fmt"><i class="fas fa-film" style="color:var(--orange);"></i> WebM &rarr; MP4 / GIF</div>
    <div class="fp-fmt"><i class="fas fa-film" style="color:var(--orange);"></i> Extract Audio from Video</div>
    <div class="fp-fmt"><i class="fas fa-film" style="color:var(--orange);"></i> Video &rarr; GIF / Frames</div>
    <div class="fp-fmt"><i class="fas fa-film" style="color:var(--orange);"></i> MKV &rarr; MP4</div>
  </div></div>
</div>
<div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true"><span class="fp-dot" style="background:var(--purple);"></span>Data &amp; Code<i class="fas fa-chevron-down fp-arr"></i></button>
  <div class="fp-cat-body"><div class="fp-fmts">
    <div class="fp-fmt"><i class="fas fa-file-code" style="color:var(--purple);"></i> JSON &#8596; CSV / XML / YAML</div>
    <div class="fp-fmt"><i class="fas fa-file-code" style="color:var(--purple);"></i> CSV &rarr; JSON / Excel / PDF</div>
    <div class="fp-fmt"><i class="fas fa-file-code" style="color:var(--purple);"></i> XML &#8596; JSON / CSV</div>
    <div class="fp-fmt"><i class="fas fa-file-code" style="color:var(--purple);"></i> SQL &rarr; CSV / JSON</div>
    <div class="fp-fmt"><i class="fas fa-file-code" style="color:var(--purple);"></i> HTML &rarr; Markdown</div>
    <div class="fp-fmt"><i class="fas fa-file-code" style="color:var(--purple);"></i> Base64 &#8596; File</div>
  </div></div>
</div>
</div>
<div class="fp-alt">
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Core Features</span>
  <h2>Smart Conversion Engine</h2>
  
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--cyan);"></span>
    Conversion &amp; Processing
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-eye"></i></div>
        <h3>Live Preview Before Download</h3>
        <p>Preview converted output before downloading. Image preview, document first-page preview, and audio waveform.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-compress"></i></div>
        <h3>Compression &amp; Quality Control</h3>
        <p>Per-format quality sliders (JPEG 1–100, video bitrate, audio kbps). Estimated output size shown before conversion.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-crop-alt"></i></div>
        <h3>Image Resize &amp; Crop</h3>
        <p>Set target dimensions, maintain aspect ratio, pad/fill, or free-crop before final output.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-font"></i></div>
        <h3>OCR Text Extraction</h3>
        <p>Tesseract OCR with 100+ language support. Output as TXT, DOCX, or searchable PDF.</p>
      <span class="fp-tag">OCR</span>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-lock"></i></div>
        <h3>PDF Password Protection</h3>
        <p>Encrypt PDF output with owner + user passwords. Set permissions (print, copy, edit) per document.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-object-group"></i></div>
        <h3>PDF Merge &amp; Split</h3>
        <p>Merge multiple PDFs into one document. Split by page range, every N pages, or by bookmarks.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-stamp"></i></div>
        <h3>Watermark Injection</h3>
        <p>Add text or image watermarks. Configurable opacity, position, rotation, and font.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-redo"></i></div>
        <h3>Image Rotation &amp; Flip</h3>
        <p>Rotate by arbitrary degrees, flip horizontal/vertical, and auto-rotate via EXIF data.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-cut"></i></div>
        <h3>Video Trim &amp; Clip</h3>
        <p>Frame-accurate in/out points via FFmpeg. Extract exactly the segment you need before conversion.</p>
      </div>
    </div>
  </div>
</div></div>
</div>
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Batch Processing</span>
  <h2>Convert Thousands at Once</h2>
  
</div><div class="fp-steps fp-anim">
  <div class="fp-step"><div class="fp-step-n">1</div><h4>Multi-File Upload</h4><p>Drag &amp; drop up to 500 files. Parallel upload with per-file progress.</p></div>
  <div class="fp-step"><div class="fp-step-n">2</div><h4>Apply Settings</h4><p>Set output format and options globally for all files.</p></div>
  <div class="fp-step"><div class="fp-step-n">3</div><h4>Queue &amp; Priority</h4><p>Redis-backed queue. Priority lanes for Pro plans. Pause/resume.</p></div>
  <div class="fp-step"><div class="fp-step-n">4</div><h4>Notification</h4><p>Email/webhook alert on batch complete with partial-success report.</p></div>
  <div class="fp-step"><div class="fp-step-n">5</div><h4>ZIP Download</h4><p>All files in one ZIP. CDN-hosted with 24 h expiry link.</p></div>
</div></div>
<div class="fp-alt">
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Developer API</span>
  <h2>API-First Conversion</h2>
  
</div><div class="fp-api fp-anim">
  <div class="fp-api-h"><span class="fp-m fp-m-post">POST</span><span class="fp-api-p">/api/v1/convert</span></div>
  <div class="fp-api-b">{
  "input_url": "https://example.com/document.docx",
  "output_format": "pdf",
  "options": {
    "quality": 90,
    "watermark": { "text": "CONFIDENTIAL", "opacity": 0.3 },
    "password": "secret123"
  },
  "webhook_url": "https://yourapp.com/webhook/convert",
  "async": true
}</div>
</div><div class="fp-api fp-anim">
  <div class="fp-api-h"><span class="fp-m fp-m-get">GET</span><span class="fp-api-p">/api/v1/jobs/{id}/status</span></div>
  <div class="fp-api-b">{ "status": "completed", "input_format": "docx", "output_format": "pdf",
  "file_size_kb": 248, "download_url": "https://cdn.example.com/out/job_xyz.pdf",
  "expires_at": "2025-12-31T23:59:59Z" }</div>
</div><div class="fp-grid fp-anim">
      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-link"></i></div>
        <h3>URL &amp; S3 Input Sources</h3>
        <p>Submit files via direct URL, Amazon S3 presigned URL, Google Cloud Storage, or Dropbox/Drive links.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-cloud-upload-alt"></i></div>
        <h3>S3 Output Delivery</h3>
        <p>Deliver converted files directly to your S3 bucket, GCS bucket, or FTP/SFTP server.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-tachometer-alt"></i></div>
        <h3>Throughput SLAs</h3>
        <p>P95 target: under 5 s for documents, under 30 s per minute of video content.</p>
      </div></div>
</div>
</div>
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Security</span>
  <h2>Your Files Are Safe</h2>
  
</div><div class="fp-grid fp-anim">
      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-trash-alt"></i></div>
        <h3>Auto-Delete After Conversion</h3>
        <p>All uploaded files and outputs permanently deleted after 1 hour (configurable). Zero long-term storage.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-shield-alt"></i></div>
        <h3>End-to-End Encryption</h3>
        <p>Files encrypted in transit (TLS 1.3) and at rest (AES-256). Per-user keys on Enterprise.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-virus-slash"></i></div>
        <h3>Malware Scanning</h3>
        <p>All uploads scanned with ClamAV before processing. Infected files rejected with detailed error report.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-eye-slash"></i></div>
        <h3>Isolated Sandbox</h3>
        <p>Files processed in per-request sandboxed containers. No cross-tenant data access possible.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-file-contract"></i></div>
        <h3>GDPR Compliance</h3>
        <p>Right-to-erasure endpoint, data processing agreements, and processing log export for audits.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-list-alt"></i></div>
        <h3>File Type Controls</h3>
        <p>Configurable per-plan file size limits. Extension whitelist/blacklist for security hardening.</p>
      </div></div>
</div>
<div class="fp-tw fp-anim">
  <table class="fp-t">
    <thead><tr><th>Feature</th><th>Free</th><th>Pro</th><th>Enterprise</th></tr></thead>
    <tbody><tr><td>Max File Size</td><td>25 MB</td><td>500 MB</td><td>10 GB</td></tr>
<tr><td>Formats</td><td>50+</td><td>200+</td><td>200+</td></tr>
<tr><td>OCR</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>Batch Processing</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>API Access</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>S3 Delivery</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>SLA Guarantee</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td></tr>
</tbody>
  </table>
</div><div class="fp-hl fp-anim">
  <h3><i class="fas fa-bolt"></i> Powered by FFmpeg &amp; LibreOffice</h3>
  <p>ConvertX uses battle-tested open-source engines under a hardened API layer — giving you reliable, high-fidelity conversions at any scale.</p>
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
