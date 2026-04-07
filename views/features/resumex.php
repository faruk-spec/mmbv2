<?php use Core\View; ?>
<?php View::extend('main'); ?>

<?php $title = 'ResumeX – Features'; ?>

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
  <div class="fp-badge"><i class="fas fa-file-alt"></i> ResumeX Module</div>
  <h1>Build Resumes That<br>Get You Hired</h1>
  <p>Full-featured resume builder with AI writing assistance, ATS optimization, real-time collaboration, and 50+ professional templates.</p>
  <div class="fp-btns">
    <a href="#" class="fp-btn"><i class="fas fa-plus"></i> Create Resume</a>
    <a href="#builder" class="fp-btn-o"><i class="fas fa-list"></i> See Features</a>
  </div>
  <div class="fp-stats">
  <div class="fp-stat"><div class="fp-sn">50+</div><div class="fp-sl">Templates</div></div>
  <div class="fp-stat"><div class="fp-sn">AI</div><div class="fp-sl">Writing Assistant</div></div>
  <div class="fp-stat"><div class="fp-sn">ATS</div><div class="fp-sl">Score Checker</div></div>
  <div class="fp-stat"><div class="fp-sn">PDF</div><div class="fp-sl">Export</div></div>
  </div>
</section><div class="fp-sec" id="builder">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Builder</span>
  <h2>Drag, Drop, Done</h2>
  <p>Powerful editor with live preview — no design skills needed.</p>
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--cyan);"></span>
    Section Types
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-user"></i></div>
        <h3>Personal Info Header</h3>
        <p>Name, title, photo (circle/square/none), contact details, social links with icon auto-detection (LinkedIn, GitHub, Twitter).</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-briefcase"></i></div>
        <h3>Work Experience</h3>
        <p>Multi-entry timeline with company, role, dates, location, and bullet-point achievements. Date range picker with "Present" option.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-graduation-cap"></i></div>
        <h3>Education</h3>
        <p>Institution, degree, field, GPA, honors, relevant coursework list, and thesis title fields.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-tools"></i></div>
        <h3>Skills &amp; Proficiency</h3>
        <p>Tag-based skill list with optional proficiency bars (beginner/intermediate/expert) or star ratings. Category grouping.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-trophy"></i></div>
        <h3>Projects &amp; Portfolio</h3>
        <p>Project cards with title, description, tech stack badges, live URL, and GitHub link. Thumbnail support.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-certificate"></i></div>
        <h3>Certifications &amp; Awards</h3>
        <p>Certification name, issuer, date, credential ID, and verification URL. Badge display mode.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-language"></i></div>
        <h3>Languages</h3>
        <p>Language name with proficiency (A1–C2 / Native). Visual flag icon support.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-heart"></i></div>
        <h3>Volunteer / Custom Sections</h3>
        <p>Free-form custom sections with any label — volunteer work, publications, patents, or hobbies.</p>
      </div>
    </div>
  </div>
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--purple);"></span>
    Editor Capabilities
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-sync"></i></div>
        <h3>Real-Time Live Preview</h3>
        <p>Every change renders instantly. Split-screen with zoom controls, page-break visualization, and print-safe margin guides.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-arrows-alt"></i></div>
        <h3>Drag-and-Drop Reordering</h3>
        <p>Reorder any section by dragging. Reorder bullet points within sections. Keyboard-accessible.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-undo"></i></div>
        <h3>Undo / Redo (50 steps)</h3>
        <p>Full undo/redo history. Named checkpoints let you jump to any editing state instantly.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-save"></i></div>
        <h3>Auto-Save Every 30 s</h3>
        <p>Changes saved automatically. Never lose work. Local-storage fallback if network is unavailable.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-file-alt"></i></div>
        <h3>Multi-Page Support</h3>
        <p>Automatic overflow to page 2+. Manual page-break markers. Per-page margin control.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-bold"></i></div>
        <h3>Rich Text Editing</h3>
        <p>Inline bold, italic, underline, ordered/unordered lists per bullet. Paste formatting stripped automatically.</p>
      </div>
    </div>
  </div>
