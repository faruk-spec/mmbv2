<?php use Core\View; use Core\Helpers; use Core\Security; ?>
<?php View::extend('main'); ?>

<?php View::section('styles'); ?>
<style>.main { padding: 0 !important; }</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<style>
    /* === Layout === */
    .fx-layout{display:flex;min-height:calc(100vh - 70px);}
    /* === Sidebar === */
    .fx-sidebar{width:220px;flex-shrink:0;background:var(--bg-card);border-right:1px solid var(--border-color);display:flex;flex-direction:column;padding:24px 0 20px;position:sticky;top:0;height:calc(100vh - 70px);overflow-y:auto;}
    .fx-sidebar-logo{display:flex;align-items:center;gap:10px;padding:0 16px 18px;border-bottom:1px solid var(--border-color);margin-bottom:10px;}
    .fx-sidebar-logo-icon{width:32px;height:32px;border-radius:7px;background:linear-gradient(135deg,var(--cyan),var(--purple));display:flex;align-items:center;justify-content:center;font-weight:800;color:#06060a;font-size:.85rem;flex-shrink:0;}
    .fx-sidebar-logo-text{font-size:1rem;font-weight:800;background:linear-gradient(135deg,var(--cyan),var(--purple));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
    .fx-nav-section{padding:2px 8px;margin-bottom:2px;}
    .fx-nav-title{font-size:.65rem;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:var(--text-secondary);padding:7px 8px 3px;opacity:.6;}
    .fx-nav-link{display:flex;align-items:center;gap:9px;padding:7px 9px;border-radius:7px;color:var(--text-secondary);text-decoration:none;font-size:.845rem;font-weight:500;transition:background .15s,color .15s;position:relative;}
    .fx-nav-link:hover{background:rgba(0,240,255,.07);color:var(--text-primary);text-decoration:none;}
    .fx-nav-link.active{background:rgba(0,240,255,.1);color:var(--cyan);}
    .fx-nav-link.active::before{content:'';position:absolute;left:0;top:50%;transform:translateY(-50%);width:3px;height:60%;background:var(--cyan);border-radius:0 3px 3px 0;}
    .fx-nav-link i{width:16px;flex-shrink:0;text-align:center;opacity:.75;}
    .fx-nav-link.active i{opacity:1;}
    /* === Builder main area === */
    .fx-builder-main{flex:1;min-width:0;display:flex;flex-direction:column;height:calc(100vh - 70px);overflow:hidden;}
    /* === Builder toolbar === */
    .fx-toolbar{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;background:var(--bg-card);border-bottom:1px solid var(--border-color);gap:12px;flex-wrap:wrap;}
    .fx-toolbar-title{font-weight:700;font-size:.95rem;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .fx-toolbar-actions{display:flex;gap:8px;align-items:center;flex-shrink:0;}
    .fx-btn{display:inline-flex;align-items:center;gap:6px;padding:7px 14px;border-radius:8px;font-size:.8rem;font-weight:600;cursor:pointer;text-decoration:none;border:1px solid transparent;transition:all .15s;line-height:1;}
    .fx-btn-primary{background:linear-gradient(135deg,var(--cyan),var(--purple));color:#06060a;border:none;box-shadow:0 3px 14px rgba(0,240,255,.3);}
    .fx-btn-primary:hover{transform:translateY(-1px);box-shadow:0 6px 22px rgba(0,240,255,.45);color:#06060a;text-decoration:none;}
    .fx-btn-secondary{background:var(--bg-secondary);border-color:var(--border-color);color:var(--text-secondary);}
    .fx-btn-secondary:hover{background:rgba(0,240,255,.07);border-color:rgba(0,240,255,.25);color:var(--text-primary);text-decoration:none;}
    .fx-btn-danger{background:rgba(255,107,107,.08);border-color:rgba(255,107,107,.2);color:var(--red);}
    .fx-btn-danger:hover{background:rgba(255,107,107,.18);text-decoration:none;color:var(--red);}
    /* === Builder body (3 columns) === */
    .fx-builder-body{display:flex;flex:1;overflow:hidden;}
    /* === Left panel === */
    .fx-left-panel{width:230px;flex-shrink:0;background:var(--bg-secondary);border-right:1px solid var(--border-color);overflow-y:auto;padding:16px 12px;}
    .fx-panel-title{font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-secondary);margin-bottom:10px;padding-left:4px;}
    /* Form settings inputs */
    .fx-field-group{margin-bottom:14px;}
    .fx-label{font-size:.78rem;color:var(--text-secondary);display:block;margin-bottom:4px;}
    .fx-input,.fx-select,.fx-textarea{width:100%;padding:7px 10px;background:var(--bg-card);border:1px solid var(--border-color);border-radius:6px;color:var(--text-primary);font-size:.82rem;transition:border-color .15s;}
    .fx-input:focus,.fx-select:focus,.fx-textarea:focus{outline:none;border-color:var(--cyan);}
    .fx-textarea{resize:vertical;min-height:52px;}
    /* Palette items */
    .fx-palette-item{display:flex;align-items:center;gap:9px;padding:9px 10px;background:var(--bg-card);border:1px solid var(--border-color);border-radius:8px;cursor:grab;user-select:none;transition:all .18s;margin-bottom:7px;font-size:.82rem;font-weight:500;}
    .fx-palette-item:active{cursor:grabbing;}
    .fx-palette-item:hover{border-color:var(--cyan);color:var(--cyan);}
    .fx-palette-item i{width:16px;text-align:center;font-size:.8rem;opacity:.7;}
    .fx-palette-item:hover i{opacity:1;}
    /* === Canvas === */
    .fx-canvas-wrap{flex:1;min-width:0;overflow-y:auto;padding:20px;}
    #fxCanvas{min-height:420px;background:var(--bg-card);border:2px dashed var(--border-color);border-radius:12px;padding:20px;transition:border-color .2s;}
    #fxCanvas.drag-over{border-color:var(--cyan);background:rgba(0,240,255,.03);}
    .fx-canvas-placeholder{text-align:center;padding:60px 20px;color:var(--text-secondary);pointer-events:none;}
    .fx-canvas-placeholder i{font-size:2.5rem;opacity:.2;display:block;margin-bottom:12px;}
    /* Canvas field card */
    .fx-field-card{background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:9px;padding:14px 14px 10px;margin-bottom:10px;cursor:default;position:relative;transition:border-color .15s;}
    .fx-field-card.selected{border-color:var(--cyan);box-shadow:0 0 0 2px rgba(0,240,255,.12);}
    .fx-field-card-header{display:flex;align-items:center;gap:8px;margin-bottom:8px;}
    .fx-field-card-drag{cursor:grab;color:var(--text-secondary);opacity:.4;padding:0 4px;}
    .fx-field-card-drag:hover{opacity:.8;}
    .fx-field-card-type{font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--cyan);background:rgba(0,240,255,.1);padding:2px 8px;border-radius:4px;}
    .fx-field-card-label{font-size:.875rem;font-weight:600;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
    .fx-field-card-actions{display:flex;gap:4px;margin-left:auto;}
    .fx-field-card-btn{background:none;border:none;cursor:pointer;padding:3px 6px;border-radius:5px;font-size:.78rem;transition:background .15s,color .15s;color:var(--text-secondary);}
    .fx-field-card-btn:hover{background:rgba(255,255,255,.06);color:var(--text-primary);}
    .fx-field-card-btn.del:hover{color:var(--red);}
    .fx-field-preview{font-size:.8rem;color:var(--text-secondary);}
    .fx-field-preview input,.fx-field-preview textarea,.fx-field-preview select{pointer-events:none;width:100%;padding:5px 8px;background:var(--bg-card);border:1px solid var(--border-color);border-radius:5px;color:var(--text-primary);font-size:.8rem;}
    /* === Right panel (field settings) === */
    .fx-right-panel{width:260px;flex-shrink:0;background:var(--bg-secondary);border-left:1px solid var(--border-color);overflow-y:auto;padding:16px 14px;}
    .fx-right-empty{display:flex;flex-direction:column;align-items:center;justify-content:center;height:200px;color:var(--text-secondary);font-size:.82rem;text-align:center;}
    .fx-right-empty i{font-size:2rem;opacity:.2;margin-bottom:10px;}
    /* === Public URL bar === */
    .fx-url-bar{margin-top:16px;padding:11px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;font-size:.8rem;color:var(--text-secondary);display:flex;align-items:center;gap:8px;}
    .fx-url-bar a{color:var(--cyan);text-decoration:none;}
    .fx-url-bar a:hover{text-decoration:underline;}
    /* Mobile */
    .fx-sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99;}
    .fx-sidebar-toggle{display:none;position:fixed;bottom:24px;right:20px;z-index:100;width:48px;height:48px;border-radius:50%;background:linear-gradient(135deg,var(--cyan),var(--purple));border:none;cursor:pointer;align-items:center;justify-content:center;box-shadow:0 4px 18px rgba(0,240,255,.4);color:#06060a;font-size:1.1rem;}
    @media(max-width:1100px){.fx-right-panel{display:none;}}
    @media(max-width:900px){
        .fx-sidebar{position:fixed;left:-240px;top:0;height:100vh;z-index:100;width:220px;transition:left .25s;padding-top:70px;}
        .fx-sidebar.open{left:0;}
        .fx-sidebar-overlay{display:block;opacity:0;pointer-events:none;transition:opacity .25s;}
        .fx-sidebar-overlay.active{opacity:1;pointer-events:all;}
        .fx-sidebar-toggle{display:flex;}
        .fx-left-panel{width:200px;}
    }
    @media(max-width:680px){
        .fx-left-panel{display:none;}
        .fx-builder-body{flex-direction:column;}
        .fx-canvas-wrap{padding:14px;}
    }
</style>

<div class="fx-layout">
    <!-- Sidebar -->
    <aside class="fx-sidebar" id="fxSidebar">
        <div class="fx-sidebar-logo">
            <div class="fx-sidebar-logo-icon"><i class="fas fa-wpforms" style="-webkit-text-fill-color:#06060a;"></i></div>
            <span class="fx-sidebar-logo-text">FormX</span>
        </div>
        <div class="fx-nav-section">
            <div class="fx-nav-title">Workspace</div>
            <a href="/projects/formx" class="fx-nav-link"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
            <a href="/projects/formx/create" class="fx-nav-link <?= !$form ? 'active' : '' ?>"><i class="fas fa-plus-circle"></i><span>New Form</span></a>
            <a href="/projects/formx/forms" class="fx-nav-link <?= $form ? 'active' : '' ?>"><i class="fas fa-list"></i><span>My Forms</span></a>
        </div>
        <?php if (!empty($sidebarForms)): ?>
        <div class="fx-nav-section">
            <div class="fx-nav-title">Recent Forms</div>
            <?php foreach ($sidebarForms as $sf): ?>
            <a href="/projects/formx/<?= (int)$sf['id'] ?>/edit"
               class="fx-nav-link <?= $form && (int)$form['id'] === (int)$sf['id'] ? 'active' : '' ?>"
               title="<?= htmlspecialchars($sf['title']) ?>">
                <i class="fas fa-file-alt" style="font-size:.75rem;"></i>
                <span style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($sf['title']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </aside>

    <!-- Builder -->
    <div class="fx-builder-main">
        <!-- Toolbar -->
        <div class="fx-toolbar">
            <a href="/projects/formx/forms" class="fx-btn fx-btn-secondary" style="flex-shrink:0;">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div class="fx-toolbar-title"><?= View::e($title) ?></div>
            <div class="fx-toolbar-actions">
                <?php if ($form): ?>
                <a href="/projects/formx/<?= $form['id'] ?>/submissions" class="fx-btn fx-btn-secondary">
                    <i class="fas fa-inbox"></i> <span>Submissions</span>
                </a>
                <a href="/projects/formx/<?= $form['id'] ?>/analytics" class="fx-btn fx-btn-secondary" title="Analytics">
                    <i class="fas fa-chart-bar"></i> <span>Analytics</span>
                </a>
                <a href="/projects/formx/<?= $form['id'] ?>/versions" class="fx-btn fx-btn-secondary" title="Versions">
                    <i class="fas fa-history"></i>
                </a>
                <?php if ($form['status'] === 'active'): ?>
                <a href="/forms/<?= View::e($form['slug']) ?>" target="_blank" class="fx-btn fx-btn-secondary">
                    <i class="fas fa-external-link-alt"></i>
                </a>
                <?php endif; ?>
                <button type="button" class="fx-btn fx-btn-secondary" onclick="openShareModal()" title="Share">
                    <i class="fas fa-share-alt"></i> Share
                </button>
                <?php endif; ?>
                <button type="button" class="fx-btn fx-btn-primary" onclick="submitBuilder()">
                    <i class="fas fa-save"></i> Save
                </button>
            </div>
        </div>

        <!-- Flash -->
        <?php if (Helpers::hasFlash('success') || Helpers::hasFlash('error')): ?>
        <div style="padding:10px 20px;">
        <?php if (Helpers::hasFlash('success')): ?>
        <div style="background:rgba(0,255,136,.1);border:1px solid var(--green);color:var(--green);padding:9px 14px;border-radius:7px;font-size:.85rem;">
            <i class="fas fa-check-circle"></i> <?= View::e(Helpers::getFlash('success')) ?>
        </div>
        <?php endif; ?>
        <?php if (Helpers::hasFlash('error')): ?>
        <div style="background:rgba(255,107,107,.1);border:1px solid var(--red);color:var(--red);padding:9px 14px;border-radius:7px;font-size:.85rem;">
            <i class="fas fa-exclamation-circle"></i> <?= View::e(Helpers::getFlash('error')) ?>
        </div>
        <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Builder body -->
        <div class="fx-builder-body">

            <!-- Left panel: settings + palette -->
            <div class="fx-left-panel">
                <!-- Form Settings -->
                <div class="fx-panel-title">Form Settings</div>
                <div class="fx-field-group">
                    <label class="fx-label">Title <span style="color:var(--red);">*</span></label>
                    <input type="text" id="settingTitle" class="fx-input"
                           value="<?= View::e($form['title'] ?? '') ?>"
                           placeholder="My Form" required>
                </div>
                <div class="fx-field-group">
                    <label class="fx-label">Description</label>
                    <textarea id="settingDesc" class="fx-textarea fx-input"
                              placeholder="Short description…"><?= View::e($form['description'] ?? '') ?></textarea>
                </div>
                <div class="fx-field-group">
                    <label class="fx-label">Status</label>
                    <select id="settingStatus" class="fx-select fx-input">
                        <option value="draft"    <?= ($form['status'] ?? 'draft') === 'draft'    ? 'selected' : '' ?>>Draft</option>
                        <option value="active"   <?= ($form['status'] ?? '') === 'active'   ? 'selected' : '' ?>>Active (Public)</option>
                        <option value="inactive" <?= ($form['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
                <div class="fx-field-group">
                    <label class="fx-label">Success Message</label>
                    <textarea id="settingSuccessMsg" class="fx-textarea fx-input"
                              placeholder="Thank you for your submission!"><?= View::e($form['settings']['success_message'] ?? '') ?></textarea>
                </div>
                <div class="fx-field-group">
                    <label class="fx-label">Redirect URL</label>
                    <input type="text" id="settingRedirect" class="fx-input"
                           value="<?= View::e($form['settings']['redirect_url'] ?? '') ?>"
                           placeholder="https://…">
                </div>
                <div class="fx-field-group">
                    <label class="fx-label">Notify Email</label>
                    <input type="email" id="settingEmail" class="fx-input"
                           value="<?= View::e($form['settings']['notify_email'] ?? '') ?>"
                           placeholder="you@example.com">
                </div>
                <div class="fx-field-group">
                    <label class="fx-label">Expires At <span style="color:var(--text-secondary);font-weight:400;">(optional)</span></label>
                    <input type="datetime-local" id="settingExpiresAt" class="fx-input"
                           value="<?= htmlspecialchars(!empty($form['expires_at']) ? date('Y-m-d\TH:i', strtotime($form['expires_at'])) : '') ?>">
                    <p style="font-size:.72rem;color:var(--text-secondary);margin-top:3px;">Leave blank to never expire.</p>
                </div>
                <div class="fx-field-group">
                    <label class="fx-label">Access Control</label>
                    <select id="settingAccessMode" class="fx-select fx-input">
                        <option value="public"   <?= ($form['settings']['access_mode'] ?? 'public') === 'public'         ? 'selected' : '' ?>>Public (anyone)</option>
                        <option value="password" <?= ($form['settings']['access_mode'] ?? '') === 'password'             ? 'selected' : '' ?>>Password Protected</option>
                        <option value="login"    <?= ($form['settings']['access_mode'] ?? '') === 'login'                ? 'selected' : '' ?>>Login Required</option>
                    </select>
                </div>
                <div class="fx-field-group" id="settingPasswordGroup" style="display:<?= ($form['settings']['access_mode'] ?? '') === 'password' ? '' : 'none' ?>;">
                    <label class="fx-label">Access Password</label>
                    <input type="password" id="settingAccessPassword" class="fx-input"
                           placeholder="<?= !empty($form['settings']['access_password']) ? '(password set – enter new to change)' : 'Enter password…' ?>">
                    <p style="font-size:.72rem;color:var(--text-secondary);margin-top:3px;"><i class="fas fa-info-circle"></i> Leave blank to keep existing password.</p>
                </div>
                <div class="fx-field-group">
                    <label class="fx-label">Max Total Submissions <span style="color:var(--text-secondary);font-weight:400;">(0 = unlimited)</span></label>
                    <input type="number" id="settingMaxSubmissions" class="fx-input" min="0" step="1"
                           value="<?= (int)($form['settings']['max_submissions'] ?? 0) ?>"
                           placeholder="0">
                </div>
                <div class="fx-field-group">
                    <label class="fx-label">Max Submissions Per IP Address <span style="color:var(--text-secondary);font-weight:400;">(0 = unlimited)</span></label>
                    <input type="number" id="settingMaxPerIP" class="fx-input" min="0" step="1"
                           value="<?= (int)($form['settings']['max_submissions_per_ip'] ?? 0) ?>"
                           placeholder="0">
                    <p style="font-size:.72rem;color:var(--text-secondary);margin-top:3px;">Limit per IP address.</p>
                </div>
                <div class="fx-field-group">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;">
                        <input type="checkbox" id="settingConfirmSubmit" style="accent-color:var(--cyan);"
                               <?= !empty($form['settings']['confirm_submit']) ? 'checked' : '' ?>>
                        <span class="fx-label" style="margin:0;">Show confirmation popup before submit</span>
                    </label>
                </div>

                <div style="border-top:1px solid var(--border-color);margin:16px 0 12px;"></div>
                <div class="fx-panel-title">Add Fields</div>
                <p style="font-size:.74rem;color:var(--text-secondary);margin-bottom:10px;">Drag onto canvas →</p>

                <?php
                $palette = [
                    ['text',     'fa-font',              'Text'],
                    ['textarea', 'fa-align-left',        'Textarea'],
                    ['email',    'fa-envelope',          'Email'],
                    ['phone',    'fa-phone',             'Phone'],
                    ['number',   'fa-hashtag',           'Number'],
                    ['url',      'fa-link',              'URL'],
                    ['date',     'fa-calendar',          'Date'],
                    ['time',     'fa-clock',             'Time'],
                    ['select',   'fa-caret-square-down', 'Dropdown'],
                    ['radio',    'fa-dot-circle',        'Radio'],
                    ['checkbox', 'fa-check-square',      'Checkboxes'],
                    ['file',     'fa-file-upload',       'File Upload'],
                    ['rating',   'fa-star',              'Rating'],
                    ['heading',  'fa-heading',           'Heading'],
                    ['paragraph','fa-paragraph',         'Paragraph'],
                    ['divider',  'fa-minus',             'Divider'],
                    ['hidden',   'fa-eye-slash',         'Hidden'],
                ];
                foreach ($palette as [$type, $icon, $label]): ?>
                <div class="fx-palette-item" draggable="true" data-field-type="<?= $type ?>">
                    <i class="fas <?= $icon ?>"></i><?= $label ?>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Canvas -->
            <div class="fx-canvas-wrap">
                <div id="fxCanvas"
                     ondragover="canvasDragOver(event)"
                     ondragleave="canvasDragLeave(event)"
                     ondrop="canvasDrop(event)">
                    <div id="fxPlaceholder" class="fx-canvas-placeholder">
                        <i class="fas fa-hand-pointer"></i>
                        <p style="font-size:.875rem;">Drag fields here to build your form</p>
                    </div>
                </div>

                <?php if ($form): ?>
                <div class="fx-url-bar">
                    <i class="fas fa-link" style="color:var(--cyan);"></i>
                    Public URL:
                    <a href="/forms/<?= View::e($form['slug']) ?>" target="_blank">/forms/<?= View::e($form['slug']) ?></a>
                    &nbsp;·&nbsp;
                    <a href="/projects/formx/<?= $form['id'] ?>/submissions">
                        <?= (int)($form['submissions_count'] ?? 0) ?> submission<?= $form['submissions_count'] != 1 ? 's' : '' ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Right panel: selected field settings -->
            <div class="fx-right-panel" id="fxFieldSettings">
                <div class="fx-right-empty" id="fxRightEmpty">
                    <i class="fas fa-mouse-pointer"></i>
                    <p>Click a field on the canvas to edit its settings</p>
                </div>
                <div id="fxRightForm" style="display:none;"></div>
            </div>

        </div>
    </div>
</div>

<!-- Hidden form for submission -->
<form id="builderForm" method="POST" action="<?= View::e($action) ?>" style="display:none;">
    <input type="hidden" name="_token"       value="<?= $csrfToken ?>">
    <input type="hidden" name="id"           value="<?= $form ? (int)$form['id'] : '' ?>">
    <input type="hidden" name="title"        id="hiddenTitle">
    <input type="hidden" name="description"  id="hiddenDesc">
    <input type="hidden" name="status"       id="hiddenStatus">
    <input type="hidden" name="expires_at"   id="hiddenExpiresAt">
    <input type="hidden" name="fields_json"  id="hiddenFields">
    <input type="hidden" name="settings_json" id="hiddenSettings">
</form>

<!-- ── Share Modal ─────────────────────────────────────────────────────── -->
<?php if ($form): ?>
<div id="fxShareModal" style="display:none;position:fixed;inset:0;z-index:200;align-items:center;justify-content:center;background:rgba(0,0,0,.6);backdrop-filter:blur(4px);">
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:14px;padding:28px;width:min(480px,92vw);box-shadow:0 20px 60px rgba(0,0,0,.5);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
            <h3 style="font-size:1rem;font-weight:700;margin:0;"><i class="fas fa-share-alt" style="color:var(--cyan);margin-right:8px;"></i>Share Form</h3>
            <button onclick="closeShareModal()" style="background:none;border:none;cursor:pointer;color:var(--text-secondary);font-size:1.2rem;padding:4px 8px;">&times;</button>
        </div>

        <!-- Form status notice -->
        <?php if ($form['status'] !== 'active'): ?>
        <div style="background:rgba(255,170,0,.1);border:1px solid rgba(255,170,0,.3);color:var(--orange);padding:10px 14px;border-radius:8px;font-size:.82rem;margin-bottom:16px;">
            <i class="fas fa-exclamation-triangle"></i> Form is currently <strong><?= htmlspecialchars($form['status']) ?></strong>. Set status to <strong>Active</strong> and save before sharing.
        </div>
        <?php endif; ?>

        <!-- Public link -->
        <div style="margin-bottom:18px;">
            <label style="font-size:.78rem;color:var(--text-secondary);display:block;margin-bottom:6px;">Public Link</label>
            <div style="display:flex;gap:8px;">
                <input id="shareUrlInput" type="text" readonly
                       value="<?= htmlspecialchars((isset($_SERVER['HTTP_HOST']) ? 'https://'.$_SERVER['HTTP_HOST'] : '') . '/forms/' . $form['slug']) ?>"
                       style="flex:1;padding:9px 12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.85rem;outline:none;">
                <button onclick="copyShareUrl()" id="copyShareBtn"
                        style="padding:9px 16px;background:rgba(0,240,255,.1);border:1px solid rgba(0,240,255,.25);border-radius:8px;color:var(--cyan);cursor:pointer;font-size:.82rem;font-weight:600;white-space:nowrap;">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
        </div>

        <!-- QR code placeholder -->
        <div style="margin-bottom:18px;text-align:center;">
            <div style="display:inline-block;padding:12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:10px;">
                <div id="qrCodePlaceholder" style="width:120px;height:120px;display:flex;align-items:center;justify-content:center;background:#fff;border-radius:6px;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=<?= urlencode((isset($_SERVER['HTTP_HOST']) ? 'https://'.$_SERVER['HTTP_HOST'] : '') . '/forms/' . $form['slug']) ?>"
                         alt="QR Code" style="width:120px;height:120px;border-radius:6px;" onerror="this.parentElement.innerHTML='<span style=\'font-size:.75rem;color:#666;\'>QR N/A</span>'">
                </div>
            </div>
            <p style="font-size:.75rem;color:var(--text-secondary);margin-top:6px;">Scan to open form</p>
        </div>

        <div style="display:flex;gap:10px;">
            <button onclick="saveShareSettings()" class="fx-btn fx-btn-secondary" style="flex:1;">
                <i class="fas fa-times"></i> Close
            </button>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="fx-sidebar-overlay" id="fxOverlay"></div>
<button class="fx-sidebar-toggle" id="fxToggle"><i class="fas fa-bars"></i></button>

<script>
(function() {

// ─── Sidebar toggle ───────────────────────────────────────────────────────────
const sidebar = document.getElementById('fxSidebar');
const overlay = document.getElementById('fxOverlay');
const toggle  = document.getElementById('fxToggle');
function openSidebar()  { sidebar.classList.add('open'); overlay.classList.add('active'); toggle.innerHTML='<i class="fas fa-times"></i>'; }
function closeSidebar() { sidebar.classList.remove('open'); overlay.classList.remove('active'); toggle.innerHTML='<i class="fas fa-bars"></i>'; }
toggle.addEventListener('click', () => sidebar.classList.contains('open') ? closeSidebar() : openSidebar());
overlay.addEventListener('click', closeSidebar);

// ─── State ────────────────────────────────────────────────────────────────────
let fields = (function() {
    var raw = <?= json_encode($form ? ($form['fields'] ?? []) : []) ?>;
    // Normalize: ensure options are always newline-separated strings (admin stores as arrays)
    return raw.map(function(f) {
        if (Array.isArray(f.options)) {
            f.options = f.options.join('\n');
        }
        return f;
    });
})();
let selectedIdx = null;

// ─── Render canvas ────────────────────────────────────────────────────────────
function renderCanvas() {
    const canvas = document.getElementById('fxCanvas');
    const ph     = document.getElementById('fxPlaceholder');

    // Remove existing field cards
    canvas.querySelectorAll('.fx-field-card').forEach(el => el.remove());

    if (fields.length === 0) {
        if (ph) ph.style.display = '';
        return;
    }
    if (ph) ph.style.display = 'none';

    fields.forEach((field, idx) => {
        const card = buildCard(field, idx);
        canvas.appendChild(card);
    });
}

function buildCard(field, idx) {
    const card = document.createElement('div');
    card.className = 'fx-field-card' + (selectedIdx === idx ? ' selected' : '');
    card.dataset.idx = idx;
    card.draggable = true;
    card.innerHTML = `
        <div class="fx-field-card-header">
            <span class="fx-field-card-drag"><i class="fas fa-grip-vertical"></i></span>
            <span class="fx-field-card-type">${escHtml(field.type)}</span>
            <span class="fx-field-card-label">${escHtml(field.label || 'Untitled')}</span>
            <div class="fx-field-card-actions">
                <button type="button" class="fx-field-card-btn" onclick="moveField(${idx},-1)" title="Move up"><i class="fas fa-chevron-up"></i></button>
                <button type="button" class="fx-field-card-btn" onclick="moveField(${idx},1)" title="Move down"><i class="fas fa-chevron-down"></i></button>
                <button type="button" class="fx-field-card-btn del" onclick="removeField(${idx})" title="Delete"><i class="fas fa-trash"></i></button>
            </div>
        </div>
        <div class="fx-field-preview">${fieldPreview(field)}</div>`;

    card.addEventListener('click', (e) => {
        if (e.target.closest('button')) return;
        selectField(idx);
    });

    // Drag-reorder
    card.addEventListener('dragstart', e => { e.dataTransfer.setData('reorder', idx); e.dataTransfer.effectAllowed = 'move'; });
    card.addEventListener('dragover',  e => { e.preventDefault(); card.style.borderColor = 'var(--cyan)'; });
    card.addEventListener('dragleave', e => { card.style.borderColor = ''; });
    card.addEventListener('drop', e => {
        e.preventDefault(); card.style.borderColor = '';
        const from = parseInt(e.dataTransfer.getData('reorder'));
        if (!isNaN(from) && from !== idx) {
            const moved = fields.splice(from, 1)[0];
            fields.splice(idx, 0, moved);
            if (selectedIdx === from) selectedIdx = idx;
            renderCanvas();
        }
    });

    return card;
}

function fieldPreview(f) {
    switch (f.type) {
        case 'text': case 'email': case 'phone': case 'number': case 'url': case 'date': case 'time':
            return `<input type="${f.type}" placeholder="${escHtml(f.placeholder||f.label||'')}" tabindex="-1">`;
        case 'textarea':
            return `<textarea placeholder="${escHtml(f.placeholder||f.label||'')}" tabindex="-1" style="height:52px;"></textarea>`;
        case 'select':
            const opts = (f.options || '').split('\n').map(o => `<option>${escHtml(o.trim())}</option>`).join('');
            return `<select tabindex="-1"><option value="">-- Select --</option>${opts}</select>`;
        case 'radio':
            return (f.options || 'Option 1\nOption 2').split('\n').slice(0,3).map(o =>
                `<label style="display:block;margin:2px 0;font-size:.78rem;"><input type="radio" disabled> ${escHtml(o.trim())}</label>`).join('');
        case 'checkbox':
            return (f.options || 'Option 1\nOption 2').split('\n').slice(0,3).map(o =>
                `<label style="display:block;margin:2px 0;font-size:.78rem;"><input type="checkbox" disabled> ${escHtml(o.trim())}</label>`).join('');
        case 'file':
            return `<input type="file" tabindex="-1" disabled style="font-size:.78rem;">`;
        case 'heading':
            return `<h3 style="margin:0;font-size:1rem;font-weight:700;">${escHtml(f.label||'Heading')}</h3>`;
        case 'paragraph':
            return `<p style="margin:0;font-size:.82rem;">${escHtml(f.content||f.label||'Paragraph text')}</p>`;
        case 'divider':
            return `<hr style="border-color:var(--border-color);">`;
        case 'hidden':
            return `<small style="opacity:.5;font-size:.7rem;"><i class="fas fa-eye-slash"></i> Hidden field: ${escHtml(f.name||'')}</small>`;
        case 'rating':
            return `<div>${'★'.repeat(5).split('').map((s,i)=>`<span style="color:${i<3?'var(--orange)':'var(--border-color)'};font-size:1.2rem;">${s}</span>`).join('')}</div>`;
        default:
            return `<small style="color:var(--text-secondary);font-size:.78rem;">${escHtml(f.type)}</small>`;
    }
}

// ─── Select field → right panel ──────────────────────────────────────────────
function selectField(idx) {
    selectedIdx = idx;
    renderCanvas();
    showFieldSettings(fields[idx], idx);
}

function showFieldSettings(field, idx) {
    document.getElementById('fxRightEmpty').style.display = 'none';
    const form = document.getElementById('fxRightForm');
    form.style.display = '';

    const needsOpts = ['select','radio','checkbox'].includes(field.type);
    const isContent = ['heading','paragraph','divider'].includes(field.type);

    form.innerHTML = `
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
            <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:var(--text-secondary);">Field Settings</div>
            <span style="font-size:.7rem;padding:2px 8px;border-radius:4px;background:rgba(0,240,255,.1);color:var(--cyan);">${escHtml(field.type)}</span>
        </div>
        ${!isContent ? `
        <div class="fx-field-group">
            <label class="fx-label">Label</label>
            <input class="fx-input" id="rpLabel" value="${escHtml(field.label||'')}">
        </div>
        <div class="fx-field-group">
            <label class="fx-label">Name (key)</label>
            <input class="fx-input" id="rpName" value="${escHtml(field.name||'')}" placeholder="auto">
        </div>
        <div class="fx-field-group">
            <label class="fx-label"><input type="checkbox" id="rpRequired" ${field.required?'checked':''}> Required</label>
        </div>
        ` : ''}
        ${['text','email','phone','number','url','textarea'].includes(field.type) ? `
        <div class="fx-field-group">
            <label class="fx-label">Placeholder</label>
            <input class="fx-input" id="rpPlaceholder" value="${escHtml(field.placeholder||'')}">
        </div>
        ` : ''}
        ${field.type === 'hidden' ? `
        <div class="fx-field-group">
            <label class="fx-label">Value</label>
            <input class="fx-input" id="rpValue" value="${escHtml(field.value||'')}">
        </div>
        ` : ''}
        ${needsOpts ? `
        <div class="fx-field-group">
            <label class="fx-label">Options <small>(one per line)</small></label>
            <textarea class="fx-textarea fx-input" id="rpOptions" style="min-height:80px;">${escHtml(field.options||'')}</textarea>
        </div>
        ` : ''}
        ${field.type === 'paragraph' ? `
        <div class="fx-field-group">
            <label class="fx-label">Content</label>
            <textarea class="fx-textarea fx-input" id="rpContent" style="min-height:60px;">${escHtml(field.content||'')}</textarea>
        </div>
        ` : ''}
        ${field.type === 'heading' ? `
        <div class="fx-field-group">
            <label class="fx-label">Text</label>
            <input class="fx-input" id="rpLabel" value="${escHtml(field.label||'')}">
        </div>
        ` : ''}
        <button type="button" class="fx-btn fx-btn-primary" style="width:100%;" onclick="saveFieldSettings(${idx})">
            <i class="fas fa-check"></i> Apply
        </button>
        <button type="button" class="fx-btn fx-btn-danger" style="width:100%;margin-top:8px;" onclick="removeField(${idx})">
            <i class="fas fa-trash"></i> Remove Field
        </button>`;
}

window.saveFieldSettings = function(idx) {
    const f = { ...fields[idx] };
    if (document.getElementById('rpLabel'))       f.label       = document.getElementById('rpLabel').value.trim();
    if (document.getElementById('rpName'))        f.name        = document.getElementById('rpName').value.trim() || slugify(f.label);
    if (document.getElementById('rpRequired'))    f.required    = document.getElementById('rpRequired').checked;
    if (document.getElementById('rpPlaceholder')) f.placeholder = document.getElementById('rpPlaceholder').value;
    if (document.getElementById('rpOptions'))     f.options     = document.getElementById('rpOptions').value;
    if (document.getElementById('rpContent'))     f.content     = document.getElementById('rpContent').value;
    if (document.getElementById('rpValue'))       f.value       = document.getElementById('rpValue').value;
    fields[idx] = f;
    renderCanvas();
    showFieldSettings(f, idx);
};

// ─── Canvas drag (from palette) ──────────────────────────────────────────────
document.querySelectorAll('.fx-palette-item').forEach(el => {
    el.addEventListener('dragstart', e => {
        e.dataTransfer.setData('newField', el.dataset.fieldType);
        e.dataTransfer.effectAllowed = 'copy';
    });
});

window.canvasDragOver  = (e) => { e.preventDefault(); document.getElementById('fxCanvas').classList.add('drag-over'); };
window.canvasDragLeave = (e) => { document.getElementById('fxCanvas').classList.remove('drag-over'); };
window.canvasDrop      = (e) => {
    e.preventDefault();
    document.getElementById('fxCanvas').classList.remove('drag-over');
    const type = e.dataTransfer.getData('newField');
    if (!type) return;
    addField(type);
};

function addField(type) {
    const label = type.charAt(0).toUpperCase() + type.slice(1).replace(/([A-Z])/g,' $1');
    const field = { type, label, name: slugify(label) + '_' + (fields.length+1), required: false };
    if (['select','radio','checkbox'].includes(type)) field.options = 'Option 1\nOption 2\nOption 3';
    if (type === 'paragraph') field.content = 'Enter your paragraph text here.';
    fields.push(field);
    selectedIdx = fields.length - 1;
    renderCanvas();
    showFieldSettings(fields[selectedIdx], selectedIdx);
}

window.moveField = function(idx, dir) {
    const newIdx = idx + dir;
    if (newIdx < 0 || newIdx >= fields.length) return;
    [fields[idx], fields[newIdx]] = [fields[newIdx], fields[idx]];
    if (selectedIdx === idx) selectedIdx = newIdx;
    renderCanvas();
};

window.removeField = function(idx) {
    fields.splice(idx, 1);
    selectedIdx = null;
    document.getElementById('fxRightEmpty').style.display = '';
    document.getElementById('fxRightForm').style.display = 'none';
    renderCanvas();
};

// ─── Submit ───────────────────────────────────────────────────────────────────
window.submitBuilder = function() {
    const titleEl = document.getElementById('settingTitle');
    if (!titleEl.value.trim()) { titleEl.focus(); titleEl.style.borderColor = 'var(--red)'; return; }

    document.getElementById('hiddenTitle').value    = titleEl.value.trim();
    document.getElementById('hiddenDesc').value     = document.getElementById('settingDesc').value;
    document.getElementById('hiddenStatus').value   = document.getElementById('settingStatus').value;
    document.getElementById('hiddenExpiresAt').value = document.getElementById('settingExpiresAt') ? document.getElementById('settingExpiresAt').value : '';
    // Normalize: convert options strings back to arrays for consistent DB storage
    const normalizedFields = fields.map(function(f) {
        var nf = Object.assign({}, f);
        if (typeof nf.options === 'string') {
            nf.options = nf.options.split('\n').map(s => s.trim()).filter(Boolean);
        }
        return nf;
    });
    document.getElementById('hiddenFields').value   = JSON.stringify(normalizedFields);
    // Get access password: use new value if typed, otherwise preserve existing hash
    const accessModeEl = document.getElementById('settingAccessMode');
    const accessPwEl   = document.getElementById('settingAccessPassword');
    // Use a boolean flag to avoid sending the existing hash to the client
    const hasExistingPassword = <?= json_encode(!empty($form['settings']['access_password'])) ?>;
    document.getElementById('hiddenSettings').value = JSON.stringify({
        success_message:        document.getElementById('settingSuccessMsg').value,
        redirect_url:           document.getElementById('settingRedirect').value,
        notify_email:           document.getElementById('settingEmail').value,
        access_mode:            accessModeEl ? accessModeEl.value : 'public',
        // Send new password if typed; send empty string to signal "keep existing" on server
        access_password_new:    (accessPwEl && accessPwEl.value) ? accessPwEl.value : '',
        access_password_keep:   (accessPwEl && accessPwEl.value) ? false : hasExistingPassword,
        max_submissions:        parseInt(document.getElementById('settingMaxSubmissions')?.value, 10) || 0,
        max_submissions_per_ip: parseInt(document.getElementById('settingMaxPerIP')?.value, 10) || 0,
        confirm_submit:         !!(document.getElementById('settingConfirmSubmit')?.checked),
    });
    document.getElementById('builderForm').submit();
};

// ─── Utils ────────────────────────────────────────────────────────────────────
function escHtml(s) {
    return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}
function slugify(s) {
    return String(s||'').toLowerCase().replace(/[^a-z0-9]+/g,'_').replace(/^_+|_+$/g,'');
}

// ─── Init ────────────────────────────────────────────────────────────────────
renderCanvas();

// Show/hide password field based on access mode selector
(function(){
    const modeEl = document.getElementById('settingAccessMode');
    const pwGroup = document.getElementById('settingPasswordGroup');
    if (modeEl && pwGroup) {
        modeEl.addEventListener('change', function() {
            pwGroup.style.display = this.value === 'password' ? '' : 'none';
        });
    }
})();

// ─── Share Modal ─────────────────────────────────────────────────────────────
window.openShareModal = function() {
    const modal = document.getElementById('fxShareModal');
    if (!modal) return;
    modal.style.display = 'flex';
};

window.closeShareModal = function() {
    const modal = document.getElementById('fxShareModal');
    if (modal) modal.style.display = 'none';
};

window.copyShareUrl = function() {
    const inp = document.getElementById('shareUrlInput');
    if (!inp) return;
    inp.select();
    inp.setSelectionRange(0, 9999);
    navigator.clipboard.writeText(inp.value).then(() => {
        const btn = document.getElementById('copyShareBtn');
        if (btn) { btn.innerHTML = '<i class="fas fa-check"></i> Copied!'; setTimeout(() => btn.innerHTML = '<i class="fas fa-copy"></i> Copy', 2000); }
    }).catch(() => { document.execCommand('copy'); });
};

window.saveShareSettings = function() {
    closeShareModal();
};

// Close modal on backdrop click
document.getElementById('fxShareModal') && document.getElementById('fxShareModal').addEventListener('click', function(e) {
    if (e.target === this) closeShareModal();
});

})();
</script>

<?php View::endSection(); ?>
