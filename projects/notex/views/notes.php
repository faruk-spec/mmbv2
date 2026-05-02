<?php use Core\View; use Core\Security; ?>
<?php View::extend('notex:app'); ?>
<?php View::section('content'); ?>

<style>
    .nx-notes-toolbar {
        display: flex;
        gap: 0.625rem;
        margin-bottom: 1.25rem;
        flex-wrap: wrap;
        align-items: center;
    }
    .nx-notes-toolbar .search-wrap {
        flex: 1;
        min-width: 10rem;
        position: relative;
    }
    .nx-notes-toolbar .search-wrap i {
        position: absolute;
        left: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        color: var(--text-secondary);
        font-size: 0.8125rem;
        pointer-events: none;
    }
    .nx-notes-toolbar .search-wrap input {
        width: 100%;
        padding: 0.625rem 0.875rem 0.625rem 2.25rem;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        color: var(--text-primary);
        font-family: inherit;
        font-size: var(--font-sm);
    }
    .nx-notes-toolbar select {
        padding: 0.625rem 0.875rem;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        color: var(--text-primary);
        font-family: inherit;
        font-size: var(--font-sm);
    }
    .note-card-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }
    .note-card-link .note-card {
        cursor: pointer;
        position: relative;
    }
    .note-card-link .note-card:hover {
        border-color: rgba(245,158,11,0.4);
        transform: translateY(-0.125rem);
        box-shadow: 0 0.375rem 1.25rem rgba(0,0,0,0.2);
    }
    .note-card-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 0.75rem;
        padding-top: 0.625rem;
        border-top: 1px solid var(--border-color);
    }
    .note-card-actions-row {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        z-index: 2;
        display: flex;
        gap: 0.25rem;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .note-card-link:hover .note-card-actions-row { opacity: 1; }
    .note-card-delete {
        position: static;
        z-index: auto;
        opacity: 1;
        transition: none;
    }
    .note-card-link:hover .note-card-delete { opacity: 1; }
    @media (max-width: 48rem) {
        .nx-notes-toolbar { flex-direction: column; align-items: stretch; }
        .nx-notes-toolbar .search-wrap { min-width: 0; }
        .note-card-actions-row { opacity: 1; }
    }
    /* Three-dot dropdown */
    .nx-3dot-wrap { position: relative; display: inline-block; }
    .nx-3dot-dropdown {
        position: absolute;
        top: calc(100% + 0.25rem);
        right: 0;
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        min-width: 9.5rem;
        z-index: 20;
        box-shadow: 0 0.375rem 1.25rem rgba(0,0,0,0.35);
        display: none;
        flex-direction: column;
        overflow: hidden;
    }
    .nx-3dot-dropdown.open { display: flex; }
    .nx-3dot-dropdown a,
    .nx-3dot-dropdown button {
        padding: 0.5rem 0.875rem;
        font-size: var(--font-xs);
        color: var(--text-primary);
        background: transparent;
        border: none;
        text-align: left;
        cursor: pointer;
        text-decoration: none;
        font-family: inherit;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: background 0.15s;
        line-height: 1.4;
    }
    .nx-3dot-dropdown a:hover,
    .nx-3dot-dropdown button:hover { background: var(--bg-secondary); }
    .nx-3dot-dropdown button.nx-dd-danger { color: var(--red, #ff6b6b); }
    /* QR modal */
    .nx-qr-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.75);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }
    .nx-qr-backdrop.open { display: flex; }
    .nx-qr-modal {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 0.75rem;
        padding: 1.5rem;
        max-width: 21rem;
        width: 92%;
        text-align: center;
    }
    .nx-qr-modal-head {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 0.75rem;
    }
    .nx-qr-modal-head h3 { font-size: var(--font-md); font-weight: 700; }
    .nx-qr-url {
        font-size: var(--font-xs);
        color: var(--text-secondary);
        word-break: break-all;
        margin-bottom: 0.875rem;
    }
    #nxQrContainer { display: flex; justify-content: center; min-height: 10rem; margin-bottom: 0.75rem; }
    #nxQrContainer canvas,
    #nxQrContainer img { border-radius: 0.5rem; border: 3px solid #fff; }
    .nx-qr-modal-btns { display: flex; gap: 0.5rem; }
    .nx-qr-modal-btns a,
    .nx-qr-modal-btns button {
        flex: 1;
        padding: 0.5rem 0.5rem;
        font-size: var(--font-xs);
        border-radius: 0.375rem;
        text-decoration: none;
        text-align: center;
        cursor: pointer;
        border: 1px solid var(--border-color);
        background: var(--bg-secondary);
        color: var(--text-primary);
        font-family: inherit;
        transition: background 0.15s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.375rem;
    }
    .nx-qr-modal-btns a:hover,
    .nx-qr-modal-btns button:hover { background: var(--border-color); }
    .nx-qr-modal-btns a.nx-qr-primary { background: var(--cyan, #00f0ff); color: #000; border-color: var(--cyan, #00f0ff); }
    .nx-qr-modal-btns a.nx-qr-primary:hover { opacity: 0.85; }
</style>

<!-- Search & Filter bar -->
<form method="GET" action="/projects/notex/notes" class="nx-notes-toolbar">
    <div class="search-wrap">
        <i class="fas fa-search"></i>
        <input type="text" name="q" value="<?= View::e($search ?? '') ?>" placeholder="Search notes…">
    </div>
    <select name="folder">
        <option value="">All Folders</option>
        <?php foreach ($folders as $folder): ?>
        <option value="<?= $folder['id'] ?>" <?= ($currentFolder ?? null) == $folder['id'] ? 'selected' : '' ?>><?= View::e($folder['name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-search"></i></button>
    <?php if ($search || $currentFolder): ?>
    <a href="/projects/notex/notes" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
    <?php endif; ?>
    <a href="/projects/notex/create<?= $currentFolder ? '?folder=' . (int)$currentFolder : '' ?>" class="btn btn-primary btn-sm">
        <i class="fas fa-plus"></i> New Note
    </a>
</form>

<?php if (!empty($notes)): ?>
<div class="notes-grid">
    <?php foreach ($notes as $note): ?>
    <a href="/projects/notex/notes/<?= $note['id'] ?>/edit" class="note-card-link">
        <div class="note-card" id="nc-<?= $note['id'] ?>">
            <div class="note-card-accent" style="background:<?= View::e($note['color'] ?? '#ffd700') ?>;"></div>
            <!-- Actions: pin + qr + three-dot -->
            <div class="note-card-actions-row" onclick="event.stopPropagation();event.preventDefault();">
                <button type="button"
                        class="btn btn-secondary btn-sm btn-icon pin-btn"
                        data-note-id="<?= $note['id'] ?>"
                        data-pinned="<?= $note['is_pinned'] ? '1' : '0' ?>"
                        title="<?= $note['is_pinned'] ? 'Unpin' : 'Pin' ?>"
                        style="color:<?= $note['is_pinned'] ? 'var(--nx-accent)' : 'var(--text-secondary)' ?>;">
                    <i class="fas fa-thumbtack" style="font-size:0.6875rem;"></i>
                </button>
                <?php if (!empty($note['share_token'])): ?>
                <button type="button"
                        class="btn btn-secondary btn-sm btn-icon"
                        style="color:var(--cyan,#00f0ff);"
                        title="View Share QR"
                        data-share-url="<?= View::e((defined('APP_URL') ? APP_URL : '') . '/projects/notex/shared/' . $note['share_token']) ?>"
                        data-note-title="<?= View::e($note['title']) ?>"
                        onclick="nxOpenQr(this)">
                    <i class="fas fa-qrcode" style="font-size:0.6875rem;"></i>
                </button>
                <?php endif; ?>
                <!-- Three-dot menu -->
                <div class="nx-3dot-wrap">
                    <button type="button"
                            class="btn btn-secondary btn-sm btn-icon"
                            title="More options"
                            onclick="nxToggle3dot(this)">
                        <i class="fas fa-ellipsis-v" style="font-size:0.6875rem;"></i>
                    </button>
                    <div class="nx-3dot-dropdown">
                        <a href="/projects/notex/notes/<?= $note['id'] ?>/edit">
                            <i class="fas fa-edit" style="color:var(--nx-accent);font-size:0.75rem;"></i> Edit
                        </a>
                        <?php if (!empty($note['share_token'])): ?>
                        <a href="/projects/qr/analytics" target="_blank" rel="noopener">
                            <i class="fas fa-chart-bar" style="color:var(--cyan,#00f0ff);font-size:0.75rem;"></i> View Analytics
                        </a>
                        <?php else: ?>
                        <a href="/projects/notex/notes/<?= $note['id'] ?>/edit#share">
                            <i class="fas fa-chart-bar" style="color:var(--text-secondary);font-size:0.75rem;"></i> View Analytics
                        </a>
                        <?php endif; ?>
                        <form method="POST" action="/projects/notex/notes/<?= $note['id'] ?>/delete"
                              onsubmit="return confirm('Move to trash?');" style="margin:0;padding:0;">
                            <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
                            <button type="submit" class="nx-dd-danger" style="width:100%;">
                                <i class="fas fa-trash" style="font-size:0.75rem;"></i> Delete
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div style="padding-left:0.625rem;">
                <div class="note-card-title">
                    <?php if ($note['is_pinned']): ?>
                        <i class="fas fa-thumbtack pin-icon nc-pin-icon" style="margin-right:0.25rem;font-size:0.75rem;"></i>
                    <?php endif; ?>
                    <?= View::e($note['title']) ?>
                </div>
                <div class="note-card-preview"><?= View::e(substr(strip_tags($note['content'] ?? ''), 0, 150)) ?></div>
                <div class="note-card-footer">
                    <span style="font-size:var(--font-xs);color:var(--text-secondary);">
                        <?= $note['folder_name'] ? '<i class="fas fa-folder" style="margin-right:0.1875rem;"></i>' . View::e($note['folder_name']) : '&nbsp;' ?>
                    </span>
                    <span style="font-size:var(--font-xs);color:var(--text-secondary);">
                        <?= date('M d', strtotime($note['updated_at'] ?? $note['created_at'])) ?>
                    </span>
                </div>
            </div>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="empty-state">
    <div class="empty-icon"><i class="fas fa-sticky-note"></i></div>
    <p style="color:var(--text-secondary);margin-bottom:1rem;">No notes found.</p>
    <a href="/projects/notex/create" class="btn btn-primary"><i class="fas fa-plus"></i> Create Note</a>
</div>
<?php endif; ?>

<!-- QR Modal -->
<div class="nx-qr-backdrop" id="nxQrBackdrop" onclick="if(event.target===this)nxCloseQr()">
    <div class="nx-qr-modal">
        <div class="nx-qr-modal-head">
            <h3><i class="fas fa-qrcode" style="color:var(--cyan,#00f0ff);margin-right:0.375rem;"></i>Share QR Code</h3>
            <button type="button" class="btn btn-secondary btn-sm btn-icon" onclick="nxCloseQr()">
                <i class="fas fa-times" style="font-size:0.75rem;"></i>
            </button>
        </div>
        <div class="nx-qr-url" id="nxQrUrlLabel"></div>
        <div id="nxQrContainer"></div>
        <div class="nx-qr-modal-btns">
            <button type="button" onclick="nxCloseQr()">
                <i class="fas fa-times"></i> Close
            </button>
            <a id="nxQrOpenInQrx" href="/projects/qr/generate" target="_blank" rel="noopener" class="nx-qr-primary">
                <i class="fas fa-external-link-alt"></i> Open in QRx
            </a>
        </div>
    </div>
</div>

<script>
(function() {
    var csrfToken = document.querySelector('meta[name="csrf-token"]')
        ? document.querySelector('meta[name="csrf-token"]').content : '';

    // ── Pin ──────────────────────────────────────────────────────────────
    document.querySelectorAll('.pin-btn').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var noteId = this.dataset.noteId;
            var self = this;
            fetch('/projects/notex/notes/' + noteId + '/pin', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: '_token=' + encodeURIComponent(csrfToken)
            }).then(function(r) { return r.json(); }).then(function(data) {
                if (data.success) {
                    self.dataset.pinned = data.pinned ? '1' : '0';
                    self.title = data.pinned ? 'Unpin' : 'Pin';
                    self.style.color = data.pinned ? 'var(--nx-accent)' : 'var(--text-secondary)';
                    var card = document.getElementById('nc-' + noteId);
                    if (card) {
                        var titlePin = card.querySelector('.nc-pin-icon');
                        if (data.pinned && !titlePin) {
                            var t = card.querySelector('.note-card-title');
                            if (t) {
                                var ic = document.createElement('i');
                                ic.className = 'fas fa-thumbtack pin-icon nc-pin-icon';
                                ic.style.marginRight = '0.25rem';
                                ic.style.fontSize = '0.75rem';
                                t.insertBefore(ic, t.firstChild);
                            }
                        } else if (!data.pinned && titlePin) {
                            titlePin.remove();
                        }
                    }
                }
            }).catch(function() {});
        });
    });

    // ── Three-dot dropdown ───────────────────────────────────────────────
    window.nxToggle3dot = function(btn) {
        event.preventDefault();
        event.stopPropagation();
        var dd = btn.nextElementSibling;
        var wasOpen = dd.classList.contains('open');
        document.querySelectorAll('.nx-3dot-dropdown.open').forEach(function(d) { d.classList.remove('open'); });
        if (!wasOpen) dd.classList.add('open');
    };

    document.addEventListener('click', function() {
        document.querySelectorAll('.nx-3dot-dropdown.open').forEach(function(d) { d.classList.remove('open'); });
    });

    // ── QR Modal ─────────────────────────────────────────────────────────
    var _qrLib = null;

    function loadQrLib(cb) {
        if (window.QRCode) { cb(); return; }
        var s = document.createElement('script');
        s.src = 'https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js';
        s.integrity = 'sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSQX0FslNhTDadL4O5SAGapGt4FodqL8My0mA==';
        s.crossOrigin = 'anonymous';
        s.onload = cb;
        s.onerror = function() {
            var s2 = document.createElement('script');
            s2.src = 'https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js';
            s2.onload = cb;
            document.head.appendChild(s2);
        };
        document.head.appendChild(s);
    }

    window.nxOpenQr = function(btn) {
        event.preventDefault();
        event.stopPropagation();
        var url = btn.dataset.shareUrl;
        document.getElementById('nxQrUrlLabel').textContent = url;
        document.getElementById('nxQrOpenInQrx').href = '/projects/qr/generate';
        document.getElementById('nxQrContainer').innerHTML = '';
        document.getElementById('nxQrBackdrop').classList.add('open');
        loadQrLib(function() {
            document.getElementById('nxQrContainer').innerHTML = '';
            new QRCode(document.getElementById('nxQrContainer'), {
                text: url,
                width: 180,
                height: 180,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
        });
    };

    window.nxCloseQr = function() {
        document.getElementById('nxQrBackdrop').classList.remove('open');
        document.getElementById('nxQrContainer').innerHTML = '';
    };

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') nxCloseQr();
    });
})();
</script>

<?php View::end(); ?>
