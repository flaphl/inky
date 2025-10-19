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
 * Root document node.
 */
class RootNode extends Node
{
    public function __construct(array $children = [])
    {
        parent::__construct(1, $children);
    }

    public function getType(): string
    {
        return 'root';
    }

    public function compile(CompilerInterface $compiler): string
    {
        $compiled = '';
        
        foreach ($this->children as $child) {
            $compiled .= $child->compile($compiler);
        }
        
        return $compiled;
    }
}

/**
 * Text node for literal content.
 */
class TextNode extends Node
{
    public function __construct(
        private readonly string $text,
        int $line
    ) {
        parent::__construct($line);
    }

    public function getType(): string
    {
        return 'text';
    }

    public function compile(CompilerInterface $compiler): string
    {
        return var_export($this->text, true) . '; ';
    }

    public function getText(): string
    {
        return $this->text;
    }
}

/**
 * Variable node for outputting values.
 */
class VarNode extends Node
{
    public function __construct(
        private readonly string $expression,
        int $line
    ) {
        parent::__construct($line);
    }

    public function getType(): string
    {
        return 'var';
    }

    public function compile(CompilerInterface $compiler): string
    {
        $escaped = $compiler->escape('$' . $this->expression);
        return "<?php echo {$escaped}; ?>";
    }

    public function getExpression(): string
    {
        return $this->expression;
    }
}

/**
 * Raw/unescaped output node.
 */
class RawNode extends Node
{
    public function __construct(
        private readonly string $expression,
        int $line
    ) {
        parent::__construct($line);
    }

    public function getType(): string
    {
        return 'raw';
    }

    public function compile(CompilerInterface $compiler): string
    {
        return "<?php echo \${$this->expression}; ?>";
    }

    public function getExpression(): string
    {
        return $this->expression;
    }
}

/**
 * Comment node (not output).
 */
class CommentNode extends Node
{
    public function __construct(
        private readonly string $comment,
        int $line
    ) {
        parent::__construct($line);
    }

    public function getType(): string
    {
        return 'comment';
    }

    public function compile(CompilerInterface $compiler): string
    {
        return "<?php /* {$this->comment} */ ?>";
    }

    public function getComment(): string
    {
        return $this->comment;
    }
}

/**
 * Directive node (control structures).
 */
class DirectiveNode extends Node
{
    public function __construct(
        private readonly string $name,
        private readonly string $arguments,
        int $line
    ) {
        parent::__construct($line);
    }

    public function getType(): string
    {
        return 'directive';
    }

    public function compile(CompilerInterface $compiler): string
    {
        return $compiler->compileDirective($this->name, $this->arguments);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getArguments(): string
    {
        return $this->arguments;
    }
}

/**
 * Block node for template sections.
 */
class BlockNode extends Node
{
    public function __construct(
        private readonly string $name,
        int $line,
        array $children = []
    ) {
        parent::__construct($line, $children);
    }

    public function getType(): string
    {
        return 'block';
    }

    public function compile(CompilerInterface $compiler): string
    {
        $compiled = "<?php \$this->registerBlock('{$this->name}', function(\$context) { ?>";
        
        foreach ($this->children as $child) {
            $compiled .= $child->compile($compiler);
        }
        
        $compiled .= "<?php }); ?>";
        
        return $compiled;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
