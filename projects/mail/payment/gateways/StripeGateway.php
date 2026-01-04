<?php

/**
 * Stripe Payment Gateway
 * 
 * Handles Stripe payment processing for international payments
 * Supports 135+ currencies, credit/debit cards, digital wallets
 */

namespace Mail\Payment\Gateways;

use Mail\Payment\PaymentGatewayInterface;

class StripeGateway implements PaymentGatewayInterface
{
    private $publicKey;
    private $secretKey;
    private $webhookSecret;
    
    public function __construct()
    {
        $this->publicKey = env('STRIPE_PUBLIC_KEY');
        $this->secretKey = env('STRIPE_SECRET_KEY');
        $this->webhookSecret = env('STRIPE_WEBHOOK_SECRET');
        
        // Initialize Stripe library
        if (class_exists('\Stripe\Stripe')) {
            \Stripe\Stripe::setApiKey($this->secretKey);
        }
    }
    
    public function createCheckoutSession(array $data): array
    {
        try {
            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($data['currency']),
                        'product_data' => [
                            'name' => $data['plan_name'],
                            'description' => $data['plan_description'] ?? '',
                        ],
                        'unit_amount' => $data['amount'] * 100, // Convert to cents
                        'recurring' => [
                            'interval' => $data['billing_cycle'] ?? 'month',
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'subscription',
                'success_url' => $data['success_url'],
                'cancel_url' => $data['cancel_url'],
                'client_reference_id' => $data['subscriber_id'],
                'metadata' => [
                    'subscriber_id' => $data['subscriber_id'],
                    'plan_id' => $data['plan_id'],
                ],
            ]);
            
            return [
                'success' => true,
                'session_id' => $session->id,
                'redirect_url' => $session->url,
                'gateway' => 'stripe',
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
            $session = \Stripe\Checkout\Session::retrieve($sessionId);
            
            return [
                'success' => true,
                'payment_status' => $session->payment_status,
                'amount' => $session->amount_total / 100,
                'currency' => strtoupper($session->currency),
                'customer_email' => $session->customer_email,
                'subscription_id' => $session->subscription,
                'metadata' => $session->metadata->toArray(),
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
            $event = \Stripe\Webhook::constructEvent(
                json_encode($payload),
                $signature,
                $this->webhookSecret
            );
            
            $response = [
                'success' => true,
                'event_type' => $event->type,
                'data' => [],
            ];
            
            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;
                    $response['data'] = [
                        'subscriber_id' => $session->metadata->subscriber_id ?? null,
                        'plan_id' => $session->metadata->plan_id ?? null,
                        'subscription_id' => $session->subscription,
                        'customer_id' => $session->customer,
                        'amount' => $session->amount_total / 100,
                        'currency' => strtoupper($session->currency),
                    ];
                    break;
                    
                case 'customer.subscription.updated':
                case 'customer.subscription.deleted':
                    $subscription = $event->data->object;
                    $response['data'] = [
                        'subscription_id' => $subscription->id,
                        'status' => $subscription->status,
                        'current_period_end' => $subscription->current_period_end,
                    ];
                    break;
                    
                case 'invoice.payment_succeeded':
                case 'invoice.payment_failed':
                    $invoice = $event->data->object;
                    $response['data'] = [
                        'subscription_id' => $invoice->subscription,
                        'amount' => $invoice->amount_paid / 100,
                        'currency' => strtoupper($invoice->currency),
                        'status' => $invoice->status,
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
        // Handled via checkout session in Stripe
        return $this->createCheckoutSession($data);
    }
    
    public function cancelSubscription(string $subscriptionId): bool
    {
        try {
            $subscription = \Stripe\Subscription::retrieve($subscriptionId);
            $subscription->cancel();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    public function processRefund(string $paymentId, float $amount): array
    {
        try {
            $refund = \Stripe\Refund::create([
                'payment_intent' => $paymentId,
                'amount' => $amount * 100, // Convert to cents
            ]);
            
            return [
                'success' => true,
                'refund_id' => $refund->id,
                'amount' => $refund->amount / 100,
                'status' => $refund->status,
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
        return 'stripe';
    }
    
    public function getSupportedCurrencies(): array
    {
        return ['USD', 'EUR', 'GBP', 'AUD', 'CAD', 'JPY', 'SGD', 'HKD'];
    }
}
