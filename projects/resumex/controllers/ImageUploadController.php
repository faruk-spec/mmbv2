<?php
/**
 * ResumeX Image Upload Controller
 *
 * Handles profile photo / image uploads for resumes.
 * Available to any authenticated user.
 *
 * Upload endpoint: POST /projects/resumex/upload-image
 *
 * Fields:
 *   _token  — CSRF token
 *   photo   — image file (JPEG / PNG / GIF / WebP, max 5 MB)
 *
 * Success response:
 *   { "success": true, "url": "/storage/uploads/resumex/images/<filename>" }
 *
 * Error response:
 *   { "success": false, "error": "<message>" }
 *
 * @package MMB\Projects\ResumeX\Controllers
 */

namespace Projects\ResumeX\Controllers;

use Core\Auth;
use Core\Security;
use Core\Logger;

class ImageUploadController
{
    private const MAX_FILE_SIZE     = 5 * 1024 * 1024;  // 5 MB
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];

    private string $storageDir;
    private string $storageUrl;

    public function __construct()
    {
        $this->storageDir = BASE_PATH . '/storage/uploads/resumex/images';
        $this->storageUrl = '/storage/uploads/resumex/images';
        $this->ensureStorageDir();
    }

    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Handle image upload request.
     * Always responds with JSON.
     */
    public function upload(): void
    {
        header('Content-Type: application/json');

        // Auth check
        $userId = Auth::id();
        if (!$userId) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Not authenticated.']);
            exit;
        }

        // CSRF check
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'Invalid security token.']);
            exit;
        }

        // File presence check
        if (empty($_FILES['photo']) || $_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'No file was uploaded.']);
            exit;
        }

        $file = $_FILES['photo'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Upload error (code ' . $file['error'] . ').']);
            exit;
        }

        // Size check
        if ($file['size'] > self::MAX_FILE_SIZE) {
            http_response_code(413);
            echo json_encode(['success' => false, 'error' => 'Image must be smaller than 5 MB.']);
            exit;
        }

        // Extension pre-check (quick reject before finfo)
        $origExt = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($origExt, $allowedExtensions, true)) {
            http_response_code(415);
            echo json_encode(['success' => false, 'error' => 'Only JPEG, PNG, GIF, and WebP images are accepted.']);
            exit;
        }

        // MIME type check via finfo (server-side, not trusting the browser)
        $finfo    = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!array_key_exists($mimeType, self::ALLOWED_MIME_TYPES)) {
            http_response_code(415);
            echo json_encode(['success' => false, 'error' => 'Invalid image file. Accepted formats: JPEG, PNG, GIF, WebP.']);
            exit;
        }

        // Derive extension from the validated MIME type (not from user-supplied filename)
        $ext = self::ALLOWED_MIME_TYPES[$mimeType];

        // Build a safe, unique file name
        $fileName = sprintf('%d_%s_%s.%s', $userId, date('Ymd'), bin2hex(random_bytes(8)), $ext);
        $destPath = $this->storageDir . '/' . $fileName;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            Logger::error('ImageUploadController: move_uploaded_file failed for user ' . $userId);
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Could not save the image. Please try again.']);
            exit;
        }

        $url = $this->storageUrl . '/' . $fileName;

        echo json_encode(['success' => true, 'url' => $url]);
        exit;
    }

    // ──────────────────────────────────────────────────────────────────────────

    private function ensureStorageDir(): void
    {
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0755, true);
        }
    }
}
