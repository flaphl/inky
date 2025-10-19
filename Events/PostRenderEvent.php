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
 * Post-render event implementation.
 */
class PostRenderEvent extends TemplateEvent implements PostRenderEventInterface
{
    private string $output;

    /**
     * Create a new post-render event.
     *
     * @param TemplateInterface $template Template instance
     * @param string $templateName Template name
     * @param array<string, mixed> $context Template context
     * @param string $output Rendered output
     */
    public function __construct(
        TemplateInterface $template,
        string $templateName,
        array $context,
        string $output
    ) {
        parent::__construct($template, $templateName, $context);
        $this->output = $output;
    }

    /**
     * Get the rendered output.
     *
     * @return string
     */
    public function getOutput(): string
    {
        return $this->output;
    }

    /**
     * Set the rendered output.
     *
     * @param string $output
     *
     * @return void
     */
    public function setOutput(string $output): void
    {
        $this->output = $output;
    }

    /**
     * Modify the output using a callback.
     *
     * @param callable $callback
     *
     * @return void
     */
    public function modifyOutput(callable $callback): void
    {
        $this->output = $callback($this->output);
    }
}
