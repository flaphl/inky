<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\Engine;

/**
 * Escapes output for safe HTML rendering.
 */
interface EscaperInterface
{
    /**
     * Escape a value for safe output.
     *
     * @param mixed $value Value to escape
     * @param string $strategy Escaping strategy ('html', 'js', 'css', 'url', 'attr')
     *
     * @return string Escaped value
     */
    public function escape(mixed $value, string $strategy = 'html'): string;

    /**
     * Set the default escaping strategy.
     *
     * @param string $strategy Escaping strategy
     *
     * @return self
     */
    public function setDefaultStrategy(string $strategy): self;

    /**
     * Get the default escaping strategy.
     *
     * @return string Escaping strategy
     */
    public function getDefaultStrategy(): string;

    /**
     * Add a custom escaping strategy.
     *
     * @param string $name Strategy name
     * @param callable(mixed): string $escaper Escape function
     *
     * @return self
     */
    public function addStrategy(string $name, callable $escaper): self;

    /**
     * Check if an escaping strategy exists.
     *
     * @param string $name Strategy name
     *
     * @return bool True if strategy exists
     */
    public function hasStrategy(string $name): bool;
}
