# Inky Templating Engine

Modern, fast, and secure templating engine for PHP 8.2+, part of the Flaphl framework.

## Features

- **Fast** - Compiles templates to optimized PHP code
- **Secure** - Automatic HTML escaping and XSS protection
- **Extensible** - Custom filters, functions, and extensions
- **Template Inheritance** - Block-based template extends system
- **Event-Driven** - PSR-14 event integration for lifecycle hooks
- **Strict Mode** - Optional runtime variable validation
- **Debuggable** - Comprehensive error rendering and profiling

## Installation

```bash
composer require flaphl/inky
```

## Quick Start

```php
use Flaphl\Fridge\Inky\Engine\{Engine, Loader};
use Flaphl\Fridge\Inky\Compilers\Compiler;

// Create engine
$engine = new Engine(
    new Loader([__DIR__ . '/templates'])
);

// Render template
echo $engine->render('welcome.inky', ['name' => 'World']);
```

**templates/welcome.inky:**
```inky
{{ # Welcome template }}
Hello, {{ name }}!
```

## Template Syntax

### Variables
```inky
{{ name }}                    # Escaped output
{{ user.email }}              # Object/array access
{{ items[0] }}                # Array index
{{ ! htmlContent }}           # Raw/unescaped output
```

### Filters
```inky
{{ name | upper }}            # Single filter
{{ price | number_format }}   # Format numbers
{{ text | upper | trim }}     # Chain filters
{{ content | json }}          # JSON encode
```

### Control Structures
```inky
{{ @if($loggedIn) }}
    Welcome back, {{ username }}!
{{ @endif }}

{{ @foreach($items as $item) }}
    <li>{{ item }}</li>
{{ @endforeach }}
```

### Template Inheritance
```inky
{{ # base.inky }}
<html>
    <header>{{ @block header }}Default Header{{ @endblock }}</header>
    <main>{{ @block content }}{{ @endblock }}</main>
</html>

{{ # page.inky }}
{{ @extends "base.inky" }}
{{ @block content }}Custom Content{{ @endblock }}
```

### Includes
```inky
{{ @include "header.inky" }}
```

### Comments
```inky
{{ # This is a comment }}
```

## Extensions

### Using Built-in Extensions

```php
use Flaphl\Fridge\Inky\Extensions\CoreExtension;

$engine->registerExtension(new CoreExtension());
```

**CoreExtension** provides 30+ filters and 15+ functions:
- **Filters**: `upper`, `lower`, `trim`, `json`, `date`, `replace`, `truncate`, `escape`
- **Functions**: `range`, `cycle`, `min`, `max`, `sum`, `count`, `random`

### Creating Custom Extensions

```php
use Flaphl\Fridge\Inky\Extensions\ExtensionInterface;

class MyExtension implements ExtensionInterface
{
    public function getName(): string 
    { 
        return 'my_extension'; 
    }
    
    public function getFilters(): array
    {
        return [
            'slug' => fn($text) => strtolower(str_replace(' ', '-', $text)),
            'reverse' => fn($text) => strrev($text),
        ];
    }
    
    public function getFunctions(): array
    {
        return [
            'greet' => fn($name) => "Hello, {$name}!",
        ];
    }
    
    public function register($engine): void
    {
        // Optional: Register custom logic with engine
    }
}

$engine->registerExtension(new MyExtension());
```

## Advanced Features

### Event Dispatching (PSR-14)

```php
use Flaphl\Fridge\Inky\Events\{PreRenderEvent, PostRenderEvent};

// Set event dispatcher
$engine->setEventDispatcher($dispatcher);

// Events are automatically dispatched during rendering:
// - PreRenderEvent: Before template rendering
// - PostRenderEvent: After successful rendering
// - RenderErrorEvent: On rendering errors
```

### Strict Mode

```php
// Enable strict variable checking
$engine->setStrictMode(true);

// Now undefined variables throw InkyException
echo $engine->render('template.inky', []); // Throws if {{ undefined }} exists
```

### Performance Profiling

```php
use Flaphl\Fridge\Inky\Utilities\DataCollector;

$collector = new DataCollector();
$engine->setDataCollector($collector);

// Render templates...

// Get statistics
$stats = $collector->getStatistics();
echo "Total renders: {$stats['total_renders']}\n";
echo "Total time: {$stats['total_time']}s\n";
echo "Cache hit rate: {$stats['cache_hit_rate']}%\n";

// Get slowest templates
$slowest = $collector->getSlowestTemplates(5);
foreach ($slowest as $template) {
    echo "{$template['name']}: {$template['average_time']}s\n";
}
```

### Error Rendering

```php
use Flaphl\Fridge\Inky\Utilities\ErrorRenderer;

$renderer = new ErrorRenderer();
$renderer->setFormat('html')->setDebug(true);

try {
    $engine->render('template.inky');
} catch (\Throwable $e) {
    // Render in HTML, JSON, or text format
    echo $renderer->renderHtml($e);
    // or
    echo $renderer->renderJson($e);
    // or  
    echo $renderer->renderText($e);
}
```

### Template Caching

```php
// Set cache directory for compiled templates
$engine->setCacheDirectory(__DIR__ . '/cache');

// Enable auto-reload (recompile on source change)
$engine->setAutoReload(true);
```

## Configuration

```php
// Global variables available to all templates
$engine->addGlobal('siteName', 'My Website');
$engine->addGlobal('version', '1.0.0');

// Debug mode
$engine->setDebug(true);

// Strict variables
$engine->setStrictMode(true);

// Auto-reload templates
$engine->setAutoReload(true);
```

## Testing

Run the comprehensive test suite:

```bash
composer test

# Or with coverage
composer test:coverage
```

**Test Statistics:**
- 534 tests passing
- 946 assertions
- 100% pass rate

## Requirements

- PHP 8.2 or higher
- PSR-6 (Cache) - optional
- PSR-14 (Event Dispatcher) - optional

## Contributing

Contributions are welcome! Please follow Flaphl's coding standards and ensure all tests pass.

## License

MIT License - see [LICENSE](LICENSE) file for details.

## Credits

Part of the [Flaphl Framework](https://github.com/flaphl/flaphl) by Jade Phyressi.

