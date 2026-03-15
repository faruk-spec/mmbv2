<?php use Core\View; use Core\Auth; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<!-- ================================================================
     AUDIT EXPLORER – Superset-style visual query builder
     ================================================================ -->

<style>
.ae-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:24px;}
.ae-stat{background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:16px 20px;flex:1;min-width:140px;}
.ae-stat .val{font-size:1.6rem;font-weight:700;}
.ae-stat .lbl{font-size:12px;color:var(--text-secondary);margin-top:2px;}

.qb-card{background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;margin-bottom:16px;}
.qb-title{font-size:13px;font-weight:600;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.7px;margin-bottom:12px;}

.condition-row{display:flex;gap:8px;align-items:center;margin-bottom:8px;flex-wrap:wrap;}
.condition-row select,.condition-row input{flex:1;min-width:120px;}

.result-table-wrap{overflow-x:auto;}
.result-table{width:100%;border-collapse:collapse;font-size:12px;}
.result-table th{background:var(--bg-secondary);padding:8px 12px;text-align:left;font-weight:600;white-space:nowrap;position:sticky;top:0;z-index:1;}
.result-table td{padding:7px 12px;border-bottom:1px solid var(--border-color);white-space:nowrap;max-width:260px;overflow:hidden;text-overflow:ellipsis;}
.result-table tbody tr:hover{background:var(--bg-secondary);}

.sql-preview{background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;padding:12px 16px;font-family:monospace;font-size:12px;color:var(--cyan);overflow-x:auto;white-space:pre-wrap;margin-top:12px;}

