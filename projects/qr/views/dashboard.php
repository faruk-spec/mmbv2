<div class="page-header" style="margin-bottom: 30px;">
    <h1 class="dashboard-title" style="font-size: 2rem; font-weight: 700;">QR Generator Dashboard</h1>
    <p class="dashboard-subtitle" style="color: var(--text-secondary); margin-top: 8px;">Create and manage your QR codes</p>
</div>

<!-- Quick Generate - Promoted Section -->
<div class="card quick-generate-card" style="margin-bottom: 30px; background: linear-gradient(135deg, var(--purple), var(--cyan)); padding: 30px; border: none;">
    <h3 style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px; color: white;">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="16"/>
            <line x1="8" y1="12" x2="16" y2="12"/>
        </svg>
        Quick Generate
    </h3>
    <p style="color: rgba(255,255,255,0.9); margin-bottom: 20px; font-size: 14px; line-height: 1.6;">
        Create a new QR code in seconds. You have <strong><?= number_format($stats['active_codes']) ?> active QR code<?= $stats['active_codes'] != 1 ? 's' : '' ?></strong>.
    </p>
    <a href="/projects/qr/generate" class="btn" style="background: white; color: var(--purple); font-weight: 600; border: none;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="16"/>
            <line x1="8" y1="12" x2="16" y2="12"/>
        </svg>
        Generate New QR Code
    </a>
</div>

<!-- Enhanced Statistics Grid -->
<div class="grid grid-3 stats-grid" style="margin-bottom: 30px; gap: 20px;">
    <div class="card stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2);">
            <i class="fas fa-qrcode"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($stats['total_generated']) ?></div>
            <div class="stat-label">QR Codes</div>
            <div class="stat-subtext"><?= number_format($stats['active_codes']) ?> active</div>
        </div>
    </div>
    
    <div class="card stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c);">
            <i class="fas fa-eye"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($stats['total_scans']) ?></div>
            <div class="stat-label">Total Scans</div>
            <div class="stat-subtext"><?= number_format($stats['scans_today']) ?> today</div>
        </div>
    </div>
    
    <div class="card stat-card">
        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe);">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($stats['scans_this_week']) ?></div>
            <div class="stat-label">This Week</div>
            <div class="stat-subtext">Avg: <?= number_format($stats['average_scans'], 1) ?></div>
        </div>
    </div>
</div>

<!-- Recent Activity & Top Performing Grid -->
<div class="grid grid-2" style="gap: 20px; margin-bottom: 30px;">
    <!-- Recent Activity Widget -->
    <div class="card">
        <h3 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 18px;">
            <i class="fas fa-history" style="color: var(--cyan);"></i>
            Recent Activity
        </h3>
        
        <?php if (!empty($recentQRs)): ?>
            <div class="activity-list">
                <?php foreach ($recentQRs as $qr): ?>
                    <div class="activity-item">
                        <div class="activity-icon">
                            <i class="fas fa-qrcode" style="color: var(--purple);"></i>
                        </div>
                        <div class="activity-content">
                            <div class="activity-title"><?= htmlspecialchars(mb_strimwidth($qr['content'], 0, 40, '...')) ?></div>
                            <div class="activity-meta">
                                <span class="activity-type"><?= ucfirst($qr['type']) ?></span>
                                <span class="activity-time"><?= date('M j, g:i A', strtotime($qr['created_at'])) ?></span>
                                <span class="activity-scans"><?= $qr['scan_count'] ?> scans</span>
                            </div>
                        </div>
                        <div class="activity-actions">
                            <a href="/projects/qr/view?id=<?= $qr['id'] ?>" class="btn-icon" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="/projects/qr/edit?id=<?= $qr['id'] ?>" class="btn-icon" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-inbox" style="font-size: 48px; color: var(--text-secondary); opacity: 0.5; margin-bottom: 10px;"></i>
                <p style="color: var(--text-secondary);">No QR codes yet</p>
                <a href="/projects/qr/generate" class="btn btn-primary" style="margin-top: 15px;">Create Your First QR</a>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Top Performing Widget -->
    <div class="card">
        <h3 style="margin-bottom: 20px; display: flex; align-items: center; gap: 10px; font-size: 18px;">
            <i class="fas fa-trophy" style="color: var(--orange);"></i>
            Top Performing QR Codes
        </h3>
        
        <?php if (!empty($topQRs)): ?>
            <?php 
            $maxScans = max(array_column($topQRs, 'scan_count'));
            ?>
            <div class="top-qr-list">
                <?php foreach ($topQRs as $index => $qr): ?>
                    <div class="top-qr-item">
                        <div class="top-qr-rank">#<?= $index + 1 ?></div>
                        <div class="top-qr-content">
                            <div class="top-qr-title"><?= htmlspecialchars(mb_strimwidth($qr['content'], 0, 35, '...')) ?></div>
                            <div class="top-qr-progress">
                                <div class="progress-bar">
                                    <div class="progress-fill" style="width: <?= $maxScans > 0 ? ($qr['scan_count'] / $maxScans * 100) : 0 ?>%;"></div>
                                </div>
                                <span class="top-qr-scans"><?= number_format($qr['scan_count']) ?> scans</span>
                            </div>
                        </div>
                        <div class="top-qr-actions">
                            <a href="/projects/qr/analytics?id=<?= $qr['id'] ?>" class="btn-icon" title="Analytics">
                                <i class="fas fa-chart-bar"></i>
                            </a>
                            <a href="/projects/qr/edit?id=<?= $qr['id'] ?>" class="btn-icon" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-chart-line" style="font-size: 48px; color: var(--text-secondary); opacity: 0.5; margin-bottom: 10px;"></i>
                <p style="color: var(--text-secondary);">No scan data yet</p>
                <p style="color: var(--text-secondary); font-size: 14px;">Share your QR codes to see performance</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Quick Actions Grid -->
