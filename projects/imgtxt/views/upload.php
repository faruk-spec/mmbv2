<?php use Core\View; ?>
<?php View::extend('imgtxt'); ?>

<?php View::section('styles'); ?>
<style>
    .upload-container {
        max-width: 800px;
        margin: 0 auto;
    }
    .upload-area {
        background: var(--bg-card);
        border: 3px dashed var(--green);
        border-radius: 12px;
        padding: 60px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        margin-bottom: 30px;
    }
    
    .upload-area:hover,
    .upload-area.dragover {
        background: var(--bg-secondary);
        border-color: var(--cyan);
    }
    
    .upload-icon {
        font-size: 4em;
        margin-bottom: 20px;
    }
    
    .upload-text {
        font-size: 1.2em;
        color: var(--green);
        margin-bottom: 10px;
    }
    
    .upload-subtext {
        color: var(--text-secondary);
        font-size: 0.9em;
    }
    
    .file-info {
        background: var(--bg-card);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
        display: none;
        border: 1px solid var(--border-color);
    }
    
    .file-info.show {
        display: block;
    }
    
    .file-info h3 {
        color: var(--green);
        margin-bottom: 15px;
    }
    
    .file-list {
        margin-bottom: 15px;
    }
    
    .file-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 10px;
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        margin-bottom: 8px;
    }
    
    .file-item-name {
        flex: 1;
        margin-right: 10px;
    }
    
    .file-item-remove {
        background: var(--red);
        color: white;
        border: none;
        padding: 5px 10px;
        border-radius: 4px;
        cursor: pointer;
        font-size: 12px;
    }
    
    .file-item-remove:hover {
        opacity: 0.8;
    }
    
    .progress-container {
        background: var(--bg-card);
        border-radius: 12px;
        padding: 20px;
        margin-top: 20px;
        display: none;
        border: 1px solid var(--border-color);
    }
    
    .progress-container.show {
        display: block;
    }
    
    .progress-container h3 {
        color: var(--green);
        margin-bottom: 15px;
    }
    
    .progress-bar {
        width: 100%;
        height: 30px;
        background: var(--bg-secondary);
        border-radius: 15px;
        overflow: hidden;
        margin-bottom: 10px;
    }
    
    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--green), var(--cyan));
        width: 0%;
        transition: width 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--bg-primary);
        font-weight: bold;
    }
    
    .result-container {
        background: var(--bg-card);
        border-radius: 12px;
        padding: 20px;
        margin-top: 20px;
        display: none;
        border: 1px solid var(--border-color);
    }
    
    .result-container.show {
        display: block;
    }
    
    .result-container h3 {
        color: var(--green);
        margin-bottom: 15px;
    }
    
    .result-box {
        background: var(--bg-card);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .result-box-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid var(--green);
    }
    
    .result-box-title {
        color: var(--green);
        font-weight: 600;
        font-size: 1.1rem;
    }
    
    .result-box-actions {
        display: flex;
        gap: 8px;
    }
    
    .result-text {
        background: var(--bg-secondary);
        border: 1px solid var(--border-color);
        border-radius: 8px;
        padding: 15px;
        max-height: 400px;
        overflow-y: auto;
        white-space: pre-wrap;
        word-wrap: break-word;
        margin-bottom: 15px;
    }
    
    .btn-group {
        display: flex;
        gap: 10px;
    }
    
    /* Mobile Responsive */
    @media (max-width: 768px) {
        .upload-area {
            padding: 40px 15px;
        }
        
        .upload-icon {
            font-size: 3em;
        }
        
        .btn-group {
            flex-direction: column;
        }
    }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>

<div class="upload-container">
    <div class="upload-area" id="uploadArea">
        <div class="upload-icon">📁</div>
        <div class="upload-text">Click to upload or drag and drop</div>
        <div class="upload-subtext">Supports JPG, PNG, GIF, PDF (Max 10MB per file) - Multiple files allowed</div>
        <input type="file" id="fileInput" accept="image/*,.pdf" multiple style="display: none;">
    </div>
    
    <div class="file-info" id="fileInfo">
        <h3>Selected Files</h3>
        <div class="file-list" id="fileList"></div>
        
        <div class="form-group">
            <label for="language">Language:</label>
            <select id="language" class="form-control">
                <?php
                $languages = [
                    'eng' => 'English',
                    'spa' => 'Spanish',
                    'fra' => 'French',
                    'deu' => 'German',
                    'ita' => 'Italian',
                    'por' => 'Portuguese',
                    'rus' => 'Russian',
                    'chi_sim' => 'Chinese (Simplified)',
                    'jpn' => 'Japanese',
                    'ara' => 'Arabic'
                ];
                $defaultLang = $settings['default_language'] ?? 'eng';
                foreach ($languages as $code => $name) {
                    $selected = ($code === $defaultLang) ? 'selected' : '';
                    echo "<option value=\"{$code}\" {$selected}>{$name}</option>";
                }
                ?>
            </select>
        </div>
        
        <button class="btn btn-primary" style="width: 100%;" onclick="processOCR()">Extract Text</button>
    </div>
    
    <div class="progress-container" id="progressContainer">
        <h3>Processing...</h3>
        <div class="progress-bar">
            <div class="progress-fill" id="progressFill">0%</div>
        </div>
        <p id="progressText" style="text-align: center; color: var(--text-secondary);">Uploading file...</p>
    </div>
    
    <div class="result-container" id="resultContainer">
        <h3>Extracted Text</h3>
        <div id="resultsWrapper"></div>
        <div class="btn-group">
            <button class="btn btn-primary" onclick="downloadAllResults()">Download All Text</button>
            <button class="btn btn-secondary" onclick="resetForm()">Process Another</button>
        </div>
    </div>
