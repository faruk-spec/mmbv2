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
<style>
.dashboard-main-content { padding: 0 !important; }
#support-wizard-root { min-height: 400px; }
</style>
<?php View::endSection(); ?>

<?php View::section('content'); ?>
<div style="display:flex;min-height:calc(100vh - 64px);align-items:stretch;">

    <!-- Sidebar -->
    <?php include __DIR__ . '/_sidebar.php'; ?>

    <!-- Main: React wizard mount -->
    <div style="flex:1;padding:24px 28px;min-width:0;">

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
</div>
<?php View::endSection(); ?>
