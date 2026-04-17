<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<?php
/**
 * ResumeX Visual Template Designer
 *
 * A drag-and-drop A4 canvas editor for building resume templates visually.
 * Injected variables:
 *   $csrfToken   — string
 *   $template    — array|null  : existing DB row (null = new)
 *   $templateId  — int         : 0 = new
 *   $designJson  — string      : JSON-encoded design or 'null'
 */
$isEdit = $templateId > 0 && $template !== null;
?>

<style>
/* ── Layout ──────────────────────────────────────────────────────────────── */
.rxd-wrap {
    display: flex;
    height: calc(100vh - 130px);
    gap: 0;
    border: 1px solid var(--border-color);
    border-radius: 10px;
    overflow: hidden;
    background: #14141f;
}
/* Left sidebar */
.rxd-sidebar {
    width: 260px;
    min-width: 220px;
    max-width: 320px;
    display: flex;
    flex-direction: column;
    background: #0e0e1a;
    border-right: 1px solid var(--border-color);
    overflow: hidden;
    flex-shrink: 0;
}
.rxd-sidebar-tabs {
    display: flex;
    border-bottom: 1px solid var(--border-color);
    background: #0a0a14;
    flex-shrink: 0;
}
.rxd-sidebar-tab {
    flex: 1;
    padding: 9px 4px;
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    font-size: .78rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .04em;
    transition: color .15s, background .15s;
    border-bottom: 2px solid transparent;
}
.rxd-sidebar-tab.active {
    color: var(--cyan);
    border-bottom-color: var(--cyan);
    background: rgba(59,130,246,.04);
}
.rxd-sidebar-panel { display: none; flex: 1; overflow-y: auto; padding: 12px; }
.rxd-sidebar-panel.active { display: block; }

/* Field palette */
.rxd-fields-group { margin-bottom: 14px; }
.rxd-fields-group-title {
    font-size: .7rem;
    font-weight: 700;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-bottom: 6px;
    padding: 0 2px;
}
.rxd-field-chip {
    display: flex;
    align-items: center;
    gap: 7px;
    background: rgba(255,255,255,.04);
    border: 1px solid var(--border-color);
    border-radius: 7px;
    padding: 6px 10px;
    margin-bottom: 5px;
    cursor: grab;
    font-size: .8rem;
    color: var(--text-primary);
    transition: border-color .15s, background .15s;
    user-select: none;
}
.rxd-field-chip:active { cursor: grabbing; }
.rxd-field-chip:hover { border-color: rgba(59,130,246,.5); background: rgba(59,130,246,.06); }
.rxd-field-chip i { color: var(--cyan); width: 14px; text-align: center; }

/* Style panel */
.rxd-style-panel-empty {
    padding: 24px 8px;
    text-align: center;
    color: var(--text-secondary);
    font-size: .82rem;
}
.rxd-style-row { margin-bottom: 10px; }
.rxd-style-label {
    font-size: .72rem;
    color: var(--text-secondary);
    margin-bottom: 3px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .04em;
}
.rxd-style-input {
    width: 100%;
    background: rgba(255,255,255,.05);
    border: 1px solid var(--border-color);
    border-radius: 5px;
    color: var(--text-primary);
    padding: 5px 8px;
    font-size: .82rem;
}
.rxd-style-input:focus { outline: none; border-color: rgba(59,130,246,.4); }
.rxd-style-row-inline { display: flex; gap: 6px; }
.rxd-style-row-inline .rxd-style-row { flex: 1; margin-bottom: 0; }
.rxd-del-btn {
    width: 100%;
    margin-top: 14px;
    padding: 7px;
    background: rgba(239,68,68,.12);
    border: 1px solid rgba(239,68,68,.3);
    border-radius: 6px;
    color: #f87171;
    cursor: pointer;
    font-size: .82rem;
    transition: background .15s;
}
.rxd-del-btn:hover { background: rgba(239,68,68,.22); }

/* Canvas area */
.rxd-canvas-area {
    flex: 1;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    background: #1a1a2e;
}
.rxd-toolbar {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    background: #0e0e1a;
    border-bottom: 1px solid var(--border-color);
    flex-shrink: 0;
    flex-wrap: wrap;
}
.rxd-toolbar-btn {
    padding: 5px 12px;
    border-radius: 6px;
    border: 1px solid var(--border-color);
    background: rgba(255,255,255,.04);
    color: var(--text-primary);
    font-size: .8rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: border-color .15s, background .15s;
    white-space: nowrap;
}
.rxd-toolbar-btn:hover { border-color: rgba(59,130,246,.4); background: rgba(59,130,246,.06); }
.rxd-toolbar-btn.primary { background: var(--cyan); color: #fff; border-color: var(--cyan); font-weight: 700; }
.rxd-toolbar-btn.primary:hover { filter: brightness(1.1); }
.rxd-toolbar-btn.danger { border-color: rgba(239,68,68,.4); color: #f87171; }
.rxd-toolbar-btn.danger:hover { background: rgba(239,68,68,.1); }
.rxd-toolbar-sep { width: 1px; height: 20px; background: var(--border-color); margin: 0 2px; }
.rxd-zoom-label { font-size: .78rem; color: var(--text-secondary); min-width: 38px; text-align: center; }

.rxd-canvas-scroll {
    flex: 1;
    overflow: auto;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding: 32px;
}

/* A4 canvas */
#rxdCanvas {
    position: relative;
    width: 794px;
    min-height: 1123px;
    background: #ffffff;
    box-shadow: 0 4px 40px rgba(0,0,0,.5);
    flex-shrink: 0;
    transform-origin: top center;
    font-family: Inter, sans-serif;
}
#rxdCanvas.drag-over { outline: 2px dashed rgba(59,130,246,.6); }

/* Blocks on canvas */
.rxd-block {
    position: absolute;
    box-sizing: border-box;
    cursor: move;
    user-select: none;
    outline: 1px solid transparent;
    transition: outline-color .1s;
}
.rxd-block:hover { outline-color: rgba(59,130,246,.35); }
.rxd-block.selected { outline: 2px solid var(--cyan) !important; }
/* Resize handles */
.rxd-resize-handle {
    position: absolute;
    width: 9px;
    height: 9px;
    background: var(--cyan);
    border: 1px solid #06060a;
    border-radius: 2px;
    z-index: 10;
    display: none;
}
.rxd-block.selected .rxd-resize-handle { display: block; }
.rxd-resize-handle.se { right: -5px; bottom: -5px; cursor: se-resize; }
.rxd-resize-handle.sw { left: -5px; bottom: -5px; cursor: sw-resize; }
.rxd-resize-handle.ne { right: -5px; top: -5px; cursor: ne-resize; }
.rxd-resize-handle.nw { left: -5px; top: -5px; cursor: nw-resize; }
.rxd-resize-handle.n  { left: 50%; top: -5px; transform: translateX(-50%); cursor: n-resize; }
.rxd-resize-handle.s  { left: 50%; bottom: -5px; transform: translateX(-50%); cursor: s-resize; }
.rxd-resize-handle.e  { top: 50%; right: -5px; transform: translateY(-50%); cursor: e-resize; }
.rxd-resize-handle.w  { top: 50%; left: -5px; transform: translateY(-50%); cursor: w-resize; }

/* Settings panel (right side) */
.rxd-settings {
    width: 230px;
    min-width: 200px;
    background: #0e0e1a;
    border-left: 1px solid var(--border-color);
    display: flex;
    flex-direction: column;
    overflow-y: auto;
    padding: 14px;
    flex-shrink: 0;
}
.rxd-settings-title {
    font-size: .75rem;
    font-weight: 700;
    color: var(--text-secondary);
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-bottom: 12px;
}
.rxd-variant-row {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
    background: rgba(255,255,255,.03);
    border: 1px solid var(--border-color);
    border-radius: 6px;
    padding: 6px 8px;
}
.rxd-variant-label { flex: 1; font-size: .8rem; color: var(--text-primary); }
.rxd-variant-swatch { width: 18px; height: 18px; border-radius: 50%; border: 1px solid rgba(255,255,255,.2); }
.rxd-variant-del { background: none; border: none; color: #f87171; cursor: pointer; font-size: .9rem; padding: 0 2px; }

/* Grid overlay */
#rxdGrid { display:none; }
#rxdCanvas.show-grid #rxdGrid {
    display: block;
    position: absolute;
    inset: 0;
    pointer-events: none;
    background-image:
        linear-gradient(rgba(0,0,0,.06) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0,0,0,.06) 1px, transparent 1px);
    background-size: 20px 20px;
    z-index: 0;
}

