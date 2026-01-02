<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div style="margin-bottom: 30px;">
    <a href="/admin/logs" style="color: var(--text-secondary);">&larr; Back to Logs</a>
    <h1 style="margin-top: 10px;">System Logs</h1>
</div>

<div class="grid grid-4">
    <div>
        <div class="card">
            <h4 style="margin-bottom: 15px;">Log Files</h4>
            
            <?php if (empty($logFiles)): ?>
                <p style="color: var(--text-secondary); font-size: 14px;">No log files found</p>
            <?php else: ?>
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    <?php foreach ($logFiles as $file): ?>
                        <a href="?file=<?= urlencode($file) ?>" 
                           style="padding: 10px; background: <?= $selectedFile === $file ? 'var(--cyan)' : 'var(--bg-secondary)' ?>; 
                                  color: <?= $selectedFile === $file ? 'var(--bg-primary)' : 'var(--text-primary)' ?>; 
                                  border-radius: 6px; font-size: 14px;">
                            <?= View::e($file) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <div style="grid-column: span 3;">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><?= $selectedFile ? View::e($selectedFile) : 'Select a log file' ?></h3>
            </div>
            
            <?php if (empty($logContent)): ?>
                <p style="color: var(--text-secondary); text-align: center; padding: 30px;">
                    <?= $selectedFile ? 'Log file is empty' : 'Select a log file to view its contents' ?>
                </p>
            <?php else: ?>
                <div style="background: var(--bg-secondary); border-radius: 8px; padding: 15px; max-height: 600px; overflow: auto;">
                    <pre style="margin: 0; font-size: 13px; font-family: 'Fira Code', monospace; line-height: 1.6; white-space: pre-wrap; word-break: break-all;"><?php 
                        foreach ($logContent as $line) {
                            $line = htmlspecialchars($line);
                            // Colorize log levels
                            $line = preg_replace('/\[ERROR\]/', '<span style="color: var(--red);">[ERROR]</span>', $line);
                            $line = preg_replace('/\[WARNING\]/', '<span style="color: var(--orange);">[WARNING]</span>', $line);
                            $line = preg_replace('/\[INFO\]/', '<span style="color: var(--cyan);">[INFO]</span>', $line);
                            $line = preg_replace('/\[DEBUG\]/', '<span style="color: var(--text-secondary);">[DEBUG]</span>', $line);
                            echo $line;
                        }
                    ?></pre>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php View::endSection(); ?>
