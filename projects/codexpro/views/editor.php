<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CodeXPro - Live Editor</title>
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
            overflow: hidden;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: scale(0.95);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .editor-container {
            display: flex;
            flex-direction: column;
            height: 100vh;
        }
        
        .editor-header {
            background: #1a1a2e;
            border-bottom: 2px solid #00f0ff;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .project-name {
            color: #00f0ff;
            font-size: 1.2em;
            font-weight: bold;
        }
        
        .header-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .header-actions .btn i {
            margin-right: 5px;
        }
        
        .btn {
            background: #00f0ff;
            color: #0f0f23;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn:hover {
            background: #00d4dd;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: #333;
            color: #fff;
        }
        
        .btn-secondary:hover {
            background: #444;
        }
        
        .editor-body {
            display: flex;
            flex: 1;
            overflow: hidden;
            position: relative;
        }
        
        .code-panels {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            width: 50%;
        }
        
        /* Resizer - Only on desktop */
        .resizer {
            width: 5px;
            background: rgba(0, 240, 255, 0.3);
            cursor: ew-resize;
            position: relative;
            z-index: 10;
            transition: all 0.2s;
            flex-shrink: 0;
        }
        
        .resizer:hover {
            background: rgba(0, 240, 255, 0.6);
            width: 8px;
        }
        
        .resizer:active {
            background: #00f0ff;
        }
        
        .resizer::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 3px;
            height: 40px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 2px;
        }
        
        .tabs {
            background: #16213e;
            display: flex;
            border-bottom: 1px solid #333;
        }
        
        .tab {
            padding: 10px 20px;
            cursor: pointer;
            border-right: 1px solid #333;
            transition: all 0.3s;
        }
        
        .tab.active {
            background: #1a1a2e;
            color: #00f0ff;
            border-bottom: 2px solid #00f0ff;
        }
        
        .tab:hover:not(.active) {
            background: #1a1a2e;
        }
        
        .code-editor {
            flex: 1;
            background: #1a1a2e;
            padding: 10px;
            overflow: auto;
        }
        
        textarea {
            width: 100%;
            height: 100%;
            background: #0f0f23;
            color: #fff;
            border: 1px solid #333;
            padding: 15px;
            font-family: 'Courier New', Courier, monospace;
            font-size: <?= max(10, min(24, (int)($settings['font_size'] ?? 14))) ?>px;
            tab-size: <?= max(2, min(8, (int)($settings['tab_size'] ?? 2))) ?>;
            resize: none;
            outline: none;
        }
        
        .preview-panel {
            flex: 1;
            background: #fff;
            border-left: 2px solid #00f0ff;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }
        
        .preview-header {
            background: #1a1a2e;
            padding: 10px 20px;
            color: #00f0ff;
            font-weight: bold;
            border-bottom: 1px solid #333;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .preview-content {
            flex: 1;
            background: #fff;
            overflow: auto;
        }
        
        iframe {
            width: 100%;
            height: 100%;
            border: none;
            background: #fff;
        }
        
        .status-bar {
            background: #16213e;
            padding: 5px 20px;
            font-size: 0.9em;
            color: #888;
            border-top: 1px solid #333;
            display: flex;
            justify-content: space-between;
        }
        
        .status-saved {
            color: #0f0;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .editor-body {
                flex-direction: column;
            }
            
            .code-panels,
            .preview-panel {
                flex: 1;
                min-height: 50vh;
                width: 100% !important;
            }
            
            .preview-panel {
                border-left: none;
                border-top: 2px solid #00f0ff;
            }
            
            /* Hide resizer on mobile */
            .resizer {
                display: none;
            }
            
            .header-actions {
                width: 100%;
                justify-content: flex-start;
            }
            
            .header-actions .btn {
                flex: 0 1 auto;
                min-width: fit-content;
                padding: 8px 12px;
                font-size: 0.85em;
            }
            
            .header-actions .btn i {
                font-size: 0.9em;
            }
        }
        
        @media (max-width: 480px) {
            .editor-header {
                padding: 10px;
            }
            
            .project-name {
                font-size: 1em;
                width: 100%;
                margin-bottom: 8px;
            }
            
            .tab {
                padding: 8px 12px;
                font-size: 0.9em;
            }
            
            textarea {
                font-size: 12px;
            }
            
            .header-actions .btn {
                padding: 6px 10px;
                font-size: 0.8em;
            }
            
            .header-actions .btn i {
                margin-right: 3px;
            }
            
            .status-bar {
                font-size: 0.8em;
                padding: 5px 10px;
            }
        }
    </style>
