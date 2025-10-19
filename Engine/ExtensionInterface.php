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
 * Extends template engine with custom functionality.
 */
interface ExtensionInterface
{
    /**
     * Get the extension name.
     *
     * @return string Extension identifier
     */
    public function getName(): string;

    /**
     * Get template filters provided by this extension.
     *
     * @return array<string, callable> Filter name => callable
     */
    public function getFilters(): array;

    /**
     * Get template functions provided by this extension.
     *
     * @return array<string, callable> Function name => callable
     */
    public function getFunctions(): array;

    /**
     * Get template tests provided by this extension.
     *
     * @return array<string, callable> Test name => callable
     */
    public function getTests(): array;

    /**
     * Get global variables provided by this extension.
     *
     * @return array<string, mixed> Variable name => value
     */
    public function getGlobals(): array;

    /**
     * Initialize the extension.
     *
     * @param EngineInterface $engine The template engine
     *
     * @return void
     */
    public function initialize(EngineInterface $engine): void;
}
