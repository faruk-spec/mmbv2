<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div style="padding:28px;">
    <div style="margin-bottom:24px;">
        <h1 style="font-size:1.5rem;font-weight:700;color:var(--text-primary,#e8eefc);margin:0 0 4px;">
            <i class="fas fa-envelope-open-text" style="color:#00f0ff;margin-right:10px;"></i>Email Templates
        </h1>
        <p style="color:var(--text-secondary,#8892a6);margin:0;font-size:.85rem;">Edit the HTML email templates sent automatically by the system.</p>
    </div>

    <?php
    // Friendly labels and descriptions for known templates
    $templateMeta = [
        'support-ticket-created' => [
            'label'    => 'Support — Ticket Created',
            'desc'     => 'Sent to the user when they raise a new support ticket.',
            'category' => 'support',
        ],
        'support-ticket-reply'   => [
            'label'    => 'Support — Agent Reply',
            'desc'     => 'Sent to the user when a support agent replies to their ticket.',
            'category' => 'support',
        ],
        'support-ticket-closed'  => [
            'label'    => 'Support — Ticket Closed',
            'desc'     => 'Sent to the user when their support ticket is marked as closed.',
            'category' => 'support',
        ],
    ];

    // Group by category
    $grouped = ['support' => [], 'other' => []];
    foreach ($templates as $tpl) {
        $meta = $templateMeta[$tpl['name']] ?? null;
        $tpl['label']    = $meta['label']    ?? ucwords(str_replace(['-', '_'], ' ', $tpl['name']));
        $tpl['desc']     = $meta['desc']     ?? '';
        $tpl['category'] = $meta['category'] ?? 'other';
        $grouped[$tpl['category']][] = $tpl;
    }
    ?>

    <?php if (!empty($grouped['support'])): ?>
    <!-- Support Auto-Reply Templates -->
    <div style="margin-bottom:32px;">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;background:rgba(255,159,67,.12);color:#ff9f43;border-radius:20px;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">
                <i class="fas fa-headset"></i> Customer Support
            </span>
            <span style="font-size:.82rem;color:var(--text-secondary,#8892a6);">Auto-reply emails for support tickets</span>
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">
            <?php foreach ($grouped['support'] as $tpl): ?>
            <div style="background:var(--bg-card,#0f0f18);border:1px solid rgba(255,159,67,.2);border-radius:12px;padding:20px;display:flex;flex-direction:column;gap:14px;">
                <div>
                    <div style="font-weight:600;color:var(--text-primary,#e8eefc);font-size:.92rem;margin-bottom:4px;">
                        <i class="fas fa-envelope" style="color:#ff9f43;margin-right:7px;"></i><?= htmlspecialchars($tpl['label']) ?>
                    </div>
                    <?php if ($tpl['desc']): ?>
                    <div style="font-size:.8rem;color:var(--text-secondary,#8892a6);line-height:1.45;"><?= htmlspecialchars($tpl['desc']) ?></div>
                    <?php endif; ?>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:.75rem;color:var(--text-secondary,#8892a6);">
                        Modified: <?= date('M j, Y H:i', $tpl['modified']) ?>
                    </span>
                    <div style="display:flex;gap:8px;">
                        <a href="/admin/email/templates/view?template=<?= urlencode($tpl['name']) ?>"
                           style="display:inline-flex;align-items:center;gap:5px;padding:6px 12px;background:rgba(0,240,255,.1);color:#00f0ff;border-radius:6px;text-decoration:none;font-size:.78rem;font-weight:500;">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <a href="/admin/email/templates/edit?template=<?= urlencode($tpl['name']) ?>"
                           style="display:inline-flex;align-items:center;gap:5px;padding:6px 12px;background:linear-gradient(135deg,rgba(255,159,67,.2),rgba(255,46,196,.15));color:#ff9f43;border-radius:6px;text-decoration:none;font-size:.78rem;font-weight:600;">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- All Other Templates -->
    <?php if (!empty($grouped['other'])): ?>
    <div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;background:rgba(0,240,255,.1);color:#00f0ff;border-radius:20px;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">
                <i class="fas fa-envelope"></i> System
            </span>
            <span style="font-size:.82rem;color:var(--text-secondary,#8892a6);">Other notification emails</span>
        </div>
        <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:12px;overflow:hidden;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="border-bottom:1px solid var(--border-color,rgba(255,255,255,.08));">
                        <th style="padding:12px 16px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Template</th>
                        <th style="padding:12px 16px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Size</th>
                        <th style="padding:12px 16px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Modified</th>
                        <th style="padding:12px 16px;text-align:left;color:var(--text-secondary,#8892a6);font-size:.75rem;font-weight:600;text-transform:uppercase;letter-spacing:.05em;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($grouped['other'] as $tpl): ?>
                    <tr style="border-bottom:1px solid var(--border-color,rgba(255,255,255,.04));transition:background .15s;" onmouseover="this.style.background='rgba(255,255,255,.02)'" onmouseout="this.style.background=''">
                        <td style="padding:12px 16px;">
                            <div style="font-weight:600;color:var(--text-primary,#e8eefc);font-size:.88rem;"><?= htmlspecialchars($tpl['label']) ?></div>
                            <div style="font-size:.75rem;color:var(--text-secondary,#8892a6);font-family:monospace;margin-top:2px;"><?= htmlspecialchars($tpl['name']) ?>.php</div>
                        </td>
                        <td style="padding:12px 16px;color:var(--text-secondary,#8892a6);font-size:.83rem;"><?= number_format($tpl['size']) ?> B</td>
                        <td style="padding:12px 16px;color:var(--text-secondary,#8892a6);font-size:.83rem;"><?= date('M j, Y H:i', $tpl['modified']) ?></td>
                        <td style="padding:12px 16px;">
                            <div style="display:flex;gap:8px;">
                                <a href="/admin/email/templates/view?template=<?= urlencode($tpl['name']) ?>"
                                   style="display:inline-flex;align-items:center;gap:5px;padding:5px 11px;background:rgba(0,240,255,.1);color:#00f0ff;border-radius:6px;text-decoration:none;font-size:.78rem;font-weight:500;">
                                    <i class="fas fa-eye"></i> View
                                </a>
                                <a href="/admin/email/templates/edit?template=<?= urlencode($tpl['name']) ?>"
                                   style="display:inline-flex;align-items:center;gap:5px;padding:5px 11px;background:rgba(167,139,250,.1);color:#a78bfa;border-radius:6px;text-decoration:none;font-size:.78rem;font-weight:500;">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($templates)): ?>
    <div style="padding:60px;text-align:center;color:var(--text-secondary,#8892a6);">
        <i class="fas fa-envelope-open" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:14px;"></i>
        No email templates found.
    </div>
    <?php endif; ?>
</div>
<?php View::endSection(); ?>
