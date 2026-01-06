<!-- Add New User View -->
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-0">
                        <i class="fas fa-user-plus mr-2"></i>
                        Add New User
                    </h2>
                    <p class="text-muted">Create a new email account in your subscription</p>
                </div>
                <div>
                    <a href="/projects/mail/subscriber/users" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Users
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Add User Form -->
    <div class="row">
        <div class="col-lg-8 offset-lg-2">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user mr-2"></i>
                        User Information
                    </h3>
                </div>
                <form method="POST" action="/projects/mail/subscriber/users/add" id="addUserForm">
                    <div class="card-body">
                        <!-- Email Address -->
                        <div class="form-group">
                            <label for="email">
                                Email Address * 
                                <small class="text-muted">(The complete email address for this user)</small>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                </div>
                                <input type="email" class="form-control" id="email" name="email" 
                                       placeholder="user@example.com" required>
                            </div>
                            <small class="form-text text-muted">
                                This will be the user's login email and mailbox address
                            </small>
                        </div>

                        <!-- Username -->
                        <div class="form-group">
                            <label for="username">
                                Username * 
                                <small class="text-muted">(Username portion of the email)</small>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-user"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" id="username" name="username" 
                                       placeholder="username" required pattern="[a-zA-Z0-9._-]+">
                            </div>
                        </div>

                        <!-- Display Name -->
                        <div class="form-group">
                            <label for="display_name">
                                Display Name
                                <small class="text-muted">(Optional - shown in sent emails)</small>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-id-card"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control" id="display_name" name="display_name" 
                                       placeholder="John Doe">
                            </div>
                        </div>

                        <!-- Domain Selection -->
                        <div class="form-group">
                            <label for="domain_id">
                                Domain * 
                                <small class="text-muted">(Select which domain this user belongs to)</small>
                            </label>
                            <select class="form-control" id="domain_id" name="domain_id" required>
                                <option value="">-- Select a domain --</option>
                                <?php if (empty($domains)): ?>
                                <option value="" disabled>No verified domains available</option>
                                <?php else: ?>
                                    <?php foreach ($domains as $domain): ?>
                                    <option value="<?= $domain['id'] ?>">
                                        <?= View::e($domain['domain_name']) ?>
                                        <?php if ($domain['is_verified']): ?>
                                            ✓ Verified
                                        <?php else: ?>
                                            ⚠ Pending Verification
                                        <?php endif; ?>
                                    </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <?php if (empty($domains)): ?>
                            <small class="form-text text-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                You need to add and verify at least one domain before creating users.
                                <a href="/projects/mail/subscriber/domains/add">Add a domain now</a>
                            </small>
                            <?php endif; ?>
                        </div>

                        <!-- Password -->
                        <div class="form-group">
                            <label for="password">
                                Password * 
                                <small class="text-muted">(Minimum 8 characters)</small>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                </div>
                                <input type="password" class="form-control" id="password" name="password" 
                                       required minlength="8">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" 
                                            onclick="togglePassword()">
                                        <i class="fas fa-eye" id="toggleIcon"></i>
                                    </button>
                                    <button class="btn btn-outline-info" type="button" 
                                            onclick="generatePassword()">
                                        <i class="fas fa-random"></i> Generate
                                    </button>
                                </div>
                            </div>
                            <div class="password-strength mt-2" style="display: none;">
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar" id="strengthBar" style="width: 0%"></div>
                                </div>
                                <small class="text-muted" id="strengthText"></small>
                            </div>
                        </div>

                        <!-- Role Type -->
                        <div class="form-group">
                            <label for="role_type">
                                User Role * 
                                <small class="text-muted">(Determines what the user can do)</small>
                            </label>
                            <select class="form-control" id="role_type" name="role_type" required>
                                <option value="end_user" selected>
                                    End User - Basic email access (send/receive only)
                                </option>
                                <option value="domain_admin">
                                    Domain Admin - Can manage users for this domain
                                </option>
                            </select>
                            <small class="form-text text-info">
                                <i class="fas fa-info-circle"></i>
                                Domain admins can add/edit/delete other users within their assigned domains
                            </small>
                        </div>

                        <!-- Storage Quota -->
                        <div class="form-group">
                            <label for="storage_quota">
                                Storage Quota (GB) * 
                                <small class="text-muted">(Mailbox storage limit)</small>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <i class="fas fa-hdd"></i>
                                    </span>
                                </div>
                                <input type="number" class="form-control" id="storage_quota" 
                                       name="storage_quota" value="5" required min="1" max="100" step="1">
                                <div class="input-group-append">
                                    <span class="input-group-text">GB</span>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Default: 5GB. Adjust based on user needs and your plan limits.
                            </small>
                        </div>

                        <!-- Additional Options -->
                        <div class="card bg-light">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="fas fa-cog"></i> Additional Options
                                </h5>
                                
                                <div class="custom-control custom-switch mb-2">
                                    <input type="checkbox" class="custom-control-input" id="receive_enabled" 
                                           name="receive_enabled" value="1" checked>
                                    <label class="custom-control-label" for="receive_enabled">
                                        Enable receiving emails
                                    </label>
                                </div>

                                <div class="custom-control custom-switch mb-2">
                                    <input type="checkbox" class="custom-control-input" id="send_enabled" 
                                           name="send_enabled" value="1" checked>
                                    <label class="custom-control-label" for="send_enabled">
                                        Enable sending emails
                                    </label>
                                </div>

                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" id="send_welcome_email" 
                                           name="send_welcome_email" value="1" checked>
                                    <label class="custom-control-label" for="send_welcome_email">
                                        Send welcome email with login credentials
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary btn-lg" <?= empty($domains) ? 'disabled' : '' ?>>
                            <i class="fas fa-plus-circle"></i> Create User
                        </button>
                        <a href="/projects/mail/subscriber/users" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-fill email from username and domain
