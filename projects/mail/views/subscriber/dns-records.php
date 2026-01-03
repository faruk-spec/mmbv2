<!-- DNS Records View -->
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-cog mr-2"></i>
                        DNS Records for <?= View::e($domain['domain_name']) ?>
                    </h2>
                    <p class="text-muted">Configure these DNS records at your domain registrar</p>
                </div>
                <div>
                    <button class="btn btn-success" onclick="verifyDomain(<?= $domain['id'] ?>)">
                        <i class="fas fa-check-circle"></i> Verify Now
                    </button>
                    <a href="/projects/mail/subscriber/domains" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Domains
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Verification Status -->
    <div class="row mb-4">
        <div class="col-12">
            <?php if ($domain['is_verified']): ?>
            <div class="alert alert-success">
                <h4 class="alert-heading">
                    <i class="fas fa-check-circle"></i> Domain Verified!
                </h4>
                <p class="mb-0">
                    Your domain has been successfully verified and is ready to send and receive emails.
                    Verified on: <strong><?= date('F d, Y \a\t H:i', strtotime($domain['verified_at'])) ?></strong>
                </p>
            </div>
            <?php else: ?>
            <div class="alert alert-warning">
                <h4 class="alert-heading">
                    <i class="fas fa-exclamation-triangle"></i> Verification Pending
                </h4>
                <p class="mb-0">
                    Your domain is not yet verified. Please add the DNS records below to your domain registrar
                    and click "Verify Now" after they have propagated (typically 1-48 hours).
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- DNS Records -->
    <div class="row">
        <!-- MX Records -->
        <div class="col-12 mb-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-mail-bulk mr-2"></i>
                        MX Records (Mail Exchange)
                    </h3>
                    <div class="card-tools">
                        <button class="btn btn-tool btn-sm" onclick="copyAllMX()">
                            <i class="fas fa-copy"></i> Copy All
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        MX records tell other mail servers where to deliver emails for your domain.
                    </p>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Name/Host</th>
                                    <th>Value/Points To</th>
                                    <th>Priority</th>
                                    <th>TTL</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge badge-primary">MX</span></td>
                                    <td><code>@</code> or <code><?= View::e($domain['domain_name']) ?></code></td>
                                    <td><code id="mx_value">mail.<?= View::e(config('app.domain', 'yourdomain.com')) ?></code></td>
                                    <td><code>10</code></td>
                                    <td><code>3600</code></td>
                                    <td>
                                        <button class="btn btn-sm btn-info" onclick="copyToClipboard('mx_value')">
                                            <i class="fas fa-copy"></i> Copy
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- SPF Record -->
        <div class="col-md-6 mb-4">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-shield-alt mr-2"></i>
                        SPF Record (Sender Policy Framework)
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        SPF helps prevent spammers from sending emails on behalf of your domain.
                    </p>
                    <div class="form-group">
                        <label class="small"><strong>Type:</strong></label>
                        <p><span class="badge badge-success">TXT</span></p>
                    </div>
                    <div class="form-group">
                        <label class="small"><strong>Name/Host:</strong></label>
                        <p><code>@</code> or <code><?= View::e($domain['domain_name']) ?></code></p>
                    </div>
                    <div class="form-group">
                        <label class="small"><strong>Value:</strong></label>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm" id="spf_value" readonly
                                   value='v=spf1 include:<?= View::e(config('app.domain', 'yourdomain.com')) ?> ~all'>
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-info" onclick="copyToClipboard('spf_value')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DKIM Record -->
        <div class="col-md-6 mb-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-key mr-2"></i>
                        DKIM Record (DomainKeys Identified Mail)
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        DKIM adds a digital signature to your emails to verify authenticity.
                    </p>
                    <div class="form-group">
                        <label class="small"><strong>Type:</strong></label>
                        <p><span class="badge badge-info">TXT</span></p>
                    </div>
                    <div class="form-group">
                        <label class="small"><strong>Name/Host:</strong></label>
                        <p><code>default._domainkey</code></p>
                    </div>
                    <div class="form-group">
                        <label class="small"><strong>Value:</strong></label>
                        <div class="input-group">
                            <textarea class="form-control form-control-sm" id="dkim_value" rows="3" readonly>v=DKIM1; k=rsa; p=<?= $dkimPublicKey ?? 'YOUR_DKIM_PUBLIC_KEY_WILL_BE_GENERATED_HERE' ?></textarea>
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-info" onclick="copyToClipboard('dkim_value')">
                                    <i class="fas fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- DMARC Record -->
        <div class="col-12 mb-4">
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-shield mr-2"></i>
                        DMARC Record (Domain-based Message Authentication)
                    </h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        DMARC tells receiving servers what to do with emails that fail SPF or DKIM checks.
                    </p>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="small"><strong>Type:</strong></label>
                            <p><span class="badge badge-warning">TXT</span></p>
                        </div>
                        <div class="col-md-3">
                            <label class="small"><strong>Name/Host:</strong></label>
                            <p><code>_dmarc</code></p>
                        </div>
                        <div class="col-md-6">
                            <label class="small"><strong>Value:</strong></label>
                            <div class="input-group">
                                <input type="text" class="form-control form-control-sm" id="dmarc_value" readonly
                                       value='v=DMARC1; p=quarantine; rua=mailto:dmarc@<?= View::e($domain['domain_name']) ?>'>
                                <div class="input-group-append">
                                    <button class="btn btn-sm btn-info" onclick="copyToClipboard('dmarc_value')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Setup Instructions -->
    <div class="row">
        <div class="col-12">
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list-ol mr-2"></i>
                        Setup Instructions
                    </h3>
                </div>
                <div class="card-body">
                    <h5>How to Add DNS Records:</h5>
                    <ol>
                        <li class="mb-2">
                            <strong>Log in to your domain registrar</strong> (GoDaddy, Namecheap, Cloudflare, etc.)
                        </li>
                        <li class="mb-2">
                            <strong>Find the DNS management</strong> or DNS settings page for your domain
                        </li>
                        <li class="mb-2">
                            <strong>Add each record</strong> shown above using the Type, Name/Host, and Value fields
                        </li>
                        <li class="mb-2">
                            <strong>Set the TTL</strong> (Time To Live) to 3600 seconds (1 hour) or use the default
                        </li>
                        <li class="mb-2">
                            <strong>Save the records</strong> and wait for DNS propagation (1-48 hours)
                        </li>
                        <li class="mb-2">
                            <strong>Click "Verify Now"</strong> button above to check if the records are properly configured
                        </li>
                    </ol>

                    <div class="alert alert-info mt-3">
                        <h5 class="alert-heading">
                            <i class="fas fa-lightbulb"></i> Pro Tips
                        </h5>
                        <ul class="mb-0">
                            <li>Use <a href="https://mxtoolbox.com/" target="_blank">MXToolbox</a> to verify your DNS records</li>
                            <li>DNS changes can take up to 48 hours to propagate globally</li>
                            <li>Some registrars use "@" for the root domain, others use the domain name itself</li>
                            <li>Make sure to remove any conflicting MX or SPF records before adding new ones</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(elementId) {
    const element = document.getElementById(elementId);
    const text = element.value || element.textContent;
    
    navigator.clipboard.writeText(text).then(() => {
        // Show success message
        const btn = event.target.closest('button');
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        btn.classList.remove('btn-info');
        btn.classList.add('btn-success');
        
        setTimeout(() => {
            btn.innerHTML = originalHTML;
            btn.classList.remove('btn-success');
            btn.classList.add('btn-info');
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy:', err);
        alert('Failed to copy to clipboard');
    });
}

function copyAllMX() {
    const mxValue = document.getElementById('mx_value').textContent;
    const text = `Type: MX\nName: @\nValue: ${mxValue}\nPriority: 10\nTTL: 3600`;
    
    navigator.clipboard.writeText(text).then(() => {
        alert('MX record copied to clipboard!');
    }).catch(err => {
        console.error('Failed to copy:', err);
        alert('Failed to copy to clipboard');
    });
}

function verifyDomain(domainId) {
    const btn = event.target;
    const originalHTML = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
    
    fetch('/projects/mail/subscriber/domains/verify', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: `domain_id=${domainId}`
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            if (d.verified) {
                alert('✅ Domain verified successfully! Your domain is now ready to use.');
                location.reload();
            } else {
                alert('⚠️ Domain verification failed. Please ensure all DNS records are properly configured and try again in a few hours.\n\nDetails: ' + (d.message || 'DNS records not found'));
            }
        } else {
            alert('Error: ' + d.message);
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('An error occurred while verifying the domain');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHTML;
    });
}
</script>

<style>
code {
    background-color: #f4f4f4;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 90%;
}
</style>
