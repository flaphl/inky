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
 * Defines a template slot for component composition.
 */
#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Slot
{
    /**
     * Create a new slot attribute.
     *
     * @param string|null $name Slot name (defaults to property name)
     * @param bool $required Whether slot content is required
     */
    public function __construct(
        public readonly ?string $name = null,
        public readonly bool $required = false
    ) {
    }
}
