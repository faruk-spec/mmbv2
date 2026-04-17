<?php
/**
 * Universal Theme Override Partial
 * 
 * Include this file in layout <head> sections to apply Universal Theme CSS overrides
 * when "Use Universal Theme" is enabled in Admin → Settings → Universal Theme.
 *
 * When disabled, this outputs nothing (zero impact on the page).
 */

try {
    $__utConfig = \Controllers\Admin\ThemeController::loadThemeForLayout();
} catch (\Throwable $e) {
    $__utConfig = ['enabled' => false];
}

if (!empty($__utConfig['enabled'])):
    // Theme token definitions matching the admin panel exactly
    $__themeTokens = [
        'default' => [
            'dark'  => ['--bg-primary' => '#09090b', '--bg-secondary' => '#111113', '--bg-card' => '#18181b', '--cyan' => '#3b82f6', '--magenta' => '#8b5cf6', '--green' => '#22c55e', '--orange' => '#f59e0b', '--red' => '#ef4444', '--purple' => '#8b5cf6', '--text-primary' => '#fafafa', '--text-secondary' => '#a1a1aa', '--border-color' => 'rgba(255,255,255,0.08)'],
            'light' => ['--bg-primary' => '#fafafa', '--bg-secondary' => '#ffffff', '--bg-card' => '#ffffff', '--cyan' => '#2563eb', '--magenta' => '#7c3aed', '--green' => '#16a34a', '--orange' => '#d97706', '--red' => '#dc2626', '--purple' => '#7c3aed', '--text-primary' => '#18181b', '--text-secondary' => '#52525b', '--border-color' => 'rgba(0,0,0,0.08)'],
        ],
        'soft' => [
            'dark'  => ['--bg-primary' => '#1a1a1a', '--bg-secondary' => '#222222', '--bg-card' => '#2a2a2a', '--cyan' => '#6b9fff', '--magenta' => '#b197fc', '--green' => '#69db7c', '--orange' => '#ffd43b', '--red' => '#ff6b6b', '--purple' => '#b197fc', '--text-primary' => '#ececec', '--text-secondary' => '#999999', '--border-color' => 'rgba(255,255,255,0.06)'],
            'light' => ['--bg-primary' => '#f7f6f3', '--bg-secondary' => '#ffffff', '--bg-card' => '#ffffff', '--cyan' => '#2f7aeb', '--magenta' => '#7048c6', '--green' => '#2b9348', '--orange' => '#cc8a00', '--red' => '#cc3333', '--purple' => '#7048c6', '--text-primary' => '#37352f', '--text-secondary' => '#787774', '--border-color' => 'rgba(55,53,47,0.09)'],
        ],
        'corporate' => [
            'dark'  => ['--bg-primary' => '#0a0e1a', '--bg-secondary' => '#111827', '--bg-card' => '#1f2937', '--cyan' => '#635bff', '--magenta' => '#a78bfa', '--green' => '#10b981', '--orange' => '#f97316', '--red' => '#ef4444', '--purple' => '#a78bfa', '--text-primary' => '#f9fafb', '--text-secondary' => '#9ca3af', '--border-color' => 'rgba(255,255,255,0.06)'],
            'light' => ['--bg-primary' => '#f6f9fc', '--bg-secondary' => '#ffffff', '--bg-card' => '#ffffff', '--cyan' => '#635bff', '--magenta' => '#7c3aed', '--green' => '#0ea170', '--orange' => '#d97706', '--red' => '#dc2626', '--purple' => '#7c3aed', '--text-primary' => '#0a2540', '--text-secondary' => '#425466', '--border-color' => 'rgba(10,37,64,0.08)'],
        ],
        'neon' => [
            'dark'  => ['--bg-primary' => '#08070b', '--bg-secondary' => '#110f1a', '--bg-card' => '#19172a', '--cyan' => '#6c63ff', '--magenta' => '#e879f9', '--green' => '#34d399', '--orange' => '#fbbf24', '--red' => '#f43f5e', '--purple' => '#a855f7', '--text-primary' => '#f5f3ff', '--text-secondary' => '#a5a0c8', '--border-color' => 'rgba(168,85,247,0.10)'],
            'light' => ['--bg-primary' => '#faf8ff', '--bg-secondary' => '#ffffff', '--bg-card' => '#ffffff', '--cyan' => '#5b52e5', '--magenta' => '#c026d3', '--green' => '#16a34a', '--orange' => '#d97706', '--red' => '#e11d48', '--purple' => '#9333ea', '--text-primary' => '#1e1b4b', '--text-secondary' => '#4c4678', '--border-color' => 'rgba(91,82,229,0.10)'],
        ],
    ];

    $__themeName = $__utConfig['theme'] ?? 'default';
    $__themeMode = $__utConfig['mode'] ?? 'dark';
    $__overrides = $__utConfig['overrides'] ?? [];
    $__san = '\\Controllers\\Admin\\ThemeController::sanitizeCssValue';

    // Start with base tokens for the selected theme + mode
    $__baseTokens = $__themeTokens[$__themeName][$__themeMode]
                    ?? $__themeTokens['default']['dark'];

    // Apply custom color overrides from the admin panel
    $__colorMap = [
        'cyan' => '--cyan', 'magenta' => '--magenta', 'green' => '--green',
        'orange' => '--orange', 'purple' => '--purple', 'red' => '--red',
    ];
    foreach ($__colorMap as $ovKey => $cssVar) {
        if (!empty($__overrides[$ovKey])) {
            $sanitized = $__san($__overrides[$ovKey]);
            if ($sanitized !== '') {
                $__baseTokens[$cssVar] = $sanitized;
            }
        }
    }

    // Apply background overrides for current mode
    $__bgSuffix = $__themeMode === 'light' ? '_light' : '_dark';
    $__bgMap = [
        'bg_primary' . $__bgSuffix  => '--bg-primary',
        'bg_secondary' . $__bgSuffix => '--bg-secondary',
        'bg_card' . $__bgSuffix     => '--bg-card',
    ];
    foreach ($__bgMap as $ovKey => $cssVar) {
        if (!empty($__overrides[$ovKey])) {
            $sanitized = $__san($__overrides[$ovKey]);
            if ($sanitized !== '') {
                $__baseTokens[$cssVar] = $sanitized;
            }
        }
    }

    // Apply text color overrides for current mode
    $__textMap = [
        'text_primary' . $__bgSuffix   => '--text-primary',
        'text_secondary' . $__bgSuffix  => '--text-secondary',
        'text_tertiary' . $__bgSuffix   => '--text-tertiary',
    ];
    foreach ($__textMap as $ovKey => $cssVar) {
        if (!empty($__overrides[$ovKey])) {
            $sanitized = $__san($__overrides[$ovKey]);
            if ($sanitized !== '') {
                $__baseTokens[$cssVar] = $sanitized;
            }
        }
    }

    // Also set card-inner-bg to match bg-card
    if (isset($__baseTokens['--bg-card'])) {
        $__baseTokens['--card-inner-bg'] = $__baseTokens['--bg-card'];
    }

    // Build CSS string
    $__cssProps = [];
    foreach ($__baseTokens as $prop => $val) {
        $__cssProps[] = $prop . ': ' . $val;
    }
    $__cssStr = implode('; ', $__cssProps);
?>
<!-- Universal Theme Override (enabled) -->
<style id="universal-theme-override">
:root, :root[data-theme="dark"], :root[data-theme="light"], [data-theme="dark"], [data-theme="light"] {
    <?= $__cssStr ?>;
}
<?php if ($__themeMode === 'light'): ?>
body { background: <?= htmlspecialchars($__baseTokens['--bg-primary'] ?? '#fafafa', ENT_QUOTES) ?>; }
<?php else: ?>
body { background: <?= htmlspecialchars($__baseTokens['--bg-primary'] ?? '#09090b', ENT_QUOTES) ?>; }
<?php endif; ?>
</style>
<script>
// Override data-theme attribute to match Universal Theme setting
document.documentElement.setAttribute('data-theme', <?= json_encode($__themeMode) ?>);
</script>
<?php
endif; // enabled check
// Cleanup temp variables
unset($__utConfig, $__themeTokens, $__themeName, $__themeMode, $__overrides, $__san, $__baseTokens, $__colorMap, $__bgSuffix, $__bgMap, $__textMap, $__cssProps, $__cssStr);
?>
