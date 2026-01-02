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
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border-color);">
                    <th style="padding: 12px; text-align: left; color: var(--text-secondary);">Preview</th>
                    <th style="padding: 12px; text-align: left; color: var(--text-secondary);">Content</th>
                    <th style="padding: 12px; text-align: left; color: var(--text-secondary);">Type</th>
                    <th style="padding: 12px; text-align: left; color: var(--text-secondary);">Created</th>
                    <th style="padding: 12px; text-align: left; color: var(--text-secondary);">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $qr): ?>
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td style="padding: 12px;">
                            <div class="qr-preview" style="padding: 5px;">
                                <img src="<?= htmlspecialchars($qr['image']) ?>" alt="QR" style="width: 50px; height: 50px;">
                            </div>
                        </td>
                        <td style="padding: 12px;"><?= htmlspecialchars(substr($qr['content'], 0, 40)) ?>...</td>
                        <td style="padding: 12px;"><?= ucfirst($qr['type']) ?></td>
                        <td style="padding: 12px;"><?= $qr['created_at'] ?></td>
                        <td style="padding: 12px;">
                            <a href="<?= htmlspecialchars($qr['image']) ?>" download class="btn btn-secondary" style="padding: 6px 12px; font-size: 12px;">Download</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