/* Save status */
#rxdSaveStatus { font-size:.78rem; color:var(--text-secondary); }
#rxdSaveStatus.saving { color: var(--cyan); }
#rxdSaveStatus.saved  { color: #4ade80; }
#rxdSaveStatus.error  { color: #f87171; }
</style>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
    <div>
        <h1 style="margin-bottom:2px;">
            <i class="fas fa-magic" style="color:var(--cyan);"></i>
            <?= $isEdit ? 'Edit Template: <em style="color:var(--cyan);">' . htmlspecialchars($template['name']) . '</em>' : 'Visual Template Designer' ?>
        </h1>
        <p style="color:var(--text-secondary);font-size:.85rem;">
            Drag fields from the left panel onto the A4 canvas. Click a block to edit its style.
        </p>
    </div>
    <a href="/admin/projects/resumex/templates" class="btn btn-secondary" style="flex-shrink:0;">
        <i class="fas fa-arrow-left"></i> Templates
    </a>
</div>

<!-- Template meta (key, name, category) -->
<div class="card" style="margin-bottom:14px;padding:14px 18px;">
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr auto auto;gap:12px;align-items:end;">
        <div>
            <label class="form-label">Template Key <?= $isEdit ? '' : '<span style="color:#f87171;">*</span>' ?></label>
            <input class="form-input" type="text" id="rxdKey" placeholder="my-resume"
                   pattern="[a-z0-9\-]+" maxlength="100"
                   value="<?= htmlspecialchars($template['key'] ?? '') ?>"
                   <?= $isEdit ? 'readonly style="opacity:.6;"' : '' ?>>
        </div>
        <div>
            <label class="form-label">Display Name <span style="color:#f87171;">*</span></label>
            <input class="form-input" type="text" id="rxdName" maxlength="255"
                   value="<?= htmlspecialchars($template['name'] ?? '') ?>" placeholder="My Custom Resume">
        </div>
        <div>
            <label class="form-label">Category</label>
            <select class="form-input" id="rxdCategory">
                <?php foreach (['custom','professional','academic','dark','light','creative','warm'] as $cat): ?>
                <option value="<?= $cat ?>" <?= ($template['category'] ?? 'custom') === $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="form-label">BG Colour</label>
            <input type="color" id="rxdBg" value="<?= htmlspecialchars($template['display_bg'] ?? '#ffffff') ?>"
                   title="Canvas background" style="width:38px;height:36px;border:none;background:none;cursor:pointer;padding:0;">
        </div>
        <div>
            <label class="form-label">Accent</label>
            <input type="color" id="rxdPri" value="<?= htmlspecialchars($template['display_pri'] ?? '#007bff') ?>"
                   title="Primary accent colour" style="width:38px;height:36px;border:none;background:none;cursor:pointer;padding:0;">
        </div>
    </div>
</div>

