<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\Compilers;

/**
 * Caches compiled templates.
 */
interface CacheInterface
{
    /**
     * Get cached compiled template.
     *
     * @param string $key Cache key
     *
     * @return string|null Compiled template or null if not cached
     */
    public function get(string $key): ?string;

    /**
     * Store compiled template in cache.
     *
     * @param string $key Cache key
     * @param string $content Compiled template content
     *
     * @return void
     */
    public function set(string $key, string $content): void;

    /**
     * Check if a compiled template is cached.
     *
     * @param string $key Cache key
     *
     * @return bool True if cached
     */
    public function has(string $key): bool;

    /**
     * Get the timestamp of a cached template.
     *
     * @param string $key Cache key
     *
     * @return int|null Timestamp or null if not cached
     */
    public function getTimestamp(string $key): ?int;

    /**
     * Delete a cached template.
     *
     * @param string $key Cache key
     *
     * @return bool True on success
     */
    public function delete(string $key): bool;

    /**
     * Clear all cached templates.
     *
     * @return bool True on success
     */
    public function clear(): bool;

    /**
     * Get the cache directory path.
     *
     * @return string Cache directory
     */
    public function getCacheDirectory(): string;
}
