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
 * Represents a compiled template.
 */
interface TemplateInterface
{
    /**
     * Render the template with given context.
     *
     * @param array<string, mixed> $context Template variables
     *
     * @return string Rendered template output
     */
    public function render(array $context = []): string;

    /**
     * Display the template (echo rendered output).
     *
     * @param array<string, mixed> $context Template variables
     *
     * @return void
     */
    public function display(array $context = []): void;

    /**
     * Get the template name/path.
     *
     * @return string The template identifier
     */
    public function getName(): string;

    /**
     * Get the source code of the template.
     *
     * @return string The template source
     */
    public function getSource(): string;

    /**
     * Check if template has a specific block.
     *
     * @param string $name Block name
     *
     * @return bool True if block exists
     */
    public function hasBlock(string $name): bool;

    /**
     * Render a specific block.
     *
     * @param string $name Block name
     * @param array<string, mixed> $context Block context
     *
     * @return string Rendered block content
     */
    public function renderBlock(string $name, array $context = []): string;

    /**
     * Get all block names in the template.
     *
     * @return array<string> Block names
     */
    public function getBlockNames(): array;
}
