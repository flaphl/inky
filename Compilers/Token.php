<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\Compilers;

/**
 * Token implementation.
 */
class Token implements TokenInterface
{
    /**
     * Create a new token.
     *
     * @param TokenType $type Token type
     * @param string $value Token value
     * @param int $line Line number
     * @param int $column Column number
     */
    public function __construct(
        private readonly TokenType $type,
        private readonly string $value,
        private readonly int $line,
        private readonly int $column
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getType(): TokenType
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * {@inheritdoc}
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumn(): int
    {
        return $this->column;
    }

    /**
     * {@inheritdoc}
     */
    public function is(TokenType $type): bool
    {
        return $this->type === $type;
    }

    /**
     * {@inheritdoc}
     */
    public function getPosition(): array
    {
        return [
            'line' => $this->line,
            'column' => $this->column,
        ];
    }

    /**
     * String representation of token.
     *
     * @return string Token description
     */
    public function __toString(): string
    {
        return sprintf(
            '%s("%s") at line %d, column %d',
            $this->type->label(),
            $this->value,
            $this->line,
            $this->column
        );
    }
}
