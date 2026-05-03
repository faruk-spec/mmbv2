<?php
use Core\Auth;
use Core\Database;
use Core\View;

$ecosystemEvents = [];
try {
    $userId = Auth::id();
    if ($userId) {
        $db = Database::getInstance();
        $ecosystemEvents = $db->fetchAll(
            "SELECT action, module, readable_message, created_at
             FROM activity_logs
             WHERE user_id = ? AND (
                 action = 'ecosystem_handoff'
                 OR action IN (
                     'linkshortner_link_created',
                     'proshare_file_uploaded',
                     'form_created',
                     'qr_created'
                 )
             )
             ORDER BY created_at DESC
             LIMIT 6",
            [$userId]
        );
    }
} catch (\Throwable $e) {
    $ecosystemEvents = [];
}
?>

<?php if (!empty($ecosystemEvents)): ?>
    <div style="margin-top: 1rem; background: var(--bg-card); border: 1px solid var(--border-color); border-radius: 10px; padding: 0.9rem;">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:0.75rem;margin-bottom:0.6rem;">
            <h4 style="margin:0;font-size:0.9rem;">
                <i class="fas fa-network-wired" style="color:#00f0ff;"></i> Recent Ecosystem Activity
            </h4>
        </div>
        <div style="display:grid;gap:0.45rem;">
            <?php foreach ($ecosystemEvents as $event): ?>
                <div style="display:flex;justify-content:space-between;gap:0.75rem;padding:0.45rem 0.55rem;background:rgba(255,255,255,0.02);border:1px solid var(--border-color);border-radius:8px;">
                    <div style="font-size:0.8rem; color: var(--text-primary);">
                        <?= View::e($event['readable_message'] ?: (($event['module'] ?: 'platform') . ' • ' . $event['action'])) ?>
                    </div>
                    <div style="font-size:0.72rem;color:var(--text-secondary);white-space:nowrap;">
                        <?= date('M j, H:i', strtotime($event['created_at'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
