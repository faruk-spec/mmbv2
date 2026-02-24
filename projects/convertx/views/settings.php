<?php
/**
 * ConvertX – General Settings View
 */
$currentView = 'settings';
$csrfToken   = \Core\Security::generateCsrfToken();
$prefs       = $prefs ?? [];
$user        = $user  ?? \Core\Auth::user();
?>

<!-- Page header -->
<div class="page-header">
    <h1>Settings</h1>
    <p>Manage your account preferences and conversion defaults</p>
</div>

<form method="POST" action="/projects/convertx/settings" style="display:contents;">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <input type="hidden" name="action"  value="save_settings">

    <!-- Account info (read-only) -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-circle-user"></i> Account
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:1.25rem;">
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">
                    <i class="fa-solid fa-user" style="color:var(--cx-primary);"></i> Username
                </label>
                <input type="text" class="form-control"
                       value="<?= htmlspecialchars($user['username'] ?? $user['name'] ?? 'User') ?>"
                       readonly style="opacity:.7;cursor:default;">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">
                    <i class="fa-solid fa-envelope" style="color:var(--cx-primary);"></i> Email
                </label>
                <input type="email" class="form-control"
                       value="<?= htmlspecialchars($user['email'] ?? '') ?>"
                       readonly style="opacity:.7;cursor:default;">
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">
                    <i class="fa-solid fa-crown" style="color:var(--cx-warning);"></i> Current Plan
                </label>
                <div style="display:flex;align-items:center;gap:.5rem;height:2.625rem;">
                    <span class="ai-badge" style="background:linear-gradient(135deg,var(--cx-primary),var(--cx-secondary));">
                        <i class="fa-solid fa-star"></i>
                        <?= htmlspecialchars(ucfirst($user['plan_slug'] ?? 'Free')) ?>
                    </span>
                    <a href="/projects/convertx/plan" style="font-size:.78rem;color:var(--cx-primary);text-decoration:none;">
                        Upgrade <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Conversion defaults -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-sliders"></i> Conversion Defaults
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:1.5rem;">

            <!-- Default image quality -->
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">
                    <i class="fa-solid fa-image" style="color:var(--cx-primary);"></i>
                    Default Image Quality: <strong id="qualityDisplay"><?= (int)($prefs['default_quality'] ?? 85) ?></strong>%
                </label>
                <input type="range" name="default_quality" id="qualitySlider"
                       min="10" max="100" value="<?= (int)($prefs['default_quality'] ?? 85) ?>"
                       style="width:100%;accent-color:var(--cx-primary);margin-top:.25rem;"
                       oninput="document.getElementById('qualityDisplay').textContent=this.value">
                <div style="display:flex;justify-content:space-between;font-size:.72rem;color:var(--text-muted);margin-top:.2rem;">
                    <span>10% — Small</span><span>85% — Balanced</span><span>100% — Max</span>
                </div>
            </div>

            <!-- Default DPI -->
            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">
                    <i class="fa-solid fa-expand" style="color:var(--cx-primary);"></i> Default DPI / Resolution
                </label>
                <select class="form-control" name="default_dpi">
                    <?php foreach ([72 => '72 DPI — Screen / web', 96 => '96 DPI — Standard', 150 => '150 DPI — Good quality', 300 => '300 DPI — Print quality', 600 => '600 DPI — High-res print'] as $v => $lbl): ?>
                    <option value="<?= $v ?>" <?= ((int)($prefs['default_dpi'] ?? 150) === $v) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lbl) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>
    </div>

    <!-- Notifications -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-bell"></i> Notifications
        </div>

        <label class="cx-ai-option" style="max-width:400px;">
            <input type="checkbox" name="notify_on_complete" value="1"
                   <?= !empty($prefs['notify_on_complete']) ? 'checked' : '' ?>>
            <i class="fa-solid fa-envelope-circle-check" style="color:var(--cx-primary);"></i>
            <div>
                <div style="font-weight:600;font-size:.875rem;color:var(--text-primary);">Email on job complete</div>
                <div style="font-size:.78rem;color:var(--text-secondary);">Receive an email when each conversion finishes</div>
            </div>
        </label>
    </div>

    <!-- Save button -->
    <div style="display:flex;gap:.75rem;align-items:center;padding-bottom:2rem;">
        <button type="submit" class="btn btn-primary">
            <i class="fa-solid fa-floppy-disk"></i> Save Settings
        </button>
        <a href="/projects/convertx/apikeys" class="btn btn-secondary">
            <i class="fa-solid fa-key"></i> Manage API Keys
        </a>
    </div>

</form>
