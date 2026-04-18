<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('scripts'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js" integrity="sha384-bbDI8mCZLZIl99ttY1LlBdJ+F4C5SnKOL3LSvDFPsVlwlFn5XjPmC7z8cFWFcPSZ" crossorigin="anonymous"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const hourly = <?= json_encode($hourly) ?>;
    const ctx = document.getElementById('hourlyChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: hourly.map(h => h.hour),
            datasets: [
                {
                    label: 'Uploads',
                    data: hourly.map(h => h.uploads),
                    backgroundColor: 'rgba(0,255,136,0.55)',
                    borderColor: '#00ff88',
                    borderWidth: 1
                },
                {
                    label: 'Downloads',
                    data: hourly.map(h => h.downloads),
                    backgroundColor: 'rgba(79,172,254,0.55)',
                    borderColor: '#4facfe',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { labels: { color: '#e0e0e0' } } },
            scales: {
                y: { beginAtZero: true, ticks: { color: '#e0e0e0' }, grid: { color: 'rgba(255,255,255,0.08)' } },
                x: { ticks: { color: '#e0e0e0', maxRotation: 45 }, grid: { color: 'rgba(255,255,255,0.04)' } }
            }
        }
    });

    // Auto-refresh every 60 seconds
    setTimeout(() => location.reload(), 60000);
});
</script>
<?php View::endSection(); ?>

<?php View::section('content'); ?>

<div class="content-header" style="margin-bottom: 24px;">
    <h1 style="margin-bottom: 6px;">📊 ProShare — Activity Tracker</h1>
    <p class="text-muted" style="font-size: 0.875rem;">Live overview of uploads, downloads, and file activity. Auto-refreshes every 60 seconds.</p>
</div>

<!-- KPI Cards -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; margin-bottom: 28px;">
    <div class="stat-card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px;">
        <div style="font-size: 2rem; font-weight: 700; color: var(--cyan);"><?= number_format($totalFiles) ?></div>
        <div style="color: var(--text-secondary); font-size: 13px; margin-top: 4px;">Total Files</div>
    </div>
    <div class="stat-card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px;">
        <div style="font-size: 2rem; font-weight: 700; color: #00ff88;"><?= number_format($activeFiles) ?></div>
        <div style="color: var(--text-secondary); font-size: 13px; margin-top: 4px;">Active Files</div>
    </div>
    <div class="stat-card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px;">
        <div style="font-size: 2rem; font-weight: 700; color: #f39c12;"><?= number_format($inactiveFiles) ?></div>
        <div style="color: var(--text-secondary); font-size: 13px; margin-top: 4px;">Inactive Files</div>
    </div>
    <div class="stat-card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px;">
        <div style="font-size: 2rem; font-weight: 700; color: #4facfe;"><?= number_format($totalDownloads) ?></div>
        <div style="color: var(--text-secondary); font-size: 13px; margin-top: 4px;">Total Downloads</div>
    </div>
    <div class="stat-card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px;">
        <div style="font-size: 2rem; font-weight: 700; color: #9b59b6;"><?= number_format($totalTexts) ?></div>
        <div style="color: var(--text-secondary); font-size: 13px; margin-top: 4px;">Text Shares</div>
    </div>
    <div class="stat-card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px;">
        <div style="font-size: 2rem; font-weight: 700; color: #1abc9c;"><?= number_format($uploadsToday) ?></div>
        <div style="color: var(--text-secondary); font-size: 13px; margin-top: 4px;">Uploads Today</div>
    </div>
    <div class="stat-card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px;">
        <div style="font-size: 2rem; font-weight: 700; color: #e74c3c;"><?= number_format($dlToday) ?></div>
        <div style="color: var(--text-secondary); font-size: 13px; margin-top: 4px;">Downloads Today</div>
    </div>
</div>

