<?php use Core\View; use Core\Auth; ?>
<?php View::extend('audit'); ?>

<?php View::section('content'); ?>
<style>
:root { font-size: 13px; }
.ae-wrap{display:grid;grid-template-columns:270px 1fr;height:100%;overflow:hidden;}
/* ── Sidebar ── */
.ae-sidebar{background:var(--bg-s);border-right:1px solid var(--border);overflow-y:auto;display:flex;flex-direction:column;height:100%;}
.ae-sidebar::-webkit-scrollbar{width:4px;}.ae-sidebar::-webkit-scrollbar-thumb{background:var(--border);border-radius:2px;}
.ae-sb-logo{padding:14px 16px 10px;border-bottom:1px solid var(--border);flex-shrink:0;}
.ae-sb-logo h2{font-size:13px;font-weight:700;margin:0;}.ae-sb-logo p{font-size:10px;color:var(--text-m);margin:2px 0 0;}
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
.ae-export-row{padding:10px 14px;border-top:1px solid var(--border);display:flex;gap:6px;flex-shrink:0;margin-top:auto;}
.ae-sm-btn{flex:1;background:none;border:1px solid var(--border);color:var(--text-m);border-radius:6px;padding:5px 0;font-size:11px;cursor:pointer;font-family:inherit;transition:.12s;}
.ae-sm-btn:hover{border-color:var(--cyan);color:var(--cyan);}
/* ── Main panel ── */
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
.ae-sql-bar{padding:6px 14px;background:var(--bg-s);border-bottom:1px solid var(--border);font-family:'JetBrains Mono',monospace,inherit;font-size:11px;color:var(--cyan);overflow-x:auto;white-space:nowrap;flex-shrink:0;display:none;}
.ae-results{flex:1;overflow:auto;}
.ae-placeholder{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;color:var(--text-m);gap:8px;}
.ae-placeholder i{font-size:2.5rem;opacity:.25;}
.ae-placeholder strong{font-size:14px;font-weight:600;color:var(--text-m);}
.ae-table{width:100%;border-collapse:collapse;font-size:11px;}
.ae-table th{background:var(--bg-s);padding:7px 12px;text-align:left;font-weight:600;white-space:nowrap;position:sticky;top:0;z-index:2;border-bottom:2px solid var(--border);}
.ae-table td{padding:6px 12px;border-bottom:1px solid var(--border);word-break:break-word;max-width:340px;vertical-align:top;}
.ae-table td pre{font-size:10px;font-family:monospace;white-space:pre-wrap;margin:0;color:var(--text-m);}
.ae-table tbody tr:hover{background:rgba(0,240,255,.04);}
.badge-s{color:var(--green);font-weight:600;}.badge-f{color:var(--red);font-weight:600;}.badge-p{color:var(--orange);font-weight:600;}
@media(max-width:760px){.ae-wrap{grid-template-columns:1fr;}.ae-sidebar{max-height:50vh;height:auto;}}
</style>

<div class="ae-wrap">

