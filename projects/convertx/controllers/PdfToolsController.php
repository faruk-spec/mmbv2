<?php
/**
 * ConvertX PDF Tools Controller
 *
 * Handles Merge PDFs, Split PDFs, Compress PDF, and Compress Images.
 * Processing is synchronous (no job queue): upload → process → download.
 *
 * Download tokens are stored in the session so they survive a page reload
 * without exposing raw file paths in the URL.
 *
 * @package MMB\Projects\ConvertX\Controllers
 */

namespace Projects\ConvertX\Controllers;

use Core\Auth;
use Core\Database;
use Core\Security;
use Core\Logger;
use Core\ActivityLogger;
use Core\SecureUpload;
use Projects\ConvertX\Services\PdfToolsService;

class PdfToolsController
{
    private PdfToolsService $svc;
    private ?array $imgLimits = null;   // admin-configured limits (lazy-loaded)

    private const MAX_PDF_SIZE_BYTES   = 200 * 1024 * 1024;   // 200 MB per file
    private const MAX_IMAGE_SIZE_BYTES =  50 * 1024 * 1024;   //  50 MB per file
    private const MAX_MERGE_FILES      = 20;
    private const MAX_COMPRESS_FILES   = 20;

    // Defaults used when admin has not configured limits yet
    private const DEFAULT_MAX_FILES      = 20;
    private const DEFAULT_MAX_SIZE_MB    = 50;
    private const DEFAULT_ALLOWED_EXTS   = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

    public function __construct()
    {
        $this->svc = new PdfToolsService();
    }

    /**
     * Record a completed/failed synchronous tool operation in `convertx_tool_jobs`.
     *
     * @param string $toolType     e.g. 'pdf_merge', 'img_compress'
     * @param array  $names        original input filenames
     * @param string $outFilename  download filename (e.g. 'merged.pdf')
     * @param int    $origSize     total original bytes
     * @param int    $outSize      total output bytes
     * @param string $status       'completed' or 'failed'
     * @param string $errorMsg
     */
    private function logToolJob(
        string $toolType,
        array  $names,
        string $outFilename,
        int    $origSize = 0,
        int    $outSize  = 0,
        string $status   = 'completed',
        string $errorMsg = ''
    ): void {
        $userId = Auth::id();
        if (!$userId) {
            return;
        }
        try {
            require_once PROJECT_PATH . '/models/ConversionJobModel.php';
            (new \Projects\ConvertX\Models\ConversionJobModel())->createToolJob(
                $userId, $toolType, count($names), $names,
                $outFilename, $origSize, $outSize, $status, $errorMsg
            );
        } catch (\Throwable $_) {}
    }

    /**
     * Load admin-configured image-tool limits from DB (cached per request).
     */
    private function imgLimits(): array
    {
        if ($this->imgLimits !== null) {
            return $this->imgLimits;
        }
        $settings = [];
        try {
            $rows = Database::getInstance()->fetchAll(
                'SELECT setting_key, setting_value FROM convertx_image_tools_settings
                  WHERE setting_key IN (:k1, :k2, :k3)',
                ['k1' => 'max_files', 'k2' => 'max_file_size_mb', 'k3' => 'allowed_image_formats']
            );
            foreach ($rows as $r) {
                $settings[$r['setting_key']] = $r['setting_value'];
            }
        } catch (\Throwable $_) { /* table may not exist yet */ }

        $maxFiles   = isset($settings['max_files'])       && (int)$settings['max_files']       > 0
                        ? (int)$settings['max_files']       : self::DEFAULT_MAX_FILES;
        $maxSizeMb  = isset($settings['max_file_size_mb']) && (int)$settings['max_file_size_mb'] > 0
                        ? (int)$settings['max_file_size_mb'] : self::DEFAULT_MAX_SIZE_MB;
        $allowedRaw = !empty($settings['allowed_image_formats'])
                        ? $settings['allowed_image_formats']
                        : implode(',', self::DEFAULT_ALLOWED_EXTS);
        $allowedExts = array_filter(array_map('trim', explode(',', strtolower($allowedRaw))));
        if (empty($allowedExts)) {
            $allowedExts = self::DEFAULT_ALLOWED_EXTS;
        }

        $this->imgLimits = [
            'max_files'    => $maxFiles,
            'max_size_bytes' => $maxSizeMb * 1024 * 1024,
            'max_size_mb'  => $maxSizeMb,
            'allowed_exts' => array_values($allowedExts),
        ];
        return $this->imgLimits;
    }

    // ================================================================== //
    //  Show form pages                                                    //
    // ================================================================== //

    public function showMerge(): void
    {
        $this->render('pdf-merge', [
            'title'    => 'Merge PDFs',
            'user'     => Auth::user(),
            'hasGs'    => $this->svc->findGhostscript() !== null,
            'backends' => ['gs' => $this->svc->findGhostscript() !== null],
        ]);
    }

    public function showSplit(): void
    {
        $this->render('pdf-split', [
            'title'    => 'Split PDF',
            'user'     => Auth::user(),
            'hasGs'    => $this->svc->findGhostscript() !== null,
            'backends' => ['gs' => $this->svc->findGhostscript() !== null],
        ]);
    }

    public function showCompressPdf(): void
    {
        $this->render('pdf-compress', [
            'title'    => 'Compress PDF',
            'user'     => Auth::user(),
            'hasGs'    => $this->svc->findGhostscript() !== null,
            'backends' => ['gs' => $this->svc->findGhostscript() !== null],
        ]);
    }

    public function showCompressImages(): void
    {
        $limits = $this->imgLimits();
        $this->render('img-compress', [
            'title'       => 'Compress Images',
            'user'        => Auth::user(),
            'hasGd'       => $this->svc->hasGd(),
            'maxFiles'    => $limits['max_files'],
            'maxSizeMb'   => $limits['max_size_mb'],
            'allowedExts' => $limits['allowed_exts'],
        ]);
    }

    // ================================================================== //
    //  Submit handlers                                                    //
    // ================================================================== //

    /**
     * POST /pdf-merge
     * Accepts 2–20 PDF files, merges them, returns {success, token, filename, size}
     */
    public function submitMerge(): void
    {
        ob_start();

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->jsonError('Invalid request token', 403);
            return;
        }
        $userId = Auth::id();
        if (!$userId) {
            $this->jsonError('Authentication required', 401);
            return;
        }

        $files = $_FILES['pdfs'] ?? null;
        if (!$files || empty($files['name'])) {
            $this->jsonError('No files uploaded', 400);
            return;
        }

        $fileList = $this->normaliseFiles($files);
        if (count($fileList) < 2) {
            $this->jsonError('Please upload at least 2 PDF files to merge.', 400);
            return;
        }
        if (count($fileList) > self::MAX_MERGE_FILES) {
            $this->jsonError('Maximum ' . self::MAX_MERGE_FILES . ' files per merge.', 400);
            return;
        }

        $tmpDir = BASE_PATH . '/storage/uploads/convertx/' . $userId . '/pdftools';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $savedPaths = [];
        $errors     = [];

