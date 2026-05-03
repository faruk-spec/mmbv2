<?php
$entityType = $entityType ?? '';
$context = is_array($context ?? null) ? $context : [];
$actions = \Core\EcosystemIntegration::actionsForEntity($entityType, $context);
if (empty($actions)) {
    return;
}
?>
<?php foreach ($actions as $action): ?>
    <?php if (($action['type'] ?? '') === 'qr_modal'): ?>
        <button type="button"
                class="<?= \Core\View::e($action['class'] ?? 'btn btn-secondary btn-sm') ?>"
                style="<?= \Core\View::e($action['style'] ?? '') ?>"
                title="<?= \Core\View::e($action['title'] ?? '') ?>"
                onclick="ecoQrOpen('<?= \Core\View::e($action['url'] ?? '') ?>')">
            <i class="fas <?= \Core\View::e($action['icon'] ?? 'fa-qrcode') ?>"></i>
        </button>
    <?php else: ?>
        <a href="<?= \Core\View::e($action['url'] ?? '#') ?>"
           class="<?= \Core\View::e($action['class'] ?? 'btn btn-secondary btn-sm') ?>"
           style="<?= \Core\View::e($action['style'] ?? '') ?>"
           title="<?= \Core\View::e($action['title'] ?? '') ?>"
           <?= !empty($action['target']) ? 'target="' . \Core\View::e($action['target']) . '" rel="noopener"' : '' ?>>
            <i class="fas <?= \Core\View::e($action['icon'] ?? 'fa-external-link-alt') ?>"></i>
        </a>
    <?php endif; ?>
<?php endforeach; ?>
