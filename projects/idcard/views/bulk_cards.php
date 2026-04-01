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
.bc-grid {
    display:grid;
    grid-template-columns:repeat(auto-fill,minmax(270px,1fr));
    gap:14px;
    margin-top:18px;
}
.bc-item {
    background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;
    padding:16px;display:flex;flex-direction:column;gap:10px;
}
.bc-item-meta { font-size:12px;color:var(--text-secondary); }
.bc-item-actions { display:flex;gap:8px;flex-wrap:wrap;margin-top:4px; }
</style>

<!-- Header -->
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px;">
    <div>
        <h1 style="font-size:1.2rem;font-weight:800;display:flex;align-items:center;gap:8px;">
            <i class="fas fa-layer-group" style="color:var(--indigo);"></i> My Bulk-Generated Cards
        </h1>
        <p style="font-size:0.8rem;color:var(--text-secondary);">
            All <?= number_format($total) ?> card<?= $total !== 1 ? 's' : '' ?> generated via bulk upload
        </p>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a href="/projects/idcard/bulk" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Bulk Generator</a>
        <a href="/projects/idcard/history" class="btn btn-secondary btn-sm"><i class="fas fa-list"></i> All My Cards</a>
    </div>
</div>

<?php if (empty($cards)): ?>
<div style="text-align:center;padding:56px;background:var(--bg-card);border:1px solid var(--border-color);border-radius:14px;">
    <i class="fas fa-id-card" style="font-size:2.5rem;opacity:0.25;display:block;margin-bottom:12px;"></i>
    <p style="color:var(--text-secondary);">No cards yet. <a href="/projects/idcard/bulk" style="color:var(--indigo);">Create your first bulk job →</a></p>
</div>
<?php else: ?>

<div class="bc-grid">
    <?php foreach ($cards as $card):
        $tplColor = $templates[$card['template_key']]['color'] ?? '#6366f1';
        $cardName = htmlspecialchars($card['card_data']['name'] ?? '—');
    ?>
    <div class="bc-item">
        <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:40px;height:40px;border-radius:10px;background:<?= htmlspecialchars($tplColor) ?>;
                         display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-id-card" style="color:#fff;font-size:1rem;"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-weight:700;font-size:0.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <?= $cardName ?>
                </div>
                <div class="bc-item-meta">
                    <span style="background:rgba(99,102,241,0.12);color:var(--indigo);padding:1px 8px;border-radius:8px;font-size:11px;font-weight:600;">
                        <?= htmlspecialchars($card['template_key']) ?>
                    </span>
                    &nbsp;<?= htmlspecialchars($card['card_number']) ?>
                </div>
            </div>
        </div>
        <div class="bc-item-meta">
            <?php
            $extra = [];
            foreach (['designation','department','company_name','school_name','event_name'] as $f) {
                if (!empty($card['card_data'][$f])) { $extra[] = htmlspecialchars($card['card_data'][$f]); }
            }
            if ($extra) echo implode(' &middot; ', array_slice($extra, 0, 2));
            ?>
        </div>
        <div class="bc-item-meta">
            <i class="fas fa-clock" style="font-size:10px;"></i>
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
<div style="display:flex;justify-content:center;gap:6px;margin-top:22px;flex-wrap:wrap;">
    <?php for ($p = 1; $p <= $pages; $p++): ?>
    <a href="/projects/idcard/bulk/cards?page=<?= $p ?>"
       style="display:inline-flex;align-items:center;justify-content:center;
              width:34px;height:34px;border-radius:8px;font-size:13px;font-weight:600;text-decoration:none;
              <?= $p === $page
                  ? 'background:var(--indigo);color:#fff;'
                  : 'background:var(--bg-card);border:1px solid var(--border-color);color:var(--text-primary);' ?>">
        <?= $p ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php endif; ?>
