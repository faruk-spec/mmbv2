<?php $csrfToken = \Core\Security::generateCsrfToken(); ob_start(); ?>
<h1 style="margin:0 0 .25rem;font-size:1.3rem;">Agent Live Support Console</h1>
<p style="margin:0 0 1rem;color:var(--text-secondary);font-size:.88rem;">Respond to live conversations and handle AI handoffs in real time.</p>

<div class="grid g2">
    <div class="card">
        <h2 style="margin:.15rem 0 .8rem;font-size:1rem;">Active Sessions</h2>
        <?php if (!empty($sessions)): ?>
            <div style="display:grid;gap:.5rem;">
            <?php foreach ($sessions as $session): ?>
                <a href="/projects/helpdeskpro/agent/live-support?sid=<?= (int) $session['id'] ?>" style="display:block;padding:.65rem .7rem;border:1px solid var(--border);border-radius:.55rem;text-decoration:none;color:var(--text-primary);background:<?= (!empty($activeSession) && (int) $activeSession['id'] === (int) $session['id']) ? 'rgba(59,130,246,.13)' : 'transparent' ?>;">
                    <div style="display:flex;justify-content:space-between;gap:.4rem;">
                        <strong style="font-size:.84rem;">#<?= (int) $session['id'] ?> · <?= htmlspecialchars($session['user_name'] ?? $session['customer_name'] ?? 'Customer') ?></strong>
                        <span style="font-size:.72rem;color:var(--text-secondary);"><?= htmlspecialchars($session['status']) ?></span>
                    </div>
                    <div style="font-size:.75rem;color:var(--text-secondary);margin-top:.25rem;">Msgs: <?= (int) ($session['message_count'] ?? 0) ?><?php if (!empty($session['assigned_agent_name'])): ?> · Agent: <?= htmlspecialchars($session['assigned_agent_name']) ?><?php endif; ?></div>
                </a>
            <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p style="color:var(--text-secondary);font-size:.88rem;">No active sessions currently.</p>
        <?php endif; ?>
    </div>

    <div class="card">
        <?php if (empty($activeSession)): ?>
            <p style="color:var(--text-secondary);font-size:.88rem;">Select a session to view full transcript and reply.</p>
        <?php else: ?>
            <div style="display:flex;justify-content:space-between;gap:.5rem;align-items:center;margin-bottom:.75rem;">
                <strong>Session #<?= (int) $activeSession['id'] ?></strong>
                <span style="font-size:.78rem;color:var(--text-secondary);"><?= htmlspecialchars($activeSession['user_name'] ?? $activeSession['customer_name'] ?? 'Customer') ?> · <?= htmlspecialchars($activeSession['user_email'] ?? $activeSession['customer_email'] ?? '') ?></span>
            </div>

            <div style="max-height:18rem;overflow:auto;border:1px solid var(--border);border-radius:.55rem;padding:.6rem;margin-bottom:.75rem;display:grid;gap:.5rem;">
                <?php foreach (($messages ?? []) as $message): ?>
                    <?php $st = $message['sender_type'] ?? 'customer'; $bg = $st === 'agent' ? 'rgba(16,185,129,.14)' : ($st === 'ai' ? 'rgba(6,182,212,.12)' : 'rgba(59,130,246,.12)'); ?>
                    <div style="padding:.6rem .65rem;border:1px solid var(--border);border-radius:.5rem;background:<?= $bg ?>;">
                        <div style="display:flex;justify-content:space-between;gap:.4rem;font-size:.73rem;color:var(--text-secondary);margin-bottom:.3rem;">
                            <strong style="color:var(--text-primary);"><?= htmlspecialchars(strtoupper($st)) ?></strong>
                            <span><?= htmlspecialchars($message['created_at']) ?></span>
                        </div>
                        <div style="font-size:.84rem;line-height:1.5;"><?= nl2br(htmlspecialchars($message['message'])) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <form method="post" action="/projects/helpdeskpro/agent/live-support/reply/<?= (int) $activeSession['id'] ?>">
                <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
                <label for="agent_reply_message" style="display:block;margin-bottom:.35rem;font-size:.82rem;color:var(--text-secondary);">Agent Reply</label>
                <textarea id="agent_reply_message" name="message" required maxlength="3000" placeholder="Send a human support reply..."></textarea>
                <button type="submit" class="btn btn-primary" style="margin-top:.65rem;"><i class="fas fa-paper-plane"></i> Send Reply</button>
            </form>
        <?php endif; ?>
    </div>
</div>
<?php $content = ob_get_clean(); require PROJECT_PATH . '/views/layout.php'; ?>
