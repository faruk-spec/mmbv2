<?php
/**
 * ConvertX PDF Tools Service
 *
 * Provides Merge PDFs, Split PDFs, Compress PDF, and Compress Images.
 * Backend strategy (in priority order):
 *   PDF ops  — Ghostscript (gs)
 *   Image ops— GD extension (always available on modern PHP)
 *
 * @package MMB\Projects\ConvertX\Services
 */

namespace Projects\ConvertX\Services;

class PdfToolsService
{
    // ------------------------------------------------------------------ //
    //  Backend detection                                                   //
    // ------------------------------------------------------------------ //

    /** @return string|null  Absolute path to 'gs' binary, or null if absent */
    public function findGhostscript(): ?string
    {
        static $cache = false;
        if ($cache !== false) {
            return $cache;
        }
        $gs = trim((string) shell_exec('which gs 2>/dev/null'));
        $cache = $gs ?: null;
        return $cache;
    }

    /** @return bool  Whether the GD image extension is loaded */
    public function hasGd(): bool
    {
        return extension_loaded('gd');
    }

    // ------------------------------------------------------------------ //
    //  Merge PDFs                                                          //
    // ------------------------------------------------------------------ //

    /**
     * Merge multiple PDF files into a single PDF.
     *
     * @param string[] $inputPaths  Ordered list of absolute paths to source PDFs
     * @param string   $outputPath  Absolute path for the merged output
     * @return bool
     * @throws \RuntimeException  If Ghostscript is unavailable or the command fails
     */
    public function mergePdfs(array $inputPaths, string $outputPath): bool
    {
        if (count($inputPaths) < 2) {
            throw new \InvalidArgumentException('At least 2 PDF files are required to merge.');
        }

        $gs = $this->findGhostscript();
        if (!$gs) {
            throw new \RuntimeException(
                'PDF merge requires Ghostscript (gs). '
                . 'Install it with: apt install ghostscript'
            );
        }

        $inputArgs = implode(' ', array_map('escapeshellarg', $inputPaths));
        $cmd = sprintf(
            '%s -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -dPDFSETTINGS=/prepress -sOutputFile=%s %s 2>&1',
            escapeshellarg($gs),
            escapeshellarg($outputPath),
            $inputArgs
        );

        $output = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($outputPath) || filesize($outputPath) === 0) {
            throw new \RuntimeException(
                'PDF merge failed (Ghostscript exit ' . $exitCode . '). '
                . implode(' ', $output)
            );
        }

