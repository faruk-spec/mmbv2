<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf-token" content="<?= htmlspecialchars(\Core\Security::generateCsrfToken(), ENT_QUOTES, 'UTF-8') ?>">
<title><?= htmlspecialchars($pageTitle ?? 'Inbox', ENT_QUOTES, 'UTF-8') ?> – <?= htmlspecialchars(defined('APP_NAME') ? APP_NAME : 'Mail', ENT_QUOTES, 'UTF-8') ?> Mail</title>
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
.mail-app{display:flex;height:100vh;overflow:hidden}
.mail-sidebar{width:220px;min-width:220px;background:#0a0a12;border-right:1px solid rgba(255,255,255,.07);display:flex;flex-direction:column;overflow-y:auto}
.mail-main{flex:1;display:flex;flex-direction:column;overflow:hidden}
.mail-topbar{background:#0a0a12;border-bottom:1px solid rgba(255,255,255,.07);padding:0 20px;height:56px;display:flex;align-items:center;gap:12px;flex-shrink:0}
.mail-content{flex:1;overflow-y:auto;padding:20px}

/* ─── Sidebar ─── */
.sidebar-logo{padding:16px 20px;border-bottom:1px solid rgba(255,255,255,.07);display:flex;align-items:center;gap:10px}
.sidebar-logo .logo-icon{width:32px;height:32px;background:linear-gradient(135deg,#667eea,#764ba2);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:15px;color:#fff}
.sidebar-logo .logo-text{font-size:15px;font-weight:600;color:#e2e8f0}
.sidebar-logo .logo-sub{font-size:11px;color:#64748b}
.sidebar-compose{padding:12px 16px}
.btn-compose{width:100%;padding:10px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:8px;transition:.2s;cursor:pointer}
.btn-compose:hover{opacity:.9;transform:translateY(-1px)}
.sidebar-nav{flex:1;padding:8px 0}
.mail-nav-item{display:flex;align-items:center;gap:10px;padding:9px 20px;color:#94a3b8;font-size:13px;transition:.15s;cursor:pointer;border-left:3px solid transparent;text-decoration:none}
.mail-nav-item:hover{background:rgba(255,255,255,.04);color:#e2e8f0}
.mail-nav-item.active{background:rgba(102,126,234,.1);color:#667eea;border-left-color:#667eea}
.mail-nav-item .nav-badge{margin-left:auto;background:#e74c3c;color:#fff;border-radius:10px;padding:1px 7px;font-size:11px;font-weight:600}
.sidebar-footer{padding:12px 16px;border-top:1px solid rgba(255,255,255,.07)}
.sidebar-user{display:flex;align-items:center;gap:10px}
.user-avatar{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#fff;flex-shrink:0}
.user-info .u-name{font-size:13px;font-weight:500;color:#e2e8f0}
.user-info .u-email{font-size:11px;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:140px}

/* ─── Topbar ─── */
.topbar-search{flex:1;max-width:400px}
.mail-search-input{width:100%;padding:8px 14px 8px 36px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#e2e8f0;font-size:13px;outline:none;transition:.2s}
.mail-search-input:focus{border-color:#667eea;background:rgba(102,126,234,.08)}
.search-wrap{position:relative}
.search-wrap i{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#64748b;font-size:13px}
.topbar-actions{display:flex;align-items:center;gap:8px;margin-left:auto}
.btn-icon{width:34px;height:34px;border:none;background:rgba(255,255,255,.06);border-radius:8px;color:#94a3b8;display:flex;align-items:center;justify-content:center;transition:.15s;font-size:13px;cursor:pointer}
.btn-icon:hover{background:rgba(255,255,255,.1);color:#e2e8f0}

/* ─── Alerts ─── */
.mail-alert{padding:12px 16px;border-radius:8px;margin-bottom:16px;font-size:13px;display:flex;align-items:center;gap:10px}
.mail-alert-success{background:rgba(16,185,129,.15);border:1px solid rgba(16,185,129,.3);color:#6ee7b7}
.mail-alert-error{background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5}

/* ─── Buttons ─── */
.btn{padding:8px 16px;border-radius:8px;border:none;font-size:13px;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:.15s;text-decoration:none}
.btn-primary{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff}
.btn-primary:hover{opacity:.9}
.btn-secondary{background:rgba(255,255,255,.08);color:#cbd5e1;border:1px solid rgba(255,255,255,.1)}
.btn-secondary:hover{background:rgba(255,255,255,.12)}
.btn-danger{background:rgba(239,68,68,.15);color:#fca5a5;border:1px solid rgba(239,68,68,.2)}
.btn-danger:hover{background:rgba(239,68,68,.25)}
.btn-sm{padding:5px 10px;font-size:12px}

/* ─── Cards & Tables ─── */
.mail-card{background:#111117;border:1px solid rgba(255,255,255,.07);border-radius:12px;padding:20px;margin-bottom:16px}
.mail-table{width:100%;border-collapse:collapse}
.mail-table th{padding:10px 12px;text-align:left;font-size:12px;font-weight:500;color:#64748b;border-bottom:1px solid rgba(255,255,255,.07);text-transform:uppercase;letter-spacing:.5px}
.mail-table td{padding:12px;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px;vertical-align:middle}
.mail-table tr:hover td{background:rgba(255,255,255,.03)}
.mail-table tr.unread td{background:rgba(102,126,234,.04)}
.mail-table tr.unread td:first-child{border-left:3px solid #667eea}

/* ─── Misc ─── */
.text-muted{color:#64748b}
.star-btn{background:none;border:none;color:#64748b;font-size:15px;transition:.15s;cursor:pointer}
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
    .mail-sidebar{width:0;min-width:0;position:fixed;z-index:100;transition:.3s;overflow:hidden}
    .mail-sidebar.open{width:220px;min-width:220px}
    .mail-main{width:100%}
}
</style>
</head>
<body>
<?php
$_curUri   = $_SERVER['REQUEST_URI'] ?? '/mail';
$_folder   = $_GET['folder'] ?? 'inbox';
$_mailUser = \Core\Auth::user() ?? [];
$_appName  = defined('APP_NAME') ? APP_NAME : 'Platform';
$_appUrl   = defined('APP_URL')  ? APP_URL  : '';
?>
<div class="mail-app">
    <!-- Sidebar -->
    <aside class="mail-sidebar" id="mailSidebar">
        <div class="sidebar-logo">
            <div class="logo-icon"><i class="fas fa-envelope"></i></div>
            <div>
                <div class="logo-text"><?= htmlspecialchars($_appName, ENT_QUOTES, 'UTF-8') ?></div>
                <div class="logo-sub">Mail</div>
            </div>
        </div>
        <div class="sidebar-compose">
            <a href="/mail/compose" class="btn-compose"><i class="fas fa-pen"></i> Compose</a>
        </div>
        <nav class="sidebar-nav">
            <?php
            $_isSearch   = strpos($_curUri, '/mail/search') !== false;
            $_isSettings = strpos($_curUri, '/mail/settings') !== false;
            $_isView     = strpos($_curUri, '/mail/view/') !== false;
            $_isCompose  = strpos($_curUri, '/mail/compose') !== false;
            $_isInbox    = !$_isSearch && !$_isSettings && !$_isView && !$_isCompose;
            ?>
            <a href="/mail" class="mail-nav-item <?= ($_isInbox && ($_folder === 'inbox' || $_folder === '')) ? 'active' : '' ?>">
                <i class="fas fa-inbox"></i> Inbox
                <?php
                try {
                    $__db = \Core\Database::getInstance();
                    $__uc = (int)($__db->fetch("SELECT COUNT(*) AS c FROM mail_synced_messages WHERE user_id = ? AND is_read = 0 AND is_deleted = 0 AND is_archived = 0", [\Core\Auth::id()])['c'] ?? 0);
                    if ($__uc > 0) echo '<span class="nav-badge">' . $__uc . '</span>';
                } catch (\Exception $e) {}
                ?>
            </a>
            <a href="/mail?folder=starred" class="mail-nav-item <?= ($_isInbox && $_folder === 'starred') ? 'active' : '' ?>">
                <i class="fas fa-star"></i> Starred
            </a>
            <a href="/mail?folder=archived" class="mail-nav-item <?= ($_isInbox && $_folder === 'archived') ? 'active' : '' ?>">
                <i class="fas fa-archive"></i> Archived
            </a>
            <a href="/mail/search" class="mail-nav-item <?= $_isSearch ? 'active' : '' ?>">
                <i class="fas fa-search"></i> Search
            </a>
            <a href="/mail/settings" class="mail-nav-item <?= $_isSettings ? 'active' : '' ?>">
                <i class="fas fa-cog"></i> Settings
            </a>
        </nav>
        <div class="sidebar-footer">
            <div class="sidebar-user">
                <div class="user-avatar"><?= strtoupper(substr($_mailUser['name'] ?? 'U', 0, 1)) ?></div>
                <div class="user-info">
                    <div class="u-name"><?= htmlspecialchars($_mailUser['name'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                    <div class="u-email"><?= htmlspecialchars($_mailUser['email'] ?? '', ENT_QUOTES, 'UTF-8') ?></div>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main area -->
    <div class="mail-main">
        <div class="mail-topbar">
            <button class="btn-icon" onclick="document.getElementById('mailSidebar').classList.toggle('open')" title="Toggle sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-search">
                <form action="/mail/search" method="GET">
                    <div class="search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" name="q" class="mail-search-input" placeholder="Search emails…"
                               value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </form>
            </div>
            <div class="topbar-actions">
                <button class="btn-icon" id="mailSyncBtn" onclick="mailSyncInbox()" title="Sync inbox">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <a href="<?= htmlspecialchars($_appUrl, ENT_QUOTES, 'UTF-8') ?>/dashboard" class="btn-icon" title="Back to platform">
                    <i class="fas fa-home"></i>
                </a>
            </div>
        </div>

        <div class="mail-content">
            <?php if (\Core\Helpers::hasFlash('success')): ?>
            <div class="mail-alert mail-alert-success">
                <i class="fas fa-check-circle"></i> <?= htmlspecialchars(\Core\Helpers::getFlash('success'), ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php endif; ?>
            <?php if (\Core\Helpers::hasFlash('error')): ?>
            <div class="mail-alert mail-alert-error">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars(\Core\Helpers::getFlash('error'), ENT_QUOTES, 'UTF-8') ?>
            </div>
            <?php endif; ?>

            <?php \Core\View::yield('content'); ?>
        </div>
    </div>
</div>

<script>
const mailCsrfToken = document.querySelector('meta[name="csrf-token"]').content;

function mailSyncInbox() {
    const btn = document.getElementById('mailSyncBtn');
    btn.querySelector('i').classList.add('fa-spin');
    fetch('/mail/sync', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: '_csrf_token=' + encodeURIComponent(mailCsrfToken)
    }).then(r => r.json()).then(d => {
        btn.querySelector('i').classList.remove('fa-spin');
        if (d.synced > 0) location.reload();
    }).catch(() => btn.querySelector('i').classList.remove('fa-spin'));
}

function mailPostAction(url, params, callback) {
    const allParams = Object.assign({_csrf_token: mailCsrfToken}, params);
    const body = Object.entries(allParams)
        .map(([k, v]) => encodeURIComponent(k) + '=' + encodeURIComponent(v))
        .join('&');
    fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body
    }).then(r => r.json()).then(callback).catch(e => console.error(e));
}
</script>
</body>
</html>
