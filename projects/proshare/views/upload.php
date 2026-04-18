<?php use Core\View; use Core\Security; ?>
<?php View::extend('proshare:app'); ?>

<?php View::section('content'); ?>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-cloud-upload-alt"></i> Upload Files
        </h3>
        <span class="text-muted" style="font-size: 0.8rem;">Max 500 MB per file</span>
    </div>
    
    <!-- Upload Zone -->
    <div id="uploadZone" style="padding: 60px 20px; text-align: center; border: 2px dashed var(--border-color); border-radius: 12px; cursor: pointer; transition: all 0.3s ease; margin: 1rem;">
        <i class="fas fa-cloud-upload-alt" style="font-size: 3.5rem; color: var(--cyan); margin-bottom: 16px; display: block;"></i>
        <div style="font-size: 1.1rem; color: var(--text-primary); margin-bottom: 8px; font-weight: 600;">
            Drag &amp; drop files here or <span style="color: var(--cyan); text-decoration: underline;">browse</span>
        </div>
        <div class="text-muted" style="font-size: 0.85rem;">
            Supports: Images, PDF, ZIP, Documents, Videos, Audio &mdash; up to 500 MB
        </div>
        <input type="file" id="fileInput" multiple style="display: none;">
    </div>
</div>

<!-- Options Panel -->
<div class="card mb-3" id="optionsPanel" style="display: none;">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-cog"></i> Upload Options
        </h3>
        <button onclick="resetUpload()" class="btn btn-secondary" style="padding: 6px 14px; font-size: 0.8rem;">
            <i class="fas fa-arrow-left"></i> Change Files
        </button>
    </div>
    
    <div id="selectedFilesList" style="margin-bottom: 1.25rem; padding: 0 0.25rem;"></div>

    <form id="uploadForm">
        <input type="hidden" name="_csrf_token" value="<?= Security::generateCsrfToken() ?>">
        
        <div class="ps-grid ps-grid-2" style="margin-bottom: 1rem;">
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-clock"></i> Link Expiry
                </label>
                <select name="expiry" class="form-control">
                    <option value="1">1 Hour</option>
                    <option value="6">6 Hours</option>
                    <option value="24" selected>24 Hours</option>
                    <option value="168">7 Days</option>
                    <option value="720">30 Days</option>
                    <option value="0">Never</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <i class="fas fa-download"></i> Max Downloads
                </label>
                <select name="max_downloads" class="form-control">
                    <option value="">Unlimited</option>
                    <option value="1">1 Download</option>
                    <option value="5">5 Downloads</option>
                    <option value="10">10 Downloads</option>
                    <option value="50">50 Downloads</option>
                    <option value="100">100 Downloads</option>
                </select>
            </div>
        </div>
        
        <div class="form-group">
            <label class="form-label">
                <i class="fas fa-lock"></i> Password Protection <span class="text-muted">(optional)</span>
            </label>
            <input type="password" name="password" class="form-control" placeholder="Leave empty for no password">
        </div>
        
        <div class="ps-grid ps-grid-2" style="margin-bottom: 1rem;">
            <div class="form-group" style="margin-bottom: 0;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 12px 16px; background: var(--bg-secondary); border-radius: 8px; border: 1px solid var(--border-color);">
                    <input type="checkbox" name="self_destruct" value="1" style="width: 16px; height: 16px; accent-color: var(--cyan);">
                    <span><i class="fas fa-fire" style="color: var(--ps-danger);"></i> Self-destruct after first download</span>
                </label>
            </div>
            
            <div class="form-group" style="margin-bottom: 0;">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer; padding: 12px 16px; background: var(--bg-secondary); border-radius: 8px; border: 1px solid var(--border-color);">
                    <input type="checkbox" name="compression" value="1" checked style="width: 16px; height: 16px; accent-color: var(--cyan);">
                    <span><i class="fas fa-compress" style="color: var(--cyan);"></i> Enable compression</span>
                </label>
            </div>
        </div>
        
        <!-- Progress Bar -->
        <div id="progressBar" style="display: none; margin: 1.25rem 0;">
            <div style="height: 10px; background: var(--bg-secondary); border-radius: 999px; overflow: hidden; border: 1px solid var(--border-color);">
                <div id="progressFill" style="height: 100%; background: linear-gradient(90deg, var(--cyan), var(--ps-secondary)); width: 0%; transition: width 0.3s;"></div>
            </div>
            <div id="progressText" class="text-muted" style="font-size: 0.85rem; margin-top: 6px;">
                Preparing upload…
            </div>
        </div>
        
        <button type="submit" id="uploadBtn" class="btn btn-primary">
            <i class="fas fa-upload"></i> Upload Files
        </button>
    </form>
