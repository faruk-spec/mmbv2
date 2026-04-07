<?php use Core\View; ?>
<?php View::section('content'); ?>
<div class="page-body">
    <h1 style="margin-bottom:20px;font-size:2rem;color:var(--text-primary);"><?= View::e($page['title']) ?></h1>
    <div class="page-html-content">
        <?= $page['content'] ?>
    </div>
</div>
<style>
.page-html-content { color: var(--text-primary); line-height: 1.8; }
.page-html-content h1,.page-html-content h2,.page-html-content h3 { margin: 20px 0 10px; }
.page-html-content p { margin-bottom: 15px; }
.page-html-content a { color: var(--cyan); }
.page-html-content img { max-width: 100%; height: auto; border-radius: 8px; }
.page-html-content ul,.page-html-content ol { padding-left: 25px; margin-bottom: 15px; }
.page-html-content table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
.page-html-content th,.page-html-content td { padding: 10px; border: 1px solid var(--border-color); }
</style>
<?php View::endSection(); ?>
