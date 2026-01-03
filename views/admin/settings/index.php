<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h1>Site Settings</h1>
        <p style="color: var(--text-secondary);">Configure platform settings</p>
    </div>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div class="grid grid-3">
    <div style="grid-column: span 2;">
        <div class="card">
            <form method="POST" action="/admin/settings">
                <?= \Core\Security::csrfField() ?>
                
                <div class="form-group">
                    <label class="form-label">Site Name</label>
                    <input type="text" name="site_name" class="form-input" 
                           value="<?= View::e($settings['site_name'] ?? APP_NAME) ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Site Description</label>
                    <textarea name="site_description" class="form-input" rows="3"><?= View::e($settings['site_description'] ?? '') ?></textarea>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Contact Email</label>
                    <input type="email" name="contact_email" class="form-input" 
                           value="<?= View::e($settings['contact_email'] ?? '') ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">System Timezone</label>
                    <select name="system_timezone" class="form-input">
                        <?php
                        $currentTimezone = $settings['system_timezone'] ?? 'UTC';
                        $timezones = [
                            'UTC' => 'UTC (Coordinated Universal Time)',
                            'America/New_York' => 'America/New York (EST/EDT)',
                            'America/Chicago' => 'America/Chicago (CST/CDT)',
                            'America/Denver' => 'America/Denver (MST/MDT)',
                            'America/Los_Angeles' => 'America/Los Angeles (PST/PDT)',
                            'America/Toronto' => 'America/Toronto',
                            'America/Mexico_City' => 'America/Mexico City',
                            'America/Sao_Paulo' => 'America/São Paulo',
                            'Europe/London' => 'Europe/London (GMT/BST)',
                            'Europe/Paris' => 'Europe/Paris (CET/CEST)',
                            'Europe/Berlin' => 'Europe/Berlin (CET/CEST)',
                            'Europe/Rome' => 'Europe/Rome (CET/CEST)',
                            'Europe/Madrid' => 'Europe/Madrid (CET/CEST)',
                            'Europe/Amsterdam' => 'Europe/Amsterdam (CET/CEST)',
                            'Europe/Brussels' => 'Europe/Brussels (CET/CEST)',
                            'Europe/Vienna' => 'Europe/Vienna (CET/CEST)',
                            'Europe/Stockholm' => 'Europe/Stockholm (CET/CEST)',
                            'Europe/Warsaw' => 'Europe/Warsaw (CET/CEST)',
                            'Europe/Athens' => 'Europe/Athens (EET/EEST)',
                            'Europe/Istanbul' => 'Europe/Istanbul (TRT)',
                            'Europe/Moscow' => 'Europe/Moscow (MSK)',
                            'Africa/Cairo' => 'Africa/Cairo (EET)',
                            'Africa/Johannesburg' => 'Africa/Johannesburg (SAST)',
                            'Asia/Dubai' => 'Asia/Dubai (GST)',
                            'Asia/Kolkata' => 'Asia/Kolkata (IST)',
                            'Asia/Bangkok' => 'Asia/Bangkok (ICT)',
                            'Asia/Singapore' => 'Asia/Singapore (SGT)',
                            'Asia/Hong_Kong' => 'Asia/Hong Kong (HKT)',
                            'Asia/Shanghai' => 'Asia/Shanghai (CST)',
                            'Asia/Tokyo' => 'Asia/Tokyo (JST)',
                            'Asia/Seoul' => 'Asia/Seoul (KST)',
                            'Australia/Sydney' => 'Australia/Sydney (AEDT/AEST)',
                            'Australia/Melbourne' => 'Australia/Melbourne (AEDT/AEST)',
                            'Australia/Perth' => 'Australia/Perth (AWST)',
                            'Pacific/Auckland' => 'Pacific/Auckland (NZDT/NZST)',
                        ];
                        foreach ($timezones as $tz => $label):
                        ?>
                            <option value="<?= $tz ?>" <?= $currentTimezone === $tz ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-help">Current server time: <?= date('Y-m-d H:i:s T') ?></small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Date Format</label>
                    <select name="date_format" class="form-input">
                        <?php
                        $currentFormat = $settings['date_format'] ?? 'M d, Y';
                        $formats = [
                            'M d, Y' => date('M d, Y') . ' (M d, Y)',
                            'Y-m-d' => date('Y-m-d') . ' (Y-m-d)',
                            'd/m/Y' => date('d/m/Y') . ' (d/m/Y)',
                            'm/d/Y' => date('m/d/Y') . ' (m/d/Y)',
                            'F j, Y' => date('F j, Y') . ' (F j, Y)',
                            'd-M-Y' => date('d-M-Y') . ' (d-M-Y)',
                        ];
                        foreach ($formats as $format => $label):
                        ?>
                            <option value="<?= $format ?>" <?= $currentFormat === $format ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Time Format</label>
                    <select name="time_format" class="form-input">
                        <?php
                        $currentTimeFormat = $settings['time_format'] ?? 'g:i A';
                        $timeFormats = [
                            'g:i A' => date('g:i A') . ' (12-hour with AM/PM)',
                            'H:i' => date('H:i') . ' (24-hour)',
                            'h:i:s A' => date('h:i:s A') . ' (12-hour with seconds)',
                            'H:i:s' => date('H:i:s') . ' (24-hour with seconds)',
                        ];
                        foreach ($timeFormats as $format => $label):
                        ?>
                            <option value="<?= $format ?>" <?= $currentTimeFormat === $format ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-checkbox">
                        <input type="checkbox" name="registration_enabled" value="1" 
                               <?= ($settings['registration_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <span>Enable User Registration</span>
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </form>
        </div>
    </div>
    
    <div>
        <div class="card">
            <h4 style="margin-bottom: 15px;">Quick Actions</h4>
            
            <div style="display: flex; flex-direction: column; gap: 15px;">
                <a href="/admin/settings/maintenance" class="btn btn-secondary" style="width: 100%;">
                    <i class="fas fa-tools"></i> Maintenance Mode
                </a>
            </div>
        </div>
        
        <div class="card mt-2">
            <h4 style="margin-bottom: 15px;">System Info</h4>
            
            <div style="font-size: 14px;">
                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="color: var(--text-secondary);">PHP Version</span>
                    <span><?= PHP_VERSION ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border-color);">
                    <span style="color: var(--text-secondary);">Platform Version</span>
                    <span><?= APP_VERSION ?></span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 8px 0;">
                    <span style="color: var(--text-secondary);">Server</span>
                    <span><?= $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown' ?></span>
                </div>
            </div>
        </div>
    </div>
</div>
<?php View::endSection(); ?>
