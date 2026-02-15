<?php
/**
 * Campaign View - Single Campaign Details
 */
?>

<div class="glass-card">
    <!-- Campaign Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div style="display: flex; align-items: center; gap: 15px;">
            <a href="/projects/qr/campaigns" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h3 class="section-title" style="margin: 0;">
                <i class="fas fa-bullhorn"></i> <?= htmlspecialchars($campaign['name']) ?>
            </h3>
            <span class="badge badge-<?= $campaign['status'] ?>" style="padding: 6px 12px; border-radius: 20px; font-size: 12px;">
                <?= ucfirst($campaign['status']) ?>
            </span>
        </div>
        <div style="display: flex; gap: 10px;">
            <button class="btn btn-secondary btn-sm" onclick="editCampaign(<?= $campaign['id'] ?>)">
                <i class="fas fa-edit"></i> Edit
            </button>
            <button class="btn btn-danger btn-sm" onclick="deleteCampaign(<?= $campaign['id'] ?>)">
                <i class="fas fa-trash"></i> Delete
            </button>
        </div>
    </div>
    
    <!-- Alerts -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
    
    <!-- Campaign Details -->
    <div class="campaign-details" style="margin-bottom: 40px;">
        <div class="grid grid-2" style="gap: 20px;">
            <div class="detail-item">
                <label style="color: var(--text-secondary); font-size: 13px; display: block; margin-bottom: 5px;">
                    <i class="fas fa-info-circle"></i> Description
                </label>
                <p style="color: var(--text-primary);">
                    <?= $campaign['description'] ? htmlspecialchars($campaign['description']) : '<em style="color: var(--text-secondary);">No description</em>' ?>
                </p>
            </div>
            <div class="detail-item">
                <label style="color: var(--text-secondary); font-size: 13px; display: block; margin-bottom: 5px;">
                    <i class="fas fa-calendar"></i> Created
                </label>
                <p style="color: var(--text-primary);">
                    <?= date('M d, Y', strtotime($campaign['created_at'])) ?>
                </p>
            </div>
        </div>
    </div>
    
    <!-- QR Codes Section -->
    <div style="border-top: 1px solid var(--border-color); padding-top: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h4 style="font-size: 18px; color: var(--text-primary); margin: 0;">
                <i class="fas fa-qrcode"></i> QR Codes (<?= count($qrCodes) ?>)
            </h4>
            <a href="/projects/qr/generate?campaign_id=<?= $campaign['id'] ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Add QR Code
            </a>
        </div>
        
        <?php if (empty($qrCodes)): ?>
            <div class="empty-state" style="padding: 40px 20px;">
                <div class="empty-icon" style="font-size: 48px;">
                    <i class="fas fa-qrcode"></i>
                </div>
                <h3 style="font-size: 18px; margin-bottom: 10px; color: var(--text-primary);">No QR Codes Yet</h3>
                <p style="color: var(--text-secondary); margin-bottom: 20px;">Add QR codes to this campaign to get started.</p>
                <a href="/projects/qr/generate?campaign_id=<?= $campaign['id'] ?>" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create First QR Code
                </a>
            </div>
        <?php else: ?>
            <div class="qr-codes-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 20px;">
                <?php foreach ($qrCodes as $qr): ?>
                    <div class="qr-card glass-card" style="padding: 20px; text-align: center;">
                        <div class="qr-preview" style="background: white; padding: 15px; border-radius: 10px; margin-bottom: 15px; position: relative;">
                            <?php if (!empty($qr['image_url'])): ?>
                                <img src="<?= htmlspecialchars($qr['image_url']) ?>" 
                                     alt="QR Code" 
                                     style="width: 100%; height: auto; max-width: 200px; margin: 0 auto; display: block;">
                            <?php else: ?>
                                <!-- Generate QR code on-the-fly using content -->
                                <div id="qr-<?= $qr['id'] ?>" class="qr-placeholder" data-qr-id="<?= $qr['id'] ?>" 
                                     data-qr-data='<?= htmlspecialchars(json_encode($qr), ENT_QUOTES, 'UTF-8') ?>'
                                     style="width: 200px; height: 200px; margin: 0 auto; display: flex; align-items: center; justify-content: center; background: rgba(153, 69, 255, 0.05); border-radius: 8px;">
                                    <i class="fas fa-spinner fa-spin" style="font-size: 24px; color: var(--purple);"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <h5 style="font-size: 14px; color: var(--text-primary); margin-bottom: 8px; font-weight: 600;">
                            <?= htmlspecialchars($qr['name'] ?? 'QR Code #' . $qr['id']) ?>
                        </h5>
                        <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 12px; word-break: break-all;">
                            <?= htmlspecialchars(substr($qr['content'] ?? '', 0, 50)) ?><?= strlen($qr['content'] ?? '') > 50 ? '...' : '' ?>
                        </p>
                        <div style="display: flex; gap: 8px; justify-content: center;">
                            <button onclick="viewQRDetails(<?= $qr['id'] ?>, <?= htmlspecialchars(json_encode($qr), ENT_QUOTES, 'UTF-8') ?>)" class="btn btn-secondary btn-sm" style="flex: 1;">
                                <i class="fas fa-eye"></i> View
                            </button>
                            <button onclick="downloadQR(<?= $qr['id'] ?>)" class="btn btn-primary btn-sm" style="flex: 1;">
                                <i class="fas fa-download"></i> Download
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.badge {
    display: inline-block;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-active {
    background: rgba(46, 213, 115, 0.1);
    color: #2ed573;
    border: 1px solid rgba(46, 213, 115, 0.3);
}

.badge-paused {
    background: rgba(255, 168, 0, 0.1);
    color: #ffa800;
    border: 1px solid rgba(255, 168, 0, 0.3);
}

.badge-archived {
    background: rgba(255, 71, 87, 0.1);
    color: #ff4757;
    border: 1px solid rgba(255, 71, 87, 0.3);
}

.detail-item {
    padding: 15px;
    background: var(--bg-secondary);
    border-radius: 8px;
}

.qr-card {
    transition: all 0.3s ease;
}

.qr-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 24px rgba(153, 69, 255, 0.2);
}

