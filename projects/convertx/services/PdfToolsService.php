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
    //  Resize Image                                                        //
    // ------------------------------------------------------------------ //

    /**
     * Resize an image by pixel dimensions or percentage.
     *
     * @param string $inputPath
     * @param string $outputPath
     * @param array  $opts  Keys: width, height, percent (0-200), maintain_ratio (bool), quality (1-100)
     */
    public function resizeImage(string $inputPath, string $outputPath, array $opts): bool
    {
        if (!$this->hasGd()) {
            throw new \RuntimeException('Image resize requires the PHP GD extension.');
        }

        [$im, $fmt] = $this->loadImage($inputPath);
        if ($im === null) {
            throw new \RuntimeException('Could not open image.');
        }

        $origW = imagesx($im);
        $origH = imagesy($im);

        $percent       = isset($opts['percent']) ? (int) $opts['percent'] : 0;
        $maintainRatio = !empty($opts['maintain_ratio']);
        $quality       = max(1, min(100, (int) ($opts['quality'] ?? 82)));

        if ($percent > 0) {
            $newW = (int) round($origW * $percent / 100);
            $newH = (int) round($origH * $percent / 100);
        } else {
            $newW = isset($opts['width'])  ? max(1, (int) $opts['width'])  : 0;
            $newH = isset($opts['height']) ? max(1, (int) $opts['height']) : 0;

            if ($maintainRatio) {
                if ($newW > 0 && $newH === 0) {
                    $newH = (int) round($origH * $newW / $origW);
                } elseif ($newH > 0 && $newW === 0) {
                    $newW = (int) round($origW * $newH / $origH);
                } elseif ($newW > 0 && $newH > 0) {
                    $scaleW = $newW / $origW;
                    $scaleH = $newH / $origH;
                    $scale  = min($scaleW, $scaleH);
                    $newW   = (int) round($origW * $scale);
                    $newH   = (int) round($origH * $scale);
                }
            }

            if ($newW <= 0) {
                $newW = $origW;
            }
            if ($newH <= 0) {
                $newH = $origH;
            }
        }

        $outExt = strtolower(pathinfo($outputPath, PATHINFO_EXTENSION)) ?: $fmt;

        $resized = imagecreatetruecolor($newW, $newH);
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        $transparent = imagecolorallocatealpha($resized, 0, 0, 0, 127);
        imagefill($resized, 0, 0, $transparent);
        imagecopyresampled($resized, $im, 0, 0, 0, 0, $newW, $newH, $origW, $origH);
        imagedestroy($im);

        $ok = $this->saveImage($resized, $outputPath, $outExt, $quality);
        imagedestroy($resized);

        if (!$ok || !file_exists($outputPath) || filesize($outputPath) === 0) {
            throw new \RuntimeException('Image resize failed: could not write output file.');
        }

        return true;
    }

    // ------------------------------------------------------------------ //
    //  Crop Image                                                          //
    // ------------------------------------------------------------------ //

    /**
     * Crop an image to the given rectangle.
     *
     * @param array $opts  Keys: x, y, crop_width, crop_height
     */
    public function cropImage(string $inputPath, string $outputPath, array $opts): bool
    {
        if (!$this->hasGd()) {
            throw new \RuntimeException('Image crop requires the PHP GD extension.');
        }

        [$im, $fmt] = $this->loadImage($inputPath);
        if ($im === null) {
            throw new \RuntimeException('Could not open image.');
        }

        $x          = max(0, (int) ($opts['x']           ?? 0));
        $y          = max(0, (int) ($opts['y']           ?? 0));
        $cropWidth  = max(1, (int) ($opts['crop_width']  ?? imagesx($im)));
        $cropHeight = max(1, (int) ($opts['crop_height'] ?? imagesy($im)));
        $quality    = max(1, min(100, (int) ($opts['quality'] ?? 90)));

        $outExt = strtolower(pathinfo($outputPath, PATHINFO_EXTENSION)) ?: $fmt;

        $rect    = ['x' => $x, 'y' => $y, 'width' => $cropWidth, 'height' => $cropHeight];
        $cropped = imagecrop($im, $rect);
        imagedestroy($im);

        if ($cropped === false) {
            throw new \RuntimeException('imagecrop() failed — coordinates may be out of bounds.');
        }

        $ok = $this->saveImage($cropped, $outputPath, $outExt, $quality);
        imagedestroy($cropped);

        if (!$ok || !file_exists($outputPath) || filesize($outputPath) === 0) {
            throw new \RuntimeException('Image crop failed: could not write output file.');
        }

        return true;
    }

    // ------------------------------------------------------------------ //
    //  Watermark Image                                                     //
    // ------------------------------------------------------------------ //

    /**
     * Apply a text or image watermark.
     *
     * @param array $opts  Keys: text, font_size (8-72), opacity (0-100), position, color_hex, watermark_image_path
     */
    public function watermarkImage(string $inputPath, string $outputPath, array $opts): bool
    {
        if (!$this->hasGd()) {
            throw new \RuntimeException('Image watermark requires the PHP GD extension.');
        }

        [$im, $fmt] = $this->loadImage($inputPath);
        if ($im === null) {
            throw new \RuntimeException('Could not open image.');
        }

        $imgW    = imagesx($im);
        $imgH    = imagesy($im);
        $opacity = max(0, min(100, (int) ($opts['opacity'] ?? 50)));
        $quality = max(1, min(100, (int) ($opts['quality'] ?? 90)));
        $outExt  = strtolower(pathinfo($outputPath, PATHINFO_EXTENSION)) ?: $fmt;

        $wmImagePath = $opts['watermark_image_path'] ?? '';

        if (!empty($wmImagePath) && file_exists($wmImagePath)) {
            // Image watermark
            [$wmIm, ] = $this->loadImage($wmImagePath);
            if ($wmIm !== null) {
                $wmW   = imagesx($wmIm);
                $wmH   = imagesy($wmIm);
                [$dstX, $dstY] = $this->calcWatermarkPos(
                    $opts['position'] ?? 'bottomright',
                    $imgW, $imgH, $wmW, $wmH
                );
                imagecopymerge($im, $wmIm, $dstX, $dstY, 0, 0, $wmW, $wmH, $opacity);
                imagedestroy($wmIm);
            }
        } else {
            // Text watermark
            $text     = (string) ($opts['text']      ?? 'Watermark');
            $fontSize = max(8, min(72, (int) ($opts['font_size'] ?? 24)));
            $colorHex = ltrim($opts['color_hex'] ?? '#ffffff', '#');
            $r = hexdec(substr($colorHex, 0, 2));
            $g = hexdec(substr($colorHex, 2, 2));
            $b = hexdec(substr($colorHex, 4, 2));

            // Try TTF first
            $fontPath = $this->findFont();
            if ($fontPath && function_exists('imagettftext')) {
                $bbox   = imagettfbbox($fontSize, 0, $fontPath, $text);
                $textW  = abs($bbox[2] - $bbox[0]);
                $textH  = abs($bbox[5] - $bbox[1]);
                [$dstX, $dstY] = $this->calcWatermarkPos(
                    $opts['position'] ?? 'bottomright',
                    $imgW, $imgH, $textW, $textH
                );

                // Merge onto temp canvas for opacity support
                $overlay = imagecreatetruecolor($imgW, $imgH);
                imagecopy($overlay, $im, 0, 0, 0, 0, $imgW, $imgH);
                $col = imagecolorallocate($overlay, (int)$r, (int)$g, (int)$b);
                imagettftext($overlay, $fontSize, 0, $dstX, $dstY + $textH, $col, $fontPath, $text);
                imagecopymerge($im, $overlay, 0, 0, 0, 0, $imgW, $imgH, $opacity);
                imagedestroy($overlay);
            } else {
                // Fallback: imagestring (fixed 5 = 9px wide chars)
                $charW  = 9;
                $charH  = 15;
                $textW  = strlen($text) * $charW;
                $textH  = $charH;
                [$dstX, $dstY] = $this->calcWatermarkPos(
                    $opts['position'] ?? 'bottomright',
                    $imgW, $imgH, $textW, $textH
                );
                $col = imagecolorallocate($im, (int)$r, (int)$g, (int)$b);
                imagestring($im, 5, $dstX, $dstY, $text, $col);
            }
        }

        $ok = $this->saveImage($im, $outputPath, $outExt, $quality);
        imagedestroy($im);

        if (!$ok || !file_exists($outputPath) || filesize($outputPath) === 0) {
            throw new \RuntimeException('Watermark failed: could not write output file.');
        }

        return true;
    }

    // ------------------------------------------------------------------ //
    //  Rotate Image                                                        //
    // ------------------------------------------------------------------ //

    /**
     * Rotate an image by the specified degrees (clockwise).
     */
    public function rotateImage(string $inputPath, string $outputPath, int $degrees): bool
    {
        if (!$this->hasGd()) {
            throw new \RuntimeException('Image rotate requires the PHP GD extension.');
        }

        [$im, $fmt] = $this->loadImage($inputPath);
        if ($im === null) {
            throw new \RuntimeException('Could not open image.');
        }

        $outExt = strtolower(pathinfo($outputPath, PATHINFO_EXTENSION)) ?: $fmt;

        // imagerotate rotates counter-clockwise, so negate for clockwise
        $rotated    = imagerotate($im, -$degrees, 0);
        imagedestroy($im);

        if ($rotated === false) {
            throw new \RuntimeException('imagerotate() failed.');
        }

        $ok = $this->saveImage($rotated, $outputPath, $outExt, 90);
        imagedestroy($rotated);

        if (!$ok || !file_exists($outputPath) || filesize($outputPath) === 0) {
            throw new \RuntimeException('Image rotate failed: could not write output file.');
        }

        return true;
    }

    // ------------------------------------------------------------------ //
    //  Meme Generator                                                      //
    // ------------------------------------------------------------------ //

    /**
     * Add classic meme-style top/bottom text to an image.
     *
     * @param array $opts  Keys: top_text, bottom_text, font_size, text_color, stroke_color, font_path
     */
    public function addMemeText(string $inputPath, string $outputPath, array $opts): bool
    {
        if (!$this->hasGd()) {
            throw new \RuntimeException('Meme generator requires the PHP GD extension.');
        }

        [$im, $fmt] = $this->loadImage($inputPath);
        if ($im === null) {
            throw new \RuntimeException('Could not open image.');
        }

        $outExt    = strtolower(pathinfo($outputPath, PATHINFO_EXTENSION)) ?: $fmt;
        $imgW      = imagesx($im);
        $imgH      = imagesy($im);
        $topText   = strtoupper((string) ($opts['top_text']    ?? ''));
        $botText   = strtoupper((string) ($opts['bottom_text'] ?? ''));
        $fontSize  = max(12, min(120, (int) ($opts['font_size'] ?? 48)));
        $textColor = $opts['text_color']   ?? 'white';
        $strokeClr = $opts['stroke_color'] ?? 'black';

        $colorMap = [
            'white'  => [255, 255, 255],
            'black'  => [0,   0,   0],
            'yellow' => [255, 255, 0],
        ];
        [$tr, $tg, $tb] = $colorMap[$textColor]  ?? [255, 255, 255];
        [$sr, $sg, $sb] = $colorMap[$strokeClr]  ?? [0,   0,   0];

        $fontPath = $opts['font_path'] ?? $this->findFont();
        $margin   = (int) ($imgH * 0.03);

        if ($fontPath && function_exists('imagettftext')) {
            $this->drawMemeTextTtf($im, $topText, $fontPath, $fontSize, $imgW, $margin,
                                   $tr, $tg, $tb, $sr, $sg, $sb, true);
            $this->drawMemeTextTtf($im, $botText, $fontPath, $fontSize, $imgW, $imgH - $margin,
                                   $tr, $tg, $tb, $sr, $sg, $sb, false);
        } else {
            $col    = imagecolorallocate($im, $tr, $tg, $tb);
            $stroke = imagecolorallocate($im, $sr, $sg, $sb);
            $font   = 5;
            $charW  = 9;
            $charH  = 15;

            if ($topText !== '') {
                $tw = strlen($topText) * $charW;
                $tx = (int) (($imgW - $tw) / 2);
                imagestring($im, $font, $tx + 1, $margin + 1, $topText, $stroke);
                imagestring($im, $font, $tx, $margin, $topText, $col);
            }
            if ($botText !== '') {
                $tw = strlen($botText) * $charW;
                $tx = (int) (($imgW - $tw) / 2);
                $ty = $imgH - $margin - $charH;
                imagestring($im, $font, $tx + 1, $ty + 1, $botText, $stroke);
                imagestring($im, $font, $tx, $ty, $botText, $col);
            }
        }

        $ok = $this->saveImage($im, $outputPath, $outExt, 92);
        imagedestroy($im);

        if (!$ok || !file_exists($outputPath) || filesize($outputPath) === 0) {
            throw new \RuntimeException('Meme generation failed: could not write output file.');
        }

        return true;
    }

    // ------------------------------------------------------------------ //
    //  Shared image helpers (public so controller can use them if needed)  //
    // ------------------------------------------------------------------ //

    /**
     * Load an image via GD and return [resource|null, format_string].
     * @return array  First element is \GdImage|null, second is string format
     */
    public function loadImage(string $path): array
    {
        if (!file_exists($path)) {
            return [null, ''];
        }
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $im  = $this->gdLoadImage($path, $ext);
        return [$im, $ext];
    }

    /**
     * Save a GD image resource to disk.
     */
    public function saveImage(\GdImage $img, string $outputPath, string $fmt, int $quality = 90): bool
    {
        return $this->gdSaveImage($img, $outputPath, $fmt, $quality);
    }

    // ------------------------------------------------------------------ //
    //  Private helpers for new image tools                                 //
    // ------------------------------------------------------------------ //

    /** Calculate the destination X,Y for a watermark given its position. */
    private function calcWatermarkPos(string $pos, int $imgW, int $imgH, int $wmW, int $wmH): array
    {
        $pad  = 10;
        // Clamp watermark dimensions to image size
        $wmW  = min($wmW, $imgW - $pad);
        $wmH  = min($wmH, $imgH - $pad);
        return match ($pos) {
            'topleft'     => [$pad, $pad],
            'topright'    => [max($pad, $imgW - $wmW - $pad), $pad],
            'bottomleft'  => [$pad, max($pad, $imgH - $wmH - $pad)],
            'center'      => [(int)(($imgW - $wmW) / 2), (int)(($imgH - $wmH) / 2)],
            default       => [max($pad, $imgW - $wmW - $pad), max($pad, $imgH - $wmH - $pad)], // bottomright
        };
    }

    /** Find a TTF font file bundled with the project. */
    private function findFont(): ?string
    {
        $candidates = [
            defined('PROJECT_PATH') ? PROJECT_PATH . '/assets/fonts/impact.ttf'    : null,
            defined('PROJECT_PATH') ? PROJECT_PATH . '/assets/fonts/Arial.ttf'     : null,
            defined('PROJECT_PATH') ? PROJECT_PATH . '/assets/fonts/DejaVuSans.ttf': null,
            '/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf',
            '/usr/share/fonts/truetype/liberation/LiberationSans-Bold.ttf',
            '/usr/share/fonts/truetype/freefont/FreeSansBold.ttf',
        ];
        foreach ($candidates as $c) {
            if ($c && file_exists($c)) {
                return $c;
            }
        }
        return null;
    }

    /** Draw a single meme text line with TTF including a stroke effect. */
    private function drawMemeTextTtf(
        \GdImage $im,
        string   $text,
        string   $fontPath,
        int      $fontSize,
        int      $imgW,
        int      $baseY,
        int      $tr, int $tg, int $tb,
        int      $sr, int $sg, int $sb,
        bool     $isTop
    ): void {
        if ($text === '') {
            return;
        }
        $bbox  = imagettfbbox($fontSize, 0, $fontPath, $text);
        $textW = abs($bbox[2] - $bbox[0]);
        $textH = abs($bbox[5] - $bbox[1]);
        $x     = (int) (($imgW - $textW) / 2);
        $y     = $isTop ? $baseY + $textH : $baseY;

        $stroke = imagecolorallocate($im, $sr, $sg, $sb);
        $fill   = imagecolorallocate($im, $tr, $tg, $tb);
        $sw     = max(2, (int) ($fontSize / 20));

        for ($dx = -$sw; $dx <= $sw; $dx++) {
            for ($dy = -$sw; $dy <= $sw; $dy++) {
                if ($dx !== 0 || $dy !== 0) {
                    imagettftext($im, $fontSize, 0, $x + $dx, $y + $dy, $stroke, $fontPath, $text);
                }
            }
        }
        imagettftext($im, $fontSize, 0, $x, $y, $fill, $fontPath, $text);
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
