<?php
$pageTitle = "API Documentation";
require_once 'layout.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">API Documentation</h1>
            <p class="lead">Complete reference for the Mail Hosting REST API</p>
        </div>
    </div>

    <!-- API Key Management -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Your API Keys</h3>
                    <div class="card-tools">
                        <button class="btn btn-primary btn-sm" onclick="generateApiKey()">
                            <i class="fas fa-plus"></i> Generate New Key
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Keep your API keys secure!</strong> Do not share them in publicly accessible areas.
                    </div>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Key Name</th>
                                <th>API Key</th>
                                <th>Created</th>
                                <th>Last Used</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="api-keys-list">
                            <tr>
                                <td>Production Key</td>
                                <td>
                                    <code>mail_live_abc123def456...</code>
                                    <button class="btn btn-sm btn-outline-secondary ml-2" onclick="copyToClipboard('mail_live_abc123def456')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </td>
                                <td>Jan 1, 2026</td>
                                <td>2 hours ago</td>
                                <td>
                                    <button class="btn btn-sm btn-danger" onclick="revokeApiKey(1)">
                                        <i class="fas fa-trash"></i> Revoke
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="row mt-4">
        <div class="col-12">
            <ul class="nav nav-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" data-toggle="tab" href="#getting-started">Getting Started</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#authentication">Authentication</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#email-api">Email Operations</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#mailbox-api">Mailbox Operations</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#domain-api">Domain Operations</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" data-toggle="tab" href="#rate-limits">Rate Limits</a>
                </li>
            </ul>

            <div class="tab-content mt-3">
                <!-- Getting Started -->
                <div id="getting-started" class="tab-pane fade show active">
                    <div class="card">
                        <div class="card-body">
                            <h3>Getting Started</h3>
                            <p>Our REST API allows you to interact with the mail hosting platform programmatically.</p>
                            
                            <h4>Base URL</h4>
                            <pre><code>https://yourdomain.com/api/v1</code></pre>
                            
                            <h4>Quick Example</h4>
                            <pre><code>curl -X POST https://yourdomain.com/api/v1/mail/send \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "recipient@example.com",
    "subject": "Hello World",
    "body": "This is a test email",
    "html": true
  }'</code></pre>
                        </div>
                    </div>
                </div>

                <!-- Authentication -->
                <div id="authentication" class="tab-pane fade">
                    <div class="card">
                        <div class="card-body">
                            <h3>Authentication</h3>
                            <p>All API requests must be authenticated using a Bearer token in the Authorization header.</p>
                            
                            <h4>Header Format</h4>
                            <pre><code>Authorization: Bearer YOUR_API_KEY</code></pre>
                            
                            <h4>Example Request</h4>
                            <pre><code>curl https://yourdomain.com/api/v1/stats \
  -H "Authorization: Bearer mail_live_abc123def456"</code></pre>
                            
                            <div class="alert alert-warning mt-3">
                                <strong>Security:</strong> Always use HTTPS in production. Never commit API keys to version control.
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Email API -->
                <div id="email-api" class="tab-pane fade">
                    <div class="card">
                        <div class="card-body">
                            <h3>Email Operations</h3>
                            
                            <!-- Send Email -->
                            <div class="mb-4">
                                <h4>Send Email</h4>
                                <p><span class="badge badge-success">POST</span> <code>/api/v1/mail/send</code></p>
                                
                                <h5>Request Body</h5>
                                <pre><code>{
  "to": "recipient@example.com",
  "subject": "Email Subject",
  "body": "Email body content",
  "html": true,
  "cc": ["cc@example.com"],
  "bcc": ["bcc@example.com"],
  "attachments": ["path/to/file.pdf"]
}</code></pre>

                                <h5>Response</h5>
                                <pre><code>{
  "success": true,
  "data": {
    "message_id": 12345,
    "status": "queued"
  },
  "timestamp": 1672531200
}</code></pre>

                                <h5>cURL Example</h5>
                                <pre><code>curl -X POST https://yourdomain.com/api/v1/mail/send \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "user@example.com",
    "subject": "Test",
    "body": "Hello!",
    "html": true
  }'</code></pre>
                            </div>

                            <!-- List Inbox -->
                            <div class="mb-4">
                                <h4>List Inbox Messages</h4>
                                <p><span class="badge badge-info">GET</span> <code>/api/v1/mail/inbox</code></p>
                                
                                <h5>Query Parameters</h5>
                                <table class="table table-sm">
                                    <tr>
                                        <td><code>page</code></td>
                                        <td>Page number (default: 1)</td>
                                    </tr>
                                    <tr>
                                        <td><code>limit</code></td>
                                        <td>Results per page (default: 50, max: 100)</td>
                                    </tr>
                                    <tr>
                                        <td><code>mailbox_id</code></td>
                                        <td>Filter by specific mailbox</td>
                                    </tr>
                                </table>

                                <h5>Response</h5>
                                <pre><code>{
  "success": true,
  "data": {
    "messages": [
      {
        "id": 123,
        "from_email": "sender@example.com",
        "subject": "Hello",
        "is_read": false,
        "created_at": "2026-01-03 10:30:00"
      }
    ],
    "page": 1,
    "limit": 50
  }
}</code></pre>
                            </div>

                            <!-- Get Message -->
                            <div class="mb-4">
                                <h4>Get Single Message</h4>
                                <p><span class="badge badge-info">GET</span> <code>/api/v1/mail/messages/{id}</code></p>
                                
                                <h5>Response</h5>
                                <pre><code>{
  "success": true,
  "data": {
    "id": 123,
    "from_email": "sender@example.com",
    "to_email": "recipient@example.com",
    "subject": "Hello",
    "body_html": "<p>Message content</p>",
    "is_read": false,
    "has_attachments": true,
    "attachments": [
      {
        "file_name": "document.pdf",
        "file_size": 102400,
        "mime_type": "application/pdf"
      }
    ]
  }
}</code></pre>
                            </div>

                            <!-- Delete Message -->
                            <div class="mb-4">
                                <h4>Delete Message</h4>
                                <p><span class="badge badge-danger">DELETE</span> <code>/api/v1/mail/messages/{id}</code></p>
                                
                                <h5>Response</h5>
                                <pre><code>{
  "success": true,
  "data": {
    "message": "Message deleted"
  }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mailbox API -->
                <div id="mailbox-api" class="tab-pane fade">
                    <div class="card">
                        <div class="card-body">
                            <h3>Mailbox Operations</h3>
                            
                            <div class="mb-4">
                                <h4>List Mailboxes</h4>
                                <p><span class="badge badge-info">GET</span> <code>/api/v1/mailboxes</code></p>
                                
                                <h5>Response</h5>
                                <pre><code>{
  "success": true,
  "data": {
    "mailboxes": [
      {
        "id": 1,
        "email": "user@example.com",
        "role_type": "end_user",
        "storage_used": 1024000,
        "storage_quota": 5368709120,
        "is_active": true
      }
    ]
  }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Domain API -->
                <div id="domain-api" class="tab-pane fade">
                    <div class="card">
                        <div class="card-body">
                            <h3>Domain Operations</h3>
                            
                            <div class="mb-4">
                                <h4>List Domains</h4>
                                <p><span class="badge badge-info">GET</span> <code>/api/v1/domains</code></p>
                                
                                <h5>Response</h5>
                                <pre><code>{
  "success": true,
  "data": {
    "domains": [
      {
        "id": 1,
        "domain_name": "example.com",
        "is_verified": true,
        "created_at": "2026-01-01 10:00:00"
      }
    ]
  }
}</code></pre>
                            </div>

                            <div class="mb-4">
                                <h4>Verify Domain</h4>
                                <p><span class="badge badge-success">POST</span> <code>/api/v1/domains/{id}/verify</code></p>
                                
                                <h5>Response</h5>
                                <pre><code>{
  "success": true,
  "data": {
    "verified": true,
    "mx_records": ["mail.example.com"],
    "spf_valid": true,
    "dkim_valid": true
  }
}</code></pre>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Rate Limits -->
                <div id="rate-limits" class="tab-pane fade">
                    <div class="card">
                        <div class="card-body">
                            <h3>Rate Limits</h3>
                            <p>API rate limits vary by plan:</p>
                            
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Plan</th>
                                        <th>Requests per Hour</th>
                                        <th>Emails per Day</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Free</td>
                                        <td>100</td>
                                        <td>50</td>
                                    </tr>
                                    <tr>
                                        <td>Starter</td>
                                        <td>1,000</td>
                                        <td>500</td>
                                    </tr>
                                    <tr>
                                        <td>Business</td>
                                        <td>5,000</td>
                                        <td>2,000</td>
                                    </tr>
                                    <tr>
                                        <td>Developer</td>
                                        <td>10,000</td>
                                        <td>10,000</td>
                                    </tr>
                                </tbody>
                            </table>

                            <h4>Rate Limit Headers</h4>
                            <p>Each response includes rate limit information:</p>
                            <pre><code>X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 998
X-RateLimit-Reset: 1672534800</code></pre>

                            <div class="alert alert-warning mt-3">
                                <strong>429 Too Many Requests:</strong> If you exceed the rate limit, you'll receive a 429 status code. Wait until the reset time before making more requests.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Code Examples -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Code Examples</h3>
                </div>
                <div class="card-body">
                    <ul class="nav nav-pills mb-3">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="tab" href="#example-curl">cURL</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#example-php">PHP</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#example-python">Python</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="tab" href="#example-javascript">JavaScript</a>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div id="example-curl" class="tab-pane fade show active">
                            <pre><code>curl -X POST https://yourdomain.com/api/v1/mail/send \
  -H "Authorization: Bearer YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d '{
    "to": "recipient@example.com",
    "subject": "Test Email",
    "body": "Hello from cURL!",
    "html": true
  }'</code></pre>
                        </div>

                        <div id="example-php" class="tab-pane fade">
                            <pre><code>&lt;?php
$apiKey = 'YOUR_API_KEY';
$url = 'https://yourdomain.com/api/v1/mail/send';

$data = [
    'to' => 'recipient@example.com',
    'subject' => 'Test Email',
    'body' => 'Hello from PHP!',
    'html' => true
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $apiKey,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$result = json_decode($response, true);

if ($result['success']) {
    echo "Email sent! ID: " . $result['data']['message_id'];
}
?&gt;</code></pre>
                        </div>

                        <div id="example-python" class="tab-pane fade">
                            <pre><code>import requests

api_key = 'YOUR_API_KEY'
url = 'https://yourdomain.com/api/v1/mail/send'

headers = {
    'Authorization': f'Bearer {api_key}',
    'Content-Type': 'application/json'
}

data = {
    'to': 'recipient@example.com',
    'subject': 'Test Email',
    'body': 'Hello from Python!',
    'html': True
}

response = requests.post(url, json=data, headers=headers)
result = response.json()

if result['success']:
    print(f"Email sent! ID: {result['data']['message_id']}")</code></pre>
                        </div>

                        <div id="example-javascript" class="tab-pane fade">
                            <pre><code>const apiKey = 'YOUR_API_KEY';
const url = 'https://yourdomain.com/api/v1/mail/send';

const data = {
  to: 'recipient@example.com',
  subject: 'Test Email',
  body: 'Hello from JavaScript!',
  html: true
};

fetch(url, {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${apiKey}`,
    'Content-Type': 'application/json'
  },
  body: JSON.stringify(data)
})
.then(response => response.json())
.then(result => {
  if (result.success) {
    console.log(`Email sent! ID: ${result.data.message_id}`);
  }
})
.catch(error => console.error('Error:', error));</code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function generateApiKey() {
    const keyName = prompt('Enter a name for this API key:');
    if (!keyName) return;
    
    fetch('/projects/mail/api/keys/generate', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({name: keyName})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('API Key Generated!\n\n' + data.data.api_key + '\n\nSave this key securely. It will only be shown once.');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    });
}

function revokeApiKey(keyId) {
    if (!confirm('Are you sure you want to revoke this API key? This action cannot be undone.')) {
        return;
    }
    
    fetch('/projects/mail/api/keys/' + keyId + '/revoke', {
        method: 'DELETE'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('API key revoked successfully');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    });
}

function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('API key copied to clipboard');
    });
}
</script>

<?php require_once 'layout_footer.php'; ?>
