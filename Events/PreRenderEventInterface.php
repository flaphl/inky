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

/**
 * Dispatched before template rendering.
 */
interface PreRenderEventInterface extends TemplateEventInterface
{
    /**
     * Stop event propagation.
     *
     * @return void
     */
    public function stopPropagation(): void;

    /**
     * Check if event propagation is stopped.
     *
     * @return bool True if propagation stopped
     */
    public function isPropagationStopped(): bool;

    /**
     * Prevent the template from rendering.
     *
     * @return void
     */
    public function preventRendering(): void;

    /**
     * Check if rendering should be prevented.
     *
     * @return bool True if rendering prevented
     */
    public function isRenderingPrevented(): bool;

    /**
     * Set pre-rendered content (bypasses template).
     *
     * @param string $content Pre-rendered content
     *
     * @return void
     */
    public function setPreRenderedContent(string $content): void;

    /**
     * Get pre-rendered content.
     *
     * @return string|null Pre-rendered content or null
     */
    public function getPreRenderedContent(): ?string;
}
