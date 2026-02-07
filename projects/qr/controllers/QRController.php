<?php
/**
 * QR Code Controller
 * 
 * @package MMB\Projects\QR\Controllers
 */

namespace Projects\QR\Controllers;

use Core\Auth;
use Core\Security;
use Core\Helpers;
use Core\Logger;
use Projects\QR\Models\QRModel;

class QRController
{
    private QRModel $qrModel;
    
    public function __construct()
    {
        $this->qrModel = new QRModel();
    }
    
    /**
     * Show QR generation form
     */
    public function showForm(): void
    {
        $this->render('generate', [
            'title' => 'Generate QR Code',
            'user' => Auth::user()
        ]);
    }
    
    /**
     * Generate QR code with enhanced features
     */
    public function generate(): void
    {
        // Verify CSRF
        if (!Security::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Helpers::flash('error', 'Invalid request.');
            Helpers::redirect('/projects/qr/generate');
            return;
        }
        
        // Get content based on type
        $type = Security::sanitize($_POST['type'] ?? 'text');
        $content = $this->buildContent($type, $_POST);
        
        if (empty($content)) {
            Helpers::flash('error', 'Please enter content for the QR code.');
            Helpers::redirect('/projects/qr/generate');
            return;
        }
        
        // Basic settings
        $size = max(100, min(500, (int) ($_POST['size'] ?? 300)));
        $foregroundColor = '#' . ltrim(Security::sanitize($_POST['foreground_color'] ?? '000000'), '#');
        $backgroundColor = '#' . ltrim(Security::sanitize($_POST['background_color'] ?? 'ffffff'), '#');
        $errorCorrection = Security::sanitize($_POST['error_correction'] ?? 'H');
        
        // Design options
        $frameStyle = Security::sanitize($_POST['frame_style'] ?? 'none');
        
        // Advanced features
        $isDynamic = isset($_POST['is_dynamic']) ? 1 : 0;
        $redirectUrl = $isDynamic ? Security::sanitize($_POST['redirect_url'] ?? '') : null;
        $hasPassword = isset($_POST['has_password']) ? 1 : 0;
        $passwordHash = null;
        if ($hasPassword && !empty($_POST['password'])) {
            $passwordHash = password_hash($_POST['password'], PASSWORD_BCRYPT);
        }
        $hasExpiry = isset($_POST['has_expiry']) ? 1 : 0;
        $expiresAt = $hasExpiry && !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;
        $campaignId = !empty($_POST['campaign_id']) ? (int) $_POST['campaign_id'] : null;
        
        // Handle logo upload
        $logoPath = null;
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $logoPath = $this->handleLogoUpload($_FILES['logo']);
        }
        
        // Store in session for immediate display
        $_SESSION['generated_qr'] = [
            'content' => $content,
            'type' => $type,
            'size' => $size,
            'foreground_color' => $foregroundColor,
            'background_color' => $backgroundColor,
            'error_correction' => $errorCorrection,
            'frame_style' => $frameStyle,
            'is_dynamic' => $isDynamic,
            'has_password' => $hasPassword,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        // Save to database
        $userId = Auth::id();
        if ($userId) {
            $qrId = $this->qrModel->save($userId, [
                'content' => $content,
                'type' => $type,
                'size' => $size,
                'foreground_color' => $foregroundColor,
                'background_color' => $backgroundColor,
                'error_correction' => $errorCorrection,
                'frame_style' => $frameStyle,
                'logo_path' => $logoPath,
                'is_dynamic' => $isDynamic,
                'redirect_url' => $redirectUrl,
                'password_hash' => $passwordHash,
                'expires_at' => $expiresAt,
                'campaign_id' => $campaignId,
                'status' => 'active'
            ]);
            
            if ($qrId) {
                // Generate short code for dynamic QR
                if ($isDynamic) {
                    $shortCode = $this->generateShortCode($qrId);
                    $this->qrModel->updateShortCode($qrId, $shortCode);
                }
                
                Logger::activity($userId, 'qr_generated', ['type' => $type, 'qr_id' => $qrId, 'is_dynamic' => $isDynamic]);
                Helpers::flash('success', 'QR code generated successfully!' . ($isDynamic ? ' Short URL: ' . $shortCode : ''));
            } else {
                Logger::error('Failed to save QR code to database for user ' . $userId);
                Helpers::flash('error', 'Failed to save QR code to database.');
            }
        } else {
            Helpers::flash('success', 'QR code generated successfully!');
        }
        
        Helpers::redirect('/projects/qr/generate');
    }
    
