<?php
/**
 * Campaigns View
 */
?>

<div class="glass-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h3 class="section-title">
            <i class="fas fa-bullhorn"></i> Campaigns
        </h3>
        <button class="btn-primary" onclick="showCreateCampaignModal()">
            <i class="fas fa-plus"></i> New Campaign
        </button>
    </div>
    
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
    
    <?php if (empty($campaigns)): ?>
        <div class="empty-state" style="padding: 60px 20px;">
            <div class="empty-icon">
                <i class="fas fa-bullhorn"></i>
            </div>
            <h2 style="font-size: 24px; margin-bottom: 15px; color: var(--text-primary);">No Campaigns Yet</h2>
            <p style="color: var(--text-secondary); margin-bottom: 30px;">Create your first campaign to organize your QR codes.</p>
            <button class="btn-primary" onclick="showCreateCampaignModal()">
                <i class="fas fa-plus"></i> Create First Campaign
            </button>
        </div>
    <?php else: ?>
        <div class="campaigns-grid">
            <?php foreach ($campaigns as $campaign): ?>
                <div class="campaign-card glass-card">
                    <div class="campaign-header">
                        <h4><?= htmlspecialchars($campaign['name']) ?></h4>
                        <span class="campaign-status status-<?= $campaign['status'] ?>">
                            <?= ucfirst($campaign['status']) ?>
                        </span>
                    </div>
                    
                    <?php if (!empty($campaign['description'])): ?>
                        <p class="campaign-description"><?= htmlspecialchars($campaign['description']) ?></p>
                    <?php endif; ?>
                    
                    <div class="campaign-stats">
                        <div class="stat">
                            <i class="fas fa-qrcode"></i>
                            <span><?= $campaign['qr_count'] ?? 0 ?> QR Codes</span>
                        </div>
                        <div class="stat">
                            <i class="fas fa-chart-line"></i>
                            <span><?= number_format($campaign['total_scans'] ?? 0) ?> Scans</span>
                        </div>
                    </div>
                    
                    <div class="campaign-actions">
                        <a href="/projects/qr/campaigns/view?id=<?= $campaign['id'] ?>" class="btn-secondary">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <button class="btn-secondary" onclick="editCampaign(<?= $campaign['id'] ?>)">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn-danger" onclick="deleteCampaign(<?= $campaign['id'] ?>)">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Create/Edit Campaign Modal -->
<div id="campaignModal" class="modal" style="display: none;">
    <div class="modal-content glass-card">
        <div class="modal-header">
            <h3 id="modalTitle">New Campaign</h3>
            <button class="modal-close" onclick="closeCampaignModal()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="campaignForm" method="POST" action="/projects/qr/campaigns/create">
            <input type="hidden" id="campaignId" name="id">
            
            <div class="form-group">
                <label>Campaign Name *</label>
                <input type="text" name="name" id="campaignName" required class="form-control">
            </div>
            
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" id="campaignDescription" rows="3" class="form-control"></textarea>
            </div>
            
            <div class="form-group">
                <label>Status</label>
                <select name="status" id="campaignStatus" class="form-select">
                    <option value="active">Active</option>
                    <option value="paused">Paused</option>
                    <option value="archived">Archived</option>
                </select>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="closeCampaignModal()">Cancel</button>
                <button type="submit" class="btn-primary">Save Campaign</button>
            </div>
        </form>
    </div>
</div>

<style>
.campaigns-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
}

.campaign-card {
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.campaign-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.campaign-header h4 {
    margin: 0;
    color: var(--text-primary);
    font-size: 18px;
}

.campaign-status {
    padding: 4px 12px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.status-active {
    background: rgba(46, 213, 115, 0.2);
    color: #2ed573;
}

.status-paused {
    background: rgba(255, 159, 64, 0.2);
    color: #ff9f40;
}

.status-archived {
    background: rgba(136, 136, 136, 0.2);
    color: #888;
}

.campaign-description {
    color: var(--text-secondary);
    font-size: 14px;
    margin: 0;
}

.campaign-stats {
    display: flex;
    gap: 20px;
}

.campaign-stats .stat {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--text-secondary);
    font-size: 14px;
}

.campaign-stats .stat i {
    color: var(--cyan);
}

.campaign-actions {
    display: flex;
    gap: 10px;
    margin-top: auto;
}

.campaign-actions .btn-secondary,
.campaign-actions .btn-danger {
    flex: 1;
    padding: 8px;
    text-align: center;
}

.modal {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.7);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
}

.modal-content {
    width: 90%;
    max-width: 500px;
    max-height: 90vh;
    overflow-y: auto;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.modal-header h3 {
    margin: 0;
    color: var(--text-primary);
}

.modal-close {
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    font-size: 20px;
    padding: 5px;
}

.modal-close:hover {
    color: var(--text-primary);
}

.alert {
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.alert-success {
    background: rgba(46, 213, 115, 0.1);
    color: #2ed573;
    border: 1px solid rgba(46, 213, 115, 0.3);
}

.alert-error {
    background: rgba(255, 71, 87, 0.1);
    color: #ff4757;
    border: 1px solid rgba(255, 71, 87, 0.3);
}

/* Responsive Styles */
@media (max-width: 768px) {
    .campaigns-grid {
        grid-template-columns: 1fr;
    }
    
    .campaign-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .campaign-stats {
        flex-direction: column;
        gap: 10px;
    }
    
    .campaign-actions {
        flex-wrap: wrap;
    }
    
    .modal-content {
        width: 95%;
        padding: 20px;
    }
}

@media (max-width: 480px) {
    .btn-primary, .btn-secondary {
        font-size: 14px;
        padding: 10px 16px;
    }
    
    .campaign-card {
        padding: 15px;
    }
    
    .section-title {
        font-size: 20px;
    }
}
</style>

<script>
function showCreateCampaignModal() {
    document.getElementById('modalTitle').textContent = 'New Campaign';
    document.getElementById('campaignForm').action = '/projects/qr/campaigns/create';
    document.getElementById('campaignId').value = '';
    document.getElementById('campaignName').value = '';
    document.getElementById('campaignDescription').value = '';
    document.getElementById('campaignStatus').value = 'active';
    document.getElementById('campaignModal').style.display = 'flex';
}

function editCampaign(id) {
    // In a real implementation, fetch campaign data via AJAX
    // For now, redirect to edit page
    window.location.href = '/projects/qr/campaigns/edit?id=' + id;
}

function closeCampaignModal() {
    document.getElementById('campaignModal').style.display = 'none';
}

function deleteCampaign(id) {
    if (!confirm('Are you sure you want to delete this campaign? QR codes will not be deleted, just unlinked.')) {
        return;
    }
    
    fetch('/projects/qr/campaigns/delete', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'id=' + id
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to delete campaign');
        }
    })
    .catch(error => {
        alert('Error deleting campaign');
        console.error(error);
    });
}

// Close modal on outside click
document.getElementById('campaignModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeCampaignModal();
    }
});
</script>