<!-- Main designer layout -->
<div class="rxd-wrap">

    <!-- Left sidebar: Fields + Style -->
    <div class="rxd-sidebar">
        <div class="rxd-sidebar-tabs">
            <button class="rxd-sidebar-tab active" data-panel="fields" onclick="rxdSwitchTab('fields',this)">
                <i class="fas fa-th-list"></i> Fields
            </button>
            <button class="rxd-sidebar-tab" data-panel="style" onclick="rxdSwitchTab('style',this)">
                <i class="fas fa-paint-brush"></i> Style
            </button>
        </div>

        <!-- Fields panel -->
        <div class="rxd-sidebar-panel active" id="rxdPanelFields">
            <div class="rxd-fields-group">
                <div class="rxd-fields-group-title"><i class="fas fa-user"></i> Contact Info</div>
                <div class="rxd-field-chip" draggable="true" data-field="contact.name"  data-label="Full Name"  data-type="field"><i class="fas fa-user"></i> Full Name</div>
                <div class="rxd-field-chip" draggable="true" data-field="contact.email" data-label="Email"     data-type="field"><i class="fas fa-envelope"></i> Email</div>
                <div class="rxd-field-chip" draggable="true" data-field="contact.phone" data-label="Phone"     data-type="field"><i class="fas fa-phone"></i> Phone</div>
                <div class="rxd-field-chip" draggable="true" data-field="contact.location" data-label="Location" data-type="field"><i class="fas fa-map-marker-alt"></i> Location</div>
                <div class="rxd-field-chip" draggable="true" data-field="contact.website"  data-label="Website"  data-type="field"><i class="fas fa-globe"></i> Website</div>
                <div class="rxd-field-chip" draggable="true" data-field="contact.linkedin" data-label="LinkedIn" data-type="field"><i class="fab fa-linkedin"></i> LinkedIn</div>
                <div class="rxd-field-chip" draggable="true" data-field="contact.github"   data-label="GitHub"   data-type="field"><i class="fab fa-github"></i> GitHub</div>
                <div class="rxd-field-chip" draggable="true" data-field="contact.photo"    data-label="Photo"    data-type="field"><i class="fas fa-camera"></i> Photo</div>
            </div>
            <div class="rxd-fields-group">
                <div class="rxd-fields-group-title"><i class="fas fa-align-left"></i> Content</div>
                <div class="rxd-field-chip" draggable="true" data-field="summary"         data-label="Summary"        data-type="field"><i class="fas fa-align-left"></i> Summary</div>
                <div class="rxd-field-chip" draggable="true" data-field="experience"      data-label="Experience"     data-type="field"><i class="fas fa-briefcase"></i> Work Experience</div>
                <div class="rxd-field-chip" draggable="true" data-field="education"       data-label="Education"      data-type="field"><i class="fas fa-graduation-cap"></i> Education</div>
                <div class="rxd-field-chip" draggable="true" data-field="skills"          data-label="Skills"         data-type="field"><i class="fas fa-tools"></i> Skills</div>
                <div class="rxd-field-chip" draggable="true" data-field="projects"        data-label="Projects"       data-type="field"><i class="fas fa-project-diagram"></i> Projects</div>
                <div class="rxd-field-chip" draggable="true" data-field="certifications"  data-label="Certifications" data-type="field"><i class="fas fa-certificate"></i> Certifications</div>
                <div class="rxd-field-chip" draggable="true" data-field="languages"       data-label="Languages"      data-type="field"><i class="fas fa-language"></i> Languages</div>
                <div class="rxd-field-chip" draggable="true" data-field="awards"          data-label="Awards"         data-type="field"><i class="fas fa-trophy"></i> Awards</div>
                <div class="rxd-field-chip" draggable="true" data-field="volunteer"       data-label="Volunteer"      data-type="field"><i class="fas fa-hands-helping"></i> Volunteer</div>
                <div class="rxd-field-chip" draggable="true" data-field="hobbies"         data-label="Hobbies"        data-type="field"><i class="fas fa-heart"></i> Hobbies</div>
                <div class="rxd-field-chip" draggable="true" data-field="references"      data-label="References"     data-type="field"><i class="fas fa-user-friends"></i> References</div>
                <div class="rxd-field-chip" draggable="true" data-field="publications"    data-label="Publications"   data-type="field"><i class="fas fa-book"></i> Publications</div>
            </div>
            <div class="rxd-fields-group">
                <div class="rxd-fields-group-title"><i class="fas fa-shapes"></i> Layout Elements</div>
                <div class="rxd-field-chip" draggable="true" data-field=""         data-label="Section Heading" data-type="section_heading"><i class="fas fa-heading"></i> Section Heading</div>
                <div class="rxd-field-chip" draggable="true" data-field=""         data-label="Custom Text"     data-type="text"><i class="fas fa-font"></i> Custom Text</div>
                <div class="rxd-field-chip" draggable="true" data-field=""         data-label="Divider"         data-type="divider"><i class="fas fa-minus"></i> Divider Line</div>
                <div class="rxd-field-chip" draggable="true" data-field=""         data-label="Spacer"          data-type="spacer"><i class="fas fa-arrows-alt-v"></i> Spacer</div>
                <div class="rxd-field-chip" draggable="true" data-field=""         data-label="Background Block" data-type="bg_block"><i class="fas fa-square"></i> Background Block</div>
            </div>
        </div>

        <!-- Style panel -->
        <div class="rxd-sidebar-panel" id="rxdPanelStyle">
            <div id="rxdStyleEmpty" class="rxd-style-panel-empty">
                <i class="fas fa-mouse-pointer" style="font-size:1.6rem;margin-bottom:8px;display:block;"></i>
                Click a block on the canvas to edit its style.
            </div>
            <div id="rxdStyleControls" style="display:none;">
                <div class="rxd-style-row">
                    <div class="rxd-style-label">Content / Label</div>
                    <input class="rxd-style-input" type="text" id="sContent" placeholder="Label text">
                </div>
                <div class="rxd-style-row-inline">
                    <div class="rxd-style-row">
                        <div class="rxd-style-label">Font size (px)</div>
                        <input class="rxd-style-input" type="number" id="sFontSize" min="6" max="120" value="14">
                    </div>
                    <div class="rxd-style-row">
                        <div class="rxd-style-label">Weight</div>
                        <select class="rxd-style-input" id="sFontWeight">
                            <option value="300">Light</option>
                            <option value="400">Normal</option>
                            <option value="600">SemiBold</option>
                            <option value="700">Bold</option>
                            <option value="800">ExtraBold</option>
                        </select>
                    </div>
                </div>
                <div class="rxd-style-row">
                    <div class="rxd-style-label">Text Align</div>
                    <select class="rxd-style-input" id="sTextAlign">
                        <option value="left">Left</option>
                        <option value="center">Center</option>
                        <option value="right">Right</option>
                    </select>
                </div>
                <div class="rxd-style-row-inline">
                    <div class="rxd-style-row">
                        <div class="rxd-style-label">Text colour</div>
                        <input class="rxd-style-input" type="color" id="sColor" value="#000000" style="height:32px;padding:2px;">
                    </div>
                    <div class="rxd-style-row" style="flex:2;">
                        <div class="rxd-style-label">Or colour token</div>
                        <select class="rxd-style-input" id="sColorToken">
                            <option value="">— pick above —</option>
                            <option value="{{primary}}">{{primary}}</option>
                            <option value="{{secondary}}">{{secondary}}</option>
                        </select>
                    </div>
                </div>
                <div class="rxd-style-row-inline">
                    <div class="rxd-style-row">
                        <div class="rxd-style-label">Background</div>
                        <input class="rxd-style-input" type="color" id="sBgColor" value="#ffffff" style="height:32px;padding:2px;">
                    </div>
                    <div class="rxd-style-row" style="flex:2;">
                        <div class="rxd-style-label">Or token</div>
                        <select class="rxd-style-input" id="sBgToken">
                            <option value="">— pick above —</option>
                            <option value="transparent">transparent</option>
                            <option value="{{primary}}">{{primary}}</option>
                            <option value="{{secondary}}">{{secondary}}</option>
                        </select>
                    </div>
                </div>
                <div class="rxd-style-row">
                    <div class="rxd-style-label">Background image URL</div>
                    <input class="rxd-style-input" type="text" id="sBgImage" placeholder="https://…">
                </div>
                <div class="rxd-style-row-inline">
                    <div class="rxd-style-row">
                        <div class="rxd-style-label">Padding</div>
                        <input class="rxd-style-input" type="text" id="sPadding" placeholder="4px 8px">
                    </div>
                    <div class="rxd-style-row">
                        <div class="rxd-style-label">Radius (px)</div>
                        <input class="rxd-style-input" type="number" id="sBorderRadius" min="0" max="200" value="0">
                    </div>
                </div>
                <div class="rxd-style-row">
                    <div class="rxd-style-label">Line height</div>
                    <input class="rxd-style-input" type="text" id="sLineHeight" placeholder="1.4">
                </div>
                <div class="rxd-style-row">
                    <div class="rxd-style-label">Border (CSS)</div>
                    <input class="rxd-style-input" type="text" id="sBorder" placeholder="1px solid #ddd">
                </div>
                <div class="rxd-style-row">
                    <div class="rxd-style-label">Box shadow</div>
                    <input class="rxd-style-input" type="text" id="sBoxShadow" placeholder="0 2px 8px rgba(0,0,0,.1)">
                </div>
                <div class="rxd-style-row">
                    <div class="rxd-style-label">Position &amp; size (px)</div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:4px;">
                        <input class="rxd-style-input" type="number" id="sPosX" placeholder="X (left)">
                        <input class="rxd-style-input" type="number" id="sPosY" placeholder="Y (top)">
                        <input class="rxd-style-input" type="number" id="sPosW" placeholder="Width" min="10">
                        <input class="rxd-style-input" type="number" id="sPosH" placeholder="Height" min="10">
                    </div>
                </div>
                <div class="rxd-style-row">
                    <div class="rxd-style-label">Z-index (layer)</div>
                    <input class="rxd-style-input" type="number" id="sZIndex" value="1" min="0" max="999">
                </div>
                <button class="rxd-del-btn" onclick="rxdDeleteSelected()">
                    <i class="fas fa-trash"></i> Remove Block
                </button>
            </div>
        </div>
    </div>

    <!-- Canvas area -->
    <div class="rxd-canvas-area">
        <!-- Toolbar -->
        <div class="rxd-toolbar">
            <button class="rxd-toolbar-btn" onclick="rxdZoom(-0.1)"><i class="fas fa-search-minus"></i></button>
            <span class="rxd-zoom-label" id="rxdZoomLabel">100%</span>
            <button class="rxd-toolbar-btn" onclick="rxdZoom(+0.1)"><i class="fas fa-search-plus"></i></button>
            <button class="rxd-toolbar-btn" onclick="rxdZoom(0, 1)"><i class="fas fa-expand-arrows-alt"></i> Fit</button>
            <div class="rxd-toolbar-sep"></div>
            <button class="rxd-toolbar-btn" onclick="rxdToggleGrid()" id="rxdGridBtn"><i class="fas fa-th"></i> Grid</button>
            <div class="rxd-toolbar-sep"></div>
            <button class="rxd-toolbar-btn" onclick="rxdUndo()" title="Undo (Ctrl+Z)"><i class="fas fa-undo"></i></button>
            <button class="rxd-toolbar-btn" onclick="rxdRedo()" title="Redo (Ctrl+Y)"><i class="fas fa-redo"></i></button>
            <div class="rxd-toolbar-sep"></div>
            <button class="rxd-toolbar-btn" onclick="rxdDuplicateSelected()"><i class="fas fa-copy"></i> Duplicate</button>
            <button class="rxd-toolbar-btn danger" onclick="rxdDeleteSelected()"><i class="fas fa-trash"></i> Delete</button>
            <div class="rxd-toolbar-sep"></div>
            <span id="rxdSaveStatus"></span>
            <button class="rxd-toolbar-btn primary" onclick="rxdSave()"><i class="fas fa-save"></i> Save Template</button>
        </div>

        <!-- Scrollable canvas -->
        <div class="rxd-canvas-scroll" id="rxdCanvasScroll">
            <div id="rxdCanvas">
                <div id="rxdGrid"></div>
                <!-- Blocks are inserted here by JS -->
            </div>
        </div>
    </div>

    <!-- Right settings panel: Canvas settings + Color variants -->
    <div class="rxd-settings">
        <div class="rxd-settings-title"><i class="fas fa-cog"></i> Canvas Settings</div>
        <div class="rxd-style-row">
            <div class="rxd-style-label">Background</div>
            <input class="rxd-style-input" type="color" id="rxdCanvasBg" value="#ffffff"
                   style="height:32px;padding:2px;" oninput="rxdUpdateCanvasBg()">
        </div>
        <div class="rxd-style-row">
            <div class="rxd-style-label">Font family</div>
            <select class="rxd-style-input" id="rxdCanvasFont" onchange="rxdUpdateCanvasFont()">
                <option value="Inter">Inter</option>
                <option value="Roboto">Roboto</option>
                <option value="Open Sans">Open Sans</option>
                <option value="Lato">Lato</option>
                <option value="Montserrat">Montserrat</option>
                <option value="Raleway">Raleway</option>
                <option value="Poppins">Poppins</option>
                <option value="Playfair Display">Playfair Display</option>
                <option value="Merriweather">Merriweather</option>
                <option value="PT Serif">PT Serif</option>
                <option value="Georgia, serif">Georgia</option>
                <option value="Times New Roman, serif">Times New Roman</option>
            </select>
        </div>

        <div class="rxd-settings-title" style="margin-top:18px;"><i class="fas fa-palette"></i> Colour Variants</div>
        <p style="font-size:.73rem;color:var(--text-secondary);margin-bottom:10px;">Add multiple colour schemes for users to choose from.</p>
        <div id="rxdVariantList"></div>
        <button class="rxd-toolbar-btn" style="width:100%;justify-content:center;margin-top:6px;" onclick="rxdAddVariant()">
            <i class="fas fa-plus"></i> Add Variant
        </button>

        <div style="margin-top: auto; padding-top: 16px; font-size:.72rem; color:var(--text-secondary);">
            <p>A4 = 794 × 1123 px at screen resolution.</p>
        </div>
    </div>
