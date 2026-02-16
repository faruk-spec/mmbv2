<?php
/**
 * Analytics Dashboard View
 */
?>

<style>
/* Table responsive container - CRITICAL for horizontal scrolling */
.table-responsive {
    display: block;
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    width: 100%;
    /* Ensure scrolling works on all devices */
    overflow-y: visible;
    /* Add subtle border to indicate scrollable area */
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    background: var(--bg-secondary);
}

/* Ensure data table has minimum width to trigger scroll */
.data-table {
    width: 100%;
    min-width: 50rem; /* Minimum width to enable horizontal scroll on small screens */
    border-collapse: collapse;
}

/* Mobile Responsive Styles for Analytics */
@media (max-width: 768px) {
    /* Stack stat cards vertically on mobile */
    .grid-3 {
        grid-template-columns: 1fr !important;
    }
    
    /* Make controls responsive */
    .analytics-controls {
        flex-direction: column !important;
        align-items: stretch !important;
    }
    
    /* Table responsive - ensure scrolling works */
    .table-responsive {
        display: block;
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch;
        width: 100%;
        overflow-y: visible;
        margin: 0;
        padding: 0;
    }
    
    .data-table {
        font-size: 0.875rem;
    }
    
    /* Pagination responsive */
    .pagination-wrapper {
        flex-direction: column !important;
        text-align: center;
    }
    
    .pagination-controls {
        justify-content: center !important;
        flex-wrap: nowrap;
    }
    
    /* Hide less important columns on small screens */
    @media (max-width: 640px) {
        .hide-on-mobile {
            display: none !important;
        }
        
        .stat-card {
            padding: 1rem !important;
        }
        
        .stat-icon {
            width: 40px !important;
            height: 40px !important;
            font-size: 1.25rem !important;
        }
    }
    
    /* Mobile optimization for action buttons */
    .icon-only-btn {
        min-width: 2rem !important;
        width: 2rem !important;
        height: 2rem !important;
        padding: 0.25rem !important;
    }
    
    .icon-only-btn i {
        font-size: 0.75rem !important;
    }
    
    /* Keep action buttons in single line */
    td:last-child {
        white-space: nowrap !important;
    }
}

/* Improve button styling */
.btn-secondary:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    transition: all 0.2s ease;
}

/* Icon-only button styling */
.icon-only-btn {
    min-width: 2.5rem;
    position: relative;
}

/* Enhanced tooltip styling */
.icon-only-btn:hover::after {
    content: attr(title);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%) translateY(-8px);
    background: rgba(0, 0, 0, 0.9);
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    white-space: nowrap;
    pointer-events: none;
    z-index: 1000;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.icon-only-btn:hover::before {
    content: '';
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%) translateY(-2px);
    border: 5px solid transparent;
    border-top-color: rgba(0, 0, 0, 0.9);
    pointer-events: none;
    z-index: 1000;
}
</style>

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
            <h3><?= number_format($scanStats['total'] ?? 0) ?></h3>
            <p>Total Scans</p>
        </div>
    </div>
</div>

<!-- Additional Analytics Stats -->
<div class="grid grid-3" style="gap: 20px; margin-bottom: 30px;">
    <div class="glass-card stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #fa709a, #fee140);">
            <i class="fas fa-calendar-day"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($scanStats['today'] ?? 0) ?></h3>
            <p>Scans Today</p>
        </div>
    </div>
    
    <div class="glass-card stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #30cfd0, #330867);">
            <i class="fas fa-calendar-week"></i>
        </div>
        <div class="stat-content">
            <h3><?= number_format($scanStats['this_week'] ?? 0) ?></h3>
            <p>Scans This Week</p>
        </div>
    </div>
    
    <div class="glass-card stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #a8edea, #fed6e3);">
            <i class="fas fa-chart-bar"></i>
        </div>
        <div class="stat-content">
            <h3><?= $activeQRs > 0 ? number_format($scanStats['total'] / $activeQRs, 1) : '0' ?></h3>
            <p>Avg Scans per QR</p>
        </div>
    </div>
</div>