@media (max-width: 768px) {
    .grid-2 {
        grid-template-columns: 1fr;
    }
    
    .qr-codes-grid {
        grid-template-columns: 1fr !important;
    }
}
</style>

<!-- QR Details Modal -->
<div id="qrDetailsModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); z-index: 9999; align-items: center; justify-content: center;">
    <div class="glass-card" style="max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; position: relative;">
        <button onclick="closeQRModal()" style="position: absolute; top: 15px; right: 15px; background: none; border: none; color: var(--text-secondary); font-size: 24px; cursor: pointer; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 4px; transition: all 0.2s;">
            <i class="fas fa-times"></i>
        </button>
        
        <h3 style="margin-bottom: 20px; color: var(--text-primary);">
            <i class="fas fa-qrcode"></i> QR Code Details
        </h3>
        
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="background: white; padding: 20px; border-radius: 10px; display: inline-block;">
                <div id="modalQRCode" style="width: 300px; height: 300px;"></div>
            </div>
        </div>
        
        <div style="margin-bottom: 15px;">
            <label style="color: var(--text-secondary); font-size: 13px; display: block; margin-bottom: 5px;">
                <i class="fas fa-link"></i> Content
            </label>
            <p id="modalContent" style="color: var(--text-primary); word-break: break-all; background: var(--bg-secondary); padding: 10px; border-radius: 6px;"></p>
        </div>
        
        <div class="grid grid-2" style="gap: 15px; margin-bottom: 15px;">
            <div>
                <label style="color: var(--text-secondary); font-size: 13px; display: block; margin-bottom: 5px;">
                    <i class="fas fa-calendar"></i> Created
                </label>
                <p id="modalCreated" style="color: var(--text-primary);"></p>
            </div>
            <div>
                <label style="color: var(--text-secondary); font-size: 13px; display: block; margin-bottom: 5px;">
                    <i class="fas fa-eye"></i> Scans
                </label>
                <p id="modalScans" style="color: var(--text-primary);"></p>
            </div>
        </div>
        
        <div id="modalPassword" style="display: none; margin-bottom: 15px;">
            <label style="color: var(--text-secondary); font-size: 13px; display: block; margin-bottom: 5px;">
                <i class="fas fa-lock"></i> Password Protected
            </label>
            <p style="color: var(--purple);">Yes</p>
        </div>
        
        <div id="modalExpiry" style="display: none; margin-bottom: 15px;">
            <label style="color: var(--text-secondary); font-size: 13px; display: block; margin-bottom: 5px;">
                <i class="fas fa-clock"></i> Expires At
            </label>
            <p id="modalExpiryDate" style="color: var(--text-primary);"></p>
        </div>
        
        <div style="display: flex; gap: 10px; margin-top: 20px;">
            <button onclick="downloadQRFromModal()" class="btn btn-primary" style="flex: 1;">
                <i class="fas fa-download"></i> Download
            </button>
            <button onclick="closeQRModal()" class="btn btn-secondary" style="flex: 1;">
                Close
            </button>
        </div>
    </div>
