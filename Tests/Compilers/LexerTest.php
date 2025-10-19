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
use Flaphl\Fridge\Inky\Compilers\Lexer;
use Flaphl\Fridge\Inky\Compilers\TokenType;

class LexerTest extends TestCase
{
    private Lexer $lexer;

    protected function setUp(): void
    {
        $this->lexer = new Lexer();
    }

    public function testLexerConstruction(): void
    {
        $this->assertInstanceOf(Lexer::class, $this->lexer);
    }

    public function testTokenizeSimpleText(): void
    {
        $tokens = $this->lexer->tokenize('Hello World');
        
        $this->assertCount(2, $tokens); // TEXT + EOF
        $this->assertTrue($tokens[0]->is(TokenType::TEXT));
        $this->assertEquals('Hello World', $tokens[0]->getValue());
        $this->assertTrue($tokens[1]->is(TokenType::EOF));
    }

    public function testTokenizeVariable(): void
    {
        $tokens = $this->lexer->tokenize('{{ name }}');
        
        $this->assertGreaterThanOrEqual(2, count($tokens));
        $this->assertTrue($tokens[0]->is(TokenType::VAR));
        $this->assertEquals('name', $tokens[0]->getValue());
    }

    public function testTokenizeTextWithVariable(): void
    {
        $tokens = $this->lexer->tokenize('Hello {{ name }}!');
        
        $this->assertGreaterThanOrEqual(3, count($tokens)); // TEXT, VAR, TEXT, EOF
    }

    public function testTokenizeComment(): void
    {
        $tokens = $this->lexer->tokenize('{{ # This is a comment }}');
        
        $foundComment = false;
        foreach ($tokens as $token) {
            if ($token->is(TokenType::COMMENT)) {
                $foundComment = true;
                break;
            }
        }
        $this->assertTrue($foundComment);
    }

    public function testTokenizeDirective(): void
    {
        $tokens = $this->lexer->tokenize('{{ @if }}');
        
        $foundDirective = false;
        foreach ($tokens as $token) {
            if ($token->is(TokenType::DIRECTIVE)) {
                $foundDirective = true;
                break;
            }
        }
        $this->assertTrue($foundDirective);
    }

    public function testTokenizeRaw(): void
    {
        $tokens = $this->lexer->tokenize('{{ ! html }}');
        
        $foundRaw = false;
        foreach ($tokens as $token) {
            if ($token->is(TokenType::RAW)) {
                $foundRaw = true;
                break;
            }
        }
        $this->assertTrue($foundRaw);
    }

    public function testSetDelimiters(): void
    {
        $returned = $this->lexer->setDelimiters('{%', '%}');
        $this->assertSame($this->lexer, $returned);
        
        $this->assertEquals('{%', $this->lexer->getOpenDelimiter());
        $this->assertEquals('%}', $this->lexer->getCloseDelimiter());
    }

    public function testCustomDelimiters(): void
    {
        $this->lexer->setDelimiters('{%', '%}');
        $tokens = $this->lexer->tokenize('{% name %}');
        
        $foundVar = false;
        foreach ($tokens as $token) {
            if ($token->is(TokenType::VAR) || $token->is(TokenType::TEXT)) {
                $foundVar = true;
                break;
            }
        }
        $this->assertTrue($foundVar);
    }

    public function testGetOpenDelimiter(): void
    {
        $this->assertEquals('{{', $this->lexer->getOpenDelimiter());
    }

    public function testGetCloseDelimiter(): void
    {
        $this->assertEquals('}}', $this->lexer->getCloseDelimiter());
    }

    public function testTokenizeEmptyString(): void
    {
        $tokens = $this->lexer->tokenize('');
        
        $this->assertCount(1, $tokens); // Just EOF
        $this->assertTrue($tokens[0]->is(TokenType::EOF));
    }

    public function testTokenizeUnclosedDelimiter(): void
    {
        $tokens = $this->lexer->tokenize('{{ name');
        
        // Should handle gracefully
        $this->assertIsArray($tokens);
        $this->assertNotEmpty($tokens);
    }

    public function testTokenPositionTracking(): void
    {
        $tokens = $this->lexer->tokenize('Line 1{{ var }}');
        
        foreach ($tokens as $token) {
            $this->assertGreaterThanOrEqual(1, $token->getLine());
            $this->assertGreaterThanOrEqual(1, $token->getColumn());
        }
    }

    public function testMultipleVariables(): void
    {
        $tokens = $this->lexer->tokenize('{{ var1 }} and {{ var2 }}');
        
        $varCount = 0;
        foreach ($tokens as $token) {
            if ($token->is(TokenType::VAR)) {
                $varCount++;
            }
        }
        
        $this->assertEquals(2, $varCount);
    }

    public function testMixedContent(): void
    {
        $source = 'Text {{ var }} more text {{ @directive }} {{ # comment }}';
        $tokens = $this->lexer->tokenize($source);
        
        $this->assertIsArray($tokens);
        $this->assertGreaterThan(3, count($tokens));
    }
}
