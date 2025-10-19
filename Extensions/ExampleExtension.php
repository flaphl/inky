<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\Extensions;

use Flaphl\Fridge\Inky\Engine\EngineInterface;
use Flaphl\Fridge\Inky\Engine\ExtensionInterface;

/**
 * Example custom extension showing how to extend Inky.
 */
class ExampleExtension implements ExtensionInterface
{
    private string $appName;

    /**
     * Create a new example extension.
     *
     * @param string $appName Application name
     */
    public function __construct(string $appName = 'MyApp')
    {
        $this->appName = $appName;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'example';
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            // String reverse
            'reverse' => fn($text) => strrev($text),
            
            // Custom slug filter
            'slug' => function($text) {
                $text = preg_replace('/[^a-z0-9]+/i', '-', strtolower($text));
                return trim($text, '-');
            },
            
            // Money formatting
            'money' => fn($amount, $currency = '$') => $currency . number_format($amount, 2),
            
            // Truncate with ellipsis
            'truncate' => function($text, $length = 100, $ellipsis = '...') {
                if (strlen($text) <= $length) {
                    return $text;
                }
                return substr($text, 0, $length - strlen($ellipsis)) . $ellipsis;
            },
            
            // Highlight search terms
            'highlight' => function($text, $search) {
                return str_replace(
                    $search,
                    "<mark>{$search}</mark>",
                    $text
                );
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            // Greeting function
            'greet' => fn($name) => "Hello, {$name}!",
            
            // Generate a route URL
            'route' => fn($name, $params = []) => "/{$name}?" . http_build_query($params),
            
            // Asset URL helper
            'asset' => fn($path) => "/assets/{$path}",
            
            // CSRF token (simplified example)
            'csrf_token' => fn() => bin2hex(random_bytes(32)),
            
            // Old input helper (for forms)
            'old' => fn($field, $default = '') => $_POST[$field] ?? $default,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTests(): array
    {
        return [
            // Test if user is authenticated (example)
            'authenticated' => fn($user) => $user !== null && isset($user['id']),
            
            // Test if value is a valid email
            'email' => fn($value) => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            
            // Test if value is a valid URL
            'url' => fn($value) => filter_var($value, FILTER_VALIDATE_URL) !== false,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobals(): array
    {
        return [
            'app_name' => $this->appName,
            'app_env' => getenv('APP_ENV') ?: 'production',
            'app_debug' => getenv('APP_DEBUG') === 'true',
        ];
    }

    /**
     * Get directives provided by this extension.
     *
     * @return array<string, callable> Directives
     */
    public function getDirectives(): array
    {
        return [];
    }

    /**
     * Register the extension with the engine.
     *
     * @param EngineInterface $engine Engine instance
     *
     * @return void
     */
    public function register(EngineInterface $engine): void
    {
        // Register filters, functions, tests, and globals with the engine
        foreach ($this->getGlobals() as $name => $value) {
            $engine->addGlobal($name, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(EngineInterface $engine): void
    {
        // Could register additional configuration here
        // For example, adding custom template paths
        // $engine->addPath(__DIR__ . '/templates');
    }
}
