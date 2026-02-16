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
use Projects\QR\Models\SettingsModel;

class QRController
{
    private QRModel $qrModel;
    private SettingsModel $settingsModel;
    
    public function __construct()
    {
        $this->qrModel = new QRModel();
        $this->settingsModel = new SettingsModel();
    }
    
    /**
     * Show QR generation form
     */
    public function showForm(): void
    {
        $userId = Auth::id();
        $settings = [];
        
        // Load user settings if logged in
        if ($userId) {
            $settings = $this->settingsModel->get($userId);
        }
        
        $this->render('generate', [
            'title' => 'Generate QR Code',
            'user' => Auth::user(),
            'settings' => $settings
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
        
        // Gradient settings
        $gradientEnabled = isset($_POST['gradient_enabled']) ? 1 : 0;
        $gradientColor = '#' . ltrim(Security::sanitize($_POST['gradient_color'] ?? '9945ff'), '#');
        $transparentBg = isset($_POST['transparent_bg']) ? 1 : 0;
        
        // Design styles
        $cornerStyle = Security::sanitize($_POST['corner_style'] ?? 'square');
        $dotStyle = Security::sanitize($_POST['dot_style'] ?? 'dots');
        $markerBorderStyle = Security::sanitize($_POST['marker_border_style'] ?? 'square');
        $markerCenterStyle = Security::sanitize($_POST['marker_center_style'] ?? 'square');
        
        // Marker colors
        $customMarkerColor = isset($_POST['custom_marker_color']) ? 1 : 0;
        $markerColor = $customMarkerColor ? '#' . ltrim(Security::sanitize($_POST['marker_color'] ?? '9945ff'), '#') : null;
        
        // Frame options
        $frameStyle = Security::sanitize($_POST['frame_style'] ?? 'none');
        $frameLabel = Security::sanitize($_POST['frame_label'] ?? '');
        $frameFont = Security::sanitize($_POST['frame_font'] ?? '');
        $frameColor = !empty($_POST['frame_color']) ? '#' . ltrim(Security::sanitize($_POST['frame_color']), '#') : null;
        
        // Logo options
        $logoColor = '#' . ltrim(Security::sanitize($_POST['logo_color'] ?? '9945ff'), '#');
        $logoSize = !empty($_POST['logo_size']) ? (float) $_POST['logo_size'] : 0.3;
        $logoRemoveBg = isset($_POST['logo_remove_bg']) ? 1 : 0;
        
        // Advanced features
        $isDynamic = isset($_POST['is_dynamic']) ? 1 : 0;
        $redirectUrl = $isDynamic ? Security::sanitize($_POST['redirect_url'] ?? '') : null;
        
        // Campaign (can come from URL or form)
        $campaignId = !empty($_POST['campaign_id']) ? (int) $_POST['campaign_id'] : null;
        if (!$campaignId && !empty($_GET['campaign_id'])) {
            $campaignId = (int) $_GET['campaign_id'];
        }
        
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
            'gradient_enabled' => $gradientEnabled,
            'gradient_color' => $gradientColor,
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
                'gradient_enabled' => $gradientEnabled,
                'gradient_color' => $gradientColor,
                'transparent_bg' => $transparentBg,
                'corner_style' => $cornerStyle,
                'dot_style' => $dotStyle,
                'marker_border_style' => $markerBorderStyle,
                'marker_center_style' => $markerCenterStyle,
                'custom_marker_color' => $customMarkerColor,
                'marker_color' => $markerColor,
                'frame_style' => $frameStyle,
                'frame_label' => $frameLabel,
                'frame_font' => $frameFont,
                'frame_color' => $frameColor,
                'logo_path' => $logoPath,
                'logo_color' => $logoColor,
                'logo_size' => $logoSize,
                'logo_remove_bg' => $logoRemoveBg,
                'is_dynamic' => $isDynamic,
                'redirect_url' => $redirectUrl,
                'campaign_id' => $campaignId,
                'status' => 'active'
            ]);
            
            if ($qrId) {
                // Generate short code for dynamic QR codes
                if ($isDynamic) {
                    $shortCode = $this->generateShortCode($qrId);
                    $this->qrModel->updateShortCode($qrId, $shortCode);
                    
                    // Build access URL
                    $accessUrl = 'https://' . $_SERVER['HTTP_HOST'] . '/projects/qr/access/' . $shortCode;
                    
                    // Update the content field to the access URL for dynamic QRs
                    $this->qrModel->update($qrId, $userId, ['content' => $accessUrl]);
                    // Update session content so the displayed QR has the correct access URL
                    $_SESSION['generated_qr']['content'] = $accessUrl;
                    
                    // Update session with short code URL for display
                    $_SESSION['generated_qr']['short_code'] = $shortCode;
                    $_SESSION['generated_qr']['access_url'] = $accessUrl;
                }
                
                Logger::activity($userId, 'qr_generated', ['type' => $type, 'qr_id' => $qrId, 'is_dynamic' => $isDynamic, 'campaign_id' => $campaignId]);
                
                $message = 'QR code generated successfully!';
                if ($isDynamic && isset($shortCode)) {
                    $message .= ' Access URL: https://' . $_SERVER['HTTP_HOST'] . '/projects/qr/access/' . $shortCode;
                }
                Helpers::flash('success', $message);
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
        $totalCount = 0;
        $perPage = (int)($_GET['per_page'] ?? 10);
        $page = max(1, (int)($_GET['page'] ?? 1));
        $offset = ($page - 1) * $perPage;
        
        // Validate per_page value
        if (!in_array($perPage, [10, 25, 50, 100])) {
            $perPage = 10;
        }
        
        if ($userId) {
            // Fetch QR codes from database with pagination
            $history = $this->qrModel->getByUser($userId, $perPage, $offset);
            
            // Get total count for pagination
            $totalCount = $this->qrModel->countByUser($userId);
        }
        
        $totalPages = $totalCount > 0 ? (int)ceil($totalCount / $perPage) : 1;
        
        $this->render('history', [
            'title' => 'QR Code History',
            'user' => Auth::user(),
            'history' => $history,
            'totalCount' => $totalCount,
            'perPage' => $perPage,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'offset' => $offset
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
     * Bulk delete QR codes
     */
    public function bulkDelete(): void
    {
        if (!Security::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Helpers::flash('error', 'Invalid request.');
            Helpers::redirect('/projects/qr/history');
            return;
        }
        
        $ids = $_POST['qr_ids'] ?? [];
        $userId = Auth::id();
        
        if (empty($ids) || !is_array($ids)) {
            Helpers::flash('error', 'No QR codes selected.');
            Helpers::redirect('/projects/qr/history');
            return;
        }
        
        $deleted = 0;
        foreach ($ids as $id) {
            if ($this->qrModel->delete((int)$id, $userId)) {
                $deleted++;
            }
        }
        
        if ($deleted > 0) {
            Helpers::flash('success', "$deleted QR code(s) deleted successfully.");
        } else {
            Helpers::flash('error', 'Failed to delete QR codes.');
        }
        
        Helpers::redirect('/projects/qr/history');
    }
    
    /**
     * Update QR code campaign assignment
     */
    public function updateCampaign(): void
    {
        if (!Security::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            return;
        }
        
        $qrId = (int)($_POST['qr_id'] ?? 0);
        $campaignId = !empty($_POST['campaign_id']) ? (int)$_POST['campaign_id'] : null;
        $userId = Auth::id();
        
        if (!$qrId || !$userId) {
            echo json_encode(['success' => false, 'message' => 'Invalid request.']);
            return;
        }
        
        // Verify QR code belongs to user
        $qr = $this->qrModel->getById($qrId, $userId);
        if (!$qr) {
            echo json_encode(['success' => false, 'message' => 'QR code not found.']);
            return;
        }
        
        // Update campaign
        if ($this->qrModel->update($qrId, $userId, ['campaign_id' => $campaignId])) {
            echo json_encode(['success' => true, 'message' => 'Campaign updated successfully.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update campaign.']);
        }
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
        
        if ($this->qrModel->update($id, $userId, $updateData)) {
            Logger::activity($userId, 'qr_updated', ['qr_id' => $id]);
            Helpers::flash('success', 'QR code updated successfully!');
        } else {
            Helpers::flash('error', 'Failed to update QR code.');
        }
        
        Helpers::redirect('/projects/qr/view/' . $id);
    }
    
    /**
     * Show access form for dynamic QR codes
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
        
        // No protection, redirect directly
        $this->redirectQR($qr);
    }
    
    /**
     * Verify access to QR code (kept for backward compatibility but not used)
     */
    public function verifyAccess(string $code): void
    {
        // Simply redirect to the QR code
        $this->showAccessForm($code);
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
