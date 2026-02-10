<?php
/**
 * Bulk Generate View
 */
?>

<div class="glass-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl); flex-wrap: wrap; gap: var(--space-md);">
        <h3 class="section-title" style="margin-bottom: 0;">
            <i class="fas fa-layer-group"></i> Bulk QR Generation
        </h3>
    </div>
    
    <div class="bulk-upload-section glass-card" style="margin-bottom: var(--space-xl);">
        <h4 style="margin-bottom: var(--space-md); color: var(--text-primary); font-size: var(--font-lg);">
            <i class="fas fa-upload"></i> Upload CSV File
        </h4>
        <p style="color: var(--text-secondary); margin-bottom: var(--space-lg); font-size: var(--font-sm);">
            Upload a CSV file with URLs or text to generate multiple QR codes at once.
        </p>
        
        <form id="bulkUploadForm" enctype="multipart/form-data">
            <div class="form-group">
                <label>Campaign (Optional)</label>
                <select name="campaign_id" id="campaignId" class="form-select">
                    <option value="">No Campaign</option>
                    <?php if (!empty($campaigns)): ?>
                        <?php foreach ($campaigns as $campaign): ?>
                            <option value="<?= $campaign['id'] ?>">
                                <?= htmlspecialchars($campaign['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>CSV File *</label>
                <input type="file" name="csv_file" id="csvFile" accept=".csv,.txt" required class="form-control">
                <small style="color: var(--text-secondary); margin-top: 5px; display: block;">
                    First column should contain URLs or text. First row will be treated as headers.
                </small>
            </div>
            
            <!-- Sample CSV Download Section -->
            <div class="sample-download-section" style="margin: 20px 0; padding: 15px; background: rgba(87, 96, 255, 0.1); border-radius: 10px; border: 1px solid rgba(87, 96, 255, 0.3);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px;">
                    <i class="fas fa-file-download" style="color: var(--purple); font-size: 18px;"></i>
                    <strong style="color: var(--text-primary);">Need a sample CSV file?</strong>
                </div>
                <div style="display: flex; gap: 10px; align-items: end;">
                    <div style="flex: 1;">
                        <label style="display: block; margin-bottom: 5px; color: var(--text-secondary); font-size: 13px;">Select Type</label>
                        <select id="sampleType" class="form-select">
                            <option value="url">URL / Website</option>
                            <option value="text">Plain Text</option>
                            <option value="email">Email Address</option>
                            <option value="location">Location</option>
                            <option value="phone">Phone Number</option>
                            <option value="sms">SMS Message</option>
                            <option value="whatsapp">WhatsApp</option>
                            <option value="skype">Skype</option>
                            <option value="zoom">Zoom</option>
                            <option value="wifi">WiFi Network</option>
                            <option value="vcard">vCard (Contact)</option>
                            <option value="event">Event (Calendar)</option>
                            <option value="paypal">PayPal</option>
                            <option value="payment">Payment (UPI)</option>
                        </select>
                    </div>
                    <button type="button" class="btn-primary" style="padding: 10px 20px;" onclick="downloadSample()">
                        <i class="fas fa-download"></i> Download Sample
                    </button>
                </div>
            </div>
            
            <div class="form-actions" style="margin-top: 20px;">
                <button type="submit" class="btn-primary" id="uploadBtn">
                    <i class="fas fa-upload"></i> Upload & Preview
                </button>
            </div>
        </form>
        
        <div id="uploadProgress" style="display: none; margin-top: 20px;">
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
            <p id="progressText" style="text-align: center; margin-top: 10px; color: var(--text-secondary);"></p>
        </div>
    </div>
    
    <?php if (!empty($jobs)): ?>
        <div class="jobs-section">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-lg); flex-wrap: wrap; gap: var(--space-md);">
                <h4 style="margin-bottom: 0; color: var(--text-primary); font-size: var(--font-lg);">
                    <i class="fas fa-history"></i> Recent Jobs
                </h4>
                <div style="display: flex; gap: var(--space-sm);">
                    <select id="filterJobStatus" class="form-select" onchange="filterJobs()" style="width: auto; min-width: 10rem; padding: 0.625rem 1rem; font-size: var(--font-sm);">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="processing">Processing</option>
                        <option value="completed">Completed</option>
                        <option value="failed">Failed</option>
                    </select>
                </div>
            </div>
            
            <div class="jobs-list" id="jobsList"><?php foreach ($jobs as $job): ?>
                <div class="job-card glass-card"
                     data-status="<?= $job['status'] ?>"
                     data-date="<?= strtotime($job['created_at']) ?>"
                     data-campaign="<?= $job['campaign_name'] ?? '' ?>">
                    <div class="job-header">
                        <div>
                            <strong>Job #<?= $job['id'] ?></strong>
                            <?php if ($job['campaign_name']): ?>
                                <span class="job-campaign">
                                    <i class="fas fa-bullhorn"></i> <?= htmlspecialchars($job['campaign_name']) ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <span class="job-status status-<?= $job['status'] ?>">
                            <?= ucfirst($job['status']) ?>
                        </span>
                    </div>
                    
                    <div class="job-stats">
                        <div class="stat">
                            <span class="stat-label">Total:</span>
                            <span class="stat-value"><?= $job['total_count'] ?></span>
                        </div>
                        <div class="stat">
                            <span class="stat-label">Completed:</span>
                            <span class="stat-value"><?= $job['completed_count'] ?></span>
                        </div>
                        <?php if ($job['failed_count'] > 0): ?>
                            <div class="stat">
                                <span class="stat-label">Failed:</span>
                                <span class="stat-value" style="color: #ff4757;"><?= $job['failed_count'] ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="job-date">
                        <i class="fas fa-clock"></i>
                        <?= date('M j, Y g:i A', strtotime($job['created_at'])) ?>
                    </div>
                    
                    <?php if ($job['status'] === 'completed' && !empty($job['file_path'])): ?>
                        <a href="<?= $job['file_path'] ?>" class="btn-primary btn-sm" download>
                            <i class="fas fa-download"></i> Download ZIP
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            </div>
            <div id="noJobsResult" style="display: none; text-align: center; padding: var(--space-2xl); color: var(--text-secondary);">
                <i class="fas fa-search" style="font-size: 3rem; opacity: 0.5; margin-bottom: var(--space-md);"></i>
                <p>No jobs found matching your criteria.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
function filterJobs() {
    const statusFilter = document.getElementById('filterJobStatus').value.toLowerCase();
    const cards = document.querySelectorAll('.job-card');
    let visibleCount = 0;
    
    cards.forEach(card => {
        const status = card.getAttribute('data-status');
        const matchesStatus = !statusFilter || status === statusFilter;
        
        if (matchesStatus) {
            card.style.display = 'flex';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });
    
    // Show/hide no results message
    const noResults = document.getElementById('noJobsResult');
    const jobsList = document.getElementById('jobsList');
    if (visibleCount === 0) {
        jobsList.style.display = 'none';
        noResults.style.display = 'block';
    } else {
        jobsList.style.display = 'flex';
        noResults.style.display = 'none';
    }
}
</script>

<style>
.bulk-upload-section {
    padding: 1.5625rem; /* 25px to rem */
}

.jobs-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
}

.job-card {
    padding: var(--space-lg);
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.job-card:hover {
    transform: translateY(-0.125rem);
}

.job-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: var(--space-sm);
}

.job-header strong {
    color: var(--text-primary);
    font-size: var(--font-md);
}

.job-campaign {
    display: inline-block;
    margin-left: var(--space-sm);
    padding: 0.25rem 0.625rem; /* 4px 10px to rem */
    background: rgba(87, 96, 255, 0.1);
    border-radius: 0.75rem; /* 12px to rem */
    font-size: var(--font-xs);
    color: var(--purple);
}

.job-status {
    padding: 0.25rem 0.75rem; /* 4px 12px to rem */
    border-radius: 0.75rem;
    font-size: var(--font-xs);
    font-weight: 600;
    white-space: nowrap;
}

.status-pending {
    background: rgba(255, 159, 64, 0.2);
    color: #ff9f40;
}

.status-processing {
    background: rgba(87, 96, 255, 0.2);
    color: var(--purple);
}

.status-completed {
    background: rgba(46, 213, 115, 0.2);
    color: #2ed573;
}

.status-failed {
    background: rgba(255, 71, 87, 0.2);
    color: #ff4757;
}

.job-stats {
    display: flex;
    gap: var(--space-lg);
    flex-wrap: wrap;
}

.job-stats .stat {
    display: flex;
    flex-direction: column;
    gap: 0.25rem; /* 4px to rem */
}

.stat-label {
    color: var(--text-secondary);
    font-size: var(--font-xs);
}

.stat-value {
    color: var(--text-primary);
    font-size: var(--font-lg);
    font-weight: 600;
}

.job-date {
    color: var(--text-secondary);
    font-size: 0.8125rem; /* 13px to rem */
}

.progress-bar {
    width: 100%;
    height: 0.5rem; /* 8px to rem */
    background: rgba(255, 255, 255, 0.1);
    border-radius: 0.25rem; /* 4px to rem */
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(135deg, var(--purple), var(--cyan));
    width: 0%;
    transition: width 0.3s ease;
}

/* Responsive Styles */
@media (max-width: 48rem) { /* 768px to rem */
    .bulk-upload-section {
        padding: var(--space-lg);
    }
    
    .sample-download-section {
        padding: var(--space-md);
    }
    
    .sample-download-section > div:last-child {
        flex-direction: column;
    }
    
    .sample-download-section > div:last-child > div {
        width: 100%;
    }
    
    .job-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }
    
    .job-stats {
        flex-wrap: wrap;
    }
}

