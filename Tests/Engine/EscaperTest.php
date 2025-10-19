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
use Flaphl\Fridge\Inky\Engine\Escaper;

class EscaperTest extends TestCase
{
    private Escaper $escaper;

    protected function setUp(): void
    {
        $this->escaper = new Escaper();
    }

    public function testHtmlEscaping(): void
    {
        $result = $this->escaper->escape('<script>alert("XSS")</script>', 'html');
        $this->assertEquals('&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;', $result);
    }

    public function testHtmlEscapingWithQuotes(): void
    {
        $result = $this->escaper->escape("It's a \"test\"", 'html');
        $this->assertStringContainsString('&quot;', $result);
        $this->assertStringContainsString('&#039;', $result);
    }

    public function testJavaScriptEscaping(): void
    {
        $result = $this->escaper->escape('Hello "World"', 'js');
        $this->assertStringContainsString('Hello', $result);
        $this->assertIsString($result);
    }

    public function testUrlEscaping(): void
    {
        $result = $this->escaper->escape('hello world', 'url');
        $this->assertEquals('hello%20world', $result);
    }

    public function testUrlEscapingSpecialChars(): void
    {
        $result = $this->escaper->escape('test@example.com', 'url');
        $this->assertEquals('test%40example.com', $result);
    }

    public function testCssEscaping(): void
    {
        $result = $this->escaper->escape('color: red;', 'css');
        $this->assertIsString($result);
    }

    public function testAttrEscaping(): void
    {
        $result = $this->escaper->escape('<div class="test">', 'attr');
        $this->assertStringNotContainsString('<', $result);
        $this->assertStringNotContainsString('>', $result);
    }

    public function testDefaultStrategyIsHtml(): void
    {
        $this->assertEquals('html', $this->escaper->getDefaultStrategy());
    }

    public function testSetDefaultStrategy(): void
    {
        $this->escaper->setDefaultStrategy('js');
        $this->assertEquals('js', $this->escaper->getDefaultStrategy());
    }

    public function testEscapeWithDefaultStrategy(): void
    {
        $result = $this->escaper->escape('<script>test</script>');
        $this->assertStringNotContainsString('<script>', $result);
    }

    public function testEscapeNull(): void
    {
        $result = $this->escaper->escape(null);
        $this->assertEquals('', $result);
    }

    public function testEscapeBoolean(): void
    {
        $this->assertEquals('true', $this->escaper->escape(true));
        $this->assertEquals('false', $this->escaper->escape(false));
    }

    public function testEscapeArray(): void
    {
        $result = $this->escaper->escape(['key' => 'value']);
        $this->assertJson($result);
    }

    public function testEscapeObject(): void
    {
        $obj = new \stdClass();
        $obj->name = 'test';
        $result = $this->escaper->escape($obj);
        $this->assertJson($result);
    }

    public function testAddCustomStrategy(): void
    {
        $this->escaper->addStrategy('custom', fn($value) => strtoupper($value));
        $result = $this->escaper->escape('hello', 'custom');
        $this->assertEquals('HELLO', $result);
    }

    public function testHasStrategy(): void
    {
        $this->assertTrue($this->escaper->hasStrategy('html'));
        $this->assertTrue($this->escaper->hasStrategy('js'));
        $this->assertTrue($this->escaper->hasStrategy('css'));
        $this->assertTrue($this->escaper->hasStrategy('url'));
        $this->assertTrue($this->escaper->hasStrategy('attr'));
        $this->assertFalse($this->escaper->hasStrategy('nonexistent'));
    }

    public function testInvalidStrategyThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->escaper->escape('test', 'invalid_strategy');
    }

    public function testChainableSetDefaultStrategy(): void
    {
        $returned = $this->escaper->setDefaultStrategy('url');
        $this->assertSame($this->escaper, $returned);
    }

    public function testChainableAddStrategy(): void
    {
        $returned = $this->escaper->addStrategy('test', fn($v) => $v);
        $this->assertSame($this->escaper, $returned);
    }
}
