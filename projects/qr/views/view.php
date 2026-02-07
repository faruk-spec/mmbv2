<a href="/projects/qr/history" class="back-link">← Back to History</a>

<h1 style="margin-bottom: 30px;">View QR Code</h1>

<div class="grid grid-2">
    <!-- QR Code Preview -->
    <div class="card" style="text-align: center;">
        <h3 style="margin-bottom: 20px;">QR Code Preview</h3>
        
        <div id="qrPreviewContainer" style="background: white; padding: 30px; border-radius: 12px; display: inline-block; margin-bottom: 20px;">
            <div id="qrcode"></div>
        </div>
        
        <div style="display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
            <button onclick="downloadQR()" class="btn btn-primary">
                📥 Download PNG
            </button>
            <?php if ($qr['is_dynamic'] ?? false): ?>
            <a href="/projects/qr/edit/<?= $qr['id'] ?>" class="btn btn-secondary" style="text-decoration: none;">
                ✏️ Edit QR Code
            </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- QR Details -->
    <div class="card">
        <h3 style="margin-bottom: 20px;">QR Code Details</h3>
        
        <div style="display: flex; flex-direction: column; gap: 15px;">
            <div style="padding: 15px; background: var(--bg-secondary); border-radius: 8px;">
                <div style="color: var(--text-secondary); font-size: 12px; margin-bottom: 5px;">Content Type</div>
                <div style="font-weight: 600;"><?= ucfirst(htmlspecialchars($qr['type'])) ?></div>
            </div>
            
            <div style="padding: 15px; background: var(--bg-secondary); border-radius: 8px;">
                <div style="color: var(--text-secondary); font-size: 12px; margin-bottom: 5px;">Content</div>
                <div style="font-weight: 600; word-break: break-all;"><?= htmlspecialchars($qr['content']) ?></div>
            </div>
            
            <?php if ($qr['is_dynamic'] ?? false): ?>
            <div style="padding: 15px; background: rgba(0, 123, 255, 0.1); border-radius: 8px; border: 1px solid #007bff;">
                <div style="color: #007bff; font-size: 12px; margin-bottom: 5px;">🔄 Dynamic QR Code</div>
                <div style="font-weight: 600; word-break: break-all;">
                    <?php if (!empty($qr['redirect_url'])): ?>
                        Redirects to: <?= htmlspecialchars($qr['redirect_url']) ?>
                    <?php endif; ?>
                    <?php if (!empty($qr['short_code'])): ?>
                        <div style="margin-top: 5px;">Short URL: <?= htmlspecialchars($qr['short_code']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($qr['password_hash'])): ?>
            <div style="padding: 15px; background: rgba(255, 193, 7, 0.1); border-radius: 8px; border: 1px solid #ffc107;">
                <div style="color: #ffc107; font-size: 12px; margin-bottom: 5px;">🔒 Password Protected</div>
                <div style="font-weight: 600;">Users must enter password to access content</div>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($qr['expires_at'])): ?>
            <div style="padding: 15px; background: rgba(255, 107, 107, 0.1); border-radius: 8px; border: 1px solid #ff6b6b;">
                <div style="color: #ff6b6b; font-size: 12px; margin-bottom: 5px;">⏰ Expires On</div>
                <div style="font-weight: 600;"><?= date('M j, Y g:i A', strtotime($qr['expires_at'])) ?></div>
            </div>
            <?php endif; ?>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div style="padding: 15px; background: var(--bg-secondary); border-radius: 8px;">
                    <div style="color: var(--text-secondary); font-size: 12px; margin-bottom: 5px;">Size</div>
                    <div style="font-weight: 600;"><?= htmlspecialchars($qr['size'] ?? 300) ?>px</div>
                </div>
                
                <div style="padding: 15px; background: var(--bg-secondary); border-radius: 8px;">
                    <div style="color: var(--text-secondary); font-size: 12px; margin-bottom: 5px;">Frame Style</div>
                    <div style="font-weight: 600;"><?= ucfirst(htmlspecialchars($qr['frame_style'] ?? 'none')) ?></div>
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div style="padding: 15px; background: var(--bg-secondary); border-radius: 8px;">
                    <div style="color: var(--text-secondary); font-size: 12px; margin-bottom: 5px;">Colors</div>
                    <div style="display: flex; gap: 10px; align-items: center; margin-top: 5px;">
                        <div style="width: 30px; height: 30px; border-radius: 4px; background: <?= htmlspecialchars($qr['foreground_color'] ?? '#000000') ?>; border: 1px solid var(--border-color);"></div>
                        <div style="width: 30px; height: 30px; border-radius: 4px; background: <?= htmlspecialchars($qr['background_color'] ?? '#ffffff') ?>; border: 1px solid var(--border-color);"></div>
                    </div>
                </div>
                
                <div style="padding: 15px; background: var(--bg-secondary); border-radius: 8px;">
                    <div style="color: var(--text-secondary); font-size: 12px; margin-bottom: 5px;">Total Scans</div>
                    <div style="font-weight: 600; font-size: 24px; color: var(--purple);"><?= (int)($qr['scan_count'] ?? 0) ?></div>
                </div>
            </div>
            
            <div style="padding: 15px; background: var(--bg-secondary); border-radius: 8px;">
                <div style="color: var(--text-secondary); font-size: 12px; margin-bottom: 5px;">Created</div>
                <div style="font-weight: 600;"><?= date('M j, Y g:i A', strtotime($qr['created_at'])) ?></div>
            </div>
        </div>
    </div>