</div>

<?php View::endSection(); ?>

<?php View::section('scripts'); ?>
    <script>
        let selectedFiles = [];
        let processedResults = [];
        
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const fileInfo = document.getElementById('fileInfo');
        const fileList = document.getElementById('fileList');
        const progressContainer = document.getElementById('progressContainer');
        const progressFill = document.getElementById('progressFill');
        const progressText = document.getElementById('progressText');
        const resultContainer = document.getElementById('resultContainer');
        const resultText = document.getElementById('resultText');
        
        // Click to upload
        uploadArea.addEventListener('click', () => fileInput.click());
        
        // File selection
        fileInput.addEventListener('change', function(e) {
            handleFiles(Array.from(e.target.files));
        });
        
        // Drag and drop
        uploadArea.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });
        
        uploadArea.addEventListener('dragleave', function() {
            uploadArea.classList.remove('dragover');
        });
        
        uploadArea.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            handleFiles(Array.from(e.dataTransfer.files));
        });
        
        function handleFiles(files) {
            for (const file of files) {
                // Validate file size
                if (file.size > 10485760) {
                    alert(`File ${file.name} exceeds 10MB limit`);
                    continue;
                }
                
                // Validate file type
                const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
                if (!allowedTypes.includes(file.type)) {
                    alert(`File ${file.name} is not a valid type. Please upload JPG, PNG, GIF, or PDF`);
                    continue;
                }
                
                selectedFiles.push(file);
            }
            
            if (selectedFiles.length > 0) {
                updateFileList();
                fileInfo.classList.add('show');
                uploadArea.style.display = 'none';
            }
        }
        
        function updateFileList() {
            fileList.innerHTML = '';
            selectedFiles.forEach((file, index) => {
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.innerHTML = `
                    <div class="file-item-name">${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)</div>
                    <button class="file-item-remove" onclick="removeFile(${index})">Remove</button>
                `;
                fileList.appendChild(fileItem);
            });
        }
        
        function removeFile(index) {
            selectedFiles.splice(index, 1);
            if (selectedFiles.length === 0) {
                resetForm();
            } else {
                updateFileList();
            }
        }
        
        async function processOCR() {
            if (selectedFiles.length === 0) return;
            
            fileInfo.style.display = 'none';
            progressContainer.classList.add('show');
            processedResults = [];
            
            const language = document.getElementById('language').value;
            const totalFiles = selectedFiles.length;
            
            for (let i = 0; i < totalFiles; i++) {
                const file = selectedFiles[i];
                progressText.textContent = `Processing file ${i + 1} of ${totalFiles}: ${file.name}`;
                
                try {
                    // Upload file
                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('language', language);
                    
                    updateProgress(((i / totalFiles) * 100));
                    
                    const uploadResponse = await fetch('/projects/imgtxt/upload', {
                        method: 'POST',
                        body: formData
                    });
                    
                    // Check if response is ok
                    if (!uploadResponse.ok) {
                        const text = await uploadResponse.text();
                        throw new Error(`Server error (${uploadResponse.status}): ${text.substring(0, 100)}`);
                    }
                    
                    // Get response text first
                    const uploadText = await uploadResponse.text();
                    
                    // Try to parse JSON
                    let uploadData;
                    try {
                        uploadData = JSON.parse(uploadText);
                    } catch (e) {
                        console.error('Invalid JSON response:', uploadText.substring(0, 500));
                        throw new Error('Server returned invalid response. Please check if you are logged in and try again.');
                    }
                    
                    if (!uploadData.success) {
                        throw new Error(uploadData.error || 'Upload failed');
                    }
                    
                    const jobId = uploadData.job_id;
                    updateProgress(((i + 0.4) / totalFiles) * 100);
                    
                    // Process OCR
                    const processFormData = new FormData();
                    processFormData.append('job_id', jobId);
                    
                    const processResponse = await fetch('/projects/imgtxt/process', {
                        method: 'POST',
                        body: processFormData
                    });
                    
                    // Check if response is ok
                    if (!processResponse.ok) {
                        const text = await processResponse.text();
                        throw new Error(`Processing error (${processResponse.status}): ${text.substring(0, 100)}`);
                    }
                    
                    // Get response text first
                    const processText = await processResponse.text();
                    
                    // Try to parse JSON
                    let processData;
                    try {
                        processData = JSON.parse(processText);
                    } catch (e) {
                        console.error('Invalid JSON response:', processText.substring(0, 500));
                        throw new Error('Server returned invalid response during processing. Check browser console for details.');
                    }
                    
                    if (!processData.success) {
                        throw new Error(processData.error || 'OCR processing failed');
                    }
                    
                    processedResults.push({
                        filename: file.name,
                        text: processData.text,
                        jobId: jobId
                    });
                    
                } catch (error) {
                    processedResults.push({
                        filename: file.name,
                        text: `Error processing ${file.name}: ${error.message}`,
                        error: true
                    });
                }
            }
            
            updateProgress(100);
            progressText.textContent = 'Complete!';
            
            // Show results
            setTimeout(() => {
                progressContainer.classList.remove('show');
                displayResults();
                resultContainer.classList.add('show');
            }, 500);
        }
        
        function displayResults() {
            const wrapper = document.getElementById('resultsWrapper');
            wrapper.innerHTML = '';
            
            processedResults.forEach((result, index) => {
                const resultBox = document.createElement('div');
                resultBox.className = 'result-box';
                
                const header = document.createElement('div');
                header.className = 'result-box-header';
                
                const title = document.createElement('div');
                title.className = 'result-box-title';
                title.textContent = result.filename;
                
                const actions = document.createElement('div');
                actions.className = 'result-box-actions';
                
                const copyBtn = document.createElement('button');
                copyBtn.className = 'btn btn-sm';
                copyBtn.innerHTML = '<i class="fas fa-copy"></i> Copy';
                copyBtn.onclick = () => copyText(result.text);
                
                const downloadBtn = document.createElement('button');
                downloadBtn.className = 'btn btn-sm btn-primary';
                downloadBtn.innerHTML = '<i class="fas fa-download"></i> Download';
                downloadBtn.onclick = () => downloadSingleResult(result.text, result.filename);
                
                actions.appendChild(copyBtn);
                actions.appendChild(downloadBtn);
                
                header.appendChild(title);
                header.appendChild(actions);
                
                const textPre = document.createElement('pre');
                textPre.style.cssText = 'background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 8px; padding: 15px; max-height: 400px; overflow-y: auto; white-space: pre-wrap; word-wrap: break-word; color: var(--text-primary); font-family: "Courier New", monospace; font-size: 14px; line-height: 1.6;';
                textPre.textContent = result.text;
                
                resultBox.appendChild(header);
                resultBox.appendChild(textPre);
                
                wrapper.appendChild(resultBox);
            });
        }
        
        function copyText(text) {
            navigator.clipboard.writeText(text).then(() => {
                alert('Text copied to clipboard!');
            });
        }
        
        function downloadSingleResult(text, filename) {
            const blob = new Blob([text], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            const cleanName = filename.replace(/\.[^/.]+$/, '');
            a.download = cleanName + '_extracted.txt';
            a.click();
            URL.revokeObjectURL(url);
        }
        
        function downloadAllResults() {
            let combinedText = '';
            processedResults.forEach((result, index) => {
                if (index > 0) combinedText += '\n\n' + '='.repeat(80) + '\n\n';
                combinedText += `File: ${result.filename}\n`;
                combinedText += '─'.repeat(80) + '\n';
                combinedText += result.text;
            });
            
            const blob = new Blob([combinedText], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = 'all_extracted_text_' + Date.now() + '.txt';
            a.click();
            URL.revokeObjectURL(url);
        }
        
        function updateProgress(percent) {
            const rounded = Math.round(percent);
            progressFill.style.width = rounded + '%';
            progressFill.textContent = rounded + '%';
        }
        
        function resetForm() {
            selectedFiles = [];
            processedResults = [];
            fileInput.value = '';
            fileList.innerHTML = '';
            fileInfo.classList.remove('show');
            progressContainer.classList.remove('show');
            resultContainer.classList.remove('show');
            uploadArea.style.display = 'block';
            updateProgress(0);
        }
    </script>
<?php View::endSection(); ?>
