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
 * Base template event implementation.
 */
class TemplateEvent implements TemplateEventInterface
{
    /**
     * Create a new template event.
     *
     * @param TemplateInterface $template Template instance
     * @param string $templateName Template name
     * @param array<string, mixed> $context Template context
     */
    public function __construct(
        private readonly TemplateInterface $template,
        private readonly string $templateName,
        private array $context
    ) {
    }

    /**
     * Get the template instance.
     *
     * @return TemplateInterface
     */
    public function getTemplate(): TemplateInterface
    {
        return $this->template;
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplateName(): string
    {
        return $this->templateName;
    }

    /**
     * Get the template context.
     *
     * @return array<string, mixed>
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Set the template context.
     *
     * @param array<string, mixed> $context
     *
     * @return void
     */
    public function setContext(array $context): void
    {
        $this->context = $context;
    }

    /**
     * Merge additional context.
     *
     * @param array<string, mixed> $context
     *
     * @return void
     */
    public function mergeContext(array $context): void
    {
        $this->context = array_merge($this->context, $context);
    }

    /**
     * Get a single context value.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return mixed
     */
    public function getContextValue(string $key, mixed $default = null): mixed
    {
        return $this->context[$key] ?? $default;
    }

    /**
     * Set a single context value.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public function setContextValue(string $key, mixed $value): void
    {
        $this->context[$key] = $value;
    }
}
