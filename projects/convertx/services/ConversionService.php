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
     * The plan tier of the current user ('free'|'pro'|'enterprise').
     * Passed through to AI service calls so provider routing respects the user's
     * subscription level.  Defaults to 'free' so free-tier-capable providers are
     * always tried.
     */
    private string $planTier = 'free';

    /**
     * Inject the AI service to enable AI-powered OCR fallback during conversion.
     * Call this before convert() when an AIService instance is available.
     */
    public function setAIService(AIService $aiService): void
    {
        $this->aiService = $aiService;
    }

    /**
     * Set the current user's plan tier so AI provider routing uses the correct
     * capability tier ('free'|'pro'|'enterprise').
     */
    public function setPlanTier(string $planTier): void
    {
        $this->planTier = in_array($planTier, ['free', 'pro', 'enterprise'], true)
            ? $planTier
            : 'free';
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
        // Priority — OCR is MANDATORY:
        //   A. AI document OCR (GPT-4o vision) — highest quality; extracts structured
        //      text (headings, paragraphs, tables) then writes a proper text document.
        //   B. Tesseract OCR (local, no API key) — good quality; always attempted
        //      when AI is unavailable so the user always gets searchable text.
        //   C. PHP ZipArchive image-embed — lowest quality (no searchable text) but
        //      still produces a valid document that opens correctly.
        $phpWriterFormats = ['docx', 'odt', 'rtf', 'doc'];
        if ($isInputImage && in_array($outputFormat, $phpWriterFormats, true)) {
            // A. AI path
            if ($this->aiService !== null) {
                if ($this->convertImageToDocumentWithOcr($inputPath, $inputFormat, $outputFormat, $outputPath)) {
                    return true;
                }
            }
            // B. Tesseract mandatory fallback (local OCR, no API key required)
            if ($this->convertImageToDocumentWithTesseract($inputPath, $inputFormat, $outputFormat, $outputPath)) {
                return true;
            }
            // C. Image-embed fallback (ZipArchive) — still valid, just not searchable
            if ($this->convertImageToDocumentWithPhp($inputPath, $inputFormat, $outputFormat, $outputPath)) {
                return true;
            }
            // D. Chain fallback (image→PDF→writer via LibreOffice)
        }

        // 4c. Image → plain-text formats (txt / html / md / csv): OCR mandatory.
        $textOutputFormats = ['txt', 'html', 'md', 'csv'];
        if ($isInputImage && in_array($outputFormat, $textOutputFormats, true)) {
            return $this->convertImageToTextWithOcr($inputPath, $outputFormat, $outputPath);
        }

        // 4d. Image → spreadsheet formats (xlsx / xls / ods): OCR mandatory.
        $spreadsheetOutputFormats = ['xlsx', 'xls', 'ods'];
        if ($isInputImage && in_array($outputFormat, $spreadsheetOutputFormats, true)) {
            return $this->convertImageToSpreadsheetWithOcr($inputPath, $outputFormat, $outputPath);
        }

        // 4e. Image → presentation formats (pptx / odp / ppt).
        //     Priority: AI OCR text → text-based slide; then image-embed fallback.
        $presentationOutputFormats = ['pptx', 'odp', 'ppt'];
        if ($isInputImage && in_array($outputFormat, $presentationOutputFormats, true)) {
            if ($this->convertImageToPresentationWithOcr($inputPath, $inputFormat, $outputFormat, $outputPath)) {
                return true;
            }
            if ($this->convertImageToPresentationWithPhp($inputPath, $inputFormat, $outputFormat, $outputPath)) {
                return true;
            }
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
        $officeFormats = ['pdf', 'docx', 'doc', 'odt', 'rtf', 'html', 'xlsx', 'xls', 'ods', 'pptx', 'ppt', 'odp', 'epub'];
        if (in_array($inputFormat, $officeFormats, true) || in_array($outputFormat, $officeFormats, true)) {
            // PDF → spreadsheet / presentation: LibreOffice crashes on these.
            // Use OCR pipeline instead: rasterize first page → OCR → write output.
            if ($inputFormat === 'pdf'
                && in_array($outputFormat, ['xlsx', 'xls', 'ods', 'csv', 'pptx', 'ppt', 'odp'], true)
            ) {
                if (in_array($outputFormat, ['xlsx', 'xls', 'ods', 'csv'], true)) {
                    return $this->convertPdfToSpreadsheet($inputPath, $outputFormat, $outputPath);
                }
                return $this->convertPdfToPresentation($inputPath, $outputFormat, $outputPath);
            }

            // PDF → writer: try LibreOffice (needs pdfimport), fall back to OCR pipeline
            if ($inputFormat === 'pdf' && in_array($outputFormat, ['docx', 'doc', 'odt', 'rtf'], true)) {
                try {
                    return $this->convertWithLibreOffice($inputPath, $inputFormat, $outputFormat, $outputPath);
                } catch (\RuntimeException $e) {
                    // LibreOffice unavailable or pdfimport not installed → rasterize → OCR
                    return $this->convertPdfToDocument($inputPath, $outputFormat, $outputPath);
                }
            }

            // PDF → plain text: pdftotext first, then rasterize → OCR fallback
            if ($inputFormat === 'pdf' && in_array($outputFormat, ['txt', 'html', 'md'], true)) {
                return $this->convertPdfToText($inputPath, $outputFormat, $outputPath);
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

    // ------------------------------------------------------------------ //
    //  Page-size & PDF rasterization helpers                               //
    // ------------------------------------------------------------------ //

    /**
     * Detect the nearest standard page size from image pixel dimensions.
     *
     * Uses both the aspect ratio AND the estimated physical size at a typical
     * scan resolution (150 DPI for scanned documents) to distinguish between
     * sizes that share the same aspect ratio (e.g., A4 vs A3 vs A5).
     *
     * Returns an array with:
     *   name      — "A4", "Letter", "A3", "Legal", "A5"
     *   twip_w    — page width in twips (OOXML: 1 twip = 1/1440 in)
     *   twip_h    — page height in twips
     *   cm_w      — page width in cm (ODF)
     *   cm_h      — page height in cm
     *   landscape — bool
     *
     * @param int $pxW  Image width in pixels
     * @param int $pxH  Image height in pixels
     * @return array
     */
    private function detectPageSizeFromImage(int $pxW, int $pxH): array
    {
        // Standard page sizes in points (portrait orientation).
        // 1 pt = 1/72 in; A4 = 210×297 mm = 595×842 pt
        $standards = [
            'A4'     => [595,  842],   // most common document size
            'Letter' => [612,  792],   // US Letter
            'A3'     => [842, 1191],   // 2× A4
            'Legal'  => [612, 1008],   // US Legal
            'A5'     => [420,  595],   // half A4
            'A6'     => [298,  420],
        ];

        $landscape = ($pxW > $pxH);

        // Normalize to portrait for comparison (short side × long side)
        $shortPx = (int) min($pxW, $pxH);
        $longPx  = (int) max($pxW, $pxH);

        // We assume 150 DPI (typical document scan) to estimate physical size.
        // The match score combines aspect ratio AND scale similarity.
        $assumedDpi  = 150.0;
        $shortInches = $shortPx / $assumedDpi;
        $longInches  = $longPx  / $assumedDpi;

        $best      = 'A4';
        $bestScore = PHP_FLOAT_MAX;
        foreach ($standards as $name => [$ptW, $ptH]) {
            // Portrait: $ptW is short side, $ptH is long side (both in pt; 72 pt = 1 in)
            $stdShortIn = $ptW / 72.0;
            $stdLongIn  = $ptH / 72.0;

            // Aspect-ratio error
            $aspectDiff = abs(($shortInches / $longInches) - ($stdShortIn / $stdLongIn));
            // Scale error: how far are we from this standard at 150 DPI (normalised 0–1)
            $scaleDiff  = abs($shortInches / $stdShortIn - 1.0)
                        + abs($longInches  / $stdLongIn  - 1.0);

            $score = $aspectDiff * 5.0 + $scaleDiff;   // weight aspect ratio higher
            if ($score < $bestScore) {
                $bestScore = $score;
                $best      = $name;
            }
        }

        [$ptW, $ptH] = $standards[$best];
        if ($landscape) {
            [$ptW, $ptH] = [$ptH, $ptW];
        }

        return [
            'name'      => $best,
            'landscape' => $landscape,
            'twip_w'    => (int) ($ptW * 20),        // 20 twips per point
            'twip_h'    => (int) ($ptH * 20),
            'cm_w'      => round($ptW * 2.54 / 72, 3),
            'cm_h'      => round($ptH * 2.54 / 72, 3),
        ];
    }

    /**
     * Rasterize the first page of a PDF to a high-resolution PNG.
     *
     * Uses Ghostscript (preferred) or ImageMagick.  Returns the absolute path
     * of the temp PNG or null if neither tool is available.
     *
     * The caller is responsible for deleting the returned file.
     *
     * @param string $pdfPath Absolute path to the PDF file
     * @param int    $dpi     Resolution (150–300 dpi gives good OCR quality)
     * @return string|null    Temp PNG path, or null on failure
     */
    private function rasterizePdf(string $pdfPath, int $dpi = 200): ?string
    {
        if (!file_exists($pdfPath)) {
            return null;
        }

        $tmpPng = tempnam(sys_get_temp_dir(), 'cx_pdf_') . '.png';

        // ── Ghostscript (preferred — not blocked by policy.xml) ───────────
        $gs = trim((string) shell_exec('which gs 2>/dev/null'));
        if ($gs) {
            exec(
                escapeshellarg($gs)
                . ' -dNOPAUSE -dBATCH -dSAFER -sDEVICE=pngalpha'
                . ' -r' . (int) $dpi
                . ' -dFirstPage=1 -dLastPage=1'
                . ' -sOutputFile=' . escapeshellarg($tmpPng)
                . ' ' . escapeshellarg($pdfPath) . ' 2>/dev/null',
                $_o, $code
            );
            if ($code === 0 && file_exists($tmpPng) && filesize($tmpPng) > 500) {
                return $tmpPng;
            }
        }

        // ── ImageMagick / Magick ──────────────────────────────────────────
        $im = trim((string) shell_exec('which convert 2>/dev/null'))
           ?: trim((string) shell_exec('which magick 2>/dev/null'));
        if ($im) {
            exec(
                escapeshellarg($im)
                . ' -density ' . (int) $dpi
                . ' ' . escapeshellarg($pdfPath . '[0]')   // first page only
                . ' -quality 95 -background white -alpha remove'
                . ' ' . escapeshellarg($tmpPng) . ' 2>/dev/null',
                $_o, $code
            );
            if ($code === 0 && file_exists($tmpPng) && filesize($tmpPng) > 500) {
                return $tmpPng;
            }
        }

        @unlink($tmpPng);
        return null;
    }

    /**
     * PDF → spreadsheet via OCR.
     *
     * Rasterizes the first PDF page to PNG then passes it through the same
     * AI/Tesseract spreadsheet pipeline used for image→spreadsheet.
     */
    private function convertPdfToSpreadsheet(
        string $inputPath,
        string $outputFormat,
        string $outputPath
    ): bool {
        $tmpPng = $this->rasterizePdf($inputPath);
        if ($tmpPng !== null) {
            try {
                return $this->convertImageToSpreadsheetWithOcr($tmpPng, $outputFormat, $outputPath);
            } finally {
                @unlink($tmpPng);
            }
        }

        // No rasterizer — try AI directly on the PDF if it has a data-URI pathway
        if ($this->aiService !== null) {
            $aiTable = $this->aiService->ocrTable($inputPath, $this->planTier);
            if ($aiTable['success'] && !empty($aiTable['rows'])) {
                return $this->writeSpreadsheetFromRows($aiTable['rows'], $outputFormat, $outputPath);
            }
        }

        throw new \RuntimeException(
            "PDF to spreadsheet conversion requires Ghostscript or ImageMagick for rasterization "
            . "plus Tesseract or an AI provider for OCR. "
            . "Install ghostscript (apt install ghostscript) or configure an AI provider."
        );
    }

    /**
     * PDF → writer document (DOCX/ODT/RTF/DOC) via OCR.
     *
     * Rasterizes the first PDF page to PNG then passes it through the same
     * AI/Tesseract document pipeline used for image→document.
     */
    private function convertPdfToDocument(
        string $inputPath,
        string $outputFormat,
        string $outputPath
    ): bool {
        $tmpPng = $this->rasterizePdf($inputPath);
        if ($tmpPng !== null) {
            try {
                if ($this->aiService !== null
                    && $this->convertImageToDocumentWithOcr($tmpPng, 'png', $outputFormat, $outputPath)
                ) {
                    return true;
                }
                if ($this->convertImageToDocumentWithTesseract($tmpPng, 'png', $outputFormat, $outputPath)) {
                    return true;
                }
                return $this->convertImageToDocumentWithPhp($tmpPng, 'png', $outputFormat, $outputPath);
            } finally {
                @unlink($tmpPng);
            }
        }

        throw new \RuntimeException(
            "PDF to document conversion requires Ghostscript or ImageMagick to rasterize the PDF. "
            . "Install ghostscript (apt install ghostscript) or imagemagick."
        );
    }

    /**
     * PDF → plain text (txt / html / md).
     *
     * Priority:
     *   1. pdftotext (poppler-utils) — fast, accurate for digital PDFs
     *   2. Rasterize → Tesseract/AI OCR — for scanned PDFs
     *   3. LibreOffice text export
     */
    private function convertPdfToText(
        string $inputPath,
        string $outputFormat,
        string $outputPath
    ): bool {
        // ── 1. pdftotext (digital PDF) ────────────────────────────────────
        $pdfToText = trim((string) shell_exec('which pdftotext 2>/dev/null'));
        if ($pdfToText) {
            $tmpTxt = tempnam(sys_get_temp_dir(), 'cx_pdft_') . '.txt';
            exec(
                escapeshellarg($pdfToText) . ' -layout '
                . escapeshellarg($inputPath) . ' '
                . escapeshellarg($tmpTxt) . ' 2>/dev/null',
                $_o, $code
            );
            if ($code === 0 && file_exists($tmpTxt) && trim((string) file_get_contents($tmpTxt)) !== '') {
                $text = (string) file_get_contents($tmpTxt);
                @unlink($tmpTxt);
                return $this->writeTextOutput($text, $outputFormat, $outputPath);
            }
            @unlink($tmpTxt);
        }

        // ── 2. Rasterize → OCR (scanned PDF) ─────────────────────────────
        $tmpPng = $this->rasterizePdf($inputPath);
        if ($tmpPng !== null) {
            try {
                return $this->convertImageToTextWithOcr($tmpPng, $outputFormat, $outputPath);
            } finally {
                @unlink($tmpPng);
            }
        }

        // ── 3. LibreOffice text export ────────────────────────────────────
        try {
            return $this->convertWithLibreOffice($inputPath, 'pdf', $outputFormat, $outputPath);
        } catch (\RuntimeException $e) {
            // fall through
        }

        throw new \RuntimeException(
            "PDF to text extraction requires poppler-utils (apt install poppler-utils) "
            . "or Ghostscript/ImageMagick for rasterization plus OCR."
        );
    }

    /**
     * PDF → presentation (pptx / odp / ppt).
     *
     * Rasterizes the first page then embeds the image in a slide at the correct
     * page dimensions.
     */
    private function convertPdfToPresentation(
        string $inputPath,
        string $outputFormat,
        string $outputPath
    ): bool {
        $tmpPng = $this->rasterizePdf($inputPath);
        if ($tmpPng !== null) {
            try {
                if ($this->convertImageToPresentationWithOcr($tmpPng, 'png', $outputFormat, $outputPath)) {
                    return true;
                }
                return $this->convertImageToPresentationWithPhp($tmpPng, 'png', $outputFormat, $outputPath);
            } finally {
                @unlink($tmpPng);
            }
        }

        throw new \RuntimeException(
            "PDF to presentation conversion requires Ghostscript or ImageMagick. "
            . "Install ghostscript (apt install ghostscript)."
        );
    }

    /**
     * Mandatory Tesseract OCR path for image → writer format (DOCX/ODT/RTF/DOC).
     *
     * Pre-processes the image for better OCR accuracy (grayscale + contrast
     * normalisation), then runs Tesseract in plain-text mode and passes the
     * extracted text through the same document builders used by the AI path.
     * This ensures searchable text is always produced when Tesseract is
     * available, even without an AI API key.
     *
     * Returns false silently when Tesseract is not installed.
     */
    private function convertImageToDocumentWithTesseract(
        string $inputPath,
        string $inputFormat,
        string $outputFormat,
        string $outputPath
    ): bool {
        if (!file_exists($inputPath)) {
            return false;
        }

        $tess = trim((string) shell_exec('which tesseract 2>/dev/null'));
        if (!$tess) {
            return false;
        }

        // Pre-process for better OCR on coloured backgrounds
        $processedPath = $this->preprocessImageForOcr($inputPath);
        $ocrInput      = $processedPath ?? $inputPath;

        $tmpBase = sys_get_temp_dir() . '/cx_tess_' . bin2hex(random_bytes(12));
        exec(
            escapeshellarg($tess) . ' ' . escapeshellarg($ocrInput)
            . ' ' . escapeshellarg($tmpBase) . ' --psm 6 -l eng 2>/dev/null',
            $_o, $code
        );
        if ($processedPath !== null) {
            @unlink($processedPath);
        }

        $txtFile = $tmpBase . '.txt';
        if ($code !== 0 || !file_exists($txtFile)) {
            return false;
        }
        $text = trim((string) file_get_contents($txtFile));
        @unlink($txtFile);

        if (empty($text)) {
            return false;
        }

        // Detect page size so the output document matches the original
        $imgData  = @getimagesize($inputPath);
        $pageSize = $this->detectPageSizeFromImage(
            $imgData ? (int) $imgData[0] : 800,
            $imgData ? (int) $imgData[1] : 1131   // default A4 proportion
        );

        return match ($outputFormat) {
            'docx' => $this->writeDocxFromText($text, $outputPath, $inputPath, $inputFormat, $pageSize),
            'odt'  => $this->writeOdtFromText($text, $outputPath, $inputPath, $inputFormat, $pageSize),
            'rtf'  => $this->writeRtfFromText($text, $outputPath, $pageSize),
            'doc'  => $this->writeDocViaDocxFromText($text, $outputPath, $inputPath, $inputFormat, $pageSize),
            default => false,
        };
    }


    /**
     * Image → presentation with AI text extraction.
     *
     * Extracts text from the image via AI, then builds a text-based slide deck.
     * Falls back silently when AI is unavailable.
     */
    private function convertImageToPresentationWithOcr(
        string $inputPath,
        string $inputFormat,
        string $outputFormat,
        string $outputPath
    ): bool {
        if (!file_exists($inputPath) || $this->aiService === null) {
            return false;
        }

        // Use document OCR (Markdown) so we get structured headings and bullets
        $aiResult = $this->aiService->ocrDocument($inputPath, $this->planTier);
        if (!$aiResult['success'] || empty(trim($aiResult['text'] ?? ''))) {
            return false;
        }

        $text    = $aiResult['text'];
        $imgData = @getimagesize($inputPath);
        $pxW     = $imgData ? (int) $imgData[0] : 1280;
        $pxH     = $imgData ? (int) $imgData[1] : 720;

        return match ($outputFormat) {
            'pptx' => $this->writePptxFromText($text, $inputPath, $inputFormat, $outputPath, $pxW, $pxH),
            'odp'  => $this->writeOdpFromText($text, $inputPath, $inputFormat, $outputPath, $pxW, $pxH),
            default => false,
        };
    }

    /**
     * AI-powered image → document conversion.
     *
     * Uses the vision model to extract structured content (headings, paragraphs,
     * tables, lists) from the image and writes it as a proper text-based document.
     * The source image is embedded on page 1 so the original visual is preserved;
     * the extracted text follows (giving both searchable content and correct appearance).
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
        $aiResult = $this->aiService->ocrDocument($inputPath, $this->planTier);
        if (!$aiResult['success'] || empty(trim($aiResult['text'] ?? ''))) {
            return false;
        }

        $text = $aiResult['text'];

        // Detect page size from the image so the output document matches the original
        $imgData  = @getimagesize($inputPath);
        $pageSize = $this->detectPageSizeFromImage(
            $imgData ? (int) $imgData[0] : 800,
            $imgData ? (int) $imgData[1] : 1131
        );

        return match ($outputFormat) {
            'docx' => $this->writeDocxFromText($text, $outputPath, $inputPath, $inputFormat, $pageSize),
            'odt'  => $this->writeOdtFromText($text, $outputPath, $inputPath, $inputFormat, $pageSize),
            'rtf'  => $this->writeRtfFromText($text, $outputPath, $pageSize),
            'doc'  => $this->writeDocViaDocxFromText($text, $outputPath, $inputPath, $inputFormat, $pageSize),
            default => false,
        };
    }

    /**
     * Build a DOCX file from structured Markdown-like text (from AI / Tesseract OCR).
     *
     * Supported Markdown constructs:
     *   # Heading 1    → Heading1 paragraph style (blue, bold, large)
     *   ## Heading 2   → Heading2 paragraph style (blue, bold, medium)
     *   ### Heading 3  → Heading3 paragraph style (dark blue, bold, normal)
     *   | col | col |  → OOXML <w:tbl> table with borders
     *   - item         → ListBullet paragraph style (indented bullet)
     *   1. item        → ListNumber paragraph style (indented numbered)
     *   blank line     → empty paragraph (spacer)
     *   everything else → Normal paragraph style
     *
     * A complete styles.xml is embedded so all styles render correctly in Word/LibreOffice.
     * The source image (if provided) is embedded FIRST on page 1 so the original
     * visual is preserved; extracted text follows on the same/subsequent pages.
     *
     * @param array $pageSize  Output of detectPageSizeFromImage() — controls page dimensions
     */
    private function writeDocxFromText(
        string $text,
        string $outputPath,
        string $imagePath = '',
        string $inputFormat = '',
        array  $pageSize = []
    ): bool {
        if (!class_exists('ZipArchive')) {
            return false;
        }

        // Default to A4 if no page size provided
        if (empty($pageSize)) {
            $pageSize = ['twip_w' => 11906, 'twip_h' => 16838, 'name' => 'A4', 'landscape' => false];
        }
        $pgW  = (int) $pageSize['twip_w'];
        $pgH  = (int) $pageSize['twip_h'];
        // Standard margins: 1 inch = 1440 twips
        $pgMar = 1440;

        $bodyXml    = '';
        $mediaFiles = [];
        $imgRelXml  = '';
        $imgCTXml   = '';

        // ── Embed source image on page 1 (preserves original visual design) ─
        if ($imagePath && file_exists($imagePath)) {
            $mimeMap = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
                        'gif' => 'image/gif',  'webp' => 'image/webp', 'bmp' => 'image/bmp',
                        'tiff' => 'image/tiff', 'svg' => 'image/svg+xml'];
            $imgExt  = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION)) ?: strtolower($inputFormat);
            $mime    = $mimeMap[$imgExt] ?? 'image/png';
            $media   = 'image1.' . $imgExt;
            $imgData = @getimagesize($imagePath);
            $pxW     = $imgData ? max(1, (int) $imgData[0]) : 800;
            $pxH     = $imgData ? max(1, (int) $imgData[1]) : 600;

            // Scale image to fit content area (page width minus margins)
            $maxEmuW  = (int) (($pgW - 2 * $pgMar) * 914400 / 1440);
            $emuPerPx = 914400.0 / 96.0;
            $emuW     = (int) min($maxEmuW, round($pxW * $emuPerPx));
            $emuH     = (int) round($emuW * $pxH / $pxW);

            $bodyXml .= '<w:p><w:r><w:drawing>'
                     . '<wp:inline distT="0" distB="0" distL="0" distR="0"'
                     . ' xmlns:wp="http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing">'
                     . '<wp:extent cx="' . $emuW . '" cy="' . $emuH . '"/>'
                     . '<wp:effectExtent l="0" t="0" r="0" b="0"/>'
                     . '<wp:docPr id="1" name="Source Image"/>'
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
                     . '</w:drawing></w:r></w:p>'
                     // Page break before extracted text section
                     . '<w:p><w:r><w:br w:type="page"/></w:r></w:p>';

            $mediaFiles[$media] = $imagePath;
            $imgRelXml = '<Relationship Id="rId1"'
                . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image"'
                . ' Target="media/' . $media . '"/>';
            $imgCTXml = '<Override PartName="/word/media/' . $media . '" ContentType="' . $mime . '"/>';
        }

        // ── Parse Markdown text into DOCX body XML ────────────────────────
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $i = 0;
        while ($i < count($lines)) {
            $line = $lines[$i];

            // Markdown pipe table
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
                $bodyXml .= $this->docxParagraph(htmlspecialchars($m[2], ENT_XML1), "Heading{$lvl}");
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

            // Blank line → spacer paragraph
            if (trim($line) === '') {
                $bodyXml .= '<w:p/>';
                $i++;
                continue;
            }

            // Inline **bold** and *italic*
            $safe = htmlspecialchars($line, ENT_XML1);
            $safe = preg_replace('/\*\*(.+?)\*\*/', '<w:r><w:rPr><w:b/></w:rPr><w:t xml:space="preserve">$1</w:t></w:r>', $safe);
            $safe = preg_replace('/\*(.+?)\*/',     '<w:r><w:rPr><w:i/></w:rPr><w:t xml:space="preserve">$1</w:t></w:r>', $safe);

            if (!str_contains((string) $safe, '<w:r>')) {
                $bodyXml .= $this->docxParagraph((string) $safe, 'Normal');
            } else {
                $bodyXml .= '<w:p>' . $safe . '</w:p>';
            }
            $i++;
        }

        // ── Styles.xml — full built-in Word styles so headings/lists render correctly
        $stylesXml = $this->buildDocxStylesXml();

        // ── Assemble document.xml ─────────────────────────────────────────
        $landscape  = !empty($pageSize['landscape']) ? ' w:orient="landscape"' : '';
        $document =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:document'
            . ' xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<w:body>'
            . $bodyXml
            . '<w:sectPr>'
            . '<w:pgSz w:w="' . $pgW . '" w:h="' . $pgH . '"' . $landscape . '/>'
            . '<w:pgMar w:top="' . $pgMar . '" w:right="' . $pgMar . '"'
            . ' w:bottom="' . $pgMar . '" w:left="' . $pgMar . '"/>'
            . '</w:sectPr>'
            . '</w:body></w:document>';

        $rels =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . $imgRelXml
            . '<Relationship Id="rId2"'
            . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"'
            . ' Target="styles.xml"/>'
            . '</Relationships>';

        $contentTypesXml =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/word/document.xml"'
            . ' ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>'
            . '<Override PartName="/word/styles.xml"'
            . ' ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>'
            . $imgCTXml
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
        $zip->addFromString('word/styles.xml',              $stylesXml);
        $zip->addFromString('word/_rels/document.xml.rels', $rels);
        foreach ($mediaFiles as $zipEntry => $srcPath) {
            $zip->addFile($srcPath, 'word/media/' . $zipEntry);
        }
        $zip->close();

        return file_exists($outputPath) && filesize($outputPath) > 100;
    }

    /**
     * Build a Word styles.xml with real built-in styles.
     *
     * Includes: Normal, Heading1 (blue #2F5496 bold 18pt), Heading2 (blue bold 14pt),
     * Heading3 (dark bold 12pt), ListBullet (indented bullet), ListNumber (indented),
     * TableGrid (bordered table), Caption.
     */
    private function buildDocxStylesXml(): string
    {
        $ns = 'xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"';

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<w:styles ' . $ns . ' w:docDefaults="">'
            . '<w:docDefaults>'
            . '<w:rPrDefault><w:rPr>'
            . '<w:rFonts w:ascii="Calibri" w:hAnsi="Calibri" w:cs="Times New Roman"/>'
            . '<w:sz w:val="22"/><w:szCs w:val="22"/>'
            . '</w:rPr></w:rPrDefault>'
            . '<w:pPrDefault><w:pPr>'
            . '<w:spacing w:after="160" w:line="259" w:lineRule="auto"/>'
            . '</w:pPr></w:pPrDefault>'
            . '</w:docDefaults>'
            // Normal
            . '<w:style w:type="paragraph" w:default="1" w:styleId="Normal">'
            . '<w:name w:val="Normal"/>'
            . '<w:pPr><w:spacing w:after="160"/></w:pPr>'
            . '<w:rPr><w:rFonts w:ascii="Calibri" w:hAnsi="Calibri"/><w:sz w:val="22"/></w:rPr>'
            . '</w:style>'
            // Heading 1 — blue bold 18pt
            . '<w:style w:type="paragraph" w:styleId="Heading1">'
            . '<w:name w:val="heading 1"/>'
            . '<w:basedOn w:val="Normal"/>'
            . '<w:next w:val="Normal"/>'
            . '<w:pPr><w:spacing w:before="480" w:after="120"/><w:outlineLvl w:val="0"/></w:pPr>'
            . '<w:rPr><w:b/><w:color w:val="2F5496"/>'
            . '<w:sz w:val="36"/><w:szCs w:val="36"/>'
            . '<w:rFonts w:ascii="Calibri Light" w:hAnsi="Calibri Light"/></w:rPr>'
            . '</w:style>'
            // Heading 2 — blue bold 14pt
            . '<w:style w:type="paragraph" w:styleId="Heading2">'
            . '<w:name w:val="heading 2"/>'
            . '<w:basedOn w:val="Normal"/>'
            . '<w:next w:val="Normal"/>'
            . '<w:pPr><w:spacing w:before="360" w:after="80"/><w:outlineLvl w:val="1"/></w:pPr>'
            . '<w:rPr><w:b/><w:color w:val="2E74B5"/>'
            . '<w:sz w:val="28"/><w:szCs w:val="28"/>'
            . '<w:rFonts w:ascii="Calibri Light" w:hAnsi="Calibri Light"/></w:rPr>'
            . '</w:style>'
            // Heading 3 — dark blue bold 12pt
            . '<w:style w:type="paragraph" w:styleId="Heading3">'
            . '<w:name w:val="heading 3"/>'
            . '<w:basedOn w:val="Normal"/>'
            . '<w:next w:val="Normal"/>'
            . '<w:pPr><w:spacing w:before="240" w:after="60"/><w:outlineLvl w:val="2"/></w:pPr>'
            . '<w:rPr><w:b/><w:color w:val="1F3864"/>'
            . '<w:sz w:val="24"/><w:szCs w:val="24"/></w:rPr>'
            . '</w:style>'
            // List Bullet
            . '<w:style w:type="paragraph" w:styleId="ListBullet">'
            . '<w:name w:val="List Bullet"/>'
            . '<w:basedOn w:val="Normal"/>'
            . '<w:pPr>'
            . '<w:numPr><w:ilvl w:val="0"/><w:numId w:val="1"/></w:numPr>'
            . '<w:ind w:left="720" w:hanging="360"/>'
            . '</w:pPr>'
            . '</w:style>'
            // List Number
            . '<w:style w:type="paragraph" w:styleId="ListNumber">'
            . '<w:name w:val="List Number"/>'
            . '<w:basedOn w:val="Normal"/>'
            . '<w:pPr>'
            . '<w:numPr><w:ilvl w:val="0"/><w:numId w:val="2"/></w:numPr>'
            . '<w:ind w:left="720" w:hanging="360"/>'
            . '</w:pPr>'
            . '</w:style>'
            // Table Grid
            . '<w:style w:type="table" w:styleId="TableGrid">'
            . '<w:name w:val="Table Grid"/>'
            . '<w:tblPr>'
            . '<w:tblBorders>'
            . '<w:top w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
            . '<w:left w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
            . '<w:bottom w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
            . '<w:right w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
            . '<w:insideH w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
            . '<w:insideV w:val="single" w:sz="4" w:space="0" w:color="000000"/>'
            . '</w:tblBorders>'
            . '</w:tblPr>'
            . '</w:style>'
            . '</w:styles>';
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
            $tblXml  .= '<w:tr>';
            foreach ($cells as $cell) {
                $cellText = htmlspecialchars($cell, ENT_XML1);
                $runPr    = $isHeader ? '<w:rPr><w:b/><w:color w:val="FFFFFF"/></w:rPr>' : '';
                $shadePr  = $isHeader
                    ? '<w:tcPr><w:tcW w:w="0" w:type="auto"/><w:shd w:val="clear" w:color="auto" w:fill="2F75B6"/></w:tcPr>'
                    : '<w:tcPr><w:tcW w:w="0" w:type="auto"/></w:tcPr>';
                $tblXml  .= '<w:tc>'
                          . $shadePr
                          . '<w:p><w:r>' . $runPr
                          . '<w:t xml:space="preserve">' . $cellText . '</w:t>'
                          . '</w:r></w:p>'
                          . '</w:tc>';
            }
            $tblXml .= '</w:tr>';
        }

        $tblXml .= '</w:tbl><w:p/>';
        return $tblXml;
    }

    /**
     * Build an ODT file from structured Markdown-like text.
     *
     * Supports headings (#/##/###), paragraphs, pipe tables, bullet/numbered lists.
     * The source image is embedded on page 1 so the original visual is preserved;
     * extracted text follows.  Page dimensions are set from $pageSize.
     *
     * @param array $pageSize  Output of detectPageSizeFromImage()
     */
    private function writeOdtFromText(
        string $text,
        string $outputPath,
        string $imagePath = '',
        string $inputFormat = '',
        array  $pageSize = []
    ): bool {
        if (!class_exists('ZipArchive')) {
            return false;
        }

        if (empty($pageSize)) {
            $pageSize = ['cm_w' => 21.0, 'cm_h' => 29.7, 'name' => 'A4', 'landscape' => false];
        }
        $pgW    = number_format((float) $pageSize['cm_w'], 3);
        $pgH    = number_format((float) $pageSize['cm_h'], 3);
        $margin = '2.000';  // 2 cm margins

        $bodyXml         = '';
        $mediaFiles      = [];
        $manifestEntries = '';

        // ── Embed source image on page 1 (preserves original visual design) ─
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
            $maxCmW  = (float) $pageSize['cm_w'] - 4.0; // content width
            $cmPerPx = 2.54 / 96.0;
            $cmW     = number_format(min($maxCmW, $pxW * $cmPerPx), 3);
            $cmH     = number_format((float) $cmW * $pxH / $pxW, 3);

            $bodyXml .= '<text:p>'
                     . '<draw:frame draw:style-name="fr1" draw:name="SourceImage"'
                     . ' svg:width="' . $cmW . 'cm" svg:height="' . $cmH . 'cm"'
                     . ' text:anchor-type="paragraph">'
                     . '<draw:image xlink:href="' . $media . '" xlink:type="simple"'
                     . ' xlink:show="embed" xlink:actuate="onLoad"/>'
                     . '</draw:frame></text:p>'
                     . '<text:p><text:soft-page-break/></text:p>';  // page break before text

            $mediaFiles[$media]  = $imagePath;
            $manifestEntries    .= '<manifest:file-entry manifest:full-path="' . $media
                                 . '" manifest:media-type="' . $mime . '"/>';
        }

        // ── Parse Markdown text into ODF body XML ─────────────────────────
        $lines = preg_split('/\r\n|\r|\n/', $text) ?: [];
        $i = 0;
        while ($i < count($lines)) {
            $line = $lines[$i];

            if (str_starts_with(trim($line), '|')) {
                $tableLines = [];
                while ($i < count($lines) && str_starts_with(trim($lines[$i]), '|')) {
                    $tableLines[] = $lines[$i];
                    $i++;
                }
                $bodyXml .= $this->markdownTableToOdtXml($tableLines);
                continue;
            }

            if (preg_match('/^(#{1,3})\s+(.+)$/', $line, $m)) {
                $lvl      = strlen($m[1]);
                // ODF built-in style names use "Heading N" (not "_20_" which is URL encoding)
                $bodyXml .= '<text:h text:style-name="Heading ' . $lvl . '" text:outline-level="' . $lvl . '">'
                          . htmlspecialchars($m[2], ENT_XML1) . '</text:h>';
                $i++;
                continue;
            }

            if (preg_match('/^[-*]\s+(.+)$/', $line, $m)) {
                $bodyXml .= '<text:list text:style-name="List Bullet"><text:list-item><text:p>'
                          . htmlspecialchars($m[1], ENT_XML1) . '</text:p></text:list-item></text:list>';
                $i++;
                continue;
            }

            if (preg_match('/^\d+[.)]\s+(.+)$/', $line, $m)) {
                $bodyXml .= '<text:list text:style-name="List Number"><text:list-item><text:p>'
                          . htmlspecialchars($m[1], ENT_XML1) . '</text:p></text:list-item></text:list>';
                $i++;
                continue;
            }

            if (trim($line) === '') {
                $bodyXml .= '<text:p/>';
                $i++;
                continue;
            }

            $safe = htmlspecialchars($line, ENT_XML1);
            $safe = preg_replace('/\*\*(.+?)\*\*/', '<text:span text:style-name="Strong_20_Emphasis">$1</text:span>', $safe);
            $safe = preg_replace('/\*(.+?)\*/',     '<text:span text:style-name="Emphasis">$1</text:span>',          $safe);
            $bodyXml .= '<text:p>' . $safe . '</text:p>';
            $i++;
        }

        // ── Build ODT ZIP ─────────────────────────────────────────────────
        $manifest =
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0" manifest:version="1.3">'
            . '<manifest:file-entry manifest:full-path="/" manifest:version="1.3" manifest:media-type="application/vnd.oasis.opendocument.text"/>'
            . '<manifest:file-entry manifest:full-path="content.xml" manifest:media-type="text/xml"/>'
            . '<manifest:file-entry manifest:full-path="styles.xml" manifest:media-type="text/xml"/>'
            . $manifestEntries
            . '</manifest:manifest>';

        // styles.xml — page layout with correct dimensions
        $landscape   = !empty($pageSize['landscape']) ? ' style:print-orientation="landscape"' : '';
        $stylesXml   =
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<office:document-styles'
            . ' xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"'
            . ' xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0"'
            . ' xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0"'
            . ' office:version="1.3">'
            . '<office:styles>'
            // Heading 1 style
            . '<style:style style:name="Heading 1" style:family="paragraph" style:class="text">'
            . '<style:paragraph-properties fo:margin-top="0.494cm" fo:margin-bottom="0.247cm"/>'
            . '<style:text-properties fo:font-size="18pt" fo:font-weight="bold" fo:color="#2F5496"'
            . ' style:font-name="Calibri Light"/>'
            . '</style:style>'
            // Heading 2
            . '<style:style style:name="Heading 2" style:family="paragraph" style:class="text">'
            . '<style:paragraph-properties fo:margin-top="0.353cm" fo:margin-bottom="0.176cm"/>'
            . '<style:text-properties fo:font-size="14pt" fo:font-weight="bold" fo:color="#2E74B5"'
            . ' style:font-name="Calibri Light"/>'
            . '</style:style>'
            // Heading 3
            . '<style:style style:name="Heading 3" style:family="paragraph" style:class="text">'
            . '<style:paragraph-properties fo:margin-top="0.247cm" fo:margin-bottom="0.118cm"/>'
            . '<style:text-properties fo:font-size="12pt" fo:font-weight="bold" fo:color="#1F3864"/>'
            . '</style:style>'
            . '</office:styles>'
            . '<office:automatic-styles>'
            . '<style:page-layout style:name="PageLayout">'
            . '<style:page-layout-properties'
            . ' fo:page-width="' . $pgW . 'cm"'
            . ' fo:page-height="' . $pgH . 'cm"'
            . $landscape
            . ' fo:margin-top="' . $margin . 'cm"'
            . ' fo:margin-bottom="' . $margin . 'cm"'
            . ' fo:margin-left="' . $margin . 'cm"'
            . ' fo:margin-right="' . $margin . 'cm"/>'
            . '</style:page-layout>'
            . '</office:automatic-styles>'
            . '<office:master-styles>'
            . '<style:master-page style:name="Standard" style:page-layout-name="PageLayout"/>'
            . '</office:master-styles>'
            . '</office:document-styles>';

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
        $zip->addFromString('styles.xml',            $stylesXml);
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
        $xml = '<table:table table:name="Table1">';

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
     * Supports headings (#/##/###), bullet/numbered lists, pipe tables,
     * bold/italic inline formatting, and paragraph breaks.
     * Page size is set via RTF \paperw / \paperh control words (in twips).
     *
     * @param array $pageSize  Output of detectPageSizeFromImage()
     */
    private function writeRtfFromText(string $text, string $outputPath, array $pageSize = []): bool
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
             . '{\\colortbl;\\red47\\green84\\blue150;\\red0\\green0\\blue0;}' . "\n"
             . '\\deflang1033\\widowctrl\\hyphauto'
             // Page size in twips (default A4: 11906 × 16838)
             . '\\paperw' . (empty($pageSize) ? 11906 : (int) $pageSize['twip_w'])
             . '\\paperh' . (empty($pageSize) ? 16838 : (int) $pageSize['twip_h'])
             . '\\margl1440\\margr1440\\margt1440\\margb1440' . "\n"
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
        string $inputFormat = '',
        array  $pageSize = []
    ): bool {
        $tmpBase = tempnam(sys_get_temp_dir(), 'cx_doc_');
        @unlink($tmpBase);
        $tmpDocx = $tmpBase . '.docx';
        try {
            if (!$this->writeDocxFromText($text, $tmpDocx, $imagePath, $inputFormat, $pageSize)) {
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
            $aiTable = $this->aiService->ocrTable($inputPath, $this->planTier);
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
            $aiOcr = $this->aiService->ocr($inputPath, $this->planTier);
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
     * Core algorithm:
     *   1. Pre-process the image (grayscale + contrast normalization) so Tesseract
     *      can read white text on coloured cell backgrounds (e.g., blue headers).
     *   2. Collect every WORD-level bounding box (TSV level = 5) — this gives us
     *      the spatial coordinates of each recognised token.
     *   3. Cluster words into visual rows by grouping tokens whose vertical centres
     *      are within one average character-height of each other.
     *   4. Detect global column boundaries via LEFT-EDGE gap analysis:
     *      sort all word left-edges, then find positions where adjacent left-edges
     *      are separated by more than the median word width.  This correctly handles
     *      empty cells (they leave no words, but the next occupied column still
     *      starts at the right position).
     *   5. Assign words to their column by comparing the word's left edge against
     *      the midpoints between adjacent column starts.
     *
     * @return array<int, array<int, string>>
     */
    private function extractRowsFromTesseractTsv(string $inputPath): array
    {
        $tess = trim((string) shell_exec('which tesseract 2>/dev/null'));
        if (!$tess) {
            return [];
        }

        // ── 1. Pre-process the image for better OCR accuracy ─────────────
        //
        // Tables with coloured headers (e.g. white text on blue background) are
        // misread by Tesseract unless we first convert to high-contrast grayscale.
        $processedPath = $this->preprocessImageForOcr($inputPath);
        $ocrInput      = $processedPath ?? $inputPath;

        $tmpBase = sys_get_temp_dir() . '/cx_tess_' . bin2hex(random_bytes(12));
        exec(
            escapeshellarg($tess) . ' ' . escapeshellarg($ocrInput)
            . ' ' . escapeshellarg($tmpBase) . ' --psm 6 tsv -l eng 2>/dev/null',
            $_out, $code
        );
        if ($processedPath !== null) {
            @unlink($processedPath);
        }

        $tsvFile = $tmpBase . '.tsv';
        if ($code !== 0 || !file_exists($tsvFile)) {
            return [];
        }

        $tsv = (string) file_get_contents($tsvFile);
        @unlink($tsvFile);

        // ── 2. Collect WORD-level bounding boxes (level = 5) ─────────────
        $words = [];
        foreach (explode("\n", $tsv) as $i => $rawLine) {
            if ($i === 0 || trim($rawLine) === '') {
                continue;
            }
            $cols = explode("\t", $rawLine);
            if (count($cols) < 12) {
                continue;
            }
            if ((int) $cols[0] !== 5) {
                continue;   // only word-level rows
            }
            $conf = (int) $cols[10];
            $word = trim($cols[11]);
            if ($conf === -1 || $word === '') {
                continue;
            }
            // Skip very low-confidence short tokens (OCR noise from UI borders)
            if ($conf < 15 && mb_strlen($word) <= 2) {
                continue;
            }

            $left   = (int) $cols[6];
            $top    = (int) $cols[7];
            $width  = (int) $cols[8];
            $height = (int) $cols[9];

            $words[] = [
                'left'     => $left,
                'top'      => $top,
                'right'    => $left + $width,
                'bottom'   => $top + $height,
                'center_y' => $top + (int) ($height / 2),
                'width'    => $width,
                'height'   => $height,
                'text'     => $word,
            ];
        }

        if (empty($words)) {
            return [];
        }

        // ── 3. Cluster words into visual rows by vertical-centre position ─
        usort($words, fn($a, $b) => $a['center_y'] <=> $b['center_y']);

        $allH    = array_column($words, 'height');
        sort($allH);
        $medH    = $allH[(int) ((count($allH) - 1) / 2)] ?? 20;
        $rowTol  = max(4, (int) ($medH * 0.45));

        $visualRows = [];
        $curRow     = [$words[0]];
        $rowCenter  = $words[0]['center_y'];

        for ($i = 1, $n = count($words); $i < $n; $i++) {
            if (abs($words[$i]['center_y'] - $rowCenter) <= $rowTol) {
                $curRow[] = $words[$i];
            } else {
                $visualRows[] = $curRow;
                $curRow       = [$words[$i]];
                $rowCenter    = $words[$i]['center_y'];
            }
        }
        $visualRows[] = $curRow;

        // Sort words left→right within each visual row
        foreach ($visualRows as &$vRow) {
            usort($vRow, fn($a, $b) => $a['left'] <=> $b['left']);
        }
        unset($vRow);

        // ── 4. Merge split currency/number tokens ─────────────────────────
        //
        // Tesseract often splits "$16,753.00" into ["$16,", "753.00"] when
        // kerning is tight.  Re-join adjacent tokens that form a valid pattern.
        foreach ($visualRows as &$vRow) {
            $merged = [];
            for ($j = 0, $len = count($vRow); $j < $len; $j++) {
                $cur  = $vRow[$j];
                $next = $vRow[$j + 1] ?? null;
                if (
                    $next !== null
                    && preg_match('/^[£$€¥₹]?\d[\d,]*,$/', $cur['text'])
                    && preg_match('/^\d{3}(\.\d+)?$/', $next['text'])
                    && ($next['left'] - $cur['right']) < max(8, (int) ($cur['width'] * 0.5))
                ) {
                    $cur['text']  = $cur['text'] . $next['text'];
                    $cur['right'] = $next['right'];
                    $j++;
                }
                $merged[] = $cur;
            }
            $vRow = $merged;
        }
        unset($vRow);

        // ── 5. Detect column boundaries via LEFT-EDGE gap analysis ────────
        //
        // Key insight: words in the same column tend to have similar LEFT edges.
        // By sorting all left-edge positions and finding gaps larger than the
        // median word width, we reliably identify where each new column begins —
        // even when some cells in that column are empty (they leave no words but
        // do not shift the next column's left-edge position).
        $allLefts  = [];
        $allWidths = [];
        foreach ($visualRows as $vRow) {
            foreach ($vRow as $w) {
                $allLefts[]  = $w['left'];
                $allWidths[] = $w['width'];
            }
        }
        sort($allLefts);
        sort($allWidths);

        $medW       = $allWidths[(int) ((count($allWidths) - 1) / 2)] ?? 30;
        $colGapMin  = max(12, (int) ($medW * 0.45));   // minimum gap between columns

        // Find groups of left-edges (each group = one column)
        $colStarts = [];
        $group     = $allLefts[0];
        for ($i = 1, $n = count($allLefts); $i < $n; $i++) {
            if ($allLefts[$i] - $allLefts[$i - 1] > $colGapMin) {
                $colStarts[] = (int) round($group);     // representative for this column
                $group       = $allLefts[$i];
            } else {
                // Keep the smallest left-edge as the column representative
                $group = min($group, $allLefts[$i]);
            }
        }
        $colStarts[] = (int) round($group);
        sort($colStarts);

        // Merge column starts that are suspiciously close (< colGapMin / 2)
        $mergeMin = max(6, (int) ($colGapMin / 2));
        $merged   = [];
        $prev     = null;
        foreach ($colStarts as $cs) {
            if ($prev === null || $cs - $prev > $mergeMin) {
                $merged[] = $cs;
                $prev     = $cs;
            }
        }
        $colStarts = $merged;

        if (count($colStarts) <= 1) {
            // Only one column detected — return space-joined plain text per row
            return array_map(
                fn($vRow) => [implode(' ', array_column($vRow, 'text'))],
                $visualRows
            );
        }

        // Compute boundary midpoints between adjacent column starts.
        // A word belongs to column $i if its left-edge is < $boundaries[$i].
        $boundaries = [];
        for ($i = 0, $n = count($colStarts) - 1; $i < $n; $i++) {
            $boundaries[] = (int) (($colStarts[$i] + $colStarts[$i + 1]) / 2);
        }

        // ── 6. Assign words to columns and build output rows ──────────────
        $rows    = [];
        $numCols = count($colStarts);

        foreach ($visualRows as $vRow) {
            $cells = array_fill(0, $numCols, '');
            foreach ($vRow as $w) {
                $colIdx = $numCols - 1;   // default: last column
                foreach ($boundaries as $bi => $bound) {
                    if ($w['left'] < $bound) {
                        $colIdx = $bi;
                        break;
                    }
                }
                $cells[$colIdx] = ($cells[$colIdx] !== '' ? $cells[$colIdx] . ' ' : '')
                                 . $w['text'];
            }
            $rows[] = $cells;
        }

        return $rows;
    }

    /**
     * Pre-process an image to improve Tesseract OCR accuracy.
     *
     * Converts to grayscale and normalises the contrast so that:
     *   • white text on coloured backgrounds (e.g. blue Excel headers) becomes
     *     dark text on a light background
     *   • low-contrast scans are stretched to full black–white range
     *
     * Uses ImageMagick when available; falls back to PHP's GD extension.
     * Returns the path of the pre-processed temp file, or null if neither
     * tool is available.  The caller is responsible for deleting the file.
     *
     * @param string $inputPath Absolute path to the source image
     * @return string|null      Temp PNG path, or null on failure
     */
    private function preprocessImageForOcr(string $inputPath): ?string
    {
        if (!file_exists($inputPath)) {
            return null;
        }

        $outPath = tempnam(sys_get_temp_dir(), 'cx_preocr_') . '.png';

        // ── ImageMagick (preferred) ───────────────────────────────────────
        $im = trim((string) shell_exec('which convert 2>/dev/null'))
           ?: trim((string) shell_exec('which magick 2>/dev/null'));
        if ($im) {
            exec(
                escapeshellarg($im)
                . ' ' . escapeshellarg($inputPath)
                . ' -colorspace Gray'      // convert to grayscale
                . ' -normalize'            // stretch histogram to full 0–255 range
                . ' -sharpen 0x0.5'        // mild sharpening for better letter edges
                . ' ' . escapeshellarg($outPath) . ' 2>/dev/null',
                $_o, $code
            );
            if ($code === 0 && file_exists($outPath) && filesize($outPath) > 100) {
                return $outPath;
            }
            @unlink($outPath);
        }

        // ── PHP GD fallback ───────────────────────────────────────────────
        if (extension_loaded('gd')) {
            $ext = strtolower(pathinfo($inputPath, PATHINFO_EXTENSION));
            $src = match ($ext) {
                'jpg', 'jpeg' => @imagecreatefromjpeg($inputPath),
                'png'         => @imagecreatefrompng($inputPath),
                'gif'         => @imagecreatefromgif($inputPath),
                'bmp'         => function_exists('imagecreatefrombmp')
                                  ? @imagecreatefrombmp($inputPath) : null,
                'webp'        => function_exists('imagecreatefromwebp')
                                  ? @imagecreatefromwebp($inputPath) : null,
                default       => null,
            };
            if ($src !== null && $src !== false) {
                // Grayscale + contrast boost
                imagefilter($src, IMG_FILTER_GRAYSCALE);
                imagefilter($src, IMG_FILTER_CONTRAST, -20);  // negative = more contrast

                if (@imagepng($src, $outPath) !== false
                    && file_exists($outPath) && filesize($outPath) > 100
                ) {
                    imagedestroy($src);
                    return $outPath;
                }
                imagedestroy($src);
            }
        }

        @unlink($outPath);
        return null;
    }

    /**
     * Run Tesseract in plain text mode and split each non-empty line into a
     * single-cell row.  Used as a fallback when TSV mode fails or returns nothing.
     *
     * Also applies preprocessImageForOcr() for the same accuracy improvements as
     * the TSV path.
     *
     * @return array<int, array<int, string>>
     */
    private function extractRowsFromTesseractPlain(string $inputPath): array
    {
        $tess = trim((string) shell_exec('which tesseract 2>/dev/null'));
        if (!$tess) {
            return [];
        }

        $processedPath = $this->preprocessImageForOcr($inputPath);
        $ocrInput      = $processedPath ?? $inputPath;

        $tmpBase = sys_get_temp_dir() . '/cx_tess_' . bin2hex(random_bytes(12));
        exec(
            escapeshellarg($tess) . ' ' . escapeshellarg($ocrInput)
            . ' ' . escapeshellarg($tmpBase) . ' --psm 6 -l eng 2>/dev/null',
            $_out, $code
        );
        if ($processedPath !== null) {
            @unlink($processedPath);
        }

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
     * Write a professional XLSX spreadsheet (OOXML) from a 2-D array of rows.
     *
     * Output features:
     *  - Row 1 styled as a bold white-text header with a blue fill and borders
     *  - Rows 2+ styled with thin borders on all cells
     *  - First row is frozen (freeze pane at A2)
     *  - Column widths are auto-sized based on the longest cell in each column
     *  - Numbers and currency are stored as strings (preserving exact formatting)
     */
    private function writeXlsxFromRows(array $rows, string $outputPath): bool
    {
        if (!class_exists('ZipArchive')) {
            return false;
        }

        // ── Calculate column widths from content ─────────────────────────────
        $colWidths = [];
        foreach ($rows as $cells) {
            foreach ((array) $cells as $c => $cell) {
                $len = mb_strlen((string) $cell, 'UTF-8');
                if (!isset($colWidths[$c]) || $len > $colWidths[$c]) {
                    $colWidths[$c] = $len;
                }
            }
        }

        // ── Build <cols> element for auto column widths ───────────────────────
        $colsXml = '<cols>';
        foreach ($colWidths as $c => $maxLen) {
            $width     = max(8.5, min(60.0, $maxLen * 1.15 + 2));
            $colNum    = $c + 1;
            $colsXml  .= "<col min=\"{$colNum}\" max=\"{$colNum}\" width=\"{$width}\" customWidth=\"1\"/>";
        }
        $colsXml .= '</cols>';

        // ── Build worksheet data rows ─────────────────────────────────────────
        $sheetData = '';
        foreach ($rows as $rowIdx => $cells) {
            $rowNum    = $rowIdx + 1;
            $isHeader  = ($rowIdx === 0);
            $styleIdx  = $isHeader ? '1' : '2'; // 1=header style, 2=data style
            $sheetData .= "<row r=\"{$rowNum}\">";
            foreach ((array) $cells as $colIdx => $cell) {
                if ($colIdx < 26) {
                    $colLetter = chr(65 + $colIdx);
                } elseif ($colIdx < 702) {
                    $colLetter = chr(64 + intdiv($colIdx, 26)) . chr(65 + ($colIdx % 26));
                } else {
                    break;
                }
                $ref     = $colLetter . $rowNum;
                $safe    = (string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string) $cell);
                $escaped = htmlspecialchars($safe, ENT_QUOTES | ENT_XML1, 'UTF-8');
                $sheetData .= "<c r=\"{$ref}\" t=\"inlineStr\" s=\"{$styleIdx}\"><is><t xml:space=\"preserve\">{$escaped}</t></is></c>";
            }
            $sheetData .= '</row>';
        }

        // ── Styles: 3 fonts, 3 fills, 2 borders, 3 cellXfs ──────────────────
        //
        //  Font 0: Calibri 11 (normal)
        //  Font 1: Calibri 11 bold white (header text)
        //  Fill 0: none (required by spec)
        //  Fill 1: gray125 (required by spec)
        //  Fill 2: solid #2F75B6 (header background — Excel "Blue, Accent 1")
        //  Border 0: none
        //  Border 1: thin black on all sides
        //  xf 0: normal (no style)
        //  xf 1: bold white + blue fill + borders (header row)
        //  xf 2: no fill + borders (data rows)
        $styles =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:x14ac="http://schemas.microsoft.com/office/spreadsheetml/2009/9/ac">'
            . '<fonts count="2">'
            .   '<font><sz val="11"/><name val="Calibri"/></font>'
            .   '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="3">'
            .   '<fill><patternFill patternType="none"/></fill>'
            .   '<fill><patternFill patternType="gray125"/></fill>'
            .   '<fill><patternFill patternType="solid"><fgColor rgb="FF2F75B6"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="2">'
            .   '<border><left/><right/><top/><bottom/><diagonal/></border>'
            .   '<border>'
            .     '<left style="thin"><color rgb="FF000000"/></left>'
            .     '<right style="thin"><color rgb="FF000000"/></right>'
            .     '<top style="thin"><color rgb="FF000000"/></top>'
            .     '<bottom style="thin"><color rgb="FF000000"/></bottom>'
            .     '<diagonal/>'
            .   '</border>'
            . '</borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="3">'
            .   '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
            .   '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1">'
            .     '<alignment wrapText="1" horizontal="center" vertical="center"/>'
            .   '</xf>'
            .   '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyBorder="1" applyAlignment="1">'
            .     '<alignment vertical="center"/>'
            .   '</xf>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';

        // ── Freeze pane at A2 (row 1 frozen as header) ───────────────────────
        $sheetViewXml =
            '<sheetViews>'
            . '<sheetView tabSelected="1" workbookViewId="0">'
            . '<pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/>'
            . '<selection pane="bottomLeft" activeCell="A2" sqref="A2"/>'
            . '</sheetView>'
            . '</sheetViews>';

        // ── Assemble worksheet XML ────────────────────────────────────────────
        $sheet =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . $sheetViewXml
            . $colsXml
            . '<sheetData>' . $sheetData . '</sheetData>'
            . '</worksheet>';

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
            . '<bookViews><workbookView xWindow="480" yWindow="60" windowWidth="18195" windowHeight="8505"/></bookViews>'
            . '<sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';

        $workbookRels =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';

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
     * Write a professional ODS spreadsheet (ODF Calc) from a 2-D array of rows.
     *
     * Output features:
     *  - Row 1 styled as a bold header with a blue background and white text
     *  - Rows 2+ have a thin border on all cells
     *  - Column widths are auto-sized
     */
    private function writeOdsCalcFromRows(array $rows, string $outputPath): bool
    {
        if (!class_exists('ZipArchive')) {
            return false;
        }

        // ── Calculate approximate column widths ──────────────────────────────
        $colWidths = [];
        foreach ($rows as $cells) {
            foreach ((array) $cells as $c => $cell) {
                $len = mb_strlen((string) $cell, 'UTF-8');
                if (!isset($colWidths[$c]) || $len > $colWidths[$c]) {
                    $colWidths[$c] = $len;
                }
            }
        }

        // ── Build <table:table-column> elements ───────────────────────────────
        $colDefs = '';
        foreach ($colWidths as $c => $maxLen) {
            $cm       = min(8.0, max(2.0, $maxLen * 0.22 + 0.5));
            $colDefs .= sprintf(
                '<table:table-column table:style-name="co%d" table:default-cell-style-name="Default"/>',
                $c
            );
        }

        // ── Build table rows ─────────────────────────────────────────────────
        $tableRows = '';
        foreach ($rows as $rowIdx => $cells) {
            $isHeader   = ($rowIdx === 0);
            $rowStyle   = $isHeader ? 'rh' : 'rn';
            $tableRows .= "<table:table-row table:style-name=\"{$rowStyle}\">";
            foreach ((array) $cells as $cell) {
                $escaped    = htmlspecialchars((string) $cell, ENT_QUOTES | ENT_XML1, 'UTF-8');
                $cellStyle  = $isHeader ? 'hdCell' : 'dtCell';
                $tableRows .= "<table:table-cell table:style-name=\"{$cellStyle}\" office:value-type=\"string\">"
                            . "<text:p>{$escaped}</text:p>"
                            . '</table:table-cell>';
            }
            $tableRows .= '</table:table-row>';
        }

        // ── Column width style snippets ───────────────────────────────────────
        $colStyles = '';
        foreach ($colWidths as $c => $maxLen) {
            $cm         = min(8.0, max(2.0, $maxLen * 0.22 + 0.5));
            $colStyles .= '<style:style style:name="co' . $c . '" style:family="table-column">'
                        . '<style:table-column-properties fo:break-before="auto"'
                        . ' style:column-width="' . number_format($cm, 3) . 'cm"/>'
                        . '</style:style>';
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
            . ' xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0"'
            . ' xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0"'
            . ' office:version="1.3">'
            // ── Automatic styles (column widths + cell styles) ────────────────
            . '<office:automatic-styles>'
            . $colStyles
            // Header row height
            . '<style:style style:name="rh" style:family="table-row">'
            . '<style:table-row-properties style:row-height="0.8cm" style:use-optimal-row-height="false"/>'
            . '</style:style>'
            // Normal row height
            . '<style:style style:name="rn" style:family="table-row">'
            . '<style:table-row-properties style:row-height="0.6cm" style:use-optimal-row-height="false"/>'
            . '</style:style>'
            // Header cell: bold, white text, blue background, all borders
            . '<style:style style:name="hdCell" style:family="table-cell">'
            . '<style:table-cell-properties'
            . ' fo:border="0.053cm solid #000000"'
            . ' fo:background-color="#2F75B6"'
            . ' style:text-align-source="fix"'
            . ' fo:text-align="center"'
            . ' style:vertical-align="middle"'
            . ' fo:padding="0.1cm"/>'
            . '<style:text-properties fo:font-weight="bold" fo:color="#FFFFFF"'
            . ' fo:font-size="11pt" style:font-name="Calibri"/>'
            . '</style:style>'
            // Data cell: normal text, all borders
            . '<style:style style:name="dtCell" style:family="table-cell">'
            . '<style:table-cell-properties'
            . ' fo:border="0.053cm solid #000000"'
            . ' fo:padding="0.08cm"/>'
            . '<style:text-properties fo:font-size="11pt" style:font-name="Calibri"/>'
            . '</style:style>'
            . '</office:automatic-styles>'
            . '<office:body><office:spreadsheet>'
            . '<table:table table:name="Sheet1">'
            . $colDefs
            . $tableRows
            . '</table:table>'
            . '</office:spreadsheet></office:body>'
            . '</office:document-content>';

        $zip = new \ZipArchive();
        if ($zip->open($outputPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return false;
        }
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
     * Build a PPTX from AI-extracted Markdown text.
     *
     * Creates a professional slide deck where:
     *   - Slide 1: The source image filling the slide (preserves original visual)
     *   - Slide 2+: Text content extracted by AI (headings become title text,
     *     paragraphs/bullets become content text blocks)
     *
     * @param string $text         Markdown text from AI OCR
     * @param string $imagePath    Source image to embed on slide 1
     * @param string $inputFormat  Source image format (png/jpg/etc.)
     * @param string $outputPath   Destination .pptx path
     * @param int    $pxW          Source image width  (for aspect ratio)
     * @param int    $pxH          Source image height (for aspect ratio)
     */
    private function writePptxFromText(
        string $text,
        string $imagePath,
        string $inputFormat,
        string $outputPath,
        int    $pxW = 1280,
        int    $pxH = 720
    ): bool {
        if (!class_exists('ZipArchive')) {
            return false;
        }

        // Slide dimensions: widescreen 13.33" × 7.5" in EMUs (1 in = 914400 EMU)
        $slideW = 9144000;
        $slideH = 5143500;

        $mimeMap = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
                    'gif' => 'image/gif',  'webp' => 'image/webp', 'bmp' => 'image/bmp',
                    'tiff' => 'image/tiff'];
        $imgExt  = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION)) ?: strtolower($inputFormat);
        $mime    = $mimeMap[$imgExt] ?? 'image/png';

        // Scale image to fill slide while preserving aspect ratio
        $srcAspect = $pxH > 0 ? ($pxW / $pxH) : (16 / 9);
        $sldAspect = $slideW / $slideH;
        if ($srcAspect > $sldAspect) {
            $imgEmuW = $slideW;
            $imgEmuH = (int) ($slideW / $srcAspect);
        } else {
            $imgEmuH = $slideH;
            $imgEmuW = (int) ($imgEmuH * $srcAspect);
        }
        $imgOffX = (int) (($slideW - $imgEmuW) / 2);
        $imgOffY = (int) (($slideH - $imgEmuH) / 2);

        // ── Slide 1: source image ─────────────────────────────────────────
        $slide1 =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sld xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"'
            . ' xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<p:cSld><p:spTree>'
            . '<p:sp><p:nvSpPr><p:cNvPr id="1" name="bg"/><p:cNvSpPr><a:spLocks/></p:cNvSpPr>'
            . '<p:nvPr/></p:nvSpPr>'
            . '<p:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $slideW . '" cy="' . $slideH . '"/></a:xfrm>'
            . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom>'
            . '<a:solidFill><a:srgbClr val="000000"/></a:solidFill></p:spPr></p:sp>'
            . '<p:pic>'
            . '<p:nvPicPr><p:cNvPr id="2" name="img"/><p:cNvPicPr/><p:nvPr/></p:nvPicPr>'
            . '<p:blipFill><a:blip r:embed="rId1"/><a:stretch><a:fillRect/></a:stretch></p:blipFill>'
            . '<p:spPr><a:xfrm><a:off x="' . $imgOffX . '" y="' . $imgOffY . '"/>'
            . '<a:ext cx="' . $imgEmuW . '" cy="' . $imgEmuH . '"/></a:xfrm>'
            . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom></p:spPr>'
            . '</p:pic>'
            . '</p:spTree></p:cSld>'
            . '</p:sld>';

        $slide1Rels =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1"'
            . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image"'
            . ' Target="../media/image1.' . $imgExt . '"/>'
            . '</Relationships>';

        // ── Slide 2+: text content ────────────────────────────────────────
        $extraSlides     = [];
        $extraSlideRels  = [];
        $lines           = preg_split('/\r\n|\r|\n/', trim($text)) ?: [];
        $currentSlide    = ['title' => '', 'body' => ''];
        $slideBodyLines  = [];
        $slideTitle      = '';

        foreach ($lines as $line) {
            if (preg_match('/^#\s+(.+)$/', $line, $m)) {
                // New slide on Heading 1
                if ($slideTitle !== '' || !empty($slideBodyLines)) {
                    $extraSlides[] = $this->buildPptxTextSlide($slideTitle, $slideBodyLines, $slideW, $slideH);
                }
                $slideTitle     = $m[1];
                $slideBodyLines = [];
            } elseif (preg_match('/^#{2,3}\s+(.+)$/', $line, $m)) {
                $slideBodyLines[] = ['type' => 'sub', 'text' => $m[1]];
            } elseif (preg_match('/^[-*]\s+(.+)$/', $line, $m)) {
                $slideBodyLines[] = ['type' => 'bullet', 'text' => '• ' . $m[1]];
            } elseif (trim($line) !== '') {
                $slideBodyLines[] = ['type' => 'para', 'text' => $line];
            }
        }
        if ($slideTitle !== '' || !empty($slideBodyLines)) {
            $extraSlides[] = $this->buildPptxTextSlide($slideTitle, $slideBodyLines, $slideW, $slideH);
        }
        // If no headings, put all text on one slide
        if (empty($extraSlides) && !empty($slideBodyLines)) {
            $extraSlides[] = $this->buildPptxTextSlide('Extracted Content', $slideBodyLines, $slideW, $slideH);
        }

        $totalSlides = 1 + count($extraSlides);

        // ── Build presentation.xml slide references ───────────────────────
        $slideIdList = '<p:sldIdLst>';
        for ($s = 1; $s <= $totalSlides; $s++) {
            $slideIdList .= '<p:sldId id="' . (255 + $s) . '" r:id="rId' . ($s + 1) . '"/>';
        }
        $slideIdList .= '</p:sldIdLst>';

        $presentation =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:presentation xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"'
            . ' xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<p:sldMasterIdLst><p:sldMasterId id="2147483648" r:id="rId1"/></p:sldMasterIdLst>'
            . $slideIdList
            . '<p:sldSz cx="' . $slideW . '" cy="' . $slideH . '" type="screen16x9"/>'
            . '<p:notesSz cx="6858000" cy="9144000"/>'
            . '</p:presentation>';

        $presRels =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1"'
            . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideMaster"'
            . ' Target="slideMasters/slideMaster1.xml"/>';
        for ($s = 1; $s <= $totalSlides; $s++) {
            $presRels .= '<Relationship Id="rId' . ($s + 1) . '"'
                      . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/slide"'
                      . ' Target="slides/slide' . $s . '.xml"/>';
        }
        $presRels .= '</Relationships>';

        // ── Minimal slide master ──────────────────────────────────────────
        $slideMaster =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<p:sldMaster xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"'
            . ' xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<p:cSld><p:bg><p:bgPr>'
            . '<a:solidFill><a:srgbClr val="FFFFFF"/></a:solidFill>'
            . '</p:bgPr></p:bg><p:spTree>'
            . '<p:sp><p:nvSpPr><p:cNvPr id="1" name="title"/><p:cNvSpPr><a:spLocks/></p:cNvSpPr>'
            . '<p:nvPr><p:ph type="title"/></p:nvPr></p:nvSpPr>'
            . '<p:spPr/><p:txBody><a:bodyPr/><a:p/></p:txBody></p:sp>'
            . '</p:spTree></p:cSld>'
            . '<p:sldLayoutIdLst/>'
            . '</p:sldMaster>';

        $slideMasterRels =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"/>';

        // ── Content types ─────────────────────────────────────────────────
        $ctSlides = '';
        for ($s = 1; $s <= $totalSlides; $s++) {
            $ctSlides .= '<Override PartName="/ppt/slides/slide' . $s . '.xml"'
                      . ' ContentType="application/vnd.openxmlformats-officedocument.presentationml.slide+xml"/>';
        }

        $contentTypes =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/ppt/presentation.xml"'
            . ' ContentType="application/vnd.openxmlformats-officedocument.presentationml.presentation.main+xml"/>'
            . '<Override PartName="/ppt/slideMasters/slideMaster1.xml"'
            . ' ContentType="application/vnd.openxmlformats-officedocument.presentationml.slideMaster+xml"/>'
            . '<Override PartName="/ppt/media/image1.' . $imgExt . '" ContentType="' . $mime . '"/>'
            . $ctSlides
            . '</Types>';

        $relsMain =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1"'
            . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument"'
            . ' Target="ppt/presentation.xml"/>'
            . '</Relationships>';

        // ── Assemble ZIP ──────────────────────────────────────────────────
        $zip = new \ZipArchive();
        if ($zip->open($outputPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return false;
        }
        $zip->addFromString('[Content_Types].xml',                              $contentTypes);
        $zip->addFromString('_rels/.rels',                                      $relsMain);
        $zip->addFromString('ppt/presentation.xml',                             $presentation);
        $zip->addFromString('ppt/_rels/presentation.xml.rels',                  $presRels);
        $zip->addFromString('ppt/slideMasters/slideMaster1.xml',                $slideMaster);
        $zip->addFromString('ppt/slideMasters/_rels/slideMaster1.xml.rels',     $slideMasterRels);
        $zip->addFromString('ppt/slides/slide1.xml',                            $slide1);
        $zip->addFromString('ppt/slides/_rels/slide1.xml.rels',                 $slide1Rels);
        $zip->addFile($imagePath, 'ppt/media/image1.' . $imgExt);

        // Extra text slides
        $noImgRels =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"/>';
        foreach ($extraSlides as $idx => $slideXml) {
            $slideNum = $idx + 2;
            $zip->addFromString('ppt/slides/slide' . $slideNum . '.xml',              $slideXml);
            $zip->addFromString('ppt/slides/_rels/slide' . $slideNum . '.xml.rels',   $noImgRels);
        }
        $zip->close();

        return file_exists($outputPath) && filesize($outputPath) > 100;
    }

    /** Build a single PPTX text slide with a title and body lines. */
    private function buildPptxTextSlide(string $title, array $bodyLines, int $slideW, int $slideH): string
    {
        $ns = 'xmlns:p="http://schemas.openxmlformats.org/presentationml/2006/main"'
            . ' xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main"';

        // Title text box
        $titleXml = '';
        if ($title !== '') {
            $escaped   = htmlspecialchars($title, ENT_XML1);
            $titleXml  = '<p:sp><p:nvSpPr><p:cNvPr id="10" name="Title"/>'
                       . '<p:cNvSpPr><a:spLocks/></p:cNvSpPr><p:nvPr/></p:nvSpPr>'
                       . '<p:spPr><a:xfrm><a:off x="457200" y="274638"/>'
                       . '<a:ext cx="' . ($slideW - 914400) . '" cy="1143000"/></a:xfrm>'
                       . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom></p:spPr>'
                       . '<p:txBody><a:bodyPr/>'
                       . '<a:p><a:r>'
                       . '<a:rPr b="1" sz="2800" dirty="0"><a:solidFill><a:srgbClr val="2F5496"/></a:solidFill></a:rPr>'
                       . '<a:t>' . $escaped . '</a:t></a:r></a:p>'
                       . '</p:txBody></p:sp>';
        }

        // Body text box
        $bodyParas = '';
        foreach ($bodyLines as $line) {
            $escaped    = htmlspecialchars($line['text'] ?? '', ENT_XML1);
            $sz         = $line['type'] === 'sub' ? '2000' : '1800';
            $bold       = $line['type'] === 'sub' ? ' b="1"' : '';
            $bodyParas .= '<a:p><a:r><a:rPr sz="' . $sz . '"' . $bold . ' dirty="0"/>'
                        . '<a:t>' . $escaped . '</a:t></a:r></a:p>';
        }

        $bodyXml = '';
        if ($bodyParas !== '') {
            $bodyOffY = $title !== '' ? 1600000 : 457200;
            $bodyHt   = $slideH - $bodyOffY - 457200;
            $bodyXml  = '<p:sp><p:nvSpPr><p:cNvPr id="11" name="Body"/>'
                      . '<p:cNvSpPr><a:spLocks/></p:cNvSpPr><p:nvPr/></p:nvSpPr>'
                      . '<p:spPr><a:xfrm><a:off x="457200" y="' . $bodyOffY . '"/>'
                      . '<a:ext cx="' . ($slideW - 914400) . '" cy="' . $bodyHt . '"/></a:xfrm>'
                      . '<a:prstGeom prst="rect"><a:avLst/></a:prstGeom></p:spPr>'
                      . '<p:txBody><a:bodyPr wrap="square" autofit="spAutoFit"/>'
                      . $bodyParas
                      . '</p:txBody></p:sp>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
             . '<p:sld ' . $ns . '>'
             . '<p:cSld><p:bg><p:bgPr>'
             . '<a:solidFill><a:srgbClr val="FFFFFF"/></a:solidFill>'
             . '</p:bgPr></p:bg>'
             . '<p:spTree>'
             . $titleXml
             . $bodyXml
             . '</p:spTree></p:cSld>'
             . '</p:sld>';
    }

    /**
     * Build an ODP from AI-extracted Markdown text.
     *
     * Slide 1: source image; subsequent slides: extracted text.
     */
    private function writeOdpFromText(
        string $text,
        string $imagePath,
        string $inputFormat,
        string $outputPath,
        int    $pxW = 1280,
        int    $pxH = 720
    ): bool {
        // Convert PPTX first (better tooling) then rename, or build minimal ODP directly
        // For simplicity, build a minimal ODP zip that embeds the image and text
        if (!class_exists('ZipArchive')) {
            return false;
        }

        $mimeMap = ['jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png',
                    'gif' => 'image/gif',  'webp' => 'image/webp', 'bmp' => 'image/bmp'];
        $imgExt  = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION)) ?: strtolower($inputFormat);
        $mime    = $mimeMap[$imgExt] ?? 'image/png';
        $media   = 'Pictures/image1.' . $imgExt;

        // Slide size: 25.4cm × 14.29cm (16:9 widescreen)
        $slideW = '25.400cm';
        $slideH = '14.288cm';

        // Scale image to fill slide
        $srcAspect = $pxH > 0 ? ($pxW / $pxH) : (16 / 9);
        $sldAspect = 25.4 / 14.288;
        if ($srcAspect > $sldAspect) {
            $imgW = 25.4;
            $imgH = round(25.4 / $srcAspect, 3);
        } else {
            $imgH = 14.288;
            $imgW = round(14.288 * $srcAspect, 3);
        }
        $offX = round((25.4 - $imgW) / 2, 3);
        $offY = round((14.288 - $imgH) / 2, 3);

        // Build text content from AI OCR
        $textSlides = '';
        $lines = preg_split('/\r\n|\r|\n/', trim($text)) ?: [];
        $currentTitle = '';
        $currentBody  = '';

        $flushSlide = function() use (&$textSlides, &$currentTitle, &$currentBody, $slideW, $slideH) {
            if ($currentTitle === '' && $currentBody === '') return;
            $titleXml = $currentTitle !== ''
                ? '<draw:frame draw:name="title" presentation:style-name="Default-title"'
                . ' svg:x="1.270cm" svg:y="0.762cm" svg:width="22.860cm" svg:height="3.175cm"'
                . ' presentation:class="title">'
                . '<draw:text-box><text:p><text:span text:style-name="T1">'
                . htmlspecialchars($currentTitle, ENT_XML1) . '</text:span></text:p></draw:text-box>'
                . '</draw:frame>'
                : '';
            $bodyXml = $currentBody !== ''
                ? '<draw:frame draw:name="content" svg:x="1.270cm" svg:y="4.445cm"'
                . ' svg:width="22.860cm" svg:height="9.208cm">'
                . '<draw:text-box>' . $currentBody . '</draw:text-box>'
                . '</draw:frame>'
                : '';
            $textSlides .= '<draw:page draw:name="text" draw:master-page-name="Default">'
                        . $titleXml . $bodyXml . '</draw:page>';
            $currentTitle = '';
            $currentBody  = '';
        };

        foreach ($lines as $line) {
            if (preg_match('/^#\s+(.+)$/', $line, $m)) {
                $flushSlide();
                $currentTitle = $m[1];
            } elseif (preg_match('/^#{2,3}\s+(.+)$/', $line, $m)) {
                $currentBody .= '<text:p><text:span text:style-name="T2">'
                              . htmlspecialchars($m[1], ENT_XML1) . '</text:span></text:p>';
            } elseif (preg_match('/^[-*]\s+(.+)$/', $line, $m)) {
                $currentBody .= '<text:p>• ' . htmlspecialchars($m[1], ENT_XML1) . '</text:p>';
            } elseif (trim($line) !== '') {
                $currentBody .= '<text:p>' . htmlspecialchars($line, ENT_XML1) . '</text:p>';
            }
        }
        $flushSlide();

        $content =
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<office:document-content'
            . ' xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"'
            . ' xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"'
            . ' xmlns:presentation="urn:oasis:names:tc:opendocument:xmlns:presentation:1.0"'
            . ' xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0"'
            . ' xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0"'
            . ' xmlns:xlink="http://www.w3.org/1999/xlink"'
            . ' xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0"'
            . ' office:version="1.3">'
            . '<office:automatic-styles>'
            . '<style:style style:name="T1" style:family="text">'
            . '<style:text-properties fo:font-size="28pt" fo:font-weight="bold" fo:color="#2F5496"/>'
            . '</style:style>'
            . '<style:style style:name="T2" style:family="text">'
            . '<style:text-properties fo:font-size="20pt" fo:font-weight="bold" fo:color="#2E74B5"/>'
            . '</style:style>'
            . '</office:automatic-styles>'
            . '<office:body><office:presentation>'
            // Slide 1: source image
            . '<draw:page draw:name="slide1" draw:master-page-name="Default">'
            . '<draw:frame draw:name="img" svg:x="' . $offX . 'cm" svg:y="' . $offY . 'cm"'
            . ' svg:width="' . $imgW . 'cm" svg:height="' . $imgH . 'cm">'
            . '<draw:image xlink:href="' . $media . '" xlink:type="simple"'
            . ' xlink:show="embed" xlink:actuate="onLoad"/>'
            . '</draw:frame></draw:page>'
            // Text slides
            . $textSlides
            . '</office:presentation></office:body>'
            . '</office:document-content>';

        $manifest =
            '<?xml version="1.0" encoding="UTF-8"?>'
            . '<manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0" manifest:version="1.3">'
            . '<manifest:file-entry manifest:full-path="/" manifest:version="1.3" manifest:media-type="application/vnd.oasis.opendocument.presentation"/>'
            . '<manifest:file-entry manifest:full-path="content.xml" manifest:media-type="text/xml"/>'
            . '<manifest:file-entry manifest:full-path="' . $media . '" manifest:media-type="' . $mime . '"/>'
            . '</manifest:manifest>';

        $zip = new \ZipArchive();
        if ($zip->open($outputPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            return false;
        }
        $zip->addFromString('mimetype', 'application/vnd.oasis.opendocument.presentation');
        if (method_exists($zip, 'setCompressionName')) {
            $zip->setCompressionName('mimetype', \ZipArchive::CM_STORE);
        }
        $zip->addFromString('META-INF/manifest.xml', $manifest);
        $zip->addFromString('content.xml',           $content);
        $zip->addFile($imagePath, $media);
        $zip->close();

        return file_exists($outputPath) && filesize($outputPath) > 100;
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
            $aiOcr = $this->aiService->ocrForFormat($inputPath, $outputFormat, $this->planTier);
            if ($aiOcr['success'] && !empty(trim($aiOcr['text'] ?? ''))) {
                return $this->writeTextOutput($aiOcr['text'], $outputFormat, $outputPath);
            }
        }

        // ── 2. Tesseract plain-text OCR (local fallback) ──────────────────
        $text = '';
        $tess = trim((string) shell_exec('which tesseract 2>/dev/null'));
        if ($tess) {
            // Pre-process for better OCR accuracy on coloured backgrounds
            $processedPath = $this->preprocessImageForOcr($inputPath);
            $ocrInput      = $processedPath ?? $inputPath;

            $tmpBase = sys_get_temp_dir() . '/cx_tess_' . bin2hex(random_bytes(12));
            exec(
                escapeshellarg($tess) . ' ' . escapeshellarg($ocrInput)
                . ' ' . escapeshellarg($tmpBase) . ' --psm 6 -l eng 2>/dev/null',
                $_lines, $tessCode
            );
            if ($processedPath !== null) {
                @unlink($processedPath);
            }
            $tessOut = $tmpBase . '.txt';
            if ($tessCode === 0 && file_exists($tessOut)) {
                $text = (string) file_get_contents($tessOut);
                @unlink($tessOut);
            }
        }

        // ── 3. AI generic OCR (when Tesseract is not installed) ────────────
        if (empty(trim($text)) && $this->aiService !== null) {
            $aiOcr = $this->aiService->ocr($inputPath, $this->planTier);
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
