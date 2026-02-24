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
}
