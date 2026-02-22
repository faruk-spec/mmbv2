<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<!-- Overview Stats -->
<div class="grid grid-3 mb-3">
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:var(--cyan);"><?= number_format($overallStats['total_scans'] ?? 0) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Total Scans</div>
    </div>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:var(--green);"><?= number_format($overallStats['unique_qr_scanned'] ?? 0) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Unique QR Codes Scanned</div>
    </div>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
        <div style="font-size:2rem;font-weight:700;color:var(--magenta);"><?= number_format($overallStats['unique_visitors'] ?? 0) ?></div>
        <div style="color:var(--text-secondary);font-size:13px;">Unique Visitors</div>
    </div>
</div>

<div class="grid grid-2 mb-3">
    <!-- Daily Scans Chart -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-chart-line"></i> Daily Scans (Last 30 Days)</h3>
        </div>
        <?php if (empty($dailyScans)): ?>
            <p style="color:var(--text-secondary);text-align:center;padding:30px;">No scan data available.</p>
        <?php else: ?>
            <div style="padding:20px 10px 40px;">
                <?php
                $maxScans = max(array_column($dailyScans, 'scans')) ?: 1;
                ?>
                <div style="display:flex;align-items:flex-end;gap:4px;height:160px;">
                    <?php foreach ($dailyScans as $day): ?>
                        <?php $h = max(4, (int)(($day['scans'] / $maxScans) * 140)); ?>
                        <div style="flex:1;background:linear-gradient(180deg,var(--cyan),var(--magenta));border-radius:3px 3px 0 0;height:<?= $h ?>px;position:relative;" title="<?= $day['date'] ?>: <?= $day['scans'] ?> scans">
                            <span style="position:absolute;bottom:-22px;left:50%;transform:translateX(-50%);font-size:9px;color:var(--text-secondary);white-space:nowrap;"><?= date('M j', strtotime($day['date'])) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Scans by Device -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-mobile-alt"></i> Scans by Device</h3>
        </div>
        <?php if (empty($byDevice)): ?>
            <p style="color:var(--text-secondary);text-align:center;padding:30px;">No data available.</p>
        <?php else: ?>
            <div style="padding:10px 0;">
                <?php
                $totalDev = array_sum(array_column($byDevice, 'scans')) ?: 1;
                $devColors = ['mobile' => 'var(--cyan)', 'desktop' => 'var(--green)', 'tablet' => 'var(--orange)', 'other' => 'var(--text-secondary)'];
                ?>
                <?php foreach ($byDevice as $dev): ?>
                    <?php $pct = round(($dev['scans'] / $totalDev) * 100); ?>
                    <div style="padding:8px 16px;">
                        <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                            <span style="font-size:13px;"><?= View::e(ucfirst($dev['device_type'] ?? 'Unknown')) ?></span>
                            <span style="font-size:13px;color:var(--text-secondary);"><?= number_format($dev['scans']) ?> (<?= $pct ?>%)</span>
                        </div>
                        <div style="background:var(--bg-secondary);border-radius:4px;height:6px;">
                            <div style="background:<?= $devColors[strtolower($dev['device_type'] ?? '')] ?? 'var(--cyan)' ?>;width:<?= $pct ?>%;height:6px;border-radius:4px;"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="grid grid-2">
    <!-- Top QR Codes -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-trophy"></i> Top QR Codes by Scans</h3>
        </div>
        <?php if (empty($topQR)): ?>
            <p style="color:var(--text-secondary);text-align:center;padding:30px;">No data available.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr><th>#</th><th>Content</th><th>Type</th><th>Owner</th><th>Scans</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($topQR as $i => $qr): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= View::e($qr['content']) ?>">
                                <?= View::e(substr($qr['content'], 0, 40)) ?>…
                            </td>
                            <td><span class="badge badge-info"><?= View::e($qr['type']) ?></span></td>
                            <td style="font-size:12px;"><?= View::e($qr['user_name'] ?? '—') ?></td>
                            <td><strong><?= number_format($qr['scan_count']) ?></strong></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <!-- Scans by Country -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-globe"></i> Top Countries</h3>
        </div>
        <?php if (empty($byCountry)): ?>
            <p style="color:var(--text-secondary);text-align:center;padding:30px;">No data available.</p>
        <?php else: ?>
            <table class="table">
                <thead>
                    <tr><th>Country</th><th>Scans</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($byCountry as $c): ?>
                        <tr>
                            <td><?= View::e($c['country']) ?></td>
                            <td><?= number_format($c['scans']) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</div>
<?php View::endSection(); ?>