<!-- Hourly Activity Chart -->
<div class="card mb-3" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 24px; margin-bottom: 24px;">
    <h3 style="margin: 0 0 18px; font-size: 1rem; color: var(--text-primary);">📈 Hourly Activity — Today (<?= date('Y-m-d') ?>)</h3>
    <div style="height: 260px;">
        <canvas id="hourlyChart"></canvas>
    </div>
</div>

<!-- Recent Downloads -->
<div class="card mb-3" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 24px; margin-bottom: 24px;">
    <h3 style="margin: 0 0 18px; font-size: 1rem; color: var(--text-primary);">⬇️ Recent Downloads (Last 50)</h3>
    <?php if (!empty($recentDownloads)): ?>
    <div class="table-responsive">
        <table class="table" style="font-size: 0.85rem;">
            <thead>
                <tr>
                    <th>File</th>
                    <th>Uploader</th>
                    <th>IP Address</th>
                    <th>User Agent</th>
                    <th>Downloaded At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentDownloads as $dl): ?>
                <tr>
                    <td>
                        <a href="/admin/projects/proshare/files" style="color: var(--cyan);">
                            <?= htmlspecialchars($dl['original_name'] ?? '—') ?>
                        </a>
                    </td>
                    <td><?= htmlspecialchars($dl['uploader'] ?? 'Anonymous') ?></td>
                    <td style="font-family: monospace; font-size: 0.8rem;"><?= htmlspecialchars($dl['ip_address'] ?? '—') ?></td>
                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 0.78rem; color: var(--text-secondary);"
                        title="<?= htmlspecialchars($dl['user_agent'] ?? '') ?>">
                        <?= htmlspecialchars(mb_strimwidth($dl['user_agent'] ?? '—', 0, 60, '…')) ?>
                    </td>
                    <td style="white-space: nowrap; color: var(--text-secondary);"><?= date('Y-m-d H:i:s', strtotime($dl['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p class="text-secondary">No download activity yet.</p>
    <?php endif; ?>
</div>

<!-- Recent Uploads -->
<div class="card" style="background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 12px; padding: 24px;">
    <h3 style="margin: 0 0 18px; font-size: 1rem; color: var(--text-primary);">⬆️ Recent Uploads (Last 20)</h3>
    <?php if (!empty($recentUploads)): ?>
    <div class="table-responsive">
        <table class="table" style="font-size: 0.85rem;">
            <thead>
                <tr>
                    <th>File</th>
                    <th>Uploader</th>
                    <th>Size</th>
                    <th>Status</th>
                    <th>Downloads</th>
                    <th>Expires</th>
                    <th>Uploaded At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentUploads as $f): ?>
                <tr>
                    <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"
                        title="<?= htmlspecialchars($f['original_name']) ?>">
                        <?= htmlspecialchars(mb_strimwidth($f['original_name'], 0, 40, '…')) ?>
                    </td>
                    <td><?= htmlspecialchars($f['uploader'] ?? 'Anonymous') ?></td>
                    <td><?= number_format($f['size'] / 1048576, 2) ?> MB</td>
                    <td>
                        <?php
                        $statusColor = match($f['status']) {
                            'active'   => '#00ff88',
                            'expired'  => '#f39c12',
                            'deleted'  => '#e74c3c',
                            'inactive' => '#95a5a6',
                            default    => '#bdc3c7',
                        };
                        ?>
                        <span style="color: <?= $statusColor ?>; font-weight: 600; text-transform: capitalize;">
                            <?= htmlspecialchars($f['status']) ?>
                        </span>
                    </td>
                    <td><?= number_format($f['downloads']) ?><?= $f['max_downloads'] ? ' / ' . $f['max_downloads'] : '' ?></td>
                    <td style="color: var(--text-secondary); white-space: nowrap;">
                        <?= $f['expires_at'] ? date('Y-m-d H:i', strtotime($f['expires_at'])) : 'Never' ?>
                    </td>
                    <td style="white-space: nowrap; color: var(--text-secondary);"><?= date('Y-m-d H:i', strtotime($f['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p class="text-secondary">No uploads yet.</p>
    <?php endif; ?>
</div>

<?php View::endSection(); ?>
