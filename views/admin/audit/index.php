<?php use Core\View; use Core\Auth; ?>
<?php View::extend('audit'); ?>

<?php View::section('content'); ?>
<style>
:root { font-size: 13px; }
.ae-wrap{display:grid;grid-template-columns:270px 1fr;height:100%;overflow:hidden;}
.ae-sidebar{background:var(--bg-s);border-right:1px solid var(--border);overflow-y:auto;display:flex;flex-direction:column;height:100%;}
.ae-sidebar::-webkit-scrollbar{width:4px;}.ae-sidebar::-webkit-scrollbar-thumb{background:var(--border);border-radius:2px;}
.ae-sb-logo{padding:12px 16px 8px;border-bottom:1px solid var(--border);flex-shrink:0;}
.ae-sb-logo h2{font-size:13px;font-weight:700;margin:0;}.ae-sb-logo p{font-size:10px;color:var(--text-m);margin:2px 0 0;}
.ae-mode-tabs{display:flex;border-bottom:1px solid var(--border);flex-shrink:0;}
.ae-mode-tab{flex:1;padding:9px 0;font-size:11px;font-weight:600;text-align:center;cursor:pointer;border:none;background:none;color:var(--text-m);border-bottom:2px solid transparent;transition:.12s;font-family:inherit;}
.ae-mode-tab.active{color:var(--cyan);border-bottom-color:var(--cyan);}
.ae-vb-panel{display:flex;flex-direction:column;flex:1;overflow-y:auto;min-height:0;}
.ae-run-btn{margin:10px 14px;background:var(--cyan);color:#000;border:none;border-radius:8px;padding:9px 0;font-weight:700;font-size:12px;cursor:pointer;width:calc(100% - 28px);transition:opacity .15s;flex-shrink:0;font-family:inherit;}
.ae-run-btn:hover:not(:disabled){opacity:.85;}.ae-run-btn:disabled{opacity:.5;cursor:not-allowed;}
.ae-sec{border-bottom:1px solid var(--border);}
.ae-sec-hdr{padding:8px 16px;display:flex;align-items:center;justify-content:space-between;cursor:pointer;user-select:none;}
.ae-sec-hdr .lbl{font-size:10px;font-weight:700;letter-spacing:.9px;text-transform:uppercase;color:var(--text-m);}
.ae-sec-hdr .arr{font-size:9px;color:var(--text-m);transition:transform .15s;}
.ae-sec-hdr.open .arr{transform:rotate(90deg);}
.ae-sec-body{padding:2px 14px 12px;display:none;}
.ae-sec-body.open{display:block;}
.fl{display:block;font-size:10px;color:var(--text-m);font-weight:600;margin:8px 0 3px;}
.fi{width:100%;padding:5px 8px;border-radius:6px;border:1px solid var(--border);background:var(--bg-card);color:var(--text);font-size:11px;font-family:inherit;}
.fi:focus{outline:none;border-color:var(--cyan);}
.tpl-btn{display:block;width:100%;text-align:left;background:var(--bg-card);border:1px solid var(--border);border-radius:6px;padding:6px 9px;font-size:10.5px;cursor:pointer;margin-bottom:4px;color:var(--text);transition:.12s;font-family:inherit;}
.tpl-btn:hover{border-color:var(--cyan);color:var(--cyan);}
.sv-item{display:flex;align-items:center;gap:5px;margin-bottom:3px;}
.sv-name{flex:1;font-size:11px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.sv-load{background:none;border:1px solid var(--border);color:var(--text-m);border-radius:5px;padding:2px 6px;font-size:10px;cursor:pointer;font-family:inherit;}
.sv-del{background:none;border:none;color:var(--red);cursor:pointer;font-size:12px;padding:0 2px;}
/* Multi-field exclusion / inclusion filter */
.ae-mf-row{display:flex;gap:4px;align-items:center;margin-bottom:6px;}
.ae-mf-col{flex:0 0 auto;padding:4px 6px;border-radius:6px;border:1px solid var(--border);background:var(--bg-card);color:var(--text);font-size:10.5px;font-family:inherit;max-width:112px;}
.ae-mf-col:focus{outline:none;border-color:var(--cyan);}
.ae-mf-val{flex:1;min-width:0;padding:4px 7px;border-radius:6px;border:1px solid var(--border);background:var(--bg-card);color:var(--text);font-size:11px;font-family:inherit;}
.ae-mf-val:focus{outline:none;border-color:var(--cyan);}
.ae-mf-add-btn{flex-shrink:0;background:none;border:1px solid var(--border);color:var(--text-m);border-radius:6px;padding:4px 8px;font-size:11px;cursor:pointer;font-family:inherit;transition:.12s;}
.ae-mf-add-btn:hover{border-color:var(--cyan);color:var(--cyan);}
.ae-mf-chips{display:flex;flex-wrap:wrap;gap:5px;padding:2px 0;min-height:0;}
.ae-mf-chip{display:inline-flex;align-items:center;gap:3px;padding:2px 5px 2px 8px;border-radius:20px;font-size:10px;font-weight:600;line-height:1.5;}
.ae-mf-chip-exc{border:1px solid var(--orange);background:rgba(255,120,50,.12);color:var(--orange);}
.ae-mf-chip-inc{border:1px solid var(--cyan);background:rgba(0,240,255,.10);color:var(--cyan);}
.ae-mf-chip-label{max-width:145px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.ae-mf-chip-del{background:none;border:none;cursor:pointer;font-size:12px;padding:0 1px;line-height:1;font-family:inherit;}
.ae-mf-chip-exc .ae-mf-chip-del{color:var(--orange);}.ae-mf-chip-inc .ae-mf-chip-del{color:var(--cyan);}
.ae-export-row{padding:10px 14px;border-top:1px solid var(--border);display:flex;gap:6px;flex-shrink:0;margin-top:auto;}
.ae-sm-btn{flex:1;background:none;border:1px solid var(--border);color:var(--text-m);border-radius:6px;padding:5px 0;font-size:11px;cursor:pointer;font-family:inherit;transition:.12s;}
.ae-sm-btn:hover{border-color:var(--cyan);color:var(--cyan);}
/* SQL Editor panel */
.ae-sql-panel{display:none;flex-direction:column;flex:1;overflow-y:auto;padding:12px 14px;gap:10px;}
.ae-sql-panel.visible{display:flex;}
.ae-sql-editor-label{font-size:10px;font-weight:700;letter-spacing:.9px;text-transform:uppercase;color:var(--text-m);}
.ae-sql-ta{width:100%;min-height:180px;padding:10px;border-radius:8px;border:1px solid var(--border);background:#0d1117;color:#79c0ff;font-family:'JetBrains Mono',monospace,sans-serif;font-size:11.5px;line-height:1.6;resize:vertical;box-sizing:border-box;outline:none;transition:.12s;caret-color:#79c0ff;}
.ae-sql-ta:focus{border-color:var(--cyan);box-shadow:0 0 0 2px rgba(0,240,255,.1);}
.ae-sql-run-btn{background:var(--cyan);color:#000;border:none;border-radius:8px;padding:9px 0;font-weight:700;font-size:12px;cursor:pointer;width:100%;transition:opacity .15s;font-family:inherit;}
.ae-sql-run-btn:hover:not(:disabled){opacity:.85;}
.ae-sql-run-btn:disabled{opacity:.5;cursor:not-allowed;}
.ae-schema-label{font-size:10px;font-weight:700;letter-spacing:.9px;text-transform:uppercase;color:var(--text-m);margin-bottom:4px;}
.ae-schema-box{background:var(--bg-card);border:1px solid var(--border);border-radius:8px;padding:10px;font-size:10.5px;font-family:'JetBrains Mono',monospace,sans-serif;overflow-y:auto;max-height:200px;}
.ae-schema-box .t{font-weight:700;color:var(--cyan);margin-bottom:4px;display:block;}
.ae-schema-box .c{color:var(--text-m);padding-left:8px;margin-bottom:1px;display:block;}
.ae-schema-hint{font-size:10px;color:var(--text-m);margin-top:6px;line-height:1.5;}
/* Main panel */
.ae-main{display:flex;flex-direction:column;overflow:hidden;height:100%;background:var(--bg-card);}
.ae-stats{display:flex;border-bottom:1px solid var(--border);flex-shrink:0;}
.ae-stat{flex:1;text-align:center;padding:10px 8px;border-right:1px solid var(--border);}
.ae-stat:last-child{border-right:none;}
.ae-stat .v{font-size:1.1rem;font-weight:700;line-height:1;}
.ae-stat .l{font-size:9px;color:var(--text-m);margin-top:2px;text-transform:uppercase;letter-spacing:.5px;}
.ae-toolbar{padding:8px 14px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:8px;flex-wrap:wrap;flex-shrink:0;background:var(--bg-s);}
.ae-toolbar-lbl{font-size:10px;font-weight:700;color:var(--text-m);text-transform:uppercase;letter-spacing:.7px;white-space:nowrap;}
.ae-sel-wrap{display:flex;gap:6px;flex-wrap:wrap;align-items:center;flex:1;}
.ae-col-in{padding:4px 8px;border-radius:6px;border:1px solid var(--border);background:var(--bg-card);color:var(--text);font-size:11px;font-family:'JetBrains Mono',monospace,inherit;width:180px;}
.ae-col-in:focus{outline:none;border-color:var(--cyan);}
.ae-add-col{background:none;border:1px dashed var(--border);color:var(--text-m);border-radius:6px;padding:4px 8px;font-size:11px;cursor:pointer;font-family:inherit;}
.ae-add-col:hover{border-color:var(--cyan);color:var(--cyan);}
.ae-result-meta{margin-left:auto;font-size:11px;color:var(--text-m);white-space:nowrap;}
.ae-sql-bar{padding:6px 14px;background:#0d1117;border-bottom:1px solid var(--border);font-family:'JetBrains Mono',monospace,inherit;font-size:11px;color:#79c0ff;overflow-x:auto;white-space:nowrap;flex-shrink:0;display:none;}
.ae-results{flex:1;overflow:auto;}
.ae-placeholder{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:var(--text-m);gap:8px;}
.ae-placeholder i{font-size:2.5rem;opacity:.25;}
.ae-placeholder strong{font-size:14px;font-weight:600;color:var(--text-m);}
/* Industry-standard table */
.ae-table{width:100%;border-collapse:collapse;font-size:11.5px;}
.ae-table th{background:var(--bg-s);padding:8px 12px;text-align:left;font-weight:600;font-size:10.5px;white-space:nowrap;position:sticky;top:0;z-index:2;border-bottom:2px solid var(--border);color:var(--text-m);text-transform:uppercase;letter-spacing:.5px;}
.ae-table td{padding:8px 12px;border-bottom:1px solid var(--border);word-break:break-word;max-width:300px;vertical-align:middle;}
.ae-table tbody tr:hover{background:rgba(0,240,255,.03);}
.ae-table td pre{font-size:10px;font-family:monospace;white-space:pre-wrap;margin:0;color:var(--text-m);}
/* Badges */
.ab{display:inline-flex;align-items:center;gap:4px;padding:2px 7px;border-radius:20px;font-size:10.5px;font-weight:600;white-space:nowrap;}
.ab-create{background:rgba(46,204,113,.15);color:#2ecc71;}
.ab-update{background:rgba(243,156,18,.15);color:#f39c12;}
.ab-delete{background:rgba(231,76,60,.15);color:#e74c3c;}
.ab-login{background:rgba(0,240,255,.12);color:var(--cyan);}
.ab-logout{background:rgba(136,146,166,.15);color:#8892a6;}
.ab-failure{background:rgba(231,76,60,.2);color:#e74c3c;}
.ab-default{background:rgba(155,89,182,.12);color:#9b59b6;}
.sb{display:inline-flex;align-items:center;gap:3px;padding:2px 7px;border-radius:12px;font-size:10px;font-weight:700;}
.sb-success{background:rgba(46,204,113,.12);color:#2ecc71;}
.sb-failure{background:rgba(231,76,60,.15);color:#e74c3c;}
.sb-pending{background:rgba(243,156,18,.12);color:#f39c12;}
.mb{display:inline-block;padding:1px 6px;border-radius:6px;background:rgba(155,89,182,.12);color:#9b59b6;font-size:10px;font-weight:600;}
/* User cell */
.uc{display:flex;align-items:center;gap:6px;}
.uc-av{width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:9px;font-weight:700;color:#000;flex-shrink:0;background:linear-gradient(135deg,var(--cyan),#9b59b6);}
.uc-name{font-size:11px;font-weight:600;white-space:nowrap;display:block;}
.uc-email{font-size:9.5px;color:var(--text-m);white-space:nowrap;display:block;}
/* Timestamps */
.ts-rel{font-size:11px;display:block;}
.ts-abs{font-size:9.5px;color:var(--text-m);display:block;}
/* Diff */
.diff-wrap{font-size:10px;font-family:monospace;}
.diff-old{color:#e74c3c;background:rgba(231,76,60,.07);padding:1px 4px;border-radius:3px;display:block;margin-bottom:1px;}
.diff-new{color:#2ecc71;background:rgba(46,204,113,.07);padding:1px 4px;border-radius:3px;display:block;}
.diff-toggle{background:none;border:1px solid var(--border);color:var(--text-m);border-radius:4px;padding:1px 6px;font-size:10px;cursor:pointer;font-family:inherit;margin-top:3px;}
.diff-toggle:hover{border-color:var(--cyan);color:var(--cyan);}
.ip-badge{font-family:monospace;font-size:10px;color:var(--text-m);background:var(--bg-s);padding:1px 5px;border-radius:4px;}
.sev{display:inline-block;width:7px;height:7px;border-radius:50%;flex-shrink:0;margin-right:2px;}
.sev-info{background:var(--cyan);}.sev-warn{background:#f39c12;}.sev-error{background:#e74c3c;}.sev-ok{background:#2ecc71;}
/* View toggle */
.ae-vt-btn{background:none;border:1px solid var(--border);color:var(--text-m);padding:4px 9px;font-size:11px;cursor:pointer;font-family:inherit;transition:.1s;}
.ae-vt-btn:first-child{border-radius:6px 0 0 6px;}
.ae-vt-btn:last-child{border-radius:0 6px 6px 0;border-left:none;}
.ae-vt-btn.active{background:var(--cyan);color:#000;border-color:var(--cyan);}
/* Refresh + auto-refresh controls */
.ae-refresh-grp{position:relative;display:inline-flex;gap:0;margin-left:4px;}
.ae-refresh-grp .ae-vt-btn:first-child{border-radius:6px 0 0 6px;padding:4px 7px;}
.ae-refresh-grp .ae-vt-btn:last-child{border-radius:0 6px 6px 0;border-left:none;padding:4px 6px;font-size:13px;letter-spacing:.5px;}
.ae-refresh-grp .ae-vt-btn.ar-on{background:rgba(0,240,255,.15);color:var(--cyan);border-color:var(--cyan);}
.ae-ar-menu{display:none;position:absolute;top:calc(100% + 4px);right:0;z-index:200;background:var(--bg-card);border:1px solid var(--border);border-radius:8px;min-width:168px;padding:4px 0;box-shadow:0 6px 20px rgba(0,0,0,.5);}
.ae-ar-menu.open{display:block;}
.ae-ar-opt{padding:7px 14px;font-size:11.5px;cursor:pointer;color:var(--text);transition:.1s;}
.ae-ar-opt:hover{background:rgba(0,240,255,.08);color:var(--cyan);}
.ae-ar-opt.active{color:var(--cyan);font-weight:600;}
.ae-ar-sep{height:1px;background:var(--border);margin:3px 0;}
.ae-ar-badge{font-size:9px;background:rgba(0,240,255,.15);color:var(--cyan);border-radius:8px;padding:1px 6px;margin-left:5px;}
/* Timeline */
.ae-timeline{padding:14px 20px;display:flex;flex-direction:column;}
.ae-tl-item{display:flex;gap:14px;padding-bottom:18px;position:relative;}
.ae-tl-item:not(:last-child)::before{content:'';position:absolute;left:15px;top:32px;bottom:0;width:2px;background:var(--border);}
.ae-tl-dot{width:32px;height:32px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;flex-shrink:0;z-index:1;}
.ae-tl-body{flex:1;background:var(--bg-s);border:1px solid var(--border);border-radius:10px;padding:10px 14px;}
.ae-tl-header{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-bottom:4px;}
.ae-tl-msg{font-size:12px;color:var(--text);margin:4px 0;}
.ae-tl-meta{display:flex;gap:10px;flex-wrap:wrap;margin-top:6px;}
.ae-tl-meta span{font-size:10.5px;color:var(--text-m);}
/* Compact view */
.ae-compact{width:100%;border-collapse:collapse;font-size:11.5px;}
.ae-compact th{background:var(--bg-s);padding:6px 10px;text-align:left;font-weight:600;font-size:10px;white-space:nowrap;position:sticky;top:0;z-index:2;border-bottom:2px solid var(--border);color:var(--text-m);text-transform:uppercase;letter-spacing:.5px;}
.ae-compact td{padding:4px 10px;border-bottom:1px solid var(--border);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:220px;vertical-align:middle;font-size:11px;}
.ae-compact tbody tr:hover{background:rgba(0,240,255,.04);}
.ae-compact tr.ae-exp-row td{white-space:normal;overflow:visible;max-width:none;background:var(--bg-s);padding:10px 14px;font-size:11px;}
.ae-exp-btn{background:none;border:1px solid var(--border);color:var(--cyan);border-radius:4px;padding:1px 6px;font-size:11px;cursor:pointer;font-family:inherit;line-height:1.4;transition:.1s;flex-shrink:0;}
.ae-exp-btn:hover{background:rgba(0,240,255,.1);}
.ae-raw-pre{font-family:'JetBrains Mono',monospace;font-size:10px;white-space:pre-wrap;word-break:break-word;color:var(--text-m);margin:0;}
@media(max-width:760px){.ae-wrap{grid-template-columns:1fr;}.ae-sidebar{max-height:45vh;height:auto;}}
</style>

<div class="ae-wrap">
<aside class="ae-sidebar">
    <div class="ae-sb-logo">
        <h2>&#x1F50D; Audit Explorer</h2>
        <p>Visual builder &middot; SQL editor &middot; Export</p>
    </div>

    <div class="ae-mode-tabs">
        <button type="button" class="ae-mode-tab active" id="tabVB" onclick="setMode('vb')">
            <i class="fas fa-sliders-h" style="margin-right:4px;"></i>Visual
        </button>
        <button type="button" class="ae-mode-tab" id="tabSQL" onclick="setMode('sql')">
            <i class="fas fa-terminal" style="margin-right:4px;"></i>SQL
        </button>
    </div>

    <!-- Visual Builder panel -->
    <div class="ae-vb-panel" id="vbPanel">
        <button type="button" class="ae-run-btn" id="runBtn" onclick="runQuery()">&#9654; Run Query</button>

        <div class="ae-sec">
            <div class="ae-sec-hdr open" onclick="toggleSec(this)">
                <span class="lbl">&#9889; Filters</span>
                <i class="fas fa-chevron-right arr"></i>
            </div>
            <div class="ae-sec-body open">
                <label class="fl">Module</label>
                <select id="fModule" class="fi">
                    <option value="">All modules</option>
                    <?php foreach ($modules as $m): ?>
                        <option value="<?= View::e($m['module']) ?>"><?= View::e(ucfirst($m['module'])) ?></option>
                    <?php endforeach; ?>
                </select>
                <label class="fl">Action</label>
                <select id="fAction" class="fi">
                    <option value="">All actions</option>
                    <?php foreach ($actions as $a): ?>
                        <option value="<?= View::e($a['action']) ?>"><?= View::e($a['action']) ?></option>
                    <?php endforeach; ?>
                </select>
                <label class="fl">Status</label>
                <select id="fStatus" class="fi">
                    <option value="">All</option>
                    <option value="success">Success</option>
                    <option value="failure">Failure</option>
                    <option value="pending">Pending</option>
                </select>
                <label class="fl">User Role</label>
                <select id="fUserRole" class="fi">
                    <option value="">All roles</option>
                    <?php foreach ($userRoles as $r): ?>
                        <option value="<?= View::e($r['user_role']) ?>"><?= View::e(ucfirst($r['user_role'])) ?></option>
                    <?php endforeach; ?>
                </select>
                <label class="fl">User Name</label>
                <input type="text" id="fUserName" class="fi" placeholder="e.g. John" list="dlUsers">
                <label class="fl">User Email</label>
                <input type="text" id="fUserEmail" class="fi" placeholder="e.g. john@..." list="dlUserEmails">
                <datalist id="dlUsers">
                    <?php foreach ($users as $u): ?><option value="<?= View::e($u['name']) ?>"><?php endforeach; ?>
                </datalist>
                <datalist id="dlUserEmails">
                    <?php foreach ($users as $u): ?><option value="<?= View::e($u['email']) ?>"><?php endforeach; ?>
                </datalist>
                <label class="fl">User ID</label>
                <input type="number" id="fUserId" class="fi" placeholder="e.g. 3">
                <label class="fl">Date From</label>
                <input type="date" id="fDateFrom" class="fi">
                <label class="fl">Date To</label>
                <input type="date" id="fDateTo" class="fi">
                <label class="fl">Keyword (message / IP / action)</label>
                <input type="text" id="fSearch" class="fi" placeholder="message, action, IP...">
            </div>
        </div>

        <div class="ae-sec">
            <div class="ae-sec-hdr" onclick="toggleSec(this)">
                <span class="lbl">&#x1F4D0; Group &amp; Sort</span>
                <i class="fas fa-chevron-right arr"></i>
            </div>
            <div class="ae-sec-body">
                <label class="fl">Group By</label>
                <input type="text" id="fGroupBy" class="fi" placeholder="action, module">
                <label class="fl">Order By</label>
                <input type="text" id="fOrderBy" class="fi" value="created_at">
                <label class="fl">Direction</label>
                <select id="fOrderDir" class="fi">
                    <option value="DESC">Newest first</option>
                    <option value="ASC">Oldest first</option>
                </select>
                <label class="fl">Limit</label>
                <input type="number" id="fLimit" class="fi" value="100" min="1" max="10000">
            </div>
        </div>

        <div class="ae-sec">
            <div class="ae-sec-hdr" onclick="toggleSec(this)">
                <span class="lbl">&#x1F6AB; Exclusion Filters</span>
                <i class="fas fa-chevron-right arr"></i>
            </div>
            <div class="ae-sec-body">
                <div style="font-size:10px;color:var(--text-m);margin-bottom:7px;line-height:1.5;">
                    Exclude rows where the field matches any added value.
                </div>
                <div class="ae-mf-row">
                    <select id="exColSel" class="ae-mf-col" onchange="switchMfDatalist('dlEx','exColSel','exInput')">
                        <option value="action">Action</option>
                        <option value="module">Module</option>
                        <option value="status">Status</option>
                        <option value="resource_type">Resource Type</option>
                        <option value="user_name">User Name</option>
                        <option value="user_id">User ID</option>
                        <option value="entity_name">Entity Name</option>
                        <option value="ip_address">IP Address</option>
                        <option value="id">ID</option>
                    </select>
                    <input type="text" id="exInput" class="ae-mf-val" placeholder="value…"
                           list="dlEx" autocomplete="off"
                           onkeydown="if(event.key==='Enter'){event.preventDefault();addMfChip('exChips','exColSel','exInput','exc');}">
                    <datalist id="dlEx"></datalist>
                    <button type="button" class="ae-mf-add-btn" onclick="addMfChip('exChips','exColSel','exInput','exc')">+ Add</button>
                </div>
                <div class="ae-mf-chips" id="exChips"></div>
                <button type="button" class="ae-sm-btn" style="width:100%;margin-top:4px;" onclick="clearMfChips('exChips')">Clear exclusions</button>
            </div>
        </div>

        <div class="ae-sec">
            <div class="ae-sec-hdr" onclick="toggleSec(this)">
                <span class="lbl">&#x2705; Inclusion Filters</span>
                <i class="fas fa-chevron-right arr"></i>
            </div>
            <div class="ae-sec-body">
                <div style="font-size:10px;color:var(--text-m);margin-bottom:7px;line-height:1.5;">
                    Include only rows where the field matches any added value.
                </div>
                <div class="ae-mf-row">
                    <select id="inColSel" class="ae-mf-col" onchange="switchMfDatalist('dlIn','inColSel','inInput')">
                        <option value="action">Action</option>
                        <option value="module">Module</option>
                        <option value="status">Status</option>
                        <option value="resource_type">Resource Type</option>
                        <option value="user_name">User Name</option>
                        <option value="user_id">User ID</option>
                        <option value="entity_name">Entity Name</option>
                        <option value="ip_address">IP Address</option>
                        <option value="id">ID</option>
                    </select>
                    <input type="text" id="inInput" class="ae-mf-val" placeholder="value…"
                           list="dlIn" autocomplete="off"
                           onkeydown="if(event.key==='Enter'){event.preventDefault();addMfChip('inChips','inColSel','inInput','inc');}">
                    <datalist id="dlIn"></datalist>
                    <button type="button" class="ae-mf-add-btn" onclick="addMfChip('inChips','inColSel','inInput','inc')">+ Add</button>
                </div>
                <div class="ae-mf-chips" id="inChips"></div>
                <button type="button" class="ae-sm-btn" style="width:100%;margin-top:4px;" onclick="clearMfChips('inChips')">Clear inclusions</button>
            </div>
        </div>

        <div class="ae-sec">
            <div class="ae-sec-hdr" onclick="toggleSec(this)">
                <span class="lbl">&#9889; Templates</span>
                <i class="fas fa-chevron-right arr"></i>
            </div>
            <div class="ae-sec-body">
                <?php
                $tpls = [
                    ['Latest 100 events',         ['select'=>['*'],'where'=>[],'order_by'=>'created_at','limit'=>100]],
                    ['Login events today',         ['select'=>['*'],'where'=>[['col'=>'action','op'=>'=','val'=>'login'],['col'=>'created_at','op'=>'>=','val'=>date('Y-m-d')]],'order_by'=>'created_at','limit'=>100]],
                    ['All failures',              ['select'=>['*'],'where'=>[['col'=>'status','op'=>'=','val'=>'failure']],'order_by'=>'created_at','limit'=>200]],
                    ['Top actions (grouped)',      ['select'=>['action','COUNT(*) AS cnt'],'where'=>[],'group_by'=>['action'],'order_by'=>'COUNT(*)','order_dir'=>'DESC','limit'=>20]],
                    ['Per-module counts',          ['select'=>['module','COUNT(*) AS cnt'],'where'=>[],'group_by'=>['module'],'order_by'=>'COUNT(*)','order_dir'=>'DESC','limit'=>20]],
                    ['Events by user',             ['select'=>['user_name','user_email','COUNT(*) AS cnt'],'where'=>[],'group_by'=>['user_name','user_email'],'order_by'=>'COUNT(*)','order_dir'=>'DESC','limit'=>50]],
                    ['WhatsApp events',            ['select'=>['*'],'where'=>[['col'=>'module','op'=>'=','val'=>'whatsapp']],'order_by'=>'created_at','limit'=>100]],
                    ['Admin actions',              ['select'=>['*'],'where'=>[['col'=>'user_role','op'=>'=','val'=>'admin']],'order_by'=>'created_at','limit'=>100]],
                    ['Failures last 7 days',       ['select'=>['*'],'where'=>[['col'=>'status','op'=>'=','val'=>'failure'],['col'=>'created_at','op'=>'>=','val'=>date('Y-m-d',strtotime('-7 days'))]],'order_by'=>'created_at','limit'=>200]],
                    ['Settings changes (old/new)', ['select'=>['user_name','action','module','readable_message','old_values','new_values','created_at'],'where'=>[['col'=>'action','op'=>'LIKE','val'=>'%_updated']],'order_by'=>'created_at','limit'=>200]],
                    ['By IP address',              ['select'=>['ip_address','COUNT(*) AS cnt'],'where'=>[],'group_by'=>['ip_address'],'order_by'=>'COUNT(*)','order_dir'=>'DESC','limit'=>50]],
                    ['Deletes today',              ['select'=>['*'],'where'=>[['col'=>'action','op'=>'LIKE','val'=>'%_deleted'],['col'=>'created_at','op'=>'>=','val'=>date('Y-m-d')]],'order_by'=>'created_at','limit'=>100]],
                ];
                foreach ($tpls as [$label, $spec]): ?>
                <button type="button" class="tpl-btn" onclick='loadTemplate(<?= htmlspecialchars(json_encode($spec), ENT_QUOTES) ?>)'><?= htmlspecialchars($label) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="ae-sec">
            <div class="ae-sec-hdr" onclick="toggleSec(this)">
                <span class="lbl">&#x1F4BE; Saved Queries</span>
                <i class="fas fa-chevron-right arr"></i>
            </div>
            <div class="ae-sec-body">
                <div id="savedList"><p style="font-size:10.5px;color:var(--text-m);">No saved queries yet.</p></div>
                <button type="button" class="ae-sm-btn" style="width:100%;margin-top:6px;" onclick="saveQuery()">+ Save current query</button>
            </div>
        </div>

        <div style="flex:1;min-height:10px;"></div>
        <div class="ae-export-row">
            <button type="button" class="ae-sm-btn" onclick="exportResult('csv')">&#x2B07; CSV</button>
            <button type="button" class="ae-sm-btn" onclick="exportResult('json')">&#x2B07; JSON</button>
            <button type="button" class="ae-sm-btn" onclick="clearAll()">&#x21BA; Reset</button>
        </div>
    </div>

    <!-- SQL Editor panel -->
    <div class="ae-sql-panel" id="sqlPanel">
        <div>
            <div class="ae-sql-editor-label" style="margin-bottom:6px;">&#x1F4DD; SQL Query</div>
            <textarea id="sqlInput" class="ae-sql-ta" spellcheck="false"
                placeholder="SELECT al.*, u.name AS user_name&#10;FROM activity_logs al&#10;LEFT JOIN users u ON al.user_id = u.id&#10;WHERE al.status = 'failure'&#10;ORDER BY al.created_at DESC&#10;LIMIT 100"></textarea>
            <button type="button" class="ae-sql-run-btn" id="sqlRunBtn" onclick="runRawSql()" style="margin-top:8px;">
                &#9654; Execute SQL
            </button>
        </div>
        <div>
            <div class="ae-schema-label">&#x1F4CB; Schema Reference</div>
            <div class="ae-schema-box">
                <span class="t">activity_logs (al.*)</span>
                <?php foreach (['id','user_id','user_name','action','module','tenant_id','resource_type','resource_id','entity_name','user_role','status','readable_message','ip_address','device','browser','request_id','old_values','new_values','changes','created_at'] as $col): ?>
                <span class="c">&#x21B3; <?= $col ?></span>
                <?php endforeach; ?>
                <span class="t" style="margin-top:8px;">users (JOIN on user_id)</span>
                <span class="c">&#x21B3; u.name AS user_name</span>
                <span class="c">&#x21B3; u.email AS user_email</span>
            </div>
            <div class="ae-schema-hint">
                Only <strong>SELECT</strong> from <code>activity_logs</code> + <code>users</code>.<br>
                Max 5 000 rows &middot; No semicolons &middot; No DDL/DML
            </div>
        </div>
        <div style="flex:1;min-height:10px;"></div>
        <div class="ae-export-row">
            <button type="button" class="ae-sm-btn" onclick="exportSqlResult('csv')">&#x2B07; CSV</button>
            <button type="button" class="ae-sm-btn" onclick="exportSqlResult('json')">&#x2B07; JSON</button>
        </div>
    </div>
</aside>

<section class="ae-main">
    <div class="ae-stats">
        <div class="ae-stat"><div class="v" style="color:var(--cyan);"><?= number_format($stats['total'] ?? 0) ?></div><div class="l">Total Events</div></div>
        <div class="ae-stat"><div class="v" style="color:var(--green);"><?= number_format($stats['unique_users'] ?? 0) ?></div><div class="l">Users</div></div>
        <div class="ae-stat"><div class="v" style="color:var(--orange);"><?= number_format($stats['unique_actions'] ?? 0) ?></div><div class="l">Action Types</div></div>
        <div class="ae-stat"><div class="v" style="color:#ff2ec4;"><?= number_format($stats['unique_modules'] ?? 0) ?></div><div class="l">Modules</div></div>
    </div>

    <div class="ae-toolbar" id="selectToolbar">
        <span class="ae-toolbar-lbl">SELECT</span>
        <div class="ae-sel-wrap" id="selectWrap">
            <input type="text" class="ae-col-in sel-expr" value="*" placeholder="col or COUNT(*) AS n" list="dlCols" ondblclick="this.remove()">
        </div>
        <button type="button" class="ae-add-col" onclick="addColIn()">+ col</button>
        <datalist id="dlCols">
            <?php foreach ($allowedCols as $c): ?><option value="<?= View::e($c) ?>"><?php endforeach; ?>
            <option value="COUNT(*)"><option value="COUNT(*) AS cnt">
            <option value="user_name"><option value="user_email">
            <option value="entity_name"><option value="changes">
        </datalist>
        <span style="display:flex;gap:0;">
            <button type="button" class="ae-vt-btn active" id="btnCompact" onclick="setView('compact')" title="Compact view"><i class="fas fa-list"></i></button>
            <button type="button" class="ae-vt-btn" id="btnTable" onclick="setView('table')" title="Table view"><i class="fas fa-table"></i></button>
            <button type="button" class="ae-vt-btn" id="btnTimeline" onclick="setView('timeline')" title="Timeline view"><i class="fas fa-stream"></i></button>
        </span>
        <span class="ae-refresh-grp">
            <button type="button" class="ae-vt-btn" id="refreshBtn" onclick="manualRefresh(event)" title="Refresh results"><i class="fas fa-sync-alt" id="refreshIcon"></i></button>
            <button type="button" class="ae-vt-btn" id="arTrigger" onclick="toggleArMenu(event)" title="Auto-refresh options">&#x22EF;</button>
            <div class="ae-ar-menu" id="arMenu">
                <div class="ae-ar-opt active" data-ms="0">&#x2715; Off</div>
                <div class="ae-ar-sep"></div>
                <div class="ae-ar-opt" data-ms="10000">Every 10 s</div>
                <div class="ae-ar-opt" data-ms="30000">Every 30 s</div>
                <div class="ae-ar-opt" data-ms="60000">Every 1 min</div>
                <div class="ae-ar-opt" data-ms="300000">Every 5 min</div>
                <div class="ae-ar-sep"></div>
                <div class="ae-ar-opt" data-ms="custom">Custom&#x2026;</div>
            </div>
        </span>
        <span class="ae-result-meta" id="resultMeta"></span>
    </div>

    <div class="ae-sql-bar" id="sqlBar"></div>
    <div class="ae-results" id="resultsArea">
        <div class="ae-placeholder">
            <i class="fas fa-database"></i>
            <strong>No query run yet</strong>
            <span style="font-size:12px;">Set filters and click <b>Run Query</b> — or use the <b>SQL</b> tab</span>
            <span style="font-size:11px;opacity:.7;">Ctrl+Enter to run</span>
        </div>
    </div>
</section>
</div>
<?php View::endSection(); ?>

<?php View::section('scripts'); ?>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
let currentView = 'compact';
let lastSpec = null;
let lastSqlRows = null;

function setMode(mode) {
    const isVb = mode === 'vb';
    document.getElementById('tabVB').classList.toggle('active', isVb);
    document.getElementById('tabSQL').classList.toggle('active', !isVb);
    document.getElementById('vbPanel').style.display = isVb ? '' : 'none';
    document.getElementById('sqlPanel').classList.toggle('visible', !isVb);
    document.getElementById('selectToolbar').style.display = isVb ? '' : 'none';
    resetResults();
}

function resetResults() {
    document.getElementById('sqlBar').style.display = 'none';
    document.getElementById('resultMeta').textContent = '';
    document.getElementById('resultsArea').innerHTML =
        '<div class="ae-placeholder"><i class="fas fa-database"></i><strong>No query run yet</strong></div>';
    lastSpec = null; lastSqlRows = null;
}

function setView(v) {
    currentView = v;
    document.getElementById('btnCompact').classList.toggle('active', v==='compact');
    document.getElementById('btnTable').classList.toggle('active', v==='table');
    document.getElementById('btnTimeline').classList.toggle('active', v==='timeline');
    if (lastSpec) runQuery();
}

function toggleSec(hdr) {
    hdr.classList.toggle('open');
    hdr.nextElementSibling.classList.toggle('open');
}

function addColIn(val) {
    const inp = document.createElement('input');
    inp.type='text'; inp.className='ae-col-in sel-expr';
    inp.placeholder='col or COUNT(*)'; inp.value=val||'';
    inp.setAttribute('list','dlCols');
    inp.ondblclick=()=>inp.remove();
    document.getElementById('selectWrap').appendChild(inp);
    inp.focus();
}

function buildSpec() {
    const select=[...document.querySelectorAll('.sel-expr')].map(i=>i.value.trim()).filter(Boolean);
    const where=[];
    const push=(col,op,val)=>{if(val!==''&&val!=null)where.push({col,op,val});};
    push('module','=',document.getElementById('fModule').value);
    push('action','=',document.getElementById('fAction').value);
    push('status','=',document.getElementById('fStatus').value);
    push('user_role','=',document.getElementById('fUserRole').value);
    push('user_id','=',document.getElementById('fUserId').value);
    const df=document.getElementById('fDateFrom').value;
    const dt=document.getElementById('fDateTo').value;
    if(df)push('created_at','>=',df+' 00:00:00');
    if(dt)push('created_at','<=',dt+' 23:59:59');
    const un=document.getElementById('fUserName').value.trim();
    if(un)where.push({col:'user_name',op:'LIKE',val:'%'+un+'%'});
    const ue=document.getElementById('fUserEmail').value.trim();
    if(ue)where.push({col:'user_email',op:'LIKE',val:'%'+ue+'%'});
    const kw=document.getElementById('fSearch').value.trim();
    if(kw)where.push({col:'readable_message',op:'LIKE',val:'%'+kw+'%'});
    // Exclusion filter chips (grouped by column → NOT IN)
    const exChips = [...document.querySelectorAll('#exChips .ae-mf-chip')];
    const exByCol = {};
    exChips.forEach(chip => {
        const col = chip.dataset.col, val = chip.dataset.val;
        if (col && val) { if (!exByCol[col]) exByCol[col] = []; exByCol[col].push(val); }
    });
    Object.entries(exByCol).forEach(([col, vals]) => where.push({col, op:'NOT IN', val:vals}));
    // Inclusion filter chips (grouped by column → IN)
    const inChips = [...document.querySelectorAll('#inChips .ae-mf-chip')];
    const inByCol = {};
    inChips.forEach(chip => {
        const col = chip.dataset.col, val = chip.dataset.val;
        if (col && val) { if (!inByCol[col]) inByCol[col] = []; inByCol[col].push(val); }
    });
    Object.entries(inByCol).forEach(([col, vals]) => where.push({col, op:'IN', val:vals}));
    const gbRaw=document.getElementById('fGroupBy').value.trim();
    return {
        select:select.length?select:['*'],
        where,
        group_by:gbRaw?gbRaw.split(',').map(s=>s.trim()).filter(Boolean):[],
        order_by:document.getElementById('fOrderBy').value.trim()||'created_at',
        order_dir:document.getElementById('fOrderDir').value,
        limit:Math.min(10000,Math.max(1,parseInt(document.getElementById('fLimit').value)||100)),
    };
}

async function runQuery() {
    const spec=buildSpec();
    lastSpec=spec;
    const btn=document.getElementById('runBtn');
    btn.textContent='\u23F3 Running\u2026'; btn.disabled=true;
    const ra=document.getElementById('resultsArea');
    window.mmbSkeleton.show(ra,'table');
    try {
        const res=await fetch('/admin/audit/query',{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
            body:JSON.stringify(spec),
        });
        const data=await res.json();
        if(!res.ok||data.error)showError(data.error||'HTTP '+res.status);
        else showResults(data);
    } catch(e){showError('Network error: '+e.message);}
    finally{btn.textContent='\u25B6 Run Query'; btn.disabled=false; window.mmbSkeleton.hide(ra);}
}

async function runRawSql() {
    const sql=document.getElementById('sqlInput').value.trim();
    if(!sql){showError('Please enter a SQL query.');return;}
    const btn=document.getElementById('sqlRunBtn');
    btn.textContent='\u23F3 Executing\u2026'; btn.disabled=true;
    const ra=document.getElementById('resultsArea');
    window.mmbSkeleton.show(ra,'table');
    try {
        const res=await fetch('/admin/audit/sql',{
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
            body:JSON.stringify({sql}),
        });
        const data=await res.json();
        if(!res.ok||data.error)showError(data.error||'HTTP '+res.status);
        else{lastSqlRows=data.data;showResults(data);}
    } catch(e){showError('Network error: '+e.message);}
    finally{btn.textContent='\u25B6 Execute SQL'; btn.disabled=false; window.mmbSkeleton.hide(ra);}
}

function exportSqlResult(fmt) {
    if(!lastSqlRows){alert('Run a SQL query first.');return;}
    const filename='audit_sql_'+new Date().toISOString().slice(0,19).replace(/:/g,'-');
    if(fmt==='json'){
        const blob=new Blob([JSON.stringify(lastSqlRows,null,2)],{type:'application/json'});
        const a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download=filename+'.json';a.click();
    } else {
        if(!lastSqlRows.length){alert('No rows to export.');return;}
        const cols=Object.keys(lastSqlRows[0]);
        const lines=[cols.join(','),...lastSqlRows.map(r=>cols.map(c=>JSON.stringify(r[c]??'')).join(','))];
        const blob=new Blob(['\uFEFF'+lines.join('\r\n')],{type:'text/csv;charset=utf-8'});
        const a=document.createElement('a');a.href=URL.createObjectURL(blob);a.download=filename+'.csv';a.click();
    }
}

function showError(msg) {
    document.getElementById('sqlBar').style.display='none';
    document.getElementById('resultMeta').textContent='';
    document.getElementById('resultsArea').innerHTML=
        '<div class="ae-placeholder"><i class="fas fa-exclamation-triangle" style="color:var(--red)"></i><strong style="color:var(--red);">'+esc(msg)+'</strong></div>';
}

function showResults(json) {
    document.getElementById('resultMeta').textContent=(json.count||0).toLocaleString()+' row(s)';
    const bar=document.getElementById('sqlBar');
    bar.textContent=json.sql||''; bar.style.display=json.sql?'':'none';
    if(!json.data||!json.data.length){
        document.getElementById('resultsArea').innerHTML='<div class="ae-placeholder"><i class="fas fa-inbox"></i><strong>No rows found.</strong></div>';
        return;
    }
    if(currentView==='timeline') renderTimeline(json.data);
    else if(currentView==='compact') renderCompact(json.data);
    else renderTable(json.data);
}

function actionBadge(action) {
    if(!action)return '';
    const a=String(action).toLowerCase();
    let cls='ab-default',icon='fas fa-circle';
    if(a.includes('login'))          {cls='ab-login';icon='fas fa-sign-in-alt';}
    else if(a.includes('logout'))    {cls='ab-logout';icon='fas fa-sign-out-alt';}
    else if(a.includes('creat')||a.includes('add')||a.endsWith('_created')){cls='ab-create';icon='fas fa-plus-circle';}
    else if(a.includes('updat')||a.includes('edit')||a.endsWith('_updated')){cls='ab-update';icon='fas fa-edit';}
    else if(a.includes('delet')||a.includes('remov')||a.endsWith('_deleted')){cls='ab-delete';icon='fas fa-trash';}
    else if(a.includes('fail')||a.includes('error')||a.includes('block')){cls='ab-failure';icon='fas fa-exclamation-circle';}
    return '<span class="ab '+cls+'"><i class="'+icon+'"></i>'+esc(action)+'</span>';
}

function statusBadge(s) {
    if(!s)return '<span style="opacity:.35">\u2014</span>';
    const cls={success:'sb-success',failure:'sb-failure',pending:'sb-pending'}[s]||'';
    const icon={success:'fas fa-check',failure:'fas fa-times',pending:'fas fa-clock'}[s]||'fas fa-circle';
    return '<span class="sb '+cls+'"><i class="'+icon+'"></i>'+esc(s)+'</span>';
}

function moduleBadge(m){if(!m)return '<span style="opacity:.35">\u2014</span>';return '<span class="mb">'+esc(m)+'</span>';}

function userCell(name,email){
    if(!name&&!email)return '<span style="opacity:.35">\u2014</span>';
    const init=(name||email||'?')[0].toUpperCase();
    return '<div class="uc"><div class="uc-av">'+esc(init)+'</div><div><span class="uc-name">'+esc(name||'\u2014')+'</span><span class="uc-email">'+esc(email||'')+'</span></div></div>';
}

function tsCell(ts){
    if(!ts)return '<span style="opacity:.35">\u2014</span>';
    const d=new Date(ts);
    return '<span class="ts-rel" title="'+esc(d.toLocaleString())+'">'+esc(timeAgo(d))+'</span><span class="ts-abs">'+esc(d.toLocaleString())+'</span>';
}

function timeAgo(d){
    const s=Math.floor((Date.now()-d.getTime())/1000);
    if(s<60)return s+'s ago';if(s<3600)return Math.floor(s/60)+'m ago';
    if(s<86400)return Math.floor(s/3600)+'h ago';if(s<604800)return Math.floor(s/86400)+'d ago';
    return d.toLocaleDateString();
}

// Store diff changes keyed by id to avoid inline onclick JSON injection
const diffStore = {};

function diffCell(oldVal,newVal,idx){
    if(!oldVal&&!newVal)return '<span style="opacity:.35">\u2014</span>';
    try{
        const o=oldVal?JSON.parse(oldVal):null;
        const n=newVal?JSON.parse(newVal):null;
        const keys=new Set([...Object.keys(o||{}),...Object.keys(n||{})]);
        const changes=[];
        keys.forEach(k=>{
            const ov=(o||{})[k],nv=(n||{})[k];
            if(JSON.stringify(ov)!==JSON.stringify(nv))changes.push({k,ov,nv});
        });
        if(!changes.length)return '<span style="opacity:.35">\u2014</span>';
        const id='df'+idx;
        diffStore[id]=changes;
        const visible=changes.slice(0,2);
        let html='<div class="diff-wrap" id="'+id+'">';
        visible.forEach(c=>{
            html+='<span class="diff-old">- '+esc(c.k)+': '+esc(JSON.stringify(c.ov))+'</span>';
            html+='<span class="diff-new">+ '+esc(c.k)+': '+esc(JSON.stringify(c.nv))+'</span>';
        });
        if(changes.length>2){
            html+='<button type="button" class="diff-toggle" data-diffid="'+esc(id)+'">+'+(changes.length-2)+' more</button>';
        }
        return html+'</div>';
    }catch(e){
        return '<span style="font-size:10px;color:var(--text-m);">'+esc(String(oldVal||'').slice(0,40))+' \u2192 '+esc(String(newVal||'').slice(0,40))+'</span>';
    }
}

// Event delegation for diff expand buttons (avoids inline onclick with JSON)
document.addEventListener('click', function(e) {
    const btn = e.target.closest('button[data-diffid]');
    if (!btn) return;
    const id = btn.dataset.diffid;
    const changes = diffStore[id];
    if (!changes) return;
    const el = document.getElementById(id);
    if (!el) return;
    let html='';
    changes.forEach(c=>{
        html+='<span class="diff-old">- '+esc(c.k)+': '+esc(JSON.stringify(c.ov))+'</span>';
        html+='<span class="diff-new">+ '+esc(c.k)+': '+esc(JSON.stringify(c.nv))+'</span>';
    });
    el.innerHTML=html;
});

// Render the DB-stored `changes` JSON column (field-level diff format: {field:{old,new}})
function renderDbChanges(changesJson, idx) {
    try {
        const data = typeof changesJson === 'string' ? JSON.parse(changesJson) : changesJson;
        if (!data || typeof data !== 'object') return '<span style="opacity:.35">\u2014</span>';
        const fields = Object.keys(data);
        if (!fields.length) return '<span style="opacity:.35">\u2014</span>';
        const id = 'dbc' + idx;
        const changes = fields.map(k => ({k, ov: data[k]['old'], nv: data[k]['new']}));
        diffStore[id] = changes;
        const visible = changes.slice(0, 2);
        let html = '<div class="diff-wrap" id="' + id + '">';
        visible.forEach(c => {
            const oldStr = c.ov != null ? JSON.stringify(c.ov) : '(empty)';
            const newStr = c.nv != null ? JSON.stringify(c.nv) : '(empty)';
            html += '<span class="diff-old">\u2212 ' + esc(c.k) + ': ' + esc(oldStr) + '</span>';
            html += '<span class="diff-new">+ ' + esc(c.k) + ': ' + esc(newStr) + '</span>';
        });
        if (changes.length > 2) {
            html += '<button type="button" class="diff-toggle" data-diffid="' + esc(id) + '">+' + (changes.length - 2) + ' more</button>';
        }
        return html + '</div>';
    } catch(e) {
        return '<span style="font-size:10px;color:var(--text-m);">' + esc(String(changesJson).slice(0, 60)) + '</span>';
    }
}

// ─── Compact view ────────────────────────────────────────────────────────────
// Renders a dense single-line-per-event table with a + button to expand raw JSON.
let compactExpanded = {};

function renderCompact(rows) {
    if (!rows || !rows.length) {
        document.getElementById('resultsArea').innerHTML='<div class="ae-placeholder"><i class="fas fa-inbox"></i><strong>No rows found.</strong></div>';
        return;
    }
    compactExpanded = {};
    const cols = Object.keys(rows[0]);
    // Core columns always shown inline (in priority order)
    const INLINE = ['id','action','module','user_name','user_id','entity_name','resource_type','status','ip_address','created_at'];
    // Detail columns: everything not in INLINE (plus user_email which is a join helper)
    const DETAIL_SKIP = new Set([...INLINE, 'user_email']);
    const COMPACT_TRUNC = 40; // max chars for inline cell truncation

    const inline = INLINE.filter(c => cols.includes(c));
    const hasUser = cols.includes('user_email') || cols.includes('user_id');

    let html = '<table class="ae-compact"><thead><tr>';
    html += '<th style="width:22px;"></th>'; // expand button col
    inline.forEach(c => { html += '<th>' + esc(c.replace(/_/g,' ')) + '</th>'; });
    html += '</tr></thead><tbody>';

    rows.forEach((row, idx) => {
        const rid = 'cr' + idx;
        const action = String(row['action'] || '').toLowerCase();
        const status = String(row['status'] || '').toLowerCase();
        let sevClass = 'sev-info';
        if (status === 'failure' || action.includes('fail') || action.includes('block')) sevClass = 'sev-error';
        else if (action.includes('delet')) sevClass = 'sev-warn';
        else if (action.includes('creat') || action.includes('login') || status === 'success') sevClass = 'sev-ok';

        html += '<tr>';
        html += '<td style="padding:3px 8px;"><button type="button" class="ae-exp-btn" data-crid="' + rid + '" title="Expand details">+</button></td>';
        inline.forEach(c => {
            const raw = row[c];
            if (raw == null || raw === '') { html += '<td><span style="opacity:.3">\u2014</span></td>'; return; }
            const s = String(raw);
            if (c === 'action')     { html += '<td>' + actionBadge(s) + '</td>'; return; }
            if (c === 'status')     { html += '<td>' + statusBadge(s) + '</td>'; return; }
            if (c === 'module')     { html += '<td>' + moduleBadge(s) + '</td>'; return; }
            if (c === 'created_at') { html += '<td style="font-size:10px;color:var(--text-m);">' + esc(s) + '</td>'; return; }
            if (c === 'user_name')  { html += '<td>' + userCell(s, row['user_email'] || '') + '</td>'; return; }
            if (c === 'user_id')    { html += '<td style="font-family:monospace;font-size:10px;color:var(--text-m);">#' + esc(s) + '</td>'; return; }
            if (c === 'ip_address') { html += '<td><span class="ip-badge">' + esc(s) + '</span></td>'; return; }
            html += '<td title="' + esc(s) + '">' + esc(s.length > COMPACT_TRUNC ? s.slice(0, COMPACT_TRUNC - 2) + '\u2026' : s) + '</td>';
        });
        html += '</tr>';

        // Detail/raw expansion row (hidden initially)
        const detail = {};
        cols.forEach(c => { detail[c] = row[c]; });
        html += '<tr class="ae-exp-row" id="' + rid + '" style="display:none;">';
        html += '<td></td><td colspan="' + inline.length + '">';
        // Field grid
        html += '<div style="display:flex;flex-wrap:wrap;gap:8px 18px;margin-bottom:8px;">';
        cols.forEach(c => {
            if (DETAIL_SKIP.has(c)) return;
            const v = row[c];
            if (v == null || v === '') return;
            const s = String(v);
            const isJson = s.startsWith('{') || s.startsWith('[');
            let valHtml;
            if (isJson) {
                try { valHtml = '<pre class="ae-raw-pre">' + esc(JSON.stringify(JSON.parse(s), null, 2)) + '</pre>'; }
                catch(e) { valHtml = '<span>' + esc(s) + '</span>'; }
            } else {
                valHtml = '<span style="word-break:break-all;">' + esc(s) + '</span>';
            }
            html += '<div style="min-width:180px;max-width:360px;flex:1;">'
                  + '<div style="font-size:9.5px;font-weight:700;color:var(--text-m);text-transform:uppercase;letter-spacing:.6px;margin-bottom:2px;">' + esc(c.replace(/_/g,' ')) + '</div>'
                  + valHtml + '</div>';
        });
        html += '</div>';
        // Changes diff
        if (row['changes'] || row['old_values'] || row['new_values']) {
            const diffHtml = row['changes'] ? renderDbChanges(row['changes'], idx) : diffCell(row['old_values'], row['new_values'], idx);
            html += '<div style="margin-top:4px;">' + diffHtml + '</div>';
        }
        // Raw JSON
        html += '<details style="margin-top:6px;"><summary style="font-size:10px;cursor:pointer;color:var(--text-m);">Raw JSON</summary>'
              + '<pre class="ae-raw-pre" style="margin-top:4px;max-height:220px;overflow-y:auto;">' + esc(JSON.stringify(row, null, 2)) + '</pre></details>';
        html += '</td></tr>';
    });

    document.getElementById('resultsArea').innerHTML = html + '</tbody></table>';
}

// Event delegation for compact expand buttons
document.addEventListener('click', function(e) {
    const btn = e.target.closest('button[data-crid]');
    if (!btn) return;
    const id = btn.dataset.crid;
    const row = document.getElementById(id);
    if (!row) return;
    const expanded = row.style.display !== 'none';
    row.style.display = expanded ? 'none' : '';
    btn.textContent = expanded ? '+' : '\u2212';
});

function renderTable(rows){
    const cols=Object.keys(rows[0]);
    const isFullLog=cols.includes('action')&&cols.includes('created_at');
    let dc=cols.filter(c=>c!=='user_email'&&c!=='user_id');
    // If old_values/new_values are present, replace them with a unified "changes" column.
    // Also remove any existing "changes" DB column to avoid duplicates before re-appending.
    const hasDiff=cols.includes('old_values')&&cols.includes('new_values');
    if(hasDiff)dc=[...dc.filter(c=>c!=='old_values'&&c!=='new_values'&&c!=='changes'),'changes'];

    let html='<table class="ae-table"><thead><tr>';
    if(isFullLog)html+='<th style="width:12px;"></th>';
    dc.forEach(c=>{html+='<th>'+esc(c.replace(/_/g,' '))+'</th>';});
    html+='</tr></thead><tbody>';

    rows.forEach((row,idx)=>{
        const action=String(row['action']||'').toLowerCase();
        const status=String(row['status']||'').toLowerCase();
        let sevClass='sev-info';
        if(status==='failure'||action.includes('fail')||action.includes('block'))sevClass='sev-error';
        else if(action.includes('delet'))sevClass='sev-warn';
        else if(action.includes('creat')||action.includes('login')||status==='success')sevClass='sev-ok';

        html+='<tr>';
        if(isFullLog)html+='<td style="padding-right:0;vertical-align:middle;"><span class="sev '+sevClass+'"></span></td>';
        dc.forEach(c=>{
            if(c==='changes'){
                // Prefer the DB-computed changes JSON column if present and non-null
                const dbChanges=row['changes'];
                if(dbChanges){html+='<td>'+renderDbChanges(dbChanges,idx)+'</td>';}
                else{html+='<td>'+diffCell(row['old_values'],row['new_values'],idx)+'</td>';}
                return;
            }
            const raw=row[c];
            if(raw==null||raw===''){html+='<td><span style="opacity:.35">\u2014</span></td>';return;}
            const s=String(raw);
            if(c==='action'){html+='<td>'+actionBadge(s)+'</td>';return;}
            if(c==='status'){html+='<td>'+statusBadge(s)+'</td>';return;}
            if(c==='module'){html+='<td>'+moduleBadge(s)+'</td>';return;}
            if(c==='user_name'){html+='<td>'+userCell(s,row['user_email']||'')+'</td>';return;}
            if(c==='created_at'){html+='<td>'+tsCell(s)+'</td>';return;}
            if(c==='readable_message'){html+='<td style="font-size:11.5px;">'+esc(s)+'</td>';return;}
            if(c==='ip_address'){html+='<td><span class="ip-badge">'+esc(s)+'</span></td>';return;}
            if((c==='old_values'||c==='new_values')&&(s.startsWith('{')||s.startsWith('['))){
                try{const p=JSON.stringify(JSON.parse(s),null,2);html+='<td><pre>'+esc(p)+'</pre></td>';}catch(e){html+='<td>'+esc(s)+'</td>';}
                return;
            }
            html+='<td title="'+esc(s)+'">'+esc(s.length>120?s.slice(0,120)+'\u2026':s)+'</td>';
        });
        html+='</tr>';
    });
    document.getElementById('resultsArea').innerHTML=html+'</tbody></table>';
}

function renderTimeline(rows){
    let html='<div class="ae-timeline">';
    rows.forEach((row,idx)=>{
        const action=String(row['action']||'').toLowerCase();
        const status=String(row['status']||'').toLowerCase();
        let dotBg='rgba(0,240,255,.2)',dotColor='var(--cyan)',icon='fas fa-circle';
        if(action.includes('login')){dotBg='rgba(0,240,255,.2)';icon='fas fa-sign-in-alt';}
        else if(action.includes('logout')){dotBg='rgba(136,146,166,.2)';dotColor='#8892a6';icon='fas fa-sign-out-alt';}
        else if(action.includes('creat')||action.endsWith('_created')){dotBg='rgba(46,204,113,.2)';dotColor='#2ecc71';icon='fas fa-plus';}
        else if(action.includes('updat')||action.endsWith('_updated')){dotBg='rgba(243,156,18,.2)';dotColor='#f39c12';icon='fas fa-pen';}
        else if(action.includes('delet')||action.endsWith('_deleted')){dotBg='rgba(231,76,60,.2)';dotColor='#e74c3c';icon='fas fa-trash';}
        else if(status==='failure'||action.includes('fail')){dotBg='rgba(231,76,60,.25)';dotColor='#e74c3c';icon='fas fa-exclamation';}
        const msg=row['readable_message']||row['action']||'(no message)';
        const user=row['user_name']||row['user_email']||(row['user_id']?'User #'+row['user_id']:'System');
        html+='<div class="ae-tl-item"><div class="ae-tl-dot" style="background:'+dotBg+';color:'+dotColor+'"><i class="'+icon+'"></i></div>';
        html+='<div class="ae-tl-body"><div class="ae-tl-header">';
        if(row['action'])html+=actionBadge(row['action']);
        if(row['status'])html+=statusBadge(row['status']);
        if(row['module'])html+=moduleBadge(row['module']);
        html+='</div><div class="ae-tl-msg">'+esc(msg)+'</div>';
        html+='<div class="ae-tl-meta"><span><i class="fas fa-user"></i> '+esc(user)+'</span>';
        if(row['ip_address'])html+='<span><i class="fas fa-network-wired"></i> <span class="ip-badge">'+esc(row['ip_address'])+'</span></span>';
        if(row['created_at'])html+='<span><i class="fas fa-clock"></i> '+tsCell(row['created_at'])+'</span>';
        html+='</div>';
        if(row['changes']||row['old_values']||row['new_values']){
            const diffHtml=row['changes']?renderDbChanges(row['changes'],idx):diffCell(row['old_values'],row['new_values'],idx);
            html+='<div style="margin-top:6px;">'+diffHtml+'</div>';
        }
        html+='</div></div>';
    });
    document.getElementById('resultsArea').innerHTML=html+'</div>';
}

function loadTemplate(spec){
    clearAll(false);
    const exprs=spec.select||['*'];
    document.getElementById('selectWrap').innerHTML='';
    exprs.forEach(e=>{
        const inp=document.createElement('input');
        inp.type='text';inp.className='ae-col-in sel-expr';inp.value=e;
        inp.setAttribute('list','dlCols');inp.ondblclick=()=>inp.remove();
        document.getElementById('selectWrap').appendChild(inp);
    });
    const fc=(col,op)=>(spec.where||[]).find(w=>w.col===col&&w.op===op);
    const sv=(id,v)=>{if(v!=null)document.getElementById(id).value=v;};
    sv('fModule',fc('module','=')?.val||'');
    sv('fAction',fc('action','=')?.val||'');
    sv('fStatus',fc('status','=')?.val||'');
    sv('fUserRole',fc('user_role','=')?.val||'');
    sv('fUserId',fc('user_id','=')?.val||'');
    const df=fc('created_at','>=');if(df)sv('fDateFrom',df.val.substring(0,10));
    const dt=fc('created_at','<=');if(dt)sv('fDateTo',dt.val.substring(0,10));
    sv('fGroupBy',(spec.group_by||[]).join(', '));
    sv('fOrderBy',spec.order_by||'created_at');
    sv('fOrderDir',spec.order_dir||'DESC');
    sv('fLimit',spec.limit||100);
    runQuery();
}

function saveQuery(){
    const name=prompt('Name for this query:');if(!name)return;
    const saved=JSON.parse(localStorage.getItem('aeQ')||'[]');
    saved.push({name,spec:buildSpec()});
    localStorage.setItem('aeQ',JSON.stringify(saved));
    renderSaved();
}
function renderSaved(){
    const saved=JSON.parse(localStorage.getItem('aeQ')||'[]');
    const list=document.getElementById('savedList');
    if(!saved.length){list.innerHTML='<p style="font-size:10.5px;color:var(--text-m);">No saved queries yet.</p>';return;}
    list.innerHTML='';
    saved.forEach((item,idx)=>{
        const d=document.createElement('div');d.className='sv-item';
        d.innerHTML='<span class="sv-name" title="'+esc(item.name)+'">'+esc(item.name)+'</span><button type="button" class="sv-load" onclick="loadSaved('+idx+')">Load</button><button type="button" class="sv-del" onclick="delSaved('+idx+')">\u2715</button>';
        list.appendChild(d);
    });
}
function loadSaved(i){const s=JSON.parse(localStorage.getItem('aeQ')||'[]');if(s[i])loadTemplate(s[i].spec);}
function delSaved(i){const s=JSON.parse(localStorage.getItem('aeQ')||'[]');s.splice(i,1);localStorage.setItem('aeQ',JSON.stringify(s));renderSaved();}

function exportResult(fmt){
    if(!lastSpec){alert('Run a query first.');return;}
    const s={...lastSpec,limit:10000};
    window.location.href='/admin/audit/export?format='+fmt
        +'&select='+encodeURIComponent((s.select||['*']).join(','))
        +'&where='+encodeURIComponent(JSON.stringify(s.where||[]))
        +'&group_by='+encodeURIComponent((s.group_by||[]).join(','))
        +'&order_by='+encodeURIComponent(s.order_by||'created_at')
        +'&order_dir='+(s.order_dir||'DESC')+'&limit=10000';
}

function clearAll(resetResults=true){
    ['fModule','fAction','fStatus','fUserRole','fUserId','fDateFrom','fDateTo','fSearch','fGroupBy','fUserName','fUserEmail']
        .forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
    document.getElementById('fOrderBy').value='created_at';
    document.getElementById('fOrderDir').value='DESC';
    document.getElementById('fLimit').value='100';
    document.getElementById('selectWrap').innerHTML='';
    const inp=document.createElement('input');inp.type='text';inp.className='ae-col-in sel-expr';
    inp.value='*';inp.setAttribute('list','dlCols');inp.ondblclick=()=>inp.remove();
    document.getElementById('selectWrap').appendChild(inp);
    // Reset results first so lastSpec=null, then clear chips silently
    if(resetResults){resetResults2();}
    // Clear exclusion and inclusion chips silently (no re-run, results already wiped)
    document.getElementById('exChips').innerHTML = '';
    document.getElementById('inChips').innerHTML = '';
}
function resetResults2(){
    document.getElementById('sqlBar').style.display='none';
    document.getElementById('resultMeta').textContent='';
    document.getElementById('resultsArea').innerHTML='<div class="ae-placeholder"><i class="fas fa-database"></i><strong>No query run yet</strong></div>';
    lastSpec=null;
}

// ─── Multi-field Exclusion / Inclusion Filters ───────────────────────────────
const FIELD_OPTS = {
    action:        <?= json_encode(array_column($actions, 'action'), JSON_UNESCAPED_UNICODE) ?>,
    module:        <?= json_encode(array_map(fn($m) => $m['module'], $modules), JSON_UNESCAPED_UNICODE) ?>,
    status:        ['success','failure','pending'],
    resource_type: <?= json_encode(array_column($resourceTypes, 'resource_type'), JSON_UNESCAPED_UNICODE) ?>,
    user_name:     <?= json_encode(array_column($users, 'name'), JSON_UNESCAPED_UNICODE) ?>,
    user_id:       <?= json_encode(array_column($users, 'id'), JSON_UNESCAPED_UNICODE) ?>,
    entity_name:   <?= json_encode(array_column($entityNames, 'entity_name'), JSON_UNESCAPED_UNICODE) ?>,
    ip_address:    <?= json_encode(array_column($ipAddresses, 'ip_address'), JSON_UNESCAPED_UNICODE) ?>,
    id:            [],
};

function switchMfDatalist(dlId, selId, inputId) {
    const col = document.getElementById(selId).value;
    const dl = document.getElementById(dlId);
    dl.innerHTML = (FIELD_OPTS[col] || []).map(v => '<option value="' + esc(String(v)) + '">').join('');
    document.getElementById(inputId).value = '';
}

function addMfChip(chipsId, selId, inputId, type) {
    const col = document.getElementById(selId).value;
    const val = document.getElementById(inputId).value.trim();
    if (!col || !val) return;
    // Prevent duplicate col+val
    const dup = [...document.querySelectorAll('#' + chipsId + ' .ae-mf-chip')]
        .some(c => c.dataset.col === col && c.dataset.val === val);
    if (dup) { document.getElementById(inputId).value = ''; return; }
    const chip = document.createElement('span');
    chip.className = 'ae-mf-chip ae-mf-chip-' + type;
    chip.dataset.col = col;
    chip.dataset.val = val;
    const colLabel = col.replace(/_/g, ' ');
    chip.innerHTML = '<span class="ae-mf-chip-label">' + esc(colLabel) + ': ' + esc(val) + '</span>'
        + ' <button type="button" class="ae-mf-chip-del" onclick="this.closest(\'.ae-mf-chip\').remove()">&#x2715;</button>';
    document.getElementById(chipsId).appendChild(chip);
    document.getElementById(inputId).value = '';
}

function clearMfChips(chipsId) {
    const had = document.querySelectorAll('#' + chipsId + ' .ae-mf-chip').length > 0;
    document.getElementById(chipsId).innerHTML = '';
    if (had && lastSpec) runQuery();
}

function esc(s){
    return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ─── Refresh & Auto-refresh ───────────────────────────────────────────────────
let arTimer = null;
let arIntervalMs = 0;
let arCountdown = 0;
let arCountdownTimer = null;

function manualRefresh(e) {
    e.preventDefault();
    e.stopPropagation();
    const icon = document.getElementById('refreshIcon');
    icon.classList.add('fa-spin');
    const sqlPanel = document.getElementById('sqlPanel');
    const p = sqlPanel && sqlPanel.classList.contains('visible') ? runRawSql() : runQuery();
    Promise.resolve(p).finally(() => { setTimeout(() => icon.classList.remove('fa-spin'), 300); });
}

function toggleArMenu(e) {
    e.preventDefault();
    e.stopPropagation();
    document.getElementById('arMenu').classList.toggle('open');
}

function setAutoRefresh(ms) {
    // Clear existing timers
    if (arTimer) { clearInterval(arTimer); arTimer = null; }
    if (arCountdownTimer) { clearInterval(arCountdownTimer); arCountdownTimer = null; }
    arIntervalMs = ms;

    // Update active state on options
    document.querySelectorAll('.ae-ar-opt').forEach(o => {
        o.classList.toggle('active', parseInt(o.dataset.ms) === ms || (ms === 0 && o.dataset.ms === '0'));
    });

    // Update the ⋯ button appearance
    const trigger = document.getElementById('arTrigger');
    if (ms > 0) {
        trigger.classList.add('ar-on');
        // Start countdown display
        arCountdown = ms / 1000;
        updateArBadge();
        arCountdownTimer = setInterval(() => {
            arCountdown = Math.max(0, arCountdown - 1);
            updateArBadge();
        }, 1000);
        // Start refresh interval
        arTimer = setInterval(() => {
            arCountdown = ms / 1000;
            const icon = document.getElementById('refreshIcon');
            icon.classList.add('fa-spin');
            const sqlPanel = document.getElementById('sqlPanel');
            const p = sqlPanel && sqlPanel.classList.contains('visible') ? runRawSql() : runQuery();
            Promise.resolve(p).finally(() => setTimeout(() => icon.classList.remove('fa-spin'), 300));
        }, ms);
    } else {
        trigger.classList.remove('ar-on');
        updateArBadge(true);
    }
    document.getElementById('arMenu').classList.remove('open');
}

function updateArBadge(clear) {
    const trigger = document.getElementById('arTrigger');
    // Remove existing badge
    const existing = trigger.querySelector('.ae-ar-badge');
    if (existing) existing.remove();
    if (clear || arIntervalMs === 0) { trigger.innerHTML = '&#x22EF;'; return; }
    const s = arCountdown;
    const label = s >= 60 ? Math.ceil(s/60)+'m' : s+'s';
    trigger.innerHTML = '&#x22EF;<span class="ae-ar-badge">'+label+'</span>';
}

// Close auto-refresh menu when clicking outside
document.addEventListener('click', function(e) {
    const menu = document.getElementById('arMenu');
    const trigger = document.getElementById('arTrigger');
    if (menu && trigger && !menu.contains(e.target) && !trigger.contains(e.target)) {
        menu.classList.remove('open');
    }
});

// Handle auto-refresh option clicks (registered in main DOMContentLoaded below)

document.addEventListener('DOMContentLoaded',()=>{
    renderSaved();
    // Populate datalists with default field options
    switchMfDatalist('dlEx', 'exColSel', 'exInput');
    switchMfDatalist('dlIn', 'inColSel', 'inInput');
    document.addEventListener('keydown',e=>{
        if((e.ctrlKey||e.metaKey)&&e.key==='Enter'){
            e.preventDefault();
            const sqlPanel=document.getElementById('sqlPanel');
            if(sqlPanel.classList.contains('visible'))runRawSql();
            else runQuery();
        }
    });
    // Auto-refresh option click handlers
    document.querySelectorAll('.ae-ar-opt').forEach(opt => {
        opt.addEventListener('click', (e) => {
            e.stopPropagation();
            const ms = opt.dataset.ms;
            if (ms === 'custom') {
                const val = prompt('Auto-refresh interval (seconds):', '60');
                if (val === null) return;
                const n = parseInt(val);
                if (!n || n < 5) { alert('Minimum interval is 5 seconds.'); return; }
                setAutoRefresh(n * 1000);
            } else {
                setAutoRefresh(parseInt(ms));
            }
        });
    });
    document.getElementById('resultsArea').innerHTML='<div class="ae-placeholder"><i class="fas fa-spinner fa-spin"></i><strong>Loading latest events...</strong></div>';
    runQuery();
});
</script>
<?php View::endSection(); ?>
