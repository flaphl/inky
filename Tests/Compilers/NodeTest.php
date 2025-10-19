<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\Tests\Compilers;

use PHPUnit\Framework\TestCase;
use Flaphl\Fridge\Inky\Compilers\{
    TextNode, VarNode, RawNode, CommentNode, DirectiveNode, BlockNode, RootNode,
    Compiler, Lexer, Parser
};

class NodeTest extends TestCase
{
    private Compiler $compiler;

    protected function setUp(): void
    {
        $this->compiler = new Compiler(new Lexer(), new Parser());
    }

    public function testTextNodeConstruction(): void
    {
        $node = new TextNode('Hello', 1);
        $this->assertEquals('text', $node->getType());
        $this->assertEquals('Hello', $node->getText());
        $this->assertEquals(1, $node->getLine());
    }

    public function testTextNodeCompile(): void
    {
        $node = new TextNode('Test text', 1);
        $compiled = $node->compile($this->compiler);
        
        $this->assertIsString($compiled);
        $this->assertStringContainsString('Test text', $compiled);
    }

    public function testVarNodeConstruction(): void
    {
        $node = new VarNode('name', 1);
        $this->assertEquals('var', $node->getType());
        $this->assertEquals('name', $node->getExpression());
    }

    public function testVarNodeCompile(): void
    {
        $node = new VarNode('name', 1);
        $compiled = $node->compile($this->compiler);
        
        $this->assertIsString($compiled);
        $this->assertStringContainsString('<?php', $compiled);
        $this->assertStringContainsString('echo', $compiled);
    }

    public function testRawNodeConstruction(): void
    {
        $node = new RawNode('html', 1);
        $this->assertEquals('raw', $node->getType());
        $this->assertEquals('html', $node->getExpression());
    }

    public function testRawNodeCompile(): void
    {
        $node = new RawNode('content', 1);
        $compiled = $node->compile($this->compiler);
        
        $this->assertIsString($compiled);
        $this->assertStringContainsString('<?php', $compiled);
        $this->assertStringContainsString('echo', $compiled);
    }

    public function testCommentNodeConstruction(): void
    {
        $node = new CommentNode('This is a comment', 1);
        $this->assertEquals('comment', $node->getType());
        $this->assertEquals('This is a comment', $node->getComment());
    }

    public function testCommentNodeCompile(): void
    {
        $node = new CommentNode('comment text', 1);
        $compiled = $node->compile($this->compiler);
        
        $this->assertIsString($compiled);
        $this->assertStringContainsString('/*', $compiled);
        $this->assertStringContainsString('comment text', $compiled);
    }

    public function testDirectiveNodeConstruction(): void
    {
        $node = new DirectiveNode('if', '$condition', 1);
        $this->assertEquals('directive', $node->getType());
        $this->assertEquals('if', $node->getName());
        $this->assertEquals('$condition', $node->getArguments());
    }

    public function testDirectiveNodeCompile(): void
    {
        $node = new DirectiveNode('if', '$test', 1);
        $compiled = $node->compile($this->compiler);
        
        $this->assertIsString($compiled);
        $this->assertStringContainsString('<?php', $compiled);
    }

    public function testBlockNodeConstruction(): void
    {
        $node = new BlockNode('content', 1);
        $this->assertEquals('block', $node->getType());
        $this->assertEquals('content', $node->getName());
    }

    public function testBlockNodeCompile(): void
    {
        $node = new BlockNode('sidebar', 1);
        $compiled = $node->compile($this->compiler);
        
        $this->assertIsString($compiled);
        $this->assertStringContainsString('registerBlock', $compiled);
        $this->assertStringContainsString('sidebar', $compiled);
    }

    public function testRootNodeConstruction(): void
    {
        $node = new RootNode();
        $this->assertEquals('root', $node->getType());
        $this->assertEquals(1, $node->getLine());
    }

    public function testRootNodeWithChildren(): void
    {
        $child1 = new TextNode('Hello', 1);
        $child2 = new VarNode('name', 1);
        
        $node = new RootNode([$child1, $child2]);
        $children = $node->getChildren();
        
        $this->assertCount(2, $children);
        $this->assertSame($child1, $children[0]);
        $this->assertSame($child2, $children[1]);
    }

    public function testRootNodeCompile(): void
    {
        $child = new TextNode('Test', 1);
        $node = new RootNode([$child]);
        
        $compiled = $node->compile($this->compiler);
        $this->assertIsString($compiled);
    }

    public function testNodeGetChildren(): void
    {
        $node = new RootNode([]);
        $this->assertIsArray($node->getChildren());
        $this->assertEmpty($node->getChildren());
    }

    public function testNodeGetLine(): void
    {
        $node = new TextNode('test', 42);
        $this->assertEquals(42, $node->getLine());
    }

    public function testNodeAcceptVisitor(): void
    {
        $visitor = new class implements \Flaphl\Fridge\Inky\Compilers\NodeVisitorInterface {
            public function enterNode(\Flaphl\Fridge\Inky\Compilers\NodeInterface $node): \Flaphl\Fridge\Inky\Compilers\NodeInterface {
                return $node;
            }
            
            public function leaveNode(\Flaphl\Fridge\Inky\Compilers\NodeInterface $node): \Flaphl\Fridge\Inky\Compilers\NodeInterface {
                return $node;
            }
            
            public function getPriority(): int {
                return 0;
            }
        };
        
        $node = new TextNode('test', 1);
        $result = $node->accept($visitor);
        
        $this->assertInstanceOf(\Flaphl\Fridge\Inky\Compilers\NodeInterface::class, $result);
    }
}