        return true;
    }

    // ------------------------------------------------------------------ //
    //  Split PDF                                                           //
    // ------------------------------------------------------------------ //

    /**
     * Split a PDF into individual page PDFs and return the list of output paths.
     *
     * @param string $inputPath   Absolute path to the source PDF
     * @param string $outputDir   Directory where page PDFs will be written
     * @param int[]  $pageRange   Optional specific pages to extract (1-based); empty = all pages
     * @return string[]  Absolute paths to the generated page PDFs
     * @throws \RuntimeException  If Ghostscript is unavailable or the command fails
     */
    public function splitPdf(string $inputPath, string $outputDir, array $pageRange = []): array
    {
        $gs = $this->findGhostscript();
        if (!$gs) {
            throw new \RuntimeException(
                'PDF split requires Ghostscript (gs). '
                . 'Install it with: apt install ghostscript'
            );
        }

        if (!file_exists($inputPath)) {
            throw new \InvalidArgumentException('Source PDF file not found.');
        }

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        // Determine page count
        $pageCount = $this->getPdfPageCount($inputPath, $gs);
        if ($pageCount < 1) {
            throw new \RuntimeException('Could not determine page count of the PDF.');
        }

        // Resolve pages to extract
        if (empty($pageRange)) {
            $pageRange = range(1, $pageCount);
        } else {
            $pageRange = array_filter($pageRange, fn($p) => $p >= 1 && $p <= $pageCount);
        }

        $pageRange  = array_values(array_unique($pageRange));
        sort($pageRange);

        $outputPaths = [];
        $baseName    = pathinfo($inputPath, PATHINFO_FILENAME);

        foreach ($pageRange as $page) {
            $outFile = $outputDir . '/' . $baseName . '_page' . $page . '.pdf';
            $cmd = sprintf(
                '%s -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite'
                . ' -dFirstPage=%d -dLastPage=%d'
                . ' -sOutputFile=%s %s 2>&1',
                escapeshellarg($gs),
                $page,
                $page,
                escapeshellarg($outFile),
                escapeshellarg($inputPath)
            );

            $output   = [];
            $exitCode = 0;
            exec($cmd, $output, $exitCode);

            if ($exitCode === 0 && file_exists($outFile) && filesize($outFile) > 0) {
                $outputPaths[] = $outFile;
            }
        }

        if (empty($outputPaths)) {
            throw new \RuntimeException('PDF split produced no output files.');
        }

        return $outputPaths;
    }

    // ------------------------------------------------------------------ //
    //  Compress PDF                                                        //
    // ------------------------------------------------------------------ //

    /**
     * Compress a PDF using Ghostscript PDF optimisation settings.
     *
     * @param string $inputPath   Absolute path to the source PDF
     * @param string $outputPath  Absolute path for the compressed PDF
     * @param string $quality     'screen' | 'ebook' | 'printer' | 'prepress' | 'default'
     * @return bool
     * @throws \RuntimeException  If Ghostscript is unavailable or the command fails
     */
    public function compressPdf(string $inputPath, string $outputPath, string $quality = 'ebook'): bool
    {
        $gs = $this->findGhostscript();
        if (!$gs) {
            throw new \RuntimeException(
                'PDF compression requires Ghostscript (gs). '
                . 'Install it with: apt install ghostscript'
            );
        }

        $allowed = ['screen', 'ebook', 'printer', 'prepress', 'default'];
        if (!in_array($quality, $allowed, true)) {
            $quality = 'ebook';
        }

        $cmd = sprintf(
            '%s -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite'
            . ' -dCompatibilityLevel=1.4'
            . ' -dPDFSETTINGS=/%s'
            . ' -sOutputFile=%s %s 2>&1',
            escapeshellarg($gs),
            $quality,
            escapeshellarg($outputPath),
            escapeshellarg($inputPath)
        );

        $output   = [];
        $exitCode = 0;
        exec($cmd, $output, $exitCode);

        if ($exitCode !== 0 || !file_exists($outputPath) || filesize($outputPath) === 0) {
            throw new \RuntimeException(
                'PDF compression failed (Ghostscript exit ' . $exitCode . '). '
                . implode(' ', $output)
            );
        }

        return true;
    }

    // ------------------------------------------------------------------ //
    //  Compress Images                                                     //
    // ------------------------------------------------------------------ //

    /**
     * Compress an image using GD, producing a JPEG or PNG at the requested quality.
     *
     * Supported input formats: jpg, jpeg, png, gif, webp, bmp
     * Output format is inferred from $outputPath extension (defaults to jpg).
     *
     * @param string $inputPath    Absolute path to the source image
     * @param string $outputPath   Absolute path for the compressed image
     * @param int    $quality      1–100 for JPEG/WebP; 0–9 PNG compression level
     * @param int    $maxWidthPx   If > 0 and image width exceeds this, rescale proportionally
     * @return bool
     * @throws \RuntimeException  If GD is unavailable or the image cannot be processed
     */
    public function compressImage(
        string $inputPath,
        string $outputPath,
        int    $quality   = 82,
        int    $maxWidthPx = 0
    ): bool {
        if (!$this->hasGd()) {
            throw new \RuntimeException('Image compression requires the PHP GD extension.');
        }

        if (!file_exists($inputPath)) {
            throw new \InvalidArgumentException('Source image file not found.');
        }

        $inputExt  = strtolower(pathinfo($inputPath,  PATHINFO_EXTENSION));
        $outputExt = strtolower(pathinfo($outputPath, PATHINFO_EXTENSION));

        $im = $this->gdLoadImage($inputPath, $inputExt);
        if ($im === null) {
            throw new \RuntimeException(
                'Could not open image (unsupported format: ' . $inputExt . ').'
            );
        }

        // Optional resize
        if ($maxWidthPx > 0) {
            $origW = imagesx($im);
            $origH = imagesy($im);
            if ($origW > $maxWidthPx) {
                $scale  = $maxWidthPx / $origW;
                $newW   = (int) round($origW * $scale);
                $newH   = (int) round($origH * $scale);
                $resized = imagecreatetruecolor($newW, $newH);
                // Preserve alpha for PNG/WebP
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
                imagefill($resized, 0, 0, $transparent);
                imagecopyresampled($resized, $im, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
                imagedestroy($im);
                $im = $resized;
            }
        }

        $quality = max(1, min(100, $quality));
        $ok      = $this->gdSaveImage($im, $outputPath, $outputExt ?: $inputExt, $quality);
        imagedestroy($im);

        if (!$ok || !file_exists($outputPath) || filesize($outputPath) === 0) {
            throw new \RuntimeException('Image compression failed: could not write output file.');
        }

        return true;
    }

    // ------------------------------------------------------------------ //
    //  Helpers                                                             //
    // ------------------------------------------------------------------ //

    /**
     * Count pages in a PDF using Ghostscript.
     * Returns 0 on failure.
     */
    public function getPdfPageCount(string $pdfPath, ?string $gs = null): int
    {
        $gs = $gs ?? $this->findGhostscript();
        if (!$gs || !file_exists($pdfPath)) {
            return 0;
        }

        $cmd = sprintf(
            '%s -dBATCH -dNOPAUSE -q -sDEVICE=nullpage %s 2>&1',
            escapeshellarg($gs),
            escapeshellarg($pdfPath)
        );

        // Ghostscript prints "Page N" for each page to stderr/stdout with nullpage
        // A more reliable approach: use pdfinfo or count pages via PostScript
        $postscript = 'currentfile closefile';
        $cmd2 = sprintf(
            '%s -dBATCH -dNOPAUSE -q -sDEVICE=nullpage'
            . ' -dNODISPLAY -c "(%s) (r) file runpdfbegin pdfpagecount = quit" 2>&1',
            escapeshellarg($gs),
            addslashes($pdfPath)
        );
        $out2 = trim((string) shell_exec($cmd2));
        if (is_numeric($out2) && (int) $out2 > 0) {
            return (int) $out2;
        }

        // Fallback: run Ghostscript and count how many times it processes a page
        $cmd3 = sprintf(
            '%s -dBATCH -dNOPAUSE -q -sDEVICE=nullpage %s 2>&1 | wc -l',
            escapeshellarg($gs),
            escapeshellarg($pdfPath)
        );
        // This is unreliable; read page count from output
        // Reliable fallback: use pdfinfo
        $pdfinfo = trim((string) shell_exec('which pdfinfo 2>/dev/null'));
        if ($pdfinfo) {
            $info = (string) shell_exec(escapeshellarg($pdfinfo) . ' ' . escapeshellarg($pdfPath) . ' 2>/dev/null');
            if (preg_match('/Pages:\s+(\d+)/i', $info, $m)) {
                return (int) $m[1];
            }
        }

        // Last resort: use gs with a PostScript snippet to print page count
        $cmd4 = sprintf(
            'echo "(%s) (r) file runpdfbegin pdfpagecount = quit" | %s -dBATCH -dNOPAUSE -q -sDEVICE=nullpage -dNODISPLAY - 2>/dev/null',
            addslashes($pdfPath),
            escapeshellarg($gs)
        );
        $out4 = trim((string) shell_exec($cmd4));
        if (is_numeric($out4) && (int) $out4 > 0) {
            return (int) $out4;
        }

        return 0;
    }

    /** Load an image with GD based on extension. Returns null on failure. */
    private function gdLoadImage(string $path, string $ext): ?\GdImage
    {
        return match ($ext) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path) ?: null,
            'png'         => @imagecreatefrompng($path)  ?: null,
            'gif'         => @imagecreatefromgif($path)  ?: null,
            'webp'        => function_exists('imagecreatefromwebp') ? (@imagecreatefromwebp($path) ?: null) : null,
            'bmp'         => function_exists('imagecreatefrombmp')  ? (@imagecreatefrombmp($path)  ?: null) : null,
            default       => null,
        };
    }

    /** Save an image with GD. Returns false on failure. */
    private function gdSaveImage(\GdImage $im, string $path, string $ext, int $quality): bool
    {
        return match ($ext) {
            'jpg', 'jpeg' => (bool) imagejpeg($im, $path, $quality),
            'png'         => (bool) imagepng($im, $path, (int) round((100 - $quality) * 9 / 100)),
            'gif'         => (bool) imagegif($im, $path),
            'webp'        => function_exists('imagewebp') && imagewebp($im, $path, $quality),
            'bmp'         => function_exists('imagebmp')  && imagebmp($im, $path),
            default       => (bool) imagejpeg($im, $path, $quality),
        };
    }
}
