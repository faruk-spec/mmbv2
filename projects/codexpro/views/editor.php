<?php
$defaultTheme = 'dark';
$csrfToken = \Core\Security::generateCsrfToken();
try {
    $db = \Core\Database::getInstance();
    $navbarSettings = $db->fetch("SELECT default_theme FROM navbar_settings WHERE id = 1");
    if ($navbarSettings && !empty($navbarSettings['default_theme'])) {
        $defaultTheme = $navbarSettings['default_theme'];
    }
} catch (\Exception $e) {
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?= htmlspecialchars($defaultTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars($csrfToken) ?>">
    <title><?= htmlspecialchars($project['name'] ?? 'Untitled Project') ?> — CodeXPro Editor</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/codemirror.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/theme/dracula.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/addon/fold/foldgutter.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/addon/hint/show-hint.min.css">
    <link rel="stylesheet" href="/css/universal-theme.css">

    <style>
        :root {
            --editor-bg: #0f0f18;
            --editor-surface: #13131f;
            --editor-toolbar: #0c0c16;
            --editor-border: rgba(255,255,255,0.08);
            --editor-accent: #00f0ff;
            --editor-text: #e8eefc;
            --editor-muted: #8892a6;
            --navbar-height: 3.75rem;
        }
        [data-theme="light"] {
            --editor-bg: #f0f2f8;
            --editor-surface: #ffffff;
            --editor-toolbar: #e8eaf2;
            --editor-border: rgba(0,0,0,0.1);
            --editor-accent: #0369a1;
            --editor-text: #1a2035;
            --editor-muted: #5a6379;
        }
        *,*::before,*::after{margin:0;padding:0;box-sizing:border-box}
        html,body{height:100%;overflow:hidden;font-family:'Segoe UI',system-ui,sans-serif;background:var(--editor-bg);color:var(--editor-text)}

        /* Toolbar */
        #editor-toolbar{
            position:fixed;top:var(--navbar-height);left:0;right:0;height:48px;
            background:var(--editor-toolbar);border-bottom:1px solid var(--editor-border);
            display:flex;align-items:center;gap:4px;padding:0 10px;z-index:100;user-select:none;
        }
        #project-name-display{
            display:flex;align-items:center;gap:6px;cursor:pointer;padding:4px 8px;
            border-radius:6px;border:1px solid transparent;transition:border-color .2s;
            min-width:100px;max-width:220px;white-space:nowrap;overflow:hidden;
        }
        #project-name-display:hover{border-color:var(--editor-border)}
        #project-name-display span{color:var(--editor-accent);font-weight:600;font-size:.85rem;overflow:hidden;text-overflow:ellipsis}
        #project-name-display .fa-pencil{font-size:.65rem;color:var(--editor-muted)}
        #project-name-input{
            display:none;background:var(--editor-surface);border:1px solid var(--editor-accent);
            color:var(--editor-text);border-radius:5px;padding:4px 8px;font-size:.85rem;
            font-weight:600;min-width:100px;max-width:220px;outline:none;
        }
        .toolbar-sep{width:1px;height:28px;background:var(--editor-border);flex-shrink:0;margin:0 4px}
        .lang-tabs{display:flex;gap:2px}
        .lang-tab{
            padding:5px 13px;border-radius:5px;cursor:pointer;font-size:.78rem;font-weight:600;
            color:var(--editor-muted);border:1px solid transparent;transition:all .15s;background:transparent;
        }
        .lang-tab:hover{color:var(--editor-text);background:var(--editor-surface)}
        .lang-tab.active{color:var(--editor-accent);border-color:var(--editor-accent);background:color-mix(in srgb,var(--editor-accent) 10%,transparent)}
        .lang-dot{display:inline-block;width:7px;height:7px;border-radius:50%;margin-right:5px}
        .lang-tab[data-lang="html"] .lang-dot{background:#e44d26}
        .lang-tab[data-lang="css"]  .lang-dot{background:#264de4}
        .lang-tab[data-lang="js"]   .lang-dot{background:#f7df1e}
        .tb-spacer{flex:1}
        .tb-btn{
            display:flex;align-items:center;gap:5px;padding:5px 10px;border-radius:5px;
            border:1px solid var(--editor-border);background:transparent;color:var(--editor-text);
            font-size:.78rem;cursor:pointer;transition:all .15s;white-space:nowrap;
        }
        .tb-btn:hover{background:var(--editor-surface);border-color:var(--editor-accent);color:var(--editor-accent)}
        .tb-btn.primary{background:var(--editor-accent);color:#0a0a14;border-color:var(--editor-accent);font-weight:700}
        .tb-btn.primary:hover{opacity:.88}
        .tb-btn i{font-size:.8rem}
        .tb-dropdown{position:relative}
        .tb-dropdown-menu{
            display:none;position:absolute;top:calc(100% + 6px);right:0;
            background:var(--editor-surface);border:1px solid var(--editor-border);
            border-radius:8px;min-width:160px;z-index:300;overflow:hidden;
            box-shadow:0 8px 24px rgba(0,0,0,.4);
        }
        .tb-dropdown.open .tb-dropdown-menu{display:block}
        .tb-dropdown-item{
            display:flex;align-items:center;gap:8px;padding:9px 14px;cursor:pointer;
            font-size:.82rem;color:var(--editor-text);transition:background .15s;
        }
        .tb-dropdown-item:hover{background:color-mix(in srgb,var(--editor-accent) 12%,transparent);color:var(--editor-accent)}
        .tb-dropdown-item i{width:14px;text-align:center}
        .tb-icon-btn{
            width:32px;height:32px;border-radius:6px;border:1px solid var(--editor-border);
            background:transparent;color:var(--editor-muted);display:flex;align-items:center;
            justify-content:center;cursor:pointer;font-size:.85rem;transition:all .15s;
            text-decoration:none;
        }
        .tb-icon-btn:hover{background:var(--editor-surface);color:var(--editor-text);border-color:var(--editor-accent)}

        /* Editor body */
        #editor-body{
            position:fixed;top:calc(var(--navbar-height) + 48px);left:0;right:0;bottom:24px;
            display:flex;overflow:hidden;
        }
        #code-panel{flex:1 1 50%;min-width:250px;display:flex;flex-direction:column;overflow:hidden;position:relative}
        .cm-editor-wrap{display:none;flex:1;overflow:hidden;position:relative;flex-direction:column}
        .cm-editor-wrap.active{display:flex}
        .cm-editor-wrap .CodeMirror{flex:1;height:100%;font-size:13.5px;font-family:'Fira Code','Cascadia Code',Consolas,monospace;line-height:1.6}

        /* ── Resizer ─────────────────────────────────────────────────────── */
        #resizer{flex:0 0 5px;background:var(--editor-border);cursor:ew-resize;transition:background .2s;position:relative;z-index:10;touch-action:none}
        #resizer:hover,#resizer.dragging{background:var(--editor-accent)}
        #resizer::after{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:3px;height:36px;background:rgba(255,255,255,.18);border-radius:2px}
        /* Transparent overlay covers iframe during drag to prevent event capture */
        #iframe-drag-guard{display:none;position:absolute;inset:0;z-index:50;cursor:ew-resize}
        #iframe-drag-guard.active{display:block}

        #preview-panel{flex:1 1 50%;min-width:200px;display:flex;flex-direction:column;overflow:hidden}
        #preview-header{
            height:32px;background:var(--editor-toolbar);border-bottom:1px solid var(--editor-border);
            display:flex;align-items:center;padding:0 10px;gap:8px;flex-shrink:0;
        }
        #preview-header span{font-size:.72rem;color:var(--editor-muted);font-weight:600;text-transform:uppercase;letter-spacing:.06em}
        #preview-refresh-btn{margin-left:auto;background:none;border:none;color:var(--editor-muted);cursor:pointer;font-size:.8rem;padding:2px 5px;border-radius:4px;transition:color .15s}
        #preview-refresh-btn:hover{color:var(--editor-accent)}
        #preview-panel-inner{flex:1;display:flex;align-items:stretch;justify-content:center;background:var(--editor-bg);overflow:hidden;position:relative}
        #preview-frame-wrap{width:100%;height:100%;overflow:hidden;transition:width .3s}
        #preview-iframe{width:100%;height:100%;border:none;background:#fff;display:block}

        /* ── Status bar ────────────────────────────────────────────────────── */
        #status-bar{
            position:fixed;bottom:0;left:0;right:0;height:24px;
            background:var(--editor-toolbar);border-top:1px solid var(--editor-border);
            display:flex;align-items:center;padding:0 12px;gap:16px;
            font-size:.72rem;color:var(--editor-muted);z-index:100;
        }
        .sb-item{display:flex;align-items:center;gap:4px}
        .sb-sep{color:var(--editor-border)}
        #autosave-indicator{color:var(--editor-muted);transition:color .3s}
        #autosave-indicator.saving{color:#f7c948}
        #autosave-indicator.saved{color:#4ade80}

        /* ── Toast ─────────────────────────────────────────────────────────── */
        #save-toast{
            position:fixed;bottom:36px;right:24px;background:#1a3d2b;border:1px solid #4ade80;
            color:#4ade80;padding:10px 18px;border-radius:8px;font-size:.85rem;font-weight:600;
            display:none;align-items:center;gap:8px;z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,.5);
        }
        #save-toast.error{background:#3d1a1a;border-color:#f87171;color:#f87171}

        /* ── Modals ────────────────────────────────────────────────────────── */
        .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:1000;align-items:center;justify-content:center}
        .modal-overlay.open{display:flex}
        .modal-box{background:var(--editor-surface);border:1px solid var(--editor-border);border-radius:12px;padding:24px;max-width:760px;width:92%;max-height:82vh;overflow-y:auto;box-shadow:0 20px 60px rgba(0,0,0,.6)}
        .modal-title{font-size:1.05rem;font-weight:700;color:var(--editor-accent);margin-bottom:16px;display:flex;align-items:center;gap:8px}
        .modal-close{margin-left:auto;background:none;border:none;color:var(--editor-muted);font-size:1.1rem;cursor:pointer;padding:2px 6px;border-radius:4px;transition:color .15s}
        .modal-close:hover{color:var(--editor-text)}

        #template-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:12px}
        .template-card{background:var(--editor-toolbar);border:1px solid var(--editor-border);border-radius:8px;padding:14px;cursor:pointer;transition:all .2s}
        .template-card:hover{border-color:var(--editor-accent);background:color-mix(in srgb,var(--editor-accent) 8%,var(--editor-toolbar))}
        .template-card-icon{font-size:1.6rem;margin-bottom:8px}
        .template-card-name{font-size:.85rem;font-weight:600;color:var(--editor-text)}
        .template-card-desc{font-size:.75rem;color:var(--editor-muted);margin-top:4px}

        #validate-result{font-size:.83rem;margin-top:12px;padding:10px 14px;border-radius:6px;background:var(--editor-toolbar);border:1px solid var(--editor-border);color:var(--editor-muted);min-height:44px;white-space:pre-wrap}

        .CodeMirror-scroll{overflow:auto!important}
        [data-theme="dark"]  .CodeMirror{background:#1e1e2e!important}
        [data-theme="light"] .CodeMirror{background:#f8f9fc!important;color:#1a2035!important}
        [data-theme="light"] .CodeMirror-gutters{background:#eef0f8!important;border-right:1px solid #d5d9e9!important}

        /* ── Responsive ─────────────────────────────────────────────────────── */
        /* Mobile toggle button (only shows on narrow screens) */
        #mobile-preview-toggle{
            display:none;position:fixed;bottom:34px;right:14px;z-index:200;
            background:var(--editor-accent);color:#0a0a14;border:none;border-radius:50%;
            width:42px;height:42px;font-size:1rem;cursor:pointer;
            box-shadow:0 2px 12px rgba(0,240,255,.4);transition:opacity .2s;
        }
        #mobile-preview-toggle:hover{opacity:.85}

        @media(max-width:900px){
            /* Project name gets shorter */
            #project-name-display,#project-name-input{max-width:120px!important}
            /* Toolbar wraps if needed */
            #editor-toolbar{flex-wrap:wrap;height:auto;min-height:48px;padding:4px 8px}
        }
        @media(max-width:768px){
            /* Hide horizontal resizer */
            #resizer{display:none}
            /* Stack editor and preview vertically */
            #editor-body{flex-direction:column}
            #code-panel{
                flex-basis:auto!important;flex:1 1 50%;min-width:0!important;
                min-height:0;
            }
            #preview-panel{
                flex-basis:auto!important;flex:1 1 50%;min-width:0!important;
                border-top:2px solid var(--editor-accent);
            }
            /* Show mobile toggle FAB */
            #mobile-preview-toggle{display:flex;align-items:center;justify-content:center}
            /* Mobile: collapse preview by default, toggle shows it */
            #preview-panel.mobile-hidden{display:none}
            #code-panel.mobile-hidden{display:none}
            /* Toolbar: keep project name + lang tabs on first row, actions wrap */
            .tb-spacer{display:none}
            /* Reduce toolbar button text on mobile */
            .tb-btn .btn-label{display:none}
            #status-bar .sb-item:not(:first-child){display:none}
        }
        @media(max-width:480px){
            #editor-toolbar{gap:2px;padding:3px 6px}
            .lang-tab{padding:4px 8px;font-size:.72rem}
            .tb-btn{padding:4px 7px;font-size:.72rem}
        }
        /* ── Collaboration ────────────────────────────────────── */
        .collab-avatar{
            width:26px;height:26px;border-radius:50%;display:flex;align-items:center;
            justify-content:center;font-size:.65rem;font-weight:700;color:#0a0a14;
            border:2px solid var(--editor-bg);position:relative;cursor:default;flex-shrink:0;
        }
        .collab-avatar .av-tooltip{
            display:none;position:absolute;bottom:calc(100% + 6px);left:50%;transform:translateX(-50%);
            background:#1a1a2e;border:1px solid var(--editor-border);border-radius:5px;
            padding:3px 8px;font-size:.72rem;color:var(--editor-text);white-space:nowrap;pointer-events:none;
        }
        .collab-avatar:hover .av-tooltip{display:block}
        .collab-cursor{
            position:absolute;width:2px;pointer-events:none;z-index:200;
        }
        .collab-cursor-label{
            position:absolute;top:-16px;left:0;font-size:.62rem;font-weight:700;
            white-space:nowrap;padding:1px 5px;border-radius:3px;color:#0a0a14;
        }
        /* Version list item */
        .ver-item{
            display:flex;align-items:center;gap:10px;padding:9px 12px;
            background:var(--editor-toolbar);border:1px solid var(--editor-border);
            border-radius:6px;cursor:pointer;transition:border-color .15s;
        }
        .ver-item:hover{border-color:var(--editor-accent)}
        .ver-num{font-size:.72rem;color:var(--editor-muted);min-width:36px}
        .ver-label{flex:1;font-size:.82rem;color:var(--editor-text);font-weight:500}
        .ver-meta{font-size:.72rem;color:var(--editor-muted);text-align:right}
        .ver-restore-btn{
            background:none;border:1px solid var(--editor-border);border-radius:4px;
            color:var(--editor-muted);font-size:.72rem;padding:2px 8px;cursor:pointer;
            transition:all .15s;white-space:nowrap;
        }
        .ver-restore-btn:hover{border-color:var(--editor-accent);color:var(--editor-accent)}
        /* Sync dot states */
        #sync-dot.syncing{background:#f7c948;animation:sync-pulse 1s infinite}
        #sync-dot.error{background:#f87171}
        @keyframes sync-pulse{0%,100%{opacity:1}50%{opacity:.4}}
    </style>
