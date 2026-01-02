<h1 style="margin-bottom: 30px;">QR Generator Dashboard</h1>

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
        <h3 style="margin-bottom: 20px;">Quick Generate</h3>
        <p style="color: var(--text-secondary); margin-bottom: 20px;">
            Create a new QR code in seconds. Choose from various types including URLs, text, WiFi credentials, and more.
        </p>
        <a href="/projects/qr/generate" class="btn btn-primary">Generate New QR Code</a>
    </div>
    
    <div class="card">
        <h3 style="margin-bottom: 20px;">Features</h3>
        <ul style="color: var(--text-secondary); list-style: none;">
            <li style="padding: 8px 0; border-bottom: 1px solid var(--border-color);">✓ Multiple QR code types</li>
            <li style="padding: 8px 0; border-bottom: 1px solid var(--border-color);">✓ Custom colors and sizes</li>
            <li style="padding: 8px 0; border-bottom: 1px solid var(--border-color);">✓ Download as PNG/SVG</li>
            <li style="padding: 8px 0;">✓ Scan analytics (coming soon)</li>
        </ul>
    </div>
</div>
