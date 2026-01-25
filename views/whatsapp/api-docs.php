<?php use Core\View; use Core\Helpers; use Core\Auth; $currentUser = Auth::user(); ?>
<?php View::extend('Projects\\WhatsApp', 'app'); ?>

<?php View::section('content'); ?>

<style>
.api-docs-container {
    max-width: 1200px;
    margin: 0 auto;
}

.api-sidebar {
    position: sticky;
    top: 20px;
}

.api-nav-item {
    padding: 10px 16px;
    margin-bottom: 8px;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    font-size: 0.875rem;
    border-left: 3px solid transparent;
}

.api-nav-item:hover {
    background: rgba(37, 211, 102, 0.1);
    border-left-color: #25D366;
}

.api-nav-item.active {
    background: rgba(37, 211, 102, 0.2);
    border-left-color: #25D366;
    font-weight: 600;
}

.endpoint-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 24px;
    margin-bottom: 32px;
    scroll-margin-top: 80px;
}

.endpoint-header {
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border-color);
}

.endpoint-title {
    font-size: 1.4rem;
    font-weight: 600;
    margin-bottom: 12px;
    color: #25D366;
}

.endpoint-badge {
    display: inline-block;
    padding: 6px 12px;
    border-radius: 6px;
    font-weight: 600;
    font-size: 0.75rem;
    margin-right: 12px;
}

.badge-post {
    background: rgba(37, 211, 102, 0.2);
    color: #25D366;
}

.badge-get {
    background: rgba(0, 136, 204, 0.2);
    color: #0088cc;
}

.endpoint-url {
    font-family: 'Courier New', monospace;
    font-size: 0.9rem;
    color: var(--text-secondary);
}

.params-table {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
}

.params-table th {
    background: rgba(37, 211, 102, 0.1);
    padding: 12px;
    text-align: left;
    font-size: 0.875rem;
    font-weight: 600;
    border-bottom: 2px solid var(--border-color);
}

.params-table td {
    padding: 12px;
    border-bottom: 1px solid var(--border-color);
    font-size: 0.875rem;
}

.code-block {
    background: var(--bg-primary);
    border: 1px solid var(--border-color);
    border-radius: 8px;
    padding: 16px;
    margin: 16px 0;
    overflow-x: auto;
}

.code-block code {
    font-family: 'Courier New', monospace;
    font-size: 0.85rem;
    line-height: 1.6;
    color: #25D366;
}

.required-badge {
    color: #ff6b6b;
    font-size: 0.75rem;
    font-weight: 600;
}

.optional-badge {
    color: var(--text-secondary);
    font-size: 0.75rem;
}
</style>

