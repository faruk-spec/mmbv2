<?php
/**
 * ConvertX – API Keys & Analytics View
 */
$currentView = 'apikeys';
$csrfToken   = \Core\Security::generateCsrfToken();
$baseUrl     = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'yourdomain.com');

// Usage & analytics data (passed from controller)
$usage    = $usage    ?? [];
$formats  = $formats  ?? [];
$activity = $activity ?? [];

// Active tab from query param (default: apikey)
$allowedTabs = ['apikey', 'usage', 'analytics'];
$activeTab   = in_array($_GET['tab'] ?? '', $allowedTabs, true) ? $_GET['tab'] : 'apikey';

// Bar widths for format breakdown
$maxFormatCnt = max(1, ...array_column($formats ?: [['cnt' => 1]], 'cnt'));

// Activity sparkline data
$activityMap = [];
foreach ($activity as $row) {
    $activityMap[$row['day']] = (int) $row['cnt'];
}
$sparkMax = max(1, ...($activityMap ? array_values($activityMap) : [1]));

// Check for newly generated key stored in session
$newlyGeneratedKey = null;
if (!empty($_SESSION['_new_api_key'])) {
    $newlyGeneratedKey = $_SESSION['_new_api_key'];
    unset($_SESSION['_new_api_key']);
}

// Flash messages
$flashSuccess = null;
if (!empty($_SESSION['_flash']['success'])) {
    $flashSuccess = $_SESSION['_flash']['success'];
    unset($_SESSION['_flash']['success']);
}
$flashError = null;
if (!empty($_SESSION['_flash']['error'])) {
    $flashError = $_SESSION['_flash']['error'];
    unset($_SESSION['_flash']['error']);
}
?>

<!-- Page header -->
<div class="page-header">
    <h1>API Keys &amp; Analytics</h1>
    <p>Manage your API credentials and track usage</p>
</div>

<?php if ($flashSuccess): ?>
<div style="background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.4);border-radius:.625rem;padding:.875rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.625rem;font-size:.875rem;color:var(--cx-success);">
    <i class="fa-solid fa-circle-check"></i>
    <span><?= htmlspecialchars($flashSuccess) ?></span>
</div>
<?php endif; ?>

<?php if ($flashError): ?>
<div style="background:rgba(239,68,68,.12);border:1px solid rgba(239,68,68,.4);border-radius:.625rem;padding:.875rem 1rem;margin-bottom:1.25rem;display:flex;align-items:center;gap:.625rem;font-size:.875rem;color:var(--cx-danger,#ef4444);">
    <i class="fa-solid fa-circle-xmark"></i>
    <span><?= htmlspecialchars($flashError) ?></span>
</div>
<?php endif; ?>

<?php if ($newlyGeneratedKey): ?>
<div style="background:linear-gradient(135deg,rgba(99,102,241,.15),rgba(6,182,212,.10));border:1px solid rgba(99,102,241,.45);border-radius:.75rem;padding:1rem 1.25rem;margin-bottom:1.25rem;animation:cx-neon-pulse 2s ease infinite;">
    <div style="display:flex;align-items:center;gap:.625rem;margin-bottom:.625rem;">
        <i class="fa-solid fa-key" style="color:var(--cx-primary);font-size:1.1rem;"></i>
        <strong style="color:var(--text-primary);font-size:.9rem;">Your New API Key — Copy it now!</strong>
        <span style="font-size:.72rem;color:var(--cx-warning);background:rgba(245,158,11,.12);border:1px solid rgba(245,158,11,.3);padding:.15rem .5rem;border-radius:.375rem;margin-left:auto;">
            <i class="fa-solid fa-triangle-exclamation"></i> Shown only once
        </span>
    </div>
    <div style="display:flex;gap:.5rem;align-items:center;">
        <input type="text" id="newKeyDisplay" value="<?= htmlspecialchars($newlyGeneratedKey) ?>"
               readonly style="flex:1;font-family:monospace;font-size:.82rem;letter-spacing:.04em;background:var(--cx-code-bg);border:1px solid rgba(99,102,241,.35);border-radius:.5rem;padding:.5rem .75rem;color:var(--cx-primary);">
        <button type="button" onclick="copyNewKey(event)" class="btn btn-primary btn-sm" style="flex-shrink:0;">
            <i class="fa-solid fa-copy"></i> Copy
        </button>
    </div>
