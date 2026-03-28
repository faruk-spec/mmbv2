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
        'image/x-icon'                                                       => 'ico',
        'image/vnd.microsoft.icon'                                           => 'ico',
        'application/epub+zip'                                               => 'epub',
        'text/tab-separated-values'                                          => 'tsv',
    ];

    // Formats that may contain scanned (rasterised) content → trigger OCR
    private const OCR_CANDIDATE_FORMATS = ['pdf', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'webp', 'ico'];

    /**
     * Optional AI service: injected by the job queue so that AI OCR is automatically
     * used as a fallback when local tools (Tesseract) are absent or return no text.
     */
    private ?AIService $aiService = null;

    /**
     * Inject the AI service to enable AI-powered OCR fallback during conversion.
     * Call this before convert() when an AIService instance is available.
     */
    public function setAIService(AIService $aiService): void
    {
        $this->aiService = $aiService;
    }

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
            return ['success' => false, 'output_path' => '', 'error' => "Conversion did not produce output for {$inputFormat} → {$outputFormat}. This format combination may not be supported on this server."];
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

        $imageFormats  = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'svg', 'ico'];
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
                "PDF to image conversion is not available on this server. "
                . "Please try a different output format."
            );
        }

        // 4b. Image → writer format (docx / odt / rtf / doc):
        //
        // Priority:
        //   A. AI document OCR — extract structured text via vision model, then write
        //      a proper text-based document (headings, paragraphs, tables).  This is
        //      far superior to image-embedding for scanned documents / screenshots.
        //   B. PHP ZipArchive image-embed — fast fallback when AI is unavailable.
        //   C. Two-step chain (image→PDF→writer) as last resort.
        //
        // 'doc' (binary OLE) is handled by building a DOCX then converting DOCX→DOC
        // via LibreOffice (same Writer family, no --infilter, no pdfimport needed).
        $phpWriterFormats = ['docx', 'odt', 'rtf', 'doc'];
        if ($isInputImage && in_array($outputFormat, $phpWriterFormats, true)) {
            // A. AI path: extract text → write proper document
            if ($this->aiService !== null) {
                if ($this->convertImageToDocumentWithOcr($inputPath, $inputFormat, $outputFormat, $outputPath)) {
                    return true;
                }
            }
            // B. Image-embed fallback (ZipArchive)
            if ($this->convertImageToDocumentWithPhp($inputPath, $inputFormat, $outputFormat, $outputPath)) {
                return true;
            }
            // C. Chain fallback
        }

        // 4c. Image → plain-text formats (txt / html / md / csv): use Tesseract OCR
        //     to extract text and write it to the output file.
        //     Going through the PDF chain here is unreliable — pdfimport produces
        //     unreadable output and LibreOffice Calc crashes on PDF→csv.
        $textOutputFormats = ['txt', 'html', 'md', 'csv'];
        if ($isInputImage && in_array($outputFormat, $textOutputFormats, true)) {
            return $this->convertImageToTextWithOcr($inputPath, $outputFormat, $outputPath);
        }

        // 4d. Image → spreadsheet formats (xlsx / xls / ods):
        //     Use Tesseract OCR to extract text and write it into a minimal spreadsheet.
        //     The PDF-chain approach is not supported for these formats.
        $spreadsheetOutputFormats = ['xlsx', 'xls', 'ods'];
        if ($isInputImage && in_array($outputFormat, $spreadsheetOutputFormats, true)) {
            return $this->convertImageToSpreadsheetWithOcr($inputPath, $outputFormat, $outputPath);
        }

        // 4e. Image → presentation formats (pptx / odp / ppt):
        //     Embed the image directly in a minimal presentation file using ZipArchive.
        $presentationOutputFormats = ['pptx', 'odp', 'ppt'];
        if ($isInputImage && in_array($outputFormat, $presentationOutputFormats, true)) {
            if ($this->convertImageToPresentationWithPhp($inputPath, $inputFormat, $outputFormat, $outputPath)) {
                return true;
            }
            // ZipArchive unavailable — fall through to chain
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
        // 'epub' is handled by LibreOffice Writer (requires the epub export extension).
        $officeFormats = ['pdf', 'docx', 'doc', 'odt', 'rtf', 'html', 'xlsx', 'xls', 'ods', 'pptx', 'ppt', 'odp', 'epub'];
        if (in_array($inputFormat, $officeFormats, true) || in_array($outputFormat, $officeFormats, true)) {
            // Guard: LibreOffice crashes (SIGABRT/exit 134) when importing a PDF
            // into Calc or Impress — the same crash that happens in convertViaChain.
            if ($inputFormat === 'pdf'
                && in_array($outputFormat, ['xlsx', 'xls', 'ods', 'csv', 'pptx', 'ppt', 'odp'], true)
            ) {
                throw new \RuntimeException(
                    "PDF to spreadsheet or presentation conversion is not supported. "
                    . "Try converting to a document format (DOCX, ODT, or TXT) first."
                );
            }
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
        $phpFormats = ['txt', 'html', 'md', 'csv', 'tsv'];
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

        // TSV → TXT
        if ($inputFormat === 'tsv' && $outputFormat === 'txt') {
            return $this->csvToText($inputPath, $outputPath, "\t");
        }

        // TXT → CSV (single-column) — RFC 4180 via fputcsv
        if ($inputFormat === 'txt' && $outputFormat === 'csv') {
            $lines = preg_split('/\r\n|\r|\n/', $content) ?: [];
            $fh = fopen($outputPath, 'w');
            if (!$fh) {
                return false;
            }
            foreach ($lines as $line) {
                fputcsv($fh, [$line]);
            }
            fclose($fh);
            return file_exists($outputPath);
        }

        // CSV ↔ TSV — change delimiter
        if (in_array($inputFormat, ['csv', 'tsv'], true) && in_array($outputFormat, ['csv', 'tsv'], true)) {
            $inDelim  = ($inputFormat  === 'tsv') ? "\t" : ',';
            $outDelim = ($outputFormat === 'tsv') ? "\t" : ',';
            $fhIn  = fopen($inputPath,  'r');
            $fhOut = fopen($outputPath, 'w');
            if (!$fhIn || !$fhOut) {
                return false;
            }
            while (($row = fgetcsv($fhIn, 0, $inDelim)) !== false) {
                fputcsv($fhOut, $row, $outDelim);
            }
            fclose($fhIn);
            fclose($fhOut);
            return file_exists($outputPath);
        }

        // TSV → any other format: treat same as CSV conversion via TSV delimiter
        if ($inputFormat === 'tsv') {
            return $this->csvToText($inputPath, $outputPath, "\t");
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
            'epub' => 'epub:EPUB Export',
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
        $imageFormats = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'tiff', 'svg', 'ico'];
        $isInputImage = in_array($inputFormat, $imageFormats, true);

        // Unique intermediate PDF in system temp dir
        $tmpPdf = sys_get_temp_dir() . '/cx_chain_' . getmypid() . '_' . bin2hex(random_bytes(8)) . '.pdf';

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
                throw new \RuntimeException(
                    "Conversion failed at the intermediate step ({$inputFormat} → PDF). "
                    . "Please verify the input file is valid and try a different output format."
                );
            }

            // ── Step 2: convert intermediate PDF to target ─────────────────
            if (in_array($outputFormat, $imageFormats, true)) {
                return $this->convertWithImageMagick($tmpPdf, $outputPath, $options);
            }

            // LibreOffice crashes (SIGABRT / exit 134) when asked to import a PDF
            // into Calc or Impress.  These families are not PDF-import capable in
            // the headless mode used here.  Throw a clear error instead of crashing.
            $pdfUnsafeTargets = ['xlsx', 'xls', 'ods', 'csv', 'pptx', 'ppt', 'odp'];
            if (in_array($outputFormat, $pdfUnsafeTargets, true)) {
                throw new \RuntimeException(
                    "This image to {$outputFormat} conversion is not supported via the standard path. "
                    . "Try enabling AI OCR on the conversion for better results."
                );
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
     * AI-powered image → document conversion.
     *
     * Uses the vision model to extract structured content (headings, paragraphs,
     * tables, lists) from the image and writes it as a proper text-based document.
     * This produces far better output than simply embedding the image.
     *
     * Falls back silently and returns false if AI is unavailable or extraction fails.
     */
    private function convertImageToDocumentWithOcr(
        string $inputPath,
        string $inputFormat,
        string $outputFormat,
        string $outputPath
    ): bool {
        if (!file_exists($inputPath) || $this->aiService === null) {
            return false;
        }

        // Get structured Markdown text from the AI vision model
        $aiResult = $this->aiService->ocrDocument($inputPath, 'free');
        if (!$aiResult['success'] || empty(trim($aiResult['text'] ?? ''))) {
            return false;
        }

        $text = $aiResult['text'];

        return match ($outputFormat) {
            'docx' => $this->writeDocxFromText($text, $outputPath, $inputPath, $inputFormat),
            'odt'  => $this->writeOdtFromText($text, $outputPath, $inputPath, $inputFormat),
            'rtf'  => $this->writeRtfFromText($text, $outputPath),
            'doc'  => $this->writeDocViaDocxFromText($text, $outputPath, $inputPath, $inputFormat),
            default => false,
        };
    }

    /**
     * Build a DOCX file from structured Markdown-like text (from AI document OCR).
     *
     * Supported Markdown constructs:
     *   # Heading 1    → Heading1 paragraph style
     *   ## Heading 2   → Heading2 paragraph style
     *   ### Heading 3  → Heading3 paragraph style
     *   | col | col |  → OOXML <w:tbl> table
     *   - item         → ListBullet paragraph style
     *   1. item        → ListNumber paragraph style
     *   blank line     → empty paragraph (spacer)
     *   everything else → Normal paragraph style
     *
     * When $imagePath is provided the image is also embedded after the text.
     */
    private function writeDocxFromText(
        string $text,
        string $outputPath,
        string $imagePath = '',
        string $inputFormat = ''
    ): bool {
        if (!class_exists('ZipArchive')) {
            return false;
        }

        $bodyXml      = '';
        $relationships = '';
        $contentTypes  = '';
        $imageEntry    = '';
        $rId           = 1;

        // ── Parse text into DOCX body XML ────────────────────────────────
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $i = 0;
        while ($i < count($lines)) {
            $line = $lines[$i];

            // Markdown pipe table: collect consecutive | lines
            if (str_starts_with(trim($line), '|')) {
                $tableLines = [];
                while ($i < count($lines) && str_starts_with(trim($lines[$i]), '|')) {
                    $tableLines[] = $lines[$i];
                    $i++;
                }
                $bodyXml .= $this->markdownTableToDocxXml($tableLines);
                continue;
            }

            // Heading levels
            if (preg_match('/^(#{1,3})\s+(.+)$/', $line, $m)) {
                $lvl      = strlen($m[1]);
                $style    = "Heading{$lvl}";
                $bodyXml .= $this->docxParagraph(htmlspecialchars($m[2], ENT_XML1), $style);
                $i++;
                continue;
            }

            // Bullet list
            if (preg_match('/^[-*]\s+(.+)$/', $line, $m)) {
                $bodyXml .= $this->docxParagraph(htmlspecialchars($m[1], ENT_XML1), 'ListBullet');
                $i++;
                continue;
            }

            // Numbered list
            if (preg_match('/^\d+[.)]\s+(.+)$/', $line, $m)) {
                $bodyXml .= $this->docxParagraph(htmlspecialchars($m[1], ENT_XML1), 'ListNumber');
                $i++;
                continue;
            }

            // Blank line → empty spacer paragraph
            if (trim($line) === '') {
                $bodyXml .= '<w:p/>';
                $i++;
                continue;
            }

            // Inline formatting: **bold** and *italic*
            $safe = htmlspecialchars($line, ENT_XML1);
            $safe = preg_replace('/\*\*(.+?)\*\*/', '<w:r><w:rPr><w:b/></w:rPr><w:t xml:space="preserve">$1</w:t></w:r>', $safe);
            $safe = preg_replace('/\*(.+?)\*/',     '<w:r><w:rPr><w:i/></w:rPr><w:t xml:space="preserve">$1</w:t></w:r>', $safe);

            // If the line still has no <w:r> tags it's plain text
            if (!str_contains((string) $safe, '<w:r>')) {
                $bodyXml .= $this->docxParagraph((string) $safe, 'Normal');
            } else {
                $bodyXml .= '<w:p>' . $safe . '</w:p>';
            }
            $i++;
        }

        // ── Optional: append the source image ────────────────────────────
        $mediaFiles = [];
        if ($imagePath && file_exists($imagePath)) {
            $mimeMap   = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
                          'gif' => 'image/gif', 'webp' => 'image/webp', 'bmp' => 'image/bmp',
                          'tiff' => 'image/tiff', 'svg' => 'image/svg+xml'];
            $imgExt    = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION)) ?: strtolower($inputFormat);
            $mime      = $mimeMap[$imgExt] ?? 'image/png';
            $media     = 'image1.' . $imgExt;
            $imgData   = @getimagesize($imagePath);
            $pxW       = $imgData ? max(1, (int) $imgData[0]) : 800;
            $pxH       = $imgData ? max(1, (int) $imgData[1]) : 600;
            $emuPerPx  = 914400.0 / 96.0;
            $maxEmuW   = 5486400;
            $emuW      = (int) min($maxEmuW, round($pxW * $emuPerPx));
            $emuH      = (int) round($emuW * $pxH / $pxW);

            $bodyXml .= '<w:p/>'  // blank line before image
                     . '<w:p><w:r><w:drawing>'
                     . '<wp:inline distT="0" distB="0" distL="0" distR="0"'
                     . ' xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing">'
                     . '<wp:extent cx="' . $emuW . '" cy="' . $emuH . '"/>'
                     . '<wp:effectExtent l="0" t="0" r="0" b="0"/>'
                     . '<wp:docPr id="1" name="Picture 1"/>'
                     . '<wp:cNvGraphicFramePr>'
                     . '<a:graphicFrameLocks noChangeAspectRatio="1"'
                     . ' xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"/>'
                     . '</wp:cNvGraphicFramePr>'
                     . '<a:graphic xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">'
                     . '<a:graphicData uri="http://schemas.openxmlformats.org/drawingml/2006/picture">'
                     . '<pic:pic xmlns:pic="http://schemas.openxmlformats.org/drawingml/2006/picture">'
                     . '<pic:nvPicPr><pic:cNvPr id="0" name="img"/><pic:cNvPicPr/></pic:nvPicPr>'
                     . '<pic:blipFill>'
                     . '<a:blip r:embed="rId1" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"/>'
                     . '<a:stretch><a:fillRect/></a:stretch></pic:blipFill>'
                     . '<pic:spPr>'
                     . '<a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $emuW . '" cy="' . $emuH . '"/></a:xfrm>'
                     . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom>'
                     . '</pic:spPr>'
                     . '</pic:pic></a:graphicData></a:graphic>'
                     . '</wp:inline>'
                     . '</w:drawing></w:r></w:p>';

            $mediaFiles[$media] = $imagePath;
            $relationships      = '<Relationship Id="rId1"'
                . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image"'
                . ' Target="media/' . $media . '"/>';
            $contentTypes       = '<Override PartName="/word/media/' . $media . '" ContentType="' . $mime . '"/>';
        }

        // ── Assemble the DOCX ZIP ─────────────────────────────────────────
        $document =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document'
            . ' xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<w:body>'
            . $bodyXml
            . '<w:sectPr><w:pgSz w:w="12240" w:h="15840"/>'
            . '<w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440"/></w:sectPr>'
            . '</w:body></w:document>';

        $rels =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . $relationships
            . '</Relationships>';

        $contentTypesXml =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/word/document.xml"'
            . ' ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            . $contentTypes
            . '</Types>';

        $relsMain =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1"'
            . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument"'
            . ' Target="word/document.xml"/>'
            . '</Relationships>';

        $zip = new \ZipArchive();
        if ($zip->open($outputPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return false;
        }
        $zip->addFromString('[Content_Types].xml',          $contentTypesXml);
        $zip->addFromString('_rels/.rels',                  $relsMain);
        $zip->addFromString('word/document.xml',            $document);
        $zip->addFromString('word/_rels/document.xml.rels', $rels);
        foreach ($mediaFiles as $zipEntry => $srcPath) {
            $zip->addFile($srcPath, 'word/media/' . $zipEntry);
        }
        $zip->close();

        return file_exists($outputPath) && filesize($outputPath) > 100;
    }

    /** Build a single DOCX <w:p> element with an optional paragraph style. */
    private function docxParagraph(string $xmlText, string $style = 'Normal'): string
    {
        $stylePr = ($style !== 'Normal')
            ? '<w:pPr><w:pStyle w:val="' . htmlspecialchars($style, ENT_XML1) . '"/></w:pPr>'
            : '';
        return '<w:p>' . $stylePr . '<w:r><w:t xml:space="preserve">'
             . $xmlText . '</w:t></w:r></w:p>';
    }

    /**
     * Convert an array of Markdown pipe-table lines to DOCX <w:tbl> XML.
     *
     * Example input:
     *   ['| Product | Qtr 1 |', '|---------|-------|', '| Choc    | $7.00 |']
     */
    private function markdownTableToDocxXml(array $lines): string
    {
        $tblXml = '<w:tbl>'
            . '<w:tblPr>'
            . '<w:tblStyle w:val="TableGrid"/>'
            . '<w:tblW w:w="0" w:type="auto"/>'
            . '</w:tblPr>';

        $headerDone = false;
        foreach ($lines as $line) {
            // Skip the separator row (| --- | --- |)
            if (preg_match('/^\|[\s\-:|]+\|/', trim($line))) {
                $headerDone = true;
                continue;
            }

            // Split on | and trim; remove empty first/last entries from leading/trailing |
            $cells = array_map('trim', explode('|', $line));
            $cells = array_values(array_filter($cells, fn($c) => $c !== ''));
            if (empty($cells)) {
                continue;
            }

            $isHeader = !$headerDone;
            $tblXml  .= '<w:tr>';
            foreach ($cells as $cell) {
                $cellText = htmlspecialchars($cell, ENT_XML1);
                $runPr    = $isHeader ? '<w:rPr><w:b/></w:rPr>' : '';
                $tblXml  .= '<w:tc>'
                          . '<w:tcPr><w:tcW w:w="0" w:type="auto"/></w:tcPr>'
                          . '<w:p><w:r>' . $runPr
                          . '<w:t xml:space="preserve">' . $cellText . '</w:t>'
                          . '</w:r></w:p>'
                          . '</w:tc>';
            }
            $tblXml .= '</w:tr>';
        }

        $tblXml .= '</w:tbl><w:p/>'; // blank paragraph after table (required by OOXML)
        return $tblXml;
    }

    /**
     * Build an ODT file from structured Markdown-like text.
     *
     * Supports headings (# ## ###), paragraphs, and pipe tables.
     * When $imagePath is provided, the image is appended after the text.
     */
    private function writeOdtFromText(
        string $text,
        string $outputPath,
        string $imagePath = '',
        string $inputFormat = ''
    ): bool {
        if (!class_exists('ZipArchive')) {
            return false;
        }

        $bodyXml    = '';
        $mediaFiles = [];
        $manifestEntries = '';

        // ── Parse text into ODF body XML ──────────────────────────────────
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $i = 0;
        while ($i < count($lines)) {
            $line = $lines[$i];

            // Pipe table: collect consecutive | lines
            if (str_starts_with(trim($line), '|')) {
                $tableLines = [];
                while ($i < count($lines) && str_starts_with(trim($lines[$i]), '|')) {
                    $tableLines[] = $lines[$i];
                    $i++;
                }
                $bodyXml .= $this->markdownTableToOdtXml($tableLines);
                continue;
            }

            // Headings
            if (preg_match('/^(#{1,3})\s+(.+)$/', $line, $m)) {
                $lvl      = strlen($m[1]);
                $style    = 'Heading_20_' . $lvl;
                $bodyXml .= '<text:h text:style-name="' . $style . '" text:outline-level="' . $lvl . '">'
                          . htmlspecialchars($m[2], ENT_XML1) . '</text:h>';
                $i++;
                continue;
            }

            // Bullet list
            if (preg_match('/^[-*]\s+(.+)$/', $line, $m)) {
                $bodyXml .= '<text:list><text:list-item><text:p>'
                          . htmlspecialchars($m[1], ENT_XML1) . '</text:p></text:list-item></text:list>';
                $i++;
                continue;
            }

            // Numbered list
            if (preg_match('/^\d+[.)]\s+(.+)$/', $line, $m)) {
                $bodyXml .= '<text:list text:style-name="List_20_Number"><text:list-item><text:p>'
                          . htmlspecialchars($m[1], ENT_XML1) . '</text:p></text:list-item></text:list>';
                $i++;
                continue;
            }

            // Blank → empty paragraph
            if (trim($line) === '') {
                $bodyXml .= '<text:p/>';
                $i++;
                continue;
            }

            // Regular paragraph (with basic inline bold/italic support)
            $safe = htmlspecialchars($line, ENT_XML1);
            $safe = preg_replace('/\*\*(.+?)\*\*/', '<text:span text:style-name="Strong_20_Emphasis">$1</text:span>', $safe);
            $safe = preg_replace('/\*(.+?)\*/',     '<text:span text:style-name="Emphasis">$1</text:span>',          $safe);
            $bodyXml .= '<text:p>' . $safe . '</text:p>';
            $i++;
        }

        // ── Optional: append source image ─────────────────────────────────
        if ($imagePath && file_exists($imagePath)) {
            $mimeMap = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
                        'gif' => 'image/gif',  'webp' => 'image/webp', 'bmp' => 'image/bmp',
                        'tiff' => 'image/tiff', 'svg' => 'image/svg+xml'];
            $imgExt  = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION)) ?: strtolower($inputFormat);
            $mime    = $mimeMap[$imgExt] ?? 'image/png';
            $media   = 'Pictures/image.' . $imgExt;
            $imgData = @getimagesize($imagePath);
            $pxW     = $imgData ? max(1, (int) $imgData[0]) : 800;
            $pxH     = $imgData ? max(1, (int) $imgData[1]) : 600;
            $maxCmW  = 16.0;
            $cmPerPx = 2.54 / 96.0;
            $cmW     = min($maxCmW, round($pxW * $cmPerPx, 3));
            $cmH     = round($cmW * $pxH / $pxW, 3);

            $bodyXml .= '<text:p>'
                     . '<draw:frame draw:style-name="fr1" draw:name="Image1"'
                     . ' svg:width="' . $cmW . 'cm" svg:height="' . $cmH . 'cm"'
                     . ' text:anchor-type="paragraph">'
                     . '<draw:image xlink:href="' . $media . '" xlink:type="simple"'
                     . ' xlink:show="embed" xlink:actuate="onLoad"/>'
                     . '</draw:frame></text:p>';

            $mediaFiles[$media] = $imagePath;
            $manifestEntries   .= '<manifest:file-entry manifest:full-path="' . $media
                                . '" manifest:media-type="' . $mime . '"/>';
        }

        // ── Build the ODT ZIP ─────────────────────────────────────────────
        $manifest =
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0" manifest:version="1.3">'
            . '<manifest:file-entry manifest:full-path="/" manifest:version="1.3" manifest:media-type="application/vnd.oasis.opendocument.text"/>'
            . '<manifest:file-entry manifest:full-path="content.xml" manifest:media-type="text/xml"/>'
            . $manifestEntries
            . '</manifest:manifest>';

        $content =
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<office:document-content'
            . ' xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"'
            . ' xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0"'
            . ' xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0"'
            . ' xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"'
            . ' xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0"'
            . ' xmlns:xlink="http://www.w3.org/1999/xlink"'
            . ' xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0">'
            . '<office:automatic-styles>'
            . '<style:style style:name="fr1" style:family="graphic">'
            . '<style:graphic-properties style:run-through="foreground" style:wrap="none"/>'
            . '</style:style>'
            . '</office:automatic-styles>'
            . '<office:body><office:text>'
            . $bodyXml
            . '</office:text></office:body>'
            . '</office:document-content>';

        $zip = new \ZipArchive();
        if ($zip->open($outputPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return false;
        }
        $zip->addFromString('mimetype', 'application/vnd.oasis.opendocument.text');
        if (method_exists($zip, 'setCompressionName')) {
            $zip->setCompressionName('mimetype', \ZipArchive::CM_STORE);
        }
        $zip->addFromString('META-INF/manifest.xml', $manifest);
        $zip->addFromString('content.xml',           $content);
        foreach ($mediaFiles as $zipEntry => $srcPath) {
            $zip->addFile($srcPath, $zipEntry);
        }
        $zip->close();

        return file_exists($outputPath) && filesize($outputPath) > 100;
    }

    /**
     * Convert an array of Markdown pipe-table lines to ODF <table:table> XML.
     */
    private function markdownTableToOdtXml(array $lines): string
    {
        $xml = '<table:table table:name="Table1" table:style-name="TableGrid">';

        $headerDone = false;
        foreach ($lines as $line) {
            if (preg_match('/^\|[\s\-:|]+\|/', trim($line))) {
                $headerDone = true;
                continue;
            }
            $cells = array_map('trim', explode('|', $line));
            $cells = array_values(array_filter($cells, fn($c) => $c !== ''));
            if (empty($cells)) {
                continue;
            }
            $isHeader = !$headerDone;
            $xml .= '<table:table-row>';
            foreach ($cells as $cell) {
                $cellText = htmlspecialchars($cell, ENT_XML1);
                $inner    = $isHeader
                    ? '<text:p><text:span text:style-name="Strong_20_Emphasis">' . $cellText . '</text:span></text:p>'
                    : '<text:p>' . $cellText . '</text:p>';
                $xml .= '<table:table-cell>' . $inner . '</table:table-cell>';
            }
            $xml .= '</table:table-row>';
        }
        $xml .= '</table:table><text:p/>';
        return $xml;
    }

    /**
     * Build an RTF document from structured Markdown-like text.
     *
     * Supports headings (# ## ###), bullet/numbered lists, pipe tables,
     * bold/italic inline formatting, and paragraph breaks.
     */
    private function writeRtfFromText(string $text, string $outputPath): bool
    {
        $rtfBody = '';

        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $i = 0;
        while ($i < count($lines)) {
            $line = $lines[$i];

            // Pipe table → simple tab-separated RTF table row
            if (str_starts_with(trim($line), '|')) {
                $tableLines = [];
                while ($i < count($lines) && str_starts_with(trim($lines[$i]), '|')) {
                    $tableLines[] = $lines[$i];
                    $i++;
                }
                $rtfBody .= $this->markdownTableToRtf($tableLines);
                continue;
            }

            // Heading 1
            if (preg_match('/^#\s+(.+)$/', $line, $m)) {
                $rtfBody .= '\\pard\\sb240\\sa120{\\b\\fs36 '
                          . $this->rtfEscape($m[1]) . '}\\par\\pard' . "\n";
                $i++;
                continue;
            }
            // Heading 2
            if (preg_match('/^##\s+(.+)$/', $line, $m)) {
                $rtfBody .= '\\pard\\sb240\\sa120{\\b\\fs28 '
                          . $this->rtfEscape($m[1]) . '}\\par\\pard' . "\n";
                $i++;
                continue;
            }
            // Heading 3
            if (preg_match('/^###\s+(.+)$/', $line, $m)) {
                $rtfBody .= '\\pard\\sb120\\sa60{\\b\\fs24 '
                          . $this->rtfEscape($m[1]) . '}\\par\\pard' . "\n";
                $i++;
                continue;
            }

            // Bullet list
            if (preg_match('/^[-*]\s+(.+)$/', $line, $m)) {
                $rtfBody .= '\\pard\\li360\\fi-360{\\bullet\\tab '
                          . $this->rtfInline($m[1]) . '}\\par\\pard' . "\n";
                $i++;
                continue;
            }

            // Numbered list
            if (preg_match('/^(\d+)[.)]\s+(.+)$/', $line, $m)) {
                $rtfBody .= '\\pard\\li360\\fi-360{' . $m[1] . '.\\tab '
                          . $this->rtfInline($m[2]) . '}\\par\\pard' . "\n";
                $i++;
                continue;
            }

            // Blank → paragraph break
            if (trim($line) === '') {
                $rtfBody .= '\\par' . "\n";
                $i++;
                continue;
            }

            // Regular paragraph
            $rtfBody .= '\\pard ' . $this->rtfInline($line) . '\\par\\pard' . "\n";
            $i++;
        }

        $rtf = '{\\rtf1\\ansi\\deff0' . "\n"
             . '{\\fonttbl{\\f0\\froman\\fcharset0 Times New Roman;}{\\f1\\fswiss\\fcharset0 Arial;}}' . "\n"
             . '{\\colortbl;\\red0\\green0\\blue0;}' . "\n"
             . '\\deflang1033\\widowctrl\\hyphauto' . "\n"
             . $rtfBody
             . '}';

        return file_put_contents($outputPath, $rtf) !== false;
    }

    /**
     * Convert Markdown pipe-table lines to RTF tab-separated rows.
     */
    private function markdownTableToRtf(array $lines): string
    {
        $rtf         = '';
        $headerDone  = false;
        $colCount    = 0;

        foreach ($lines as $line) {
            if (preg_match('/^\|[\s\-:|]+\|/', trim($line))) {
                $headerDone = true;
                continue;
            }
            $cells = array_map('trim', explode('|', $line));
            $cells = array_values(array_filter($cells, fn($c) => $c !== ''));
            if (empty($cells)) {
                continue;
            }
            if ($colCount === 0) {
                $colCount = count($cells);
            }

            // Build RTF table row definition (each column = 2880 twips ≈ 2 inches)
            $rowDef = '\\trowd\\trqc';
            $pos    = 0;
            for ($c = 0; $c < count($cells); $c++) {
                $pos     += 2880;
                $rowDef  .= '\\cellx' . $pos;
            }

            $isHeader = !$headerDone;
            $rtf .= $rowDef;
            foreach ($cells as $cell) {
                $fmt   = $isHeader ? '{\\b ' . $this->rtfEscape($cell) . '}' : $this->rtfInline($cell);
                $rtf  .= '\\pard\\intbl ' . $fmt . '\\cell';
            }
            $rtf .= '\\row' . "\n";
        }

        return $rtf . '\\par' . "\n";
    }

    /** Escape a plain string for RTF (backslash, braces, non-ASCII). */
    private function rtfEscape(string $text): string
    {
        $text = str_replace(['\\', '{', '}'], ['\\\\', '\\{', '\\}'], $text);
        // Convert non-ASCII to RTF Unicode escapes
        $out = '';
        for ($j = 0; $j < mb_strlen($text, 'UTF-8'); $j++) {
            $char = mb_substr($text, $j, 1, 'UTF-8');
            $ord  = mb_ord($char, 'UTF-8');
            $out .= ($ord > 127) ? '\\u' . $ord . '?' : $char;
        }
        return $out;
    }

    /** Apply inline **bold** and *italic* formatting to an RTF string. */
    private function rtfInline(string $text): string
    {
        $text = $this->rtfEscape($text);
        $text = preg_replace('/\*\*(.+?)\*\*/', '{\\b $1}', $text);
        $text = preg_replace('/\*(.+?)\*/',     '{\\i $1}', $text);
        return (string) $text;
    }

    /**
     * Write a .doc file from structured text by first building a DOCX via
     * writeDocxFromText() then letting LibreOffice convert DOCX→DOC.
     */
    private function writeDocViaDocxFromText(
        string $text,
        string $outputPath,
        string $imagePath = '',
        string $inputFormat = ''
    ): bool {
        $tmpBase = tempnam(sys_get_temp_dir(), 'cx_doc_');
        @unlink($tmpBase);
        $tmpDocx = $tmpBase . '.docx';
        try {
            if (!$this->writeDocxFromText($text, $tmpDocx, $imagePath, $inputFormat)) {
                return false;
            }
            return $this->convertWithLibreOffice($tmpDocx, 'docx', 'doc', $outputPath);
        } finally {
            if (file_exists($tmpDocx)) {
                @unlink($tmpDocx);
            }
        }
    }

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
            'doc'  => $this->convertImageToDocViaDocx($inputPath, $inputFormat, $outputPath),
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

    /**
     * Convert an image to a legacy .doc file (OLE binary format) by:
     *   1. Building a DOCX in a temp file using convertImageToDocxWithPhp()
     *   2. Having LibreOffice convert the DOCX → DOC (same Writer family;
     *      no --infilter, no PDF intermediate, no libreoffice-pdfimport needed).
     */
    private function convertImageToDocViaDocx(
        string $inputPath,
        string $inputFormat,
        string $outputPath
    ): bool {
        // Generate a unique temp path with .docx extension.
        // tempnam() creates a 0-byte file; unlink it so ZipArchive can create the DOCX.
        $tmpBase = tempnam(sys_get_temp_dir(), 'cx_doc_');
        @unlink($tmpBase);
        $tmpDocx = $tmpBase . '.docx';
        try {
            if (!$this->convertImageToDocxWithPhp($inputPath, $inputFormat, $tmpDocx)) {
                return false;
            }
            return $this->convertWithLibreOffice($tmpDocx, 'docx', 'doc', $outputPath);
        } finally {
            if (file_exists($tmpDocx)) {
                @unlink($tmpDocx);
            }
        }
    }

    /**
     * Create a spreadsheet (xlsx / xls / ods) from an image using Tesseract OCR.
     * Each text line extracted becomes a row; tab-separated values become multiple cells.
     */
    private function convertImageToSpreadsheetWithOcr(
        string $inputPath,
        string $outputFormat,
        string $outputPath
    ): bool {
        if (!file_exists($inputPath)) {
            return false;
        }

        // ── 1. AI table OCR (best quality: preserves column structure) ────────
        //
        // Try this FIRST when an AIService is available.  The vision model is
        // asked to return RFC 4180 CSV so that multi-column tables are extracted
        // correctly rather than as a flat stream of text that Tesseract produces.
        if ($this->aiService !== null) {
            $aiTable = $this->aiService->ocrTable($inputPath, 'free');
            if ($aiTable['success'] && !empty($aiTable['rows'])) {
                $rows = $aiTable['rows'];
                return $this->writeSpreadsheetFromRows($rows, $outputFormat, $outputPath);
            }
        }

        // ── 2. Tesseract TSV mode (local, preserves column layout better than plain text) ─
        //
        // `tesseract … tsv` outputs a tab-delimited file with one word per row
        // including conf, left, top, width, height, and text columns.  We
        // reconstruct spreadsheet rows by grouping words that share the same
        // line number (field index 5) and sorting by their horizontal position.
        $rows = $this->extractRowsFromTesseractTsv($inputPath);

        // ── 3. Tesseract plain-text fallback ──────────────────────────────────
        if (empty($rows)) {
            $rows = $this->extractRowsFromTesseractPlain($inputPath);
        }

        // ── 4. AI plain OCR (last resort when Tesseract is not installed) ─────
        if (empty($rows) && $this->aiService !== null) {
            $aiOcr = $this->aiService->ocr($inputPath, 'free');
            if ($aiOcr['success'] && !empty(trim($aiOcr['text'] ?? ''))) {
                $rows = $this->parseTextIntoRows($aiOcr['text']);
            }
        }

        if (empty($rows)) {
            $rows = [['No text could be extracted from this image.']];
        }

        return $this->writeSpreadsheetFromRows($rows, $outputFormat, $outputPath);
    }

    /**
     * Run Tesseract in TSV mode and reconstruct table rows from the output.
     *
     * TSV columns (0-based): level, page_num, block_num, par_num, line_num,
     * word_num, left, top, width, height, conf, text.
     * Words with conf = -1 are layout elements (not text) and are skipped.
     *
     * @return array<int, array<int, string>>
     */
    private function extractRowsFromTesseractTsv(string $inputPath): array
    {
        $tess = trim((string) shell_exec('which tesseract 2>/dev/null'));
        if (!$tess) {
            return [];
        }

        $tmpBase = sys_get_temp_dir() . '/cx_tess_' . getmypid() . '_' . bin2hex(random_bytes(8));
        // --psm 6: treat the image as a single uniform block of text — better for tables/grids
        exec(
            escapeshellarg($tess) . ' ' . escapeshellarg($inputPath)
            . ' ' . escapeshellarg($tmpBase) . ' --psm 6 tsv -l eng 2>/dev/null',
            $_out, $code
        );
        $tsvFile = $tmpBase . '.tsv';
        if ($code !== 0 || !file_exists($tsvFile)) {
            return [];
        }

        $tsv = (string) file_get_contents($tsvFile);
        @unlink($tsvFile);

        // Group words by (block_num, par_num, line_num) → reconstruct logical lines,
        // then group logical lines by approximate vertical band into spreadsheet rows.
        $lines  = [];  // keyed by "block.par.line"
        $topMap = [];  // top pixel of each line

        foreach (explode("\n", $tsv) as $i => $rawLine) {
            if ($i === 0 || trim($rawLine) === '') {
                continue; // skip TSV header row and blank lines
            }
            $cols = explode("\t", $rawLine);
            if (count($cols) < 12) {
                continue;
            }
            $conf = (int) $cols[10];
            $word = trim($cols[11]);
            if ($conf === -1 || $word === '') {
                continue;
            }
            // Filter out very low-confidence tokens (likely OCR garbage from UI elements)
            if ($conf < 10 && strlen($word) <= 2) {
                continue;
            }
            $key   = $cols[2] . '.' . $cols[3] . '.' . $cols[4]; // block.par.line
            $left  = (int) $cols[6];
            $top   = (int) $cols[7];
            $width = (int) $cols[8];

            if (!isset($lines[$key])) {
                $lines[$key]  = [];
                $topMap[$key] = $top;
            }
            // Store left, center, and text for each word
            $lines[$key][] = [
                'left'   => $left,
                'center' => $left + (int) ($width / 2),
                'text'   => $word,
            ];
        }

        if (empty($lines)) {
            return [];
        }

        // Sort words within each line by horizontal position (left edge)
        $sortedLines = [];
        foreach ($lines as $key => $words) {
            usort($words, fn($a, $b) => $a['left'] <=> $b['left']);

            // Merge adjacent tokens that Tesseract split at a comma inside a number.
            // e.g. ["$5,", "079.60"] → ["$5,079.60"]
            //      ["$1,",  "249.20"] → ["$1,249.20"]
            $merged = [];
            for ($j = 0; $j < count($words); $j++) {
                $cur = $words[$j];
                if (
                    isset($words[$j + 1])
                    && preg_match('/^[£$€¥₹]?\d[\d,]*,$/', $cur['text'])   // ends with digit + comma
                    && preg_match('/^\d{3}(\.\d+)?$/', $words[$j + 1]['text'])  // next is NNN or NNN.NN
                ) {
                    // Merge: keep the left/center of the first token
                    $merged[] = [
                        'left'   => $cur['left'],
                        'center' => $cur['center'],
                        'text'   => $cur['text'] . $words[$j + 1]['text'],
                    ];
                    $j++; // skip the next token
                } else {
                    $merged[] = $cur;
                }
            }

            $sortedLines[$key] = ['top' => $topMap[$key], 'words' => $merged];
        }

        // Sort lines by vertical position
        uasort($sortedLines, fn($a, $b) => $a['top'] <=> $b['top']);

        // ── Adaptive column zone detection ──────────────────────────────────
        //
        // Collect CENTER positions of all words across all lines, then find the
        // gaps between adjacent sorted positions.  Gaps larger than the adaptive
        // threshold indicate column boundaries.
        //
        // Using CENTER (left + width/2) rather than just left is more reliable for
        // right-aligned numeric columns where the left edge varies with digit count.
        $allCenters = [];
        foreach ($sortedLines as $lineData) {
            foreach ($lineData['words'] as $w) {
                $allCenters[] = $w['center'];
            }
        }
        sort($allCenters);

        // Find the largest gap across all adjacent center positions
        $maxGap = 1;
        for ($i = 1; $i < count($allCenters); $i++) {
            $gap    = $allCenters[$i] - $allCenters[$i - 1];
            $maxGap = max($maxGap, $gap);
        }

        // A new column zone starts when the gap exceeds 40% of the largest gap
        // (minimum 30 px so noise on very small images doesn't create false columns).
        $threshold = max(30, (int) ($maxGap * 0.40));

        $colZones  = [];
        $zoneStart = null;
        foreach ($allCenters as $pos) {
            if ($zoneStart === null) {
                $zoneStart = $pos;
            } elseif ($pos - $zoneStart > $threshold) {
                $colZones[] = $zoneStart;
                $zoneStart  = $pos;
            }
        }
        if ($zoneStart !== null) {
            $colZones[] = $zoneStart;
        }
        $colZones = array_unique($colZones);
        sort($colZones);

        if (count($colZones) <= 1) {
            // Only one column detected — fall back to space-joined plain text per line
            $rows = [];
            foreach ($sortedLines as $lineData) {
                $rows[] = [implode(' ', array_column($lineData['words'], 'text'))];
            }
            return $rows;
        }

        // Assign each word to the nearest column zone using CENTER position
        $rows = [];
        foreach ($sortedLines as $lineData) {
            $cells = array_fill(0, count($colZones), '');
            foreach ($lineData['words'] as $w) {
                $colIdx  = 0;
                $minDist = PHP_INT_MAX;
                foreach ($colZones as $zi => $zone) {
                    $dist = abs($w['center'] - $zone);
                    if ($dist < $minDist) {
                        $minDist = $dist;
                        $colIdx  = $zi;
                    }
                }
                $cells[$colIdx] = ($cells[$colIdx] !== '' ? $cells[$colIdx] . ' ' : '') . $w['text'];
            }
            $rows[] = $cells;
        }

        return $rows;
    }

    /**
     * Run Tesseract in plain text mode and split each non-empty line into a
     * single-cell row.  Used as a fallback when TSV mode fails or returns nothing.
     *
     * @return array<int, array<int, string>>
     */
    private function extractRowsFromTesseractPlain(string $inputPath): array
    {
        $tess = trim((string) shell_exec('which tesseract 2>/dev/null'));
        if (!$tess) {
            return [];
        }

        $tmpBase = sys_get_temp_dir() . '/cx_tess_' . getmypid() . '_' . bin2hex(random_bytes(8));
        exec(
            escapeshellarg($tess) . ' ' . escapeshellarg($inputPath)
            . ' ' . escapeshellarg($tmpBase) . ' --psm 6 -l eng 2>/dev/null',
            $_out, $code
        );
        $txtFile = $tmpBase . '.txt';
        if ($code !== 0 || !file_exists($txtFile)) {
            return [];
        }
        $text = (string) file_get_contents($txtFile);
        @unlink($txtFile);

        return $this->parseTextIntoRows($text);
    }

    /**
     * Split raw text into spreadsheet rows.
     * Tab-separated values become multiple cells; every other non-empty line
     * is treated as a single cell.
     *
     * IMPORTANT: Do NOT use str_getcsv() here.  OCR plain-text output is not
     * RFC 4180 CSV — it contains currency values like "$5,079.60" that would be
     * incorrectly split at the comma into ["$5", "079.60"].
     *
     * @return array<int, array<int, string>>
     */
    private function parseTextIntoRows(string $text): array
    {
        $rows = [];
        foreach (preg_split('/\r\n|\r|\n/', trim($text)) as $line) {
            if (trim($line) === '') {
                continue;
            }
            if (str_contains($line, "\t")) {
                $rows[] = explode("\t", $line);
            } else {
                $rows[] = [$line];
            }
        }
        return $rows;
    }

    /**
     * Write rows to the requested spreadsheet format (xlsx / xls / ods).
     */
    private function writeSpreadsheetFromRows(array $rows, string $outputFormat, string $outputPath): bool
    {
        if ($outputFormat === 'ods') {
            return $this->writeOdsCalcFromRows($rows, $outputPath);
        }

        $xlsxOk = $this->writeXlsxFromRows($rows, $outputPath);
        if ($xlsxOk && $outputFormat === 'xls') {
            $tmpXlsx = $outputPath . '_tmp.xlsx';
            try {
                if (@rename($outputPath, $tmpXlsx)) {
                    $this->convertWithLibreOffice($tmpXlsx, 'xlsx', 'xls', $outputPath);
                    @unlink($tmpXlsx);
                }
            } catch (\Exception $e) {
                if (!file_exists($outputPath) && file_exists($tmpXlsx)) {
                    @rename($tmpXlsx, $outputPath);
                }
            }
        }
        return $xlsxOk;
    }

    /**
     * Write a minimal but valid xlsx spreadsheet (OOXML) from a 2-D array of rows.
     */
    private function writeXlsxFromRows(array $rows, string $outputPath): bool
    {
        if (!class_exists('ZipArchive')) {
            return false;
        }

        // Build worksheet XML
        $sheetData = '';
        foreach ($rows as $rowIdx => $cells) {
            $rowNum     = $rowIdx + 1;
            $sheetData .= "<row r=\"{$rowNum}\">";
            foreach ((array) $cells as $colIdx => $cell) {
                // Column letter: A–Z for 0–25; AA–ZZ for 26–701
                if ($colIdx < 26) {
                    $colLetter = chr(65 + $colIdx);
                } elseif ($colIdx < 702) {
                    $colLetter = chr(64 + intdiv($colIdx, 26)) . chr(65 + ($colIdx % 26));
                } else {
                    break; // skip beyond ZZ (702 columns)
                }
                $ref  = $colLetter . $rowNum;
                // Strip XML 1.0 forbidden control characters (U+0000–U+0008, U+000B–U+000C, U+000E–U+001F)
                // before escaping — these bytes make the XML unparseable by Excel.
                $safe     = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string) $cell);
                $escaped  = htmlspecialchars($safe, ENT_QUOTES | ENT_XML1, 'UTF-8');
                $sheetData .= "<c r=\"{$ref}\" t=\"inlineStr\"><is><t xml:space=\"preserve\">{$escaped}</t></is></c>";
            }
            $sheetData .= '</row>';
        }

        $contentTypes =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';

        $relsMain =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';

        $workbook =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';

        // workbookRels: rId1 = sheet, rId2 = styles (Excel requires styles even for simple files)
        $workbookRels =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';

        // Minimal styles.xml — Excel will not open xlsx files without it.
        $styles =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts>'
            . '<fills count="2">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '</fills>'
            . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/></cellXfs>'
            . '</styleSheet>';

        $sheet =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetData>' . $sheetData . '</sheetData>'
            . '</worksheet>';

        $zip = new \ZipArchive();
        if ($zip->open($outputPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return false;
        }
        $zip->addFromString('[Content_Types].xml',        $contentTypes);
        $zip->addFromString('_rels/.rels',                $relsMain);
        $zip->addFromString('xl/workbook.xml',            $workbook);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRels);
        $zip->addFromString('xl/styles.xml',              $styles);
        $zip->addFromString('xl/worksheets/sheet1.xml',   $sheet);
        $zip->close();

        return file_exists($outputPath) && filesize($outputPath) > 100;
    }

    /**
     * Write a minimal ODS spreadsheet (ODF Calc) from a 2-D array of rows.
     */
    private function writeOdsCalcFromRows(array $rows, string $outputPath): bool
    {
        if (!class_exists('ZipArchive')) {
            return false;
        }

        $tableRows = '';
        foreach ($rows as $cells) {
            $tableRows .= '<table:table-row>';
            foreach ((array) $cells as $cell) {
                $escaped    = htmlspecialchars((string) $cell, ENT_QUOTES | ENT_XML1, 'UTF-8');
                $tableRows .= '<table:table-cell office:value-type="string"><text:p>' . $escaped . '</text:p></table:table-cell>';
            }
            $tableRows .= '</table:table-row>';
        }

        $manifest =
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0" manifest:version="1.3">'
            . '<manifest:file-entry manifest:full-path="/" manifest:version="1.3" manifest:media-type="application/vnd.oasis.opendocument.spreadsheet"/>'
            . '<manifest:file-entry manifest:full-path="content.xml" manifest:media-type="text/xml"/>'
            . '</manifest:manifest>';

        $content =
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<office:document-content'
            . ' xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"'
            . ' xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0"'
            . ' xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0"'
            . ' office:version="1.3">'
            . '<office:body><office:spreadsheet>'
            . '<table:table table:name="Sheet1">'
            . $tableRows
            . '</table:table>'
            . '</office:spreadsheet></office:body>'
            . '</office:document-content>';

        $zip = new \ZipArchive();
        if ($zip->open($outputPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return false;
        }
        // ODF spec: 'mimetype' MUST be the first entry and STORED (not compressed)
        $zip->addFromString('mimetype', 'application/vnd.oasis.opendocument.spreadsheet');
        if (method_exists($zip, 'setCompressionName')) {
            $zip->setCompressionName('mimetype', \ZipArchive::CM_STORE);
        }
        $zip->addFromString('META-INF/manifest.xml', $manifest);
        $zip->addFromString('content.xml',           $content);
        $zip->close();

        return file_exists($outputPath) && filesize($outputPath) > 100;
    }

    /**
     * Dispatch image → presentation format to the correct PHP builder.
     */
    private function convertImageToPresentationWithPhp(
        string $inputPath,
        string $inputFormat,
        string $outputFormat,
        string $outputPath
    ): bool {
        return match ($outputFormat) {
            'pptx' => $this->convertImageToPptxWithPhp($inputPath, $inputFormat, $outputPath),
            'ppt'  => $this->convertImageToPptxViaPptx($inputPath, $inputFormat, $outputPath),
            'odp'  => $this->convertImageToOdpWithPhp($inputPath, $inputFormat, $outputPath),
            default => false,
        };
    }

    /**
     * Create a minimal PPTX with the image centred on a blank slide.
     * A PPTX is a ZIP (OOXML) containing presentation.xml, a slide, a slide
     * master, and a slide layout.
     */
    private function convertImageToPptxWithPhp(
        string $inputPath,
        string $inputFormat,
        string $outputPath
    ): bool {
        if (!class_exists('ZipArchive')) {
            return false;
        }

        $imgExt  = strtolower(pathinfo($inputPath, PATHINFO_EXTENSION)) ?: strtolower($inputFormat);
        $mimeMap = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png'  => 'image/png',
            'gif' => 'image/gif',  'webp' => 'image/webp', 'bmp'  => 'image/bmp',
            'tiff' => 'image/tiff', 'svg' => 'image/svg+xml',
        ];
        $mime  = $mimeMap[$imgExt] ?? 'image/png';
        $media = 'image1.' . $imgExt;

        // Slide canvas: 10 in × 7.5 in in EMU (914400 EMU per inch)
        $slideW = 9144000;
        $slideH = 6858000;

        // Scale image to fit the slide with 5 % margin on each side
        $imgData = @getimagesize($inputPath);
        $pxW     = $imgData ? max(1, (int) $imgData[0]) : 800;
        $pxH     = $imgData ? max(1, (int) $imgData[1]) : 600;
        $margin  = (int) ($slideW * 0.05);
        $maxW    = $slideW - 2 * $margin;
        $maxH    = $slideH - 2 * $margin;
        $scale   = min($maxW / $pxW, $maxH / $pxH);
        $emuW    = (int) round($pxW * $scale);
        $emuH    = (int) round($pxH * $scale);
        $offX    = (int) (($slideW - $emuW) / 2);
        $offY    = (int) (($slideH - $emuH) / 2);

        $ctXml =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/ppt/presentation.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.presentation.main+xml"/>'
            . '<Override PartName="/ppt/slides/slide1.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slide+xml"/>'
            . '<Override PartName="/ppt/slideLayouts/slideLayout1.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slideLayout+xml"/>'
            . '<Override PartName="/ppt/slideMasters/slideMaster1.xml" ContentType="application/vnd.openxmlformats-officedocument.presentationml.slideMaster+xml"/>'
            . '</Types>';

        $relsMain =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="ppt/presentation.xml"/>'
            . '</Relationships>';

        $presentation =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:presentation xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"'
            . ' xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">'
            . '<p:sldMasterIdLst><p:sldMasterId id="2147483648" r:id="rId1"/></p:sldMasterIdLst>'
            . '<p:sldSz cx="' . $slideW . '" cy="' . $slideH . '"/>'
            . '<p:notesSz cx="6858000" cy="9144000"/>'
            . '<p:sldIdLst><p:sldId id="256" r:id="rId2"/></p:sldIdLst>'
            . '</p:presentation>';

        $presRels =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideMaster" Target="slideMasters/slideMaster1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slide" Target="slides/slide1.xml"/>'
            . '</Relationships>';

        $slide =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sld xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"'
            . ' xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<p:cSld><p:spTree>'
            . '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/>'
            . '<a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'
            . '<p:pic>'
            . '<p:nvPicPr><p:cNvPr id="2" name="Picture 1"/><p:cNvPicPr/><p:nvPr/></p:nvPicPr>'
            . '<p:blipFill><a:blip r:embed="rId1"/><a:stretch><a:fillRect/></a:stretch></p:blipFill>'
            . '<p:spPr>'
            . '<a:xfrm><a:off x="' . $offX . '" y="' . $offY . '"/><a:ext cx="' . $emuW . '" cy="' . $emuH . '"/></a:xfrm>'
            . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom>'
            . '</p:spPr>'
            . '</p:pic>'
            . '</p:spTree></p:cSld>'
            . '<p:clrMapOvr><a:masterClrMapping/></p:clrMapOvr>'
            . '</p:sld>';

        $slideRels =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/' . $media . '"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideLayout" Target="../slideLayouts/slideLayout1.xml"/>'
            . '</Relationships>';

        $slideMaster =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sldMaster xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"'
            . ' xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<p:cSld><p:spTree>'
            . '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/>'
            . '<a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'
            . '</p:spTree></p:cSld>'
            . '<p:clrMap bg1="lt1" tx1="dk1" bg2="lt2" tx2="dk2" accent1="accent1" accent2="accent2" accent3="accent3" accent4="accent4" accent5="accent5" accent6="accent6" hlink="hlink" folHlink="folHlink"/>'
            . '<p:sldLayoutIdLst><p:sldLayoutId id="2147483649" r:id="rId1"/></p:sldLayoutIdLst>'
            . '<p:txStyles>'
            . '<p:titleStyle><a:lstStyle/></p:titleStyle>'
            . '<p:bodyStyle><a:lstStyle/></p:bodyStyle>'
            . '<p:otherStyle><a:lstStyle/></p:otherStyle>'
            . '</p:txStyles>'
            . '</p:sldMaster>';

        $masterRels =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideLayout" Target="../slideLayouts/slideLayout1.xml"/>'
            . '</Relationships>';

        $slideLayout =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sldLayout xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"'
            . ' xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"'
            . ' type="blank" preserve="1">'
            . '<p:cSld name="Blank"><p:spTree>'
            . '<p:nvGrpSpPr><p:cNvPr id="1" name=""/><p:cNvGrpSpPr/><p:nvPr/></p:nvGrpSpPr>'
            . '<p:grpSpPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="0" cy="0"/>'
            . '<a:chOff x="0" y="0"/><a:chExt cx="0" cy="0"/></a:xfrm></p:grpSpPr>'
            . '</p:spTree></p:cSld>'
            . '<p:clrMapOvr><a:masterClrMapping/></p:clrMapOvr>'
            . '</p:sldLayout>';

        $layoutRels =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideMaster" Target="../slideMasters/slideMaster1.xml"/>'
            . '</Relationships>';

        $zip = new \ZipArchive();
        if ($zip->open($outputPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return false;
        }
        $zip->addFromString('[Content_Types].xml',                         $ctXml);
        $zip->addFromString('_rels/.rels',                                 $relsMain);
        $zip->addFromString('ppt/presentation.xml',                        $presentation);
        $zip->addFromString('ppt/_rels/presentation.xml.rels',             $presRels);
        $zip->addFromString('ppt/slides/slide1.xml',                       $slide);
        $zip->addFromString('ppt/slides/_rels/slide1.xml.rels',            $slideRels);
        $zip->addFromString('ppt/slideMasters/slideMaster1.xml',           $slideMaster);
        $zip->addFromString('ppt/slideMasters/_rels/slideMaster1.xml.rels', $masterRels);
        $zip->addFromString('ppt/slideLayouts/slideLayout1.xml',           $slideLayout);
        $zip->addFromString('ppt/slideLayouts/_rels/slideLayout1.xml.rels', $layoutRels);
        $zip->addFile($inputPath, 'ppt/media/' . $media);
        $zip->close();

        return file_exists($outputPath) && filesize($outputPath) > 100;
    }

    /**
     * Convert an image to PPT (legacy binary format) via a temporary PPTX intermediate.
     */
    private function convertImageToPptxViaPptx(
        string $inputPath,
        string $inputFormat,
        string $outputPath
    ): bool {
        $tmpBase = tempnam(sys_get_temp_dir(), 'cx_ppt_');
        @unlink($tmpBase);
        $tmpPptx = $tmpBase . '.pptx';
        try {
            if (!$this->convertImageToPptxWithPhp($inputPath, $inputFormat, $tmpPptx)) {
                return false;
            }
            return $this->convertWithLibreOffice($tmpPptx, 'pptx', 'ppt', $outputPath);
        } finally {
            if (file_exists($tmpPptx)) {
                @unlink($tmpPptx);
            }
        }
    }

    /**
     * Create a minimal ODP (ODF Presentation) with the image centred on a single slide.
     */
    private function convertImageToOdpWithPhp(
        string $inputPath,
        string $inputFormat,
        string $outputPath
    ): bool {
        if (!class_exists('ZipArchive')) {
            return false;
        }

        $imgExt  = strtolower(pathinfo($inputPath, PATHINFO_EXTENSION)) ?: strtolower($inputFormat);
        $mimeMap = [
            'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png'  => 'image/png',
            'gif' => 'image/gif',  'webp' => 'image/webp', 'bmp'  => 'image/bmp',
            'tiff' => 'image/tiff', 'svg' => 'image/svg+xml',
        ];
        $mime  = $mimeMap[$imgExt] ?? 'image/png';
        $media = 'Pictures/image.' . $imgExt;

        // Scale image to fit A4 landscape (25.4 cm × 19.05 cm) with 5 % margin
        $imgData = @getimagesize($inputPath);
        $pxW     = $imgData ? max(1, (int) $imgData[0]) : 800;
        $pxH     = $imgData ? max(1, (int) $imgData[1]) : 600;
        $maxCmW  = 24.13;
        $maxCmH  = 18.1;
        $cmPerPx = 2.54 / 96.0;
        $cmW     = min($maxCmW, round($pxW * $cmPerPx, 3));
        $cmH     = round($cmW * $pxH / $pxW, 3);
        if ($cmH > $maxCmH) {
            $cmH = $maxCmH;
            $cmW = round($cmH * $pxW / $pxH, 3);
        }
        $xCm = round((25.4 - $cmW) / 2, 3);
        $yCm = round((19.05 - $cmH) / 2, 3);

        $manifest =
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0" manifest:version="1.3">'
            . '<manifest:file-entry manifest:full-path="/" manifest:version="1.3" manifest:media-type="application/vnd.oasis.opendocument.presentation"/>'
            . '<manifest:file-entry manifest:full-path="content.xml" manifest:media-type="text/xml"/>'
            . '<manifest:file-entry manifest:full-path="' . $media . '" manifest:media-type="' . $mime . '"/>'
            . '</manifest:manifest>';

        $content =
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<office:document-content'
            . ' xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"'
            . ' xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"'
            . ' xmlns:presentation="urn:oasis:names:tc:opendocument:xmlns:presentation:1.0"'
            . ' xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0"'
            . ' xmlns:xlink="http://www.w3.org/1999/xlink"'
            . ' xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0">'
            . '<office:automatic-styles>'
            . '<style:style style:name="dp1" style:family="drawing-page"/>'
            . '<style:style style:name="fr1" style:family="graphic">'
            . '<style:graphic-properties style:run-through="foreground" style:wrap="none"/>'
            . '</style:style>'
            . '</office:automatic-styles>'
            . '<office:body><office:presentation>'
            . '<draw:page draw:name="page1" draw:style-name="dp1" draw:master-page-name="Default">'
            . '<draw:frame draw:style-name="fr1" draw:name="Image1"'
            . ' svg:x="' . $xCm . 'cm" svg:y="' . $yCm . 'cm"'
            . ' svg:width="' . $cmW . 'cm" svg:height="' . $cmH . 'cm">'
            . '<draw:image xlink:href="' . $media . '" xlink:type="simple" xlink:show="embed" xlink:actuate="onLoad"/>'
            . '</draw:frame>'
            . '</draw:page>'
            . '</office:presentation></office:body>'
            . '</office:document-content>';

        $zip = new \ZipArchive();
        if ($zip->open($outputPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return false;
        }
        // ODF spec: 'mimetype' MUST be the first entry and STORED (not compressed)
        $zip->addFromString('mimetype', 'application/vnd.oasis.opendocument.presentation');
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
     * Extract text from an image and write it to a text-format output file
     * (txt / html / md / csv).
     *
     * Priority chain:
     *   1. AI format-specific OCR (vision model with a prompt tailored to the
     *      target format — produces HTML, Markdown, CSV, or plain text directly).
     *   2. Tesseract plain-text OCR (local, --psm 6).
     *   3. AI generic OCR fallback (if Tesseract is absent).
     *   4. Error if all three fail.
     */
    private function convertImageToTextWithOcr(
        string $inputPath,
        string $outputFormat,
        string $outputPath
    ): bool {
        if (!file_exists($inputPath)) {
            throw new \RuntimeException(
                "Image file not found: cannot perform OCR."
            );
        }

        // ── 1. AI format-specific OCR (best quality) ──────────────────────
        //
        // Use the vision model with a prompt tailored to the target format.
        // For CSV: returns RFC 4180 CSV preserving table column structure.
        // For HTML: returns semantic HTML with headings, paragraphs, tables.
        // For MD:   returns Markdown with headings, pipe tables, lists.
        // For TXT:  returns plain text in reading order.
        if ($this->aiService !== null) {
            $aiOcr = $this->aiService->ocrForFormat($inputPath, $outputFormat, 'free');
            if ($aiOcr['success'] && !empty(trim($aiOcr['text'] ?? ''))) {
                return $this->writeTextOutput($aiOcr['text'], $outputFormat, $outputPath);
            }
        }

        // ── 2. Tesseract plain-text OCR (local fallback) ──────────────────
        $text = '';
        $tess = trim((string) shell_exec('which tesseract 2>/dev/null'));
        if ($tess) {
            $tmpBase = sys_get_temp_dir() . '/cx_tess_' . getmypid() . '_' . bin2hex(random_bytes(8));
            exec(
                escapeshellarg($tess) . ' ' . escapeshellarg($inputPath)
                . ' ' . escapeshellarg($tmpBase) . ' --psm 6 -l eng 2>/dev/null',
                $_lines, $tessCode
            );
            $tessOut = $tmpBase . '.txt';
            if ($tessCode === 0 && file_exists($tessOut)) {
                $text = (string) file_get_contents($tessOut);
                @unlink($tessOut);
            }
        }

        // ── 3. AI generic OCR (when Tesseract is not installed) ────────────
        if (empty(trim($text)) && $this->aiService !== null) {
            $aiOcr = $this->aiService->ocr($inputPath, 'free');
            if ($aiOcr['success'] && !empty(trim($aiOcr['text'] ?? ''))) {
                $text = $aiOcr['text'];
            }
        }

        // ── 4. Error if nothing worked ─────────────────────────────────────
        if (empty(trim($text))) {
            throw new \RuntimeException(
                "Text extraction from images requires Tesseract or a configured AI provider. "
                . "Install Tesseract (apt-get install tesseract-ocr) or add an AI provider in Settings."
            );
        }

        return $this->writeTextOutput($text, $outputFormat, $outputPath);
    }

    /**
     * Write extracted OCR text to the target text-format output file.
     *
     * When called after `ocrForFormat()`, the $text is already in the target
     * format (HTML, Markdown, CSV) so it can be written directly.  When called
     * with raw Tesseract plain-text, a minimal conversion is applied so the
     * output is at least syntactically valid for the target format.
     */
    private function writeTextOutput(string $text, string $outputFormat, string $outputPath): bool
    {
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        switch ($outputFormat) {
            case 'html':
                // If the text is already HTML (from ocrForFormat), wrap it in a
                // minimal document shell; otherwise convert plain text to HTML.
                if (stripos($text, '<') !== false && stripos($text, '>') !== false) {
                    // Already looks like HTML — wrap in document shell if needed
                    if (stripos($text, '<html') === false) {
                        $text = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body>'
                              . "\n" . $text . "\n</body></html>";
                    }
                } else {
                    $body = nl2br(htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
                    $text = '<!DOCTYPE html><html><head><meta charset="UTF-8"></head><body><p>'
                          . $body . '</p></body></html>';
                }
                return file_put_contents($outputPath, $text) !== false;

            case 'md':
                // Already Markdown (from ocrForFormat) or plain text — write as-is
                return file_put_contents($outputPath, $text) !== false;

            case 'csv':
                // If the text is already RFC 4180 CSV (from ocrForFormat), write directly
                if (str_contains($text, ',') || str_contains($text, '"')) {
                    return file_put_contents($outputPath, $text) !== false;
                }
                // Otherwise: each non-empty line → single-column CSV row
                $fh = fopen($outputPath, 'w');
                if (!$fh) {
                    return false;
                }
                $lines = preg_split('/\r\n|\r|\n/', trim($text)) ?: [];
                foreach ($lines as $line) {
                    if (trim($line) !== '') {
                        fputcsv($fh, [$line]);
                    }
                }
                fclose($fh);
                return file_exists($outputPath);

            default: // txt
                return file_put_contents($outputPath, $text) !== false;
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
                "This document conversion is not available on this server. "
                . "Only text, markup, and image-to-image conversions are supported."
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
        // --infilter={filter} – force PDF Import filter when input is PDF.
        //   Without this, headless LO on some installs fails to select the filter
        //   automatically, producing empty or corrupt output.
        //   NOTE: LibreOffice requires equals-sign syntax (--infilter={filter}),
        //   NOT space-separated (--infilter filter) — the latter causes exit 1
        //   with "Error in option: --infilter".
        $pid      = getmypid();
        $infilter = ($inputFormat === 'pdf')
            ? '--infilter=' . escapeshellarg('draw_pdf_import') . ' '
            : '';
        $cmd = "DISPLAY= HOME=/tmp {$lo} --headless --norestore --nolockcheck "
             . "-env:UserInstallation=file:///tmp/lo-{$pid} "
             . $infilter
             . "--convert-to {$filterSpec} {$inFile} --outdir {$outDirEsc} 2>&1";
        exec($cmd, $output, $code);

        if ($code !== 0) {
            // Log the full output for debugging but don't expose it to users
            Logger::warning('Document conversion failed (exit ' . $code . '): ' . implode("\n", $output));
            throw new \RuntimeException(
                "Document conversion failed. Please try a different output format."
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
                "Conversion produced an empty file. Please try a different output format."
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
                "Image conversion is not available on this server. "
                . "Please try a different output format."
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
                "This text conversion is not available on this server."
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

    private function csvToText(string $inputPath, string $outputPath, string $delimiter = ','): bool
    {
        $lines = file($inputPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return false;
        }
        $text = implode("\n", array_map(function ($line) use ($delimiter) {
            return implode("\t", str_getcsv($line, $delimiter));
        }, $lines));
        return file_put_contents($outputPath, $text) !== false;
    }
}