</head>
<body>
<?php include BASE_PATH . '/views/layouts/navbar.php'; ?>

<div id="editor-toolbar">
    <div id="project-name-display" title="Click to rename">
        <i class="fa fa-pencil"></i>
        <span id="project-name-text"><?= htmlspecialchars($project['name'] ?? 'Untitled Project') ?></span>
    </div>
    <input type="text" id="project-name-input"
           value="<?= htmlspecialchars($project['name'] ?? 'Untitled Project') ?>"
           maxlength="120">

    <div class="toolbar-sep"></div>

    <div class="lang-tabs">
        <button class="lang-tab active" data-lang="html"><span class="lang-dot"></span>HTML</button>
        <button class="lang-tab" data-lang="css"><span class="lang-dot"></span>CSS</button>
        <button class="lang-tab" data-lang="js"><span class="lang-dot"></span>JS</button>
    </div>

    <div class="tb-spacer"></div>

    <button class="tb-btn primary" id="save-btn" title="Save (Ctrl+S)">
        <i class="fa fa-floppy-disk"></i><span class="btn-label">Save</span>
    </button>
    <button class="tb-btn" id="format-btn" title="Format (Alt+Shift+F)">
        <i class="fa fa-wand-magic-sparkles"></i><span class="btn-label">Format</span>
    </button>
    <button class="tb-btn" id="validate-btn" title="Validate code">
        <i class="fa fa-circle-check"></i><span class="btn-label">Validate</span>
    </button>
    <button class="tb-btn" id="export-btn" title="Export as HTML">
        <i class="fa fa-file-export"></i><span class="btn-label">Export</span>
    </button>

    <div class="tb-dropdown" id="templates-dropdown">
        <button class="tb-btn" id="templates-btn">
            <i class="fa fa-layer-group"></i><span class="btn-label">Templates</span>
            <i class="fa fa-chevron-down" style="font-size:.6rem;margin-left:2px"></i>
        </button>
        <div class="tb-dropdown-menu">
            <div class="tb-dropdown-item" id="open-templates-modal">
                <i class="fa fa-th-large"></i> Browse Templates
            </div>
        </div>
    </div>

    <div class="tb-dropdown" id="responsive-dropdown">
        <button class="tb-btn" id="responsive-btn">
            <i class="fa fa-mobile-screen-button"></i><span class="btn-label">Responsive</span>
            <i class="fa fa-chevron-down" style="font-size:.6rem;margin-left:2px"></i>
        </button>
        <div class="tb-dropdown-menu">
            <div class="tb-dropdown-item" data-width="375"><i class="fa fa-mobile-screen-button"></i> Mobile (375px)</div>
            <div class="tb-dropdown-item" data-width="768"><i class="fa fa-tablet-screen-button"></i> Tablet (768px)</div>
            <div class="tb-dropdown-item" data-width="1280"><i class="fa fa-desktop"></i> Desktop (1280px)</div>
            <div class="tb-dropdown-item" data-width="full"><i class="fa fa-expand"></i> Full Width</div>
        </div>
    </div>

    <div class="toolbar-sep"></div>

    <!-- Collaborators presence avatars (shown only when others are online) -->
    <div id="collab-bar" style="display:none;align-items:center;gap:4px">
        <div id="collab-avatars" style="display:flex;gap:3px"></div>
    </div>

    <?php if (!empty($project['id'])): ?>
    <!-- Invite collaborator button -->
    <button class="tb-icon-btn" id="invite-btn" title="Invite collaborator"><i class="fa fa-user-plus"></i></button>

    <!-- Version history button -->
    <button class="tb-icon-btn" id="history-btn" title="Version history"><i class="fa fa-clock-rotate-left"></i></button>

    <!-- Sync status dot -->
    <span id="sync-dot" title="Cloud sync" style="width:8px;height:8px;border-radius:50%;background:#4ade80;flex-shrink:0;transition:background .3s"></span>
    <?php endif; ?>

    <div class="toolbar-sep"></div>
    <a href="/projects/codexpro" class="tb-icon-btn" title="Back to dashboard">
        <i class="fa fa-arrow-left"></i>
    </a>
