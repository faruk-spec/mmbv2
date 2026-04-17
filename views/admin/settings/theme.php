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

    <!-- Use Universal Theme Toggle -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header" style="margin-bottom: 16px; padding-bottom: 12px; border-bottom: 1px solid var(--border-color);">
            <h3 class="card-title" style="font-size: 1rem; font-weight: 600;"><i class="fas fa-toggle-on" style="margin-right: 8px; color: var(--cyan);"></i>Use Universal Theme</h3>
        </div>

        <div style="display: flex; align-items: center; gap: 16px; padding: 16px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 8px;">
            <div style="flex: 1;">
                <p style="font-size: 14px; font-weight: 600; color: var(--text-primary); margin-bottom: 4px;">Enable Universal Theme System</p>
                <p style="font-size: 13px; color: var(--text-secondary); margin: 0;">
                    When enabled, the theme settings below will be applied across all pages.
                    When disabled, the platform uses its original default styles.
                </p>
            </div>
            <div style="display: flex; gap: 8px;">
                <?php $isEnabled = !empty($useUniversalTheme); ?>
                <label style="cursor: pointer; padding: 8px 20px; border: 2px solid <?= $isEnabled ? 'var(--green, #00ff88)' : 'var(--border-color)' ?>; border-radius: 8px; background: <?= $isEnabled ? 'rgba(0,255,136,0.1)' : 'transparent' ?>; color: <?= $isEnabled ? 'var(--green, #00ff88)' : 'var(--text-secondary)' ?>; font-weight: 600; font-size: 14px; transition: all 0.2s;">
                    <input type="radio" name="use_universal_theme" value="yes" <?= $isEnabled ? 'checked' : '' ?> style="display: none;" onchange="toggleUniversalTheme(this)">
                    <i class="fas fa-check-circle" style="margin-right: 4px;"></i> Yes
                </label>
                <label style="cursor: pointer; padding: 8px 20px; border: 2px solid <?= !$isEnabled ? 'var(--red, #ff6b6b)' : 'var(--border-color)' ?>; border-radius: 8px; background: <?= !$isEnabled ? 'rgba(255,107,107,0.1)' : 'transparent' ?>; color: <?= !$isEnabled ? 'var(--red, #ff6b6b)' : 'var(--text-secondary)' ?>; font-weight: 600; font-size: 14px; transition: all 0.2s;">
                    <input type="radio" name="use_universal_theme" value="no" <?= !$isEnabled ? 'checked' : '' ?> style="display: none;" onchange="toggleUniversalTheme(this)">
                    <i class="fas fa-times-circle" style="margin-right: 4px;"></i> No
                </label>
            </div>
        </div>
        <?php if (!$isEnabled): ?>
        <div style="margin-top: 12px; padding: 12px 16px; background: rgba(255,170,0,0.1); border: 1px solid rgba(255,170,0,0.3); border-radius: 8px; color: var(--orange, #ffaa00); font-size: 13px;">
            <i class="fas fa-info-circle" style="margin-right: 6px;"></i>
            Universal Theme is currently <strong>disabled</strong>. The settings below are saved but not applied. Enable it to apply the theme across your platform.
        </div>
        <?php endif; ?>
    </div>

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
                    'dark'  => ['#09090b', '#18181b', '#3b82f6', '#fafafa', '#a1a1aa', 'rgba(255,255,255,0.08)'],
                    'light' => ['#fafafa', '#ffffff', '#2563eb', '#18181b', '#52525b', 'rgba(0,0,0,0.08)'],
                    'radius' => '8px',
                ],
                'soft' => [
                    'name'  => 'Soft',
                    'desc'  => 'Notion-inspired warm neutral design',
                    'icon'  => 'fas fa-feather-alt',
                    'dark'  => ['#1a1a1a', '#2a2a2a', '#6b9fff', '#ececec', '#999999', 'rgba(255,255,255,0.06)'],
                    'light' => ['#f7f6f3', '#ffffff', '#2f7aeb', '#37352f', '#787774', 'rgba(55,53,47,0.09)'],
                    'radius' => '10px',
                ],
                'corporate' => [
                    'name'  => 'Corporate',
                    'desc'  => 'Stripe-inspired sharp enterprise look',
                    'icon'  => 'fas fa-briefcase',
                    'dark'  => ['#0a0e1a', '#1f2937', '#635bff', '#f9fafb', '#9ca3af', 'rgba(255,255,255,0.06)'],
                    'light' => ['#f6f9fc', '#ffffff', '#635bff', '#0a2540', '#425466', 'rgba(10,37,64,0.08)'],
                    'radius' => '6px',
                ],
                'neon' => [
                    'name'  => 'Neon',
                    'desc'  => 'Linear-inspired vibrant developer theme',
                    'icon'  => 'fas fa-bolt',
                    'dark'  => ['#08070b', '#19172a', '#6c63ff', '#f5f3ff', '#a5a0c8', 'rgba(168,85,247,0.10)'],
                    'light' => ['#faf8ff', '#ffffff', '#5b52e5', '#1e1b4b', '#4c4678', 'rgba(91,82,229,0.10)'],
                    'radius' => '10px',
                ],
            ];
            foreach ($themes as $key => $theme):
                $isActive = ($activeTheme ?? 'default') === $key;
                // dark: [bgPage, bgCard, accent, textPrimary, textSecondary, border]
                $d = $theme['dark'];
                $l = $theme['light'];
                $rad = $theme['radius'];
            ?>
            <label class="theme-card <?= $isActive ? 'active' : '' ?>" style="cursor: pointer; display: block; padding: 0; border: 2px solid <?= $isActive ? 'var(--cyan)' : 'var(--border-color)' ?>; border-radius: var(--radius-lg); overflow: hidden; transition: all 0.2s ease; background: var(--bg-card);">
                <input type="radio" name="active_theme" value="<?= $key ?>" <?= $isActive ? 'checked' : '' ?> style="display: none;" onchange="selectTheme(this)">

                <!-- Mini UI Preview -->
                <div style="display: flex; height: 110px; overflow: hidden;">
                    <!-- Dark preview -->
                    <div style="flex: 1; background: <?= $d[0] ?>; padding: 8px; display: flex; flex-direction: column; gap: 5px; border-right: 1px solid <?= $d[5] ?>;">
                        <div style="font-size: 7px; font-weight: 600; color: <?= $d[3] ?>; margin-bottom: 2px;">Dark</div>
                        <!-- Mini card -->
                        <div style="background: <?= $d[1] ?>; border: 1px solid <?= $d[5] ?>; border-radius: <?= $rad ?>; padding: 6px; flex: 1;">
                            <div style="width: 60%; height: 4px; background: <?= $d[3] ?>; border-radius: 2px; margin-bottom: 4px; opacity: 0.9;"></div>
                            <div style="width: 80%; height: 3px; background: <?= $d[4] ?>; border-radius: 2px; margin-bottom: 6px; opacity: 0.5;"></div>
                            <div style="display: flex; gap: 4px;">
                                <div style="height: 12px; padding: 0 6px; background: <?= $d[2] ?>; border-radius: 3px; display: flex; align-items: center;">
                                    <span style="font-size: 5px; color: #fff; font-weight: 600;">Button</span>
                                </div>
                                <div style="height: 12px; padding: 0 6px; background: <?= $d[1] ?>; border: 1px solid <?= $d[5] ?>; border-radius: 3px; display: flex; align-items: center;">
                                    <span style="font-size: 5px; color: <?= $d[4] ?>;">Sec</span>
                                </div>
                            </div>
                        </div>
                        <!-- Mini badges -->
                        <div style="display: flex; gap: 3px;">
                            <span style="font-size: 5px; background: rgba(34,197,94,0.15); color: #22c55e; padding: 1px 4px; border-radius: 6px;">OK</span>
                            <span style="font-size: 5px; background: rgba(239,68,68,0.15); color: #ef4444; padding: 1px 4px; border-radius: 6px;">Err</span>
                            <span style="font-size: 5px; background: <?= $d[2] ?>22; color: <?= $d[2] ?>; padding: 1px 4px; border-radius: 6px;">Info</span>
                        </div>
                    </div>
                    <!-- Light preview -->
                    <div style="flex: 1; background: <?= $l[0] ?>; padding: 8px; display: flex; flex-direction: column; gap: 5px;">
                        <div style="font-size: 7px; font-weight: 600; color: <?= $l[3] ?>; margin-bottom: 2px;">Light</div>
                        <div style="background: <?= $l[1] ?>; border: 1px solid <?= $l[5] ?>; border-radius: <?= $rad ?>; padding: 6px; flex: 1; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
                            <div style="width: 60%; height: 4px; background: <?= $l[3] ?>; border-radius: 2px; margin-bottom: 4px; opacity: 0.9;"></div>
                            <div style="width: 80%; height: 3px; background: <?= $l[4] ?>; border-radius: 2px; margin-bottom: 6px; opacity: 0.5;"></div>
                            <div style="display: flex; gap: 4px;">
                                <div style="height: 12px; padding: 0 6px; background: <?= $l[2] ?>; border-radius: 3px; display: flex; align-items: center;">
                                    <span style="font-size: 5px; color: #fff; font-weight: 600;">Button</span>
                                </div>
                                <div style="height: 12px; padding: 0 6px; background: <?= $l[1] ?>; border: 1px solid <?= $l[5] ?>; border-radius: 3px; display: flex; align-items: center;">
                                    <span style="font-size: 5px; color: <?= $l[4] ?>;">Sec</span>
                                </div>
                            </div>
                        </div>
                        <div style="display: flex; gap: 3px;">
                            <span style="font-size: 5px; background: rgba(34,197,94,0.12); color: #16a34a; padding: 1px 4px; border-radius: 6px;">OK</span>
                            <span style="font-size: 5px; background: rgba(239,68,68,0.12); color: #dc2626; padding: 1px 4px; border-radius: 6px;">Err</span>
                            <span style="font-size: 5px; background: <?= $l[2] ?>15; color: <?= $l[2] ?>; padding: 1px 4px; border-radius: 6px;">Info</span>
                        </div>
                    </div>
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
                    <input type="color" value="<?= htmlspecialchars($currentVal ?: $field['default']) ?>"
                           style="width: 36px; height: 36px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); cursor: pointer; padding: 2px; background: var(--bg-secondary);"
                           data-default="<?= $field['default'] ?>"
                           class="color-override"
                           data-target="override_<?= $key ?>">
                    <input type="text" name="override_<?= $key ?>" value="<?= htmlspecialchars($currentVal) ?>"
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
                        <input type="color" value="<?= htmlspecialchars($currentVal ?: $field['default']) ?>"
                               style="width: 32px; height: 32px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); cursor: pointer; padding: 2px; background: var(--bg-secondary);"
                               data-default="<?= $field['default'] ?>" class="color-override" data-target="override_<?= $key ?>">
                        <input type="text" name="override_<?= $key ?>" value="<?= htmlspecialchars($currentVal) ?>"
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
                        <input type="color" value="<?= htmlspecialchars($currentVal ?: $field['default']) ?>"
                               style="width: 32px; height: 32px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); cursor: pointer; padding: 2px; background: var(--bg-secondary);"
                               data-default="<?= $field['default'] ?>" class="color-override" data-target="override_<?= $key ?>">
                        <input type="text" name="override_<?= $key ?>" value="<?= htmlspecialchars($currentVal) ?>"
                               placeholder="<?= $field['default'] ?>"
                               style="flex: 1; padding: 6px 8px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); font-size: 12px; font-family: monospace;"
                               class="color-text-input" data-for="override_<?= $key ?>">
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Text Color Overrides -->
    <div class="card" style="margin-bottom: 24px;">
        <div class="card-header" style="margin-bottom: 20px; padding-bottom: 16px; border-bottom: 1px solid var(--border-color);">
            <h3 class="card-title" style="font-size: 1rem; font-weight: 600;"><i class="fas fa-font" style="margin-right: 8px; color: var(--cyan);"></i>Text Color Customization</h3>
        </div>

        <p style="font-size: 13px; color: var(--text-secondary); margin-bottom: 20px;">
            Customize text colors for dark and light modes. Leave empty to use theme defaults. This is useful if text is hard to read on your chosen background colors.
        </p>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">
            <!-- Dark text colors -->
            <div>
                <h4 style="font-size: 13px; font-weight: 600; margin-bottom: 12px; color: var(--text-primary);"><i class="fas fa-moon" style="margin-right: 6px;"></i>Dark Mode Text</h4>
                <?php
                $darkTextFields = [
                    'text_primary_dark'   => ['label' => 'Primary Text',    'default' => '#fafafa'],
                    'text_secondary_dark' => ['label' => 'Secondary Text',  'default' => '#a1a1aa'],
                    'text_tertiary_dark'  => ['label' => 'Tertiary / Muted','default' => '#71717a'],
                ];
                foreach ($darkTextFields as $key => $field):
                    $currentVal = $customOverrides[$key] ?? '';
                ?>
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 12px; font-weight: 500; color: var(--text-secondary); margin-bottom: 4px;"><?= $field['label'] ?></label>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="color" value="<?= htmlspecialchars($currentVal ?: $field['default']) ?>"
                               style="width: 32px; height: 32px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); cursor: pointer; padding: 2px; background: var(--bg-secondary);"
                               data-default="<?= $field['default'] ?>" class="color-override" data-target="override_<?= $key ?>">
                        <input type="text" name="override_<?= $key ?>" value="<?= htmlspecialchars($currentVal) ?>"
                               placeholder="<?= $field['default'] ?>"
                               style="flex: 1; padding: 6px 8px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); font-size: 12px; font-family: monospace;"
                               class="color-text-input" data-for="override_<?= $key ?>">
                    </div>
                </div>
                <?php endforeach; ?>
                <!-- Dark mode preview swatch -->
                <div style="margin-top: 8px; padding: 12px; background: #09090b; border: 1px solid rgba(255,255,255,0.08); border-radius: var(--radius-md);">
                    <div class="pv-dark-text-primary" style="font-size: 13px; font-weight: 600; color: #fafafa; margin-bottom: 4px;">Primary text preview</div>
                    <div class="pv-dark-text-secondary" style="font-size: 12px; color: #a1a1aa; margin-bottom: 2px;">Secondary text preview</div>
                    <div class="pv-dark-text-tertiary" style="font-size: 11px; color: #71717a;">Tertiary / muted text preview</div>
                </div>
            </div>

            <!-- Light text colors -->
            <div>
                <h4 style="font-size: 13px; font-weight: 600; margin-bottom: 12px; color: var(--text-primary);"><i class="fas fa-sun" style="margin-right: 6px;"></i>Light Mode Text</h4>
                <?php
                $lightTextFields = [
                    'text_primary_light'   => ['label' => 'Primary Text',    'default' => '#18181b'],
                    'text_secondary_light' => ['label' => 'Secondary Text',  'default' => '#52525b'],
                    'text_tertiary_light'  => ['label' => 'Tertiary / Muted','default' => '#a1a1aa'],
                ];
                foreach ($lightTextFields as $key => $field):
                    $currentVal = $customOverrides[$key] ?? '';
                ?>
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 12px; font-weight: 500; color: var(--text-secondary); margin-bottom: 4px;"><?= $field['label'] ?></label>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <input type="color" value="<?= htmlspecialchars($currentVal ?: $field['default']) ?>"
                               style="width: 32px; height: 32px; border: 1px solid var(--border-color); border-radius: var(--radius-sm); cursor: pointer; padding: 2px; background: var(--bg-secondary);"
                               data-default="<?= $field['default'] ?>" class="color-override" data-target="override_<?= $key ?>">
                        <input type="text" name="override_<?= $key ?>" value="<?= htmlspecialchars($currentVal) ?>"
                               placeholder="<?= $field['default'] ?>"
                               style="flex: 1; padding: 6px 8px; background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: var(--radius-sm); color: var(--text-primary); font-size: 12px; font-family: monospace;"
                               class="color-text-input" data-for="override_<?= $key ?>">
                    </div>
                </div>
                <?php endforeach; ?>
                <!-- Light mode preview swatch -->
                <div style="margin-top: 8px; padding: 12px; background: #fafafa; border: 1px solid rgba(0,0,0,0.08); border-radius: var(--radius-md);">
                    <div class="pv-light-text-primary" style="font-size: 13px; font-weight: 600; color: #18181b; margin-bottom: 4px;">Primary text preview</div>
                    <div class="pv-light-text-secondary" style="font-size: 12px; color: #52525b; margin-bottom: 2px;">Secondary text preview</div>
                    <div class="pv-light-text-tertiary" style="font-size: 11px; color: #a1a1aa;">Tertiary / muted text preview</div>
                </div>
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
            <span class="pv-header-label" style="font-size: 12px; color: var(--text-secondary);"></span>
        </div>

        <div id="themePreview" style="padding: 24px; border: 1px solid var(--border-color); border-radius: var(--radius-md); background: var(--bg-secondary); transition: background 0.3s ease;">
            <div style="display: flex; gap: 16px; flex-wrap: wrap; margin-bottom: 16px;">
                <button type="button" class="btn btn-primary pv-btn-primary" style="pointer-events: none;">Primary Button</button>
                <button type="button" class="btn btn-secondary pv-btn-secondary" style="pointer-events: none; border: 1px solid var(--border-color);">Secondary</button>
                <button type="button" class="btn pv-btn-danger" style="pointer-events: none; border: 1px solid;">Danger</button>
                <button type="button" class="btn" style="pointer-events: none; opacity: 0.5;">Disabled</button>
            </div>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 12px; margin-bottom: 16px;">
                <div class="pv-card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 16px; box-shadow: var(--card-shadow, none); transition: all 0.3s ease;">
                    <div class="pv-card-title" style="font-size: 13px; font-weight: 600; color: var(--text-primary); margin-bottom: 4px;">Sample Card</div>
                    <div class="pv-card-text" style="font-size: 12px; color: var(--text-secondary);">Card content preview</div>
                </div>
                <div class="pv-card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 16px; box-shadow: var(--card-shadow, none); transition: all 0.3s ease;">
                    <div class="pv-card-title" style="font-size: 13px; font-weight: 600; color: var(--text-primary); margin-bottom: 4px;">Another Card</div>
                    <div class="pv-card-text" style="font-size: 12px; color: var(--text-secondary);">More content here</div>
                </div>
                <div class="pv-card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: var(--radius-lg); padding: 16px; box-shadow: var(--card-shadow, none); transition: all 0.3s ease;">
                    <div class="pv-card-title" style="font-size: 13px; font-weight: 600; color: var(--text-primary); margin-bottom: 4px;">Third Card</div>
                    <div class="pv-card-text" style="font-size: 12px; color: var(--text-secondary);">Additional item</div>
                </div>
            </div>
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <span class="badge pv-badge-success" style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:500;">Success</span>
                <span class="badge pv-badge-danger" style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:500;">Danger</span>
                <span class="badge pv-badge-warning" style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:500;">Warning</span>
                <span class="badge pv-badge-info" style="display:inline-flex;align-items:center;padding:2px 8px;border-radius:9999px;font-size:11px;font-weight:500;">Info</span>
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
function toggleUniversalTheme(radio) {
    document.querySelectorAll('[name="use_universal_theme"]').forEach(r => {
        const lbl = r.closest('label');
        if (r.value === 'yes') {
            lbl.style.borderColor = r.checked ? 'var(--green, #00ff88)' : 'var(--border-color)';
            lbl.style.background = r.checked ? 'rgba(0,255,136,0.1)' : 'transparent';
            lbl.style.color = r.checked ? 'var(--green, #00ff88)' : 'var(--text-secondary)';
        } else {
            lbl.style.borderColor = r.checked ? 'var(--red, #ff6b6b)' : 'var(--border-color)';
            lbl.style.background = r.checked ? 'rgba(255,107,107,0.1)' : 'transparent';
            lbl.style.color = r.checked ? 'var(--red, #ff6b6b)' : 'var(--text-secondary)';
        }
    });
}

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
    updatePreview();
}

