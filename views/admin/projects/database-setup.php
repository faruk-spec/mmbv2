<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('styles'); ?>
<style>
/* Database Setup Page Styles */
.page-header {
    margin-bottom: 30px;
}

.page-title {
    color: var(--text-primary);
    font-size: 2rem;
    font-weight: 600;
    margin-bottom: 8px;
}

.page-description {
    color: var(--text-secondary);
    font-size: 1rem;
}

.db-setup-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 24px;
    margin-top: 24px;
}

.project-card {
    background: var(--bg-card);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    transition: var(--transition);
}

.project-card:hover {
    border-color: var(--cyan);
    box-shadow: 0 4px 20px var(--shadow);
    transform: translateY(-2px);
}

.project-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border-color);
}

.project-name {
    color: var(--text-primary);
    font-size: 1.25rem;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.project-name::before {
    content: '';
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--cyan);
}

.status-badge {
    padding: 4px 12px;
    border-radius: 16px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.status-connected {
    background: var(--badge-success-bg);
    color: var(--green);
}

.status-disconnected {
    background: var(--badge-danger-bg);
    color: var(--red);
}

.status-pending {
    background: var(--badge-warning-bg);
    color: var(--orange);
}

.config-info {
    margin: 20px 0;
}

.config-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 0;
    border-bottom: 1px solid var(--border-color);
}

.config-row:last-child {
    border-bottom: none;
}

.config-label {
    color: var(--text-secondary);
    font-size: 0.875rem;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
}

.config-label i {
    width: 16px;
    text-align: center;
    color: var(--cyan);
}

.config-value {
    color: var(--text-primary);
    font-size: 0.875rem;
    font-weight: 500;
    text-align: right;
}

.config-value em {
    color: var(--text-secondary);
    font-style: italic;
    opacity: 0.7;
}

.action-buttons {
    display: flex;
    gap: 12px;
    margin-top: 20px;
    padding-top: 16px;
    border-top: 1px solid var(--border-color);
}

.btn {
    flex: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 12px 20px;
    border: none;
    border-radius: 8px;
    font-size: 0.875rem;
    font-weight: 500;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, var(--cyan), var(--magenta));
    color: white;
}

.btn-primary:hover {
    opacity: 0.9;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 240, 255, 0.3);
}

.btn-secondary {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    color: var(--text-primary);
}

.btn-secondary:hover {
    background: var(--hover-bg);
    border-color: var(--cyan);
}

.alert {
    padding: 16px 20px;
    border-radius: 8px;
    margin-bottom: 24px;
    display: flex;
    align-items: center;
    gap: 12px;
    border-left: 4px solid;
}

.alert-success {
    background: var(--badge-success-bg);
    color: var(--green);
    border-left-color: var(--green);
}

.alert-error {
    background: var(--badge-danger-bg);
    color: var(--red);
    border-left-color: var(--red);
}

/* Responsive */
@media (max-width: 768px) {
    .db-setup-grid {
        grid-template-columns: 1fr;
    }
    
    .page-title {
        font-size: 1.5rem;
    }
    
    .action-buttons {
        flex-direction: column;
    }
}
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div class="page-header">
    <h1 class="page-title">Project Database Setup</h1>
    <p class="page-description">Configure database connections for CodeXPro, ImgTxt, and ProShare projects</p>
</div>

<?php if (isset($_SESSION['success'])): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?= htmlspecialchars($_SESSION['success']) ?>
        <?php unset($_SESSION['success']); ?>
    </div>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-circle"></i>
        <?= htmlspecialchars($_SESSION['error']) ?>
        <?php unset($_SESSION['error']); ?>
    </div>
<?php endif; ?>

<div class="db-setup-grid">
    <?php foreach ($projects as $key => $project): ?>
        <div class="project-card">
            <div class="project-header">
                <h2 class="project-name"><?= htmlspecialchars($project['name']) ?></h2>
                <span class="status-badge status-<?= htmlspecialchars($project['status']) ?>">
                    <?= ucfirst(htmlspecialchars($project['status'])) ?>
                </span>
            </div>
            
            <div class="config-info">
                <div class="config-row">
                    <span class="config-label">
                        <i class="fas fa-database"></i> Database
                    </span>
                    <span class="config-value"><?= htmlspecialchars($project['config']['database'] ?? $key) ?></span>
                </div>
                <div class="config-row">
                    <span class="config-label">
                        <i class="fas fa-server"></i> Host
                    </span>
                    <span class="config-value"><?= htmlspecialchars($project['config']['host'] ?? 'localhost') ?></span>
                </div>
                <div class="config-row">
                    <span class="config-label">
                        <i class="fas fa-user"></i> Username
                    </span>
                    <span class="config-value">
                        <?= !empty($project['config']['username']) ? htmlspecialchars($project['config']['username']) : '<em>Not configured</em>' ?>
                    </span>
                </div>
                <?php if ($project['last_tested']): ?>
                    <div class="config-row">
                        <span class="config-label">
                            <i class="fas fa-clock"></i> Last Tested
                        </span>
                        <span class="config-value"><?= htmlspecialchars($project['last_tested']) ?></span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="action-buttons">
                <a href="/admin/projects/database-setup/<?= urlencode($key) ?>" class="btn btn-primary">
                    <i class="fas fa-cog"></i> Configure
                </a>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php View::endSection(); ?>
