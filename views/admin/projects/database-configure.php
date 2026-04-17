<?php use Core\View; ?>

<div class="admin-content">
    <div class="content-header">
        <h2><?= View::e($project_name) ?> Database Configuration</h2>
        <p>Configure database connection for <?= View::e($project_name) ?> project</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h3>Database Connection Settings</h3>
        </div>
        <div class="card-body">
            <form id="configForm" method="POST" action="/admin/projects/database-setup/save">
                <input type="hidden" name="_csrf_token" value="<?= $csrf_token ?>">
                <input type="hidden" name="project" value="<?= View::e($project) ?>">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="host">Database Host</label>
                        <input type="text" id="host" name="host" class="form-control" 
                               value="<?= View::e($config['host'] ?? 'localhost') ?>" required>
                        <small>Usually localhost or 127.0.0.1</small>
                    </div>

                    <div class="form-group">
                        <label for="port">Database Port</label>
                        <input type="number" id="port" name="port" class="form-control" 
                               value="<?= View::e($config['port'] ?? '3306') ?>" required>
                        <small>Default MySQL port is 3306</small>
                    </div>

                    <div class="form-group">
                        <label for="database">Database Name</label>
                        <input type="text" id="database" name="database" class="form-control" 
                               value="<?= View::e($config['database'] ?? $project) ?>" required>
                        <small>Name of the database for this project</small>
                    </div>

                    <div class="form-group">
                        <label for="username">Database Username</label>
                        <input type="text" id="username" name="username" class="form-control" 
                               value="<?= View::e($config['username'] ?? '') ?>" required>
                        <small>MySQL user with access to this database</small>
                    </div>

                    <div class="form-group full-width">
                        <label for="password">Database Password</label>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="<?= !empty($config['password']) ? '••••••••' : 'Enter password' ?>">
                        <small>Leave blank to keep existing password</small>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" id="testBtn" class="btn btn-secondary">
                        <i class="fas fa-vial"></i> Test Connection
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Configuration
                    </button>
                    <a href="/admin/projects/database-setup" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </form>

            <div id="testResult" class="alert" style="display: none; margin-top: 20px;"></div>
        </div>
    </div>

    <?php if ($has_schema): ?>
    <div class="card" style="margin-top: 20px;">
        <div class="card-header">
            <h3>Import Database Schema</h3>
        </div>
        <div class="card-body">
            <p>Import the SQL schema for <?= View::e($project_name) ?> project. This will create all required tables.</p>
            
            <form id="importForm" enctype="multipart/form-data">
                <input type="hidden" name="project" value="<?= View::e($project) ?>">
                
                <div class="form-group">
                    <label>Choose Import Method</label>
                    <div class="radio-group">
                        <label>
                            <input type="radio" name="import_method" value="file" checked>
                            Upload SQL File
                        </label>
                        <label>
                            <input type="radio" name="import_method" value="paste">
                            Paste SQL Content
                        </label>
                    </div>
                </div>

                <div id="fileUpload" class="form-group">
                    <label for="sql_file">SQL File</label>
                    <input type="file" id="sql_file" name="sql_file" accept=".sql" class="form-control">
                    <small>Upload the schema.sql file from projects/<?= View::e($project) ?>/</small>
                </div>

                <div id="sqlPaste" class="form-group" style="display: none;">
                    <label for="sql_content">SQL Content</label>
                    <textarea id="sql_content" name="sql_content" class="form-control" rows="10" 
                              placeholder="Paste SQL schema here..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-file-import"></i> Import Schema
                    </button>
                </div>
            </form>

            <div id="importResult" class="alert" style="display: none; margin-top: 20px;"></div>
        </div>
    </div>
    <?php endif; ?>
</div>

<style>
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 500;
    margin-bottom: 8px;
    color: var(--cyan);
}

.form-control {
    padding: 12px;
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    color: var(--text-primary);
    font-size: 14px;
    transition: var(--transition);
}

.form-control:focus {
    outline: none;
    border-color: var(--cyan);
    box-shadow: 0 0 0 3px rgba(0, 240, 255, 0.1);
}

.form-group small {
    margin-top: 6px;
    font-size: 12px;
    color: var(--text-secondary);
}

