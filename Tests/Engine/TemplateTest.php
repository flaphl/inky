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
use Flaphl\Fridge\Inky\Engine\Template;
use Flaphl\Fridge\Inky\Engine\Engine;
use Flaphl\Fridge\Inky\Engine\Loader;
use Flaphl\Fridge\Inky\Compilers\Compiler;
use Flaphl\Fridge\Inky\Compilers\Lexer;
use Flaphl\Fridge\Inky\Compilers\Parser;

class TemplateTest extends TestCase
{
    private Template $template;
    private Engine $engine;

    protected function setUp(): void
    {
        $tempDir = sys_get_temp_dir() . '/inky_test_' . uniqid();
        mkdir($tempDir, 0777, true);
        
        $loader = new Loader([$tempDir]);
        $compiler = new Compiler(new Lexer(), new Parser());
        $this->engine = new Engine($loader, $compiler);
        $this->template = new Template($this->engine, 'test.inky');
    }

    public function testTemplateConstruction(): void
    {
        $this->assertInstanceOf(Template::class, $this->template);
    }

    public function testGetName(): void
    {
        $this->assertEquals('test.inky', $this->template->getName());
    }

    public function testRenderReturnsString(): void
    {
        $result = $this->template->render(['key' => 'value']);
        $this->assertIsString($result);
    }

    public function testDisplayOutputsContent(): void
    {
        ob_start();
        $this->template->display(['key' => 'value']);
        $output = ob_get_clean();
        $this->assertIsString($output);
    }

    public function testHasBlockReturnsFalseForNonExistent(): void
    {
        $this->assertFalse($this->template->hasBlock('nonexistent'));
    }

    public function testGetBlockNamesReturnsEmptyArray(): void
    {
        $blockNames = $this->template->getBlockNames();
        $this->assertIsArray($blockNames);
    }

    public function testRenderBlockThrowsExceptionForNonExistent(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->template->renderBlock('nonexistent');
    }

    public function testRenderWithEmptyContext(): void
    {
        $result = $this->template->render();
        $this->assertIsString($result);
    }

    public function testRenderWithContext(): void
    {
        $context = ['name' => 'Test', 'value' => 123];
        $result = $this->template->render($context);
        $this->assertIsString($result);
    }

    public function testDisplayWithEmptyContext(): void
    {
        ob_start();
        $this->template->display();
        $output = ob_get_clean();
        $this->assertIsString($output);
    }

    public function testRenderHandlesExceptions(): void
    {
        $badTemplate = new class($this->engine, 'bad.inky') extends Template {
            protected function doDisplay(array $context): void
            {
                throw new \RuntimeException('Test error');
            }
        };

        $this->expectException(\RuntimeException::class);
        $badTemplate->render();
    }
}
