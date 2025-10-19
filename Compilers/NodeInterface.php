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
 * Represents a node in the abstract syntax tree.
 */
interface NodeInterface
{
    /**
     * Compile the node to PHP code.
     *
     * @param CompilerInterface $compiler The compiler
     *
     * @return string Compiled PHP code
     */
    public function compile(CompilerInterface $compiler): string;

    /**
     * Get child nodes.
     *
     * @return array<NodeInterface> Child nodes
     */
    public function getChildren(): array;

    /**
     * Get the line number where this node appears.
     *
     * @return int Line number
     */
    public function getLine(): int;

    /**
     * Get the node type.
     *
     * @return string Node type identifier
     */
    public function getType(): string;

    /**
     * Accept a visitor.
     *
     * @param NodeVisitorInterface $visitor The visitor
     *
     * @return NodeInterface Modified node
     */
    public function accept(NodeVisitorInterface $visitor): NodeInterface;
}
