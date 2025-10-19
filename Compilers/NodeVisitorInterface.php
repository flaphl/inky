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
 * Visits and transforms nodes in the AST.
 */
interface NodeVisitorInterface
{
    /**
     * Visit a node before its children.
     *
     * @param NodeInterface $node The node
     *
     * @return NodeInterface Modified node
     */
    public function enterNode(NodeInterface $node): NodeInterface;

    /**
     * Visit a node after its children.
     *
     * @param NodeInterface $node The node
     *
     * @return NodeInterface Modified node
     */
    public function leaveNode(NodeInterface $node): NodeInterface;

    /**
     * Get the priority of this visitor.
     *
     * Higher priority visitors run first.
     *
     * @return int Priority (0 = default)
     */
    public function getPriority(): int;
}