</div>

<div id="editor-body">
    <div id="code-panel">
        <div class="cm-editor-wrap active" id="wrap-html"><textarea id="cm-html"></textarea></div>
        <div class="cm-editor-wrap" id="wrap-css"><textarea id="cm-css"></textarea></div>
        <div class="cm-editor-wrap" id="wrap-js"><textarea id="cm-js"></textarea></div>
    </div>
    <div id="resizer"></div>
    <div id="preview-panel">
        <div id="preview-header">
            <i class="fa fa-eye" style="font-size:.75rem;color:var(--editor-muted)"></i>
            <span>Preview</span>
            <button id="preview-refresh-btn" title="Refresh"><i class="fa fa-rotate-right"></i></button>
        </div>
        <div id="preview-panel-inner">
            <div id="preview-frame-wrap">
                <iframe id="preview-iframe" sandbox="allow-scripts allow-same-origin allow-forms" title="Live Preview"></iframe>
                <div id="iframe-drag-guard"></div>
            </div>
        </div>
    </div>
</div>

<!-- Mobile toggle: editor ↔ preview -->
<button id="mobile-preview-toggle" title="Toggle Editor / Preview" aria-label="Toggle Editor/Preview">
    <i class="fa fa-eye" id="mobile-toggle-icon"></i>
</button>

<div id="status-bar">
    <span class="sb-item"><i class="fa fa-map-pin" style="font-size:.65rem"></i><span id="sb-cursor">Ln 1, Col 1</span></span>
    <span class="sb-sep">|</span>
    <span class="sb-item" id="sb-chars">0 chars</span>
    <span class="sb-sep">|</span>
    <span class="sb-item" id="sb-lang">HTML</span>
    <span class="tb-spacer"></span>
    <span id="autosave-indicator"><i class="fa fa-cloud"></i> <span id="autosave-text">Auto-save ready</span></span>
</div>

<div id="save-toast"><i class="fa fa-circle-check"></i><span id="toast-msg">Saved!</span></div>

<div class="modal-overlay" id="validate-modal">
    <div class="modal-box" style="max-width:520px">
        <div class="modal-title">
            <i class="fa fa-circle-check"></i> Validation Results
            <button class="modal-close" data-close="validate-modal"><i class="fa fa-times"></i></button>
        </div>
        <div id="validate-result">Click Validate to check your code...</div>
    </div>
</div>

<div class="modal-overlay" id="templates-modal">
    <div class="modal-box">
        <div class="modal-title">
            <i class="fa fa-layer-group"></i> Starter Templates
            <button class="modal-close" data-close="templates-modal"><i class="fa fa-times"></i></button>
        </div>
        <div id="template-grid"><div style="color:var(--editor-muted);font-size:.85rem">Loading templates...</div></div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/xml/xml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/css/css.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/mode/htmlmixed/htmlmixed.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/addon/edit/closebrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/addon/edit/matchbrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/addon/edit/closetag.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/addon/fold/foldcode.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/addon/fold/foldgutter.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/addon/fold/brace-fold.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/addon/fold/xml-fold.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/addon/selection/active-line.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/addon/hint/show-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/addon/hint/javascript-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/addon/hint/css-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/addon/hint/html-hint.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.18/addon/comment/comment.min.js"></script>

