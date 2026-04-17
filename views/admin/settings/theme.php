<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h1><i class="fas fa-palette" style="color: var(--cyan); margin-right: 8px;"></i>Universal Theme</h1>
        <p style="color: var(--text-secondary);">Configure the global look & feel across your entire platform</p>
    </div>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success" style="display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-check-circle"></i>
        <?= View::e(Helpers::getFlash('success')) ?>
    </div>
<?php endif; ?>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error" style="display: flex; align-items: center; gap: 8px;">
        <i class="fas fa-exclamation-circle"></i>
        <?= View::e(Helpers::getFlash('error')) ?>
    </div>
<?php endif; ?>

<form method="POST" action="/admin/settings/theme" id="themeForm">
    <?= \Core\Security::csrfField() ?>

    <!-- Theme Selector -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header" style="margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color);">
            <h3 class="card-title" style="font-size: 1rem; font-weight: 600;"><i class="fas fa-swatchbook" style="margin-right: 8px; color: var(--cyan);"></i>Select Theme</h3>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 16px;">
            <?php
            $themes = [
                'default' => [
                    'name'  => 'Default',
                    'desc'  => 'Vercel-inspired clean developer aesthetic',
                    'icon'  => 'fas fa-code',
                    'dark'  => ['#09090b', '#18181b', '#3b82f6'],
                    'light' => ['#fafafa', '#ffffff', '#2563eb'],
                ],
                'soft' => [
                    'name'  => 'Soft',
                    'desc'  => 'Notion-inspired warm neutral design',
                    'icon'  => 'fas fa-feather-alt',
                    'dark'  => ['#1a1a1a', '#2a2a2a', '#6b9fff'],
                    'light' => ['#f7f6f3', '#ffffff', '#2f7aeb'],
                ],
                'corporate' => [
                    'name'  => 'Corporate',
                    'desc'  => 'Stripe-inspired sharp enterprise look',
                    'icon'  => 'fas fa-briefcase',
                    'dark'  => ['#0a0e1a', '#1f2937', '#635bff'],
                    'light' => ['#f6f9fc', '#ffffff', '#635bff'],
                ],
                'neon' => [
                    'name'  => 'Neon',
                    'desc'  => 'Linear-inspired vibrant developer theme',
                    'icon'  => 'fas fa-bolt',
                    'dark'  => ['#08070b', '#19172a', '#6c63ff'],
                    'light' => ['#faf8ff', '#ffffff', '#5b52e5'],
                ],
            ];
            foreach ($themes as $key => $theme):
                $isActive = ($activeTheme ?? 'default') === $key;
            ?>
            <label class="theme-card <?= $isActive ? 'active' : '' ?>" style="cursor: pointer; display: block; padding: 0; border: 2px solid <?= $isActive ? 'var(--cyan)' : 'var(--border-color)' ?>; border-radius: var(--radius-lg); overflow: hidden; transition: all 0.2s ease; background: var(--bg-card);">
                <input type="radio" name="active_theme" value="<?= $key ?>" <?= $isActive ? 'checked' : '' ?> style="display: none;" onchange="selectTheme(this)">

                <!-- Color preview bar -->
                <div style="display: flex; height: 48px;">
                    <div style="flex: 1; background: <?= $theme['dark'][0] ?>;"></div>
                    <div style="flex: 1; background: <?= $theme['dark'][1] ?>;"></div>
                    <div style="flex: 0.6; background: <?= $theme['dark'][2] ?>;"></div>
                    <div style="flex: 1; background: <?= $theme['light'][0] ?>;"></div>
                    <div style="flex: 1; background: <?= $theme['light'][1] ?>;"></div>
                    <div style="flex: 0.6; background: <?= $theme['light'][2] ?>;"></div>
                </div>

                <div style="padding: 16px;">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px;">
                        <i class="<?= $theme['icon'] ?>" style="color: <?= $theme['dark'][2] ?>; font-size: 16px;"></i>
                        <span style="font-weight: 600; font-size: 14px; color: var(--text-primary);"><?= $theme['name'] ?></span>
                        <?php if ($isActive): ?>
                        <span style="margin-left: auto; font-size: 11px; background: color-mix(in srgb, var(--cyan) 12%, transparent); color: var(--cyan); padding: 2px 8px; border-radius: 9999px; font-weight: 500;">Active</span>
                        <?php endif; ?>
                    </div>
                    <p style="font-size: 12px; color: var(--text-secondary); margin: 0; line-height: 1.4;"><?= $theme['desc'] ?></p>
                </div>
            </label>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Default Mode -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header" style="margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color);">
            <h3 class="card-title" style="font-size: 1rem; font-weight: 600;"><i class="fas fa-adjust" style="margin-right: 8px; color: var(--cyan);"></i>Default Mode</h3>
        </div>

        <div style="display: flex; gap: 16px; flex-wrap: wrap;">
            <?php $currentMode = $defaultMode ?? 'dark'; ?>
            <label style="cursor: pointer; display: flex; align-items: center; gap: 12px; padding: 16px 24px; border: 2px solid <?= $currentMode === 'dark' ? 'var(--cyan)' : 'var(--border-color)' ?>; border-radius: var(--radius-md); flex: 1; min-width: 200px; background: var(--bg-card); transition: all 0.2s ease;">
                <input type="radio" name="default_mode" value="dark" <?= $currentMode === 'dark' ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: var(--cyan);" onchange="selectMode(this)">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-moon" style="color: var(--text-secondary);"></i>
                        <span style="font-weight: 600; font-size: 14px;">Dark Mode</span>
                    </div>
                    <p style="font-size: 12px; color: var(--text-secondary); margin: 4px 0 0;">Default for all users</p>
                </div>
            </label>

            <label style="cursor: pointer; display: flex; align-items: center; gap: 12px; padding: 16px 24px; border: 2px solid <?= $currentMode === 'light' ? 'var(--cyan)' : 'var(--border-color)' ?>; border-radius: var(--radius-md); flex: 1; min-width: 200px; background: var(--bg-card); transition: all 0.2s ease;">
                <input type="radio" name="default_mode" value="light" <?= $currentMode === 'light' ? 'checked' : '' ?> style="width: 18px; height: 18px; accent-color: var(--cyan);" onchange="selectMode(this)">
                <div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <i class="fas fa-sun" style="color: var(--text-secondary);"></i>
                        <span style="font-weight: 600; font-size: 14px;">Light Mode</span>
                    </div>
                    <p style="font-size: 12px; color: var(--text-secondary); margin: 4px 0 0;">Default for all users</p>
                </div>
            </label>
        </div>
        <p style="font-size: 12px; color: var(--text-tertiary); margin-top: 12px;">
            <i class="fas fa-info-circle" style="margin-right: 4px;"></i>
            Users can still toggle dark/light mode individually. This sets the default for new visitors.
        </p>
    </div>

    <!-- Custom Color Overrides -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header" style="margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color);">
            <h3 class="card-title" style="font-size: 1rem; font-weight: 600;"><i class="fas fa-tint" style="margin-right: 8px; color: var(--cyan);"></i>Color Customization</h3>
            <button type="button" onclick="resetColors()" class="btn btn-secondary btn-sm" style="font-size: 12px;">
                <i class="fas fa-undo"></i> Reset to Theme Defaults
            </button>
        </div>

        <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 20px;">
            Override specific colors for the selected theme. Leave empty to use theme defaults.
        </p>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 16px;">
            <?php
            $colorFields = [
                'cyan'    => ['label' => 'Primary / Accent', 'default' => '#3b82f6'],
                'magenta' => ['label' => 'Secondary',        'default' => '#8b5cf6'],
                'green'   => ['label' => 'Success',          'default' => '#22c55e'],
                'orange'  => ['label' => 'Warning',          'default' => '#f59e0b'],
                'red'     => ['label' => 'Danger',           'default' => '#ef4444'],
                'purple'  => ['label' => 'Purple / Accent 2','default' => '#8b5cf6'],
            ];
            foreach ($colorFields as $key => $field):
                $currentVal = $customOverrides[$key] ?? '';
            ?>
            <div>
                <label style="display: block; font-size: 12px; font-weight: 500; color: var(--text-secondary); margin-bottom: 6px;"><?= $field['label'] ?></label>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <input type="color" name="override_<?= $key ?>" value="<?= htmlspecialchars($currentVal ?: $field['default']) ?>"
                           style="width: 36px; height: 36px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); cursor: pointer; padding: 2px; background: var(--bg-secondary);"
                           data-default="<?= $field['default'] ?>"
                           class="color-override">
                    <input type="text" value="<?= htmlspecialchars($currentVal) ?>"
                           placeholder="<?= $field['default'] ?>"
                           style="flex: 1; padding: 8px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); font-size: 12px; font-family: monospace;"
                           class="color-text-input"
                           data-for="override_<?= $key ?>">
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Background Overrides -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header" style="margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color);">
            <h3 class="card-title" style="font-size: 1rem; font-weight: 600;"><i class="fas fa-fill-drip" style="margin-right: 8px; color: var(--cyan);"></i>Background Overrides</h3>
        </div>

        <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 20px;">
            Customize dark and light mode background colors. Leave empty for theme defaults.
        </p>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <!-- Dark backgrounds -->
            <div>
                <h4 style="font-size: 13px; font-weight: 600; margin-bottom: 12px; color: var(--text-primary);"><i class="fas fa-moon" style="margin-right: 6px;"></i>Dark Mode</h4>
                <?php
                $darkBgFields = [
                    'bg_primary_dark'   => ['label' => 'Page Background',    'default' => '#09090b'],
                    'bg_secondary_dark' => ['label' => 'Secondary BG',       'default' => '#111113'],
                    'bg_card_dark'      => ['label' => 'Card Background',    'default' => '#18181b'],
                ];
                foreach ($darkBgFields as $key => $field):
                    $currentVal = $customOverrides[$key] ?? '';
                ?>
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 12px; font-weight: 500; color: var(--text-secondary); margin-bottom: 4px;"><?= $field['label'] ?></label>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="color" name="override_<?= $key ?>" value="<?= htmlspecialchars($currentVal ?: $field['default']) ?>"
                               style="width: 32px; height: 32px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); cursor: pointer; padding: 2px; background: var(--bg-secondary);"
                               data-default="<?= $field['default'] ?>" class="color-override">
                        <input type="text" value="<?= htmlspecialchars($currentVal) ?>"
                               placeholder="<?= $field['default'] ?>"
                               style="flex: 1; padding: 6px 8px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); font-size: 12px; font-family: monospace;"
                               class="color-text-input" data-for="override_<?= $key ?>">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Light backgrounds -->
            <div>
                <h4 style="font-size: 13px; font-weight: 600; margin-bottom: 12px; color: var(--text-primary);"><i class="fas fa-sun" style="margin-right: 6px;"></i>Light Mode</h4>
                <?php
                $lightBgFields = [
                    'bg_primary_light'   => ['label' => 'Page Background',    'default' => '#fafafa'],
                    'bg_secondary_light' => ['label' => 'Secondary BG',       'default' => '#ffffff'],
                    'bg_card_light'      => ['label' => 'Card Background',    'default' => '#ffffff'],
                ];
                foreach ($lightBgFields as $key => $field):
                    $currentVal = $customOverrides[$key] ?? '';
                ?>
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 12px; font-weight: 500; color: var(--text-secondary); margin-bottom: 4px;"><?= $field['label'] ?></label>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="color" name="override_<?= $key ?>" value="<?= htmlspecialchars($currentVal ?: $field['default']) ?>"
                               style="width: 32px; height: 32px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); cursor: pointer; padding: 2px; background: var(--bg-secondary);"
                               data-default="<?= $field['default'] ?>" class="color-override">
                        <input type="text" value="<?= htmlspecialchars($currentVal) ?>"
                               placeholder="<?= $field['default'] ?>"
                               style="flex: 1; padding: 6px 8px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); font-size: 12px; font-family: monospace;"
                               class="color-text-input" data-for="override_<?= $key ?>">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Shape & Style -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header" style="margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color);">
            <h3 class="card-title" style="font-size: 1rem; font-weight: 600;"><i class="fas fa-vector-square" style="margin-right: 8px; color: var(--cyan);"></i>Shape & Style</h3>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <!-- Border Radius -->
            <div>
                <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary); margin-bottom: 8px;">Border Radius</label>
                <?php $currentRadius = $customOverrides['radius_level'] ?? ''; ?>
                <div style="display: flex; gap: 8px;">
                    <?php
                    $radiusOptions = [
                        'sharp'   => ['label' => 'Sharp',   'value' => '4px',  'preview' => '2px'],
                        'medium'  => ['label' => 'Medium',  'value' => '8px',  'preview' => '6px'],
                        'rounded' => ['label' => 'Rounded', 'value' => '14px', 'preview' => '10px'],
                    ];
                    foreach ($radiusOptions as $rKey => $rOpt):
                        $isSelected = $currentRadius === $rKey;
                    ?>
                    <label style="cursor: pointer; flex: 1; text-align: center; padding: 12px; border: 2px solid <?= $isSelected ? 'var(--cyan)' : 'var(--border-color)' ?>; border-radius: var(--radius-md); background: var(--bg-secondary); transition: all 0.2s ease;">
                        <input type="radio" name="override_radius_level" value="<?= $rKey ?>" <?= $isSelected ? 'checked' : '' ?> style="display: none;" onchange="this.closest('form').querySelectorAll('[name=override_radius_level]').forEach(r => { r.closest('label').style.borderColor = r.checked ? 'var(--cyan)' : 'var(--border-color)'; });">
                        <div style="width: 32px; height: 32px; border: 2px solid var(--text-tertiary); border-radius: <?= $rOpt['preview'] ?>; margin: 0 auto 6px;"></div>
                        <span style="font-size: 12px; font-weight: 500; color: var(--text-primary);"><?= $rOpt['label'] ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Shadow Intensity -->
            <div>
                <label style="display: block; font-size: 13px; font-weight: 500; color: var(--text-secondary); margin-bottom: 8px;">Shadow Intensity</label>
                <?php $currentShadow = $customOverrides['shadow_intensity'] ?? ''; ?>
                <div style="display: flex; gap: 8px;">
                    <?php
                    $shadowOptions = [
                        'none'   => ['label' => 'None',   'preview' => 'none'],
                        'subtle' => ['label' => 'Subtle', 'preview' => '0 1px 4px rgba(0,0,0,0.08)'],
                        'normal' => ['label' => 'Normal', 'preview' => '0 2px 8px rgba(0,0,0,0.12)'],
                        'strong' => ['label' => 'Strong', 'preview' => '0 4px 16px rgba(0,0,0,0.18)'],
                    ];
                    foreach ($shadowOptions as $sKey => $sOpt):
                        $isSelected = $currentShadow === $sKey;
                    ?>
                    <label style="cursor: pointer; flex: 1; text-align: center; padding: 12px; border: 2px solid <?= $isSelected ? 'var(--cyan)' : 'var(--border-color)' ?>; border-radius: var(--radius-md); background: var(--bg-secondary); transition: all 0.2s ease;">
                        <input type="radio" name="override_shadow_intensity" value="<?= $sKey ?>" <?= $isSelected ? 'checked' : '' ?> style="display: none;" onchange="this.closest('form').querySelectorAll('[name=override_shadow_intensity]').forEach(r => { r.closest('label').style.borderColor = r.checked ? 'var(--cyan)' : 'var(--border-color)'; });">
                        <div style="width: 32px; height: 24px; background: var(--bg-card); border-radius: 4px; margin: 0 auto 6px; box-shadow: <?= $sOpt['preview'] ?>;"></div>
                        <span style="font-size: 12px; font-weight: 500; color: var(--text-primary);"><?= $sOpt['label'] ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Preview -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header" style="margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color);">
            <h3 class="card-title" style="font-size: 1rem; font-weight: 600;"><i class="fas fa-eye" style="margin-right: 8px; color: var(--cyan);"></i>Live Preview</h3>
        </div>

        <div id="themePreview" style="padding: 24px; border: 1px solid var(--border-color); border-radius: var(--radius-md); background: var(--bg-secondary);">
            <div style="display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 16px;">
                <button type="button" class="btn btn-primary" style="pointer-events: none;">Primary Button</button>
                <button type="button" class="btn btn-secondary" style="pointer-events: none;">Secondary</button>
                <button type="button" class="btn btn-danger" style="pointer-events: none;">Danger</button>
                <button type="button" class="btn" style="pointer-events: none; opacity: 0.5;">Disabled</button>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; margin-bottom: 16px;">
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 16px; box-shadow: var(--card-shadow, none);">
                    <div style="font-size: 13px; font-weight: 600; color: var(--text-primary); margin-bottom: 4px;">Sample Card</div>
                    <div style="font-size: 12px; color: var(--text-secondary);">Card content</div>
                </div>
                <div style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 16px; box-shadow: var(--card-shadow, none);">
                    <div style="font-size: 13px; font-weight: 600; color: var(--text-primary); margin-bottom: 4px;">Another Card</div>
                    <div style="font-size: 12px; color: var(--text-secondary);">More content</div>
                </div>
            </div>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <span class="badge badge-success">Success</span>
                <span class="badge badge-danger">Danger</span>
                <span class="badge badge-warning">Warning</span>
                <span class="badge badge-info">Info</span>
            </div>
        </div>
    </div>

    <!-- Save -->
    <div style="display: flex; justify-content: flex-end; gap: 12px;">
        <a href="/admin/settings" class="btn btn-secondary">Cancel</a>
        <button type="submit" class="btn btn-primary" style="padding: 10px 32px;">
            <i class="fas fa-save" style="margin-right: 6px;"></i> Save Theme Settings
        </button>
    </div>
