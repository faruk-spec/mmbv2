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
                                <div id="qr-<?= $qr['id'] ?>" style="width: 200px; height: 200px; margin: 0 auto;"></div>
                                <script>
                                    (function() {
                                        const qrDiv = document.getElementById('qr-<?= $qr['id'] ?>');
                                        if (qrDiv && typeof QRCodeStyling !== 'undefined') {
                                            const qr = new QRCodeStyling({
                                                width: 200,
                                                height: 200,
                                                data: <?= json_encode($qr['content']) ?>,
                                                margin: 10,
                                                qrOptions: {
                                                    errorCorrectionLevel: <?= json_encode($qr['error_correction'] ?? 'H') ?>
                                                },
                                                dotsOptions: {
                                                    color: <?= json_encode($qr['foreground_color'] ?? '#000000') ?>,
                                                    type: "rounded"
                                                },
                                                backgroundOptions: {
                                                    color: <?= json_encode($qr['background_color'] ?? '#ffffff') ?>
                                                },
                                                cornersSquareOptions: {
                                                    type: "extra-rounded"
                                                },
                                                cornersDotOptions: {
                                                    type: "dot"
                                                }
                                            });
                                            qr.append(qrDiv);
                                        }
                                    })();
                                </script>
                            <?php endif; ?>
                        </div>
                        <h5 style="font-size: 14px; color: var(--text-primary); margin-bottom: 8px; font-weight: 600;">
                            <?= htmlspecialchars($qr['name'] ?? 'QR Code #' . $qr['id']) ?>
                        </h5>
                        <p style="font-size: 12px; color: var(--text-secondary); margin-bottom: 12px; word-break: break-all;">
                            <?= htmlspecialchars(substr($qr['content'] ?? '', 0, 50)) ?><?= strlen($qr['content'] ?? '') > 50 ? '...' : '' ?>
                        </p>
                        <div style="display: flex; gap: 8px; justify-content: center;">
                            <a href="/projects/qr/history?id=<?= $qr['id'] ?>" class="btn btn-secondary btn-sm" style="flex: 1;">
                                <i class="fas fa-eye"></i> View
                            </a>
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

<script>
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
    window.location.href = '/projects/qr/download?id=' + id;
}
</script>
