<?php
// Fetch campaigns for dropdown
$campaigns = [];
$userId = \Core\Auth::id();
if ($userId) {
    $db = \Core\Database::getInstance();
    $campaigns = $db->fetchAll("SELECT id, name FROM qr_campaigns WHERE user_id = ? ORDER BY name", [$userId]);
}
?>

<style>
/* Mobile Responsive Styles */
@media (max-width: 768px) {
    /* Make controls stack vertically on mobile */
    .controls-wrapper {
        flex-direction: column !important;
        align-items: stretch !important;
    }
    
    /* Make table scrollable horizontally on mobile */
    .table-scroll {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    
    /* Reduce min-width for mobile */
    .history-table {
        min-width: 100% !important;
        font-size: 0.875rem;
    }
    
    /* Stack action buttons vertically */
    .action-buttons {
        flex-direction: column !important;
        align-items: stretch !important;
    }
    
    .action-buttons > * {
        width: 100% !important;
        justify-content: center;
    }
    
    /* Make pagination stack on mobile */
    .pagination-wrapper {
        flex-direction: column !important;
        gap: 15px !important;
    }
    
    /* Adjust button sizes for mobile */
    .btn-sm {
        padding: 0.5rem !important;
        font-size: 0.875rem !important;
    }
    
    /* Hide less important columns on small screens */
    @media (max-width: 640px) {
        .hide-on-mobile {
            display: none !important;
        }
    }
}

/* Action button improvements */
.action-buttons {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.action-buttons .btn {
    transition: all 0.2s ease;
}

.action-buttons .btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

/* Icon-only button styling */
.icon-only-btn {
    min-width: 2.5rem;
    position: relative;
}

/* Enhanced tooltip styling */
.icon-only-btn:hover::after {
    content: attr(title);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%) translateY(-8px);
    background: rgba(0, 0, 0, 0.9);
    color: white;
    padding: 0.5rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.75rem;
    white-space: nowrap;
    pointer-events: none;
    z-index: 1000;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}

.icon-only-btn:hover::before {
    content: '';
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%) translateY(-2px);
    border: 5px solid transparent;
    border-top-color: rgba(0, 0, 0, 0.9);
    pointer-events: none;
    z-index: 1000;
}

/* Improve button styling */
.btn-info {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    color: white;
}

.btn-info:hover {
    opacity: 0.9;
}

.btn-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
    color: white;
}

.btn-success:hover {
    opacity: 0.9;
}
</style>

<div style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
    <h1 style="margin: 0;">QR Code History</h1>
    <a href="/projects/qr" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Back to Dashboard
    </a>
</div>

