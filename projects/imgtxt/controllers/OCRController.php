<?php
/**
 * ImgTxt OCR Controller
 * 
 * @package MMB\Projects\ImgTxt\Controllers
 */

namespace Projects\ImgTxt\Controllers;

use Core\Database;
use Core\View;
use Core\Auth;
use Core\Security;
use Core\Helpers;

class OCRController
{
    private const ALLOWED_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
    private const MAX_FILE_SIZE = 10485760; // 10MB
    
    /**
     * Show upload form
     */
    public function showUpload(): void
    {
        $user = Auth::user();
        $db = Database::projectConnection('imgtxt');
        
        // Get user settings
        $settings = $db->fetch(
            "SELECT * FROM user_settings WHERE user_id = ?",
            [$user['id']]
        );
        
        if (!$settings) {
            $db->insert('user_settings', ['user_id' => $user['id']]);
            $settings = $db->fetch("SELECT * FROM user_settings WHERE user_id = ?", [$user['id']]);
        }
        
        View::render('projects/imgtxt/upload', [
            'settings' => $settings,
            'title' => 'Upload & OCR',
            'subtitle' => 'Extract text from images',
            'currentPage' => 'upload',
            'user' => $user,
        ]);
    }
    
    /**
     * Handle file upload
     */
    public function upload(): void
    {
        // Clear any output that may have been generated
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json');
        
        try {
            $user = Auth::user();
            
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'error' => 'Please select a valid file']);
                return;
            }
            
            $file = $_FILES['file'];
            
            // Validate file size
            if ($file['size'] > self::MAX_FILE_SIZE) {
                echo json_encode(['success' => false, 'error' => 'File size exceeds 10MB limit']);
                return;
            }
            
            // Validate file type
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            
            if (!in_array($mimeType, self::ALLOWED_TYPES)) {
                echo json_encode(['success' => false, 'error' => 'File type not allowed. Please upload JPG, PNG, GIF, or PDF']);
                return;
            }
            
            // Generate unique filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $storedFilename = uniqid('ocr_', true) . '.' . strtolower($ext);
            
            // Create upload directory
            $uploadDir = BASE_PATH . '/storage/uploads/imgtxt/' . date('Y/m');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $destination = $uploadDir . '/' . $storedFilename;
            
            if (!move_uploaded_file($file['tmp_name'], $destination)) {
                echo json_encode(['success' => false, 'error' => 'Failed to save file']);
                return;
            }
            
            // Store in database
            $db = Database::projectConnection('imgtxt');
            $language = Security::sanitize($_POST['language'] ?? 'eng');
            
            $jobId = $db->insert('ocr_jobs', [
                'user_id' => $user['id'],
                'original_filename' => $file['name'],
                'stored_filename' => $storedFilename,
                'file_path' => $destination,
                'file_size' => $file['size'],
                'mime_type' => $mimeType,
                'language' => $language,
                'status' => 'pending',
            ]);
            
            if ($jobId) {
                echo json_encode([
                    'success' => true,
                    'job_id' => $jobId,
                    'message' => 'File uploaded successfully'
                ]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Failed to create OCR job']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Process OCR job
     */
    public function process(): void
    {
        // Clear any output that may have been generated
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json');
        
        try {
            $user = Auth::user();
            $jobId = (int)($_POST['job_id'] ?? 0);
            
            if (!$jobId) {
                echo json_encode(['success' => false, 'error' => 'Job ID required']);
                return;
            }
            
            $db = Database::projectConnection('imgtxt');
            
            $job = $db->fetch(
                "SELECT * FROM ocr_jobs WHERE id = ? AND user_id = ?",
                [$jobId, $user['id']]
            );
            
            if (!$job) {
                echo json_encode(['success' => false, 'error' => 'Job not found']);
                return;
            }
            
            // Update status to processing
            $db->update('ocr_jobs', ['status' => 'processing'], 'id = ?', [$jobId]);
            
            $startTime = microtime(true);
            
            // Perform OCR processing
            $result = $this->performOCR($job['file_path'], $job['language']);
            
            $processingTime = round(microtime(true) - $startTime);
            
            if ($result['success']) {
                // Update job with results
                $db->update('ocr_jobs', [
                    'status' => 'completed',
                    'extracted_text' => $result['text'],
                    'confidence' => $result['confidence'] ?? null,
                    'processing_time' => $processingTime,
                ], 'id = ?', [$jobId]);
                
                // Update usage stats
                $this->updateUsageStats($user['id'], $db, true);
                
                echo json_encode([
                    'success' => true,
                    'job_id' => $jobId,
                    'text' => $result['text'],
                    'confidence' => $result['confidence'] ?? null,
                    'processing_time' => $processingTime
                ]);
            } else {
                // Update job with error
                $db->update('ocr_jobs', [
                    'status' => 'failed',
                    'error_message' => $result['error'],
                    'processing_time' => $processingTime,
                ], 'id = ?', [$jobId]);
                
                // Update usage stats
                $this->updateUsageStats($user['id'], $db, false);
                
                echo json_encode([
                    'success' => false,
                    'error' => $result['error']
                ]);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Server error: ' . $e->getMessage()]);
        }
    }
    
    /**
     * Perform OCR using Tesseract or fallback method
     */
    private function performOCR(string $filePath, string $language = 'eng'): array
    {
        // Check if Tesseract is available
        $tesseractPath = $this->findTesseract();
        
        if ($tesseractPath) {
            // Preprocess image for better OCR accuracy
            $preprocessedPath = $this->preprocessImage($filePath);
            $result = $this->performTesseractOCR($preprocessedPath ?: $filePath, $language, $tesseractPath);
            
            // Clean up preprocessed file if created
            if ($preprocessedPath && $preprocessedPath !== $filePath && file_exists($preprocessedPath)) {
                @unlink($preprocessedPath);
            }
            
            return $result;
        } else {
            // Fallback: Return a placeholder message
            return [
                'success' => true,
                'text' => "OCR processing requires Tesseract OCR engine to be installed on the server.\n\nPlease contact your system administrator to enable OCR functionality.\n\nFilename: " . basename($filePath),
                'confidence' => null
            ];
        }
    }
    
    /**
     * Preprocess image for better OCR accuracy
     */
    private function preprocessImage(string $filePath): ?string
    {
        // Check if GD or Imagick is available
        if (!extension_loaded('gd') && !extension_loaded('imagick')) {
            return null; // No preprocessing available
        }
        
        try {
            $imageInfo = getimagesize($filePath);
            if (!$imageInfo) {
                return null;
            }
            
            $mimeType = $imageInfo['mime'];
            
            // Load image based on type
            if (extension_loaded('gd')) {
                switch ($mimeType) {
                    case 'image/jpeg':
                        $image = imagecreatefromjpeg($filePath);
                        break;
                    case 'image/png':
                        $image = imagecreatefrompng($filePath);
                        break;
                    case 'image/gif':
                        $image = imagecreatefromgif($filePath);
                        break;
                    default:
                        return null;
                }
                
                if (!$image) {
                    return null;
                }
                
                // Apply image enhancements
                // 1. Convert to grayscale for better text detection
                imagefilter($image, IMG_FILTER_GRAYSCALE);
                
                // 2. Increase contrast
                imagefilter($image, IMG_FILTER_CONTRAST, -20);
                
                // 3. Sharpen image
                imagefilter($image, IMG_FILTER_MEAN_REMOVAL);
                
                // Save preprocessed image
                $preprocessedPath = tempnam(sys_get_temp_dir(), 'ocr_preprocessed_') . '.png';
                imagepng($image, $preprocessedPath, 9);
                imagedestroy($image);
                
                return $preprocessedPath;
            }
        } catch (\Exception $e) {
            // If preprocessing fails, return null to use original
            return null;
        }
        
        return null;
    }
    
    /**
     * Find Tesseract binary
     */
    private function findTesseract(): ?string
    {
        $paths = ['/usr/bin/tesseract', '/usr/local/bin/tesseract', 'tesseract'];
        
        foreach ($paths as $path) {
            if (@exec("which $path 2>/dev/null", $output) || file_exists($path)) {
                return $path;
            }
        }
        
        return null;
    }
    
    /**
     * Perform OCR using Tesseract
     */
    private function performTesseractOCR(string $filePath, string $language, string $tesseractPath): array
    {
        $outputFile = tempnam(sys_get_temp_dir(), 'ocr_');
        
        // Run Tesseract with optimized parameters for better accuracy
        // --psm 3: Fully automatic page segmentation, but no OSD (Orientation and Script Detection)
        // --oem 1: Neural nets LSTM engine only
        $command = escapeshellcmd($tesseractPath) . ' ' . 
                   escapeshellarg($filePath) . ' ' . 
                   escapeshellarg($outputFile) . ' -l ' . 
                   escapeshellarg($language) . 
                   ' --psm 3 --oem 1 2>&1';
        
        exec($command, $output, $returnCode);
        
        if ($returnCode === 0 && file_exists($outputFile . '.txt')) {
            $text = file_get_contents($outputFile . '.txt');
            @unlink($outputFile . '.txt');
            
            // Clean up and improve text output
            $text = $this->cleanOCRText($text);
            
            return [
                'success' => true,
                'text' => $text,
                'confidence' => null
            ];
        } else {
            @unlink($outputFile . '.txt');
            return [
                'success' => false,
                'error' => 'OCR processing failed: ' . implode("\n", $output)
            ];
        }
    }
    
    /**
     * Clean and improve OCR text output
     */
    private function cleanOCRText(string $text): string
    {
        // Remove extra whitespace
        $text = preg_replace('/[ \t]+/', ' ', $text);
        
        // Normalize line breaks (remove excessive blank lines)
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        
        // Trim each line
        $lines = explode("\n", $text);
        $lines = array_map('trim', $lines);
        $text = implode("\n", $lines);
        
        return trim($text);
    }
    
    /**
     * Update usage statistics
     */
    private function updateUsageStats(int $userId, Database $db, bool $success): void
    {
        $today = date('Y-m-d');
        
        $stats = $db->fetch(
            "SELECT * FROM usage_stats WHERE user_id = ? AND date = ?",
            [$userId, $today]
        );
        
        if ($stats) {
            $db->query(
                "UPDATE usage_stats SET 
                 total_jobs = total_jobs + 1,
                 successful_jobs = successful_jobs + ?,
                 failed_jobs = failed_jobs + ?
                 WHERE user_id = ? AND date = ?",
                [$success ? 1 : 0, $success ? 0 : 1, $userId, $today]
            );
        } else {
            $db->insert('usage_stats', [
                'user_id' => $userId,
                'date' => $today,
                'total_jobs' => 1,
                'successful_jobs' => $success ? 1 : 0,
                'failed_jobs' => $success ? 0 : 1,
            ]);
        }
    }
    
    /**
     * Show result
     */
    public function result(int $id): void
    {
        $user = Auth::user();
        $db = Database::projectConnection('imgtxt');
        
        $job = $db->fetch(
            "SELECT * FROM ocr_jobs WHERE id = ? AND user_id = ?",
            [$id, $user['id']]
        );
        
        if (!$job) {
            http_response_code(404);
            View::render('errors/404');
            return;
        }
        
        View::render('projects/imgtxt/result', [
            'job' => $job,
            'title' => 'OCR Result #' . $id,
            'subtitle' => 'View extracted text',
            'currentPage' => 'result',
            'user' => $user,
        ]);
    }
    
    /**
     * Download result
     */
    public function download(int $id): void
    {
        $user = Auth::user();
        $db = Database::projectConnection('imgtxt');
        
        $job = $db->fetch(
            "SELECT * FROM ocr_jobs WHERE id = ? AND user_id = ?",
            [$id, $user['id']]
        );
        
        if (!$job || $job['status'] !== 'completed') {
            http_response_code(404);
            echo "File not found or not ready";
            return;
        }
        
        $filename = pathinfo($job['original_filename'], PATHINFO_FILENAME) . '.txt';
        
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($job['extracted_text']));
        
        echo $job['extracted_text'];
    }
}
