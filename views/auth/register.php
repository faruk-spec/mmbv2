<?php use Core\View; use Core\Helpers; use Core\GoogleOAuth; ?>
<?php View::extend('main'); ?>

<?php View::section('styles'); ?>
<style>
/* Reuse auth-split styles from login.php (main layout caches nothing, so re-declaring is safe) */
.auth-split {
    display: grid;
    grid-template-columns: 1fr 1fr;
    min-height: calc(100vh - 62px);
}

.auth-visual {
    background: var(--bg-secondary);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 40px;
    position: relative;
    overflow: hidden;
    border-right: 1px solid var(--border-color);
}

.auth-visual-orb {
    position: absolute;
    border-radius: 50%;
    filter: blur(80px);
    pointer-events: none;
}

.auth-visual-orb-1 {
    width: 320px; height: 320px;
    background: rgba(0, 240, 255, 0.18);
    top: -80px; right: -80px;
}

.auth-visual-orb-2 {
    width: 250px; height: 250px;
    background: rgba(153, 69, 255, 0.18);
    bottom: -60px; left: -60px;
}

.auth-visual-icon {
    width: 96px; height: 96px;
    background: rgba(0, 240, 255, 0.08);
    border: 1px solid rgba(0, 240, 255, 0.25);
    border-radius: var(--radius-xl);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 32px;
    position: relative;
    z-index: 1;
    box-shadow: 0 0 40px rgba(0, 240, 255, 0.15), 0 0 80px rgba(153, 69, 255, 0.08);
}

.auth-visual-title {
    font-family: var(--font-heading);
    font-size: 2rem;
    font-weight: 800;
    letter-spacing: -0.03em;
    background: linear-gradient(135deg, var(--cyan), var(--purple));
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
    text-align: center;
    position: relative;
    z-index: 1;
    margin-bottom: 12px;
}

.auth-visual-sub {
    font-size: 0.9rem;
    color: var(--text-secondary);
    text-align: center;
    max-width: 300px;
    line-height: 1.6;
    position: relative;
    z-index: 1;
}

.auth-step-list {
    margin-top: 36px;
    display: flex;
    flex-direction: column;
    gap: 16px;
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 300px;
}

.auth-step-item {
    display: flex;
    align-items: flex-start;
    gap: 14px;
}

.auth-step-num {
    width: 28px; height: 28px;
    border-radius: var(--radius-full);
    background: var(--gradient-primary);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    color: #fff;
    flex-shrink: 0;
    font-family: var(--font-heading);
}

.auth-step-text {
    font-size: 13px;
    color: var(--text-secondary);
    line-height: 1.5;
    padding-top: 4px;
}

.auth-form-panel {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 60px 50px;
    background: var(--bg-primary);
}

.auth-form-inner {
    width: 100%;
    max-width: 420px;
}

.auth-form-title {
    font-family: var(--font-heading);
    font-size: 1.8rem;
    font-weight: 800;
    letter-spacing: -0.025em;
    margin-bottom: 6px;
    color: var(--text-primary);
}

.auth-form-sub {
    font-size: 14px;
    color: var(--text-secondary);
    margin-bottom: 32px;
}

.auth-divider {
    display: flex;
    align-items: center;
    gap: 14px;
    margin: 20px 0;
    color: var(--text-secondary);
    font-size: 12px;
}

.auth-divider::before,
.auth-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: var(--border-color);
}

.auth-google-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    width: 100%;
    padding: 11px 20px;
    border-radius: var(--radius-full);
    border: 1px solid var(--border-color);
    background: var(--bg-card);
    color: var(--text-primary);
    font-family: var(--font-heading);
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: var(--transition);
    text-decoration: none;
}

.auth-google-btn:hover {
    border-color: var(--cyan);
    background: rgba(0, 240, 255, 0.06);
    color: var(--text-primary);
    box-shadow: var(--glow-cyan);
}

