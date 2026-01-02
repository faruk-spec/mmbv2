<?php

/**
 * OcrProcessor Class
 * 
 * Advanced OCR processing with multi-page PDF support, table detection,
 * image preprocessing, and batch processing capabilities.
 * 
 * Features:
 * - Multi-page PDF processing with page extraction
 * - Table detection and structured extraction
 * - Image preprocessing (deskew, denoise, enhance)
 * - Batch job queue system
 * - OCR confidence scores and validation
 * - Progress tracking for long-running jobs
 */
class OcrProcessor
{
    private $tesseractPath = '/usr/bin/tesseract';
    private $pdfiumPath = '/usr/bin/pdftoppm';
    private $imageMagickPath = '/usr/bin/convert';
    
    /**
     * Process multi-page PDF document
     * 
     * @param string $pdfPath Path to PDF file
     * @param string $language OCR language (default: 'eng')
     * @param array $options Processing options
     * @return array Results with text per page
     */
    public static function processPDF($pdfPath, $language = 'eng', $options = [])
    {
        if (!file_exists($pdfPath)) {
            throw new Exception('PDF file not found');
        }
        
        $processor = new self();
        $tempDir = sys_get_temp_dir() . '/ocr_pdf_' . uniqid();
        mkdir($tempDir, 0755, true);
        
        try {
            // Extract pages as images
            $pages = $processor->extractPDFPages($pdfPath, $tempDir);
            
            $results = [
                'success' => true,
                'pages' => [],
                'total_pages' => count($pages),
                'full_text' => '',
                'confidence' => 0
            ];
            
            $totalConfidence = 0;
            
            // Process each page
            foreach ($pages as $pageNum => $imagePath) {
                $pageResult = self::processImage($imagePath, $language, $options);
                
                $results['pages'][] = [
                    'page' => $pageNum + 1,
                    'text' => $pageResult['text'],
                    'confidence' => $pageResult['confidence'],
                    'has_tables' => $pageResult['has_tables'] ?? false
                ];
                
                $results['full_text'] .= "=== Page " . ($pageNum + 1) . " ===\n\n";
                $results['full_text'] .= $pageResult['text'] . "\n\n";
                
                $totalConfidence += $pageResult['confidence'];
            }
            
            $results['confidence'] = round($totalConfidence / count($pages), 2);
            
            // Cleanup
            $processor->cleanupDirectory($tempDir);
            
            return $results;
            
        } catch (Exception $e) {
            $processor->cleanupDirectory($tempDir);
            throw $e;
        }
    }
    
    /**
     * Process single image with OCR
     * 
     * @param string $imagePath Path to image file
     * @param string $language OCR language
     * @param array $options Processing options (preprocess, detect_tables, etc.)
     * @return array OCR results with text and confidence
     */
    public static function processImage($imagePath, $language = 'eng', $options = [])
    {
        if (!file_exists($imagePath)) {
            throw new Exception('Image file not found');
        }
        
        $processor = new self();
        $tempImage = $imagePath;
        
        // Preprocess image if requested
        if (!empty($options['preprocess'])) {
            $tempImage = $processor->preprocessImage($imagePath, $options);
        }
        
        // Perform OCR
        $ocrResult = $processor->performOCR($tempImage, $language);
        
        // Detect tables if requested
        $hasTables = false;
        $tables = [];
        
        if (!empty($options['detect_tables'])) {
            $tableData = $processor->detectTables($ocrResult['hocr']);
            $hasTables = !empty($tableData);
            $tables = $tableData;
        }
        
        // Cleanup temp image if it was created
        if ($tempImage !== $imagePath && file_exists($tempImage)) {
            unlink($tempImage);
        }
        
        return [
            'success' => true,
            'text' => $ocrResult['text'],
            'confidence' => $ocrResult['confidence'],
            'has_tables' => $hasTables,
            'tables' => $tables,
            'language' => $language
        ];
    }
    
    /**
     * Preprocess image to improve OCR accuracy
     * 
     * @param string $imagePath Original image path
     * @param array $options Preprocessing options (deskew, denoise, enhance, etc.)
     * @return string Path to preprocessed image
     */
    private function preprocessImage($imagePath, $options = [])
    {
        $outputPath = sys_get_temp_dir() . '/ocr_preprocessed_' . uniqid() . '.png';
        
        $commands = [];
        
        // Deskew (fix rotated text)
        if (!empty($options['deskew'])) {
            $commands[] = '-deskew 40%';
        }
        
        // Denoise
        if (!empty($options['denoise'])) {
            $commands[] = '-despeckle';
            $commands[] = '-median 1';
        }
        
        // Enhance contrast
        if (!empty($options['enhance'])) {
            $commands[] = '-contrast-stretch 0';
            $commands[] = '-normalize';
        }
        
        // Convert to grayscale
        $commands[] = '-colorspace Gray';
        
        // Sharpen
        if (!empty($options['sharpen'])) {
            $commands[] = '-sharpen 0x1';
        }
        
        // Increase DPI
        $commands[] = '-density 300';
        $commands[] = '-units PixelsPerInch';
        
        $command = sprintf(
            '%s %s %s %s',
            escapeshellarg($this->imageMagickPath),
            escapeshellarg($imagePath),
            implode(' ', $commands),
            escapeshellarg($outputPath)
        );
        
        exec($command, $output, $returnCode);
        
        if ($returnCode !== 0 || !file_exists($outputPath)) {
            // Fallback to original if preprocessing fails
            return $imagePath;
        }
        
        return $outputPath;
    }
    