</div></div>
<div class="fp-alt">
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">AI Features</span>
  <h2>Write Better, Faster</h2>
  <p>Embedded AI tools that transform rough notes into compelling, ATS-optimized content.</p>
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--purple);"></span>
    AI Writing &amp; Optimization
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-magic"></i></div>
        <h3>Bullet Point Enhancer</h3>
        <p>Paste rough bullet → AI rewrites with action verbs, metrics, and impact statements. "Show, don't tell" engine.</p>
      <span class="fp-tag">AI</span>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-align-left"></i></div>
        <h3>Summary / Objective Writer</h3>
        <p>Enter role title, years of experience, and key skills → AI generates a tailored 3–4 sentence professional summary.</p>
      <span class="fp-tag">AI</span>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-check-double"></i></div>
        <h3>ATS Score Checker</h3>
        <p>Paste a job description → match score (0–100) with missing keywords highlighted and priority fix suggestions.</p>
      <span class="fp-tag">ATS</span>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-sync"></i></div>
        <h3>Tone Adjuster</h3>
        <p>Switch writing tone: formal, confident, creative, concise — while preserving factual accuracy.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-search"></i></div>
        <h3>Keyword Gap Analysis</h3>
        <p>Compare resume against job description. Shows matched, missing, and over-used terms with frequency scores.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-lightbulb"></i></div>
        <h3>Role-Specific Suggestions</h3>
        <p>Based on target job title, AI suggests sections to add, skills to highlight, and achievements to quantify.</p>
      </div>
    </div>
  </div>
</div></div>
</div>
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Templates</span>
  <h2>50+ Professional Designs</h2>
  
</div><div class="fp-cat fp-anim">
  <button class="fp-cat-btn" aria-expanded="true">
    <span class="fp-dot" style="background:var(--orange);"></span>
    Template Categories
    <i class="fas fa-chevron-down fp-arr"></i>
  </button>
  <div class="fp-cat-body">
    <div class="fp-grid">
      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-columns"></i></div>
        <h3>One-Column Classic</h3>
        <p>Traditional single-column. Maximum ATS compatibility. Preferred by Fortune 500 recruiters.</p>
      <span class="fp-tag">ATS Safe</span>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-th-large"></i></div>
        <h3>Two-Column Modern</h3>
        <p>Skills/info sidebar with main content column. Eye-catching for creative and tech roles.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-paint-brush"></i></div>
        <h3>Creative / Design Portfolio</h3>
        <p>Full-color header, project image strips, and icon-rich layout for designers and creatives.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-code"></i></div>
        <h3>Tech / Developer</h3>
        <p>GitHub-inspired dark template with code block sections and tech stack badges.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-user-tie"></i></div>
        <h3>Executive / C-Level</h3>
        <p>Minimal, authority-projecting layout. Large name, vertical divider sections, premium typography.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-graduation-cap"></i></div>
        <h3>Academic / CV</h3>
        <p>Multi-page academic CV with publications, conferences, grants, and teaching experience.</p>
      </div>
    </div>
  </div>
</div><div class="fp-hl fp-anim">
  <h3><i class="fas fa-sliders-h"></i> Template Customization</h3>
  <p>Every template supports full color palette swap, font family choice (12 options), spacing density (compact/standard/relaxed), and section visibility toggles — without touching any code.</p>
</div></div>
<div class="fp-alt">
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Export &amp; Sharing</span>
  <h2>Share Your Resume, Your Way</h2>
  
