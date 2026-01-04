<?php
$pageTitle = "Admin Action Logs - Mail Server Admin";
require_once __DIR__ . '/../../layouts/admin.php';
?>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Admin Action Logs</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="/admin">Home</a></li>
                        <li class="breadcrumb-item"><a href="/admin/projects/mail">Mail Server</a></li>
                        <li class="breadcrumb-item active">Admin Logs</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Audit Trail</h3>
                    <div class="card-tools">
                        <button class="btn btn-sm btn-primary" onclick="exportLogs()">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
                
                <!-- Filters -->
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Action Type</label>
                                <select class="form-control" id="filter-action-type">
                                    <option value="">All Actions</option>
                                    <option value="subscriber_created">Subscriber Created</option>
                                    <option value="subscriber_suspended">Subscriber Suspended</option>
                                    <option value="subscriber_activated">Subscriber Activated</option>
                                    <option value="plan_changed">Plan Changed</option>
                                    <option value="feature_toggled">Feature Toggled</option>
                                    <option value="abuse_report_handled">Abuse Report Handled</option>
                                    <option value="settings_updated">Settings Updated</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Admin User</label>
                                <select class="form-control" id="filter-admin">
                                    <option value="">All Admins</option>
                                    <option value="1">John Doe</option>
                                    <option value="2">Jane Smith</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Date From</label>
                                <input type="date" class="form-control" id="filter-date-from">
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Date To</label>
                                <input type="date" class="form-control" id="filter-date-to">
                            </div>
                        </div>
                    </div>
                    
                    <button class="btn btn-primary" onclick="applyFilters()">
                        <i class="fas fa-filter"></i> Apply Filters
                    </button>
                    <button class="btn btn-secondary" onclick="clearFilters()">
                        <i class="fas fa-times"></i> Clear
                    </button>
                </div>
                
                <!-- Logs Table -->
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Timestamp</th>
                                <th>Admin</th>
                                <th>Action</th>
                                <th>Target</th>
                                <th>Description</th>
                                <th>IP Address</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody id="logs-table-body">
                            <tr>
                                <td>2026-01-03 10:30:15</td>
                                <td>John Doe</td>
                                <td>
                                    <span class="badge badge-warning">Subscriber Suspended</span>
                                </td>
                                <td>Acme Corp</td>
                                <td>Suspended subscriber for payment failure</td>
                                <td>192.168.1.100</td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewDetails(1)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>2026-01-03 09:15:42</td>
                                <td>Jane Smith</td>
                                <td>
                                    <span class="badge badge-info">Plan Changed</span>
                                </td>
                                <td>Tech Solutions</td>
                                <td>Changed plan from Starter to Business</td>
                                <td>192.168.1.101</td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewDetails(2)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>2026-01-03 08:45:20</td>
                                <td>John Doe</td>
                                <td>
                                    <span class="badge badge-danger">Abuse Report Handled</span>
                                </td>
                                <td>spam@example.com</td>
                                <td>Resolved spam complaint - account warned</td>
                                <td>192.168.1.100</td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewDetails(3)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>2026-01-03 07:30:10</td>
                                <td>Jane Smith</td>
                                <td>
                                    <span class="badge badge-primary">Settings Updated</span>
                                </td>
                                <td>SMTP Configuration</td>
                                <td>Updated SMTP rate limit to 1000/hour</td>
                                <td>192.168.1.101</td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewDetails(4)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>2026-01-02 18:20:35</td>
                                <td>John Doe</td>
                                <td>
                                    <span class="badge badge-success">Subscriber Activated</span>
                                </td>
                                <td>Startup Inc</td>
                                <td>Activated suspended subscriber after payment</td>
                                <td>192.168.1.100</td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="viewDetails(5)">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
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
            
            <!-- Statistics -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>1,250</h3>
                            <p>Total Actions (30 days)</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-list"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>45</h3>
                            <p>Today's Actions</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>12</h3>
                            <p>Critical Actions</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h3>5</h3>
                            <p>Active Admins</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- Log Details Modal -->
<div class="modal fade" id="logModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Action Details</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="log-details">
                <p>Loading...</p>
            </div>
        </div>
    </div>
</div>

<script>
function viewDetails(logId) {
    $('#logModal').modal('show');
    $('#log-details').html('<p>Loading log details...</p>');
    
    // Fetch log details
    fetch('/admin/projects/mail/api/logs/' + logId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const log = data.data;
                $('#log-details').html(`
                    <dl class="row">
                        <dt class="col-sm-4">Timestamp:</dt>
                        <dd class="col-sm-8">${log.created_at}</dd>
                        
                        <dt class="col-sm-4">Admin User:</dt>
                        <dd class="col-sm-8">${log.admin_name}</dd>
                        
                        <dt class="col-sm-4">Action Type:</dt>
                        <dd class="col-sm-8">
                            <span class="badge badge-info">${log.action_type}</span>
                        </dd>
                        
                        <dt class="col-sm-4">Target Type:</dt>
                        <dd class="col-sm-8">${log.target_type}</dd>
                        
                        <dt class="col-sm-4">Target ID:</dt>
                        <dd class="col-sm-8">${log.target_id}</dd>
                        
                        <dt class="col-sm-4">Description:</dt>
                        <dd class="col-sm-8">${log.action_description}</dd>
                        
                        <dt class="col-sm-4">IP Address:</dt>
                        <dd class="col-sm-8">${log.ip_address}</dd>
                        
                        <dt class="col-sm-4">User Agent:</dt>
                        <dd class="col-sm-8">${log.user_agent}</dd>
                        
                        ${log.metadata ? `
                        <dt class="col-sm-4">Additional Data:</dt>
                        <dd class="col-sm-8"><pre>${JSON.stringify(JSON.parse(log.metadata), null, 2)}</pre></dd>
                        ` : ''}
                    </dl>
                `);
            } else {
                $('#log-details').html('<p class="text-danger">Error loading log details</p>');
            }
        })
        .catch(error => {
            $('#log-details').html('<p class="text-danger">Error: ' + error.message + '</p>');
        });
}

function applyFilters() {
    const actionType = $('#filter-action-type').val();
    const admin = $('#filter-admin').val();
    const dateFrom = $('#filter-date-from').val();
    const dateTo = $('#filter-date-to').val();
    
    // Build query string
    const params = new URLSearchParams();
    if (actionType) params.append('action_type', actionType);
    if (admin) params.append('admin_id', admin);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    
    // Reload with filters
    window.location.href = '/admin/projects/mail/logs?' + params.toString();
}

function clearFilters() {
    window.location.href = '/admin/projects/mail/logs';
}

function exportLogs() {
    const actionType = $('#filter-action-type').val();
    const admin = $('#filter-admin').val();
    const dateFrom = $('#filter-date-from').val();
    const dateTo = $('#filter-date-to').val();
    
    // Build export URL
    const params = new URLSearchParams();
    if (actionType) params.append('action_type', actionType);
    if (admin) params.append('admin_id', admin);
    if (dateFrom) params.append('date_from', dateFrom);
    if (dateTo) params.append('date_to', dateTo);
    
    window.open('/admin/projects/mail/api/logs/export?' + params.toString(), '_blank');
}
</script>

<?php require_once __DIR__ . '/../../layouts/admin_footer.php'; ?>
