<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\Exception;

use Exception;

/**
 * Base exception for all Inky template engine errors.
 */
class InkyException extends \RuntimeException
{
    private ?string $templateName = null;
    private ?int $templateLine = null;
    private ?string $sourceContext = null;

    /**
     * Create a new Inky exception.
     *
     * @param string $message Error message
     * @param string|null $templateName Template name
     * @param int|null $templateLine Line number
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message,
        ?string $templateName = null,
        ?int $templateLine = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->templateName = $templateName;
        $this->templateLine = $templateLine;
    }

    /**
     * Get the template name.
     *
     * @return string|null
     */
    public function getTemplateName(): ?string
    {
        return $this->templateName;
    }

    /**
     * Set the template name.
     *
     * @param string $name Template name
     *
     * @return void
     */
    public function setTemplateName(string $name): void
    {
        $this->templateName = $name;
    }

    /**
     * Get the template line number.
     *
     * @return int|null
     */
    public function getTemplateLine(): ?int
    {
        return $this->templateLine;
    }

    /**
     * Set the template line number.
     *
     * @param int $line Line number
     *
     * @return void
     */
    public function setTemplateLine(int $line): void
    {
        $this->templateLine = $line;
    }

    /**
     * Get the source context.
     *
     * @return string|null
     */
    public function getSourceContext(): ?string
    {
        return $this->sourceContext;
    }

    /**
     * Set the source context.
     *
     * @param string $source Source code
     *
     * @return void
     */
    public function setSourceContext(string $source): void
    {
        $this->sourceContext = $source;
    }

    /**
     * Convert exception to string with template context.
     *
     * @return string
     */
    public function __toString(): string
    {
        $str = parent::__toString();
        
        if ($this->templateName !== null) {
            $location = "Template: {$this->templateName}";
            if ($this->templateLine !== null) {
                $location .= " (line {$this->templateLine})";
            }
            $str = $location . "\n" . $str;
        }
        
        return $str;
    }

    /**
     * Create exception for template not found.
     *
     * @param string $name Template name
     * @param array<string> $paths Searched paths
     *
     * @return self
     */
    public static function templateNotFound(string $name, array $paths = []): self
    {
        $message = sprintf('Template not found: %s', $name);
        
        if (!empty($paths)) {
            $message .= sprintf(' (searched in: %s)', implode(', ', $paths));
        }
        
        return new self($message);
    }

    /**
     * Alias for templateNotFound.
     *
     * @param string $name Template name
     *
     * @return self
     */
    public static function forTemplateNotFound(string $name): self
    {
        return self::templateNotFound($name);
    }

    /**
     * Create exception for syntax error.
     *
     * @param string $message Error message
     * @param string $template Template name
     * @param int $line Line number
     *
     * @return self
     */
    public static function syntaxError(string $message, string $template, int $line): self
    {
        return new self(sprintf('Syntax error in "%s" at line %d: %s', $template, $line, $message), $template, $line);
    }

    /**
     * Alias for syntaxError.
     *
     * @param string $message Error message
     * @param string $template Template name
     * @param int $line Line number
     *
     * @return self
     */
    public static function forSyntaxError(string $message, string $template, int $line): self
    {
        return self::syntaxError($message, $template, $line);
    }

    /**
     * Create exception for runtime error.
     *
     * @param string $message Error message
     * @param string $template Template name
     * @param int|null $line Line number
     *
     * @return self
     */
    public static function runtimeError(string $message, string $template, ?int $line = null): self
    {
        return new self(sprintf('Runtime error in "%s": %s', $template, $message), $template, $line);
    }

    /**
     * Alias for runtimeError.
     *
     * @param string $message Error message
     * @param string $template Template name
     * @param int|null $line Line number
     *
     * @return self
     */
    public static function forRuntimeError(string $message, string $template, ?int $line = null): self
    {
        return self::runtimeError($message, $template, $line);
    }

    /**
     * Create exception for undefined variable.
     *
     * @param string $variable Variable name
     * @param string $template Template name
     * @param int|null $line Line number
     *
     * @return self
     */
    public static function undefinedVariable(string $variable, string $template, ?int $line = null): self
    {
        return new self(sprintf('Undefined variable "%s" in template "%s"', $variable, $template), $template, $line);
    }

    /**
     * Alias for undefinedVariable.
     *
     * @param string $variable Variable name
     * @param string $template Template name
     * @param int|null $line Line number
     *
     * @return self
     */
    public static function forUndefinedVariable(string $variable, string $template, ?int $line = null): self
    {
        return self::undefinedVariable($variable, $template, $line);
    }

    /**
     * Create exception for undefined filter.
     *
     * @param string $filter Filter name
     * @param string|null $template Template name
     *
     * @return self
     */
    public static function undefinedFilter(string $filter, ?string $template = null): self
    {
        $message = sprintf('Undefined filter "%s"', $filter);
        if ($template) {
            $message .= sprintf(' in template "%s"', $template);
        }
        return new self($message, $template);
    }

    /**
     * Alias for undefinedFilter.
     *
     * @param string $filter Filter name
     *
     * @return self
     */
    public static function forUndefinedFilter(string $filter): self
    {
        return self::undefinedFilter($filter);
    }

    /**
     * Create exception for undefined function.
     *
     * @param string $function Function name
     * @param string|null $template Template name
     *
     * @return self
     */
    public static function undefinedFunction(string $function, ?string $template = null): self
    {
        $message = sprintf('Undefined function "%s"', $function);
        if ($template) {
            $message .= sprintf(' in template "%s"', $template);
        }
        return new self($message, $template);
    }

    /**
     * Alias for undefinedFunction.
     *
     * @param string $function Function name
     *
     * @return self
     */
    public static function forUndefinedFunction(string $function): self
    {
        return self::undefinedFunction($function);
    }

    /**
     * Create exception for circular reference.
     *
     * @param string $template Template name
     * @param array<string> $chain Template chain
     *
     * @return self
     */
    public static function forCircularReference(string $template, array $chain): self
    {
        return new self(
            sprintf('Circular reference detected: %s', implode(' -> ', array_merge($chain, [$template]))),
            $template
        );
    }

    /**
     * Create exception for loader error.
     *
     * @param string $message Error message
     *
     * @return self
     */
    public static function loaderError(string $message): self
    {
        return new self(sprintf('Template loader error: %s', $message));
    }

    /**
     * Create exception for compilation error.
     *
     * @param string $message Error message
     * @param string $template Template name
     *
     * @return self
     */
    public static function compilationError(string $message, string $template): self
    {
        return new self(sprintf('Failed to compile template "%s": %s', $template, $message));
    }
}
