<a href="/projects/qr" class="back-link">← Back to Dashboard</a>

<h1 style="margin-bottom: 30px;">Generate QR Code</h1>

<div class="grid grid-2">
    <div class="card">
        <h3 style="margin-bottom: 20px;">QR Code Options</h3>
        
        <form method="POST" action="/projects/qr/generate">
            <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
            
            <div class="form-group">
                <label class="form-label">Content Type</label>
                <select name="type" class="form-select" id="qrType">
                    <option value="url">URL / Website</option>
                    <option value="text">Plain Text</option>
                    <option value="email">Email Address</option>
                    <option value="phone">Phone Number</option>
                    <option value="sms">SMS Message</option>
                    <option value="wifi">WiFi Network</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Content</label>
                <textarea name="content" class="form-textarea" rows="4" placeholder="Enter URL, text, or other content..." required></textarea>
            </div>
            
            <div class="grid grid-2">
                <div class="form-group">
                    <label class="form-label">Size (px)</label>
                    <select name="size" class="form-select">
                        <option value="150">Small (150x150)</option>
                        <option value="200" selected>Medium (200x200)</option>
                        <option value="300">Large (300x300)</option>
                        <option value="400">Extra Large (400x400)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">QR Color</label>
                    <input type="color" name="color" value="#000000" class="form-input" style="height: 45px; padding: 5px;">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Background Color</label>
                <input type="color" name="bg_color" value="#ffffff" class="form-input" style="height: 45px; padding: 5px; width: 100px;">
            </div>
            
            <button type="submit" class="btn btn-primary">Generate QR Code</button>
        </form>
    </div>
    
    <div class="card" style="text-align: center;">
        <h3 style="margin-bottom: 20px;">Preview</h3>
        
        <?php if (isset($_SESSION['generated_qr'])): ?>
            <div class="qr-preview">
                <img src="<?= htmlspecialchars($_SESSION['generated_qr']['image']) ?>" 
                     alt="Generated QR Code"
                     style="max-width: 100%;">
            </div>
            
            <div style="margin-top: 20px;">
                <p style="color: var(--text-secondary); margin-bottom: 15px; font-size: 14px;">
                    Content: <?= htmlspecialchars(substr($_SESSION['generated_qr']['content'], 0, 50)) ?>...
                </p>
                
                <a href="<?= htmlspecialchars($_SESSION['generated_qr']['image']) ?>" 
                   download="qrcode.png" 
                   class="btn btn-primary" 
                   target="_blank">
                    Download QR Code
                </a>
            </div>
        <?php else: ?>
            <div style="padding: 60px 20px; color: var(--text-secondary);">
                <svg width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="opacity: 0.5; margin-bottom: 20px;">
                    <rect x="3" y="3" width="7" height="7"/>
                    <rect x="14" y="3" width="7" height="7"/>
                    <rect x="14" y="14" width="7" height="7"/>
                    <rect x="3" y="14" width="7" height="7"/>
                </svg>
                <p>Your QR code will appear here</p>
            </div>
        <?php endif; ?>
    </div>
</div>
