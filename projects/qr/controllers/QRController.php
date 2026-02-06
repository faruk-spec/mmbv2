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

class QRController
{
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
     * Generate QR code
     */
    public function generate(): void
    {
        // Verify CSRF
        if (!Security::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Helpers::flash('error', 'Invalid request.');
            Helpers::redirect('/projects/qr/generate');
            return;
        }
        
        $content = Security::sanitize($_POST['content'] ?? '');
        $type = Security::sanitize($_POST['type'] ?? 'text');
        $size = (int) ($_POST['size'] ?? 200);
        $color = Security::sanitize($_POST['color'] ?? '000000');
        $bgColor = Security::sanitize($_POST['bg_color'] ?? 'ffffff');
        
        if (empty($content)) {
            Helpers::flash('error', 'Please enter content for the QR code.');
            Helpers::redirect('/projects/qr/generate');
            return;
        }
        
        // Validate size
        $size = max(100, min(500, $size));
        
        // Generate QR code using Google Charts API (simple implementation)
        $qrData = $this->generateQRCode($content, $size, $color, $bgColor);
        
        if ($qrData) {
            // Store in session for display
            $_SESSION['generated_qr'] = [
                'content' => $content,
                'type' => $type,
                'size' => $size,
                'image' => $qrData,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            Logger::activity(Auth::id(), 'qr_generated', ['type' => $type]);
            
            Helpers::flash('success', 'QR code generated successfully!');
        } else {
            Helpers::flash('error', 'Failed to generate QR code.');
        }
        
        Helpers::redirect('/projects/qr/generate');
    }
    
    /**
     * Generate QR code image
     */
    private function generateQRCode(string $content, int $size, string $color, string $bgColor): ?string
    {
        try {
            // Use our standalone QR code generator
            $dataUrl = \Core\QRCodeGenerator::generate($content, $size, '#' . $color, '#' . $bgColor);
            return $dataUrl;
        } catch (\Exception $e) {
            \Core\Logger::error('QR generation failed: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Show QR code history
     */
    public function history(): void
    {
        // Placeholder - in production, fetch from database
        $history = [];
        
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
        
        // Redirect to the QR image URL for download
        header('Location: ' . $qr['image']);
        exit;
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
        
        // Placeholder - in production, delete from database
        Helpers::flash('success', 'QR code deleted.');
        Helpers::redirect('/projects/qr/history');
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
