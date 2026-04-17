<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;">
    <div>
        <h1>Preloader &amp; Loader Settings</h1>
        <p style="color:var(--text-secondary);">
            Configure the home-page preloader, page-change spinner, and skeleton placeholders.
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

            <!-- ── Home-page Full-page Preloader ──────────── -->
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-home" style="color:var(--cyan);margin-right:8px;"></i>Home-Page Preloader</h3>
                </div>
                <div style="padding:20px;">
                    <p style="color:var(--text-secondary);font-size:13px;margin-bottom:16px;">
                        <i class="fas fa-info-circle" style="color:var(--cyan);"></i>
                        This full-screen overlay <strong>only appears on the home page</strong> (<code>/</code>).
                        All other page navigation uses the spinner below.
                    </p>

                    <div class="form-group">
                        <label class="form-checkbox">
                            <input type="checkbox" name="preloader_enabled" value="1"
                                   id="preloaderEnabled"
                                <?= ($settings['preloader_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                            <span>Enable home-page preloader</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Preloader Type</label>
                        <div style="display:flex;gap:20px;">
                            <label class="form-checkbox">
                                <input type="radio" name="preloader_type" value="text" id="typeText"
                                    <?= ($settings['preloader_type'] ?? 'text') === 'text' ? 'checked' : '' ?>>
                                <span>Text Animation</span>
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
                            <select name="preloader_animation" class="form-input" id="animSelect">
                                <?php
                                $animations = [
                                    'reveal' => 'Reveal (letters slide up — recommended)',
                                    'wave'   => 'Wave (letters animate one by one)',
                                    'pulse'  => 'Pulse (fade in/out)',
                                    'spin'   => 'Spinning icon',
                                    'bounce' => 'Bounce',
                                ];
                                $current = $settings['preloader_animation'] ?? 'reveal';
                                foreach ($animations as $val => $label): ?>
                                <option value="<?= $val ?>" <?= $current === $val ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                            <div class="form-group">
                                <label class="form-label">Text / Gradient Start Color</label>
                                <div style="display:flex;gap:10px;align-items:center;">
                                    <input type="color" name="preloader_text_color" class="form-input" id="textColorPicker"
                                           style="width:50px;height:38px;padding:2px;cursor:pointer;"
                                           value="<?= View::e($settings['preloader_text_color'] ?? '#7C3AED') ?>">
                                    <input type="text" id="textColorHex" class="form-input" style="flex:1;"
                                           value="<?= View::e($settings['preloader_text_color'] ?? '#7C3AED') ?>"
                                           placeholder="#7C3AED" maxlength="9" readonly>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Background Color</label>
                                <div style="display:flex;gap:10px;align-items:center;">
                                    <input type="color" name="preloader_bg_color" class="form-input" id="bgColorPicker"
                                           style="width:50px;height:38px;padding:2px;cursor:pointer;"
                                           value="<?= View::e($settings['preloader_bg_color'] ?? '#06060a') ?>">
                                    <input type="text" id="bgColorHex" class="form-input" style="flex:1;"
                                           value="<?= View::e($settings['preloader_bg_color'] ?? '#06060a') ?>"
                                           placeholder="#06060a" maxlength="9" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Animation Speed (ms)</label>
                            <input type="range" name="preloader_speed" class="form-input" id="speedRange"
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
                                     style="max-height:70px;border-radius:4px;background:rgba(255,255,255,.05);padding:4px;">
                                <span style="font-size:12px;color:var(--text-secondary);">Current image</span>
                            </div>
                            <?php endif; ?>
                            <p style="color:var(--text-secondary);font-size:12px;margin-top:6px;">Leave empty to keep the existing image.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Page-Change / Action Spinner ───────────── -->
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-circle-notch" style="color:var(--magenta);margin-right:8px;"></i>Page-Change &amp; Action Spinner</h3>
                </div>
                <div style="padding:20px;">
                    <p style="color:var(--text-secondary);font-size:13px;margin-bottom:16px;">
                        <i class="fas fa-info-circle" style="color:var(--cyan);"></i>
                        A centered overlay spinner shown on <strong>every link click / page navigation</strong> and form submit.
                        Uses a backdrop blur effect.
                    </p>

                    <div class="form-group">
                        <label class="form-checkbox">
                            <input type="checkbox" name="action_loader_enabled" value="1"
                                <?= ($settings['action_loader_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                            <span>Enable page-change spinner</span>
                        </label>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Backdrop Blur Amount</label>
                        <input type="range" name="action_loader_blur" class="form-input" id="blurRange"
                               min="0" max="20" step="1"
                               value="<?= (int)($settings['action_loader_blur'] ?? 8) ?>"
                               style="padding:0;height:auto;">
                        <div style="display:flex;justify-content:space-between;font-size:12px;color:var(--text-secondary);margin-top:4px;">
                            <span>No blur (0px)</span>
                            <span id="blurVal"><?= (int)($settings['action_loader_blur'] ?? 8) ?>px</span>
                            <span>Max blur (20px)</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Custom Spinner Image (SVG / GIF / PNG — optional)</label>
                        <input type="file" name="action_loader_image" class="form-input"
                               accept=".svg,.gif,.png,.webp">
                        <?php if (!empty($settings['action_loader_image'])): ?>
                        <div style="margin-top:10px;display:flex;align-items:center;gap:14px;">
                            <img src="<?= View::e($settings['action_loader_image']) ?>"
                                 alt="Current spinner image"
                                 style="max-height:60px;border-radius:4px;background:rgba(255,255,255,.05);padding:4px;">
                            <span style="font-size:12px;color:var(--text-secondary);">Current spinner</span>
                        </div>
                        <?php endif; ?>
                        <p style="color:var(--text-secondary);font-size:12px;margin-top:6px;">
                            Leave empty to use the default gradient SVG spinner. Leave existing to keep it.
                        </p>
                    </div>
                </div>
            </div>

            <!-- ── Skeleton ───────────────────────────────── -->
            <div class="card" style="margin-bottom:20px;">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-layer-group" style="color:var(--orange);margin-right:8px;"></i>Skeleton Placeholders</h3>
                </div>
                <div style="padding:20px;">
                    <div class="form-group">
                        <label class="form-checkbox">
                            <input type="checkbox" name="skeleton_enabled" value="1"
                                <?= ($settings['skeleton_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                            <span>Enable skeleton placeholders on dashboard / apps grid</span>
                        </label>
                        <p style="color:var(--text-secondary);font-size:12px;margin-top:4px;">
                            Shows animated shimmer cards for ~400ms while the apps grid is loading, then fades them out when real content is ready.
                        </p>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Save Settings
            </button>
        </form>
    </div>

    <!-- ── Preview Panel ──────────────────────────── -->
    <div>
        <div class="card" style="position:sticky;top:20px;">
            <h3 style="margin-bottom:14px;padding:16px 16px 0;">Live Preview</h3>
            <div style="padding:0 16px 16px;">
                <div id="previewBox" style="border-radius:8px;overflow:hidden;border:1px solid var(--border-color);min-height:140px;display:flex;align-items:center;justify-content:center;background:#06060a;transition:background .3s;position:relative;">
                    <div id="previewInner" style="text-align:center;padding:20px;"></div>
                </div>

                <hr style="margin:16px 0;border-color:var(--border-color);">
                <p style="font-size:12px;color:var(--text-secondary);margin-bottom:10px;">
                    <i class="fas fa-info-circle" style="color:var(--orange);"></i>
                    Skeleton shimmer example:
                </p>
                <div style="padding:12px;background:var(--bg-secondary);border-radius:8px;">
                    <?php for ($i = 0; $i < 3; $i++): ?>
                    <div style="height:12px;border-radius:4px;margin-bottom:8px;width:<?= [80,60,90][$i] ?>%;
                                background:linear-gradient(90deg,rgba(255,255,255,.06) 25%,rgba(255,255,255,.12) 50%,rgba(255,255,255,.06) 75%);
                                background-size:200% 100%;
                                animation:skeletonShimmer 1.4s infinite <?= $i * 0.2 ?>s;"></div>
                    <?php endfor; ?>
                    <div style="height:50px;border-radius:8px;
                                background:linear-gradient(90deg,rgba(255,255,255,.06) 25%,rgba(255,255,255,.12) 50%,rgba(255,255,255,.06) 75%);
                                background-size:200% 100%;
                                animation:skeletonShimmer 1.4s infinite .4s;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes skeletonShimmer {
    0%   { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
@keyframes revealChar { to { transform: translateY(0); } }
@keyframes spinIcon   { to { transform: rotate(360deg); } }
@keyframes waveChar   { 0%{transform:translateY(0)} 100%{transform:translateY(-10px)} }
@keyframes pulseText  { 0%,100%{opacity:1} 50%{opacity:.3} }
@keyframes bounceText { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
@keyframes barFill    { from{width:0} to{width:100%} }
</style>

<script>
(function () {
    var typeRadios = document.querySelectorAll('input[name="preloader_type"]');
    typeRadios.forEach(function(r) {
        r.addEventListener('change', function() {
            document.getElementById('textOptions').style.display  = (r.value === 'text')  ? '' : 'none';
            document.getElementById('imageOptions').style.display = (r.value === 'image') ? '' : 'none';
            updatePreview();
        });
    });

    document.getElementById('textColorPicker').addEventListener('input', function() {
        document.getElementById('textColorHex').value = this.value;
        updatePreview();
    });
    document.getElementById('bgColorPicker').addEventListener('input', function() {
        document.getElementById('bgColorHex').value = this.value;
        document.getElementById('previewBox').style.background = this.value;
        updatePreview();
    });

    var speedRange = document.getElementById('speedRange');
    if (speedRange) {
        speedRange.addEventListener('input', function() {
            document.getElementById('speedVal').textContent = this.value + 'ms';
            updatePreview();
        });
    }

    var blurRange = document.getElementById('blurRange');
    if (blurRange) {
        blurRange.addEventListener('input', function() {
            document.getElementById('blurVal').textContent = this.value + 'px';
        });
    }

    var textInput = document.querySelector('input[name="preloader_text"]');
    if (textInput) textInput.addEventListener('input', updatePreview);
    var animSelect = document.getElementById('animSelect');
    if (animSelect) animSelect.addEventListener('change', updatePreview);

    function updatePreview() {
        var type  = document.querySelector('input[name="preloader_type"]:checked').value;
        var inner = document.getElementById('previewInner');
        var box   = document.getElementById('previewBox');
        var bg    = (document.getElementById('bgColorPicker').value || '#06060a');
        var color = (document.getElementById('textColorPicker').value || '#7C3AED');
        var text  = (textInput ? textInput.value : '') || 'Loading…';
        var anim  = animSelect ? animSelect.value : 'reveal';
        var speed = (speedRange ? speedRange.value : 800) + 'ms';

        box.style.background = bg;

        if (type === 'text') {
            if (anim === 'reveal') {
                var chars = text.split('').map(function(c, i) {
                    return '<span style="display:inline-block;overflow:hidden;vertical-align:bottom;">'
                         + '<span style="display:inline-block;transform:translateY(110%);animation:revealChar 0.6s cubic-bezier(0.22,0.61,0.36,1) forwards;animation-delay:' + (i * 60) + 'ms;">'
                         + (c === ' ' ? '&nbsp;' : c) + '</span></span>';
                }).join('');
                inner.innerHTML = '<div style="font-family:\'Poppins\',sans-serif;font-size:1.5rem;font-weight:700;letter-spacing:6px;'
                    + 'background:linear-gradient(135deg,' + color + ',#00F5FF);'
                    + '-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">' + chars + '</div>'
                    + '<div style="width:100px;height:2px;background:rgba(255,255,255,.12);border-radius:2px;margin:20px auto 0;overflow:hidden;">'
                    + '<div style="height:100%;background:linear-gradient(90deg,' + color + ',#00F5FF);animation:barFill 1.2s ease-out forwards;"></div></div>';
            } else if (anim === 'wave') {
                var wLetters = text.split('').map(function(c, i) {
                    return '<span style="display:inline-block;color:' + color + ';animation:waveChar ' + speed + ' ease-in-out ' + (i * 80) + 'ms infinite alternate;">' + (c === ' ' ? '&nbsp;' : c) + '</span>';
                }).join('');
                inner.innerHTML = '<div style="font-family:\'Poppins\',sans-serif;font-size:1.5rem;font-weight:700;letter-spacing:5px;">' + wLetters + '</div>';
            } else if (anim === 'pulse') {
                inner.innerHTML = '<div style="font-family:\'Poppins\',sans-serif;font-size:1.5rem;font-weight:700;letter-spacing:5px;color:' + color + ';animation:pulseText ' + speed + ' ease-in-out infinite;">' + text + '</div>';
            } else if (anim === 'spin') {
                inner.innerHTML = '<div style="display:flex;flex-direction:column;align-items:center;gap:12px;color:' + color + ';">'
                    + '<svg width="42" height="42" viewBox="0 0 54 54" style="animation:spinIcon ' + speed + ' linear infinite;filter:drop-shadow(0 0 6px rgba(124,58,237,.6));">'
                    + '<circle cx="27" cy="27" r="22" fill="none" stroke="' + color + '" stroke-width="4" stroke-dasharray="110 30" stroke-linecap="round"/></svg>'
                    + '<span style="font-family:\'Poppins\',sans-serif;font-size:.9rem;">' + text + '</span></div>';
            } else {
                inner.innerHTML = '<div style="font-family:\'Poppins\',sans-serif;font-size:1.5rem;font-weight:700;letter-spacing:5px;color:' + color + ';animation:bounceText ' + speed + ' ease infinite;">' + text + '</div>';
            }
        } else {
            inner.innerHTML = '<div style="color:rgba(255,255,255,.4);font-size:.85rem;">[Image preloader — preview not available]</div>';
        }
    }

    // Initial run
    var bg0 = document.getElementById('bgColorPicker').value || '#06060a';
    document.getElementById('previewBox').style.background = bg0;
    updatePreview();
})();
</script>
<?php View::endSection(); ?>
