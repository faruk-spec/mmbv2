<?php
/**
 * Conversion Service
 *
 * Core file-conversion engine for the ConvertX platform.
 * Handles format detection, conversion dispatch, and output delivery.
 *
 * Conversion chain example:
 *   image (scanned) → OCR → plain text → summarize → PDF
 *
 * @package MMB\Projects\ConvertX\Services
 */

namespace Projects\ConvertX\Services;

use Core\Logger;

class ConversionService
{
    // MIME-type → format slug mapping
    private const MIME_MAP = [
        'application/pdf'                                                    => 'pdf',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/msword'                                                 => 'doc',
        'application/vnd.oasis.opendocument.text'                           => 'odt',
        'application/rtf'                                                    => 'rtf',
        'text/plain'                                                         => 'txt',
        'text/html'                                                          => 'html',
        'text/markdown'                                                      => 'md',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.ms-excel'                                          => 'xls',
        'application/vnd.oasis.opendocument.spreadsheet'                    => 'ods',
        'text/csv'                                                           => 'csv',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation' => 'pptx',
        'application/vnd.ms-powerpoint'                                     => 'ppt',
        'application/vnd.oasis.opendocument.presentation'                   => 'odp',
        'image/jpeg'                                                         => 'jpg',
        'image/png'                                                          => 'png',
        'image/gif'                                                          => 'gif',
        'image/webp'                                                         => 'webp',
        'image/bmp'                                                          => 'bmp',
        'image/tiff'                                                         => 'tiff',
        'image/svg+xml'                                                      => 'svg',
    ];