</form>

<style>
    .theme-card:hover {
        border-color: var(--border-hover) !important;
        box-shadow: var(--shadow-md);
    }
    .theme-card.active {
        box-shadow: 0 0 0 1px var(--cyan);
    }
    /* Color picker styling */
    input[type="color"] {
        -webkit-appearance: none;
        border: none;
        cursor: pointer;
    }
    input[type="color"]::-webkit-color-swatch-wrapper {
        padding: 2px;
    }
    input[type="color"]::-webkit-color-swatch {
        border: none;
        border-radius: 3px;
    }
</style>

<script>
function selectTheme(radio) {
    document.querySelectorAll('.theme-card').forEach(card => {
        card.style.borderColor = 'var(--border-color)';
        card.classList.remove('active');
        const badge = card.querySelector('[style*="Active"]');
        if (badge) badge.remove();
    });
    const card = radio.closest('.theme-card');
    card.style.borderColor = 'var(--cyan)';
    card.classList.add('active');
}

function selectMode(radio) {
    document.querySelectorAll('[name="default_mode"]').forEach(r => {
        r.closest('label').style.borderColor = r.checked ? 'var(--cyan)' : 'var(--border-color)';
    });
}

function resetColors() {
    if (!confirm('Reset all color overrides to theme defaults?')) return;
    document.querySelectorAll('.color-override').forEach(input => {
        input.value = input.dataset.default;
    });
    document.querySelectorAll('.color-text-input').forEach(input => {
        input.value = '';
    });
}

// Sync color picker ↔ text input
document.querySelectorAll('.color-override').forEach(picker => {
    picker.addEventListener('input', function() {
        const textInput = this.closest('div').parentElement.querySelector('.color-text-input');
        if (textInput) textInput.value = this.value;
    });
});

document.querySelectorAll('.color-text-input').forEach(textInput => {
    textInput.addEventListener('input', function() {
        const name = this.dataset.for;
        const picker = document.querySelector(`[name="${name}"]`);
        if (picker && /^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            picker.value = this.value;
        }
    });
});
</script>

<?php View::endSection(); ?>
