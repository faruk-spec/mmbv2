<?php $csrfToken = \Core\Security::generateCsrfToken(); ob_start(); ?>
<h1 style="margin:0 0 .25rem;font-size:1.3rem;">Live Support Chat</h1>
<p style="margin:0 0 1rem;color:var(--text-secondary);font-size:.88rem;">Chat with our support assistant instantly, with human handoff when needed.</p>

<?php if (empty($session)): ?>
<form method="post" action="/projects/helpdeskpro/live-support/start" class="card" style="max-width:34rem;">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <div style="margin-bottom:.8rem;">
        <label style="display:block;margin-bottom:.35rem;font-size:.82rem;color:var(--text-secondary);">Your Name</label>
        <input type="text" name="name" required maxlength="120" value="<?= htmlspecialchars($prefillName ?? '') ?>">
    </div>
    <div style="margin-bottom:1rem;">
        <label style="display:block;margin-bottom:.35rem;font-size:.82rem;color:var(--text-secondary);">Email</label>
        <input type="email" name="email" required maxlength="255" value="<?= htmlspecialchars($prefillEmail ?? '') ?>">
    </div>
    <button type="submit" class="btn btn-primary"><i class="fas fa-headset"></i> Start Live Support</button>
</form>
<?php else: ?>
<div class="card" style="margin-bottom:.85rem;display:flex;justify-content:space-between;gap:1rem;align-items:center;flex-wrap:wrap;">
    <div style="font-size:.85rem;color:var(--text-secondary);">Session #<?= (int) $session['id'] ?> · Status: <strong style="color:var(--text-primary);"><?= htmlspecialchars(strtoupper(str_replace('_', ' ', $session['status'] ?? 'open'))) ?></strong>
    <?php if (!empty($session['assigned_agent_name'])): ?> · Agent: <strong style="color:#93c5fd;"><?= htmlspecialchars($session['assigned_agent_name']) ?></strong><?php endif; ?></div>
    <a href="/projects/helpdeskpro/live-support" class="btn btn-secondary">Refresh</a>
</div>

<div class="card" style="max-height:26rem;overflow:auto;">
    <div style="display:grid;gap:.6rem;">
        <?php foreach (($messages ?? []) as $message): ?>
            <?php $st = $message['sender_type'] ?? 'customer'; $bg = $st === 'customer' ? 'rgba(59,130,246,.12)' : ($st === 'agent' ? 'rgba(16,185,129,.12)' : 'rgba(6,182,212,.12)'); ?>
            <div style="padding:.7rem .75rem;border:1px solid var(--border);border-radius:.6rem;background:<?= $bg ?>;">
                <div style="display:flex;justify-content:space-between;gap:.5rem;font-size:.75rem;color:var(--text-secondary);margin-bottom:.35rem;">
                    <strong style="color:var(--text-primary);"><?= htmlspecialchars(strtoupper($st)) ?></strong>
                    <span><?= htmlspecialchars($message['created_at']) ?></span>
                </div>
                <div style="font-size:.88rem;line-height:1.5;"><?= nl2br(htmlspecialchars($message['message'])) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<form method="post" action="/projects/helpdeskpro/live-support/send" class="card">
    <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <textarea name="message" required maxlength="3000" placeholder="Type your message... (ask for 'human agent' anytime)"></textarea>
    <button type="submit" class="btn btn-primary" style="margin-top:.7rem;"><i class="fas fa-paper-plane"></i> Send</button>
</form>
<?php endif; ?>
<?php $content = ob_get_clean(); require PROJECT_PATH . '/views/layout.php'; ?>
