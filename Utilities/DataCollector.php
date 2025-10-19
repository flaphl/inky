<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\Utilities;

/**
 * Data collector implementation for template profiling.
 */
class DataCollector
{
    private array $templates = [];
    private array $activeRenders = [];
    private array $cacheStats = [
        'hits' => 0,
        'misses' => 0,
    ];
    private int $totalCompiles = 0;
    private float $compileTime = 0.0;

    /**
     * Record a template render with duration.
     *
     * @param string $name Template name
     * @param float $duration Duration in seconds
     *
     * @return void
     */
    public function recordRender(string $name, float $duration): void
    {
        if (!isset($this->templates[$name])) {
            $this->templates[$name] = [
                'name' => $name,
                'count' => 0,
                'total_time' => 0.0,
                'average_time' => 0.0,
                'min_time' => PHP_FLOAT_MAX,
                'max_time' => 0.0,
                'compiles' => 0,
                'loads' => 0,
            ];
        }

        $this->templates[$name]['count']++;
        $this->templates[$name]['total_time'] += $duration;
        $this->templates[$name]['min_time'] = min($this->templates[$name]['min_time'], $duration);
        $this->templates[$name]['max_time'] = max($this->templates[$name]['max_time'], $duration);
        $this->templates[$name]['average_time'] = 
            $this->templates[$name]['total_time'] / $this->templates[$name]['count'];
    }

    /**
     * Record a cache hit.
     *
     * @param string $name Template name
     *
     * @return void
     */
    public function recordCacheHit(string $name): void
    {
        $this->cacheStats['hits']++;
    }

    /**
     * Record a cache miss.
     *
     * @param string $name Template name
     *
     * @return void
     */
    public function recordCacheMiss(string $name): void
    {
        $this->cacheStats['misses']++;
    }

    /**
     * Get all statistics.
     *
     * @return array Statistics
     */
    public function getStatistics(): array
    {
        $totalRenders = $this->getTotalRenders();
        $totalTime = $this->getTotalDuration();
        $totalCacheRequests = $this->cacheStats['hits'] + $this->cacheStats['misses'];
        $cacheHitRate = $totalCacheRequests > 0 
            ? ($this->cacheStats['hits'] / $totalCacheRequests) * 100
            : 0.0;
        
        return [
            'templates' => $this->templates,
            'total_renders' => $totalRenders,
            'total_time' => $totalTime,
            'average_time' => $totalRenders > 0 ? $totalTime / $totalRenders : 0.0,
            'total_compiles' => $this->totalCompiles,
            'compile_time' => $this->compileTime,
            'cache_hits' => $this->cacheStats['hits'],
            'cache_misses' => $this->cacheStats['misses'],
            'cache_hit_rate' => $cacheHitRate,
            'memory_usage' => memory_get_usage(),
            'peak_memory' => memory_get_peak_usage(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function startRender(string $name, array $variables): void
    {
        $this->activeRenders[$name] = [
            'start' => microtime(true),
            'variables' => $variables,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function endRender(string $name): void
    {
        if (!isset($this->activeRenders[$name])) {
            return;
        }

        $duration = (microtime(true) - $this->activeRenders[$name]['start']) * 1000;
        
        if (!isset($this->templates[$name])) {
            $this->templates[$name] = [
                'name' => $name,
                'count' => 0,
                'total_time' => 0.0,
                'average_time' => 0.0,
                'compiles' => 0,
                'loads' => 0,
            ];
        }

        $this->templates[$name]['count']++;
        $this->templates[$name]['total_time'] += $duration;
        $this->templates[$name]['average_time'] = 
            $this->templates[$name]['total_time'] / $this->templates[$name]['count'];

        unset($this->activeRenders[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function recordLoad(string $name, float $duration): void
    {
        if (!isset($this->templates[$name])) {
            $this->templates[$name] = [
                'name' => $name,
                'count' => 0,
                'total_time' => 0.0,
                'average_time' => 0.0,
                'compiles' => 0,
                'loads' => 0,
            ];
        }

        $this->templates[$name]['loads']++;
    }

    /**
     * {@inheritdoc}
     */
    public function recordCompile(string $name, float $duration): void
    {
        if (!isset($this->templates[$name])) {
            $this->templates[$name] = [
                'name' => $name,
                'count' => 0,
                'total_time' => 0.0,
                'average_time' => 0.0,
                'compiles' => 0,
                'loads' => 0,
            ];
        }

        $this->templates[$name]['compiles']++;
        $this->totalCompiles++;
        $this->compileTime += $duration;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateData(): array
    {
        return $this->templates;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalRenders(): int
    {
        return array_sum(array_column($this->templates, 'count'));
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalDuration(): float
    {
        return array_sum(array_column($this->templates, 'total_time'));
    }

    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->templates = [];
        $this->activeRenders = [];
        $this->cacheStats = ['hits' => 0, 'misses' => 0];
        $this->totalCompiles = 0;
        $this->compileTime = 0.0;
    }

    /**
     * Get template statistics sorted by total duration.
     *
     * @return array<string, array> Sorted template data
     */
    public function getTemplatesByDuration(): array
    {
        $templates = $this->templates;
        uasort($templates, fn($a, $b) => $b['total_time'] <=> $a['total_time']);
        return $templates;
    }

    /**
     * Get template statistics sorted by render count.
     *
     * @return array<string, array> Sorted template data
     */
    public function getTemplatesByRenderCount(): array
    {
        $templates = $this->templates;
        uasort($templates, fn($a, $b) => $b['count'] <=> $a['count']);
        return $templates;
    }

    /**
     * Get slowest templates by average duration.
     *
     * @param int $limit Number of templates to return
     *
     * @return array<int, array> Slowest templates with 'name' keys
     */
    public function getSlowestTemplates(int $limit = 10): array
    {
        $templates = $this->getTemplatesByDuration();
        $result = [];
        
        $count = 0;
        foreach ($templates as $name => $data) {
            if ($count >= $limit) {
                break;
            }
            $result[] = array_merge(['name' => $name], $data);
            $count++;
        }
        
        return $result;
    }

    /**
     * Get most rendered templates.
     *
     * @param int $limit Number of templates to return
     *
     * @return array<int, array> Most rendered templates with 'name' keys
     */
    public function getMostRendered(int $limit = 10): array
    {
        $templates = $this->getTemplatesByRenderCount();
        $result = [];
        
        $count = 0;
        foreach ($templates as $name => $data) {
            if ($count >= $limit) {
                break;
            }
            $result[] = array_merge(['name' => $name], $data);
            $count++;
        }
        
        return $result;
    }
}