.form-actions {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 500;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    transition: var(--transition);
    border: none;
    font-size: 14px;
}

.btn-primary {
    background: linear-gradient(135deg, var(--cyan), var(--purple));
    color: white;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 20px rgba(0, 240, 255, 0.3);
}

.btn-secondary {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-primary);
    border: 1px solid var(--border-color);
}

.btn-secondary:hover {
    background: rgba(255, 255, 255, 0.15);
}

.btn-outline {
    background: transparent;
    color: var(--text-secondary);
    border: 1px solid var(--border-color);
}

.btn-outline:hover {
    background: rgba(255, 255, 255, 0.05);
    color: var(--text-primary);
}

.alert {
    padding: 16px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.alert.success {
    background: rgba(0, 255, 136, 0.1);
    border: 1px solid var(--green);
    color: var(--green);
}

.alert.error {
    background: rgba(255, 107, 107, 0.1);
    border: 1px solid var(--red);
    color: var(--red);
}

.alert.info {
    background: rgba(0, 240, 255, 0.1);
    border: 1px solid var(--cyan);
    color: var(--cyan);
}

.radio-group {
    display: flex;
    gap: 20px;
    margin-top: 8px;
}

.radio-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    font-weight: normal;
    color: var(--text-primary);
}

.radio-group input[type="radio"] {
    cursor: pointer;
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const testBtn = document.getElementById('testBtn');
    const testResult = document.getElementById('testResult');
    const importForm = document.getElementById('importForm');
    const importResult = document.getElementById('importResult');
    const importMethodRadios = document.querySelectorAll('input[name="import_method"]');
    const fileUpload = document.getElementById('fileUpload');
    const sqlPaste = document.getElementById('sqlPaste');

    // Test Connection
    if (testBtn) {
        testBtn.addEventListener('click', async function() {
            const host = document.getElementById('host').value;
            const port = document.getElementById('port').value;
            const database = document.getElementById('database').value;
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            testBtn.disabled = true;
            testBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Testing...';

            try {
                const response = await fetch('/admin/projects/database-setup/test', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        project: '<?= $project ?>',
                        host, port, database, username, password
                    })
                });

                const result = await response.json();

                testResult.style.display = 'block';
                if (result.success) {
                    testResult.className = 'alert success';
                    testResult.innerHTML = `<i class="fas fa-check-circle"></i> ${result.message}`;
                    if (result.table_count !== undefined) {
                        testResult.innerHTML += `<br><small>${result.table_count} tables found</small>`;
                    }
                } else {
                    testResult.className = 'alert error';
                    testResult.innerHTML = `<i class="fas fa-times-circle"></i> ${result.message}`;
                }
            } catch (error) {
                testResult.style.display = 'block';
                testResult.className = 'alert error';
                testResult.innerHTML = `<i class="fas fa-times-circle"></i> Connection test failed: ${error.message}`;
            } finally {
                testBtn.disabled = false;
                testBtn.innerHTML = '<i class="fas fa-vial"></i> Test Connection';
            }
        });
    }

    // Toggle import method
    importMethodRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'file') {
                fileUpload.style.display = 'block';
                sqlPaste.style.display = 'none';
            } else {
                fileUpload.style.display = 'none';
                sqlPaste.style.display = 'block';
            }
        });
    });

    // Import Schema
    if (importForm) {
        importForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(importForm);
            const submitBtn = importForm.querySelector('button[type="submit"]');
            
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Importing...';

            try {
                const response = await fetch('/admin/projects/database-setup/import', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                importResult.style.display = 'block';
                if (result.success) {
                    importResult.className = 'alert success';
                    importResult.innerHTML = `<i class="fas fa-check-circle"></i> ${result.message}`;
                    if (result.tables) {
                        importResult.innerHTML += `<br><small>Tables: ${result.tables.join(', ')}</small>`;
                    }
                } else {
                    importResult.className = 'alert error';
                    importResult.innerHTML = `<i class="fas fa-times-circle"></i> ${result.message}`;
                }
            } catch (error) {
                importResult.style.display = 'block';
                importResult.className = 'alert error';
                importResult.innerHTML = `<i class="fas fa-times-circle"></i> Import failed: ${error.message}`;
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-file-import"></i> Import Schema';
            }
        });
    }
});
</script>
