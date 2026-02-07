<a href="/projects/qr" class="back-link">← Back to Dashboard</a>

<h1 style="margin-bottom: 30px;">QR Code History</h1>

<div class="card">
    <?php if (empty($history)): ?>
        <div style="text-align: center; padding: 60px 20px; color: var(--text-secondary);">
            <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="opacity: 0.5; margin-bottom: 15px;">
                <rect x="3" y="3" width="7" height="7"/>
                <rect x="14" y="3" width="7" height="7"/>
                <rect x="14" y="14" width="7" height="7"/>
                <rect x="3" y="14" width="7" height="7"/>
            </svg>
            <h3 style="margin-bottom: 10px;">No QR Codes Yet</h3>
            <p style="margin-bottom: 20px;">Your generated QR codes will appear here.</p>
            <a href="/projects/qr/generate" class="btn btn-primary">Generate Your First QR Code</a>
        </div>
    <?php else: ?>
        <div style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <table style="width: 100%; border-collapse: collapse; min-width: 60rem; table-layout: fixed;">
                <thead>
                    <tr style="border-bottom: 2px solid var(--border-color);">
                        <th style="padding: 0.75rem; text-align: left; color: var(--text-secondary); font-weight: 600; width: 5rem; white-space: nowrap;">Preview</th>
                        <th style="padding: 0.75rem; text-align: left; color: var(--text-secondary); font-weight: 600; width: 20rem; white-space: nowrap;">Content</th>
                        <th style="padding: 0.75rem; text-align: left; color: var(--text-secondary); font-weight: 600; width: 6rem; white-space: nowrap;">Type</th>
                        <th style="padding: 0.75rem; text-align: left; color: var(--text-secondary); font-weight: 600; width: 5rem; white-space: nowrap;">Size</th>
                        <th style="padding: 0.75rem; text-align: left; color: var(--text-secondary); font-weight: 600; width: 5rem; white-space: nowrap;">Scans</th>
                        <th style="padding: 0.75rem; text-align: left; color: var(--text-secondary); font-weight: 600; width: 8rem; white-space: nowrap;">Created</th>
                        <th style="padding: 0.75rem; text-align: left; color: var(--text-secondary); font-weight: 600; width: 14rem; white-space: nowrap;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($history as $qr): ?>
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td style="padding: 0.75rem; white-space: nowrap;">
                                <div style="background: white; padding: 0.5rem; border-radius: 0.25rem; display: inline-block;">
                                    <div id="qr-<?= $qr['id'] ?>" style="width: 3.75rem; height: 3.75rem;"></div>
                                </div>
                            </td>
                            <td style="padding: 0.75rem; max-width: 20rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" 
                                title="<?= htmlspecialchars($qr['content']) ?>">
                                <?= htmlspecialchars(substr($qr['content'], 0, 50)) ?><?= strlen($qr['content']) > 50 ? '...' : '' ?>
                            </td>
                            <td style="padding: 0.75rem; white-space: nowrap;">
                                <span style="background: var(--bg-secondary); padding: 0.25rem 0.5rem; border-radius: 0.25rem; font-size: 0.75rem;">
                                    <?= ucfirst(htmlspecialchars($qr['type'])) ?>
                                </span>
                            </td>
                            <td style="padding: 0.75rem; white-space: nowrap;">
                                <?= htmlspecialchars($qr['size'] ?? 200) ?>px
                            </td>
                            <td style="padding: 0.75rem; white-space: nowrap;">
                                <?= (int)($qr['scan_count'] ?? 0) ?>
                            </td>
                            <td style="padding: 0.75rem; color: var(--text-secondary); font-size: 0.875rem; white-space: nowrap;">
                                <?= date('M j, Y', strtotime($qr['created_at'])) ?>
                            </td>
                            <td style="padding: 0.75rem; white-space: nowrap;">
                                <div style="display: flex; gap: 0.5rem; flex-wrap: nowrap;">
                                    <a href="/projects/qr/view/<?= $qr['id'] ?>" 
                                       class="btn btn-secondary" 
                                       style="padding: 0.375rem 0.75rem; font-size: 0.75rem; text-decoration: none; white-space: nowrap;">
                                        👁️ View
                                    </a>
                                    <?php if ($qr['is_dynamic'] ?? false): ?>
                                    <a href="/projects/qr/edit/<?= $qr['id'] ?>" 
                                       class="btn btn-secondary" 
                                       style="padding: 0.375rem 0.75rem; font-size: 0.75rem; text-decoration: none; background: rgba(0, 123, 255, 0.1); border-color: #007bff; color: #007bff; white-space: nowrap;">
                                        ✏️ Edit
                                    </a>
                                    <?php endif; ?>
                                    <button onclick="downloadQRCode(<?= $qr['id'] ?>)" 
                                            class="btn btn-secondary" 
                                            style="padding: 0.375rem 0.75rem; font-size: 0.75rem; white-space: nowrap;">
                                        📥 Download
                                    </button>
                                    <form method="POST" action="/projects/qr/delete" style="display: inline;">
                                        <input type="hidden" name="_csrf_token" value="<?= \Core\Security::generateCsrfToken() ?>">
                                        <input type="hidden" name="id" value="<?= $qr['id'] ?>">
                                        <button type="submit" 
                                                class="btn btn-secondary" 
                                                style="padding: 0.375rem 0.75rem; font-size: 0.75rem; background: rgba(255, 107, 107, 0.1); border-color: #ff6b6b; color: #ff6b6b; white-space: nowrap;"
                                                onclick="return confirm('Are you sure you want to delete this QR code?')">
                                            🗑️ Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <div style="margin-top: 20px; padding: 15px; background: var(--bg-secondary); border-radius: 8px; text-align: center;">
            <p style="color: var(--text-secondary); margin: 0;">
                Total QR Codes: <strong><?= count($history) ?></strong>
            </p>
        </div>
    <?php endif; ?>
</div>

<!-- QR Code Library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
// Generate QR codes for history
<?php foreach ($history as $qr): ?>
new QRCode(document.getElementById("qr-<?= $qr['id'] ?>"), {
    text: <?= json_encode($qr['content']) ?>,
    width: 60,
    height: 60,
    colorDark: "<?= htmlspecialchars($qr['foreground_color'] ?? '#000000') ?>",
    colorLight: "<?= htmlspecialchars($qr['background_color'] ?? '#ffffff') ?>",
    correctLevel: QRCode.CorrectLevel.H
});
<?php endforeach; ?>

// Download QR code
function downloadQRCode(qrId) {
    const canvas = document.querySelector('#qr-' + qrId + ' canvas');
    if (canvas) {
        const dataUrl = canvas.toDataURL('image/png');
        const link = document.createElement('a');
        link.download = 'qr-code-' + qrId + '.png';
        link.href = dataUrl;
        link.click();
    }
}
</script>

<style>
    @media (max-width: 768px) {
        table {
            font-size: 12px;
        }
        
        th, td {
            padding: 8px !important;
        }
        
        .btn {
            padding: 4px 8px !important;
            font-size: 11px !important;
        }
    }
</style>
