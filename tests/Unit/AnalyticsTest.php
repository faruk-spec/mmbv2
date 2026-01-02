<?php
/**
 * Analytics Class Unit Tests
 * 
 * Tests for the Analytics tracking class
 * Part of Phase 12: Testing & Quality Assurance
 */

use PHPUnit\Framework\TestCase;
use Core\Analytics;
use Core\Cache;

class AnalyticsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clear cache before each test
        Cache::clear();
    }
    
    protected function tearDown(): void
    {
        // Clear cache after each test
        Cache::clear();
        parent::tearDown();
    }
    
    public function testTrackEvent()
    {
        $event = 'test_event';
        $data = ['key' => 'value'];
        $userId = 123;
        
        $result = Analytics::track($event, $data, $userId);
        
        $this->assertTrue($result, 'Analytics::track should return true');
        
        // Verify event is queued in cache
        $cacheKey = 'analytics_queue_' . date('YmdH');
        $queue = Cache::get($cacheKey, []);
        
        $this->assertIsArray($queue);
        $this->assertNotEmpty($queue);
        
        $lastEvent = end($queue);
        $this->assertEquals($event, $lastEvent['event']);
        $this->assertEquals($userId, $lastEvent['user_id']);
    }
    
    public function testTrackDownload()
    {
        $fileId = 456;
        $userId = 789;
        
        $result = Analytics::trackDownload($fileId, $userId);
        
        $this->assertTrue($result);
        
        // Verify download event is tracked
        $cacheKey = 'analytics_queue_' . date('YmdH');
        $queue = Cache::get($cacheKey, []);
        
        $this->assertNotEmpty($queue);
        
        $lastEvent = end($queue);
        $this->assertEquals('file_download', $lastEvent['event']);
        
        $eventData = json_decode($lastEvent['data'], true);
        $this->assertEquals($fileId, $eventData['file_id']);
    }
    
    public function testTrackPageView()
    {
        $page = 'test_page';
        $userId = 111;
        
        $result = Analytics::trackPageView($page, $userId);
        
        $this->assertTrue($result);
        
        // Verify page view event is tracked
        $cacheKey = 'analytics_queue_' . date('YmdH');
        $queue = Cache::get($cacheKey, []);
        
        $this->assertNotEmpty($queue);
        
        $lastEvent = end($queue);
        $this->assertEquals('page_view', $lastEvent['event']);
    }
    
    public function testGetSummary()
    {
        $startDate = date('Y-m-d', strtotime('-7 days'));
        $endDate = date('Y-m-d');
        
        $summary = Analytics::getSummary($startDate, $endDate);
        
        $this->assertIsArray($summary);
        $this->assertArrayHasKey('total_events', $summary);
        $this->assertArrayHasKey('unique_users', $summary);
        $this->assertArrayHasKey('top_events', $summary);
        $this->assertArrayHasKey('timeline', $summary);
    }
    
    public function testGetDownloadStats()
    {
        $fileId = 123;
        $days = 7;
        
        $stats = Analytics::getDownloadStats($fileId, $days);
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_downloads', $stats);
        $this->assertArrayHasKey('unique_downloaders', $stats);
        $this->assertArrayHasKey('downloads_by_day', $stats);
        $this->assertArrayHasKey('downloads_by_country', $stats);
        $this->assertArrayHasKey('downloads_by_browser', $stats);
        $this->assertArrayHasKey('downloads_by_platform', $stats);
    }
    
    public function testGetDownloadStatsWithoutFileId()
    {
        $days = 30;
        
        $stats = Analytics::getDownloadStats(null, $days);
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_downloads', $stats);
    }
    
    public function testGenerateReport()
    {
        $report = Analytics::generateReport('daily', 'html');
        
        $this->assertIsArray($report);
        $this->assertArrayHasKey('type', $report);
        $this->assertArrayHasKey('generated_at', $report);
        $this->assertEquals('daily', $report['type']);
    }
    
    public function testGenerateReportCSV()
    {
        $report = Analytics::generateReport('weekly', 'csv');
        
        $this->assertIsArray($report);
        $this->assertArrayHasKey('data', $report);
        $this->assertArrayHasKey('filename', $report);
        $this->assertIsString($report['data']);
        $this->assertStringContainsString('.csv', $report['filename']);
    }
    
    public function testGenerateReportJSON()
    {
        $report = Analytics::generateReport('monthly', 'json');
        
        $this->assertIsArray($report);
        $this->assertArrayHasKey('data', $report);
        
        $decoded = json_decode($report['data'], true);
        $this->assertIsArray($decoded);
    }
    
    public function testFlush()
    {
        // Add some events to queue
        Analytics::track('event1', ['data' => 'test1']);
        Analytics::track('event2', ['data' => 'test2']);
        Analytics::track('event3', ['data' => 'test3']);
        
        $flushed = Analytics::flush();
        
        $this->assertIsInt($flushed);
        $this->assertGreaterThanOrEqual(3, $flushed);
        
        // Queue should be empty after flush
        $cacheKey = 'analytics_queue_' . date('YmdH');
        $queue = Cache::get($cacheKey, []);
        $this->assertEmpty($queue);
    }
    
    public function testFlushEmptyQueue()
    {
        $flushed = Analytics::flush();
        
        $this->assertEquals(0, $flushed);
    }
    
    public function testMultipleEvents()
    {
        Analytics::track('event1', ['key1' => 'value1'], 1);
        Analytics::track('event2', ['key2' => 'value2'], 2);
        Analytics::track('event3', ['key3' => 'value3'], 3);
        
        $cacheKey = 'analytics_queue_' . date('YmdH');
        $queue = Cache::get($cacheKey, []);
        
        $this->assertCount(3, $queue);
        $this->assertEquals('event1', $queue[0]['event']);
        $this->assertEquals('event2', $queue[1]['event']);
        $this->assertEquals('event3', $queue[2]['event']);
    }
}
