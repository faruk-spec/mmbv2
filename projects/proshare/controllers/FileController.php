<?php
/**
 * File Controller
 * 
 * @package MMB\Projects\ProShare\Controllers
 */

namespace Projects\ProShare\Controllers;

use Core\Auth;
use Core\Security;
use Core\Helpers;
use Core\Logger;

class FileController
{
    /**
     * Show upload form
     */
    public function showUpload(): void
    {
        $this->render('upload', [
            'title' => 'Upload File',
            'user' => Auth::user(),
            'maxSize' => $this->getMaxUploadSize()
        ]);
    }
    
    /**
     * Handle file upload
     */
    public function upload(): void
    {
        // Verify CSRF
        if (!Security::verifyCsrfToken($_POST['_csrf_token'] ?? '')) {
            Helpers::flash('error', 'Invalid request.');
            Helpers::redirect('/projects/proshare/upload');
            return;
        }
        
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            Helpers::flash('error', 'Please select a valid file.');
            Helpers::redirect('/projects/proshare/upload');
            return;
        }
        
        $file = $_FILES['file'];
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 
                         'application/zip', 'text/plain', 'application/msword',
                         'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        
        // Validate file type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, $allowedTypes)) {
            Helpers::flash('error', 'File type not allowed.');
            Helpers::redirect('/projects/proshare/upload');
            return;
        }
        
        // Generate unique filename
        $shortCode = $this->generateShortCode();
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $safeFilename = $shortCode . '.' . strtolower($ext);
        
        // Create upload directory
        $uploadDir = BASE_PATH . '/storage/uploads/proshare/' . date('Y/m');
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $destination = $uploadDir . '/' . $safeFilename;
        
        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Store file info in session (placeholder for database)
            $fileInfo = [
                'short_code' => $shortCode,
                'original_name' => $file['name'],
                'filename' => $safeFilename,
                'path' => $destination,
                'size' => $file['size'],
                'mime_type' => $mimeType,
                'downloads' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'user_id' => Auth::id()
            ];
            
            if (!isset($_SESSION['proshare_files'])) {
                $_SESSION['proshare_files'] = [];
            }
            $_SESSION['proshare_files'][$shortCode] = $fileInfo;
            
            Logger::activity(Auth::id(), 'file_uploaded', [
                'filename' => $file['name'],
                'size' => $file['size']
            ]);
            
            Helpers::flash('success', 'File uploaded successfully!');
            $_SESSION['last_upload'] = $fileInfo;
        } else {
            Helpers::flash('error', 'Failed to upload file.');
        }
        
        Helpers::redirect('/projects/proshare/upload');
    }
    
    /**
     * Show my files
     */
    public function myFiles(): void
    {
        $files = $_SESSION['proshare_files'] ?? [];
        
        $this->render('files', [
            'title' => 'My Files',
            'user' => Auth::user(),
            'files' => $files
        ]);
    }
    
    /**
     * Download file
     */
    public function download(string $shortcode): void
    {
        $files = $_SESSION['proshare_files'] ?? [];
        
        if (!isset($files[$shortcode])) {
            Helpers::flash('error', 'File not found.');
            Helpers::redirect('/projects/proshare');
            return;
        }
        
        $file = $files[$shortcode];
        
        if (!file_exists($file['path'])) {
            Helpers::flash('error', 'File no longer exists.');
            Helpers::redirect('/projects/proshare');
            return;
        }
        
        // Increment download count
        $_SESSION['proshare_files'][$shortcode]['downloads']++;
        
        // Send file
        header('Content-Type: ' . $file['mime_type']);
        header('Content-Disposition: attachment; filename="' . $file['original_name'] . '"');
        header('Content-Length: ' . filesize($file['path']));
        header('Cache-Control: no-cache, must-revalidate');
        
        readfile($file['path']);
        exit;
    }
    
    /**
     * Share settings
     */
    public function shareSettings(string $shortCode): void
    {
        $files = $_SESSION['proshare_files'] ?? [];
        
        if (!isset($files[$shortCode])) {
            Helpers::flash('error', 'File not found.');
            Helpers::redirect('/projects/proshare');
            return;
        }
        
        $file = $files[$shortCode];
        $shareUrl = APP_URL . '/projects/proshare/download/' . $shortCode;
        
        $this->render('share', [
            'title' => 'Share File',
            'user' => Auth::user(),
            'file' => $file,
            'shareUrl' => $shareUrl
        ]);
    }
    
    /**
     * Delete file
     */
    public function delete(string $shortcode): void
    {
        // Set JSON header at the very beginning
        header('Content-Type: application/json');
        
        try {
            $user = Auth::user();
            if (!$user) {
                echo json_encode(['success' => false, 'error' => 'Unauthorized']);
                return;
            }
            
            $db = \Core\Database::projectConnection('proshare');
            
            // Get file info
            $file = $db->fetch(
                "SELECT * FROM files WHERE short_code = ? AND user_id = ?",
                [$shortcode, $user['id']]
            );
            
            if (!$file) {
                echo json_encode(['success' => false, 'error' => 'File not found or you do not have permission to delete it']);
                return;
            }
            
            // Delete physical file
            if (file_exists($file['path'])) {
                @unlink($file['path']);
            }
            
            // Update status in database
            $db->update('files', ['status' => 'deleted'], 'id = ?', [$file['id']]);
            
            // Try to log the deletion (don't fail if logging fails)
            try {
                $db->insert('audit_logs', [
                    'user_id' => $user['id'],
                    'action' => 'file_deleted',
                    'resource_type' => 'file',
                    'resource_id' => $file['id'],
                    'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
                    'details' => json_encode(['filename' => $file['original_name']]),
                    'created_at' => date('Y-m-d H:i:s'),
                ]);
            } catch (\Exception $logError) {
                // Log error but don't fail the delete
                error_log('Audit log insert failed: ' . $logError->getMessage());
            }
            
            echo json_encode(['success' => true, 'message' => 'File deleted successfully']);
        } catch (\Exception $e) {
            error_log('File deletion failed: ' . $e->getMessage());
            echo json_encode(['success' => false, 'error' => 'Failed to delete file: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Generate short code
     */
    private function generateShortCode(): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $code = '';
        for ($i = 0; $i < 8; $i++) {
            $code .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $code;
    }
    
    /**
     * Get max upload size
     */
    private function getMaxUploadSize(): string
    {
        $maxUpload = ini_get('upload_max_filesize');
        $maxPost = ini_get('post_max_size');
        
        $maxUploadBytes = $this->parseSize($maxUpload);
        $maxPostBytes = $this->parseSize($maxPost);
        
        $max = min($maxUploadBytes, $maxPostBytes);
        
        return $this->formatSize($max);
    }
    
    /**
     * Parse size string to bytes
     */
    private function parseSize(string $size): int
    {
        $unit = strtolower(substr($size, -1));
        $value = (int) $size;
        
        switch ($unit) {
            case 'g':
                $value *= 1024 * 1024 * 1024;
                break;
            case 'm':
                $value *= 1024 * 1024;
                break;
            case 'k':
                $value *= 1024;
                break;
        }
        
        return $value;
    }
    
    /**
     * Format bytes to human readable
     */
    private function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
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
