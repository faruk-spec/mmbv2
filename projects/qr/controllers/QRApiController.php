<?php
/**
 * QR REST API Controller
 *
 * Provides a developer API for programmatic QR code generation and management.
 *
 * Authentication: API key via X-Api-Key header or ?api_key= query param.
 * This controller NEVER redirects to a login page or outputs HTML — all responses
 * are JSON.  Session/SSO auth is intentionally bypassed for these routes; the
 * QR project index.php skips the SSO guard when it detects an /api/* path.
 *
 * Routes (handled in projects/qr/routes/web.php, case 'api'):
 *   GET    /projects/qr/api                 → index()         – API docs
 *   POST   /projects/qr/api/generate        → generate()      – create QR code
 *   GET    /projects/qr/api/list            → list()          – list QR codes
 *   GET    /projects/qr/api/view/{id}       → view(id)        – get one QR code
 *   DELETE /projects/qr/api/delete/{id}     → delete(id)      – delete QR code
 *   GET    /projects/qr/api/usage           → usage()         – usage summary
 *   GET    /projects/qr/api/plans           → plans()         – available plans
 *
 * @package MMB\Projects\QR\Controllers
 */

namespace Projects\QR\Controllers;

use Core\Database;
use Core\Logger;
use Core\Security;
use Projects\QR\Models\QRModel;
use Projects\QR\Services\QRFeatureService;

class QRApiController
{
    private const API_VERSION        = 'v1';
    private const RATE_LIMIT_PER_MIN = 60;

    private QRModel        $qrModel;
    private QRFeatureService $featureService;

    /** Resolved API key row from DB (set by authenticateApiKey) */
    private ?array $currentKeyRow = null;

    /** Request start time (microtime) for latency tracking */
    private float $requestStart;

    public function __construct()
    {
        $this->requestStart = microtime(true);
        ob_start(); // swallow any stray output before we send JSON headers
        $this->qrModel        = new QRModel();
        $this->featureService = new QRFeatureService();
        ob_end_clean();

        header('Content-Type: application/json');
        header('X-Api-Version: ' . self::API_VERSION);
    }

    // ------------------------------------------------------------------ //
    //  Documentation index                                                 //
    // ------------------------------------------------------------------ //

    public function index(): void
    {
        echo json_encode([
            'api'       => 'QR Generator',
            'version'   => self::API_VERSION,
            'endpoints' => [
                'POST   /projects/qr/api/generate'    => 'Generate a new QR code',
                'GET    /projects/qr/api/list'         => 'List your QR codes (paginated)',
                'GET    /projects/qr/api/view/{id}'    => 'Get details of a QR code',
                'DELETE /projects/qr/api/delete/{id}'  => 'Delete a QR code',
                'GET    /projects/qr/api/usage'        => 'Show usage summary',
                'GET    /projects/qr/api/plans'        => 'List available subscription plans',
            ],
        ]);
    }

    // ------------------------------------------------------------------ //
    //  Generate QR code                                                    //
    // ------------------------------------------------------------------ //

    public function generate(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->error('Method not allowed', 405);
            return;
        }

        $userId = $this->authenticate();
        if (!$userId) {
            return;
        }

        if (!$this->checkRateLimit($userId)) {
            return;
        }

        // Parse JSON body or fall back to POST fields
        $body = $this->parseBody();

        $content = trim((string) ($body['content'] ?? ''));
        if ($content === '') {
            $this->error('content is required', 400);
            return;
        }

        $type = Security::sanitize($body['type'] ?? 'url');

        // Feature checks — always return JSON, never redirect
        $isDynamic = !empty($body['is_dynamic']);
        if ($isDynamic && !$this->featureService->can($userId, 'dynamic_qr')) {
            $this->error('Dynamic QR codes are not available on your current plan. Please upgrade.', 403);
            return;
        }
        if (!empty($body['password']) && !$this->featureService->can($userId, 'password_protection')) {
            $this->error('Password protection is not available on your current plan. Please upgrade.', 403);
            return;
        }
        if (!empty($body['expires_at']) && !$this->featureService->can($userId, 'expiry_date')) {
            $this->error('Expiry date is not available on your current plan. Please upgrade.', 403);
            return;
        }

