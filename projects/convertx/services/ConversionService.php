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
     * Detect which conversion backends are available on this server.
     * Results are cached in a static variable so shell calls happen once per request.
     *
     * @return array{php:bool, gd:bool, libreoffice:bool, imagemagick:bool, pandoc:bool}
     */
    public function getAvailableBackends(): array
    {
        static $cache = null;
        if ($cache !== null) {
            return $cache;
        }

        $loRaw = trim((string) shell_exec('which libreoffice 2>/dev/null'));
        if (empty($loRaw)) {
            $loRaw = trim((string) shell_exec('which soffice 2>/dev/null'));
        }

        $imRaw = trim((string) shell_exec('which convert 2>/dev/null'));
        if (empty($imRaw)) {
            $imRaw = trim((string) shell_exec('which magick 2>/dev/null'));
        }

        $pandoc = trim((string) shell_exec('which pandoc 2>/dev/null'));

        $cache = [
            'php'          => true,
            'gd'           => extension_loaded('gd'),
            'libreoffice'  => !empty($loRaw),
            'imagemagick'  => !empty($imRaw),
            'pandoc'       => !empty($pandoc),
        ];

        return $cache;
    }

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
            return ['success' => false, 'output_path' => '', 'error' => "Conversion did not produce an output file for {$inputFormat} → {$outputFormat}. Check that the required tool (LibreOffice / ImageMagick) is installed and accessible by the web server."];
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

        $imageFormats  = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'svg'];
        $isInputImage  = in_array($inputFormat, $imageFormats, true);
        $isOutputImage = in_array($outputFormat, $imageFormats, true);

        // 2. Image ↔ image: try GD first, then ImageMagick, then LibreOffice Draw.
        //    GD is skipped for SVG/TIFF (it cannot decode those formats).
        //    LibreOffice Draw handles SVG natively and is a reliable fallback for
        //    formats that ImageMagick's policy.xml may block on Debian/Ubuntu.
        if ($isInputImage && $isOutputImage) {
            if ($this->convertWithGD($inputPath, $outputPath, $options)) {
                return true;
            }
            if ($this->convertWithImageMagick($inputPath, $outputPath, $options)) {
                return true;
            }
            // LibreOffice Draw as final fallback (handles SVG, TIFF, BMP, etc.)
            try {
                return $this->convertWithLibreOffice($inputPath, $inputFormat, $outputFormat, $outputPath);
            } catch (\RuntimeException $e) {
                return false; // LO not installed; all three backends failed
            }
        }

        // 3. Image → PDF: try ImageMagick first; fall back to LibreOffice Draw.
        //    ImageMagick often fails on Debian/Ubuntu where policy.xml sets
        //    rights="none" for the PDF coder.  LibreOffice Draw can open common
        //    image formats (PNG, JPG, GIF, WebP, BMP, SVG) and export to PDF
        //    via draw_pdf_Export without the policy restriction.
        if ($isInputImage && $outputFormat === 'pdf') {
            if ($this->convertWithImageMagick($inputPath, $outputPath, $options)) {
                return true;
            }
            // ImageMagick failed — try LibreOffice Draw as fallback
            return $this->convertWithLibreOffice($inputPath, $inputFormat, 'pdf', $outputPath);
        }

        // 4. PDF → image: ImageMagick (rasterisation).
        //    Throws a clear RuntimeException when IM is blocked/unavailable so
        //    the error message surfaces to the user instead of "Conversion failed".
        if ($inputFormat === 'pdf' && $isOutputImage) {
            if ($this->convertWithImageMagick($inputPath, $outputPath, $options)) {
                return true;
            }
            throw new \RuntimeException(
                "PDF to image conversion requires ImageMagick. "
                . "If ImageMagick is installed, check that /etc/ImageMagick-*/policy.xml "
                . "does not have rights=\"none\" for the PDF coder."
            );
        }

        // 4b. Image → writer format (docx / odt / rtf): create a proper document
        //     that embeds the image using PHP ZipArchive — no external tools needed.
        //     This is far more reliable than the 2-step chain (image→PDF→DOCX) which
        //     requires the optional libreoffice-pdfimport package for the PDF→Writer
        //     leg, and often silently produces empty output when that package is absent.
        $phpWriterFormats = ['docx', 'odt', 'rtf'];
        if ($isInputImage && in_array($outputFormat, $phpWriterFormats, true)) {
            if ($this->convertImageToDocumentWithPhp($inputPath, $inputFormat, $outputFormat, $outputPath)) {
                return true;
            }
            // ZipArchive unavailable or failed — fall through to chain
        }

        // 5. Cross-family: image → office/text (non-pdf)
        //    Chain: image → PDF (ImageMagick or LO Draw) → target (LibreOffice)
        if ($isInputImage && !$isOutputImage) {
            return $this->convertViaChain($inputPath, $inputFormat, $outputFormat, $outputPath, $options);
        }

        // 6. Cross-family: office/text → image (non-pdf input)
        //    Chain: input → PDF (LibreOffice) → image (ImageMagick)
        if (!$isInputImage && $isOutputImage) {
            return $this->convertViaChain($inputPath, $inputFormat, $outputFormat, $outputPath, $options);
        }

        // 7. Document / office formats → LibreOffice
        // Note: 'csv' is intentionally excluded from this list — CSV ↔ plain-text
        // pairs are already handled by the PHP engine above (step 1).  CSV → XLSX/ODS
        // is caught here because 'xlsx'/'ods' appear in $officeFormats as output.
        // 'html' is included so that docx→html and xlsx→html use LibreOffice's
        // HTML export filter rather than falling through to Pandoc (step 8), which
        // may not be installed.
        $officeFormats = ['pdf', 'docx', 'doc', 'odt', 'rtf', 'html', 'xlsx', 'xls', 'ods', 'pptx', 'ppt', 'odp'];
        if (in_array($inputFormat, $officeFormats, true) || in_array($outputFormat, $officeFormats, true)) {
            return $this->convertWithLibreOffice($inputPath, $inputFormat, $outputFormat, $outputPath);
        }

        // 8. Remaining plain-text variants → Pandoc
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

        // GD cannot reliably decode SVG (vector) or TIFF (LibTIFF not always compiled in).
        // Let ImageMagick or LibreOffice Draw handle these formats.
        $inputExt = strtolower(pathinfo($inputPath, PATHINFO_EXTENSION));
        if (in_array($inputExt, ['svg', 'tiff', 'tif'], true)) {
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

    /**
     * Map an output format to the explicit LibreOffice filter name.
     *
     * Using an explicit filter (e.g. "xlsx:Calc MS Excel 2007 XML") is far more
     * reliable than leaving LibreOffice to auto-detect; without it some server
     * installs silently produce empty files or fail with exit 0.
     *
     * @param string $outputFormat  e.g. 'xlsx'
     * @param string $inputFormat   e.g. 'ods' — used only to pick the right PDF filter
     * @return string  Argument for --convert-to, e.g. "xlsx:Calc MS Excel 2007 XML"
     */
    private static function getLibreOfficeFilter(string $outputFormat, string $inputFormat): string
    {
        // PDF export: LibreOffice has separate filters per application family
        if ($outputFormat === 'pdf') {
            $calcInputs    = ['xlsx', 'xls', 'ods', 'csv'];
            $impressInputs = ['pptx', 'ppt', 'odp'];
            $drawInputs    = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'tif', 'svg'];
            if (in_array($inputFormat, $calcInputs, true)) {
                return 'pdf:calc_pdf_Export';
            }
            if (in_array($inputFormat, $impressInputs, true)) {
                return 'pdf:impress_pdf_Export';
            }
            if (in_array($inputFormat, $drawInputs, true)) {
                return 'pdf:draw_pdf_Export';
            }
            return 'pdf:writer_pdf_Export';
        }

        // Image output from LibreOffice Draw.
        // Used when SVG or TIFF is opened in Draw and exported to a raster format
        // (the image↔image LibreOffice Draw fallback in dispatch() step 2).
        $drawInputs = ['svg', 'tiff', 'tif'];
        if (in_array($inputFormat, $drawInputs, true)) {
            $drawMap = [
                'png'  => 'png:draw_png_Export',
                'jpg'  => 'jpg:draw_jpg_Export',
                'jpeg' => 'jpg:draw_jpg_Export',
                'bmp'  => 'bmp:draw_bmp_Export',
                'gif'  => 'gif:draw_gif_Export',
                'webp' => 'webp:draw_webp_Export',
            ];
            if (isset($drawMap[$outputFormat])) {
                return $drawMap[$outputFormat];
            }
        }

        $map = [
            // Writer / text
            'docx' => 'docx:MS Word 2007 XML',
            'doc'  => 'doc:MS Word 97',
            'odt'  => 'odt:writer8',
            'rtf'  => 'rtf:Rich Text Format',
            'txt'  => 'txt:Text',
            // Calc / spreadsheet
            'xlsx' => 'xlsx:Calc MS Excel 2007 XML',
            'xls'  => 'xls:MS Excel 97',
            'ods'  => 'ods:calc8',
            'csv'  => 'csv:Text - txt - csv (StarCalc)',
            // Impress / presentation
            'pptx' => 'pptx:Impress MS PowerPoint 2007 XML',
            'ppt'  => 'ppt:MS PowerPoint 97',
            'odp'  => 'odp:impress8',
            // HTML
            'html' => 'html:HTML (StarWriter)',
        ];

        // Fall back to bare extension if not in map (LibreOffice will try auto-detect)
        return $map[$outputFormat] ?? $outputFormat;
    }

    /**
     * Two-step PDF-bridge conversion for cross-family formats.
     *
     * image  → office : image → PDF (ImageMagick) → office (LibreOffice)
     * office → image  : office → PDF (LibreOffice) → image (ImageMagick)
     * text   → image  : text  → PDF (LibreOffice) → image (ImageMagick)
     *
     * The intermediate PDF is written to the system temp directory and
     * deleted in the `finally` block regardless of success or failure.
     */
    private function convertViaChain(
        string $inputPath,
        string $inputFormat,
        string $outputFormat,
        string $outputPath,
        array  $options
    ): bool {
        $imageFormats = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'svg'];
        $isInputImage = in_array($inputFormat, $imageFormats, true);

        // Unique intermediate PDF in system temp dir
        $tmpPdf = sys_get_temp_dir() . '/cx_chain_' . getmypid() . '_' . uniqid() . '.pdf';

        try {
            // ── Step 1: convert input to intermediate PDF ──────────────────
            $step1Error = '';
            if ($isInputImage) {
                // Try ImageMagick first. It may fail if the server's ImageMagick
                // policy.xml has rights="none" for the PDF coder (Ubuntu/Debian default).
                // In that case fall back to LibreOffice Draw, which can open most
                // image formats and export them to PDF via draw_pdf_Export.
                $step1ok = $this->convertWithImageMagick($inputPath, $tmpPdf, $options);
                if (!$step1ok || !file_exists($tmpPdf)) {
                    try {
                        $step1ok = $this->convertWithLibreOffice($inputPath, $inputFormat, 'pdf', $tmpPdf);
                    } catch (\RuntimeException $e) {
                        $step1ok    = false;
                        $step1Error = $e->getMessage();
                    }
                }
            } else {
                try {
                    $step1ok = $this->convertWithLibreOffice($inputPath, $inputFormat, 'pdf', $tmpPdf);
                } catch (\RuntimeException $e) {
                    $step1ok    = false;
                    $step1Error = $e->getMessage();
                }
            }

            if (!$step1ok || !file_exists($tmpPdf)) {
                $hint = $step1Error
                    ? "Step 1 error: {$step1Error}"
                    : "Ensure both LibreOffice and ImageMagick are installed and that "
                      . "ImageMagick policy.xml allows PDF output.";
                throw new \RuntimeException(
                    "Two-step conversion failed at step 1 ({$inputFormat} → PDF). {$hint}"
                );
            }

            // ── Step 2: convert intermediate PDF to target ─────────────────
            if (in_array($outputFormat, $imageFormats, true)) {
                return $this->convertWithImageMagick($tmpPdf, $outputPath, $options);
            }
            return $this->convertWithLibreOffice($tmpPdf, 'pdf', $outputFormat, $outputPath);

        } finally {
            if (file_exists($tmpPdf)) {
                @unlink($tmpPdf);
            }
        }
    }

    /**
     * Move a file, falling back to copy+unlink when rename() would cross device boundaries.
     */
    private function safeMoveFile(string $from, string $to): bool
    {
        if (@rename($from, $to)) {
            return true;
        }
        // rename() fails across filesystems (e.g. /var/www → /tmp on separate mounts)
        if (@copy($from, $to)) {
            @unlink($from);
            return true;
        }
        return false;
    }

    // ------------------------------------------------------------------ //
    //  PHP-native document builders (no external tools)                    //
    // ------------------------------------------------------------------ //

    /**
     * Embed a source image directly inside a writer-format document using
     * PHP's built-in ZipArchive extension — no LibreOffice or ImageMagick needed.
     *
     * Supported output formats: docx, odt, rtf.
     *
     * This bypasses the unreliable image→PDF→DOCX two-step chain whose second
     * leg (PDF→DOCX via LibreOffice) requires the optional libreoffice-pdfimport
     * package and often produces empty output when that package is absent.
     */
    private function convertImageToDocumentWithPhp(
        string $inputPath,
        string $inputFormat,
        string $outputFormat,
        string $outputPath
    ): bool {
        if (!file_exists($inputPath)) {
            return false;
        }
        return match ($outputFormat) {
            'docx' => $this->convertImageToDocxWithPhp($inputPath, $inputFormat, $outputPath),
            'odt'  => $this->convertImageToOdtWithPhp($inputPath, $inputFormat, $outputPath),
            'rtf'  => $this->convertImageToRtfWithPhp($inputPath, $inputFormat, $outputPath),
            default => false,
        };
    }

    /**
     * Create a minimal but fully valid DOCX file with the image embedded.
     * A DOCX is a ZIP archive (Open Packaging Convention) containing XML files.
     */
    private function convertImageToDocxWithPhp(
        string $inputPath,
        string $inputFormat,
        string $outputPath
    ): bool {
        if (!class_exists('ZipArchive')) {
            return false;
        }

        $imgData  = @getimagesize($inputPath);
        $pxW      = $imgData ? max(1, (int) $imgData[0]) : 800;
        $pxH      = $imgData ? max(1, (int) $imgData[1]) : 600;

        // Convert pixels → EMU (1 in = 914400 EMU; assume 96 DPI)
        // Cap at A4 content width: 6 in = 5 486 400 EMU
        $maxEmuW  = 5486400;
        $emuPerPx = 914400.0 / 96.0;
        $emuW     = (int) min($maxEmuW, round($pxW * $emuPerPx));
        $emuH     = (int) round($emuW * $pxH / $pxW);

        $mimeMap  = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png'  => 'image/png',
            'gif' => 'image/gif',  'webp' => 'image/webp', 'bmp'  => 'image/bmp',
            'tiff' => 'image/tiff', 'svg' => 'image/svg+xml',
        ];
        $imgExt   = strtolower(pathinfo($inputPath, PATHINFO_EXTENSION)) ?: strtolower($inputFormat);
        $mime     = $mimeMap[$imgExt] ?? 'image/png';
        $media    = 'image1.' . $imgExt;
        $imgName  = htmlspecialchars(basename($inputPath), ENT_QUOTES | ENT_XML1);

        $contentTypes =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            . '<Override PartName="/word/media/' . $media . '" ContentType="' . $mime . '"/>'
            . '</Types>';

        $relsMain =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>'
            . '</Relationships>';

        $relsDoc =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="media/' . $media . '"/>'
            . '</Relationships>';

        $document =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"'
            . ' xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing"'
            . ' xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'
            . ' xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture"'
            . ' xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">'
            . '<w:body><w:p><w:r><w:drawing>'
            . '<wp:inline distT="0" distB="0" distL="0" distR="0">'
            . '<wp:extent cx="' . $emuW . '" cy="' . $emuH . '"/>'
            . '<wp:effectExtent l="0" t="0" r="0" b="0"/>'
            . '<wp:docPr id="1" name="Picture 1" descr="' . $imgName . '"/>'
            . '<wp:cNvGraphicFramePr><a:graphicFrameLocks noChangeAspectRatio="1"/></wp:cNvGraphicFramePr>'
            . '<a:graphic><a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">'
            . '<pic:pic>'
            . '<pic:nvPicPr><pic:cNvPr id="0" name="' . $imgName . '"/><pic:cNvPicPr/></pic:nvPicPr>'
            . '<pic:blipFill><a:blip r:embed="rId1"/><a:stretch><a:fillRect/></a:stretch></pic:blipFill>'
            . '<pic:spPr>'
            . '<a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $emuW . '" cy="' . $emuH . '"/></a:xfrm>'
            . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom>'
            . '</pic:spPr>'
            . '</pic:pic>'
            . '</a:graphicData></a:graphic>'
            . '</wp:inline>'
            . '</w:drawing></w:r></w:p>'
            . '<w:sectPr><w:pgSz w:w="12240" w:h="15840"/><w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440"/></w:sectPr>'
            . '</w:body></w:document>';

        $zip = new \ZipArchive();
        if ($zip->open($outputPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return false;
        }
        $zip->addFromString('[Content_Types].xml',         $contentTypes);
        $zip->addFromString('_rels/.rels',                 $relsMain);
        $zip->addFromString('word/document.xml',           $document);
        $zip->addFromString('word/_rels/document.xml.rels', $relsDoc);
        $zip->addFile($inputPath, 'word/media/' . $media);
        $zip->close();

        return file_exists($outputPath) && filesize($outputPath) > 100;
    }

    /**
     * Create a minimal but valid ODT (ODF Text Document) with the image embedded.
     * An ODT is a ZIP archive following the ODF specification.
     */
    private function convertImageToOdtWithPhp(
        string $inputPath,
        string $inputFormat,
        string $outputPath
    ): bool {
        if (!class_exists('ZipArchive')) {
            return false;
        }

        $imgData  = @getimagesize($inputPath);
        $pxW      = $imgData ? max(1, (int) $imgData[0]) : 800;
        $pxH      = $imgData ? max(1, (int) $imgData[1]) : 600;

        // Fit to A4 content width (16 cm) at 96 DPI
        $maxCmW   = 16.0;
        $cmPerPx  = 2.54 / 96.0;
        $cmW      = min($maxCmW, round($pxW * $cmPerPx, 3));
        $cmH      = round($cmW * $pxH / $pxW, 3);

        $mimeMap  = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png'  => 'image/png',
            'gif' => 'image/gif',  'webp' => 'image/webp', 'bmp'  => 'image/bmp',
            'tiff' => 'image/tiff', 'svg' => 'image/svg+xml',
        ];
        $imgExt   = strtolower(pathinfo($inputPath, PATHINFO_EXTENSION)) ?: strtolower($inputFormat);
        $mime     = $mimeMap[$imgExt] ?? 'image/png';
        $media    = 'Pictures/image.' . $imgExt;

        $manifest =
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0" manifest:version="1.3">'
            . '<manifest:file-entry manifest:full-path="/" manifest:version="1.3" manifest:media-type="application/vnd.oasis.opendocument.text"/>'
            . '<manifest:file-entry manifest:full-path="content.xml" manifest:media-type="text/xml"/>'
            . '<manifest:file-entry manifest:full-path="' . $media . '" manifest:media-type="' . $mime . '"/>'
            . '</manifest:manifest>';

        $content =
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<office:document-content'
            . ' xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"'
            . ' xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0"'
            . ' xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"'
            . ' xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0"'
            . ' xmlns:xlink="http://www.w3.org/1999/xlink"'
            . ' xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0">'
            . '<office:automatic-styles>'
            . '<style:style style:name="fr1" style:family="graphic">'
            . '<style:graphic-properties style:run-through="foreground" style:wrap="none"'
            . ' style:vertical-pos="top" style:vertical-rel="paragraph"'
            . ' style:horizontal-pos="from-left" style:horizontal-rel="paragraph"/>'
            . '</style:style>'
            . '</office:automatic-styles>'
            . '<office:body><office:text><text:p>'
            . '<draw:frame draw:style-name="fr1" draw:name="Image1"'
            . ' svg:width="' . $cmW . 'cm" svg:height="' . $cmH . 'cm"'
            . ' text:anchor-type="paragraph">'
            . '<draw:image xlink:href="' . $media . '" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>'
            . '</draw:frame>'
            . '</text:p></office:text></office:body>'
            . '</office:document-content>';

        $zip = new \ZipArchive();
        if ($zip->open($outputPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return false;
        }
        // ODF spec: 'mimetype' MUST be the first entry and STORED (not compressed)
        $zip->addFromString('mimetype', 'application/vnd.oasis.opendocument.text');
        if (method_exists($zip, 'setCompressionName')) {
            $zip->setCompressionName('mimetype', \ZipArchive::CM_STORE);
        }
        $zip->addFromString('META-INF/manifest.xml', $manifest);
        $zip->addFromString('content.xml',           $content);
        $zip->addFile($inputPath, $media);
        $zip->close();

        return file_exists($outputPath) && filesize($outputPath) > 100;
    }

    /**
     * Create a minimal RTF document with the image embedded as a hex binary.
     * Natively supports PNG and JPEG; other formats are converted via GD first.
     */
    private function convertImageToRtfWithPhp(
        string $inputPath,
        string $inputFormat,
        string $outputPath
    ): bool {
        $imgExt  = strtolower(pathinfo($inputPath, PATHINFO_EXTENSION)) ?: strtolower($inputFormat);
        $rtfType = match ($imgExt) {
            'png'         => 'pngblip',
            'jpg', 'jpeg' => 'jpegblip',
            default       => null,
        };

        $workPath = $inputPath;
        $tmpPng   = null;

        if ($rtfType === null) {
            // Convert to PNG via GD so it can be embedded as pngblip
            if (!extension_loaded('gd')) {
                return false;
            }
            $img = @imagecreatefromstring((string) file_get_contents($inputPath));
            if ($img === false) {
                return false;
            }
            $tmpPng = tempnam(sys_get_temp_dir(), 'cx_rtf_') . '.png';
            if (!imagepng($img, $tmpPng)) {
                imagedestroy($img);
                return false;
            }
            imagedestroy($img);
            $workPath = $tmpPng;
            $rtfType  = 'pngblip';
        }

        try {
            $imgData  = @getimagesize($workPath);
            $pxW      = $imgData ? max(1, (int) $imgData[0]) : 800;
            $pxH      = $imgData ? max(1, (int) $imgData[1]) : 600;
            // RTF dimensions in twips (1 in = 1440 twips; 96 DPI → 15 twips/pixel)
            $twipPerPx = 1440.0 / 96.0;
            $twW      = (int) round($pxW * $twipPerPx);
            $twH      = (int) round($pxH * $twipPerPx);
            $hexData  = bin2hex((string) file_get_contents($workPath));

            $rtf = '{\rtf1\ansi\deff0{\fonttbl{\f0 Arial;}}' . "\n"
                 . '\pard\sa0' . "\n"
                 . '{\pict\\' . $rtfType
                 . '\picw' . $pxW  . '\pich' . $pxH
                 . '\picwgoal' . $twW . '\pichgoal' . $twH . "\n"
                 . $hexData . "}\n"
                 . '}';

            return file_put_contents($outputPath, $rtf) !== false;
        } finally {
            if ($tmpPng !== null && file_exists($tmpPng)) {
                @unlink($tmpPng);
            }
        }
    }

    private function convertWithLibreOffice(
        string $inputPath,
        string $inputFormat,
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

        // Explicit filter spec (e.g. "xlsx:Calc MS Excel 2007 XML") is far more
        // reliable than bare format names on many server configurations.
        $filterSpec = escapeshellarg(self::getLibreOfficeFilter($outputFormat, $inputFormat));

        // DISPLAY=   – prevents LibreOffice from connecting to any X display.
        // HOME=/tmp  – required when web user (www-data) has no writable home.
        // --norestore / --nolockcheck – skip recovery dialogs and stale lock files.
        // -env:UserInstallation – per-process profile so concurrent conversions
        //   don't corrupt each other's profile directory.
        //   NOTE: single dash (-env:) is the correct LibreOffice syntax; double
        //   dash (--env:) is rejected by LibreOffice 7.x with "Error in option".
        // --infilter "draw_pdf_import" – force PDF Import filter when input is PDF.
        //   Without this, headless LO on some installs fails to select the filter
        //   automatically, producing empty or corrupt output.
        $pid      = getmypid();
        $infilter = ($inputFormat === 'pdf')
            ? '--infilter ' . escapeshellarg('draw_pdf_import') . ' '
            : '';
        $cmd = "DISPLAY= HOME=/tmp {$lo} --headless --norestore --nolockcheck "
             . "-env:UserInstallation=file:///tmp/lo-{$pid} "
             . $infilter
             . "--convert-to {$filterSpec} {$inFile} --outdir {$outDirEsc} 2>&1";
        exec($cmd, $output, $code);

        if ($code !== 0) {
            // Surface the full LibreOffice output so users and logs show what went wrong.
            $detail = trim(implode(' | ', array_filter($output)));
            Logger::warning('LibreOffice conversion failed: ' . implode("\n", $output));
            throw new \RuntimeException(
                "LibreOffice conversion failed (exit {$code})"
                . ($detail ? ": {$detail}" : '.')
            );
        }

        // LibreOffice names the output: {inputBasename}.{outputFormat}
        // Our expected path has the '_converted' suffix — rename if needed.
        $libreOutput = $outDirReal . '/' . pathinfo($inputPath, PATHINFO_FILENAME) . '.' . $outputFormat;
        if ($libreOutput !== $outputPath && file_exists($libreOutput)) {
            $this->safeMoveFile($libreOutput, $outputPath);
        }

        // Reject empty/tiny output — LibreOffice can silently produce 0-byte files
        // when the PDF import filter is missing or a cross-component export fails.
        if (file_exists($outputPath) && filesize($outputPath) < 10) {
            @unlink($outputPath);
            throw new \RuntimeException(
                "LibreOffice produced an empty output for {$inputFormat} → {$outputFormat}. "
                . "For PDF input, ensure the libreoffice-pdfimport package is installed."
            );
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
