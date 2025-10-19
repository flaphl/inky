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
 * Compiles template source into executable PHP code.
 */
interface CompilerInterface
{
    /**
     * Compile template source to PHP code.
     *
     * @param string $source Template source code
     * @param string $name Template name
     *
     * @return string Compiled PHP code
     */
    public function compile(string $source, string $name): string;

    /**
     * Get the compiled template class name.
     *
     * @param string $name Template name
     *
     * @return string The class name
     */
    public function getCompiledClassName(string $name): string;

    /**
     * Add a directive to the compiler.
     *
     * @param string $name Directive name (e.g., 'if', 'foreach')
     * @param callable $handler Directive compiler callback
     *
     * @return self
     */
    public function addDirective(string $name, callable $handler): self;

    /**
     * Check if a directive exists.
     *
     * @param string $name Directive name
     *
     * @return bool True if directive is registered
     */
    public function hasDirective(string $name): bool;

    /**
     * Remove a directive.
     *
     * @param string $name Directive name
     *
     * @return self
     */
    public function removeDirective(string $name): self;

    /**
     * Get all registered directives.
     *
     * @return array<string, callable> Directive handlers
     */
    public function getDirectives(): array;

    /**
     * Set the escape function for output escaping.
     *
     * @param \Flaphl\Fridge\Inky\Engine\EscaperInterface $escaper Escaper instance
     *
     * @return self
     */
    public function setEscaper(\Flaphl\Fridge\Inky\Engine\EscaperInterface $escaper): self;

    /**
     * Get the escape function.
     *
     * @return \Flaphl\Fridge\Inky\Engine\EscaperInterface|null The escaper instance
     */
    public function getEscaper(): ?\Flaphl\Fridge\Inky\Engine\EscaperInterface;
}