</div>

<script>
let currentModalQRId = null;

function viewQRDetails(id, qrData) {
    currentModalQRId = id;
    const modal = document.getElementById('qrDetailsModal');
    
    // Set content details
    document.getElementById('modalContent').textContent = qrData.content || '';
    document.getElementById('modalCreated').textContent = qrData.created_at ? new Date(qrData.created_at).toLocaleString() : 'N/A';
    document.getElementById('modalScans').textContent = qrData.scan_count || '0';
    
    // Show/hide password protection
    const passwordDiv = document.getElementById('modalPassword');
    if (qrData.password_hash) {
        passwordDiv.style.display = 'block';
    } else {
        passwordDiv.style.display = 'none';
    }
    
    // Show/hide expiry
    const expiryDiv = document.getElementById('modalExpiry');
    if (qrData.expires_at) {
        expiryDiv.style.display = 'block';
        document.getElementById('modalExpiryDate').textContent = new Date(qrData.expires_at).toLocaleString();
    } else {
        expiryDiv.style.display = 'none';
    }
    
    // Generate QR code in modal
    const qrDiv = document.getElementById('modalQRCode');
    qrDiv.innerHTML = ''; // Clear previous
    
    if (typeof QRCodeStyling !== 'undefined') {
        // Build gradient color if enabled
        const dotColor = qrData.gradient_enabled && qrData.gradient_color
            ? {
                type: 'gradient',
                rotation: 0,
                colorStops: [
                    { offset: 0, color: qrData.foreground_color || '#000000' },
                    { offset: 1, color: qrData.gradient_color }
                ]
            }
            : qrData.foreground_color || '#000000';
        
        const qr = new QRCodeStyling({
            width: 300,
            height: 300,
            data: qrData.content,
            margin: 10,
            qrOptions: {
                errorCorrectionLevel: qrData.error_correction || 'H'
            },
            dotsOptions: {
                color: dotColor,
                type: qrData.dot_style || 'rounded'
            },
            backgroundOptions: {
                color: qrData.transparent_bg ? 'rgba(0,0,0,0)' : (qrData.background_color || '#ffffff')
            },
            cornersSquareOptions: {
                type: qrData.corner_style || 'extra-rounded',
                color: qrData.custom_marker_color && qrData.marker_color 
                    ? qrData.marker_color 
                    : (qrData.gradient_enabled ? dotColor : (qrData.foreground_color || '#000000'))
            },
            cornersDotOptions: {
                type: qrData.marker_center_style || 'dot',
                color: qrData.custom_marker_color && qrData.marker_color 
                    ? qrData.marker_color 
                    : (qrData.gradient_enabled ? dotColor : (qrData.foreground_color || '#000000'))
            }
        });
        qr.append(qrDiv);
    }
    
    // Show modal
    modal.style.display = 'flex';
}

