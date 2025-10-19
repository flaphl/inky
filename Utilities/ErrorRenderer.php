<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\Utilities;

use Throwable;
use Flaphl\Fridge\Inky\Exception\InkyException;

/**
 * Error renderer implementation.
 */
class ErrorRenderer
{
    private string $format = 'html';
    private bool $debug = false;
    private int $maxContextLines = 5;

    /**
     * Set maximum context lines to show.
     *
     * @param int $lines Maximum context lines
     *
     * @return self
     */
    public function setMaxContextLines(int $lines): self
    {
        $this->maxContextLines = $lines;
        return $this;
    }

    /**
     * Get context lines around error.
     *
     * @param string $source Source code
     * @param int $errorLine Error line number (1-indexed)
     * @param int|null $contextLines Number of context lines (defaults to maxContextLines)
     *
     * @return array<int, string> Context lines indexed by line number
     */
    public function getContextLines(string $source, int $errorLine, ?int $contextLines = null): array
    {
        $contextLines = $contextLines ?? $this->maxContextLines;
        $lines = explode("\n", $source);
        $start = max(0, $errorLine - $contextLines - 1);
        $end = min(count($lines) - 1, $errorLine + $contextLines - 1);
        
        $context = [];
        for ($i = $start; $i <= $end; $i++) {
            $context[$i + 1] = $lines[$i];
        }
        
        return $context;
    }

    /**
     * {@inheritdoc}
     */
    public function render(Throwable $exception, ?string $template = null, ?int $line = null): string
    {
        return match ($this->format) {
            'html' => $this->renderHtml($exception, $template, $line),
            'text' => $this->renderText($exception, $template, $line),
            'json' => $this->renderJson($exception, $template, $line),
            default => $this->renderText($exception, $template, $line),
        };
    }

    /**
     * {@inheritdoc}
     */
    public function canRender(Throwable $exception): bool
    {
        return $exception instanceof InkyException || $this->debug;
    }

    /**
     * {@inheritdoc}
     */
    public function setFormat(string $format): self
    {
        $this->format = $format;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;
        return $this;
    }