    /**
     * Perform OCR using Tesseract
     * 
     * @param string $imagePath Image to process
     * @param string $language OCR language
     * @return array Text and confidence score
     */
    private function performOCR($imagePath, $language = 'eng')
    {
        $outputBase = sys_get_temp_dir() . '/ocr_output_' . uniqid();
        
        // Run Tesseract with hOCR output for table detection
        $command = sprintf(
            '%s %s %s -l %s hocr 2>&1',
            escapeshellarg($this->tesseractPath),
            escapeshellarg($imagePath),
            escapeshellarg($outputBase),
            escapeshellarg($language)
        );
        
        exec($command, $output, $returnCode);
        
        $hocrFile = $outputBase . '.hocr';
        $hocr = file_exists($hocrFile) ? file_get_contents($hocrFile) : '';
        
        // Also get plain text
        $command = sprintf(
            '%s %s stdout -l %s 2>&1',
            escapeshellarg($this->tesseractPath),
            escapeshellarg($imagePath),
            escapeshellarg($language)
        );
        
        exec($command, $textOutput, $returnCode);
        
        $text = implode("\n", $textOutput);
        $confidence = $this->calculateConfidence($hocr);
        
        // Cleanup
        if (file_exists($hocrFile)) {
            unlink($hocrFile);
        }
        
        return [
            'text' => $text,
            'hocr' => $hocr,
            'confidence' => $confidence
        ];
    }
    
    /**
     * Calculate average confidence score from hOCR output
     * 
     * @param string $hocr hOCR formatted output
     * @return float Average confidence (0-100)
     */
    private function calculateConfidence($hocr)
    {
        if (empty($hocr)) {
            return 0;
        }
        
        // Extract confidence scores from hOCR
        preg_match_all('/x_wconf\s+(\d+)/', $hocr, $matches);
        
        if (empty($matches[1])) {
            return 0;
        }
        
        $confidences = array_map('intval', $matches[1]);
        $average = array_sum($confidences) / count($confidences);
        
        return round($average, 2);
    }
    
