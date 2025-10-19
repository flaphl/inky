<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\Engine;

/**
 * Loads templates from storage.
 */
interface LoaderInterface
{
    /**
     * Get the source code of a template.
     *
     * @param string $name Template name/path
     *
     * @return string The template source code
     */
    public function getSource(string $name): string;

    /**
     * Get the cache key for a template.
     *
     * @param string $name Template name/path
     *
     * @return string The cache key
     */
    public function getCacheKey(string $name): string;

    /**
     * Check if the template has been modified since cached.
     *
     * @param string $name Template name/path
     * @param int $time Cached template timestamp
     *
     * @return bool True if template is fresh
     */
    public function isFresh(string $name, int $time): bool;

    /**
     * Check if a template exists.
     *
     * @param string $name Template name/path
     *
     * @return bool True if template exists
     */
    public function exists(string $name): bool;

    /**
     * Get the absolute path to a template.
     *
     * @param string $name Template name/path
     *
     * @return string The absolute file path
     */
    public function getPath(string $name): string;
}