</div>
<?php endif; ?>

<!-- ── Tab nav ── -->
<div class="cx-tab-nav" role="tablist">
    <a href="?tab=apikey"    class="cx-tab <?= $activeTab === 'apikey'    ? 'active' : '' ?>" role="tab">
        <i class="fa-solid fa-key"></i> API Key
    </a>
    <a href="?tab=usage"     class="cx-tab <?= $activeTab === 'usage'     ? 'active' : '' ?>" role="tab">
        <i class="fa-solid fa-chart-bar"></i> Usage
    </a>
    <a href="?tab=analytics" class="cx-tab <?= $activeTab === 'analytics' ? 'active' : '' ?>" role="tab">
        <i class="fa-solid fa-chart-line"></i> Analytics
    </a>
</div>

<!-- ══════════════════════════════════════════
     TAB 1 – API Key management
═══════════════════════════════════════════ -->
<?php if ($activeTab === 'apikey'): ?>

    <!-- Key card -->
    <div class="card" style="border-color:rgba(99,102,241,.3);">
        <div class="card-header">
            <i class="fa-solid fa-key"></i> Your API Key
            <?php if ($apiKey): ?>
                <span class="ai-badge" style="margin-left:auto;">
                    <i class="fa-solid fa-circle-check"></i> Active
                </span>
            <?php endif; ?>
        </div>

        <div class="form-group">
            <label class="form-label">
                <i class="fa-solid fa-shield-halved" style="color:var(--cx-primary);"></i>
                Send as <code style="background:rgba(99,102,241,.12);padding:.1rem .4rem;border-radius:.25rem;font-size:.85em;">X-Api-Key</code> header in every request
            </label>
            <div style="display:flex;gap:.5rem;">
                <input type="<?= $apiKey ? 'password' : 'text' ?>" class="form-control" id="apiKeyDisplay"
                       value="<?= $apiKey ? htmlspecialchars($apiKey) : '' ?>"
                       placeholder="<?= $apiKey ? '' : '(no key yet — generate one below)' ?>"
                       readonly
                       style="font-family:monospace;font-size:.85rem;<?= $apiKey ? 'letter-spacing:.04em;' : '' ?>">
                <?php if ($apiKey): ?>
                <button type="button" onclick="toggleKey()" class="btn btn-secondary" id="toggleBtn" title="Show / hide">
                    <i class="fa-solid fa-eye" id="toggleIcon"></i>
                </button>
                <button type="button" onclick="copyKey(event)" class="btn btn-secondary" title="Copy to clipboard">
                    <i class="fa-solid fa-copy"></i>
                </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="form-actions">
            <form method="POST" action="/projects/convertx/apikeys" style="display:inline;">
                <input type="hidden" name="_token"  value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="action"  value="generate_api_key">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-rotate"></i> <?= $apiKey ? 'Regenerate Key' : 'Generate API Key' ?>
                </button>
            </form>
            <?php if ($apiKey): ?>
            <form method="POST" action="/projects/convertx/apikeys" style="display:inline;"
                  onsubmit="return confirm('Revoke this key? All integrations will stop working immediately.')">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="action" value="revoke_api_key">
                <button type="submit" class="btn btn-danger">
                    <i class="fa-solid fa-ban"></i> Revoke
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick-start example -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-terminal"></i> Quick Start
        </div>
        <pre style="background:var(--cx-code-bg);border:1px solid var(--border-color);border-radius:.5rem;padding:1rem;font-size:.78rem;overflow-x:auto;line-height:1.7;"><span style="color:var(--cx-primary);">curl</span> -X POST <?= htmlspecialchars($baseUrl) ?>/projects/convertx/api/convert \
  -H <span style="color:var(--cx-accent);">"X-Api-Key: <?= $apiKey ? htmlspecialchars($apiKey) : 'cx_your_api_key' ?>"</span> \
  -F <span style="color:var(--cx-success);">"file=@document.pdf"</span> \
  -F <span style="color:var(--cx-success);">"output_format=docx"</span></pre>
        <div style="margin-top:1rem;">
            <a href="/projects/convertx/docs" class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-book-open"></i> Full API Documentation
            </a>
        </div>
    </div>

    <!-- Security tips -->
    <div class="card" style="border-color:rgba(245,158,11,.3);background:rgba(245,158,11,.04);">
        <div class="card-header" style="border-color:rgba(245,158,11,.2);">
            <i class="fa-solid fa-triangle-exclamation" style="color:var(--cx-warning);"></i>
            <span style="color:var(--cx-warning);">Security Tips</span>
        </div>
        <ul style="list-style:none;display:flex;flex-direction:column;gap:.625rem;font-size:.875rem;color:var(--text-secondary);">
            <li><i class="fa-solid fa-circle-check" style="color:var(--cx-success);margin-right:.4rem;"></i> Never expose your key in client-side JavaScript or public repositories</li>
            <li><i class="fa-solid fa-circle-check" style="color:var(--cx-success);margin-right:.4rem;"></i> Store keys in environment variables or secret managers</li>
            <li><i class="fa-solid fa-circle-check" style="color:var(--cx-success);margin-right:.4rem;"></i> Regenerate immediately if you suspect it has been compromised</li>
        </ul>
    </div>

