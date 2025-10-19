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
 * Pre-render event implementation.
 */
class PreRenderEvent extends TemplateEvent implements PreRenderEventInterface
{
    private bool $renderingPrevented = false;
    private ?string $preRenderedContent = null;
    private bool $propagationStopped = false;

    /**
     * {@inheritdoc}
     */
    public function preventRendering(): void
    {
        $this->renderingPrevented = true;
    }

    /**
     * {@inheritdoc}
     */
    public function isRenderingPrevented(): bool
    {
        return $this->renderingPrevented;
    }

    /**
     * {@inheritdoc}
     */
    public function setPreRenderedContent(string $content): void
    {
        $this->preRenderedContent = $content;
        $this->preventRendering();
    }

    /**
     * {@inheritdoc}
     */
    public function getPreRenderedContent(): ?string
    {
        return $this->preRenderedContent;
    }

    /**
     * Stop event propagation.
     *
     * @return void
     */
    public function stopPropagation(): void
    {
        $this->propagationStopped = true;
    }

    /**
     * Check if propagation is stopped.
     *
     * @return bool
     */
    public function isPropagationStopped(): bool
    {
        return $this->propagationStopped;
    }
}
