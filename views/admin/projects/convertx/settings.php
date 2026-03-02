<?php
/**
 * ConvertX Admin — Settings / AI Providers (full CRUD)
 */
use Core\View;
View::extend('admin');
?>

<?php View::section('content'); ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-cog text-primary"></i> ConvertX — Settings</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
                    <li class="breadcrumb-item"><a href="/admin/projects/convertx">ConvertX</a></li>
                    <li class="breadcrumb-item active">Settings</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Flash messages -->
        <?php
        $flashSuccess = $_SESSION['_flash']['success'] ?? null;
        $flashError   = $_SESSION['_flash']['error']   ?? null;
        unset($_SESSION['_flash']['success'], $_SESSION['_flash']['error']);
        // Build a keyed lookup for quick access
        $providerBySlug = [];
        foreach ($providers as $p) {
            $providerBySlug[$p['slug']] = $p;
        }
        ?>
        <?php if ($flashSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($flashSuccess) ?>
        </div>
        <?php endif; ?>
        <?php if ($flashError): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($flashError) ?>
        </div>
        <?php endif; ?>

        <!-- Quick Config Cards -->
        <div class="row mb-3">
            <?php
            $quickProviders = [
                'openai'      => ['label' => 'OpenAI',      'icon' => 'fas fa-brain',   'color' => 'primary',  'defaultUrl' => 'https://api.openai.com',            'defaultModel' => 'gpt-4o-mini',          'defaultCaps' => 'ocr,summarization,translation,classification', 'defaultTiers' => 'pro,enterprise',       'defaultCost' => '0.000150'],
                'huggingface' => ['label' => 'HuggingFace', 'icon' => 'fas fa-robot',   'color' => 'warning',  'defaultUrl' => 'https://api-inference.huggingface.co', 'defaultModel' => 'facebook/bart-large-cnn', 'defaultCaps' => 'summarization,classification',             'defaultTiers' => 'free,pro,enterprise',  'defaultCost' => '0.000010'],
                'tesseract'   => ['label' => 'Tesseract',   'icon' => 'fas fa-file-alt','color' => 'success',  'defaultUrl' => '',                                  'defaultModel' => '',                     'defaultCaps' => 'ocr',                                      'defaultTiers' => 'free,pro,enterprise',  'defaultCost' => '0.000000'],
            ];
            foreach ($quickProviders as $slug => $meta):
                $p = $providerBySlug[$slug] ?? null;
                $configured = $p && !empty($p['api_key']);
                $active     = $p && $p['is_active'];
                $statusBadge = $p
                    ? ($active ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-secondary">Disabled</span>')
                    : '<span class="badge badge-light">Not Added</span>';
                $keyBadge = $configured
                    ? '<span class="badge badge-success"><i class="fas fa-key"></i> API Key Set</span>'
                    : '<span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> No API Key</span>';
            ?>
            <div class="col-lg-4 col-md-6 mb-3">
                <div class="card card-outline card-<?= $meta['color'] ?> h-100">
                    <div class="card-header d-flex align-items-center">
                        <i class="<?= $meta['icon'] ?> text-<?= $meta['color'] ?> mr-2"></i>
                        <h5 class="card-title mb-0"><?= $meta['label'] ?></h5>
                        <div class="ml-auto">
                            <?= $statusBadge ?>
                        </div>
                    </div>
                    <div class="card-body">
                        <?= $keyBadge ?>
                        <?php if ($p): ?>
                        <p class="text-muted small mt-2 mb-0">Model: <strong><?= htmlspecialchars($p['model'] ?? '—') ?></strong></p>
                        <?php if ($p['api_key']): ?>
                        <p class="text-muted small mb-0">Key: <code><?= htmlspecialchars(substr($p['api_key'], 0, 6)) ?>••••••••</code></p>
                        <?php endif; ?>
                        <?php else: ?>
                        <p class="text-muted small mt-2 mb-0">Provider not yet added to the system.</p>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-transparent border-top-0 pt-0">
                        <?php if ($p): ?>
                        <button type="button" class="btn btn-sm btn-outline-<?= $meta['color'] ?>" onclick="toggleEditProvider(<?= (int)$p['id'] ?>)">
                            <i class="fas fa-edit"></i> <?= $configured ? 'Update Key' : 'Add API Key' ?>
                        </button>
                        <?php else: ?>
                        <button type="button" class="btn btn-sm btn-<?= $meta['color'] ?>"
                                onclick="fillQuickAdd(<?= htmlspecialchars(json_encode([
                                    'name'          => $meta['label'],
                                    'slug'          => $slug,
                                    'base_url'      => $meta['defaultUrl'],
                                    'model'         => $meta['defaultModel'],
                                    'capabilities'  => $meta['defaultCaps'],
                                    'allowed_tiers' => $meta['defaultTiers'],
                                    'cost'          => $meta['defaultCost'],
                                ], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES) ?>)">
                            <i class="fas fa-plus"></i> Quick Add
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Add Provider -->
        <div class="card mb-4" id="card-add-provider">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-plus-circle"></i> Add New AI Provider</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/projects/convertx/settings/create-provider" id="form-add-provider">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken()) ?>">
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label>Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="qa-name" class="form-control" placeholder="e.g. OpenAI" required>
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Slug <span class="text-danger">*</span></label>
                            <input type="text" name="slug" id="qa-slug" class="form-control" placeholder="e.g. openai" required>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Base URL</label>
                            <input type="url" name="base_url" id="qa-base_url" class="form-control" placeholder="https://api.openai.com">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>API Key <small class="text-muted">(stored encrypted)</small></label>
                            <div class="input-group">
                                <input type="password" name="api_key" id="qa-api_key" class="form-control" placeholder="sk-...">
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePw('qa-api_key')"><i class="fas fa-eye"></i></button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Model</label>
                            <input type="text" name="model" id="qa-model" class="form-control" placeholder="gpt-4o-mini">
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Capabilities <small class="text-muted">(comma-separated)</small></label>
                            <input type="text" name="capabilities" id="qa-capabilities" class="form-control" placeholder="ocr,summarization,translation,classification">
                        </div>
                        <div class="col-md-3 form-group">
                            <label>Allowed Tiers <small class="text-muted">(comma-separated)</small></label>
                            <input type="text" name="allowed_tiers" id="qa-allowed_tiers" class="form-control" value="free,pro,enterprise">
                        </div>
                        <div class="col-md-1 form-group">
                            <label>Priority</label>
                            <input type="number" name="priority" id="qa-priority" class="form-control" value="10" min="1" max="100">
                        </div>
                        <div class="col-md-2 form-group">
                            <label>Cost / 1K tokens ($)</label>
                            <input type="number" name="cost_per_1k_tokens" id="qa-cost" class="form-control" value="0.000150" step="0.000001" min="0">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Add Provider</button>
                </form>
            </div>
        </div>

        <!-- Providers list -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-robot"></i> AI Providers</h3>
                <div class="card-tools">
                    <span class="badge badge-primary"><?= count($providers) ?> provider<?= count($providers) !== 1 ? 's' : '' ?></span>
                </div>
            </div>
            <div class="card-body p-0">
                <?php if (empty($providers)): ?>
                <p class="text-center text-muted py-4">No providers found. Use the Quick Add buttons above or add one manually.</p>
                <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0">
                        <thead class="thead-light">
                            <tr>
                                <th>Provider</th>
                                <th>Model</th>
                                <th>Capabilities</th>
                                <th>Tiers</th>
                                <th class="text-center">Priority</th>
                                <th class="text-center">API Key</th>
                                <th class="text-center">Usage</th>
                                <th class="text-center">Status</th>
                                <th class="text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($providers as $p): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($p['name']) ?></strong><br>
                                <small class="text-muted"><code><?= htmlspecialchars($p['slug']) ?></code></small>
                            </td>
                            <td>
                                <small><?= htmlspecialchars($p['model'] ?? '—') ?></small>
                            </td>
                            <td>
                                <?php $caps = json_decode($p['capabilities'] ?? '[]', true) ?: []; ?>
                                <?php foreach ($caps as $c): ?>
                                <span class="badge badge-light border"><?= htmlspecialchars($c) ?></span>
                                <?php endforeach; ?>
                            </td>
                            <td>
                                <?php $tiers = json_decode($p['allowed_tiers'] ?? '[]', true) ?: []; ?>
                                <small><?= implode(', ', array_map('htmlspecialchars', $tiers)) ?></small>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-secondary"><?= (int)$p['priority'] ?></span>
                            </td>
                            <td class="text-center">
                                <?php if (!empty($p['api_key'])): ?>
                                <span class="badge badge-success" title="Key configured"><i class="fas fa-check"></i> Set</span>
                                <?php else: ?>
                                <span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> None</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center" style="font-size:.8rem;">
                                <?php
                                $tokens = (int)($p['total_tokens_used'] ?? 0);
                                $cost   = (float)($p['total_cost_usd'] ?? 0);
                                ?>
                                <span title="<?= number_format($tokens) ?> tokens">
                                    <?= $tokens > 1000 ? number_format($tokens / 1000, 1) . 'K' : $tokens ?>
                                    <small class="text-muted">tok</small>
                                </span><br>
                                <small class="text-muted">$<?= number_format($cost, 4) ?></small>
                            </td>
                            <td class="text-center">
                                <?php if ($p['is_active']): ?>
                                <span class="badge badge-success">Active</span>
                                <?php else: ?>
                                <span class="badge badge-secondary">Disabled</span>
                                <?php endif; ?>
                                <?php if (!$p['is_healthy']): ?>
                                <span class="badge badge-danger ml-1" title="Health check failed"><i class="fas fa-heartbeat"></i></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-right text-nowrap">
                                <!-- Toggle enable/disable -->
                                <form method="POST" action="/admin/projects/convertx/settings" style="display:inline;">
                                    <input type="hidden" name="_token" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken()) ?>">
                                    <input type="hidden" name="provider_id" value="<?= (int)$p['id'] ?>">
                                    <input type="hidden" name="is_active" value="<?= $p['is_active'] ? '0' : '1' ?>">
                                    <button type="submit" class="btn btn-xs <?= $p['is_active'] ? 'btn-warning' : 'btn-success' ?>" title="<?= $p['is_active'] ? 'Disable' : 'Enable' ?>">
                                        <i class="fas <?= $p['is_active'] ? 'fa-ban' : 'fa-check' ?>"></i>
                                    </button>
                                </form>
                                <button type="button" class="btn btn-xs btn-info" onclick="toggleEditProvider(<?= (int)$p['id'] ?>)" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <!-- Test connection -->
                                <button type="button" class="btn btn-xs btn-secondary"
                                        id="test-btn-<?= (int)$p['id'] ?>"
                                        onclick="testConnection(<?= (int)$p['id'] ?>, <?= json_encode(\Core\Security::generateCsrfToken(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)"
                                        title="Test connection">
                                    <i class="fas fa-plug"></i>
                                </button>
                                <form method="POST" action="/admin/projects/convertx/settings/delete-provider" style="display:inline;"
                                      onsubmit="return confirm('Delete provider ' + <?= json_encode($p['name'], JSON_HEX_APOS | JSON_HEX_QUOT) ?> + '? This cannot be undone.')">
                                    <input type="hidden" name="_token" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken()) ?>">
                                    <input type="hidden" name="provider_id" value="<?= (int)$p['id'] ?>">
                                    <button type="submit" class="btn btn-xs btn-danger" title="Delete"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                        <!-- Test result row (shown after clicking test button) -->
                        <tr id="test-result-row-<?= (int)$p['id'] ?>" style="display:none;background:#fff3cd;">
                            <td colspan="9" style="padding:6px 24px;">
                                <span id="test-result-<?= (int)$p['id'] ?>" class="small"></span>
                            </td>
                        </tr>
                        <!-- Inline edit row -->
                        <tr id="edit-provider-<?= (int)$p['id'] ?>" style="display:none;background:#f8f9fa;">
                            <td colspan="9" style="padding:16px 24px;">
                                <form method="POST" action="/admin/projects/convertx/settings/edit-provider">
                                    <input type="hidden" name="_token" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken()) ?>">
                                    <input type="hidden" name="provider_id" value="<?= (int)$p['id'] ?>">
                                    <div class="row">
                                        <div class="col-md-3 form-group">
                                            <label>Name</label>
                                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($p['name']) ?>" required>
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Base URL</label>
                                            <input type="url" name="base_url" class="form-control" value="<?= htmlspecialchars($p['base_url'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>API Key <small class="text-muted">(leave blank to keep current)</small></label>
                                            <div class="input-group">
                                                <input type="password" name="api_key" id="edit-key-<?= (int)$p['id'] ?>" class="form-control"
                                                       placeholder="<?= !empty($p['api_key']) ? '••••••• (set — type new to replace)' : 'Enter API key' ?>">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                                            onclick="togglePw('edit-key-<?= (int)$p['id'] ?>')"><i class="fas fa-eye"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Model</label>
                                            <input type="text" name="model" class="form-control" value="<?= htmlspecialchars($p['model'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-4 form-group">
                                            <label>Capabilities (comma-separated)</label>
                                            <input type="text" name="capabilities" class="form-control"
                                                   value="<?= htmlspecialchars(implode(',', json_decode($p['capabilities'] ?? '[]', true) ?: [])) ?>">
                                        </div>
                                        <div class="col-md-3 form-group">
                                            <label>Allowed Tiers (comma-separated)</label>
                                            <input type="text" name="allowed_tiers" class="form-control"
                                                   value="<?= htmlspecialchars(implode(',', json_decode($p['allowed_tiers'] ?? '[]', true) ?: [])) ?>">
                                        </div>
                                        <div class="col-md-1 form-group">
                                            <label>Priority</label>
                                            <input type="number" name="priority" class="form-control" value="<?= (int)$p['priority'] ?>" min="1" max="100">
                                        </div>
                                        <div class="col-md-2 form-group">
                                            <label>Cost / 1K tokens ($)</label>
                                            <input type="number" name="cost_per_1k_tokens" class="form-control"
                                                   value="<?= number_format((float)$p['cost_per_1k_tokens'], 6, '.', '') ?>"
                                                   step="0.000001" min="0">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-save"></i> Save Changes</button>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleEditProvider(<?= (int)$p['id'] ?>)">Cancel</button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm ml-2"
                                            id="test-btn-edit-<?= (int)$p['id'] ?>"
                                            onclick="testConnection(<?= (int)$p['id'] ?>, <?= json_encode(\Core\Security::generateCsrfToken(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>)">
                                        <i class="fas fa-plug"></i> Test Connection
                                    </button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php endif; ?>
            </div>
            <div class="card-footer text-muted small">
                <i class="fas fa-info-circle"></i>
                Priority: lower number = higher priority. Providers are tried in order; the first successful response is used.
                Leave API Key blank when editing to keep the existing key.
            </div>
        </div>

    </div>
