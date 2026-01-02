<?php
/**
 * ImgTxt Admin - Settings
 * Configure OCR engine and processing settings
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
                    <i class="fas fa-cog text-primary"></i>
                    <?= $title ?? 'ImgTxt Settings' ?>
                </h1>
                <p class="text-muted mb-0">Configure OCR engine and processing settings</p>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin"><i class="fas fa-home"></i> Admin</a></li>
                    <li class="breadcrumb-item"><a href="/admin/projects/imgtxt">ImgTxt</a></li>
                    <li class="breadcrumb-item active">Settings</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php if (isset($_SESSION['flash_message'])): ?>
            <?php 
                // Validate flash_type to prevent XSS
                $allowedTypes = ['success', 'info', 'warning', 'danger'];
                $flashType = in_array($_SESSION['flash_type'] ?? '', $allowedTypes) ? $_SESSION['flash_type'] : 'info';
            ?>
            <div class="alert alert-<?= $flashType ?> alert-dismissible">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <i class="icon fas fa-<?= $flashType == 'success' ? 'check' : 'info' ?>-circle"></i>
                <?= htmlspecialchars($_SESSION['flash_message']) ?>
            </div>
            <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
        <?php endif; ?>

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cog mr-2"></i>
                    <strong>Configuration Settings</strong>
                </h3>
            </div>
            <form method="POST" action="/admin/projects/imgtxt/settings">
                <div class="card-body">
                    <input type="hidden" name="_csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                    <!-- File Upload Settings -->
                    <div class="mb-4">
                        <h5 class="text-primary">
                            <i class="fas fa-upload mr-2"></i>
                            File Upload Settings
                        </h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_file_size">Maximum File Size (MB)</label>
                                    <input type="number" id="max_file_size" name="max_file_size" 
                                           value="<?= $settings['max_file_size'] ?? 10 ?>" 
                                           min="1" max="100" class="form-control">
                                    <small class="form-text text-muted">Maximum size for uploaded images</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="batch_size">Batch Processing Size</label>
                                    <input type="number" id="batch_size" name="batch_size" 
                                           value="<?= $settings['batch_size'] ?? 5 ?>" 
                                           min="1" max="20" class="form-control">
                                    <small class="form-text text-muted">Maximum number of files to process at once</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- OCR Engine Settings -->
                    <div class="mb-4">
                        <h5 class="text-primary">
                            <i class="fas fa-cogs mr-2"></i>
                            OCR Engine Configuration
                        </h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="ocr_engine">OCR Engine</label>
                                    <select id="ocr_engine" name="ocr_engine" class="form-control">
                                        <option value="tesseract" <?= ($settings['ocr_engine'] ?? 'tesseract') == 'tesseract' ? 'selected' : '' ?>>Tesseract</option>
                                        <option value="tesseract_legacy" <?= ($settings['ocr_engine'] ?? '') == 'tesseract_legacy' ? 'selected' : '' ?>>Tesseract Legacy</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="default_language">Default Language</label>
                                    <select id="default_language" name="default_language" class="form-control">
                                        <option value="eng" <?= ($settings['default_language'] ?? 'eng') == 'eng' ? 'selected' : '' ?>>English</option>
                                        <option value="spa" <?= ($settings['default_language'] ?? '') == 'spa' ? 'selected' : '' ?>>Spanish</option>
                                        <option value="fra" <?= ($settings['default_language'] ?? '') == 'fra' ? 'selected' : '' ?>>French</option>
                                        <option value="deu" <?= ($settings['default_language'] ?? '') == 'deu' ? 'selected' : '' ?>>German</option>
                                        <option value="ita" <?= ($settings['default_language'] ?? '') == 'ita' ? 'selected' : '' ?>>Italian</option>
                                        <option value="por" <?= ($settings['default_language'] ?? '') == 'por' ? 'selected' : '' ?>>Portuguese</option>
                                        <option value="rus" <?= ($settings['default_language'] ?? '') == 'rus' ? 'selected' : '' ?>>Russian</option>
                                        <option value="chi_sim" <?= ($settings['default_language'] ?? '') == 'chi_sim' ? 'selected' : '' ?>>Chinese Simplified</option>
                                        <option value="jpn" <?= ($settings['default_language'] ?? '') == 'jpn' ? 'selected' : '' ?>>Japanese</option>
                                        <option value="kor" <?= ($settings['default_language'] ?? '') == 'kor' ? 'selected' : '' ?>>Korean</option>
                                        <option value="ara" <?= ($settings['default_language'] ?? '') == 'ara' ? 'selected' : '' ?>>Arabic</option>
                                        <option value="hin" <?= ($settings['default_language'] ?? '') == 'hin' ? 'selected' : '' ?>>Hindi</option>
                                        <option value="tur" <?= ($settings['default_language'] ?? '') == 'tur' ? 'selected' : '' ?>>Turkish</option>
                                        <option value="vie" <?= ($settings['default_language'] ?? '') == 'vie' ? 'selected' : '' ?>>Vietnamese</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Feature Toggles -->
                    <div class="mb-4">
                        <h5 class="text-primary">
                            <i class="fas fa-toggle-on mr-2"></i>
                            Features
                        </h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="batch_processing_enabled" name="batch_processing_enabled" 
                                               <?= ($settings['batch_processing_enabled'] ?? true) ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="batch_processing_enabled">
                                            <strong>Batch Processing</strong>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Allow users to process multiple files at once</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="multi_language_enabled" name="multi_language_enabled" 
                                               <?= ($settings['multi_language_enabled'] ?? true) ? 'checked' : '' ?>>
                                        <label class="custom-control-label" for="multi_language_enabled">
                                            <strong>Multi-Language Support</strong>
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Enable OCR in multiple languages</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save mr-2"></i>
                        Save Settings
                    </button>
                    <a href="/admin/projects/imgtxt" class="btn btn-secondary">
                        <i class="fas fa-times mr-2"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</section>

<?php View::endSection(); ?>