        // Plan limits
        $limits = $this->featureService->getPlanLimits($userId);
        if ($limits) {
            if ($isDynamic) {
                $max = (int) $limits['max_dynamic_qr'];
                if ($max !== -1 && $this->qrModel->countDynamicByUser($userId) >= $max) {
                    $this->error("Plan limit reached: maximum {$max} dynamic QR code(s) allowed.", 403);
                    return;
                }
            } else {
                $max = (int) $limits['max_static_qr'];
                if ($max !== -1 && $this->qrModel->countStaticByUser($userId) >= $max) {
                    $this->error("Plan limit reached: maximum {$max} static QR code(s) allowed.", 403);
                    return;
                }
            }
        }

        // Validate redirect URL for dynamic QR
        $redirectUrl = null;
        if ($isDynamic) {
            $redirectUrl = trim((string) ($body['redirect_url'] ?? ''));
            if ($redirectUrl === '') {
                $redirectUrl = $content; // default redirect to the content URL
            }
            if (!filter_var($redirectUrl, FILTER_VALIDATE_URL) ||
                !in_array(strtolower(parse_url($redirectUrl, PHP_URL_SCHEME) ?? ''), ['http', 'https'], true)) {
                $this->error('redirect_url must be a valid http or https URL.', 400);
                return;
            }
        }

        // Password
        $passwordHash = null;
        $rawPassword  = (string) ($body['password'] ?? '');
        if ($rawPassword !== '') {
            if (strlen($rawPassword) < 4) {
                $this->error('Password must be at least 4 characters.', 400);
                return;
            }
            $passwordHash = password_hash($rawPassword, PASSWORD_DEFAULT);
        }

        // Expiry
        $expiresAt = null;
        $rawExpiry = trim((string) ($body['expires_at'] ?? ''));
        if ($rawExpiry !== '') {
            $ts = strtotime($rawExpiry);
            if ($ts === false || $ts <= time()) {
                $this->error('expires_at must be a valid future date/time.', 400);
                return;
            }
            $expiresAt = date('Y-m-d H:i:s', $ts);
        }

        // Error correction
        $errorCorrection = strtoupper((string) ($body['error_correction'] ?? 'H'));
        if (!in_array($errorCorrection, ['L', 'M', 'Q', 'H'], true)) {
            $errorCorrection = 'H';
        }

        $size = max(100, min(500, (int) ($body['size'] ?? 300)));

        $data = [
            'content'          => $content,
            'type'             => $type,
            'size'             => $size,
            'foreground_color' => '#' . ltrim(Security::sanitize((string) ($body['foreground_color'] ?? '000000')), '#'),
            'background_color' => '#' . ltrim(Security::sanitize((string) ($body['background_color'] ?? 'ffffff')), '#'),
            'error_correction' => $errorCorrection,
            'gradient_enabled' => empty($body['gradient_enabled']) ? 0 : 1,
            'gradient_color'   => '#' . ltrim(Security::sanitize((string) ($body['gradient_color'] ?? '9945ff')), '#'),
            'transparent_bg'   => empty($body['transparent_bg']) ? 0 : 1,
            'corner_style'     => Security::sanitize((string) ($body['corner_style'] ?? 'square')),
            'dot_style'        => Security::sanitize((string) ($body['dot_style'] ?? 'dots')),
            'marker_border_style' => Security::sanitize((string) ($body['marker_border_style'] ?? 'square')),
            'marker_center_style' => Security::sanitize((string) ($body['marker_center_style'] ?? 'square')),
            'custom_marker_color' => empty($body['custom_marker_color']) ? 0 : 1,
            'marker_color'     => null,
            'frame_style'      => Security::sanitize((string) ($body['frame_style'] ?? 'none')),
            'frame_label'      => Security::sanitize((string) ($body['frame_label'] ?? '')),
            'frame_font'       => Security::sanitize((string) ($body['frame_font'] ?? '')),
            'frame_color'      => null,
            'logo_path'        => null,
            'logo_color'       => '#' . ltrim(Security::sanitize((string) ($body['logo_color'] ?? '9945ff')), '#'),
            'logo_size'        => max(0.1, min(0.5, (float) ($body['logo_size'] ?? 0.3))),
            'logo_remove_bg'   => 0,
            'is_dynamic'       => $isDynamic ? 1 : 0,
            'redirect_url'     => $redirectUrl,
            'password_hash'    => $passwordHash,
            'expires_at'       => $expiresAt,
            'campaign_id'      => null,
            'note'             => Security::sanitize((string) ($body['label'] ?? '')),
            'scan_limit'       => !empty($body['scan_limit']) && (int) $body['scan_limit'] > 0
                ? (int) $body['scan_limit'] : null,
        ];

