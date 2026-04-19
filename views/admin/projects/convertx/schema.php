<?php
/**
 * ConvertX Admin — SQL Schema
 */
use Core\View;
View::extend('admin');
?>

<?php View::section('content'); ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0"><i class="fas fa-table text-primary"></i> ConvertX — SQL Schema</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="/admin">Admin</a></li>
                    <li class="breadcrumb-item"><a href="/admin/projects/convertx">ConvertX</a></li>
                    <li class="breadcrumb-item active">SQL Schema</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h3 class="card-title mb-0"><i class="fas fa-code"></i> schema.sql — ConvertX Tables</h3>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copySchema()">
                    <i class="fas fa-copy"></i> Copy
                </button>
            </div>
            <div class="card-body p-0">
                <pre id="schemaContent" style="margin:0;padding:1.25rem;background:#1e1e2e;color:#cdd6f4;font-size:.8rem;line-height:1.65;overflow-x:auto;border-radius:0 0 .375rem .375rem;max-height:75vh;overflow-y:auto;"><?= htmlspecialchars($schema) ?></pre>
            </div>
        </div>

    </div>
</section>

<script>
function copySchema() {
    var text = document.getElementById('schemaContent').textContent;
    navigator.clipboard.writeText(text).then(function () {
        var btn = event.currentTarget;
        btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
        setTimeout(function () { btn.innerHTML = '<i class="fas fa-copy"></i> Copy'; }, 2000);
    });
}
</script>
<?php View::endSection(); ?>
