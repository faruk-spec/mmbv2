<?php
use Core\View;
View::extend('admin');
?>
<?php View::section('content'); ?>
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-chart-line text-primary"></i> ConvertX — API Keys &amp; Usage</h1></div>
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
          <li class="breadcrumb-item"><a href="/admin/projects/convertx">ConvertX</a></li>
          <li class="breadcrumb-item active">API Keys &amp; Usage</li>
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

    <!-- Active user filter banner -->
    <?php if (!empty($filterUser)): ?>
    <div class="alert alert-info alert-dismissible" style="border-left:4px solid #17a2b8;">
      <i class="fas fa-filter"></i> Showing API keys for user <strong><?= htmlspecialchars($filterUser['name']) ?></strong>
      (<?= htmlspecialchars($filterUser['email']) ?>)
      — <a href="/admin/projects/convertx/api-keys" class="text-danger font-weight-bold">Clear filter</a>
    </div>
    <?php endif; ?>

    <!-- Per-User Usage Summary -->
    <?php if (!empty($userUsage) && empty($filterUser)): ?>
    <div class="card mb-3">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-users"></i> API Usage by User <small class="text-muted ml-2">(click a user to filter)</small></h3>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-hover mb-0">
            <thead><tr>
              <th>User</th><th>Email</th><th>Keys</th><th>Total Requests</th><th>Last Used</th><th>Filter</th>
            </tr></thead>
            <tbody>
            <?php foreach ($userUsage as $uu): ?>
            <tr>
              <td><?= htmlspecialchars($uu['user_name'] ?? 'Unknown') ?></td>
              <td><?= htmlspecialchars($uu['user_email'] ?? '') ?></td>
              <td><?= (int) $uu['key_count'] ?></td>
              <td><strong style="color:#007bff;"><?= number_format((int) $uu['total_requests']) ?></strong></td>
              <td><?= $uu['last_used_at'] ? date('M d, Y H:i', strtotime($uu['last_used_at'])) : '—' ?></td>
              <td>
                <?php if ($uu['id']): ?>
                <a href="/admin/projects/convertx/api-keys?user_id=<?= (int) $uu['id'] ?>" class="btn btn-xs btn-outline-secondary">
                  <i class="fas fa-search"></i> View Keys
                </a>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <?php endif; ?>

    <!-- Generate API key for user -->
    <div class="card mb-3">
      <div class="card-header">
        <h3 class="card-title"><i class="fas fa-plus-circle"></i> Generate API Key for User</h3>
      </div>
      <div class="card-body">
        <form method="POST" action="/admin/projects/convertx/api-keys/generate">
          <input type="hidden" name="_token" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken()) ?>">
          <div class="form-row align-items-end">
            <div class="col-md-5 mb-2">
              <label class="form-label font-weight-bold">Select User</label>
              <?php if (!empty($users)): ?>
              <select name="user_id" class="form-control" required id="userSelectDropdown">
                <option value="">— choose a user —</option>
                <?php foreach ($users as $u): ?>
                <option value="<?= (int)$u['id'] ?>">
                  <?= htmlspecialchars($u['name']) ?> &lt;<?= htmlspecialchars($u['email']) ?>&gt; (#<?= (int)$u['id'] ?>)
                </option>
                <?php endforeach; ?>
              </select>
              <?php else: ?>
              <input type="number" name="user_id" class="form-control" placeholder="Enter user ID" required min="1">
              <small class="text-muted">No users found — enter user ID manually.</small>
              <?php endif; ?>
            </div>
            <div class="col-md-4 mb-2">
              <label class="form-label font-weight-bold">Search / filter</label>
              <input type="text" id="userSearchInput" class="form-control" placeholder="Type to filter users…" oninput="filterUserSelect(this.value)">
            </div>
            <div class="col-auto mb-2">
              <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Generate Key</button>
            </div>
          </div>
          <small class="text-muted">This will revoke any existing ConvertX API key for the selected user and create a new one.</small>
        </form>
        <script>
        function filterUserSelect(query) {
            const sel = document.getElementById('userSelectDropdown');
            if (!sel) return;
            const q = query.toLowerCase();
            for (let i = 0; i < sel.options.length; i++) {
                const opt = sel.options[i];
                opt.hidden = q.length > 0 && opt.value !== '' && opt.text.toLowerCase().indexOf(q) === -1;
            }
        }
        </script>
      </div>
    </div>

    <!-- Keys table -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <?= $filterUser ? 'API Keys for ' . htmlspecialchars($filterUser['name']) : 'All ConvertX API Keys' ?>
        </h3>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-sm table-hover mb-0">
            <thead><tr>
              <th>ID</th><th>User</th><th>Email</th><th>Key (partial)</th><th>Requests</th><th>Last Used</th><th>Status</th><th>Created</th><th>Actions</th>
            </tr></thead>
            <tbody>
            <?php if (empty($keys)): ?>
              <tr><td colspan="9" class="text-center text-muted py-3">No ConvertX API keys found</td></tr>
            <?php else: ?>
              <?php foreach ($keys as $k): ?>
              <tr>
                <td><?= (int)$k['id'] ?></td>
                <td>
                  <a href="/admin/projects/convertx/api-keys?user_id=<?= (int) $k['user_id'] ?>" style="color:inherit;text-decoration:none;">
                    <?= htmlspecialchars($k['user_name'] ?? '') ?>
                  </a>
                </td>
                <td><?= htmlspecialchars($k['user_email'] ?? '') ?></td>
                <td><code><?= htmlspecialchars(substr($k['api_key'], 0, 12)) ?>…</code></td>
                <td><strong style="color:#007bff;"><?= number_format((int) ($k['request_count'] ?? 0)) ?></strong></td>
                <td style="font-size:.8rem;color:#6c757d;"><?= $k['last_used_at'] ? date('M d, Y H:i', strtotime($k['last_used_at'])) : '—' ?></td>
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

<!-- ── Request Logs Section ─────────────────────────────────────────────── -->
<?php
$recentLogs   = $recentLogs   ?? [];
$filterUserId = $filterUserId ?? null;
?>
<section class="content">
  <div class="container-fluid">
    <div class="card">
      <div class="card-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
        <h3 class="card-title">
          <i class="fas fa-list"></i>
          API Request Logs <?php if ($filterUserId): ?>(User #<?= (int)$filterUserId ?>)<?php endif; ?>
          <small style="font-weight:400;opacity:.7;">(last 200)</small>
        </h3>
        <?php if ($filterUserId): ?>
          <a href="/admin/projects/convertx/api-keys" class="btn btn-sm btn-default">
            <i class="fas fa-times"></i> Clear filter
          </a>
        <?php endif; ?>
      </div>
      <div class="card-body p-0">
        <?php if (empty($recentLogs)): ?>
          <div style="padding:2rem;text-align:center;color:#888;">
            No API request logs recorded yet.
          </div>
        <?php else: ?>
          <div style="overflow-x:auto;">
            <table class="table table-sm table-hover" style="font-size:.8rem;margin:0;">
              <thead>
                <tr>
                  <th>Time</th>
                  <th>User</th>
                  <th>Key Prefix</th>
                  <th>Endpoint</th>
                  <th>Method</th>
                  <th>Action</th>
                  <th>Status</th>
                  <th style="text-align:right;">Latency</th>
                  <th>IP</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($recentLogs as $log):
                    $sc = (int) $log['status_code'];
                    $badgeClass = $sc >= 500 ? 'badge-danger' : ($sc >= 400 ? 'badge-warning' : 'badge-success');
                ?>
                <tr>
                  <td style="white-space:nowrap;"><?= htmlspecialchars($log['created_at']) ?></td>
                  <td>
                    <a href="?user_id=<?= (int)$log['user_id'] ?>" title="Filter by user" style="text-decoration:none;">
                      <?= htmlspecialchars($log['user_name'] ?? '') ?>
                    </a>
                    <br><small class="text-muted"><?= htmlspecialchars($log['email'] ?? '') ?></small>
                  </td>
                  <td style="font-family:monospace;"><?= htmlspecialchars($log['api_key_prefix']) ?>...</td>
                  <td style="font-family:monospace;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= htmlspecialchars($log['endpoint']) ?>"><?= htmlspecialchars($log['endpoint']) ?></td>
                  <td style="font-family:monospace;"><?= htmlspecialchars($log['method']) ?></td>
                  <td><?= htmlspecialchars($log['action']) ?></td>
                  <td><span class="badge <?= $badgeClass ?>"><?= $sc ?></span></td>
                  <td style="text-align:right;"><?= number_format((int)$log['response_time']) ?> ms</td>
                  <td style="font-family:monospace;"><?= htmlspecialchars($log['ip_address']) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<?php View::endSection(); ?>