<!-- ════ LEFT SIDEBAR ════ -->
<aside class="ae-sidebar">
    <div class="ae-sb-logo">
        <h2>🔍 Audit Explorer</h2>
        <p>Filter and query audit log data</p>
    </div>

    <button class="ae-run-btn" id="runBtn" onclick="runQuery()">▶ Run Query</button>

    <!-- FILTERS -->
    <div class="ae-sec">
        <div class="ae-sec-hdr open" onclick="toggleSec(this)">
            <span class="lbl">⚡ Filters</span>
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
            <input type="text" id="fUserEmail" class="fi" placeholder="e.g. john@…" list="dlUserEmails">
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
            <input type="text" id="fSearch" class="fi" placeholder="message, action, IP…">
        </div>
    </div>

    <!-- GROUP & SORT -->
    <div class="ae-sec">
        <div class="ae-sec-hdr" onclick="toggleSec(this)">
            <span class="lbl">📐 Group &amp; Sort</span>
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

    <!-- TEMPLATES -->
    <div class="ae-sec">
        <div class="ae-sec-hdr" onclick="toggleSec(this)">
            <span class="lbl">⚡ Templates</span>
            <i class="fas fa-chevron-right arr"></i>
        </div>
        <div class="ae-sec-body">
            <?php
            $tpls = [
                ['Latest 100 events',          ['select'=>['*'],'where'=>[],'order_by'=>'created_at','limit'=>100]],
                ['Login events today',          ['select'=>['*'],'where'=>[['col'=>'action','op'=>'=','val'=>'login'],['col'=>'created_at','op'=>'>=','val'=>date('Y-m-d')]],'order_by'=>'created_at','limit'=>100]],
                ['All failures',               ['select'=>['*'],'where'=>[['col'=>'status','op'=>'=','val'=>'failure']],'order_by'=>'created_at','limit'=>200]],
                ['Top actions (grouped)',       ['select'=>['action','COUNT(*) AS cnt'],'where'=>[],'group_by'=>['action'],'order_by'=>'COUNT(*)','order_dir'=>'DESC','limit'=>20]],
                ['Per-module counts',           ['select'=>['module','COUNT(*) AS cnt'],'where'=>[],'group_by'=>['module'],'order_by'=>'COUNT(*)','order_dir'=>'DESC','limit'=>20]],
                ['Events by user',              ['select'=>['user_name','user_email','COUNT(*) AS cnt'],'where'=>[],'group_by'=>['user_name','user_email'],'order_by'=>'COUNT(*)','order_dir'=>'DESC','limit'=>50]],
                ['WhatsApp events',             ['select'=>['*'],'where'=>[['col'=>'module','op'=>'=','val'=>'whatsapp']],'order_by'=>'created_at','limit'=>100]],
                ['Admin actions',               ['select'=>['*'],'where'=>[['col'=>'user_role','op'=>'=','val'=>'admin']],'order_by'=>'created_at','limit'=>100]],
                ['Failures last 7 days',        ['select'=>['*'],'where'=>[['col'=>'status','op'=>'=','val'=>'failure'],['col'=>'created_at','op'=>'>=','val'=>date('Y-m-d',strtotime('-7 days'))]],'order_by'=>'created_at','limit'=>200]],
                ['QR events',                   ['select'=>['*'],'where'=>[['col'=>'module','op'=>'=','val'=>'qr']],'order_by'=>'created_at','limit'=>100]],
                ['Settings changes (old→new)',  ['select'=>['user_name','action','module','readable_message','old_values','new_values','created_at'],'where'=>[['col'=>'action','op'=>'LIKE','val'=>'%_updated']],'order_by'=>'created_at','limit'=>200]],
                ['By IP address',               ['select'=>['ip_address','COUNT(*) AS cnt'],'where'=>[],'group_by'=>['ip_address'],'order_by'=>'COUNT(*)','order_dir'=>'DESC','limit'=>50]],
            ];
            foreach ($tpls as [$label, $spec]): ?>
            <button class="tpl-btn" onclick='loadTemplate(<?= htmlspecialchars(json_encode($spec), ENT_QUOTES) ?>)'><?= htmlspecialchars($label) ?></button>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- SAVED QUERIES -->
    <div class="ae-sec">
        <div class="ae-sec-hdr" onclick="toggleSec(this)">
            <span class="lbl">💾 Saved Queries</span>
            <i class="fas fa-chevron-right arr"></i>
        </div>
        <div class="ae-sec-body">
            <div id="savedList">
                <p id="noSaved" style="font-size:10.5px;color:var(--text-m);">No saved queries yet.</p>
            </div>
            <button class="ae-sm-btn" style="width:100%;margin-top:6px;" onclick="saveQuery()">+ Save current query</button>
        </div>
    </div>

    <div style="flex:1;min-height:10px;"></div>

    <div class="ae-export-row">
        <button class="ae-sm-btn" onclick="exportResult('csv')">⬇ CSV</button>
        <button class="ae-sm-btn" onclick="exportResult('json')">⬇ JSON</button>
        <button class="ae-sm-btn" onclick="clearAll()" title="Reset">↺ Reset</button>
    </div>
</aside>