function closeQRModal() {
    document.getElementById('qrDetailsModal').style.display = 'none';
    currentModalQRId = null;
}

function downloadQRFromModal() {
    if (currentModalQRId) {
        downloadQR(currentModalQRId);
    }
}

// Close modal when clicking outside
document.getElementById('qrDetailsModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeQRModal();
    }
});

function editCampaign(id) {
    window.location.href = '/projects/qr/campaigns/edit?id=' + id;
}

function deleteCampaign(id) {
    if (confirm('Are you sure you want to delete this campaign? This action cannot be undone.')) {
        fetch('/projects/qr/campaigns/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.href = '/projects/qr/campaigns';
            } else {
                alert(data.message || 'Failed to delete campaign');
            }
        })
        .catch(error => {
            alert('An error occurred while deleting the campaign');
            console.error('Error:', error);
        });
    }
}

function downloadQR(id) {
    // Find the QR code instance by ID and trigger download
    const qrDiv = document.getElementById('qr-' + id);
    if (qrDiv && qrDiv.qrInstance) {
        qrDiv.qrInstance.download({ name: 'qr-code-' + id, extension: 'png' });
    } else {
        alert('QR code not found. Please try viewing the QR first.');
    }
}

// Generate all QR codes after library loads
(function initializeQRCodes() {
    if (typeof QRCodeStyling === 'undefined') {
        console.log('Waiting for QRCodeStyling library...');
        setTimeout(initializeQRCodes, 100);
        return;
    }
    
    console.log('Generating QR codes...');
    const qrPlaceholders = document.querySelectorAll('.qr-placeholder');
    
    qrPlaceholders.forEach(placeholder => {
        try {
            const qrData = JSON.parse(placeholder.getAttribute('data-qr-data'));
            const qrDiv = placeholder;
            
            // Clear loading spinner
            qrDiv.innerHTML = '';
            qrDiv.style.background = 'none';
            
            // Build gradient color if enabled
            const dotColor = qrData.gradient_enabled && qrData.gradient_color
                ? {
                    type: 'gradient',
                    rotation: 0,
                    colorStops: [
                        { offset: 0, color: qrData.foreground_color || '#000000' },
                        { offset: 1, color: qrData.gradient_color }
                    ]
                }
                : qrData.foreground_color || '#000000';
            
            const markerColor = qrData.custom_marker_color && qrData.marker_color 
                ? qrData.marker_color 
                : (qrData.gradient_enabled ? dotColor : (qrData.foreground_color || '#000000'));
            
            const qr = new QRCodeStyling({
                width: 200,
                height: 200,
                data: qrData.content,
                margin: 10,
                qrOptions: {
                    errorCorrectionLevel: qrData.error_correction || 'H'
                },
                dotsOptions: {
                    color: dotColor,
                    type: qrData.dot_style || 'rounded'
                },
                backgroundOptions: {
                    color: qrData.transparent_bg ? 'rgba(0,0,0,0)' : (qrData.background_color || '#ffffff')
                },
                cornersSquareOptions: {
                    type: qrData.corner_style || 'extra-rounded',
                    color: markerColor
                },
                cornersDotOptions: {
                    type: qrData.marker_center_style || 'dot',
                    color: markerColor
                }
            });
            
            qr.append(qrDiv);
            
            // Store QR instance on the div for download functionality
            qrDiv.qrInstance = qr;
        } catch (error) {
            console.error('Error generating QR code:', error);
            qrDiv.innerHTML = '<span style="color: #ff4757; font-size: 12px;">Error loading QR</span>';
        }
    });
    
    console.log('QR codes generated:', qrPlaceholders.length);
})();
</script>
