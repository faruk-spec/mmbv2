<?php
$pageTitle = "Upgrade Plan";
require_once 'layout.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Choose Your Plan</h1>
            <p class="lead">Select the plan that best fits your needs</p>
        </div>
    </div>

    <!-- Plan Comparison -->
    <div class="row">
        <!-- Free Plan -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header text-center">
                    <h3>Free</h3>
                    <h2>$0<small>/month</small></h2>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> 1 User</li>
                        <li><i class="fas fa-check text-success"></i> 1GB Storage</li>
                        <li><i class="fas fa-check text-success"></i> 50 emails/day</li>
                        <li><i class="fas fa-check text-success"></i> Webmail Access</li>
                        <li><i class="fas fa-times text-muted"></i> SMTP/IMAP</li>
                        <li><i class="fas fa-times text-muted"></i> API Access</li>
                    </ul>
                </div>
                <div class="card-footer">
                    <button class="btn btn-outline-secondary btn-block" disabled>
                        Current Plan
                    </button>
                </div>
            </div>
        </div>

        <!-- Starter Plan -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100 border-primary">
                <div class="card-header bg-primary text-white text-center">
                    <h3>Starter</h3>
                    <h2>$9.99<small>/month</small></h2>
                    <span class="badge badge-warning">Most Popular</span>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> 5 Users</li>
                        <li><i class="fas fa-check text-success"></i> 5GB Storage</li>
                        <li><i class="fas fa-check text-success"></i> 500 emails/day</li>
                        <li><i class="fas fa-check text-success"></i> Webmail Access</li>
                        <li><i class="fas fa-check text-success"></i> SMTP/IMAP</li>
                        <li><i class="fas fa-check text-success"></i> 2FA Support</li>
                        <li><i class="fas fa-times text-muted"></i> API Access</li>
                    </ul>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary btn-block" onclick="selectPlan(2, 'Starter', 9.99)">
                        Upgrade Now
                    </button>
                </div>
            </div>
        </div>

        <!-- Business Plan -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header text-center">
                    <h3>Business</h3>
                    <h2>$29.99<small>/month</small></h2>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> 25 Users</li>
                        <li><i class="fas fa-check text-success"></i> 25GB Storage</li>
                        <li><i class="fas fa-check text-success"></i> 2,000 emails/day</li>
                        <li><i class="fas fa-check text-success"></i> Webmail Access</li>
                        <li><i class="fas fa-check text-success"></i> SMTP/IMAP</li>
                        <li><i class="fas fa-check text-success"></i> 2FA Support</li>
                        <li><i class="fas fa-check text-success"></i> API Access</li>
                    </ul>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary btn-block" onclick="selectPlan(3, 'Business', 29.99)">
                        Upgrade Now
                    </button>
                </div>
            </div>
        </div>

        <!-- Developer Plan -->
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card h-100">
                <div class="card-header text-center">
                    <h3>Developer</h3>
                    <h2>$49.99<small>/month</small></h2>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check text-success"></i> 100 Users</li>
                        <li><i class="fas fa-check text-success"></i> 50GB Storage</li>
                        <li><i class="fas fa-check text-success"></i> 10,000 emails/day</li>
                        <li><i class="fas fa-check text-success"></i> Webmail Access</li>
                        <li><i class="fas fa-check text-success"></i> SMTP/IMAP</li>
                        <li><i class="fas fa-check text-success"></i> 2FA Support</li>
                        <li><i class="fas fa-check text-success"></i> API Access</li>
                        <li><i class="fas fa-check text-success"></i> Webhooks</li>
                    </ul>
                </div>
                <div class="card-footer">
                    <button class="btn btn-primary btn-block" onclick="selectPlan(4, 'Developer', 49.99)">
                        Upgrade Now
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Gateway Selection Modal -->
    <div class="modal fade" id="gatewayModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Select Payment Gateway</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>You've selected: <strong id="selected-plan-name"></strong> - 
                       $<span id="selected-plan-price"></span>/month</p>
                    
                    <div class="form-group">
                        <label>Select Currency:</label>
                        <select id="currency-select" class="form-control" onchange="updateGateways()">
                            <option value="USD">USD - US Dollar</option>
                            <option value="EUR">EUR - Euro</option>
                            <option value="GBP">GBP - British Pound</option>
                            <option value="INR">INR - Indian Rupee</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Select Payment Gateway:</label>
                        <div id="gateway-options">
                            <button class="btn btn-outline-primary btn-block mb-2" onclick="checkout('stripe')">
                                <i class="fab fa-stripe"></i> Pay with Stripe
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let selectedPlanId = null;

function selectPlan(planId, planName, price) {
    selectedPlanId = planId;
    $('#selected-plan-name').text(planName);
    $('#selected-plan-price').text(price);
    $('#gatewayModal').modal('show');
}

function updateGateways() {
    const currency = $('#currency-select').val();
    let html = '';
    
    if (currency === 'INR') {
        html = `
            <button class="btn btn-outline-primary btn-block mb-2" onclick="checkout('razorpay')">
                <img src="/assets/img/razorpay.png" height="20"> Pay with Razorpay
            </button>
            <button class="btn btn-outline-primary btn-block mb-2" onclick="checkout('cashfree')">
                <img src="/assets/img/cashfree.png" height="20"> Pay with Cashfree
            </button>
        `;
    } else {
        html = `
            <button class="btn btn-outline-primary btn-block mb-2" onclick="checkout('stripe')">
                <i class="fab fa-stripe"></i> Pay with Stripe
            </button>
        `;
    }
    
    $('#gateway-options').html(html);
}

function checkout(gateway) {
    const currency = $('#currency-select').val();
    
    // Create checkout session
    fetch('/projects/mail/api/payment/checkout', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({
            plan_id: selectedPlanId,
            gateway: gateway,
            currency: currency
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && data.data.redirect_url) {
            // Redirect to payment gateway
            window.location.href = data.data.redirect_url;
        } else {
            alert('Error: ' + (data.error || 'Checkout failed'));
        }
    })
    .catch(error => {
        alert('Error: ' + error.message);
    });
}
</script>

<?php require_once 'layout_footer.php'; ?>