<div class="grid grid-2">
    <div class="card">
        <h3 style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M9 11l3 3L22 4"/>
                <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
            </svg>
            Features
        </h3>
        <ul style="color: var(--text-secondary); list-style: none;">
            <li style="padding: 10px 0; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 10px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--green); flex-shrink: 0;">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                11 QR code types supported
            </li>
            <li style="padding: 10px 0; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 10px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--green); flex-shrink: 0;">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Custom colors and sizes
            </li>
            <li style="padding: 10px 0; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; gap: 10px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--green); flex-shrink: 0;">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Download as PNG/SVG
            </li>
            <li style="padding: 10px 0; display: flex; align-items: center; gap: 10px;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--green); flex-shrink: 0;">
                    <polyline points="20 6 9 17 4 12"/>
                </svg>
                Real-time scan analytics
            </li>
        </ul>
    </div>
</div>

<div class="grid grid-3" style="margin-top: 30px;">
    <a href="/projects/qr/campaigns" class="card" style="text-decoration: none; cursor: pointer; transition: all 0.3s;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--cyan)" stroke-width="2">
                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                <polyline points="22,6 12,13 2,6"/>
            </svg>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </div>
        <h4 style="margin-bottom: 8px;">Campaigns</h4>
        <p style="color: var(--text-secondary); font-size: 13px;">Organize QR codes into campaigns</p>
    </a>
    
    <a href="/projects/qr/analytics" class="card" style="text-decoration: none; cursor: pointer; transition: all 0.3s;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--magenta)" stroke-width="2">
                <path d="M3 3v18h18"/>
                <path d="M18 17l-5-5-5 5-5-5"/>
            </svg>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </div>
        <h4 style="margin-bottom: 8px;">Analytics</h4>
        <p style="color: var(--text-secondary); font-size: 13px;">Track scans and performance</p>
    </a>
    
    <a href="/projects/qr/bulk" class="card" style="text-decoration: none; cursor: pointer; transition: all 0.3s;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="var(--green)" stroke-width="2">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
                <line x1="12" y1="18" x2="12" y2="12"/>
                <line x1="9" y1="15" x2="15" y2="15"/>
            </svg>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </div>
        <h4 style="margin-bottom: 8px;">Bulk Generate</h4>
        <p style="color: var(--text-secondary); font-size: 13px;">Generate multiple QR codes at once</p>
    </a>
</div>

