<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Flaphl\Fridge\Inky\Engine\{Engine, Loader, Escaper, Template};
use Flaphl\Fridge\Inky\Compilers\{Compiler, Lexer, Parser, Cache};
use Flaphl\Fridge\Inky\Extensions\CoreExtension;
use Flaphl\Fridge\Inky\Events\{PreRenderEvent, PostRenderEvent};
use Psr\EventDispatcher\EventDispatcherInterface;

class IntegrationTest extends TestCase
{
    private string $tempDir;
    private Engine $engine;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/inky_test_' . uniqid();
        mkdir($this->tempDir);
        mkdir($this->tempDir . '/templates');
        mkdir($this->tempDir . '/cache');
        
        $loader = new Loader([$this->tempDir . '/templates']);
        $this->engine = new Engine($loader);
        $this->engine->setCacheDirectory($this->tempDir . '/cache');
    }

    protected function tearDown(): void
    {
        $this->cleanDirectory($this->tempDir);
    }

    private function cleanDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->cleanDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function testCompleteRenderWorkflow(): void
    {
        file_put_contents($this->tempDir . '/templates/test.inky', 'Hello {{ name }}!');
        
        $output = $this->engine->render('test.inky', ['name' => 'World']);
        
        $this->assertEquals('Hello World!', $output);
    }

    public function testTemplateWithFilters(): void
    {
        $this->engine->registerExtension(new CoreExtension());
        file_put_contents($this->tempDir . '/templates/filter.inky', '{{ name | upper }}');
        
        $output = $this->engine->render('filter.inky', ['name' => 'hello']);
        
        $this->assertEquals('HELLO', $output);
    }

    public function testTemplateWithDirectives(): void
    {
        $template = '{{ @if $show }}Visible{{ @endif }}';
        file_put_contents($this->tempDir . '/templates/directive.inky', $template);
        
        $output = $this->engine->render('directive.inky', ['show' => true]);
        
        $this->assertStringContainsString('Visible', $output);
    }

    public function testTemplateWithLoops(): void
    {
        $template = '{{ @foreach $items as $item }}{{ $item }}{{ @endforeach }}';
        file_put_contents($this->tempDir . '/templates/loop.inky', $template);
        
        $output = $this->engine->render('loop.inky', ['items' => ['a', 'b', 'c']]);
        
        $this->assertStringContainsString('abc', $output);
    }

    public function testTemplateCaching(): void
    {
        file_put_contents($this->tempDir . '/templates/cached.inky', 'Cached {{ name }}');
        
        // First render - compiles and caches
        $output1 = $this->engine->render('cached.inky', ['name' => 'Test']);
        
        // Second render - uses cache
        $output2 = $this->engine->render('cached.inky', ['name' => 'Test']);
        
        $this->assertEquals($output1, $output2);
        $this->assertTrue(is_dir($this->tempDir . '/cache'));
    }

    public function testEventDispatcherIntegration(): void
    {
        $eventFired = false;
        
        $dispatcher = new class($eventFired) implements EventDispatcherInterface {
            public function __construct(private bool &$fired) {}
            
            public function dispatch(object $event): object {
                $this->fired = true;
                return $event;
            }
        };
        
        $this->engine->setEventDispatcher($dispatcher);
        file_put_contents($this->tempDir . '/templates/event.inky', 'Test');
        
        $this->engine->render('event.inky');
        
        $this->assertTrue($eventFired);
    }

    public function testGlobalVariablesAccessible(): void
    {
        $this->engine->addGlobal('site_name', 'My Site');
        file_put_contents($this->tempDir . '/templates/global.inky', '{{ site_name }}');
        
        $output = $this->engine->render('global.inky');
        
        $this->assertStringContainsString('My Site', $output);
    }

    public function testMultipleExtensions(): void
    {
        $this->engine->registerExtension(new CoreExtension());
        file_put_contents($this->tempDir . '/templates/multi.inky', '{{ name | upper | trim }}');
        
        $output = $this->engine->render('multi.inky', ['name' => '  hello  ']);
        
        $this->assertEquals('HELLO', $output);
    }

    public function testTemplateInheritance(): void
    {
        file_put_contents($this->tempDir . '/templates/base.inky', 'Header {{ @block content }}Default{{ @endblock }} Footer');
        file_put_contents($this->tempDir . '/templates/child.inky', '{{ @extends "base.inky" }}{{ @block content }}Custom{{ @endblock }}');
        
        $output = $this->engine->render('child.inky');
        
        $this->assertStringContainsString('Header', $output);
        $this->assertStringContainsString('Custom', $output);
        $this->assertStringContainsString('Footer', $output);
    }

    public function testTemplateIncludes(): void
    {
        file_put_contents($this->tempDir . '/templates/partial.inky', 'Partial Content');
        file_put_contents($this->tempDir . '/templates/main.inky', 'Before {{ @include "partial.inky" }} After');
        
        $output = $this->engine->render('main.inky');
        
        $this->assertStringContainsString('Before', $output);
        $this->assertStringContainsString('Partial Content', $output);
        $this->assertStringContainsString('After', $output);
    }

    public function testEscapingIntegration(): void
    {
        file_put_contents($this->tempDir . '/templates/escape.inky', '{{ html }}');
        
        $output = $this->engine->render('escape.inky', ['html' => '<script>alert("xss")</script>']);
        
        $this->assertStringNotContainsString('<script>', $output);
        $this->assertStringContainsString('&lt;script&gt;', $output);
    }

    public function testRawOutputNoEscaping(): void
    {
        file_put_contents($this->tempDir . '/templates/raw.inky', '{{ ! html }}');
        
        $output = $this->engine->render('raw.inky', ['html' => '<b>bold</b>']);
        
        $this->assertStringContainsString('<b>bold</b>', $output);
    }

    public function testComments(): void
    {
        file_put_contents($this->tempDir . '/templates/comment.inky', 'Before {{ # This is a comment }} After');
        
        $output = $this->engine->render('comment.inky');
        
        $this->assertStringContainsString('Before', $output);
        $this->assertStringContainsString('After', $output);
        $this->assertStringNotContainsString('This is a comment', $output);
    }

    public function testStrictMode(): void
    {
        $this->engine->setStrictMode(true);
        file_put_contents($this->tempDir . '/templates/strict.inky', '{{ undefined }}');
        
        $this->expectException(\Flaphl\Fridge\Inky\Exception\InkyException::class);
        $this->engine->render('strict.inky');
    }

    public function testAutoReload(): void
    {
        $this->engine->setAutoReload(true);
        file_put_contents($this->tempDir . '/templates/reload.inky', 'Version 1');
        
        $output1 = $this->engine->render('reload.inky');
        
        sleep(1);
        file_put_contents($this->tempDir . '/templates/reload.inky', 'Version 2');
        
        $output2 = $this->engine->render('reload.inky');
        
        $this->assertStringContainsString('Version 1', $output1);
        $this->assertStringContainsString('Version 2', $output2);
    }

    public function testDebugMode(): void
    {
        $this->engine->setDebug(true);
        file_put_contents($this->tempDir . '/templates/debug.inky', '{{ name }}');
        
        $output = $this->engine->render('debug.inky', ['name' => 'Test']);
        
        $this->assertIsString($output);
    }

    public function testChainedFilters(): void
    {
        $this->engine->registerExtension(new CoreExtension());
        file_put_contents($this->tempDir . '/templates/chain.inky', '{{ text | upper | trim | length }}');
        
        $output = $this->engine->render('chain.inky', ['text' => '  hello  ']);
        
        $this->assertEquals('5', trim($output));
    }

    public function testNestedVariables(): void
    {
        file_put_contents($this->tempDir . '/templates/nested.inky', '{{ user.name }}');
        
        $output = $this->engine->render('nested.inky', ['user' => ['name' => 'John']]);
        
        $this->assertStringContainsString('John', $output);
    }

    public function testArrayAccess(): void
    {
        file_put_contents($this->tempDir . '/templates/array.inky', '{{ items[0] }}');
        
        $output = $this->engine->render('array.inky', ['items' => ['first', 'second']]);
        
        $this->assertStringContainsString('first', $output);
    }
}
