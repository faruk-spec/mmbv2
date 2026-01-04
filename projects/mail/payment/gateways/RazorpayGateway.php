<?php

/**
 * Razorpay Payment Gateway
 * 
 * Handles Razorpay payment processing for Indian market
 * Supports UPI, Net Banking, Cards, Wallets
 */

namespace Mail\Payment\Gateways;

use Mail\Payment\PaymentGatewayInterface;

class RazorpayGateway implements PaymentGatewayInterface
{
    private $keyId;
    private $keySecret;
    private $webhookSecret;
    
    public function __construct()
    {
        $this->keyId = env('RAZORPAY_KEY_ID');
        $this->keySecret = env('RAZORPAY_KEY_SECRET');
        $this->webhookSecret = env('RAZORPAY_WEBHOOK_SECRET');
    }
    
    public function createCheckoutSession(array $data): array
    {
        try {
            $api = new \Razorpay\Api\Api($this->keyId, $this->keySecret);
            
            $orderData = [
                'receipt' => 'sub_' . $data['subscriber_id'] . '_' . time(),
                'amount' => $data['amount'] * 100, // Convert to paise
                'currency' => $data['currency'],
                'notes' => [
                    'subscriber_id' => $data['subscriber_id'],
                    'plan_id' => $data['plan_id'],
                    'plan_name' => $data['plan_name'],
                ],
            ];
            
            $order = $api->order->create($orderData);
            
            return [
                'success' => true,
                'order_id' => $order['id'],
                'amount' => $order['amount'] / 100,
                'currency' => $order['currency'],
                'gateway' => 'razorpay',
                'key_id' => $this->keyId,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    public function verifyPayment(string $sessionId): array
    {
        try {
            $api = new \Razorpay\Api\Api($this->keyId, $this->keySecret);
            $payment = $api->payment->fetch($sessionId);
            
            return [
                'success' => true,
                'payment_id' => $payment['id'],
                'order_id' => $payment['order_id'],
                'amount' => $payment['amount'] / 100,
                'currency' => $payment['currency'],
                'status' => $payment['status'],
                'method' => $payment['method'],
                'email' => $payment['email'] ?? null,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    public function handleWebhook(array $payload, string $signature): array
    {
        try {
            // Verify webhook signature
            $expectedSignature = hash_hmac('sha256', json_encode($payload), $this->webhookSecret);
            
            if ($signature !== $expectedSignature) {
                return [
                    'success' => false,
                    'error' => 'Invalid signature',
                ];
            }
            
            $event = $payload['event'];
            $paymentData = $payload['payload']['payment']['entity'] ?? [];
            $subscriptionData = $payload['payload']['subscription']['entity'] ?? [];
            
            $response = [
                'success' => true,
                'event_type' => $event,
                'data' => [],
            ];
            
            switch ($event) {
                case 'payment.captured':
                    $response['data'] = [
                        'payment_id' => $paymentData['id'],
                        'order_id' => $paymentData['order_id'],
                        'amount' => $paymentData['amount'] / 100,
                        'currency' => $paymentData['currency'],
                        'method' => $paymentData['method'],
                        'email' => $paymentData['email'] ?? null,
                    ];
                    break;
                    
                case 'payment.failed':
                    $response['data'] = [
                        'payment_id' => $paymentData['id'],
                        'order_id' => $paymentData['order_id'],
                        'error_code' => $paymentData['error_code'] ?? null,
                        'error_description' => $paymentData['error_description'] ?? null,
                    ];
                    break;
                    
                case 'subscription.charged':
                case 'subscription.cancelled':
                    $response['data'] = [
                        'subscription_id' => $subscriptionData['id'],
                        'status' => $subscriptionData['status'],
                        'current_end' => $subscriptionData['current_end'] ?? null,
                    ];
                    break;
            }
            
            return $response;
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    public function createSubscription(array $data): array
    {
        try {
            $api = new \Razorpay\Api\Api($this->keyId, $this->keySecret);
            
            // Create subscription plan first
            $plan = $api->plan->create([
                'period' => $data['billing_cycle'] ?? 'monthly',
                'interval' => 1,
                'item' => [
                    'name' => $data['plan_name'],
                    'description' => $data['plan_description'] ?? '',
                    'amount' => $data['amount'] * 100,
                    'currency' => $data['currency'],
                ],
            ]);
            
            // Create subscription
            $subscription = $api->subscription->create([
                'plan_id' => $plan['id'],
                'customer_notify' => 1,
                'quantity' => 1,
                'total_count' => 12, // 12 months
                'notes' => [
                    'subscriber_id' => $data['subscriber_id'],
                    'plan_id' => $data['plan_id'],
                ],
            ]);
            
            return [
                'success' => true,
                'subscription_id' => $subscription['id'],
                'plan_id' => $plan['id'],
                'status' => $subscription['status'],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    public function cancelSubscription(string $subscriptionId): bool
    {
        try {
            $api = new \Razorpay\Api\Api($this->keyId, $this->keySecret);
            $subscription = $api->subscription->fetch($subscriptionId)->cancel();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function processRefund(string $paymentId, float $amount): array
    {
        try {
            $api = new \Razorpay\Api\Api($this->keyId, $this->keySecret);
            
            $refund = $api->payment->fetch($paymentId)->refund([
                'amount' => $amount * 100, // Convert to paise
            ]);
            
            return [
                'success' => true,
                'refund_id' => $refund['id'],
                'amount' => $refund['amount'] / 100,
                'status' => $refund['status'],
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
    
    public function getGatewayName(): string
    {
        return 'razorpay';
    }
    
    public function getSupportedCurrencies(): array
    {
        return ['INR'];
    }
}