    /**
     * Build QR content based on type
     */
    private function buildContent(string $type, array $data): string
    {
        switch ($type) {
            case 'url':
            case 'text':
                return Security::sanitize($data['content'] ?? '');
                
            case 'email':
                return 'mailto:' . Security::sanitize($data['content'] ?? '');
                
            case 'phone':
                return 'tel:' . Security::sanitize($data['content'] ?? '');
                
            case 'sms':
                $smsData = explode(':', $data['content'] ?? '');
                $phone = $smsData[0] ?? '';
                $message = $smsData[1] ?? '';
                return 'sms:' . $phone . ($message ? '?body=' . urlencode($message) : '');
                
            case 'whatsapp':
                $phone = preg_replace('/\D/', '', $data['whatsapp_phone'] ?? '');
                $message = $data['whatsapp_message'] ?? '';
                return 'https://wa.me/' . $phone . ($message ? '?text=' . urlencode($message) : '');
                
            case 'wifi':
                $ssid = Security::sanitize($data['wifi_ssid'] ?? '');
                $password = Security::sanitize($data['wifi_password'] ?? '');
                $encryption = Security::sanitize($data['wifi_encryption'] ?? 'WPA');
                return "WIFI:T:$encryption;S:$ssid;P:$password;;";
                
            case 'vcard':
                $name = Security::sanitize($data['vcard_name'] ?? '');
                $phone = Security::sanitize($data['vcard_phone'] ?? '');
                $email = Security::sanitize($data['vcard_email'] ?? '');
                $org = Security::sanitize($data['vcard_org'] ?? '');
                return "BEGIN:VCARD\nVERSION:3.0\nFN:$name\nTEL:$phone\nEMAIL:$email" . ($org ? "\nORG:$org" : '') . "\nEND:VCARD";
                
            case 'location':
                $lat = Security::sanitize($data['location_lat'] ?? '');
                $lng = Security::sanitize($data['location_lng'] ?? '');
                return "geo:$lat,$lng";
                
            case 'event':
                $title = Security::sanitize($data['event_title'] ?? '');
                $start = str_replace(['-', ':', ' '], '', $data['event_start'] ?? '');
                $end = str_replace(['-', ':', ' '], '', $data['event_end'] ?? '');
                $location = Security::sanitize($data['event_location'] ?? '');
                return "BEGIN:VEVENT\nSUMMARY:$title\nDTSTART:$start\nDTEND:$end" . ($location ? "\nLOCATION:$location" : '') . "\nEND:VEVENT";
                
            case 'payment':
                $payType = Security::sanitize($data['payment_type'] ?? 'upi');
                $address = Security::sanitize($data['payment_address'] ?? '');
                $amount = Security::sanitize($data['payment_amount'] ?? '');
                
                if ($payType === 'upi') {
                    return 'upi://pay?pa=' . $address . ($amount ? '&am=' . $amount : '');
                } elseif ($payType === 'paypal') {
                    return 'https://paypal.me/' . $address . ($amount ? '/' . $amount : '');
                } elseif ($payType === 'bitcoin') {
                    return 'bitcoin:' . $address . ($amount ? '?amount=' . $amount : '');
                }
                break;
                
            default:
                return Security::sanitize($data['content'] ?? '');
        }
        
        return '';
    }
    
    /**
     * Handle logo upload
     */
    private function handleLogoUpload(array $file): ?string
    {
        // Validate file
        $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($file['type'], $allowedTypes)) {
            Helpers::flash('warning', 'Logo must be PNG or JPG format.');
            return null;
        }
        
        if ($file['size'] > $maxSize) {
            Helpers::flash('warning', 'Logo file size must be less than 2MB.');
            return null;
        }
        