</div>

<!-- Result Panel -->
<div class="card" id="resultPanel" style="display: none;">
    <div style="padding: 2rem; text-align: center;">
        <div id="resultIcon" style="font-size: 3.5rem; margin-bottom: 1rem;"></div>
        <h3 id="resultHeading" style="margin-bottom: 1.25rem;"></h3>
        
        <div id="resultLinks" style="margin-top: 1.25rem; text-align: left;"></div>
        
        <div style="margin-top: 1.5rem; display: flex; gap: 12px; justify-content: center; flex-wrap: wrap;">
            <a href="/projects/proshare/dashboard" class="btn btn-primary">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <button onclick="resetUpload()" class="btn btn-secondary">
                <i class="fas fa-upload"></i> Upload More
            </button>
        </div>
    </div>
</div>

<?php View::endSection(); ?>

<?php View::section('scripts'); ?>
<script>
    const uploadZone    = document.getElementById('uploadZone');
    const fileInput     = document.getElementById('fileInput');
    const optionsPanel  = document.getElementById('optionsPanel');
    const uploadForm    = document.getElementById('uploadForm');
    const progressBar   = document.getElementById('progressBar');
    const progressFill  = document.getElementById('progressFill');
    const progressText  = document.getElementById('progressText');
    const resultPanel   = document.getElementById('resultPanel');
    const resultLinks   = document.getElementById('resultLinks');
    const resultIcon    = document.getElementById('resultIcon');
    const resultHeading = document.getElementById('resultHeading');
    const uploadBtn     = document.getElementById('uploadBtn');
    
    let selectedFiles = [];
    
    // Drag and drop handlers
    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.style.borderColor = 'var(--cyan)';
        uploadZone.style.background  = 'rgba(0, 240, 255, 0.04)';
    });
    
    uploadZone.addEventListener('dragleave', () => {
        uploadZone.style.borderColor = 'var(--border-color)';
        uploadZone.style.background  = 'transparent';
    });
    
    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.style.borderColor = 'var(--border-color)';
        uploadZone.style.background  = 'transparent';
        selectedFiles = Array.from(e.dataTransfer.files);
        showOptions();
    });
    
    uploadZone.addEventListener('click', () => fileInput.click());
    
    fileInput.addEventListener('change', (e) => {
        selectedFiles = Array.from(e.target.files);
        showOptions();
    });
    
    function showOptions() {
        if (selectedFiles.length === 0) return;
        
        optionsPanel.style.display = 'block';
        uploadZone.closest('.card').style.display = 'none';
        
        const filesList = document.getElementById('selectedFilesList');
        filesList.innerHTML = `
            <div style="font-weight: 600; color: var(--text-secondary); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 8px;">
                <i class="fas fa-paperclip"></i> Selected Files (${selectedFiles.length})
            </div>
            ${selectedFiles.map(f => `
                <div style="padding: 10px 14px; background: var(--bg-secondary); border-radius: 8px; margin-bottom: 6px; display: flex; align-items: center; gap: 10px; border: 1px solid var(--border-color);">
                    <i class="fas fa-file" style="color: var(--cyan); flex-shrink: 0;"></i>
                    <span style="flex: 1; font-size: 0.875rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">${f.name}</span>
                    <span class="text-muted" style="font-size: 0.8rem; flex-shrink: 0;">${(f.size / 1024 / 1024).toFixed(2)} MB</span>
                </div>
            `).join('')}
        `;
    }
    
    uploadForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        if (selectedFiles.length === 0) {
            alert('Please select files to upload');
            return;
        }
        
        uploadBtn.disabled = true;
        progressBar.style.display = 'block';
        
        const formData = new FormData(uploadForm);
        const results  = [];
        
        for (let i = 0; i < selectedFiles.length; i++) {
            const file = selectedFiles[i];
            progressText.textContent = `Uploading ${file.name} (${i + 1} / ${selectedFiles.length})…`;
            
            const fd = new FormData();
            fd.append('file',          file);
            fd.append('_csrf_token',   formData.get('_csrf_token'));
            fd.append('expiry',        formData.get('expiry'));
            fd.append('max_downloads', formData.get('max_downloads') || '');
            fd.append('password',      formData.get('password') || '');
            fd.append('self_destruct', formData.get('self_destruct') || '0');
            fd.append('compression',   formData.get('compression') || '0');
            
            try {
                const response = await fetch('/projects/proshare/upload', {
                    method: 'POST',
                    headers: { 'Accept': 'application/json' },
                    body: fd
                });
                
                const text = await response.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch (_) {
                    throw new Error('Server returned an unexpected response. Please try again.');
                }
                
                if (data.success) {
                    results.push({ name: file.name, link: data.share_link || data.share_url, short_code: data.short_code });
                } else {
                    results.push({ name: file.name, error: data.error || 'Upload failed' });
                }
            } catch (err) {
                results.push({ name: file.name, error: err.message || 'Upload failed' });
            }
            
            const pct = Math.round(((i + 1) / selectedFiles.length) * 100);
            progressFill.style.width = pct + '%';
        }
        
        // Show results
        optionsPanel.style.display = 'none';
        resultPanel.style.display  = 'block';
        
        const successCount = results.filter(r => !r.error).length;
        const failCount    = results.filter(r =>  r.error).length;
        
        if (failCount === 0) {
            resultIcon.innerHTML    = '<i class="fas fa-check-circle" style="color: var(--green);"></i>';
            resultHeading.style.color = 'var(--green)';
            resultHeading.textContent = successCount === 1 ? 'Upload Successful!' : `${successCount} Files Uploaded!`;
        } else if (successCount === 0) {
            resultIcon.innerHTML    = '<i class="fas fa-times-circle" style="color: var(--ps-danger);"></i>';
            resultHeading.style.color = 'var(--ps-danger)';
            resultHeading.textContent = 'Upload Failed';
        } else {
            resultIcon.innerHTML    = '<i class="fas fa-exclamation-triangle" style="color: var(--ps-warning);"></i>';
            resultHeading.style.color = 'var(--ps-warning)';
            resultHeading.textContent = `${successCount} Uploaded, ${failCount} Failed`;
        }
        
        resultLinks.innerHTML = results.map(r => r.error
            ? `<div style="background: rgba(255,107,107,0.06); border: 1px solid rgba(255,107,107,0.3); border-radius: 8px; padding: 16px; margin-bottom: 12px;">
                   <div style="font-weight: 600; color: var(--ps-danger); margin-bottom: 6px;"><i class="fas fa-exclamation-triangle"></i> ${r.name}</div>
                   <div style="color: var(--ps-danger); font-size: 0.875rem;">${r.error}</div>
               </div>`
            : `<div style="background: rgba(0,240,255,0.04); border: 1px solid var(--border-color); border-radius: 8px; padding: 16px; margin-bottom: 12px;">
                   <div style="font-weight: 600; color: var(--text-primary); margin-bottom: 10px;"><i class="fas fa-file" style="color: var(--cyan);"></i> ${r.name}</div>
                   <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                       <input type="text" value="${r.link}" readonly class="form-control" style="flex: 1; min-width: 0; font-size: 0.85rem;">
                       <button onclick="copyToClipboard('${r.link}', this)" class="btn btn-secondary" style="white-space: nowrap; padding: 6px 14px;">
                           <i class="fas fa-copy"></i> Copy
                       </button>
                       <a href="${r.link}" target="_blank" class="btn btn-secondary" style="white-space: nowrap; padding: 6px 14px;">
                           <i class="fas fa-external-link-alt"></i> Open
                       </a>
                   </div>
               </div>`
        ).join('');
    });
    
    function copyToClipboard(text, btn) {
        navigator.clipboard.writeText(text).then(() => {
            const orig = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            setTimeout(() => { btn.innerHTML = orig; }, 2000);
        });
    }
    
    function resetUpload() {
        selectedFiles = [];
        fileInput.value = '';
        uploadForm.reset();
        optionsPanel.style.display = 'none';
        resultPanel.style.display  = 'none';
        uploadZone.closest('.card').style.display = 'block';
        progressBar.style.display  = 'none';
        progressFill.style.width   = '0%';
        uploadBtn.disabled = false;
        document.getElementById('selectedFilesList').innerHTML = '';
    }
</script>
<?php View::endSection(); ?>