</section>

<script>
function toggleEditProvider(id) {
    const row = document.getElementById('edit-provider-' + id);
    if (row) {
        row.style.display = row.style.display === 'none' ? 'table-row' : 'none';
    }
}

function togglePw(id) {
    const el  = document.getElementById(id);
    const btn = el && el.nextElementSibling && el.nextElementSibling.querySelector('button i');
    if (el) {
        const isHidden = el.type === 'password';
        el.type = isHidden ? 'text' : 'password';
        if (btn) {
            btn.classList.toggle('fa-eye',      !isHidden);
            btn.classList.toggle('fa-eye-slash', isHidden);
        }
    }
}

function fillQuickAdd(data) {
    // Expand the Add Provider card and pre-fill the form
    const cardEl = document.getElementById('card-add-provider');
    if (cardEl) {
        const body = cardEl.querySelector('.card-body');
        if (body) body.style.display = 'block';
    }
    const fields = ['name', 'slug', 'base_url', 'model', 'capabilities', 'allowed_tiers'];
    fields.forEach(function(f) {
        const el = document.getElementById('qa-' + f);
        if (el && data[f] !== undefined) el.value = data[f];
    });
    const costEl = document.getElementById('qa-cost');
    if (costEl && data.cost !== undefined) costEl.value = data.cost;
    document.getElementById('qa-name').focus();
    cardEl.scrollIntoView({behavior: 'smooth', block: 'start'});
}

