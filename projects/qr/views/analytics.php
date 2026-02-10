<?php
/**
 * Analytics Dashboard View
 */
?>

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
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-lg); flex-wrap: wrap; gap: var(--space-md);">
        <h3 class="section-title" style="margin-bottom: 0;">
            <i class="fas fa-history"></i> Recent QR Codes
        </h3>
        <div style="display: flex; align-items: center; gap: var(--space-sm);">
            <label style="font-size: var(--font-sm); color: var(--text-secondary);">Show:</label>
            <select id="perPageSelect" class="form-select" style="width: auto; padding: 0.5rem 2rem 0.5rem 0.75rem;" onchange="changePerPage(this.value)">
                <option value="10" <?= ($perPage ?? 25) == 10 ? 'selected' : '' ?>>10</option>
                <option value="25" <?= ($perPage ?? 25) == 25 ? 'selected' : '' ?>>25</option>
                <option value="50" <?= ($perPage ?? 25) == 50 ? 'selected' : '' ?>>50</option>
                <option value="100" <?= ($perPage ?? 25) == 100 ? 'selected' : '' ?>>100</option>
            </select>
            <span style="font-size: var(--font-sm); color: var(--text-secondary);">
                Showing <?= ($offset ?? 0) + 1 ?>-<?= min(($offset ?? 0) + ($perPage ?? 25), $totalQRs) ?> of <?= number_format($totalQRs) ?>
            </span>
        </div>
    </div>
    
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
        
        <!-- Pagination Controls -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination-wrapper" style="display: flex; justify-content: space-between; align-items: center; margin-top: var(--space-xl); padding-top: var(--space-lg); border-top: 1px solid var(--border-color); flex-wrap: wrap; gap: var(--space-md);">
            <div class="pagination-info" style="font-size: var(--font-sm); color: var(--text-secondary);">
                Page <?= $page ?> of <?= $totalPages ?>
            </div>
            <div class="pagination-controls" style="display: flex; gap: var(--space-xs);">
                <?php if ($page > 1): ?>
                    <a href="?page=1&per_page=<?= $perPage ?>" class="btn-secondary btn-sm" title="First Page">
                        <i class="fas fa-angle-double-left"></i>
                    </a>
                    <a href="?page=<?= $page - 1 ?>&per_page=<?= $perPage ?>" class="btn-secondary btn-sm" title="Previous">
                        <i class="fas fa-angle-left"></i> Prev
                    </a>
                <?php endif; ?>
                
                <?php
                // Show page numbers
                $startPage = max(1, $page - 2);
                $endPage = min($totalPages, $page + 2);
                
                for ($i = $startPage; $i <= $endPage; $i++):
                    if ($i == $page):
                ?>
                    <span class="btn-primary btn-sm" style="pointer-events: none;"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?>&per_page=<?= $perPage ?>" class="btn-secondary btn-sm"><?= $i ?></a>
                <?php 
                    endif;
                endfor;
                ?>
                
                <?php if ($page < $totalPages): ?>
                    <a href="?page=<?= $page + 1 ?>&per_page=<?= $perPage ?>" class="btn-secondary btn-sm" title="Next">
                        Next <i class="fas fa-angle-right"></i>
                    </a>
                    <a href="?page=<?= $totalPages ?>&per_page=<?= $perPage ?>" class="btn-secondary btn-sm" title="Last Page">
                        <i class="fas fa-angle-double-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="empty-state">
            <p>No QR codes generated yet. <a href="/projects/qr/generate">Create your first QR code</a></p>
        </div>
    <?php endif; ?>
</div>

<script>
function changePerPage(perPage) {
    window.location.href = '?page=1&per_page=' + perPage;
}
</script>

<style>
.stat-card {
    display: flex;
    align-items: center;
    gap: var(--space-lg);
    padding: 1.5625rem; /* 25px */
}

.stat-icon {
    width: 3.75rem; /* 60px */
    height: 3.75rem;
    border-radius: 0.75rem; /* 12px */
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem; /* 24px */
    color: white;
    flex-shrink: 0;
}

.stat-content h3 {
    font-size: 2rem; /* 32px */
    font-weight: 700;
    margin-bottom: var(--space-xs);
    color: var(--text-primary);
}

.stat-content p {
    color: var(--text-secondary);
    font-size: var(--font-sm);
}

.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}

.data-table {
    width: 100%;
    border-collapse: collapse;
    margin-top: var(--space-lg);
    font-size: var(--font-sm);
}

.data-table th {
    text-align: left;
    padding: 0.75rem; /* 12px */
    border-bottom: 2px solid rgba(255, 255, 255, 0.1);
    color: var(--text-secondary);
    font-size: 0.8125rem; /* 13px */
    font-weight: 600;
    text-transform: uppercase;
}

.data-table td {
    padding: 0.75rem;
    border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    color: var(--text-primary);
    font-size: var(--font-sm);
}

.type-badge {
    display: inline-block;
    padding: 0.25rem 0.625rem; /* 4px 10px */
    background: linear-gradient(135deg, var(--purple), var(--cyan));
    color: white;
    border-radius: 0.75rem; /* 12px */
    font-size: 0.6875rem; /* 11px */
    font-weight: 600;
    text-transform: uppercase;
}

.content-cell {
    max-width: 18.75rem; /* 300px */
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.status-badge {
    display: inline-block;
    padding: 0.25rem 0.625rem;
    border-radius: 0.75rem;
    font-size: 0.6875rem;
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

.pagination-controls .btn-sm {
    min-width: 2.5rem; /* 40px */
}

@media (max-width: 48rem) {
    .pagination-wrapper {
        flex-direction: column;
        text-align: center;
    }
    
    .pagination-controls {
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .data-table {
        font-size: var(--font-xs);
    }
    
    .data-table th,
    .data-table td {
        padding: 0.5rem; /* 8px */
    }
    
    .content-cell {
        max-width: 12.5rem; /* 200px on mobile */
    }
}
</style>