    /**
     * Detect tables in hOCR output
     * 
     * @param string $hocr hOCR formatted output
     * @return array Detected tables with structure
     */
    private function detectTables($hocr)
    {
        if (empty($hocr)) {
            return [];
        }
        
        $tables = [];
        
        // Simple table detection based on text alignment patterns
        // This is a basic implementation - production would use more sophisticated algorithms
        
        // Extract all text blocks with coordinates
        preg_match_all(
            '/ocr_line.*?bbox\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+).*?>(.*?)<\/span>/s',
            $hocr,
            $matches,
            PREG_SET_ORDER
        );
        
        if (empty($matches)) {
            return [];
        }
        
        // Group lines by similar y-coordinates (rows)
        $rows = [];
        foreach ($matches as $match) {
            $y = (int)$match[2];
            $text = strip_tags($match[5]);
            $text = trim($text);
            
            if (empty($text)) {
                continue;
            }
            
            // Group by y-coordinate with tolerance
            $found = false;
            foreach ($rows as $rowY => &$row) {
                if (abs($rowY - $y) < 10) {
                    $row[] = [
                        'x' => (int)$match[1],
                        'text' => $text
                    ];
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $rows[$y] = [[
                    'x' => (int)$match[1],
                    'text' => $text
                ]];
            }
        }
        
        // If we have multiple rows with similar column structure, it might be a table
        if (count($rows) >= 3) {
            $tables[] = [
                'rows' => count($rows),
                'estimated_columns' => max(array_map('count', $rows)),
                'data' => array_values($rows)
            ];
        }
        
        return $tables;
    }
    
    /**
     * Extract pages from PDF as images
     * 
     * @param string $pdfPath Path to PDF
     * @param string $outputDir Directory for extracted pages
     * @return array Paths to extracted page images
     */
    private function extractPDFPages($pdfPath, $outputDir)
    {
        $outputPattern = $outputDir . '/page';
        
        $command = sprintf(
            '%s -png -r 300 %s %s 2>&1',
            escapeshellarg($this->pdfiumPath),
            escapeshellarg($pdfPath),
            escapeshellarg($outputPattern)
        );
        
        exec($command, $output, $returnCode);
        
        // Find all extracted pages
        $pages = glob($outputDir . '/page-*.png');
        sort($pages, SORT_NATURAL);
        
        if (empty($pages)) {
            throw new Exception('Failed to extract pages from PDF');
        }
        
        return $pages;
    }
    
    /**
     * Create batch OCR job
     * 
     * @param int $userId User ID
     * @param array $files Array of file paths to process
     * @param array $options Processing options
     * @return int Job ID
     */
    public static function createBatchJob($userId, $files, $options = [])
    {
        if (empty($files)) {
            throw new Exception('No files provided for batch processing');
        }
        
        // Get database connection from ImgTxt config
        $config = require __DIR__ . '/../../projects/imgtxt/config.php';
        $db = new PDO(
            "mysql:host={$config['db_host']};dbname={$config['db_name']}",
            $config['db_user'],
            $config['db_pass']
        );
        
        // Create batch job record
        $stmt = $db->prepare("
            INSERT INTO batch_jobs (user_id, total_files, status, options, created_at)
            VALUES (?, ?, 'pending', ?, NOW())
        ");
        
        $stmt->execute([
            $userId,
            count($files),
            json_encode($options)
        ]);
        
        $jobId = $db->lastInsertId();
        
        // Add files to job
        $stmt = $db->prepare("
            INSERT INTO batch_job_files (job_id, file_path, status)
            VALUES (?, ?, 'pending')
        ");
        
        foreach ($files as $file) {
            $stmt->execute([$jobId, $file]);
        }
        
        return $jobId;
    }
    
    /**
     * Process batch job
     * 
     * @param int $jobId Job ID
     * @param callable $progressCallback Optional callback for progress updates
     * @return array Job results
     */
    public static function processBatchJob($jobId, $progressCallback = null)
    {
        // Get database connection from ImgTxt config
        $config = require __DIR__ . '/../../projects/imgtxt/config.php';
        $db = new PDO(
            "mysql:host={$config['db_host']};dbname={$config['db_name']}",
            $config['db_user'],
            $config['db_pass']
        );
        
        // Get job details
        $stmt = $db->prepare("SELECT * FROM batch_jobs WHERE id = ?");
        $stmt->execute([$jobId]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$job) {
            throw new Exception('Batch job not found');
        }
        
        $options = json_decode($job['options'], true) ?: [];
        
        // Update job status
        $db->prepare("UPDATE batch_jobs SET status = 'processing' WHERE id = ?")
           ->execute([$jobId]);
        
        // Get files
        $stmt = $db->prepare("
            SELECT * FROM batch_job_files
            WHERE job_id = ? AND status = 'pending'
            ORDER BY id
        ");
        $stmt->execute([$jobId]);
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $results = [];
        $processed = 0;
        $failed = 0;
        
        foreach ($files as $file) {
            try {
                // Process file
                $result = self::processImage($file['file_path'], $options['language'] ?? 'eng', $options);
                
                // Update file status
                $db->prepare("
                    UPDATE batch_job_files
                    SET status = 'completed', result_text = ?, confidence = ?, processed_at = NOW()
                    WHERE id = ?
                ")->execute([
                    $result['text'],
                    $result['confidence'],
                    $file['id']
                ]);
                
                $processed++;
                $results[] = $result;
                
            } catch (Exception $e) {
                // Mark as failed
                $db->prepare("
                    UPDATE batch_job_files
                    SET status = 'failed', error_message = ?, processed_at = NOW()
                    WHERE id = ?
                ")->execute([
                    $e->getMessage(),
                    $file['id']
                ]);
                
                $failed++;
            }
            
            // Call progress callback
            if ($progressCallback) {
                call_user_func($progressCallback, [
                    'total' => count($files),
                    'processed' => $processed + $failed,
                    'success' => $processed,
                    'failed' => $failed
                ]);
            }
        }
        
        // Update job completion
        $status = ($failed === 0) ? 'completed' : 'completed_with_errors';
        $db->prepare("
            UPDATE batch_jobs
            SET status = ?, processed_files = ?, failed_files = ?, completed_at = NOW()
            WHERE id = ?
        ")->execute([
            $status,
            $processed,
            $failed,
            $jobId
        ]);
        
        return [
            'job_id' => $jobId,
            'total' => count($files),
            'processed' => $processed,
            'failed' => $failed,
            'results' => $results
        ];
    }
    
    /**
     * Get batch job status
     * 
     * @param int $jobId Job ID
     * @return array Job status
     */
    public static function getBatchJobStatus($jobId)
    {
        $config = require __DIR__ . '/../../projects/imgtxt/config.php';
        $db = new PDO(
            "mysql:host={$config['db_host']};dbname={$config['db_name']}",
            $config['db_user'],
            $config['db_pass']
        );
        
        $stmt = $db->prepare("SELECT * FROM batch_jobs WHERE id = ?");
        $stmt->execute([$jobId]);
        $job = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$job) {
            throw new Exception('Batch job not found');
        }
        
        return [
            'job_id' => $job['id'],
            'status' => $job['status'],
            'total_files' => $job['total_files'],
            'processed_files' => $job['processed_files'] ?? 0,
            'failed_files' => $job['failed_files'] ?? 0,
            'progress' => $job['total_files'] > 0
                ? round(($job['processed_files'] ?? 0) / $job['total_files'] * 100, 2)
                : 0,
            'created_at' => $job['created_at'],
            'completed_at' => $job['completed_at']
        ];
    }
    
    /**
     * Clean up temporary directory
     * 
     * @param string $dir Directory to clean
     */
    private function cleanupDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        
        rmdir($dir);
    }
}
