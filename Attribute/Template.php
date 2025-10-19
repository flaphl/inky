<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\Attribute;

use Attribute;

/**
 * Marks a class as a template component.
 */
#[Attribute(Attribute::TARGET_CLASS)]
class Template
{
    /**
     * Create a new template attribute.
     *
     * @param string|null $name Template name (defaults to class name)
     * @param array<string> $vars Expected variables
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly array $vars = []
    ) {
    }
}
