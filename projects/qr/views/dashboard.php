<div class="page-header" style="margin-bottom: 30px;">
    <h1 style="font-size: 2rem; font-weight: 700;">QR Generator Dashboard</h1>
    <p style="color: var(--text-secondary); margin-top: 8px;">Create and manage your QR codes</p>
</div>

<div class="grid grid-3" style="margin-bottom: 30px;">
    <div class="card stat-card">
        <div class="stat-value"><?= $stats['total_generated'] ?></div>
        <div class="stat-label">QR Codes Generated</div>
    </div>
    
    <div class="card stat-card">
        <div class="stat-value"><?= $stats['total_scans'] ?></div>
        <div class="stat-label">Total Scans</div>
    </div>
    
    <div class="card stat-card">
        <div class="stat-value"><?= $stats['active_codes'] ?></div>
        <div class="stat-label">Active Codes</div>
    </div>
</div>

<div class="grid grid-2">
    <div class="card">
        <h3 style="margin-bottom: 15px; display: flex; align-items: center; gap: 10px;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="16"/>
                <line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
            Quick Generate
        </h3>
        <p style="color: var(--text-secondary); margin-bottom: 20px; font-size: 14px; line-height: 1.6;">
            Create a new QR code in seconds. Choose from various types including URLs, text, WiFi credentials, and more.
        </p>
        <a href="/projects/qr/generate" class="btn btn-primary">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="16"/>
                <line x1="8" y1="12" x2="16" y2="12"/>
            </svg>
            Generate New QR Code
        </a>
    </div>
    
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
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--cyan); flex-shrink: 0;">
                    <circle cx="12" cy="12" r="10"/>
                    <polyline points="12 6 12 12 16 14"/>
                </svg>
                Scan analytics (coming soon)
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
</style>
