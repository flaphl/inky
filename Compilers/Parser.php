<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\Compilers;

use Flaphl\Fridge\Inky\Exception\InkyException;

/**
 * Parser implementation for building AST from tokens.
 */
class Parser implements ParserInterface
{
    private array $tokens = [];
    private int $position = 0;

    /**
     * {@inheritdoc}
     */
    public function parse(array $tokens): NodeInterface
    {
        $this->setTokenStream($tokens);
        return $this->parseRoot();
    }

    /**
     * {@inheritdoc}
     */
    public function setTokenStream(array $tokens): self
    {
        $this->tokens = $tokens;
        $this->position = 0;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrentToken(): TokenInterface
    {
        return $this->tokens[$this->position] ?? $this->tokens[count($this->tokens) - 1];
    }

    /**
     * {@inheritdoc}
     */
    public function nextToken(): TokenInterface
    {
        $this->position++;
        return $this->getCurrentToken();
    }

    /**
     * {@inheritdoc}
     */
    public function test(TokenType $type): bool
    {
        return $this->getCurrentToken()->is($type);
    }

    /**
     * {@inheritdoc}
     */
    public function expect(TokenType $type): TokenInterface
    {
        $token = $this->getCurrentToken();
        
        if (!$token->is($type)) {
            throw InkyException::syntaxError(
                sprintf('Expected %s, got %s', $type->label(), $token->getType()->label()),
                'unknown',
                $token->getLine()
            );
        }

        $this->nextToken();
        return $token;
    }

    /**
     * Parse root node (document).
     *
     * @return NodeInterface Root node
     */
    private function parseRoot(): NodeInterface
    {
        $children = [];

        while (!$this->test(TokenType::EOF)) {
            $children[] = $this->parseNode();
        }

        return new RootNode($children);
    }

    /**
     * Parse a single node.
     *
     * @return NodeInterface Parsed node
     */
    private function parseNode(): NodeInterface
    {
        $token = $this->getCurrentToken();

        return match ($token->getType()) {
            TokenType::TEXT => $this->parseText(),
            TokenType::VAR => $this->parseVariable(),
            TokenType::DIRECTIVE => $this->parseDirective(),
            TokenType::COMMENT => $this->parseComment(),
            TokenType::RAW => $this->parseRaw(),
            TokenType::BLOCK_START => $this->parseBlock(),
            default => $this->parseText(),
        };
    }

    /**
     * Parse text node.
     *
     * @return NodeInterface Text node
     */
    private function parseText(): NodeInterface
    {
        $token = $this->expect(TokenType::TEXT);
        return new TextNode($token->getValue(), $token->getLine());
    }

    /**
     * Parse variable node.
     *
     * @return NodeInterface Variable node
     */
    private function parseVariable(): NodeInterface
    {
        $token = $this->expect(TokenType::VAR);
        return new VarNode($token->getValue(), $token->getLine());
    }

    /**
     * Parse directive node.
     *
     * @return NodeInterface Directive node
     */
    private function parseDirective(): NodeInterface
    {
        $token = $this->expect(TokenType::DIRECTIVE);
        $content = ltrim($token->getValue(), '@');
        
        // Split directive name and arguments
        $parts = preg_split('/\s+/', $content, 2);
        $name = $parts[0];
        $args = $parts[1] ?? '';

        return new DirectiveNode($name, $args, $token->getLine());
    }

    /**
     * Parse comment node.
     *
     * @return NodeInterface Comment node
     */
    private function parseComment(): NodeInterface
    {
        $token = $this->expect(TokenType::COMMENT);
        return new CommentNode($token->getValue(), $token->getLine());
    }

    /**
     * Parse raw/unescaped node.
     *
     * @return NodeInterface Raw node
     */
    private function parseRaw(): NodeInterface
    {
        $token = $this->expect(TokenType::RAW);
        $value = ltrim($token->getValue(), '!');
        return new RawNode($value, $token->getLine());
    }

    /**
     * Parse block node.
     *
     * @return NodeInterface Block node
     */
    private function parseBlock(): NodeInterface
    {
        $token = $this->expect(TokenType::BLOCK_START);
        return new BlockNode($token->getValue(), $token->getLine());
    }
}