.tab-btn{padding:8px 16px;border:1px solid var(--border-color);background:transparent;color:var(--text-secondary);border-radius:8px;cursor:pointer;font-size:13px;transition:.15s;}
.tab-btn.active{background:var(--cyan);border-color:var(--cyan);color:#000;font-weight:600;}

.saved-item{display:flex;align-items:center;gap:8px;padding:8px 10px;border-radius:8px;cursor:pointer;transition:.15s;}
.saved-item:hover{background:var(--bg-secondary);}
.saved-item .saved-name{flex:1;font-size:13px;}

#runBtn{min-width:110px;}
#runBtn .spinner{display:none;border:2px solid rgba(0,0,0,.3);border-top-color:#000;border-radius:50%;width:14px;height:14px;animation:spin .6s linear infinite;margin:0 auto;}
@keyframes spin{to{transform:rotate(360deg);}}
#runBtn.loading .btn-text{display:none;}
#runBtn.loading .spinner{display:inline-block;}
</style>

<!-- Header -->
<div class="ae-header">
    <div>
        <h1 style="margin:0;font-size:1.5rem;">🔍 Audit Explorer</h1>
        <p style="color:var(--text-secondary);margin:4px 0 0;font-size:13px;">
            Visual query builder for <code>activity_logs</code> — no SQL knowledge required.
            <?php if (Auth::isAdmin()): ?>
                <a href="/admin/logs/activity" style="color:var(--cyan);margin-left:8px;">← Activity Timeline</a>
            <?php endif; ?>
        </p>
    </div>
</div>

<!-- Quick stats -->
<div style="display:flex;gap:12px;flex-wrap:wrap;margin-bottom:24px;">
    <div class="ae-stat"><div class="val" style="color:var(--cyan);"><?= number_format($stats['total'] ?? 0) ?></div><div class="lbl">Total Events</div></div>
    <div class="ae-stat"><div class="val" style="color:var(--green);"><?= number_format($stats['unique_users'] ?? 0) ?></div><div class="lbl">Unique Users</div></div>
    <div class="ae-stat"><div class="val" style="color:var(--orange);"><?= number_format($stats['unique_actions'] ?? 0) ?></div><div class="lbl">Action Types</div></div>
    <div class="ae-stat"><div class="val" style="color:var(--magenta, #ff2ec4);"><?= number_format($stats['unique_modules'] ?? 0) ?></div><div class="lbl">Modules</div></div>
</div>

<div style="display:grid;grid-template-columns:260px 1fr;gap:16px;align-items:start;">

    <!-- ============ LEFT: Saved Queries ============ -->
    <div>
        <div class="qb-card">
            <div class="qb-title">📁 Saved Queries</div>
            <div id="savedList" style="display:flex;flex-direction:column;gap:4px;max-height:320px;overflow-y:auto;">
                <!-- Populated from localStorage -->
                <p id="noSaved" style="color:var(--text-secondary);font-size:12px;">No saved queries yet.</p>
            </div>
            <button class="btn btn-sm btn-secondary" style="width:100%;margin-top:10px;" onclick="saveQuery()">
                💾 Save current query
            </button>
        </div>

        <!-- Quick Templates -->
        <div class="qb-card">
            <div class="qb-title">⚡ Quick Templates</div>
            <div style="display:flex;flex-direction:column;gap:6px;">
                <?php
                $templates = [
                    ['label'=>'Login events today',        'q'=>['select'=>['action','user_name','ip_address','created_at'],'where'=>[['col'=>'action','op'=>'=','val'=>'login'],['col'=>'created_at','op'=>'>=','val'=>date('Y-m-d')]],'order_by'=>'created_at','limit'=>100]],
                    ['label'=>'Failures last 7 days',      'q'=>['select'=>['*'],'where'=>[['col'=>'status','op'=>'=','val'=>'failure'],['col'=>'created_at','op'=>'>=','val'=>date('Y-m-d',strtotime('-7 days'))]],'order_by'=>'created_at','limit'=>200]],
                    ['label'=>'Top actions (grouped)',     'q'=>['select'=>['action','COUNT(*) AS cnt'],'where'=>[],'group_by'=>['action'],'order_by'=>'COUNT(*)','order_dir'=>'DESC','limit'=>20]],
                    ['label'=>'Per-module counts',         'q'=>['select'=>['module','COUNT(*) AS cnt'],'where'=>[],'group_by'=>['module'],'order_by'=>'COUNT(*)','order_dir'=>'DESC','limit'=>20]],
                    ['label'=>'Admin actions',             'q'=>['select'=>['*'],'where'=>[['col'=>'user_role','op'=>'=','val'=>'admin']],'order_by'=>'created_at','limit'=>100]],
                    ['label'=>'Events by user',            'q'=>['select'=>['user_name','user_email','COUNT(*) AS cnt'],'where'=>[],'group_by'=>['user_name','user_email'],'order_by'=>'COUNT(*)','order_dir'=>'DESC','limit'=>50]],
                ];
                foreach ($templates as $t): ?>
                    <button class="btn btn-sm btn-secondary" style="text-align:left;"
                            onclick='loadTemplate(<?= htmlspecialchars(json_encode($t['q']), ENT_QUOTES) ?>)'>
                        <?= htmlspecialchars($t['label']) ?>
                    </button>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- ============ RIGHT: Query Builder ============ -->
    <div>
        <!-- SELECT -->
        <div class="qb-card">
            <div class="qb-title">SELECT columns / expressions</div>
            <div id="selectList" style="display:flex;flex-direction:column;gap:6px;">
                <div class="select-row" style="display:flex;gap:8px;">
                    <input type="text" class="form-input select-expr" value="*" placeholder="column or COUNT(*)" style="flex:1;">
                    <button class="btn btn-sm btn-secondary" onclick="removeRow(this)">✕</button>
                </div>
            </div>
            <button class="btn btn-sm btn-secondary" style="margin-top:8px;" onclick="addSelectRow()">+ Add column</button>
            <div style="margin-top:10px;display:flex;gap:12px;flex-wrap:wrap;align-items:center;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <label style="font-size:12px;color:var(--text-secondary);">GROUP BY</label>
                    <input type="text" id="groupByInput" class="form-input" placeholder="e.g. action, module" style="min-width:200px;">
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <label style="font-size:12px;color:var(--text-secondary);">ORDER BY</label>
                    <input type="text" id="orderByInput" class="form-input" value="created_at" style="min-width:130px;">
                    <select id="orderDirInput" class="form-input" style="width:90px;">
                        <option value="DESC">DESC</option>
                        <option value="ASC">ASC</option>
                    </select>
                </div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <label style="font-size:12px;color:var(--text-secondary);">LIMIT</label>
                    <input type="number" id="limitInput" class="form-input" value="100" min="1" max="10000" style="width:80px;">
                </div>
            </div>
        </div>

        <!-- WHERE conditions -->
        <div class="qb-card">
            <div class="qb-title">WHERE conditions <span style="font-weight:400;font-size:11px;">(joined with AND)</span></div>
            <div id="conditionList"></div>
            <button class="btn btn-sm btn-secondary" onclick="addCondition()">+ Add condition</button>
        </div>

        <!-- Run / Export bar -->
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin-bottom:12px;">
            <button id="runBtn" class="btn btn-primary" onclick="runQuery()">
                <span class="btn-text">▶ Run Query</span>
                <span class="spinner"></span>
            </button>
            <button class="btn btn-secondary" onclick="clearAll()">↺ Reset</button>
            <button class="btn btn-secondary" onclick="exportResult('csv')">⬇ CSV</button>
            <button class="btn btn-secondary" onclick="exportResult('json')">⬇ JSON</button>
            <span id="resultMeta" style="font-size:12px;color:var(--text-secondary);margin-left:auto;"></span>
        </div>

        <!-- SQL Preview -->
        <div id="sqlPreviewBox" class="sql-preview" style="display:none;"></div>

        <!-- Result table -->
        <div class="qb-card" style="padding:0;overflow:hidden;">
            <div id="resultWrap" style="max-height:500px;overflow:auto;">
                <div id="resultPlaceholder" style="padding:40px;text-align:center;color:var(--text-secondary);">
                    <div style="font-size:2rem;margin-bottom:8px;">📊</div>
                    <div>Run a query to see results here.</div>
                </div>
                <div class="result-table-wrap" id="resultTableWrap" style="display:none;"></div>
            </div>
        </div>
    </div>
</div>

<script>
// ─────────── Column definitions ──────────────────────────────────────────────
const COLS = <?= json_encode(array_values($allowedCols)) ?>;
const ACTIONS  = <?= json_encode(array_column($actions, 'action')) ?>;
const MODULES  = <?= json_encode(array_column($modules, 'module')) ?>;
const USER_ROLES = <?= json_encode(array_column($userRoles, 'user_role')) ?>;
const CSRF     = <?= json_encode($csrf_token) ?>;

// Value suggestions per column
const SUGGESTIONS = {
    action:    ACTIONS,
    module:    MODULES,
    user_role: USER_ROLES,
    status:    ['success','failure','pending'],
    device:    ['Desktop','Mobile','Tablet','Bot'],
};

// ─────────── Condition rows ───────────────────────────────────────────────────
function makeColSelect(val) {
    const sel = document.createElement('select');
    sel.className = 'form-input cond-col';
    sel.style.minWidth = '140px';
    COLS.filter(c => !['*'].includes(c)).forEach(c => {
        const o = document.createElement('option');
        o.value = c; o.textContent = c;
        if (c === val) o.selected = true;
        sel.appendChild(o);
    });
    sel.addEventListener('change', () => updateValInput(sel));
    return sel;
}
function makeOpSelect(val) {
    const sel = document.createElement('select');
    sel.className = 'form-input cond-op';
    sel.style.width = '130px';
    ['=','!=','LIKE','NOT LIKE','>','<','>=','<=','IS NULL','IS NOT NULL'].forEach(op => {
        const o = document.createElement('option');
        o.value = op; o.textContent = op;
        if (op === val) o.selected = true;
        sel.appendChild(o);
    });
    sel.addEventListener('change', function() {
        const row = this.closest('.condition-row');
        const isNull = ['IS NULL','IS NOT NULL'].includes(this.value);
        const vInput = row.querySelector('.cond-val');
        if (vInput) vInput.style.display = isNull ? 'none' : '';
    });
    return sel;
}
function makeValInput(col, val) {
    const wrap = document.createElement('div');
    wrap.style.cssText = 'flex:1;min-width:130px;position:relative;';
    const input = document.createElement('input');
    input.type = 'text'; input.className = 'form-input cond-val';
    input.placeholder = 'value'; input.value = val || '';
    wrap.appendChild(input);

    // Datalist suggestions
    const sugg = SUGGESTIONS[col];
    if (sugg && sugg.length) {
        const dl = document.createElement('datalist');
        dl.id = 'dl_' + Math.random().toString(36).slice(2);
        sugg.forEach(s => { const o = document.createElement('option'); o.value = s; dl.appendChild(o); });
        input.setAttribute('list', dl.id);
        wrap.appendChild(dl);
    }
    return wrap;
}
function updateValInput(colSel) {
    const row = colSel.closest('.condition-row');
    const oldWrap = row.querySelector('.cond-val')?.parentNode;
    if (oldWrap && oldWrap !== row) oldWrap.remove();
    const newWrap = makeValInput(colSel.value, '');
    // insert before remove btn
    const removeBtn = row.querySelector('.remove-cond');
    row.insertBefore(newWrap, removeBtn);
}
function addCondition(col, op, val) {
    const row = document.createElement('div');
    row.className = 'condition-row';
    row.appendChild(makeColSelect(col || 'action'));
    row.appendChild(makeOpSelect(op || '='));
    row.appendChild(makeValInput(col || 'action', val || ''));
    const rm = document.createElement('button');
    rm.className = 'btn btn-sm btn-secondary remove-cond';
    rm.textContent = '✕'; rm.onclick = () => row.remove();
    row.appendChild(rm);
    document.getElementById('conditionList').appendChild(row);
}
function removeRow(btn) { btn.closest('.select-row, .condition-row')?.remove(); }
function addSelectRow(val) {
    const row = document.createElement('div');
    row.className = 'select-row'; row.style.cssText = 'display:flex;gap:8px;';
    const dl = document.createElement('datalist');
    dl.id = 'dlsel_' + Math.random().toString(36).slice(2);
    [...COLS, 'COUNT(*)','COUNT(*) AS cnt','SUM(*)'].forEach(c => {
        const o = document.createElement('option'); o.value = c; dl.appendChild(o);
    });
    const inp = document.createElement('input');
    inp.type = 'text'; inp.className = 'form-input select-expr';
    inp.placeholder = 'column or COUNT(*)'; inp.style.flex = '1';
    inp.value = val || ''; inp.setAttribute('list', dl.id);
    row.appendChild(dl); row.appendChild(inp);
    const rm = document.createElement('button');
    rm.className = 'btn btn-sm btn-secondary'; rm.textContent = '✕';
    rm.onclick = () => row.remove();
    row.appendChild(rm);
    document.getElementById('selectList').appendChild(row);
}

// ─────────── Build query spec from UI ─────────────────────────────────────────
function buildSpec() {
    const select = [...document.querySelectorAll('.select-expr')]
        .map(i => i.value.trim()).filter(Boolean);
    const groupByRaw = document.getElementById('groupByInput').value.trim();
    const groupBy = groupByRaw ? groupByRaw.split(',').map(s => s.trim()).filter(Boolean) : [];
    const where = [];
    document.querySelectorAll('.condition-row').forEach(row => {
        const col = row.querySelector('.cond-col')?.value;
        const op  = row.querySelector('.cond-op')?.value;
        const val = row.querySelector('.cond-val')?.value ?? '';
        if (col && op) where.push({col, op, val});
    });
    return {
        select: select.length ? select : ['*'],
        where,
        group_by: groupBy,
        order_by: document.getElementById('orderByInput').value.trim() || 'created_at',
        order_dir: document.getElementById('orderDirInput').value,
        limit: Math.min(10000, Math.max(1, parseInt(document.getElementById('limitInput').value) || 100)),
    };
}

// ─────────── Run query ────────────────────────────────────────────────────────
let lastSpec = null;
async function runQuery() {
    const spec = buildSpec();
    lastSpec = spec;
    const btn = document.getElementById('runBtn');
    btn.classList.add('loading'); btn.disabled = true;

    try {
        const res = await fetch('/admin/audit/query', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN': CSRF},
            body: JSON.stringify(spec),
        });
        const json = await res.json();
        if (!res.ok || json.error) {
            showError(json.error || 'Query failed');
        } else {
            showResults(json);
        }
    } catch (e) {
        showError(e.message);
    } finally {
        btn.classList.remove('loading'); btn.disabled = false;
    }
}

