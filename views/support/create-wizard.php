<?php
/**
 * Create Support Ticket — Dynamic Wizard
 * Hosts the React ticket-creation wizard app.
 */
use Core\View;
use Core\Auth;
use Core\Security;

View::extend('main');

$user      = Auth::user();
$csrfToken = Security::generateCsrfToken();
?>

<?php View::section('styles'); ?>
<?php include __DIR__ . '/_styles.php'; ?>
<style>#support-wizard-root { min-height: 400px; }</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div class="sp-layout">

    <!-- Sidebar -->
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <!-- Main: React wizard mount -->
    <div class="sp-main">

        <!-- Mobile menu button -->
        <button class="sp-menu-btn" onclick="spOpenMenu()">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            Menu
        </button>

        <script>
        window.__SUPPORT_WIZARD_CONFIG__ = {
            apiBase:   '/api/support',
            submitUrl: '/api/support/tickets',
            csrfToken: <?= json_encode($csrfToken) ?>,
            successRedirect: '/support',
            userName: <?= json_encode($user['name'] ?? '') ?>
        };
        </script>

        <div id="support-wizard-root"></div>

        <!-- React bundle (built from resources/support/) -->
        <script type="module" src="/assets/js/support/wizard.js"></script>

    </div>
</div><!-- /sp-layout -->
<?php View::endSection(); ?>