@media (max-width: 480px) {
    .glass-card {
        padding: 15px;
    }
    
    .jobs-list {
        gap: 10px;
    }
    
    .job-card {
        padding: 15px;
    }
    
    .form-select, .form-control {
        font-size: 14px;
    }
}
</style>

<script>
let currentJobId = null;

// Download sample CSV function
function downloadSample() {
    const sampleType = document.getElementById('sampleType').value;
    window.location.href = `/projects/qr/bulk/sample?type=${sampleType}`;
}

document.getElementById('bulkUploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const uploadBtn = document.getElementById('uploadBtn');
    const progressDiv = document.getElementById('uploadProgress');
    const progressFill = document.getElementById('progressFill');
    const progressText = document.getElementById('progressText');
    
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
    progressDiv.style.display = 'block';
    progressFill.style.width = '30%';
    progressText.textContent = 'Uploading file...';
    
    try {
        const response = await fetch('/projects/qr/bulk/upload', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            currentJobId = data.job_id;
            progressFill.style.width = '60%';
            progressText.textContent = `File uploaded. ${data.total} rows found. Processing...`;
            
            // Start generation
            await generateBulk(data.job_id);
        } else {
            alert(data.message || 'Upload failed');
            resetForm();
        }
    } catch (error) {
        alert('Error uploading file');
        console.error(error);
        resetForm();
    }
});

async function generateBulk(jobId) {
    const progressFill = document.getElementById('progressFill');
    const progressText = document.getElementById('progressText');
    
    try {
        const formData = new FormData();
        formData.append('job_id', jobId);
        
        const response = await fetch('/projects/qr/bulk/generate', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            progressFill.style.width = '100%';
            progressText.textContent = `✓ Complete! Generated ${data.completed} QR codes successfully.`;
            
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            alert(data.message || 'Generation failed');
            resetForm();
        }
    } catch (error) {
        alert('Error generating QR codes');
        console.error(error);
        resetForm();
    }
}

function resetForm() {
    const uploadBtn = document.getElementById('uploadBtn');
    const progressDiv = document.getElementById('uploadProgress');
    
    uploadBtn.disabled = false;
    uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Upload & Preview';
    progressDiv.style.display = 'none';
    document.getElementById('bulkUploadForm').reset();
}
</script>
