<?php use Core\View; use Core\Helpers; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>
<div class="admin-content">
    <h1><?= $title ?></h1>
    <div class="card">
        <form method="post">
            <?php foreach ($settings as $setting): ?>
            <div class="form-group">
                <label><?= str_replace("_", " ", ucwords(str_replace("websocket_", "", $setting["setting_key"]))) ?></label>
                <input type="text" name="<?= $setting["setting_key"] ?>" value="<?= htmlspecialchars($setting["setting_value"]) ?>" class="form-control">
            </div>
            <?php endforeach; ?>
            <button type="submit" class="btn btn-primary">Save Settings</button>
        </form>
    </div>
</div>
<?php View::endSection(); ?>
