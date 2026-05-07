<?php
use Core\View;
use Core\Security;
View::extend('whatsapp:app');
View::section('content');
?>
<div style="padding:4px 0 20px;">
    <h2 style="font-size:1.5rem;font-weight:700;margin-bottom:4px;color:var(--whatsapp-green);">
        <i class="fab fa-whatsapp" style="margin-right:8px;"></i>WhatsApp Dashboard
    </h2>
    <p style="color:var(--text-secondary);font-size:.9rem;">Welcome back, <?= View::e($user['name'] ?? 'User') ?>!</p>
</div>

<!-- Stats Row -->
<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:28px;">
    <?php
    $statCards = [
        ['label'=>'Total Sessions','value'=>$stats['totalSessions']??0,'icon'=>'fa-mobile-alt','color'=>'var(--whatsapp-green)'],
        ['label'=>'Active Sessions','value'=>$stats['activeSessions']??0,'icon'=>'fa-circle','color'=>'#00ff88'],
        ['label'=>'Messages Today','value'=>$stats['messagesToday']??0,'icon'=>'fa-comment-dots','color'=>'var(--whatsapp-light)'],
        ['label'=>'API Calls Today','value'=>$stats['apiCallsToday']??0,'icon'=>'fa-chart-bar','color'=>'#9945ff'],
    ];
    foreach ($statCards as $c): ?>
    <div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;display:flex;flex-direction:column;gap:8px;">
        <div style="font-size:1.5rem;color:<?= $c['color'] ?>;"><i class="fas <?= $c['icon'] ?>"></i></div>
        <div style="font-size:1.6rem;font-weight:800;color:<?= $c['color'] ?>"><?= number_format((int)$c['value']) ?></div>
        <div style="font-size:.8rem;color:var(--text-secondary);"><?= $c['label'] ?></div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Quick Actions -->
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;margin-bottom:24px;">
    <h3 style="font-size:1rem;font-weight:700;margin-bottom:14px;color:var(--text-primary);">Quick Actions</h3>
    <div style="display:flex;flex-wrap:wrap;gap:10px;">
        <a href="/projects/whatsapp/sessions" style="display:inline-flex;align-items:center;gap:8px;padding:10px 18px;background:var(--whatsapp-green);color:#fff;border-radius:8px;text-decoration:none;font-size:.9rem;font-weight:600;">
            <i class="fas fa-plus"></i> New Session
        </a>
        <a href="/projects/whatsapp/messages" style="display:inline-flex;align-items:center;gap:8px;padding:10px 18px;background:var(--bg-secondary);border:1px solid var(--border-color);color:var(--text-primary);border-radius:8px;text-decoration:none;font-size:.9rem;">
            <i class="fas fa-paper-plane"></i> Send Message
        </a>
        <a href="/projects/whatsapp/api" style="display:inline-flex;align-items:center;gap:8px;padding:10px 18px;background:var(--bg-secondary);border:1px solid var(--border-color);color:var(--text-primary);border-radius:8px;text-decoration:none;font-size:.9rem;">
            <i class="fas fa-key"></i> API Keys
        </a>
    </div>
</div>

<!-- Recent Sessions -->
<?php if (!empty($recentSessions)): ?>
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;margin-bottom:24px;">
    <h3 style="font-size:1rem;font-weight:700;margin-bottom:14px;color:var(--text-primary);">Recent Sessions</h3>
    <div style="display:flex;flex-direction:column;gap:8px;">
        <?php foreach ($recentSessions as $s): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:var(--bg-secondary);border-radius:8px;border:1px solid var(--border-color);">
            <span style="font-weight:600;color:var(--text-primary);"><?= View::e($s['session_name'] ?? 'Session '.$s['id']) ?></span>
            <span style="font-size:.75rem;padding:3px 10px;border-radius:20px;background:<?= ($s['status']??'')=='active'?'rgba(0,255,136,.15)':'rgba(255,100,100,.12)' ?>;color:<?= ($s['status']??'')=='active'?'var(--whatsapp-green)':'#ff6464' ?>;">
                <?= View::e(ucfirst($s['status'] ?? 'unknown')) ?>
            </span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Recent Messages -->
<?php if (!empty($recentMessages)): ?>
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;">
    <h3 style="font-size:1rem;font-weight:700;margin-bottom:14px;color:var(--text-primary);">Recent Messages</h3>
    <div style="display:flex;flex-direction:column;gap:8px;">
        <?php foreach (array_slice($recentMessages, 0, 5) as $m): ?>
        <div style="display:flex;align-items:center;justify-content:space-between;padding:10px 14px;background:var(--bg-secondary);border-radius:8px;border:1px solid var(--border-color);">
            <div>
                <div style="font-size:.85rem;font-weight:600;color:var(--text-primary);">To: <?= View::e($m['recipient'] ?? '—') ?></div>
                <div style="font-size:.78rem;color:var(--text-secondary);margin-top:2px;"><?= View::e(mb_substr($m['message'] ?? '', 0, 60)) ?></div>
            </div>
            <span style="font-size:.72rem;color:var(--text-secondary);"><?= date('M j, H:i', strtotime($m['created_at'] ?? 'now')) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
<?php View::end(); ?>