<!-- ════ RIGHT MAIN PANEL ════ -->
<section class="ae-main">

    <!-- Stats -->
    <div class="ae-stats">
        <div class="ae-stat"><div class="v" style="color:var(--cyan);"><?= number_format($stats['total'] ?? 0) ?></div><div class="l">Total Events</div></div>
        <div class="ae-stat"><div class="v" style="color:var(--green);"><?= number_format($stats['unique_users'] ?? 0) ?></div><div class="l">Users</div></div>
        <div class="ae-stat"><div class="v" style="color:var(--orange);"><?= number_format($stats['unique_actions'] ?? 0) ?></div><div class="l">Action Types</div></div>
        <div class="ae-stat"><div class="v" style="color:#ff2ec4;"><?= number_format($stats['unique_modules'] ?? 0) ?></div><div class="l">Modules</div></div>
    </div>

    <!-- SELECT toolbar -->
    <div class="ae-toolbar">
        <span class="ae-toolbar-lbl">SELECT</span>
        <div class="ae-sel-wrap" id="selectWrap">
            <input type="text" class="ae-col-in sel-expr" value="*"
                   placeholder="col or COUNT(*) AS n" list="dlCols"
                   ondblclick="this.remove()">
        </div>
        <button class="ae-add-col" onclick="addColIn()">+ col</button>
        <datalist id="dlCols">
            <?php foreach ($allowedCols as $c): ?><option value="<?= View::e($c) ?>"><?php endforeach; ?>
            <option value="COUNT(*)"><option value="COUNT(*) AS cnt">
            <option value="user_name"><option value="user_email">
        </datalist>
        <span class="ae-result-meta" id="resultMeta"></span>
    </div>

    <!-- SQL preview bar -->
    <div class="ae-sql-bar" id="sqlBar"></div>

    <!-- Results -->
    <div class="ae-results" id="resultsArea">
        <div class="ae-placeholder" id="placeholder">
            <i class="fas fa-database"></i>
            <strong>No query run yet</strong>
            <span style="font-size:12px;">Set filters and click <b>Run Query</b></span>
            <span style="font-size:11px;opacity:.7;">Ctrl+Enter to run &nbsp;·&nbsp; double-click a col input to remove it</span>
        </div>
    </div>

</section>
</div>
<?php View::endSection(); ?>

<?php View::section('scripts'); ?>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

function toggleSec(hdr) {
    hdr.classList.toggle('open');
    hdr.nextElementSibling.classList.toggle('open');
}

function addColIn(val) {
    const inp = document.createElement('input');
    inp.type = 'text'; inp.className = 'ae-col-in sel-expr';
    inp.placeholder = 'col or COUNT(*)'; inp.value = val || '';
    inp.setAttribute('list', 'dlCols');
    inp.ondblclick = () => inp.remove();
    document.getElementById('selectWrap').appendChild(inp);
    inp.focus();
}

function buildSpec() {
    const select = [...document.querySelectorAll('.sel-expr')].map(i => i.value.trim()).filter(Boolean);
    const where = [];
    const push = (col, op, val) => { if (val !== '' && val != null) where.push({col, op, val}); };
    push('module',    '=', document.getElementById('fModule').value);
    push('action',    '=', document.getElementById('fAction').value);
    push('status',    '=', document.getElementById('fStatus').value);
    push('user_role', '=', document.getElementById('fUserRole').value);
    push('user_id',   '=', document.getElementById('fUserId').value);
    const df = document.getElementById('fDateFrom').value;
    const dt = document.getElementById('fDateTo').value;
    if (df) push('created_at', '>=', df + ' 00:00:00');
    if (dt) push('created_at', '<=', dt + ' 23:59:59');
    // user name / email filters
    const un = document.getElementById('fUserName').value.trim();
    if (un) where.push({col:'user_name', op:'LIKE', val:'%'+un+'%'});
    const ue = document.getElementById('fUserEmail').value.trim();
    if (ue) where.push({col:'user_email', op:'LIKE', val:'%'+ue+'%'});
    // keyword searches across message, action, and IP
    const kw = document.getElementById('fSearch').value.trim();
    if (kw) {
        // push keyword search across readable_message
        where.push({col:'readable_message', op:'LIKE', val:'%'+kw+'%'});
    }
    const gbRaw = document.getElementById('fGroupBy').value.trim();
    return {
        select: select.length ? select : ['*'],
        where,
        group_by: gbRaw ? gbRaw.split(',').map(s=>s.trim()).filter(Boolean) : [],
        order_by:  document.getElementById('fOrderBy').value.trim() || 'created_at',
        order_dir: document.getElementById('fOrderDir').value,
        limit: Math.min(10000, Math.max(1, parseInt(document.getElementById('fLimit').value) || 100)),
    };
}

