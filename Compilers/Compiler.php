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

use Flaphl\Fridge\Inky\Engine\EscaperInterface;

/**
 * Template compiler implementation.
 */
class Compiler implements CompilerInterface
{
    private array $directives = [];
    private ?EscaperInterface $escaper = null;
    private readonly LexerInterface $lexer;
    private readonly ParserInterface $parser;

    /**
     * Create a new compiler instance.
     *
     * @param LexerInterface|null $lexer Token lexer (optional, creates default if not provided)
     * @param ParserInterface|null $parser AST parser (optional, creates default if not provided)
     */
    public function __construct(
        ?LexerInterface $lexer = null,
        ?ParserInterface $parser = null
    ) {
        $this->lexer = $lexer ?? new Lexer();
        $this->parser = $parser ?? new Parser();
        $this->registerDefaultDirectives();
    }

    /**
     * {@inheritdoc}
     */
    public function compile(string $source, string $name = 'unknown'): string
    {
        // Tokenize the source
        $tokens = $this->lexer->tokenize($source, $name);
        
        // Parse tokens into AST
        $ast = $this->parser->parse($tokens);
        
        // Compile AST to PHP
        $compiled = $ast->compile($this);
        
        // Wrap in template class
        return $this->wrapInTemplate($compiled, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function addDirective(string $name, callable $handler): self
    {
        $this->directives[$name] = $handler;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasDirective(string $name): bool
    {
        return isset($this->directives[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function getDirective(string $name): ?callable
    {
        return $this->directives[$name] ?? null;
    }

    /**
     * {@inheritdoc}
     */
    public function setEscaper(EscaperInterface $escaper): self
    {
        $this->escaper = $escaper;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getEscaper(): ?EscaperInterface
    {
        return $this->escaper;
    }

    /**
     * {@inheritdoc}
     */
    public function getCompiledClassName(string $name): string
    {
        return 'Template_' . md5($name);
    }

    /**
     * {@inheritdoc}
     */
    public function removeDirective(string $name): self
    {
        unset($this->directives[$name]);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDirectives(): array
    {
        return $this->directives;
    }

    /**
     * Compile a directive.
     *
     * @param string $name Directive name
     * @param string $content Directive content
     *
     * @return string Compiled PHP code
     */
    public function compileDirective(string $name, string $content): string
    {
        if (!$this->hasDirective($name)) {
            return "<?php // Unknown directive: @{$name} ?>";
        }

        return ($this->directives[$name])($content, $this);
    }

    /**
     * Escape a value for output.
     *
     * @param string $value Value expression
     * @param string $strategy Escaping strategy
     *
     * @return string Escaped expression
     */
    public function escape(string $value, string $strategy = 'html'): string
    {
        if ($this->escaper === null) {
            return $value;
        }

        return sprintf('$this->getEngine()->getEscaper()->escape(%s, %s)', $value, var_export($strategy, true));
    }

    /**
     * Register default directives.
     *
     * @return void
     */
    private function registerDefaultDirectives(): void
    {
        // @if directive
        $this->addDirective('if', function ($content) {
            return "<?php if ({$content}): ?>";
        });

        // @else directive
        $this->addDirective('else', function ($content) {
            return "<?php else: ?>";
        });

        // @endif directive
        $this->addDirective('endif', function ($content) {
            return "<?php endif; ?>";
        });

        // @foreach directive
        $this->addDirective('foreach', function ($content) {
            return "<?php foreach ({$content}): ?>";
        });

        // @endforeach directive
        $this->addDirective('endforeach', function ($content) {
            return "<?php endforeach; ?>";
        });

        // @for directive
        $this->addDirective('for', function ($content) {
            return "<?php for ({$content}): ?>";
        });

        // @endfor directive
        $this->addDirective('endfor', function ($content) {
            return "<?php endfor; ?>";
        });

        // @while directive
        $this->addDirective('while', function ($content) {
            return "<?php while ({$content}): ?>";
        });

        // @endwhile directive
        $this->addDirective('endwhile', function ($content) {
            return "<?php endwhile; ?>";
        });
    }

    /**
     * Wrap compiled code in template class.
     *
     * @param string $compiled Compiled PHP code
     * @param string $name Template name
     *
     * @return string Complete PHP class
     */
    private function wrapInTemplate(string $compiled, string $name): string
    {
        return <<<PHP
<?php

namespace Flaphl\Fridge\Inky\Generated;

use Flaphl\Fridge\Inky\Engine\Template;

class CompiledTemplate extends Template
{
    protected function doDisplay(array \$context): void
    {
        extract(\$context);
        {$compiled}
    }
}

return new CompiledTemplate(\$this, '{$name}');
PHP;
    }
}
