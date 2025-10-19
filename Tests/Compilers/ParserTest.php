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
use Flaphl\Fridge\Inky\Compilers\{Parser, Lexer, Token, TokenType, NodeInterface};

class ParserTest extends TestCase
{
    private Parser $parser;
    private Lexer $lexer;

    protected function setUp(): void
    {
        $this->parser = new Parser();
        $this->lexer = new Lexer();
    }

    public function testParserConstruction(): void
    {
        $this->assertInstanceOf(Parser::class, $this->parser);
    }

    public function testParseReturnsNode(): void
    {
        $tokens = [new Token(TokenType::EOF, '', 1, 0)];
        $node = $this->parser->parse($tokens);
        
        $this->assertInstanceOf(NodeInterface::class, $node);
    }

    public function testSetTokenStream(): void
    {
        $tokens = [new Token(TokenType::TEXT, 'test', 1, 0)];
        $returned = $this->parser->setTokenStream($tokens);
        
        $this->assertSame($this->parser, $returned);
    }

    public function testGetCurrentToken(): void
    {
        $token = new Token(TokenType::TEXT, 'test', 1, 0);
        $this->parser->setTokenStream([$token]);
        
        $this->assertSame($token, $this->parser->getCurrentToken());
    }

    public function testNextToken(): void
    {
        $token1 = new Token(TokenType::TEXT, 'first', 1, 0);
        $token2 = new Token(TokenType::VAR, 'second', 1, 5);
        $this->parser->setTokenStream([$token1, $token2]);
        
        $this->assertSame($token1, $this->parser->getCurrentToken());
        $this->assertSame($token2, $this->parser->nextToken());
    }

    public function testTestReturnsTrueForMatchingType(): void
    {
        $token = new Token(TokenType::TEXT, 'test', 1, 0);
        $this->parser->setTokenStream([$token]);
        
        $this->assertTrue($this->parser->test(TokenType::TEXT));
    }

    public function testTestReturnsFalseForNonMatchingType(): void
    {
        $token = new Token(TokenType::TEXT, 'test', 1, 0);
        $this->parser->setTokenStream([$token]);
        
        $this->assertFalse($this->parser->test(TokenType::VAR));
    }

    public function testExpectConsumesToken(): void
    {
        $token1 = new Token(TokenType::TEXT, 'first', 1, 0);
        $token2 = new Token(TokenType::VAR, 'second', 1, 5);
        $this->parser->setTokenStream([$token1, $token2]);
        
        $consumed = $this->parser->expect(TokenType::TEXT);
        $this->assertSame($token1, $consumed);
        $this->assertSame($token2, $this->parser->getCurrentToken());
    }

    public function testExpectThrowsOnMismatch(): void
    {
        $token = new Token(TokenType::TEXT, 'test', 1, 0);
        $this->parser->setTokenStream([$token]);
        
        $this->expectException(\Flaphl\Fridge\Inky\Exception\InkyException::class);
        $this->parser->expect(TokenType::VAR);
    }

    public function testParseSimpleText(): void
    {
        $tokens = $this->lexer->tokenize('Hello World');
        $node = $this->parser->parse($tokens);
        
        $this->assertInstanceOf(NodeInterface::class, $node);
        $this->assertEquals('root', $node->getType());
    }

    public function testParseVariable(): void
    {
        $tokens = $this->lexer->tokenize('{{ name }}');
        $node = $this->parser->parse($tokens);
        
        $this->assertInstanceOf(NodeInterface::class, $node);
        $children = $node->getChildren();
        $this->assertNotEmpty($children);
    }

    public function testParseComment(): void
    {
        $tokens = $this->lexer->tokenize('{{ # comment }}');
        $node = $this->parser->parse($tokens);
        
        $this->assertInstanceOf(NodeInterface::class, $node);
    }

    public function testParseDirective(): void
    {
        $tokens = $this->lexer->tokenize('{{ @if }}');
        $node = $this->parser->parse($tokens);
        
        $this->assertInstanceOf(NodeInterface::class, $node);
    }

    public function testParseRaw(): void
    {
        $tokens = $this->lexer->tokenize('{{ ! html }}');
        $node = $this->parser->parse($tokens);
        
        $this->assertInstanceOf(NodeInterface::class, $node);
    }

    public function testParseMultipleNodes(): void
    {
        $tokens = $this->lexer->tokenize('Text {{ var }} more text');
        $node = $this->parser->parse($tokens);
        
        $children = $node->getChildren();
        $this->assertGreaterThan(1, count($children));
    }
}
