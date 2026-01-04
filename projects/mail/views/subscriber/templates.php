<?php
// Email Templates View
$pageTitle = 'Email Templates';
include 'layout.php';
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-file-alt"></i> Email Templates</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/projects/mail/subscriber/dashboard">Home</a></li>
                        <li class="breadcrumb-item active">Templates</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-6">
                            <form method="GET" class="form-inline">
                                <div class="input-group">
                                    <input type="text" name="search" class="form-control" placeholder="Search templates..." value="<?= htmlspecialchars($search ?? '') ?>">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-default">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6 text-right">
                            <a href="/projects/mail/templates/add" class="btn btn-primary">
                                <i class="fas fa-plus"></i> New Template
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <?php if (empty($templates)): ?>
                        <div class="text-center p-5">
                            <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                            <h4>No Templates Found</h4>
                            <p class="text-muted">Create reusable email templates to save time when composing emails.</p>
                            <a href="/projects/mail/templates/add" class="btn btn-primary mt-2">
                                <i class="fas fa-plus"></i> Create First Template
                            </a>
                        </div>
                    <?php else: ?>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Template Name</th>
                                    <th>Subject</th>
                                    <th>Type</th>
                                    <th>Created</th>
                                    <th width="200">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($templates as $template): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($template['name']) ?></strong>
                                        </td>
                                        <td><?= htmlspecialchars($template['subject']) ?></td>
                                        <td>
                                            <?php if ($template['is_html']): ?>
                                                <span class="badge badge-info">HTML</span>
                                            <?php else: ?>
                                                <span class="badge badge-secondary">Plain Text</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?= date('M d, Y', strtotime($template['created_at'])) ?></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-success" 
                                                    onclick="useTemplate(<?= $template['id'] ?>)" title="Use Template">
                                                <i class="fas fa-paper-plane"></i> Use
                                            </button>
                                            <a href="/projects/mail/templates/edit/<?= $template['id'] ?>" 
                                               class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-info" 
                                                    onclick="duplicateTemplate(<?= $template['id'] ?>)" title="Duplicate">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="deleteTemplate(<?= $template['id'] ?>)" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <!-- Pagination -->
                        <?php if ($total > $perPage): ?>
                            <div class="card-footer clearfix">
                                <ul class="pagination pagination-sm m-0 float-right">
                                    <?php
                                    $totalPages = ceil($total / $perPage);
                                    $currentPage = $page ?? 1;
                                    
                                    if ($currentPage > 1):
                                    ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $currentPage - 1 ?>&search=<?= urlencode($search ?? '') ?>">
                                                &laquo;
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?>&search=<?= urlencode($search ?? '') ?>">
                                                <?= $i ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>

                                    <?php if ($currentPage < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?page=<?= $currentPage + 1 ?>&search=<?= urlencode($search ?? '') ?>">
                                                &raquo;
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
function useTemplate(id) {
    // Fetch template and redirect to compose with template data
    fetch('/projects/mail/templates/get/' + id)
        .then(response => response.json())
        .then(data => {
            const params = new URLSearchParams({
                template_subject: data.subject,
                template_body: data.body,
                template_html: data.is_html
            });
            window.location.href = '/projects/mail/webmail/compose?' + params.toString();
        })
        .catch(error => {
            alert('Failed to load template');
        });
}

function duplicateTemplate(id) {
    if (confirm('Create a copy of this template?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/projects/mail/templates/duplicate/' + id;
        form.innerHTML = '<?= csrf_field() ?>';
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteTemplate(id) {
    if (confirm('Are you sure you want to delete this template?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/projects/mail/templates/delete/' + id;
        form.innerHTML = '<?= csrf_field() ?>';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include 'footer.php'; ?>
