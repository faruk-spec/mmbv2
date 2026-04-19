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

        #resizer{flex:0 0 5px;background:var(--editor-border);cursor:ew-resize;transition:background .2s;position:relative;z-index:10}
        #resizer:hover,#resizer.dragging{background:var(--editor-accent)}
        #resizer::after{content:'';position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:3px;height:36px;background:rgba(255,255,255,.18);border-radius:2px}

        #preview-panel{flex:1 1 50%;min-width:200px;display:flex;flex-direction:column;overflow:hidden}
        #preview-header{
            height:32px;background:var(--editor-toolbar);border-bottom:1px solid var(--editor-border);
            display:flex;align-items:center;padding:0 10px;gap:8px;flex-shrink:0;
        }
        #preview-header span{font-size:.72rem;color:var(--editor-muted);font-weight:600;text-transform:uppercase;letter-spacing:.06em}
        #preview-refresh-btn{margin-left:auto;background:none;border:none;color:var(--editor-muted);cursor:pointer;font-size:.8rem;padding:2px 5px;border-radius:4px;transition:color .15s}
        #preview-refresh-btn:hover{color:var(--editor-accent)}
        #preview-panel-inner{flex:1;display:flex;align-items:stretch;justify-content:center;background:var(--editor-bg);overflow:hidden}
        #preview-frame-wrap{width:100%;height:100%;overflow:hidden;transition:width .3s}
        #preview-iframe{width:100%;height:100%;border:none;background:#fff;display:block}

        /* Status bar */
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

        /* Toast */
        #save-toast{
            position:fixed;bottom:36px;right:24px;background:#1a3d2b;border:1px solid #4ade80;
            color:#4ade80;padding:10px 18px;border-radius:8px;font-size:.85rem;font-weight:600;
            display:none;align-items:center;gap:8px;z-index:9999;box-shadow:0 4px 20px rgba(0,0,0,.5);
        }
        #save-toast.error{background:#3d1a1a;border-color:#f87171;color:#f87171}

        /* Modals */
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

        @media(max-width:768px){#resizer,#preview-panel{display:none}#code-panel{flex-basis:100%!important}}
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
        <i class="fa fa-floppy-disk"></i><span>Save</span>
    </button>
    <button class="tb-btn" id="format-btn" title="Format (Alt+Shift+F)">
        <i class="fa fa-wand-magic-sparkles"></i><span>Format</span>
    </button>
    <button class="tb-btn" id="validate-btn" title="Validate code">
        <i class="fa fa-circle-check"></i><span>Validate</span>
    </button>
    <button class="tb-btn" id="export-btn" title="Export as HTML">
        <i class="fa fa-file-export"></i><span>Export</span>
    </button>

    <div class="tb-dropdown" id="templates-dropdown">
        <button class="tb-btn" id="templates-btn">
            <i class="fa fa-layer-group"></i><span>Templates</span>
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
            <i class="fa fa-mobile-screen-button"></i><span>Responsive</span>
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
            </div>
        </div>
    </div>
</div>

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

    const PROJECT_ID = <?= json_encode($project['id'] ?? null) ?>;
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

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
        editors[lang].on('change', function() {
            if (lang === activeLang) updateStatusBar();
            schedulePreviewRefresh();
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

    /* Resizer */
    var editorBody   = document.getElementById('editor-body');
    var codePanel    = document.getElementById('code-panel');
    var previewPanel = document.getElementById('preview-panel');
    var resizer      = document.getElementById('resizer');
    var isResizing   = false;

    resizer.addEventListener('mousedown', function(e) {
        isResizing = true;
        resizer.classList.add('dragging');
        document.body.style.cursor     = 'ew-resize';
        document.body.style.userSelect = 'none';
        e.preventDefault();
    });

    document.addEventListener('mousemove', function(e) {
        if (!isResizing) return;
        var rect   = editorBody.getBoundingClientRect();
        var totalW = rect.width - 5;
        var leftPx = e.clientX - rect.left;
        var minPx  = 250;
        var maxPx  = totalW * 0.80;
        leftPx = Math.max(minPx, Math.min(maxPx, leftPx));
        codePanel.style.flexBasis    = leftPx + 'px';
        codePanel.style.flexGrow     = '0';
        previewPanel.style.flexBasis = (totalW - leftPx) + 'px';
        previewPanel.style.flexGrow  = '0';
    });

    document.addEventListener('mouseup', function() {
        if (!isResizing) return;
        isResizing = false;
        resizer.classList.remove('dragging');
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
        var fd = new FormData();
        if (PROJECT_ID) fd.append('project_id', PROJECT_ID);
        fd.append('name',         nameText.textContent.trim());
        fd.append('html_content', editors.html.getValue());
        fd.append('css_content',  editors.css.getValue());
        fd.append('js_content',   editors.js.getValue());
        fd.append('visibility',   'private');
        fd.append('_token',       CSRF_TOKEN);

        fetch('/projects/codexpro/editor/save', {
            method: 'POST',
            headers: { 'X-CSRF-Token': CSRF_TOKEN },
            body: fd
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                showToast('Saved successfully!');
            } else {
                showToast(data.message || 'Save failed', true);
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
</script>
</body>
</html>
