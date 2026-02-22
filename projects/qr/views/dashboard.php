<div class="page-header" style="margin-bottom: 30px; text-align: center;">
    <h1 class="dashboard-title" style="font-size: 2.5rem; font-weight: 700; background: linear-gradient(135deg, var(--purple), var(--cyan)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;">
        QR Generator Dashboard
    </h1>
    <p class="dashboard-subtitle" style="color: var(--text-secondary); margin-top: 8px; font-size: 1.1rem;">
        Welcome back! Manage and track your QR codes
    </p>
</div>

<!-- Quick Actions Bar -->
<div class="quick-actions-bar" style="display: flex; gap: 15px; margin-bottom: 30px; flex-wrap: wrap; justify-content: center;">
    <a href="/projects/qr/generate" class="quick-action-btn" style="flex: 1; min-width: 200px; max-width: 250px; background: linear-gradient(135deg, var(--purple), var(--cyan)); color: white; padding: 20px; border-radius: 12px; text-decoration: none; text-align: center; box-shadow: 0 4px 15px rgba(153, 69, 255, 0.3); transition: transform 0.2s;">
        <i class="fas fa-plus-circle" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
        <strong style="font-size: 1.1rem;">Generate QR</strong>
        <p style="margin: 5px 0 0 0; font-size: 0.85rem; opacity: 0.9;">Create new code</p>
    </a>
    
    <a href="/projects/qr/history" class="quick-action-btn" style="flex: 1; min-width: 200px; max-width: 250px; background: linear-gradient(135deg, #f093fb, #f5576c); color: white; padding: 20px; border-radius: 12px; text-decoration: none; text-align: center; box-shadow: 0 4px 15px rgba(240, 147, 251, 0.3); transition: transform 0.2s;">
        <i class="fas fa-history" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
        <strong style="font-size: 1.1rem;">View History</strong>
        <p style="margin: 5px 0 0 0; font-size: 0.85rem; opacity: 0.9;"><?= number_format($stats['active_codes']) ?> active codes</p>
    </a>
    
    <a href="/projects/qr/analytics" class="quick-action-btn" style="flex: 1; min-width: 200px; max-width: 250px; background: linear-gradient(135deg, #4facfe, #00f2fe); color: white; padding: 20px; border-radius: 12px; text-decoration: none; text-align: center; box-shadow: 0 4px 15px rgba(79, 172, 254, 0.3); transition: transform 0.2s;">
        <i class="fas fa-chart-line" style="font-size: 2rem; margin-bottom: 10px; display: block;"></i>
        <strong style="font-size: 1.1rem;">Analytics</strong>
        <p style="margin: 5px 0 0 0; font-size: 0.85rem; opacity: 0.9;"><?= number_format($stats['total_scans']) ?> total scans</p>
    </a>
</div>

