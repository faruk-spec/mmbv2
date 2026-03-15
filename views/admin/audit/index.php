<?php use Core\View; use Core\Auth; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<!-- ================================================================
     AUDIT EXPLORER  –  Apache Superset-inspired UI
     Left sidebar: filter controls + saved queries + templates
     Right panel:  SELECT builder, result table, SQL preview
     ================================================================ -->
<style>
/* ── Layout ── */
.ae-wrap{display:grid;grid-template-columns:280px 1fr;gap:0;height:calc(100vh - 130px);min-height:500px;overflow:hidden;border:1px solid var(--border-color);border-radius:14px;}

/* ── Left sidebar ── */
.ae-sidebar{background:var(--bg-secondary);border-right:1px solid var(--border-color);overflow-y:auto;display:flex;flex-direction:column;}
.ae-sidebar-header{padding:16px 18px 12px;border-bottom:1px solid var(--border-color);flex-shrink:0;}
.ae-sidebar-header h2{font-size:14px;font-weight:700;margin:0 0 2px;}
.ae-sidebar-header p{font-size:11px;color:var(--text-secondary);margin:0;}

.ae-section{border-bottom:1px solid var(--border-color);}
.ae-section-hdr{padding:10px 18px;display:flex;align-items:center;justify-content:space-between;cursor:pointer;user-select:none;}
.ae-section-hdr span{font-size:11px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--text-secondary);}
.ae-section-hdr i{font-size:10px;color:var(--text-secondary);transition:.15s;}
.ae-section-hdr.open i{transform:rotate(90deg);}
.ae-section-body{padding:0 18px 14px;display:none;}
.ae-section-body.open{display:block;}

.ae-filter-label{font-size:11px;color:var(--text-secondary);margin:10px 0 4px;display:block;font-weight:600;}
.ae-filter-input{width:100%;padding:6px 10px;border-radius:8px;border:1px solid var(--border-color);background:var(--bg-card);color:inherit;font-size:12px;}
.ae-filter-input:focus{outline:none;border-color:var(--cyan);}