<div class="card">
    <?php if (empty($history)): ?>
        <div style="text-align: center; padding: 60px 20px; color: var(--text-secondary);">
            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="opacity: 0.5; margin-bottom: 15px;">
                <rect x="3" y="3" width="7" height="7"/>
                <rect x="14" y="3" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/>
                <rect x="3" y="14" width="7" height="7"/>
            </svg>
            <h3 style="margin-bottom: 10px;">No QR Codes Yet</h3>
            <p style="margin-bottom: 20px;">Your generated QR codes will appear here.</p>
            <a href="/projects/qr/generate" class="btn btn-primary">Generate Your First QR Code</a>
        </div>
    <?php else: ?>
        <!-- Controls -->
        <div class="controls-wrapper" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px;">
            <form method="POST" action="/projects/qr/bulk-delete" id="bulkDeleteForm" style="display: flex; align-items: center; gap: 10px;">
                <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                    <input type="checkbox" id="selectAll" style="width: 18px; height: 18px; cursor: pointer;">
                    <span style="font-size: 0.875rem; color: var(--text-secondary);">Select All</span>
                </label>
                <button type="button" onclick="confirmBulkDelete()" class="btn btn-danger btn-sm" id="bulkDeleteBtn" style="display: none;">
                    <i class="fas fa-trash"></i> Delete Selected
                </button>
            </form>
            
            <div style="display: flex; align-items: center; gap: 10px;">
                <label style="font-size: 0.875rem; color: var(--text-secondary);">Show:</label>
                <select onchange="changePerPage(this.value)" class="form-select" style="width: auto; padding: 0.5rem 2rem 0.5rem 0.75rem;">
                    <option value="10" <?= $perPage == 10 ? 'selected' : '' ?>>10</option>
                    <option value="25" <?= $perPage == 25 ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= $perPage == 50 ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= $perPage == 100 ? 'selected' : '' ?>>100</option>
                </select>
                <span style="font-size: 0.875rem; color: var(--text-secondary);">
                    Showing <?= $offset + 1 ?>-<?= min($offset + $perPage, $totalCount) ?> of <?= number_format($totalCount) ?>
                </span>
            </div>
        </div>
        
        <div class="table-scroll" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table class="history-table" style="width: 100%; border-collapse: collapse; min-width: 80rem;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color);">
                        <th style="padding: 0.75rem; text-align: left; width: 3rem;"></th>
                        <th style="padding: 0.75rem; text-align: left; width: 5rem;">Preview</th>
                        <th style="padding: 0.75rem; text-align: left; width: 15rem;">Content</th>
                        <th style="padding: 0.75rem; text-align: left; width: 6rem;">Type</th>
                        <th style="padding: 0.75rem; text-align: left; width: 5rem;">Size</th>
                        <th style="padding: 0.75rem; text-align: left; width: 5rem;">Scans</th>
                        <th style="padding: 0.75rem; text-align: left; width: 8rem;">Campaign</th>
                        <th style="padding: 0.75rem; text-align: left; width: 7rem;">Password</th>
                        <th style="padding: 0.75rem; text-align: left; width: 8rem;">Expiry</th>
                        <th style="padding: 0.75rem; text-align: left; width: 8rem;">Created</th>
                        <th style="padding: 0.75rem; text-align: left; width: 14rem;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $qr): ?>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 0.75rem;">
                                <input type="checkbox" name="qr_ids[]" value="<?= $qr['id'] ?>" class="qr-checkbox" form="bulkDeleteForm" style="width: 18px; height: 18px; cursor: pointer;">
                            </td>
                            <td style="padding: 0.75rem;">
                                <div style="background: white; padding: 0.5rem; border-radius: 0.25rem; display: inline-block;">
                                    <div id="qr-<?= $qr['id'] ?>" style="width: 3.75rem; height: 3.75rem;"></div>
                                </div>
                            </td>
                            <td style="padding: 0.75rem; max-width: 15rem; overflow: hidden; text-overflow: ellipsis;" 
                                title="<?= htmlspecialchars($qr['content']) ?>">
                                <?= htmlspecialchars(substr($qr['content'], 0, 40)) ?><?= strlen($qr['content']) > 40 ? '...' : '' ?>
                            </td>
                            <td style="padding: 0.75rem;">
                                <span style="background: var(--bg-secondary); padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem;">
                                    <?= ucfirst(htmlspecialchars($qr['type'])) ?>
                                </span>
                            </td>
                            <td style="padding: 0.75rem;">
                                <?= htmlspecialchars($qr['size'] ?? 200) ?>px
                            </td>
                            <td style="padding: 0.75rem;">
                                <?= (int)($qr['scan_count'] ?? 0) ?>
                            </td>
                            <td style="padding: 0.75rem;">
                                <select onchange="updateCampaign(<?= $qr['id'] ?>, this.value)" class="form-select" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                    <option value="">None</option>
                                    <?php foreach ($campaigns as $campaign): ?>
                                        <option value="<?= $campaign['id'] ?>" <?= ($qr['campaign_id'] ?? 0) == $campaign['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($campaign['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td style="padding: 0.75rem;">
                                <?php if (!empty($qr['password_hash'])): ?>
                                    <span style="background: rgba(239, 68, 68, 0.1); color: #dc2626; padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem;">
                                        <i class="fas fa-lock"></i> Protected
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--text-secondary); font-size: 0.75rem;">None</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 0.75rem;">
                                <?php if (!empty($qr['expires_at'])): ?>
                                    <?php 
                                    $expiryTime = strtotime($qr['expires_at']);
                                    $isExpired = $expiryTime < time();
                                    $color = $isExpired ? '#dc2626' : '#10b981';
                                    ?>
                                    <span style="color: <?= $color ?>; font-size: 0.75rem;">
                                        <?= date('M j, Y', $expiryTime) ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: var(--text-secondary); font-size: 0.75rem;">Never</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 0.75rem; color: var(--text-secondary); font-size: 0.875rem;">
                                <?= date('M j, Y', strtotime($qr['created_at'])) ?>
                            </td>
                            <td style="padding: 0.75rem;">
                                <div class="action-buttons" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                    <a href="/projects/qr/view/<?= $qr['id'] ?>" 
                                       class="btn btn-secondary btn-sm icon-only-btn" 
                                       title="View QR Code"
                                       style="padding: 0.5rem 0.75rem; text-decoration: none;">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ($qr['is_dynamic'] ?? false): ?>
                                    <a href="/projects/qr/edit/<?= $qr['id'] ?>" 
                                       class="btn btn-info btn-sm icon-only-btn" 
                                       title="Edit QR Code"
                                       style="padding: 0.5rem 0.75rem; text-decoration: none;">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php endif; ?>
                                    <button onclick="downloadQRCode(<?= $qr['id'] ?>)" 
                                            class="btn btn-success btn-sm icon-only-btn" 
                                            title="Download QR Code"
                                            style="padding: 0.5rem 0.75rem;">
                                        <i class="fas fa-download"></i>
                                    </button>
                                    <form method="POST" action="/projects/qr/delete" style="display: inline;">
                                        <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
                                        <input type="hidden" name="id" value="<?= $qr['id'] ?>">
                                        <button type="submit" 
                                                onclick="return confirm('Are you sure you want to delete this QR code?')" 
                                                class="btn btn-danger btn-sm icon-only-btn" 
                                                title="Delete QR Code"
                                                style="padding: 0.5rem 0.75rem;">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
        <div class="pagination-wrapper" style="margin-top: 20px; display: flex; justify-content: center; align-items: center; gap: 10px;">
            <?php if ($currentPage > 1): ?>
                <a href="?page=<?= $currentPage - 1 ?>&per_page=<?= $perPage ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
            <?php endif; ?>
            
            <div style="display: flex; gap: 5px;">
                <?php 
                $start = max(1, $currentPage - 2);
                $end = min($totalPages, $currentPage + 2);
                
                if ($start > 1): ?>
                    <a href="?page=1&per_page=<?= $perPage ?>" class="btn btn-secondary btn-sm">1</a>
                    <?php if ($start > 2): ?>
                        <span style="padding: 0.5rem;">...</span>
                    <?php endif; ?>
                <?php endif; ?>
                
                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <a href="?page=<?= $i ?>&per_page=<?= $perPage ?>" 
                       class="btn btn-sm <?= $i == $currentPage ? 'btn-primary' : 'btn-secondary' ?>">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>
                
                <?php if ($end < $totalPages): ?>
                    <?php if ($end < $totalPages - 1): ?>
                        <span style="padding: 0.5rem;">...</span>
                    <?php endif; ?>
                    <a href="?page=<?= $totalPages ?>&per_page=<?= $perPage ?>" class="btn btn-secondary btn-sm"><?= $totalPages ?></a>
                <?php endif; ?>
            </div>
            
            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?= $currentPage + 1 ?>&per_page=<?= $perPage ?>" class="btn btn-secondary btn-sm">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<script src="https://unpkg.com/qrcode-generator@1.4.4/qrcode.js"></script>
