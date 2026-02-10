<?php
/**
 * Campaigns View
 */
?>

<div class="glass-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl); flex-wrap: wrap; gap: var(--space-md);">
        <h3 class="section-title" style="margin-bottom: 0;">
            <i class="fas fa-bullhorn"></i> Campaigns
        </h3>
        <button class="btn-primary" onclick="showCreateCampaignModal()">
            <i class="fas fa-plus"></i> New Campaign
        </button>
    </div>
    
    <!-- Search and Filter Section -->
    <?php if (!empty($campaigns)): ?>
    <div style="display: flex; gap: var(--space-md); margin-bottom: var(--space-xl); flex-wrap: wrap;">
        <div style="flex: 1; min-width: 15rem;">
            <input type="text" 
                   id="searchCampaigns" 
                   class="form-control" 
                   placeholder="Search campaigns..." 
                   onkeyup="filterCampaigns()"
                   style="padding: 0.625rem 1rem;">
        </div>
        <select id="filterStatus" 
                class="form-select" 
                onchange="filterCampaigns()" 
                style="width: auto; min-width: 10rem; padding: 0.625rem 1rem;">
            <option value="">All Status</option>
            <option value="active">Active</option>
            <option value="paused">Paused</option>
            <option value="archived">Archived</option>
        </select>
        <select id="sortBy" 
                class="form-select" 
                onchange="sortCampaigns()" 
                style="width: auto; min-width: 10rem; padding: 0.625rem 1rem;">
            <option value="recent">Most Recent</option>
            <option value="name">Name (A-Z)</option>
            <option value="qr_count">Most QR Codes</option>
            <option value="scans">Most Scans</option>
        </select>
    </div>
    <?php endif; ?>
    
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
        <div class="empty-state">
            <div class="empty-icon">
                <i class="fas fa-bullhorn"></i>
            </div>
            <h2 style="font-size: var(--font-2xl); margin-bottom: var(--space-md); color: var(--text-primary);">No Campaigns Yet</h2>
            <p style="color: var(--text-secondary); margin-bottom: var(--space-xl);">Create your first campaign to organize your QR codes.</p>
            <button class="btn-primary" onclick="showCreateCampaignModal()">
                <i class="fas fa-plus"></i> Create First Campaign
            </button>
        </div>
    <?php else: ?>
        <div class="campaigns-grid" id="campaignsGrid">
            <?php foreach ($campaigns as $campaign): ?>
                <div class="campaign-card glass-card" 
                     data-name="<?= strtolower(htmlspecialchars($campaign['name'])) ?>"
                     data-status="<?= $campaign['status'] ?>"
                     data-qr-count="<?= $campaign['qr_count'] ?? 0 ?>"
                     data-scans="<?= $campaign['total_scans'] ?? 0 ?>"
                     data-date="<?= strtotime($campaign['created_at']) ?>">
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
                            <i class="fas fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div id="noResults" style="display: none; text-align: center; padding: var(--space-2xl); color: var(--text-secondary);">
            <i class="fas fa-search" style="font-size: 3rem; opacity: 0.5; margin-bottom: var(--space-md);"></i>
            <p>No campaigns found matching your criteria.</p>
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
    grid-template-columns: repeat(auto-fill, minmax(18.75rem, 1fr)); /* 300px */
    gap: var(--space-lg);
}

.campaign-card {
    padding: var(--space-lg);
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.campaign-card:hover {
    transform: translateY(-0.125rem); /* -2px */
}

.campaign-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: var(--space-sm);
}

.campaign-header h4 {
    margin: 0;
    color: var(--text-primary);
    font-size: var(--font-lg);
    font-weight: 600;
    word-break: break-word;
}

.campaign-status {
    padding: 0.25rem 0.75rem; /* 4px 12px */
    border-radius: 0.75rem; /* 12px */
    font-size: var(--font-xs);
    font-weight: 600;
    white-space: nowrap;
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
    font-size: var(--font-sm);
    margin: 0;
    line-height: 1.5;
}

.campaign-stats {
    display: flex;
    gap: var(--space-lg);
    flex-wrap: wrap;
}

.campaign-stats .stat {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    color: var(--text-secondary);
    font-size: var(--font-sm);
}

.campaign-stats .stat i {
    color: var(--cyan);
}

.campaign-actions {
    display: flex;
    gap: var(--space-sm);
    margin-top: auto;
}

.campaign-actions .btn-secondary,
.campaign-actions .btn-danger {
    flex: 1;
    padding: 0.5rem; /* 8px */
    text-align: center;
    font-size: var(--font-xs);
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
    backdrop-filter: blur(4px);
}

.modal-content {
    width: 90%;
    max-width: 31.25rem; /* 500px */
    max-height: 90vh;
    overflow-y: auto;
    /* Better scrolling on touch devices */
    -webkit-overflow-scrolling: touch;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-lg);
}

.modal-header h3 {
    margin: 0;
    color: var(--text-primary);
    font-size: var(--font-xl);
}

.modal-close {
    background: none;
    border: none;
    color: var(--text-secondary);
    cursor: pointer;
    font-size: var(--font-xl);
    padding: var(--space-xs);
    transition: color 0.2s ease;
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
@media (max-width: 48rem) { /* 768px */
    .campaigns-grid {
        grid-template-columns: 1fr;
    }
    
    .campaign-header {
        flex-direction: column;
        align-items: flex-start;
        gap: var(--space-sm);
    }
    
    .campaign-stats {
        flex-direction: column;
        gap: var(--space-sm);
    }
    
    .campaign-actions {
        flex-wrap: wrap;
    }
    
    .modal-content {
        width: 95%;
        padding: var(--space-lg);
    }
}

@media (max-width: 30rem) { /* 480px */
    .campaign-card {
        padding: var(--space-md);
    }
    
    .campaign-actions {
        flex-direction: column;
    }
    
    .campaign-actions .btn-secondary,
    .campaign-actions .btn-danger {
        width: 100%;
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

// Filter and Search Functions
function filterCampaigns() {
    const searchTerm = document.getElementById('searchCampaigns').value.toLowerCase();
    const statusFilter = document.getElementById('filterStatus').value.toLowerCase();
    const cards = document.querySelectorAll('.campaign-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const name = card.getAttribute('data-name');
        const status = card.getAttribute('data-status');
        
        const matchesSearch = name.includes(searchTerm);
        const matchesStatus = !statusFilter || status === statusFilter;
        
        if (matchesSearch && matchesStatus) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Show/hide no results message
    const noResults = document.getElementById('noResults');
    const grid = document.getElementById('campaignsGrid');
    if (visibleCount === 0) {
        grid.style.display = 'none';
        noResults.style.display = 'block';
    } else {
        grid.style.display = 'grid';
        noResults.style.display = 'none';
    }
}

function sortCampaigns() {
    const sortBy = document.getElementById('sortBy').value;
    const grid = document.getElementById('campaignsGrid');
    const cards = Array.from(grid.querySelectorAll('.campaign-card'));
    
    cards.sort((a, b) => {
        switch(sortBy) {
            case 'name':
                return a.getAttribute('data-name').localeCompare(b.getAttribute('data-name'));
            case 'qr_count':
                return parseInt(b.getAttribute('data-qr-count')) - parseInt(a.getAttribute('data-qr-count'));
            case 'scans':
                return parseInt(b.getAttribute('data-scans')) - parseInt(a.getAttribute('data-scans'));
            case 'recent':
            default:
                return parseInt(b.getAttribute('data-date')) - parseInt(a.getAttribute('data-date'));
        }
    });
    
    // Re-append sorted cards
    cards.forEach(card => grid.appendChild(card));
}
</script>
