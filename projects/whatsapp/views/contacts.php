<?php
use Core\View;
use Core\Security;
View::extend('whatsapp:app');
View::section('content');
?>
<div style="padding:4px 0 20px;">
    <h2 style="font-size:1.5rem;font-weight:700;margin-bottom:4px;color:var(--whatsapp-green);">
        <i class="fas fa-address-book" style="margin-right:8px;"></i>Contacts
    </h2>
    <p style="color:var(--text-secondary);font-size:.9rem;">Manage your WhatsApp contacts.</p>
</div>

<?php if (empty($contacts)): ?>
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:40px;text-align:center;color:var(--text-secondary);">
    <i class="fas fa-address-book" style="font-size:3rem;margin-bottom:16px;opacity:.4;display:block;color:var(--whatsapp-green);"></i>
    <p style="margin-bottom:14px;">No contacts synced yet.</p>
    <?php if (!empty($sessions)): ?>
    <form method="POST" action="/projects/whatsapp/contacts/sync" style="display:inline;">
        <?= Security::csrfField() ?>
        <select name="session_id" style="padding:8px 12px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:6px;color:var(--text-primary);margin-right:8px;">
            <?php foreach ($sessions as $s): ?>
            <option value="<?= (int)$s['id'] ?>"><?= View::e($s['session_name'] ?? 'Session '.$s['id']) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" style="padding:8px 18px;background:var(--whatsapp-green);color:#fff;border:none;border-radius:6px;cursor:pointer;font-weight:600;">
            <i class="fas fa-sync" style="margin-right:6px;"></i>Sync Contacts
        </button>
    </form>
    <?php endif; ?>
</div>
<?php else: ?>
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;margin-bottom:20px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px;">
        <h3 style="font-size:.95rem;font-weight:700;color:var(--text-primary);"><?= count($contacts) ?> Contacts</h3>
        <?php if (!empty($sessions)): ?>
        <form method="POST" action="/projects/whatsapp/contacts/sync">
            <?= Security::csrfField() ?>
            <select name="session_id" style="padding:6px 10px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:6px;color:var(--text-primary);font-size:.8rem;margin-right:6px;">
                <?php foreach ($sessions as $s): ?>
                <option value="<?= (int)$s['id'] ?>"><?= View::e($s['session_name'] ?? 'Session '.$s['id']) ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" style="padding:6px 14px;background:var(--whatsapp-green);color:#fff;border:none;border-radius:6px;cursor:pointer;font-size:.8rem;font-weight:600;">
                <i class="fas fa-sync" style="margin-right:4px;"></i>Sync
            </button>
        </form>
        <?php endif; ?>
    </div>
    <div style="display:flex;flex-direction:column;gap:8px;">
        <?php foreach ($contacts as $c): ?>
        <div style="display:flex;align-items:center;gap:12px;padding:10px 14px;background:var(--bg-secondary);border-radius:8px;border:1px solid var(--border-color);">
            <div style="width:36px;height:36px;border-radius:50%;background:var(--whatsapp-green)22;display:flex;align-items:center;justify-content:center;color:var(--whatsapp-green);font-size:.9rem;flex-shrink:0;">
                <i class="fas fa-user"></i>
            </div>
            <div>
                <div style="font-weight:600;color:var(--text-primary);font-size:.9rem;"><?= View::e($c['name'] ?? $c['phone_number'] ?? 'Unknown') ?></div>
                <?php if (!empty($c['phone_number'])): ?>
                <div style="font-size:.78rem;color:var(--text-secondary);"><?= View::e($c['phone_number']) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
<?php View::end(); ?>