</div>

<script>
(function() {
'use strict';

/* ── State ──────────────────────────────────────────────────────────────── */
var CANVAS_W   = 794;
var CANVAS_H   = 1123;
var csrfToken  = <?= json_encode($csrfToken) ?>;
var templateId = <?= (int)$templateId ?>;
var zoom       = 1;
var showGrid   = false;
var selectedId = null;
var dragOffset = {x:0, y:0};
var isDragging = false;
var isResizing = false;
var resizeEdge = '';
var resizeStart = null;
var undoStack  = [];
var redoStack  = [];
var dirtyTimer = null;

// The design state
var state = {
    canvas:        { background: '#ffffff', fontFamily: 'Inter' },
    blocks:        [],   // [{id,type,field,label,content,x,y,w,h,zIndex,style:{…}}, …]
    colorVariants: [],   // [{label,primary,secondary}, …]
};

/* ── Bootstrap from existing design ─────────────────────────────────────── */
var savedDesign = <?= $designJson ?>;
if (savedDesign && typeof savedDesign === 'object') {
    if (savedDesign.canvas)        state.canvas        = savedDesign.canvas;
    if (savedDesign.blocks)        state.blocks        = savedDesign.blocks;
    if (savedDesign.colorVariants) state.colorVariants = savedDesign.colorVariants;
}

/* ── DOM refs ────────────────────────────────────────────────────────────── */
var canvas   = document.getElementById('rxdCanvas');
var zoomLbl  = document.getElementById('rxdZoomLabel');
var saveStatus = document.getElementById('rxdSaveStatus');

/* ── Sidebar tab switch ──────────────────────────────────────────────────── */
window.rxdSwitchTab = function(name, btn) {
    document.querySelectorAll('.rxd-sidebar-tab').forEach(function(b) { b.classList.remove('active'); });
    document.querySelectorAll('.rxd-sidebar-panel').forEach(function(p) { p.classList.remove('active'); });
    btn.classList.add('active');
    document.getElementById('rxdPanel' + name.charAt(0).toUpperCase() + name.slice(1)).classList.add('active');
};

/* ── Zoom ────────────────────────────────────────────────────────────────── */
window.rxdZoom = function(delta, absolute) {
    if (absolute !== undefined) { zoom = absolute; }
    else { zoom = Math.min(2, Math.max(0.3, zoom + delta)); }
    zoom = Math.round(zoom * 10) / 10;
    canvas.style.transform = 'scale(' + zoom + ')';
    zoomLbl.textContent = Math.round(zoom * 100) + '%';
    // Adjust scroll area height
    var scroll = document.getElementById('rxdCanvasScroll');
    scroll.style.minHeight = (CANVAS_H * zoom + 80) + 'px';
};
// Default: fit to viewport
(function() {
    var scroll = document.getElementById('rxdCanvasScroll');
    var availH = scroll.clientHeight || 600;
    var availW = scroll.clientWidth  || 700;
    var fitZoom = Math.min((availW - 80) / CANVAS_W, (availH - 80) / CANVAS_H, 1);
    rxdZoom(0, Math.round(fitZoom * 10) / 10);
}());

/* ── Grid ────────────────────────────────────────────────────────────────── */
window.rxdToggleGrid = function() {
    showGrid = !showGrid;
    canvas.classList.toggle('show-grid', showGrid);
    document.getElementById('rxdGridBtn').style.borderColor = showGrid ? 'rgba(59,130,246,.5)' : '';
    document.getElementById('rxdGridBtn').style.color       = showGrid ? 'var(--cyan)' : '';
};

/* ── Block helpers ───────────────────────────────────────────────────────── */
var _blockId = Date.now();
function genId() { return 'b' + (++_blockId); }

function getBlock(id) {
    return state.blocks.find(function(b) { return b.id === id; }) || null;
}

function snapToGrid(v, gridSize) {
    gridSize = gridSize || (showGrid ? 10 : 1);
    return Math.round(v / gridSize) * gridSize;
}

/* ── Render all blocks ───────────────────────────────────────────────────── */
function renderAllBlocks() {
    // Remove existing block divs
    canvas.querySelectorAll('.rxd-block').forEach(function(el) { el.remove(); });
    state.blocks.forEach(function(block) { renderBlock(block); });
    applyCanvasStyles();
}

function renderBlock(block) {
    var el = document.createElement('div');
    el.className = 'rxd-block';
    el.id = 'rxdB-' + block.id;
    el.dataset.blockId = block.id;
    el.style.zIndex = block.zIndex || 1;

    applyBlockStyles(el, block);
    el.innerHTML = blockInnerHtml(block) + resizeHandlesHtml();

    canvas.appendChild(el);
    attachBlockEvents(el, block);
}

function blockInnerHtml(block) {
    var type  = block.type  || 'field';
    var field = block.field || '';
    var label = block.label || field || block.content || 'Block';

    if (type === 'divider') {
        return '<hr style="border:none;border-top:1px solid currentColor;margin:0;height:1px;">';
    }
    if (type === 'spacer' || type === 'bg_block') {
        return '<div style="width:100%;height:100%;"></div>';
    }
    var disp = block.content || label;
    return '<div class="rxd-block-inner" style="width:100%;height:100%;overflow:hidden;">' + escHtml(disp) + '</div>';
}

function resizeHandlesHtml() {
    return ['n','s','e','w','ne','nw','se','sw'].map(function(d) {
        return '<div class="rxd-resize-handle ' + d + '" data-edge="' + d + '"></div>';
    }).join('');
}

function applyBlockStyles(el, block) {
    var s = block.style || {};
    el.style.left      = (block.x || 0) + 'px';
    el.style.top       = (block.y || 0) + 'px';
    el.style.width     = (block.w || 200) + 'px';
    el.style.minHeight = (block.h || 40) + 'px';
    el.style.height    = (block.h || 40) + 'px';

    el.style.fontSize       = s.fontSize       ? s.fontSize + 'px' : '';
    el.style.fontWeight     = s.fontWeight     || '';
    el.style.color          = resolveToken(s.color || '');
    el.style.textAlign      = s.textAlign      || '';
    el.style.lineHeight     = s.lineHeight     || '';
    el.style.padding        = s.padding        || '';
    el.style.borderRadius   = s.borderRadius   ? s.borderRadius + 'px' : '';
    el.style.border         = s.border         || '';
    el.style.boxShadow      = s.boxShadow      || '';

    var bg = resolveToken(s.backgroundColor || '');
    el.style.backgroundColor = (bg && bg !== 'transparent') ? bg : '';

    if (s.backgroundImage) {
        el.style.backgroundImage    = 'url("' + s.backgroundImage + '")';
        el.style.backgroundSize     = 'cover';
        el.style.backgroundPosition = 'center';
    } else {
        el.style.backgroundImage = '';
    }
}

function resolveToken(val) {
    if (!val) return val;
    var pri = (state.colorVariants[0] && state.colorVariants[0].primary)   || '#007bff';
    var sec = (state.colorVariants[0] && state.colorVariants[0].secondary) || '#6f42c1';
    return val.replace(/\{\{primary\}\}/g, pri).replace(/\{\{secondary\}\}/g, sec);
}

function applyCanvasStyles() {
    canvas.style.backgroundColor = state.canvas.background || '#ffffff';
    canvas.style.fontFamily      = state.canvas.fontFamily  || 'Inter, sans-serif';
    document.getElementById('rxdCanvasBg').value   = state.canvas.background  || '#ffffff';
    document.getElementById('rxdCanvasFont').value = state.canvas.fontFamily   || 'Inter';
}

/* ── Canvas background/font ──────────────────────────────────────────────── */
window.rxdUpdateCanvasBg = function() {
    state.canvas.background = document.getElementById('rxdCanvasBg').value;
    canvas.style.backgroundColor = state.canvas.background;
    markDirty();
};
window.rxdUpdateCanvasFont = function() {
    state.canvas.fontFamily = document.getElementById('rxdCanvasFont').value;
    canvas.style.fontFamily = state.canvas.fontFamily;
    markDirty();
};

/* ── Colour variants ─────────────────────────────────────────────────────── */
function renderVariants() {
    var list = document.getElementById('rxdVariantList');
    list.innerHTML = '';
    state.colorVariants.forEach(function(v, i) {
        var row = document.createElement('div');
        row.className = 'rxd-variant-row';
        row.innerHTML =
            '<input type="color" class="rxd-variant-swatch" value="' + escAttr(v.primary) + '" data-vi="' + i + '" data-key="primary" title="Primary colour">' +
            '<input type="color" class="rxd-variant-swatch" value="' + escAttr(v.secondary) + '" data-vi="' + i + '" data-key="secondary" title="Secondary colour">' +
            '<input type="text" class="rxd-style-input rxd-variant-label" value="' + escAttr(v.label) + '" data-vi="' + i + '" style="flex:1;" placeholder="Variant name">' +
            '<button class="rxd-variant-del" data-vi="' + i + '" title="Remove">×</button>';
        list.appendChild(row);
    });
    // Events
    list.querySelectorAll('input[type=color]').forEach(function(inp) {
        inp.addEventListener('input', function() {
            var i = parseInt(this.dataset.vi, 10);
            state.colorVariants[i][this.dataset.key] = this.value;
            renderAllBlocks();
            markDirty();
        });
    });
    list.querySelectorAll('input[type=text].rxd-variant-label').forEach(function(inp) {
        inp.addEventListener('input', function() {
            var i = parseInt(this.dataset.vi, 10);
            state.colorVariants[i].label = this.value;
            markDirty();
        });
    });
    list.querySelectorAll('.rxd-variant-del').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var i = parseInt(this.dataset.vi, 10);
            state.colorVariants.splice(i, 1);
            renderVariants();
            markDirty();
        });
    });
}
window.rxdAddVariant = function() {
    state.colorVariants.push({ label: 'Variant ' + (state.colorVariants.length + 1), primary: '#007bff', secondary: '#6f42c1' });
    renderVariants();
    markDirty();
};

