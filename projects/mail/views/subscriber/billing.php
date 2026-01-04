<?php
$pageTitle = "Billing & Subscription";
require_once 'layout.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Billing & Subscription</h1>
        </div>
    </div>

    <!-- Current Subscription Card -->
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Current Subscription</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h4 id="current-plan-name">Starter Plan</h4>
                            <p class="text-muted">$9.99/month</p>
                            <p><strong>Status:</strong> <span class="badge badge-success">Active</span></p>
                            <p><strong>Next Billing Date:</strong> <span id="next-billing">Jan 15, 2026</span></p>
                        </div>
                        <div class="col-md-6">
                            <h5>Plan Features:</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> 5 Users</li>
                                <li><i class="fas fa-check text-success"></i> 5GB Storage</li>
                                <li><i class="fas fa-check text-success"></i> 500 emails/day</li>
                                <li><i class="fas fa-check text-success"></i> SMTP/IMAP Access</li>
                            </ul>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="upgrade" class="btn btn-primary">
                            <i class="fas fa-arrow-up"></i> Upgrade Plan
                        </a>
                        <button class="btn btn-outline-danger" onclick="cancelSubscription()">
                            <i class="fas fa-times"></i> Cancel Subscription
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Usage Statistics -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Usage This Month</h3>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label>Users</label>
                        <div class="progress">
                            <div class="progress-bar" role="progressbar" style="width: 60%" 
                                 aria-valuenow="3" aria-valuemin="0" aria-valuemax="5">
                                3 / 5
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Storage</label>
                        <div class="progress">
                            <div class="progress-bar bg-info" role="progressbar" style="width: 45%" 
                                 aria-valuenow="2.25" aria-valuemin="0" aria-valuemax="5">
                                2.25GB / 5GB
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Emails Sent Today</label>
                        <div class="progress">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: 30%" 
                                 aria-valuenow="150" aria-valuemin="0" aria-valuemax="500">
                                150 / 500
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Method -->
    <div class="row mt-4">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Payment Method</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fab fa-cc-visa fa-2x text-primary"></i>
                            <span class="ml-2">•••• •••• •••• 4242</span>
                            <p class="text-muted mb-0">Expires 12/2026</p>
                        </div>
                        <button class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-edit"></i> Update
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Billing Information</h3>
                </div>
                <div class="card-body">
                    <p><strong>Company:</strong> Example Inc.</p>
                    <p><strong>Email:</strong> billing@example.com</p>
                    <p><strong>Address:</strong> 123 Main St, City, Country</p>
                    <button class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Billing History -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Billing History</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Date</th>
                                <th>Description</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="billing-history">
                            <tr>
                                <td>INV-2026-000001</td>
                                <td>Jan 1, 2026</td>
                                <td>Starter Plan - Monthly</td>
                                <td>$9.99</td>
                                <td><span class="badge badge-success">Paid</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="downloadInvoice(1)">
                                        <i class="fas fa-download"></i> Download
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>INV-2025-000012</td>
                                <td>Dec 1, 2025</td>
                                <td>Starter Plan - Monthly</td>
                                <td>$9.99</td>
                                <td><span class="badge badge-success">Paid</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="downloadInvoice(12)">
                                        <i class="fas fa-download"></i> Download
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function cancelSubscription() {
    if (!confirm('Are you sure you want to cancel your subscription? You will lose access to premium features at the end of the current billing period.')) {
        return;
    }
    
    // Send cancel request
    fetch('/projects/mail/api/subscription/cancel', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'}
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Subscription cancelled successfully');
            location.reload();
        } else {
            alert('Error: ' + data.error);
        }
    });
}

function downloadInvoice(invoiceId) {
    window.open('/projects/mail/api/invoice/' + invoiceId + '/download', '_blank');
}
</script>

<?php require_once 'layout_footer.php'; ?>
