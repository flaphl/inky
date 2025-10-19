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

/**
 * Parses tokens into an abstract syntax tree.
 */
interface ParserInterface
{
    /**
     * Parse tokens into an AST.
     *
     * @param array<TokenInterface> $tokens Tokens from lexer
     *
     * @return NodeInterface Root node of AST
     */
    public function parse(array $tokens): NodeInterface;

    /**
     * Set the current stream of tokens.
     *
     * @param array<TokenInterface> $tokens Token stream
     *
     * @return self
     */
    public function setTokenStream(array $tokens): self;

    /**
     * Get the current token.
     *
     * @return TokenInterface Current token
     */
    public function getCurrentToken(): TokenInterface;

    /**
     * Advance to the next token.
     *
     * @return TokenInterface Next token
     */
    public function nextToken(): TokenInterface;

    /**
     * Check if current token matches a type.
     *
     * @param TokenType $type Expected token type
     *
     * @return bool True if matches
     */
    public function test(TokenType $type): bool;

    /**
     * Expect a specific token type and consume it.
     *
     * @param TokenType $type Expected token type
     *
     * @return TokenInterface The consumed token
     *
     * @throws \Flaphl\Fridge\Inky\Exception\InkyException If token doesn't match
     */
    public function expect(TokenType $type): TokenInterface;
}