/* ── Drag from palette onto canvas ──────────────────────────────────────── */
var _dragField = null;
document.querySelectorAll('.rxd-field-chip').forEach(function(chip) {
    chip.addEventListener('dragstart', function(e) {
        _dragField = {
            field:   this.dataset.field,
            label:   this.dataset.label,
            type:    this.dataset.type || 'field',
            content: this.dataset.label,
        };
        e.dataTransfer.effectAllowed = 'copy';
    });
    chip.addEventListener('dragend', function() { _dragField = null; });
});

canvas.addEventListener('dragover', function(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'copy';
    canvas.classList.add('drag-over');
});
canvas.addEventListener('dragleave', function(e) {
    if (!canvas.contains(e.relatedTarget)) canvas.classList.remove('drag-over');
});
canvas.addEventListener('drop', function(e) {
    e.preventDefault();
    canvas.classList.remove('drag-over');
    if (!_dragField) return;

    var rect  = canvas.getBoundingClientRect();
    var rawX  = (e.clientX - rect.left) / zoom;
    var rawY  = (e.clientY - rect.top)  / zoom;
    var x = Math.max(0, snapToGrid(rawX - 100));
    var y = Math.max(0, snapToGrid(rawY - 20));

    var block = {
        id:      genId(),
        type:    _dragField.type,
        field:   _dragField.field,
        label:   _dragField.label,
        content: _dragField.content,
        x:       x,
        y:       y,
        w:       _dragField.type === 'divider' ? 500 : (_dragField.type === 'spacer' ? 200 : 300),
        h:       _dragField.type === 'divider' ? 10  : (_dragField.type === 'spacer' ? 20  : 40),
        zIndex:  state.blocks.length + 1,
        style: {
            fontSize:       _dragField.field === 'contact.name' ? 24 : 14,
            fontWeight:     _dragField.field === 'contact.name' ? '700' : '400',
            color:          '#111111',
            textAlign:      'left',
            backgroundColor:'',
            padding:        '4px 8px',
            borderRadius:   0,
        }
    };

    pushUndo();
    state.blocks.push(block);
    renderBlock(block);
    selectBlock(block.id);
    markDirty();
    _dragField = null;
});

