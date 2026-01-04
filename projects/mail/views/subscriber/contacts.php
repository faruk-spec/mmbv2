<?php
// Contact Management View
$pageTitle = 'Contacts';
include 'layout.php';
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1><i class="fas fa-address-book"></i> Contacts</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/projects/mail/subscriber/dashboard">Home</a></li>
                        <li class="breadcrumb-item active">Contacts</li>
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
                                    <input type="text" name="search" class="form-control" placeholder="Search contacts..." value="<?= htmlspecialchars($search ?? '') ?>">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-default">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <div class="col-md-6 text-right">
                            <button type="button" class="btn btn-info" data-toggle="modal" data-target="#importModal">
                                <i class="fas fa-file-import"></i> Import CSV
                            </button>
                            <a href="/projects/mail/contacts/export" class="btn btn-secondary">
                                <i class="fas fa-file-export"></i> Export CSV
                            </a>
                            <a href="/projects/mail/contacts/add" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Contact
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <?php if (empty($contacts)): ?>
                        <div class="text-center p-5">
                            <i class="fas fa-address-book fa-3x text-muted mb-3"></i>
                            <h4>No Contacts Found</h4>
                            <p class="text-muted">Start building your contact list by adding contacts manually or importing from CSV.</p>
                            <a href="/projects/mail/contacts/add" class="btn btn-primary mt-2">
                                <i class="fas fa-plus"></i> Add First Contact
                            </a>
                        </div>
                    <?php else: ?>
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Company</th>
                                    <th>Phone</th>
                                    <th width="150">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($contacts as $contact): ?>
                                    <tr>
                                        <td>
                                            <strong><?= htmlspecialchars($contact['name']) ?></strong>
                                        </td>
                                        <td>
                                            <a href="mailto:<?= htmlspecialchars($contact['email']) ?>">
                                                <?= htmlspecialchars($contact['email']) ?>
                                            </a>
                                        </td>
                                        <td><?= htmlspecialchars($contact['company'] ?: '-') ?></td>
                                        <td><?= htmlspecialchars($contact['phone'] ?: '-') ?></td>
                                        <td>
                                            <a href="/projects/mail/webmail/compose?to=<?= urlencode($contact['email']) ?>" 
                                               class="btn btn-sm btn-success" title="Compose Email">
                                                <i class="fas fa-envelope"></i>
                                            </a>
                                            <a href="/projects/mail/contacts/edit/<?= $contact['id'] ?>" 
                                               class="btn btn-sm btn-primary" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="deleteContact(<?= $contact['id'] ?>)" title="Delete">
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

<!-- Import CSV Modal -->
<div class="modal fade" id="importModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="POST" action="/projects/mail/contacts/import" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title">Import Contacts from CSV</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>CSV File</label>
                        <input type="file" name="csv_file" class="form-control" accept=".csv" required>
                        <small class="form-text text-muted">
                            CSV format: Name, Email, Company, Phone, Notes<br>
                            First row should be headers.
                        </small>
                    </div>
                    <div class="alert alert-info">
                        <strong>Note:</strong> Duplicate emails will be skipped automatically.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-file-import"></i> Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function deleteContact(id) {
    if (confirm('Are you sure you want to delete this contact?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/projects/mail/contacts/delete/' + id;
        form.innerHTML = '<?= csrf_field() ?>';
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<?php include 'footer.php'; ?>
