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

use Flaphl\Fridge\Inky\Exception\InkyException;

/**
 * Default filesystem-based template loader.
 */
class Loader implements LoaderInterface
{
    private array $paths = [];
    private array $cache = [];

    /**
     * Create a new loader.
     *
     * @param array<string> $paths Template directories
     */
    public function __construct(array $paths = [])
    {
        foreach ($paths as $path) {
            $this->addPath($path);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSource(string $name): string
    {
        $path = $this->getPath($name);
        
        if (!is_readable($path)) {
            throw InkyException::loaderError(sprintf('Template "%s" is not readable', $name));
        }

        return file_get_contents($path);
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheKey(string $name): string
    {
        return $this->getPath($name);
    }

    /**
     * {@inheritdoc}
     */
    public function isFresh(string $name, int $time): bool
    {
        return filemtime($this->getPath($name)) <= $time;
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $name): bool
    {
        if (isset($this->cache[$name])) {
            return true;
        }

        try {
            $this->getPath($name);
            return true;
        } catch (InkyException) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPath(string $name): string
    {
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        // If absolute path and exists, use it
        if (str_starts_with($name, '/') && is_file($name)) {
            return $this->cache[$name] = $name;
        }

        // Search in registered paths
        foreach ($this->paths as $path) {
            $file = $path . DIRECTORY_SEPARATOR . $name;
            if (is_file($file)) {
                return $this->cache[$name] = $file;
            }
        }

        throw InkyException::templateNotFound($name, $this->paths);
    }

    /**
     * Add a template search path.
     *
     * @param string $path Directory path
     *
     * @return self
     */
    public function addPath(string $path): self
    {
        if (!is_dir($path)) {
            throw new \InvalidArgumentException(sprintf('Path "%s" is not a directory', $path));
        }

        $this->paths[] = realpath($path);
        return $this;
    }

    /**
     * Get all registered paths.
     *
     * @return array<string> Paths
     */
    public function getPaths(): array
    {
        return $this->paths;
    }
}