/* ── Block drag (move) ───────────────────────────────────────────────────── */
function attachBlockEvents(el, block) {
    // Click to select
    el.addEventListener('mousedown', function(e) {
        if (e.target.classList.contains('rxd-resize-handle')) return;
        e.stopPropagation();
        selectBlock(block.id);

        var startX = e.clientX;
        var startY = e.clientY;
        var origX  = block.x;
        var origY  = block.y;

        function onMove(ev) {
            isDragging = true;
            var dx = (ev.clientX - startX) / zoom;
            var dy = (ev.clientY - startY) / zoom;
            block.x = Math.max(0, Math.min(CANVAS_W - block.w, snapToGrid(origX + dx)));
            block.y = Math.max(0, snapToGrid(origY + dy));
            el.style.left = block.x + 'px';
            el.style.top  = block.y + 'px';
            updateStylePanelPosition();
        }
        function onUp() {
            if (isDragging) { markDirty(); }
            isDragging = false;
            document.removeEventListener('mousemove', onMove);
            document.removeEventListener('mouseup', onUp);
        }
        document.addEventListener('mousemove', onMove);
        document.addEventListener('mouseup', onUp);
    });

    // Resize handles
    el.querySelectorAll('.rxd-resize-handle').forEach(function(handle) {
        handle.addEventListener('mousedown', function(e) {
            e.stopPropagation();
            e.preventDefault();
            var edge    = handle.dataset.edge;
            var startX  = e.clientX;
            var startY  = e.clientY;
            var origX   = block.x;
            var origY   = block.y;
            var origW   = block.w;
            var origH   = block.h;

            function onMove(ev) {
                var dx = (ev.clientX - startX) / zoom;
                var dy = (ev.clientY - startY) / zoom;
                var newX = origX, newY = origY, newW = origW, newH = origH;

                if (edge.includes('e')) { newW = Math.max(30, origW + dx); }
                if (edge.includes('w')) { newW = Math.max(30, origW - dx); newX = origX + (origW - newW); }
                if (edge.includes('s')) { newH = Math.max(10, origH + dy); }
                if (edge.includes('n')) { newH = Math.max(10, origH - dy); newY = origY + (origH - newH); }

                block.x = snapToGrid(newX); block.y = snapToGrid(newY);
                block.w = snapToGrid(newW); block.h = snapToGrid(newH);

                el.style.left      = block.x + 'px';
                el.style.top       = block.y + 'px';
                el.style.width     = block.w + 'px';
                el.style.height    = block.h + 'px';
                el.style.minHeight = block.h + 'px';
                updateStylePanelPosition();
            }
            function onUp() {
                markDirty();
                document.removeEventListener('mousemove', onMove);
                document.removeEventListener('mouseup', onUp);
            }
            document.addEventListener('mousemove', onMove);
            document.addEventListener('mouseup', onUp);
        });
    });
}

