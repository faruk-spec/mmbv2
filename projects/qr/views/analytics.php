<?php
/**
 * Analytics Dashboard View
 */
?>

<a href="/projects/qr" class="back-link">← Back to Dashboard</a>

<h1 style="margin-bottom: 30px; background: linear-gradient(135deg, var(--purple), var(--cyan)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
    <i class="fas fa-chart-line"></i> Analytics
</h1>

<!-- Stats Overview -->
<div class="grid grid-3" style="gap: 20px; margin-bottom: 30px;">
    <div class="glass-card stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
            <i class="fas fa-qrcode"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($totalQRs) ?></h3>
            <p>Total QR Codes</p>
        </div>
    </div>
    
    <div class="glass-card stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($activeQRs) ?></h3>
            <p>Active QR Codes</p>
        </div>
    </div>
    
    <div class="glass-card stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
            <i class="fas fa-eye"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format(array_sum(array_column($recentQRs, 'scan_count'))) ?></h3>
            <p>Total Scans</p>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="glass-card">
    <h3 class="section-title">
        <i class="fas fa-history"></i> Recent QR Codes
    </h3>
    
    <?php if (!empty($recentQRs)): ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Content</th>
                        <th>Scans</th>
                        <th>Created</th>
                        <th>Last Scanned</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentQRs as $qr): ?>
                        <tr>
                            <td><span class="type-badge"><?= htmlspecialchars($qr['type']) ?></span></td>
                            <td class="content-cell"><?= htmlspecialchars(substr($qr['content'], 0, 50)) ?><?= strlen($qr['content']) > 50 ? '...' : '' ?></td>
                            <td><strong><?= number_format($qr['scan_count'] ?? 0) ?></strong></td>
                            <td><?= date('M d, Y', strtotime($qr['created_at'])) ?></td>
                            <td><?= $qr['last_scanned_at'] ? date('M d, Y', strtotime($qr['last_scanned_at'])) : 'Never' ?></td>
                            <td><span class="status-badge status-<?= $qr['status'] ?>"><?= ucfirst($qr['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="empty-state">
            <p>No QR codes generated yet. <a href="/projects/qr/generate">Create your first QR code</a></p>
        </div>
    <?php endif; ?>
</div>

<style>
.grid-3 {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
}

@media (max-width: 768px) {
    .grid-3 {
        grid-template-columns: 1fr;
    }
}

.stat-card {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 25px;
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    flex-shrink: 0;
}

.stat-content h3 {
    font-size: 32px;
    font-weight: 700;
    margin-bottom: 5px;
    color: var(--text-primary);
}

.stat-content p {
    color: var(--text-secondary);
    font-size: 14px;
}

.table-responsive {
    overflow-x: auto;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.data-table th {
    text-align: left;
    padding: 12px;
    border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    color: var(--text-secondary);
    font-size: 13px;
    font-weight: 600;
    text-transform: uppercase;
}

.data-table td {
    padding: 12px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    color: var(--text-primary);
    font-size: 14px;
}

.type-badge {
    display: inline-block;
    padding: 4px 10px;
    background: linear-gradient(135deg, var(--purple), var(--cyan));
    color: white;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.content-cell {
    max-width: 300px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.status-badge {
    display: inline-block;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-active {
    background: #4CAF50;
    color: white;
}

.status-inactive {
    background: #9E9E9E;
    color: white;
}

.status-expired {
    background: #F44336;
    color: white;
}
</style>