        // Create upload directory if it doesn't exist
        $uploadDir = __DIR__ . '/../../../storage/qr_logos/' . date('Y/m');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid('logo_') . '.' . $extension;
        $filepath = $uploadDir . '/' . $filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            // Return relative path for storage
            return '/storage/qr_logos/' . date('Y/m') . '/' . $filename;
        }
        
        Helpers::flash('warning', 'Failed to upload logo.');
        return null;
    }
    
    /**
     * Generate short code for dynamic QR
     */
    private function generateShortCode(int $qrId): string
    {
        // Generate a short alphanumeric code
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $code = '';
        for ($i = 0; $i < 6; $i++) {
            $code .= $characters[random_int(0, strlen($characters) - 1)];
        }
        
        // Prepend QR ID to ensure uniqueness
        return $code . $qrId;
    }
    
    /**
     * Show QR code history
     */
    public function history(): void
    {
        $userId = Auth::id();
        $history = [];
        
        if ($userId) {
            // Fetch QR codes from database
            $history = $this->qrModel->getByUser($userId, 50);
        }
        
        $this->render('history', [
            'title' => 'QR Code History',
            'user' => Auth::user(),
            'history' => $history
        ]);
    }
    
    /**
     * Download QR code
     */
    public function download(): void
    {
        $qr = $_SESSION['generated_qr'] ?? null;
        
        if (!$qr) {
            Helpers::flash('error', 'No QR code to download.');
            Helpers::redirect('/projects/qr/generate');
            return;
        }
        
        // Client-side handles download
        Helpers::redirect('/projects/qr/generate');
    }
    
    /**
     * Delete QR code
     */
    public function delete(): void
    {
        if (!Security::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Helpers::flash('error', 'Invalid request.');
            Helpers::redirect('/projects/qr/history');
            return;
        }
        
        $id = (int) ($_POST['id'] ?? 0);
        $userId = Auth::id();
        
        if ($id && $userId) {
            if ($this->qrModel->delete($id, $userId)) {
                Helpers::flash('success', 'QR code deleted successfully.');
            } else {
                Helpers::flash('error', 'Failed to delete QR code.');
            }
        } else {
            Helpers::flash('error', 'Invalid request.');
        }
        
        Helpers::redirect('/projects/qr/history');
    }
    
    /**
     * View QR code details
     */
    public function view(int $id): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            Helpers::flash('error', 'Please login to view QR codes.');
            Helpers::redirect('/login');
            return;
        }
        
        $qr = $this->qrModel->getById($id, $userId);
        
        if (!$qr) {
            Helpers::flash('error', 'QR code not found.');
            Helpers::redirect('/projects/qr/history');
            return;
        }
        
        $this->render('view', [
            'title' => 'View QR Code',
            'user' => Auth::user(),
            'qr' => $qr
        ]);
    }
    
    /**
     * Show edit form for dynamic QR code
     */
    public function edit(int $id): void
    {
        $userId = Auth::id();
        
        if (!$userId) {
            Helpers::flash('error', 'Please login to edit QR codes.');
            Helpers::redirect('/login');
            return;
        }
        
        $qr = $this->qrModel->getById($id, $userId);
        
        if (!$qr) {
            Helpers::flash('error', 'QR code not found.');
            Helpers::redirect('/projects/qr/history');
            return;
        }
        
        $this->render('edit', [
            'title' => 'Edit QR Code',
            'user' => Auth::user(),
            'qr' => $qr
        ]);
    }
    
    /**
     * Update dynamic QR code
     */
    public function update(int $id): void
    {
        if (!Security::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Helpers::flash('error', 'Invalid request.');
            Helpers::redirect('/projects/qr/history');
            return;
        }
        
        $userId = Auth::id();
        
        if (!$userId) {
            Helpers::flash('error', 'Please login to update QR codes.');
            Helpers::redirect('/login');
            return;
        }
        
        // Get existing QR code
        $qr = $this->qrModel->getById($id, $userId);
        
        if (!$qr) {
            Helpers::flash('error', 'QR code not found.');
            Helpers::redirect('/projects/qr/history');
            return;
        }
        
        if (!($qr['is_dynamic'] ?? false)) {
            Helpers::flash('error', 'This QR code is not dynamic and cannot be edited.');
            Helpers::redirect('/projects/qr/view/' . $id);
            return;
        }
        
        // Update data
        $updateData = [
            'redirect_url' => Security::sanitize($_POST['redirect_url'] ?? ''),
            'status' => Security::sanitize($_POST['status'] ?? 'active')
        ];
        
        // Update password if provided
        $hasPassword = isset($_POST['has_password']) ? 1 : 0;
        if ($hasPassword && !empty($_POST['password'])) {
            $updateData['password_hash'] = password_hash($_POST['password'], PASSWORD_BCRYPT);
        } elseif (!$hasPassword) {
            $updateData['password_hash'] = null;
        }
        
        // Update expiry date
        $hasExpiry = isset($_POST['has_expiry']) ? 1 : 0;
        if ($hasExpiry && !empty($_POST['expires_at'])) {
            $updateData['expires_at'] = $_POST['expires_at'];
        } elseif (!$hasExpiry) {
            $updateData['expires_at'] = null;
        }
        
        if ($this->qrModel->update($id, $userId, $updateData)) {
            Logger::activity($userId, 'qr_updated', ['qr_id' => $id]);
            Helpers::flash('success', 'QR code updated successfully!');
        } else {
            Helpers::flash('error', 'Failed to update QR code.');
        }
        
        Helpers::redirect('/projects/qr/view/' . $id);
    }
    
    /**
     * Show access form for protected/dynamic QR codes
     */
    public function showAccessForm(string $code): void
    {
        // Find QR code by short code
        $qr = $this->qrModel->getByShortCode($code);
        
        if (!$qr) {
            Helpers::flash('error', 'QR code not found.');
            http_response_code(404);
            echo "QR code not found";
            return;
        }
        
        // Check expiry first
        if ($qr['expires_at'] && strtotime($qr['expires_at']) < time()) {
            $this->render('expired', [
                'title' => 'QR Code Expired',
                'qr' => $qr
            ]);
            return;
        }
        
        // Check if password protected
        if ($qr['password_hash']) {
            $this->render('access', [
                'title' => 'Enter Password',
                'qr' => $qr,
                'code' => $code
            ]);
            return;
        }
        
        // No protection, redirect directly
        $this->redirectQR($qr);
    }
    
    /**
     * Verify access to protected QR code
     */
    public function verifyAccess(string $code): void
    {
        // Verify CSRF
        if (!Security::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Helpers::flash('error', 'Invalid request.');
            Helpers::redirect('/projects/qr/access/' . $code);
            return;
        }
        
        // Basic rate limiting: Check for too many failed attempts
        $sessionKey = 'qr_access_attempts_' . $code;
        $attempts = $_SESSION[$sessionKey] ?? 0;
        $lastAttempt = $_SESSION[$sessionKey . '_time'] ?? 0;
        
        // Reset counter after 5 minutes
        if (time() - $lastAttempt > 300) {
            $attempts = 0;
        }
        
        // Block after 5 failed attempts
        if ($attempts >= 5) {
            $waitTime = 300 - (time() - $lastAttempt);
            Helpers::flash('error', "Too many failed attempts. Please try again in " . ceil($waitTime / 60) . " minutes.");
            Helpers::redirect('/projects/qr/access/' . $code);
            return;
        }
        
        // Find QR code by short code
        $qr = $this->qrModel->getByShortCode($code);
        
        if (!$qr) {
            Helpers::flash('error', 'QR code not found.');
            Helpers::redirect('/');
            return;
        }
        
        // Check expiry
        if ($qr['expires_at'] && strtotime($qr['expires_at']) < time()) {
            Helpers::flash('error', 'This QR code has expired.');
            Helpers::redirect('/projects/qr/access/' . $code);
            return;
        }
        
        // Verify password
        if ($qr['password_hash']) {
            $password = $_POST['password'] ?? '';
            if (!password_verify($password, $qr['password_hash'])) {
                // Increment failed attempts
                $_SESSION[$sessionKey] = $attempts + 1;
                $_SESSION[$sessionKey . '_time'] = time();
                
                Helpers::flash('error', 'Incorrect password.');
                Helpers::redirect('/projects/qr/access/' . $code);
                return;
            }
            
            // Clear failed attempts on success
            unset($_SESSION[$sessionKey]);
            unset($_SESSION[$sessionKey . '_time']);
        }
        
        // Track scan
        $this->qrModel->trackScan($qr['id']);
        
        // Redirect to content
        $this->redirectQR($qr);
    }
    
    /**
     * Redirect QR code to its destination
     */
    private function redirectQR(array $qr): void
    {
        // For dynamic QR codes, use redirect_url
        if ($qr['is_dynamic'] && !empty($qr['redirect_url'])) {
            header('Location: ' . $qr['redirect_url']);
            exit;
        }
        
        // For static QR codes, redirect to content directly
        // Handle different content types
        $content = $qr['content'];
        
        // If it's already a URL, redirect
        if (filter_var($content, FILTER_VALIDATE_URL)) {
            header('Location: ' . $content);
            exit;
        }
        
        // Otherwise display the content
        $this->render('content', [
            'title' => 'QR Code Content',
            'qr' => $qr,
            'content' => $content
        ]);
    }
    
    /**
     * Render a project view
     */
    protected function render(string $view, array $data = []): void
    {
        extract($data);
        
        ob_start();
        include PROJECT_PATH . '/views/' . $view . '.php';
        $content = ob_get_clean();
        
        include PROJECT_PATH . '/views/layout.php';
    }
}
