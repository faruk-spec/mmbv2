<?php
/**
 * ConvertX Dashboard View
 */
$currentView = 'dashboard';
?>

<!-- ── Page header ── -->
<div class="page-header" style="margin-bottom:2rem;text-align:center;">
    <h1 style="font-size:2.2rem;font-weight:700;background:linear-gradient(135deg,var(--cx-primary),var(--cx-accent));-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;">
        ConvertX Dashboard
    </h1>
    <p style="color:var(--text-secondary);margin-top:.5rem;font-size:1rem;">
        AI-powered document conversion — fast, smart, and secure
    </p>
</div>

<!-- ── Stats row ── -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-bolt"></i></div>
        <div class="value"><?= (int) ($usage['total_jobs'] ?? 0) ?></div>
        <div class="label">Jobs This Month</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
        <div class="value"><?= (int) ($usage['completed'] ?? 0) ?></div>
        <div class="label">Completed</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-circle-xmark"></i></div>
        <div class="value"><?= (int) ($usage['failed'] ?? 0) ?></div>
        <div class="label">Failed</div>
    </div>
    <div class="stat-card">
        <div class="stat-icon"><i class="fa-solid fa-microchip"></i></div>
        <div class="value"><?= number_format((int) ($usage['tokens_used'] ?? 0)) ?></div>
        <div class="label">AI Tokens Used</div>
    </div>
</div>

<!-- ── Quick action cards ── -->
<div class="quick-actions-bar" style="display:flex;gap:1rem;margin-bottom:2rem;flex-wrap:wrap;">
    <a href="/projects/convertx/convert" style="flex:1;min-width:200px;max-width:260px;background:linear-gradient(135deg,var(--cx-primary),var(--cx-secondary));color:#fff;padding:1.25rem;border-radius:.75rem;text-decoration:none;text-align:center;box-shadow:0 4px 16px rgba(99,102,241,.35);transition:transform .2s,box-shadow .2s;display:block;"
       onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 28px rgba(99,102,241,.5)'"
       onmouseout="this.style.transform='';this.style.boxShadow='0 4px 16px rgba(99,102,241,.35)'">
        <i class="fa-solid fa-arrow-right-arrow-left" style="font-size:1.75rem;margin-bottom:.5rem;display:block;"></i>
        <strong style="font-size:1rem;">Convert File</strong>
        <p style="margin:.25rem 0 0;font-size:.8rem;opacity:.85;">Upload &amp; transform any format</p>
    </a>

    <a href="/projects/convertx/batch" style="flex:1;min-width:200px;max-width:260px;background:linear-gradient(135deg,#7c3aed,#06b6d4);color:#fff;padding:1.25rem;border-radius:.75rem;text-decoration:none;text-align:center;box-shadow:0 4px 16px rgba(124,58,237,.3);transition:transform .2s,box-shadow .2s;display:block;"
       onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 28px rgba(124,58,237,.45)'"
       onmouseout="this.style.transform='';this.style.boxShadow='0 4px 16px rgba(124,58,237,.3)'">
        <i class="fa-solid fa-layer-group" style="font-size:1.75rem;margin-bottom:.5rem;display:block;"></i>
        <strong style="font-size:1rem;">Batch Convert</strong>
        <p style="margin:.25rem 0 0;font-size:.8rem;opacity:.85;">Process 50 files at once</p>
    </a>

    <a href="/projects/convertx/docs" style="flex:1;min-width:200px;max-width:260px;background:linear-gradient(135deg,#0891b2,#10b981);color:#fff;padding:1.25rem;border-radius:.75rem;text-decoration:none;text-align:center;box-shadow:0 4px 16px rgba(8,145,178,.3);transition:transform .2s,box-shadow .2s;display:block;"
       onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 28px rgba(8,145,178,.45)'"
       onmouseout="this.style.transform='';this.style.boxShadow='0 4px 16px rgba(8,145,178,.3)'">
        <i class="fa-solid fa-code" style="font-size:1.75rem;margin-bottom:.5rem;display:block;"></i>
        <strong style="font-size:1rem;">REST API</strong>
        <p style="margin:.25rem 0 0;font-size:.8rem;opacity:.85;">Integrate into your app</p>
    </a>
