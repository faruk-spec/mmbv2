<?php
use Core\View;
use Core\Security;
View::extend('whatsapp:app');
View::section('content');
?>
<div style="padding:4px 0 20px;">
    <h2 style="font-size:1.5rem;font-weight:700;margin-bottom:4px;color:var(--whatsapp-green);">
        <i class="fas fa-mobile-alt" style="margin-right:8px;"></i>Sessions
    </h2>
    <p style="color:var(--text-secondary);font-size:.9rem;">Manage your WhatsApp device sessions.</p>
</div>

<?php if (!empty($_GET['error'])): ?>
<div style="background:rgba(255,100,100,.1);border:1px solid rgba(255,100,100,.4);border-radius:8px;padding:12px 16px;margin-bottom:16px;color:#ff6464;font-size:.9rem;">
    <i class="fas fa-exclamation-circle" style="margin-right:6px;"></i><?= View::e($_GET['error']) ?>
</div>
<?php endif; ?>

<!-- Create Session Card -->
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;margin-bottom:24px;">
    <h3 style="font-size:.95rem;font-weight:700;margin-bottom:14px;color:var(--text-primary);">Create New Session</h3>
    <form method="POST" action="/projects/whatsapp/sessions/create" style="display:flex;gap:10px;flex-wrap:wrap;">
        <?= Security::csrfField() ?>
        <input type="text" name="session_name" placeholder="Session name (e.g. Business Phone)" required
               style="flex:1;min-width:200px;padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.9rem;">
        <button type="submit" style="padding:10px 20px;background:var(--whatsapp-green);color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:.9rem;">
            <i class="fas fa-plus" style="margin-right:6px;"></i>Create
        </button>
    </form>
</div>

<!-- Sessions List -->
<?php if (empty($sessions)): ?>
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:40px;text-align:center;color:var(--text-secondary);">
    <i class="fas fa-mobile-alt" style="font-size:3rem;margin-bottom:16px;opacity:.4;display:block;color:var(--whatsapp-green);"></i>
    <p>No sessions yet. Create your first WhatsApp session above.</p>
</div>
<?php else: ?>
<div style="display:flex;flex-direction:column;gap:12px;">
    <?php foreach ($sessions as $s): ?>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:16px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
        <div>
            <div style="font-weight:700;color:var(--text-primary);"><?= View::e($s['session_name'] ?? 'Session '.$s['id']) ?></div>
            <div style="font-size:.8rem;color:var(--text-secondary);margin-top:2px;">
                Created <?= date('M j, Y H:i', strtotime($s['created_at'] ?? 'now')) ?>
                <?php if (!empty($s['phone_number'])): ?> &middot; <?= View::e($s['phone_number']) ?><?php endif; ?>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            <span style="font-size:.75rem;padding:4px 12px;border-radius:20px;font-weight:600;
                background:<?= ($s['status']??'')=='active'?'rgba(0,255,136,.15)' : (($s['status']??'')=='connecting'?'rgba(255,170,0,.15)':'rgba(255,100,100,.12)') ?>;
                color:<?= ($s['status']??'')=='active'?'var(--whatsapp-green)' : (($s['status']??'')=='connecting'?'#ffaa00':'#ff6464') ?>;">
                <i class="fas fa-circle" style="font-size:.5rem;vertical-align:middle;margin-right:4px;"></i>
                <?= View::e(ucfirst($s['status'] ?? 'unknown')) ?>
            </span>
            <form method="POST" action="/projects/whatsapp/sessions/disconnect" style="display:inline;">
                <?= Security::csrfField() ?>
                <input type="hidden" name="session_id" value="<?= (int)$s['id'] ?>">
                <button type="submit" style="padding:6px 12px;background:transparent;border:1px solid rgba(255,170,0,.4);color:#ffaa00;border-radius:6px;cursor:pointer;font-size:.78rem;" title="Disconnect">
                    <i class="fas fa-unlink"></i>
                </button>
            </form>
            <form method="POST" action="/projects/whatsapp/sessions/delete" style="display:inline;" onsubmit="return confirm('Delete this session?')">
                <?= Security::csrfField() ?>
                <input type="hidden" name="session_id" value="<?= (int)$s['id'] ?>">
                <button type="submit" style="padding:6px 12px;background:transparent;border:1px solid rgba(255,100,100,.4);color:#ff6464;border-radius:6px;cursor:pointer;font-size:.78rem;" title="Delete">
                    <i class="fas fa-trash"></i>
                </button>
            </form>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php View::end(); ?>
