<?php
/**
 * Admin: Dynamic Form Builder
 * Hosts the React drag-and-drop form builder app.
 */
use Core\View;
use Core\Security;

View::extend('admin');
View::section('content');

$catId      = (int) ($category['id']   ?? 0);
$catName    = $category['name']         ?? '';
$groupName  = $category['group_name']   ?? '';
$groupId    = (int) ($category['group_id'] ?? 0);
$schemaJson = 'null';
if (!empty($template['schema_json'])) {
    $schemaJson = $template['schema_json'];
}
$version    = (int) ($template['version'] ?? 0);
$csrfToken  = Security::generateCsrfToken();
?>

<style>
#support-builder-root { min-height: 600px; }
</style>

<!-- Pass PHP context into the React app via a global config object -->
<script>
window.__SUPPORT_BUILDER_CONFIG__ = {
    categoryId:   <?= $catId ?>,
    categoryName: <?= json_encode($catName) ?>,
    groupId:      <?= $groupId ?>,
    groupName:    <?= json_encode($groupName) ?>,
    currentVersion: <?= $version ?>,
    existingSchema: <?= $schemaJson ?>,
    csrfToken: <?= json_encode($csrfToken) ?>,
    apiBase: '/api/admin/support',
    builderBackUrl: <?= json_encode('/admin/support/groups/' . $groupId . '/categories') ?>,
    history: <?= json_encode(array_map(function($h) {
        return [
            'id'              => (int) $h['id'],
            'version'         => (int) $h['version'],
            'is_active'       => (bool) $h['is_active'],
            'created_at'      => $h['created_at'],
            'created_by_name' => $h['created_by_name'] ?? null,
        ];
    }, $history ?? [])) ?>
};
</script>

<!-- React app mount point -->
<div id="support-builder-root"></div>

<!-- React bundle (built from resources/support/) -->
<script type="module" src="/assets/js/support/builder.js"></script>

<?php View::endSection(); ?>
