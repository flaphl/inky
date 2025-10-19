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
 * Base abstract node implementation.
 */
abstract class Node implements NodeInterface
{
    /**
     * Create a new node.
     *
     * @param int $line Line number
     * @param array<NodeInterface> $children Child nodes
     */
    public function __construct(
        protected readonly int $line,
        protected array $children = []
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getChildren(): array
    {
        return $this->children;
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
    abstract public function getType(): string;

    /**
     * {@inheritdoc}
     */
    abstract public function compile(CompilerInterface $compiler): string;

    /**
     * {@inheritdoc}
     */
    public function accept(NodeVisitorInterface $visitor): NodeInterface
    {
        $node = $visitor->enterNode($this);
        
        // Visit children
        foreach ($this->children as $i => $child) {
            $this->children[$i] = $child->accept($visitor);
        }
        
        return $visitor->leaveNode($node);
    }
}