<?php elseif ($activeTab === 'usage'): ?>

<!-- ══════════════════════════════════════════
     TAB 2 – Usage statistics
═══════════════════════════════════════════ -->

    <!-- Monthly summary stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-icon"><i class="fa-solid fa-bolt"></i></span>
            <span class="value"><?= number_format((int)($usage['total_jobs'] ?? 0)) ?></span>
            <span class="label">Jobs This Month</span>
        </div>
        <div class="stat-card">
            <span class="stat-icon"><i class="fa-solid fa-circle-check"></i></span>
            <span class="value"><?= number_format((int)($usage['completed'] ?? 0)) ?></span>
            <span class="label">Completed</span>
        </div>
        <div class="stat-card">
            <span class="stat-icon"><i class="fa-solid fa-circle-xmark"></i></span>
            <span class="value"><?= number_format((int)($usage['failed'] ?? 0)) ?></span>
            <span class="label">Failed</span>
        </div>
        <div class="stat-card">
            <span class="stat-icon"><i class="fa-solid fa-microchip"></i></span>
            <span class="value"><?= number_format((int)($usage['tokens_used'] ?? 0)) ?></span>
            <span class="label">AI Tokens Used</span>
        </div>
    </div>

    <!-- Success rate -->
    <?php
    $total     = max(1, (int)($usage['total_jobs'] ?? 0));
    $completed = (int)($usage['completed'] ?? 0);
    $pct       = $total > 0 ? round($completed / $total * 100) : 0;
    ?>
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-chart-pie"></i> Success Rate This Month
        </div>
        <div style="display:flex;align-items:center;gap:1rem;flex-wrap:wrap;">
            <div style="font-size:3rem;font-weight:800;background:linear-gradient(135deg,var(--cx-success),#059669);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;line-height:1;">
                <?= $pct ?>%
            </div>
            <div style="flex:1;min-width:160px;">
                <div style="height:10px;background:var(--bg-secondary);border-radius:10px;overflow:hidden;margin-bottom:.5rem;">
                    <div style="height:100%;width:<?= $pct ?>%;background:linear-gradient(90deg,var(--cx-success),#059669);border-radius:10px;transition:width 1s ease;"></div>
                </div>
                <p style="font-size:.8rem;color:var(--text-secondary);">
                    <?= number_format($completed) ?> completed out of <?= number_format($total) ?> total conversions
                </p>
            </div>
        </div>
    </div>

    <!-- Plan limits -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-gauge-high"></i> Plan Limits
        </div>
        <div style="display:flex;gap:1.5rem;flex-wrap:wrap;font-size:.875rem;color:var(--text-secondary);">
            <div>
                <i class="fa-solid fa-file-arrow-up" style="color:var(--cx-primary);margin-right:.3rem;"></i>
                <strong style="color:var(--text-primary);">File size limit:</strong>
                Free plan — 10 MB &nbsp;|&nbsp; Pro — 100 MB &nbsp;|&nbsp; Enterprise — 500 MB
            </div>
            <div>
                <i class="fa-solid fa-repeat" style="color:var(--cx-accent);margin-right:.3rem;"></i>
                <strong style="color:var(--text-primary);">Monthly conversions:</strong>
                Free — 50 &nbsp;|&nbsp; Pro — 1,000 &nbsp;|&nbsp; Enterprise — unlimited
            </div>
        </div>
        <div style="margin-top:1rem;">
            <a href="/projects/convertx/plan" class="btn btn-primary btn-sm">
                <i class="fa-solid fa-arrow-up"></i> Upgrade Plan
            </a>
        </div>
    </div>

