<?php

/**
 * Cashfree Payment Gateway
 * 
 * Handles Cashfree payment processing for Indian market
 * Fast settlement (T+1), supports UPI, Cards, Net Banking, Wallets
 */

namespace Mail\Payment\Gateways;

use Mail\Payment\PaymentGatewayInterface;

class CashfreeGateway implements PaymentGatewayInterface
{
    private $appId;
    private $secretKey;
    private $apiUrl;
    
    public function __construct()
    {
        $this->appId = env('CASHFREE_APP_ID');
        $this->secretKey = env('CASHFREE_SECRET_KEY');
        $this->apiUrl = env('CASHFREE_ENV', 'production') === 'production' 
            ? 'https://api.cashfree.com/pg' 
            : 'https://sandbox.cashfree.com/pg';
    }
    
    public function createCheckoutSession(array $data): array
    {
        try {
            $orderId = 'order_' . $data['subscriber_id'] . '_' . time();
            
            $orderData = [
                'order_id' => $orderId,
                'order_amount' => $data['amount'],
                'order_currency' => $data['currency'],
                'customer_details' => [
                    'customer_id' => 'sub_' . $data['subscriber_id'],
                    'customer_email' => $data['email'],
                    'customer_phone' => $data['phone'] ?? '9999999999',
                ],
                'order_meta' => [
                    'return_url' => $data['success_url'],
                    'notify_url' => $data['webhook_url'] ?? '',
                ],
                'order_note' => $data['plan_name'],
            ];
            
            $response = $this->makeApiCall('/orders', $orderData, 'POST');
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'order_id' => $orderId,
                    'payment_session_id' => $response['data']['payment_session_id'] ?? null,
                    'redirect_url' => $response['data']['payment_link'] ?? null,
                    'gateway' => 'cashfree',
                ];
            }
            
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Unknown error',
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
            $response = $this->makeApiCall("/orders/{$sessionId}", [], 'GET');
            
            if ($response['success']) {
                $order = $response['data'];
                return [
                    'success' => true,
                    'order_id' => $order['order_id'],
                    'order_status' => $order['order_status'],
                    'amount' => $order['order_amount'],
                    'currency' => $order['order_currency'],
                    'payment_method' => $order['payment_method'] ?? null,
                ];
            }
            
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Verification failed',
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
            $computedSignature = $this->generateSignature($payload);
            
            if ($signature !== $computedSignature) {
                return [
                    'success' => false,
                    'error' => 'Invalid signature',
                ];
            }
            
            $event = $payload['type'] ?? '';
            $data = $payload['data'] ?? [];
            
            $response = [
                'success' => true,
                'event_type' => $event,
                'data' => [],
            ];
            
            switch ($event) {
                case 'PAYMENT_SUCCESS':
                    $response['data'] = [
                        'order_id' => $data['order']['order_id'] ?? null,
                        'order_amount' => $data['order']['order_amount'] ?? 0,
                        'order_currency' => $data['order']['order_currency'] ?? 'INR',
                        'payment_method' => $data['payment']['payment_method'] ?? null,
                        'payment_time' => $data['payment']['payment_time'] ?? null,
                    ];
                    break;
                    
                case 'PAYMENT_FAILED':
                    $response['data'] = [
                        'order_id' => $data['order']['order_id'] ?? null,
                        'error_text' => $data['error_details']['error_description'] ?? 'Payment failed',
                    ];
                    break;
                    
                case 'SETTLEMENT':
                    $response['data'] = [
                        'order_id' => $data['order_id'] ?? null,
                        'settlement_amount' => $data['settlement_amount'] ?? 0,
                        'settlement_date' => $data['settlement_date'] ?? null,
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
        // Cashfree doesn't have native subscription support
        // Use recurring payments or create orders monthly
        return $this->createCheckoutSession($data);
    }
    
    public function cancelSubscription(string $subscriptionId): bool
    {
        // Handle subscription cancellation in your database
        return true;
    }
    
    public function processRefund(string $paymentId, float $amount): array
    {
        try {
            $refundData = [
                'refund_amount' => $amount,
                'refund_id' => 'refund_' . time(),
                'refund_note' => 'Customer refund request',
            ];
            
            $response = $this->makeApiCall("/orders/{$paymentId}/refunds", $refundData, 'POST');
            
            if ($response['success']) {
                return [
                    'success' => true,
                    'refund_id' => $response['data']['refund_id'] ?? null,
                    'amount' => $response['data']['refund_amount'] ?? $amount,
                    'status' => $response['data']['refund_status'] ?? 'PENDING',
                ];
            }
            
            return [
                'success' => false,
                'error' => $response['error'] ?? 'Refund failed',
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
        return 'cashfree';
    }
    
    public function getSupportedCurrencies(): array
    {
        return ['INR'];
    }
    
    private function makeApiCall(string $endpoint, array $data = [], string $method = 'GET'): array
    {
        $url = $this->apiUrl . $endpoint;
        
        $headers = [
            'Content-Type: application/json',
            'x-api-version: 2022-09-01',
            'x-client-id: ' . $this->appId,
            'x-client-secret: ' . $this->secretKey,
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $result = json_decode($response, true);
        
        if ($httpCode >= 200 && $httpCode < 300) {
            return [
                'success' => true,
                'data' => $result,
            ];
        }
        
        return [
            'success' => false,
            'error' => $result['message'] ?? 'API call failed',
        ];
    }
    
    private function generateSignature(array $data): string
    {
        ksort($data);
        $signatureData = '';
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $signatureData .= $key . $value;
        }
        return base64_encode(hash_hmac('sha256', $signatureData, $this->secretKey, true));
    }
}
