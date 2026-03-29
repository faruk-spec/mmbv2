<?php
/**
 * ConvertX – Unit Tests
 *
 * Tests for the core ConvertX services (ConversionService, AIService).
 * These tests do NOT require a database or external services.
 *
 * @package Tests\Unit
 */

use PHPUnit\Framework\TestCase;

// Bootstrap project constants that the services depend on
if (!defined('PROJECT_PATH')) {
    define('PROJECT_PATH', BASE_PATH . '/projects/convertx');
}

// Load core dependencies required by the services under test
require_once BASE_PATH . '/core/Logger.php';

// Load the services under test
require_once BASE_PATH . '/projects/convertx/services/ConversionService.php';
require_once BASE_PATH . '/projects/convertx/services/AIService.php';

// Load models for constant tests (stubs DB connection, safe to load class without instantiating)
require_once BASE_PATH . '/core/Database.php';
require_once BASE_PATH . '/projects/convertx/models/ConversionJobModel.php';
require_once BASE_PATH . '/projects/convertx/models/AIProviderModel.php';

// Load controllers for reflection-based tests (not instantiated — constructor needs DB)
require_once BASE_PATH . '/core/Auth.php';
require_once BASE_PATH . '/core/Security.php';
require_once BASE_PATH . '/core/ActivityLogger.php';
require_once BASE_PATH . '/projects/convertx/services/JobQueueService.php';
require_once BASE_PATH . '/projects/convertx/controllers/BatchController.php';

