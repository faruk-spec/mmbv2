<?php
/**
 * @var string $title
 * @var array  $user
 * @var array  $cards
 * @var int    $total
 * @var int    $page
 * @var int    $perPage
 * @var int    $pages
 * @var array  $templates
 */
?>
<style>
/* ── Bulk cards page ─────────────────────────────────────────────── */
.bc-header {
    display:flex;justify-content:space-between;align-items:flex-start;
    margin-bottom:20px;gap:12px;flex-wrap:wrap;
}
.bc-header-actions { display:flex;gap:8px;flex-wrap:wrap;flex-shrink:0; }

.bc-stats-bar {
    display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px;
}
.bc-stat-pill {
    display:inline-flex;align-items:center;gap:6px;
    background:var(--bg-card);border:1px solid var(--border-color);
    border-radius:20px;padding:5px 14px;font-size:0.78rem;font-weight:600;
    color:var(--text-secondary);
}
.bc-stat-pill strong { color:var(--indigo); }

.bc-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(260px,1fr));
    gap:14px;
}
@media(max-width:480px){ .bc-grid { grid-template-columns:1fr; gap:10px; } }

.bc-item {
    background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;
    padding:15px;display:flex;flex-direction:column;gap:10px;
    transition:border-color 0.18s,box-shadow 0.18s;
}
.bc-item:hover {
    border-color:rgba(99,102,241,0.35);
    box-shadow:0 4px 20px rgba(0,0,0,0.18);
}
.bc-item-head { display:flex;align-items:center;gap:10px; }
.bc-item-icon {
    width:38px;height:38px;min-width:38px;border-radius:10px;
    display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.bc-item-name {
    font-weight:700;font-size:0.88rem;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
}
.bc-item-meta { font-size:11.5px;color:var(--text-secondary);line-height:1.5; }
.bc-item-tag {
    display:inline-flex;align-items:center;
    background:rgba(99,102,241,0.12);color:var(--indigo);
    padding:1px 8px;border-radius:8px;font-size:11px;font-weight:600;
}
.bc-item-time {
    display:flex;align-items:center;gap:4px;font-size:11px;color:var(--text-secondary);
}
.bc-item-actions { display:flex;gap:7px;flex-wrap:wrap;margin-top:2px; }

/* ── Pagination ──────────────────────────────────────────────────── */
.bc-pagination {
    display:flex;justify-content:center;align-items:center;
    gap:5px;margin-top:24px;flex-wrap:wrap;
}
.bc-page-btn {
    display:inline-flex;align-items:center;justify-content:center;
    min-width:34px;height:34px;padding:0 8px;
    border-radius:8px;font-size:13px;font-weight:600;
    text-decoration:none;transition:all 0.15s;border:1px solid var(--border-color);
    color:var(--text-primary);background:var(--bg-card);
}
.bc-page-btn:hover { border-color:var(--indigo);color:var(--indigo); }
.bc-page-btn.active { background:var(--indigo);color:#fff;border-color:var(--indigo); }
.bc-page-btn.disabled { opacity:0.35;pointer-events:none; }
</style>

<!-- Header -->
<div class="bc-header">
    <div>
        <h1 style="font-size:1.15rem;font-weight:800;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
            <i class="fas fa-layer-group" style="color:var(--indigo);"></i>
            My Bulk-Generated Cards
        </h1>
        <p style="font-size:0.8rem;color:var(--text-secondary);margin-top:3px;">
            Cards generated via bulk CSV upload
        </p>
    </div>
    <div class="bc-header-actions">
        <a href="/projects/idcard/bulk" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Bulk Generator</a>
        <a href="/projects/idcard/history" class="btn btn-secondary btn-sm"><i class="fas fa-list"></i> All My Cards</a>
    </div>
</div>

<!-- Stats pill -->
<?php if ($total > 0): ?>
<div class="bc-stats-bar">
    <div class="bc-stat-pill">
        <i class="fas fa-id-card" style="color:var(--indigo);font-size:0.78rem;"></i>
        <strong><?= number_format($total) ?></strong> card<?= $total !== 1 ? 's' : '' ?>
    </div>
    <div class="bc-stat-pill">
        <i class="fas fa-file-alt" style="color:var(--indigo);font-size:0.78rem;"></i>
        Page <strong><?= $page ?></strong> of <strong><?= $pages ?></strong>
    </div>
</div>
<?php endif; ?>

<?php if (empty($cards)): ?>
<div style="text-align:center;padding:56px 20px;background:var(--bg-card);border:1px solid var(--border-color);border-radius:14px;">
    <i class="fas fa-id-card" style="font-size:2.5rem;opacity:0.22;display:block;margin-bottom:12px;color:var(--indigo);"></i>
    <p style="color:var(--text-secondary);margin-bottom:14px;">No bulk-generated cards yet.</p>
    <a href="/projects/idcard/bulk" class="btn btn-primary btn-sm"><i class="fas fa-bolt"></i> Create Bulk Cards</a>
</div>
<?php else: ?>

<div class="bc-grid">
    <?php foreach ($cards as $card):
        $tplColor = $templates[$card['template_key']]['color'] ?? '#6366f1';
        $cardName = htmlspecialchars($card['card_data']['name'] ?? '—');
        $extra = [];
        foreach (['designation','department','company_name','school_name','event_name'] as $f) {
            if (!empty($card['card_data'][$f])) { $extra[] = htmlspecialchars($card['card_data'][$f]); }
        }
    ?>
    <div class="bc-item">
        <div class="bc-item-head">
            <div class="bc-item-icon" style="background:<?= htmlspecialchars($tplColor) ?>20;border:1px solid <?= htmlspecialchars($tplColor) ?>40;">
                <i class="fas fa-id-card" style="color:<?= htmlspecialchars($tplColor) ?>;font-size:1rem;"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div class="bc-item-name"><?= $cardName ?></div>
                <div class="bc-item-meta">
                    <span class="bc-item-tag"><?= htmlspecialchars($card['template_key']) ?></span>
                    &nbsp;<span style="font-family:monospace;font-size:10.5px;"><?= htmlspecialchars($card['card_number']) ?></span>
                </div>
            </div>
        </div>

        <?php if ($extra): ?>
        <div class="bc-item-meta" style="padding:6px 8px;background:var(--bg-secondary);border-radius:7px;">
            <?= implode(' &middot; ', array_slice($extra, 0, 2)) ?>
        </div>
        <?php endif; ?>

        <div class="bc-item-time">
            <i class="fas fa-clock" style="font-size:10px;opacity:0.6;"></i>
            <?= date('d M Y, H:i', strtotime($card['created_at'])) ?>
        </div>
        <div class="bc-item-actions">
            <a href="/projects/idcard/view/<?= (int)$card['id'] ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-eye"></i> View
            </a>
            <a href="/projects/idcard/download/<?= (int)$card['id'] ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-download"></i> Download
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Pagination -->
<?php if ($pages > 1): ?>
<div class="bc-pagination">
    <?php if ($page > 1): ?>
    <a href="/projects/idcard/bulk/cards?page=<?= $page - 1 ?>" class="bc-page-btn" aria-label="Previous">&#8249;</a>
    <?php else: ?>
    <span class="bc-page-btn disabled">&#8249;</span>
    <?php endif; ?>

    <?php
    // Show a window of page numbers
    $window = 2;
    for ($p = 1; $p <= $pages; $p++):
        if ($p === 1 || $p === $pages || abs($p - $page) <= $window):
    ?>
    <a href="/projects/idcard/bulk/cards?page=<?= $p ?>"
       class="bc-page-btn<?= $p === $page ? ' active' : '' ?>"><?= $p ?></a>
    <?php
        elseif (abs($p - $page) === $window + 1):
            echo '<span class="bc-page-btn disabled" style="border:none;min-width:auto;padding:0 2px;">…</span>';
        endif;
    endfor; ?>

    <?php if ($page < $pages): ?>
    <a href="/projects/idcard/bulk/cards?page=<?= $page + 1 ?>" class="bc-page-btn" aria-label="Next">&#8250;</a>
    <?php else: ?>
    <span class="bc-page-btn disabled">&#8250;</span>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php endif; ?>
