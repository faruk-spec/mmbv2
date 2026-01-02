<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ProShare - Secure File Sharing</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #0f0f23;
            color: #fff;
            min-height: 100vh;
            padding: 20px;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        
        h1 {
            color: #00f0ff;
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .tagline {
            color: #888;
            font-size: 1.1em;
        }
        
        .upload-zone {
            background: #1a1a2e;
            border: 3px dashed #00f0ff;
            border-radius: 12px;
            padding: 60px 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s;
            margin-bottom: 30px;
        }
        
        .upload-zone:hover,
        .upload-zone.dragover {
            background: #16213e;
            border-color: #00d4dd;
            transform: scale(1.02);
        }
        
        .upload-icon {
            font-size: 4em;
            margin-bottom: 20px;
        }
        
        .upload-text {
            font-size: 1.3em;
            color: #00f0ff;
            margin-bottom: 10px;
        }
        
        .upload-subtext {
            color: #888;
        }
        
        .options-panel {
            background: #1a1a2e;
            border-radius: 8px;
            padding: 30px;
            margin-bottom: 20px;
            display: none;
        }
        
        .options-panel.show {
            display: block;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            color: #00f0ff;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        input, select {
            width: 100%;
            padding: 12px;
            background: #0f0f23;
            color: #fff;
            border: 1px solid #00f0ff;
            border-radius: 4px;
            font-size: 1em;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
            cursor: pointer;
        }
        
        .checkbox-group label {
            margin: 0;
            cursor: pointer;
            font-weight: normal;
        }
        
        .btn {
            background: #00f0ff;
            color: #0f0f23;
            border: none;
            padding: 15px 30px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.1em;
            width: 100%;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #00d4dd;
            transform: translateY(-2px);
        }
        
        .btn:disabled {
            background: #333;
            cursor: not-allowed;
            transform: none;
        }
        
        .progress-bar {
            height: 40px;
            background: #0f0f23;
            border-radius: 20px;
            overflow: hidden;
            margin: 20px 0;
            display: none;
        }
        
        .progress-bar.show {
            display: block;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #00f0ff, #00d4dd);
            width: 0%;
            transition: width 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #0f0f23;
            font-weight: bold;
        }
        
        .result-panel {
            background: #1a1a2e;
            border: 2px solid #0f0;
            border-radius: 8px;
            padding: 30px;
            text-align: center;
            display: none;
            animation: slideIn 0.3s;
        }
        
        .result-panel.show {
            display: block;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .share-url {
            background: #0f0f23;
            border: 1px solid #0f0;
            border-radius: 4px;
            padding: 15px;
            font-size: 1.1em;
            color: #0f0;
            margin: 20px 0;
            word-break: break-all;
        }
        
        .btn-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-top: 20px;
        }
        
        .btn-secondary {
            background: #333;
            color: #fff;
        }
        
        .btn-secondary:hover {
            background: #444;
        }
        
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin: 40px 0;
        }
        
        .feature-card {
            background: #1a1a2e;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
        }
        
        .feature-icon {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .feature-title {
            color: #00f0ff;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .feature-desc {
            color: #888;
            font-size: 0.9em;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            h1 {
                font-size: 2em;
            }
            
            .upload-zone {
                padding: 40px 15px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .btn-group {
                grid-template-columns: 1fr;
            }
            
            .features {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔒 ProShare</h1>
            <p class="tagline">Secure, Anonymous, Instant File Sharing</p>
            <?php if ($user): ?>
                <p style="color: #888; margin-top: 10px;">Welcome, <?= htmlspecialchars($user['name']) ?> | <a href="/projects/proshare/dashboard" style="color: #00f0ff;">Dashboard</a></p>
            <?php else: ?>
                <p style="color: #888; margin-top: 10px;">Share anonymously - No account required!</p>
            <?php endif; ?>
        </div>
        
        <div class="upload-zone" id="uploadZone">
            <div class="upload-icon">📁</div>
            <div class="upload-text">Drop files here or click to browse</div>
            <div class="upload-subtext">Max size: <?= round($maxSize / 1024 / 1024) ?>MB | All file types supported</div>
            <input type="file" id="fileInput" multiple style="display: none;">
        </div>
        
        <div class="options-panel" id="optionsPanel">
            <h2 style="color: #00f0ff; margin-bottom: 20px;">Share Options</h2>
            
            <div id="filesList" style="color: #888; margin-bottom: 20px;"></div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Expires in (hours):</label>
                    <select id="expiryHours">
                        <option value="1">1 hour</option>
                        <option value="6">6 hours</option>
                        <option value="24" selected>24 hours</option>
                        <option value="72">3 days</option>
                        <option value="168">7 days</option>
                        <option value="720">30 days</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Max downloads:</label>
                    <select id="maxDownloads">
                        <option value="">Unlimited</option>
                        <option value="1">1 download</option>
                        <option value="5">5 downloads</option>
                        <option value="10">10 downloads</option>
                        <option value="50">50 downloads</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Password (optional):</label>
                <input type="password" id="password" placeholder="Leave empty for no password">
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" id="selfDestruct">
                <label for="selfDestruct">🔥 Self-destruct after first view</label>
            </div>
            
            <div class="checkbox-group">
                <input type="checkbox" id="enableCompression" checked>
                <label for="enableCompression">📦 Compress file (faster upload/download)</label>
            </div>
            
            <div class="checkbox-group" style="display: none;">
                <input type="checkbox" id="enableEncryption" disabled>
                <label for="enableEncryption" style="color: #666;">🔐 Enable end-to-end encryption (Coming Soon)</label>
            </div>
            
            <button class="btn" onclick="uploadFiles()">Upload & Share</button>
        </div>
        
        <div class="progress-bar" id="progressBar">
            <div class="progress-fill" id="progressFill">0%</div>
        </div>
        
        <div class="result-panel" id="resultPanel">
            <h2 style="color: #0f0; margin-bottom: 15px;">✅ File Shared Successfully!</h2>
            <p style="color: #888; margin-bottom: 15px;">Share this link:</p>
            <div class="share-url" id="shareUrl"></div>
            <p style="color: #888; font-size: 0.9em;">Expires: <span id="expiresAt"></span></p>
            
            <div class="btn-group">
                <button class="btn" onclick="copyToClipboard()">📋 Copy Link</button>
                <button class="btn btn-secondary" onclick="resetForm()">Share Another</button>
            </div>
        </div>
        
        <div class="features">
            <div class="feature-card">
                <div class="feature-icon">🚀</div>
                <div class="feature-title">Instant Sharing</div>
                <div class="feature-desc">Upload and share files in seconds</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">🔒</div>
                <div class="feature-title">Secure</div>
                <div class="feature-desc">Password protection & encryption</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">👤</div>
                <div class="feature-title">Anonymous</div>
                <div class="feature-desc">No account required to share</div>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⏰</div>
                <div class="feature-title">Auto-Expire</div>
                <div class="feature-desc">Files automatically delete after expiry</div>
            </div>
        </div>
    </div>
    
    <script>
        let selectedFiles = [];
        
        const uploadZone = document.getElementById('uploadZone');
        const fileInput = document.getElementById('fileInput');
        const optionsPanel = document.getElementById('optionsPanel');
        const filesList = document.getElementById('filesList');
        const progressBar = document.getElementById('progressBar');
        const progressFill = document.getElementById('progressFill');
        const resultPanel = document.getElementById('resultPanel');
        const shareUrl = document.getElementById('shareUrl');
        const expiresAt = document.getElementById('expiresAt');
        
        uploadZone.addEventListener('click', () => fileInput.click());
        
        fileInput.addEventListener('change', function(e) {
            handleFiles(Array.from(e.target.files));
        });
        
        uploadZone.addEventListener('dragover', function(e) {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });
        
        uploadZone.addEventListener('dragleave', function() {
            uploadZone.classList.remove('dragover');
        });
        
        uploadZone.addEventListener('drop', function(e) {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            handleFiles(Array.from(e.dataTransfer.files));
        });
        
        function handleFiles(files) {
            if (files.length === 0) return;
            
            selectedFiles = files;
            
            let fileNames = files.map(f => `${f.name} (${(f.size / 1024 / 1024).toFixed(2)} MB)`).join('<br>');
            filesList.innerHTML = `<strong>Selected files:</strong><br>${fileNames}`;
            
            uploadZone.style.display = 'none';
            optionsPanel.classList.add('show');
        }
        
        async function uploadFiles() {
            if (selectedFiles.length === 0) return;
            
            optionsPanel.style.display = 'none';
            progressBar.classList.add('show');
            
            try {
                for (let i = 0; i < selectedFiles.length; i++) {
                    const file = selectedFiles[i];
                    await uploadSingleFile(file, i + 1, selectedFiles.length);
                }
            } catch (error) {
                alert('Upload failed: ' + error.message);
                resetForm();
            }
        }
        
        async function uploadSingleFile(file, current, total) {
            const formData = new FormData();
            formData.append('file', file);
            formData.append('expiry_hours', document.getElementById('expiryHours').value);
            formData.append('max_downloads', document.getElementById('maxDownloads').value);
            formData.append('password', document.getElementById('password').value);
            
            if (document.getElementById('selfDestruct').checked) {
                formData.append('self_destruct', '1');
            }
            if (document.getElementById('enableCompression').checked) {
                formData.append('enable_compression', '1');
            }
            if (document.getElementById('enableEncryption').checked) {
                formData.append('enable_encryption', '1');
            }
            
            const xhr = new XMLHttpRequest();
            
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    updateProgress(percent, `Uploading ${current}/${total}`);
                }
            });
            
            const response = await new Promise((resolve, reject) => {
                xhr.onload = () => {
                    if (xhr.status === 200) {
                        resolve(JSON.parse(xhr.responseText));
                    } else {
                        reject(new Error('Upload failed'));
                    }
                };
                xhr.onerror = () => reject(new Error('Network error'));
                xhr.open('POST', '/projects/proshare/upload');
                xhr.send(formData);
            });
            
            if (!response.success) {
                throw new Error(response.error);
            }
            
            // Show result for last file
            if (current === total) {
                showResult(response);
            }
        }
        
        function updateProgress(percent, text = '') {
            progressFill.style.width = percent + '%';
            progressFill.textContent = text || percent + '%';
        }
        
        function showResult(data) {
            progressBar.classList.remove('show');
            shareUrl.textContent = data.share_url;
            expiresAt.textContent = new Date(data.expires_at).toLocaleString();
            resultPanel.classList.add('show');
        }
        
        function copyToClipboard() {
            const url = shareUrl.textContent;
            navigator.clipboard.writeText(url).then(() => {
                alert('Link copied to clipboard!');
            });
        }
        
        function resetForm() {
            selectedFiles = [];
            fileInput.value = '';
            filesList.innerHTML = '';
            optionsPanel.classList.remove('show');
            resultPanel.classList.remove('show');
            progressBar.classList.remove('show');
            uploadZone.style.display = 'block';
            updateProgress(0);
        }
    </script>
</body>
</html>