function testConnection(providerId, csrfToken) {
    const resultEl  = document.getElementById('test-result-' + providerId);
    const resultRow = document.getElementById('test-result-row-' + providerId);
    const btnEls    = [
        document.getElementById('test-btn-' + providerId),
        document.getElementById('test-btn-edit-' + providerId),
    ];

    function setButtons(disabled, iconHtml) {
        btnEls.forEach(function(b) { if (b) { b.disabled = disabled; b.innerHTML = iconHtml; } });
    }

    setButtons(true, '<i class="fas fa-spinner fa-spin"></i>');
    if (resultEl) resultEl.innerHTML = '<i class="fas fa-spinner fa-spin text-muted"></i> Testing…';
    if (resultRow) resultRow.style.display = 'table-row';

    fetch('/admin/projects/convertx/settings/test-provider', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: '_token=' + encodeURIComponent(csrfToken) + '&provider_id=' + providerId,
    })
    .then(function(r) {
        if (!r.ok) {
            return r.text().then(function(t) { throw new Error('HTTP ' + r.status + ': ' + t.substring(0, 200)); });
        }
        return r.json();
    })
    .then(function(data) {
        const icon    = data.success ? '✅' : '❌';
        const latency = data.latency_ms !== undefined ? ' (' + data.latency_ms + 'ms)' : '';
        const color   = data.success ? 'text-success' : 'text-danger';
        if (resultEl) {
            resultEl.className = 'small ' + color;
            resultEl.textContent = icon + ' ' + (data.message || '') + latency;
        }
        if (resultRow) {
            resultRow.style.background = data.success ? '#d4edda' : '#f8d7da';
        }
        setButtons(false, '<i class="fas fa-plug"></i>');
    })
    .catch(function(err) {
        if (resultEl) {
            resultEl.className = 'small text-danger';
            resultEl.textContent = '❌ ' + (err.message || 'Request failed');
        }
        if (resultRow) resultRow.style.background = '#f8d7da';
        setButtons(false, '<i class="fas fa-plug"></i>');
    });
}
</script>
<?php View::endSection(); ?>