let lastSpec = null;

async function runQuery() {
    const spec = buildSpec();
    lastSpec = spec;
    const btn = document.getElementById('runBtn');
    btn.textContent = '⏳ Running…'; btn.disabled = true;
    try {
        const res  = await fetch('/admin/audit/query', {
            method: 'POST',
            headers: {'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
            body: JSON.stringify(spec),
        });
        const data = await res.json();
        if (!res.ok || data.error) showError(data.error || 'HTTP ' + res.status);
        else showResults(data);
    } catch(e) {
        showError('Network error: ' + e.message);
    } finally {
        btn.textContent = '▶ Run Query'; btn.disabled = false;
    }
}

function showError(msg) {
    document.getElementById('sqlBar').style.display = 'none';
    document.getElementById('resultMeta').textContent = '';
    document.getElementById('resultsArea').innerHTML =
        `<div class="ae-placeholder"><i class="fas fa-exclamation-triangle" style="color:var(--red)"></i>
         <strong style="color:var(--red);">${esc(msg)}</strong></div>`;
}

function showResults(json) {
    document.getElementById('resultMeta').textContent = (json.count || 0).toLocaleString() + ' row(s)';
    const bar = document.getElementById('sqlBar');
    bar.textContent = json.sql || ''; bar.style.display = '';

    if (!json.data || !json.data.length) {
        document.getElementById('resultsArea').innerHTML =
            '<div class="ae-placeholder"><i class="fas fa-inbox"></i><strong>No rows found.</strong><span>Try relaxing your filters.</span></div>';
        return;
    }
    const cols = Object.keys(json.data[0]);
    let html = '<table class="ae-table"><thead><tr>' + cols.map(c=>`<th>${esc(c)}</th>`).join('') + '</tr></thead><tbody>';
    const jsonCols = new Set(['old_values','new_values','data','action_data']);
    json.data.forEach(row => {
        html += '<tr>' + cols.map(c => {
            let raw = row[c];
            if (raw == null) return `<td><span style="opacity:.35">—</span></td>`;
            let s = String(raw);
            // Pretty-print JSON columns
            if (jsonCols.has(c) && (s.startsWith('{') || s.startsWith('['))) {
                try { s = JSON.stringify(JSON.parse(s), null, 2); } catch(e) {}
                return `<td><pre>${esc(s)}</pre></td>`;
            }
            // Status badge
            if (c === 'status') {
                const cls = {success:'badge-s',failure:'badge-f',pending:'badge-p'}[s] || '';
                return `<td><span class="${cls}">${esc(s)}</span></td>`;
            }
            return `<td title="${esc(s)}">${esc(s)}</td>`;
        }).join('') + '</tr>';
    });
    html += '</tbody></table>';
    document.getElementById('resultsArea').innerHTML = html;
}

function esc(s) {
    return String(s??'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function loadTemplate(spec) {
    clearAll(false);
    const exprs = spec.select || ['*'];
    document.getElementById('selectWrap').innerHTML = '';
    exprs.forEach(e => {
        const inp = document.createElement('input');
        inp.type='text'; inp.className='ae-col-in sel-expr'; inp.value=e;
        inp.setAttribute('list','dlCols'); inp.ondblclick=()=>inp.remove();
        document.getElementById('selectWrap').appendChild(inp);
    });
    const fc=(col,op)=>(spec.where||[]).find(w=>w.col===col&&w.op===op);
    const sv=(id,v)=>{ if(v!=null) document.getElementById(id).value=v; };
    sv('fModule',   fc('module','=')?.val||'');
    sv('fAction',   fc('action','=')?.val||'');
    sv('fStatus',   fc('status','=')?.val||'');
    sv('fUserRole', fc('user_role','=')?.val||'');
    sv('fUserId',   fc('user_id','=')?.val||'');
    const df=fc('created_at','>='); if(df) sv('fDateFrom',df.val.substring(0,10));
    const dt=fc('created_at','<='); if(dt) sv('fDateTo',  dt.val.substring(0,10));
    sv('fGroupBy',  (spec.group_by||[]).join(', '));
    sv('fOrderBy',  spec.order_by||'created_at');
    sv('fOrderDir', spec.order_dir||'DESC');
    sv('fLimit',    spec.limit||100);
    runQuery();
}

function saveQuery() {
    const name = prompt('Name for this query:'); if (!name) return;
    const saved = JSON.parse(localStorage.getItem('aeQ')||'[]');
    saved.push({name, spec:buildSpec()});
    localStorage.setItem('aeQ', JSON.stringify(saved));
    renderSaved();
}
function renderSaved() {
    const saved = JSON.parse(localStorage.getItem('aeQ')||'[]');
    const list  = document.getElementById('savedList');
    if (!saved.length) { list.innerHTML='<p id="noSaved" style="font-size:10.5px;color:var(--text-m);">No saved queries yet.</p>'; return; }
    list.innerHTML = '';
    saved.forEach((item, idx) => {
        const d = document.createElement('div'); d.className='sv-item';
        d.innerHTML=`<span class="sv-name" title="${esc(item.name)}">${esc(item.name)}</span>
            <button class="sv-load" onclick="loadSaved(${idx})">Load</button>
            <button class="sv-del" onclick="delSaved(${idx})">✕</button>`;
        list.appendChild(d);
    });
}
function loadSaved(i){const s=JSON.parse(localStorage.getItem('aeQ')||'[]');if(s[i])loadTemplate(s[i].spec);}
function delSaved(i){const s=JSON.parse(localStorage.getItem('aeQ')||'[]');s.splice(i,1);localStorage.setItem('aeQ',JSON.stringify(s));renderSaved();}

function exportResult(fmt) {
    if (!lastSpec){alert('Run a query first.');return;}
    const s={...lastSpec,limit:10000};
    window.location.href=`/admin/audit/export?format=${fmt}`
        +`&select=${encodeURIComponent((s.select||['*']).join(','))}`
        +`&where=${encodeURIComponent(JSON.stringify(s.where||[]))}`
        +`&group_by=${encodeURIComponent((s.group_by||[]).join(','))}`
        +`&order_by=${encodeURIComponent(s.order_by||'created_at')}`
        +`&order_dir=${s.order_dir||'DESC'}&limit=10000`;
}

function clearAll(resetResults=true) {
    ['fModule','fAction','fStatus','fUserRole','fUserId','fDateFrom','fDateTo','fSearch','fGroupBy','fUserName','fUserEmail']
        .forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
    document.getElementById('fOrderBy').value='created_at';
    document.getElementById('fOrderDir').value='DESC';
    document.getElementById('fLimit').value='100';
    document.getElementById('selectWrap').innerHTML='';
    const inp=document.createElement('input');inp.type='text';inp.className='ae-col-in sel-expr';
    inp.value='*';inp.setAttribute('list','dlCols');inp.ondblclick=()=>inp.remove();
    document.getElementById('selectWrap').appendChild(inp);
    if(resetResults){
        document.getElementById('sqlBar').style.display='none';
        document.getElementById('resultMeta').textContent='';
        document.getElementById('resultsArea').innerHTML='<div class="ae-placeholder"><i class="fas fa-database"></i><strong>No query run yet</strong><span style="font-size:12px;">Set filters and click Run Query</span></div>';
        lastSpec=null;
    }
}

document.addEventListener('DOMContentLoaded',()=>{
    renderSaved();
    document.querySelectorAll('.ae-sec-hdr').forEach((hdr,i)=>{
        if(i===0){hdr.classList.add('open');hdr.nextElementSibling.classList.add('open');}
    });
    document.addEventListener('keydown',e=>{if((e.ctrlKey||e.metaKey)&&e.key==='Enter'){e.preventDefault();runQuery();}});
    // Show loading state immediately, then auto-run "latest 100 events"
    document.getElementById('resultsArea').innerHTML=
        '<div class="ae-placeholder"><i class="fas fa-spinner fa-spin"></i><strong>Loading latest events…</strong></div>';
    runQuery();
});
</script>
<?php View::endSection(); ?>