<script>
(function () {
    'use strict';

    const PROJECT_ID   = <?= json_encode(isset($project['id']) ? (int)$project['id'] : null) ?>;
    const CSRF_TOKEN   = document.querySelector('meta[name="csrf-token"]').content;
    const READONLY     = <?= json_encode(!empty($project['_readonly'])) ?>;
    const COLLAB_TOKEN = <?= json_encode($project['collab_token'] ?? null) ?>;
    const MY_USER_ID   = <?= json_encode((int)($_SESSION['user_id'] ?? 0)) ?>;
    let   SERVER_VER   = <?= json_encode(isset($project['version']) ? (int)$project['version'] : 0) ?>;

    function cmTheme() {
        return document.documentElement.dataset.theme === 'light' ? 'default' : 'dracula';
    }

    function cmOptions(mode) {
        return {
            mode: mode,
            theme: cmTheme(),
            lineNumbers: true,
            autoCloseBrackets: true,
            matchBrackets: true,
            autoCloseTags: true,
            styleActiveLine: true,
            foldGutter: true,
            gutters: ['CodeMirror-linenumbers', 'CodeMirror-foldgutter'],
            indentUnit: 2,
            tabSize: 2,
            indentWithTabs: false,
            lineWrapping: false,
            hintOptions: { completeSingle: false, closeOnUnfocus: true },
            extraKeys: {
                'Ctrl-/':      function(cm) { cm.execCommand('toggleComment'); },
                'Alt-Shift-F': function()   { doFormat(); },
                'Ctrl-S':      function()   { doSave(); },
                'Ctrl-Space':  'autocomplete',
                'Tab':         function(cm) {
                    if (cm.somethingSelected()) {
                        cm.indentSelection('add');
                    } else {
                        cm.replaceSelection('  ', 'end');
                    }
                }
            }
        };
    }

    var editors = {
        html: CodeMirror.fromTextArea(document.getElementById('cm-html'), cmOptions('htmlmixed')),
        css:  CodeMirror.fromTextArea(document.getElementById('cm-css'),  cmOptions('css')),
        js:   CodeMirror.fromTextArea(document.getElementById('cm-js'),   cmOptions('javascript'))
    };

    editors.html.setValue(<?= json_encode($project['html_content'] ?? "<!-- Start coding here -->\n") ?>);
    editors.css.setValue(<?= json_encode($project['css_content']   ?? "/* Your CSS here */\n") ?>);
    editors.js.setValue(<?= json_encode($project['js_content']     ?? "// Your JavaScript here\n") ?>);

    Object.values(editors).forEach(function(cm) {
        cm.getWrapperElement().style.height = '100%';
        cm.refresh();
    });

    var activeLang = 'html';
    var langLabels = { html: 'HTML', css: 'CSS', js: 'JavaScript' };

    function switchLang(lang) {
        activeLang = lang;
        document.querySelectorAll('.lang-tab').forEach(function(t) {
            t.classList.toggle('active', t.dataset.lang === lang);
        });
        document.querySelectorAll('.cm-editor-wrap').forEach(function(w) {
            w.classList.toggle('active', w.id === 'wrap-' + lang);
        });
        editors[lang].refresh();
        editors[lang].focus();
        updateStatusBar();
    }

    document.querySelectorAll('.lang-tab').forEach(function(tab) {
        tab.addEventListener('click', function() { switchLang(tab.dataset.lang); });
    });

    function updateStatusBar() {
        var cm = editors[activeLang];
        var cur = cm.getCursor();
        document.getElementById('sb-cursor').textContent = 'Ln ' + (cur.line + 1) + ', Col ' + (cur.ch + 1);
        document.getElementById('sb-chars').textContent  = cm.getValue().length + ' chars';
        document.getElementById('sb-lang').textContent   = langLabels[activeLang] || activeLang.toUpperCase();
    }

    Object.keys(editors).forEach(function(lang) {
        editors[lang].on('cursorActivity', function() { if (lang === activeLang) updateStatusBar(); });
        editors[lang].on('change', function(cm, change) {
            if (lang === activeLang) updateStatusBar();
            schedulePreviewRefresh();
            // Trigger autocomplete on regular character input (not undo/redo/paste)
            if (change.origin === '+input' || change.origin === '+delete') {
                var text = change.text[0] || '';
                // Fire hints for word chars, CSS properties, HTML tag chars
                if (/[\w.\-#@:<"'(]/.test(text)) {
                    clearTimeout(cm._hintTimer);
                    cm._hintTimer = setTimeout(function() {
                        if (!cm.state.completionActive) {
                            cm.showHint({ completeSingle: false, closeOnUnfocus: true });
                        }
                    }, 150);
                }
            }
        });
    });
    updateStatusBar();

    /* Preview */
    var previewIframe = document.getElementById('preview-iframe');
    var previewTimer  = null;

    function buildPreviewDoc() {
        return '<!DOCTYPE html><html><head><meta charset="UTF-8">' +
            '<meta name="viewport" content="width=device-width,initial-scale=1">' +
            '<style>' + editors.css.getValue() + '</style>' +
            '</head><body>' + editors.html.getValue() +
            '<script>' + editors.js.getValue() + '<\/script></body></html>';
    }

    function refreshPreview() {
        previewIframe.srcdoc = buildPreviewDoc();
    }

    function schedulePreviewRefresh() {
        clearTimeout(previewTimer);
        previewTimer = setTimeout(refreshPreview, 600);
    }

    document.getElementById('preview-refresh-btn').addEventListener('click', refreshPreview);
    refreshPreview();

    /* Resizer — RAF-throttled, iframe drag-guard prevents sticky behaviour */
    var editorBody   = document.getElementById('editor-body');
    var codePanel    = document.getElementById('code-panel');
    var previewPanel = document.getElementById('preview-panel');
    var resizer      = document.getElementById('resizer');
    var dragGuard    = document.getElementById('iframe-drag-guard');
    var isResizing   = false;
    var rafPending   = false;
    var pendingX     = 0;

    resizer.addEventListener('mousedown', function(e) {
        if (window.innerWidth <= 768) return;
        isResizing = true;
        rafPending = false;
        resizer.classList.add('dragging');
        dragGuard.classList.add('active');        // Block iframe mouse capture
        document.body.style.cursor     = 'ew-resize';
        document.body.style.userSelect = 'none';
        e.preventDefault();
    });

    document.addEventListener('mousemove', function(e) {
        if (!isResizing) return;
        pendingX = e.clientX;
        if (!rafPending) {
            rafPending = true;
            requestAnimationFrame(function() {
                rafPending = false;
                var rect   = editorBody.getBoundingClientRect();
                var totalW = rect.width - 5;
                var leftPx = Math.round(pendingX - rect.left);
                var minPx  = 250;
                var maxPx  = Math.round(totalW * 0.80);
                leftPx = Math.max(minPx, Math.min(maxPx, leftPx));
                codePanel.style.flexBasis    = leftPx + 'px';
                codePanel.style.flexGrow     = '0';
                previewPanel.style.flexBasis = (totalW - leftPx) + 'px';
                previewPanel.style.flexGrow  = '0';
            });
        }
    });

    document.addEventListener('mouseup', function() {
        if (!isResizing) return;
        isResizing = false;
        resizer.classList.remove('dragging');
        dragGuard.classList.remove('active');     // Re-enable iframe
        document.body.style.cursor     = '';
        document.body.style.userSelect = '';
        Object.values(editors).forEach(function(cm) { cm.refresh(); });
    });

    /* Touch-based resize for tablets */
    resizer.addEventListener('touchstart', function(e) {
        if (window.innerWidth <= 768) return;
        isResizing = true;
        rafPending = false;
        resizer.classList.add('dragging');
        dragGuard.classList.add('active');
        e.preventDefault();
    }, { passive: false });

    document.addEventListener('touchmove', function(e) {
        if (!isResizing) return;
        pendingX = e.touches[0].clientX;
        if (!rafPending) {
            rafPending = true;
            requestAnimationFrame(function() {
                rafPending = false;
                var rect   = editorBody.getBoundingClientRect();
                var totalW = rect.width - 5;
                var leftPx = Math.round(pendingX - rect.left);
                var minPx  = 250;
                var maxPx  = Math.round(totalW * 0.80);
                leftPx = Math.max(minPx, Math.min(maxPx, leftPx));
                codePanel.style.flexBasis    = leftPx + 'px';
                codePanel.style.flexGrow     = '0';
                previewPanel.style.flexBasis = (totalW - leftPx) + 'px';
                previewPanel.style.flexGrow  = '0';
            });
        }
    }, { passive: true });

    document.addEventListener('touchend', function() {
        if (!isResizing) return;
        isResizing = false;
        resizer.classList.remove('dragging');
        dragGuard.classList.remove('active');
        document.body.style.cursor     = '';
        document.body.style.userSelect = '';
        Object.values(editors).forEach(function(cm) { cm.refresh(); });
    });

    /* Responsive preview */
    var previewFrameWrap = document.getElementById('preview-frame-wrap');
    document.querySelectorAll('#responsive-dropdown .tb-dropdown-item[data-width]').forEach(function(item) {
        item.addEventListener('click', function() {
            var w = item.dataset.width;
            previewFrameWrap.style.width = (w === 'full') ? '100%' : w + 'px';
            closeAllDropdowns();
        });
    });

    /* Mobile toggle: editor ↔ preview */
    var mobileToggleBtn  = document.getElementById('mobile-preview-toggle');
    var mobileToggleIcon = document.getElementById('mobile-toggle-icon');
    var showingPreview   = false;

    function applyMobileLayout() {
        if (window.innerWidth > 768) {
            // Restore both panels on desktop
            codePanel.classList.remove('mobile-hidden');
            previewPanel.classList.remove('mobile-hidden');
            return;
        }
        if (showingPreview) {
            codePanel.classList.add('mobile-hidden');
            previewPanel.classList.remove('mobile-hidden');
            mobileToggleIcon.className = 'fa fa-code';
            mobileToggleBtn.title = 'Show Editor';
        } else {
            codePanel.classList.remove('mobile-hidden');
            previewPanel.classList.add('mobile-hidden');
            mobileToggleIcon.className = 'fa fa-eye';
            mobileToggleBtn.title = 'Show Preview';
        }
        Object.values(editors).forEach(function(cm) { cm.refresh(); });
    }

    mobileToggleBtn.addEventListener('click', function() {
        showingPreview = !showingPreview;
        applyMobileLayout();
    });

    window.addEventListener('resize', applyMobileLayout);
    applyMobileLayout();

    /* Dropdowns */
    function closeAllDropdowns() {
        document.querySelectorAll('.tb-dropdown.open').forEach(function(d) { d.classList.remove('open'); });
    }

    document.querySelectorAll('.tb-dropdown > .tb-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var parent  = btn.closest('.tb-dropdown');
            var wasOpen = parent.classList.contains('open');
            closeAllDropdowns();
            if (!wasOpen) parent.classList.add('open');
        });
    });

    document.addEventListener('click', closeAllDropdowns);

    /* Project name editing */
    var nameDisplay = document.getElementById('project-name-display');
    var nameText    = document.getElementById('project-name-text');
    var nameInput   = document.getElementById('project-name-input');

    nameDisplay.addEventListener('click', function() {
        nameDisplay.style.display = 'none';
        nameInput.style.display   = 'block';
        nameInput.value           = nameText.textContent;
        nameInput.focus();
        nameInput.select();
    });

    function commitName() {
        var val = nameInput.value.trim() || 'Untitled Project';
        nameText.textContent      = val;
        nameInput.style.display   = 'none';
        nameDisplay.style.display = 'flex';
    }

    nameInput.addEventListener('blur', commitName);
    nameInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter')  { e.preventDefault(); commitName(); }
        if (e.key === 'Escape') { nameInput.value = nameText.textContent; commitName(); }
    });

    /* Helpers */
    function csrfHeaders() {
        return { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN };
    }

    function showToast(msg, isError) {
        var t = document.getElementById('save-toast');
        document.getElementById('toast-msg').textContent = msg;
        t.className = isError ? 'error' : '';
        t.style.display = 'flex';
        setTimeout(function() { t.style.display = 'none'; }, 3000);
    }

    /* Save */
    function doSave() {
        if (READONLY) { showToast('This project is read-only', true); return; }
        var fd = new FormData();
        if (PROJECT_ID) fd.append('project_id', PROJECT_ID);
        fd.append('name',         nameText.textContent.trim());
        fd.append('html_content', editors.html.getValue());
        fd.append('css_content',  editors.css.getValue());
        fd.append('js_content',   editors.js.getValue());
        fd.append('visibility',   'private');
        fd.append('version', SERVER_VER);
        fd.append('_csrf_token',  CSRF_TOKEN);

        fetch('/projects/codexpro/editor/save', {
            method: 'POST',
            headers: { 'X-CSRF-Token': CSRF_TOKEN },
            body: fd
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                SERVER_VER = data.version || SERVER_VER;
                showToast(data.conflict ? '⚠ Saved (conflict detected – check history)' : 'Saved ✓');
                if (data.project_id && !PROJECT_ID) {
                    window.location.href = '/projects/codexpro/editor/' + data.project_id;
                }
            } else {
                showToast(data.error || data.message || 'Save failed', true);
            }
        })
        .catch(function() { showToast('Network error during save', true); });
    }

    document.getElementById('save-btn').addEventListener('click', doSave);

    /* Auto-save */
    if (PROJECT_ID) {
        var autoSaveTimer   = null;
        var autoIndicator   = document.getElementById('autosave-indicator');
        var autoText        = document.getElementById('autosave-text');

        function scheduleAutoSave() {
            clearTimeout(autoSaveTimer);
            autoSaveTimer = setTimeout(function() {
                autoIndicator.className = 'saving';
                autoText.textContent    = 'Saving\u2026';

                var fd = new FormData();
                fd.append('project_id',   PROJECT_ID);
                fd.append('html_content', editors.html.getValue());
                fd.append('css_content',  editors.css.getValue());
                fd.append('js_content',   editors.js.getValue());
                fd.append('_token',       CSRF_TOKEN);

                fetch('/projects/codexpro/editor/autosave', {
                    method: 'POST',
                    headers: { 'X-CSRF-Token': CSRF_TOKEN },
                    body: fd
                })
                .then(function(r) { return r.json(); })
                .then(function() {
                    autoIndicator.className = 'saved';
                    autoText.textContent    = 'Auto-saved';
                    setTimeout(function() {
                        autoIndicator.className = '';
                        autoText.textContent    = 'Auto-save ready';
                    }, 2500);
                })
                .catch(function() {
                    autoIndicator.className = '';
                    autoText.textContent    = 'Auto-save failed';
                });
            }, 3000);
        }

        Object.values(editors).forEach(function(cm) { cm.on('change', scheduleAutoSave); });
    }

    /* Format */
    function doFormat() {
        var cm   = editors[activeLang];
        var code = cm.getValue();

        fetch('/projects/codexpro/api/format', {
            method: 'POST',
            headers: csrfHeaders(),
            body: JSON.stringify({ code: code, language: activeLang })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.formatted) {
                var cursor = cm.getCursor();
                cm.setValue(data.formatted);
                cm.setCursor(cursor);
                showToast('Code formatted!');
            } else {
                showToast(data.message || 'Format failed', true);
            }
        })
        .catch(function() { showToast('Network error during format', true); });
    }

    document.getElementById('format-btn').addEventListener('click', doFormat);

    /* Validate */
    document.getElementById('validate-btn').addEventListener('click', function() {
        openModal('validate-modal');
        document.getElementById('validate-result').textContent = 'Validating\u2026';

        fetch('/projects/codexpro/api/validate', {
            method: 'POST',
            headers: csrfHeaders(),
            body: JSON.stringify({
                html: editors.html.getValue(),
                css:  editors.css.getValue(),
                js:   editors.js.getValue()
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var el = document.getElementById('validate-result');
            el.textContent = data.result || data.message || JSON.stringify(data, null, 2);
            el.style.color = data.errors ? '#f87171' : '#4ade80';
        })
        .catch(function() {
            document.getElementById('validate-result').textContent = 'Validation request failed.';
        });
    });

    /* Export */
    document.getElementById('export-btn').addEventListener('click', function() {
        var doc  = buildPreviewDoc();
        var blob = new Blob([doc], { type: 'text/html' });
        var url  = URL.createObjectURL(blob);
        var a    = document.createElement('a');
        a.href     = url;
        a.download = (nameText.textContent.trim() || 'project') + '.html';
        a.click();
        URL.revokeObjectURL(url);
    });

    /* Templates */
    document.getElementById('open-templates-modal').addEventListener('click', function() {
        closeAllDropdowns();
        openModal('templates-modal');
        loadTemplates();
    });

    var templatesLoaded = false;

    function loadTemplates() {
        if (templatesLoaded) return;
        var grid = document.getElementById('template-grid');
        grid.innerHTML = '<div style="color:var(--editor-muted);font-size:.85rem">Loading...</div>';

        fetch('/projects/codexpro/api/starter-templates', {
            headers: { 'X-CSRF-Token': CSRF_TOKEN }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var templates = Array.isArray(data) ? data : (data.templates || []);
            if (!templates.length) {
                grid.innerHTML = '<div style="color:var(--editor-muted)">No templates available.</div>';
                return;
            }
            grid.innerHTML = '';
            templates.forEach(function(tpl) {
                var card = document.createElement('div');
                card.className = 'template-card';
                card.innerHTML =
                    '<div class="template-card-icon">' + (tpl.icon || '\uD83D\uDCC4') + '</div>' +
                    '<div class="template-card-name">' + escHtml(tpl.name || 'Template') + '</div>' +
                    '<div class="template-card-desc">' + escHtml(tpl.description || '') + '</div>';
                card.addEventListener('click', function() { applyTemplate(tpl); });
                grid.appendChild(card);
            });
            templatesLoaded = true;
        })
        .catch(function() {
            grid.innerHTML = '<div style="color:#f87171">Failed to load templates.</div>';
        });
    }

    function applyTemplate(tpl) {
        if (!confirm('Apply template "' + (tpl.name || 'Template') + '"? This will replace current code.')) return;
        closeModal('templates-modal');

        fetch('/projects/codexpro/api/create-from-template', {
            method: 'POST',
            headers: csrfHeaders(),
            body: JSON.stringify({ template_id: tpl.id || tpl.slug })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.html !== undefined) editors.html.setValue(data.html || '');
            if (data.css  !== undefined) editors.css.setValue(data.css   || '');
            if (data.js   !== undefined) editors.js.setValue(data.js     || '');
            refreshPreview();
            showToast('Template applied!');
        })
        .catch(function() { showToast('Failed to apply template', true); });
    }

    /* Modal helpers */
    function openModal(id)  { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }

    document.querySelectorAll('.modal-close[data-close]').forEach(function(btn) {
        btn.addEventListener('click', function() { closeModal(btn.dataset.close); });
    });
    document.querySelectorAll('.modal-overlay').forEach(function(overlay) {
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) closeModal(overlay.id);
        });
    });

    /* Global keyboard shortcuts */
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); doSave(); }
        if (e.altKey && e.shiftKey && e.key === 'F')    { e.preventDefault(); doFormat(); }
        if (e.key === 'Escape') closeAllDropdowns();
    });

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /* Observe theme changes to swap CodeMirror theme */
    var themeObserver = new MutationObserver(function() {
        var t = cmTheme();
        Object.values(editors).forEach(function(cm) { cm.setOption('theme', t); });
    });
    themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['data-theme'] });

    /* Initial layout refresh */
    requestAnimationFrame(function() {
        Object.values(editors).forEach(function(cm) { cm.refresh(); });
        updateStatusBar();
    });
}());