@media (max-width: 860px) {
    .auth-split { grid-template-columns: 1fr; min-height: auto; }
    .auth-visual { padding: 40px 30px; border-right: none; border-bottom: 1px solid var(--border-color); }
    .auth-step-list { display: none; }
    .auth-form-panel { padding: 40px 24px; }
}
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div class="auth-split">
    <!-- Left Visual Panel -->
    <div class="auth-visual">
        <div class="auth-visual-orb auth-visual-orb-1"></div>
        <div class="auth-visual-orb auth-visual-orb-2"></div>

        <div class="auth-visual-icon">
            <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="url(#ag2)" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <defs><linearGradient id="ag2" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="#00f0ff"/><stop offset="100%" stop-color="#9945ff"/></linearGradient></defs>
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <line x1="19" y1="8" x2="19" y2="14"/>
                <line x1="22" y1="11" x2="16" y2="11"/>
            </svg>
        </div>

        <div class="auth-visual-title">Join <?= htmlspecialchars(defined('APP_NAME') ? APP_NAME : 'MyMultiBranch') ?></div>
        <p class="auth-visual-sub">Create your account and get instant access to 12+ professional tools.</p>

        <div class="auth-step-list">
            <div class="auth-step-item">
                <div class="auth-step-num">1</div>
                <div class="auth-step-text">Fill in your name, email and a strong password</div>
            </div>
            <div class="auth-step-item">
                <div class="auth-step-num">2</div>
                <div class="auth-step-text">Verify your email to activate your account</div>
            </div>
            <div class="auth-step-item">
                <div class="auth-step-num">3</div>
                <div class="auth-step-text">Access all platform tools with a single login</div>
            </div>
            <div class="auth-step-item">
                <div class="auth-step-num">4</div>
                <div class="auth-step-text">Enable 2FA for maximum account security</div>
            </div>
        </div>
    </div>

    <!-- Right Form Panel -->
    <div class="auth-form-panel">
        <div class="auth-form-inner">
            <h1 class="auth-form-title">Create an account</h1>
            <p class="auth-form-sub">Free forever. No credit card required.</p>

            <?php if (Helpers::hasFlash('error')): ?>
                <div class="alert alert-error"><?= View::e(Helpers::getFlash('error')) ?></div>
            <?php endif; ?>

            <?php if (GoogleOAuth::isEnabled()): ?>
            <a href="/auth/google?register=1" class="auth-google-btn">
                <svg width="18" height="18" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                    <g fill="none" fill-rule="evenodd">
                        <path d="M17.64 9.205c0-.639-.057-1.252-.164-1.841H9v3.481h4.844a4.14 4.14 0 0 1-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/>
                        <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"/>
                        <path d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
                        <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
                    </g>
                </svg>
                Sign up with Google
            </a>
            <div class="auth-divider">or</div>
            <?php endif; ?>

            <form method="POST" action="/register">
                <?= \Core\Security::csrfField() ?>
                
                <div class="form-group">
                    <label class="form-label" for="name">Full Name</label>
                    <input type="text" id="name" name="name" class="form-input" 
                           value="<?= View::old('name') ?>" placeholder="John Doe" required minlength="2" maxlength="50" autocomplete="name">
                    <?php if (View::hasError('name')): ?>
                        <div class="form-error"><?= View::error('name') ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="email">Email address</label>
                    <input type="email" id="email" name="email" class="form-input" 
                           value="<?= View::old('email') ?>" placeholder="you@example.com" required autocomplete="email">
                    <?php if (View::hasError('email')): ?>
                        <div class="form-error"><?= View::error('email') ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-input" 
                           placeholder="Min. 8 characters" required minlength="8" autocomplete="new-password">
                    <?php if (View::hasError('password')): ?>
                        <div class="form-error"><?= View::error('password') ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password_confirmation">Confirm Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" 
                           class="form-input" placeholder="Repeat password" required minlength="8" autocomplete="new-password">
                </div>
                
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px 20px; font-size: 14px; border-radius: var(--radius-full);">
                    Create Account
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </button>
            </form>
            
            <p style="text-align: center; margin-top: 24px; color: var(--text-secondary); font-size: 14px;">
                Already have an account? <a href="/login" style="color: var(--cyan); font-weight: 600;">Sign In</a>
            </p>
        </div>
    </div>
</div>
<script>
(function() {
    var form = document.querySelector('form[action="/register"]');
    if (!form) return;
    form.addEventListener('submit', function(e) {
        var pw = document.getElementById('password');
        var pw2 = document.getElementById('password_confirmation');
        if (pw && pw2 && pw.value !== pw2.value) {
            e.preventDefault();
            var errEl = document.getElementById('pw-mismatch-error');
            if (!errEl) {
                errEl = document.createElement('div');
                errEl.id = 'pw-mismatch-error';
                errEl.className = 'form-error';
                pw2.parentNode.appendChild(errEl);
            }
            errEl.textContent = 'Passwords do not match. Please try again.';
            pw2.focus();
        }
    });
})();
</script>
<?php View::endSection(); ?>
