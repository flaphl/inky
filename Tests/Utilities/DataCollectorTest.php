<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\Tests\Utilities;

use PHPUnit\Framework\TestCase;
use Flaphl\Fridge\Inky\Utilities\DataCollector;

class DataCollectorTest extends TestCase
{
    private DataCollector $collector;

    protected function setUp(): void
    {
        $this->collector = new DataCollector();
    }

    public function testConstruction(): void
    {
        $this->assertInstanceOf(DataCollector::class, $this->collector);
    }

    public function testRecordRender(): void
    {
        $this->collector->recordRender('test.inky', 0.05);
        $stats = $this->collector->getStatistics();
        
        $this->assertEquals(1, $stats['total_renders']);
        $this->assertArrayHasKey('test.inky', $stats['templates']);
    }

    public function testRecordMultipleRenders(): void
    {
        $this->collector->recordRender('test.inky', 0.05);
        $this->collector->recordRender('test.inky', 0.03);
        $this->collector->recordRender('other.inky', 0.02);
        
        $stats = $this->collector->getStatistics();
        $this->assertEquals(3, $stats['total_renders']);
    }

    public function testCalculateTotalTime(): void
    {
        $this->collector->recordRender('test.inky', 0.05);
        $this->collector->recordRender('test.inky', 0.03);
        
        $stats = $this->collector->getStatistics();
        $this->assertEquals(0.08, $stats['total_time']);
    }

    public function testCalculateAverageTime(): void
    {
        $this->collector->recordRender('test.inky', 0.06);
        $this->collector->recordRender('test.inky', 0.04);
        
        $stats = $this->collector->getStatistics();
        $this->assertEquals(0.05, $stats['average_time']);
    }

    public function testTrackPerTemplateStats(): void
    {
        $this->collector->recordRender('test.inky', 0.05);
        $this->collector->recordRender('test.inky', 0.03);
        
        $stats = $this->collector->getStatistics();
        $templateStats = $stats['templates']['test.inky'];
        
        $this->assertEquals(2, $templateStats['count']);
        $this->assertEquals(0.08, $templateStats['total_time']);
        $this->assertEquals(0.04, $templateStats['average_time']);
    }

    public function testRecordCompile(): void
    {
        $this->collector->recordCompile('test.inky', 0.1);
        $stats = $this->collector->getStatistics();
        
        $this->assertEquals(1, $stats['total_compiles']);
        $this->assertEquals(0.1, $stats['compile_time']);
    }

    public function testRecordCacheHit(): void
    {
        $this->collector->recordCacheHit('test.inky');
        $stats = $this->collector->getStatistics();
        
        $this->assertEquals(1, $stats['cache_hits']);
    }

    public function testRecordCacheMiss(): void
    {
        $this->collector->recordCacheMiss('test.inky');
        $stats = $this->collector->getStatistics();
        
        $this->assertEquals(1, $stats['cache_misses']);
    }

    public function testCalculateCacheHitRate(): void
    {
        $this->collector->recordCacheHit('test.inky');
        $this->collector->recordCacheHit('other.inky');
        $this->collector->recordCacheMiss('third.inky');
        
        $stats = $this->collector->getStatistics();
        $this->assertEquals(66.67, round($stats['cache_hit_rate'], 2));
    }

    public function testGetSlowestTemplates(): void
    {
        $this->collector->recordRender('slow.inky', 0.5);
        $this->collector->recordRender('fast.inky', 0.01);
        $this->collector->recordRender('medium.inky', 0.1);
        
        $slowest = $this->collector->getSlowestTemplates(2);
        
        $this->assertCount(2, $slowest);
        $this->assertEquals('slow.inky', $slowest[0]['name']);
        $this->assertEquals('medium.inky', $slowest[1]['name']);
    }

    public function testGetMostRendered(): void
    {
        $this->collector->recordRender('popular.inky', 0.01);
        $this->collector->recordRender('popular.inky', 0.01);
        $this->collector->recordRender('popular.inky', 0.01);
        $this->collector->recordRender('rare.inky', 0.01);
        
        $mostRendered = $this->collector->getMostRendered(1);
        
        $this->assertCount(1, $mostRendered);
        $this->assertEquals('popular.inky', $mostRendered[0]['name']);
        $this->assertEquals(3, $mostRendered[0]['count']);
    }

    public function testReset(): void
    {
        $this->collector->recordRender('test.inky', 0.05);
        $this->collector->recordCompile('test.inky', 0.1);
        $this->collector->recordCacheHit('test.inky');
        
        $this->collector->reset();
        $stats = $this->collector->getStatistics();
        
        $this->assertEquals(0, $stats['total_renders']);
        $this->assertEquals(0, $stats['total_compiles']);
        $this->assertEquals(0, $stats['cache_hits']);
    }

    public function testGetMemoryUsage(): void
    {
        $stats = $this->collector->getStatistics();
        
        $this->assertArrayHasKey('memory_usage', $stats);
        $this->assertIsInt($stats['memory_usage']);
        $this->assertGreaterThan(0, $stats['memory_usage']);
    }

    public function testGetPeakMemoryUsage(): void
    {
        $stats = $this->collector->getStatistics();
        
        $this->assertArrayHasKey('peak_memory', $stats);
        $this->assertIsInt($stats['peak_memory']);
        $this->assertGreaterThan(0, $stats['peak_memory']);
    }
}
