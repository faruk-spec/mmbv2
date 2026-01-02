<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<div style="text-align: center; padding: 80px 0;">
    <div style="font-size: 6rem; margin-bottom: 30px;">
        <span style="animation: pulse 2s ease-in-out infinite;">🔒</span>
    </div>
    <h1 style="margin-bottom: 20px; font-size: 2.5rem;">Application Temporarily Unavailable</h1>
    <p style="color: var(--text-secondary); margin-bottom: 10px; max-width: 500px; margin-left: auto; margin-right: auto; font-size: 1.1rem;">
        <?php 
        $projectInfo = Helpers::getProject($project ?? '');
        $projectName = $projectInfo['name'] ?? 'This application';
        ?>
        You are trying to visit <strong style="color: var(--cyan);"><?= View::e($projectName) ?></strong>
    </p>
    <p style="color: var(--text-secondary); margin-bottom: 40px; max-width: 500px; margin-left: auto; margin-right: auto;">
        This application is currently disabled. We'll be back soon!
    </p>
    <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
        <a href="/dashboard" class="btn btn-primary">Go to Dashboard</a>
        <a href="/" class="btn btn-secondary">Go Home</a>
    </div>
</div>

<style>
@keyframes pulse {
    0%, 100% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.7; transform: scale(1.1); }
}
</style>
<?php View::endSection(); ?>