<?php else: ?>

<!-- ══════════════════════════════════════════
     TAB 3 – Analytics
═══════════════════════════════════════════ -->

    <!-- Output format breakdown -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-chart-bar"></i> Top Output Formats
            <span style="font-size:.75rem;font-weight:400;color:var(--text-muted);margin-left:.5rem;">(completed jobs, all time)</span>
        </div>

        <?php if (empty($formats)): ?>
            <div style="text-align:center;padding:2.5rem;color:var(--text-secondary);">
                <i class="fa-solid fa-chart-bar" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem;"></i>
                No conversion data yet. Start converting files to see analytics.
            </div>
        <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:.75rem;">
                <?php foreach ($formats as $row):
                    $barPct = round($row['cnt'] / $maxFormatCnt * 100);
                    $fmtUpper = strtoupper(htmlspecialchars($row['output_format']));
                ?>
                <div>
                    <div style="display:flex;justify-content:space-between;font-size:.8rem;margin-bottom:.3rem;">
                        <span style="font-weight:600;color:var(--text-primary);"><?= $fmtUpper ?></span>
                        <span style="color:var(--text-secondary);"><?= number_format((int)$row['cnt']) ?> jobs</span>
                    </div>
                    <div style="height:8px;background:var(--bg-secondary);border-radius:8px;overflow:hidden;">
                        <div style="height:100%;width:<?= $barPct ?>%;background:linear-gradient(90deg,var(--cx-primary),var(--cx-accent));border-radius:8px;transition:width 1s ease;"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- 14-day activity sparkline -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-chart-line"></i> Last 14 Days Activity
        </div>

        <?php if (empty($activityMap)): ?>
            <div style="text-align:center;padding:2.5rem;color:var(--text-secondary);">
                <i class="fa-solid fa-chart-line" style="font-size:2.5rem;opacity:.3;display:block;margin-bottom:.75rem;"></i>
                No recent activity. Activity will appear here after you start converting files.
            </div>
        <?php else: ?>
            <div style="display:flex;align-items:flex-end;gap:4px;height:80px;">
                <?php
                // Build 14 days from today backwards
                for ($d = 13; $d >= 0; $d--) {
                    $day = date('Y-m-d', strtotime("-{$d} days"));
                    $cnt = $activityMap[$day] ?? 0;
                    $h   = $sparkMax > 0 ? max(4, round($cnt / $sparkMax * 72)) : 4;
                    echo '<div title="' . $day . ': ' . $cnt . ' jobs" style="'
                       . 'flex:1;height:' . $h . 'px;'
                       . 'background:linear-gradient(180deg,var(--cx-primary),var(--cx-accent));'
                       . 'border-radius:3px 3px 0 0;'
                       . 'opacity:' . ($cnt > 0 ? '1' : '0.2') . ';'
                       . 'transition:opacity .2s,height .5s;'
                       . '" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=' . ($cnt > 0 ? '1' : '0.2') . '"></div>';
                }
                ?>
            </div>
            <div style="display:flex;justify-content:space-between;font-size:.65rem;color:var(--text-muted);margin-top:.5rem;">
                <span><?= date('M j', strtotime('-13 days')) ?></span>
                <span>Today</span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Conversion insights -->
    <div class="card">
        <div class="card-header">
            <i class="fa-solid fa-lightbulb"></i> Quick Insights
        </div>
        <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;font-size:.875rem;">
            <div style="padding:.875rem;background:var(--bg-secondary);border-radius:.625rem;border:1px solid var(--border-color);">
                <i class="fa-solid fa-trophy" style="color:var(--cx-warning);font-size:1.25rem;margin-bottom:.4rem;display:block;"></i>
                <strong style="color:var(--text-primary);">Most Used Format</strong>
                <p style="color:var(--text-secondary);margin-top:.2rem;">
                    <?= !empty($formats[0]['output_format']) ? strtoupper(htmlspecialchars($formats[0]['output_format'])) : '—' ?>
                </p>
            </div>
            <div style="padding:.875rem;background:var(--bg-secondary);border-radius:.625rem;border:1px solid var(--border-color);">
                <i class="fa-solid fa-fire" style="color:var(--cx-danger);font-size:1.25rem;margin-bottom:.4rem;display:block;"></i>
                <strong style="color:var(--text-primary);">This Month</strong>
                <p style="color:var(--text-secondary);margin-top:.2rem;">
                    <?= number_format((int)($usage['total_jobs'] ?? 0)) ?> conversions
                </p>
            </div>
            <div style="padding:.875rem;background:var(--bg-secondary);border-radius:.625rem;border:1px solid var(--border-color);">
                <i class="fa-solid fa-wand-magic-sparkles" style="color:var(--cx-primary);font-size:1.25rem;margin-bottom:.4rem;display:block;"></i>
                <strong style="color:var(--text-primary);">AI Tokens</strong>
                <p style="color:var(--text-secondary);margin-top:.2rem;">
                    <?= number_format((int)($usage['tokens_used'] ?? 0)) ?> used this month
                </p>
            </div>
        </div>
    </div>