    /**
     * Render error as HTML.
     *
     * @param Throwable $exception Exception
     * @param string|null $template Template name
     * @param int|null $line Line number
     *
     * @return string HTML output
     */
    public function renderHtml(Throwable $exception, ?string $template = null, ?int $line = null): string
    {
        // Detect if template parameter is actually source context (contains newlines)
        $sourceContext = null;
        if ($template !== null && str_contains($template, "\n")) {
            $sourceContext = $template;
            $template = null;
        }
        
        // Extract template/line from InkyException if available
        if ($exception instanceof InkyException) {
            $template = $template ?? $exception->getTemplateName();
            $line = $line ?? $exception->getTemplateLine();
            $sourceContext = $sourceContext ?? $exception->getSourceContext();
        }
        
        $message = htmlspecialchars($exception->getMessage());
        $class = htmlspecialchars(get_class($exception));
        $file = htmlspecialchars($exception->getFile());
        $exceptionLine = $exception->getLine();

        $html = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Template Error</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }
        .error-container { max-width: 1000px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .error-header { background: #dc3545; color: white; padding: 20px; border-radius: 8px 8px 0 0; }
        .error-header h1 { margin: 0; font-size: 24px; font-weight: 600; }
        .error-body { padding: 20px; }
        .error-message { background: #fff3cd; border-left: 4px solid #ffc107; padding: 15px; margin-bottom: 20px; }
        .error-location { background: #f8f9fa; padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .error-location strong { display: block; margin-bottom: 5px; }
        .stack-trace { background: #212529; color: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
        .stack-trace pre { margin: 0; font-family: 'Monaco', 'Menlo', 'Courier New', monospace; font-size: 12px; }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-header">
            <h1>Template Error</h1>
        </div>
        <div class="error-body">
            <div class="error-message">
                <strong>{$class}</strong>
                <p>{$message}</p>
            </div>
HTML;

        if ($template !== null) {
            $templateHtml = htmlspecialchars($template);
            $lineHtml = $line !== null ? " at line <span class=\"error-line\">{$line}</span>" : '';
            $html .= <<<HTML
            <div class="error-location">
                <strong>Template:</strong>
                {$templateHtml}{$lineHtml}
            </div>
HTML;
        }
        
        // Add source context if available
        if ($sourceContext !== null && $line !== null) {
            $lines = explode("\n", $sourceContext);
            $contextHtml = '';
            foreach ($lines as $lineNum => $lineContent) {
                $num = $lineNum + 1;
                $content = htmlspecialchars($lineContent);
                $classes = $num === $line ? ' class="error-line" style="background: #fff3cd;"' : '';
                $contextHtml .= "<div{$classes}><span style='color: #999;'>{$num}:</span> {$content}</div>";
            }
            $html .= <<<HTML
            <div class="error-location">
                <strong>Source:</strong>
                <pre style="background: #f8f9fa; padding: 10px; border-radius: 4px; overflow-x: auto;">{$contextHtml}</pre>
            </div>
HTML;
        }

        // Always show stack trace
        $trace = htmlspecialchars($exception->getTraceAsString());
        $html .= <<<HTML
            <div class="stack-trace">
                <strong>Stack Trace:</strong>
                <pre>{$trace}</pre>
            </div>
HTML;

        // Show previous exceptions (nested)
        $previous = $exception->getPrevious();
        if ($previous !== null) {
            $prevMessage = htmlspecialchars($previous->getMessage());
            $prevClass = htmlspecialchars(get_class($previous));
            $html .= <<<HTML
            <div class="error-message" style="margin-top: 20px; background: #f8d7da; border-left-color: #dc3545;">
                <strong>Previous Exception: {$prevClass}</strong>
                <p>{$prevMessage}</p>
            </div>
HTML;
        }

        if ($this->debug) {
            $html .= <<<HTML
            <div class="error-location">
                <strong>PHP File:</strong>
                {$file} on line {$exceptionLine}
            </div>
HTML;
        }

        $html .= <<<HTML
        </div>
    </div>
</body>
</html>
HTML;

        return $html;
    }

    /**
     * Render error as text.
     *
     * @param Throwable $exception Exception
     * @param string|null $template Template name
     * @param int|null $line Line number
     *
     * @return string Text output
     */
    public function renderText(Throwable $exception, ?string $template = null, ?int $line = null): string
    {
        // Extract template/line from InkyException if available
        if ($exception instanceof InkyException) {
            $template = $template ?? $exception->getTemplateName();
            $line = $line ?? $exception->getTemplateLine();
        }
        
        $output = "Template Error\n";
        $output .= str_repeat('=', 50) . "\n\n";
        $output .= get_class($exception) . ": " . $exception->getMessage() . "\n\n";

        if ($template !== null) {
            $output .= "Template: {$template}\n";
            if ($line !== null) {
                $output .= "Line: {$line}\n";
            }
            $output .= "\n";
        }

        // Always show stack trace
        $output .= "Stack Trace:\n";
        $output .= $exception->getTraceAsString() . "\n\n";

        if ($this->debug) {
            $output .= "PHP File: {$exception->getFile()} on line {$exception->getLine()}\n\n";
        }

        return $output;
    }

    /**
     * Render error as JSON.
     *
     * @param Throwable $exception Exception
     * @param string|null $template Template name
     * @param int|null $line Line number
     *
     * @return string JSON output
     */
    public function renderJson(Throwable $exception, ?string $template = null, ?int $line = null): string
    {
        // Detect if template parameter is actually source context (contains newlines)
        $sourceContext = null;
        if ($template !== null && str_contains($template, "\n")) {
            $sourceContext = $template;
            $template = null;
        }
        
        // Extract template/line from InkyException if available
        if ($exception instanceof InkyException) {
            $template = $template ?? $exception->getTemplateName();
            $line = $line ?? $exception->getTemplateLine();
            $sourceContext = $sourceContext ?? $exception->getSourceContext();
        }
        
        $data = [
            'error' => $exception->getMessage(),
            'type' => get_class($exception),
            'message' => $exception->getMessage(),
        ];

        if ($template !== null) {
            $data['file'] = $template;
        }

        if ($line !== null) {
            $data['line'] = $line;
        }

        // Always include trace
        $data['trace'] = array_map(function ($frame) {
            return [
                'file' => $frame['file'] ?? 'unknown',
                'line' => $frame['line'] ?? 0,
                'function' => $frame['function'] ?? '',
                'class' => $frame['class'] ?? '',
            ];
        }, $exception->getTrace());
        
        // Add source context if available
        if ($sourceContext !== null) {
            $sourceLines = explode("\n", $sourceContext);
            $data['context'] = $sourceLines;
        }

        if ($this->debug) {
            $data['exception_file'] = $exception->getFile();
            $data['exception_line'] = $exception->getLine();
        }

        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}
