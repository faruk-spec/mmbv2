<?php
/**
 * ConvertX Admin — Image Tools API Settings
 */
use Core\View;
View::extend('admin');
?>

<?php View::section('content'); ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-magic text-primary"></i> Image Tools APIs</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
                    <li class="breadcrumb-item"><a href="/admin/projects/convertx">ConvertX</a></li>
                    <li class="breadcrumb-item active">Image Tools APIs</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">

        <?php
        $flashSuccess = $_SESSION['_flash']['success'] ?? null;
        $flashError   = $_SESSION['_flash']['error']   ?? null;
        unset($_SESSION['_flash']['success'], $_SESSION['_flash']['error']);
        ?>
        <?php if ($flashSuccess): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-check-circle"></i> <?= htmlspecialchars($flashSuccess) ?>
        </div>
        <?php endif; ?>
        <?php if ($flashError): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($flashError) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="/admin/projects/convertx/image-tools-settings">
            <input type="hidden" name="_token" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken()) ?>">

            <div class="row">

                <!-- Upscale Image API -->
                <div class="col-lg-6">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-arrow-up text-primary mr-1"></i>
                                Upscale Image API
                            </h3>
                            <div class="card-tools">
                                <span class="badge badge-primary">AI-Powered</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                Configure an AI image upscaling API provider to enable the
                                <strong>Upscale Image</strong> feature. Supported providers include
                                <a href="https://www.iloveapi.com" target="_blank" rel="noopener">iLoveAPI</a>,
                                <a href="https://replicate.com" target="_blank" rel="noopener">Replicate</a>,
                                and others.
                            </p>

                            <div class="form-group">
                                <label for="upscale_api_provider">Provider Name</label>
                                <input type="text" class="form-control" id="upscale_api_provider"
                                       name="upscale_api_provider"
                                       value="<?= htmlspecialchars($settings['upscale_api_provider'] ?? '') ?>"
                                       placeholder="e.g. iloveapi, replicate">
                                <small class="form-text text-muted">Identifier for the API provider (lowercase).</small>
                            </div>

                            <div class="form-group">
                                <label for="upscale_api_key">API Key</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="upscale_api_key"
                                           name="upscale_api_key"
                                           value="<?= htmlspecialchars($settings['upscale_api_key'] ?? '') ?>"
                                           placeholder="Enter your API key">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary btn-toggle-pw"
                                                data-target="upscale_api_key">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    Your secret API key. It will be stored securely.
                                    <?php if (!empty($settings['upscale_api_key'])): ?>
                                    <span class="text-success"><i class="fas fa-check-circle"></i> Key is set.</span>
                                    <?php else: ?>
                                    <span class="text-warning"><i class="fas fa-exclamation-triangle"></i> No key configured.</span>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Remove Background API -->
                <div class="col-lg-6">
                    <div class="card card-outline card-purple">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-magic text-purple mr-1" style="color:#7c3aed;"></i>
                                Remove Background API
                            </h3>
                            <div class="card-tools">
                                <span class="badge" style="background:#7c3aed;color:#fff;">iLoveAPI</span>
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                Uses <a href="https://www.iloveapi.com" target="_blank" rel="noopener">iLoveAPI</a>
                                for background removal. Get your API key from the
                                <a href="https://developer.iloveapi.com" target="_blank" rel="noopener">iLoveAPI developer portal</a>.
                            </p>

                            <div class="form-group">
                                <label for="removebg_api_provider">Provider</label>
                                <input type="text" class="form-control" id="removebg_api_provider"
                                       name="removebg_api_provider"
                                       value="<?= htmlspecialchars($settings['removebg_api_provider'] ?? 'iloveapi') ?>"
                                       readonly>
                                <small class="form-text text-muted">Provider is fixed to <strong>iloveapi</strong>.</small>
                            </div>

                            <div class="form-group">
                                <label for="removebg_api_key">API Key</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="removebg_api_key"
                                           name="removebg_api_key"
                                           value="<?= htmlspecialchars($settings['removebg_api_key'] ?? '') ?>"
                                           placeholder="Enter your iLoveAPI key">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-outline-secondary btn-toggle-pw"
                                                data-target="removebg_api_key">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <small class="form-text text-muted">
                                    Your iLoveAPI secret key.
                                    <?php if (!empty($settings['removebg_api_key'])): ?>
                                    <span class="text-success"><i class="fas fa-check-circle"></i> Key is set.</span>
                                    <?php else: ?>
                                    <span class="text-warning"><i class="fas fa-exclamation-triangle"></i> No key configured.</span>
                                    <?php endif; ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

            </div><!-- /row -->

            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                    <a href="/admin/projects/convertx" class="btn btn-default ml-2">
                        <i class="fas fa-arrow-left"></i> Back to ConvertX
                    </a>
                </div>
            </div>

        </form>

    </div>
</section>

<script>
document.querySelectorAll('.btn-toggle-pw').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var target = document.getElementById(this.dataset.target);
        if (target) {
            target.type = target.type === 'password' ? 'text' : 'password';
            this.querySelector('i').className = target.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
        }
    });
});
</script>

<?php View::endSection(); ?>