<!-- Date Range Filter -->
<div class="glass-card" style="margin-bottom: 30px; position: relative;">
    <!-- Loading Overlay -->
    <div id="filterLoadingOverlay" style="display: none; position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0, 0, 0, 0.5); backdrop-filter: blur(4px); border-radius: 12px; z-index: 100; align-items: center; justify-content: center;">
        <div style="text-align: center; color: white;">
            <div class="spinner" style="width: 40px; height: 40px; border: 4px solid rgba(255, 255, 255, 0.3); border-top-color: white; border-radius: 50%; animation: spin 1s linear infinite; margin: 0 auto 10px;"></div>
            <p style="font-size: 14px; font-weight: 500;">Loading...</p>
        </div>
    </div>
    
    <h3 class="section-title" style="margin-bottom: var(--space-lg);">
        <i class="fas fa-filter"></i> Filter Analytics
    </h3>
    <form id="dateFilterForm" method="GET" onsubmit="showFilterLoading()" style="display: flex; flex-wrap: wrap; gap: var(--space-md); align-items: end;">
        <div style="flex: 1; min-width: 200px;">
            <label for="start_date" style="display: block; margin-bottom: 0.5rem; font-size: var(--font-sm); color: var(--text-secondary);">Start Date</label>
            <input type="date" id="start_date" name="start_date" class="form-input" value="<?= $_GET['start_date'] ?? '' ?>" style="width: 100%;">
        </div>
        <div style="flex: 1; min-width: 200px;">
            <label for="end_date" style="display: block; margin-bottom: 0.5rem; font-size: var(--font-sm); color: var(--text-secondary);">End Date</label>
            <input type="date" id="end_date" name="end_date" class="form-input" value="<?= $_GET['end_date'] ?? '' ?>" style="width: 100%;">
        </div>
        <div style="display: flex; gap: var(--space-sm); flex-wrap: wrap;">
            <button type="button" class="btn-secondary btn-sm" onclick="setDateRange('all')">All Time</button>
            <button type="button" class="btn-secondary btn-sm" onclick="setDateRange(7)">Last 7 Days</button>
            <button type="button" class="btn-secondary btn-sm" onclick="setDateRange(30)">Last 30 Days</button>
            <button type="button" class="btn-secondary btn-sm" onclick="setDateRange(90)">Last 90 Days</button>
        </div>
        <div style="display: flex; gap: var(--space-sm);">
            <button type="submit" class="btn-primary btn-sm" id="applyFilterBtn">
                <i class="fas fa-check"></i> Apply
            </button>
            <button type="button" class="btn-secondary btn-sm" onclick="exportCSV()" id="exportBtn">
                <i class="fas fa-download"></i> Export CSV
            </button>
        </div>
    </form>
</div>

<!-- Charts Section -->
<div class="grid grid-2" style="gap: 20px; margin-bottom: 30px;">
    <div class="glass-card" style="position: relative;">
        <div id="chartLoading1" class="chart-loading" style="display: flex; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">
            <div class="spinner" style="width: 30px; height: 30px; border: 3px solid rgba(153, 69, 255, 0.3); border-top-color: var(--purple); border-radius: 50%; animation: spin 1s linear infinite;"></div>
        </div>
        <h3 class="section-title" style="margin-bottom: var(--space-lg);">
            <i class="fas fa-chart-line"></i> Scan Trends
        </h3>
        <canvas id="scanTrendsChart" style="max-height: 300px;"></canvas>
    </div>
    <div class="glass-card" style="position: relative;">
        <div id="chartLoading2" class="chart-loading" style="display: flex; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10;">
            <div class="spinner" style="width: 30px; height: 30px; border: 3px solid rgba(153, 69, 255, 0.3); border-top-color: var(--purple); border-radius: 50%; animation: spin 1s linear infinite;"></div>
        </div>
        <h3 class="section-title" style="margin-bottom: var(--space-lg);">
            <i class="fas fa-chart-bar"></i> Top QR Codes
        </h3>
        <canvas id="topQRsChart" style="max-height: 300px;"></canvas>
    </div>
</div>

