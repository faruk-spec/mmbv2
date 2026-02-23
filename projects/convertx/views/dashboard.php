<?php
/**
 * ConvertX Dashboard View
 */
$currentView = 'dashboard';
?>

<!-- Page header -->
<div class="page-header">
    <h1>ConvertX Dashboard</h1>
    <p>AI-powered document conversion — fast, smart, and secure</p>
</div>

<!-- ── Stats ── -->
<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-icon"><i class="fa-solid fa-bolt"></i></span>
        <span class="value"><?= (int)($usage['total_jobs'] ?? 0) ?></span>
        <span class="label">Jobs This Month</span>
    </div>
    <div class="stat-card">
        <span class="stat-icon"><i class="fa-solid fa-circle-check"></i></span>
        <span class="value"><?= (int)($usage['completed'] ?? 0) ?></span>
        <span class="label">Completed</span>
    </div>
    <div class="stat-card">
        <span class="stat-icon"><i class="fa-solid fa-circle-xmark"></i></span>
        <span class="value"><?= (int)($usage['failed'] ?? 0) ?></span>
        <span class="label">Failed</span>
    </div>
    <div class="stat-card">
        <span class="stat-icon"><i class="fa-solid fa-microchip"></i></span>
        <span class="value"><?= number_format((int)($usage['tokens_used'] ?? 0)) ?></span>
        <span class="label">AI Tokens Used</span>
    </div>
</div>

<!-- ── Quick actions ── -->
<div style="display:flex;gap:1rem;margin-bottom:2rem;flex-wrap:wrap;">
    <a href="/projects/convertx/convert"
       class="cx-quick-card"
       style="background:linear-gradient(135deg,var(--cx-primary),var(--cx-secondary));box-shadow:0 4px 16px rgba(99,102,241,.35);">
        <i class="fa-solid fa-arrow-right-arrow-left qc-icon"></i>
        <strong>Convert File</strong>
        <p>Upload &amp; transform any format</p>
    </a>
    <a href="/projects/convertx/batch"
       class="cx-quick-card"
       style="background:linear-gradient(135deg,#7c3aed,#06b6d4);box-shadow:0 4px 16px rgba(124,58,237,.3);">
        <i class="fa-solid fa-layer-group qc-icon"></i>
        <strong>Batch Convert</strong>
        <p>Process 50 files at once</p>
    </a>
    <a href="/projects/convertx/docs"
       class="cx-quick-card"
       style="background:linear-gradient(135deg,#0891b2,#10b981);box-shadow:0 4px 16px rgba(8,145,178,.3);">
        <i class="fa-solid fa-code qc-icon"></i>
        <strong>REST API</strong>
        <p>Integrate into your app</p>
    </a>
</div>

<!-- ── AI Intelligence Hub ── -->
<div class="card" style="border-color:var(--border-hover);background:linear-gradient(135deg,rgba(99,102,241,.06),rgba(139,92,246,.04));">
    <div class="card-header" style="border-color:rgba(99,102,241,.2);">
        <div style="width:40px;height:40px;background:linear-gradient(135deg,var(--cx-primary),var(--cx-secondary));border-radius:.625rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            <i class="fa-solid fa-wand-magic-sparkles" style="color:#fff;font-size:1rem;"></i>
        </div>
        <div>
            <div style="font-weight:700;color:var(--text-primary);">AI Intelligence Hub</div>
            <div style="font-size:.78rem;font-weight:400;color:var(--text-secondary);">Smart conversion enhancement</div>
        </div>
        <span class="ai-badge" style="margin-left:auto;">✨ AI POWERED</span>
    </div>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(130px,1fr));gap:.75rem;">
        <a href="/projects/convertx/convert?ai=ocr" class="cx-ai-tile">
            <div class="tile-icon" style="background:linear-gradient(135deg,var(--cx-primary),var(--cx-secondary));">
                <i class="fa-solid fa-eye"></i>
            </div>
            <div class="tile-title">OCR</div>
            <div class="tile-desc">Extract text from images</div>
        </a>

        <a href="/projects/convertx/convert?ai=summarize" class="cx-ai-tile">
            <div class="tile-icon" style="background:linear-gradient(135deg,#7c3aed,#06b6d4);">
                <i class="fa-solid fa-list-check"></i>
            </div>
            <div class="tile-title">Summarize</div>
            <div class="tile-desc">AI document summary</div>
        </a>

        <a href="/projects/convertx/convert?ai=translate" class="cx-ai-tile">
            <div class="tile-icon" style="background:linear-gradient(135deg,#0891b2,#10b981);">
                <i class="fa-solid fa-language"></i>
            </div>
            <div class="tile-title">Translate</div>
            <div class="tile-desc">10+ languages</div>
        </a>

        <a href="/projects/convertx/convert?ai=classify" class="cx-ai-tile">
            <div class="tile-icon" style="background:linear-gradient(135deg,#f59e0b,#ef4444);">
                <i class="fa-solid fa-tags"></i>
            </div>
            <div class="tile-title">Classify</div>
            <div class="tile-desc">Auto document type</div>
        </a>
    </div>
</div>

<!-- ── Recent conversions ── -->
<div class="card">
    <div class="card-header" style="justify-content:space-between;">
        <span><i class="fa-solid fa-clock-rotate-left"></i> Recent Conversions</span>
        <a href="/projects/convertx/history" style="font-size:.8rem;color:var(--cx-primary);text-decoration:none;">View all →</a>
    </div>

    <?php if (empty($recent)): ?>
        <div style="text-align:center;padding:2.5rem 1rem;">
            <i class="fa-solid fa-inbox" style="font-size:3rem;background:linear-gradient(135deg,var(--cx-primary),var(--cx-accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;margin-bottom:.75rem;display:block;"></i>
            <p style="color:var(--text-secondary);font-size:.9rem;">No conversions yet.</p>
            <a href="/projects/convertx/convert" class="btn btn-primary" style="margin-top:1rem;">
                <i class="fa-solid fa-arrow-right-arrow-left"></i> Start your first conversion
            </a>
        </div>
    <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="cx-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>File</th>
                        <th>Conversion</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent as $job): ?>
                    <tr>
                        <td style="color:var(--text-secondary);"><?= (int)$job['id'] ?></td>
                        <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:var(--text-primary);">
                            <?= htmlspecialchars($job['input_filename'] ?? '') ?>
                        </td>
                        <td>
                            <span style="color:var(--text-secondary);font-size:.8rem;"><?= htmlspecialchars(strtoupper($job['input_format'] ?? '')) ?></span>
                            <i class="fa-solid fa-arrow-right" style="font-size:.6rem;margin:0 .35rem;color:var(--text-secondary);"></i>
                            <span style="color:var(--cx-accent);font-weight:600;font-size:.8rem;"><?= htmlspecialchars(strtoupper($job['output_format'] ?? '')) ?></span>
                        </td>
                        <td><span class="badge badge-<?= htmlspecialchars($job['status']) ?>"><?= htmlspecialchars(ucfirst($job['status'])) ?></span></td>
                        <td style="color:var(--text-secondary);font-size:.78rem;"><?= htmlspecialchars(substr($job['created_at'] ?? '', 0, 16)) ?></td>
                        <td>
                            <?php if ($job['status'] === 'completed'): ?>
                                <a href="/projects/convertx/job/<?= (int)$job['id'] ?>/download" class="btn btn-success btn-sm">
                                    <i class="fa-solid fa-download"></i> Download
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
