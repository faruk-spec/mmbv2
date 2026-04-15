<?php $csrfToken = \Core\Security::generateCsrfToken(); ob_start(); ?>
<div style="display:flex;justify-content:space-between;align-items:flex-start;gap:1rem;flex-wrap:wrap;">
    <div>
        <h1 style="margin:0 0 .25rem;font-size:1.3rem;">Ticket #<?= (int) $ticket['id'] ?> — <?= htmlspecialchars($ticket['subject']) ?></h1>
        <p style="margin:0;color:var(--text-secondary);font-size:.86rem;">Requester: <?= htmlspecialchars($ticket['requester_name'] ?? 'Customer') ?> · Created <?= htmlspecialchars($ticket['created_at']) ?></p>
    </div>
    <div style="display:flex;gap:.5rem;align-items:center;">
        <span class="badge badge-<?= htmlspecialchars($ticket['status']) ?>"><?= htmlspecialchars(strtoupper(str_replace('_', ' ', $ticket['status']))) ?></span>
        <span class="badge" style="background:rgba(59,130,246,.14);color:#93c5fd;">Priority <?= htmlspecialchars(strtoupper($ticket['priority'])) ?></span>
    </div>
</div>

<div class="card" style="margin-top:1rem;">
    <h3 style="margin:.2rem 0 .8rem;font-size:.95rem;">Conversation</h3>
    <?php if (!empty($messages)): ?>
        <div style="display:grid;gap:.7rem;">
        <?php foreach ($messages as $message): ?>
            <?php
                $senderType = $message['sender_type'] ?? 'customer';
                $bg = $senderType === 'agent' ? 'rgba(59,130,246,.13)' : ($senderType === 'ai' ? 'rgba(6,182,212,.12)' : 'rgba(148,163,184,.12)');
                $name = $message['sender_name'] ?? ucfirst($senderType);
            ?>
            <div style="padding:.75rem .8rem;border:1px solid var(--border);border-radius:.65rem;background:<?= $bg ?>;">
                <div style="display:flex;justify-content:space-between;gap:.6rem;margin-bottom:.4rem;">
                    <strong style="font-size:.82rem;"><?= htmlspecialchars($name) ?> (<?= htmlspecialchars(strtoupper($senderType)) ?>)</strong>
                    <span style="font-size:.75rem;color:var(--text-secondary);"><?= htmlspecialchars($message['created_at']) ?></span>
                </div>
                <div style="font-size:.89rem;line-height:1.5;"><?= nl2br(htmlspecialchars($message['message'])) ?></div>
                <?php if (!empty($message['is_internal'])): ?><div style="margin-top:.35rem;font-size:.72rem;color:#fbbf24;">Internal note</div><?php endif; ?>
            </div>
        <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p style="color:var(--text-secondary);font-size:.88rem;">No replies yet.</p>
    <?php endif; ?>
</div>

<div class="grid g2">
    <form method="post" action="/projects/helpdeskpro/tickets/reply/<?= (int) $ticket['id'] ?>" class="card">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <h3 style="margin:.2rem 0 .7rem;font-size:.95rem;">Add Reply</h3>
        <label for="ticket_reply_message" style="display:block;margin-bottom:.35rem;font-size:.82rem;color:var(--text-secondary);">Reply Message</label>
        <textarea id="ticket_reply_message" name="message" maxlength="5000" required placeholder="Write your response..."></textarea>
        <?php if (!empty($isAgent)): ?>
            <label style="display:flex;align-items:center;gap:.45rem;margin-top:.55rem;font-size:.8rem;color:var(--text-secondary);">
                <input type="checkbox" name="is_internal" value="1" style="width:auto;"> Add as internal note
            </label>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary" style="margin-top:.7rem;"><i class="fas fa-reply"></i> Send Reply</button>
    </form>

    <?php if (!empty($isAgent)): ?>
    <form method="post" action="/projects/helpdeskpro/tickets/status/<?= (int) $ticket['id'] ?>" class="card">
        <input type="hidden" name="_token" value="<?= htmlspecialchars($csrfToken) ?>">
        <h3 style="margin:.2rem 0 .7rem;font-size:.95rem;">Update Status</h3>
        <select name="status">
            <?php foreach (($statuses ?? []) as $status): ?>
                <option value="<?= htmlspecialchars($status) ?>" <?= (($ticket['status'] ?? '') === $status) ? 'selected' : '' ?>><?= htmlspecialchars(strtoupper(str_replace('_', ' ', $status))) ?></option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-secondary" style="margin-top:.7rem;"><i class="fas fa-rotate"></i> Update</button>
    </form>
    <?php endif; ?>
</div>
<?php $content = ob_get_clean(); require PROJECT_PATH . '/views/layout.php'; ?>