/* ═══════════════════════════════════════════════════════════
   Enterprise Collaboration & Version History Module
   ═══════════════════════════════════════════════════════════ */
(function () {
    'use strict';

    if (!PROJECT_ID) return; // No collab on new unsaved projects

    var syncDot       = document.getElementById('sync-dot');
    var collabBar     = document.getElementById('collab-bar');
    var collabAvatars = document.getElementById('collab-avatars');
    var eventSource   = null;
    var lastSeq       = 0;
    var applyingRemote = false;

    /* ── SSE connection ─────────────────────────────────────── */
    function connectSSE() {
        if (eventSource) { eventSource.close(); }
        var url = '/projects/codexpro/collab/' + PROJECT_ID + '/stream?since=' + lastSeq
                + (COLLAB_TOKEN ? '&token=' + encodeURIComponent(COLLAB_TOKEN) : '');
        eventSource = new EventSource(url);

        eventSource.addEventListener('change', function (e) {
            var d = JSON.parse(e.data);
            lastSeq = Math.max(lastSeq, d._seq || 0);
            applyRemoteChange(d);
        });

        eventSource.addEventListener('presence', function (e) {
            var d = JSON.parse(e.data);
            renderPresence(d.collaborators || []);
        });

        eventSource.addEventListener('disconnect', function () {
            renderPresence([]);
        });

        eventSource.addEventListener('ping', function (e) {
            var d = JSON.parse(e.data);
            lastSeq = Math.max(lastSeq, d.since || lastSeq);
            setSyncDot('ok');
        });

        eventSource.onerror = function () {
            setSyncDot('error');
            eventSource.close();
            setTimeout(connectSSE, 5000); // reconnect after 5s
        };
    }

    /* ── Apply remote change ───────────────────────────────── */
    function applyRemoteChange(d) {
        if (!d._type || !editors) return;
        applyingRemote = true;

        try {
            if (d._type === 'html' && d.content !== undefined) {
                editors.html.setValue(d.content);
            } else if (d._type === 'css' && d.content !== undefined) {
                editors.css.setValue(d.content);
            } else if (d._type === 'js' && d.content !== undefined) {
                editors.js.setValue(d.content);
            } else if (d._type === 'meta' && d.name) {
                document.getElementById('project-name-text').textContent = d.name;
            }
        } finally {
            applyingRemote = false;
        }
    }

    /* ── Push local change ─────────────────────────────────── */
    var pushTimers = { html: null, css: null, js: null };

    function schedulePush(type, cm) {
        if (applyingRemote || READONLY) return;
        clearTimeout(pushTimers[type]);
        pushTimers[type] = setTimeout(function () {
            setSyncDot('syncing');
            fetch('/projects/codexpro/collab/' + PROJECT_ID + '/push'
                  + (COLLAB_TOKEN ? '?token=' + encodeURIComponent(COLLAB_TOKEN) : ''), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN
                },
                body: JSON.stringify({ type: type, payload: { content: cm.getValue() } })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.seq) lastSeq = Math.max(lastSeq, data.seq);
                setSyncDot('ok');
            })
            .catch(function() { setSyncDot('error'); });
        }, 400);
    }

    // Hook into existing editors
    if (typeof editors !== 'undefined') {
        editors.html.on('change', function(cm, ch) {
            if (ch.origin === '+input' || ch.origin === 'paste' || ch.origin === '+delete') {
                schedulePush('html', cm);
            }
        });
        editors.css.on('change', function(cm, ch) {
            if (ch.origin === '+input' || ch.origin === 'paste' || ch.origin === '+delete') {
                schedulePush('css', cm);
            }
        });
        editors.js.on('change', function(cm, ch) {
            if (ch.origin === '+input' || ch.origin === 'paste' || ch.origin === '+delete') {
                schedulePush('js', cm);
            }
        });

        // Push cursor position on cursor move
        var cursorDebounce = null;
        Object.keys(editors).forEach(function(lang) {
            editors[lang].on('cursorActivity', function(cm) {
                if (applyingRemote) return;
                clearTimeout(cursorDebounce);
                cursorDebounce = setTimeout(function() {
                    var cur = cm.getCursor();
                    fetch('/projects/codexpro/collab/' + PROJECT_ID + '/push'
                          + (COLLAB_TOKEN ? '?token=' + encodeURIComponent(COLLAB_TOKEN) : ''), {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                        body: JSON.stringify({ type: 'cursor', payload: { line: cur.line, ch: cur.ch, tab: lang } })
                    }).catch(function(){});
                }, 300);
            });
        });
    }

    /* ── Presence rendering ────────────────────────────────── */
    function renderPresence(collaborators) {
        collabAvatars.innerHTML = '';
        if (!collaborators.length) {
            collabBar.style.display = 'none';
            return;
        }
        collabBar.style.display = 'flex';
        collaborators.forEach(function(c) {
            var initials = (c.name || '?').split(' ').map(function(w){ return w[0]; }).join('').slice(0,2).toUpperCase();
            var av = document.createElement('div');
            av.className = 'collab-avatar';
            av.style.background = c.color || '#00f0ff';
            av.title = c.name;
            av.innerHTML = initials + '<span class="av-tooltip">' + escHtml(c.name) + ' · ' + (c.active_tab || 'html').toUpperCase() + '</span>';
            collabAvatars.appendChild(av);
        });
    }

    /* ── Sync dot state ────────────────────────────────────── */
    function setSyncDot(state) {
        syncDot.className = '';
        if (state === 'syncing') { syncDot.classList.add('syncing'); syncDot.title = 'Syncing…'; }
        else if (state === 'error') { syncDot.classList.add('error'); syncDot.title = 'Sync error'; }
        else { syncDot.title = 'Synced'; }
    }

    /* ── Invite modal ──────────────────────────────────────── */
    var inviteBtn   = document.getElementById('invite-btn');
    var historyBtn  = document.getElementById('history-btn');
    var doInviteBtn = document.getElementById('do-invite-btn');

    if (inviteBtn) {
        inviteBtn.addEventListener('click', function() {
            openModal('invite-modal');
            loadMembers();
            // Show share link if token exists
            if (COLLAB_TOKEN) {
                var row   = document.getElementById('invite-link-row');
                var input = document.getElementById('invite-link-input');
                row.style.display = 'block';
                input.value = window.location.origin + '/projects/codexpro/editor/' + PROJECT_ID + '?token=' + COLLAB_TOKEN;
            }
        });
    }

    var copyLinkBtn = document.getElementById('copy-link-btn');
    if (copyLinkBtn) {
        copyLinkBtn.addEventListener('click', function() {
            var input = document.getElementById('invite-link-input');
            navigator.clipboard.writeText(input.value).then(function() {
                copyLinkBtn.innerHTML = '<i class="fa fa-check"></i>';
                setTimeout(function() { copyLinkBtn.innerHTML = '<i class="fa fa-copy"></i>'; }, 1500);
            });
        });
    }

    if (doInviteBtn) {
        doInviteBtn.addEventListener('click', function() {
            var email   = (document.getElementById('invite-email').value || '').trim();
            var canEdit = document.getElementById('invite-can-edit').checked;
            if (!email) return;

            doInviteBtn.disabled = true;
            doInviteBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending…';

            fetch('/projects/codexpro/collab/' + PROJECT_ID + '/invite', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                body: JSON.stringify({ email: email, can_edit: canEdit })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    document.getElementById('invite-email').value = '';
                    if (data.collab_link) {
                        var row   = document.getElementById('invite-link-row');
                        var input = document.getElementById('invite-link-input');
                        row.style.display  = 'block';
                        input.value        = window.location.origin + data.collab_link;
                        // Update COLLAB_TOKEN (can't reassign const, use meta)
                    }
                    showToast('Invite sent to ' + (data.invitee ? data.invitee.name : email));
                    loadMembers();
                } else {
                    showToast(data.error || 'Invite failed', true);
                }
            })
            .catch(function() { showToast('Network error', true); })
            .finally(function() {
                doInviteBtn.disabled = false;
                doInviteBtn.innerHTML = '<i class="fa fa-paper-plane"></i> Send Invite';
            });
        });
    }

    function loadMembers() {
        fetch('/projects/codexpro/collab/' + PROJECT_ID + '/members', {
            headers: { 'X-CSRF-Token': CSRF_TOKEN }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.members || !data.members.length) return;
            var list = document.getElementById('members-list');
            var rows = document.getElementById('members-rows');
            list.style.display = 'block';
            rows.innerHTML = '';
            data.members.forEach(function(m) {
                var row = document.createElement('div');
                row.style.cssText = 'display:flex;align-items:center;gap:10px;padding:7px 0;border-bottom:1px solid var(--editor-border)';
                row.innerHTML = '<span style="flex:1;font-size:.83rem;color:var(--editor-text)">' + escHtml(m.name) + ' <span style="color:var(--editor-muted);font-size:.75rem">' + escHtml(m.email) + '</span></span>'
                    + '<span style="font-size:.72rem;padding:2px 7px;border-radius:4px;background:' + (m.can_edit ? 'rgba(0,240,255,.1)' : 'rgba(136,146,166,.1)') + ';color:' + (m.can_edit ? 'var(--editor-accent)' : 'var(--editor-muted)') + '">' + (m.can_edit ? 'Editor' : 'Viewer') + '</span>'
                    + '<span style="width:8px;height:8px;border-radius:50%;background:' + (m.online ? '#4ade80' : 'var(--editor-muted)') + ';flex-shrink:0" title="' + (m.online ? 'Online' : 'Offline') + '"></span>'
                    + '<button data-uid="' + m.user_id + '" class="revoke-btn" style="background:none;border:none;color:#f87171;cursor:pointer;font-size:.78rem;padding:2px 5px" title="Remove"><i class="fa fa-xmark"></i></button>';
                rows.appendChild(row);
            });

            rows.querySelectorAll('.revoke-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var uid = parseInt(btn.dataset.uid, 10);
                    if (!confirm('Remove this collaborator?')) return;
                    fetch('/projects/codexpro/collab/' + PROJECT_ID + '/revoke', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                        body: JSON.stringify({ user_id: uid })
                    })
                    .then(function(r) { return r.json(); })
                    .then(function() { loadMembers(); })
                    .catch(function() {});
                });
            });
        })
        .catch(function(){});
    }

    /* ── Version history ───────────────────────────────────── */
    if (historyBtn) {
        historyBtn.addEventListener('click', function() {
            openModal('history-modal');
            loadVersions();
        });
    }

    function loadVersions() {
        var list = document.getElementById('version-list');
        list.innerHTML = '<div style="color:var(--editor-muted);font-size:.83rem;padding:12px">Loading…</div>';

        fetch('/projects/codexpro/versions/' + PROJECT_ID, {
            headers: { 'X-CSRF-Token': CSRF_TOKEN }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            list.innerHTML = '';
            if (!data.versions || !data.versions.length) {
                list.innerHTML = '<div style="color:var(--editor-muted);font-size:.83rem;padding:12px">No versions yet. Versions are created automatically when you save.</div>';
                return;
            }
            data.versions.forEach(function(v) {
                var item = document.createElement('div');
                item.className = 'ver-item';
                var dt = new Date(v.created_at.replace(' ','T'));
                var dtStr = dt.toLocaleDateString() + ' ' + dt.toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});
                item.innerHTML =
                    '<span class="ver-num">v' + v.version_num + '</span>' +
                    '<span class="ver-label">' + escHtml(v.label || ('Saved by ' + (v.author || 'you'))) + '</span>' +
                    '<span class="ver-meta">' + dtStr + '</span>' +
                    (READONLY ? '' : '<button class="ver-restore-btn" data-vid="' + v.id + '">Restore</button>');
                list.appendChild(item);
            });

            list.querySelectorAll('.ver-restore-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var vid = parseInt(btn.dataset.vid, 10);
                    if (!confirm('Restore this version? Current code will be saved as a new version first.')) return;

                    fetch('/projects/codexpro/versions/' + PROJECT_ID + '/get?v=' + vid, {
                        headers: { 'X-CSRF-Token': CSRF_TOKEN }
                    })
                    .then(function(r) { return r.json(); })
                    .then(function(d) {
                        if (d.version) {
                            fetch('/projects/codexpro/versions/' + PROJECT_ID + '/restore', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': CSRF_TOKEN },
                                body: JSON.stringify({ version_id: vid })
                            })
                            .then(function(r) { return r.json(); })
                            .then(function(res) {
                                if (res.success) {
                                    editors.html.setValue(res.html_content || '');
                                    editors.css.setValue(res.css_content  || '');
                                    editors.js.setValue(res.js_content   || '');
                                    closeModal('history-modal');
                                    showToast(res.message || 'Version restored!');
                                    SERVER_VER++;
                                } else {
                                    showToast(res.error || 'Restore failed', true);
                                }
                            });
                        }
                    });
                });
            });
        })
        .catch(function() {
            list.innerHTML = '<div style="color:#f87171;font-size:.83rem;padding:12px">Failed to load version history.</div>';
        });
    }

    /* ── Start SSE ────────────────────────────────────────────── */
    connectSSE();

    /* Reconnect on visibility change (tab becomes active again) */
    document.addEventListener('visibilitychange', function() {
        if (!document.hidden && (!eventSource || eventSource.readyState === EventSource.CLOSED)) {
            connectSSE();
        }
    });

    function escHtml(str) {
        return String(str)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function openModal(id)  { document.getElementById(id).classList.add('open'); }
    function closeModal(id) { document.getElementById(id).classList.remove('open'); }

}());
</script>
<!-- ── Invite collaborator modal ──────────────────────────────── -->
<div class="modal-overlay" id="invite-modal">
  <div class="modal-box" style="max-width:480px">
    <div class="modal-title">
      <i class="fa fa-user-plus"></i> Invite Collaborator
      <button class="modal-close" data-close="invite-modal">&times;</button>
    </div>
    <div style="margin-bottom:12px;font-size:.83rem;color:var(--editor-muted)">Invite someone to collaborate on this project in real time.</div>

    <div id="invite-link-row" style="display:none;margin-bottom:14px;">
      <label style="font-size:.78rem;color:var(--editor-muted);display:block;margin-bottom:4px">Shareable link</label>
      <div style="display:flex;gap:6px">
        <input type="text" id="invite-link-input" readonly
               style="flex:1;background:var(--editor-toolbar);border:1px solid var(--editor-border);border-radius:5px;padding:6px 10px;color:var(--editor-text);font-size:.78rem;outline:none">
        <button class="tb-btn" id="copy-link-btn" style="flex-shrink:0"><i class="fa fa-copy"></i></button>
      </div>
    </div>

    <label style="font-size:.78rem;color:var(--editor-muted);display:block;margin-bottom:6px">Email address</label>
    <input type="email" id="invite-email"
           style="width:100%;background:var(--editor-toolbar);border:1px solid var(--editor-border);border-radius:5px;padding:8px 12px;color:var(--editor-text);font-size:.85rem;outline:none;margin-bottom:12px"
           placeholder="colleague@company.com">

    <label style="display:flex;align-items:center;gap:8px;font-size:.82rem;color:var(--editor-muted);margin-bottom:16px;cursor:pointer">
      <input type="checkbox" id="invite-can-edit" style="accent-color:var(--editor-accent)">
      Allow editing (not just viewing)
    </label>

    <div style="display:flex;gap:8px;justify-content:flex-end">
      <button class="tb-btn" onclick="closeModal('invite-modal')">Cancel</button>
      <button class="tb-btn primary" id="do-invite-btn"><i class="fa fa-paper-plane"></i> Send Invite</button>
    </div>

    <div id="members-list" style="margin-top:18px;border-top:1px solid var(--editor-border);padding-top:14px;display:none">
      <div style="font-size:.78rem;color:var(--editor-muted);margin-bottom:10px;font-weight:600;text-transform:uppercase;letter-spacing:.05em">Current collaborators</div>
      <div id="members-rows"></div>
    </div>
  </div>
