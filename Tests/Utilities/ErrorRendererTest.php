<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\Tests\Utilities;

use PHPUnit\Framework\TestCase;
use Flaphl\Fridge\Inky\Utilities\ErrorRenderer;
use Flaphl\Fridge\Inky\Exception\InkyException;

class ErrorRendererTest extends TestCase
{
    private ErrorRenderer $renderer;

    protected function setUp(): void
    {
        $this->renderer = new ErrorRenderer();
    }

    public function testConstruction(): void
    {
        $this->assertInstanceOf(ErrorRenderer::class, $this->renderer);
    }

    public function testRenderHtml(): void
    {
        $exception = new InkyException('Test error', 'test.inky', 10);
        $html = $this->renderer->renderHtml($exception);
        
        $this->assertIsString($html);
        $this->assertStringContainsString('Test error', $html);
        $this->assertStringContainsString('test.inky', $html);
        $this->assertStringContainsString('10', $html);
        $this->assertStringContainsString('<html>', $html);
    }

    public function testRenderHtmlContainsStackTrace(): void
    {
        $exception = new InkyException('Error');
        $html = $this->renderer->renderHtml($exception);
        
        $this->assertStringContainsString('Stack Trace', $html);
    }

    public function testRenderHtmlWithSourceContext(): void
    {
        $source = "Line 1\nLine 2\nLine 3\nLine 4\nLine 5";
        $exception = new InkyException('Error', 'test.inky', 3);
        
        $html = $this->renderer->renderHtml($exception, $source);
        
        $this->assertStringContainsString('Line 2', $html);
        $this->assertStringContainsString('Line 3', $html);
        $this->assertStringContainsString('Line 4', $html);
    }

    public function testRenderText(): void
    {
        $exception = new InkyException('Test error', 'test.inky', 10);
        $text = $this->renderer->renderText($exception);
        
        $this->assertIsString($text);
        $this->assertStringContainsString('Test error', $text);
        $this->assertStringContainsString('test.inky', $text);
        $this->assertStringContainsString('Line: 10', $text);
    }

    public function testRenderTextWithSourceContext(): void
    {
        $source = "Line 1\nLine 2\nLine 3\nLine 4\nLine 5";
        $exception = new InkyException('Error', 'test.inky', 3);
        
        $text = $this->renderer->renderText($exception, $source);
        
        $this->assertStringContainsString('Line 2', $text);
        $this->assertStringContainsString('Line 3', $text);
        $this->assertStringContainsString('Line 4', $text);
    }

    public function testRenderJson(): void
    {
        $exception = new InkyException('Test error', 'test.inky', 10);
        $json = $this->renderer->renderJson($exception);
        
        $this->assertJson($json);
        
        $data = json_decode($json, true);
        $this->assertEquals('Test error', $data['error']);
        $this->assertEquals('test.inky', $data['file']);
        $this->assertEquals(10, $data['line']);
    }

    public function testRenderJsonContainsTrace(): void
    {
        $exception = new InkyException('Error');
        $json = $this->renderer->renderJson($exception);
        
        $data = json_decode($json, true);
        $this->assertArrayHasKey('trace', $data);
        $this->assertIsArray($data['trace']);
    }

    public function testRenderJsonWithSourceContext(): void
    {
        $source = "Line 1\nLine 2\nLine 3";
        $exception = new InkyException('Error', 'test.inky', 2);
        
        $json = $this->renderer->renderJson($exception, $source);
        $data = json_decode($json, true);
        
        $this->assertArrayHasKey('context', $data);
        $this->assertIsArray($data['context']);
    }

    public function testSetMaxContextLines(): void
    {
        $this->renderer->setMaxContextLines(10);
        
        $source = implode("\n", array_fill(0, 20, 'line'));
        $exception = new InkyException('Error', 'test.inky', 10);
        
        $html = $this->renderer->renderHtml($exception, $source);
        $this->assertIsString($html);
    }

    public function testGetContextLines(): void
    {
        $source = "Line 1\nLine 2\nLine 3\nLine 4\nLine 5";
        
        $lines = $this->renderer->getContextLines($source, 3, 1);
        
        $this->assertIsArray($lines);
        $this->assertArrayHasKey(2, $lines);
        $this->assertArrayHasKey(3, $lines);
        $this->assertArrayHasKey(4, $lines);
    }

    public function testHighlightErrorLine(): void
    {
        $exception = new InkyException('Error', 'test.inky', 5);
        $html = $this->renderer->renderHtml($exception);
        
        $this->assertStringContainsString('error-line', $html);
    }

    public function testEscapeHtmlInError(): void
    {
        $exception = new InkyException('<script>alert("xss")</script>');
        $html = $this->renderer->renderHtml($exception);
        
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function testRenderWithNestedExceptions(): void
    {
        $previous = new \Exception('Previous error');
        $exception = new InkyException('Current error', 'test.inky', 5, $previous);
        
        $html = $this->renderer->renderHtml($exception);
        
        $this->assertStringContainsString('Current error', $html);
        $this->assertStringContainsString('Previous error', $html);
    }

    public function testFormatStackTrace(): void
    {
        $exception = new InkyException('Error');
        $text = $this->renderer->renderText($exception);
        
        $this->assertStringContainsString('#0', $text);
    }
}
