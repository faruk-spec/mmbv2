<?php
/**
 * ConvertX Admin — Upload Limits & Allowed Formats
 * Centralises all upload-related limits for image tools, PDF tools, and file conversions.
 */
use Core\View;
View::extend('admin');
?>

<?php View::section('content'); ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-upload text-primary"></i> Upload Limits &amp; Allowed Formats</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
                    <li class="breadcrumb-item"><a href="/admin/projects/convertx">ConvertX</a></li>
                    <li class="breadcrumb-item active">Upload Limits</li>
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

        <form method="POST" action="/admin/projects/convertx/upload-limits">
            <input type="hidden" name="_token" value="<?= htmlspecialchars(\Core\Security::generateCsrfToken()) ?>">

            <!-- ── Image Tool Limits ─────────────────────────────────── -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-image text-primary mr-1"></i>
                                Image Tools Upload Limits
                            </h3>
                            <div class="card-tools">
                                <small class="text-muted">Applies to: Compress, Resize, Crop, Watermark, Meme, Rotate</small>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="max_files">
                                            Max Files Per Upload
                                            <small class="text-muted">(1 – 200)</small>
                                        </label>
                                        <input type="number" class="form-control" id="max_files"
                                               name="max_files" min="1" max="200"
                                               value="<?= htmlspecialchars($settings['max_files'] ?? '20') ?>">
                                        <small class="form-text text-muted">Maximum number of images a user can upload at once across all image tools.</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="max_file_size_mb">
                                            Max Image File Size (MB)
                                            <small class="text-muted">(1 – 2000)</small>
                                        </label>
                                        <input type="number" class="form-control" id="max_file_size_mb"
                                               name="max_file_size_mb" min="1" max="2000"
                                               value="<?= htmlspecialchars($settings['max_file_size_mb'] ?? '50') ?>">
                                        <small class="form-text text-muted">Maximum size per uploaded image file in megabytes.</small>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="allowed_image_formats">Allowed Image Formats</label>
                                        <input type="text" class="form-control" id="allowed_image_formats"
                                               name="allowed_image_formats"
                                               value="<?= htmlspecialchars($settings['allowed_image_formats'] ?? 'jpg,jpeg,png,gif,webp,bmp') ?>"
                                               placeholder="jpg,jpeg,png,gif,webp,bmp">
                                        <small class="form-text text-muted">Comma-separated list of allowed extensions (lowercase). Example: <code>jpg,jpeg,png,gif,webp</code></small>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── PDF Tool Limits ───────────────────────────────────── -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-outline card-danger">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file-pdf text-danger mr-1"></i>
                                PDF Tools Upload Limits
                            </h3>
                            <div class="card-tools">
                                <small class="text-muted">Applies to: PDF Merge, PDF Split, PDF Compress</small>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="max_pdf_files">
                                            Max PDF Files Per Upload
                                            <small class="text-muted">(1 – 100)</small>
                                        </label>
                                        <input type="number" class="form-control" id="max_pdf_files"
                                               name="max_pdf_files" min="1" max="100"
                                               value="<?= htmlspecialchars($settings['max_pdf_files'] ?? '20') ?>">
                                        <small class="form-text text-muted">Maximum number of PDF files per PDF Merge / Split operation.</small>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="max_pdf_size_mb">
                                            Max PDF File Size (MB)
                                            <small class="text-muted">(1 – 2000)</small>
                                        </label>
                                        <input type="number" class="form-control" id="max_pdf_size_mb"
                                               name="max_pdf_size_mb" min="1" max="2000"
                                               value="<?= htmlspecialchars($settings['max_pdf_size_mb'] ?? '200') ?>">
                                        <small class="form-text text-muted">Maximum size per uploaded PDF file in megabytes.</small>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── File Conversion Limits ────────────────────────────── -->
            <div class="row">
                <div class="col-12">
                    <div class="card card-outline card-warning">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file-export text-warning mr-1"></i>
                                File Conversion Upload Limits
                            </h3>
                            <div class="card-tools">
                                <small class="text-muted">Applies to: Convert (document/image format conversions)</small>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="max_conversion_file_size_mb">
                                            Max Conversion File Size (MB)
                                            <small class="text-muted">(1 – 2000)</small>
                                        </label>
                                        <input type="number" class="form-control" id="max_conversion_file_size_mb"
                                               name="max_conversion_file_size_mb" min="1" max="2000"
                                               value="<?= htmlspecialchars($settings['max_conversion_file_size_mb'] ?? '200') ?>">
                                        <small class="form-text text-muted">Maximum size per file submitted for format conversion in megabytes.</small>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Upload Limits
                    </button>
                    <a href="/admin/projects/convertx" class="btn btn-default ml-2">
                        <i class="fas fa-arrow-left"></i> Back to ConvertX
                    </a>
                </div>
            </div>

        </form>

    </div>
</section>

<?php View::endSection(); ?>
