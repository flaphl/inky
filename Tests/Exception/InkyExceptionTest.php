<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\Tests\Exception;

use PHPUnit\Framework\TestCase;
use Flaphl\Fridge\Inky\Exception\InkyException;

class InkyExceptionTest extends TestCase
{
    public function testConstruction(): void
    {
        $exception = new InkyException('Test error');
        
        $this->assertInstanceOf(InkyException::class, $exception);
        $this->assertEquals('Test error', $exception->getMessage());
    }

    public function testConstructionWithTemplate(): void
    {
        $exception = new InkyException('Error', 'test.inky');
        
        $this->assertEquals('test.inky', $exception->getTemplateName());
    }

    public function testConstructionWithLine(): void
    {
        $exception = new InkyException('Error', 'test.inky', 42);
        
        $this->assertEquals(42, $exception->getTemplateLine());
    }

    public function testConstructionWithPrevious(): void
    {
        $previous = new \Exception('Previous error');
        $exception = new InkyException('Error', 'test.inky', 1, $previous);
        
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testGetTemplateNameReturnsNull(): void
    {
        $exception = new InkyException('Error');
        
        $this->assertNull($exception->getTemplateName());
    }

    public function testGetTemplateLineReturnsNull(): void
    {
        $exception = new InkyException('Error');
        
        $this->assertNull($exception->getTemplateLine());
    }

    public function testSetTemplateName(): void
    {
        $exception = new InkyException('Error');
        $exception->setTemplateName('new.inky');
        
        $this->assertEquals('new.inky', $exception->getTemplateName());
    }

    public function testSetTemplateLine(): void
    {
        $exception = new InkyException('Error');
        $exception->setTemplateLine(100);
        
        $this->assertEquals(100, $exception->getTemplateLine());
    }

    public function testForSyntaxError(): void
    {
        $exception = InkyException::forSyntaxError('Invalid syntax', 'test.inky', 10);
        
        $this->assertInstanceOf(InkyException::class, $exception);
        $this->assertStringContainsString('Syntax error', $exception->getMessage());
        $this->assertStringContainsString('Invalid syntax', $exception->getMessage());
        $this->assertEquals('test.inky', $exception->getTemplateName());
        $this->assertEquals(10, $exception->getTemplateLine());
    }

    public function testForRuntimeError(): void
    {
        $exception = InkyException::forRuntimeError('Runtime issue', 'test.inky', 5);
        
        $this->assertInstanceOf(InkyException::class, $exception);
        $this->assertStringContainsString('Runtime error', $exception->getMessage());
        $this->assertStringContainsString('Runtime issue', $exception->getMessage());
    }

    public function testForTemplateNotFound(): void
    {
        $exception = InkyException::forTemplateNotFound('missing.inky');
        
        $this->assertInstanceOf(InkyException::class, $exception);
        $this->assertStringContainsString('Template not found', $exception->getMessage());
        $this->assertStringContainsString('missing.inky', $exception->getMessage());
    }

    public function testForUndefinedVariable(): void
    {
        $exception = InkyException::forUndefinedVariable('varName', 'test.inky', 3);
        
        $this->assertInstanceOf(InkyException::class, $exception);
        $this->assertStringContainsString('Undefined variable', $exception->getMessage());
        $this->assertStringContainsString('varName', $exception->getMessage());
    }

    public function testForUndefinedFilter(): void
    {
        $exception = InkyException::forUndefinedFilter('customFilter');
        
        $this->assertInstanceOf(InkyException::class, $exception);
        $this->assertStringContainsString('Undefined filter', $exception->getMessage());
        $this->assertStringContainsString('customFilter', $exception->getMessage());
    }

    public function testForUndefinedFunction(): void
    {
        $exception = InkyException::forUndefinedFunction('customFunc');
        
        $this->assertInstanceOf(InkyException::class, $exception);
        $this->assertStringContainsString('Undefined function', $exception->getMessage());
        $this->assertStringContainsString('customFunc', $exception->getMessage());
    }

    public function testForCircularReference(): void
    {
        $exception = InkyException::forCircularReference('parent.inky', ['child.inky', 'parent.inky']);
        
        $this->assertInstanceOf(InkyException::class, $exception);
        $this->assertStringContainsString('Circular reference', $exception->getMessage());
    }

    public function testGetSourceContext(): void
    {
        $exception = new InkyException('Error', 'test.inky', 5);
        $exception->setSourceContext('Line 1\nLine 2\nLine 3\nLine 4\nLine 5');
        
        $this->assertStringContainsString('Line 5', $exception->getSourceContext());
    }

    public function testGetSourceContextReturnsNullInitially(): void
    {
        $exception = new InkyException('Error');
        
        $this->assertNull($exception->getSourceContext());
    }

    public function testInheritsFromRuntimeException(): void
    {
        $exception = new InkyException('Error');
        
        $this->assertInstanceOf(\RuntimeException::class, $exception);
    }

    public function testToString(): void
    {
        $exception = new InkyException('Test error', 'test.inky', 42);
        $string = (string) $exception;
        
        $this->assertStringContainsString('Test error', $string);
        $this->assertStringContainsString('test.inky', $string);
        $this->assertStringContainsString('42', $string);
    }

    public function testGetMessageWithContext(): void
    {
        $exception = new InkyException('Error', 'test.inky', 10);
        
        $this->assertIsString($exception->getMessage());
        $this->assertStringContainsString('Error', $exception->getMessage());
    }
}
