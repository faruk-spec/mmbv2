<?php use Core\View; use Core\Helpers; use Core\Auth; use Core\Security; $currentUser = Auth::user(); ?>
<?php View::extend('Projects\\WhatsApp', 'app'); ?>

<?php View::section('content'); ?>

<style>
.messages-container {
    max-width: 1400px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 350px 1fr;
    gap: 24px;
}

.sessions-sidebar {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    padding: 20px;
    height: fit-content;
}

.session-select-item {
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 1px solid transparent;
}

.session-select-item:hover {
    background: rgba(37, 211, 102, 0.1);
    border-color: #25D366;
}

.session-select-item.active {
    background: rgba(37, 211, 102, 0.2);
    border-color: #25D366;
}

.messages-main {
    background: var(--bg-secondary);
    border: 1px solid var(--border-color);
    border-radius: 12px;
    display: flex;
    flex-direction: column;
    height: calc(100vh - 200px);
}

.messages-header {
    padding: 20px 24px;
    border-bottom: 1px solid var(--border-color);
}

.send-message-form {
    padding: 24px;
    border-bottom: 1px solid var(--border-color);
}

.form-group {
    margin-bottom: 16px;
}

.form-label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    font-size: 0.875rem;
}

.form-input, .form-textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid var(--border-color);
    border-radius: 8px;
    background: var(--bg-primary);
    color: var(--text-primary);
    font-size: 0.875rem;
}

.form-textarea {
    resize: vertical;
    min-height: 100px;
}

.btn-send {
    background: #25D366;
    color: white;
    padding: 12px 32px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    width: 100%;
    transition: all 0.3s ease;
}

.btn-send:hover {
    background: #20BA58;
}

.messages-list {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
}

.message-bubble {
    max-width: 70%;
    padding: 12px 16px;
    border-radius: 12px;
    margin-bottom: 12px;
    position: relative;
}

.message-outgoing {
    margin-left: auto;
    background: #25D366;
    color: white;
}

.message-incoming {
    margin-right: auto;
    background: rgba(37, 211, 102, 0.1);
    border: 1px solid rgba(37, 211, 102, 0.3);
}

.message-time {
    font-size: 0.7rem;
    opacity: 0.7;
    margin-top: 4px;
}
</style>

<div style="margin-bottom: 30px;">
    <h1 style="font-size: 2rem; margin-bottom: 8px; display: flex; align-items: center; gap: 12px;">
        <i class="fas fa-comment-dots" style="color: #25D366; font-size: 2rem;"></i>
        Messages
    </h1>
    <p style="color: var(--text-secondary); font-size: 0.95rem;">Send and manage WhatsApp messages</p>
</div>

<div class="messages-container">
    <div class="sessions-sidebar">
        <h3 style="margin-bottom: 16px; font-size: 1rem; color: #25D366;">Active Sessions</h3>
        <?php if (empty($sessions)): ?>
            <p style="color: var(--text-secondary); text-align: center; padding: 20px; font-size: 0.875rem;">
                No active sessions.<br>
                <a href="/projects/whatsapp/sessions" style="color: #25D366;">Create a session</a>
            </p>
        <?php else: ?>
            <?php foreach ($sessions as $session): ?>
                <div class="session-select-item" data-session-id="<?= $session['id'] ?>">
                    <div style="font-weight: 600; margin-bottom: 4px;"><?= View::e($session['session_name']) ?></div>
                    <div style="font-size: 0.75rem; color: var(--text-secondary);"><?= View::e($session['phone_number'] ?? 'Not connected') ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="messages-main">
        <div class="messages-header">
            <h3 style="margin: 0; font-size: 1.1rem;" id="currentSessionName">Select a session to start messaging</h3>
        </div>

        <div class="send-message-form" id="sendMessageForm" style="display: none;">
            <div class="form-group">
                <label class="form-label">Recipient Phone Number</label>
                <input type="text" class="form-input" id="recipientPhone" placeholder="+1234567890" />
            </div>
            <div class="form-group">
                <label class="form-label">Message</label>
                <textarea class="form-textarea" id="messageText" placeholder="Type your message..."></textarea>
            </div>
            <button class="btn-send" onclick="sendMessage()">
                <i class="fas fa-paper-plane" style="margin-right: 6px;"></i>
                Send Message
            </button>
        </div>

        <div class="messages-list" id="messagesList">
            <div style="text-align: center; color: var(--text-secondary); padding: 60px 20px;">
                <i class="fas fa-comments" style="font-size: 64px; opacity: 0.3; margin-bottom: 16px;"></i>
                <p>Select a session from the sidebar to send messages</p>
            </div>
        </div>
    </div>
</div>

<script>
let currentSessionId = null;

document.querySelectorAll('.session-select-item').forEach(item => {
    item.addEventListener('click', function() {
        document.querySelectorAll('.session-select-item').forEach(i => i.classList.remove('active'));
        this.classList.add('active');
        
        currentSessionId = this.dataset.sessionId;
        document.getElementById('currentSessionName').textContent = this.querySelector('div').textContent;
        document.getElementById('sendMessageForm').style.display = 'block';
        
        loadMessages(currentSessionId);
    });
});

function loadMessages(sessionId) {
    fetch('/projects/whatsapp/messages/history?session_id=' + sessionId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayMessages(data.messages);
            }
        });
}

function displayMessages(messages) {
    const container = document.getElementById('messagesList');
    
    if (messages.length === 0) {
        container.innerHTML = '<div style="text-align: center; color: var(--text-secondary); padding: 40px;">No messages yet. Send your first message!</div>';
        return;
    }
    
    container.innerHTML = messages.map(msg => `
        <div class="message-bubble message-${msg.direction}">
            <div>${msg.message}</div>
            <div class="message-time">${new Date(msg.created_at).toLocaleString()}</div>
        </div>
    `).join('');
    
    container.scrollTop = container.scrollHeight;
}

function sendMessage() {
    if (!currentSessionId) {
        alert('Please select a session first');
        return;
    }
    
    const recipient = document.getElementById('recipientPhone').value;
    const message = document.getElementById('messageText').value;
    
    if (!recipient || !message) {
        alert('Please fill in all fields');
        return;
    }
    
    fetch('/projects/whatsapp/messages/send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `session_id=${currentSessionId}&recipient=${encodeURIComponent(recipient)}&message=${encodeURIComponent(message)}&csrf_token=<?= Security::generateCsrfToken() ?>`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Message sent successfully!');
            document.getElementById('messageText').value = '';
            loadMessages(currentSessionId);
        } else {
            alert('Error: ' + data.message);
        }
    });
}
</script>

<?php View::endSection(); ?>
