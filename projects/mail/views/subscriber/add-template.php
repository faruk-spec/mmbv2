<?php
// Add Template View
$pageTitle = 'Create Email Template';
include 'layout.php';
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-file-plus"></i> Create Email Template</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/projects/mail/subscriber/dashboard">Home</a></li>
                        <li class="breadcrumb-item"><a href="/projects/mail/templates">Templates</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-9">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Template Details</h3>
                        </div>
                        <form method="POST" action="/projects/mail/templates/store">
                            <?= csrf_field() ?>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Template Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" required 
                                           placeholder="e.g., Welcome Email, Weekly Newsletter" autofocus>
                                </div>

                                <div class="form-group">
                                    <label>Subject Line <span class="text-danger">*</span></label>
                                    <input type="text" name="subject" class="form-control" required 
                                           placeholder="Email subject">
                                    <small class="form-text text-muted">
                                        You can use variables: {{name}}, {{email}}, {{company}}
                                    </small>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="is_html" 
                                               name="is_html" checked onchange="toggleEditor()">
                                        <label class="custom-control-label" for="is_html">
                                            HTML Email (Rich Text Editor)
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Email Body <span class="text-danger">*</span></label>
                                    <textarea name="body" id="template_body" class="form-control" rows="15" required></textarea>
                                    <small class="form-text text-muted">
                                        Variables: {{name}}, {{email}}, {{company}}, {{phone}}
                                    </small>
                                </div>
                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Template
                                </button>
                                <a href="/projects/mail/templates" class="btn btn-default">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="card card-info">
                        <div class="card-header">
                            <h3 class="card-title">Quick Templates</h3>
                        </div>
                        <div class="card-body p-2">
                            <button type="button" class="btn btn-sm btn-block btn-outline-info mb-2" 
                                    onclick="loadQuickTemplate('welcome')">
                                Welcome Email
                            </button>
                            <button type="button" class="btn btn-sm btn-block btn-outline-info mb-2" 
                                    onclick="loadQuickTemplate('newsletter')">
                                Newsletter
                            </button>
                            <button type="button" class="btn btn-sm btn-block btn-outline-info mb-2" 
                                    onclick="loadQuickTemplate('meeting')">
                                Meeting Request
                            </button>
                            <button type="button" class="btn btn-sm btn-block btn-outline-info mb-2" 
                                    onclick="loadQuickTemplate('followup')">
                                Follow-up
                            </button>
                        </div>
                    </div>

                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">Variables</h3>
                        </div>
                        <div class="card-body">
                            <p class="small mb-2"><strong>Available variables:</strong></p>
                            <ul class="pl-3 small">
                                <li><code>{{name}}</code> - Contact name</li>
                                <li><code>{{email}}</code> - Email address</li>
                                <li><code>{{company}}</code> - Company name</li>
                                <li><code>{{phone}}</code> - Phone number</li>
                            </ul>
                            <p class="small text-muted mt-2">
                                These will be replaced with actual contact data when used.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- TinyMCE -->
<script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js"></script>

<script>
let editor;

function initTinyMCE() {
    tinymce.init({
        selector: '#template_body',
        height: 400,
        menubar: false,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | formatselect | bold italic | alignleft aligncenter alignright | bullist numlist | link image | code',
        content_style: 'body { font-family: Arial, sans-serif; font-size: 14px; }'
    });
}

function toggleEditor() {
    const isHtml = document.getElementById('is_html').checked;
    if (isHtml && !editor) {
        initTinyMCE();
    } else if (!isHtml && editor) {
        tinymce.remove('#template_body');
        editor = null;
    }
}

function loadQuickTemplate(type) {
    const templates = {
        welcome: {
            name: 'Welcome Email',
            subject: 'Welcome to our service, {{name}}!',
            body: '<p>Hi {{name}},</p><p>Welcome to our service! We\'re excited to have you on board.</p><p>Best regards,<br>The Team</p>'
        },
        newsletter: {
            name: 'Newsletter Template',
            subject: 'Monthly Newsletter',
            body: '<h2>This Month\'s Updates</h2><p>Hi {{name}},</p><p>Here are this month\'s highlights...</p>'
        },
        meeting: {
            name: 'Meeting Request',
            subject: 'Meeting Request',
            body: '<p>Hi {{name}},</p><p>I would like to schedule a meeting with you to discuss...</p><p>Best regards</p>'
        },
        followup: {
            name: 'Follow-up Email',
            subject: 'Following up on our conversation',
            body: '<p>Hi {{name}},</p><p>I wanted to follow up on our recent conversation about...</p><p>Looking forward to hearing from you.</p>'
        }
    };

    const template = templates[type];
    if (template) {
        document.querySelector('input[name="name"]').value = template.name;
        document.querySelector('input[name="subject"]').value = template.subject;
        if (tinymce.get('template_body')) {
            tinymce.get('template_body').setContent(template.body);
        } else {
            document.getElementById('template_body').value = template.body;
        }
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('is_html').checked) {
        initTinyMCE();
    }
});
</script>

<?php include 'footer.php'; ?>
