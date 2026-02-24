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
     * Priority order:
     *   1. Pure-PHP text conversions (always available, no external tools)
     *   2. Image ↔ image: GD (built-in) then ImageMagick fallback
     *   3. Office / document formats: LibreOffice
     *   4. Plain-text variants: Pandoc
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
        // 1. Pure-PHP text/markup conversions (no external dependencies)
        if ($this->canConvertWithPhp($inputFormat, $outputFormat)) {
            return $this->convertWithPhp($inputPath, $inputFormat, $outputFormat, $outputPath);
        }

        // 2. Image ↔ image: try GD first, then ImageMagick
        $imageFormats = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'svg'];
        if (in_array($inputFormat, $imageFormats, true) && in_array($outputFormat, $imageFormats, true)) {
            if ($this->convertWithGD($inputPath, $outputPath, $options)) {
                return true;
            }
            return $this->convertWithImageMagick($inputPath, $outputPath, $options);
        }

        // 3. Document / office formats → LibreOffice
        $officeFormats = ['pdf', 'docx', 'doc', 'odt', 'rtf', 'xlsx', 'xls', 'ods', 'csv', 'pptx', 'ppt', 'odp'];
        if (in_array($inputFormat, $officeFormats, true) || in_array($outputFormat, $officeFormats, true)) {
            return $this->convertWithLibreOffice($inputPath, $outputFormat, $outputPath);
        }

        // 4. Remaining plain-text variants → Pandoc
        $pandocFormats = ['md', 'html', 'txt', 'rst'];
        if (in_array($inputFormat, $pandocFormats, true) || in_array($outputFormat, $pandocFormats, true)) {
            return $this->convertWithPandoc($inputPath, $inputFormat, $outputFormat, $outputPath);
        }

        throw new \RuntimeException(
            "No conversion backend for {$inputFormat} → {$outputFormat}"
        );
    }

    /**
     * Return true if both formats can be handled with built-in PHP (no external tools).
     */
    private function canConvertWithPhp(string $inputFormat, string $outputFormat): bool
    {
        $phpFormats = ['txt', 'html', 'md', 'csv'];
        return in_array($inputFormat, $phpFormats, true)
            && in_array($outputFormat, $phpFormats, true);
    }

    /**
     * Pure-PHP conversion between text/markup formats.
     * Handles: txt↔html, txt↔csv, md→html, md→txt, csv→txt, same-format copy.
     */
    private function convertWithPhp(
        string $inputPath,
        string $inputFormat,
        string $outputFormat,
        string $outputPath
    ): bool {
        $content = file_get_contents($inputPath);
        if ($content === false) {
            return false;
        }

        // Same format – direct copy
        if ($inputFormat === $outputFormat) {
            return file_put_contents($outputPath, $content) !== false;
        }

        // HTML → TXT
        if ($inputFormat === 'html' && $outputFormat === 'txt') {
            return file_put_contents($outputPath, strip_tags($content)) !== false;
        }

        // TXT → HTML
        if ($inputFormat === 'txt' && $outputFormat === 'html') {
            $html = '<!DOCTYPE html><html><body><pre>'
                  . htmlspecialchars($content, ENT_QUOTES | ENT_SUBSTITUTE)
                  . '</pre></body></html>';
            return file_put_contents($outputPath, $html) !== false;
        }

        // MD → HTML
        if ($inputFormat === 'md' && $outputFormat === 'html') {
            return file_put_contents($outputPath, $this->basicMarkdownToHtml($content)) !== false;
        }

        // MD → TXT
        if ($inputFormat === 'md' && $outputFormat === 'txt') {
            // Strip common markdown syntax
            $text = preg_replace('/^#{1,6}\s+/m', '', $content);    // headers
            $text = preg_replace('/(\*\*|__|~~)(.*?)\1/', '$2', $text);   // bold/strike
            $text = preg_replace('/(\*|_)(.*?)\1/', '$2', $text);         // italic
            $text = preg_replace('/`{1,3}.*?`{1,3}/s', '', $text);        // code
            $text = preg_replace('/\[([^\]]+)\]\([^\)]+\)/', '$1', $text); // links
            return file_put_contents($outputPath, $text) !== false;
        }

        // CSV → TXT
        if ($inputFormat === 'csv' && $outputFormat === 'txt') {
            return $this->csvToText($inputPath, $outputPath);
        }

        // TXT → CSV (single-column)
        if ($inputFormat === 'txt' && $outputFormat === 'csv') {
            $lines  = explode("\n", str_replace("\r\n", "\n", $content));
            $output = implode("\n", array_map(fn($l) => '"' . str_replace('"', '""', $l) . '"', $lines));
            return file_put_contents($outputPath, $output) !== false;
        }

        // HTML → MD (basic)
        if ($inputFormat === 'html' && $outputFormat === 'md') {
            $text = strip_tags(
                preg_replace(['/<h[1-6][^>]*>/i', '/<\/h[1-6]>/i', '/<br\s*\/?>/i', '/<p[^>]*>/i'],
                             ['## ', "\n", "\n", "\n"], $content)
            );
            return file_put_contents($outputPath, trim($text)) !== false;
        }

        return false;
    }

    /**
     * Basic Markdown → HTML converter (no external library needed).
     */
    private function basicMarkdownToHtml(string $md): string
    {
        $lines  = explode("\n", str_replace("\r\n", "\n", $md));
        $html   = '';
        foreach ($lines as $line) {
            if (preg_match('/^### (.+)/', $line, $m)) {
                $html .= '<h3>' . htmlspecialchars($m[1]) . "</h3>\n";
            } elseif (preg_match('/^## (.+)/', $line, $m)) {
                $html .= '<h2>' . htmlspecialchars($m[1]) . "</h2>\n";
            } elseif (preg_match('/^# (.+)/', $line, $m)) {
                $html .= '<h1>' . htmlspecialchars($m[1]) . "</h1>\n";
            } elseif (trim($line) === '') {
                $html .= "<br>\n";
            } else {
                $escaped = htmlspecialchars($line, ENT_QUOTES | ENT_SUBSTITUTE);
                $escaped = preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $escaped);
                $escaped = preg_replace('/\*(.+?)\*/',     '<em>$1</em>',         $escaped);
                $escaped = preg_replace('/`(.+?)`/',       '<code>$1</code>',     $escaped);
                $html   .= '<p>' . $escaped . "</p>\n";
            }
        }
        return '<!DOCTYPE html><html><body>' . "\n" . $html . '</body></html>';
    }

    /**
     * Convert image using PHP's built-in GD extension.
     * Supports JPG, PNG, GIF, WebP, BMP output formats.
     */
    private function convertWithGD(string $inputPath, string $outputPath, array $options): bool
    {
        if (!extension_loaded('gd') || !file_exists($inputPath)) {
            return false;
        }

        $image = @imagecreatefromstring((string) file_get_contents($inputPath));
        if ($image === false) {
            return false;
        }

        $quality      = max(1, min(100, (int) ($options['quality'] ?? 85)));
        $outputFormat = strtolower(pathinfo($outputPath, PATHINFO_EXTENSION));

        $result = match ($outputFormat) {
            'jpg', 'jpeg' => imagejpeg($image, $outputPath, $quality),
            'png'  => imagepng($image, $outputPath, max(0, min(9, (int) round(9 - $quality / 11.2)))),
            'gif'  => imagegif($image, $outputPath),
            'webp' => function_exists('imagewebp') ? imagewebp($image, $outputPath, $quality) : false,
            'bmp'  => function_exists('imagebmp')  ? imagebmp($image, $outputPath)            : false,
            default => false,
        };

        imagedestroy($image);
        return (bool) $result;
    }

    // ------------------------------------------------------------------ //
    //  Backend helpers                                                      //
    // ------------------------------------------------------------------ //

    private function convertWithLibreOffice(
        string $inputPath,
        string $outputFormat,
        string $outputPath
    ): bool {
        // Check LibreOffice is available before attempting exec
        $lo = trim((string) shell_exec('which libreoffice 2>/dev/null'))
           ?: trim((string) shell_exec('which soffice 2>/dev/null'));
        if (!$lo) {
            throw new \RuntimeException(
                "LibreOffice is not installed on this server. "
                . "Only text/markup conversions (TXT, HTML, MD, CSV) and "
                . "image-to-image conversions are available without additional tools."
            );
        }

        $outDirReal = dirname($inputPath);
        $outDirEsc  = escapeshellarg($outDirReal);
        $inFile     = escapeshellarg($inputPath);
        $fmt        = escapeshellarg($outputFormat);
        $cmd        = "{$lo} --headless --convert-to {$fmt} {$inFile} --outdir {$outDirEsc} 2>&1";
        exec($cmd, $output, $code);

        if ($code !== 0) {
            Logger::warning('LibreOffice conversion failed: ' . implode("\n", $output));
            return false;
        }

        // LibreOffice names the output: {inputBasename}.{outputFormat}
        // Our expected path has the '_converted' suffix — rename if needed.
        $libreOutput = $outDirReal . '/' . pathinfo($inputPath, PATHINFO_FILENAME) . '.' . $outputFormat;
        if ($libreOutput !== $outputPath && file_exists($libreOutput)) {
            rename($libreOutput, $outputPath);
        }

        return file_exists($outputPath);
    }

    private function convertWithImageMagick(
        string $inputPath,
        string $outputPath,
        array  $options
    ): bool {
        $im = trim((string) shell_exec('which convert 2>/dev/null'))
           ?: trim((string) shell_exec('which magick 2>/dev/null'));
        if (!$im) {
            throw new \RuntimeException(
                "ImageMagick is not installed on this server. "
                . "Image-to-image conversion requires ImageMagick or PHP GD (GD failed too)."
            );
        }

        $quality = (int) ($options['quality'] ?? 85);
        $in      = escapeshellarg($inputPath);
        $out     = escapeshellarg($outputPath);
        $cmd     = "{$im} {$in} -quality {$quality} {$out} 2>&1";
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
        $pandoc = trim((string) shell_exec('which pandoc 2>/dev/null'));
        if (!$pandoc) {
            throw new \RuntimeException(
                "Pandoc is not installed on this server. "
                . "Plain-text markup conversion (MD, RST, HTML) requires Pandoc."
            );
        }

        $in  = escapeshellarg($inputPath);
        $out = escapeshellarg($outputPath);
        $inf = escapeshellarg($inputFormat);
        $outf = escapeshellarg($outputFormat);
        $cmd = "{$pandoc} -f {$inf} -t {$outf} {$in} -o {$out} 2>&1";
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
