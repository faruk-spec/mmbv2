<?php

/**
 * Payment Gateway Interface
 * 
 * Standardized interface for all payment gateway implementations
 * Ensures consistent API across Stripe, Razorpay, Cashfree, etc.
 */

namespace Mail\Payment;

interface PaymentGatewayInterface
{
    /**
     * Create a checkout session for subscription payment
     * 
     * @param array $data Payment data (plan_id, subscriber_id, amount, currency, etc.)
     * @return array Session details with redirect URL
     */
    public function createCheckoutSession(array $data): array;
    
    /**
     * Verify payment completion
     * 
     * @param string $sessionId Session/Order ID from gateway
     * @return array Payment verification result
     */
    public function verifyPayment(string $sessionId): array;
    
    /**
     * Handle webhook notification from gateway
     * 
     * @param array $payload Webhook payload
     * @param string $signature Webhook signature for verification
     * @return array Processed webhook data
     */
    public function handleWebhook(array $payload, string $signature): array;
    
    /**
     * Create subscription for recurring payments
     * 
     * @param array $data Subscription data
     * @return array Subscription details
     */
    public function createSubscription(array $data): array;
    
    /**
     * Cancel subscription
     * 
     * @param string $subscriptionId Gateway subscription ID
     * @return bool Success status
     */
    public function cancelSubscription(string $subscriptionId): bool;
    
    /**
     * Process refund
     * 
     * @param string $paymentId Payment ID to refund
     * @param float $amount Refund amount
     * @return array Refund details
     */
    public function processRefund(string $paymentId, float $amount): array;
    
    /**
     * Get payment gateway name
     * 
     * @return string Gateway name (stripe, razorpay, cashfree)
     */
    public function getGatewayName(): string;
    
    /**
     * Get supported currencies
     * 
     * @return array List of supported currency codes
     */
    public function getSupportedCurrencies(): array;
}
