<?php include __DIR__ . '/layout.php'; ?>

<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Auto-Responder</h2>
            <p class="text-muted mb-0">Set up automatic replies for incoming emails</p>
        </div>
    </div>

    <!-- Auto-Responder Configuration -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Auto-Responder Configuration</h5>
                </div>
                <div class="card-body">
                    <form id="autoResponderForm">
                        <!-- Enable/Disable -->
                        <div class="form-group mb-4">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="isActive" name="is_active">
                                <label class="custom-control-label" for="isActive">
                                    <strong>Enable Auto-Responder</strong>
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                When enabled, automatic replies will be sent to incoming emails
                            </small>
                        </div>

                        <!-- Subject -->
                        <div class="form-group">
                            <label for="subject">Subject Line</label>
                            <input type="text" class="form-control" id="subject" name="subject" 
                                   placeholder="e.g., Out of Office" maxlength="255">
                        </div>

                        <!-- Message -->
                        <div class="form-group">
                            <label for="message">Message</label>
                            <textarea class="form-control" id="message" name="message" rows="8" 
                                      placeholder="Enter your automatic reply message..."></textarea>
                            <small class="form-text text-muted">
                                This message will be sent automatically to anyone who emails you
                            </small>
                        </div>

                        <!-- Date Range -->
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="startDate">Start Date (Optional)</label>
                                    <input type="date" class="form-control" id="startDate" name="start_date">
                                    <small class="form-text text-muted">
                                        Leave empty for no start date
                                    </small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="endDate">End Date (Optional)</label>
                                    <input type="date" class="form-control" id="endDate" name="end_date">
                                    <small class="form-text text-muted">
                                        Leave empty for no end date
                                    </small>
                                </div>
                            </div>
                        </div>

                        <!-- Send Once Per Sender -->
                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="sendOnce" 
                                       name="send_once_per_sender" checked>
                                <label class="custom-control-label" for="sendOnce">
                                    Send only once to each sender during the active period
                                </label>
                            </div>
                            <small class="form-text text-muted">
                                Prevents sending multiple auto-responses to the same person
                            </small>
                        </div>

                        <!-- Actions -->
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-2"></i>Save Auto-Responder
                            </button>
                            <button type="button" class="btn btn-outline-secondary" onclick="loadAutoResponder()">
                                <i class="fas fa-undo mr-2"></i>Reset
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Help Sidebar -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-info-circle mr-2"></i>About Auto-Responder</h6>
                </div>
                <div class="card-body">
                    <p class="small">
                        Auto-responder automatically sends a pre-written reply to anyone who emails you. 
                        This is useful for:
                    </p>
                    <ul class="small">
                        <li>Vacation or out of office messages</li>
                        <li>Holiday notifications</li>
                        <li>Temporary unavailability</li>
                        <li>Business hours information</li>
                    </ul>
                    
                    <hr>
                    
                    <h6 class="small font-weight-bold mb-2">Best Practices</h6>
                    <ul class="small mb-0">
                        <li>Keep message brief and professional</li>
                        <li>Include when you'll be available again</li>
                        <li>Provide alternative contact if urgent</li>
                        <li>Set end date to auto-disable</li>
                        <li>Test before activating</li>
                    </ul>
                </div>
            </div>

            <!-- Templates -->
            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="mb-0"><i class="fas fa-file-alt mr-2"></i>Message Templates</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <button type="button" class="list-group-item list-group-item-action small" 
                                onclick="useTemplate('vacation')">
                            <strong>Vacation</strong>
                        </button>
                        <button type="button" class="list-group-item list-group-item-action small" 
                                onclick="useTemplate('business')">
                            <strong>Business Hours</strong>
                        </button>
                        <button type="button" class="list-group-item list-group-item-action small" 
                                onclick="useTemplate('temporary')">
                            <strong>Temporarily Away</strong>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Templates
const templates = {
    vacation: {
        subject: 'Out of Office',
        message: 'Thank you for your email. I am currently out of the office and will have limited access to email.\n\nI will return on [DATE] and will respond to your message as soon as possible.\n\nIf your matter is urgent, please contact [ALTERNATIVE CONTACT].\n\nBest regards'
    },
    business: {
        subject: 'Auto-Reply: Business Hours',
        message: 'Thank you for contacting us. This is an automated response to let you know we have received your email.\n\nOur business hours are Monday-Friday, 9:00 AM to 5:00 PM.\n\nWe will respond to your inquiry within 24 business hours.\n\nBest regards'
    },
    temporary: {
        subject: 'Temporarily Unavailable',
        message: 'Thank you for your email. I am temporarily away from my desk and will respond to your message as soon as I return.\n\nIf this is urgent, please call [PHONE NUMBER].\n\nThank you for your patience.'
    }
};

// Use template
function useTemplate(type) {
    if (templates[type]) {
        document.getElementById('subject').value = templates[type].subject;
        document.getElementById('message').value = templates[type].message;
    }
}

// Load auto-responder
function loadAutoResponder() {
    fetch('/projects/mail/subscriber/autoresponder/get')
        .then(r => r.json())
        .then(data => {
            if (data.success && data.autoresponder) {
                document.getElementById('isActive').checked = data.autoresponder.is_active == 1;
                document.getElementById('subject').value = data.autoresponder.subject || '';
                document.getElementById('message').value = data.autoresponder.message || '';
                document.getElementById('startDate').value = data.autoresponder.start_date || '';
                document.getElementById('endDate').value = data.autoresponder.end_date || '';
                document.getElementById('sendOnce').checked = data.autoresponder.send_once_per_sender == 1;
            }
        });
}

// Save auto-responder
document.getElementById('autoResponderForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.set('is_active', document.getElementById('isActive').checked ? 1 : 0);
    formData.set('send_once_per_sender', document.getElementById('sendOnce').checked ? 1 : 0);
    
    fetch('/projects/mail/subscriber/autoresponder/save', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('Auto-responder saved successfully!');
        } else {
            alert('Error: ' + data.message);
        }
    });
});

// Load on page load
loadAutoResponder();
</script>
