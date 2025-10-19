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
use Flaphl\Fridge\Inky\Compilers\Token;
use Flaphl\Fridge\Inky\Compilers\TokenType;

class TokenTest extends TestCase
{
    public function testTokenConstruction(): void
    {
        $token = new Token(TokenType::TEXT, 'Hello', 1, 0);
        $this->assertInstanceOf(Token::class, $token);
    }

    public function testGetType(): void
    {
        $token = new Token(TokenType::VAR, 'name', 1, 5);
        $this->assertSame(TokenType::VAR, $token->getType());
    }

    public function testGetValue(): void
    {
        $token = new Token(TokenType::TEXT, 'test value', 1, 0);
        $this->assertEquals('test value', $token->getValue());
    }

    public function testGetLine(): void
    {
        $token = new Token(TokenType::COMMENT, 'comment', 42, 10);
        $this->assertEquals(42, $token->getLine());
    }

    public function testGetColumn(): void
    {
        $token = new Token(TokenType::DIRECTIVE, '@if', 1, 15);
        $this->assertEquals(15, $token->getColumn());
    }

    public function testIsReturnsTrueForMatchingType(): void
    {
        $token = new Token(TokenType::BLOCK_START, 'block', 1, 0);
        $this->assertTrue($token->is(TokenType::BLOCK_START));
    }

    public function testIsReturnsFalseForDifferentType(): void
    {
        $token = new Token(TokenType::TEXT, 'text', 1, 0);
        $this->assertFalse($token->is(TokenType::VAR));
    }

    public function testGetPosition(): void
    {
        $token = new Token(TokenType::RAW, 'raw', 10, 25);
        $position = $token->getPosition();
        
        $this->assertIsArray($position);
        $this->assertEquals(10, $position['line']);
        $this->assertEquals(25, $position['column']);
    }

    public function testToString(): void
    {
        $token = new Token(TokenType::VAR, 'name', 5, 12);
        $string = (string) $token;
        
        $this->assertStringContainsString('Variable', $string);
        $this->assertStringContainsString('name', $string);
        $this->assertStringContainsString('5', $string);
        $this->assertStringContainsString('12', $string);
    }

    public function testAllTokenTypes(): void
    {
        $types = [
            TokenType::TEXT,
            TokenType::VAR,
            TokenType::BLOCK_START,
            TokenType::BLOCK_END,
            TokenType::COMMENT,
            TokenType::RAW,
            TokenType::DIRECTIVE,
            TokenType::EOF,
        ];

        foreach ($types as $type) {
            $token = new Token($type, 'test', 1, 0);
            $this->assertTrue($token->is($type));
        }
    }

    public function testTokenTypeLabel(): void
    {
        $this->assertEquals('Text', TokenType::TEXT->label());
        $this->assertEquals('Variable', TokenType::VAR->label());
        $this->assertEquals('Comment', TokenType::COMMENT->label());
        $this->assertEquals('End of File', TokenType::EOF->label());
    }

    public function testTokenTypeIsExecutable(): void
    {
        $this->assertTrue(TokenType::VAR->isExecutable());
        $this->assertTrue(TokenType::DIRECTIVE->isExecutable());
        $this->assertTrue(TokenType::RAW->isExecutable());
        $this->assertFalse(TokenType::TEXT->isExecutable());
        $this->assertFalse(TokenType::COMMENT->isExecutable());
        $this->assertFalse(TokenType::EOF->isExecutable());
    }
}
