<?php use Core\View; use Core\Security; ?>
<?php View::extend('notex:app'); ?>
<?php View::section('content'); ?>

<style>
    .nx-editor-layout {
        display: grid;
        grid-template-columns: 1fr 18rem;
        gap: 1rem;
        align-items: start;
    }
    .nx-editor-main { display: flex; flex-direction: column; gap: 0.75rem; }
    .nx-editor-sidebar { display: flex; flex-direction: column; gap: 0.75rem; }
    .nx-field-title {
        width: 100%;
        padding: 0.75rem 1rem;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        color: var(--text-primary);
        font-family: inherit;
        font-size: 1.1rem;
        font-weight: 600;
        transition: border-color 0.2s;
    }
    .nx-field-title:focus { outline: none; border-color: var(--nx-accent); box-shadow: 0 0 0 3px rgba(245,158,11,0.1); }
    .nx-field-content {
        width: 100%;
        min-height: 28rem;
        padding: 1rem;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 0.5rem;
        color: var(--text-primary);
        font-family: inherit;
        font-size: var(--font-sm);
        line-height: 1.8;
        resize: vertical;
        transition: border-color 0.2s;
    }
    .nx-field-content:focus { outline: none; border-color: var(--nx-accent); box-shadow: 0 0 0 3px rgba(245,158,11,0.1); }
    .nx-sidebar-card {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 0.625rem;
        padding: 1rem;
    }
    .nx-sidebar-card-title {
        font-size: var(--font-xs);
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-secondary);
        margin-bottom: 0.75rem;
    }
    .nx-pin-toggle {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        cursor: pointer;
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        background: var(--bg-secondary);
        transition: background 0.2s;
    }
    .nx-pin-toggle:hover { background: rgba(245,158,11,0.1); }
    .nx-tag-chip {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        padding: 0.3125rem 0.625rem;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 1.25rem;
        font-size: var(--font-xs);
        cursor: pointer;
        transition: border-color 0.2s;
        user-select: none;
    }
    .nx-tag-chip input[type=checkbox] { display: none; }
    .nx-tag-chip.checked { border-color: var(--nx-accent); background: rgba(245,158,11,0.1); }
    .nx-danger-row {
        display: flex;
        gap: 0.5rem;
        padding-top: 0.75rem;
        border-top: 1px solid var(--border-color);
        margin-top: 0.25rem;
    }
    @media (max-width: 56rem) {
        .nx-editor-layout { grid-template-columns: 1fr; }
        .nx-editor-sidebar { order: -1; }
        .nx-field-content { min-height: 14rem; }
    }
</style>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1rem;flex-wrap:wrap;gap:0.5rem;">
    <h2 style="font-size:var(--font-xl);font-weight:700;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:70%;">
        <i class="fas fa-edit" style="color:var(--nx-accent);margin-right:0.375rem;"></i><?= View::e($note['title']) ?>
    </h2>
    <a href="/projects/notex/notes" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Back
    </a>
</div>

