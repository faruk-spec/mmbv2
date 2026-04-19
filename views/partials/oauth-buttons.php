<?php
use Core\OAuthProvider;
use Core\View;

$oauthMode = $oauthMode ?? 'login';
$enabledProviders = OAuthProvider::getEnabledProviders();
$supportedNames = ['google', 'github', 'apple', 'microsoft'];
$providerTooltip = [
    'google'    => 'Google',
    'github'    => 'GitHub',
    'apple'     => 'Apple',
    'microsoft' => 'Microsoft',
];
$filteredProviders = array_filter($enabledProviders, fn($p) => in_array(strtolower($p['name']), $supportedNames, true));
?>

<?php if (!empty($filteredProviders)): ?>
    <div style="margin-bottom: 16px;">
        <p style="text-align: center; color: var(--text-secondary); font-size: 0.85rem; margin: 0 0 10px;">
            Continue with
        </p>
        <div style="display: flex; justify-content: center; align-items: center; gap: 12px; flex-wrap: wrap;">
            <?php foreach ($filteredProviders as $provider): ?>
                <?php $providerName = strtolower($provider['name']); ?>
                <a href="/auth/<?= View::e($providerName) ?><?= $oauthMode === 'register' ? '?register=1' : '' ?>"
                   title="<?= View::e($providerTooltip[$providerName] ?? ucfirst($providerName)) ?>"
                   style="display: inline-flex; align-items: center; justify-content: center; width: 46px; height: 46px; background: #fff; border: 1px solid #e2e8f0; border-radius: 10px; transition: box-shadow 0.15s, border-color 0.15s; text-decoration: none;"
                   onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,0.12)';this.style.borderColor='#cbd5e1';"
                   onmouseout="this.style.boxShadow='none';this.style.borderColor='#e2e8f0';">
                    <?php if ($providerName === 'google'): ?>
                        <svg width="20" height="20" viewBox="0 0 18 18" xmlns="http://www.w3.org/2000/svg">
                            <g fill="none" fill-rule="evenodd">
                                <path d="M17.64 9.205c0-.639-.057-1.252-.164-1.841H9v3.481h4.844a4.14 4.14 0 0 1-1.796 2.716v2.259h2.908c1.702-1.567 2.684-3.875 2.684-6.615z" fill="#4285F4"/>
                                <path d="M9 18c2.43 0 4.467-.806 5.956-2.18l-2.908-2.259c-.806.54-1.837.86-3.048.86-2.344 0-4.328-1.584-5.036-3.711H.957v2.332A8.997 8.997 0 0 0 9 18z" fill="#34A853"/>
                                <path d="M3.964 10.71A5.41 5.41 0 0 1 3.682 9c0-.593.102-1.17.282-1.71V4.958H.957A8.996 8.996 0 0 0 0 9c0 1.452.348 2.827.957 4.042l3.007-2.332z" fill="#FBBC05"/>
                                <path d="M9 3.58c1.321 0 2.508.454 3.44 1.345l2.582-2.58C13.463.891 11.426 0 9 0A8.997 8.997 0 0 0 .957 4.958L3.964 7.29C4.672 5.163 6.656 3.58 9 3.58z" fill="#EA4335"/>
                            </g>
                        </svg>
                    <?php elseif ($providerName === 'github'): ?>
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="#111827" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 .5C5.65.5.5 5.78.5 12.3c0 5.22 3.3 9.64 7.88 11.2.58.11.8-.26.8-.58 0-.29-.01-1.05-.02-2.07-3.2.71-3.88-1.58-3.88-1.58-.52-1.37-1.28-1.74-1.28-1.74-1.05-.74.08-.73.08-.73 1.16.08 1.77 1.23 1.77 1.23 1.03 1.82 2.7 1.29 3.36.98.1-.77.4-1.29.73-1.59-2.55-.3-5.23-1.32-5.23-5.87 0-1.29.45-2.35 1.18-3.18-.12-.3-.52-1.5.11-3.12 0 0 .97-.32 3.17 1.22a10.78 10.78 0 0 1 5.78 0c2.2-1.54 3.17-1.22 3.17-1.22.63 1.62.23 2.82.11 3.12.73.83 1.18 1.89 1.18 3.18 0 4.56-2.68 5.57-5.24 5.86.41.36.78 1.07.78 2.17 0 1.56-.01 2.82-.01 3.2 0 .32.21.7.8.58 4.58-1.56 7.87-5.99 7.87-11.2C23.5 5.78 18.35.5 12 .5z"/>
                        </svg>
                    <?php elseif ($providerName === 'apple'): ?>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="#000000" xmlns="http://www.w3.org/2000/svg">
                            <path d="M16.37 1.43c0 1.14-.47 2.24-1.23 3.01-.82.83-2.14 1.46-3.27 1.37-.14-1.09.4-2.22 1.18-3.01.79-.8 2.17-1.43 3.32-1.37zM20.99 17.02c-.38.88-.83 1.69-1.36 2.45-.72 1.03-1.31 1.74-1.78 2.12-.73.62-1.52.94-2.37.96-.61 0-1.34-.18-2.2-.54-.86-.36-1.66-.54-2.4-.54-.77 0-1.59.18-2.47.54-.88.36-1.59.55-2.14.57-.82.04-1.63-.3-2.44-.99-.51-.44-1.13-1.18-1.85-2.23-.77-1.11-1.41-2.4-1.91-3.89-.54-1.61-.81-3.17-.81-4.67 0-1.72.36-3.2 1.08-4.43.57-1 1.33-1.8 2.28-2.39.95-.59 1.98-.89 3.08-.91.61 0 1.41.19 2.43.58 1.01.39 1.66.58 1.93.58.2 0 .87-.22 2.01-.66 1.08-.4 1.99-.57 2.74-.51 2.03.17 3.56.98 4.58 2.43-1.82 1.12-2.72 2.69-2.7 4.71.02 1.58.57 2.9 1.67 3.96.49.49 1.04.86 1.65 1.11-.13.38-.27.76-.42 1.12z"/>
                        </svg>
                    <?php elseif ($providerName === 'microsoft'): ?>
                        <svg width="20" height="20" viewBox="0 0 23 23" xmlns="http://www.w3.org/2000/svg">
                            <rect x="1" y="1" width="10" height="10" fill="#F25022"/>
                            <rect x="12" y="1" width="10" height="10" fill="#7FBA00"/>
                            <rect x="1" y="12" width="10" height="10" fill="#00A4EF"/>
                            <rect x="12" y="12" width="10" height="10" fill="#FFB900"/>
                        </svg>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="auth-divider">or</div>
<?php endif; ?>