.ae-run-btn{margin:14px 18px;background:var(--cyan);color:#000;border:none;border-radius:10px;padding:10px 0;font-weight:700;font-size:13px;cursor:pointer;width:calc(100% - 36px);letter-spacing:.3px;transition:.15s;}
.ae-run-btn:hover{opacity:.88;}
.ae-run-btn.loading{opacity:.6;cursor:not-allowed;}

.ae-saved-item{display:flex;align-items:center;gap:6px;padding:6px 8px;border-radius:8px;cursor:pointer;transition:.1s;margin-bottom:2px;}
.ae-saved-item:hover{background:var(--bg-card);}
.ae-saved-item .name{flex:1;font-size:12px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;}
.ae-saved-load{background:none;border:1px solid var(--border-color);color:var(--text-secondary);border-radius:6px;padding:2px 7px;font-size:10px;cursor:pointer;}
.ae-saved-del{background:none;border:none;color:#e74c3c;cursor:pointer;padding:0 2px;font-size:12px;}

.ae-tpl-btn{display:block;width:100%;text-align:left;background:var(--bg-card);border:1px solid var(--border-color);border-radius:8px;padding:7px 10px;font-size:11px;cursor:pointer;margin-bottom:5px;color:inherit;transition:.1s;}
.ae-tpl-btn:hover{border-color:var(--cyan);color:var(--cyan);}

/* ── Right main panel ── */
.ae-main{display:flex;flex-direction:column;overflow:hidden;background:var(--bg-card);}
.ae-toolbar{padding:12px 16px;border-bottom:1px solid var(--border-color);display:flex;align-items:center;gap:8px;flex-wrap:wrap;flex-shrink:0;}
.ae-toolbar-title{font-size:13px;font-weight:600;color:var(--text-secondary);margin-right:8px;}
.ae-result-meta{font-size:12px;color:var(--text-secondary);margin-left:auto;}

/* SELECT builder */
.ae-builder{padding:14px 16px;border-bottom:1px solid var(--border-color);flex-shrink:0;}
.ae-builder-row{display:flex;align-items:center;gap:8px;margin-bottom:6px;flex-wrap:wrap;}
.ae-builder-row label{font-size:11px;color:var(--text-secondary);font-weight:600;white-space:nowrap;min-width:60px;}
.ae-builder-input{flex:1;min-width:120px;padding:5px 9px;border-radius:7px;border:1px solid var(--border-color);background:var(--bg-secondary);color:inherit;font-size:12px;}
.ae-builder-input:focus{outline:none;border-color:var(--cyan);}
.ae-small-btn{background:none;border:1px solid var(--border-color);color:var(--text-secondary);border-radius:6px;padding:3px 8px;font-size:11px;cursor:pointer;}
.ae-small-btn:hover{border-color:var(--cyan);color:var(--cyan);}

/* SQL preview */
.ae-sql{background:var(--bg-secondary);border-bottom:1px solid var(--border-color);padding:8px 16px;font-family:monospace;font-size:11px;color:var(--cyan);overflow-x:auto;white-space:nowrap;flex-shrink:0;display:none;}

/* Results */
.ae-results{flex:1;overflow:auto;}
.ae-placeholder{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:var(--text-secondary);gap:8px;}
.ae-placeholder i{font-size:2.5rem;opacity:.35;}

.ae-table{width:100%;border-collapse:collapse;font-size:12px;}
.ae-table th{background:var(--bg-secondary);padding:8px 12px;text-align:left;font-weight:600;white-space:nowrap;position:sticky;top:0;z-index:2;border-bottom:1px solid var(--border-color);}
.ae-table td{padding:7px 12px;border-bottom:1px solid var(--border-color);white-space:nowrap;max-width:280px;overflow:hidden;text-overflow:ellipsis;}
.ae-table tbody tr:hover{background:var(--bg-secondary);}

.ae-stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;padding:12px 16px;border-bottom:1px solid var(--border-color);flex-shrink:0;}
.ae-stat-mini{text-align:center;}
.ae-stat-mini .v{font-size:1.2rem;font-weight:700;}
.ae-stat-mini .l{font-size:10px;color:var(--text-secondary);}

@media(max-width:800px){.ae-wrap{grid-template-columns:1fr;height:auto;}.ae-sidebar{max-height:320px;}}
</style>

<!-- Breadcrumb -->
<div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;font-size:13px;">
    <a href="/admin/logs" style="color:var(--text-secondary);">Logs</a>
    <span style="color:var(--text-secondary);">›</span>
    <span style="font-weight:600;">Audit Explorer</span>
    <a href="/admin/logs/activity" style="color:var(--cyan);margin-left:auto;font-size:12px;">← Activity Timeline</a>
</div>

<!-- Stats mini row above the explorer -->
<div class="ae-stats-row" style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;margin-bottom:14px;">
    <div class="ae-stat-mini"><div class="v" style="color:var(--cyan);"><?= number_format($stats['total'] ?? 0) ?></div><div class="l">Total Events</div></div>
    <div class="ae-stat-mini"><div class="v" style="color:var(--green);"><?= number_format($stats['unique_users'] ?? 0) ?></div><div class="l">Users</div></div>
    <div class="ae-stat-mini"><div class="v" style="color:var(--orange);"><?= number_format($stats['unique_actions'] ?? 0) ?></div><div class="l">Action Types</div></div>
    <div class="ae-stat-mini"><div class="v" style="color:#ff2ec4;"><?= number_format($stats['unique_modules'] ?? 0) ?></div><div class="l">Modules</div></div>
</div>

<!-- Main split layout -->
<div class="ae-wrap">

    <!-- ════════════ LEFT SIDEBAR ════════════ -->
    <div class="ae-sidebar">
        <div class="ae-sidebar-header">
            <h2>🔍 Audit Explorer</h2>
            <p>Filter, query and export audit data</p>
        </div>

        <!-- Run button (always visible) -->
        <button class="ae-run-btn" id="runBtn" onclick="runQuery()">▶ Run Query</button>

        <!-- ── Filters section ── -->
        <div class="ae-section">
            <div class="ae-section-hdr open" onclick="toggleSection(this)">
                <span>⚡ Filters</span><i class="fas fa-chevron-right"></i>
            </div>
            <div class="ae-section-body open">
                <label class="ae-filter-label">Module</label>
                <select id="fModule" class="ae-filter-input">
                    <option value="">All modules</option>
                    <?php foreach ($modules as $m): ?>
                        <option value="<?= View::e($m['module']) ?>"><?= View::e(ucfirst($m['module'])) ?></option>
                    <?php endforeach; ?>
                </select>

                <label class="ae-filter-label">Action</label>
                <select id="fAction" class="ae-filter-input">
                    <option value="">All actions</option>
                    <?php foreach ($actions as $a): ?>
                        <option value="<?= View::e($a['action']) ?>"><?= View::e($a['action']) ?></option>
                    <?php endforeach; ?>
                </select>

                <label class="ae-filter-label">Status</label>
                <select id="fStatus" class="ae-filter-input">
                    <option value="">All statuses</option>
                    <option value="success">Success</option>
                    <option value="failure">Failure</option>
                    <option value="pending">Pending</option>
                </select>

                <label class="ae-filter-label">User Role</label>
                <select id="fUserRole" class="ae-filter-input">
                    <option value="">All roles</option>
                    <?php foreach ($userRoles as $r): ?>
                        <option value="<?= View::e($r['user_role']) ?>"><?= View::e(ucfirst($r['user_role'])) ?></option>
                    <?php endforeach; ?>
                </select>

                <label class="ae-filter-label">User ID</label>
                <input type="number" id="fUserId" class="ae-filter-input" placeholder="e.g. 3">

                <label class="ae-filter-label">Date From</label>
                <input type="date" id="fDateFrom" class="ae-filter-input">

                <label class="ae-filter-label">Date To</label>
                <input type="date" id="fDateTo" class="ae-filter-input">

                <label class="ae-filter-label">Search keyword</label>
                <input type="text" id="fSearch" class="ae-filter-input" placeholder="action, message, IP…">
            </div>
        </div>

        <!-- ── GROUP BY / ORDER section ── -->
        <div class="ae-section">
            <div class="ae-section-hdr" onclick="toggleSection(this)">
                <span>📐 Group &amp; Sort</span><i class="fas fa-chevron-right"></i>
            </div>
            <div class="ae-section-body">
                <label class="ae-filter-label">Group By</label>
                <input type="text" id="fGroupBy" class="ae-filter-input" placeholder="e.g. action, module">

                <label class="ae-filter-label">Order By</label>
                <input type="text" id="fOrderBy" class="ae-filter-input" value="created_at">

                <label class="ae-filter-label">Order Direction</label>
                <select id="fOrderDir" class="ae-filter-input">
                    <option value="DESC">Descending (newest first)</option>
                    <option value="ASC">Ascending (oldest first)</option>
                </select>

                <label class="ae-filter-label">Limit</label>
                <input type="number" id="fLimit" class="ae-filter-input" value="100" min="1" max="10000">
            </div>
        </div>

        <!-- ── Templates section ── -->
        <div class="ae-section">
            <div class="ae-section-hdr" onclick="toggleSection(this)">
                <span>⚡ Templates</span><i class="fas fa-chevron-right"></i>
            </div>
            <div class="ae-section-body">
                <?php
                $templates = [
                    ['label'=>'Login events today',     'q'=>['select'=>['*'],'where'=>[['col'=>'action','op'=>'=','val'=>'login'],['col'=>'created_at','op'=>'>=','val'=>date('Y-m-d')]],'order_by'=>'created_at','limit'=>100]],
                    ['label'=>'All failures',            'q'=>['select'=>['*'],'where'=>[['col'=>'status','op'=>'=','val'=>'failure']],'order_by'=>'created_at','limit'=>200]],
                    ['label'=>'Top actions (grouped)',  'q'=>['select'=>['action','COUNT(*) AS cnt'],'where'=>[],'group_by'=>['action'],'order_by'=>'COUNT(*)','order_dir'=>'DESC','limit'=>20]],
                    ['label'=>'Per-module counts',      'q'=>['select'=>['module','COUNT(*) AS cnt'],'where'=>[],'group_by'=>['module'],'order_by'=>'COUNT(*)','order_dir'=>'DESC','limit'=>20]],
                    ['label'=>'Events by user',         'q'=>['select'=>['user_name','user_email','COUNT(*) AS cnt'],'where'=>[],'group_by'=>['user_name','user_email'],'order_by'=>'COUNT(*)','order_dir'=>'DESC','limit'=>50]],
                    ['label'=>'WhatsApp events',        'q'=>['select'=>['*'],'where'=>[['col'=>'module','op'=>'=','val'=>'whatsapp']],'order_by'=>'created_at','limit'=>100]],
                    ['label'=>'Admin actions',          'q'=>['select'=>['*'],'where'=>[['col'=>'user_role','op'=>'=','val'=>'admin']],'order_by'=>'created_at','limit'=>100]],
                    ['label'=>'Failures last 7 days',   'q'=>['select'=>['*'],'where'=>[['col'=>'status','op'=>'=','val'=>'failure'],['col'=>'created_at','op'=>'>=','val'=>date('Y-m-d',strtotime('-7 days'))]],'order_by'=>'created_at','limit'=>200]],
                ];
                foreach ($templates as $t): ?>
                    <button class="ae-tpl-btn" onclick='loadTemplate(<?= htmlspecialchars(json_encode($t['q']), ENT_QUOTES) ?>)'><?= htmlspecialchars($t['label']) ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- ── Saved queries section ── -->
        <div class="ae-section">
            <div class="ae-section-hdr" onclick="toggleSection(this)">
                <span>💾 Saved Queries</span><i class="fas fa-chevron-right"></i>
            </div>
            <div class="ae-section-body">
                <div id="savedList"><p id="noSaved" style="font-size:11px;color:var(--text-secondary);">No saved queries.</p></div>
                <button class="ae-small-btn" style="width:100%;margin-top:6px;" onclick="saveQuery()">+ Save current query</button>
            </div>
        </div>

        <!-- spacer -->
        <div style="flex:1;"></div>

        <!-- Export row -->
        <div style="padding:12px 18px;border-top:1px solid var(--border-color);display:flex;gap:6px;">
            <button class="ae-small-btn" style="flex:1;" onclick="exportResult('csv')">⬇ CSV</button>
            <button class="ae-small-btn" style="flex:1;" onclick="exportResult('json')">⬇ JSON</button>
            <button class="ae-small-btn" onclick="clearAll()" title="Reset">↺</button>
        </div>
    </div>

    <!-- ════════════ RIGHT MAIN PANEL ════════════ -->
    <div class="ae-main">

        <!-- SELECT columns toolbar -->
        <div class="ae-toolbar">
            <span class="ae-toolbar-title">SELECT</span>
            <div id="selectList" style="display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
                <input type="text" class="ae-builder-input select-expr" value="*" placeholder="column or COUNT(*)" style="width:180px;" list="dlCols">
            </div>
            <button class="ae-small-btn" onclick="addSelectCol()">+ col</button>
            <datalist id="dlCols">
                <?php foreach ($allowedCols as $c): ?><option value="<?= View::e($c) ?>"><?php endforeach; ?>
                <option value="COUNT(*)"><option value="COUNT(*) AS cnt">
            </datalist>
            <span class="ae-result-meta" id="resultMeta"></span>
        </div>

        <!-- SQL preview bar -->
        <div class="ae-sql" id="sqlBar"></div>

        <!-- Results area -->
        <div class="ae-results" id="resultsArea">
            <div class="ae-placeholder" id="placeholder">
                <i class="fas fa-database"></i>
                <div style="font-size:14px;font-weight:600;">No query run yet</div>
                <div style="font-size:12px;">Set filters on the left and click <strong>Run Query</strong></div>
            </div>
        </div>
    </div>

</div>

<script>
// ── Constants ────────────────────────────────────────────────────────────────
const COLS     = <?= json_encode(array_values($allowedCols)) ?>;
const ACTIONS  = <?= json_encode(array_column($actions,  'action')) ?>;
const MODULES  = <?= json_encode(array_column($modules,  'module')) ?>;
const ROLES    = <?= json_encode(array_column($userRoles,'user_role')) ?>;
const CSRF     = <?= json_encode($csrf_token) ?>;

// ── Section toggle ────────────────────────────────────────────────────────────
function toggleSection(hdr) {
    hdr.classList.toggle('open');
    hdr.nextElementSibling.classList.toggle('open');
}

// ── Add SELECT column ─────────────────────────────────────────────────────────
function addSelectCol(val) {
    const inp = document.createElement('input');
    inp.type = 'text'; inp.className = 'ae-builder-input select-expr';
    inp.placeholder = 'column or COUNT(*)'; inp.style.width = '160px';
    inp.value = val || ''; inp.setAttribute('list', 'dlCols');
    inp.ondblclick = () => inp.remove();
    document.getElementById('selectList').appendChild(inp);
}

// ── Build query spec from sidebar filters + SELECT ────────────────────────────
function buildSpec() {
    // SELECT
    const select = [...document.querySelectorAll('.select-expr')]
        .map(i => i.value.trim()).filter(Boolean);

    // WHERE from sidebar filters
    const where = [];
    const push  = (col, op, val) => { if (val !== '' && val != null) where.push({col, op, val}); };

    push('module',      '=',    document.getElementById('fModule').value);
    push('action',      '=',    document.getElementById('fAction').value);
    push('status',      '=',    document.getElementById('fStatus').value);
    push('user_role',   '=',    document.getElementById('fUserRole').value);
    push('user_id',     '=',    document.getElementById('fUserId').value);
    push('created_at',  '>=',   document.getElementById('fDateFrom').value ? document.getElementById('fDateFrom').value + ' 00:00:00' : '');
    push('created_at',  '<=',   document.getElementById('fDateTo').value   ? document.getElementById('fDateTo').value   + ' 23:59:59' : '');

    const search = document.getElementById('fSearch').value.trim();
    if (search) {
        where.push({col: 'readable_message', op: 'LIKE', val: '%' + search + '%'});
    }

    // GROUP BY
    const gbRaw = document.getElementById('fGroupBy').value.trim();
    const group_by = gbRaw ? gbRaw.split(',').map(s=>s.trim()).filter(Boolean) : [];

    return {
        select:    select.length ? select : ['*'],
        where,
        group_by,
        order_by:  document.getElementById('fOrderBy').value.trim() || 'created_at',
        order_dir: document.getElementById('fOrderDir').value,
        limit:     Math.min(10000, Math.max(1, parseInt(document.getElementById('fLimit').value) || 100)),
    };
}

// ── Run query ─────────────────────────────────────────────────────────────────
let lastSpec = null;
async function runQuery() {
    const spec = buildSpec();
    lastSpec   = spec;
    const btn  = document.getElementById('runBtn');
    btn.textContent = '⏳ Running…'; btn.classList.add('loading'); btn.disabled = true;

    try {
        const res  = await fetch('/admin/audit/query', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN': CSRF},
            body: JSON.stringify(spec),
        });
        const data = await res.json();
        if (!res.ok || data.error) { showError(data.error || 'Query failed.'); }
        else                        { showResults(data); }
    } catch(e) {
        showError(e.message);
    } finally {
        btn.textContent = '▶ Run Query'; btn.classList.remove('loading'); btn.disabled = false;
    }
}