        foreach ($fileList as $f) {
            if ($f['error'] !== UPLOAD_ERR_OK) {
                $errors[] = $f['name'] . ': upload error';
                continue;
            }
            if ($f['size'] > self::MAX_PDF_SIZE_BYTES) {
                $errors[] = $f['name'] . ': file too large (max 200 MB)';
                continue;
            }
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                $errors[] = $f['name'] . ': only PDF files are supported';
                continue;
            }
            $secureResult = SecureUpload::process($f, [
                'destination_dir'    => $tmpDir,
                'allowed_extensions' => ['pdf'],
                'allowed_mime_types' => ['application/pdf'],
                'max_size'           => self::MAX_PDF_SIZE_BYTES,
                'filename_prefix'    => 'merge',
                'source'             => 'convertx.pdf_merge',
                'user_id'            => $userId,
            ]);
            if (empty($secureResult['success'])) {
                $errors[] = $f['name'] . ': ' . ($secureResult['error'] ?? 'security check failed');
                continue;
            }
            $savedPaths[] = $secureResult['path'];
        }

        if (count($savedPaths) < 2) {
            $this->cleanupFiles($savedPaths);
            $this->jsonError('Need at least 2 valid PDFs. Errors: ' . implode('; ', $errors), 400);
            return;
        }

        $outputPath = $tmpDir . '/' . uniqid('merged_', true) . '.pdf';
        try {
            $this->svc->mergePdfs($savedPaths, $outputPath);
        } catch (\Throwable $e) {
            $this->cleanupFiles($savedPaths);
            Logger::error('PdfToolsController::submitMerge - ' . $e->getMessage());
            $this->jsonError($e->getMessage(), 500);
            return;
        }

        $this->cleanupFiles($savedPaths);

        $token   = $this->storeDownloadToken($outputPath, 'merged.pdf', 'application/pdf');
        $size    = file_exists($outputPath) ? filesize($outputPath) : 0;

        try {
            ActivityLogger::logCreate($userId, 'convertx', 'pdf_merge', 0, [
                'file_count' => count($savedPaths),
                'output_size' => $size,
            ]);
        } catch (\Throwable $_) {}
        $this->logToolJob('pdf_merge', array_map('basename', $savedPaths), 'merged.pdf', 0, $size);

        $this->cleanOutputBuffers();
        header('Content-Type: application/json');
        echo json_encode([
            'success'  => true,
            'token'    => $token,
            'filename' => 'merged.pdf',
            'size'     => $size,
            'errors'   => $errors,
        ]);
    }

    /**
     * POST /pdf-split
     * Accepts 1 PDF, optional page_range, returns ZIP of page PDFs.
     */
    public function submitSplit(): void
    {
        ob_start();

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->jsonError('Invalid request token', 403);
            return;
        }
        $userId = Auth::id();
        if (!$userId) {
            $this->jsonError('Authentication required', 401);
            return;
        }

        $file = $_FILES['pdf'] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->jsonError('No PDF file uploaded', 400);
            return;
        }
        if ($file['size'] > self::MAX_PDF_SIZE_BYTES) {
            $this->jsonError('File too large (max 200 MB)', 400);
            return;
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            $this->jsonError('Only PDF files are supported', 400);
            return;
        }

        $tmpDir = BASE_PATH . '/storage/uploads/convertx/' . $userId . '/pdftools';

        $secureResult = SecureUpload::process($file, [
            'destination_dir'    => $tmpDir,
            'allowed_extensions' => ['pdf'],
            'allowed_mime_types' => ['application/pdf'],
            'max_size'           => self::MAX_PDF_SIZE_BYTES,
            'filename_prefix'    => 'split_src',
            'source'             => 'convertx.pdf_split',
            'user_id'            => $userId,
        ]);
        if (empty($secureResult['success'])) {
            $this->jsonError($secureResult['error'] ?? 'File rejected by security checks.', 422);
            return;
        }
        $srcPath = $secureResult['path'];

        // Parse optional page range (e.g. "1,3-5,7")
        $pageRange = $this->parsePageRange($_POST['page_range'] ?? '');

        $outDir = $tmpDir . '/' . uniqid('split_out_', true);
        try {
            $pages = $this->svc->splitPdf($srcPath, $outDir, $pageRange);
        } catch (\Throwable $e) {
            @unlink($srcPath);
            Logger::error('PdfToolsController::submitSplit - ' . $e->getMessage());
            $this->jsonError($e->getMessage(), 500);
            return;
        }

        @unlink($srcPath);

        if (count($pages) === 1) {
            // Single page — serve directly
            $token = $this->storeDownloadToken($pages[0], basename($pages[0]), 'application/pdf');
            $this->cleanOutputBuffers();
            header('Content-Type: application/json');
            echo json_encode([
                'success'     => true,
                'token'       => $token,
                'filename'    => basename($pages[0]),
                'page_count'  => 1,
                'size'        => filesize($pages[0]),
            ]);
            return;
        }

        // Multiple pages — ZIP them
        $zipPath = $tmpDir . '/' . uniqid('split_zip_', true) . '.zip';
        if (!class_exists('ZipArchive')) {
            $this->cleanupFiles($pages);
            $this->jsonError('ZIP creation requires the PHP ZipArchive extension.', 501);
            return;
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            $this->cleanupFiles($pages);
            $this->jsonError('Could not create ZIP archive.', 500);
            return;
        }
        foreach ($pages as $p) {
            $zip->addFile($p, basename($p));
        }
        $zip->close();
        $this->cleanupFiles($pages);

        $token = $this->storeDownloadToken($zipPath, 'split_pages.zip', 'application/zip');

        try {
            ActivityLogger::logCreate($userId, 'convertx', 'pdf_split', 0, [
                'pages_extracted' => count($pages),
            ]);
        } catch (\Throwable $_) {}
        $this->logToolJob('pdf_split', [$file['name']], 'split_pages.zip');

        $this->cleanOutputBuffers();
        header('Content-Type: application/json');
        echo json_encode([
            'success'    => true,
            'token'      => $token,
            'filename'   => 'split_pages.zip',
            'page_count' => count($pages),
            'size'       => file_exists($zipPath) ? filesize($zipPath) : 0,
        ]);
    }

    /**
     * POST /pdf-compress
     */
    public function submitCompressPdf(): void
    {
        ob_start();

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->jsonError('Invalid request token', 403);
            return;
        }
        $userId = Auth::id();
        if (!$userId) {
            $this->jsonError('Authentication required', 401);
            return;
        }

        $file = $_FILES['pdf'] ?? null;
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $this->jsonError('No PDF file uploaded', 400);
            return;
        }
        if ($file['size'] > self::MAX_PDF_SIZE_BYTES) {
            $this->jsonError('File too large (max 200 MB)', 400);
            return;
        }
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            $this->jsonError('Only PDF files are supported', 400);
            return;
        }

        $quality = $_POST['quality'] ?? 'ebook';
        $allowed = ['screen', 'ebook', 'printer', 'prepress', 'default'];
        if (!in_array($quality, $allowed, true)) {
            $quality = 'ebook';
        }

        $tmpDir = BASE_PATH . '/storage/uploads/convertx/' . $userId . '/pdftools';

        $secureResult = SecureUpload::process($file, [
            'destination_dir'    => $tmpDir,
            'allowed_extensions' => ['pdf'],
            'allowed_mime_types' => ['application/pdf'],
            'max_size'           => self::MAX_PDF_SIZE_BYTES,
            'filename_prefix'    => 'compress_src',
            'source'             => 'convertx.pdf_compress',
            'user_id'            => $userId,
        ]);
        if (empty($secureResult['success'])) {
            $this->jsonError($secureResult['error'] ?? 'File rejected by security checks.', 422);
            return;
        }
        $srcPath  = $secureResult['path'];
        $origSize = filesize($srcPath);
        $outputPath = $tmpDir . '/' . uniqid('compressed_', true) . '.pdf';

        try {
            $this->svc->compressPdf($srcPath, $outputPath, $quality);
        } catch (\Throwable $e) {
            @unlink($srcPath);
            Logger::error('PdfToolsController::submitCompressPdf - ' . $e->getMessage());
            $this->jsonError($e->getMessage(), 500);
            return;
        }

        @unlink($srcPath);

        $newSize    = file_exists($outputPath) ? filesize($outputPath) : 0;
        $savedBytes = max(0, $origSize - $newSize);
        $savedPct   = $origSize > 0 ? round($savedBytes / $origSize * 100, 1) : 0;

        // Ghostscript might enlarge very small PDFs — handle gracefully
        $origFilename = pathinfo($file['name'], PATHINFO_FILENAME) . '_compressed.pdf';
        $token = $this->storeDownloadToken($outputPath, $origFilename, 'application/pdf');

        try {
            ActivityLogger::logCreate($userId, 'convertx', 'pdf_compress', 0, [
                'original_size'  => $origSize,
                'compressed_size'=> $newSize,
                'saved_pct'      => $savedPct,
                'quality'        => $quality,
            ]);
        } catch (\Throwable $_) {}
        $this->logToolJob('pdf_compress', [$file['name']], $origFilename, $origSize, $newSize);

        $this->cleanOutputBuffers();
        header('Content-Type: application/json');
        echo json_encode([
            'success'       => true,
            'token'         => $token,
            'filename'      => $origFilename,
            'original_size' => $origSize,
            'new_size'      => $newSize,
            'saved_bytes'   => $savedBytes,
            'saved_pct'     => $savedPct,
        ]);
    }

    /**
     * POST /img-compress
     * Accepts 1–20 images, compresses each, returns single file or ZIP.
     */
    public function submitCompressImages(): void
    {
        ob_start();

        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->jsonError('Invalid request token', 403);
            return;
        }
        $userId = Auth::id();
        if (!$userId) {
            $this->jsonError('Authentication required', 401);
            return;
        }

        $files = $_FILES['images'] ?? null;
        if (!$files || empty($files['name'])) {
            $this->jsonError('No files uploaded', 400);
            return;
        }

        $limits   = $this->imgLimits();
        $fileList = $this->normaliseFiles($files);
        if (count($fileList) > $limits['max_files']) {
            $this->jsonError('Maximum ' . $limits['max_files'] . ' images at once.', 400);
            return;
        }

        $quality   = max(1, min(100, (int) ($_POST['quality'] ?? 82)));
        $maxWidth  = max(0, (int) ($_POST['max_width'] ?? 0));
        $outputFmt = strtolower(trim($_POST['output_format'] ?? ''));
        $allowed   = ['jpg', 'jpeg', 'png', 'webp', 'bmp', ''];
        if (!in_array($outputFmt, $allowed, true)) {
            $outputFmt = '';
        }

        $tmpDir = BASE_PATH . '/storage/uploads/convertx/' . $userId . '/pdftools';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $imageExts  = $limits['allowed_exts'];
        $results    = [];
        $errors     = [];

        foreach ($fileList as $f) {
            if ($f['error'] !== UPLOAD_ERR_OK) {
                $errors[] = $f['name'] . ': upload error';
                continue;
            }
            if ($f['size'] > $limits['max_size_bytes']) {
                $errors[] = $f['name'] . ': file too large (max ' . $limits['max_size_mb'] . ' MB)';
                continue;
            }
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $imageExts, true)) {
                $errors[] = $f['name'] . ': unsupported format';
                continue;
            }

            $secureResult = SecureUpload::process($f, [
                'destination_dir'    => $tmpDir,
                'allowed_extensions' => $imageExts,
                'max_size'           => $limits['max_size_bytes'],
                'filename_prefix'    => 'img_src',
                'source'             => 'convertx.img_compress',
                'user_id'            => $userId,
            ]);
            if (empty($secureResult['success'])) {
                $errors[] = $f['name'] . ': ' . ($secureResult['error'] ?? 'security check failed');
                continue;
            }
            $srcPath = $secureResult['path'];

            $outExt  = $outputFmt ?: $ext;
            $outExt  = ($outExt === 'jpeg') ? 'jpg' : $outExt;
            $baseName = pathinfo($f['name'], PATHINFO_FILENAME);
            $outName  = $baseName . '_compressed.' . $outExt;
            $outPath  = $tmpDir . '/' . uniqid('img_out_', true) . '.' . $outExt;

            $origSize = filesize($srcPath);

            try {
                $this->svc->compressImage($srcPath, $outPath, $quality, $maxWidth);
                $newSize = file_exists($outPath) ? filesize($outPath) : 0;
                $results[] = [
                    'src_path'    => $srcPath,
                    'src_name'    => $f['name'],
                    'out_path'    => $outPath,
                    'out_name'    => $outName,
                    'orig_size'   => $origSize,
                    'new_size'    => $newSize,
                    'saved_bytes' => max(0, $origSize - $newSize),
                    'saved_pct'   => $origSize > 0 ? round(max(0, $origSize - $newSize) / $origSize * 100, 1) : 0,
                ];
            } catch (\Throwable $e) {
                @unlink($srcPath);
                $errors[] = $f['name'] . ': ' . $e->getMessage();
            }
        }

        // Cleanup source images
        foreach ($results as $r) {
            @unlink($r['src_path']);
        }

        if (empty($results)) {
            $this->jsonError('No images could be compressed. ' . implode('; ', $errors), 400);
            return;
        }

        if (count($results) === 1) {
            $r     = $results[0];
            $token = $this->storeDownloadToken($r['out_path'], $r['out_name'], 'image/*');
            $this->cleanOutputBuffers();
            header('Content-Type: application/json');
            echo json_encode([
                'success'     => true,
                'token'       => $token,
                'filename'    => $r['out_name'],
                'original_size' => $r['orig_size'],
                'new_size'    => $r['new_size'],
                'saved_bytes' => $r['saved_bytes'],
                'saved_pct'   => $r['saved_pct'],
                'errors'      => $errors,
            ]);
            return;
        }

        // Multiple images — ZIP
        if (!class_exists('ZipArchive')) {
            foreach ($results as $r) {
                @unlink($r['out_path']);
            }
            $this->jsonError('ZIP creation requires the PHP ZipArchive extension.', 501);
            return;
        }

        $zipPath = $tmpDir . '/' . uniqid('img_zip_', true) . '.zip';
        $zip     = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            foreach ($results as $r) {
                @unlink($r['out_path']);
            }
            $this->jsonError('Could not create ZIP archive.', 500);
            return;
        }
        $totalOrig = 0;
        $totalNew  = 0;
        foreach ($results as $r) {
            $zip->addFile($r['out_path'], $r['out_name']);
            $totalOrig += $r['orig_size'];
            $totalNew  += $r['new_size'];
        }
        $zip->close();
        foreach ($results as $r) {
            @unlink($r['out_path']);
        }

        $token = $this->storeDownloadToken($zipPath, 'compressed_images.zip', 'application/zip');

        try {
            ActivityLogger::logCreate($userId, 'convertx', 'img_compress', 0, [
                'image_count'    => count($results),
                'original_size'  => $totalOrig,
                'compressed_size'=> $totalNew,
            ]);
        } catch (\Throwable $_) {}
        $this->logToolJob(
            'img_compress',
            array_column($results, 'src_name'),
            'compressed_images.zip',
            $totalOrig, $totalNew
        );

        $this->cleanOutputBuffers();
        header('Content-Type: application/json');
        echo json_encode([
            'success'       => true,
            'token'         => $token,
            'filename'      => 'compressed_images.zip',
            'original_size' => $totalOrig,
            'new_size'      => $totalNew,
            'saved_bytes'   => max(0, $totalOrig - $totalNew),
            'saved_pct'     => $totalOrig > 0 ? round(max(0, $totalOrig - $totalNew) / $totalOrig * 100, 1) : 0,
            'count'         => count($results),
            'errors'        => $errors,
        ]);
    }

    // ================================================================== //
    //  Resize Images                                                      //
    // ================================================================== //

    public function showResizeImages(): void
    {
        $limits = $this->imgLimits();
        $this->render('img-resize', [
            'title'        => 'Resize Images',
            'user'         => Auth::user(),
            'hasGd'        => $this->svc->hasGd(),
            'maxFiles'     => $limits['max_files'],
            'maxSizeMb'    => $limits['max_size_mb'],
            'allowedExts'  => $limits['allowed_exts'],
        ]);
    }

    public function submitResizeImages(): void
    {
        ob_start();
        try {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->jsonError('Invalid request token', 403);
            return;
        }
        $userId = Auth::id();
        if (!$userId) {
            $this->jsonError('Authentication required', 401);
            return;
        }

        $limits   = $this->imgLimits();
        $files    = $_FILES['images'] ?? null;
        if (!$files || empty($files['name'])) {
            $this->jsonError('No files uploaded', 400);
            return;
        }

        $fileList = $this->normaliseFiles($files);
        if (count($fileList) > $limits['max_files']) {
            $this->jsonError('Maximum ' . $limits['max_files'] . ' images at once.', 400);
            return;
        }

        $opts = [
            'width'          => max(0, (int) ($_POST['width']  ?? 0)),
            'height'         => max(0, (int) ($_POST['height'] ?? 0)),
            'percent'        => max(0, min(200, (int) ($_POST['percent'] ?? 0))),
            'maintain_ratio' => !empty($_POST['maintain_ratio']),
            'quality'        => max(1, min(100, (int) ($_POST['quality'] ?? 90))),
        ];

        $outputFmt = strtolower(trim($_POST['output_format'] ?? ''));
        if (!in_array($outputFmt, array_merge($limits['allowed_exts'], ['jpeg', '']), true)) {
            $outputFmt = '';
        }

        $tmpDir = BASE_PATH . '/storage/uploads/convertx/' . $userId . '/pdftools';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $results = [];
        $errors  = [];

        foreach ($fileList as $f) {
            if ($f['error'] !== UPLOAD_ERR_OK) {
                $errors[] = $f['name'] . ': upload error';
                continue;
            }
            if ($f['size'] > $limits['max_size_bytes']) {
                $errors[] = $f['name'] . ': file too large (max ' . $limits['max_size_mb'] . ' MB)';
                continue;
            }
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $limits['allowed_exts'], true)) {
                $errors[] = $f['name'] . ': unsupported format';
                continue;
            }

            $secureResult = SecureUpload::process($f, [
                'destination_dir'    => $tmpDir,
                'allowed_extensions' => $limits['allowed_exts'],
                'max_size'           => $limits['max_size_bytes'],
                'filename_prefix'    => 'resize_src',
                'source'             => 'convertx.img_resize',
                'user_id'            => $userId,
            ]);
            if (empty($secureResult['success'])) {
                $errors[] = $f['name'] . ': ' . ($secureResult['error'] ?? 'security check failed');
                continue;
            }
            $srcPath = $secureResult['path'];

            $outExt   = $outputFmt ?: $ext;
            $outExt   = ($outExt === 'jpeg') ? 'jpg' : $outExt;
            $outName  = pathinfo($f['name'], PATHINFO_FILENAME) . '_resized.' . $outExt;
            $outPath  = $tmpDir . '/' . uniqid('resize_out_', true) . '.' . $outExt;
            $origSize = filesize($srcPath);

            try {
                $this->svc->resizeImage($srcPath, $outPath, $opts);
                $newSize   = file_exists($outPath) ? filesize($outPath) : 0;
                $results[] = [
                    'src_path'  => $srcPath,
                    'src_name'  => $f['name'],
                    'out_path'  => $outPath,
                    'out_name'  => $outName,
                    'orig_size' => $origSize,
                    'new_size'  => $newSize,
                ];
            } catch (\Throwable $e) {
                @unlink($srcPath);
                $errors[] = $f['name'] . ': ' . $e->getMessage();
            }
        }

        foreach ($results as $r) {
            @unlink($r['src_path']);
        }

        if (empty($results)) {
            $this->jsonError('No images could be resized. ' . implode('; ', $errors), 400);
            return;
        }

        if (count($results) === 1) {
            $r     = $results[0];
            $token = $this->storeDownloadToken($r['out_path'], $r['out_name'], 'image/*');
            $this->cleanOutputBuffers();
            header('Content-Type: application/json');
            echo json_encode([
                'success'       => true,
                'token'         => $token,
                'filename'      => $r['out_name'],
                'original_size' => $r['orig_size'],
                'new_size'      => $r['new_size'],
                'count'         => 1,
                'errors'        => $errors,
            ]);
            return;
        }

        // Multiple — ZIP
        if (!class_exists('ZipArchive')) {
            foreach ($results as $r) { @unlink($r['out_path']); }
            $this->jsonError('ZIP creation requires the PHP ZipArchive extension.', 501);
            return;
        }

        $zipPath = $tmpDir . '/' . uniqid('resize_zip_', true) . '.zip';
        $zip     = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            foreach ($results as $r) { @unlink($r['out_path']); }
            $this->jsonError('Could not create ZIP archive.', 500);
            return;
        }
        $totalOrig = 0;
        $totalNew  = 0;
        foreach ($results as $r) {
            $zip->addFile($r['out_path'], $r['out_name']);
            $totalOrig += $r['orig_size'];
            $totalNew  += $r['new_size'];
        }
        $zip->close();
        foreach ($results as $r) { @unlink($r['out_path']); }

        $token = $this->storeDownloadToken($zipPath, 'resized_images.zip', 'application/zip');
        $this->logToolJob('img_resize', array_column($results, 'src_name'), 'resized_images.zip', $totalOrig, $totalNew);
        header('Content-Type: application/json');
        echo json_encode([
            'success'       => true,
            'token'         => $token,
            'filename'      => 'resized_images.zip',
            'original_size' => $totalOrig,
            'new_size'      => $totalNew,
            'count'         => count($results),
            'errors'        => $errors,
        ]);
        } catch (\Throwable $e) {
            Logger::error('submitResizeImages: ' . $e->getMessage());
            $this->cleanOutputBuffers();
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    // ================================================================== //
    //  Crop Image                                                         //
    // ================================================================== //

    public function showCropImage(): void
    {
        $limits = $this->imgLimits();
        $this->render('img-crop', [
            'title'       => 'Crop Image',
            'user'        => Auth::user(),
            'hasGd'       => $this->svc->hasGd(),
            'maxFiles'    => $limits['max_files'],
            'maxSizeMb'   => $limits['max_size_mb'],
            'allowedExts' => $limits['allowed_exts'],
        ]);
    }

    public function submitCropImage(): void
    {
        ob_start();
        try {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->jsonError('Invalid request token', 403);
            return;
        }
        $userId = Auth::id();
        if (!$userId) {
            $this->jsonError('Authentication required', 401);
            return;
        }

        $limits = $this->imgLimits();

        // Accept either single file (images[0]) or multi-file (images[])
        $rawFiles = $_FILES['images'] ?? null;
        if (!$rawFiles || empty($rawFiles['name'])) {
            $this->jsonError('No files uploaded', 400);
            return;
        }

        $fileList = $this->normaliseFiles($rawFiles);
        if (count($fileList) > $limits['max_files']) {
            $this->jsonError('Maximum ' . $limits['max_files'] . ' images at once.', 400);
            return;
        }

        $opts = [
            'x'           => max(0, (int) ($_POST['x']           ?? 0)),
            'y'           => max(0, (int) ($_POST['y']           ?? 0)),
            'crop_width'  => max(1, (int) ($_POST['crop_width']  ?? 100)),
            'crop_height' => max(1, (int) ($_POST['crop_height'] ?? 100)),
            'quality'     => max(1, min(100, (int) ($_POST['quality'] ?? 90))),
        ];

        $tmpDir = BASE_PATH . '/storage/uploads/convertx/' . $userId . '/pdftools';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $results = [];
        $errors  = [];

        foreach ($fileList as $f) {
            if ($f['error'] !== UPLOAD_ERR_OK) {
                $errors[] = $f['name'] . ': upload error';
                continue;
            }
            if ($f['size'] > $limits['max_size_bytes']) {
                $errors[] = $f['name'] . ': file too large (max ' . $limits['max_size_mb'] . ' MB)';
                continue;
            }
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $limits['allowed_exts'], true)) {
                $errors[] = $f['name'] . ': unsupported format';
                continue;
            }

            $secureResult = SecureUpload::process($f, [
                'destination_dir'    => $tmpDir,
                'allowed_extensions' => $limits['allowed_exts'],
                'max_size'           => $limits['max_size_bytes'],
                'filename_prefix'    => 'crop_src',
                'source'             => 'convertx.img_crop',
                'user_id'            => $userId,
            ]);
            if (empty($secureResult['success'])) {
                $errors[] = $f['name'] . ': ' . ($secureResult['error'] ?? 'security check failed');
                continue;
            }
            $srcPath = $secureResult['path'];

            $outExt  = ($ext === 'jpeg') ? 'jpg' : $ext;
            $outName = pathinfo($f['name'], PATHINFO_FILENAME) . '_cropped.' . $outExt;
            $outPath = $tmpDir . '/' . uniqid('crop_out_', true) . '.' . $outExt;

            try {
                $this->svc->cropImage($srcPath, $outPath, $opts);
                $results[] = [
                    'src_path' => $srcPath,
                    'src_name' => $f['name'],
                    'out_path' => $outPath,
                    'out_name' => $outName,
                    'new_size' => file_exists($outPath) ? filesize($outPath) : 0,
                ];
            } catch (\Throwable $e) {
                @unlink($srcPath);
                $errors[] = $f['name'] . ': ' . $e->getMessage();
            }
        }

        foreach ($results as $r) {
            @unlink($r['src_path']);
        }

        if (empty($results)) {
            $this->jsonError('No images could be cropped. ' . implode('; ', $errors), 400);
            return;
        }

        if (count($results) === 1) {
            $r     = $results[0];
            $token = $this->storeDownloadToken($r['out_path'], $r['out_name'], 'image/*');
            $this->cleanOutputBuffers();
            header('Content-Type: application/json');
            echo json_encode([
                'success'  => true,
                'token'    => $token,
                'filename' => $r['out_name'],
                'new_size' => $r['new_size'],
                'count'    => 1,
                'errors'   => $errors,
            ]);
            return;
        }

        // Multiple — ZIP
        if (!class_exists('ZipArchive')) {
            foreach ($results as $r) { @unlink($r['out_path']); }
            $this->jsonError('ZIP creation requires the PHP ZipArchive extension.', 501);
            return;
        }

        $zipPath = $tmpDir . '/' . uniqid('crop_zip_', true) . '.zip';
        $zip     = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            foreach ($results as $r) { @unlink($r['out_path']); }
            $this->jsonError('Could not create ZIP archive.', 500);
            return;
        }
        foreach ($results as $r) {
            $zip->addFile($r['out_path'], $r['out_name']);
        }
        $zip->close();
        foreach ($results as $r) { @unlink($r['out_path']); }

        $token = $this->storeDownloadToken($zipPath, 'cropped_images.zip', 'application/zip');
        $this->logToolJob('img_crop', array_column($results, 'src_name'), 'cropped_images.zip', 0, array_sum(array_column($results, 'new_size')));
        $this->cleanOutputBuffers();
        header('Content-Type: application/json');
        echo json_encode([
            'success'  => true,
            'token'    => $token,
            'filename' => 'cropped_images.zip',
            'count'    => count($results),
            'errors'   => $errors,
        ]);
        } catch (\Throwable $e) {
            Logger::error('submitCropImage: ' . $e->getMessage());
            $this->cleanOutputBuffers();
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    // ================================================================== //
    //  Watermark Image                                                    //
    // ================================================================== //

    public function showWatermarkImage(): void
    {
        $limits = $this->imgLimits();
        $this->render('img-watermark', [
            'title'       => 'Watermark Image',
            'user'        => Auth::user(),
            'hasGd'       => $this->svc->hasGd(),
            'maxFiles'    => $limits['max_files'],
            'maxSizeMb'   => $limits['max_size_mb'],
            'allowedExts' => $limits['allowed_exts'],
        ]);
    }

    public function submitWatermarkImage(): void
    {
        ob_start();
        try {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->jsonError('Invalid request token', 403);
            return;
        }
        $userId = Auth::id();
        if (!$userId) {
            $this->jsonError('Authentication required', 401);
            return;
        }

        $limits = $this->imgLimits();
        $files  = $_FILES['images'] ?? null;
        if (!$files || empty($files['name'])) {
            $this->jsonError('No files uploaded', 400);
            return;
        }

        $fileList = $this->normaliseFiles($files);
        if (count($fileList) > $limits['max_files']) {
            $this->jsonError('Maximum ' . $limits['max_files'] . ' images at once.', 400);
            return;
        }

        $tmpDir = BASE_PATH . '/storage/uploads/convertx/' . $userId . '/pdftools';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        // Handle optional watermark image upload
        $wmImagePath = null;
        $wmFile      = $_FILES['watermark_image'] ?? null;
        if ($wmFile && $wmFile['error'] === UPLOAD_ERR_OK) {
            $wmExt = strtolower(pathinfo($wmFile['name'], PATHINFO_EXTENSION));
            if (in_array($wmExt, ['jpg', 'jpeg', 'png', 'gif', 'webp'], true)) {
                $wmResult = SecureUpload::process($wmFile, [
                    'destination_dir'    => $tmpDir,
                    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                    'max_size'           => $limits['max_size_bytes'],
                    'filename_prefix'    => 'wm',
                    'source'             => 'convertx.img_watermark_overlay',
                    'user_id'            => $userId,
                ]);
                if (!empty($wmResult['success'])) {
                    $wmImagePath = $wmResult['path'];
                }
            }
        }

        $opts = [
            'text'                => (string) ($_POST['text']       ?? 'Watermark'),
            'font_size'           => max(8, min(72, (int) ($_POST['font_size'] ?? 24))),
            'opacity'             => max(0, min(100, (int) ($_POST['opacity']  ?? 50))),
            'position'            => in_array($_POST['position'] ?? '', ['center','topleft','topright','bottomleft','bottomright','custom'], true)
                                        ? $_POST['position'] : 'bottomright',
            'color_hex'           => preg_match('/^#?[0-9a-fA-F]{6}$/', $_POST['color_hex'] ?? '')
                                        ? ltrim($_POST['color_hex'], '#') : 'ffffff',
            'watermark_image_path'=> $wmImagePath,
            'quality'             => max(1, min(100, (int) ($_POST['quality'] ?? 90))),
            'rotation'            => (int) ($_POST['rotation'] ?? 0),
            'custom_x_pct'        => max(0, min(100, (float) ($_POST['custom_x_pct'] ?? 90))),
            'custom_y_pct'        => max(0, min(100, (float) ($_POST['custom_y_pct'] ?? 90))),
        ];

        $results = [];
        $errors  = [];

        foreach ($fileList as $f) {
            if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > $limits['max_size_bytes']) {
                $errors[] = $f['name'] . ': upload error or too large';
                continue;
            }
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $limits['allowed_exts'], true)) {
                $errors[] = $f['name'] . ': unsupported format';
                continue;
            }

            $secureResult = SecureUpload::process($f, [
                'destination_dir'    => $tmpDir,
                'allowed_extensions' => $limits['allowed_exts'],
                'max_size'           => $limits['max_size_bytes'],
                'filename_prefix'    => 'wm_src',
                'source'             => 'convertx.img_watermark',
                'user_id'            => $userId,
            ]);
            if (empty($secureResult['success'])) {
                $errors[] = $f['name'] . ': ' . ($secureResult['error'] ?? 'security check failed');
                continue;
            }
            $srcPath = $secureResult['path'];

            $outExt  = ($ext === 'jpeg') ? 'jpg' : $ext;
            $outName = pathinfo($f['name'], PATHINFO_FILENAME) . '_watermarked.' . $outExt;
            $outPath = $tmpDir . '/' . uniqid('wm_out_', true) . '.' . $outExt;

            try {
                $this->svc->watermarkImage($srcPath, $outPath, $opts);
                $results[] = [
                    'src_path' => $srcPath,
                    'src_name' => $f['name'],
                    'out_path' => $outPath,
                    'out_name' => $outName,
                    'new_size' => file_exists($outPath) ? filesize($outPath) : 0,
                ];
            } catch (\Throwable $e) {
                @unlink($srcPath);
                $errors[] = $f['name'] . ': ' . $e->getMessage();
            }
        }

        if ($wmImagePath) {
            @unlink($wmImagePath);
        }
        foreach ($results as $r) {
            @unlink($r['src_path']);
        }

        if (empty($results)) {
            $this->jsonError('No images could be watermarked. ' . implode('; ', $errors), 400);
            return;
        }

        if (count($results) === 1) {
            $r     = $results[0];
            $token = $this->storeDownloadToken($r['out_path'], $r['out_name'], 'image/*');
            $this->cleanOutputBuffers();
            header('Content-Type: application/json');
            echo json_encode([
                'success'  => true,
                'token'    => $token,
                'filename' => $r['out_name'],
                'new_size' => $r['new_size'],
                'count'    => 1,
                'errors'   => $errors,
            ]);
            return;
        }

        if (!class_exists('ZipArchive')) {
            foreach ($results as $r) { @unlink($r['out_path']); }
            $this->jsonError('ZIP creation requires the PHP ZipArchive extension.', 501);
            return;
        }

        $zipPath = $tmpDir . '/' . uniqid('wm_zip_', true) . '.zip';
        $zip     = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            foreach ($results as $r) { @unlink($r['out_path']); }
            $this->jsonError('Could not create ZIP archive.', 500);
            return;
        }
        foreach ($results as $r) {
            $zip->addFile($r['out_path'], $r['out_name']);
        }
        $zip->close();
        foreach ($results as $r) { @unlink($r['out_path']); }

        $token = $this->storeDownloadToken($zipPath, 'watermarked_images.zip', 'application/zip');
        $this->logToolJob('img_watermark', array_column($results, 'src_name'), 'watermarked_images.zip', 0, array_sum(array_column($results, 'new_size')));

        $this->cleanOutputBuffers();
        header('Content-Type: application/json');
        echo json_encode([
            'success'  => true,
            'token'    => $token,
            'filename' => 'watermarked_images.zip',
            'count'    => count($results),
            'errors'   => $errors,
        ]);
        } catch (\Throwable $e) {
            Logger::error('submitWatermarkImage: ' . $e->getMessage());
            $this->cleanOutputBuffers();
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    // ================================================================== //
    //  Meme Generator                                                     //
    // ================================================================== //

    public function showMemeGenerator(): void
    {
        $limits = $this->imgLimits();
        $this->render('img-meme', [
            'title'       => 'Meme Generator',
            'user'        => Auth::user(),
            'hasGd'       => $this->svc->hasGd(),
            'maxFiles'    => $limits['max_files'],
            'maxSizeMb'   => $limits['max_size_mb'],
            'allowedExts' => $limits['allowed_exts'],
        ]);
    }

    public function submitMemeGenerator(): void
    {
        ob_start();
        try {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->jsonError('Invalid request token', 403);
            return;
        }
        $userId = Auth::id();
        if (!$userId) {
            $this->jsonError('Authentication required', 401);
            return;
        }

        $limits   = $this->imgLimits();
        $rawFiles = $_FILES['images'] ?? null;
        if (!$rawFiles || empty($rawFiles['name'])) {
            $this->jsonError('No files uploaded', 400);
            return;
        }

        $fileList = $this->normaliseFiles($rawFiles);
        if (count($fileList) > $limits['max_files']) {
            $this->jsonError('Maximum ' . $limits['max_files'] . ' images at once.', 400);
            return;
        }

        $validColors = ['white', 'black', 'yellow'];
        $opts = [
            'top_text'    => substr((string) ($_POST['top_text']    ?? ''), 0, 200),
            'bottom_text' => substr((string) ($_POST['bottom_text'] ?? ''), 0, 200),
            'font_size'   => max(12, min(120, (int) ($_POST['font_size'] ?? 48))),
            'text_color'  => in_array($_POST['text_color'] ?? '', $validColors, true)
                                ? $_POST['text_color'] : 'white',
            'stroke_color'=> 'black',
            'quality'     => max(1, min(100, (int) ($_POST['quality'] ?? 92))),
        ];

        $tmpDir = BASE_PATH . '/storage/uploads/convertx/' . $userId . '/pdftools';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $results = [];
        $errors  = [];

        foreach ($fileList as $f) {
            if ($f['error'] !== UPLOAD_ERR_OK) {
                $errors[] = $f['name'] . ': upload error';
                continue;
            }
            if ($f['size'] > $limits['max_size_bytes']) {
                $errors[] = $f['name'] . ': file too large (max ' . $limits['max_size_mb'] . ' MB)';
                continue;
            }
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $limits['allowed_exts'], true)) {
                $errors[] = $f['name'] . ': unsupported format';
                continue;
            }

            $secureResult = SecureUpload::process($f, [
                'destination_dir'    => $tmpDir,
                'allowed_extensions' => $limits['allowed_exts'],
                'max_size'           => $limits['max_size_bytes'],
                'filename_prefix'    => 'meme_src',
                'source'             => 'convertx.img_meme',
                'user_id'            => $userId,
            ]);
            if (empty($secureResult['success'])) {
                $errors[] = $f['name'] . ': ' . ($secureResult['error'] ?? 'security check failed');
                continue;
            }
            $srcPath = $secureResult['path'];

            $outExt  = ($ext === 'jpeg') ? 'jpg' : $ext;
            $outName = pathinfo($f['name'], PATHINFO_FILENAME) . '_meme.' . $outExt;
            $outPath = $tmpDir . '/' . uniqid('meme_out_', true) . '.' . $outExt;

            try {
                $this->svc->addMemeText($srcPath, $outPath, $opts);
                $results[] = [
                    'src_path' => $srcPath,
                    'src_name' => $f['name'],
                    'out_path' => $outPath,
                    'out_name' => $outName,
                    'new_size' => file_exists($outPath) ? filesize($outPath) : 0,
                ];
            } catch (\Throwable $e) {
                @unlink($srcPath);
                $errors[] = $f['name'] . ': ' . $e->getMessage();
            }
        }

        foreach ($results as $r) {
            @unlink($r['src_path']);
        }

        if (empty($results)) {
            $this->jsonError('No memes could be created. ' . implode('; ', $errors), 400);
            return;
        }

        if (count($results) === 1) {
            $r     = $results[0];
            $token = $this->storeDownloadToken($r['out_path'], $r['out_name'], 'image/*');
            $this->cleanOutputBuffers();
            header('Content-Type: application/json');
            echo json_encode([
                'success'  => true,
                'token'    => $token,
                'filename' => $r['out_name'],
                'new_size' => $r['new_size'],
                'count'    => 1,
                'errors'   => $errors,
            ]);
            return;
        }

        // Multiple — ZIP
        if (!class_exists('ZipArchive')) {
            foreach ($results as $r) { @unlink($r['out_path']); }
            $this->jsonError('ZIP creation requires the PHP ZipArchive extension.', 501);
            return;
        }

        $zipPath = $tmpDir . '/' . uniqid('meme_zip_', true) . '.zip';
        $zip     = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            foreach ($results as $r) { @unlink($r['out_path']); }
            $this->jsonError('Could not create ZIP archive.', 500);
            return;
        }
        foreach ($results as $r) {
            $zip->addFile($r['out_path'], $r['out_name']);
        }
        $zip->close();
        foreach ($results as $r) { @unlink($r['out_path']); }

        $token = $this->storeDownloadToken($zipPath, 'memes.zip', 'application/zip');
        $this->logToolJob('img_meme', array_column($results, 'src_name'), 'memes.zip', 0, array_sum(array_column($results, 'new_size')));
        $this->cleanOutputBuffers();
        header('Content-Type: application/json');
        echo json_encode([
            'success'  => true,
            'token'    => $token,
            'filename' => 'memes.zip',
            'count'    => count($results),
            'errors'   => $errors,
        ]);
        } catch (\Throwable $e) {
            Logger::error('submitMemeGenerator: ' . $e->getMessage());
            $this->cleanOutputBuffers();
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    // ================================================================== //
    //  Rotate Images                                                      //
    // ================================================================== //

    public function showRotateImages(): void
    {
        $limits = $this->imgLimits();
        $this->render('img-rotate', [
            'title'       => 'Rotate Images',
            'user'        => Auth::user(),
            'hasGd'       => $this->svc->hasGd(),
            'maxFiles'    => $limits['max_files'],
            'maxSizeMb'   => $limits['max_size_mb'],
            'allowedExts' => $limits['allowed_exts'],
        ]);
    }

    public function submitRotateImages(): void
    {
        ob_start();
        try {
        if (!Security::validateCsrfToken($_POST['_token'] ?? '')) {
            $this->jsonError('Invalid request token', 403);
            return;
        }
        $userId = Auth::id();
        if (!$userId) {
            $this->jsonError('Authentication required', 401);
            return;
        }

        $limits = $this->imgLimits();
        $files  = $_FILES['images'] ?? null;
        if (!$files || empty($files['name'])) {
            $this->jsonError('No files uploaded', 400);
            return;
        }

        $fileList = $this->normaliseFiles($files);
        if (count($fileList) > $limits['max_files']) {
            $this->jsonError('Maximum ' . $limits['max_files'] . ' images at once.', 400);
            return;
        }

        // Normalize degrees to [0, 359] range (handles negative values too)
        $degrees = (int) ($_POST['degrees'] ?? 90);
        $degrees = ((($degrees % 360) + 360) % 360);
        $quality = max(1, min(100, (int) ($_POST['quality'] ?? 90)));

        $tmpDir = BASE_PATH . '/storage/uploads/convertx/' . $userId . '/pdftools';
        if (!is_dir($tmpDir)) {
            mkdir($tmpDir, 0755, true);
        }

        $results = [];
        $errors  = [];

        foreach ($fileList as $f) {
            if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > $limits['max_size_bytes']) {
                $errors[] = $f['name'] . ': upload error or too large';
                continue;
            }
            $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $limits['allowed_exts'], true)) {
                $errors[] = $f['name'] . ': unsupported format';
                continue;
            }

            $secureResult = SecureUpload::process($f, [
                'destination_dir'    => $tmpDir,
                'allowed_extensions' => $limits['allowed_exts'],
                'max_size'           => $limits['max_size_bytes'],
                'filename_prefix'    => 'rot_src',
                'source'             => 'convertx.img_rotate',
                'user_id'            => $userId,
            ]);
            if (empty($secureResult['success'])) {
                $errors[] = $f['name'] . ': ' . ($secureResult['error'] ?? 'security check failed');
                continue;
            }
            $srcPath = $secureResult['path'];

            $outExt  = ($ext === 'jpeg') ? 'jpg' : $ext;
            $outName = pathinfo($f['name'], PATHINFO_FILENAME) . '_rotated.' . $outExt;
            $outPath = $tmpDir . '/' . uniqid('rot_out_', true) . '.' . $outExt;

            try {
                $this->svc->rotateImage($srcPath, $outPath, $degrees, $quality);
                $results[] = [
                    'src_path' => $srcPath,
                    'src_name' => $f['name'],
                    'out_path' => $outPath,
                    'out_name' => $outName,
                    'new_size' => file_exists($outPath) ? filesize($outPath) : 0,
                ];
            } catch (\Throwable $e) {
                @unlink($srcPath);
                $errors[] = $f['name'] . ': ' . $e->getMessage();
            }
        }

        foreach ($results as $r) {
            @unlink($r['src_path']);
        }

        if (empty($results)) {
            $this->jsonError('No images could be rotated. ' . implode('; ', $errors), 400);
            return;
        }

        if (count($results) === 1) {
            $r     = $results[0];
            $token = $this->storeDownloadToken($r['out_path'], $r['out_name'], 'image/*');
            $this->cleanOutputBuffers();
            header('Content-Type: application/json');
            echo json_encode([
                'success'  => true,
                'token'    => $token,
                'filename' => $r['out_name'],
                'new_size' => $r['new_size'],
                'count'    => 1,
                'errors'   => $errors,
            ]);
            return;
        }

        if (!class_exists('ZipArchive')) {
            foreach ($results as $r) { @unlink($r['out_path']); }
            $this->jsonError('ZIP creation requires the PHP ZipArchive extension.', 501);
            return;
        }

        $zipPath = $tmpDir . '/' . uniqid('rot_zip_', true) . '.zip';
        $zip     = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            foreach ($results as $r) { @unlink($r['out_path']); }
            $this->jsonError('Could not create ZIP archive.', 500);
            return;
        }
        foreach ($results as $r) {
            $zip->addFile($r['out_path'], $r['out_name']);
        }
        $zip->close();
        foreach ($results as $r) { @unlink($r['out_path']); }

        $token = $this->storeDownloadToken($zipPath, 'rotated_images.zip', 'application/zip');
        $this->logToolJob('img_rotate', array_column($results, 'src_name'), 'rotated_images.zip', 0, array_sum(array_column($results, 'new_size')));

        $this->cleanOutputBuffers();
        header('Content-Type: application/json');
        echo json_encode([
            'success'  => true,
            'token'    => $token,
            'filename' => 'rotated_images.zip',
            'count'    => count($results),
            'errors'   => $errors,
        ]);
        } catch (\Throwable $e) {
            Logger::error('submitRotateImages: ' . $e->getMessage());
            $this->cleanOutputBuffers();
            $this->jsonError('Server error: ' . $e->getMessage(), 500);
        }
    }

    // ================================================================== //
    //  Photo Editor / Upscale / Remove BG (stub pages)                   //
    // ================================================================== //

    public function showPhotoEditor(): void
    {
        $this->render('img-editor', [
            'title' => 'Photo Editor',
            'user'  => Auth::user(),
        ]);
    }

    public function showUpscaleImage(): void
    {
        $this->render('img-upscale', [
            'title' => 'Upscale Image',
            'user'  => Auth::user(),
        ]);
    }

    public function showRemoveBg(): void
    {
        $this->render('img-remove-bg', [
            'title' => 'Remove Background',
            'user'  => Auth::user(),
        ]);
    }

    /**
     * GET /pdf-tools/download/:token
     * Stream a previously processed file to the browser.
     */
    public function download(string $token): void
    {
        $userId = Auth::id();
        if (!$userId) {
            http_response_code(401);
            echo 'Authentication required';
            return;
        }

        $token    = preg_replace('/[^a-f0-9]/', '', $token);
        $store    = $_SESSION['pdftools_downloads'][$userId][$token] ?? null;

        if (!$store || !file_exists($store['path'])) {
            http_response_code(404);
            echo 'File not found or token expired';
            return;
        }

        $path     = $store['path'];
        $filename = $store['filename'];
        $mime     = $store['mime'];

        // Unregister token after first download
        unset($_SESSION['pdftools_downloads'][$userId][$token]);

        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . addslashes($filename) . '"');
        header('Content-Length: ' . filesize($path));
        header('Cache-Control: private');
        readfile($path);

        // Schedule cleanup: delete temp file after streaming
        register_shutdown_function(function () use ($path) {
            @unlink($path);
        });
    }

    // ================================================================== //
    //  Helpers                                                            //
    // ================================================================== //

    /** Normalise a multi-file $_FILES array into a flat list. */
    private function normaliseFiles(array $files): array
    {
        $result = [];
        $count  = count((array) $files['name']);
        for ($i = 0; $i < $count; $i++) {
            $result[] = [
                'name'     => $files['name'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i],
            ];
        }
        return $result;
    }

    /**
     * Parse a page-range string such as "1,3-5,7" into an array of page numbers.
     * Returns [] for an empty/invalid string (means "all pages").
     */
    private function parsePageRange(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return [];
        }
        $pages = [];
        foreach (explode(',', $raw) as $part) {
            $part = trim($part);
            if (preg_match('/^(\d+)-(\d+)$/', $part, $m)) {
                $from = (int) $m[1];
                $to   = (int) $m[2];
                if ($from <= $to) {
                    foreach (range($from, $to) as $p) {
                        $pages[] = $p;
                    }
                }
            } elseif (ctype_digit($part)) {
                $pages[] = (int) $part;
            }
        }
        return array_values(array_unique($pages));
    }

    /** Store a processed file path in session and return a random token. */
    private function storeDownloadToken(string $path, string $filename, string $mime): string
    {
        $userId = Auth::id();
        $token  = bin2hex(random_bytes(16));
        if (!isset($_SESSION['pdftools_downloads'])) {
            $_SESSION['pdftools_downloads'] = [];
        }
        if (!isset($_SESSION['pdftools_downloads'][$userId])) {
            $_SESSION['pdftools_downloads'][$userId] = [];
        }
        $_SESSION['pdftools_downloads'][$userId][$token] = [
            'path'     => $path,
            'filename' => $filename,
            'mime'     => $mime,
        ];
        return $token;
    }

    /** Drain all active output buffers. */
    private function cleanOutputBuffers(): void
    {
        while (ob_get_level() > 0) {
            ob_end_clean();
        }
    }

    private function cleanupFiles(array $paths): void
    {
        foreach ($paths as $p) {
            if (is_file($p)) {
                @unlink($p);
            }
        }
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require PROJECT_PATH . '/views/layout.php';
    }

    private function jsonError(string $message, int $code = 400): void
    {
        $this->cleanOutputBuffers();
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => $message]);
    }
}
