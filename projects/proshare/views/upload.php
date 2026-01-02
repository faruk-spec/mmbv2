<?php use Core\View; use Core\Security; ?>
<?php View::extend('proshare:app'); ?>

<?php View::section('content'); ?>

<div class="card mb-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-cloud-upload-alt"></i> Upload Files
        </h3>
    </div>
    
    <!-- Upload Zone -->
    <div id="uploadZone" style="padding: 60px 20px; text-align: center; border: 3px dashed var(--border-color); border-radius: 12px; cursor: pointer; transition: all 0.3s ease; margin: 24px;">
        <i class="fas fa-cloud-upload-alt" style="font-size: 4rem; color: var(--cyan); margin-bottom: 20px; display: block;"></i>
        <div style="font-size: 1.3rem; color: var(--text-primary); margin-bottom: 10px;">
            Drag & drop files here or click to browse
        </div>
        <div class="text-muted" style="font-size: 0.9rem;">
            Maximum file size: 500MB
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
    </div>
    
    <form id="uploadForm">
        <input type="hidden" name="csrf_token" value="<?= Security::generateToken() ?>">
        
        <div class="grid grid-2">
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
                <i class="fas fa-lock"></i> Password Protection (Optional)
            </label>
            <input type="password" name="password" class="form-control" placeholder="Leave empty for no password">
        </div>
        
        <div class="grid grid-2">
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="self_destruct" value="1">
                    <span><i class="fas fa-fire"></i> Self-destruct after first download</span>
                </label>
            </div>
            
            <div class="form-group">
                <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="compression" value="1" checked>
                    <span><i class="fas fa-compress"></i> Enable compression</span>
                </label>
            </div>
        </div>
        
        <!-- Progress Bar -->
        <div id="progressBar" style="display: none; margin: 20px 0;">
            <div style="height: 40px; background: rgba(12, 12, 18, 0.5); border-radius: 20px; overflow: hidden; border: 1px solid var(--border-color);">
                <div id="progressFill" style="height: 100%; background: linear-gradient(90deg, var(--cyan), var(--purple)); width: 0%; transition: width 0.3s; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600;">
                    0%
                </div>
            </div>
            <div id="progressText" class="text-center text-muted mt-1" style="font-size: 0.9rem;">
                Preparing upload...
            </div>
        </div>
        
        <button type="submit" id="uploadBtn" class="btn btn-primary">
            <i class="fas fa-upload"></i> Upload Files
        </button>
    </form>
</div>

<!-- Result Panel -->
<div class="card" id="resultPanel" style="display: none;">
    <div style="padding: 40px; text-align: center;">
        <i class="fas fa-check-circle" style="font-size: 4rem; color: var(--green); margin-bottom: 20px; display: block;"></i>
        <h3 style="color: var(--green); margin-bottom: 20px;">Upload Successful!</h3>
        
        <div id="resultLinks" style="margin-top: 30px;"></div>
        
        <div style="margin-top: 30px;">
            <a href="/projects/proshare/dashboard" class="btn btn-primary">
                <i class="fas fa-home"></i> Back to Dashboard
            </a>
            <button onclick="resetUpload()" class="btn btn-secondary">
                <i class="fas fa-upload"></i> Upload More Files
            </button>
        </div>
    </div>
</div>

<?php View::endSection(); ?>

