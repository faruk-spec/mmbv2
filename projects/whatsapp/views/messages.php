<?php
use Core\View;
use Core\Security;
View::extend('whatsapp:app');
View::section('content');
?>
<div style="padding:4px 0 20px;">
    <h2 style="font-size:1.5rem;font-weight:700;margin-bottom:4px;color:var(--whatsapp-green);">
        <i class="fas fa-comment-dots" style="margin-right:8px;"></i>Messages
    </h2>
    <p style="color:var(--text-secondary);font-size:.9rem;">Send messages via your active WhatsApp sessions.</p>
</div>

<?php if (empty($sessions)): ?>
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:30px;text-align:center;color:var(--text-secondary);margin-bottom:24px;">
    <i class="fas fa-exclamation-circle" style="font-size:2rem;color:#ffaa00;margin-bottom:10px;display:block;"></i>
    <p>No active sessions found. <a href="/projects/whatsapp/sessions" style="color:var(--whatsapp-green);">Create a session</a> to send messages.</p>
</div>
<?php else: ?>
<!-- Send Message Form -->
<div style="background:var(--bg-card);border:1px solid var(--border-color);border-radius:12px;padding:20px;margin-bottom:24px;">
    <h3 style="font-size:.95rem;font-weight:700;margin-bottom:14px;color:var(--text-primary);">Send Message</h3>
    <div id="sendMsgResult" style="display:none;margin-bottom:12px;"></div>
    <form id="sendMsgForm" style="display:flex;flex-direction:column;gap:12px;">
        <?= Security::csrfField() ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div>
                <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;">Session</label>
                <select name="session_id" required style="width:100%;padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.9rem;">
                    <?php foreach ($sessions as $s): ?>
                    <option value="<?= (int)$s['id'] ?>"><?= View::e($s['session_name'] ?? 'Session '.$s['id']) ?> (<?= View::e(ucfirst($s['status']??'unknown')) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;">Recipient (phone with country code)</label>
                <input type="text" name="recipient" placeholder="+911234567890" required
                       style="width:100%;padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.9rem;">
            </div>
        </div>
        <div>
            <label style="font-size:.8rem;color:var(--text-secondary);display:block;margin-bottom:4px;">Message</label>
            <textarea name="message" rows="4" required placeholder="Type your message here..." style="width:100%;padding:10px 14px;background:var(--bg-secondary);border:1px solid var(--border-color);border-radius:8px;color:var(--text-primary);font-size:.9rem;resize:vertical;"></textarea>
        </div>
        <div>
            <button type="submit" style="padding:10px 24px;background:var(--whatsapp-green);color:#fff;border:none;border-radius:8px;font-weight:600;cursor:pointer;font-size:.9rem;">
                <i class="fas fa-paper-plane" style="margin-right:6px;"></i>Send Message
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<script>
document.getElementById('sendMsgForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    const form = e.target;
    const btn = form.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Sending…';
    const res = document.getElementById('sendMsgResult');
    try {
        const resp = await fetch('/projects/whatsapp/messages/send', {
            method: 'POST',
            body: new FormData(form)
        });
        const data = await resp.json();
        res.style.display = 'block';
        if (data.success) {
            res.style.cssText = 'display:block;padding:10px 14px;background:rgba(0,255,136,.1);border:1px solid rgba(0,255,136,.4);border-radius:8px;color:var(--whatsapp-green);font-size:.9rem;margin-bottom:12px;';
            res.innerHTML = '<i class="fas fa-check-circle" style="margin-right:6px;"></i>' + (data.message || 'Message sent successfully!');
            form.reset();
        } else {
            res.style.cssText = 'display:block;padding:10px 14px;background:rgba(255,100,100,.1);border:1px solid rgba(255,100,100,.4);border-radius:8px;color:#ff6464;font-size:.9rem;margin-bottom:12px;';
            res.innerHTML = '<i class="fas fa-times-circle" style="margin-right:6px;"></i>' + (data.error || 'Failed to send message.');
        }
    } catch(err) {
        res.style.cssText = 'display:block;padding:10px 14px;background:rgba(255,100,100,.1);border:1px solid rgba(255,100,100,.4);border-radius:8px;color:#ff6464;font-size:.9rem;margin-bottom:12px;';
        res.innerHTML = '<i class="fas fa-times-circle" style="margin-right:6px;"></i>Network error.';
    }
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-paper-plane" style="margin-right:6px;"></i>Send Message';
});
</script>
<?php View::end(); ?>
