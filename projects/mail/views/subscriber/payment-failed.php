<?php
$pageTitle = "Payment Failed";
require_once 'layout.php';
?>

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-8">
            <div class="card border-danger">
                <div class="card-body text-center py-5">
                    <div class="mb-4">
                        <i class="fas fa-times-circle text-danger" style="font-size: 72px;"></i>
                    </div>
                    <h1 class="mb-3">Payment Failed</h1>
                    <p class="lead">Unfortunately, your payment could not be processed.</p>
                    
                    <div class="alert alert-danger mt-4">
                        <strong>Error:</strong> <span id="error-message">Payment was declined or cancelled.</span>
                    </div>
                    
                    <div class="mt-4">
                        <h5>Common reasons for payment failure:</h5>
                        <ul class="text-left">
                            <li>Insufficient funds in your account</li>
                            <li>Payment was cancelled before completion</li>
                            <li>Card details were incorrect</li>
                            <li>Bank declined the transaction</li>
                            <li>Payment gateway timeout</li>
                        </ul>
                    </div>
                    
                    <div class="mt-4">
                        <a href="/projects/mail/subscriber/upgrade" class="btn btn-primary mr-2">
                            <i class="fas fa-redo"></i> Try Again
                        </a>
                        <a href="/projects/mail/subscriber/dashboard" class="btn btn-outline-secondary">
                            <i class="fas fa-home"></i> Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="card mt-4">
                <div class="card-header">
                    <h4 class="mb-0">Need Help?</h4>
                </div>
                <div class="card-body">
                    <p>If you continue to experience issues with payment, please:</p>
                    <ul>
                        <li>Check your card details are correct</li>
                        <li>Ensure you have sufficient funds</li>
                        <li>Try a different payment method</li>
                        <li>Contact your bank if the issue persists</li>
                    </ul>
                    
                    <div class="alert alert-info mt-3">
                        <strong>Need assistance?</strong><br>
                        Contact our support team:<br>
                        <i class="fas fa-envelope"></i> support@yourdomain.com<br>
                        <i class="fas fa-phone"></i> +1 (555) 123-4567
                    </div>
                    
                    <div class="mt-3">
                        <h5>Alternative Payment Options:</h5>
                        <p>We accept:</p>
                        <div class="d-flex">
                            <i class="fab fa-cc-visa fa-3x mr-3 text-primary"></i>
                            <i class="fab fa-cc-mastercard fa-3x mr-3 text-warning"></i>
                            <i class="fab fa-cc-amex fa-3x mr-3 text-info"></i>
                            <i class="fab fa-cc-paypal fa-3x mr-3 text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Get error message from URL
const urlParams = new URLSearchParams(window.location.search);
const errorMsg = urlParams.get('error');

if (errorMsg) {
    $('#error-message').text(decodeURIComponent(errorMsg));
}
</script>

<?php require_once 'layout_footer.php'; ?>