<form method="POST" action="/projects/notex/notes/<?= $note['id'] ?>/update">
    <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
    <div class="nx-editor-layout">
        <!-- Main Column -->
        <div class="nx-editor-main">
            <input type="text" name="title" class="nx-field-title"
                   value="<?= View::e($note['title']) ?>" required>
            <textarea name="content" class="nx-field-content"><?= View::e($note['content'] ?? '') ?></textarea>
            <div style="display:flex;gap:0.625rem;flex-wrap:wrap;">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save
                </button>
                <a href="/projects/notex/notes" class="btn btn-secondary">Back to Notes</a>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="nx-editor-sidebar">
            <!-- Folder -->
            <div class="nx-sidebar-card">
                <div class="nx-sidebar-card-title"><i class="fas fa-folder" style="margin-right:0.3rem;"></i> Folder</div>
                <select name="folder_id" class="form-input form-select" style="font-size:var(--font-sm);">
                    <option value="">No folder</option>
                    <?php foreach ($folders as $folder): ?>
                    <option value="<?= $folder['id'] ?>" <?= $note['folder_id'] == $folder['id'] ? 'selected' : '' ?>>
                        <?= View::e($folder['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Note Color -->
            <div class="nx-sidebar-card">
                <div class="nx-sidebar-card-title"><i class="fas fa-palette" style="margin-right:0.3rem;"></i> Note Color</div>
                <input type="color" name="color" class="form-input"
                       value="<?= View::e($note['color'] ?? '#f59e0b') ?>"
                       style="height:2.75rem;padding:0.25rem 0.5rem;cursor:pointer;width:100%;">
            </div>

            <!-- Pin & Actions -->
            <div class="nx-sidebar-card">
                <div class="nx-sidebar-card-title"><i class="fas fa-sliders-h" style="margin-right:0.3rem;"></i> Options</div>
                <label class="nx-pin-toggle">
                    <input type="checkbox" name="is_pinned" value="1"
                           <?= $note['is_pinned'] ? 'checked' : '' ?>
                           style="width:1rem;height:1rem;accent-color:var(--nx-accent);">
                    <i class="fas fa-thumbtack" style="color:var(--nx-accent);font-size:0.8125rem;"></i>
                    <span style="font-size:var(--font-sm);">Pin this note</span>
                </label>
                <div class="nx-danger-row">
                    <form method="POST" action="/projects/notex/notes/<?= $note['id'] ?>/delete"
                          onsubmit="return confirm('Move to trash?');" style="flex:1;">
                        <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
                        <button type="submit" class="btn btn-danger btn-sm" style="width:100%;">
                            <i class="fas fa-trash"></i> Move to Trash
                        </button>
                    </form>
                </div>
            </div>

            <?php if (!empty($allTags)): ?>
            <!-- Tags -->
            <div class="nx-sidebar-card">
                <div class="nx-sidebar-card-title"><i class="fas fa-tags" style="margin-right:0.3rem;"></i> Tags</div>
                <div style="display:flex;flex-wrap:wrap;gap:0.375rem;">
                    <?php foreach ($allTags as $tag): ?>
                    <?php $isChecked = in_array($tag['id'], $noteTagIds ?? []); ?>
                    <label class="nx-tag-chip <?= $isChecked ? 'checked' : '' ?>">
                        <input type="checkbox" name="tag_ids[]" value="<?= $tag['id'] ?>"
                               <?= $isChecked ? 'checked' : '' ?>
                               onchange="this.closest('label').classList.toggle('checked', this.checked)">
                        <span style="color:<?= View::e($tag['color']) ?>;font-size:0.625rem;">●</span>
                        <?= View::e($tag['name']) ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Share Note -->
            <div class="nx-sidebar-card">
                <div class="nx-sidebar-card-title"><i class="fas fa-share-alt" style="margin-right:0.3rem;color:var(--cyan);"></i> Share</div>
                <form method="POST" action="/projects/notex/notes/<?= $note['id'] ?>/share">
                    <input type="hidden" name="_token" value="<?= Security::generateCsrfToken() ?>">
                    <div style="margin-bottom:0.5rem;">
                        <label class="form-label" style="font-size:var(--font-xs);">Access</label>
                        <select name="access" class="form-input form-select" style="font-size:var(--font-sm);">
                            <option value="view">View only</option>
                            <option value="edit">Can edit</option>
                        </select>
                    </div>
                    <div style="margin-bottom:0.625rem;">
                        <label class="form-label" style="font-size:var(--font-xs);">Expires</label>
                        <input type="datetime-local" name="expires_at" class="form-input" style="font-size:var(--font-sm);">
                    </div>
                    <button type="submit" class="btn btn-secondary btn-sm" style="width:100%;">
                        <i class="fas fa-link"></i> Generate Link
                    </button>
                </form>
                <?php if ($note['share_token']): ?>
                <div style="margin-top:0.625rem;padding:0.625rem;background:var(--bg-secondary);border-radius:0.375rem;font-size:var(--font-xs);word-break:break-all;">
                    <a href="/projects/notex/shared/<?= View::e($note['share_token']) ?>" style="color:var(--cyan);" target="_blank">
                        /shared/<?= View::e($note['share_token']) ?>
                    </a>
                </div>
                <div style="margin-top:0.5rem;display:flex;gap:0.375rem;">
                    <button type="button" class="btn btn-secondary btn-sm"
                            style="flex:1;font-size:var(--font-xs);"
                            onclick="nxEditShowQr()"
                            title="Generate QR for share link">
                        <i class="fas fa-qrcode" style="color:var(--cyan,#00f0ff);"></i> Generate QR
                    </button>
                    <a href="/projects/qr/generate" target="_blank" rel="noopener"
                       class="btn btn-secondary btn-sm"
                       style="flex:1;font-size:var(--font-xs);text-align:center;"
                       title="Open QRx Generator">
                        <i class="fas fa-external-link-alt" style="color:var(--nx-accent);"></i> Open in QRx
                    </a>
                </div>
                <!-- Inline QR preview -->
                <div id="nxEditQrWrap" style="display:none;margin-top:0.625rem;text-align:center;">
                    <div id="nxEditQrContainer" style="display:flex;justify-content:center;"></div>
                    <div style="margin-top:0.375rem;font-size:var(--font-xs);color:var(--text-secondary);">Scan to open shared note</div>
                </div>
                <?php endif; ?>
            </div>

            <?php if (!empty($versions)): ?>
            <!-- Version History -->
            <div class="nx-sidebar-card">
                <div class="nx-sidebar-card-title"><i class="fas fa-history" style="margin-right:0.3rem;color:var(--magenta);"></i> Version History</div>
                <div style="display:flex;flex-direction:column;gap:0.375rem;">
                    <?php foreach ($versions as $i => $ver): ?>
                    <div style="display:flex;justify-content:space-between;align-items:center;font-size:var(--font-xs);padding:0.375rem 0.5rem;background:var(--bg-secondary);border-radius:0.375rem;">
                        <span style="color:var(--text-secondary);">v<?= count($versions) - $i ?></span>
                        <span style="color:var(--text-secondary);"><?= date('M d, H:i', strtotime($ver['created_at'])) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</form>

<?php if ($note['share_token']): ?>
<script>
(function() {
    var shareUrl = <?= json_encode((defined('APP_URL') ? APP_URL : '') . '/projects/notex/shared/' . $note['share_token']) ?>;
    var qrLoaded = false;

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

    window.nxEditShowQr = function() {
        var wrap = document.getElementById('nxEditQrWrap');
        var container = document.getElementById('nxEditQrContainer');
        if (wrap.style.display !== 'none') {
            wrap.style.display = 'none';
            container.innerHTML = '';
            qrLoaded = false;
            return;
        }
        wrap.style.display = 'block';
        if (qrLoaded) return;
        container.innerHTML = '';
        loadQrLib(function() {
            container.innerHTML = '';
            new QRCode(container, {
                text: shareUrl,
                width: 160,
                height: 160,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.H
            });
            qrLoaded = true;
        });
    };
})();
</script>
<?php endif; ?>

<?php View::end(); ?>