function selectMode(radio) {
    document.querySelectorAll('[name="default_mode"]').forEach(r => {
        r.closest('label').style.borderColor = r.checked ? 'var(--cyan)' : 'var(--border-color)';
    });
    updatePreview();
}

function resetColors() {
    if (!confirm('Reset all color overrides to theme defaults?')) return;
    document.querySelectorAll('.color-override').forEach(input => {
        input.value = input.dataset.default;
    });
    document.querySelectorAll('.color-text-input').forEach(input => {
        input.value = '';
    });
    updatePreview();
}

// Sync color picker ↔ text input
document.querySelectorAll('.color-override').forEach(picker => {
    picker.addEventListener('input', function() {
        const targetName = this.dataset.target;
        const textInput = document.querySelector(`input[name="${targetName}"]`);
        if (textInput) textInput.value = this.value;
        updatePreview();
    });
});

document.querySelectorAll('.color-text-input').forEach(textInput => {
    textInput.addEventListener('input', function() {
        const targetName = this.getAttribute('name');
        const picker = document.querySelector(`.color-override[data-target="${targetName}"]`);
        if (picker && /^#[0-9A-Fa-f]{6}$/.test(this.value)) {
            picker.value = this.value;
        }
        updatePreview();
    });
});

// Radius and shadow radio buttons
document.querySelectorAll('[name="override_radius_level"], [name="override_shadow_intensity"]').forEach(radio => {
    radio.addEventListener('change', function() {
        updatePreview();
    });
});

