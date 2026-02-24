<?php
use Core\View;
View::extend('admin');
?>
<?php View::section('content'); ?>
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-key text-primary"></i> ConvertX — API Keys</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
          <li class="breadcrumb-item"><a href="/admin/projects/convertx">ConvertX</a></li>
          <li class="breadcrumb-item active">API Keys</li>
        </ol>
      </div>
    </div>
  </div>
</div>
<section class="content">
  <div class="container-fluid">
    <!-- Flash messages -->
    <?php if (!empty($_SESSION['_flash']['success'])): ?>
    <div class="alert alert-success alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_SESSION['_flash']['success']) ?>
    </div>
    <?php unset($_SESSION['_flash']['success']); ?>
    <?php endif; ?>
    <?php if (!empty($_SESSION['_flash']['error'])): ?>
    <div class="alert alert-danger alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <?= htmlspecialchars($_SESSION['_flash']['error']) ?>
    </div>
    <?php unset($_SESSION['_flash']['error']); ?>
    <?php endif; ?>

    <!-- Show newly generated key -->
    <?php if (!empty($_SESSION['_flash']['new_key'])): ?>
    <div class="alert alert-warning" style="border:2px solid #ffc107;">
      <strong><i class="fas fa-exclamation-triangle"></i> New API Key Generated — Copy Now!</strong><br>
      <span>User: <?= htmlspecialchars($_SESSION['_flash']['new_key_user'] ?? '') ?></span><br>
      <div class="input-group mt-2">
        <input type="text" id="newKeyVal" class="form-control" value="<?= htmlspecialchars($_SESSION['_flash']['new_key']) ?>" readonly style="font-family:monospace;">
        <div class="input-group-append">
          <button class="btn btn-warning" onclick="navigator.clipboard.writeText(document.getElementById('newKeyVal').value).then(()=>this.textContent='Copied!')">Copy</button>
        </div>
      </div>
      <small class="text-muted">This key will not be shown again.</small>
    </div>
    <?php unset($_SESSION['_flash']['new_key']); unset($_SESSION['_flash']['new_key_user']); ?>
    <?php endif; ?>

    <!-- Generate API key for user -->
    <div class="card mb-3">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-plus-circle"></i> Generate API Key for User</h3>
      </div>
      <div class="card-body">
        <form method="POST" action="/admin/projects/convertx/api-keys/generate" class="form-inline">
          <input type="hidden" name="_token" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken()) ?>">
          <div class="form-group mr-3">
            <label class="mr-2">User ID:</label>
            <input type="number" name="user_id" class="form-control" placeholder="Enter user ID" required min="1">
          </div>
          <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Generate Key</button>
          <small class="ml-3 text-muted">This will revoke any existing ConvertX API key for the user and create a new one.</small>
        </form>
      </div>
    </div>

    <!-- Keys table -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">ConvertX API Keys</h3>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-hover mb-0">
            <thead><tr>
              <th>ID</th><th>User</th><th>Email</th><th>Key (partial)</th><th>Status</th><th>Created</th><th>Actions</th>
            </tr></thead>
            <tbody>
            <?php if (empty($keys)): ?>
              <tr><td colspan="7" class="text-center text-muted py-3">No ConvertX API keys found</td></tr>
            <?php else: ?>
              <?php foreach ($keys as $k): ?>
              <tr>
                <td><?= (int)$k['id'] ?></td>
                <td><?= htmlspecialchars($k['user_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($k['user_email'] ?? '') ?></td>
                <td><code><?= htmlspecialchars(substr($k['api_key'], 0, 12)) ?>…</code></td>
                <td>
                  <?php if ($k['is_active']): ?>
                    <span class="badge badge-success">Active</span>
                  <?php else: ?>
                    <span class="badge badge-secondary">Revoked</span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars(date('M d, Y', strtotime($k['created_at']))) ?></td>
                <td>
                  <?php if ($k['is_active']): ?>
                  <form method="POST" action="/admin/projects/convertx/api-keys/revoke" style="display:inline;">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken()) ?>">
                    <input type="hidden" name="key_id" value="<?= (int)$k['id'] ?>">
                    <button type="submit" class="btn btn-xs btn-warning" onclick="return confirm('Revoke this key?')">
                      <i class="fas fa-ban"></i> Revoke
                    </button>
                  </form>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
<?php View::endSection(); ?>
