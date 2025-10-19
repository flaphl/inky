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
use Flaphl\Fridge\Inky\Engine\Engine;
use Flaphl\Fridge\Inky\Engine\Loader;
use Flaphl\Fridge\Inky\Engine\ExtensionInterface;
use Flaphl\Fridge\Inky\Compilers\Compiler;
use Flaphl\Fridge\Inky\Compilers\Lexer;
use Flaphl\Fridge\Inky\Compilers\Parser;
use Flaphl\Fridge\Inky\Exception\InkyException;

class EngineTest extends TestCase
{
    private string $tempDir;
    private Engine $engine;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/inky_engine_test_' . uniqid();
        mkdir($this->tempDir, 0777, true);
        
        $loader = new Loader([$this->tempDir]);
        $compiler = new Compiler(new Lexer(), new Parser());
        $this->engine = new Engine($loader, $compiler);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->tempDir);
    }

    public function testEngineConstruction(): void
    {
        $this->assertInstanceOf(Engine::class, $this->engine);
    }

    public function testExistsReturnsTrueForExistingTemplate(): void
    {
        file_put_contents($this->tempDir . '/exists.inky', 'content');
        $this->assertTrue($this->engine->exists('exists.inky'));
    }

    public function testExistsReturnsFalseForNonExistent(): void
    {
        $this->assertFalse($this->engine->exists('nonexistent.inky'));
    }

    public function testRenderThrowsExceptionForNonExistentTemplate(): void
    {
        $this->expectException(InkyException::class);
        $this->engine->render('missing.inky');
    }

    public function testAddGlobalVariable(): void
    {
        $this->engine->addGlobal('test_var', 'test_value');
        $globals = $this->engine->getGlobals();
        $this->assertArrayHasKey('test_var', $globals);
        $this->assertEquals('test_value', $globals['test_var']);
    }

    public function testAddMultipleGlobals(): void
    {
        $this->engine->addGlobal('var1', 'value1');
        $this->engine->addGlobal('var2', 'value2');
        
        $globals = $this->engine->getGlobals();
        $this->assertCount(2, $globals);
    }

    public function testGetGlobalsReturnsEmptyArrayInitially(): void
    {
        $globals = $this->engine->getGlobals();
        $this->assertIsArray($globals);
        $this->assertEmpty($globals);
    }

    public function testAddPath(): void
    {
        $newDir = sys_get_temp_dir() . '/inky_new_path_' . uniqid();
        mkdir($newDir, 0777, true);
        
        $returned = $this->engine->addPath($newDir);
        $this->assertSame($this->engine, $returned);
        
        $this->removeDirectory($newDir);
    }

    public function testGetPaths(): void
    {
        $paths = $this->engine->getPaths();
        $this->assertIsArray($paths);
    }

    public function testSetStrictVariables(): void
    {
        $returned = $this->engine->setStrictVariables(true);
        $this->assertSame($this->engine, $returned);
        $this->assertTrue($this->engine->isStrictVariables());
    }

    public function testIsStrictVariablesDefaultsFalse(): void
    {
        $this->assertFalse($this->engine->isStrictVariables());
    }

    public function testAddExtension(): void
    {
        $extension = new class implements ExtensionInterface {
            public function getName(): string { return 'test'; }
            public function getFilters(): array { return []; }
            public function getFunctions(): array { return []; }
            public function getTests(): array { return []; }
            public function getGlobals(): array { return ['ext_var' => 'ext_value']; }
            public function initialize(\Flaphl\Fridge\Inky\Engine\EngineInterface $engine): void {}
        };
        
        $this->engine->addExtension($extension);
        
        $globals = $this->engine->getGlobals();
        $this->assertArrayHasKey('ext_var', $globals);
    }

    public function testGetExtension(): void
    {
        $extension = new class implements ExtensionInterface {
            public function getName(): string { return 'testget'; }
            public function getFilters(): array { return []; }
            public function getFunctions(): array { return []; }
            public function getTests(): array { return []; }
            public function getGlobals(): array { return []; }
            public function initialize(\Flaphl\Fridge\Inky\Engine\EngineInterface $engine): void {}
        };
        
        $this->engine->addExtension($extension);
        $retrieved = $this->engine->getExtension('testget');
        
        $this->assertSame($extension, $retrieved);
    }

    public function testGetNonExistentExtensionReturnsNull(): void
    {
        $this->assertNull($this->engine->getExtension('nonexistent'));
    }

    public function testGetExtensions(): void
    {
        $ext1 = new class implements ExtensionInterface {
            public function getName(): string { return 'ext1'; }
            public function getFilters(): array { return []; }
            public function getFunctions(): array { return []; }
            public function getTests(): array { return []; }
            public function getGlobals(): array { return []; }
            public function initialize(\Flaphl\Fridge\Inky\Engine\EngineInterface $engine): void {}
        };
        
        $ext2 = new class implements ExtensionInterface {
            public function getName(): string { return 'ext2'; }
            public function getFilters(): array { return []; }
            public function getFunctions(): array { return []; }
            public function getTests(): array { return []; }
            public function getGlobals(): array { return []; }
            public function initialize(\Flaphl\Fridge\Inky\Engine\EngineInterface $engine): void {}
        };
        
        $this->engine->addExtension($ext1);
        $this->engine->addExtension($ext2);
        
        $extensions = $this->engine->getExtensions();
        $this->assertCount(2, $extensions);
        $this->assertArrayHasKey('ext1', $extensions);
        $this->assertArrayHasKey('ext2', $extensions);
    }

    public function testAddGlobalChaining(): void
    {
        $result = $this->engine
            ->addGlobal('key1', 'value1')
            ->addGlobal('key2', 'value2');
            
        $this->assertSame($this->engine, $result);
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