/* ─── Live Preview Engine ─── */
const previewEl = document.getElementById('themePreview');

// Theme definitions for preview
const themeTokens = {
    default: {
        dark:  { bgPrimary: '#09090b', bgSecondary: '#111113', bgCard: '#18181b', cyan: '#3b82f6', magenta: '#8b5cf6', green: '#22c55e', orange: '#f59e0b', red: '#ef4444', purple: '#8b5cf6', textPrimary: '#fafafa', textSecondary: '#a1a1aa', border: 'rgba(255,255,255,0.08)', cardShadow: 'none', radius: '8px' },
        light: { bgPrimary: '#fafafa', bgSecondary: '#ffffff', bgCard: '#ffffff', cyan: '#2563eb', magenta: '#7c3aed', green: '#16a34a', orange: '#d97706', red: '#dc2626', purple: '#7c3aed', textPrimary: '#18181b', textSecondary: '#52525b', border: 'rgba(0,0,0,0.08)', cardShadow: '0 1px 3px rgba(0,0,0,0.08), 0 1px 2px rgba(0,0,0,0.06)', radius: '8px' }
    },
    soft: {
        dark:  { bgPrimary: '#1a1a1a', bgSecondary: '#222222', bgCard: '#2a2a2a', cyan: '#6b9fff', magenta: '#b197fc', green: '#69db7c', orange: '#ffd43b', red: '#ff6b6b', purple: '#b197fc', textPrimary: '#ececec', textSecondary: '#999999', border: 'rgba(255,255,255,0.06)', cardShadow: 'none', radius: '10px' },
        light: { bgPrimary: '#f7f6f3', bgSecondary: '#ffffff', bgCard: '#ffffff', cyan: '#2f7aeb', magenta: '#7048c6', green: '#2b9348', orange: '#cc8a00', red: '#cc3333', purple: '#7048c6', textPrimary: '#37352f', textSecondary: '#787774', border: 'rgba(55,53,47,0.09)', cardShadow: '0 1px 3px rgba(55,53,47,0.06), 0 0 0 1px rgba(55,53,47,0.04)', radius: '10px' }
    },
    corporate: {
        dark:  { bgPrimary: '#0a0e1a', bgSecondary: '#111827', bgCard: '#1f2937', cyan: '#635bff', magenta: '#a78bfa', green: '#10b981', orange: '#f97316', red: '#ef4444', purple: '#a78bfa', textPrimary: '#f9fafb', textSecondary: '#9ca3af', border: 'rgba(255,255,255,0.06)', cardShadow: 'none', radius: '6px' },
        light: { bgPrimary: '#f6f9fc', bgSecondary: '#ffffff', bgCard: '#ffffff', cyan: '#635bff', magenta: '#7c3aed', green: '#0ea170', orange: '#d97706', red: '#dc2626', purple: '#7c3aed', textPrimary: '#0a2540', textSecondary: '#425466', border: 'rgba(10,37,64,0.08)', cardShadow: '0 1px 3px rgba(10,37,64,0.06), 0 0 0 1px rgba(10,37,64,0.04)', radius: '6px' }
    },
    neon: {
        dark:  { bgPrimary: '#08070b', bgSecondary: '#110f1a', bgCard: '#19172a', cyan: '#6c63ff', magenta: '#e879f9', green: '#34d399', orange: '#fbbf24', red: '#f43f5e', purple: '#a855f7', textPrimary: '#f5f3ff', textSecondary: '#a5a0c8', border: 'rgba(168,85,247,0.10)', cardShadow: 'none', radius: '10px' },
        light: { bgPrimary: '#faf8ff', bgSecondary: '#ffffff', bgCard: '#ffffff', cyan: '#5b52e5', magenta: '#c026d3', green: '#16a34a', orange: '#d97706', red: '#e11d48', purple: '#9333ea', textPrimary: '#1e1b4b', textSecondary: '#4c4678', border: 'rgba(91,82,229,0.10)', cardShadow: '0 1px 3px rgba(91,82,229,0.06), 0 0 0 1px rgba(91,82,229,0.04)', radius: '10px' }
    }
};