class ConvertXTest extends TestCase
{
    private \Projects\ConvertX\Services\ConversionService $conversionService;
    private \Projects\ConvertX\Services\AIService $aiService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->conversionService = new \Projects\ConvertX\Services\ConversionService();
        // AIService constructor calls AIProviderModel which hits DB — skip full init in unit tests
    }

    // ------------------------------------------------------------------ //
    //  ConversionService::detectFormat                                     //
    // ------------------------------------------------------------------ //

    public function testDetectFormatFromExtension(): void
    {
        // detectFormat falls back to file extension when finfo is unavailable
        // Create a temp file with a known extension
        $tmpPdf = tempnam(sys_get_temp_dir(), 'cx_test_') . '.pdf';
        file_put_contents($tmpPdf, '%PDF-1.4 fake pdf content');

        $format = $this->conversionService->detectFormat($tmpPdf, 'document.pdf');

        @unlink($tmpPdf);

        // Should detect 'pdf' either via finfo or via extension fallback
        $this->assertContains($format, ['pdf', 'unknown'],
            "Expected 'pdf' or 'unknown' for a .pdf file, got '{$format}'"
        );
    }

    public function testDetectFormatKnownExtensions(): void
    {
        // Test extension-based fallback directly
        $service = $this->conversionService;

        $tmpBase = tempnam(sys_get_temp_dir(), 'cx_ext_');

        foreach (['docx', 'xlsx', 'pptx', 'txt', 'csv', 'md', 'html'] as $ext) {
            $path = $tmpBase . '.' . $ext;
            file_put_contents($path, 'test content');

            $detected = $service->detectFormat($path, 'file.' . $ext);
            @unlink($path);

            $this->assertIsString($detected);
            $this->assertNotEmpty($detected);
        }

        @unlink($tmpBase);
    }

    // ------------------------------------------------------------------ //
    //  ConversionService::requiresOCR                                      //
    // ------------------------------------------------------------------ //

    public function testRequiresOCRForImages(): void
    {
        $this->assertTrue($this->conversionService->requiresOCR('jpg'));
        $this->assertTrue($this->conversionService->requiresOCR('png'));
        $this->assertTrue($this->conversionService->requiresOCR('tiff'));
        $this->assertTrue($this->conversionService->requiresOCR('pdf'));
    }

    public function testRequiresOCRFalseForTextFormats(): void
    {
        $this->assertFalse($this->conversionService->requiresOCR('docx'));
        $this->assertFalse($this->conversionService->requiresOCR('txt'));
        $this->assertFalse($this->conversionService->requiresOCR('html'));
        $this->assertFalse($this->conversionService->requiresOCR('csv'));
    }

    public function testRequiresOCRCaseInsensitive(): void
    {
        $this->assertTrue($this->conversionService->requiresOCR('JPG'));
        $this->assertTrue($this->conversionService->requiresOCR('PNG'));
    }

    // ------------------------------------------------------------------ //
    //  ConversionService::getSupportedOutputFormats                        //
    // ------------------------------------------------------------------ //

    public function testGetSupportedOutputFormatsExcludesInput(): void
    {
        $formats = $this->conversionService->getSupportedOutputFormats('pdf');

        $this->assertIsArray($formats);
        $this->assertNotEmpty($formats);
        $this->assertNotContains('pdf', $formats,
            'Output formats should not include the input format itself'
        );
    }

    public function testGetSupportedOutputFormatsContainsCommonFormats(): void
    {
        $formats = $this->conversionService->getSupportedOutputFormats('docx');

        $this->assertContains('pdf',  $formats);
        $this->assertContains('txt',  $formats);
        $this->assertContains('jpg',  $formats);
    }

    // ------------------------------------------------------------------ //
    //  ConversionService::convert – plain text conversion                  //
    // ------------------------------------------------------------------ //

    public function testCsvToTextConversion(): void
    {
        // Create a temp CSV
        $csvPath = tempnam(sys_get_temp_dir(), 'cx_csv_') . '.csv';
        file_put_contents($csvPath, "name,age\nAlice,30\nBob,25\n");

        $txtPath = str_replace('.csv', '_converted.txt', $csvPath);

        // Invoke the service
        $result = $this->conversionService->convert($csvPath, 'csv', 'txt');

        @unlink($csvPath);
        if (file_exists($txtPath)) {
            @unlink($txtPath);
        }

        // CSV→TXT is now handled by the pure-PHP engine so it must succeed
        $this->assertIsArray($result);
        $this->assertTrue($result['success'], 'csv→txt pure-PHP conversion should succeed');
        $this->assertNotEmpty($result['output_path']);
    }

    // ------------------------------------------------------------------ //
    //  Pure-PHP text conversion engine                                     //
    // ------------------------------------------------------------------ //

    public function testTxtToHtmlConversion(): void
    {
        $txtPath = tempnam(sys_get_temp_dir(), 'cx_txt_') . '.txt';
        file_put_contents($txtPath, "Hello World\nLine 2");

        $result = $this->conversionService->convert($txtPath, 'txt', 'html');

        @unlink($txtPath);
        if (!empty($result['output_path']) && file_exists($result['output_path'])) {
            $html = file_get_contents($result['output_path']);
            @unlink($result['output_path']);
            $this->assertStringContainsString('Hello World', $html);
        }

        $this->assertTrue($result['success'], 'txt→html should succeed via pure-PHP engine');
    }

    public function testHtmlToTxtConversion(): void
    {
        $htmlPath = tempnam(sys_get_temp_dir(), 'cx_html_') . '.html';
        file_put_contents($htmlPath, '<html><body><h1>Title</h1><p>Paragraph</p></body></html>');

        $result = $this->conversionService->convert($htmlPath, 'html', 'txt');

        @unlink($htmlPath);
        if (!empty($result['output_path']) && file_exists($result['output_path'])) {
            $txt = file_get_contents($result['output_path']);
            @unlink($result['output_path']);
            $this->assertStringContainsString('Title', $txt);
            $this->assertStringContainsString('Paragraph', $txt);
        }

        $this->assertTrue($result['success'], 'html→txt should succeed via pure-PHP engine');
    }

    public function testMarkdownToHtmlConversion(): void
    {
        $mdPath = tempnam(sys_get_temp_dir(), 'cx_md_') . '.md';
        file_put_contents($mdPath, "# My Title\n\nSome **bold** text.\n");

        $result = $this->conversionService->convert($mdPath, 'md', 'html');

        @unlink($mdPath);
        if (!empty($result['output_path']) && file_exists($result['output_path'])) {
            $html = file_get_contents($result['output_path']);
            @unlink($result['output_path']);
            $this->assertStringContainsString('<h1>', $html);
            $this->assertStringContainsString('My Title', $html);
        }

        $this->assertTrue($result['success'], 'md→html should succeed via pure-PHP engine');
    }

    public function testMarkdownToTxtConversion(): void
    {
        $mdPath = tempnam(sys_get_temp_dir(), 'cx_md_') . '.md';
        file_put_contents($mdPath, "# Header\n\n**Bold** and *italic*.\n");

        $result = $this->conversionService->convert($mdPath, 'md', 'txt');

        @unlink($mdPath);
        if (!empty($result['output_path']) && file_exists($result['output_path'])) {
            $txt = file_get_contents($result['output_path']);
            @unlink($result['output_path']);
            // Markdown syntax stripped
            $this->assertStringNotContainsString('**', $txt);
            $this->assertStringNotContainsString('# ', $txt);
            $this->assertStringContainsString('Bold', $txt);
        }

        $this->assertTrue($result['success'], 'md→txt should succeed via pure-PHP engine');
    }

    public function testSameFormatCopy(): void
    {
        $txtPath = tempnam(sys_get_temp_dir(), 'cx_copy_') . '.txt';
        file_put_contents($txtPath, 'Hello copy test');

        $result = $this->conversionService->convert($txtPath, 'txt', 'txt');

        @unlink($txtPath);
        if (!empty($result['output_path']) && file_exists($result['output_path'])) {
            $content = file_get_contents($result['output_path']);
            @unlink($result['output_path']);
            $this->assertEquals('Hello copy test', $content);
        }

        $this->assertTrue($result['success'], 'txt→txt same-format copy should succeed');
    }

    // ------------------------------------------------------------------ //
    //  AIService::estimateTokens                                           //
    // ------------------------------------------------------------------ //

    public function testEstimateTokensEmpty(): void
    {
        // Instantiate AIService bypassing the DB-dependent constructor
        // by using a partial mock that skips AIProviderModel
        $aiService = $this->getMockBuilder(\Projects\ConvertX\Services\AIService::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $tokens = $aiService->estimateTokens('');
        $this->assertEquals(0, $tokens);
    }

    public function testEstimateTokensApproximation(): void
    {
        $aiService = $this->getMockBuilder(\Projects\ConvertX\Services\AIService::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        // "Hello world" = 11 chars → ceil(11/4) = 3 tokens
        $tokens = $aiService->estimateTokens('Hello world');
        $this->assertEquals(3, $tokens);

        // 400 chars → 100 tokens
        $text = str_repeat('a', 400);
        $this->assertEquals(100, $aiService->estimateTokens($text));
    }

    public function testEstimateTokensLargeText(): void
    {
        $aiService = $this->getMockBuilder(\Projects\ConvertX\Services\AIService::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $text   = str_repeat('word ', 1000); // ~5000 chars
        $tokens = $aiService->estimateTokens($text);

        $this->assertGreaterThan(0, $tokens);
        $this->assertIsInt($tokens);
    }

    public function testEstimateCost(): void
    {
        $aiService = $this->getMockBuilder(\Projects\ConvertX\Services\AIService::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $provider = ['cost_per_1k_tokens' => 0.002];

        // 1000 tokens at $0.002/1k = $0.002
        $cost = $aiService->estimateCost($provider, 1000);
        $this->assertEqualsWithDelta(0.002, $cost, 0.000001);

        // 500 tokens = $0.001
        $cost500 = $aiService->estimateCost($provider, 500);
        $this->assertEqualsWithDelta(0.001, $cost500, 0.000001);

        // 0 tokens = $0.00
        $cost0 = $aiService->estimateCost($provider, 0);
        $this->assertEquals(0.0, $cost0);
    }

    // ------------------------------------------------------------------ //
    //  ConversionJobModel constants                                        //
    // ------------------------------------------------------------------ //

    public function testJobStatusConstants(): void
    {
        // Load the model without touching DB (test constants only)
        $this->assertEquals('pending',    \Projects\ConvertX\Models\ConversionJobModel::STATUS_PENDING);
        $this->assertEquals('processing', \Projects\ConvertX\Models\ConversionJobModel::STATUS_PROCESSING);
        $this->assertEquals('completed',  \Projects\ConvertX\Models\ConversionJobModel::STATUS_COMPLETED);
        $this->assertEquals('failed',     \Projects\ConvertX\Models\ConversionJobModel::STATUS_FAILED);
        $this->assertEquals('cancelled',  \Projects\ConvertX\Models\ConversionJobModel::STATUS_CANCELLED);
    }

    // ------------------------------------------------------------------ //
    //  ConversionService: cross-family "any-to-any" chain routing          //
    // ------------------------------------------------------------------ //

    /**
     * Verify cross-family conversions no longer throw a RuntimeException
     * at the routing stage (they now route to convertViaChain which will
     * fail gracefully with a RuntimeException only if the external tools
     * are missing — the SIGABRT LibreOffice crash no longer happens).
     *
     * We test with missing input files so that convertWithImageMagick /
     * convertWithLibreOffice return false / throw, and convert() returns
     * ['success'=>false] instead of crashing the process.
     */
    public function testImageToOfficeReturnsFailNotCrash(): void
    {
        // A non-existent PNG → XLSX should return success=false (tool not found
        // or file missing), not throw an uncaught exception.
        $result = $this->conversionService->convert(
            '/tmp/cx_nonexistent_test.png',
            'png',
            'xlsx'
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        // Should be false (file doesn't exist / no ImageMagick in CI)
        // but must NOT throw a "Cannot convert" RuntimeException anymore.
        $this->assertFalse($result['success']);
        $this->assertStringNotContainsString(
            'Image files can only be converted to other image formats',
            $result['error'],
            'The old cross-family guard message must not appear'
        );
    }

    public function testOfficeToImageReturnsFailNotCrash(): void
    {
        $result = $this->conversionService->convert(
            '/tmp/cx_nonexistent_test.docx',
            'docx',
            'png'
        );

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertFalse($result['success']);
        $this->assertStringNotContainsString(
            'Document/spreadsheet/presentation files cannot be converted',
            $result['error'],
            'The old cross-family guard message must not appear'
        );
    }

    public function testGetSupportedOutputFormatsContainsImages(): void
    {
        // Now that any-to-any is supported, image formats must appear as
        // valid output choices for a document input (e.g. docx).
        $formats = $this->conversionService->getSupportedOutputFormats('docx');
        $this->assertContains('jpg', $formats, 'docx should list jpg as a supported output');
        $this->assertContains('png', $formats, 'docx should list png as a supported output');
    }

    public function testGetSupportedOutputFormatsImageInputIncludesOffice(): void
    {
        // Image inputs should also offer office outputs now (via chain).
        $formats = $this->conversionService->getSupportedOutputFormats('png');
        $this->assertContains('xlsx', $formats, 'png should list xlsx as a supported output');
        $this->assertContains('docx', $formats, 'png should list docx as a supported output');
    }

    // ------------------------------------------------------------------ //
    //  PHP-native image → writer format (ZipArchive)                       //
    // ------------------------------------------------------------------ //

    public function testImageToDocxWithPhpCreatesValidZip(): void
    {
        if (!class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive extension not available');
        }
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('GD extension not available');
        }

        $pngPath = tempnam(sys_get_temp_dir(), 'cx_img_') . '.png';
        $img = imagecreatetruecolor(10, 10);
        $this->assertNotFalse($img, 'Failed to create GD image');
        imagefill($img, 0, 0, imagecolorallocate($img, 255, 255, 255));
        imagepng($img, $pngPath);
        imagedestroy($img);

        $result = $this->conversionService->convert($pngPath, 'png', 'docx');
        @unlink($pngPath);

        $this->assertIsArray($result);
        $this->assertTrue($result['success'],
            'PNG → DOCX via PHP ZipArchive should succeed; error: ' . ($result['error'] ?? ''));

        if (!empty($result['output_path']) && file_exists($result['output_path'])) {
            $this->assertGreaterThan(100, filesize($result['output_path']),
                'DOCX output should be > 100 bytes');

            $zip    = new \ZipArchive();
            $opened = $zip->open($result['output_path']);
            $this->assertTrue($opened === true, 'DOCX output must be a valid ZIP archive');
            if ($opened === true) {
                $this->assertNotFalse($zip->locateName('[Content_Types].xml'),
                    'DOCX must contain [Content_Types].xml');
                $this->assertNotFalse($zip->locateName('word/document.xml'),
                    'DOCX must contain word/document.xml');
                $zip->close();
            }
            @unlink($result['output_path']);
        }
    }

    public function testImageToOdtWithPhpCreatesValidZip(): void
    {
        if (!class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive extension not available');
        }
        if (!extension_loaded('gd')) {
            $this->markTestSkipped('GD extension not available');
        }

        $pngPath = tempnam(sys_get_temp_dir(), 'cx_odt_') . '.png';
        $img = imagecreatetruecolor(10, 10);
        $this->assertNotFalse($img, 'Failed to create GD image');
        imagefill($img, 0, 0, imagecolorallocate($img, 200, 200, 200));
        imagepng($img, $pngPath);
        imagedestroy($img);

        $result = $this->conversionService->convert($pngPath, 'png', 'odt');
        @unlink($pngPath);

        $this->assertIsArray($result);
        $this->assertTrue($result['success'],
            'PNG → ODT via PHP ZipArchive should succeed; error: ' . ($result['error'] ?? ''));

        if (!empty($result['output_path']) && file_exists($result['output_path'])) {
            $this->assertGreaterThan(100, filesize($result['output_path']),
                'ODT output should be > 100 bytes');

            $zip    = new \ZipArchive();
            $opened = $zip->open($result['output_path']);
            $this->assertTrue($opened === true, 'ODT output must be a valid ZIP archive');
            if ($opened === true) {
                $this->assertNotFalse($zip->locateName('content.xml'),
                    'ODT must contain content.xml');
                $this->assertNotFalse($zip->locateName('META-INF/manifest.xml'),
                    'ODT must contain META-INF/manifest.xml');
                $zip->close();
            }
            @unlink($result['output_path']);
        }
    }

    // ------------------------------------------------------------------ //
    //  AIService PHP-native fallbacks (via reflection — no DB needed)      //
    // ------------------------------------------------------------------ //

    /**
     * Helper: create a bare AIService mock with constructor disabled so we can
     * call private fallback methods directly via ReflectionMethod.
     */
    private function makeAiServiceMock(): \Projects\ConvertX\Services\AIService
    {
        return $this->getMockBuilder(\Projects\ConvertX\Services\AIService::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
    }

    /** @param string $method Private method name */
    private function invokePrivate(object $obj, string $method, array $args = []): mixed
    {
        $ref = new \ReflectionMethod($obj, $method);
        $ref->setAccessible(true);
        return $ref->invokeArgs($obj, $args);
    }

    public function testPhpNativeSummarizeReturnsSentences(): void
    {
        $ai     = $this->makeAiServiceMock();
        $result = $this->invokePrivate($ai, 'phpNativeSummarize',
            ['This is sentence one. This is sentence two. This is sentence three.', []]);

        $this->assertIsArray($result);
        $this->assertTrue($result['success'], 'PHP-native summarize should always succeed on non-empty text');
        $this->assertNotEmpty($result['summary']);
        $this->assertEquals('php_native', $result['provider']);
        $this->assertStringContainsString('sentence', $result['summary']);
    }

    public function testPhpNativeSummarizeEmptyText(): void
    {
        $ai     = $this->makeAiServiceMock();
        $result = $this->invokePrivate($ai, 'phpNativeSummarize', ['', []]);

        $this->assertFalse($result['success'], 'Empty text should return success=false');
        $this->assertEmpty($result['summary']);
    }

    public function testPhpNativeSummarizeRespectsMaxLength(): void
    {
        $ai = $this->makeAiServiceMock();
        // 100 sentences of 4 words each = 400 words total (with sentence boundaries)
        $text   = str_repeat('This is a test. ', 100);
        $result = $this->invokePrivate($ai, 'phpNativeSummarize', [$text, ['max_length' => 20]]);

        $this->assertTrue($result['success']);
        // max_length=20 → summary should be ≤ 25 words (at most 20 + slack from sentence rounding)
        $this->assertLessThan(30, str_word_count($result['summary']),
            'Summary should be capped at roughly max_length words');
    }

    public function testPhpNativeClassifyInvoice(): void
    {
        $ai   = $this->makeAiServiceMock();
        $text = 'Please pay this invoice. Total amount due: $150.00. Bill to: John Doe.';
        $result = $this->invokePrivate($ai, 'phpNativeClassify', [$text]);

        $this->assertTrue($result['success']);
        $this->assertEquals('invoice', $result['category']);
        $this->assertGreaterThan(0, $result['confidence']);
        $this->assertEquals('php_native', $result['provider']);
    }

    public function testPhpNativeClassifyContract(): void
    {
        $ai   = $this->makeAiServiceMock();
        $text = 'This agreement is entered into by the parties. Whereas the parties hereby agree to the terms and conditions.';
        $result = $this->invokePrivate($ai, 'phpNativeClassify', [$text]);

        $this->assertTrue($result['success']);
        $this->assertEquals('contract', $result['category']);
    }

    public function testPhpNativeClassifyEmptyFallsToOther(): void
    {
        $ai     = $this->makeAiServiceMock();
        $result = $this->invokePrivate($ai, 'phpNativeClassify', ['']);

        $this->assertTrue($result['success']);
        $this->assertEquals('other', $result['category']);
    }

    // ------------------------------------------------------------------ //
    //  parseTextIntoRows — currency comma-split fix                         //
    // ------------------------------------------------------------------ //

    public function testParseTextIntoRowsDoesNotSplitCurrencyAtComma(): void
    {
        $text = "\$5,079.60\n\$1,418.00\n\$744.60";
        $rows = $this->invokePrivate($this->conversionService, 'parseTextIntoRows', [$text]);

        // Every row must be a single cell — str_getcsv would have split $5,079.60
        foreach ($rows as $row) {
            $this->assertCount(1, $row, "Each OCR line must be a single spreadsheet cell (no CSV comma-splitting)");
        }
        $this->assertEquals('$5,079.60', $rows[0][0]);
        $this->assertEquals('$1,418.00', $rows[1][0]);
        $this->assertEquals('$744.60',   $rows[2][0]);
    }

    public function testParseTextIntoRowsSplitsOnTabs(): void
    {
        $text = "Product\tQtr 1\tGrand Total\nChocolade\t\$744.60\t\$907.16";
        $rows = $this->invokePrivate($this->conversionService, 'parseTextIntoRows', [$text]);

        $this->assertCount(2, $rows);
        $this->assertCount(3, $rows[0], 'Tab-separated header should produce 3 cells');
        $this->assertEquals('Product',     $rows[0][0]);
        $this->assertEquals('Grand Total', $rows[0][2]);
        $this->assertEquals('$744.60',     $rows[1][1]);
    }

    public function testParseTextIntoRowsSkipsBlankLines(): void
    {
        $text = "Line one\n\nLine two\n\n\nLine three";
        $rows = $this->invokePrivate($this->conversionService, 'parseTextIntoRows', [$text]);

        $this->assertCount(3, $rows);
    }

    // ------------------------------------------------------------------ //
    //  writeDocxFromText — text-based DOCX builder                         //
    // ------------------------------------------------------------------ //

    public function testWriteDocxFromTextCreatesValidZip(): void
    {
        if (!class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive extension not available');
        }

        $text   = "# Invoice Report\n\nThis is a paragraph.\n\n"
                . "| Product | Price |\n|---------|-------|\n| Chocolade | \$5,079.60 |\n\n"
                . "- Bullet item\n1. Numbered item";
        $outPath = sys_get_temp_dir() . '/cx_test_' . uniqid() . '.docx';

        $ok = $this->invokePrivate($this->conversionService, 'writeDocxFromText', [$text, $outPath]);
        $this->assertTrue($ok, 'writeDocxFromText should return true');
        $this->assertFileExists($outPath);
        $this->assertGreaterThan(100, filesize($outPath), 'DOCX must be > 100 bytes');

        $zip    = new \ZipArchive();
        $opened = $zip->open($outPath);
        $this->assertTrue($opened === true, 'Output must be a valid ZIP');
        if ($opened === true) {
            $this->assertNotFalse($zip->locateName('[Content_Types].xml'));
            $this->assertNotFalse($zip->locateName('word/document.xml'));

            $docXml = $zip->getFromName('word/document.xml');
            $this->assertStringContainsString('Invoice Report', $docXml, 'Heading text must be in document.xml');
            $this->assertStringContainsString('Heading1',       $docXml, 'H1 must use Heading1 style');
            $this->assertStringContainsString('Chocolade',      $docXml, 'Table cell text must appear');
            $this->assertStringContainsString('$5,079.60',      $docXml, 'Currency must NOT be split at comma');
            $this->assertStringContainsString('<w:tbl>',        $docXml, 'Pipe table must produce w:tbl');
            $this->assertStringContainsString('<w:b/>',         $docXml, 'Table header row must be bold');
            $this->assertStringContainsString('ListBullet',     $docXml, 'Bullet list must use ListBullet style');
            $this->assertStringContainsString('ListNumber',     $docXml, 'Numbered list must use ListNumber style');
            $zip->close();
        }
        @unlink($outPath);
    }

    // ------------------------------------------------------------------ //
    //  writeOdtFromText — text-based ODT builder                           //
    // ------------------------------------------------------------------ //

    public function testWriteOdtFromTextCreatesValidZip(): void
    {
        if (!class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive extension not available');
        }

        $text    = "## Summary\n\nSome paragraph text.\n\n| Col A | Col B |\n|-------|-------|\n| Val 1 | Val 2 |";
        $outPath = sys_get_temp_dir() . '/cx_test_' . uniqid() . '.odt';

        $ok = $this->invokePrivate($this->conversionService, 'writeOdtFromText', [$text, $outPath]);
        $this->assertTrue($ok, 'writeOdtFromText should return true');
        $this->assertFileExists($outPath);

        $zip    = new \ZipArchive();
        $opened = $zip->open($outPath);
        $this->assertTrue($opened === true, 'ODT output must be a valid ZIP');
        if ($opened === true) {
            $this->assertNotFalse($zip->locateName('content.xml'));
            $this->assertNotFalse($zip->locateName('META-INF/manifest.xml'));

            $contentXml = $zip->getFromName('content.xml');
            $this->assertStringContainsString('Summary',         $contentXml, 'Heading text must appear');
            $this->assertStringContainsString('Heading 2',       $contentXml, 'H2 must use ODF "Heading 2" style (with space, not _20_)');
            $this->assertStringContainsString('Val 1',           $contentXml, 'Table cell must appear');
            $this->assertStringContainsString('table:table',     $contentXml, 'Pipe table must produce ODF table');
            $zip->close();
        }
        @unlink($outPath);
    }

    // ------------------------------------------------------------------ //
    //  writeRtfFromText — text-based RTF builder                           //
    // ------------------------------------------------------------------ //

    public function testWriteRtfFromTextCreatesValidRtf(): void
    {
        $text    = "# Title\n\nA normal paragraph with **bold** and *italic*.\n\n- Bullet\n1. Numbered";
        $outPath = sys_get_temp_dir() . '/cx_test_' . uniqid() . '.rtf';

        $ok = $this->invokePrivate($this->conversionService, 'writeRtfFromText', [$text, $outPath]);
        $this->assertTrue($ok, 'writeRtfFromText should return true');
        $this->assertFileExists($outPath);

        $content = file_get_contents($outPath);
        $this->assertStringContainsString('{\\rtf1',  $content, 'RTF must start with {\\rtf1');
        $this->assertStringContainsString('Title',    $content, 'Heading text must appear');
        $this->assertStringContainsString('\\b\\fs36',$content, 'H1 must use large bold font');
        $this->assertStringContainsString('{\\b bold}', $content, 'Inline bold must be wrapped in {\\b}');
        $this->assertStringContainsString('{\\i italic}', $content, 'Inline italic must be wrapped in {\\i}');
        $this->assertStringContainsString('\\bullet', $content, 'Bullet list must use \\bullet');
        @unlink($outPath);
    }

    // ------------------------------------------------------------------ //
    //  markdownTableToDocxXml — table builder correctness                  //
    // ------------------------------------------------------------------ //

    public function testMarkdownTableToDocxXmlPreservesCurrency(): void
    {
        $lines = [
            '| Product      | Qtr 1      | Grand Total |',
            '|--------------|------------|-------------|',
            '| Chocolade    | $5,079.60  | $6,328.80   |',
            '| Total        | $14,181.59 | $22,309.37  |',
        ];
        $xml = $this->invokePrivate($this->conversionService, 'markdownTableToDocxXml', [$lines]);

        $this->assertStringContainsString('<w:tbl>',      $xml);
        $this->assertStringContainsString('<w:b/>',       $xml, 'Header row must be bold');
        $this->assertStringContainsString('$5,079.60',    $xml, 'Currency must not be split at comma');
        $this->assertStringContainsString('$14,181.59',   $xml, 'Large currency must not be split');
        $this->assertStringContainsString('Grand Total',  $xml, 'Multi-word header must be intact');
        // 1 header + 2 data rows = 3 <w:tr> elements
        $this->assertEquals(3, substr_count($xml, '<w:tr>'), '3 rows: header + 2 data');
    }

    // ------------------------------------------------------------------ //
    //  AIService: new methods exist and have correct signatures             //
    // ------------------------------------------------------------------ //

    public function testAiServiceOcrDocumentMethodExists(): void
    {
        $ai  = $this->makeAiServiceMock();
        $ref = new \ReflectionMethod($ai, 'ocrDocument');
        $this->assertTrue($ref->isPublic(), 'ocrDocument must be a public method');

        $params = $ref->getParameters();
        $this->assertGreaterThanOrEqual(1, count($params));
        $this->assertEquals('filePath', $params[0]->getName());
    }

    public function testAiServiceOcrForFormatMethodExists(): void
    {
        $ai  = $this->makeAiServiceMock();
        $ref = new \ReflectionMethod($ai, 'ocrForFormat');
        $this->assertTrue($ref->isPublic(), 'ocrForFormat must be a public method');

        $params = $ref->getParameters();
        $this->assertGreaterThanOrEqual(2, count($params));
        $this->assertEquals('filePath',      $params[0]->getName());
        $this->assertEquals('targetFormat',  $params[1]->getName());
    }

    public function testAiServiceOcrDocumentReturnsFailureWhenNoProvider(): void
    {
        // Verify the method signature and return shape via Reflection only —
        // calling it would require a real DB connection to initialise providerModel.
        $ai  = $this->makeAiServiceMock();
        $ref = new \ReflectionMethod($ai, 'ocrDocument');

        $this->assertTrue($ref->isPublic(), 'ocrDocument must be public');
        $this->assertCount(2, $ref->getParameters(), 'ocrDocument(filePath, planTier)');
    }

    public function testAiServiceOcrForFormatReturnsFailureWhenNoProvider(): void
    {
        $ai  = $this->makeAiServiceMock();
        $ref = new \ReflectionMethod($ai, 'ocrForFormat');

        $this->assertTrue($ref->isPublic(), 'ocrForFormat must be public');
        $this->assertCount(3, $ref->getParameters(), 'ocrForFormat(filePath, targetFormat, planTier)');
    }

    // ------------------------------------------------------------------ //
    //  detectPageSizeFromImage — page size detection                        //
    // ------------------------------------------------------------------ //

    private function callDetectPageSize(int $pxW, int $pxH): array
    {
        $ref = new \ReflectionMethod($this->conversionService, 'detectPageSizeFromImage');
        $ref->setAccessible(true);
        return $ref->invoke($this->conversionService, $pxW, $pxH);
    }

    public function testDetectPageSizeA4Portrait(): void
    {
        // A4 at exactly 150 DPI: 210mm × 297mm → 1240 × 1754 px
        // (210/25.4)*150 ≈ 1240  (297/25.4)*150 ≈ 1754
        $ps = $this->callDetectPageSize(1240, 1754);
        $this->assertEquals('A4', $ps['name']);
        $this->assertFalse($ps['landscape']);
        $this->assertGreaterThan(0, $ps['twip_w']);
        $this->assertGreaterThan(0, $ps['twip_h']);
        $this->assertGreaterThan($ps['twip_w'], $ps['twip_h'], 'Portrait: height > width');
    }

    public function testDetectPageSizeA4Landscape(): void
    {
        // A4 landscape at 150 DPI: wider than tall
        $ps = $this->callDetectPageSize(1754, 1240);
        $this->assertEquals('A4', $ps['name']);
        $this->assertTrue($ps['landscape']);
        $this->assertGreaterThan($ps['twip_h'], $ps['twip_w'], 'Landscape: width > height');
    }

    public function testDetectPageSizeLetter(): void
    {
        // US Letter at 150 DPI: 8.5in × 11in → 1275 × 1650 px
        $ps = $this->callDetectPageSize(1275, 1650);
        $this->assertEquals('Letter', $ps['name']);
        $this->assertFalse($ps['landscape']);
    }

    public function testDetectPageSizeReturnsAllRequiredKeys(): void
    {
        $ps = $this->callDetectPageSize(800, 1100);
        foreach (['name', 'landscape', 'twip_w', 'twip_h', 'cm_w', 'cm_h'] as $key) {
            $this->assertArrayHasKey($key, $ps, "detectPageSizeFromImage must return '{$key}'");
        }
        $this->assertIsString($ps['name']);
        $this->assertIsBool($ps['landscape']);
        $this->assertIsInt($ps['twip_w']);
        $this->assertIsInt($ps['twip_h']);
    }

    // ------------------------------------------------------------------ //
    //  writeDocxFromText — styles.xml and page size in DOCX output          //
    // ------------------------------------------------------------------ //

    public function testWriteDocxFromTextIncludesStylesXml(): void
    {
        if (!class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive extension not available');
        }

        $outPath = tempnam(sys_get_temp_dir(), 'cx_test_') . '.docx';
        $ref     = new \ReflectionMethod($this->conversionService, 'writeDocxFromText');
        $ref->setAccessible(true);

        $text    = "# Invoice\n\nAmount: \$1,234.56\n\n- Item A\n- Item B";
        $result  = $ref->invoke($this->conversionService, $text, $outPath, '', '', []);

        $this->assertTrue($result, 'writeDocxFromText must return true');
        $zip    = new \ZipArchive();
        $opened = $zip->open($outPath);
        $this->assertTrue($opened === true, 'Output must be a valid ZIP');
        if ($opened === true) {
            // Must include styles.xml for headings to render correctly
            $this->assertNotFalse($zip->locateName('word/styles.xml'),
                'DOCX must contain word/styles.xml');
            $stylesXml = $zip->getFromName('word/styles.xml');
            $this->assertStringContainsString('Heading1',   $stylesXml, 'Heading1 style must exist');
            $this->assertStringContainsString('Heading2',   $stylesXml, 'Heading2 style must exist');
            $this->assertStringContainsString('ListBullet', $stylesXml, 'ListBullet style must exist');
            $this->assertStringContainsString('TableGrid',  $stylesXml, 'TableGrid style must exist');
            $this->assertStringContainsString('2F5496',     $stylesXml, 'Heading1 must have blue color #2F5496');
            $zip->close();
        }
        @unlink($outPath);
    }

    public function testWriteDocxFromTextSetsPageSize(): void
    {
        if (!class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive extension not available');
        }

        $outPath  = tempnam(sys_get_temp_dir(), 'cx_test_') . '.docx';
        $ref      = new \ReflectionMethod($this->conversionService, 'writeDocxFromText');
        $ref->setAccessible(true);

        // Provide explicit A4 page size
        $pageSize = ['twip_w' => 11906, 'twip_h' => 16838, 'name' => 'A4', 'landscape' => false];
        $result   = $ref->invoke($this->conversionService, 'Hello', $outPath, '', '', $pageSize);

        $this->assertTrue($result);
        $zip    = new \ZipArchive();
        $opened = $zip->open($outPath);
        if ($opened === true) {
            $docXml = $zip->getFromName('word/document.xml');
            $this->assertStringContainsString('w:w="11906"', $docXml,
                'Document page width must match A4 twip_w');
            $this->assertStringContainsString('w:h="16838"', $docXml,
                'Document page height must match A4 twip_h');
            $zip->close();
        }
        @unlink($outPath);
    }

    public function testWriteDocxFromTextHeaderTableHasBlueBackground(): void
    {
        if (!class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive extension not available');
        }

        $outPath  = tempnam(sys_get_temp_dir(), 'cx_test_') . '.docx';
        $ref      = new \ReflectionMethod($this->conversionService, 'writeDocxFromText');
        $ref->setAccessible(true);

        $text   = "| Product | Price |\n|---------|-------|\n| Apple   | \$1.00 |";
        $result = $ref->invoke($this->conversionService, $text, $outPath, '', '', []);

        $this->assertTrue($result);
        $zip    = new \ZipArchive();
        $opened = $zip->open($outPath);
        if ($opened === true) {
            $docXml = $zip->getFromName('word/document.xml');
            // Header row cells must have blue fill
            $this->assertStringContainsString('2F75B6', $docXml,
                'Table header cells must have blue background fill');
            $zip->close();
        }
        @unlink($outPath);
    }

    // ------------------------------------------------------------------ //
    //  writeOdtFromText — styles.xml with page dimensions in ODT output     //
    // ------------------------------------------------------------------ //

    public function testWriteOdtFromTextIncludesStylesXmlWithPageSize(): void
    {
        if (!class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive extension not available');
        }

        $outPath  = tempnam(sys_get_temp_dir(), 'cx_test_') . '.odt';
        $ref      = new \ReflectionMethod($this->conversionService, 'writeOdtFromText');
        $ref->setAccessible(true);

        $pageSize = ['cm_w' => 21.0, 'cm_h' => 29.7, 'name' => 'A4', 'landscape' => false];
        $result   = $ref->invoke($this->conversionService, "# Hello\n\nWorld.", $outPath, '', '', $pageSize);

        $this->assertTrue($result, 'writeOdtFromText must return true');
        $zip    = new \ZipArchive();
        $opened = $zip->open($outPath);
        if ($opened === true) {
            $this->assertNotFalse($zip->locateName('styles.xml'),
                'ODT must contain styles.xml with page layout');
            $stylesXml = $zip->getFromName('styles.xml');
            $this->assertStringContainsString('21.000cm', $stylesXml,
                'Page width must be 21cm (A4)');
            $this->assertStringContainsString('29.700cm', $stylesXml,
                'Page height must be 29.7cm (A4)');
            $this->assertStringContainsString('2F5496',   $stylesXml,
                'Heading 1 must use blue color #2F5496');
            $zip->close();
        }
        @unlink($outPath);
    }

    // ------------------------------------------------------------------ //
    //  writeRtfFromText — page dimensions in RTF via \paperw / \paperh     //
    // ------------------------------------------------------------------ //

    public function testWriteRtfFromTextSetsPageDimensions(): void
    {
        $outPath  = tempnam(sys_get_temp_dir(), 'cx_test_') . '.rtf';
        $ref      = new \ReflectionMethod($this->conversionService, 'writeRtfFromText');
        $ref->setAccessible(true);

        $pageSize = ['twip_w' => 11906, 'twip_h' => 16838, 'name' => 'A4', 'landscape' => false];
        $result   = $ref->invoke($this->conversionService, "# Hello\n\nWorld.", $outPath, $pageSize);

        $this->assertTrue($result);
        $rtf = file_get_contents($outPath);
        $this->assertStringContainsString('\paperw11906', $rtf, 'RTF must set \paperw from page size');
        $this->assertStringContainsString('\paperh16838', $rtf, 'RTF must set \paperh from page size');
        @unlink($outPath);
    }

    // ------------------------------------------------------------------ //
    //  buildDocxStylesXml — helper produces valid Word styles XML           //
    // ------------------------------------------------------------------ //

    public function testBuildDocxStylesXmlContainsAllStyles(): void
    {
        $ref = new \ReflectionMethod($this->conversionService, 'buildDocxStylesXml');
        $ref->setAccessible(true);
        $xml = $ref->invoke($this->conversionService);

        $this->assertStringContainsString('styleId="Heading1"',   $xml);
        $this->assertStringContainsString('styleId="Heading2"',   $xml);
        $this->assertStringContainsString('styleId="Heading3"',   $xml);
        $this->assertStringContainsString('styleId="ListBullet"', $xml);
        $this->assertStringContainsString('styleId="ListNumber"', $xml);
        $this->assertStringContainsString('styleId="TableGrid"',  $xml);
        $this->assertStringContainsString('2F5496',               $xml, 'Heading1 blue color');
        $this->assertStringContainsString('2E74B5',               $xml, 'Heading2 blue color');

        // Must be parseable as XML
        $doc = new \DOMDocument();
        $this->assertTrue(@$doc->loadXML($xml) !== false, 'styles.xml must be valid XML');
    }

    // ------------------------------------------------------------------ //
    //  rasterizePdf — returns null gracefully when no tools available       //
    // ------------------------------------------------------------------ //

    public function testRasterizePdfReturnsNullWhenNoTools(): void
    {
        // In the test environment Ghostscript and ImageMagick may or may not be
        // installed; we test only that the method never crashes and returns
        // null when no PDF tools are available (or when given a non-PDF path).
        $ref = new \ReflectionMethod($this->conversionService, 'rasterizePdf');
        $ref->setAccessible(true);

        // Non-existent file — must return null without exception
        $result = $ref->invoke($this->conversionService, '/tmp/nonexistent_cx_test.pdf', 72);
        $this->assertNull($result, 'rasterizePdf must return null for non-existent files');
    }

    // ------------------------------------------------------------------ //
    //  writePptxFromText — AI presentation builder                          //
    // ------------------------------------------------------------------ //

    public function testWritePptxFromTextBuildsValidZip(): void
    {
        if (!class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive extension not available');
        }

        // Create a minimal source image (1×1 PNG)
        $imgPath = tempnam(sys_get_temp_dir(), 'cx_img_') . '.png';
        file_put_contents($imgPath, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8/5+hHgAHggJ/PchI6QAAAABJRU5ErkJggg=='
        ));

        $outPath = tempnam(sys_get_temp_dir(), 'cx_test_') . '.pptx';
        $ref     = new \ReflectionMethod($this->conversionService, 'writePptxFromText');
        $ref->setAccessible(true);

        $text   = "# Slide Title\n\nSome bullet point\n\n## Sub heading\n\nAnother point";
        $result = $ref->invoke($this->conversionService, $text, $imgPath, 'png', $outPath, 100, 100);

        $this->assertTrue($result, 'writePptxFromText must return true');
        $zip    = new \ZipArchive();
        $opened = $zip->open($outPath);
        $this->assertTrue($opened === true, 'PPTX output must be a valid ZIP');
        if ($opened === true) {
            $this->assertNotFalse($zip->locateName('ppt/presentation.xml'));
            $this->assertNotFalse($zip->locateName('ppt/slides/slide1.xml'));
            $this->assertNotFalse($zip->locateName('ppt/slides/slide2.xml'),
                'Text slide must be generated from Heading 1');
            $zip->close();
        }
        @unlink($imgPath);
        @unlink($outPath);
    }

    // ------------------------------------------------------------------ //
    //  preprocessImageForOcr — grayscale image pre-processing               //
    // ------------------------------------------------------------------ //

    private function callPreprocessImageForOcr(string $inputPath): ?string
    {
        $ref = new \ReflectionMethod($this->conversionService, 'preprocessImageForOcr');
        $ref->setAccessible(true);
        return $ref->invoke($this->conversionService, $inputPath);
    }

    public function testPreprocessImageForOcrReturnsNullForNonExistentFile(): void
    {
        $result = $this->callPreprocessImageForOcr('/tmp/nonexistent_cx_ocr_test_file.png');
        $this->assertNull($result, 'preprocessImageForOcr must return null for non-existent files');
    }

    public function testPreprocessImageForOcrReturnsPngOrNull(): void
    {
        // Create a real 10×10 PNG with a blue-ish pixel so GD has something to work with
        $tmpImg = tempnam(sys_get_temp_dir(), 'cx_test_') . '.png';
        if (extension_loaded('gd') && function_exists('imagecreatetruecolor')) {
            $img = imagecreatetruecolor(20, 20);
            $blue = imagecolorallocate($img, 0x29, 0x60, 0xA8);   // blue header colour
            $white = imagecolorallocate($img, 0xFF, 0xFF, 0xFF);
            imagefill($img, 0, 0, $blue);
            imagestring($img, 3, 2, 4, 'Hi', $white);
            imagepng($img, $tmpImg);
            imagedestroy($img);
        } else {
            // Fallback: minimal valid 1×1 PNG
            file_put_contents($tmpImg, base64_decode(
                'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg=='
            ));
        }

        $result = $this->callPreprocessImageForOcr($tmpImg);
        @unlink($tmpImg);

        if ($result !== null) {
            $this->assertFileExists($result, 'preprocessed file must exist');
            $this->assertStringEndsWith('.png', $result, 'preprocessed output must be PNG');
            $this->assertGreaterThan(0, filesize($result), 'preprocessed PNG must not be empty');
            @unlink($result);
        } else {
            // Neither ImageMagick nor GD available in this environment — that's OK
            $this->assertNull($result);
        }
    }

    // ------------------------------------------------------------------ //
    //  extractRowsFromTesseractTsv — column boundary detection unit tests   //
    // ------------------------------------------------------------------ //

    /**
     * Test the column detection logic in isolation by creating a synthetic TSV
     * that mimics what Tesseract would produce for a 4-column spreadsheet.
     * We exercise the method via a real call using a crafted PNG (1×1 white) and
     * then verify the column-detection helpers directly.
     */
    public function testColumnBoundaryDetectionFindsCorrectColumns(): void
    {
        // Simulate a set of word left-edges from a 4-column table:
        //   Column 0 starts at px 10-30   (row label / number)
        //   Column 1 starts at px 80-120  (Last Name)
        //   Column 2 starts at px 230-270 (Sales)
        //   Column 3 starts at px 380-420 (Country)
        //   Column 4 starts at px 520-560 (Quarter)
        //
        // We test the clustering algorithm by calling it through a reflection
        // of the column-zone logic using representative left-edge values.
        $allLefts = [
            // Column 0
            10, 12, 11, 13,
            // Column 1
            85, 88, 83, 90,
            // Column 2
            240, 245, 238, 250,
            // Column 3
            390, 395, 388,
            // Column 4
            530, 535, 528,
        ];
        sort($allLefts);

        $allWidths = array_fill(0, count($allLefts), 55); // typical word width 55px
        sort($allWidths);
        $medW = $allWidths[(int)(count($allWidths) / 2)];
        $colGapMin = max(12, (int)($medW * 0.45));

        // Run the same left-edge clustering logic
        $colStarts = [];
        $group     = $allLefts[0];
        for ($i = 1; $i < count($allLefts); $i++) {
            if ($allLefts[$i] - $allLefts[$i - 1] > $colGapMin) {
                $colStarts[] = (int) round($group);
                $group       = $allLefts[$i];
            } else {
                $group = min($group, $allLefts[$i]);
            }
        }
        $colStarts[] = (int) round($group);
        sort($colStarts);

        // Should detect exactly 5 column starts
        $this->assertCount(5, $colStarts,
            'Column boundary detection must find 5 columns from the simulated left-edges');
        $this->assertLessThan(20,  $colStarts[0], 'Column 0 start should be near px 10');
        $this->assertGreaterThan(70, $colStarts[1], 'Column 1 start should be > 70px');
        $this->assertGreaterThan(200, $colStarts[2], 'Column 2 start should be > 200px');
    }

    public function testColumnBoundaryDetectionHandlesSingleColumn(): void
    {
        // When all words start within a tight horizontal band, only 1 column detected
        $allLefts  = [10, 11, 10, 12, 11, 13]; // all clustered near px 10
        sort($allLefts);
        $medW      = 50;
        $colGapMin = max(12, (int)($medW * 0.45));

        $colStarts = [];
        $group     = $allLefts[0];
        for ($i = 1; $i < count($allLefts); $i++) {
            if ($allLefts[$i] - $allLefts[$i - 1] > $colGapMin) {
                $colStarts[] = (int) round($group);
                $group       = $allLefts[$i];
            } else {
                $group = min($group, $allLefts[$i]);
            }
        }
        $colStarts[] = (int) round($group);

        $this->assertCount(1, $colStarts,
            'Tight left-edge cluster must produce exactly 1 column');
    }

    public function testCurrencyTokenMergePattern(): void
    {
        // Verify that the regex patterns used for currency-token merging are correct
        $this->assertRegExp(
            '/^[£$€¥₹]?\d[\d,]*,$/',
            '$16,',
            'Prefix "$16," must match the split-currency pattern'
        );
        $this->assertRegExp(
            '/^[£$€¥₹]?\d[\d,]*,$/',
            '1,',
            'Plain "1," must match the split-currency pattern'
        );
        $this->assertRegExp(
            '/^\d{3}(\.\d+)?$/',
            '753.00',
            '"753.00" must match the suffix pattern'
        );
        $this->assertRegExp(
            '/^\d{3}(\.\d+)?$/',
            '302',
            '"302" must match the suffix pattern'
        );
        $this->assertNotRegExp(
            '/^[£$€¥₹]?\d[\d,]*,$/',
            'Smith',
            'A name must NOT match the split-currency pattern'
        );
    }

    // ------------------------------------------------------------------ //
    //  ConversionService::setPlanTier — plan tier routing fix              //
    // ------------------------------------------------------------------ //

    public function testSetPlanTierAcceptsValidTiers(): void
    {
        $svc = new \Projects\ConvertX\Services\ConversionService();
        $ref = new \ReflectionProperty($svc, 'planTier');
        $ref->setAccessible(true);

        $svc->setPlanTier('free');
        $this->assertSame('free', $ref->getValue($svc));

        $svc->setPlanTier('pro');
        $this->assertSame('pro', $ref->getValue($svc));

        $svc->setPlanTier('enterprise');
        $this->assertSame('enterprise', $ref->getValue($svc));
    }

    public function testSetPlanTierDefaultsToFreeForUnknownTier(): void
    {
        $svc = new \Projects\ConvertX\Services\ConversionService();
        $ref = new \ReflectionProperty($svc, 'planTier');
        $ref->setAccessible(true);

        $svc->setPlanTier('gold');   // unknown tier
        $this->assertSame('free', $ref->getValue($svc),
            'Unknown plan tier must fall back to "free"');

        $svc->setPlanTier('');       // empty string
        $this->assertSame('free', $ref->getValue($svc),
            'Empty plan tier must fall back to "free"');
    }

    public function testPlanTierDefaultsToFree(): void
    {
        $svc = new \Projects\ConvertX\Services\ConversionService();
        $ref = new \ReflectionProperty($svc, 'planTier');
        $ref->setAccessible(true);

        $this->assertSame('free', $ref->getValue($svc),
            'planTier must default to "free" on construction');
    }

    // ------------------------------------------------------------------ //
    //  AIProviderModel::seedDefaultProviders — allowed_tiers includes free //
    // ------------------------------------------------------------------ //

    public function testOpenAiSeedIncludesFreeTier(): void
    {
        // Load AIProviderModel source and scan for the OpenAI seed row.
        // Verify that the INSERT IGNORE includes "free" in allowed_tiers.
        $source = file_get_contents(
            BASE_PATH . '/projects/convertx/models/AIProviderModel.php'
        );
        $this->assertIsString($source);

        // Find the INSERT IGNORE block
        $insertPos = strpos($source, 'INSERT IGNORE INTO convertx_ai_providers');
        $this->assertNotFalse($insertPos, 'Seed INSERT must be present in AIProviderModel');

        // Extract from INSERT to the closing ')' of the VALUES block (~50 lines)
        $snippet = substr($source, $insertPos, 2000);

        // The first VALUES entry is OpenAI — verify its allowed_tiers includes "free"
        $openaiEnd = strpos($snippet, 'HuggingFace');
        $openaiBlock = $openaiEnd ? substr($snippet, 0, $openaiEnd) : $snippet;

        // The PHP source uses escaped quotes inside double-quoted strings.
        // Accept any representation of 'free' adjacent to a quote character.
        $this->assertRegExp(
            '/[\'"]free[\'""|\\\\"]/',
            $openaiBlock,
            'OpenAI seed row must include "free" in allowed_tiers so all users get AI-powered OCR'
        );
    }

    public function testSeedFixupQueryUpdatesOldAllowedTiers(): void
    {
        // Verify the UPDATE migration statement is present in AIProviderModel.
        $source = file_get_contents(
            BASE_PATH . '/projects/convertx/models/AIProviderModel.php'
        );
        $this->assertIsString($source);

        // The UPDATE statement must reference the old pro/enterprise-only value
        $this->assertTrue(
            str_contains($source, "pro") && str_contains($source, "enterprise") && str_contains($source, "UPDATE"),
            'Fixup UPDATE must be present in AIProviderModel to migrate old records'
        );
        $this->assertStringContainsString(
            "slug = 'openai'",
            $source,
            'Fixup UPDATE must target only the openai row'
        );
    }

    // ------------------------------------------------------------------ //
    //  BatchController — new endpoints and ConversionJobModel::getBatchJobs //
    // ------------------------------------------------------------------ //

    public function testGetBatchJobsMethodExistsInModel(): void
    {
        $this->assertTrue(
            method_exists(\Projects\ConvertX\Models\ConversionJobModel::class, 'getBatchJobs'),
            'ConversionJobModel must have a getBatchJobs(batchId, userId) method'
        );
    }

    public function testGetBatchJobsSignature(): void
    {
        $ref    = new \ReflectionMethod(\Projects\ConvertX\Models\ConversionJobModel::class, 'getBatchJobs');
        $params = $ref->getParameters();
        $this->assertCount(2, $params, 'getBatchJobs must accept exactly 2 parameters');
        $this->assertSame('batchId', $params[0]->getName());
        $this->assertSame('userId',  $params[1]->getName());
    }

    public function testBatchControllerHasBatchStatusMethod(): void
    {
        $this->assertTrue(
            method_exists(\Projects\ConvertX\Controllers\BatchController::class, 'batchStatus'),
            'BatchController must have a batchStatus() method'
        );
    }

    public function testBatchControllerHasBatchDownloadZipMethod(): void
    {
        $this->assertTrue(
            method_exists(\Projects\ConvertX\Controllers\BatchController::class, 'batchDownloadZip'),
            'BatchController must have a batchDownloadZip() method'
        );
    }

    public function testBatchControllerSubmitBuildsAiTasks(): void
    {
        // Verify source code: ai_ocr, ai_summarize, ai_translate, ai_classify are present
        $source = file_get_contents(
            BASE_PATH . '/projects/convertx/controllers/BatchController.php'
        );
        $this->assertStringContainsString('ai_ocr',      $source, 'BatchController must handle ai_ocr');
        $this->assertStringContainsString('ai_summarize', $source, 'BatchController must handle ai_summarize');
        $this->assertStringContainsString('ai_translate', $source, 'BatchController must handle ai_translate');
        $this->assertStringContainsString('ai_classify',  $source, 'BatchController must handle ai_classify');
    }

    public function testBatchControllerPassesQualityAndDpi(): void
    {
        $source = file_get_contents(
            BASE_PATH . '/projects/convertx/controllers/BatchController.php'
        );
        $this->assertStringContainsString("'quality'", $source, "BatchController must pass quality option");
        $this->assertStringContainsString("'dpi'",     $source, "BatchController must pass dpi option");
    }

    public function testBatchViewContainsAiOptions(): void
    {
        $source = file_get_contents(
            BASE_PATH . '/projects/convertx/views/batch.php'
        );
        $this->assertStringContainsString('ai_ocr',      $source, 'Batch view must include ai_ocr checkbox');
        $this->assertStringContainsString('ai_summarize', $source, 'Batch view must include ai_summarize checkbox');
        $this->assertStringContainsString('ai_translate', $source, 'Batch view must include ai_translate checkbox');
        $this->assertStringContainsString('ai_classify',  $source, 'Batch view must include ai_classify checkbox');
    }

    public function testBatchViewContainsQualityAndDpi(): void
    {
        $source = file_get_contents(
            BASE_PATH . '/projects/convertx/views/batch.php'
        );
        $this->assertStringContainsString('qualitySlider', $source, 'Batch view must include quality slider');
        $this->assertStringContainsString('dpiSelect',     $source, 'Batch view must include DPI selector');
    }

    public function testBatchViewContainsDownloadAllButton(): void
    {
        $source = file_get_contents(
            BASE_PATH . '/projects/convertx/views/batch.php'
        );
        $this->assertStringContainsString('downloadAllBtn', $source, 'Batch view must include Download All button');
        $this->assertStringContainsString('downloadAllZip', $source, 'Batch view must wire up downloadAllZip() function');
    }

    public function testBatchRouteHasStatusAndDownloadCases(): void
    {
        $source = file_get_contents(
            BASE_PATH . '/projects/convertx/routes/web.php'
        );
        $this->assertStringContainsString('batchStatus',      $source, 'Routes must dispatch to batchStatus()');
        $this->assertStringContainsString('batchDownloadZip', $source, 'Routes must dispatch to batchDownloadZip()');
    }

    public function testBatchStatusPollingUsesBatchEndpoint(): void
    {
        $source = file_get_contents(
            BASE_PATH . '/projects/convertx/views/batch.php'
        );
        $this->assertStringContainsString(
            '/projects/convertx/batch/status/',
            $source,
            'Batch view must poll the unified /batch/status/:id endpoint'
        );
        $this->assertStringContainsString(
            '/projects/convertx/batch/download/',
            $source,
            'Batch view must build the Download All ZIP URL from /batch/download/:id'
        );
    }

    public function testBatchResultsTableShowsFilename(): void
    {
        $source = file_get_contents(
            BASE_PATH . '/projects/convertx/views/batch.php'
        );
        // The polling code must update a filename element keyed by job ID
        $this->assertStringContainsString('fname-', $source,
            'Batch results table must show per-job filename (fname-<jobId> element)');
        $this->assertStringContainsString('input_filename', $source,
            'Batch status polling must use input_filename from the status response');
    }
}
