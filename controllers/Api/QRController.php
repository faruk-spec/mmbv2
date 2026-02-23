<?php
/**
 * QR Code API Controller
 *
 * Provides a REST API for managing QR codes programmatically.
 * Supports both API-key authentication (Bearer token via api_keys table)
 * and session-based authentication for same-origin AJAX calls.
 *
 * Endpoints:
 *   GET    /api/qr                – list authenticated user's QR codes
 *   GET    /api/qr/{code}         – retrieve a single QR code by short_code or id
 *   POST   /api/qr                – create a new QR code
 *   DELETE /api/qr/{code}         – soft-delete a QR code
 *
 * All endpoints require the `api_access` feature flag (checked via QRFeatureService).
 *
 * @package MMB\Controllers\Api
 */

namespace Controllers\Api;

use Controllers\BaseController;
use Core\Auth;
use Core\Logger;
use Core\Database;
use Core\API\ApiAuth;
use Projects\QR\Models\QRModel;
use Projects\QR\Services\QRFeatureService;

class QRController extends BaseController
{
    private Database $db;
    private QRModel $qrModel;
    private QRFeatureService $featureService;
    /** @var int|null Resolved user ID (session OR API key) */
    private ?int $resolvedUserId = null;

    public function __construct()
    {
        $this->db             = Database::getInstance();
        $this->qrModel        = new QRModel();
        $this->featureService = new QRFeatureService();

        // Set JSON response header up-front.
        header('Content-Type: application/json; charset=utf-8');
    }

    // -------------------------------------------------------------------------
    // Public endpoint methods (called by Router)
    // -------------------------------------------------------------------------

    /** GET /api/qr */
    public function list(): void
    {
        $userId = $this->authenticate();
        if (!$userId) return;

        $page    = max(1, (int) ($_GET['page']  ?? 1));
        $limit   = min(100, max(1, (int) ($_GET['limit'] ?? 20)));
        $offset  = ($page - 1) * $limit;

        $qrCodes = $this->qrModel->getByUser($userId, $limit, $offset);
        $total   = $this->qrModel->countByUser($userId);

        // Strip internal columns not suitable for API consumers.
        $qrCodes = array_map([$this, 'sanitizeQRForApi'], $qrCodes);

        $this->success([
            'data'       => $qrCodes,
            'pagination' => [
                'page'        => $page,
                'limit'       => $limit,
                'total'       => $total,
                'total_pages' => (int) ceil($total / $limit),
            ],
        ]);
    }

    /** GET /api/qr/{code} */
    public function show(string $code): void
    {
        $userId = $this->authenticate();
        if (!$userId) return;

        $qr = $this->getQRByCodeOrId($code, $userId);
        if (!$qr) {
            $this->error('QR code not found', 404);
            return;
        }

        $this->success(['data' => $this->sanitizeQRForApi($qr)]);
    }

    /** POST /api/qr */
    public function create(): void
    {
        $userId = $this->authenticate();
        if (!$userId) return;

        // Check api_access feature flag.
        if (!$this->featureService->can($userId, 'api_access')) {
            $this->error('API access is not enabled on your plan.', 403);
            return;
        }

        $body = $this->parseJsonBody();
        if ($body === null) {
            $this->error('Invalid JSON body', 400);
            return;
        }

        $type    = trim($body['type'] ?? 'url');
        $content = trim($body['content'] ?? '');

        if (empty($content)) {
            $this->error('content is required', 422);
            return;
        }

        // Map type → allowed list (mirrors QRController::buildContent logic).
        $allowedTypes = [
            'url','text','email','phone','sms','wifi','vcard','location',
            'whatsapp','skype','zoom','paypal','payment','social','app_store','crypto','menu',
        ];
        if (!in_array($type, $allowedTypes, true)) {
            $this->error('Unsupported QR type: ' . $type, 422);
            return;
        }

        $isDynamic = !empty($body['dynamic']);
        if ($isDynamic && !$this->featureService->can($userId, 'dynamic_qr')) {
            $this->error('Dynamic QR codes are not available on your plan.', 403);
            return;
        }

        try {
            $qrId = $this->qrModel->save($userId, [
                'content'           => $content,
                'type'              => $type,
                'is_dynamic'        => $isDynamic ? 1 : 0,
                'redirect_url'      => $isDynamic ? $content : null,
                'note'              => substr(trim($body['label'] ?? ''), 0, 255),
                'size'              => min(1000, max(100, (int) ($body['size'] ?? 300))),
                'foreground_color'  => $this->sanitizeColor($body['fg_color'] ?? '#000000'),
                'background_color'  => $this->sanitizeColor($body['bg_color'] ?? '#FFFFFF'),
                'error_correction'  => in_array($body['error_correction'] ?? 'M', ['L','M','Q','H'])
                                         ? strtoupper($body['error_correction'])
                                         : 'M',
            ]);
        } catch (\Exception $e) {
            Logger::error('QR API create error: ' . $e->getMessage());
            $this->error('Failed to create QR code', 500);
            return;
        }

        if (!$qrId) {
            $this->error('Failed to create QR code', 500);
            return;
        }

        Logger::activity($userId, 'api_qr_created', ['qr_id' => $qrId, 'type' => $type]);

        $qr = $this->qrModel->getById((int) $qrId, $userId);
        $this->success(['data' => $this->sanitizeQRForApi($qr)], 201);
    }

