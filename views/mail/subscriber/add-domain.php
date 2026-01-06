<!-- Add Domain View -->
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-plus-circle mr-2"></i>
                        Add New Domain
                    </h2>
                    <p class="text-muted">Connect your custom domain to start sending and receiving emails</p>
                </div>
                <div>
                    <a href="/projects/mail/subscriber/domains" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Domains
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Add Domain Form -->
        <div class="col-lg-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-globe mr-2"></i>
                        Domain Information
                    </h3>
                </div>
                <form method="POST" action="/projects/mail/subscriber/domains/add" id="addDomainForm">
                    <div class="card-body">
                        <!-- Domain Name -->
                        <div class="form-group">
                            <label for="domain_name">
                                Domain Name *
                                <small class="text-muted">(e.g., example.com)</small>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-globe"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" id="domain_name" name="domain_name" 
                                       placeholder="example.com" required 
                                       pattern="^[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$">
                            </div>
                            <small class="form-text text-muted">
                                Enter your domain name without "www" or "http://"
                            </small>
                        </div>

                        <!-- Domain Purpose -->
                        <div class="form-group">
                            <label for="description">
                                Domain Purpose
                                <small class="text-muted">(Optional - for your reference)</small>
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="2" 
                                      placeholder="e.g., Company email, Customer support, etc."></textarea>
                        </div>

                        <!-- Catch-all Option -->
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-cog"></i> Domain Settings
                                </h5>
                                
                                <div class="form-group">
                                    <label for="catch_all_address">
                                        Catch-All Email Address
                                        <small class="text-muted">(Optional)</small>
                                    </label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">
                                                <i class="fas fa-envelope"></i>
                                            </span>
                                        </div>
                                        <input type="email" class="form-control" id="catch_all_address" 
                                               name="catch_all_address" 
                                               placeholder="all@example.com">
                                    </div>
                                    <small class="form-text text-muted">
                                        All emails sent to non-existent addresses will be forwarded to this mailbox
                                    </small>
                                </div>

                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="is_active" 
                                           name="is_active" value="1" checked>
                                    <label class="custom-control-label" for="is_active">
                                        Activate domain immediately after verification
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Important Notes -->
                        <div class="alert alert-info mt-3">
                            <h5 class="alert-heading">
                                <i class="fas fa-info-circle"></i> Important Information
                            </h5>
                            <ul class="mb-0">
                                <li>After adding the domain, you'll need to configure DNS records</li>
                                <li>DNS propagation can take up to 48 hours</li>
                                <li>You must have access to your domain's DNS settings</li>
                                <li>The domain will not work until DNS verification is complete</li>
                            </ul>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-plus-circle"></i> Add Domain
                        </button>
                        <a href="/projects/mail/subscriber/domains" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Help & FAQ -->
        <div class="col-lg-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-question-circle mr-2"></i>
                        Need Help?
                    </h3>
                </div>
                <div class="card-body">
                    <h5>What is a custom domain?</h5>
                    <p class="small">
                        A custom domain allows you to use your own domain name for email addresses
                        (e.g., yourname@yourdomain.com) instead of a generic provider.
                    </p>

                    <h5>Where do I manage DNS?</h5>
                    <p class="small">
                        DNS records are managed at your domain registrar or hosting provider.
                        Common providers include:
                    </p>
                    <ul class="small">
                        <li>GoDaddy</li>
                        <li>Namecheap</li>
                        <li>Cloudflare</li>
                        <li>Google Domains</li>
                        <li>Route 53 (AWS)</li>
                    </ul>

                    <h5>How long does verification take?</h5>
                    <p class="small">
                        After you add the DNS records, verification can take anywhere from a few
                        minutes to 48 hours, depending on DNS propagation speed.
                    </p>

                    <h5>What DNS records are needed?</h5>
                    <p class="small">
                        You'll need to add the following records:
                    </p>
                    <ul class="small">
                        <li><strong>MX</strong> - Mail server records</li>
                        <li><strong>SPF</strong> - Sender Policy Framework</li>
                        <li><strong>DKIM</strong> - DomainKeys Identified Mail</li>
                        <li><strong>DMARC</strong> - Domain-based Message Authentication</li>
                    </ul>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-link mr-2"></i>
                        Useful Tools
                    </h3>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <a href="https://mxtoolbox.com/" target="_blank" class="btn btn-outline-info btn-block btn-sm">
                                <i class="fas fa-external-link-alt"></i> MXToolbox - Check DNS
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="https://www.whatsmydns.net/" target="_blank" class="btn btn-outline-info btn-block btn-sm">
                                <i class="fas fa-external-link-alt"></i> DNS Propagation Checker
                            </a>
                        </li>
                        <li class="mb-2">
                            <a href="https://dmarcian.com/dmarc-inspector/" target="_blank" class="btn btn-outline-info btn-block btn-sm">
                                <i class="fas fa-external-link-alt"></i> DMARC Inspector
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Domain name validation
document.getElementById('domain_name')?.addEventListener('input', function(e) {
    const domain = e.target.value.toLowerCase().trim();
    
    // Remove http://, https://, www.
    let cleaned = domain.replace(/^(https?:\/\/)?(www\.)?/, '');
    
    // Remove trailing slash
    cleaned = cleaned.replace(/\/$/, '');
    
    if (cleaned !== domain) {
        e.target.value = cleaned;
    }
    
    // Update catch-all placeholder
    const catchAllInput = document.getElementById('catch_all_address');
    if (catchAllInput && cleaned) {
        catchAllInput.placeholder = `all@${cleaned}`;
    }
});

// Form validation
document.getElementById('addDomainForm')?.addEventListener('submit', function(e) {
    const domainInput = document.getElementById('domain_name');
    const domain = domainInput.value.trim();
    
    // Basic domain validation
    const domainRegex = /^[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?(\.[a-zA-Z0-9]([a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?)*$/;
    
    if (!domainRegex.test(domain)) {
        e.preventDefault();
        alert('Please enter a valid domain name (e.g., example.com)');
        domainInput.focus();
        return false;
    }
    
    // Check for common mistakes
    if (domain.includes('http') || domain.includes('www')) {
        e.preventDefault();
        alert('Please remove "http://", "https://", or "www." from the domain name');
        domainInput.focus();
        return false;
    }
});
</script>