/* ── Click outside canvas to deselect ───────────────────────────────────── */
document.getElementById('rxdCanvasScroll').addEventListener('mousedown', function(e) {
    if (e.target === this || e.target.id === 'rxdCanvas' || e.target.id === 'rxdGrid') {
        selectBlock(null);
    }
});

/* ── Selection & style panel ─────────────────────────────────────────────── */
function selectBlock(id) {
    // Deselect all
    canvas.querySelectorAll('.rxd-block').forEach(function(el) { el.classList.remove('selected'); });
    selectedId = id;
    if (!id) {
        document.getElementById('rxdStyleEmpty').style.display    = '';
        document.getElementById('rxdStyleControls').style.display = 'none';
        return;
    }
    var el    = document.getElementById('rxdB-' + id);
    var block = getBlock(id);
    if (!el || !block) return;
    el.classList.add('selected');

    // Switch to style tab
    document.querySelectorAll('.rxd-sidebar-tab').forEach(function(b) { b.classList.remove('active'); });
    document.querySelectorAll('.rxd-sidebar-panel').forEach(function(p) { p.classList.remove('active'); });
    var styleTab = document.querySelector('.rxd-sidebar-tab[data-panel=style]');
    if (styleTab) styleTab.classList.add('active');
    document.getElementById('rxdPanelStyle').classList.add('active');

    document.getElementById('rxdStyleEmpty').style.display    = 'none';
    document.getElementById('rxdStyleControls').style.display = '';

    populateStylePanel(block);
}

function populateStylePanel(block) {
    var s = block.style || {};
    setV('sContent',      block.content || block.label || '');
    setV('sFontSize',     s.fontSize    || 14);
    setV('sFontWeight',   s.fontWeight  || '400');
    setV('sTextAlign',    s.textAlign   || 'left');
    setV('sLineHeight',   s.lineHeight  || '');
    setV('sPadding',      s.padding     || '');
    setV('sBorderRadius', s.borderRadius|| 0);
    setV('sBorder',       s.border      || '');
    setV('sBoxShadow',    s.boxShadow   || '');
    setV('sBgImage',      s.backgroundImage || '');
    setV('sPosX',  block.x); setV('sPosY', block.y);
    setV('sPosW',  block.w); setV('sPosH', block.h);
    setV('sZIndex', block.zIndex || 1);

    // Handle colour tokens
    var color = s.color || '#111111';
    if (color === '{{primary}}' || color === '{{secondary}}') {
        setV('sColorToken', color);
        document.getElementById('sColor').value = '#111111';
    } else {
        setV('sColorToken', '');
        document.getElementById('sColor').value = color || '#111111';
    }

    var bgColor = s.backgroundColor || '';
    if (bgColor === '{{primary}}' || bgColor === '{{secondary}}' || bgColor === 'transparent') {
        setV('sBgToken', bgColor);
        document.getElementById('sBgColor').value = '#ffffff';
    } else {
        setV('sBgToken', '');
        document.getElementById('sBgColor').value = bgColor || '#ffffff';
    }
}

function updateStylePanelPosition() {
    var block = getBlock(selectedId);
    if (!block) return;
    setV('sPosX', block.x); setV('sPosY', block.y);
    setV('sPosW', block.w); setV('sPosH', block.h);
}

function setV(id, val) { var el = document.getElementById(id); if (el) el.value = val; }
function getV(id) { var el = document.getElementById(id); return el ? el.value : ''; }

/* ── Style panel change handlers ─────────────────────────────────────────── */
function onStyleChange() {
    var block = getBlock(selectedId);
    if (!block) return;

    block.content = getV('sContent');
    block.zIndex  = parseInt(getV('sZIndex'), 10) || 1;

    var s = block.style || {};

    s.fontSize       = parseInt(getV('sFontSize'), 10)    || 14;
    s.fontWeight     = getV('sFontWeight')                || '400';
    s.textAlign      = getV('sTextAlign')                 || 'left';
    s.lineHeight     = getV('sLineHeight');
    s.padding        = getV('sPadding');
    s.borderRadius   = parseInt(getV('sBorderRadius'), 10)|| 0;
    s.border         = getV('sBorder');
    s.boxShadow      = getV('sBoxShadow');
    s.backgroundImage= getV('sBgImage');

    // Colour
    var colorToken = getV('sColorToken');
    s.color = colorToken || getV('sColor');

    // Background colour
    var bgToken = getV('sBgToken');
    s.backgroundColor = bgToken || (getV('sBgColor') !== '#ffffff' || bgToken === '' ? getV('sBgColor') : '');

    block.style = s;

    // Position / size from inputs
    var nx = parseInt(getV('sPosX'), 10); if (!isNaN(nx)) block.x = nx;
    var ny = parseInt(getV('sPosY'), 10); if (!isNaN(ny)) block.y = ny;
    var nw = parseInt(getV('sPosW'), 10); if (!isNaN(nw) && nw > 10) block.w = nw;
    var nh = parseInt(getV('sPosH'), 10); if (!isNaN(nh) && nh > 5)  block.h = nh;

    // Update DOM element
    var el = document.getElementById('rxdB-' + block.id);
    if (el) {
        applyBlockStyles(el, block);
        el.style.zIndex = block.zIndex;
        // Update inner content for text/heading blocks
        var inner = el.querySelector('.rxd-block-inner');
        if (inner) { inner.textContent = block.content || block.label || ''; }
    }

    markDirty();
}