        try {
            $qrId = $this->qrModel->save($userId, $data);
            if (!$qrId) {
                $this->error('Failed to save QR code', 500);
                return;
            }

            // Generate short code for dynamic / password / expiry QRs
            $shortCode  = null;
            $accessUrl  = null;
            $displayContent = $content;

            $needsAccessUrl = ($passwordHash !== null || $expiresAt !== null || $data['scan_limit'] !== null)
                && !$isDynamic;

            if ($isDynamic || $needsAccessUrl) {
                $shortCode = $this->generateShortCode($qrId);
                $this->qrModel->updateShortCode($qrId, $shortCode);
                $accessUrl = (defined('APP_URL') ? APP_URL : '') . '/projects/qr/access/' . $shortCode;
                $this->qrModel->update($qrId, $userId, ['content' => $accessUrl]);
                $displayContent = $accessUrl;
            }

            http_response_code(201);
            echo json_encode([
                'success'    => true,
                'qr_id'      => $qrId,
                'content'    => $displayContent,
                'type'       => $type,
                'is_dynamic' => (bool) $isDynamic,
                'short_code' => $shortCode,
                'access_url' => $accessUrl,
                'created_at' => date('Y-m-d H:i:s'),
            ]);
            $this->logApiRequest($userId, 'generate', 201);
        } catch (\Exception $e) {
            Logger::error('QR API generate: ' . $e->getMessage());
            $this->error('Failed to generate QR code', 500);
        }
    }

    // ------------------------------------------------------------------ //
    //  List QR codes                                                       //
    // ------------------------------------------------------------------ //

    public function list(): void
    {
        $userId = $this->authenticate();
        if (!$userId) {
            return;
        }

        $page    = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = max(1, min(100, (int) ($_GET['per_page'] ?? 20)));
        $offset  = ($page - 1) * $perPage;

        $items = $this->qrModel->getByUser($userId, $perPage, $offset);
        $total = $this->qrModel->countByUser($userId);

        echo json_encode([
            'success'  => true,
            'qr_codes' => $items,
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
        ]);
        $this->logApiRequest($userId, 'list', 200);
    }

    // ------------------------------------------------------------------ //
    //  View a single QR code                                               //
    // ------------------------------------------------------------------ //

    public function view(string $qrId): void
    {
        $userId = $this->authenticate();
        if (!$userId) {
            return;
        }

        $id = (int) $qrId;
        if (!$id) {
            $this->error('Invalid QR code ID', 400);
            return;
        }

        $qr = $this->qrModel->getById($id, $userId);
        if (!$qr) {
            $this->error('QR code not found', 404);
            return;
        }

        echo json_encode(['success' => true, 'qr_code' => $qr]);
        $this->logApiRequest($userId, 'view', 200);
    }

    // ------------------------------------------------------------------ //
    //  Delete a QR code                                                    //
    // ------------------------------------------------------------------ //

    public function delete(string $qrId): void
    {
        if (!in_array($_SERVER['REQUEST_METHOD'], ['DELETE', 'POST'], true)) {
            $this->error('Method not allowed', 405);
            return;
        }

        $userId = $this->authenticate();
        if (!$userId) {
            return;
        }

        $id = (int) $qrId;
        if (!$id) {
            $this->error('Invalid QR code ID', 400);
            return;
        }

        $qr = $this->qrModel->getById($id, $userId);
        if (!$qr) {
            $this->error('QR code not found', 404);
            return;
        }

        $this->qrModel->delete($id, $userId);
        echo json_encode(['success' => true, 'message' => 'QR code deleted.']);
        $this->logApiRequest($userId, 'delete', 200);
    }

    // ------------------------------------------------------------------ //
    //  Usage summary                                                       //
    // ------------------------------------------------------------------ //

    public function usage(): void
    {
        $userId = $this->authenticate();
        if (!$userId) {
            return;
        }

        $stats = $this->qrModel->getScanStats($userId);

        echo json_encode([
            'success'       => true,
            'period'        => date('Y-m'),
            'total_qr'      => $this->qrModel->countByUser($userId),
            'total_static'  => $this->qrModel->countStaticByUser($userId),
            'total_dynamic' => $this->qrModel->countDynamicByUser($userId),
            'scans'         => $stats,
        ]);
        $this->logApiRequest($userId, 'usage', 200);
    }

    // ------------------------------------------------------------------ //
    //  List plans                                                          //
    // ------------------------------------------------------------------ //

    public function plans(): void
    {
        try {
            $db    = Database::getInstance();
            $plans = $db->fetchAll(
                "SELECT name, slug, description, price, billing_cycle, features, is_default
                   FROM qr_subscription_plans
                  WHERE status = 'active'
                  ORDER BY sort_order ASC, price ASC"
            ) ?: [];

            // Decode features JSON for each plan
            foreach ($plans as &$plan) {
                if (isset($plan['features']) && is_string($plan['features'])) {
                    $decoded = json_decode($plan['features'], true);
                    $plan['features'] = is_array($decoded) ? $decoded : [];
                }
            }
            unset($plan);
        } catch (\Exception $e) {
            $plans = [];
        }

        echo json_encode(['success' => true, 'plans' => $plans]);
    }

    // ------------------------------------------------------------------ //
    //  Auth helpers                                                        //
    // ------------------------------------------------------------------ //

    /**
     * Validate API key from X-Api-Key header (or Authorization: Bearer / ?api_key=).
     * Also enforces the api_access feature flag for the owning user.
     *
     * Returns the user ID on success, or null after sending a JSON error.
     * This method NEVER redirects or outputs HTML.
     */
    private function authenticate(): ?int
    {
        $key = $_SERVER['HTTP_X_API_KEY'] ?? null;

        if (!$key) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
            if (str_starts_with($auth, 'Bearer ')) {
                $key = substr($auth, 7);
            }
        }

        if (!$key && isset($_GET['api_key'])) {
            $key = trim($_GET['api_key']);
        }

        if (!$key) {
            $this->error('API key required. Send X-Api-Key header or ?api_key= parameter.', 401);
            return null;
        }

        try {
            $db  = Database::getInstance();
            $row = $db->fetch(
                "SELECT id, user_id FROM api_keys WHERE api_key = ? AND is_active = 1 LIMIT 1",
                [$key]
            );

            if (!$row) {
                $this->error('Invalid or inactive API key.', 401);
                return null;
            }

            $userId = (int) $row['user_id'];

            // Store row for logging (key id, masked prefix)
            $this->currentKeyRow = [
                'id'         => (int) $row['id'],
                'user_id'    => $userId,
                'key_prefix' => substr($key, 0, 8),
            ];

            // Enforce the api_access feature flag — return JSON error, never redirect
            if (!$this->featureService->can($userId, 'api_access')) {
                $this->error('API access is not available on your current plan. Please upgrade.', 403);
                return null;
            }

            // Update last-used timestamp (best-effort)
            try {
                $db->query(
                    "UPDATE api_keys SET last_used_at = NOW(), request_count = request_count + 1
                      WHERE api_key = ? LIMIT 1",
                    [$key]
                );
            } catch (\Exception $e) {
                // Non-fatal
            }

            return $userId;
        } catch (\Exception $e) {
            Logger::error('QR API auth: ' . $e->getMessage());
            $this->error('Authentication error. Please try again.', 500);
            return null;
        }
    }

    /**
     * DB-based per-key per-minute rate limiter.
     * Falls back to allow on DB error to avoid blocking legitimate traffic.
     */
    private function checkRateLimit(int $userId): bool
    {
        try {
            $db    = Database::getInstance();
            $count = (int) $db->fetchColumn(
                "SELECT COUNT(*) FROM qr_api_request_logs
                  WHERE user_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 60 SECOND)",
                [$userId]
            );
            if ($count >= self::RATE_LIMIT_PER_MIN) {
                http_response_code(429);
                header('Retry-After: 60');
                echo json_encode(['success' => false, 'error' => 'Rate limit exceeded. Maximum ' . self::RATE_LIMIT_PER_MIN . ' requests per minute.']);
                return false;
            }
        } catch (\Exception $e) {
            Logger::error('QR API rate-limit check failed: ' . $e->getMessage());
            // Fail-open on DB error to avoid blocking legitimate traffic
        }
        return true;
    }

    private function error(string $message, int $code = 400): void
    {
        http_response_code($code);
        echo json_encode(['success' => false, 'error' => $message]);
    }

    /**
     * Log an API request to qr_api_request_logs.
     * All fields are captured automatically. Errors are swallowed.
     */
    private function logApiRequest(int $userId, string $action, int $statusCode): void
    {
        try {
            $db = Database::getInstance();

            $db->query(
                "CREATE TABLE IF NOT EXISTS qr_api_request_logs (
                    id              BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    user_id         INT UNSIGNED    NOT NULL,
                    api_key_id      INT UNSIGNED    NULL,
                    api_key_prefix  VARCHAR(16)     NOT NULL DEFAULT '',
                    session_id      VARCHAR(128)    NOT NULL DEFAULT '',
                    email           VARCHAR(255)    NOT NULL DEFAULT '',
                    endpoint        VARCHAR(255)    NOT NULL,
                    method          VARCHAR(10)     NOT NULL,
                    ip_address      VARCHAR(45)     NOT NULL,
                    user_agent      TEXT            NULL,
                    status_code     SMALLINT UNSIGNED NOT NULL,
                    response_time   INT UNSIGNED    NOT NULL DEFAULT 0 COMMENT 'Milliseconds',
                    action          VARCHAR(100)    NOT NULL DEFAULT '',
                    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_user_id  (user_id),
                    INDEX idx_created  (created_at),
                    INDEX idx_status   (status_code)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
            );

            $responseTime = (int) round((microtime(true) - $this->requestStart) * 1000);

            $email = '';
            try {
                $uRow  = $db->fetch("SELECT email FROM users WHERE id = ? LIMIT 1", [$userId]);
                $email = $uRow['email'] ?? '';
            } catch (\Exception $e) {
                // ignore
            }

            $db->query(
                "INSERT INTO qr_api_request_logs
                    (user_id, api_key_id, api_key_prefix, session_id, email,
                     endpoint, method, ip_address, user_agent,
                     status_code, response_time, action)
                 VALUES
                    (?, ?, ?, ?, ?,  ?, ?, ?, ?,  ?, ?, ?)",
                [
                    $userId,
                    $this->currentKeyRow['id'] ?? null,
                    $this->currentKeyRow['key_prefix'] ?? '',
                    substr(session_id() ?: '', 0, 128),
                    substr($email, 0, 255),
                    substr(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '', 0, 255),
                    strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
                    substr($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '', 0, 45),
                    substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 512),
                    $statusCode,
                    $responseTime,
                    substr($action, 0, 100),
                ]
            );
        } catch (\Exception $e) {
            Logger::error('QR API log write failed: ' . $e->getMessage());
        }
    }

    // ------------------------------------------------------------------ //
    //  Helpers                                                             //
    // ------------------------------------------------------------------ //

    /**
     * Parse request body: JSON or form-encoded POST fields.
     */
    private function parseBody(): array
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $raw = file_get_contents('php://input');
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }
        return $_POST;
    }

    /**
     * Generate a unique short code for a QR code ID.
     * Uses the same approach as QRController::generateShortCode(): 6 random
     * alphanumeric chars + the QR ID as suffix for guaranteed uniqueness.
     */
    private function generateShortCode(int $qrId): string
    {
        $chars  = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code   = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code . $qrId;
    }
}
