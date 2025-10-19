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
 * Tokenizes template source code.
 */
interface LexerInterface
{
    /**
     * Tokenize template source.
     *
     * @param string $source Template source code
     * @param string $name Template name (for error messages)
     *
     * @return array<TokenInterface> Array of tokens
     */
    public function tokenize(string $source, string $name): array;

    /**
     * Set the delimiter for template tags.
     *
     * @param string $open Opening delimiter (default: '{{')
     * @param string $close Closing delimiter (default: '}}')
     *
     * @return self
     */
    public function setDelimiters(string $open, string $close): self;

    /**
     * Get the opening delimiter.
     *
     * @return string Opening delimiter
     */
    public function getOpenDelimiter(): string;

    /**
     * Get the closing delimiter.
     *
     * @return string Closing delimiter
     */
    public function getCloseDelimiter(): string;
}
