<?php
/**
 * Admin Live Chat Detail View
 */
use Core\View;

View::extend('admin');
View::section('content');

$isActive = ($chat['status'] === 'active');
?>

<div style="padding:28px;max-width:800px;">
    <a href="/admin/support/live-chats" style="color:var(--text-secondary,#8892a6);text-decoration:none;font-size:.85rem;display:inline-flex;align-items:center;gap:6px;margin-bottom:18px;">
        <i class="fas fa-arrow-left"></i> Back to Live Chats
    </a>

    <!-- Chat header -->
    <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:14px;padding:20px;margin-bottom:18px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;">
        <div>
            <h1 style="font-size:1.2rem;font-weight:700;color:var(--text-primary,#e8eefc);margin:0 0 6px;">
                <i class="fas fa-comments" style="color:#ff2ec4;margin-right:8px;"></i>Chat #<?= (int)$chat['id'] ?>
            </h1>
            <div style="font-size:.85rem;color:var(--text-secondary,#8892a6);">
                <?php
                $userName = $chat['user_name'] ?? ($chat['guest_name'] ? $chat['guest_name'].' (guest)' : 'Guest');
                echo htmlspecialchars($userName);
                if (!empty($chat['guest_email'])) echo ' &lt;'.htmlspecialchars($chat['guest_email']).'&gt;';
                ?>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:10px;">
            <span style="padding:4px 12px;border-radius:20px;font-size:.78rem;font-weight:600;background:<?= $isActive ? '#00ff881a' : '#8892a61a' ?>;color:<?= $isActive ? '#00ff88' : '#8892a6' ?>">
                <?= ucfirst($chat['status']) ?>
            </span>
            <?php if ($isActive): ?>
            <form method="POST" action="/admin/support/live-chats/<?= (int)$chat['id'] ?>/close" style="display:inline;">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
                <button type="submit" style="padding:6px 14px;background:rgba(255,107,107,.15);border:1px solid rgba(255,107,107,.3);border-radius:7px;color:#ff6b6b;font-size:.8rem;font-weight:600;cursor:pointer;">
                    <i class="fas fa-times" style="margin-right:4px;"></i>Close Chat
                </button>
            </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Messages -->
    <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:12px;padding:16px;margin-bottom:18px;max-height:480px;overflow-y:auto;display:flex;flex-direction:column;gap:10px;">
        <?php foreach ($messages as $msg):
            $isAgent = ($msg['sender_type'] === 'agent');
            $isAI    = ($msg['sender_type'] === 'ai');
            $sColor  = $isAgent ? '#ff9f43' : ($isAI ? '#a78bfa' : '#00f0ff');
            $sLabel  = $isAgent ? ($msg['sender_name'] ?? 'Agent') : ($isAI ? 'AI Assistant' : ($msg['sender_name'] ?? 'User'));
        ?>
        <div style="background:rgba(255,255,255,.03);border:1px solid var(--border-color,rgba(255,255,255,.06));border-radius:10px;padding:12px 14px;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:6px;">
                <span style="font-weight:600;font-size:.8rem;color:<?= $sColor ?>;"><?= htmlspecialchars($sLabel) ?></span>
                <span style="font-size:.7rem;color:var(--text-secondary,#8892a6);"><?= date('M j H:i', strtotime($msg['created_at'])) ?></span>
            </div>
            <div style="color:var(--text-primary,#e8eefc);font-size:.88rem;line-height:1.55;white-space:pre-wrap;"><?= htmlspecialchars($msg['message']) ?></div>
        </div>
        <?php endforeach; ?>
        <?php if (empty($messages)): ?>
        <div style="text-align:center;padding:40px;color:var(--text-secondary,#8892a6);font-size:.88rem;">No messages yet.</div>
        <?php endif; ?>
    </div>

    <!-- Agent reply form -->
    <?php if ($isActive): ?>
    <div style="background:var(--bg-card,#0f0f18);border:1px solid var(--border-color,rgba(255,255,255,.08));border-radius:12px;padding:18px;">
        <h3 style="margin:0 0 12px;font-size:.9rem;font-weight:600;color:var(--text-primary,#e8eefc);">
            <i class="fas fa-reply" style="color:#ff9f43;margin-right:7px;"></i>Send Agent Message
        </h3>
        <form method="POST" action="/admin/support/live-chats/<?= (int)$chat['id'] ?>/reply">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrf_token ?? '') ?>">
            <div style="display:flex;gap:10px;align-items:flex-end;">
                <textarea name="message" required rows="2" placeholder="Type a message..."
                    style="flex:1;padding:10px 12px;border:1px solid var(--border-color,rgba(255,255,255,.1));border-radius:8px;background:var(--bg-secondary,#0c0c12);color:var(--text-primary,#e8eefc);font-size:.88rem;outline:none;resize:vertical;"></textarea>
                <button type="submit" style="padding:10px 18px;background:linear-gradient(135deg,#ff9f43,#ff2ec4);border:none;border-radius:8px;color:white;font-weight:600;font-size:.85rem;cursor:pointer;white-space:nowrap;">
                    <i class="fas fa-paper-plane" style="margin-right:5px;"></i>Send
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>

<?php View::endSection(); ?>
