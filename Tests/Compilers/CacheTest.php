<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\Tests\Compilers;

use PHPUnit\Framework\TestCase;
use Flaphl\Fridge\Inky\Compilers\Cache;

class CacheTest extends TestCase
{
    private string $cacheDir;
    private Cache $cache;

    protected function setUp(): void
    {
        $this->cacheDir = sys_get_temp_dir() . '/inky_cache_test_' . uniqid();
        $this->cache = new Cache($this->cacheDir);
    }

    protected function tearDown(): void
    {
        $this->cache->clear();
        if (is_dir($this->cacheDir)) {
            rmdir($this->cacheDir);
        }
    }

    public function testCacheConstruction(): void
    {
        $this->assertInstanceOf(Cache::class, $this->cache);
    }

    public function testCacheCreatesDirectory(): void
    {
        $this->assertDirectoryExists($this->cacheDir);
    }

    public function testSetAndGet(): void
    {
        $key = 'test_key';
        $value = '<?php echo "test"; ?>';
        
        $this->cache->set($key, $value);
        $this->assertEquals($value, $this->cache->get($key));
    }

    public function testGetNonExistentReturnsNull(): void
    {
        $this->assertNull($this->cache->get('nonexistent_key'));
    }

    public function testHasReturnsTrueForExisting(): void
    {
        $this->cache->set('existing', 'value');
        $this->assertTrue($this->cache->has('existing'));
    }

    public function testHasReturnsFalseForNonExistent(): void
    {
        $this->assertFalse($this->cache->has('nonexistent'));
    }

    public function testGetTimestamp(): void
    {
        $this->cache->set('timed', 'value');
        $timestamp = $this->cache->getTimestamp('timed');
        
        $this->assertIsInt($timestamp);
        $this->assertGreaterThan(0, $timestamp);
        $this->assertLessThanOrEqual(time(), $timestamp);
    }

    public function testGetTimestampNonExistentReturnsNull(): void
    {
        $this->assertNull($this->cache->getTimestamp('nonexistent'));
    }

    public function testDelete(): void
    {
        $this->cache->set('deleteme', 'value');
        $this->assertTrue($this->cache->has('deleteme'));
        
        $this->assertTrue($this->cache->delete('deleteme'));
        $this->assertFalse($this->cache->has('deleteme'));
    }

    public function testDeleteNonExistent(): void
    {
        $this->assertTrue($this->cache->delete('nonexistent'));
    }

    public function testClear(): void
    {
        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');
        $this->cache->set('key3', 'value3');
        
        $this->assertTrue($this->cache->clear());
        
        $this->assertFalse($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
        $this->assertFalse($this->cache->has('key3'));
    }

    public function testMemoryCacheLayer(): void
    {
        $key = 'memory_test';
        $value = 'cached value';
        
        $this->cache->set($key, $value);
        
        // First get (from file)
        $result1 = $this->cache->get($key);
        // Second get (from memory)
        $result2 = $this->cache->get($key);
        
        $this->assertEquals($value, $result1);
        $this->assertEquals($value, $result2);
    }

    public function testInMemoryCacheOnly(): void
    {
        $cache = new Cache(null);
        
        $cache->set('key', 'value');
        $this->assertTrue($cache->has('key'));
        $this->assertEquals('value', $cache->get('key'));
    }

    public function testMultipleKeys(): void
    {
        $data = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];
        
        foreach ($data as $key => $value) {
            $this->cache->set($key, $value);
        }
        
        foreach ($data as $key => $value) {
            $this->assertEquals($value, $this->cache->get($key));
        }
    }

    public function testCacheKeyHashing(): void
    {
        $longKey = str_repeat('very_long_key_name_', 100);
        $value = 'test value';
        
        $this->cache->set($longKey, $value);
        $this->assertEquals($value, $this->cache->get($longKey));
    }

    public function testDeleteOperations(): void
    {
        $this->cache->set('test', 'value');
        $this->assertTrue($this->cache->has('test'));
        
        $this->cache->delete('test');
        $this->assertFalse($this->cache->has('test'));
    }

    public function testClearOperations(): void
    {
        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');
        
        $this->cache->clear();
        $this->assertFalse($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
    }
}
