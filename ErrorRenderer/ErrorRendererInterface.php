<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\ErrorRenderer;

use Throwable;

/**
 * Renders template errors in a user-friendly format.
 */
interface ErrorRendererInterface
{
    /**
     * Render an exception with template context.
     *
     * @param Throwable $exception The exception to render
     * @param string|null $template Template name
     * @param int|null $line Line number where error occurred
     *
     * @return string Rendered error output
     */
    public function render(Throwable $exception, ?string $template = null, ?int $line = null): string;

    /**
     * Check if this renderer can handle the exception.
     *
     * @param Throwable $exception The exception
     *
     * @return bool True if this renderer can handle it
     */
    public function canRender(Throwable $exception): bool;

    /**
     * Set the output format.
     *
     * @param string $format Format ('html', 'text', 'json')
     *
     * @return self
     */
    public function setFormat(string $format): self;

    /**
     * Enable or disable debug mode.
     *
     * @param bool $debug Debug mode enabled
     *
     * @return self
     */
    public function setDebug(bool $debug): self;
}
