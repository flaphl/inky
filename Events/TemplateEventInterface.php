<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\Events;

use Flaphl\Fridge\Inky\Engine\TemplateInterface;

/**
 * Base event for template rendering.
 */
interface TemplateEventInterface
{
    /**
     * Get the template instance.
     *
     * @return TemplateInterface The template
     */
    public function getTemplate(): TemplateInterface;

    /**
     * Get the template name.
     *
     * @return string Template name
     */
    public function getTemplateName(): string;

    /**
     * Get the template context.
     *
     * @return array<string, mixed> Context variables
     */
    public function getContext(): array;

    /**
     * Set the entire template context.
     *
     * @param array<string, mixed> $context Context variables
     *
     * @return void
     */
    public function setContext(array $context): void;

    /**
     * Merge variables into the template context.
     *
     * @param array<string, mixed> $context Additional context variables
     *
     * @return void
     */
    public function mergeContext(array $context): void;

    /**
     * Get a specific context value.
     *
     * @param string $name Variable name
     * @param mixed $default Default value if not found
     *
     * @return mixed The context value or default
     */
    public function getContextValue(string $name, mixed $default = null): mixed;

    /**
     * Set a specific context value.
     *
     * @param string $name Variable name
     * @param mixed $value Variable value
     *
     * @return void
     */
    public function setContextValue(string $name, mixed $value): void;
}