// Wire up all style inputs
['sContent','sFontSize','sFontWeight','sTextAlign','sLineHeight','sPadding',
 'sBorderRadius','sBorder','sBoxShadow','sBgImage','sPosX','sPosY','sPosW','sPosH','sZIndex'
].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) {
        el.addEventListener('input',  onStyleChange);
        el.addEventListener('change', onStyleChange);
    }
});
['sColor','sBgColor','sColorToken','sBgToken'].forEach(function(id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('change', onStyleChange);
});

/* ── Delete / duplicate ─────────────────────────────────────────────────── */
window.rxdDeleteSelected = function() {
    if (!selectedId) return;
    pushUndo();
    state.blocks = state.blocks.filter(function(b) { return b.id !== selectedId; });
    var el = document.getElementById('rxdB-' + selectedId);
    if (el) el.remove();
    selectBlock(null);
    markDirty();
};
window.rxdDuplicateSelected = function() {
    var block = getBlock(selectedId);
    if (!block) return;
    pushUndo();
    var copy = JSON.parse(JSON.stringify(block));
    copy.id = genId();
    copy.x  = (copy.x || 0) + 20;
    copy.y  = (copy.y || 0) + 20;
    state.blocks.push(copy);
    renderBlock(copy);
    selectBlock(copy.id);
    markDirty();
};

/* ── Undo / Redo ────────────────────────────────────────────────────────── */
function pushUndo() {
    undoStack.push(JSON.stringify(state.blocks));
    if (undoStack.length > 50) undoStack.shift();
    redoStack = [];
}
window.rxdUndo = function() {
    if (!undoStack.length) return;
    redoStack.push(JSON.stringify(state.blocks));
    state.blocks = JSON.parse(undoStack.pop());
    selectBlock(null);
    renderAllBlocks();
};
window.rxdRedo = function() {
    if (!redoStack.length) return;
    undoStack.push(JSON.stringify(state.blocks));
    state.blocks = JSON.parse(redoStack.pop());
    selectBlock(null);
    renderAllBlocks();
};
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && !e.shiftKey && e.key === 'z') { e.preventDefault(); rxdUndo(); }
    if ((e.ctrlKey || e.metaKey) && (e.key === 'y' || (e.shiftKey && e.key === 'z'))) { e.preventDefault(); rxdRedo(); }
    if ((e.ctrlKey || e.metaKey) && e.key === 's') { e.preventDefault(); rxdSave(); }
    if (e.key === 'Delete' || e.key === 'Backspace') {
        // Only delete if not typing in an input
        var tag = document.activeElement && document.activeElement.tagName;
        if (tag !== 'INPUT' && tag !== 'TEXTAREA' && tag !== 'SELECT') { rxdDeleteSelected(); }
    }
});

/* ── Dirty / save ────────────────────────────────────────────────────────── */
function markDirty() {
    saveStatus.textContent = 'Unsaved changes…';
    saveStatus.className   = 'saving';
}

window.rxdSave = function() {
    var key  = (document.getElementById('rxdKey').value || '').trim();
    var name = (document.getElementById('rxdName').value || '').trim();
    if (!name) { alert('Please enter a template name.'); return; }
    if (!templateId && !key) { alert('Please enter a template key (slug).'); return; }
    if (!templateId && !/^[a-z0-9\-]+$/.test(key)) {
        alert('Key must contain only lowercase letters, digits, and hyphens.'); return;
    }

    var design = {
        version:       1,
        canvas:        state.canvas,
        blocks:        state.blocks,
        colorVariants: state.colorVariants,
    };

    var meta = {
        key:         key,
        name:        name,
        category:    document.getElementById('rxdCategory').value,
        display_bg:  document.getElementById('rxdBg').value,
        display_pri: state.colorVariants.length > 0 ? state.colorVariants[0].primary : document.getElementById('rxdPri').value,
    };

    saveStatus.textContent = 'Saving…';
    saveStatus.className   = 'saving';

    fetch('/admin/projects/resumex/designer/save', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ _token: csrfToken, id: templateId, meta: meta, design: design }),
    })
    .then(function(r) { return r.json(); })
    .then(function(d) {
        if (d.success) {
            saveStatus.textContent = 'Saved ✓';
            saveStatus.className   = 'saved';
            setTimeout(function() { saveStatus.textContent = ''; saveStatus.className = ''; }, 3000);
            if (!templateId && d.id) {
                templateId = d.id;
                // Update URL without reload
                if (history.pushState) {
                    history.pushState(null, '', '/admin/projects/resumex/designer/' + d.id);
                }
                // Make key read-only now
                var keyInp = document.getElementById('rxdKey');
                if (keyInp) { keyInp.readOnly = true; keyInp.style.opacity = '.6'; }
            }
        } else {
            saveStatus.textContent = 'Error: ' + (d.error || 'Save failed');
            saveStatus.className   = 'error';
        }
    })
    .catch(function(err) {
        saveStatus.textContent = 'Network error';
        saveStatus.className   = 'error';
    });
};

/* ── Helpers ─────────────────────────────────────────────────────────────── */
function escHtml(s) {
    return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function escAttr(s) { return String(s || '').replace(/"/g,'&quot;'); }

/* ── Init ────────────────────────────────────────────────────────────────── */
renderAllBlocks();
renderVariants();
// Apply saved canvas bg/font
applyCanvasStyles();
// Sync accent colour picker with first variant if present
if (state.colorVariants.length > 0) {
    document.getElementById('rxdPri').value = state.colorVariants[0].primary || '#007bff';
}
document.getElementById('rxdPri').addEventListener('input', function() {
    if (state.colorVariants.length === 0) {
        state.colorVariants.push({ label: 'Default', primary: this.value, secondary: '#6f42c1' });
        renderVariants();
    } else {
        state.colorVariants[0].primary = this.value;
        renderVariants();
    }
    markDirty();
});

}());
</script>

<?php View::endSection(); ?>
