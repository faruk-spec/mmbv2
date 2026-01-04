<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<!-- Edit Subscription Plan -->
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h2>
                    <i class="fas fa-edit mr-2"></i>
                    Edit Plan: <?= View::e($plan['plan_name']) ?>
                </h2>
                <div>
                    <a href="/admin/projects/mail/plans" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Plans
                    </a>
                </div>
            </div>
        </div>
    </div>

    <form method="POST" action="/admin/projects/mail/plans/<?= $plan['id'] ?>/edit">
        <div class="row">
            <!-- Basic Information -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-info-circle mr-2"></i>
                            Basic Information
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="plan_name">Plan Name *</label>
                            <input type="text" class="form-control" id="plan_name" name="plan_name" 
                                   value="<?= View::e($plan['plan_name']) ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"><?= View::e($plan['description']) ?></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price_monthly">Monthly Price ($) *</label>
                                    <input type="number" step="0.01" class="form-control" id="price_monthly" 
                                           name="price_monthly" value="<?= $plan['price_monthly'] ?>" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="price_yearly">Yearly Price ($) *</label>
                                    <input type="number" step="0.01" class="form-control" id="price_yearly" 
                                           name="price_yearly" value="<?= $plan['price_yearly'] ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_active" 
                                       name="is_active" value="1" <?= $plan['is_active'] ? 'checked' : '' ?>>
                                <label class="custom-control-label" for="is_active">
                                    Plan is Active
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Plan Limits -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-sliders-h mr-2"></i>
                            Plan Limits
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="max_users">
                                <i class="fas fa-users"></i> Maximum Users *
                            </label>
                            <input type="number" class="form-control" id="max_users" 
                                   name="max_users" value="<?= $plan['max_users'] ?>" required min="1">
                        </div>

                        <div class="form-group">
                            <label for="storage_per_user_gb">
                                <i class="fas fa-hdd"></i> Storage per User (GB) *
                            </label>
                            <input type="number" class="form-control" id="storage_per_user_gb" 
                                   name="storage_per_user_gb" value="<?= $plan['storage_per_user_gb'] ?>" 
                                   required min="1">
                        </div>

                        <div class="form-group">
                            <label for="daily_send_limit">
                                <i class="fas fa-paper-plane"></i> Daily Send Limit *
                            </label>
                            <input type="number" class="form-control" id="daily_send_limit" 
                                   name="daily_send_limit" value="<?= $plan['daily_send_limit'] ?>" 
                                   required min="1">
                        </div>

                        <div class="form-group">
                            <label for="max_attachment_size_mb">
                                <i class="fas fa-paperclip"></i> Max Attachment Size (MB) *
                            </label>
                            <input type="number" class="form-control" id="max_attachment_size_mb" 
                                   name="max_attachment_size_mb" value="<?= $plan['max_attachment_size_mb'] ?>" 
                                   required min="1">
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_domains">
                                        <i class="fas fa-globe"></i> Max Domains *
                                    </label>
                                    <input type="number" class="form-control" id="max_domains" 
                                           name="max_domains" value="<?= $plan['max_domains'] ?>" 
                                           required min="1">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="max_aliases">
                                        <i class="fas fa-at"></i> Max Aliases *
                                    </label>
                                    <input type="number" class="form-control" id="max_aliases" 
                                           name="max_aliases" value="<?= $plan['max_aliases'] ?>" 
                                           required min="1">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Features -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-check-square mr-2"></i>
                            Plan Features
                        </h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($features as $feature): ?>
                            <div class="col-md-4 col-lg-3 mb-3">
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" class="custom-control-input" 
                                           id="feature_<?= $feature['feature_key'] ?>" 
                                           name="features[<?= $feature['feature_key'] ?>]" 
                                           value="1" <?= $feature['is_enabled'] ? 'checked' : '' ?>>
                                    <label class="custom-control-label" for="feature_<?= $feature['feature_key'] ?>">
                                        <strong><?= View::e($feature['feature_name']) ?></strong>
                                    </label>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="row mt-3 mb-4">
            <div class="col-12">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Save Changes
                </button>
                <a href="/admin/projects/mail/plans" class="btn btn-secondary btn-lg">
                    <i class="fas fa-times"></i> Cancel
                </a>
            </div>
        </div>
    </form>
</div>

<?php View::endSection(); ?>
