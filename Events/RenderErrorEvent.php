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

use Throwable;
use Flaphl\Fridge\Inky\Engine\TemplateInterface;

/**
 * Render error event implementation.
 */
class RenderErrorEvent extends TemplateEvent implements RenderErrorEventInterface
{
    private ?string $fallbackOutput = null;
    private bool $handled = false;

    /**
     * Create a new render error event.
     *
     * @param TemplateInterface $template Template instance
     * @param string $templateName Template name
     * @param array<string, mixed> $context Template context
     * @param Throwable $exception The exception
     */
    public function __construct(
        TemplateInterface $template,
        string $templateName,
        array $context,
        private readonly Throwable $exception
    ) {
        parent::__construct($template, $templateName, $context);
    }

    /**
     * Get the exception.
     *
     * @return Throwable
     */
    public function getException(): Throwable
    {
        return $this->exception;
    }

    /**
     * Set fallback output.
     *
     * @param string $output
     *
     * @return void
     */
    public function setFallbackOutput(string $output): void
    {
        $this->fallbackOutput = $output;
        $this->handled = true;
    }

    /**
     * Get fallback output.
     *
     * @return string|null
     */
    public function getFallbackOutput(): ?string
    {
        return $this->fallbackOutput;
    }

    /**
     * Mark the error as handled.
     *
     * @param bool $handled
     *
     * @return void
     */
    public function setHandled(bool $handled): void
    {
        $this->handled = $handled;
    }

    /**
     * Check if the error is handled.
     *
     * @return bool
     */
    public function isHandled(): bool
    {
        return $this->handled;
    }
}