    // Formats that may contain scanned (rasterised) content → trigger OCR
    private const OCR_CANDIDATE_FORMATS = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'webp'];

    /**
     * Detect the format of an uploaded file.
     *
     * @param string $filePath  Absolute path on disk
     * @param string $filename  Original filename (fallback for extension detection)
     * @return string  Format slug, e.g. 'pdf', 'docx', 'jpg'
     */
    public function detectFormat(string $filePath, string $filename = ''): string
    {
        // Prefer MIME detection via finfo
        if (function_exists('finfo_open') && file_exists($filePath)) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime  = finfo_file($finfo, $filePath);
            finfo_close($finfo);

            if ($mime && isset(self::MIME_MAP[$mime])) {
                return self::MIME_MAP[$mime];
            }
        }

        // Fallback: extension from original filename
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return $ext ?: 'unknown';
    }

    /**
     * Return the set of output formats that can be produced from $inputFormat.
     *
     * @param string $inputFormat
     * @return string[]
     */
    public function getSupportedOutputFormats(string $inputFormat): array
    {
        $config  = require PROJECT_PATH . '/config.php';
        $all     = array_merge(...array_values($config['formats']));

        // Remove the input format itself from the list
        return array_values(array_filter($all, fn($f) => $f !== $inputFormat));
    }

    /**
     * Determine whether the input file is a likely OCR candidate.
     *
     * @param string $inputFormat
     * @return bool
     */
    public function requiresOCR(string $inputFormat): bool
    {
        return in_array(strtolower($inputFormat), self::OCR_CANDIDATE_FORMATS, true);
    }

    /**
     * Perform the actual conversion.
     *
     * This method dispatches to the appropriate backend:
     *   - LibreOffice (document/spreadsheet/presentation)
     *   - Ghostscript / ImageMagick (image ↔ PDF)
     *   - Pandoc (markdown / plain-text variants)
     *   - Built-in PHP (CSV ↔ plain text)
     *
     * @param string $inputPath    Absolute path to source file
     * @param string $inputFormat
     * @param string $outputFormat
     * @param array  $options      Optional: ['quality' => 80, 'dpi' => 150, ...]
     * @return array{success: bool, output_path: string, error: string}
     */
    public function convert(
        string $inputPath,
        string $inputFormat,
        string $outputFormat,
        array  $options = []
    ): array {
        $outputDir  = dirname($inputPath);
        $baseName   = pathinfo($inputPath, PATHINFO_FILENAME);
        $outputPath = $outputDir . '/' . $baseName . '_converted.' . $outputFormat;

        try {
            $result = $this->dispatch($inputPath, $inputFormat, $outputFormat, $outputPath, $options);
            if ($result) {
                return ['success' => true, 'output_path' => $outputPath, 'error' => ''];
            }
            return ['success' => false, 'output_path' => '', 'error' => 'Conversion failed'];
        } catch (\Exception $e) {
            Logger::error('ConversionService::convert - ' . $e->getMessage());
            return ['success' => false, 'output_path' => '', 'error' => $e->getMessage()];
        }
    }

    /**
     * Dispatch conversion to the right backend tool.
     *
     * @throws \RuntimeException if no suitable backend is found
     */
    private function dispatch(
        string $inputPath,
        string $inputFormat,
        string $outputFormat,
        string $outputPath,
        array  $options
    ): bool {
        // Document / office formats → LibreOffice
        $officeFormats = ['pdf', 'docx', 'doc', 'odt', 'rtf', 'xlsx', 'xls', 'ods', 'csv', 'pptx', 'ppt', 'odp'];
        if (in_array($inputFormat, $officeFormats, true) || in_array($outputFormat, $officeFormats, true)) {
            return $this->convertWithLibreOffice($inputPath, $outputFormat, $outputPath);
        }

        // Image ↔ image → ImageMagick
        $imageFormats = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'svg'];
        if (in_array($inputFormat, $imageFormats, true) && in_array($outputFormat, $imageFormats, true)) {
            return $this->convertWithImageMagick($inputPath, $outputPath, $options);
        }

        // Plain-text variants → Pandoc
        $pandocFormats = ['md', 'html', 'txt', 'rst'];
        if (in_array($inputFormat, $pandocFormats, true) || in_array($outputFormat, $pandocFormats, true)) {
            return $this->convertWithPandoc($inputPath, $inputFormat, $outputFormat, $outputPath);
        }

        // CSV ↔ TXT (built-in)
        if ($inputFormat === 'csv' && $outputFormat === 'txt') {
            return $this->csvToText($inputPath, $outputPath);
        }

        throw new \RuntimeException(
            "No conversion backend for {$inputFormat} → {$outputFormat}"
        );
    }

    // ------------------------------------------------------------------ //
    //  Backend helpers                                                      //
    // ------------------------------------------------------------------ //

    private function convertWithLibreOffice(
        string $inputPath,
        string $outputFormat,
        string $outputPath
    ): bool {
        $outDir   = escapeshellarg(dirname($outputPath));
        $inFile   = escapeshellarg($inputPath);
        $fmt      = escapeshellarg($outputFormat);
        $cmd      = "libreoffice --headless --convert-to {$fmt} {$inFile} --outdir {$outDir} 2>&1";
        exec($cmd, $output, $code);

        if ($code !== 0) {
            Logger::warning('LibreOffice conversion failed: ' . implode("\n", $output));
            return false;
        }
        return true;
    }

    private function convertWithImageMagick(
        string $inputPath,
        string $outputPath,
        array  $options
    ): bool {
        $quality = (int) ($options['quality'] ?? 85);
        $in      = escapeshellarg($inputPath);
        $out     = escapeshellarg($outputPath);
        $cmd     = "convert {$in} -quality {$quality} {$out} 2>&1";
        exec($cmd, $output, $code);

        if ($code !== 0) {
            Logger::warning('ImageMagick conversion failed: ' . implode("\n", $output));
            return false;
        }
        return true;
    }

    private function convertWithPandoc(
        string $inputPath,
        string $inputFormat,
        string $outputFormat,
        string $outputPath
    ): bool {
        $in  = escapeshellarg($inputPath);
        $out = escapeshellarg($outputPath);
        $inf = escapeshellarg($inputFormat);
        $outf = escapeshellarg($outputFormat);
        $cmd = "pandoc -f {$inf} -t {$outf} {$in} -o {$out} 2>&1";
        exec($cmd, $output, $code);

        if ($code !== 0) {
            Logger::warning('Pandoc conversion failed: ' . implode("\n", $output));
            return false;
        }
        return true;
    }

    private function csvToText(string $inputPath, string $outputPath): bool
    {
        $lines = file($inputPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return false;
        }
        $text = implode("\n", array_map(function ($line) {
            return implode("\t", str_getcsv($line));
        }, $lines));
        return file_put_contents($outputPath, $text) !== false;
    }
}
