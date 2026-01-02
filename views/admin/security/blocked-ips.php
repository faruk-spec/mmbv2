<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div style="margin-bottom: 30px;">
    <a href="/admin/security" style="color: var(--text-secondary);">&larr; Back to Security</a>
    <h1 style="margin-top: 10px;">Blocked IPs</h1>
</div>

<?php if (Helpers::hasFlash('success')): ?>
    <div class="alert alert-success"><?= View::e(Helpers::getFlash('success')) ?></div>
<?php endif; ?>

<?php if (Helpers::hasFlash('error')): ?>
    <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
<?php endif; ?>

<div class="grid grid-3">
    <div style="grid-column: span 2;">
        <div class="card">
            <?php if (empty($blockedIps)): ?>
                <p style="color: var(--text-secondary); text-align: center; padding: 30px;">No blocked IPs</p>
            <?php else: ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>IP Address</th>
                            <th>Reason</th>
                            <th>Expires</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($blockedIps as $ip): ?>
                            <tr>
                                <td style="font-family: monospace;"><?= View::e($ip['ip_address']) ?></td>
                                <td><?= View::e($ip['reason'] ?: 'No reason specified') ?></td>
                                <td><?= $ip['expires_at'] ? Helpers::formatDate($ip['expires_at']) : 'Never' ?></td>
                                <td>
                                    <form method="POST" action="/admin/security/unblock-ip/<?= $ip['id'] ?>" style="display: inline;">
                                        <?= \Core\Security::csrfField() ?>
                                        <button type="submit" class="btn btn-sm btn-secondary">Unblock</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
    
    <div>
        <div class="card">
            <h4 style="margin-bottom: 20px;">Block IP Address</h4>
            
            <form method="POST" action="/admin/security/block-ip">
                <?= \Core\Security::csrfField() ?>
                
                <div class="form-group">
                    <label class="form-label">IP Address</label>
                    <input type="text" name="ip_address" class="form-input" placeholder="192.168.1.1" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Reason (optional)</label>
                    <input type="text" name="reason" class="form-input" placeholder="Suspicious activity">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Duration</label>
                    <select name="duration" class="form-input">
                        <option value="permanent">Permanent</option>
                        <option value="1 hour">1 Hour</option>
                        <option value="1 day">1 Day</option>
                        <option value="1 week">1 Week</option>
                        <option value="1 month">1 Month</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-danger" style="width: 100%;">Block IP</button>
            </form>
        </div>
    </div>
</div>
<?php View::endSection(); ?>
