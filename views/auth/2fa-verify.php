<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('main'); ?>

<?php View::section('content'); ?>
<style>
.tfa-wrap { max-width: 420px; margin: 48px auto; }
.tfa-card { background: var(--bg-card, var(--bg-secondary)); border: 1px solid var(--border-color); border-radius: 16px; overflow: hidden; box-shadow: 0 8px 32px rgba(0,0,0,0.3); }
.tfa-header { background: linear-gradient(135deg, rgba(59,130,246,.12), rgba(139,92,246,.12)); border-bottom: 1px solid var(--border-color); padding: 28px 32px 24px; text-align: center; }
.tfa-icon { width: 60px; height: 60px; background: var(--cyan, #3b82f6); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; box-shadow: 0 4px 18px rgba(59,130,246,.2); }
.tfa-title { font-size: 1.35rem; font-weight: 700; color: var(--text-primary); margin: 0 0 6px; }
.tfa-subtitle { font-size: .875rem; color: var(--text-secondary); margin: 0; }
.tfa-body { padding: 28px 32px 32px; }
/* Countdown */
.tfa-countdown { display: flex; align-items: center; gap: 10px; margin-bottom: 22px; }
.tfa-bar-track { flex: 1; height: 4px; background: var(--border-color); border-radius: 2px; overflow: hidden; }
.tfa-bar-fill { height: 100%; background: var(--cyan, #3b82f6); border-radius: 2px; transition: width 1s linear; }
.tfa-timer { font-size: .78rem; color: var(--text-secondary); min-width: 54px; text-align: right; }
/* Digit boxes */
.tfa-digits { display: flex; gap: 8px; justify-content: center; margin-bottom: 22px; }
.tfa-digit { width: 46px; height: 58px; background: var(--bg-secondary); border: 1.5px solid var(--border-color); border-radius: 10px; font-size: 1.7rem; font-family: monospace; font-weight: 700; color: var(--text-primary); text-align: center; caret-color: var(--cyan, #3b82f6); transition: border-color .2s, box-shadow .2s; outline: none; }
.tfa-digit:focus { border-color: var(--cyan, #3b82f6); box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
.tfa-digit.filled { border-color: var(--cyan, #3b82f6); }
.tfa-btn { width: 100%; padding: 13px; background: var(--cyan, #3b82f6); border: none; border-radius: 10px; color: #ffffff; font-weight: 700; font-size: .95rem; cursor: pointer; transition: opacity .2s; }
.tfa-btn:hover { opacity: .9; }
.tfa-toggle { margin-top: 18px; text-align: center; }
.tfa-toggle-link { background: none; border: none; color: var(--cyan, #3b82f6); font-size: .85rem; cursor: pointer; text-decoration: underline; padding: 0; }
.tfa-backup-section { margin-top: 16px; display: none; }
.tfa-backup-section.visible { display: block; }
.tfa-backup-input { width: 100%; padding: 12px 16px; background: var(--bg-secondary); border: 1.5px solid var(--border-color); border-radius: 10px; color: var(--text-primary); font-family: monospace; font-size: 1rem; letter-spacing: 2px; outline: none; box-sizing: border-box; }
.tfa-backup-input:focus { border-color: var(--cyan, #3b82f6); }
.tfa-back { display: block; text-align: center; margin-top: 20px; color: var(--text-secondary); font-size: .85rem; text-decoration: none; }
.tfa-back:hover { color: var(--text-primary); }
</style>

<div class="tfa-wrap">
    <div class="tfa-card">
        <div class="tfa-header">
            <div class="tfa-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="var(--bg-primary, #09090b)" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <path d="M9 12l2 2 4-4"/>
                </svg>
            </div>
            <h1 class="tfa-title">Two-Factor Authentication</h1>
            <p class="tfa-subtitle">Enter the 6-digit code from your authenticator app.</p>
        </div>

        <div class="tfa-body">
            <?php if (Helpers::hasFlash('error')): ?>
                <div style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.4);border-radius:8px;padding:12px 14px;margin-bottom:18px;color:#ef4444;font-size:.875rem;">
                    <?= View::e(Helpers::getFlash('error')) ?>
                </div>
            <?php endif; ?>

            <!-- TOTP countdown -->
            <div class="tfa-countdown">
                <div class="tfa-bar-track"><div class="tfa-bar-fill" id="tfaBar" style="width:100%"></div></div>
                <span class="tfa-timer" id="tfaTimer">30s</span>
            </div>

            <form method="POST" action="/2fa/verify" id="tfaForm">
                <?= \Core\Security::csrfField() ?>
                <input type="hidden" name="code" id="tfaCodeHidden">
                <input type="hidden" name="use_backup" id="tfaUseBackup" value="0">

                <!-- 6-digit boxes -->
                <div class="tfa-digits" id="tfaDigits">
                    <?php for ($i = 0; $i < 6; $i++): ?>
                        <input type="text" inputmode="numeric" pattern="[0-9]" maxlength="1"
                               class="tfa-digit" data-idx="<?= $i ?>" autocomplete="<?= $i === 0 ? 'one-time-code' : 'off' ?>">
                    <?php endfor; ?>
                </div>

                <button type="submit" class="tfa-btn">Verify Code</button>

                <!-- Backup code toggle -->
                <div class="tfa-toggle">
                    <button type="button" class="tfa-toggle-link" id="tfaToggleBtn">Use a backup code instead</button>
                </div>

                <div class="tfa-backup-section" id="tfaBackupSection">
                    <label style="display:block;font-size:.85rem;color:var(--text-secondary);margin-bottom:8px;">Enter backup code:</label>
                    <input type="text" id="tfaBackupInput" class="tfa-backup-input" placeholder="xxxxxxxx" autocomplete="off">
                </div>
            </form>

            <a href="/login" class="tfa-back">&larr; Back to Login</a>
        </div>
    </div>
</div>

<script>
(function () {
    var digits   = Array.from(document.querySelectorAll('.tfa-digit'));
    var hidden   = document.getElementById('tfaCodeHidden');
    var form     = document.getElementById('tfaForm');
    var bar      = document.getElementById('tfaBar');
    var timerEl  = document.getElementById('tfaTimer');
    var toggleBtn = document.getElementById('tfaToggleBtn');
    var backupSec = document.getElementById('tfaBackupSection');
    var backupIn  = document.getElementById('tfaBackupInput');
    var useBackupField = document.getElementById('tfaUseBackup');
    var usingBackup = false;

    /* ---- TOTP 30-second countdown ---- */
    function updateCountdown() {
        var now = Math.floor(Date.now() / 1000);
        var remaining = 30 - (now % 30);
        var pct = (remaining / 30) * 100;
        bar.style.width = pct + '%';
        timerEl.textContent = remaining + 's';
    }
    updateCountdown();
    setInterval(updateCountdown, 1000);

    /* ---- Digit input handling ---- */
    function collectCode() {
        return digits.map(function (d) { return d.value; }).join('');
    }

    digits.forEach(function (input, idx) {
        input.addEventListener('input', function () {
            var val = input.value.replace(/\D/g, '');
            // Handle paste of all 6 digits into one box
            if (val.length > 1) {
                for (var i = 0; i < 6 && i < val.length; i++) {
                    digits[i].value = val[i];
                    digits[i].classList.add('filled');
                }
                var focus = Math.min(val.length, 5);
                digits[focus].focus();
                var collected = collectCode();
                hidden.value = collected;
                if (collected.length === 6) form.submit();
                return;
            }
            input.value = val;
            input.classList.toggle('filled', val !== '');
            if (val && idx < 5) digits[idx + 1].focus();
            var collected = collectCode();
            hidden.value = collected;
            if (collected.length === 6) form.submit();
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !input.value && idx > 0) {
                digits[idx - 1].value = '';
                digits[idx - 1].classList.remove('filled');
                digits[idx - 1].focus();
                hidden.value = collectCode();
            }
        });

        input.addEventListener('paste', function (e) {
            e.preventDefault();
            var paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            // Clear all digits first so shorter pastes don't leave stale characters
            digits.forEach(function (d) { d.value = ''; d.classList.remove('filled'); });
            for (var i = 0; i < 6 && i < paste.length; i++) {
                digits[i].value = paste[i];
                digits[i].classList.add('filled');
            }
            // Focus the next empty box (or last box if all filled)
            var focusIdx = Math.min(paste.length, 5);
            digits[focusIdx].focus();
            var collected = collectCode();
            hidden.value = collected;
            if (collected.length === 6) form.submit();
        });
    });

    /* ---- Backup code toggle ---- */
    toggleBtn.addEventListener('click', function () {
        usingBackup = !usingBackup;
        if (usingBackup) {
            backupSec.classList.add('visible');
            toggleBtn.textContent = 'Use authenticator app instead';
            useBackupField.value = '1';
            digits.forEach(function (d) { d.disabled = true; });
            backupIn.focus();
        } else {
            backupSec.classList.remove('visible');
            toggleBtn.textContent = 'Use a backup code instead';
            useBackupField.value = '0';
            digits.forEach(function (d) { d.disabled = false; });
            digits[0].focus();
        }
    });

    /* ---- Submit handler ---- */
    form.addEventListener('submit', function () {
        if (usingBackup) {
            hidden.value = backupIn.value.trim();
        } else {
            hidden.value = collectCode();
        }
    });

    // Auto-focus first digit
    digits[0].focus();
})();
</script>
<?php View::endSection(); ?>