</div>

<!-- ── Version history drawer ──────────────────────────────────── -->
<div class="modal-overlay" id="history-modal">
  <div class="modal-box" style="max-width:560px">
    <div class="modal-title">
      <i class="fa fa-clock-rotate-left"></i> Version History
      <button class="modal-close" data-close="history-modal">&times;</button>
    </div>
    <div id="version-list" style="display:flex;flex-direction:column;gap:6px;max-height:55vh;overflow-y:auto"></div>
  </div>
</div>

<!-- ── Conflict toast (persistent until dismissed) ─────────────── -->
<div id="conflict-banner" style="display:none;position:fixed;top:calc(var(--navbar-height) + 54px);left:50%;transform:translateX(-50%);
     background:#3d2200;border:1px solid #fb923c;color:#fb923c;padding:10px 18px;border-radius:8px;
     font-size:.83rem;font-weight:600;z-index:9000;display:none;align-items:center;gap:10px;box-shadow:0 4px 20px rgba(0,0,0,.5)">
  <i class="fa fa-triangle-exclamation"></i>
  <span>Conflict detected — someone else saved while you were editing.</span>
  <button onclick="document.getElementById('conflict-banner').style.display='none'"
          style="background:none;border:none;color:#fb923c;cursor:pointer;font-size:1rem;padding:0 4px">&times;</button>
</div>
</body>
</html>
