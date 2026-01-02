<?php
/**
 * Cache Class Unit Tests
 * 
 * Tests for the Cache utility class
 * Part of Phase 12: Testing & Quality Assurance
 */

use PHPUnit\Framework\TestCase;
use Core\Cache;

class CacheTest extends TestCase
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
    
    public function testSetAndGet()
    {
        $key = 'test_key';
        $value = 'test_value';
        
        $result = Cache::set($key, $value, 3600);
        $this->assertTrue($result, 'Cache::set should return true');
        
        $retrieved = Cache::get($key);
        $this->assertEquals($value, $retrieved, 'Retrieved value should match stored value');
    }
    
    public function testGetWithDefault()
    {
        $key = 'non_existent_key';
        $default = 'default_value';
        
        $retrieved = Cache::get($key, $default);
        $this->assertEquals($default, $retrieved, 'Should return default for non-existent key');
    }
    
    public function testHas()
    {
        $key = 'test_key';
        $value = 'test_value';
        
        $this->assertFalse(Cache::has($key), 'Key should not exist initially');
        
        Cache::set($key, $value, 3600);
        $this->assertTrue(Cache::has($key), 'Key should exist after setting');
    }
    
    public function testDelete()
    {
        $key = 'test_key';
        $value = 'test_value';
        
        Cache::set($key, $value, 3600);
        $this->assertTrue(Cache::has($key), 'Key should exist after setting');
        
        Cache::delete($key);
        $this->assertFalse(Cache::has($key), 'Key should not exist after deletion');
    }
    
    public function testExpiration()
    {
        $key = 'test_key';
        $value = 'test_value';
        $ttl = 1; // 1 second
        
        Cache::set($key, $value, $ttl);
        $this->assertTrue(Cache::has($key), 'Key should exist immediately');
        
        // Wait for expiration
        sleep(2);
        
        $this->assertFalse(Cache::has($key), 'Key should expire after TTL');
        $this->assertNull(Cache::get($key), 'Expired key should return null');
    }
    
    public function testRemember()
    {
        $key = 'test_remember';
        $callCount = 0;
        
        $callback = function() use (&$callCount) {
            $callCount++;
            return 'computed_value';
        };
        
        // First call should execute callback
        $value1 = Cache::remember($key, $callback, 3600);
        $this->assertEquals('computed_value', $value1);
        $this->assertEquals(1, $callCount, 'Callback should be called once');
        
        // Second call should use cached value
        $value2 = Cache::remember($key, $callback, 3600);
        $this->assertEquals('computed_value', $value2);
        $this->assertEquals(1, $callCount, 'Callback should not be called again');
    }
    
    public function testClear()
    {
        Cache::set('key1', 'value1', 3600);
        Cache::set('key2', 'value2', 3600);
        
        $this->assertTrue(Cache::has('key1'));
        $this->assertTrue(Cache::has('key2'));
        
        $cleared = Cache::clear();
        $this->assertGreaterThanOrEqual(2, $cleared, 'Should clear at least 2 entries');
        
        $this->assertFalse(Cache::has('key1'));
        $this->assertFalse(Cache::has('key2'));
    }
    
    public function testStats()
    {
        Cache::set('key1', 'value1', 3600);
        Cache::set('key2', 'value2', 3600);
        
        $stats = Cache::stats();
        
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_entries', $stats);
        $this->assertArrayHasKey('active_entries', $stats);
        $this->assertArrayHasKey('expired_entries', $stats);
        $this->assertArrayHasKey('total_size', $stats);
        $this->assertArrayHasKey('total_size_mb', $stats);
        
        $this->assertGreaterThanOrEqual(2, $stats['total_entries']);
    }
    
    public function testComplexDataTypes()
    {
        // Test with array
        $arrayData = ['key1' => 'value1', 'key2' => 'value2'];
        Cache::set('array_test', $arrayData, 3600);
        $this->assertEquals($arrayData, Cache::get('array_test'));
        
        // Test with object
        $objectData = (object)['prop1' => 'value1', 'prop2' => 'value2'];
        Cache::set('object_test', $objectData, 3600);
        $this->assertEquals($objectData, Cache::get('object_test'));
        
        // Test with nested array
        $nestedData = [
            'level1' => [
                'level2' => [
                    'value' => 'deep_value'
                ]
            ]
        ];
        Cache::set('nested_test', $nestedData, 3600);
        $this->assertEquals($nestedData, Cache::get('nested_test'));
    }
    
    public function testCleanup()
    {
        // Create some expired entries
        Cache::set('expired1', 'value1', 1);
        Cache::set('expired2', 'value2', 1);
        Cache::set('valid', 'value3', 3600);
        
        sleep(2); // Wait for expiration
        
        $cleaned = Cache::cleanup();
        $this->assertGreaterThanOrEqual(2, $cleaned, 'Should clean up expired entries');
        
        // Valid entry should still exist
        $this->assertTrue(Cache::has('valid'));
        $this->assertFalse(Cache::has('expired1'));
        $this->assertFalse(Cache::has('expired2'));
    }
}
