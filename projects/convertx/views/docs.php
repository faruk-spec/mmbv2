<?php
/**
 * ConvertX – API Documentation View
 */
$currentView = 'docs';
$baseUrl     = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'yourdomain.com');
?>

<!-- Page header -->
<div class="page-header">
    <h1>API Documentation</h1>
    <p>Integrate ConvertX into any application using our REST API</p>
</div>

<div class="card" style="max-width:860px;">
    <div class="card-header"><i class="fa-solid fa-book-open"></i> ConvertX REST API (v1)</div>
    <p style="font-size:.875rem;color:var(--text-secondary);margin-bottom:1.5rem;">
        Authenticate with your API key from the <a href="/projects/convertx/settings" style="color:var(--cx-primary);">Settings</a> page.
    </p>

    <!-- Authentication -->
    <h3 style="font-size:1rem;font-weight:600;margin-bottom:.75rem;">Authentication</h3>
    <pre style="background:var(--cx-code-bg);border:1px solid var(--border-color);border-radius:8px;padding:1rem;font-size:.8rem;overflow-x:auto;margin-bottom:1.5rem;">X-Api-Key: cx_your_api_key_here
# OR
Authorization: Bearer cx_your_api_key_here</pre>

    <!-- Endpoint: Convert -->
    <h3 style="font-size:1rem;font-weight:600;margin-bottom:.75rem;color:var(--cx-primary);">POST /projects/convertx/api/convert</h3>
    <p style="font-size:.85rem;color:var(--text-secondary);margin-bottom:.5rem;">Submit a file for conversion. Returns a Job ID for polling.</p>
    <p style="font-size:.8rem;margin-bottom:.25rem;"><strong>Content-Type:</strong> multipart/form-data</p>
    <table class="cx-table" style="margin-bottom:1rem;">
        <thead><tr><th>Parameter</th><th>Type</th><th>Required</th><th>Description</th></tr></thead>
        <tbody>
            <tr><td>file</td><td>file</td><td>✓</td><td>The source file to convert</td></tr>
            <tr><td>output_format</td><td>string</td><td>✓</td><td>Target format: pdf, docx, png, etc.</td></tr>
            <tr><td>ai_tasks</td><td>string</td><td></td><td>Comma-separated AI tasks: ocr, summarize, translate:fr, classify</td></tr>
            <tr><td>webhook_url</td><td>url</td><td></td><td>POST callback when job completes</td></tr>
        </tbody>
    </table>
    <pre style="background:var(--cx-code-bg);border:1px solid var(--border-color);border-radius:8px;padding:1rem;font-size:.8rem;overflow-x:auto;margin-bottom:1.5rem;">curl -X POST <?= htmlspecialchars($baseUrl) ?>/projects/convertx/api/convert \
  -H "X-Api-Key: cx_your_key" \
  -F "file=@document.pdf" \
  -F "output_format=docx" \
  -F "ai_tasks=ocr,summarize"

# Response (202 Accepted)
{
  "success": true,
  "job_id": 42,
  "status": "pending",
  "message": "Job queued. Poll /api/status/42 for updates."
}</pre>

    <!-- Endpoint: Status -->
    <h3 style="font-size:1rem;font-weight:600;margin-bottom:.75rem;color:var(--cx-primary);">GET /projects/convertx/api/status/{job_id}</h3>
    <p style="font-size:.85rem;color:var(--text-secondary);margin-bottom:.5rem;">Poll the status of a conversion job.</p>
    <pre style="background:var(--cx-code-bg);border:1px solid var(--border-color);border-radius:8px;padding:1rem;font-size:.8rem;overflow-x:auto;margin-bottom:1.5rem;">curl <?= htmlspecialchars($baseUrl) ?>/projects/convertx/api/status/42 \
  -H "X-Api-Key: cx_your_key"

# Response
{
  "success": true,
  "job_id": 42,
  "status": "completed",
  "output_filename": "document_converted.docx",
  "completed_at": "2026-01-01T12:00:00",
  "ai_result": {
    "ocr": { "text": "Extracted text..." },
    "summarize": { "summary": "This document discusses..." }
  }
}</pre>

    <!-- Endpoint: Download -->
    <h3 style="font-size:1rem;font-weight:600;margin-bottom:.75rem;color:var(--cx-primary);">GET /projects/convertx/api/download/{job_id}</h3>
    <p style="font-size:.85rem;color:var(--text-secondary);margin-bottom:.5rem;">Download the converted file (binary stream).</p>
    <pre style="background:var(--cx-code-bg);border:1px solid var(--border-color);border-radius:8px;padding:1rem;font-size:.8rem;overflow-x:auto;margin-bottom:1.5rem;">curl -O -J <?= htmlspecialchars($baseUrl) ?>/projects/convertx/api/download/42 \
  -H "X-Api-Key: cx_your_key"</pre>

    <!-- Endpoint: History -->
    <h3 style="font-size:1rem;font-weight:600;margin-bottom:.75rem;color:var(--cx-primary);">GET /projects/convertx/api/history</h3>
    <p style="font-size:.85rem;color:var(--text-secondary);margin-bottom:.5rem;">List your past conversion jobs (paginated).</p>
    <pre style="background:var(--cx-code-bg);border:1px solid var(--border-color);border-radius:8px;padding:1rem;font-size:.8rem;overflow-x:auto;margin-bottom:1.5rem;">curl "<?= htmlspecialchars($baseUrl) ?>/projects/convertx/api/history?page=1" \
  -H "X-Api-Key: cx_your_key"</pre>

    <!-- Endpoint: Usage -->
    <h3 style="font-size:1rem;font-weight:600;margin-bottom:.75rem;color:var(--cx-primary);">GET /projects/convertx/api/usage</h3>
    <p style="font-size:.85rem;color:var(--text-secondary);margin-bottom:.5rem;">Get your usage statistics for the current month.</p>
    <pre style="background:var(--cx-code-bg);border:1px solid var(--border-color);border-radius:8px;padding:1rem;font-size:.8rem;overflow-x:auto;margin-bottom:1.5rem;">curl <?= htmlspecialchars($baseUrl) ?>/projects/convertx/api/usage \
  -H "X-Api-Key: cx_your_key"

# Response
{
  "success": true,
  "period": "2026-01",
  "usage": {
    "total_jobs": 152,
    "completed": 148,
    "failed": 4,
    "tokens_used": 42500
  }
}</pre>

    <!-- Rate limits -->
    <h3 style="font-size:1rem;font-weight:600;margin-bottom:.75rem;">Rate Limits</h3>
    <table class="cx-table" style="margin-bottom:1.5rem;">
        <thead><tr><th>Plan</th><th>Requests/min</th><th>Monthly jobs</th><th>Max file size</th></tr></thead>
        <tbody>
            <tr><td>Free</td><td>10</td><td>50</td><td>10 MB</td></tr>
            <tr><td>Pro</td><td>60</td><td>1,000</td><td>100 MB</td></tr>
            <tr><td>Enterprise</td><td>300</td><td>Unlimited</td><td>500 MB</td></tr>
        </tbody>
    </table>

    <!-- Webhook payload -->
    <h3 style="font-size:1rem;font-weight:600;margin-bottom:.75rem;">Webhook Payload</h3>
    <pre style="background:var(--cx-code-bg);border:1px solid var(--border-color);border-radius:8px;padding:1rem;font-size:.8rem;overflow-x:auto;">{
  "job_id": 42,
  "status": "completed",
  "event": "job.completed",
  "data": { "ai_result": { ... } },
  "timestamp": "2026-01-01T12:00:00Z"
}</pre>
</div>
