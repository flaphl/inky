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
 * Template engine for rendering templates.
 */
interface EngineInterface
{
    /**
     * Render a template.
     *
     * @param string $name Template name/path
     * @param array<string, mixed> $context Template variables
     *
     * @return string Rendered output
     */
    public function render(string $name, array $context = []): string;

    /**
     * Check if a template exists.
     *
     * @param string $name Template name/path
     *
     * @return bool True if template exists
     */
    public function exists(string $name): bool;

    /**
     * Load a template.
     *
     * @param string $name Template name/path
     *
     * @return TemplateInterface The template instance
     */
    public function load(string $name): TemplateInterface;

    /**
     * Add a global variable available to all templates.
     *
     * @param string $name Variable name
     * @param mixed $value Variable value
     *
     * @return self
     */
    public function addGlobal(string $name, mixed $value): self;

    /**
     * Get all global variables.
     *
     * @return array<string, mixed> Global variables
     */
    public function getGlobals(): array;

    /**
     * Add a template path.
     *
     * @param string $path Template directory path
     * @param string|null $namespace Optional namespace
     *
     * @return self
     */
    public function addPath(string $path, ?string $namespace = null): self;

    /**
     * Get template paths.
     *
     * @param string|null $namespace Optional namespace filter
     *
     * @return array<string> Template paths
     */
    public function getPaths(?string $namespace = null): array;

    /**
     * Enable or disable strict variables mode.
     *
     * @param bool $strict True to enable strict mode
     *
     * @return self
     */
    public function setStrictVariables(bool $strict): self;

    /**
     * Check if strict variables mode is enabled.
     *
     * @return bool True if strict mode is enabled
     */
    public function isStrictVariables(): bool;
}
