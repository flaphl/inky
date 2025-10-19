<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\Tests\Engine;

use PHPUnit\Framework\TestCase;
use Flaphl\Fridge\Inky\Engine\Loader;
use Flaphl\Fridge\Inky\Exception\InkyException;

class LoaderTest extends TestCase
{
    private string $tempDir;
    private Loader $loader;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/inky_tests_' . uniqid();
        mkdir($this->tempDir, 0777, true);
        $this->loader = new Loader([$this->tempDir]);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    public function testLoaderAcceptsValidPath(): void
    {
        $this->assertInstanceOf(Loader::class, $this->loader);
    }

    public function testLoaderThrowsExceptionForInvalidPath(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Loader(['/nonexistent/path/that/does/not/exist']);
    }

    public function testGetSourceReturnsFileContents(): void
    {
        $content = 'Hello, {{ name }}!';
        file_put_contents($this->tempDir . '/test.inky', $content);
        
        $source = $this->loader->getSource('test.inky');
        $this->assertEquals($content, $source);
    }

    public function testGetSourceThrowsExceptionForNonExistentTemplate(): void
    {
        $this->expectException(InkyException::class);
        $this->loader->getSource('nonexistent.inky');
    }

    public function testExistsReturnsTrueForExistingTemplate(): void
    {
        file_put_contents($this->tempDir . '/exists.inky', 'content');
        $this->assertTrue($this->loader->exists('exists.inky'));
    }

    public function testExistsReturnsFalseForNonExistentTemplate(): void
    {
        $this->assertFalse($this->loader->exists('does_not_exist.inky'));
    }

    public function testGetCacheKeyReturnsPath(): void
    {
        file_put_contents($this->tempDir . '/cache.inky', 'content');
        $cacheKey = $this->loader->getCacheKey('cache.inky');
        $this->assertStringContainsString('cache.inky', $cacheKey);
    }

    public function testIsFreshReturnsTrueForUnmodifiedTemplate(): void
    {
        $file = $this->tempDir . '/fresh.inky';
        file_put_contents($file, 'content');
        $time = time() + 100; // Future time
        
        $this->assertTrue($this->loader->isFresh('fresh.inky', $time));
    }

    public function testIsFreshReturnsFalseForModifiedTemplate(): void
    {
        $file = $this->tempDir . '/stale.inky';
        file_put_contents($file, 'content');
        $time = time() - 100; // Past time
        
        $this->assertFalse($this->loader->isFresh('stale.inky', $time));
    }

    public function testGetPathReturnsFullPath(): void
    {
        file_put_contents($this->tempDir . '/path.inky', 'content');
        $path = $this->loader->getPath('path.inky');
        $this->assertStringEndsWith('path.inky', $path);
        $this->assertFileExists($path);
    }

    public function testGetPathThrowsExceptionForNonExistent(): void
    {
        $this->expectException(InkyException::class);
        $this->loader->getPath('missing.inky');
    }

    public function testAddPathAddsNewSearchPath(): void
    {
        $newDir = sys_get_temp_dir() . '/inky_new_' . uniqid();
        mkdir($newDir, 0777, true);
        
        file_put_contents($newDir . '/new.inky', 'new content');
        
        $this->loader->addPath($newDir);
        $this->assertTrue($this->loader->exists('new.inky'));
        
        $this->removeDirectory($newDir);
    }

    public function testAddPathThrowsExceptionForNonDirectory(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->loader->addPath('/not/a/directory');
    }

    public function testGetPathsReturnsAllPaths(): void
    {
        $paths = $this->loader->getPaths();
        $this->assertIsArray($paths);
        $this->assertNotEmpty($paths);
        $this->assertContains(realpath($this->tempDir), $paths);
    }

    public function testMultiplePathsSearch(): void
    {
        $dir1 = sys_get_temp_dir() . '/inky_1_' . uniqid();
        $dir2 = sys_get_temp_dir() . '/inky_2_' . uniqid();
        mkdir($dir1, 0777, true);
        mkdir($dir2, 0777, true);
        
        file_put_contents($dir2 . '/multi.inky', 'content');
        
        $loader = new Loader([$dir1, $dir2]);
        $this->assertTrue($loader->exists('multi.inky'));
        
        $this->removeDirectory($dir1);
        $this->removeDirectory($dir2);
    }

    public function testAbsolutePathTemplate(): void
    {
        $absolutePath = $this->tempDir . '/absolute.inky';
        file_put_contents($absolutePath, 'absolute content');
        
        $this->assertTrue($this->loader->exists($absolutePath));
        $this->assertEquals('absolute content', $this->loader->getSource($absolutePath));
    }

    public function testCachingGetPath(): void
    {
        file_put_contents($this->tempDir . '/cached.inky', 'content');
        
        // First call
        $path1 = $this->loader->getPath('cached.inky');
        // Second call (should use cache)
        $path2 = $this->loader->getPath('cached.inky');
        
        $this->assertEquals($path1, $path2);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            
            $path = $dir . DIRECTORY_SEPARATOR . $item;
            if (is_dir($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        
        rmdir($dir);
    }
}
