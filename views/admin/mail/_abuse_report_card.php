<div class="card mb-3 card-outline <?= $report['status'] === 'pending' ? 'card-warning' : ($report['status'] === 'investigating' ? 'card-info' : ($report['status'] === 'resolved' ? 'card-success' : 'card-secondary')) ?>">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h5 class="mb-0">
                    <i class="fas fa-exclamation-triangle text-<?= $report['status'] === 'pending' ? 'warning' : ($report['status'] === 'investigating' ? 'info' : ($report['status'] === 'resolved' ? 'success' : 'secondary')) ?>"></i>
                    <?= View::e($report['abuse_type']) ?> - Report #<?= $report['id'] ?>
                </h5>
                <small class="text-muted">
                    Reported <?= date('M d, Y H:i', strtotime($report['reported_at'])) ?>
                    <?php if ($report['reporter_email']): ?>
                        by <?= View::e($report['reporter_email']) ?>
                    <?php endif; ?>
                </small>
            </div>
            <div class="col-md-4 text-right">
                <span class="badge badge-<?= $report['status'] === 'pending' ? 'warning' : ($report['status'] === 'investigating' ? 'info' : ($report['status'] === 'resolved' ? 'success' : 'secondary')) ?> badge-lg">
                    <?= ucfirst($report['status']) ?>
                </span>
            </div>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <strong>Reported Entity:</strong><br>
                <?php if ($report['mailbox_id']): ?>
                    <i class="fas fa-envelope"></i> Mailbox: <?= View::e($report['mailbox_email'] ?? 'N/A') ?>
                <?php elseif ($report['domain_id']): ?>
                    <i class="fas fa-globe"></i> Domain: <?= View::e($report['domain_name'] ?? 'N/A') ?>
                <?php elseif ($report['subscriber_id']): ?>
                    <i class="fas fa-user"></i> Subscriber: <?= View::e($report['subscriber_email'] ?? 'N/A') ?>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <strong>Severity:</strong>
                <span class="badge badge-<?= $report['severity'] === 'critical' ? 'danger' : ($report['severity'] === 'high' ? 'warning' : ($report['severity'] === 'medium' ? 'info' : 'secondary')) ?>">
                    <?= ucfirst($report['severity']) ?>
                </span>
            </div>
        </div>

        <div class="mt-3">
            <strong>Description:</strong>
            <p class="mb-0"><?= nl2br(View::e($report['description'])) ?></p>
        </div>

        <?php if ($report['evidence_data']): ?>
            <div class="mt-3">
                <strong>Evidence:</strong>
                <pre class="bg-light p-2 rounded"><?= View::e($report['evidence_data']) ?></pre>
            </div>
        <?php endif; ?>

        <?php if ($report['resolution_notes']): ?>
            <div class="mt-3 alert alert-info">
                <strong>Resolution Notes:</strong>
                <p class="mb-0"><?= nl2br(View::e($report['resolution_notes'])) ?></p>
                <?php if ($report['resolved_at']): ?>
                    <small class="text-muted">
                        Resolved on <?= date('M d, Y H:i', strtotime($report['resolved_at'])) ?>
                        <?php if ($report['resolved_by_name']): ?>
                            by <?= View::e($report['resolved_by_name']) ?>
                        <?php endif; ?>
                    </small>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="card-footer">
        <div class="row align-items-center">
            <div class="col-md-8">
                <?php if ($report['ip_address']): ?>
                    <small class="text-muted">
                        <i class="fas fa-network-wired"></i> IP: <?= View::e($report['ip_address']) ?>
                    </small>
                <?php endif; ?>
            </div>
            <div class="col-md-4 text-right">
                <?php if ($report['status'] === 'pending'): ?>
                    <button class="btn btn-sm btn-info" onclick="updateReportStatus(<?= $report['id'] ?>, 'investigating')">
                        <i class="fas fa-search"></i> Investigate
                    </button>
                    <button class="btn btn-sm btn-secondary" onclick="dismissReport(<?= $report['id'] ?>)">
                        <i class="fas fa-times"></i> Dismiss
                    </button>
                <?php elseif ($report['status'] === 'investigating'): ?>
                    <button class="btn btn-sm btn-success" onclick="resolveReport(<?= $report['id'] ?>)">
                        <i class="fas fa-check"></i> Resolve
                    </button>
                    <button class="btn btn-sm btn-secondary" onclick="dismissReport(<?= $report['id'] ?>)">
                        <i class="fas fa-times"></i> Dismiss
                    </button>
                <?php endif; ?>
                <button class="btn btn-sm btn-default" onclick="viewReportDetails(<?= $report['id'] ?>)">
                    <i class="fas fa-eye"></i> View Details
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function updateReportStatus(reportId, status) {
    if (!confirm(`Change report status to "${status}"?`)) {
        return;
    }

    fetch(`/admin/projects/mail/abuse/${reportId}/status`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to update status');
        }
    });
}

function dismissReport(reportId) {
    const reason = prompt('Enter reason for dismissing this report:');
    if (!reason) return;

    fetch(`/admin/projects/mail/abuse/${reportId}/dismiss`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ reason: reason })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to dismiss report');
        }
    });
}

function resolveReport(reportId) {
    const notes = prompt('Enter resolution notes:');
    if (!notes) return;

    fetch(`/admin/projects/mail/abuse/${reportId}/resolve`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ notes: notes })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Failed to resolve report');
        }
    });
}

function viewReportDetails(reportId) {
    window.location.href = `/admin/projects/mail/abuse/${reportId}`;
}
</script>
