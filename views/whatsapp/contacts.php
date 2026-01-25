<?php use Core\View; use Core\Helpers; use Core\Auth; use Core\Security; $currentUser = Auth::user(); ?>
<?php View::extend('whatsapp:app'); ?>

<?php View::section('content'); ?>

<style>
.contacts-container {
    max-width: 1200px;
    margin: 0 auto;
}

.contacts-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
    margin-top: 24px;
}

.contact-card {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
    text-align: center;
    transition: all 0.3s ease;
}

.contact-card:hover {
    border-color: #25D366;
    transform: translateY(-2px);
}

.contact-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #25D366 0%, #20BA58 100%);
    margin: 0 auto 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    font-weight: 600;
    color: white;
}

.contact-name {
    font-size: 1.1rem;
    font-weight: 600;
    margin-bottom: 8px;
}

.contact-phone {
    font-size: 0.875rem;
    color: var(--text-secondary);
    margin-bottom: 16px;
}

.btn-contact-action {
    padding: 8px 16px;
    border: none;
    border-radius: 6px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-message {
    background: #25D366;
    color: white;
}

.btn-message:hover {
    background: #20BA58;
}
</style>

<div class="contacts-container">
    <div style="margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1 style="font-size: 2rem; margin-bottom: 8px; display: flex; align-items: center; gap: 12px;">
                <i class="fas fa-address-book" style="color: #25D366; font-size: 2rem;"></i>
                Contacts
            </h1>
            <p style="color: var(--text-secondary); font-size: 0.95rem;">Manage your WhatsApp contacts</p>
        </div>
        <?php if (!empty($sessions)): ?>
            <button onclick="syncContacts()" style="background: #25D366; color: white; padding: 12px 24px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                <i class="fas fa-sync" style="margin-right: 6px;"></i>
                Sync Contacts
            </button>
        <?php endif; ?>
    </div>

    <?php if (empty($sessions)): ?>
        <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 60px 24px; text-align: center;">
            <i class="fas fa-users" style="font-size: 64px; color: var(--text-secondary); opacity: 0.5; margin-bottom: 20px;"></i>
            <h3 style="color: var(--text-secondary); margin-bottom: 12px;">No Active Sessions</h3>
            <p style="color: var(--text-secondary); margin-bottom: 20px;">Create a WhatsApp session to sync your contacts</p>
            <a href="/projects/whatsapp/sessions" style="background: #25D366; color: white; padding: 12px 32px; border: none; border-radius: 8px; font-weight: 600; text-decoration: none; display: inline-block;">
                Create Session
            </a>
        </div>
    <?php elseif (empty($contacts)): ?>
        <div style="background: var(--bg-secondary); border: 1px solid var(--border-color); border-radius: 12px; padding: 60px 24px; text-align: center;">
            <i class="fas fa-address-book" style="font-size: 64px; color: var(--text-secondary); opacity: 0.5; margin-bottom: 20px;"></i>
            <h3 style="color: var(--text-secondary); margin-bottom: 12px;">No Contacts Yet</h3>
            <p style="color: var(--text-secondary); margin-bottom: 20px;">Sync your contacts from WhatsApp to see them here</p>
            <button onclick="syncContacts()" style="background: #25D366; color: white; padding: 12px 32px; border: none; border-radius: 8px; font-weight: 600; cursor: pointer;">
                Sync Contacts
            </button>
        </div>
    <?php else: ?>
        <div class="contacts-grid">
            <?php foreach ($contacts as $contact): ?>
                <div class="contact-card">
                    <div class="contact-avatar">
                        <?= strtoupper(substr($contact['name'], 0, 1)) ?>
                    </div>
                    <div class="contact-name"><?= View::e($contact['name']) ?></div>
                    <div class="contact-phone"><?= View::e($contact['phone_number']) ?></div>
                    <div style="display: flex; gap: 8px; justify-content: center;">
                        <button class="btn-contact-action btn-message" onclick="messageContact('<?= View::e($contact['phone_number']) ?>')">
                            <i class="fas fa-comment-dots" style="margin-right: 4px;"></i>
                            Message
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Session Select Modal -->
<div id="sessionModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.8); z-index: 9999; justify-content: center; align-items: center;">
    <div style="background: var(--bg-secondary); border-radius: 16px; padding: 32px; max-width: 400px; width: 90%;">
        <h3 style="margin-bottom: 20px;">Select Session</h3>
        <select id="sessionSelect" style="width: 100%; padding: 12px; border: 1px solid var(--border-color); border-radius: 8px; background: var(--bg-primary); color: var(--text-primary); margin-bottom: 20px;">
            <?php foreach ($sessions as $session): ?>
                <option value="<?= $session['id'] ?>"><?= View::e($session['session_name']) ?></option>
            <?php endforeach; ?>
        </select>
        <div style="display: flex; gap: 12px;">
            <button onclick="closeSyncModal()" style="flex: 1; padding: 12px; background: var(--bg-primary); color: var(--text-primary); border: 1px solid var(--border-color); border-radius: 8px; cursor: pointer;">Cancel</button>
            <button onclick="doSync()" style="flex: 1; padding: 12px; background: #25D366; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600;">Sync</button>
        </div>
    </div>
</div>

<script>
function syncContacts() {
    document.getElementById('sessionModal').style.display = 'flex';
}

function closeSyncModal() {
    document.getElementById('sessionModal').style.display = 'none';
}

function doSync() {
    const sessionId = document.getElementById('sessionSelect').value;
    
    fetch('/projects/whatsapp/contacts/sync', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `session_id=${sessionId}&csrf_token=<?= Security::generateCsrfToken() ?>`
    })
    .then(response => response.json())
    .then(data => {
        closeSyncModal();
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    });
}

function messageContact(phone) {
    window.location.href = '/projects/whatsapp/messages?recipient=' + encodeURIComponent(phone);
}
</script>

<?php View::endSection(); ?>
