<?php

/**
 * Payment Controller
 * 
 * Handles payment processing, billing, and subscription management
 * Supports multiple payment gateways (Stripe, Razorpay, Cashfree)
 */

namespace Controllers\Mail;

use Core\View;

use Controllers\BaseController;
use Core\Database;
use Mail\Payment\Gateways\StripeGateway;
use Mail\Payment\Gateways\RazorpayGateway;
use Mail\Payment\Gateways\CashfreeGateway;

class PaymentController extends BaseController
{
    private $db;
    private $subscriberId;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->subscriberId = $_SESSION['subscriber_id'] ?? null;
    }
    
    /**
     * Create checkout session for plan upgrade/purchase
     */
    public function createCheckoutSession()
    {
        $planId = $_POST['plan_id'] ?? null;
        $gateway = $_POST['gateway'] ?? 'stripe';
        $currency = $_POST['currency'] ?? 'USD';
        
        if (!$planId) {
            return $this->jsonError('Plan ID required');
        }
        
        // Get plan details
        $plan = $this->db->fetch(
            "SELECT * FROM mail_subscription_plans WHERE id = ?",
            [$planId]
        );
        
        if (!$plan) {
            return $this->jsonError('Invalid plan');
        }
        
        // Get subscriber details
        $subscriber = $this->db->fetch(
            "SELECT * FROM mail_subscribers WHERE id = ?",
            [$this->subscriberId]
        );
        
        // Select gateway based on currency
        if ($currency === 'INR') {
            $gateway = $gateway === 'cashfree' ? 'cashfree' : 'razorpay';
        } else {
            $gateway = 'stripe';
        }
        
        $paymentGateway = $this->getGateway($gateway);
        
        $checkoutData = [
            'plan_id' => $plan['id'],
            'plan_name' => $plan['name'],
            'plan_description' => $plan['description'] ?? '',
            'amount' => $plan['price'],
            'currency' => $currency,
            'subscriber_id' => $this->subscriberId,
            'email' => $subscriber['email'],
            'billing_cycle' => 'month',
            'success_url' => url('/projects/mail/subscriber/payment/success?session_id={CHECKOUT_SESSION_ID}'),
            'cancel_url' => url('/projects/mail/subscriber/upgrade?error=payment_cancelled'),
            'webhook_url' => url('/projects/mail/webhook/' . $gateway),
        ];
        
        $session = $paymentGateway->createCheckoutSession($checkoutData);
        
        if ($session['success']) {
            // Store pending payment record
            $this->db->query(
                "INSERT INTO mail_payments (subscriber_id, plan_id, amount, currency, gateway, 
                                           session_id, status, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())",
                [$this->subscriberId, $planId, $plan['price'], $currency, $gateway, 
                 $session['session_id'] ?? $session['order_id']]
            );
            
            return $this->jsonSuccess($session);
        }
        
        return $this->jsonError($session['error'] ?? 'Checkout creation failed');
    }
    
    /**
     * Verify payment after redirect from gateway
     */
    public function verifyPayment()
    {
        $sessionId = $_GET['session_id'] ?? $_GET['order_id'] ?? null;
        
        if (!$sessionId) {
            return $this->jsonError('Session ID required');
        }
        
        // Get payment record
        $payment = $this->db->fetch(
            "SELECT * FROM mail_payments WHERE session_id = ? AND subscriber_id = ?",
            [$sessionId, $this->subscriberId]
        );
        
        if (!$payment) {
            return $this->jsonError('Payment not found');
        }
        
        $gateway = $this->getGateway($payment['gateway']);
        $verification = $gateway->verifyPayment($sessionId);
        
        if ($verification['success'] && $verification['payment_status'] === 'paid') {
            // Update payment status
            $this->db->query(
                "UPDATE mail_payments SET status = 'completed', paid_at = NOW() WHERE id = ?",
                [$payment['id']]
            );
            
            // Update or create subscription
            $this->activateSubscription($payment);
            
            // Generate invoice
            $this->generateInvoice($payment);
            
            return $this->jsonSuccess([
                'message' => 'Payment successful',
                'payment_id' => $payment['id'],
            ]);
        }
        
        // Update payment status to failed
        $this->db->query(
            "UPDATE mail_payments SET status = 'failed' WHERE id = ?",
            [$payment['id']]
        );
        
        return $this->jsonError('Payment verification failed');
    }
    
    /**
     * Handle webhook from payment gateway
     */
    public function handleWebhook($gateway)
    {
        $payload = file_get_contents('php://input');
        $signature = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? 
                    $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? 
                    $_SERVER['HTTP_X_CASHFREE_SIGNATURE'] ?? '';
        
        $paymentGateway = $this->getGateway($gateway);
        $webhookData = $paymentGateway->handleWebhook(json_decode($payload, true), $signature);
        
        if (!$webhookData['success']) {
            http_response_code(400);
            exit;
        }
        
        // Process webhook based on event type
        switch ($webhookData['event_type']) {
            case 'checkout.session.completed':
            case 'payment.captured':
            case 'PAYMENT_SUCCESS':
                $this->processSuccessfulPayment($webhookData['data'], $gateway);
                break;
                
            case 'customer.subscription.updated':
            case 'subscription.charged':
                $this->processSubscriptionUpdate($webhookData['data']);
                break;
                
            case 'customer.subscription.deleted':
            case 'subscription.cancelled':
                $this->processSubscriptionCancellation($webhookData['data']);
                break;
        }
        
        http_response_code(200);
        echo json_encode(['received' => true]);
        exit;
    }
    
    /**
     * Cancel subscription
     */
    public function cancelSubscription()
    {
        $subscription = $this->db->fetch(
            "SELECT * FROM mail_subscriptions WHERE subscriber_id = ? AND status = 'active'",
            [$this->subscriberId]
        );
        
        if (!$subscription) {
            return $this->jsonError('No active subscription found');
        }
        
        $gateway = $this->getGateway($subscription['payment_gateway']);
        $cancelled = $gateway->cancelSubscription($subscription['gateway_subscription_id']);
        
        if ($cancelled) {
            $this->db->query(
                "UPDATE mail_subscriptions SET status = 'cancelled', cancelled_at = NOW() 
                 WHERE id = ?",
                [$subscription['id']]
            );
            
            return $this->jsonSuccess(['message' => 'Subscription cancelled']);
        }
        
        return $this->jsonError('Cancellation failed');
    }
    
    /**
     * Process refund
     */
    public function processRefund()
    {
        $paymentId = $_POST['payment_id'] ?? null;
        $amount = $_POST['amount'] ?? null;
        
        if (!$paymentId || !$amount) {
            return $this->jsonError('Payment ID and amount required');
        }
        
        $payment = $this->db->fetch(
            "SELECT * FROM mail_payments WHERE id = ?",
            [$paymentId]
        );
        
        if (!$payment) {
            return $this->jsonError('Payment not found');
        }
        
        $gateway = $this->getGateway($payment['gateway']);
        $refund = $gateway->processRefund($payment['gateway_payment_id'], $amount);
        
        if ($refund['success']) {
            $this->db->query(
                "UPDATE mail_payments SET status = 'refunded', refunded_at = NOW(), 
                 refund_amount = ? WHERE id = ?",
                [$amount, $paymentId]
            );
            
            return $this->jsonSuccess($refund);
        }
        
        return $this->jsonError($refund['error'] ?? 'Refund failed');
    }
    
    private function activateSubscription($payment)
    {
        // Check if subscription exists
        $existing = $this->db->fetch(
            "SELECT id FROM mail_subscriptions WHERE subscriber_id = ?",
            [$payment['subscriber_id']]
        );
        
        if ($existing) {
            // Update existing subscription
            $this->db->query(
                "UPDATE mail_subscriptions 
                 SET plan_id = ?, status = 'active', started_at = NOW(), 
                     current_period_end = DATE_ADD(NOW(), INTERVAL 1 MONTH)
                 WHERE subscriber_id = ?",
                [$payment['plan_id'], $payment['subscriber_id']]
            );
        } else {
            // Create new subscription
            $this->db->query(
                "INSERT INTO mail_subscriptions (subscriber_id, plan_id, status, started_at, 
                                                 current_period_end, payment_gateway, created_at)
                 VALUES (?, ?, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 1 MONTH), ?, NOW())",
                [$payment['subscriber_id'], $payment['plan_id'], $payment['gateway']]
            );
        }
    }
    
    private function generateInvoice($payment)
    {
        $invoiceNumber = 'INV-' . date('Y') . '-' . str_pad($payment['id'], 6, '0', STR_PAD_LEFT);
        
        $this->db->query(
            "INSERT INTO mail_invoices (subscriber_id, payment_id, invoice_number, amount, 
                                        currency, status, issued_at, created_at)
             VALUES (?, ?, ?, ?, ?, 'paid', NOW(), NOW())",
            [$payment['subscriber_id'], $payment['id'], $invoiceNumber, 
             $payment['amount'], $payment['currency']]
        );
    }
    
    private function getGateway($name)
    {
        switch ($name) {
            case 'stripe':
                return new StripeGateway();
            case 'razorpay':
                return new RazorpayGateway();
            case 'cashfree':
                return new CashfreeGateway();
            default:
                throw new \Exception('Invalid gateway');
        }
    }
    
    private function processSuccessfulPayment($data, $gateway)
    {
        // Implementation for processing successful payment webhooks
    }
    
    private function processSubscriptionUpdate($data)
    {
        // Implementation for subscription updates
    }
    
    private function processSubscriptionCancellation($data)
    {
        // Implementation for subscription cancellations
    }
    
    private function jsonSuccess($data)
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }
    
    private function jsonError($message)
    {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message]);
        exit;
    }
}
