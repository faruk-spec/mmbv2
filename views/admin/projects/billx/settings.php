<?php
/**
 * BillX Admin — Settings
 */
use Core\View;
use Core\Security;
View::extend('admin');
?>

<?php View::section('content'); ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-cog text-warning"></i> BillX — Settings</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
                    <li class="breadcrumb-item"><a href="/admin/projects/billx">BillX</a></li>
                    <li class="breadcrumb-item active">Settings</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <!-- Quick Nav -->
        <div class="row mb-3">
            <div class="col-12">
                <div class="card card-outline card-warning">
                    <div class="card-body py-2">
                        <a href="/admin/projects/billx" class="btn btn-sm btn-outline-secondary mr-1"><i class="fas fa-tachometer-alt"></i> Overview</a>
                        <a href="/admin/projects/billx/bills" class="btn btn-sm btn-outline-secondary mr-1"><i class="fas fa-list"></i> All Bills</a>
                        <a href="/admin/projects/billx/settings" class="btn btn-sm btn-warning"><i class="fas fa-cog"></i> Settings</a>
                    </div>
                </div>
            </div>
        </div>

        <?php if (!empty($saved)): ?>
        <div class="alert alert-success alert-dismissible">
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            <i class="fas fa-check-circle"></i> Settings saved successfully.
        </div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
        <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <div class="row">
            <!-- Database Status -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-database mr-1"></i> Database Connection</h3>
                    </div>
                    <div class="card-body">
                        <?php if ($dbConnected): ?>
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle"></i>
                            <strong>Connected</strong> — BillX database is available.
                        </div>
                        <?php else: ?>
                        <div class="alert alert-warning mb-0">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Not Connected</strong> — BillX database is not configured or unavailable.
                        </div>
                        <a href="/admin/projects/database-setup/billx" class="btn btn-sm btn-warning mt-3">
                            <i class="fas fa-plug"></i> Configure Database
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Project Toggle -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-toggle-on mr-1"></i> Project Status</h3>
                    </div>
                    <div class="card-body">
                        <p class="text-muted">Enable or disable the BillX project platform-wide via the Project Management page.</p>
                        <a href="/admin/projects/billx" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-external-link-alt"></i> Go to Project Management
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bill Types Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-info-circle mr-1"></i> Available Bill Types</h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">BillX supports the following bill/invoice types out of the box:</p>
                <div class="row">
                    <?php
                    $billTypes = [
                        ['type' => 'invoice',      'icon' => 'fas fa-file-invoice',        'desc' => 'Standard invoices for goods and services'],
                        ['type' => 'receipt',      'icon' => 'fas fa-receipt',              'desc' => 'Payment receipts for completed transactions'],
                        ['type' => 'quotation',    'icon' => 'fas fa-file-alt',             'desc' => 'Price quotations and estimates'],
                        ['type' => 'purchase_order','icon'=> 'fas fa-shopping-cart',        'desc' => 'Purchase orders sent to suppliers'],
                        ['type' => 'credit_note',  'icon' => 'fas fa-undo',                 'desc' => 'Credit notes for refunds and adjustments'],
                        ['type' => 'debit_note',   'icon' => 'fas fa-plus-circle',          'desc' => 'Debit notes for additional charges'],
                    ];
                    foreach ($billTypes as $bt):
                    ?>
                    <div class="col-md-4 mb-3">
                        <div class="info-box shadow-none border">
                            <span class="info-box-icon bg-warning"><i class="<?= $bt['icon'] ?>"></i></span>
                            <div class="info-box-content">
                                <span class="info-box-text"><?= ucwords(str_replace('_', ' ', $bt['type'])) ?></span>
                                <span class="info-box-number" style="font-size:12px;font-weight:normal;"><?= $bt['desc'] ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Save (placeholder) -->
        <div class="card">
            <div class="card-body">
                <form method="POST" action="/admin/projects/billx/settings">
                    <?= Security::csrfField() ?>
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                </form>
            </div>
        </div>

    </div>
</section>
<?php View::endSection(); ?>