function showError(msg) {
    document.getElementById('sqlBar').style.display = 'none';
    document.getElementById('resultMeta').textContent = '';
    document.getElementById('resultsArea').innerHTML =
        `<div class="ae-placeholder"><i class="fas fa-exclamation-triangle" style="color:#e74c3c"></i><div style="color:#e74c3c;">${esc(msg)}</div></div>`;
}

function showResults(json) {
    document.getElementById('resultMeta').textContent = json.count.toLocaleString() + ' row(s)';

    const bar = document.getElementById('sqlBar');
    bar.textContent = json.sql; bar.style.display = '';

    if (!json.data.length) {
        document.getElementById('resultsArea').innerHTML =
            '<div class="ae-placeholder"><i class="fas fa-inbox"></i><div>No rows found.</div></div>';
        return;
    }

    const cols = Object.keys(json.data[0]);
    let html = '<table class="ae-table"><thead><tr>' +
        cols.map(c => `<th>${esc(c)}</th>`).join('') + '</tr></thead><tbody>';

    json.data.forEach(row => {
        html += '<tr>' + cols.map(c => {
            let v = row[c] == null ? '' : String(row[c]);
            return `<td title="${esc(v)}">${esc(v.substring(0,120))}</td>`;
        }).join('') + '</tr>';
    });
    html += '</tbody></table>';
    document.getElementById('resultsArea').innerHTML = html;
}

