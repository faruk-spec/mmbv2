<?php use Core\View; use Core\Helpers; use Core\Security; use Core\VirusScanner; ?>
<?php View::extend('admin'); ?>
<?php View::section('content'); ?>
<div class="admin-content">
    <div class="page-header">
        <h1><i class="fas fa-shield-virus"></i> URL / Virus Scanner</h1>
        <p style="color:var(--text-secondary);">Manually test URLs against the built-in safety checks</p>
    </div>

    <?php if (Helpers::hasFlash('success')): ?>
        <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
    <?php endif; ?>
    <?php if (Helpers::hasFlash('error')): ?>
        <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
    <?php endif; ?>

    <?php if (!empty($scanResult)): ?>
    <div class="card" style="margin-bottom:20px;">
        <div class="card-header">
            <h3>Scan Result</h3>
        </div>
        <div class="card-body" style="padding:20px;">
            <?php if ($scanResult['safe']): ?>
                <div style="display:flex;align-items:center;gap:12px;color:var(--green);">
                    <i class="fas fa-check-circle" style="font-size:2rem;"></i>
                    <div>
                        <strong>URL appears safe</strong>
                        <?php if (!empty($scanResult['reason'])): ?><p style="color:var(--text-secondary);margin:4px 0 0;"><?= View::e($scanResult['reason']) ?></p><?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div style="display:flex;align-items:center;gap:12px;color:var(--red);">
                    <i class="fas fa-exclamation-triangle" style="font-size:2rem;"></i>
                    <div>
                        <strong>URL is UNSAFE or blocked</strong>
                        <p style="margin:4px 0 0;"><?= View::e($scanResult['reason']) ?></p>
                    </div>
                </div>
            <?php endif; ?>
            <p style="margin-top:12px;color:var(--text-secondary);font-size:12px;">Scanned URL: <code><?= View::e($scannedUrl ?? '') ?></code></p>
        </div>
    </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h3>Scan a URL</h3>
        </div>
        <div class="card-body" style="padding:20px;">
            <form method="POST" action="/admin/tools/scanner">
                <?= Security::csrfField() ?>
                <div class="form-group">
                    <label class="form-label">URL to scan</label>
                    <input type="url" name="scan_url" class="form-input" placeholder="https://example.com" required
                           value="<?= View::e($scannedUrl ?? '') ?>">
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Scan URL</button>
            </form>
        </div>
    </div>

    <div class="card" style="margin-top:20px;">
        <div class="card-header">
            <h3>Blocklist Info</h3>
        </div>
        <div class="card-body" style="padding:20px;">
            <p style="color:var(--text-secondary);">The scanner blocks:</p>
            <ul style="color:var(--text-secondary);padding-left:20px;line-height:2;">
                <li>Dangerous URL schemes: <code>javascript:</code>, <code>data:</code>, <code>vbscript:</code>, <code>file:</code></li>
                <li>Private and loopback IP addresses (127.x.x.x, 192.168.x.x, 10.x.x.x, etc.)</li>
                <li>Known malicious domains from the built-in blocklist</li>
                <?php
                $apiKey = defined('VIRUSTOTAL_API_KEY') ? VIRUSTOTAL_API_KEY : '';
                $cfg = is_file(BASE_PATH . '/config/virus_scan.php') ? include BASE_PATH . '/config/virus_scan.php' : [];
                $apiKey = $apiKey ?: ($cfg['virustotal_api_key'] ?? '');
                ?>
                <li>VirusTotal API: <?= !empty($apiKey) ? '<span style="color:var(--green)">Configured</span>' : '<span style="color:var(--orange)">Not configured (set VIRUSTOTAL_API_KEY)</span>' ?></li>
            </ul>
        </div>
    </div>
</div>
<?php View::endSection(); ?>
