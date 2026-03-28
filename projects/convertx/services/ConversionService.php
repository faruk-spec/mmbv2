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

        // 4b. Image → writer format (docx / odt / rtf / doc): create a proper document
        //     that embeds the image using PHP ZipArchive — no external tools needed.
        //     This is far more reliable than the 2-step chain (image→PDF→DOCX) which
        //     requires the optional libreoffice-pdfimport package for the PDF→Writer
        //     leg, and often silently produces empty output when that package is absent.
        //     'doc' (binary OLE format) is handled by building a temp DOCX in PHP then
        //     converting DOCX→DOC via LibreOffice (same Writer family, no --infilter).
        $phpWriterFormats = ['docx', 'odt', 'rtf', 'doc'];
        if ($isInputImage && in_array($outputFormat, $phpWriterFormats, true)) {
            if ($this->convertImageToDocumentWithPhp($inputPath, $inputFormat, $outputFormat, $outputPath)) {
                return true;
            }
            // ZipArchive unavailable or failed — fall through to chain
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
        // Extract text via Tesseract OCR
        $text = '';
        $tess = trim((string) shell_exec('which tesseract 2>/dev/null'));
        if ($tess) {
            $tmpBase = sys_get_temp_dir() . '/cx_tess_' . getmypid() . '_' . bin2hex(random_bytes(8));
            exec(
                escapeshellarg($tess) . ' ' . escapeshellarg($inputPath)
                . ' ' . escapeshellarg($tmpBase) . ' -l eng 2>/dev/null',
                $_lines, $tessCode
            );
            $tessOut = $tmpBase . '.txt';
            if ($tessCode === 0 && file_exists($tessOut)) {
                $text = (string) file_get_contents($tessOut);
                @unlink($tessOut);
            }
        }

        // Parse extracted text into rows/cells
        $rows = [];
        foreach (preg_split('/\r\n|\r|\n/', trim($text)) as $line) {
            if (trim($line) === '') {
                continue;
            }
            // Tab-separated → multiple cells; otherwise treat as single-cell CSV row
            if (str_contains($line, "\t")) {
                $rows[] = explode("\t", $line);
            } else {
                $parsed = str_getcsv($line);
                $rows[] = ($parsed !== false && $parsed !== ['']) ? $parsed : [$line];
            }
        }

        if (empty($rows)) {
            $rows = [['No text could be extracted from this image.']];
        }

        if ($outputFormat === 'ods') {
            return $this->writeOdsCalcFromRows($rows, $outputPath);
        }

        // xlsx and xls: write xlsx (xlsx is the modern binary-compatible replacement for xls)
        $xlsxOk = $this->writeXlsxFromRows($rows, $outputPath);
        if ($xlsxOk && $outputFormat === 'xls') {
            // Attempt LibreOffice xlsx→xls conversion if LibreOffice is available
            $tmpXlsx = $outputPath . '_tmp.xlsx';
            try {
                if (@rename($outputPath, $tmpXlsx)) {
                    $this->convertWithLibreOffice($tmpXlsx, 'xlsx', 'xls', $outputPath);
                    @unlink($tmpXlsx);
                }
            } catch (\Exception $e) {
                // LibreOffice unavailable — keep xlsx content under xls extension
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
                $ref        = $colLetter . $rowNum;
                $escaped    = htmlspecialchars((string) $cell, ENT_QUOTES | ENT_XML1, 'UTF-8');
                $sheetData .= "<c r=\"{$ref}\" t=\"inlineStr\"><is><t>{$escaped}</t></is></c>";
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
            . '</Types>';

        $relsMain =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';

        $workbook =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/sheet"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Sheet1" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';

        $workbookRels =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '</Relationships>';

        $sheet =
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/sheet">'
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
     * Extract text from an image using Tesseract OCR, then write the result
     * to a plain-text output file in the requested format (txt/html/md/csv).
     *
     * Falls back to LibreOffice Draw's --cat if Tesseract is absent (rare
     * success — only works for SVG; returns empty for raster images) and then
     * to an explicit error rather than silently producing garbage.
     */
    private function convertImageToTextWithOcr(
        string $inputPath,
        string $outputFormat,
        string $outputPath
    ): bool {
        $text = '';

        // 1. Tesseract (best for raster OCR) — cryptographically unique temp path,
        //    no pre-created placeholder file, no tempnam race condition.
        $tess = trim((string) shell_exec('which tesseract 2>/dev/null'));
        if ($tess) {
            $tmpBase = sys_get_temp_dir() . '/cx_tess_' . getmypid() . '_' . bin2hex(random_bytes(8));
            $lang = 'eng';
            exec(
                escapeshellarg($tess) . ' ' . escapeshellarg($inputPath)
                . ' ' . escapeshellarg($tmpBase) . ' -l ' . escapeshellarg($lang) . ' 2>/dev/null',
                $_lines, $tessCode
            );
            $tessOut = $tmpBase . '.txt';
            if ($tessCode === 0 && file_exists($tessOut)) {
                $text = (string) file_get_contents($tessOut);
                @unlink($tessOut);
            }
        }

        // 2. If no Tesseract text, give a meaningful error rather than garbage
        if (empty(trim($text))) {
            throw new \RuntimeException(
                "Text extraction from images is not available on this server. "
                . "Try a different output format."
            );
        }

        // 3. Write extracted text to the target format
        $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
        switch ($outputFormat) {
            case 'html':
                $body    = nl2br(htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
                $content = "<!DOCTYPE html><html><head><meta charset=\"UTF-8\"></head><body><p>{$body}</p></body></html>";
                return file_put_contents($outputPath, $content) !== false;
            case 'md':
                return file_put_contents($outputPath, $text) !== false;
            case 'csv':
                // Each non-empty line becomes a single-column RFC 4180 CSV row
                $fh = fopen($outputPath, 'w');
                if (!$fh) {
                    return false;
                }
                $lines = preg_split('/\r\n|\r|\n/', trim($text)) ?: [];
                foreach ($lines as $line) {
                    fputcsv($fh, [$line]);
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
