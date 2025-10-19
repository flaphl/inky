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
 * Cache implementation for compiled templates.
 */
class Cache implements CacheInterface
{
    private array $cache = [];
    private array $timestamps = [];

    /**
     * Create a new cache instance.
     *
     * @param string|null $directory Cache directory
     */
    public function __construct(
        private readonly ?string $directory = null
    ) {
        if ($directory !== null && !is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $key): ?string
    {
        // Check memory cache first
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        // Check file cache
        if ($this->directory !== null) {
            $file = $this->getFilePath($key);
            if (is_file($file)) {
                $content = file_get_contents($file);
                $this->cache[$key] = $content;
                $this->timestamps[$key] = filemtime($file);
                return $content;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function set(string $key, string $value): void
    {
        $this->cache[$key] = $value;
        $this->timestamps[$key] = time();

        // Write to file cache
        if ($this->directory !== null) {
            $file = $this->getFilePath($key);
            $dir = dirname($file);
            
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
            
            file_put_contents($file, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        if (isset($this->cache[$key])) {
            return true;
        }

        if ($this->directory !== null) {
            return is_file($this->getFilePath($key));
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getTimestamp(string $key): ?int
    {
        if (isset($this->timestamps[$key])) {
            return $this->timestamps[$key];
        }

        if ($this->directory !== null) {
            $file = $this->getFilePath($key);
            if (is_file($file)) {
                return filemtime($file);
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): bool
    {
        unset($this->cache[$key], $this->timestamps[$key]);

        if ($this->directory !== null) {
            $file = $this->getFilePath($key);
            if (is_file($file)) {
                return unlink($file);
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function clear(): bool
    {
        $this->cache = [];
        $this->timestamps = [];

        if ($this->directory !== null && is_dir($this->directory)) {
            $this->clearDirectory($this->directory);
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDirectory(): string
    {
        return $this->directory ?? '';
    }

    /**
     * Get file path for cache key.
     *
     * @param string $key Cache key
     *
     * @return string File path
     */
    private function getFilePath(string $key): string
    {
        $hash = hash('xxh128', $key);
        return $this->directory . DIRECTORY_SEPARATOR . substr($hash, 0, 2) . DIRECTORY_SEPARATOR . $hash . '.php';
    }

    /**
     * Recursively clear a directory.
     *
     * @param string $directory Directory path
     *
     * @return void
     */
    private function clearDirectory(string $directory): void
    {
        if (!is_dir($directory)) {
            return;
        }

        $items = scandir($directory);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $path = $directory . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->clearDirectory($path);
                rmdir($path);
            } else {
                unlink($path);
            }
        }
    }
}