    /** DELETE /api/qr/{code} */
    public function delete(string $code): void
    {
        $userId = $this->authenticate();
        if (!$userId) return;

        $qr = $this->getQRByCodeOrId($code, $userId);
        if (!$qr) {
            $this->error('QR code not found', 404);
            return;
        }

        $deleted = $this->qrModel->delete((int) $qr['id'], $userId);
        if (!$deleted) {
            $this->error('Failed to delete QR code', 500);
            return;
        }

        Logger::activity($userId, 'api_qr_deleted', ['qr_id' => $qr['id']]);
        $this->success(['message' => 'QR code deleted successfully']);
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    /**
     * Authenticate the request.
     * Accepts:
     *   1. Authorization: Bearer <api_key>
     *   2. X-Api-Key: <api_key> header
     *   3. ?api_key=<key> query param
     *   4. Active session (same-origin AJAX)
     *
     * Returns the resolved user ID on success, null + JSON error on failure.
     */
    private function authenticate(): ?int
    {
        // Extract API key from various sources.
        $apiKey = $this->extractApiKey();

        if ($apiKey) {
            // Validate against api_keys table.
            if (!ApiAuth::validateKey($apiKey)) {
                $this->error('Invalid or expired API key', 401);
                return null;
            }
            $userId = $this->getUserIdFromApiKey($apiKey);
            if (!$userId) {
                $this->error('API key is not associated with a user', 401);
                return null;
            }
            return $userId;
        }

        // Fall back to session authentication.
        if (Auth::check()) {
            return Auth::id();
        }

        $this->error('Authentication required. Provide an API key or log in.', 401);
        return null;
    }

    private function extractApiKey(): ?string
    {
        // Authorization: Bearer <token>
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (str_starts_with($authHeader, 'Bearer ')) {
            return trim(substr($authHeader, 7));
        }
        // X-Api-Key header
        if (!empty($_SERVER['HTTP_X_API_KEY'])) {
            return $_SERVER['HTTP_X_API_KEY'];
        }
        // Query parameter (least preferred)
        if (!empty($_GET['api_key'])) {
            return $_GET['api_key'];
        }
        return null;
    }

    private function getUserIdFromApiKey(string $apiKey): ?int
    {
        try {
            $row = $this->db->fetch(
                "SELECT user_id FROM api_keys WHERE api_key = ? AND is_active = 1 LIMIT 1",
                [$apiKey]
            );
            return $row ? (int) $row['user_id'] : null;
        } catch (\Exception $e) {
            Logger::error('QR API getUserIdFromApiKey: ' . $e->getMessage());
            return null;
        }
    }

    private function getQRByCodeOrId(string $code, int $userId): ?array
    {
        // Try short_code first.
        try {
            $qr = $this->db->fetch(
                "SELECT * FROM qr_codes WHERE short_code = ? AND user_id = ? AND deleted_at IS NULL",
                [$code, $userId]
            );
            if ($qr) return $qr;
        } catch (\Exception $e) {
            // Fall through.
        }

        // Try numeric ID.
        if (ctype_digit($code)) {
            return $this->qrModel->getById((int) $code, $userId);
        }

        return null;
    }

    private function sanitizeQRForApi(array $qr): array
    {
        // Remove sensitive internal fields.
        unset($qr['password_hash'], $qr['deleted_at']);

        // Add convenience scan URL.
        $baseUrl = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? '');
        $qr['scan_url'] = $qr['short_code']
            ? $baseUrl . '/qr/' . $qr['short_code']
            : null;

        return $qr;
    }

    private function parseJsonBody(): ?array
    {
        $raw = file_get_contents('php://input');
        if (empty($raw)) {
            // Fall back to POST data.
            return $_POST ?: [];
        }
        $decoded = json_decode($raw, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function sanitizeColor(string $color): string
    {
        // Accept #RGB and #RRGGBB only.
        return preg_match('/^#[0-9A-Fa-f]{3}([0-9A-Fa-f]{3})?$/', $color) ? strtoupper($color) : '#000000';
    }

    private function success(array $data, int $code = 200): void
    {
        http_response_code($code);
        echo json_encode(array_merge(['success' => true], $data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    private function error(string $message, int $code = 400): void
    {
        http_response_code($code);
        echo json_encode(['success' => false, 'error' => $message], JSON_UNESCAPED_UNICODE);
        exit;
    }
}
