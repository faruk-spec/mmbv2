<?php
/**
 * PdfToolsService – Unit Tests
 *
 * Tests for Merge PDFs, Split PDFs, Compress PDF, and Compress Images.
 * These tests run without a database, HTTP stack, or external PDF tools.
 * Where system tools (gs) are absent the service is expected to throw;
 * where GD is present, image compression is exercised end-to-end.
 *
 * @package Tests\Unit
 */

use PHPUnit\Framework\TestCase;

if (!defined('PROJECT_PATH')) {
    define('PROJECT_PATH', BASE_PATH . '/projects/convertx');
}

require_once BASE_PATH . '/projects/convertx/services/PdfToolsService.php';
require_once BASE_PATH . '/projects/convertx/controllers/PdfToolsController.php';

class PdfToolsTest extends TestCase
{
    private \Projects\ConvertX\Services\PdfToolsService $svc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->svc = new \Projects\ConvertX\Services\PdfToolsService();
    }

    // ------------------------------------------------------------------ //
    //  hasGd                                                               //
    // ------------------------------------------------------------------ //

    public function testHasGdMatchesExtensionLoaded(): void
    {
        $this->assertSame(extension_loaded('gd'), $this->svc->hasGd());
    }

    // ------------------------------------------------------------------ //
    //  findGhostscript                                                     //
    // ------------------------------------------------------------------ //

    public function testFindGhostscriptReturnsStringOrNull(): void
    {
        $result = $this->svc->findGhostscript();
        $this->assertTrue($result === null || is_string($result),
            'findGhostscript must return a string path or null');
    }

    public function testFindGhostscriptReturnValueIsCached(): void
    {
        $first  = $this->svc->findGhostscript();
        $second = $this->svc->findGhostscript();
        $this->assertSame($first, $second, 'findGhostscript result must be cached');
    }

    // ------------------------------------------------------------------ //
    //  mergePdfs — error paths                                             //
    // ------------------------------------------------------------------ //

    public function testMergePdfThrowsWhenGhostscriptAbsent(): void
    {
        if ($this->svc->findGhostscript() !== null) {
            $this->markTestSkipped('Ghostscript is present on this server — skipping absent-gs test.');
        }
        $this->expectException(\RuntimeException::class);
        $this->svc->mergePdfs(['/tmp/a.pdf', '/tmp/b.pdf'], '/tmp/merged.pdf');
    }

    public function testMergePdfThrowsWithFewerThanTwoFiles(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        // This must throw regardless of whether gs is installed
        $this->svc->mergePdfs(['/tmp/only_one.pdf'], '/tmp/out.pdf');
    }

    // ------------------------------------------------------------------ //
    //  splitPdf — error paths                                              //
    // ------------------------------------------------------------------ //

    public function testSplitPdfThrowsWhenGhostscriptAbsent(): void
    {
        if ($this->svc->findGhostscript() !== null) {
            $this->markTestSkipped('Ghostscript is present on this server — skipping absent-gs test.');
        }
        $this->expectException(\RuntimeException::class);
        $this->svc->splitPdf('/tmp/input.pdf', sys_get_temp_dir());
    }

    public function testSplitPdfThrowsForMissingFile(): void
    {
        if ($this->svc->findGhostscript() === null) {
            $this->markTestSkipped('Ghostscript absent — skipping file-not-found test.');
        }
        $this->expectException(\InvalidArgumentException::class);
        $this->svc->splitPdf('/tmp/does_not_exist_xyz.pdf', sys_get_temp_dir());
    }

    // ------------------------------------------------------------------ //
    //  compressPdf — error paths                                           //
    // ------------------------------------------------------------------ //

    public function testCompressPdfThrowsWhenGhostscriptAbsent(): void
    {
        if ($this->svc->findGhostscript() !== null) {
            $this->markTestSkipped('Ghostscript is present on this server — skipping absent-gs test.');
        }
        $this->expectException(\RuntimeException::class);
        $this->svc->compressPdf('/tmp/input.pdf', '/tmp/out.pdf');
    }

    // ------------------------------------------------------------------ //
    //  compressImage — error paths                                         //
    // ------------------------------------------------------------------ //

    public function testCompressImageThrowsForMissingFile(): void
    {
        if (!$this->svc->hasGd()) {
            $this->markTestSkipped('GD not available.');
        }
        $this->expectException(\InvalidArgumentException::class);
        $this->svc->compressImage('/tmp/does_not_exist_xyz.jpg', '/tmp/out.jpg');
    }

    public function testCompressImageThrowsWhenGdAbsent(): void
    {
        if ($this->svc->hasGd()) {
            $this->markTestSkipped('GD is available — skipping absent-GD test.');
        }
        $this->expectException(\RuntimeException::class);
        $this->svc->compressImage('/tmp/any.jpg', '/tmp/out.jpg');
    }

    // ------------------------------------------------------------------ //
    //  compressImage — JPEG round-trip via GD                             //
    // ------------------------------------------------------------------ //

    public function testCompressImageJpegRoundTrip(): void
    {
        if (!$this->svc->hasGd()) {
            $this->markTestSkipped('GD not available.');
        }

        // Create a 200×150 green JPEG in a temp file
        $srcPath = sys_get_temp_dir() . '/pdftools_test_src.jpg';
        $outPath = sys_get_temp_dir() . '/pdftools_test_out.jpg';
        $im = imagecreatetruecolor(200, 150);
        $green = imagecolorallocate($im, 0, 180, 60);
        imagefill($im, 0, 0, $green);
        imagejpeg($im, $srcPath, 90);
        imagedestroy($im);

        $this->assertTrue(file_exists($srcPath), 'Could not create test JPEG');

        $this->svc->compressImage($srcPath, $outPath, 60, 0);

        $this->assertTrue(file_exists($outPath), 'Compressed JPEG should exist');
        $this->assertGreaterThan(0, filesize($outPath), 'Compressed JPEG must not be empty');

        // Sanity: it is a valid image
        $info = @getimagesize($outPath);
        $this->assertIsArray($info, 'Output must be a valid image');
        $this->assertSame(200, $info[0], 'Width must be preserved when maxWidthPx=0');
        $this->assertSame(150, $info[1], 'Height must be preserved when maxWidthPx=0');

        @unlink($srcPath);
        @unlink($outPath);
    }

    public function testCompressImagePngRoundTrip(): void
    {
        if (!$this->svc->hasGd()) {
            $this->markTestSkipped('GD not available.');
        }

        $srcPath = sys_get_temp_dir() . '/pdftools_test_src.png';
        $outPath = sys_get_temp_dir() . '/pdftools_test_out.png';
        $im = imagecreatetruecolor(100, 80);
        $blue = imagecolorallocate($im, 30, 90, 200);
        imagefill($im, 0, 0, $blue);
        imagepng($im, $srcPath);
        imagedestroy($im);

        $this->svc->compressImage($srcPath, $outPath, 75, 0);
        $this->assertTrue(file_exists($outPath));
        $this->assertGreaterThan(0, filesize($outPath));

        @unlink($srcPath);
        @unlink($outPath);
    }

    public function testCompressImageRescalesWhenMaxWidthSet(): void
    {
        if (!$this->svc->hasGd()) {
            $this->markTestSkipped('GD not available.');
        }

        $srcPath = sys_get_temp_dir() . '/pdftools_test_wide.jpg';
        $outPath = sys_get_temp_dir() . '/pdftools_test_narrow.jpg';
        $im = imagecreatetruecolor(800, 400);
        $red = imagecolorallocate($im, 220, 30, 30);
        imagefill($im, 0, 0, $red);
        imagejpeg($im, $srcPath, 90);
        imagedestroy($im);

        $this->svc->compressImage($srcPath, $outPath, 82, 400);

        $info = @getimagesize($outPath);
        $this->assertIsArray($info);
        $this->assertSame(400, $info[0], 'Width must be capped to maxWidthPx');
        $this->assertSame(200, $info[1], 'Height must scale proportionally');

        @unlink($srcPath);
        @unlink($outPath);
    }

    public function testCompressImageDoesNotRescaleWhenWidthBelowMax(): void
    {
        if (!$this->svc->hasGd()) {
            $this->markTestSkipped('GD not available.');
        }

        $srcPath = sys_get_temp_dir() . '/pdftools_test_small.jpg';
        $outPath = sys_get_temp_dir() . '/pdftools_test_small_out.jpg';
        $im = imagecreatetruecolor(300, 200);
        $col = imagecolorallocate($im, 100, 150, 200);
        imagefill($im, 0, 0, $col);
        imagejpeg($im, $srcPath, 90);
        imagedestroy($im);

        // maxWidthPx = 800 — image is only 300 px wide, must NOT be upscaled
        $this->svc->compressImage($srcPath, $outPath, 82, 800);

        $info = @getimagesize($outPath);
        $this->assertIsArray($info);
        $this->assertSame(300, $info[0], 'Image smaller than maxWidthPx must not be upscaled');

        @unlink($srcPath);
        @unlink($outPath);
    }

    // ------------------------------------------------------------------ //
    //  parsePageRange reflection test                                      //
    // ------------------------------------------------------------------ //

    public function testParsePageRangeViaController(): void
    {
        $ref    = new \ReflectionMethod(\Projects\ConvertX\Controllers\PdfToolsController::class, 'parsePageRange');
        $ref->setAccessible(true);

        $ctrl = $this->getMockBuilder(\Projects\ConvertX\Controllers\PdfToolsController::class)
                     ->disableOriginalConstructor()
                     ->getMock();

        $this->assertSame([], $ref->invoke($ctrl, ''), 'Empty string should return all pages ([])');
        $this->assertSame([1, 2, 3], $ref->invoke($ctrl, '1-3'));
        $this->assertSame([1, 5, 8], $ref->invoke($ctrl, '1,5,8'));
        $this->assertSame([1, 3, 4, 5, 7], $ref->invoke($ctrl, '1,3-5,7'));
        $this->assertSame([1], $ref->invoke($ctrl, '1'));
        $this->assertSame([], $ref->invoke($ctrl, 'invalid'), 'Non-numeric input should return []');
    }

    // ------------------------------------------------------------------ //
    //  Controller method existence                                         //
    // ------------------------------------------------------------------ //

    public function testPdfToolsControllerHasAllRequiredMethods(): void
    {
        $methods = [
            'showMerge', 'showSplit', 'showCompressPdf', 'showCompressImages',
            'submitMerge', 'submitSplit', 'submitCompressPdf', 'submitCompressImages',
            'download',
        ];
        foreach ($methods as $m) {
            $this->assertTrue(
                method_exists(\Projects\ConvertX\Controllers\PdfToolsController::class, $m),
                "PdfToolsController must have method: {$m}"
            );
        }
    }

    // ------------------------------------------------------------------ //
    //  Route wiring                                                        //
    // ------------------------------------------------------------------ //

    public function testRoutesContainAllPdfToolsCases(): void
    {
        $src = file_get_contents(BASE_PATH . '/projects/convertx/routes/web.php');
        foreach (['pdf-merge', 'pdf-split', 'pdf-compress', 'img-compress', 'pdf-tools'] as $route) {
            $this->assertStringContainsString("case '{$route}'", $src,
                "routes/web.php must have a case for '{$route}'");
        }
    }

    // ------------------------------------------------------------------ //
    //  Sidebar nav                                                         //
    // ------------------------------------------------------------------ //

    public function testSidebarNavContainsPdfToolsLinks(): void
    {
        $src = file_get_contents(BASE_PATH . '/projects/convertx/views/layout.php');
        $links = [
            '/projects/convertx/pdf-merge',
            '/projects/convertx/pdf-split',
            '/projects/convertx/pdf-compress',
            '/projects/convertx/img-compress',
        ];
        foreach ($links as $link) {
            $this->assertStringContainsString($link, $src,
                "layout.php sidebar must contain link to {$link}");
        }
    }

    // ------------------------------------------------------------------ //
    //  View files exist                                                    //
    // ------------------------------------------------------------------ //

    public function testAllViewFilesExist(): void
    {
        $views = ['pdf-merge', 'pdf-split', 'pdf-compress', 'img-compress'];
        foreach ($views as $view) {
            $path = BASE_PATH . '/projects/convertx/views/' . $view . '.php';
            $this->assertFileExists($path, "View file {$view}.php must exist");
        }
    }

    public function testViewFilesContainCsrfToken(): void
    {
        $views = ['pdf-merge', 'pdf-split', 'pdf-compress', 'img-compress'];
        foreach ($views as $view) {
            $src = file_get_contents(BASE_PATH . '/projects/convertx/views/' . $view . '.php');
            $this->assertStringContainsString('_token', $src,
                "{$view}.php must include a CSRF token field");
        }
    }

    public function testViewFilesContainCorrectFormAction(): void
    {
        $map = [
            'pdf-merge'   => '/projects/convertx/pdf-merge',
            'pdf-split'   => '/projects/convertx/pdf-split',
            'pdf-compress'=> '/projects/convertx/pdf-compress',
            'img-compress'=> '/projects/convertx/img-compress',
        ];
        foreach ($map as $view => $action) {
            $src = file_get_contents(BASE_PATH . '/projects/convertx/views/' . $view . '.php');
            $this->assertStringContainsString($action, $src,
                "{$view}.php must POST to {$action}");
        }
    }

    public function testImgCompressViewContainsQualitySlider(): void
    {
        $src = file_get_contents(BASE_PATH . '/projects/convertx/views/img-compress.php');
        $this->assertStringContainsString('qualitySlider', $src);
        $this->assertStringContainsString('quality', $src);
        $this->assertStringContainsString('max_width', $src);
        $this->assertStringContainsString('output_format', $src);
    }

    public function testPdfCompressViewContainsPresets(): void
    {
        $src = file_get_contents(BASE_PATH . '/projects/convertx/views/pdf-compress.php');
        foreach (['screen', 'ebook', 'printer', 'prepress'] as $preset) {
            $this->assertStringContainsString($preset, $src,
                "pdf-compress.php must list the '{$preset}' quality preset");
        }
    }

    public function testPdfSplitViewContainsPageRangeInput(): void
    {
        $src = file_get_contents(BASE_PATH . '/projects/convertx/views/pdf-split.php');
        $this->assertStringContainsString('page_range', $src);
    }

    public function testPdfMergeViewContainsDragToReorderHint(): void
    {
        $src = file_get_contents(BASE_PATH . '/projects/convertx/views/pdf-merge.php');
        $this->assertStringContainsString('drag', strtolower($src),
            'pdf-merge.php must mention drag-to-reorder');
    }
}