<?php View::section('scripts'); ?>
<script>
    const uploadZone = document.getElementById('uploadZone');
    const fileInput = document.getElementById('fileInput');
    const optionsPanel = document.getElementById('optionsPanel');
    const uploadForm = document.getElementById('uploadForm');
    const progressBar = document.getElementById('progressBar');
    const progressFill = document.getElementById('progressFill');
    const progressText = document.getElementById('progressText');
    const resultPanel = document.getElementById('resultPanel');
    const resultLinks = document.getElementById('resultLinks');
    const uploadBtn = document.getElementById('uploadBtn');
    
    let selectedFiles = [];
    
    // Drag and drop
    uploadZone.addEventListener('dragover', (e) => {
        e.preventDefault();
        uploadZone.style.borderColor = 'var(--cyan)';
        uploadZone.style.background = 'rgba(0, 240, 255, 0.05)';
    });
    
    uploadZone.addEventListener('dragleave', () => {
        uploadZone.style.borderColor = 'var(--border-color)';
        uploadZone.style.background = 'transparent';
    });
    
    uploadZone.addEventListener('drop', (e) => {
        e.preventDefault();
        uploadZone.style.borderColor = 'var(--border-color)';
        uploadZone.style.background = 'transparent';
        
        selectedFiles = Array.from(e.dataTransfer.files);
        showOptions();
    });
    
    uploadZone.addEventListener('click', () => {
        fileInput.click();
    });
    
    fileInput.addEventListener('change', (e) => {
        selectedFiles = Array.from(e.target.files);
        showOptions();
    });
    
    function showOptions() {
        if (selectedFiles.length > 0) {
            optionsPanel.style.display = 'block';
            uploadZone.style.display = 'none';
            
            // Show selected files
            const filesInfo = selectedFiles.map(f => `<div style="padding: 8px; background: rgba(0, 240, 255, 0.05); border-radius: 6px; margin-bottom: 8px;"><i class="fas fa-file" style="color: var(--cyan); margin-right: 8px;"></i>${f.name} (${(f.size / 1024 / 1024).toFixed(2)} MB)</div>`).join('');
            
            if (!document.getElementById('selectedFilesList')) {
                const filesList = document.createElement('div');
                filesList.id = 'selectedFilesList';
                filesList.style.marginBottom = '20px';
                filesList.innerHTML = `
                    <div style="margin-bottom: 10px; font-weight: 600; color: var(--text-primary);">
                        <i class="fas fa-file-alt"></i> Selected Files (${selectedFiles.length})
                    </div>
                    ${filesInfo}
                `;
                uploadForm.insertBefore(filesList, uploadForm.firstChild);
            }
        }
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
        
        // Upload files one by one
        const results = [];
        for (let i = 0; i < selectedFiles.length; i++) {
            const file = selectedFiles[i];
            const fileFormData = new FormData();
            fileFormData.append('file', file);
            fileFormData.append('csrf_token', formData.get('csrf_token'));
            fileFormData.append('expiry', formData.get('expiry'));
            fileFormData.append('max_downloads', formData.get('max_downloads'));
            fileFormData.append('password', formData.get('password'));
            fileFormData.append('self_destruct', formData.get('self_destruct') || '0');
            fileFormData.append('compression', formData.get('compression') || '0');
            
            progressText.textContent = `Uploading ${file.name} (${i + 1}/${selectedFiles.length})...`;
            
            try {
                const response = await fetch('/projects/proshare/upload', {
                    method: 'POST',
                    body: fileFormData
                });
                
                // Check if response is ok before parsing
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                
                const data = await response.json();
                
                if (data.success) {
                    results.push({
                        name: file.name,
                        link: data.share_link,
                        short_code: data.short_code
                    });
                } else {
                    console.error('Upload failed for', file.name, ':', data.error);
                    results.push({
                        name: file.name,
                        link: null,
                        error: data.error || 'Upload failed',
                        short_code: null
                    });
                }
                
                const progress = ((i + 1) / selectedFiles.length) * 100;
                progressFill.style.width = progress + '%';
                progressFill.textContent = Math.round(progress) + '%';
            } catch (error) {
                console.error('Upload error:', error);
                results.push({
                    name: file.name,
                    link: null,
                    error: error.message || 'Upload failed',
                    short_code: null
                });
            }
        }
        
        // Show results
        optionsPanel.style.display = 'none';
        resultPanel.style.display = 'block';
        
        const linksHTML = results.map(r => {
            if (r.error) {
                return `
                    <div style="background: rgba(255, 0, 0, 0.05); border: 1px solid #ff4444; border-radius: 8px; padding: 20px; margin-bottom: 15px; text-align: left;">
                        <div style="font-weight: 600; margin-bottom: 10px; color: #ff4444;">
                            <i class="fas fa-exclamation-triangle"></i> ${r.name}
                        </div>
                        <div style="color: #ff4444;">
                            Error: ${r.error}
                        </div>
                    </div>
                `;
            }
            return `
                <div style="background: rgba(0, 240, 255, 0.05); border: 1px solid var(--border-color); border-radius: 8px; padding: 20px; margin-bottom: 15px; text-align: left;">
                    <div style="font-weight: 600; margin-bottom: 10px; color: var(--text-primary);">
                        <i class="fas fa-file"></i> ${r.name}
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <input type="text" value="${r.link}" readonly class="form-control" style="flex: 1;">
                        <button onclick="copyToClipboard('${r.link}')" class="btn btn-secondary" style="white-space: nowrap;">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                        <a href="${r.link}" target="_blank" class="btn btn-secondary" style="white-space: nowrap;">
                            <i class="fas fa-external-link-alt"></i> Open
                        </a>
                    </div>
                </div>
            `;
        }).join('');
        
        resultLinks.innerHTML = linksHTML;
    });
    
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Link copied to clipboard!');
        });
    }
    
    function resetUpload() {
        selectedFiles = [];
        fileInput.value = '';
        uploadForm.reset();
        optionsPanel.style.display = 'none';
        resultPanel.style.display = 'none';
        uploadZone.style.display = 'block';
        progressBar.style.display = 'none';
        progressFill.style.width = '0%';
        uploadBtn.disabled = false;
        
        const filesList = document.getElementById('selectedFilesList');
        if (filesList) {
            filesList.remove();
        }
    }
</script>
<?php View::endSection(); ?>
