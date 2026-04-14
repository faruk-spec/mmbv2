<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="<?= htmlspecialchars($csrfToken ?? '', ENT_QUOTES) ?>">
<title><?= htmlspecialchars($pageTitle ?? 'Inbox', ENT_QUOTES) ?> – <?= htmlspecialchars($appName, ENT_QUOTES) ?> Mail</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* ─── Reset & Base ─── */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
html,body{height:100%;font-family:'Inter',sans-serif;font-size:14px;background:#0d0d14;color:#e2e8f0}
a{color:inherit;text-decoration:none}
button{cursor:pointer;font-family:inherit}

/* ─── Layout ─── */
.app{display:flex;height:100vh;overflow:hidden}
.sidebar{width:220px;min-width:220px;background:#0a0a12;border-right:1px solid rgba(255,255,255,.07);display:flex;flex-direction:column;overflow-y:auto}
.main{flex:1;display:flex;flex-direction:column;overflow:hidden}
.topbar{background:#0a0a12;border-bottom:1px solid rgba(255,255,255,.07);padding:0 20px;height:56px;display:flex;align-items:center;gap:12px;flex-shrink:0}
.content{flex:1;overflow-y:auto;padding:20px}

/* ─── Sidebar ─── */
.sidebar-logo{padding:16px 20px;border-bottom:1px solid rgba(255,255,255,.07);display:flex;align-items:center;gap:10px}
.sidebar-logo .logo-icon{width:32px;height:32px;background:linear-gradient(135deg,#667eea,#764ba2);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:15px;color:#fff}
.sidebar-logo .logo-text{font-size:15px;font-weight:600;color:#e2e8f0}
.sidebar-logo .logo-sub{font-size:11px;color:#64748b}
.sidebar-compose{padding:12px 16px}
.btn-compose{width:100%;padding:10px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:8px;transition:.2s}
.btn-compose:hover{opacity:.9;transform:translateY(-1px)}
.sidebar-nav{flex:1;padding:8px 0}
.nav-item{display:flex;align-items:center;gap:10px;padding:9px 20px;color:#94a3b8;font-size:13px;transition:.15s;cursor:pointer;border-left:3px solid transparent}
.nav-item:hover{background:rgba(255,255,255,.04);color:#e2e8f0}
.nav-item.active{background:rgba(102,126,234,.1);color:#667eea;border-left-color:#667eea}
.nav-item .badge{margin-left:auto;background:#e74c3c;color:#fff;border-radius:10px;padding:1px 7px;font-size:11px;font-weight:600}
.sidebar-footer{padding:12px 16px;border-top:1px solid rgba(255,255,255,.07)}
.sidebar-user{display:flex;align-items:center;gap:10px}
.user-avatar{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#fff;flex-shrink:0}
.user-info .name{font-size:13px;font-weight:500;color:#e2e8f0}
.user-info .email{font-size:11px;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:140px}

/* ─── Topbar ─── */
.topbar-search{flex:1;max-width:400px}
.search-input{width:100%;padding:8px 14px 8px 36px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#e2e8f0;font-size:13px;outline:none;transition:.2s}
.search-input:focus{border-color:#667eea;background:rgba(102,126,234,.08)}
.search-wrap{position:relative}
.search-wrap i{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#64748b;font-size:13px}
.topbar-actions{display:flex;align-items:center;gap:8px;margin-left:auto}
.btn-icon{width:34px;height:34px;border:none;background:rgba(255,255,255,.06);border-radius:8px;color:#94a3b8;display:flex;align-items:center;justify-content:center;transition:.15s;font-size:13px}
.btn-icon:hover{background:rgba(255,255,255,.1);color:#e2e8f0}

/* ─── Alerts ─── */
.alert{padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;display:flex;align-items:center;gap:10px}
.alert-success{background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.3);color:#6ee7b7}
.alert-error{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5}

/* ─── Buttons ─── */
.btn{padding:8px 16px;border-radius:8px;border:none;font-size:13px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:.15s}
.btn-primary{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff}
.btn-primary:hover{opacity:.9}
.btn-secondary{background:rgba(255,255,255,.08);color:#cbd5e1;border:1px solid rgba(255,255,255,.1)}
.btn-secondary:hover{background:rgba(255,255,255,.12)}
.btn-danger{background:rgba(239,68,68,.15);color:#fca5a5;border:1px solid rgba(239,68,68,.2)}
.btn-danger:hover{background:rgba(239,68,68,.25)}
.btn-sm{padding:5px 10px;font-size:12px}

/* ─── Cards & Tables ─── */
.card{background:#111117;border:1px solid rgba(255,255,255,.07);border-radius:12px;padding:20px;margin-bottom:16px}
.table{width:100%;border-collapse:collapse}
.table th{padding:10px 12px;text-align:left;font-size:12px;font-weight:500;color:#64748b;border-bottom:1px solid rgba(255,255,255,.07);text-transform:uppercase;letter-spacing:.5px}
.table td{padding:12px;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px;vertical-align:middle}
.table tr:hover td{background:rgba(255,255,255,.03)}
.table tr.unread td{background:rgba(102,126,234,.04)}
.table tr.unread td:first-child{border-left:3px solid #667eea}

/* ─── Misc ─── */
.text-muted{color:#64748b}
.badge{display:inline-flex;align-items:center;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:500}
.badge-read{background:rgba(16,185,129,.15);color:#6ee7b7}
.badge-unread{background:rgba(102,126,234,.15);color:#a5b4fc}
.star-btn{background:none;border:none;color:#64748b;font-size:15px;transition:.15s}
.star-btn.starred{color:#f59e0b}
.star-btn:hover{color:#f59e0b}

/* ─── Form ─── */
.form-group{margin-bottom:16px}
.form-label{display:block;margin-bottom:6px;font-size:12px;font-weight:500;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px}
.form-input{width:100%;padding:10px 14px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#e2e8f0;font-size:13px;outline:none;transition:.2s;font-family:inherit}
.form-input:focus{border-color:#667eea;background:rgba(102,126,234,.06)}
textarea.form-input{resize:vertical;min-height:200px}

/* ─── Email body iframe wrapper ─── */
.email-body-wrap{background:#fff;border-radius:8px;overflow:hidden;margin-top:16px}
.email-body-wrap iframe{width:100%;min-height:400px;border:none;display:block}

/* ─── Responsive ─── */
@media(max-width:768px){
    .sidebar{width:0;min-width:0;position:fixed;z-index:100;transition:.3s;overflow:hidden}
    .sidebar.open{width:220px;min-width:220px}
    .main{width:100%}
}
</style>
</head>
<body>
<div class="app">
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <div class="logo-icon"><i class="fas fa-envelope"></i></div>
            <div>
                <div class="logo-text"><?= htmlspecialchars($appName, ENT_QUOTES) ?></div>
                <div class="logo-sub">Mail</div>
            </div>
        </div>
        <div class="sidebar-compose">
            <a href="/compose" class="btn-compose"><i class="fas fa-pen"></i> Compose</a>
        </div>
        <nav class="sidebar-nav">
            <?php
            $curUri = $_SERVER['REQUEST_URI'] ?? '/';
            $folder = $_GET['folder'] ?? 'inbox';
            ?>
            <a href="/" class="nav-item <?= (strpos($curUri, '/compose') === false && strpos($curUri, '/settings') === false && strpos($curUri, '/view/') === false && strpos($curUri, '/search') === false && ($folder === 'inbox' || $folder === '')) ? 'active' : '' ?>">
                <i class="fas fa-inbox"></i> Inbox
                <?php if (!empty($unread)): ?><span class="badge"><?= (int)$unread ?></span><?php endif; ?>
            </a>
            <a href="/?folder=starred" class="nav-item <?= $folder === 'starred' ? 'active' : '' ?>">
                <i class="fas fa-star"></i> Starred
            </a>
            <a href="/?folder=archived" class="nav-item <?= $folder === 'archived' ? 'active' : '' ?>">
                <i class="fas fa-archive"></i> Archived
            </a>
            <a href="/search" class="nav-item <?= strpos($curUri, '/search') !== false ? 'active' : '' ?>">
                <i class="fas fa-search"></i> Search
            </a>
            <a href="/settings" class="nav-item <?= strpos($curUri, '/settings') !== false ? 'active' : '' ?>">
                <i class="fas fa-cog"></i> Settings
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="user-avatar"><?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?></div>
                <div class="user-info">
                    <div class="name"><?= htmlspecialchars($user['name'] ?? '', ENT_QUOTES) ?></div>
                    <div class="email"><?= htmlspecialchars($user['email'] ?? '', ENT_QUOTES) ?></div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main area -->
    <div class="main">
        <div class="topbar">
            <button class="btn-icon" onclick="document.getElementById('sidebar').classList.toggle('open')" title="Toggle sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-search">
                <form action="/search" method="GET">
                    <div class="search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" name="q" class="search-input" placeholder="Search emails…" value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES) ?>">
                    </div>
                </form>
            </div>
            <div class="topbar-actions">
                <button class="btn-icon" id="syncBtn" onclick="syncInbox()" title="Sync inbox">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <a href="<?= htmlspecialchars($appUrl ?? '', ENT_QUOTES) ?>/dashboard" class="btn-icon" title="Back to platform">
                    <i class="fas fa-home"></i>
                </a>
            </div>
        </div>

        <div class="content">
            <?php if (!empty($flashSuccess)): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($flashSuccess, ENT_QUOTES) ?></div>
            <?php endif; ?>
            <?php if (!empty($flashError)): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($flashError, ENT_QUOTES) ?></div>
            <?php endif; ?>

            <?= $content ?? '' ?>
        </div>
    </div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

function syncInbox() {
    const btn = document.getElementById('syncBtn');
    btn.querySelector('i').classList.add('fa-spin');
    fetch('/sync', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: '_csrf_token=' + encodeURIComponent(csrfToken)
    }).then(r => r.json()).then(d => {
        btn.querySelector('i').classList.remove('fa-spin');
        if (d.synced > 0) location.reload();
    }).catch(() => btn.querySelector('i').classList.remove('fa-spin'));
}

function postAction(url, params, callback) {
    const allParams = Object.assign({_csrf_token: csrfToken}, params);
    const body = Object.entries(allParams).map(([k,v]) => encodeURIComponent(k) + '=' + encodeURIComponent(v)).join('&');
    fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body
    }).then(r => r.json()).then(callback).catch(e => console.error(e));
}
</script>
</body>
</html>