</div><div class="fp-grid fp-anim">
      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-file-pdf"></i></div>
        <h3>Pixel-Perfect PDF Export</h3>
        <p>Headless Chrome rendering. CMYK-safe colors, embedded fonts, exact margin preservation, and selectable text.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-file-word"></i></div>
        <h3>DOCX Export</h3>
        <p>Editable Word document export with styles preserved. Useful for applications requiring editable format.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-link"></i></div>
        <h3>Shareable Public Link</h3>
        <p>Unique public URL with custom slug. Password protect or set link expiry.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-chart-bar"></i></div>
        <h3>View Tracking</h3>
        <p>Know when employers view your resume. See view count, unique visitors, countries, and time spent.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-m"><i class="fas fa-code"></i></div>
        <h3>Embed Widget</h3>
        <p>Embed your resume on any website as an iframe or JS widget. Responsive with configurable height.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-qrcode"></i></div>
        <h3>Resume QR Code</h3>
        <p>Auto-generate a QR code for your public resume link. Download for business cards or cover letters.</p>
      </div></div>
</div>
</div>
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Collaboration</span>
  <h2>Resume Coaching Made Easy</h2>
  
</div><div class="fp-grid fp-anim">
      <div class="fp-card">
        <div class="fp-ic ic-o"><i class="fas fa-users"></i></div>
        <h3>Reviewer Invites</h3>
        <p>Invite mentors, career coaches, or recruiters with comment-only or edit access via email link.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-p"><i class="fas fa-comment-dots"></i></div>
        <h3>Inline Comments</h3>
        <p>Reviewers leave sticky comments on any section. Thread-based replies with resolve/reopen flow.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-g"><i class="fas fa-history"></i></div>
        <h3>Version History</h3>
        <p>Named snapshots (v1, "After Interview", "Tailored for Google"). One-click restore to any version.</p>
      </div>      <div class="fp-card">
        <div class="fp-ic ic-c"><i class="fas fa-copy"></i></div>
        <h3>Resume Duplication</h3>
        <p>Clone a base resume for job-specific variants. Track which version was sent to which employer.</p>
      </div></div>
</div>
<div class="fp-alt">
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">User Journey</span>
  <h2>Hired in 6 Steps</h2>
  
</div><div class="fp-steps fp-anim">
  <div class="fp-step"><div class="fp-step-n">1</div><h4>Choose Template</h4><p>Pick from 50+ designs</p></div>
  <div class="fp-step"><div class="fp-step-n">2</div><h4>Fill Sections</h4><p>Drag-drop, type, import</p></div>
  <div class="fp-step"><div class="fp-step-n">3</div><h4>AI Enhance</h4><p>Bullet enhancer &amp; summary writer</p></div>
  <div class="fp-step"><div class="fp-step-n">4</div><h4>ATS Check</h4><p>Score vs job description</p></div>
  <div class="fp-step"><div class="fp-step-n">5</div><h4>Export</h4><p>PDF, DOCX, or public link</p></div>
  <div class="fp-step"><div class="fp-step-n">6</div><h4>Share &amp; Track</h4><p>See who viewed your resume</p></div>
</div></div>
</div>
<div class="fp-sec">
<div class="fp-sec-hd fp-anim">
  <span class="fp-lbl">Plan Comparison</span>
  <h2>Features by Plan</h2>
  
</div><div class="fp-tw fp-anim">
  <table class="fp-t">
    <thead><tr><th>Feature</th><th>Free</th><th>Pro</th><th>Team</th></tr></thead>
    <tbody><tr><td>Resume Count</td><td>3</td><td>Unlimited</td><td>Unlimited</td></tr>
<tr><td>Templates</td><td>10</td><td>50+</td><td>50+</td></tr>
<tr><td>PDF Export</td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>AI Writing Assistant</td><td><i class="fas fa-minus pt"></i> 10/mo</td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>ATS Checker</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>View Analytics</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>Reviewer Collaboration</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
<tr><td>API Access</td><td><i class="fas fa-times no"></i></td><td><i class="fas fa-check ck"></i></td><td><i class="fas fa-check ck"></i></td></tr>
</tbody>
  </table>
</div></div>
<div class="fp-hl fp-anim">
  <h3><i class="fas fa-rocket"></i> AI-Powered Resume Engine</h3>
  <p>ResumeX uses large language models fine-tuned on thousands of successful resumes to deliver context-aware suggestions that measurably increase interview rates.</p>
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