function showError(msg) {
    document.getElementById('sqlPreviewBox').style.display = 'none';
    document.getElementById('resultTableWrap').style.display = 'none';
    document.getElementById('resultPlaceholder').style.display = '';
    document.getElementById('resultPlaceholder').innerHTML =
        `<div style="color:#e74c3c;">❌ ${escHtml(msg)}</div>`;
    document.getElementById('resultMeta').textContent = '';
}

function showResults(json) {
    document.getElementById('resultMeta').textContent =
        `${json.count.toLocaleString()} row(s) returned`;

    // SQL Preview
    const box = document.getElementById('sqlPreviewBox');
    box.textContent = json.sql;
    box.style.display = '';

    // Table
    const wrap = document.getElementById('resultTableWrap');
    wrap.style.display = '';
    document.getElementById('resultPlaceholder').style.display = 'none';

    if (!json.data.length) {
        wrap.innerHTML = '<p style="padding:20px;color:var(--text-secondary);text-align:center;">No rows found.</p>';
        return;
    }
    const cols = Object.keys(json.data[0]);
    let html = '<table class="result-table"><thead><tr>';
    cols.forEach(c => html += `<th>${escHtml(c)}</th>`);
    html += '</tr></thead><tbody>';
    json.data.forEach(row => {
        html += '<tr>';
        cols.forEach(c => {
            let v = row[c];
            if (v === null || v === undefined) v = '';
            html += `<td title="${escHtml(String(v))}">${escHtml(String(v).substring(0,120))}</td>`;
        });
        html += '</tr>';
    });
    html += '</tbody></table>';
    wrap.innerHTML = html;
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

// ─────────── Load template ────────────────────────────────────────────────────
function loadTemplate(spec) {
    // Clear existing
    clearAll(false);
    // SELECT
    const exprs = spec.select || ['*'];
    document.getElementById('selectList').innerHTML = '';
    exprs.forEach(e => addSelectRow(e));
    // GROUP BY
    document.getElementById('groupByInput').value = (spec.group_by || []).join(', ');
    // ORDER BY
    document.getElementById('orderByInput').value = spec.order_by || 'created_at';
    document.getElementById('orderDirInput').value = spec.order_dir || 'DESC';
    // LIMIT
    document.getElementById('limitInput').value = spec.limit || 100;
    // WHERE
    (spec.where || []).forEach(c => addCondition(c.col, c.op, c.val));
}

// ─────────── Save / Load queries (localStorage) ──────────────────────────────
function saveQuery() {
    const name = prompt('Query name:');
    if (!name) return;
    const saved = JSON.parse(localStorage.getItem('auditQueries') || '[]');
    saved.push({name, spec: buildSpec(), ts: Date.now()});
    localStorage.setItem('auditQueries', JSON.stringify(saved));
    renderSaved();
}
function renderSaved() {
    const saved = JSON.parse(localStorage.getItem('auditQueries') || '[]');
    const list  = document.getElementById('savedList');
    const none  = document.getElementById('noSaved');
    if (!saved.length) { none.style.display = ''; return; }
    none.style.display = 'none';
    list.innerHTML = '';
    saved.forEach((item, idx) => {
        const div = document.createElement('div');
        div.className = 'saved-item';
        div.innerHTML = `<span class="saved-name" title="${escHtml(item.name)}">${escHtml(item.name)}</span>
            <button class="btn btn-sm btn-secondary" style="padding:2px 7px;" onclick="loadSaved(${idx})">Load</button>
            <button class="btn btn-sm" style="padding:2px 7px;color:#e74c3c;background:none;border:none;cursor:pointer;" onclick="deleteSaved(${idx})">✕</button>`;
        list.appendChild(div);
    });
}
function loadSaved(idx) {
    const saved = JSON.parse(localStorage.getItem('auditQueries') || '[]');
    if (saved[idx]) loadTemplate(saved[idx].spec);
}
function deleteSaved(idx) {
    const saved = JSON.parse(localStorage.getItem('auditQueries') || '[]');
    saved.splice(idx, 1);
    localStorage.setItem('auditQueries', JSON.stringify(saved));
    renderSaved();
}

// ─────────── Export ────────────────────────────────────────────────────────────
function exportResult(fmt) {
    if (!lastSpec) { alert('Run a query first.'); return; }
    const spec  = {...lastSpec, limit: 10000};
    const where = encodeURIComponent(JSON.stringify(spec.where || []));
    const gb    = (spec.group_by || []).join(',');
    const sel   = (spec.select || ['*']).join(',');
    const url   = `/admin/audit/export?format=${fmt}&select=${encodeURIComponent(sel)}`
                + `&where=${where}&group_by=${encodeURIComponent(gb)}`
                + `&order_by=${encodeURIComponent(spec.order_by||'created_at')}`
                + `&order_dir=${spec.order_dir||'DESC'}&limit=10000`;
    window.location.href = url;
}

// ─────────── Reset ─────────────────────────────────────────────────────────────
function clearAll(resetResults = true) {
    document.getElementById('selectList').innerHTML = '';
    addSelectRow('*');
    document.getElementById('conditionList').innerHTML = '';
    document.getElementById('groupByInput').value = '';
    document.getElementById('orderByInput').value = 'created_at';
    document.getElementById('orderDirInput').value = 'DESC';
    document.getElementById('limitInput').value = '100';
    if (resetResults) {
        document.getElementById('sqlPreviewBox').style.display = 'none';
        document.getElementById('resultTableWrap').style.display = 'none';
        document.getElementById('resultPlaceholder').style.display = '';
        document.getElementById('resultPlaceholder').innerHTML = '<div style="font-size:2rem;margin-bottom:8px;">📊</div><div>Run a query to see results here.</div>';
        document.getElementById('resultMeta').textContent = '';
        lastSpec = null;
    }
}

// ─────────── Init ──────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    // start with one SELECT row (* by default)
    addSelectRow('*');
    renderSaved();
});
</script>

<?php View::endSection(); ?>
