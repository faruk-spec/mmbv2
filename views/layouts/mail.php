<!DOCTYPE html>
<?php
$_mailUiTheme = 'default';
$_mailDefaultMode = 'dark';
try {
    $_mthConfig = \Controllers\Admin\ThemeController::loadThemeForLayout();
    $_mailUiTheme = $_mthConfig['theme'] ?? 'default';
    $_mailDefaultMode = $_mthConfig['mode'] ?? 'dark';
} catch (\Exception $e) {}
?>
<html lang="en" data-ui-theme="<?= htmlspecialchars($_mailUiTheme) ?>">
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
.mail-wrapper{display:flex;flex-direction:column;height:100vh;}
.mail-app{display:flex;flex:1;overflow:hidden}
.mail-sidebar{width:220px;min-width:220px;background:#0a0a12;border-right:1px solid rgba(255,255,255,.07);display:flex;flex-direction:column;overflow-y:auto}
.mail-main{flex:1;display:flex;flex-direction:column;overflow:hidden}
.mail-topbar{background:#0a0a12;border-bottom:1px solid rgba(255,255,255,.07);padding:0 20px;height:52px;display:flex;align-items:center;gap:12px;flex-shrink:0}
.mail-content{flex:1;overflow-y:auto;padding:20px}

/* ─── Sidebar ─── */
.sidebar-logo{padding:14px 20px;border-bottom:1px solid rgba(255,255,255,.07);display:flex;align-items:center;gap:10px}
.sidebar-logo .logo-icon{width:32px;height:32px;background:linear-gradient(135deg,#667eea,#764ba2);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:15px;color:#fff}
.sidebar-logo .logo-text{font-size:15px;font-weight:600;color:#e2e8f0}
.sidebar-compose{padding:10px 14px}
.btn-compose{width:100%;padding:10px;background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border:none;border-radius:10px;font-size:13px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:8px;transition:.2s;cursor:pointer}
.btn-compose:hover{opacity:.9;transform:translateY(-1px)}
.sidebar-nav{flex:1;padding:6px 0}
.sidebar-section{font-size:10px;font-weight:600;color:var(--text-tertiary, #374151);text-transform:uppercase;letter-spacing:.8px;padding:10px 20px 4px;margin-top:4px;}
.mail-nav-item{display:flex;align-items:center;gap:10px;padding:8px 20px;color:#94a3b8;font-size:13px;transition:.15s;cursor:pointer;border-left:3px solid transparent;text-decoration:none}
.mail-nav-item:hover{background:rgba(255,255,255,.04);color:#e2e8f0}
.mail-nav-item.active{background:rgba(102,126,234,.1);color:#667eea;border-left-color:#667eea}
.mail-nav-item .nav-icon{width:16px;text-align:center;flex-shrink:0}
.mail-nav-item .nav-badge{margin-left:auto;background:#e74c3c;color:#fff;border-radius:10px;padding:1px 7px;font-size:11px;font-weight:600}
.mail-nav-item .nav-badge-gray{margin-left:auto;background:rgba(255,255,255,.1);color:#94a3b8;border-radius:10px;padding:1px 7px;font-size:11px;}
.sidebar-footer{padding:10px 14px;border-top:1px solid rgba(255,255,255,.07)}
.sidebar-user{display:flex;align-items:center;gap:10px}
.user-avatar{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#667eea,#764ba2);display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:700;color:#fff;flex-shrink:0}
.user-info .u-name{font-size:13px;font-weight:500;color:#e2e8f0}
.user-info .u-email{font-size:11px;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:130px}

/* ─── Topbar ─── */
.topbar-search{flex:1;max-width:460px}
.mail-search-input{width:100%;padding:8px 14px 8px 36px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:8px;color:#e2e8f0;font-size:13px;outline:none;transition:.2s}
.mail-search-input:focus{border-color:#667eea;background:rgba(102,126,234,.08)}
.search-wrap{position:relative}
.search-wrap i{position:absolute;left:11px;top:50%;transform:translateY(-50%);color:#64748b;font-size:13px}
.topbar-actions{display:flex;align-items:center;gap:8px;margin-left:auto}
.btn-icon{width:34px;height:34px;border:none;background:rgba(255,255,255,.06);border-radius:8px;color:#94a3b8;display:flex;align-items:center;justify-content:center;transition:.15s;font-size:13px;cursor:pointer}
.btn-icon:hover{background:rgba(255,255,255,.1);color:#e2e8f0}
.topbar-kbd-hint{font-size:11px;color:var(--text-tertiary, #374151);background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.08);border-radius:4px;padding:2px 6px;cursor:default;}
.topbar-kbd-hint:hover{color:#94a3b8;border-color:rgba(255,255,255,.15);}

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
.mail-table td{padding:10px 12px;border-bottom:1px solid rgba(255,255,255,.04);font-size:13px;vertical-align:middle}
.mail-table tr:hover td{background:rgba(255,255,255,.03)}
.mail-table tr:hover .mail-row-actions{opacity:1}
.mail-table tr.unread td{background:rgba(102,126,234,.04)}
.mail-table tr.unread td:first-child{border-left:3px solid #667eea}
.mail-row-actions{opacity:0;transition:opacity .15s;display:flex;gap:4px;align-items:center}
.mail-row-actions .btn-icon{width:28px;height:28px;font-size:12px;}

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

/* ─── Toast notifications ─── */
#mailToastArea{position:fixed;bottom:24px;left:50%;transform:translateX(-50%);z-index:9999;display:flex;flex-direction:column;gap:8px;align-items:center;pointer-events:none;}
.mail-toast{background:#1e1e2e;border:1px solid rgba(255,255,255,.15);border-radius:10px;padding:10px 18px;font-size:13px;color:#e2e8f0;box-shadow:0 4px 20px rgba(0,0,0,.5);display:flex;align-items:center;gap:12px;pointer-events:all;animation:toastIn .25s ease;}
.mail-toast .toast-action{color:#a5b4fc;cursor:pointer;font-weight:600;font-size:12px;text-decoration:underline;}
.mail-toast .toast-close{color:#64748b;cursor:pointer;background:none;border:none;font-size:16px;line-height:1;padding:0 0 0 4px;}
@keyframes toastIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}

/* ─── Keyboard shortcut modal ─── */
#kbdModal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:10000;align-items:center;justify-content:center;}
#kbdModal.open{display:flex;}
.kbd-box{background:#111117;border:1px solid rgba(255,255,255,.12);border-radius:16px;padding:28px 32px;max-width:540px;width:90%;max-height:80vh;overflow-y:auto;}
.kbd-box h3{font-size:16px;font-weight:600;margin-bottom:20px;color:#e2e8f0;}
.kbd-row{display:flex;align-items:center;gap:12px;padding:7px 0;border-bottom:1px solid rgba(255,255,255,.04);}
.kbd-row:last-child{border:none;}
.kbd-row .kbd-key{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.15);border-radius:5px;padding:2px 8px;font-size:12px;font-family:monospace;color:#a5b4fc;flex-shrink:0;min-width:28px;text-align:center;}
.kbd-row .kbd-desc{font-size:13px;color:#94a3b8;}

/* ─── Responsive ─── */
/* Desktop: sidebar always visible; collapsed when .collapsed is toggled */
.mail-sidebar.collapsed{width:0!important;min-width:0!important;overflow:hidden!important;}

@media(max-width:768px){
    .mail-sidebar{width:0;min-width:0;position:fixed;z-index:100;transition:.3s;overflow:hidden;top:0;bottom:0}
    .mail-sidebar.open{width:220px;min-width:220px}
    .mail-main{width:100%}
}

/* ─── Light Theme ─── */
html[data-theme="light"] body{background:#f4f7fb;color:#1e293b}
html[data-theme="light"] .mail-sidebar,
html[data-theme="light"] .mail-topbar{background:#ffffff;border-color:#dbe3ef}
html[data-theme="light"] .mail-main{background:#f4f7fb}
html[data-theme="light"] .mail-content{background:#f4f7fb}
html[data-theme="light"] .mail-card{background:#ffffff;border-color:#dbe3ef}
html[data-theme="light"] .mail-nav-item{color:#475569}
html[data-theme="light"] .mail-nav-item:hover{background:#eef2f8;color:#1e293b}
html[data-theme="light"] .mail-nav-item.active{background:#e8f0ff;color:#2952cc}
html[data-theme="light"] .sidebar-logo .logo-text,
html[data-theme="light"] .user-info .u-name{color:#1e293b}
html[data-theme="light"] .u-email,.text-muted{color:#64748b}
html[data-theme="light"] .mail-search-input,
html[data-theme="light"] .form-input{background:#ffffff;border-color:#dbe3ef;color:#1e293b}
html[data-theme="light"] .btn-secondary{background:#eef2f8;color:#334155;border-color:#dbe3ef}
html[data-theme="light"] .btn-icon{background:#eef2f8;color:#334155}
html[data-theme="light"] .mail-table th{color:#64748b;border-color:#dbe3ef}
html[data-theme="light"] .mail-table td{border-color:#e8edf5}
html[data-theme="light"] .mail-table tr:hover td{background:#f8fafc}
html[data-theme="light"] .topbar-kbd-hint{color:#64748b;background:#f1f5f9;border-color:#dbe3ef}
</style>
</head>
<body>
<?php
$_curUri   = $_SERVER['REQUEST_URI'] ?? '/mail';
$_folder   = $_GET['folder'] ?? 'inbox';
$_mailUser = \Core\Auth::user() ?? [];

// Pre-compute counts for sidebar badges
$_inboxUnread  = 0;
$_sentCount    = 0;
$_starredCount = 0;
$_trashCount   = 0;
try {
    $__db = \Core\Database::getInstance();
    $__uid = \Core\Auth::id();
    $_inboxUnread  = (int)($__db->fetch("SELECT COUNT(*) AS c FROM mail_synced_messages WHERE user_id = ? AND is_read = 0 AND is_deleted = 0 AND is_archived = 0", [$__uid])['c'] ?? 0);
    $_starredCount = (int)($__db->fetch("SELECT COUNT(*) AS c FROM mail_synced_messages WHERE user_id = ? AND is_starred = 1 AND is_deleted = 0", [$__uid])['c'] ?? 0);
    $_trashCount   = (int)($__db->fetch("SELECT COUNT(*) AS c FROM mail_synced_messages WHERE user_id = ? AND is_deleted = 1", [$__uid])['c'] ?? 0);
} catch (\Throwable $_e) {}

$_isSearch   = strpos($_curUri, '/mail/search') !== false;
$_isSettings = strpos($_curUri, '/mail/settings') !== false;
$_isView     = strpos($_curUri, '/mail/view/') !== false;
$_isSent     = strpos($_curUri, '/mail/sent') !== false;
$_isCompose  = strpos($_curUri, '/mail/compose') !== false;
$_isInbox    = !$_isSearch && !$_isSettings && !$_isView && !$_isCompose && !$_isSent;
?>

<div class="mail-wrapper">
<div class="mail-app">
    <!-- Sidebar -->
    <aside class="mail-sidebar" id="mailSidebar">
        <div class="sidebar-logo">
            <div class="logo-icon"><i class="fas fa-envelope"></i></div>
            <div class="logo-text">Mail</div>
        </div>
        <div class="sidebar-compose">
            <a href="/mail/compose" class="btn-compose"><i class="fas fa-pen"></i> Compose</a>
        </div>
        <nav class="sidebar-nav">
            <div class="sidebar-section">Folders</div>

            <a href="/mail" class="mail-nav-item <?= ($_isInbox && ($_folder === 'inbox' || $_folder === '')) ? 'active' : '' ?>">
                <i class="fas fa-inbox nav-icon"></i> Inbox
                <?php if ($_inboxUnread > 0): ?>
                <span class="nav-badge"><?= $_inboxUnread ?></span>
                <?php endif; ?>
            </a>

            <a href="/mail/sent" class="mail-nav-item <?= $_isSent ? 'active' : '' ?>">
                <i class="fas fa-paper-plane nav-icon"></i> Sent
            </a>

            <a href="/mail?folder=starred" class="mail-nav-item <?= ($_isInbox && $_folder === 'starred') ? 'active' : '' ?>">
                <i class="fas fa-star nav-icon"></i> Starred
                <?php if ($_starredCount > 0): ?>
                <span class="nav-badge-gray"><?= $_starredCount ?></span>
                <?php endif; ?>
            </a>

            <a href="/mail?folder=archived" class="mail-nav-item <?= ($_isInbox && $_folder === 'archived') ? 'active' : '' ?>">
                <i class="fas fa-archive nav-icon"></i> Archived
            </a>

            <a href="/mail?folder=trash" class="mail-nav-item <?= ($_isInbox && $_folder === 'trash') ? 'active' : '' ?>" style="color:<?= ($_isInbox && $_folder === 'trash') ? '' : '#94a3b8' ?>">
                <i class="fas fa-trash nav-icon"></i> Trash
                <?php if ($_trashCount > 0): ?>
                <span class="nav-badge-gray"><?= $_trashCount ?></span>
                <?php endif; ?>
            </a>

            <div class="sidebar-section" style="margin-top:8px;">Tools</div>

            <a href="/mail/search" class="mail-nav-item <?= $_isSearch ? 'active' : '' ?>">
                <i class="fas fa-search nav-icon"></i> Search
            </a>

            <a href="/mail/settings" class="mail-nav-item <?= $_isSettings ? 'active' : '' ?>">
                <i class="fas fa-cog nav-icon"></i> Settings
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
            <button class="btn-icon" onclick="mailToggleSidebar()" title="Toggle sidebar (m)">
                <i class="fas fa-bars"></i>
            </button>
            <div class="topbar-search">
                <form action="/mail/search" method="GET" id="mailSearchForm">
                    <div class="search-wrap">
                        <i class="fas fa-search"></i>
                        <input type="text" name="q" id="mailSearchInput" class="mail-search-input" placeholder="Search emails… (press / to focus)"
                               value="<?= htmlspecialchars($_GET['q'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </form>
            </div>
            <div class="topbar-actions">
                <a class="btn-icon" href="<?= \Core\Auth::isAdmin() ? '/admin' : '/dashboard' ?>" title="Go to <?= \Core\Auth::isAdmin() ? 'Admin' : 'Dashboard' ?>">
                    <i class="fas fa-compass"></i>
                </a>
                <button class="btn-icon" id="mailSyncBtn" onclick="mailSyncInbox()" title="Sync inbox (u)">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <button class="btn-icon" id="mailThemeToggle" title="Toggle theme">
                    <i class="fas fa-moon"></i>
                </button>
                <button class="btn-icon topbar-kbd-hint" onclick="document.getElementById('kbdModal').classList.add('open')" title="Keyboard shortcuts">
                    ?
                </button>
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
</div>

<!-- Toast notification area -->
<div id="mailToastArea"></div>

<!-- Keyboard shortcuts modal -->
<div id="kbdModal" onclick="if(event.target===this) this.classList.remove('open')">
    <div class="kbd-box">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
            <h3 style="margin:0;">Keyboard Shortcuts</h3>
            <button class="btn-icon" onclick="document.getElementById('kbdModal').classList.remove('open')"><i class="fas fa-times"></i></button>
        </div>
        <div class="kbd-section-title" style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.6px;color:var(--text-tertiary, #374151);margin-bottom:8px;">Navigation</div>
        <div class="kbd-row"><span class="kbd-key">c</span><span class="kbd-desc">Compose new email</span></div>
        <div class="kbd-row"><span class="kbd-key">g i</span><span class="kbd-desc">Go to Inbox</span></div>
        <div class="kbd-row"><span class="kbd-key">g s</span><span class="kbd-desc">Go to Sent</span></div>
        <div class="kbd-row"><span class="kbd-key">g t</span><span class="kbd-desc">Go to Trash</span></div>
        <div class="kbd-row"><span class="kbd-key">/</span><span class="kbd-desc">Focus search box</span></div>
        <div class="kbd-row"><span class="kbd-key">m</span><span class="kbd-desc">Toggle sidebar</span></div>
        <div class="kbd-row"><span class="kbd-key">u</span><span class="kbd-desc">Sync inbox</span></div>
        <div class="kbd-section-title" style="font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.6px;color:var(--text-tertiary, #374151);margin:14px 0 8px;">Email List</div>
        <div class="kbd-row"><span class="kbd-key">j</span><span class="kbd-desc">Select next email</span></div>
        <div class="kbd-row"><span class="kbd-key">k</span><span class="kbd-desc">Select previous email</span></div>
        <div class="kbd-row"><span class="kbd-key">↵</span><span class="kbd-desc">Open selected email</span></div>
        <div class="kbd-row"><span class="kbd-key">e</span><span class="kbd-desc">Archive selected</span></div>
        <div class="kbd-row"><span class="kbd-key">#</span><span class="kbd-desc">Delete selected</span></div>
        <div class="kbd-row"><span class="kbd-key">s</span><span class="kbd-desc">Star / unstar selected</span></div>
        <div class="kbd-row"><span class="kbd-key">Esc</span><span class="kbd-desc">Close modal / deselect</span></div>
    </div>
</div>

<script>
const mailCsrfToken = document.querySelector('meta[name="csrf-token"]').content;

/* ── Theme ── */
(function () {
    const html = document.documentElement;
    const themeBtn = document.getElementById('mailThemeToggle');
    const icon = themeBtn ? themeBtn.querySelector('i') : null;
    const saved = localStorage.getItem('mailTheme') || 'dark';
    html.setAttribute('data-theme', saved);
    if (icon) icon.className = saved === 'light' ? 'fas fa-sun' : 'fas fa-moon';

    if (themeBtn) {
        themeBtn.addEventListener('click', function () {
            const next = (html.getAttribute('data-theme') || 'dark') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('mailTheme', next);
            if (icon) icon.className = next === 'light' ? 'fas fa-sun' : 'fas fa-moon';
        });
    }
})();

/* ── Toast ── */
function mailToast(msg, opts = {}) {
    const area = document.getElementById('mailToastArea');
    const t    = document.createElement('div');
    t.className = 'mail-toast';
    const icon = opts.icon || 'fa-info-circle';
    const color = opts.color || '#e2e8f0';
    t.innerHTML = '<i class="fas ' + icon + '" style="color:' + color + ';flex-shrink:0;"></i>'
        + '<span>' + msg + '</span>'
        + (opts.action ? '<span class="toast-action" onclick="(' + opts.action + ')();this.closest(\'.mail-toast\').remove();">' + opts.actionLabel + '</span>' : '')
        + '<button class="toast-close" onclick="this.closest(\'.mail-toast\').remove();">×</button>';
    area.appendChild(t);
    setTimeout(() => { t.style.opacity='0'; t.style.transition='opacity .4s'; setTimeout(()=>t.remove(),400); }, opts.duration || 4000);
    return t;
}

/* ── Sidebar toggle ── */
function mailToggleSidebar() {
    const sb = document.getElementById('mailSidebar');
    if (!sb) return;
    if (window.innerWidth <= 768) {
        // Mobile: toggle open class (sidebar is fixed off-screen by default)
        sb.classList.toggle('open');
    } else {
        // Desktop: toggle collapsed class
        sb.classList.toggle('collapsed');
    }
}

/* ── Sync ── */
function mailSyncInbox() {
    const btn = document.getElementById('mailSyncBtn');
    btn.querySelector('i').classList.add('fa-spin');
    fetch('/mail/sync', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json'},
        body: '_csrf_token=' + encodeURIComponent(mailCsrfToken)
    }).then(r => r.json()).then(d => {
        btn.querySelector('i').classList.remove('fa-spin');
        if (d.synced > 0) {
            mailToast(d.message || d.synced + ' message(s) synced', {icon:'fa-envelope',color:'#6ee7b7',duration:3000});
            setTimeout(() => location.reload(), 1200);
        } else {
            mailToast(d.message || 'Inbox is up to date', {icon:'fa-check-circle',color:'#6ee7b7',duration:2500});
        }
    }).catch(() => {
        btn.querySelector('i').classList.remove('fa-spin');
        mailToast('Sync failed — check network', {icon:'fa-exclamation-triangle',color:'#fca5a5'});
    });
}

/* ── AJAX post helper ── */
function mailPostAction(url, params, callback) {
    const allParams = Object.assign({_csrf_token: mailCsrfToken}, params);
    const body = Object.entries(allParams)
        .map(([k, v]) => encodeURIComponent(k) + '=' + encodeURIComponent(v)).join('&');
    fetch(url, {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json'},
        body
    }).then(r => r.json()).then(callback).catch(e => console.error(e));
}

/* ── Keyboard shortcuts ── */
let mailKbdBuffer = '';
let mailKbdTimer  = null;
let mailSelected  = -1;

function mailGetRows() { return Array.from(document.querySelectorAll('.mail-table tbody tr')); }

function mailSelectRow(idx) {
    const rows = mailGetRows();
    if (!rows.length) return;
    rows.forEach(r => r.style.outline = '');
    mailSelected = Math.max(0, Math.min(idx, rows.length - 1));
    rows[mailSelected].style.outline = '2px solid rgba(102,126,234,.5)';
    rows[mailSelected].scrollIntoView({block:'nearest'});
}

document.addEventListener('keydown', function(e) {
    const tag = (e.target.tagName || '').toLowerCase();
    if (tag === 'input' || tag === 'textarea' || tag === 'select' || e.target.isContentEditable) {
        if (e.key === 'Escape') e.target.blur();
        return;
    }

    const key = e.key;

    /* Close modal */
    if (key === 'Escape') { document.getElementById('kbdModal').classList.remove('open'); return; }

    /* Focus search */
    if (key === '/') { e.preventDefault(); document.getElementById('mailSearchInput').focus(); return; }

    /* Compose */
    if (key === 'c') { window.location.href = '/mail/compose'; return; }

    /* Sync */
    if (key === 'u') { mailSyncInbox(); return; }

    /* Toggle sidebar */
    if (key === 'm') { mailToggleSidebar(); return; }

    /* Keyboard shortcuts help */
    if (key === '?') { document.getElementById('kbdModal').classList.add('open'); return; }

    /* List navigation */
    if (key === 'j') { mailSelectRow(mailSelected + 1); return; }
    if (key === 'k') { mailSelectRow(mailSelected - 1); return; }

    if (key === 'Enter' && mailSelected >= 0) {
        const row = mailGetRows()[mailSelected];
        if (row) { const a = row.querySelector('a'); if (a) a.click(); }
        return;
    }

    if (key === 'e' && mailSelected >= 0) {
        const row = mailGetRows()[mailSelected];
        if (row) {
            const id = row.id ? row.id.replace('mail-row-', '') : null;
            if (id) {
                mailPostAction('/mail/archive', {id, state:1}, d => {
                    if (d.success) { row.style.opacity='0'; row.style.transition='opacity .3s'; setTimeout(()=>row.remove(), 300); mailToast('Archived', {icon:'fa-archive',color:'#6ee7b7',duration:2500}); }
                });
            }
        }
        return;
    }

    if (key === '#' && mailSelected >= 0) {
        const row = mailGetRows()[mailSelected];
        if (row) {
            const id = row.id ? row.id.replace('mail-row-', '') : null;
            if (id) {
                mailPostAction('/mail/delete', {id}, d => {
                    if (d.success) { row.style.opacity='0'; row.style.transition='opacity .3s'; setTimeout(()=>row.remove(), 300); mailToast('Deleted', {icon:'fa-trash',color:'#fca5a5',duration:2500}); }
                });
            }
        }
        return;
    }

    if (key === 's' && mailSelected >= 0) {
        const row = mailGetRows()[mailSelected];
        if (row) {
            const starBtn = row.querySelector('.star-btn');
            if (starBtn) starBtn.click();
        }
        return;
    }

    /* Two-key combos: g-i, g-s, g-t */
    if (key === 'g') {
        mailKbdBuffer = 'g';
        clearTimeout(mailKbdTimer);
        mailKbdTimer = setTimeout(() => { mailKbdBuffer = ''; }, 1500);
        return;
    }
    if (mailKbdBuffer === 'g') {
        clearTimeout(mailKbdTimer);
        mailKbdBuffer = '';
        if (key === 'i') { window.location.href = '/mail'; return; }
        if (key === 's') { window.location.href = '/mail/sent'; return; }
        if (key === 't') { window.location.href = '/mail?folder=trash'; return; }
    }
});
</script>
</body>
</html>
