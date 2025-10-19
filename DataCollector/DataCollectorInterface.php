<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\DataCollector;

/**
 * Collects template rendering data for debugging and profiling.
 */
interface DataCollectorInterface
{
    /**
     * Start collecting data for a template render.
     *
     * @param string $name Template name
     * @param array<string, mixed> $variables Template variables
     *
     * @return void
     */
    public function startRender(string $name, array $variables): void;

    /**
     * End collecting data for a template render.
     *
     * @param string $name Template name
     *
     * @return void
     */
    public function endRender(string $name): void;

    /**
     * Record a template load event.
     *
     * @param string $name Template name
     * @param float $duration Load duration in milliseconds
     *
     * @return void
     */
    public function recordLoad(string $name, float $duration): void;

    /**
     * Record a template compile event.
     *
     * @param string $name Template name
     * @param float $duration Compile duration in milliseconds
     *
     * @return void
     */
    public function recordCompile(string $name, float $duration): void;

    /**
     * Get collected template data.
     *
     * @return array<string, array{
     *     name: string,
     *     renders: int,
     *     totalDuration: float,
     *     avgDuration: float,
     *     compiles: int,
     *     loads: int
     * }> Template statistics
     */
    public function getTemplateData(): array;

    /**
     * Get total number of templates rendered.
     *
     * @return int Total renders
     */
    public function getTotalRenders(): int;

    /**
     * Get total rendering time in milliseconds.
     *
     * @return float Total duration
     */
    public function getTotalDuration(): float;

    /**
     * Reset all collected data.
     *
     * @return void
     */
    public function reset(): void;
}
