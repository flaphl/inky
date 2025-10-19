# Changelog

All notable changes to Inky will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2025-10-19

### Achievement
- **100% Test Coverage**: All 534 tests passing with 946 assertions
- Complete implementation of Inky templating engine with full feature parity

### Added
- **Event Dispatcher Integration**: Full PSR-14 event dispatching in render pipeline
  * PreRenderEvent dispatched before template rendering
  * PostRenderEvent dispatched after successful rendering with output
  * RenderErrorEvent dispatched on exceptions with fallback support
  
- **Template Inheritance System**: Complete @extends/@block directive support
  * Child templates can extend parent templates with `@extends "parent.inky"`
  * Block override mechanism for selective content replacement
  * Full parent/child template merging with proper block extraction

- **Data Collection & Profiling**: Complete template performance tracking
  * Template compilation tracking with timing
  * Cache hit rate monitoring
  * Template usage statistics and profiling data
  
- **Advanced Error Rendering**: Multi-format error display
  * HTML error pages with syntax highlighting
  * Context line extraction (configurable before/after lines)
  * Nested exception rendering with full stack traces
  * Text and JSON output formats
  * Debug mode for detailed diagnostics

- **Extension System Enhancements**:
  * JSON encoding filter with configurable options
  * Min/max functions for array operations
  * Extension registration and initialization hooks
  * Proper extension interface compliance

### Fixed
- **DataCollector** (6 fixes):
  * Template compilation tracking now properly records timing data
  * Cache hit rate calculation accuracy
  * Template statistics aggregation
  * Profiling data structure consistency
  * Memory tracking precision
  * Event data collection completeness

- **ErrorRenderer** (10 fixes):
  * Context line extraction with proper bounds checking
  * Syntax highlighting for template code
  * Nested exception rendering chain
  * Stack trace formatting and filtering
  * HTML entity escaping in error output
  * Line number alignment and display
  * File path normalization
  * Debug information visibility
  * JSON error format structure
  * Text format readability

- **Extensions** (4 fixes):
  * JSON filter implementation with proper encoding
  * Min/max function array handling
  * Extension registration lifecycle
  * Extension interface method compliance

- **Exception Handling** (2 fixes):
  * toString method implementation for proper exception messages
  * Template not found error messages with file path context

- **Integration Tests** (4 fixes):
  * Loop directive whitespace handling
  * Strict mode variable checking
  * Event dispatcher integration in render pipeline
  * Template inheritance parsing and rendering

- **Template Processing**:
  * Whitespace normalization in loop directives
  * Strict mode undefined variable detection
  * Block extraction regex patterns
  * Parent template loading and merging

### Changed
- Enhanced render pipeline with event integration points
- Improved error messages with contextual information
- Optimized template inheritance performance
- Strengthened type safety across all components

### Technical Details
- PHP 8.2+ requirement with modern features
- PSR-14 Event Dispatcher compliance
- PHPUnit 10.5.58 test framework
- 534 comprehensive tests, 946 assertions
- Zero failures, zero errors, 100% pass rate

---

## [0.1.0] - Initial Development

### Added
- Initial contract-driven architecture with 20 core interfaces
- Engine layer: TemplateInterface, EngineInterface, LoaderInterface, ExtensionInterface, EscaperInterface
- Compiler layer: CompilerInterface, CacheInterface, LexerInterface, ParserInterface
- Token system: TokenInterface, TokenType enum
- AST system: NodeInterface, NodeVisitorInterface
- Component system: Template, Prop, Slot attributes
- Event system: TemplateEventInterface, PreRenderEventInterface, PostRenderEventInterface, RenderErrorEventInterface
- Debugging: DataCollectorInterface, ErrorRendererInterface
- Exception hierarchy: InkyException with static factory methods
- PHP 8.2+ features: Attributes, enums, readonly properties, union types

### Implemented
- Complete engine implementations (5 classes):
  * Template - Compiled template with block rendering
  * Engine - Main rendering engine with extension support
  * Loader - Filesystem-based template loading
  * Escaper - Multi-strategy output escaping (html, js, css, url, attr)
  * Extensions support with initialization hooks

- Complete compiler implementations (10 classes):
  * Compiler - Template to PHP compilation with directive system
  * Cache - File-based compiled template caching with memory layer
  * Lexer - Tokenization with configurable delimiters
  * Token - Token representation with position tracking
  * Parser - Token stream to AST parsing
  * Node - Abstract base node with visitor pattern
  * RootNode, TextNode, VarNode, RawNode, CommentNode, DirectiveNode, BlockNode

- Complete event implementations (4 classes):
  * TemplateEvent - Base event with variable management
  * PreRenderEvent - Pre-render hook with rendering prevention
  * PostRenderEvent - Post-render hook with content modification
  * RenderErrorEvent - Error hook with fallback content and suppression

- Utility implementations:
  * DataCollector - Template profiling and statistics
  * ErrorRenderer - HTML/text/JSON error rendering with debug mode

- Extension system:
  * CoreExtension - 30+ built-in filters, functions, and tests
  * ExampleExtension - Custom extension example showing best practices

- Built-in directives:
  * @if/@else/@endif - Conditional rendering
  * @foreach/@endforeach - Array iteration
  * @for/@endfor - Loop iteration
  * @while/@endwhile - While loops

- Built-in filters (CoreExtension):
  * String: upper, lower, capitalize, title, trim, length, reverse, replace
  * Array: first, last, join, sort, keys, values
  * Number: abs, round, number_format
  * Date: date
  * Encoding: json, url_encode, base64
  * HTML: nl2br, strip_tags
  * Utility: default

- Built-in functions (CoreExtension):
  * Utility: range, cycle, random
  * Type checking: is_array, is_string, is_numeric, is_null, is_empty
  * Array: min, max, sum, count
  * String: str_contains, str_starts_with, str_ends_with
  * Debug: dump

- Built-in tests (CoreExtension):
  * empty, null, array, string, numeric, even, odd, divisible_by
