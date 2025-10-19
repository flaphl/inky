<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\Tests\Events;

use PHPUnit\Framework\TestCase;
use Flaphl\Fridge\Inky\Events\{TemplateEvent, PreRenderEvent, PostRenderEvent, RenderErrorEvent};
use Flaphl\Fridge\Inky\Engine\Template;

class EventTest extends TestCase
{
    private Template $template;
    private array $context;

    protected function setUp(): void
    {
        $this->template = $this->createMock(Template::class);
        $this->context = ['name' => 'Test'];
    }

    public function testTemplateEventConstruction(): void
    {
        $event = new TemplateEvent($this->template, 'test.inky', $this->context);
        
        $this->assertInstanceOf(TemplateEvent::class, $event);
        $this->assertSame($this->template, $event->getTemplate());
        $this->assertEquals('test.inky', $event->getTemplateName());
        $this->assertEquals($this->context, $event->getContext());
    }

    public function testTemplateEventSetContext(): void
    {
        $event = new TemplateEvent($this->template, 'test.inky', $this->context);
        $newContext = ['age' => 25];
        
        $event->setContext($newContext);
        $this->assertEquals($newContext, $event->getContext());
    }

    public function testTemplateEventMergeContext(): void
    {
        $event = new TemplateEvent($this->template, 'test.inky', ['name' => 'Test']);
        $event->mergeContext(['age' => 25]);
        
        $expected = ['name' => 'Test', 'age' => 25];
        $this->assertEquals($expected, $event->getContext());
    }

    public function testTemplateEventGetContextValue(): void
    {
        $event = new TemplateEvent($this->template, 'test.inky', $this->context);
        
        $this->assertEquals('Test', $event->getContextValue('name'));
        $this->assertNull($event->getContextValue('nonexistent'));
        $this->assertEquals('default', $event->getContextValue('nonexistent', 'default'));
    }

    public function testTemplateEventSetContextValue(): void
    {
        $event = new TemplateEvent($this->template, 'test.inky', $this->context);
        $event->setContextValue('age', 25);
        
        $this->assertEquals(25, $event->getContextValue('age'));
    }

    public function testPreRenderEventConstruction(): void
    {
        $event = new PreRenderEvent($this->template, 'test.inky', $this->context);
        
        $this->assertInstanceOf(PreRenderEvent::class, $event);
        $this->assertInstanceOf(TemplateEvent::class, $event);
    }

    public function testPreRenderEventPropagation(): void
    {
        $event = new PreRenderEvent($this->template, 'test.inky', $this->context);
        
        $this->assertFalse($event->isPropagationStopped());
        
        $event->stopPropagation();
        $this->assertTrue($event->isPropagationStopped());
    }

    public function testPostRenderEventConstruction(): void
    {
        $output = '<html>test</html>';
        $event = new PostRenderEvent($this->template, 'test.inky', $this->context, $output);
        
        $this->assertInstanceOf(PostRenderEvent::class, $event);
        $this->assertEquals($output, $event->getOutput());
    }

    public function testPostRenderEventSetOutput(): void
    {
        $event = new PostRenderEvent($this->template, 'test.inky', $this->context, 'original');
        $event->setOutput('modified');
        
        $this->assertEquals('modified', $event->getOutput());
    }

    public function testPostRenderEventModifyOutput(): void
    {
        $event = new PostRenderEvent($this->template, 'test.inky', $this->context, 'Hello');
        $event->modifyOutput(fn($output) => strtoupper($output));
        
        $this->assertEquals('HELLO', $event->getOutput());
    }

    public function testRenderErrorEventConstruction(): void
    {
        $exception = new \Exception('Test error');
        $event = new RenderErrorEvent($this->template, 'test.inky', $this->context, $exception);
        
        $this->assertInstanceOf(RenderErrorEvent::class, $event);
        $this->assertSame($exception, $event->getException());
    }

    public function testRenderErrorEventHandled(): void
    {
        $exception = new \Exception('Test error');
        $event = new RenderErrorEvent($this->template, 'test.inky', $this->context, $exception);
        
        $this->assertFalse($event->isHandled());
        
        $event->setHandled(true);
        $this->assertTrue($event->isHandled());
    }

    public function testRenderErrorEventSetFallbackOutput(): void
    {
        $exception = new \Exception('Test error');
        $event = new RenderErrorEvent($this->template, 'test.inky', $this->context, $exception);
        
        $event->setFallbackOutput('Error fallback');
        $this->assertEquals('Error fallback', $event->getFallbackOutput());
    }

    public function testRenderErrorEventNullFallback(): void
    {
        $exception = new \Exception('Test error');
        $event = new RenderErrorEvent($this->template, 'test.inky', $this->context, $exception);
        
        $this->assertNull($event->getFallbackOutput());
    }

    public function testEventInheritance(): void
    {
        $this->assertInstanceOf(TemplateEvent::class, new PreRenderEvent($this->template, 'test.inky', []));
        $this->assertInstanceOf(TemplateEvent::class, new PostRenderEvent($this->template, 'test.inky', [], ''));
        $this->assertInstanceOf(TemplateEvent::class, new RenderErrorEvent($this->template, 'test.inky', [], new \Exception()));
    }
}
