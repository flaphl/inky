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

use Flaphl\Fridge\Inky\Compilers\CompilerInterface;
use Flaphl\Fridge\Inky\Exception\InkyException;

/**
 * Main template engine implementation.
 */
class Engine implements EngineInterface
{
    private array $globals = [];
    private array $extensions = [];
    private bool $strictVariables = false;
    private bool $debug = false;
    private bool $autoReload = true;
    private ?object $eventDispatcher = null;
    private readonly CompilerInterface $compiler;

    /**
     * Create a new engine instance.
     *
     * @param LoaderInterface $loader Template loader
     * @param CompilerInterface|null $compiler Template compiler (optional, creates default if not provided)
     */
    public function __construct(
        private readonly LoaderInterface $loader,
        ?CompilerInterface $compiler = null
    ) {
        $this->compiler = $compiler ?? new \Flaphl\Fridge\Inky\Compilers\Compiler();
    }

    /**
     * {@inheritdoc}
     */
    public function render(string $name, array $variables = []): string
    {
        try {
            $template = $this->load($name);
            $mergedVariables = array_merge($this->globals, $variables);
            
            // Dispatch pre-render event
            if ($this->eventDispatcher !== null) {
                $preEvent = new \Flaphl\Fridge\Inky\Events\PreRenderEvent($template, $name, $mergedVariables);
                $this->eventDispatcher->dispatch($preEvent);
            }
            
            $output = $template->render($mergedVariables);
            
            // Dispatch post-render event
            if ($this->eventDispatcher !== null) {
                $postEvent = new \Flaphl\Fridge\Inky\Events\PostRenderEvent($template, $name, $mergedVariables, $output);
                $this->eventDispatcher->dispatch($postEvent);
            }
            
            return $output;
        } catch (\Throwable $e) {
            // Dispatch error event if we have a template
            if ($this->eventDispatcher !== null && isset($template)) {
                $errorEvent = new \Flaphl\Fridge\Inky\Events\RenderErrorEvent(
                    $template, 
                    $name, 
                    $mergedVariables ?? array_merge($this->globals, $variables),
                    $e
                );
                $this->eventDispatcher->dispatch($errorEvent);
            }
            throw $e;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load(string $name): TemplateInterface
    {
        if (!$this->exists($name)) {
            throw InkyException::templateNotFound($name, $this->loader->getPaths());
        }

        // Get source and compile
        $source = $this->loader->getSource($name);
        $cacheKey = $this->loader->getCacheKey($name);
        
        // Compile the template
        $compiled = $this->compiler->compile($source, $name);
        
        // Evaluate and return template instance
        return $this->evaluateTemplate($compiled, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function exists(string $name): bool
    {
        return $this->loader->exists($name);
    }

    /**
     * {@inheritdoc}
     */
    public function addGlobal(string $name, mixed $value): self
    {
        $this->globals[$name] = $value;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobals(): array
    {
        return $this->globals;
    }

    /**
     * Get the loader instance.
     *
     * @return LoaderInterface The loader
     */
    public function getLoader(): LoaderInterface
    {
        return $this->loader;
    }

    /**
     * {@inheritdoc}
     */
    public function addPath(string $path, ?string $namespace = null): self
    {
        if (method_exists($this->loader, 'addPath')) {
            $this->loader->addPath($path);
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getPaths(?string $namespace = null): array
    {
        if (method_exists($this->loader, 'getPaths')) {
            return $this->loader->getPaths();
        }
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function setStrictVariables(bool $strict): self
    {
        $this->strictVariables = $strict;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isStrictVariables(): bool
    {
        return $this->strictVariables;
    }

    /**
     * Add an extension to the engine.
     *
     * @param ExtensionInterface $extension Extension instance
     *
     * @return self
     */
    public function addExtension(ExtensionInterface $extension): self
    {
        $this->extensions[$extension->getName()] = $extension;
        $extension->initialize($this);
        
        // Add extension globals
        foreach ($extension->getGlobals() as $name => $value) {
            $this->addGlobal($name, $value);
        }
        
        return $this;
    }

    /**
     * Get an extension by name.
     *
     * @param string $name Extension name
     *
     * @return ExtensionInterface|null Extension or null
     */
    public function getExtension(string $name): ?ExtensionInterface
    {
        return $this->extensions[$name] ?? null;
    }

    /**
     * Get all registered extensions.
     *
     * @return array<string, ExtensionInterface> Extensions
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Check if an extension is registered.
     *
     * @param string $name Extension name
     *
     * @return bool True if extension exists
     */
    public function hasExtension(string $name): bool
    {
        return isset($this->extensions[$name]);
    }

    /**
     * Check if a filter is registered.
     *
     * @param string $name Filter name
     *
     * @return bool True if filter exists
     */
    public function hasFilter(string $name): bool
    {
        foreach ($this->extensions as $extension) {
            $filters = $extension->getFilters();
            if (isset($filters[$name])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if a function is registered.
     *
     * @param string $name Function name
     *
     * @return bool True if function exists
     */
    public function hasFunction(string $name): bool
    {
        foreach ($this->extensions as $extension) {
            $functions = $extension->getFunctions();
            if (isset($functions[$name])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Register an extension with the engine.
     *
     * @param ExtensionInterface $extension Extension to register
     *
     * @return self
     */
    public function registerExtension(ExtensionInterface $extension): self
    {
        $className = get_class($extension);
        $this->extensions[$className] = $extension;
        $extension->register($this);
        return $this;
    }

    /**
     * Set cache directory for compiled templates.
     *
     * @param string $directory Cache directory path
     *
     * @return self
     */
    public function setCacheDirectory(string $directory): self
    {
        // Delegate to compiler if it supports caching
        if (method_exists($this->compiler, 'setCacheDirectory')) {
            $this->compiler->setCacheDirectory($directory);
        }
        return $this;
    }

    /**
     * Enable or disable debug mode.
     *
     * @param bool $debug Debug mode
     *
     * @return self
     */
    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * Enable or disable auto reload.
     *
     * @param bool $autoReload Auto reload mode
     *
     * @return self
     */
    public function setAutoReload(bool $autoReload): self
    {
        $this->autoReload = $autoReload;
        return $this;
    }

    /**
     * Enable or disable strict variable mode.
     *
     * @param bool $strict Strict mode
     *
     * @return self
     */
    public function setStrictMode(bool $strict): self
    {
        $this->strictVariables = $strict;
        return $this;
    }

    /**
     * Set event dispatcher.
     *
     * @param object|null $dispatcher Event dispatcher
     *
     * @return self
     */
    public function setEventDispatcher(?object $dispatcher): self
    {
        $this->eventDispatcher = $dispatcher;
        return $this;
    }

    /**
     * Evaluate compiled template code.
     *
     * @param string $compiled Compiled PHP code
     * @param string $name Template name
     *
     * @return TemplateInterface Template instance
     */
    private function evaluateTemplate(string $compiled, string $name): TemplateInterface
    {
        // For now, create a simple template instance
        // In production, this would evaluate the compiled PHP code
        return new Template($this, $name);
    }
}