<style>
    .page-header h1 {
        background: linear-gradient(135deg, var(--purple), var(--cyan));
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    a.card:hover {
        transform: translateY(-4px);
        border-color: var(--cyan);
        box-shadow: 0 8px 24px rgba(0, 240, 255, 0.2);
    }
    
    /* Enhanced Stat Cards */
    .stat-card {
        display: flex;
        align-items: center;
        gap: 15px;
        padding: 20px !important;
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
    
    .stat-content {
        flex: 1;
    }
    
    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 4px;
    }
    
    .stat-label {
        font-size: 14px;
        color: var(--text-secondary);
        margin-bottom: 4px;
    }
    
    .stat-subtext {
        font-size: 12px;
        color: var(--text-secondary);
        opacity: 0.8;
    }
    
    /* Activity List */
    .activity-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    
    .activity-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 8px;
        border: 1px solid var(--border-color);
        transition: all 0.2s;
    }
    
    .activity-item:hover {
        background: rgba(255, 255, 255, 0.06);
        border-color: var(--cyan);
    }
    
    .activity-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        background: linear-gradient(135deg, var(--purple), var(--cyan));
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    .activity-content {
        flex: 1;
        min-width: 0;
    }
    
    .activity-title {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 4px;
    }
    
    .activity-meta {
        display: flex;
        gap: 10px;
        font-size: 12px;
        color: var(--text-secondary);
        flex-wrap: wrap;
    }
    
    .activity-type {
        padding: 2px 8px;
        background: rgba(153, 69, 255, 0.2);
        border-radius: 4px;
        color: var(--purple);
        font-weight: 500;
    }
    
    .activity-time {
        opacity: 0.8;
    }
    
    .activity-scans {
        color: var(--cyan);
        font-weight: 500;
    }
    
    .activity-actions {
        display: flex;
        gap: 8px;
    }
    
    /* Top QR List */
    .top-qr-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    
    .top-qr-item {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .top-qr-rank {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--orange), var(--red));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
        flex-shrink: 0;
    }
    
    .top-qr-content {
        flex: 1;
        min-width: 0;
    }
    
    .top-qr-title {
        font-size: 14px;
        font-weight: 500;
        color: var(--text-primary);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 6px;
    }
    
    .top-qr-progress {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .progress-bar {
        flex: 1;
        height: 6px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 3px;
        overflow: hidden;
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--cyan), var(--purple));
        border-radius: 3px;
        transition: width 0.3s ease;
    }
    
    .top-qr-scans {
        font-size: 12px;
        color: var(--text-secondary);
        font-weight: 500;
        min-width: 70px;
        text-align: right;
    }
    
    .top-qr-actions {
        display: flex;
        gap: 8px;
    }
    
    /* Button Icon */
    .btn-icon {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--border-color);
        color: var(--text-secondary);
        transition: all 0.2s;
        text-decoration: none;
    }
    
    .btn-icon:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: var(--cyan);
        color: var(--cyan);
        transform: translateY(-2px);
    }
    
    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 40px 20px;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .grid-2, .grid-3 {
            grid-template-columns: 1fr !important;
        }
        
        /* Dashboard Title - Center and Single Line */
        .dashboard-title {
            font-size: 1.5rem !important;
            text-align: center;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .dashboard-subtitle {
            text-align: center;
            font-size: 0.875rem !important;
        }
        
        /* Quick Generate Card - Compact */
        .quick-generate-card {
            padding: 20px !important;
            text-align: center;
        }
        
        .quick-generate-card h3 {
            font-size: 1.125rem !important;
            justify-content: center;
        }
        
        .quick-generate-card p {
            font-size: 0.875rem !important;
            margin-bottom: 15px !important;
        }
        
        /* Stats Grid - Horizontal Row on Mobile */
        .stats-grid {
            display: flex !important;
            flex-direction: row !important;
            overflow-x: auto;
            gap: 10px !important;
            padding-bottom: 10px;
            -webkit-overflow-scrolling: touch;
        }
        
        .stats-grid .stat-card {
            flex: 0 0 auto;
            min-width: 140px;
            max-width: 160px;
            padding: 12px !important;
        }
        
        .stat-icon {
            width: 40px !important;
            height: 40px !important;
            font-size: 18px !important;
        }
        
        .stat-value {
            font-size: 1.25rem !important;
        }
        
        .stat-label {
            font-size: 0.75rem !important;
        }
        
        .stat-subtext {
            font-size: 0.7rem !important;
        }
        
        /* Activity Items */
        .activity-item {
            flex-wrap: wrap;
        }
        
        .activity-actions {
            width: 100%;
            justify-content: flex-end;
        }
    }
</style>
