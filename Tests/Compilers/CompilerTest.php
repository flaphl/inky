<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\Tests\Compilers;

use PHPUnit\Framework\TestCase;
use Flaphl\Fridge\Inky\Compilers\{Compiler, Lexer, Parser};
use Flaphl\Fridge\Inky\Engine\Escaper;

class CompilerTest extends TestCase
{
    private Compiler $compiler;

    protected function setUp(): void
    {
        $this->compiler = new Compiler(new Lexer(), new Parser());
    }

    public function testCompilerConstruction(): void
    {
        $this->assertInstanceOf(Compiler::class, $this->compiler);
    }

    public function testCompileReturnsString(): void
    {
        $result = $this->compiler->compile('Hello World', 'test.inky');
        $this->assertIsString($result);
    }

    public function testCompiledCodeContainsPhpTags(): void
    {
        $result = $this->compiler->compile('Test', 'test.inky');
        $this->assertStringContainsString('<?php', $result);
    }

    public function testCompileWrapsInTemplateClass(): void
    {
        $result = $this->compiler->compile('Test', 'test.inky');
        $this->assertStringContainsString('class', $result);
        $this->assertStringContainsString('Template', $result);
    }

    public function testAddDirective(): void
    {
        $called = false;
        $directive = function($content) use (&$called) {
            $called = true;
            return "<?php // custom directive ?>";
        };
        
        $returned = $this->compiler->addDirective('custom', $directive);
        $this->assertSame($this->compiler, $returned);
    }

    public function testHasDirectiveReturnsTrueForBuiltIn(): void
    {
        $this->assertTrue($this->compiler->hasDirective('if'));
        $this->assertTrue($this->compiler->hasDirective('foreach'));
        $this->assertTrue($this->compiler->hasDirective('for'));
        $this->assertTrue($this->compiler->hasDirective('while'));
    }

    public function testHasDirectiveReturnsFalseForNonExistent(): void
    {
        $this->assertFalse($this->compiler->hasDirective('nonexistent'));
    }

    public function testGetDirective(): void
    {
        $directive = $this->compiler->getDirective('if');
        $this->assertIsCallable($directive);
    }

    public function testGetNonExistentDirectiveReturnsNull(): void
    {
        $this->assertNull($this->compiler->getDirective('nonexistent'));
    }

    public function testSetEscaper(): void
    {
        $escaper = new Escaper();
        $returned = $this->compiler->setEscaper($escaper);
        
        $this->assertSame($this->compiler, $returned);
        $this->assertSame($escaper, $this->compiler->getEscaper());
    }

    public function testGetEscaperReturnsNullInitially(): void
    {
        $compiler = new Compiler(new Lexer(), new Parser());
        $this->assertNull($compiler->getEscaper());
    }

    public function testCompileDirectiveWithKnownDirective(): void
    {
        $result = $this->compiler->compileDirective('if', '$condition');
        $this->assertIsString($result);
        $this->assertStringContainsString('<?php', $result);
    }

    public function testCompileDirectiveWithUnknownDirective(): void
    {
        $result = $this->compiler->compileDirective('unknown', 'content');
        $this->assertStringContainsString('Unknown directive', $result);
    }

    public function testEscapeWithoutEscaper(): void
    {
        $result = $this->compiler->escape('$value');
        $this->assertEquals('$value', $result);
    }

    public function testEscapeWithEscaper(): void
    {
        $this->compiler->setEscaper(new Escaper());
        $result = $this->compiler->escape('$value', 'html');
        $this->assertStringContainsString('escape', $result);
    }

    public function testBuiltInDirectives(): void
    {
        $directives = ['if', 'else', 'endif', 'foreach', 'endforeach', 'for', 'endfor', 'while', 'endwhile'];
        
        foreach ($directives as $directive) {
            $this->assertTrue($this->compiler->hasDirective($directive), "Missing directive: {$directive}");
        }
    }

    public function testCustomDirectiveExecution(): void
    {
        $executed = false;
        $this->compiler->addDirective('test', function($content) use (&$executed) {
            $executed = true;
            return "<?php // test: {$content} ?>";
        });
        
        $result = $this->compiler->compileDirective('test', 'arg');
        $this->assertTrue($executed);
        $this->assertStringContainsString('test: arg', $result);
    }
}
