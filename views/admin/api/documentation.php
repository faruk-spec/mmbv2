<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-content">
    <div class="page-header">
        <h1><?= $title ?></h1>
    </div>
    
    <div class="card">
        <h2>API Documentation</h2>
        <p>Access the complete API documentation:</p>
        <ul>
            <li><a href="/PHASE_11_API_GUIDE.md" target="_blank">Phase 11: API Development Guide</a></li>
            <li><a href="/api/v1/proshare" target="_blank">ProShare API Endpoints</a></li>
        </ul>
        
        <h3>Quick Start</h3>
        <pre><code>curl -X GET https://yourdomain.com/api/v1/proshare/files \
  -H "X-API-Key: your_api_key_here"</code></pre>
    </div>
</div>
<?php View::endSection(); ?>