const radiusMap = { sharp: { sm: '4px', md: '6px', lg: '8px' }, medium: { sm: '6px', md: '8px', lg: '12px' }, rounded: { sm: '8px', md: '10px', lg: '16px' } };
const shadowMap = {
    none:   { sm: 'none', md: 'none', lg: 'none', card: 'none' },
    subtle: { sm: '0 1px 2px rgba(0,0,0,0.04)', md: '0 2px 6px rgba(0,0,0,0.06)', lg: '0 4px 12px rgba(0,0,0,0.08)', card: '0 1px 2px rgba(0,0,0,0.04)' },
    normal: { sm: '0 1px 3px rgba(0,0,0,0.06)', md: '0 4px 12px rgba(0,0,0,0.08)', lg: '0 8px 24px rgba(0,0,0,0.12)', card: '0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04)' },
    strong: { sm: '0 2px 4px rgba(0,0,0,0.08)', md: '0 6px 18px rgba(0,0,0,0.14)', lg: '0 12px 36px rgba(0,0,0,0.20)', card: '0 2px 6px rgba(0,0,0,0.10), 0 1px 3px rgba(0,0,0,0.06)' }
};

// Helper: get color override value (text inputs take precedence, fallback to theme default)
function getOv(key, fallback) {
    const textEl = document.querySelector(`.color-text-input[data-for="override_${key}"]`);
    return (textEl && textEl.value && /^#[0-9A-Fa-f]{6}$/.test(textEl.value)) ? textEl.value : fallback;
}

function updatePreview() {
    if (!previewEl) return;
    // Gather current form state
    const selectedTheme = document.querySelector('[name="active_theme"]:checked')?.value || 'default';
    const selectedMode = document.querySelector('[name="default_mode"]:checked')?.value || 'dark';
    const tokens = themeTokens[selectedTheme]?.[selectedMode] || themeTokens.default.dark;

    const cyan    = getOv('cyan', tokens.cyan);
    const magenta = getOv('magenta', tokens.magenta);
    const green   = getOv('green', tokens.green);
    const orange  = getOv('orange', tokens.orange);
    const red     = getOv('red', tokens.red);
    const purple  = getOv('purple', tokens.purple);

    // BG overrides
    const bgKey = selectedMode === 'light' ? 'light' : 'dark';
    const bgPrimary   = getOv('bg_primary_' + bgKey, tokens.bgPrimary);
    const bgSecondary = getOv('bg_secondary_' + bgKey, tokens.bgSecondary);
    const bgCard      = getOv('bg_card_' + bgKey, tokens.bgCard);

    // Text color overrides
    const textPrimary   = getOv('text_primary_' + bgKey, tokens.textPrimary);
    const textSecondary = getOv('text_secondary_' + bgKey, tokens.textSecondary);
    const textTertiary  = getOv('text_tertiary_' + bgKey, selectedMode === 'light' ? '#a1a1aa' : '#71717a');

    // Radius & shadow
    const radiusLevel = document.querySelector('[name="override_radius_level"]:checked')?.value || '';
    const shadowLevel = document.querySelector('[name="override_shadow_intensity"]:checked')?.value || '';
    const rad = radiusMap[radiusLevel] || { sm: tokens.radius, md: tokens.radius, lg: (parseInt(tokens.radius) + 4) + 'px' };
    const shd = shadowMap[shadowLevel] || { sm: 'none', md: 'none', lg: 'none', card: tokens.cardShadow };

    // Apply to preview container
    previewEl.style.background = bgSecondary;
    previewEl.style.borderColor = tokens.border;

    // Update CSS custom properties on preview
    previewEl.style.setProperty('--p-bg-primary', bgPrimary);
    previewEl.style.setProperty('--p-bg-secondary', bgSecondary);
    previewEl.style.setProperty('--p-bg-card', bgCard);
    previewEl.style.setProperty('--p-cyan', cyan);
    previewEl.style.setProperty('--p-magenta', magenta);
    previewEl.style.setProperty('--p-green', green);
    previewEl.style.setProperty('--p-orange', orange);
    previewEl.style.setProperty('--p-red', red);
    previewEl.style.setProperty('--p-purple', purple);
    previewEl.style.setProperty('--p-text-primary', textPrimary);
    previewEl.style.setProperty('--p-text-secondary', textSecondary);
    previewEl.style.setProperty('--p-text-tertiary', textTertiary);
    previewEl.style.setProperty('--p-border', tokens.border);
    previewEl.style.setProperty('--p-radius', rad.lg);
    previewEl.style.setProperty('--p-card-shadow', shd.card);

    // Update inline elements in preview
    previewEl.querySelectorAll('.pv-btn-primary').forEach(el => {
        el.style.background = cyan; el.style.color = '#fff';
    });
    previewEl.querySelectorAll('.pv-btn-secondary').forEach(el => {
        el.style.background = bgCard; el.style.color = textPrimary; el.style.borderColor = tokens.border;
    });
    previewEl.querySelectorAll('.pv-btn-danger').forEach(el => {
        el.style.background = red + '1a'; el.style.color = red; el.style.borderColor = red + '40';
    });
    previewEl.querySelectorAll('.pv-card').forEach(el => {
        el.style.background = bgCard; el.style.borderColor = tokens.border;
        el.style.borderRadius = rad.lg; el.style.boxShadow = shd.card;
    });
    previewEl.querySelectorAll('.pv-card-title').forEach(el => {
        el.style.color = textPrimary;
    });
    previewEl.querySelectorAll('.pv-card-text').forEach(el => {
        el.style.color = textSecondary;
    });
    previewEl.querySelectorAll('.pv-badge-success').forEach(el => {
        el.style.background = green + '1f'; el.style.color = green;
    });
    previewEl.querySelectorAll('.pv-badge-danger').forEach(el => {
        el.style.background = red + '1f'; el.style.color = red;
    });
    previewEl.querySelectorAll('.pv-badge-warning').forEach(el => {
        el.style.background = orange + '1f'; el.style.color = orange;
    });
    previewEl.querySelectorAll('.pv-badge-info').forEach(el => {
        el.style.background = cyan + '1f'; el.style.color = cyan;
    });

    // Update the preview header text
    const modeLabel = selectedMode === 'light' ? 'Light' : 'Dark';
    const themeName = selectedTheme.charAt(0).toUpperCase() + selectedTheme.slice(1);
    const headerEl = previewEl.querySelector('.pv-header-label');
    if (headerEl) headerEl.textContent = themeName + ' – ' + modeLabel + ' Mode';

    // Update text color preview swatches
    document.querySelectorAll('.pv-dark-text-primary').forEach(el => {
        el.style.color = getOv('text_primary_dark', themeTokens[selectedTheme]?.dark?.textPrimary || '#fafafa');
    });
    document.querySelectorAll('.pv-dark-text-secondary').forEach(el => {
        el.style.color = getOv('text_secondary_dark', themeTokens[selectedTheme]?.dark?.textSecondary || '#a1a1aa');
    });
    document.querySelectorAll('.pv-dark-text-tertiary').forEach(el => {
        el.style.color = getOv('text_tertiary_dark', '#71717a');
    });
    document.querySelectorAll('.pv-light-text-primary').forEach(el => {
        el.style.color = getOv('text_primary_light', themeTokens[selectedTheme]?.light?.textPrimary || '#18181b');
    });
    document.querySelectorAll('.pv-light-text-secondary').forEach(el => {
        el.style.color = getOv('text_secondary_light', themeTokens[selectedTheme]?.light?.textSecondary || '#52525b');
    });
    document.querySelectorAll('.pv-light-text-tertiary').forEach(el => {
        el.style.color = getOv('text_tertiary_light', '#a1a1aa');
    });
}

// Run initial preview update
document.addEventListener('DOMContentLoaded', updatePreview);
</script>

<?php View::endSection(); ?>
