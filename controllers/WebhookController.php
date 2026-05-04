<?php

namespace Controllers;

use Core\Database;
use Core\Logger;
use Core\SubscriptionService;

class WebhookController extends BaseController
{
    private SubscriptionService $subscriptionService;

    public function __construct()
    {
        $this->subscriptionService = new SubscriptionService(Database::getInstance());
        $this->subscriptionService->ensureInfrastructure();
    }

    public function cashfree(): void
    {
        $rawPayload = file_get_contents('php://input') ?: '';
        $payload = json_decode($rawPayload, true);

        if (!is_array($payload)) {
            $this->json(['success' => false, 'message' => 'Invalid payload.'], 400);
            return;
        }

        $orderId = $this->extractCashfreeOrderId($payload);
        if ($orderId === '') {
            Logger::warning('Cashfree webhook missing order id', ['payload' => $payload]);
            $this->json(['success' => false, 'message' => 'Missing order id.'], 400);
            return;
        }

        $payment = $this->subscriptionService->findPaymentByProviderOrderId($orderId);
        if (!$payment) {
            Logger::warning('Cashfree webhook payment not found', ['order_id' => $orderId]);
            $this->json(['success' => true, 'message' => 'Ignored unknown order.']);
            return;
        }

        $result = $this->subscriptionService->confirmCashfreePayment(
            $payment,
            $this->subscriptionService->getPaymentSettings(),
            // Webhooks are system-driven, so there is no authenticated admin/user actor.
            0
        );

        if (empty($result['success'])) {
            Logger::warning('Cashfree webhook verification failed', [
                'order_id' => $orderId,
                'payment_id' => $payment['id'] ?? null,
                'message' => $result['message'] ?? 'Unknown error',
            ]);
            $this->json(['success' => false, 'message' => $result['message'] ?? 'Verification failed.'], 400);
            return;
        }

        $this->json([
            'success' => true,
            'paid' => !empty($result['paid']),
            'approved' => !empty($result['approved']),
            'payment_id' => (int) ($payment['id'] ?? 0),
        ]);
    }

    private function extractCashfreeOrderId(array $payload): string
    {
        $candidates = [
            $payload['data']['order']['order_id'] ?? null,
            $payload['data']['payment']['order_id'] ?? null,
            $payload['order']['order_id'] ?? null,
            $payload['order_id'] ?? null,
            $payload['cf_order_id'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            $value = trim((string) $candidate);
            if ($value !== '') {
                return $value;
            }
        }

        return '';
    }
}
