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
 * Dispatched after template rendering.
 */
interface PostRenderEventInterface extends TemplateEventInterface
{
    /**
     * Get the rendered output.
     *
     * @return string Rendered output
     */
    public function getOutput(): string;

    /**
     * Set the rendered output.
     *
     * @param string $output Output content
     *
     * @return void
     */
    public function setOutput(string $output): void;

    /**
     * Modify the rendered output with a callback.
     *
     * @param callable $callback Callback that receives output and returns modified output
     *
     * @return void
     */
    public function modifyOutput(callable $callback): void;
}
