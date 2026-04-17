<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;">
    <div>
        <h1>Preloader &amp; Skeleton Settings</h1>
        <p style="color:var(--text-secondary);">
            Configure the full-page preloader, per-action loading spinner, and skeleton placeholders.
        </p>
    </div>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>
<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div class="grid grid-3">
    <div style="grid-column:span 2;">
        <form method="POST" action="/admin/settings/preloader" enctype="multipart/form-data">
            <?= \Core\Security::csrfField() ?>

            <!-- ── Full-page Preloader ─────────────────── -->
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-spinner" style="color:var(--cyan);margin-right:8px;"></i>Full-Page Preloader</h3>
                </div>

                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" name="preloader_enabled" value="1"
                               id="preloaderEnabled"
                            <?= ($settings['preloader_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <span>Enable full-page preloader</span>
                    </label>
                    <p style="color:var(--text-secondary);font-size:12px;margin-top:4px;">
                        Shows an overlay while the page is loading (on every full-page navigation).
                    </p>
                </div>

                <div class="form-group">
                    <label class="form-label">Preloader Type</label>
                    <div style="display:flex;gap:20px;">
                        <label class="form-checkbox">
                            <input type="radio" name="preloader_type" value="text" id="typeText"
                                <?= ($settings['preloader_type'] ?? 'text') === 'text' ? 'checked' : '' ?>>
                            <span>Text / Wave Animation</span>
                        </label>
                        <label class="form-checkbox">
                            <input type="radio" name="preloader_type" value="image" id="typeImage"
                                <?= ($settings['preloader_type'] ?? 'text') === 'image' ? 'checked' : '' ?>>
                            <span>Custom Image (PNG / GIF / SVG)</span>
                        </label>
                    </div>
                </div>

                <!-- Text options -->
                <div id="textOptions" style="<?= ($settings['preloader_type'] ?? 'text') === 'image' ? 'display:none;' : '' ?>">
                    <div class="form-group">
                        <label class="form-label">Loading Text</label>
                        <input type="text" name="preloader_text" class="form-input"
                               value="<?= View::e($settings['preloader_text'] ?? 'Loading…') ?>"
                               placeholder="Loading…" maxlength="60">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Animation Style</label>
                        <select name="preloader_animation" class="form-input">
                            <?php
                            $animations = [
                                'wave'   => 'Wave (letters animate one by one)',
                                'pulse'  => 'Pulse (fade in/out)',
                                'spin'   => 'Spinning icon',
                                'bounce' => 'Bounce',
                            ];
                            $current = $settings['preloader_animation'] ?? 'wave';
                            foreach ($animations as $val => $label): ?>
                            <option value="<?= $val ?>" <?= $current === $val ? 'selected' : '' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div class="form-group">
                            <label class="form-label">Text / Icon Colour</label>
                            <div style="display:flex;gap:10px;align-items:center;">
                                <input type="color" name="preloader_text_color" class="form-input"
                                       style="width:50px;height:38px;padding:2px;cursor:pointer;"
                                       value="<?= View::e($settings['preloader_text_color'] ?? '#00f0ff') ?>">
                                <input type="text" id="textColorHex" class="form-input"
                                       style="flex:1;"
                                       value="<?= View::e($settings['preloader_text_color'] ?? '#00f0ff') ?>"
                                       placeholder="#00f0ff" maxlength="9" readonly>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Background Colour</label>
                            <div style="display:flex;gap:10px;align-items:center;">
                                <input type="color" name="preloader_bg_color" class="form-input"
                                       style="width:50px;height:38px;padding:2px;cursor:pointer;"
                                       value="<?= View::e($settings['preloader_bg_color'] ?? '#06060a') ?>">
                                <input type="text" id="bgColorHex" class="form-input"
                                       style="flex:1;"
                                       value="<?= View::e($settings['preloader_bg_color'] ?? '#06060a') ?>"
                                       placeholder="#06060a" maxlength="9" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Animation Speed (ms)</label>
                        <input type="range" name="preloader_speed" class="form-input"
                               id="speedRange"
                               min="200" max="3000" step="100"
                               value="<?= (int)($settings['preloader_speed'] ?? 800) ?>"
                               style="padding:0;height:auto;">
                        <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--text-secondary);margin-top:4px;">
                            <span>Fast (200ms)</span>
                            <span id="speedVal"><?= (int)($settings['preloader_speed'] ?? 800) ?>ms</span>
                            <span>Slow (3000ms)</span>
                        </div>
                    </div>
                </div>

                <!-- Image options -->
                <div id="imageOptions" style="<?= ($settings['preloader_type'] ?? 'text') !== 'image' ? 'display:none;' : '' ?>">
                    <div class="form-group">
                        <label class="form-label">Upload Image (PNG / GIF / SVG / WebP)</label>
                        <input type="file" name="preloader_image" class="form-input"
                               accept=".png,.gif,.svg,.webp,.jpg,.jpeg">
                        <?php if (!empty($settings['preloader_image_path'])): ?>
                        <div style="margin-top:10px;display:flex;align-items:center;gap:14px;">
                            <img src="<?= View::e($settings['preloader_image_path']) ?>"
                                 alt="Current preloader image"
                                 style="max-height:60px;border-radius:4px;background:rgba(255,255,255,.05);padding:4px;">
                            <span style="font-size:12px;color:var(--text-secondary);">Current image</span>
                        </div>
                        <?php endif; ?>
                        <p style="color:var(--text-secondary);font-size:12px;margin-top:6px;">
                            Leave empty to keep the existing image.
                        </p>
                    </div>
                </div>
            </div>

            <!-- ── Skeleton & Action Loader ────────────── -->
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-layer-group" style="color:var(--magenta);margin-right:8px;"></i>Skeleton &amp; Action Loader</h3>
                </div>

                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" name="skeleton_enabled" value="1"
                            <?= ($settings['skeleton_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <span>Enable skeleton placeholders on page load</span>
                    </label>
                    <p style="color:var(--text-secondary);font-size:12px;margin-top:4px;">
                        Shows animated grey shimmer blocks while dashboard cards are loading.
                    </p>
                </div>

                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" name="action_loader_enabled" value="1"
                            <?= ($settings['action_loader_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                        <span>Enable SVG spinner on button/link clicks</span>
                    </label>
                    <p style="color:var(--text-secondary);font-size:12px;margin-top:4px;">
                        Adds a small spinner next to buttons when an action is in progress.
                    </p>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Preloader Settings
            </button>
        </form>
    </div>

    <!-- ── Preview Panel ──────────────────────────── -->
    <div>
        <div class="card" style="position:sticky;top:20px;">
            <h3 style="margin-bottom:14px;">Live Preview</h3>

            <div id="previewBox" style="border-radius:8px;overflow:hidden;border:1px solid var(--border-color);min-height:120px;display:flex;align-items:center;justify-content:center;background:#06060a;transition:background .3s;">
                <!-- Rendered by JS -->
                <div id="previewInner" style="text-align:center;padding:20px;"></div>
            </div>

            <hr style="margin:18px 0;border-color:var(--border-color);">
            <p style="font-size:12px;color:var(--text-secondary);">
                <i class="fas fa-info-circle" style="color:var(--cyan);"></i>
                Skeleton example:
            </p>
            <div style="margin-top:10px;">
                <div class="skeleton-line" style="height:14px;border-radius:4px;margin-bottom:8px;width:80%;background:linear-gradient(90deg,rgba(255,255,255,.06) 25%,rgba(255,255,255,.12) 50%,rgba(255,255,255,.06) 75%);background-size:200% 100%;animation:skeletonShimmer 1.4s infinite;"></div>
                <div class="skeleton-line" style="height:14px;border-radius:4px;margin-bottom:8px;width:60%;background:linear-gradient(90deg,rgba(255,255,255,.06) 25%,rgba(255,255,255,.12) 50%,rgba(255,255,255,.06) 75%);background-size:200% 100%;animation:skeletonShimmer 1.4s infinite .2s;"></div>
                <div class="skeleton-line" style="height:14px;border-radius:4px;width:90%;background:linear-gradient(90deg,rgba(255,255,255,.06) 25%,rgba(255,255,255,.12) 50%,rgba(255,255,255,.06) 75%);background-size:200% 100%;animation:skeletonShimmer 1.4s infinite .4s;"></div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes skeletonShimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
</style>

<script>
(function () {
    // Toggle text/image panels
    document.querySelectorAll('input[name="preloader_type"]').forEach(function (r) {
        r.addEventListener('change', function () {
            document.getElementById('textOptions').style.display  = (r.value === 'text')  ? '' : 'none';
            document.getElementById('imageOptions').style.display = (r.value === 'image') ? '' : 'none';
            updatePreview();
        });
    });

    // Sync colour pickers → hex fields
    document.querySelector('input[name="preloader_text_color"]').addEventListener('input', function () {
        document.getElementById('textColorHex').value = this.value;
        updatePreview();
    });
    document.querySelector('input[name="preloader_bg_color"]').addEventListener('input', function () {
        document.getElementById('bgColorHex').value = this.value;
        document.getElementById('previewBox').style.background = this.value;
        updatePreview();
    });

    // Speed range label
    var speedRange = document.getElementById('speedRange');
    if (speedRange) {
        speedRange.addEventListener('input', function () {
            document.getElementById('speedVal').textContent = this.value + 'ms';
            updatePreview();
        });
    }

    document.querySelector('input[name="preloader_text"]').addEventListener('input', updatePreview);
    document.querySelector('select[name="preloader_animation"]').addEventListener('change', updatePreview);

    function updatePreview() {
        var type  = document.querySelector('input[name="preloader_type"]:checked').value;
        var inner = document.getElementById('previewInner');
        var box   = document.getElementById('previewBox');
        var bg    = document.querySelector('input[name="preloader_bg_color"]').value || '#06060a';
        var color = document.querySelector('input[name="preloader_text_color"]').value || '#00f0ff';
        var text  = document.querySelector('input[name="preloader_text"]').value || 'Loading…';
        var anim  = document.querySelector('select[name="preloader_animation"]').value;
        var speed = (speedRange ? speedRange.value : 800) + 'ms';

        box.style.background = bg;

        if (type === 'text') {
            if (anim === 'wave') {
                var letters = text.split('').map(function (c, i) {
                    return '<span style="display:inline-block;animation:waveChar ' + speed + ' ease-in-out ' + (i * 80) + 'ms infinite alternate;color:' + color + ';">' + (c === ' ' ? '&nbsp;' : c) + '</span>';
                }).join('');
                inner.innerHTML = '<div style="font-size:1.4rem;font-weight:700;letter-spacing:3px;">' + letters + '</div>';
            } else if (anim === 'pulse') {
                inner.innerHTML = '<div style="font-size:1.4rem;font-weight:700;letter-spacing:3px;color:' + color + ';animation:pulseText ' + speed + ' ease-in-out infinite;">' + text + '</div>';
            } else if (anim === 'spin') {
                inner.innerHTML = '<div style="display:flex;flex-direction:column;align-items:center;gap:12px;color:' + color + ';">'
                    + '<svg width="40" height="40" viewBox="0 0 40 40" style="animation:spinIcon ' + speed + ' linear infinite;">'
                    + '<circle cx="20" cy="20" r="16" fill="none" stroke="' + color + '" stroke-width="3" stroke-dasharray="80 20"/></svg>'
                    + '<span style="font-size:.9rem;">' + text + '</span></div>';
            } else {
                inner.innerHTML = '<div style="font-size:1.4rem;font-weight:700;letter-spacing:3px;color:' + color + ';animation:bounceText ' + speed + ' ease infinite;">' + text + '</div>';
            }
        } else {
            inner.innerHTML = '<div style="color:' + color + ';font-size:.85rem;opacity:.6;">[Image preloader — see upload above]</div>';
        }
    }

    // Run once on load
    var bg0 = document.querySelector('input[name="preloader_bg_color"]').value || '#06060a';
    document.getElementById('previewBox').style.background = bg0;
    updatePreview();
})();
</script>
<?php View::endSection(); ?>