function esc(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ── Load template ──────────────────────────────────────────────────────────────
function loadTemplate(spec) {
    clearAll(false);
    // SELECT
    const exprs = spec.select || ['*'];
    document.getElementById('selectList').innerHTML = '';
    exprs.forEach((e,i) => {
        if (i===0) {
            const inp = document.createElement('input');
            inp.type='text'; inp.className='ae-builder-input select-expr';
            inp.value=e; inp.style.width='180px'; inp.setAttribute('list','dlCols');
            inp.ondblclick = () => inp.remove();
            document.getElementById('selectList').appendChild(inp);
        } else { addSelectCol(e); }
    });
    // Filters
    const findCond = (col,op) => (spec.where||[]).find(w=>w.col===col&&w.op===op);
    const setVal = (id, val) => { if (val != null) document.getElementById(id).value = val; };
    setVal('fModule',   findCond('module','=')?.val    || '');
    setVal('fAction',   findCond('action','=')?.val    || '');
    setVal('fStatus',   findCond('status','=')?.val    || '');
    setVal('fUserRole', findCond('user_role','=')?.val || '');
    setVal('fUserId',   findCond('user_id','=')?.val   || '');
    const df = findCond('created_at','>=');
    const dt = findCond('created_at','<=');
    if (df) setVal('fDateFrom', df.val.substring(0,10));
    if (dt) setVal('fDateTo',   dt.val.substring(0,10));
    setVal('fGroupBy',  (spec.group_by||[]).join(', '));
    setVal('fOrderBy',  spec.order_by  || 'created_at');
    setVal('fOrderDir', spec.order_dir || 'DESC');
    setVal('fLimit',    spec.limit     || 100);
}

// ── Saved queries ──────────────────────────────────────────────────────────────
function saveQuery() {
    const name = prompt('Query name:'); if (!name) return;
    const saved = JSON.parse(localStorage.getItem('auditQueries')||'[]');
    saved.push({name, spec: buildSpec(), ts: Date.now()});
    localStorage.setItem('auditQueries', JSON.stringify(saved));
    renderSaved();
}
function renderSaved() {
    const saved = JSON.parse(localStorage.getItem('auditQueries')||'[]');
    const list  = document.getElementById('savedList');
    const none  = document.getElementById('noSaved');
    if (!saved.length) { if(none) none.style.display=''; return; }
    if(none) none.style.display='none';
    list.innerHTML = '';
    saved.forEach((item, idx) => {
        const d = document.createElement('div');
        d.className = 'ae-saved-item';
        d.innerHTML = `<span class="name" title="${esc(item.name)}">${esc(item.name)}</span>
            <button class="ae-saved-load" onclick="loadSaved(${idx})">Load</button>
            <button class="ae-saved-del" onclick="deleteSaved(${idx})">✕</button>`;
        list.appendChild(d);
    });
}
function loadSaved(i)   { const s=JSON.parse(localStorage.getItem('auditQueries')||'[]'); if(s[i]) loadTemplate(s[i].spec); }
function deleteSaved(i) { const s=JSON.parse(localStorage.getItem('auditQueries')||'[]'); s.splice(i,1); localStorage.setItem('auditQueries',JSON.stringify(s)); renderSaved(); }

// ── Export ────────────────────────────────────────────────────────────────────
function exportResult(fmt) {
    if (!lastSpec) { alert('Run a query first.'); return; }
    const s   = {...lastSpec, limit:10000};
    const url = `/admin/audit/export?format=${fmt}&select=${encodeURIComponent((s.select||['*']).join(','))}`
              + `&where=${encodeURIComponent(JSON.stringify(s.where||[]))}`
              + `&group_by=${encodeURIComponent((s.group_by||[]).join(','))}`
              + `&order_by=${encodeURIComponent(s.order_by||'created_at')}`
              + `&order_dir=${s.order_dir||'DESC'}&limit=10000`;
    window.location.href = url;
}

// ── Reset ────────────────────────────────────────────────────────────────────
function clearAll(resetResults=true) {
    ['fModule','fAction','fStatus','fUserRole','fUserId','fDateFrom','fDateTo','fSearch','fGroupBy'].forEach(id=>{
        const el=document.getElementById(id); if(el) el.value='';
    });
    document.getElementById('fOrderBy').value  = 'created_at';
    document.getElementById('fOrderDir').value = 'DESC';
    document.getElementById('fLimit').value    = '100';
    document.getElementById('selectList').innerHTML = '';
    const inp=document.createElement('input');inp.type='text';inp.className='ae-builder-input select-expr';
    inp.value='*';inp.style.width='180px';inp.setAttribute('list','dlCols');
    inp.ondblclick=()=>inp.remove();
    document.getElementById('selectList').appendChild(inp);
    if (resetResults) {
        document.getElementById('sqlBar').style.display='none';
        document.getElementById('resultMeta').textContent='';
        document.getElementById('resultsArea').innerHTML=
            '<div class="ae-placeholder"><i class="fas fa-database"></i><div style="font-size:14px;font-weight:600;">No query run yet</div><div style="font-size:12px;">Set filters on the left and click <strong>Run Query</strong></div></div>';
        lastSpec=null;
    }
}

// ── Init ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    renderSaved();
    // open first section by default
    document.querySelectorAll('.ae-section-hdr').forEach((hdr,i) => {
        if (i===0) { hdr.classList.add('open'); hdr.nextElementSibling.classList.add('open'); }
    });
    // keyboard shortcut
    document.addEventListener('keydown', e => {
        if ((e.ctrlKey||e.metaKey) && e.key==='Enter') runQuery();
    });
});
</script>

<?php View::endSection(); ?>