document.getElementById('username')?.addEventListener('input', updateEmail);
document.getElementById('domain_id')?.addEventListener('change', updateEmail);

function updateEmail() {
    const username = document.getElementById('username').value;
    const domainSelect = document.getElementById('domain_id');
    const domainText = domainSelect.options[domainSelect.selectedIndex]?.text;
    
    if (username && domainText) {
        const domain = domainText.split(' ')[0]; // Get just the domain name
        document.getElementById('email').value = username + '@' + domain;
    }
}

// Toggle password visibility
function togglePassword() {
    const passwordInput = document.getElementById('password');
    const toggleIcon = document.getElementById('toggleIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        toggleIcon.className = 'fas fa-eye-slash';
    } else {
        passwordInput.type = 'password';
        toggleIcon.className = 'fas fa-eye';
    }
}

// Generate random password
function generatePassword() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%&*';
    let password = '';
    for (let i = 0; i < 16; i++) {
        password += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('password').value = password;
    document.getElementById('password').type = 'text';
    document.getElementById('toggleIcon').className = 'fas fa-eye-slash';
    checkPasswordStrength();
}

// Password strength checker
document.getElementById('password')?.addEventListener('input', checkPasswordStrength);

function checkPasswordStrength() {
    const password = document.getElementById('password').value;
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    const container = document.querySelector('.password-strength');
    
    if (!password) {
        container.style.display = 'none';
        return;
    }
    
    container.style.display = 'block';
    
    let strength = 0;
    if (password.length >= 8) strength += 25;
    if (password.length >= 12) strength += 25;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength += 20;
    if (/\d/.test(password)) strength += 15;
    if (/[^a-zA-Z0-9]/.test(password)) strength += 15;
    
    let color = 'danger';
    let text = 'Weak';
    
    if (strength >= 80) {
        color = 'success';
        text = 'Strong';
    } else if (strength >= 60) {
        color = 'warning';
        text = 'Good';
    } else if (strength >= 40) {
        color = 'info';
        text = 'Fair';
    }
    
    strengthBar.style.width = strength + '%';
    strengthBar.className = 'progress-bar bg-' + color;
    strengthText.textContent = 'Password strength: ' + text;
    strengthText.className = 'text-' + color;
}

// Form validation
document.getElementById('addUserForm')?.addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    if (password.length < 8) {
        e.preventDefault();
        alert('Password must be at least 8 characters long');
        return false;
    }
});
</script>