<?php endif; ?>

<style>
/* ── Tab navigation ── */
.cx-tab-nav {
    display: flex;
    gap: .375rem;
    margin-bottom: 1.5rem;
    border-bottom: 1px solid var(--border-color);
    padding-bottom: 0;
    flex-wrap: wrap;
}
.cx-tab {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .625rem 1rem;
    font-size: var(--font-sm);
    font-weight: 500;
    color: var(--text-secondary);
    text-decoration: none;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
    transition: color .2s, border-color .2s;
    white-space: nowrap;
}
.cx-tab:hover { color: var(--text-primary); }
.cx-tab.active {
    color: var(--cx-primary);
    border-bottom-color: var(--cx-primary);
    font-weight: 600;
}
</style>

<script>
function toggleKey() {
    var input = document.getElementById('apiKeyDisplay');
    var icon  = document.getElementById('toggleIcon');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'fa-solid fa-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'fa-solid fa-eye';
    }
}

function copyKey(event) {
    var input = document.getElementById('apiKeyDisplay');
    var val   = input.value;
    if (!val) return;
    navigator.clipboard.writeText(val).then(function () {
        var btn = event.currentTarget;
        btn.innerHTML = '<i class="fa-solid fa-check"></i>';
        setTimeout(function () { btn.innerHTML = '<i class="fa-solid fa-copy"></i>'; }, 1500);
    });
}

function copyNewKey(event) {
    var input = document.getElementById('newKeyDisplay');
    if (!input) return;
    navigator.clipboard.writeText(input.value).then(function () {
        var btn = event.currentTarget;
        btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
        setTimeout(function () { btn.innerHTML = '<i class="fa-solid fa-copy"></i> Copy'; }, 2000);
    });
}
</script>
