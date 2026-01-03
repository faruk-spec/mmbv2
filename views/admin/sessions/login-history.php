<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin/layout'); ?>

<?php View::section('content'); ?>
<div class="admin-content">
    <div class="page-header">
        <h1>Login History</h1>
        <p>View audit log of all login attempts</p>
    </div>
    
    <div class="card">
        <div class="card-header">
            <form method="GET" action="/admin/sessions/login-history" style="display: flex; gap: 10px; flex-wrap: wrap;">
                <input type="text" name="search" class="form-input" 
                       placeholder="Search by email or IP..." 
                       value="<?= View::e($search ?? '') ?>" 
                       style="min-width: 250px;">
                
                <select name="status" class="form-input" style="min-width: 150px;">
                    <option value="">All Status</option>
                    <option value="success" <?= ($status ?? '') === 'success' ? 'selected' : '' ?>>Success</option>
                    <option value="failed" <?= ($status ?? '') === 'failed' ? 'selected' : '' ?>>Failed</option>
                    <option value="blocked" <?= ($status ?? '') === 'blocked' ? 'selected' : '' ?>>Blocked</option>
                </select>
                
                <select name="method" class="form-input" style="min-width: 150px;">
                    <option value="">All Methods</option>
                    <option value="email_password" <?= ($method ?? '') === 'email_password' ? 'selected' : '' ?>>Email/Password</option>
                    <option value="google_oauth" <?= ($method ?? '') === 'google_oauth' ? 'selected' : '' ?>>Google OAuth</option>
                    <option value="remember_token" <?= ($method ?? '') === 'remember_token' ? 'selected' : '' ?>>Remember Token</option>
                    <option value="2fa" <?= ($method ?? '') === '2fa' ? 'selected' : '' ?>>2FA</option>
                </select>
                
                <button type="submit" class="btn btn-primary">Filter</button>
                <a href="/admin/sessions/login-history" class="btn btn-secondary">Reset</a>
            </form>
        </div>
        
        <table class="data-table">
            <thead>
                <tr>
                    <th>Time</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Method</th>
                    <th>IP Address</th>
                    <th>Status</th>
                    <th>Failure Reason</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($history)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No login history found.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($history as $entry): ?>
                        <tr>
                            <td>
                                <?= date('M d, Y', strtotime($entry['created_at'])) ?>
                                <br>
                                <small style="color: var(--text-secondary);"><?= date('H:i:s', strtotime($entry['created_at'])) ?></small>
                            </td>
                            <td>
                                <?php if ($entry['user_name']): ?>
                                    <?= View::e($entry['user_name']) ?>
                                <?php else: ?>
                                    <span style="color: var(--text-secondary);">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?= View::e($entry['email']) ?></td>
                            <td>
                                <?php
                                $methodBadges = [
                                    'email_password' => ['class' => 'badge-primary', 'text' => 'Email/Password'],
                                    'google_oauth' => ['class' => 'badge-info', 'text' => 'Google OAuth'],
                                    'remember_token' => ['class' => 'badge-secondary', 'text' => 'Remember Me'],
                                    '2fa' => ['class' => 'badge-warning', 'text' => '2FA']
                                ];
                                $methodInfo = $methodBadges[$entry['login_method']] ?? ['class' => 'badge-secondary', 'text' => $entry['login_method']];
                                ?>
                                <span class="badge <?= $methodInfo['class'] ?>"><?= $methodInfo['text'] ?></span>
                            </td>
                            <td><code><?= View::e($entry['ip_address']) ?></code></td>
                            <td>
                                <?php if ($entry['status'] === 'success'): ?>
                                    <span class="badge badge-success">Success</span>
                                <?php elseif ($entry['status'] === 'failed'): ?>
                                    <span class="badge badge-danger">Failed</span>
                                <?php else: ?>
                                    <span class="badge badge-warning">Blocked</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($entry['failure_reason']): ?>
                                    <small style="color: var(--danger);"><?= View::e($entry['failure_reason']) ?></small>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <?php if (!empty($pagination) && $pagination['total'] > 1): ?>
        <div class="pagination">
            <?php 
            $params = [];
            if (!empty($search)) $params[] = 'search=' . urlencode($search);
            if (!empty($status)) $params[] = 'status=' . urlencode($status);
            if (!empty($method)) $params[] = 'method=' . urlencode($method);
            $queryString = !empty($params) ? '&' . implode('&', $params) : '';
            ?>
            <?php for ($i = 1; $i <= $pagination['total']; $i++): ?>
                <?php if ($i == $pagination['current']): ?>
                    <span class="page-current"><?= $i ?></span>
                <?php else: ?>
                    <a href="?page=<?= $i ?><?= $queryString ?>" class="page-link"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php View::endSection(); ?>
