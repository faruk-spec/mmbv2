<?php
/**
 * ImgTxt Admin - Language Statistics
 * View language usage statistics
 */
use Core\View;

View::extend('admin');
?>

<?php View::section('content'); ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">
                    <i class="fas fa-language text-primary"></i>
                    <?= $title ?? 'Language Statistics' ?>
                </h1>
                <p class="text-muted mb-0">View OCR language usage statistics</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin"><i class="fas fa-home"></i> Admin</a></li>
                    <li class="breadcrumb-item"><a href="/admin/projects/imgtxt">ImgTxt</a></li>
                    <li class="breadcrumb-item active">Languages</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <?php foreach ($languages as $lang): ?>
                <div class="col-lg-3 col-md-4 col-sm-6">
                    <div class="info-box bg-gradient-info">
                        <span class="info-box-icon">
                            <i class="fas fa-language"></i>
                        </span>
                        <div class="info-box-content">
                            <span class="info-box-text">
                                <strong><?= htmlspecialchars($lang['name']) ?></strong>
                            </span>
                            <span class="info-box-number">
                                <?= number_format($lang['usage_count']) ?>
                            </span>
                            <div class="progress">
                                <div class="progress-bar" style="width: <?= $lang['percentage'] ?? 0 ?>%"></div>
                            </div>
                            <span class="progress-description">
                                <?= htmlspecialchars($lang['code']) ?>
                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php View::endSection(); ?>