<script>
// Generate QR code previews
<?php foreach ($history as $qr): ?>
(function() {
    try {
        const qr = qrcode(0, 'H');
        qr.addData(<?= json_encode($qr['content']) ?>);
        qr.make();
        document.getElementById('qr-<?= $qr['id'] ?>').innerHTML = qr.createImgTag(1);
    } catch (e) {
        console.error('Failed to generate QR preview:', e);
    }
})();
<?php endforeach; ?>

// Download QR code
function downloadQRCode(id) {
    window.location.href = '/projects/qr/download?id=' + id;
}

// Change items per page
function changePerPage(perPage) {
    window.location.href = '?page=1&per_page=' + perPage;
}

// Select all checkboxes
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.qr-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    toggleBulkDeleteBtn();
});

// Show/hide bulk delete button
document.querySelectorAll('.qr-checkbox').forEach(cb => {
    cb.addEventListener('change', toggleBulkDeleteBtn);
});

function toggleBulkDeleteBtn() {
    const checked = document.querySelectorAll('.qr-checkbox:checked').length;
    document.getElementById('bulkDeleteBtn').style.display = checked > 0 ? 'block' : 'none';
}

// Bulk delete confirmation
function confirmBulkDelete() {
    const checked = document.querySelectorAll('.qr-checkbox:checked').length;
    if (checked === 0) {
        // Show a styled notification instead of alert
        const msg = document.createElement('div');
        msg.textContent = 'No QR codes selected. Please select at least one QR code to delete.';
        msg.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #ef4444; color: white; padding: 15px 20px; border-radius: 8px; z-index: 9999; box-shadow: 0 4px 6px rgba(0,0,0,0.1);';
        document.body.appendChild(msg);
        setTimeout(() => msg.remove(), 3000);
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${checked} QR code(s)?`)) {
        document.getElementById('bulkDeleteForm').submit();
    }
}

// Update campaign assignment
function updateCampaign(qrId, campaignId) {
    fetch('/projects/qr/update-campaign', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `_csrf_token=<?= \Core\Security::generateCsrfToken() ?>&qr_id=${qrId}&campaign_id=${campaignId}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message briefly
            const msg = document.createElement('div');
            msg.textContent = data.message;
            msg.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #10b981; color: white; padding: 15px 20px; border-radius: 8px; z-index: 9999;';
            document.body.appendChild(msg);
            setTimeout(() => msg.remove(), 3000);
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to update campaign');
    });
}
</script>
