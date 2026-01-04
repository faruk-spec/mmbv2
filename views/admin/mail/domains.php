<?php use Core\View; ?>
<?php View::extend('admin'); ?>

<?php View::section('content'); ?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>All Domains</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/admin">Home</a></li>
                        <li class="breadcrumb-item"><a href="/admin/projects/mail">Mail Server</a></li>
                        <li class="breadcrumb-item active">All Domains</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Domain List Across All Subscribers</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input type="text" name="search" id="domain-search" class="form-control float-right" placeholder="Search domains">
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-default">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-body p-0">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Domain</th>
                                <th>Subscriber</th>
                                <th>Status</th>
                                <th>Verification</th>
                                <th>Mailboxes</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="domains-table-body">
                            <tr>
                                <td>example.com</td>
                                <td>
                                    <a href="/admin/projects/mail/subscribers/1">Acme Corp</a>
                                </td>
                                <td>
                                    <span class="badge badge-success">Active</span>
                                </td>
                                <td>
                                    <span class="badge badge-success">
                                        <i class="fas fa-check"></i> Verified
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-info">5 mailboxes</span>
                                </td>
                                <td>Jan 1, 2026</td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewDomain(1)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="verifyDNS(1)">
                                        <i class="fas fa-sync"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>company.net</td>
                                <td>
                                    <a href="/admin/projects/mail/subscribers/2">Tech Solutions</a>
                                </td>
                                <td>
                                    <span class="badge badge-success">Active</span>
                                </td>
                                <td>
                                    <span class="badge badge-warning">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-info">3 mailboxes</span>
                                </td>
                                <td>Jan 2, 2026</td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewDomain(2)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="verifyDNS(2)">
                                        <i class="fas fa-sync"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>startup.io</td>
                                <td>
                                    <a href="/admin/projects/mail/subscribers/3">Startup Inc</a>
                                </td>
                                <td>
                                    <span class="badge badge-danger">Suspended</span>
                                </td>
                                <td>
                                    <span class="badge badge-danger">
                                        <i class="fas fa-times"></i> Failed
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-info">1 mailbox</span>
                                </td>
                                <td>Jan 3, 2026</td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewDomain(3)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                    <button class="btn btn-sm btn-warning" onclick="verifyDNS(3)">
                                        <i class="fas fa-sync"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <div class="card-footer">
                    <nav>
                        <ul class="pagination pagination-sm m-0 float-right">
                            <li class="page-item"><a class="page-link" href="#">«</a></li>
                            <li class="page-item"><a class="page-link" href="#">1</a></li>
                            <li class="page-item"><a class="page-link" href="#">2</a></li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item"><a class="page-link" href="#">»</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
            
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>150</h3>
                            <p>Total Domains</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-globe"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>127</h3>
                            <p>Verified Domains</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>18</h3>
                            <p>Pending Verification</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3>5</h3>
                            <p>Verification Failed</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-times"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Domain Details Modal -->
<div class="modal fade" id="domainModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Domain Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="domain-details">
                <p>Loading...</p>
            </div>
        </div>
    </div>
</div>

<script>
function viewDomain(domainId) {
    $('#domainModal').modal('show');
    $('#domain-details').html('<p>Loading domain details...</p>');
    
    // Fetch domain details
    fetch('/admin/projects/mail/api/domains/' + domainId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const domain = data.data;
                $('#domain-details').html(`
                    <dl class="row">
                        <dt class="col-sm-4">Domain Name:</dt>
                        <dd class="col-sm-8">${domain.name}</dd>
                        
                        <dt class="col-sm-4">Subscriber:</dt>
                        <dd class="col-sm-8">${domain.subscriber_name}</dd>
                        
                        <dt class="col-sm-4">Status:</dt>
                        <dd class="col-sm-8">
                            <span class="badge badge-${domain.status === 'active' ? 'success' : 'danger'}">
                                ${domain.status}
                            </span>
                        </dd>
                        
                        <dt class="col-sm-4">Verification Status:</dt>
                        <dd class="col-sm-8">
                            <span class="badge badge-${domain.is_verified ? 'success' : 'warning'}">
                                ${domain.is_verified ? 'Verified' : 'Pending'}
                            </span>
                        </dd>
                        
                        <dt class="col-sm-4">Mailboxes:</dt>
                        <dd class="col-sm-8">${domain.mailbox_count || 0}</dd>
                        
                        <dt class="col-sm-4">Catch-all:</dt>
                        <dd class="col-sm-8">${domain.catch_all_email || 'Not set'}</dd>
                        
                        <dt class="col-sm-4">Created:</dt>
                        <dd class="col-sm-8">${domain.created_at}</dd>
                    </dl>
                `);
            } else {
                $('#domain-details').html('<p class="text-danger">Error loading domain details</p>');
            }
        })
        .catch(error => {
            $('#domain-details').html('<p class="text-danger">Error: ' + error.message + '</p>');
        });
}

function verifyDNS(domainId) {
    if (!confirm('Re-verify DNS records for this domain?')) {
        return;
    }
    
    // Trigger DNS verification
    fetch('/admin/projects/mail/api/domains/' + domainId + '/verify', {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('DNS verification started. Results will be updated shortly.');
            location.reload();
        } else {
            alert('Error: ' + (data.error || 'Verification failed'));
        }
    });
}

// Search functionality
$('#domain-search').on('keyup', function() {
    const value = $(this).val().toLowerCase();
    $('#domains-table-body tr').filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
});
</script>

<?php View::endSection(); ?>