<!-- Recent Activity -->
<div class="glass-card">
    <div class="analytics-controls" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-lg); flex-wrap: nowrap; gap: var(--space-md);">
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
        <div class="table-responsive" style="display: block; overflow-x: auto; -webkit-overflow-scrolling: touch; width: 100%; border: 1px solid var(--border-color); border-radius: 0.5rem; background: var(--bg-secondary);">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Content</th>
                        <th>Scans</th>
                        <th>Created</th>
                        <th>Last Scanned</th>
                        <th>Status</th>
                        <th>Actions</th>
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
                            <td>
                                <?php if (!empty($qr['deleted_at'])): ?>
                                    <span class="status-badge" style="background: #ef4444; color: white;">Deleted</span>
                                <?php else: ?>
                                    <span class="status-badge status-<?= $qr['status'] ?>"><?= ucfirst($qr['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (empty($qr['deleted_at'])): ?>
                                    <a href="/projects/qr/view/<?= $qr['id'] ?>" class="btn btn-secondary btn-sm icon-only-btn" title="View QR Code" style="font-size: 0.75rem; padding: 0.375rem 0.75rem;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--text-secondary); font-size: 0.75rem;">Deleted</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Controls -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination-wrapper" style="display: flex; justify-content: space-between; align-items: center; margin-top: var(--space-xl); padding-top: var(--space-lg); border-top: 1px solid var(--border-color); flex-wrap: nowrap; gap: var(--space-md);">
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
    showFilterLoading();
    window.location.href = '?page=1&per_page=' + perPage;
}

function showFilterLoading() {
    const overlay = document.getElementById('filterLoadingOverlay');
    if (overlay) {
        overlay.style.display = 'flex';
    }
}

function setDateRange(days) {
    const endDate = new Date();
    const startDate = new Date();
    
    if (days === 'all') {
        document.getElementById('start_date').value = '';
        document.getElementById('end_date').value = '';
    } else {
        startDate.setDate(endDate.getDate() - days);
        document.getElementById('start_date').value = startDate.toISOString().split('T')[0];
        document.getElementById('end_date').value = endDate.toISOString().split('T')[0];
    }
}

function exportCSV() {
    const btn = document.getElementById('exportBtn');
    const originalHTML = btn.innerHTML;
    
    // Show loading state
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Exporting...';
    
    const startDate = document.getElementById('start_date').value;
    const endDate = document.getElementById('end_date').value;
    let url = '/projects/qr/analytics/export-csv';
    const params = new URLSearchParams();
    
    if (startDate) params.append('start_date', startDate);
    if (endDate) params.append('end_date', endDate);
    
    if (params.toString()) {
        url += '?' + params.toString();
    }
    
    window.location.href = url;
    
    // Reset button after a delay
    setTimeout(() => {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    }, 2000);
}
</script>

<style>
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- Chart Initialization -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const urlParams = new URLSearchParams(window.location.search);
    const startDate = urlParams.get('start_date') || '';
    const endDate = urlParams.get('end_date') || '';
    
    // Fetch and render Scan Trends Chart
    fetch(`/projects/qr/analytics/scan-trends?start_date=${startDate}&end_date=${endDate}`)
        .then(response => response.json())
        .then(data => {
            // Hide loading spinner
            const loading1 = document.getElementById('chartLoading1');
            if (loading1) loading1.style.display = 'none';
            
            const ctx = document.getElementById('scanTrendsChart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels || [],
                    datasets: [{
                        label: 'Scans',
                        data: data.values || [],
                        borderColor: '#00f2fe',
                        backgroundColor: 'rgba(0, 242, 254, 0.1)',
                        tension: 0.4,
                        fill: true,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            labels: {
                                color: '#e0e0e0'
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                color: '#b0b0b0'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        },
                        x: {
                            ticks: {
                                color: '#b0b0b0'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error loading scan trends:', error);
            const loading1 = document.getElementById('chartLoading1');
            if (loading1) loading1.style.display = 'none';
        });
    
    // Fetch and render Top QRs Chart
    fetch(`/projects/qr/analytics/top-qrs?start_date=${startDate}&end_date=${endDate}`)
        .then(response => response.json())
        .then(data => {
            // Hide loading spinner
            const loading2 = document.getElementById('chartLoading2');
            if (loading2) loading2.style.display = 'none';
            
            const ctx = document.getElementById('topQRsChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: data.labels || [],
                    datasets: [{
                        label: 'Scans',
                        data: data.values || [],
                        backgroundColor: [
                            'rgba(102, 126, 234, 0.8)',
                            'rgba(118, 75, 162, 0.8)',
                            'rgba(240, 147, 251, 0.8)',
                            'rgba(245, 87, 108, 0.8)',
                            'rgba(79, 172, 254, 0.8)'
                        ],
                        borderColor: [
                            '#667eea',
                            '#764ba2',
                            '#f093fb',
                            '#f5576c',
                            '#4facfe'
                        ],
                        borderWidth: 2
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                color: '#b0b0b0'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        },
                        y: {
                            ticks: {
                                color: '#b0b0b0'
                            },
                            grid: {
                                color: 'rgba(255, 255, 255, 0.1)'
                            }
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error loading top QRs:', error);
            const loading2 = document.getElementById('chartLoading2');
            if (loading2) loading2.style.display = 'none';
        });
});
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
        flex-wrap: nowrap;
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