<!-- AI Design Assistant Widget -->
<div class="card ai-design-widget" style="margin-bottom: 30px; background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1)); border: 2px solid rgba(153, 69, 255, 0.3); padding: 25px;">
    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
        <h3 style="display: flex; align-items: center; gap: 12px; font-size: 1.3rem; margin: 0;">
            <div style="width: 50px; height: 50px; background: linear-gradient(135deg, var(--purple), var(--cyan)); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-magic" style="font-size: 1.5rem; color: white;"></i>
            </div>
            <div>
                <div style="font-weight: 700;">AI Design Assistant</div>
                <div style="font-size: 0.85rem; font-weight: 400; color: var(--text-secondary); margin-top: 3px;">Smart QR code design suggestions</div>
            </div>
        </h3>
        <div class="ai-badge" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white; padding: 8px 16px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; letter-spacing: 0.5px;">
            ✨ AI POWERED
        </div>
    </div>
    
    <!-- Quick Design Presets -->
    <div style="margin-bottom: 20px;">
        <h4 style="font-size: 0.95rem; margin-bottom: 12px; color: var(--text-secondary); font-weight: 600;">
            <i class="fas fa-palette"></i> Quick Design Presets
        </h4>
        <div class="design-presets-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 12px;">
            <a href="/projects/qr/generate?preset=modern" class="design-preset-card" style="text-decoration: none; padding: 15px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--border-color); border-radius: 10px; text-align: center; transition: all 0.3s; cursor: pointer;">
                <div style="width: 40px; height: 40px; margin: 0 auto 8px; background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 8px;"></div>
                <div style="font-size: 0.85rem; font-weight: 600; margin-bottom: 3px;">Modern</div>
                <div style="font-size: 0.7rem; color: var(--text-secondary);">Clean & minimal</div>
            </a>
            
            <a href="/projects/qr/generate?preset=vibrant" class="design-preset-card" style="text-decoration: none; padding: 15px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--border-color); border-radius: 10px; text-align: center; transition: all 0.3s; cursor: pointer;">
                <div style="width: 40px; height: 40px; margin: 0 auto 8px; background: linear-gradient(135deg, #f093fb, #f5576c); border-radius: 8px;"></div>
                <div style="font-size: 0.85rem; font-weight: 600; margin-bottom: 3px;">Vibrant</div>
                <div style="font-size: 0.7rem; color: var(--text-secondary);">Bold & colorful</div>
            </a>
            
            <a href="/projects/qr/generate?preset=professional" class="design-preset-card" style="text-decoration: none; padding: 15px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--border-color); border-radius: 10px; text-align: center; transition: all 0.3s; cursor: pointer;">
                <div style="width: 40px; height: 40px; margin: 0 auto 8px; background: linear-gradient(135deg, #2c3e50, #34495e); border-radius: 8px;"></div>
                <div style="font-size: 0.85rem; font-weight: 600; margin-bottom: 3px;">Professional</div>
                <div style="font-size: 0.7rem; color: var(--text-secondary);">Business ready</div>
            </a>
            
            <a href="/projects/qr/generate?preset=gradient" class="design-preset-card" style="text-decoration: none; padding: 15px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--border-color); border-radius: 10px; text-align: center; transition: all 0.3s; cursor: pointer;">
                <div style="width: 40px; height: 40px; margin: 0 auto 8px; background: linear-gradient(135deg, #4facfe, #00f2fe); border-radius: 8px;"></div>
                <div style="font-size: 0.85rem; font-weight: 600; margin-bottom: 3px;">Gradient</div>
                <div style="font-size: 0.7rem; color: var(--text-secondary);">Smooth blend</div>
            </a>
        </div>
    </div>
    
    <!-- AI Suggestions -->
    <div id="aiInsightsContainer">
        <?php if (!empty($aiInsights)): ?>
            <?php foreach ($aiInsights as $insight): ?>
                <div style="background: rgba(0, 0, 0, 0.2); padding: 15px; border-radius: 10px; border-left: 3px solid <?= 
                    $insight['type'] === 'success' ? 'var(--green)' : 
                    ($insight['type'] === 'warning' ? '#f59e0b' : 
                    ($insight['type'] === 'info' ? '#3b82f6' : 'var(--cyan)')) 
                ?>; margin-bottom: 12px;">
                    <div style="display: flex; align-items: start; gap: 12px;">
                        <i class="fas <?= htmlspecialchars($insight['icon']) ?>" style="color: <?= 
                            $insight['type'] === 'success' ? 'var(--green)' : 
                            ($insight['type'] === 'warning' ? '#f59e0b' : 
                            ($insight['type'] === 'info' ? '#3b82f6' : 'var(--cyan)')) 
                        ?>; font-size: 1.2rem; margin-top: 2px;"></i>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; margin-bottom: 8px; font-size: 0.95rem;"><?= htmlspecialchars($insight['title']) ?></div>
                            <div style="font-size: 0.85rem; line-height: 1.5; color: var(--text-secondary); margin-bottom: <?= $insight['action'] ? '10px' : '0' ?>;">
                                <?= $insight['message'] ?>
                            </div>
                            <?php if ($insight['action'] && $insight['link']): ?>
                                <a href="<?= htmlspecialchars($insight['link']) ?>" 
                                   style="display: inline-block; padding: 6px 14px; background: rgba(102, 126, 234, 0.2); color: var(--cyan); border-radius: 6px; text-decoration: none; font-size: 0.8rem; font-weight: 600; border: 1px solid rgba(102, 126, 234, 0.4); transition: all 0.2s;">
                                    <?= htmlspecialchars($insight['action']) ?> <i class="fas fa-arrow-right" style="font-size: 0.7rem;"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="background: rgba(0, 0, 0, 0.2); padding: 15px; border-radius: 10px; border-left: 3px solid var(--cyan);">
                <div style="display: flex; align-items: start; gap: 12px;">
                    <i class="fas fa-lightbulb" style="color: var(--cyan); font-size: 1.2rem; margin-top: 2px;"></i>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; margin-bottom: 8px; font-size: 0.95rem;">AI Design Tip</div>
                        <div style="font-size: 0.85rem; line-height: 1.5; color: var(--text-secondary);">
                            Based on your usage patterns, we recommend using <strong style="color: var(--cyan);">rounded corners</strong> and <strong style="color: var(--purple);">gradient colors</strong> for better scan rates. Try the "Modern" preset!
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Enhanced Statistics Grid -->
<div class="grid grid-3 stats-grid" style="margin-bottom: 30px; gap: 20px;">
    <div class="card stat-card" style="position: relative; overflow: hidden;">
        <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2); position: relative; z-index: 2;">
            <i class="fas fa-qrcode"></i>
        </div>
        <div class="stat-content" style="position: relative; z-index: 2;">
            <div class="stat-value" style="font-size: 2.5rem;"><?= number_format($stats['total_generated']) ?></div>
            <div class="stat-label" style="font-weight: 600;">Total Generated</div>
            <div class="stat-subtext" style="color: var(--green); font-weight: 500;">
                <i class="fas fa-check-circle"></i> <?= number_format($stats['active_codes']) ?> active
            </div>
        </div>
        <div class="stat-background-pattern" style="position: absolute; top: 0; right: 0; width: 100px; height: 100px; opacity: 0.05; z-index: 1;">
            <i class="fas fa-qrcode" style="font-size: 100px;"></i>
        </div>
    </div>
    
    <div class="card stat-card" style="position: relative; overflow: hidden;">
        <div class="stat-icon" style="background: linear-gradient(135deg, #f093fb, #f5576c); position: relative; z-index: 2;">
            <i class="fas fa-eye"></i>
        </div>
        <div class="stat-content" style="position: relative; z-index: 2;">
            <div class="stat-value" style="font-size: 2.5rem;"><?= number_format($stats['total_scans']) ?></div>
            <div class="stat-label" style="font-weight: 600;">Total Scans</div>
            <div class="stat-subtext" style="color: var(--cyan); font-weight: 500;">
                <i class="fas fa-arrow-up"></i> <?= number_format($stats['scans_today']) ?> today
            </div>
        </div>
        <div class="stat-background-pattern" style="position: absolute; top: 0; right: 0; width: 100px; height: 100px; opacity: 0.05; z-index: 1;">
            <i class="fas fa-eye" style="font-size: 100px;"></i>
        </div>
    </div>
    
    <div class="card stat-card" style="position: relative; overflow: hidden;">
        <div class="stat-icon" style="background: linear-gradient(135deg, #4facfe, #00f2fe); position: relative; z-index: 2;">
            <i class="fas fa-chart-line"></i>
        </div>
        <div class="stat-content" style="position: relative; z-index: 2;">
            <div class="stat-value" style="font-size: 2.5rem;"><?= number_format($stats['scans_this_week']) ?></div>
            <div class="stat-label" style="font-weight: 600;">This Week</div>
            <div class="stat-subtext" style="color: var(--purple); font-weight: 500;">
                <i class="fas fa-chart-bar"></i> Avg: <?= number_format($stats['average_scans'], 1) ?>/QR
            </div>
        </div>
        <div class="stat-background-pattern" style="position: absolute; top: 0; right: 0; width: 100px; height: 100px; opacity: 0.05; z-index: 1;">
            <i class="fas fa-chart-line" style="font-size: 100px;"></i>
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
    
    /* Quick Action Buttons */
    .quick-action-btn:hover {
        transform: translateY(-5px) scale(1.02);
        box-shadow: 0 8px 25px rgba(153, 69, 255, 0.4) !important;
    }
    
    /* AI Design Widget */
    .ai-design-widget {
        animation: slideInFromLeft 0.6s ease-out;
    }
    
    @keyframes slideInFromLeft {
        from {
            opacity: 0;
            transform: translateX(-30px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    .design-preset-card {
        position: relative;
        overflow: hidden;
    }
    
    .design-preset-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.1), transparent);
        transition: left 0.5s;
    }
    
    .design-preset-card:hover {
        transform: translateY(-3px);
        border-color: var(--cyan);
        background: rgba(255, 255, 255, 0.08);
        box-shadow: 0 4px 12px rgba(0, 240, 255, 0.2);
    }
    
    .design-preset-card:hover::before {
        left: 100%;
    }
    
    .ai-badge {
        animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
        0%, 100% {
            box-shadow: 0 0 0 0 rgba(102, 126, 234, 0.7);
        }
        50% {
            box-shadow: 0 0 0 10px rgba(102, 126, 234, 0);
        }
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
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .stat-card {
        animation: fadeInUp 0.5s ease forwards;
    }
    
    .stat-card:nth-child(1) { animation-delay: 0.1s; }
    .stat-card:nth-child(2) { animation-delay: 0.2s; }
    .stat-card:nth-child(3) { animation-delay: 0.3s; }
    
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
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
    }
    
    .stat-content {
        flex: 1;
    }
    
    .stat-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--text-primary);
        margin-bottom: 4px;
        line-height: 1;
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
        display: flex;
        align-items: center;
        gap: 5px;
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
        transform: translateX(5px);
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
        transition: all 0.2s;
    }
    
    .top-qr-item:hover {
        transform: translateX(5px);
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
        box-shadow: 0 2px 10px rgba(255, 107, 107, 0.3);
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
        
        /* Quick Actions */
        .quick-actions-bar {
            flex-direction: column;
        }
        
        .quick-action-btn {
            max-width: 100% !important;
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
