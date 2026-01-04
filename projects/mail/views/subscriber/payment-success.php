<?php
$pageTitle = "Payment Successful";
require_once 'layout.php';
?>

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 72px;"></i>
                    </div>
                    <h1 class="mb-3">Payment Successful!</h1>
                    <p class="lead">Your subscription has been activated successfully.</p>
                    
                    <div class="alert alert-info mt-4">
                        <strong>Order ID:</strong> <span id="order-id">Loading...</span><br>
                        <strong>Amount Paid:</strong> $<span id="amount">0.00</span><br>
                        <strong>Plan:</strong> <span id="plan-name">Loading...</span>
                    </div>
                    
                    <div class="mt-4">
                        <p>A confirmation email has been sent to your registered email address.</p>
                        <p>Your invoice is ready for download.</p>
                    </div>
                    
                    <div class="mt-4">
                        <a href="/projects/mail/subscriber/billing" class="btn btn-primary mr-2">
                            <i class="fas fa-file-invoice"></i> View Billing
                        </a>
                        <a href="/projects/mail/subscriber/dashboard" class="btn btn-outline-primary">
                            <i class="fas fa-home"></i> Go to Dashboard
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h4 class="mb-0">What's Next?</h4>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li class="mb-2">
                            <i class="fas fa-check text-success mr-2"></i>
                            Add team members to your account
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success mr-2"></i>
                            Configure your custom domain
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success mr-2"></i>
                            Set up email aliases for your team
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success mr-2"></i>
                            Explore webmail interface
                        </li>
                    </ul>
                    <a href="/projects/mail/subscriber/users/add" class="btn btn-success mt-3">
                        <i class="fas fa-user-plus"></i> Add Your First Team Member
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Get session ID from URL
const urlParams = new URLSearchParams(window.location.search);
const sessionId = urlParams.get('session_id') || urlParams.get('order_id');

if (sessionId) {
    // Verify payment and get details
    fetch('/projects/mail/api/payment/verify?session_id=' + sessionId)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                $('#order-id').text(sessionId);
                $('#amount').text(data.data.amount || '0.00');
                $('#plan-name').text(data.data.plan_name || 'N/A');
            }
        })
        .catch(error => console.error('Error:', error));
}
</script>

<?php require_once 'layout_footer.php'; ?>
