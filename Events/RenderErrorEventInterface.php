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

/**
 * Dispatched when template rendering fails.
 */
interface RenderErrorEventInterface extends TemplateEventInterface
{
    /**
     * Get the exception that occurred.
     *
     * @return Throwable The exception
     */
    public function getException(): Throwable;

    /**
     * Set a fallback output to use instead of exception.
     *
     * @param string $output Fallback output
     *
     * @return void
     */
    public function setFallbackOutput(string $output): void;

    /**
     * Get fallback output.
     *
     * @return string|null Fallback output or null
     */
    public function getFallbackOutput(): ?string;

    /**
     * Check if error has been handled.
     *
     * @return bool True if handled
     */
    public function isHandled(): bool;

    /**
     * Mark the error as handled.
     *
     * @param bool $handled Whether error is handled
     *
     * @return void
     */
    public function setHandled(bool $handled): void;
}
