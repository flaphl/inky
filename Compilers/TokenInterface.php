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
 * Represents a template token.
 */
interface TokenInterface
{
    /**
     * Get the token type.
     *
     * @return TokenType The token type
     */
    public function getType(): TokenType;

    /**
     * Get the token value.
     *
     * @return string The token value
     */
    public function getValue(): string;

    /**
     * Get the line number where token appears.
     *
     * @return int Line number (1-indexed)
     */
    public function getLine(): int;

    /**
     * Get the column number where token appears.
     *
     * @return int Column number (1-indexed)
     */
    public function getColumn(): int;

    /**
     * Check if token is of a given type.
     *
     * @param TokenType $type Token type to check
     *
     * @return bool True if token matches type
     */
    public function is(TokenType $type): bool;

    /**
     * Get the token position in source.
     *
     * @return array{line: int, column: int} Line and column position
     */
    public function getPosition(): array;
}