</head>
<body>
    <div class="editor-container">
        <div class="editor-header">
            <div class="project-name"><?= htmlspecialchars($project['name'] ?? 'Untitled Project') ?></div>
            <div class="header-actions">
                <button class="btn" onclick="formatCode()" title="Format Code (Alt+Shift+F)">
                    <i class="fas fa-magic"></i> Format
                </button>
                <button class="btn btn-secondary" onclick="validateCode()" title="Validate Code">
                    <i class="fas fa-check-circle"></i> Validate
                </button>
                <button class="btn" onclick="saveProject()">
                    <i class="fas fa-save"></i> Save
                </button>
                <button class="btn btn-secondary" onclick="exportCode()">
                    <i class="fas fa-download"></i> Export
                </button>
                <button class="btn btn-secondary" onclick="showTemplateModal()" title="Load Template">
                    <i class="fas fa-file-code"></i> Templates
                </button>
                <button class="btn btn-secondary" onclick="window.location.href='/projects/codexpro/dashboard'">
                    <i class="fas fa-th-large"></i> Dashboard
                </button>
            </div>
        </div>
        
        <div class="editor-body">
            <div class="code-panels">
                <div class="tabs">
                    <div class="tab active" data-tab="html">HTML</div>
                    <div class="tab" data-tab="css">CSS</div>
                    <div class="tab" data-tab="js">JavaScript</div>
                </div>
                
                <div class="code-editor">
                    <textarea id="html-editor" class="editor-textarea" placeholder="Enter HTML code..."><?= htmlspecialchars($project['html_content'] ?? '<!DOCTYPE html>\n<html>\n<head>\n  <title>My Project</title>\n</head>\n<body>\n  <h1>Hello World!</h1>\n</body>\n</html>') ?></textarea>
                    <textarea id="css-editor" class="editor-textarea" style="display: none;" placeholder="Enter CSS code..."><?= htmlspecialchars($project['css_content'] ?? 'body {\n  font-family: Arial, sans-serif;\n  padding: 20px;\n}') ?></textarea>
                    <textarea id="js-editor" class="editor-textarea" style="display: none;" placeholder="Enter JavaScript code..."><?= htmlspecialchars($project['js_content'] ?? '// Your JavaScript code here\nconsole.log("Hello World!");') ?></textarea>
                </div>
            </div>
            
            <!-- Resizer - Only visible on desktop -->
            <div class="resizer" id="resizer"></div>
            
            <div class="preview-panel">
                <div class="preview-header">
                    <span>Live Preview</span>
                    <button class="btn" onclick="refreshPreview()" style="padding: 4px 12px; font-size: 0.9em;">Refresh</button>
                </div>
                <div class="preview-content">
                    <iframe id="preview-frame"></iframe>
                </div>
            </div>
        </div>
        
        <div class="status-bar">
            <span id="status-text">Ready</span>
            <span>Lines: <span id="line-count">1</span> | Auto-save: <span id="autosave-status"><?= ($settings['auto_save'] ?? 1) ? 'ON' : 'OFF' ?></span></span>
        </div>
    </div>
    
    <script>
        const projectId = <?= $project['id'] ?? 'null' ?>;
        const autoSave = <?= ($settings['auto_save'] ?? 1) ? 'true' : 'false' ?>;
        const autoPreview = <?= ($settings['auto_preview'] ?? 1) ? 'true' : 'false' ?>;
        
        let autoSaveTimeout;
        let previewTimeout;
        
        // Tab switching
        document.querySelectorAll('.tab').forEach(tab => {
            tab.addEventListener('click', function() {
                document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
                
                const tabName = this.dataset.tab;
                document.querySelectorAll('.editor-textarea').forEach(t => t.style.display = 'none');
                document.getElementById(tabName + '-editor').style.display = 'block';
            });
        });
        
        // Resizable editor panels (Desktop only)
        (function() {
            const resizer = document.getElementById('resizer');
            const codePanels = document.querySelector('.code-panels');
            const previewPanel = document.querySelector('.preview-panel');
            const editorBody = document.querySelector('.editor-body');
            
            if (!resizer || !codePanels || !previewPanel || !editorBody) {
                console.log('Resizer elements not found:', {resizer, codePanels, previewPanel, editorBody});
                return;
            }
            
            let isResizing = false;
            
            function initResizer() {
                // Only enable on desktop (> 768px)
                if (window.innerWidth <= 768) {
                    resizer.style.display = 'none';
                    codePanels.style.width = '';
                    previewPanel.style.width = '';
                    return;
                }
                resizer.style.display = '';
            }
            
            resizer.addEventListener('mousedown', function(e) {
                if (window.innerWidth <= 768) return;
                e.preventDefault();
                isResizing = true;
                document.body.style.cursor = 'ew-resize';
                document.body.style.userSelect = 'none';
            });
            
            document.addEventListener('mousemove', function(e) {
                if (!isResizing || window.innerWidth <= 768) return;
                e.preventDefault();
                
                const containerWidth = editorBody.offsetWidth;
                const mouseX = e.clientX - editorBody.getBoundingClientRect().left;
                
                // Calculate percentage (min 20%, max 80%)
                let leftPercent = (mouseX / containerWidth) * 100;
                leftPercent = Math.max(20, Math.min(80, leftPercent));
                
                codePanels.style.width = leftPercent + '%';
                previewPanel.style.width = (100 - leftPercent) + '%';
            });
            
            document.addEventListener('mouseup', function() {
                if (isResizing) {
                    isResizing = false;
                    document.body.style.cursor = '';
                    document.body.style.userSelect = '';
                }
            });
            
            // Handle window resize
            window.addEventListener('resize', initResizer);
            
            // Initialize on load
            initResizer();
        })();
        
        // Live preview update
        function updatePreview() {
            const html = document.getElementById('html-editor').value;
            const css = document.getElementById('css-editor').value;
            const js = document.getElementById('js-editor').value;
            
            const iframe = document.getElementById('preview-frame');
            const doc = iframe.contentDocument || iframe.contentWindow.document;
            
            const fullCode = `
                <!DOCTYPE html>
                <html>
                <head>
                    <style>${css}</style>
                </head>
                <body>
                    ${html}
                    <script>${js}<\/script>
                </body>
                </html>
            `;
            
            doc.open();
            doc.write(fullCode);
            doc.close();
        }
        
        function refreshPreview() {
            updatePreview();
            document.getElementById('status-text').textContent = 'Preview updated';
            setTimeout(() => {
                document.getElementById('status-text').textContent = 'Ready';
            }, 2000);
        }
        
        // Auto-preview with debounce
        function schedulePreview() {
            if (!autoPreview) return;
            
            clearTimeout(previewTimeout);
            previewTimeout = setTimeout(updatePreview, 1000);
        }
        
        // Auto-save functionality
        function scheduleAutoSave() {
            if (!autoSave || !projectId) return;
            
            clearTimeout(autoSaveTimeout);
            autoSaveTimeout = setTimeout(autoSaveProject, 3000);
        }
        
        function autoSaveProject() {
            const formData = new FormData();
            formData.append('project_id', projectId);
            formData.append('html_content', document.getElementById('html-editor').value);
            formData.append('css_content', document.getElementById('css-editor').value);
            formData.append('js_content', document.getElementById('js-editor').value);
            
            fetch('/projects/codexpro/editor/autosave', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('status-text').innerHTML = '<span class="status-saved">Auto-saved</span>';
                    setTimeout(() => {
                        document.getElementById('status-text').textContent = 'Ready';
                    }, 2000);
                }
            });
        }
        
        // Save project
        function saveProject() {
            const formData = new FormData();
            if (projectId) formData.append('project_id', projectId);
            formData.append('name', '<?= addslashes($project['name'] ?? 'Untitled Project') ?>');
            formData.append('html_content', document.getElementById('html-editor').value);
            formData.append('css_content', document.getElementById('css-editor').value);
            formData.append('js_content', document.getElementById('js-editor').value);
            formData.append('visibility', '<?= $project['visibility'] ?? 'private' ?>');
            
            document.getElementById('status-text').textContent = 'Saving...';
            
            fetch('/projects/codexpro/editor/save', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('status-text').innerHTML = '<span class="status-saved">Saved successfully!</span>';
                    if (data.project_id && !projectId) {
                        window.location.href = '/projects/codexpro/editor/' + data.project_id;
                    }
                } else {
                    document.getElementById('status-text').textContent = 'Save failed: ' + (data.error || 'Unknown error');
                }
            });
        }
        
        // Export code
        function exportCode() {
            const html = document.getElementById('html-editor').value;
            const css = document.getElementById('css-editor').value;
            const js = document.getElementById('js-editor').value;
            
            const fullCode = `<!DOCTYPE html>
<html>
<head>
    <title><?= addslashes($project['name'] ?? 'My Project') ?></title>
    <style>
${css}
    </style>
</head>
<body>
${html}
    <script>
${js}
    <\/script>
</body>
</html>`;
            
            const blob = new Blob([fullCode], { type: 'text/html' });
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = '<?= preg_replace('/[^a-z0-9]+/i', '-', $project['name'] ?? 'project') ?>.html';
            a.click();
            URL.revokeObjectURL(url);
        }
        
        // Listen to editor changes
        document.querySelectorAll('.editor-textarea').forEach(editor => {
            editor.addEventListener('input', function() {
                schedulePreview();
                scheduleAutoSave();
                
                // Update line count
                const lines = this.value.split('\n').length;
                document.getElementById('line-count').textContent = lines;
            });
        });
        
        // Initial preview
        updatePreview();
        
        // Resizable Editor (Desktop only)
        const editorBody = document.querySelector('.editor-body');
        const codePanels = document.querySelector('.code-panels');
        const previewPanel = document.querySelector('.preview-panel');
        
        // Format code functionality
        function formatCode() {
            const activeTab = document.querySelector('.tab.active');
            const language = activeTab ? activeTab.dataset.tab : 'html';
            const editorId = language + '-editor';
            const editor = document.getElementById(editorId);
            const code = editor.value;
            
            document.getElementById('status-text').textContent = 'Formatting...';
            
            fetch('/projects/codexpro/api/format', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ code, language })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    editor.value = data.formatted;
                    schedulePreview();
                    document.getElementById('status-text').innerHTML = '<span class="status-saved">Code formatted!</span>';
                } else {
                    document.getElementById('status-text').textContent = 'Format failed: ' + (data.error || 'Unknown error');
                }
                setTimeout(() => {
                    document.getElementById('status-text').textContent = 'Ready';
                }, 2000);
            })
            .catch(err => {
                document.getElementById('status-text').textContent = 'Format failed: ' + err.message;
            });
        }
        
        // Validate code functionality
        function validateCode() {
            const activeTab = document.querySelector('.tab.active');
            const language = activeTab ? activeTab.dataset.tab : 'html';
            const editorId = language + '-editor';
            const editor = document.getElementById(editorId);
            const code = editor.value;
            
            document.getElementById('status-text').textContent = 'Validating...';
            
            fetch('/projects/codexpro/api/validate', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({ code, language })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.valid) {
                        document.getElementById('status-text').innerHTML = '<span class="status-saved">✓ Code is valid!</span>';
                    } else {
                        const errors = data.errors.join(', ');
                        document.getElementById('status-text').textContent = 'Validation errors: ' + errors;
                        alert('Validation Errors:\n\n' + data.errors.join('\n'));
                    }
                } else {
                    document.getElementById('status-text').textContent = 'Validation failed: ' + (data.error || 'Unknown error');
                }
                setTimeout(() => {
                    document.getElementById('status-text').textContent = 'Ready';
                }, 3000);
            })
            .catch(err => {
                document.getElementById('status-text').textContent = 'Validation failed: ' + err.message;
            });
        }
        
        // Template modal functionality
        function showTemplateModal() {
            fetch('/projects/codexpro/api/starter-templates')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        displayTemplateModal(data.templates);
                    } else {
                        alert('Error loading templates: ' + (data.error || 'Unknown error'));
                    }
                })
                .catch(err => {
                    alert('Error loading templates: ' + err.message);
                });
        }
        
        function displayTemplateModal(templates) {
            // Create modal overlay
            const modal = document.createElement('div');
            modal.id = 'template-modal';
            modal.style.cssText = `
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: rgba(0, 0, 0, 0.8);
                z-index: 10000;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                animation: fadeIn 0.3s ease-out;
            `;
            
            const modalContent = document.createElement('div');
            modalContent.style.cssText = `
                background: #1a1a2e;
                border-radius: 8px;
                padding: 30px;
                max-width: 800px;
                max-height: 80vh;
                overflow-y: auto;
                width: 100%;
                animation: fadeIn 0.3s ease-out;
            `;
            
            let html = '<h2 style="color: #00f0ff; margin-bottom: 20px;">Choose a Template</h2>';
            html += '<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 15px;">';
            
            for (const [key, template] of Object.entries(templates)) {
                html += `
                    <div style="background: #0f0f23; border: 2px solid #333; border-radius: 8px; padding: 15px; cursor: pointer; transition: all 0.3s;"
                         onmouseover="this.style.borderColor='#00f0ff'"
                         onmouseout="this.style.borderColor='#333'"
                         onclick="loadTemplate('${key}')">
                        <h3 style="color: #00f0ff; font-size: 1.1em; margin-bottom: 8px;">${template.name}</h3>
                        <p style="color: #888; font-size: 0.9em;">${template.description}</p>
                        <span style="display: inline-block; margin-top: 8px; padding: 4px 8px; background: #00f0ff; color: #000; border-radius: 4px; font-size: 0.8em;">${template.category}</span>
                    </div>
                `;
            }
            
            html += '</div>';
            html += '<button onclick="closeTemplateModal()" style="margin-top: 20px; padding: 10px 20px; background: #666; color: #fff; border: none; border-radius: 4px; cursor: pointer;">Close</button>';
            
            modalContent.innerHTML = html;
            modal.appendChild(modalContent);
            document.body.appendChild(modal);
            
            // Close on overlay click
            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    closeTemplateModal();
                }
            });
        }
        
        function closeTemplateModal() {
            const modal = document.getElementById('template-modal');
            if (modal) {
                modal.remove();
            }
        }
        
        function loadTemplate(templateKey) {
            if (!confirm('Loading a template will replace your current code. Continue?')) {
                return;
            }
            
            fetch('/projects/codexpro/api/starter-templates')
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.templates[templateKey]) {
                        const template = data.templates[templateKey];
                        const files = template.files;
                        
                        // Load template files into editors
                        if (files['index.html']) {
                            document.getElementById('html-editor').value = files['index.html'];
                        }
                        if (files['style.css'] || files['app.css']) {
                            document.getElementById('css-editor').value = files['style.css'] || files['app.css'];
                        }
                        if (files['script.js'] || files['app.js']) {
                            document.getElementById('js-editor').value = files['script.js'] || files['app.js'];
                        }
                        
                        updatePreview();
                        closeTemplateModal();
                        document.getElementById('status-text').innerHTML = '<span class="status-saved">Template loaded!</span>';
                        setTimeout(() => {
                            document.getElementById('status-text').textContent = 'Ready';
                        }, 2000);
                    }
                })
                .catch(err => {
                    alert('Error loading template: ' + err.message);
                });
        }
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Alt+Shift+F for format
            if (e.altKey && e.shiftKey && e.key === 'F') {
                e.preventDefault();
                formatCode();
            }
            // Ctrl+S or Cmd+S for save
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                saveProject();
            }
        });
    </script>
</body>
</html>