<div class="api-docs-container">
    <div style="margin-bottom: 40px;">
        <h1 style="font-size: 2.5rem; margin-bottom: 12px; display: flex; align-items: center; gap: 16px;">
            <i class="fas fa-book" style="color: #25D366; font-size: 2.5rem;"></i>
            WhatsApp API Documentation
        </h1>
        <p style="color: var(--text-secondary); font-size: 1rem; line-height: 1.6; max-width: 800px;">
            Complete API reference for integrating WhatsApp automation into your applications. 
            Use these endpoints to send messages, manage sessions, and automate your WhatsApp communications.
        </p>
    </div>

    <div style="display: grid; grid-template-columns: 250px 1fr; gap: 40px;">
        <!-- Sidebar Navigation -->
        <div class="api-sidebar">
            <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 20px;">
                <h3 style="font-size: 0.95rem; margin-bottom: 16px; color: #25D366;">Endpoints</h3>
                <nav>
                    <div class="api-nav-item active" onclick="scrollToEndpoint('authentication')">Authentication</div>
                    <?php foreach ($endpoints as $i => $endpoint): ?>
                        <div class="api-nav-item" onclick="scrollToEndpoint('endpoint-<?= $i ?>')">
                            <?= View::e($endpoint['name']) ?>
                        </div>
                    <?php endforeach; ?>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div>
            <!-- Authentication Section -->
            <div class="endpoint-card" id="authentication">
                <div class="endpoint-header">
                    <h2 class="endpoint-title">Authentication</h2>
                </div>
                <p style="margin-bottom: 16px; line-height: 1.6;">
                    All API requests require authentication using an API key. Include your API key in the request headers:
                </p>
                <div class="code-block">
                    <code>Authorization: Bearer YOUR_API_KEY</code>
                </div>
                <p style="margin-top: 16px; color: var(--text-secondary); font-size: 0.875rem;">
                    You can generate and manage your API keys in the <a href="/projects/whatsapp/settings" style="color: #25D366;">Settings</a> page.
                </p>
            </div>

            <!-- API Endpoints -->
            <?php foreach ($endpoints as $i => $endpoint): ?>
                <div class="endpoint-card" id="endpoint-<?= $i ?>">
                    <div class="endpoint-header">
                        <h2 class="endpoint-title"><?= View::e($endpoint['name']) ?></h2>
                        <div style="margin-bottom: 12px;">
                            <span class="endpoint-badge badge-<?= strtolower($endpoint['method']) ?>">
                                <?= strtoupper($endpoint['method']) ?>
                            </span>
                            <span class="endpoint-url"><?= View::e($endpoint['endpoint']) ?></span>
                        </div>
                        <p style="color: var(--text-secondary); line-height: 1.6;">
                            <?= View::e($endpoint['description']) ?>
                        </p>
                    </div>

                    <?php if (!empty($endpoint['parameters'])): ?>
                        <h3 style="font-size: 1.1rem; margin-bottom: 16px; font-weight: 600;">Parameters</h3>
                        <table class="params-table">
                            <thead>
                                <tr>
                                    <th>Parameter</th>
                                    <th>Type</th>
                                    <th>Required</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($endpoint['parameters'] as $param): ?>
                                    <tr>
                                        <td><code><?= View::e($param['name']) ?></code></td>
                                        <td><?= View::e($param['type']) ?></td>
                                        <td>
                                            <?php if ($param['required']): ?>
                                                <span class="required-badge">REQUIRED</span>
                                            <?php else: ?>
                                                <span class="optional-badge">Optional</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= View::e($param['description']) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>

                    <?php if (!empty($endpoint['example'])): ?>
                        <h3 style="font-size: 1.1rem; margin: 24px 0 16px; font-weight: 600;">Example</h3>
                        
                        <?php if (isset($endpoint['example']['request'])): ?>
                            <h4 style="font-size: 0.95rem; margin-bottom: 12px; color: var(--text-secondary);">Request</h4>
                            <div class="code-block">
                                <code><?= htmlspecialchars(json_encode($endpoint['example']['request'], JSON_PRETTY_PRINT)) ?></code>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($endpoint['example']['response'])): ?>
                            <h4 style="font-size: 0.95rem; margin: 20px 0 12px; color: var(--text-secondary);">Response</h4>
                            <div class="code-block">
                                <code><?= htmlspecialchars(json_encode($endpoint['example']['response'], JSON_PRETTY_PRINT)) ?></code>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>

                    <!-- cURL Example -->
                    <h3 style="font-size: 1.1rem; margin: 24px 0 16px; font-weight: 600;">cURL Example</h3>
                    <div class="code-block">
                        <code>curl -X <?= strtoupper($endpoint['method']) ?> \
  '<?= APP_URL . $endpoint['endpoint'] ?>' \
  -H 'Authorization: Bearer YOUR_API_KEY' \
  -H 'Content-Type: application/json'<?php if ($endpoint['method'] === 'POST'): ?> \
  -d '<?= isset($endpoint['example']['request']) ? json_encode($endpoint['example']['request']) : '{}' ?>'<?php endif; ?>
                        </code>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Rate Limits Section -->
            <div class="endpoint-card">
                <div class="endpoint-header">
                    <h2 class="endpoint-title">Rate Limits</h2>
                </div>
                <p style="margin-bottom: 16px; line-height: 1.6;">
                    API requests are limited to <strong>100 requests per minute</strong> per API key. 
                    If you exceed this limit, you'll receive a <code>429 Too Many Requests</code> response.
                </p>
                <div class="code-block">
                    <code>{
  "success": false,
  "error": "Rate limit exceeded"
}</code>
                </div>
            </div>

            <!-- Error Codes Section -->
            <div class="endpoint-card">
                <div class="endpoint-header">
                    <h2 class="endpoint-title">Error Codes</h2>
                </div>
                <table class="params-table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Description</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code>400</code></td>
                            <td>Bad Request - Missing or invalid parameters</td>
                        </tr>
                        <tr>
                            <td><code>401</code></td>
                            <td>Unauthorized - Invalid or missing API key</td>
                        </tr>
                        <tr>
                            <td><code>403</code></td>
                            <td>Forbidden - Access denied to resource</td>
                        </tr>
                        <tr>
                            <td><code>404</code></td>
                            <td>Not Found - Endpoint or resource not found</td>
                        </tr>
                        <tr>
                            <td><code>429</code></td>
                            <td>Too Many Requests - Rate limit exceeded</td>
                        </tr>
                        <tr>
                            <td><code>500</code></td>
                            <td>Internal Server Error - Something went wrong</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function scrollToEndpoint(id) {
    document.getElementById(id).scrollIntoView({ behavior: 'smooth' });
    
    // Update active nav item
    document.querySelectorAll('.api-nav-item').forEach(item => item.classList.remove('active'));
    event.target.classList.add('active');
}
</script>

<?php View::endSection(); ?>
