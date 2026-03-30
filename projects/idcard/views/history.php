<?php
/**
 * @var array  $cards
 * @var int    $total
 * @var int    $page
 * @var int    $pages
 * @var array  $templates
 */
$csrfToken = \Core\Security::generateCsrfToken();
?>

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;flex-wrap:wrap;gap:12px;">
    <div>
        <h2 class="section-title" style="margin-bottom:4px;"><i class="fas fa-layer-group" style="color:var(--indigo);"></i> My ID Cards</h2>
        <p style="color:var(--text-secondary);font-size:0.85rem;"><?= number_format($total) ?> card<?= $total !== 1 ? 's' : '' ?> generated</p>
    </div>
    <a href="/projects/idcard/generate" class="btn btn-primary"><i class="fas fa-plus"></i> New Card</a>
</div>

<?php if (!empty($_GET['deleted'])): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> Card deleted successfully.</div>
<?php endif; ?>

<?php if (empty($cards)): ?>
<div class="empty-state">
    <div class="empty-icon"><i class="fas fa-id-card"></i></div>
    <h2 style="font-size:1.4rem;margin-bottom:8px;">No ID Cards Yet</h2>
    <p style="color:var(--text-secondary);margin-bottom:24px;">Create your first professional ID card using our AI-powered generator.</p>
    <a href="/projects/idcard/generate" class="btn btn-primary"><i class="fas fa-plus"></i> Create First Card</a>
</div>
<?php else: ?>

<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;">
    <?php foreach ($cards as $card):
        $tplKey = $card['template_key'];
        $tplDef = $templates[$tplKey] ?? ['name' => $tplKey, 'color' => '#6366f1', 'accent' => '#818cf8', 'bg' => '#0f172a', 'text' => '#f1f5f9'];
        $cd     = $card['card_data'] ?? [];
        $name   = $cd['name'] ?? '—';
        $role   = $cd['designation'] ?? $cd['title'] ?? $cd['course'] ?? $cd['event_name'] ?? '';
        $dept   = $cd['department'] ?? $cd['organization'] ?? $cd['course'] ?? '';
    ?>
    <div class="card" style="padding:0;overflow:hidden;">
        <!-- Mini card preview header -->
        <div style="height:64px;background:<?= htmlspecialchars($tplDef['color']) ?>;position:relative;display:flex;align-items:center;padding:0 16px;gap:12px;">
            <div style="width:36px;height:36px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                <i class="fas fa-user" style="color:white;font-size:1rem;"></i>
            </div>
            <div style="min-width:0;">
                <div style="color:white;font-weight:700;font-size:0.9rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($name) ?></div>
                <?php if ($role): ?>
                <div style="color:rgba(255,255,255,0.8);font-size:0.72rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($role) ?></div>
                <?php endif; ?>
            </div>
            <div style="position:absolute;top:8px;right:10px;background:rgba(255,255,255,0.2);border-radius:6px;padding:3px 7px;font-size:0.65rem;color:white;font-weight:600;"><?= htmlspecialchars($tplDef['name']) ?></div>
        </div>

        <!-- Card body -->
        <div style="padding:14px 16px;">
            <?php if ($dept): ?>
            <p style="font-size:0.78rem;color:var(--text-secondary);margin-bottom:8px;display:flex;align-items:center;gap:5px;">
                <i class="fas fa-building" style="font-size:0.65rem;"></i> <?= htmlspecialchars($dept) ?>
            </p>
            <?php endif; ?>
            <p style="font-size:0.72rem;color:var(--text-secondary);font-family:monospace;margin-bottom:12px;">
                <?= htmlspecialchars($card['card_number']) ?>
            </p>
            <p style="font-size:0.72rem;color:var(--text-secondary);margin-bottom:14px;">
                <i class="fas fa-calendar-alt"></i> <?= date('d M Y, H:i', strtotime($card['created_at'])) ?>
            </p>
            <div style="display:flex;gap:8px;flex-wrap:wrap;">
                <a href="/projects/idcard/view/<?= (int)$card['id'] ?>" class="btn btn-primary btn-sm" style="flex:1;justify-content:center;">
                    <i class="fas fa-eye"></i> View
                </a>
                <a href="/projects/idcard/download/<?= (int)$card['id'] ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-download"></i>
                </a>
                <button type="button" class="btn btn-danger btn-sm" onclick="deleteCard(<?= (int)$card['id'] ?>)">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Pagination -->
<?php if ($pages > 1): ?>
<div style="display:flex;justify-content:center;gap:8px;margin-top:24px;flex-wrap:wrap;">
    <?php for ($i = 1; $i <= $pages; $i++): ?>
    <a href="?page=<?= $i ?>"
       style="width:36px;height:36px;display:flex;align-items:center;justify-content:center;border-radius:8px;text-decoration:none;font-size:0.85rem;font-weight:600;
              <?= $i === $page ? 'background:var(--indigo);color:#fff;' : 'background:var(--bg-secondary);color:var(--text-secondary);border:1px solid var(--border-color);' ?>">
        <?= $i ?>
    </a>
    <?php endfor; ?>
</div>
<?php endif; ?>

<?php endif; ?>

<!-- Delete confirmation modal (hidden) -->
<div id="deleteModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.6);z-index:1000;align-items:center;justify-content:center;">
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:14px;padding:28px;max-width:360px;width:90%;text-align:center;">
        <i class="fas fa-exclamation-triangle" style="font-size:2rem;color:#ff4757;margin-bottom:12px;"></i>
        <h3 style="margin-bottom:8px;">Delete ID Card?</h3>
        <p style="color:var(--text-secondary);font-size:0.85rem;margin-bottom:20px;">This action cannot be undone. The card and its uploaded files will be permanently removed.</p>
        <div style="display:flex;gap:12px;justify-content:center;">
            <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
            <form method="POST" action="/projects/idcard/delete" id="deleteForm" style="margin:0;">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <input type="hidden" name="id" id="deleteCardId" value="">
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
            </form>
        </div>
    </div>
</div>

<script>
function deleteCard(id) {
    document.getElementById('deleteCardId').value = id;
    document.getElementById('deleteModal').style.display = 'flex';
}
function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}
document.getElementById('deleteModal').addEventListener('click', function(e){
    if(e.target === this) closeDeleteModal();
});
</script>
