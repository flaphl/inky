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
 * Marks a property as a template variable/prop.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Prop
{
    /**
     * Create a new prop attribute.
     *
     * @param string|null $name Prop name (defaults to property name)
     * @param mixed $default Default value if not provided
     * @param bool $required Whether prop is required
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly mixed $default = null,
        public readonly bool $required = false
    ) {
    }
}
