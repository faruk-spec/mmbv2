<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>
<?php View::section('content'); ?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <div>
        <h1 style="margin:0;">Mail Send Log</h1>
        <p style="color:var(--text-secondary);margin:4px 0 0;">Audit trail of all outgoing emails</p>
    </div>
    <a href="/admin/mail/config" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Back</a>
</div>

<!-- View Email Modal -->
<div id="logViewModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:9999;overflow-y:auto;padding:24px;" onclick="if(event.target===this)closeLogModal()">
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:14px;max-width:700px;margin:0 auto;overflow:hidden;" onclick="event.stopPropagation()">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:16px 20px;border-bottom:1px solid var(--border-color);">
            <h3 style="margin:0;font-size:16px;"><i class="fas fa-envelope" style="color:var(--cyan);margin-right:8px;"></i> Email Details</h3>
            <button onclick="closeLogModal()" style="background:none;border:none;color:var(--text-secondary);font-size:20px;cursor:pointer;">✕</button>
        </div>
        <div style="padding:20px;">
            <table style="width:100%;border-collapse:collapse;margin-bottom:16px;font-size:14px;">
                <tr><td style="padding:6px 0;color:var(--text-secondary);width:100px;">To:</td><td id="lv-to" style="padding:6px 0;"></td></tr>
                <tr><td style="padding:6px 0;color:var(--text-secondary);">Subject:</td><td id="lv-subject" style="padding:6px 0;font-weight:600;"></td></tr>
                <tr><td style="padding:6px 0;color:var(--text-secondary);">Template:</td><td id="lv-template" style="padding:6px 0;"></td></tr>
                <tr><td style="padding:6px 0;color:var(--text-secondary);">Sent At:</td><td id="lv-sent-at" style="padding:6px 0;"></td></tr>
                <tr><td style="padding:6px 0;color:var(--text-secondary);">Status:</td><td id="lv-status" style="padding:6px 0;"></td></tr>
                <tr id="lv-error-row" style="display:none;"><td style="padding:6px 0;color:var(--red);">Error:</td><td id="lv-error" style="padding:6px 0;color:var(--red);font-size:13px;"></td></tr>
            </table>
            <div style="border:1px solid var(--border-color);border-radius:8px;overflow:hidden;">
                <div style="padding:8px 12px;background:var(--bg-secondary);font-size:12px;color:var(--text-secondary);">Email Body</div>
                <iframe id="lv-body-frame" style="width:100%;height:350px;border:none;background:#fff;"></iframe>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Recipient</th>
                    <th>Subject</th>
                    <th>Template</th>
                    <th>User</th>
                    <th>Status</th>
                    <th>Sent At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td><?= View::e($log['recipient']) ?></td>
                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= View::e($log['subject'] ?? '—') ?></td>
                    <td><?= $log['template_slug'] ? '<code>' . View::e($log['template_slug']) . '</code>' : '—' ?></td>
                    <td><?= View::e($log['user_name'] ?? '—') ?></td>
                    <td>
                        <?php if ($log['status'] === 'sent'): ?>
                            <span class="badge badge-success"><i class="fas fa-check"></i> Sent</span>
                        <?php else: ?>
                            <span class="badge badge-danger" title="<?= View::e($log['error_message'] ?? '') ?>">
                                <i class="fas fa-times"></i> Failed
                            </span>
                        <?php endif; ?>
                    </td>
                    <td style="white-space:nowrap;"><?= View::e($log['sent_at']) ?></td>
                    <td>
                        <button class="btn btn-sm btn-secondary" onclick='viewLog(<?= json_encode([
                            "to"       => $log["recipient"],
                            "subject"  => $log["subject"] ?? "",
                            "template" => $log["template_slug"] ?? "",
                            "sent_at"  => $log["sent_at"],
                            "status"   => $log["status"],
                            "error"    => $log["error_message"] ?? "",
                            "body"     => $log["body_html"] ?? "",
                        ], JSON_HEX_QUOT | JSON_HEX_TAG) ?>)'>
                            <i class="fas fa-eye"></i> View
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($logs)): ?>
                <tr><td colspan="7" style="text-align:center;color:var(--text-secondary);padding:32px;">No log entries yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div style="display:flex;justify-content:center;gap:8px;padding:16px 0 0;flex-wrap:wrap;">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="?page=<?= $i ?>" class="btn btn-sm <?= $i === $page ? 'btn-primary' : 'btn-secondary' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<script>
function viewLog(data) {
    document.getElementById('lv-to').textContent      = data.to;
    document.getElementById('lv-subject').textContent = data.subject || '(no subject)';
    document.getElementById('lv-template').textContent= data.template || '—';
    document.getElementById('lv-sent-at').textContent = data.sent_at;
    document.getElementById('lv-status').innerHTML    = data.status === 'sent'
        ? '<span style="color:var(--green);"><i class="fas fa-check"></i> Sent</span>'
        : '<span style="color:var(--red);"><i class="fas fa-times"></i> Failed</span>';

    const errRow = document.getElementById('lv-error-row');
    if (data.error) {
        errRow.style.display = '';
        document.getElementById('lv-error').textContent = data.error;
    } else {
        errRow.style.display = 'none';
    }

    const frame = document.getElementById('lv-body-frame');
    if (data.body) {
        frame.srcdoc = data.body;
    } else {
        frame.srcdoc = '<div style="padding:20px;font-family:sans-serif;color:#888;text-align:center;">No HTML body stored for this email.</div>';
    }

    document.getElementById('logViewModal').style.display = 'block';
}

function closeLogModal() {
    document.getElementById('logViewModal').style.display = 'none';
}
</script>

<?php View::endSection(); ?>