</div>

<!-- QR Code Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
// Generate QR code
(function() {
    if (typeof QRCode === 'undefined') {
        console.error('QRCode library not loaded');
        return;
    }
    
    const qrDiv = document.getElementById('qrcode');
    const frameStyle = <?= json_encode($qr['frame_style'] ?? 'none') ?>;
    
    try {
        const qrcode = new QRCode(qrDiv, {
            text: <?= json_encode($qr['content']) ?>,
            width: <?= $qr['size'] ?? 300 ?>,
            height: <?= $qr['size'] ?? 300 ?>,
            colorDark: <?= json_encode($qr['foreground_color'] ?? '#000000') ?>,
            colorLight: <?= json_encode($qr['background_color'] ?? '#ffffff') ?>,
            correctLevel: QRCode.CorrectLevel.<?= $qr['error_correction'] ?? 'H' ?>
        });
        
        // Apply frame style after generation
        setTimeout(function() {
            applyFrameStyle(qrDiv, frameStyle);
        }, 200);
        
    } catch (error) {
        console.error('Error generating QR:', error);
        qrDiv.innerHTML = '<p style="color: red;">Error generating QR code</p>';
    }
})();

// Apply frame style
function applyFrameStyle(qrDiv, style) {
    if (style === 'none') return;
    
    const canvas = qrDiv.querySelector('canvas');
    if (!canvas) return;
    
    const wrapper = document.createElement('div');
    wrapper.style.position = 'relative';
    wrapper.style.display = 'inline-block';
    
    // Different frame styles
    switch (style) {
        case 'square':
            wrapper.style.border = '8px solid #000';
            wrapper.style.padding = '10px';
            break;
        case 'circle':
            wrapper.style.border = '8px solid #000';
            wrapper.style.borderRadius = '50%';
            wrapper.style.padding = '10px';
            wrapper.style.overflow = 'hidden';
            break;
        case 'rounded':
            wrapper.style.border = '8px solid #000';
            wrapper.style.borderRadius = '20px';
            wrapper.style.padding = '10px';
            break;
        case 'banner':
            wrapper.style.border = '8px solid #000';
            wrapper.style.borderRadius = '8px';
            wrapper.style.padding = '10px';
            const banner = document.createElement('div');
            banner.style.background = '#000';
            banner.style.color = '#fff';
            banner.style.padding = '5px 10px';
            banner.style.marginTop = '10px';
            banner.style.borderRadius = '4px';
            banner.style.fontSize = '12px';
            banner.style.textAlign = 'center';
            banner.textContent = 'Scan Me';
            wrapper.appendChild(banner);
            break;
        case 'bubble':
            wrapper.style.border = '8px solid #000';
            wrapper.style.borderRadius = '20px';
            wrapper.style.padding = '15px';
            wrapper.style.position = 'relative';
            const bubble = document.createElement('div');
            bubble.style.position = 'absolute';
            bubble.style.bottom = '-15px';
            bubble.style.left = '30px';
            bubble.style.width = '0';
            bubble.style.height = '0';
            bubble.style.borderLeft = '15px solid transparent';
            bubble.style.borderRight = '15px solid transparent';
            bubble.style.borderTop = '15px solid #000';
            wrapper.appendChild(bubble);
            break;
    }
    
    // Wrap the canvas
    canvas.parentNode.insertBefore(wrapper, canvas);
    wrapper.appendChild(canvas);
}

// Download function
function downloadQR() {
    const canvas = document.querySelector('#qrcode canvas');
    if (!canvas) {
        alert('QR code not generated yet');
        return;
    }
    
    const link = document.createElement('a');
    link.download = 'qrcode-<?= $qr['id'] ?>-<?= time() ?>.png';
    link.href = canvas.toDataURL('image/png');
    link.click();
}
</script>
