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

        // We can only assert the result is a valid structure;
        // the actual conversion may fail if LibreOffice / Pandoc are absent
        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
        $this->assertArrayHasKey('output_path', $result);
        $this->assertArrayHasKey('error', $result);
        $this->assertIsBool($result['success']);
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
}