</div>

<!-- ── AI Intelligence Hub ── -->
<div class="card" style="margin-bottom:2rem;background:linear-gradient(135deg,rgba(99,102,241,.08),rgba(139,92,246,.06));border:2px solid rgba(99,102,241,.25);">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:1.25rem;flex-wrap:wrap;gap:.75rem;">
        <h3 style="display:flex;align-items:center;gap:.75rem;font-size:1.2rem;margin:0;">
            <div style="width:48px;height:48px;background:linear-gradient(135deg,var(--cx-primary),var(--cx-secondary));border-radius:.75rem;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fa-solid fa-wand-magic-sparkles" style="font-size:1.3rem;color:#fff;"></i>
            </div>
            <div>
                <div style="font-weight:700;">AI Intelligence Hub</div>
                <div style="font-size:.8rem;font-weight:400;color:var(--text-secondary);margin-top:2px;">Smart conversion enhancement</div>
            </div>
        </h3>
        <span class="ai-badge">✨ AI POWERED</span>
    </div>

    <!-- AI capabilities grid -->
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:.75rem;">
        <a href="/projects/convertx/convert?ai=ocr" class="cx-ai-tile">
            <div style="width:36px;height:36px;margin:0 auto .5rem;background:linear-gradient(135deg,var(--cx-primary),var(--cx-secondary));border-radius:.5rem;display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-eye" style="color:#fff;font-size:.9rem;"></i>
            </div>
            <div style="font-size:.85rem;font-weight:600;margin-bottom:.2rem;color:var(--text-primary);">OCR</div>
            <div style="font-size:.7rem;color:var(--text-secondary);">Extract text from images</div>
        </a>

        <a href="/projects/convertx/convert?ai=summarize" class="cx-ai-tile">
            <div style="width:36px;height:36px;margin:0 auto .5rem;background:linear-gradient(135deg,#7c3aed,#06b6d4);border-radius:.5rem;display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-list-check" style="color:#fff;font-size:.9rem;"></i>
            </div>
            <div style="font-size:.85rem;font-weight:600;margin-bottom:.2rem;color:var(--text-primary);">Summarize</div>
            <div style="font-size:.7rem;color:var(--text-secondary);">AI document summary</div>
        </a>

        <a href="/projects/convertx/convert?ai=translate" class="cx-ai-tile">
            <div style="width:36px;height:36px;margin:0 auto .5rem;background:linear-gradient(135deg,#0891b2,#10b981);border-radius:.5rem;display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-language" style="color:#fff;font-size:.9rem;"></i>
            </div>
            <div style="font-size:.85rem;font-weight:600;margin-bottom:.2rem;color:var(--text-primary);">Translate</div>
            <div style="font-size:.7rem;color:var(--text-secondary);">10+ languages</div>
        </a>

        <a href="/projects/convertx/convert?ai=classify" class="cx-ai-tile">
            <div style="width:36px;height:36px;margin:0 auto .5rem;background:linear-gradient(135deg,#f59e0b,#ef4444);border-radius:.5rem;display:flex;align-items:center;justify-content:center;">
                <i class="fa-solid fa-tags" style="color:#fff;font-size:.9rem;"></i>
            </div>
            <div style="font-size:.85rem;font-weight:600;margin-bottom:.2rem;color:var(--text-primary);">Classify</div>
            <div style="font-size:.7rem;color:var(--text-secondary);">Auto document type</div>
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
                    <td style="color:var(--text-secondary);"><?= (int) $job['id'] ?></td>
                    <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
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
                            <a href="/projects/convertx/job/<?= (int) $job['id'] ?>/download" class="btn btn-success btn-sm">
                                <i class="fa-solid fa-download"></i> Download
                            </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
