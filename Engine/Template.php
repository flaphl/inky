<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\Engine;

use Flaphl\Fridge\Inky\Exception\InkyException;

/**
 * Default implementation of a compiled template.
 */
class Template implements TemplateInterface
{
    private array $blocks = [];

    /**
     * Create a new template instance.
     *
     * @param EngineInterface $engine The template engine
     * @param string $name Template name
     */
    public function __construct(
        private readonly EngineInterface $engine,
        private readonly string $name
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function render(array $context = []): string
    {
        ob_start();
        try {
            $this->display($context);
            return ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function display(array $context = []): void
    {
        $this->doDisplay($context);
    }

    /**
     * {@inheritdoc}
     */
    public function hasBlock(string $name): bool
    {
        return isset($this->blocks[$name]);
    }

    /**
     * {@inheritdoc}
     */
    public function renderBlock(string $name, array $context = []): string
    {
        if (!$this->hasBlock($name)) {
            throw new \InvalidArgumentException(sprintf('Block "%s" does not exist', $name));
        }

        ob_start();
        try {
            ($this->blocks[$name])($context);
            return ob_get_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockNames(): array
    {
        return array_keys($this->blocks);
    }

    /**
     * Display the template content.
     *
     * Override this method in generated templates.
     *
     * @param array<string, mixed> $context Template variables
     *
     * @return void
     */
    protected function doDisplay(array $context): void
    {
        // Simple proof-of-concept: render basic template features
        // In production, this would be replaced by compiled PHP code
        try {
            $source = $this->engine->getLoader()->getSource($this->name);
            
            // Remove comments {{ # ... }}
            $source = preg_replace('/\{\{\s*#[^}]*\}\}/', '', $source);
            
            // Remove {{ }} wrappers from directives to simplify processing
            $source = preg_replace('/\{\{\s*(@\w+[^}]*)\}\}/', '$1', $source);
            
            // Handle template inheritance
            if (preg_match('/@extends\s+"([^"]+)"/', $source, $extendsMatch)) {
                $parentName = $extendsMatch[1];
                
                // Extract child blocks
                $childBlocks = [];
                preg_match_all('/@block\s+(\w+)\s*(.*?)@endblock/s', $source, $blockMatches, PREG_SET_ORDER);
                foreach ($blockMatches as $match) {
                    $childBlocks[$match[1]] = trim($match[2]);
                }
                
                // Load parent template
                $parentSource = $this->engine->getLoader()->getSource($parentName);
                $parentSource = preg_replace('/\{\{\s*#[^}]*\}\}/', '', $parentSource);
                $parentSource = preg_replace('/\{\{\s*(@\w+[^}]*)\}\}/', '$1', $parentSource);
                
                // Replace blocks in parent with child blocks
                $source = preg_replace_callback('/@block\s+(\w+)\s*(.*?)@endblock/s', function($match) use ($childBlocks) {
                    $blockName = $match[1];
                    // Use child block if defined, otherwise use parent's default
                    return isset($childBlocks[$blockName]) ? $childBlocks[$blockName] : trim($match[2]);
                }, $parentSource);
            }
            
            // Handle @if directives
            $source = preg_replace_callback('/@if\s*\(\s*\$(\w+)\s*\)(.*?)@endif/s', function($matches) use ($context) {
                $var = $matches[1];
                $content = $matches[2];
                return !empty($context[$var]) ? $content : '';
            }, $source);
            
            // Handle @foreach directives
            $source = preg_replace_callback('/@foreach\s+\$(\w+)\s+as\s+\$(\w+)\s*(.*?)@endforeach/s', function($matches) use ($context) {
                $array = $matches[1];
                $item = $matches[2];
                $content = trim($matches[3]);
                
                if (!isset($context[$array]) || !is_array($context[$array])) {
                    return '';
                }
                
                $output = '';
                foreach ($context[$array] as $value) {
                    $itemContext = array_merge($context, [$item => $value]);
                    // Replace {{ $item }} or {{ item }} within the loop
                    $itemOutput = preg_replace_callback('/\{\{\s*\$?(\w+)\s*\}\}/', function($m) use ($itemContext) {
                        return isset($itemContext[$m[1]]) ? htmlspecialchars((string)$itemContext[$m[1]], ENT_QUOTES, 'UTF-8') : '';
                    }, $content);
                    $output .= $itemOutput;
                }
                return $output;
            }, $source);
            
            // Handle @include directives
            $source = preg_replace_callback('/@include\s+"([^"]+)"/', function($matches) use ($context) {
                $templateName = $matches[1];
                try {
                    return $this->engine->render($templateName, $context);
                } catch (\Throwable $e) {
                    return '';
                }
            }, $source);
            
            // Handle raw output {{ ! var }}
            $source = preg_replace_callback('/\{\{\s*!\s*(\w+)\s*\}\}/', function($matches) use ($context) {
                $var = $matches[1];
                return isset($context[$var]) ? (string)$context[$var] : '';
            }, $source);
            
            // Handle chained filters {{ var | filter1 | filter2 }}
            $source = preg_replace_callback('/\{\{\s*(\w+)\s*(\|[\s\w\|]+)\s*\}\}/', function($matches) use ($context) {
                $var = $matches[1];
                $filterChain = $matches[2];
                
                if (!isset($context[$var])) {
                    if ($this->engine->isStrictVariables()) {
                        throw InkyException::forUndefinedVariable($var, $this->name);
                    }
                    return '';
                }
                
                $value = $context[$var];
                
                // Split filters by |
                $filters = array_filter(array_map('trim', explode('|', $filterChain)));
                
                // Apply each filter in sequence
                foreach ($filters as $filterName) {
                    foreach ($this->engine->getExtensions() as $extension) {
                        $extensionFilters = $extension->getFilters();
                        if (isset($extensionFilters[$filterName])) {
                            $value = $extensionFilters[$filterName]($value);
                            break;
                        }
                    }
                }
                
                return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
            }, $source);
            
            // Handle single filters {{ var | filter }}
            $source = preg_replace_callback('/\{\{\s*(\w+)\s*\|\s*(\w+)\s*\}\}/', function($matches) use ($context) {
                $var = $matches[1];
                $filter = $matches[2];
                
                if (!isset($context[$var])) {
                    if ($this->engine->isStrictVariables()) {
                        throw InkyException::forUndefinedVariable($var, $this->name);
                    }
                    return '';
                }
                
                $value = $context[$var];
                
                // Get filter from extensions
                foreach ($this->engine->getExtensions() as $extension) {
                    $filters = $extension->getFilters();
                    if (isset($filters[$filter])) {
                        $value = $filters[$filter]($value);
                        break;
                    }
                }
                
                return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
            }, $source);
            
            // Handle nested access {{ user.name }}
            $source = preg_replace_callback('/\{\{\s*(\w+)\.(\w+)\s*\}\}/', function($matches) use ($context) {
                $obj = $matches[1];
                $prop = $matches[2];
                
                if (isset($context[$obj])) {
                    if (is_array($context[$obj]) && isset($context[$obj][$prop])) {
                        return htmlspecialchars((string)$context[$obj][$prop], ENT_QUOTES, 'UTF-8');
                    } elseif (is_object($context[$obj]) && isset($context[$obj]->$prop)) {
                        return htmlspecialchars((string)$context[$obj]->$prop, ENT_QUOTES, 'UTF-8');
                    }
                }
                return '';
            }, $source);
            
            // Handle array access {{ items[0] }}
            $source = preg_replace_callback('/\{\{\s*(\w+)\[([0-9]+)\]\s*\}\}/', function($matches) use ($context) {
                $var = $matches[1];
                $index = (int)$matches[2];
                
                if (isset($context[$var]) && is_array($context[$var]) && isset($context[$var][$index])) {
                    return htmlspecialchars((string)$context[$var][$index], ENT_QUOTES, 'UTF-8');
                }
                return '';
            }, $source);
            
            // Handle simple variable substitution {{ var }}
            $source = preg_replace_callback('/\{\{\s*(\w+)\s*\}\}/', function($matches) use ($context) {
                $var = $matches[1];
                if (!isset($context[$var])) {
                    if ($this->engine->isStrictVariables()) {
                        throw InkyException::forUndefinedVariable($var, $this->name);
                    }
                    return '';
                }
                return htmlspecialchars((string)$context[$var], ENT_QUOTES, 'UTF-8');
            }, $source);
            
            echo $source;
        } catch (InkyException $e) {
            // Re-throw strict mode violations (undefined variable exceptions)
            if (str_contains($e->getMessage(), 'Undefined variable')) {
                throw $e;
            }
            // Otherwise suppress (template loading errors)
        } catch (\Throwable $e) {
            // If we can't load source, just return empty (compiled template scenario)
        }
    }

    /**
     * Register a block.
     *
     * @param string $name Block name
     * @param callable $block Block renderer
     *
     * @return void
     */
    protected function registerBlock(string $name, callable $block): void
    {
        $this->blocks[$name] = $block;
    }

    /**
     * Get the template name.
     *
     * @return string Template name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getSource(): string
    {
        return ''; // Generated templates don't store source
    }

    /**
     * Get the engine instance.
     *
     * @return EngineInterface Engine
     */
    protected function getEngine(): EngineInterface
    {
        return $this->engine;
    }
}
