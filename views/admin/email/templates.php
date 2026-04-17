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
    // Support ticket templates are managed in /admin/mail/templates — exclude them here.
    $supportSlugs = ['support-ticket-created','support-ticket-reply','support-ticket-closed','support-ticket-status-update'];

    // Friendly labels for remaining (system) templates
    $templateMeta = [
        'welcome'          => ['label' => 'Welcome Email',        'desc' => 'Sent to new users upon registration.'],
        'verify'           => ['label' => 'Email Verification',   'desc' => 'Sent to verify a user\'s email address.'],
        'password-reset'   => ['label' => 'Password Reset',       'desc' => 'Sent when a user requests a password reset.'],
        'password-changed' => ['label' => 'Password Changed',     'desc' => 'Sent after a user successfully changes their password.'],
        'login-alert'      => ['label' => 'Login Alert',          'desc' => 'Sent when a new login is detected on an account.'],
        'file-downloaded'  => ['label' => 'File Downloaded',      'desc' => 'Sent after a file download is completed.'],
        'ocr-completed'    => ['label' => 'OCR Completed',        'desc' => 'Sent when an OCR processing job finishes.'],
    ];
    ?>

    <!-- System Templates -->
    <div>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
            <span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;background:rgba(0,240,255,.1);color:#00f0ff;border-radius:20px;font-size:.78rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;">
                <i class="fas fa-envelope"></i> System
            </span>
            <span style="font-size:.82rem;color:var(--text-secondary,#8892a6);">PHP-file email templates</span>
            <span style="font-size:.78rem;color:var(--text-secondary,#8892a6);margin-left:4px;">
                — Support ticket templates are managed via
                <a href="/admin/mail/templates" style="color:#00f0ff;text-decoration:none;">Notification Templates</a>
            </span>
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
                    <?php $hasRows = false; foreach ($templates as $tpl):
                        if (in_array($tpl['name'], $supportSlugs, true)) continue;
                        $hasRows = true;
                        $meta = $templateMeta[$tpl['name']] ?? null;
                        $label = $meta['label'] ?? ucwords(str_replace(['-', '_'], ' ', $tpl['name']));
                        $desc  = $meta['desc']  ?? '';
                    ?>
                    <tr style="border-bottom:1px solid var(--border-color,rgba(255,255,255,.04));transition:background .15s;" onmouseover="this.style.background='rgba(255,255,255,.02)'" onmouseout="this.style.background=''">
                        <td style="padding:12px 16px;">
                            <div style="font-weight:600;color:var(--text-primary,#e8eefc);font-size:.88rem;"><?= htmlspecialchars($label) ?></div>
                            <?php if ($desc): ?><div style="font-size:.75rem;color:var(--text-secondary,#8892a6);margin-top:1px;"><?= htmlspecialchars($desc) ?></div><?php endif; ?>
                            <div style="font-size:.73rem;color:var(--text-secondary,#8892a6);font-family:monospace;margin-top:2px;opacity:.7;"><?= htmlspecialchars($tpl['name']) ?>.php</div>
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
                    <?php if (!$hasRows): ?>
                    <tr><td colspan="4" style="padding:40px;text-align:center;color:var(--text-secondary,#8892a6);font-size:.88rem;">No system email templates found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php View::endSection(); ?>
