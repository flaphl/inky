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
 * Template token types.
 */
enum TokenType: string
{
    /**
     * Plain text content.
     */
    case TEXT = 'text';

    /**
     * Variable output (e.g., {{ $name }}).
     */
    case VAR = 'var';

    /**
     * Block start (e.g., @if, @foreach).
     */
    case BLOCK_START = 'block_start';

    /**
     * Block end (e.g., @endif, @endforeach).
     */
    case BLOCK_END = 'block_end';

    /**
     * Comment (e.g., {{-- comment --}}).
     */
    case COMMENT = 'comment';

    /**
     * Raw/unescaped output (e.g., {!! $html !!}).
     */
    case RAW = 'raw';

    /**
     * Directive (e.g., @extends, @include).
     */
    case DIRECTIVE = 'directive';

    /**
     * End of file.
     */
    case EOF = 'eof';

    /**
     * Get display label for token type.
     *
     * @return string Human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::TEXT => 'Text',
            self::VAR => 'Variable',
            self::BLOCK_START => 'Block Start',
            self::BLOCK_END => 'Block End',
            self::COMMENT => 'Comment',
            self::RAW => 'Raw Output',
            self::DIRECTIVE => 'Directive',
            self::EOF => 'End of File',
        };
    }

    /**
     * Check if token type is executable.
     *
     * @return bool True if token produces output or logic
     */
    public function isExecutable(): bool
    {
        return match($this) {
            self::VAR, self::BLOCK_START, self::BLOCK_END, self::RAW, self::DIRECTIVE => true,
            self::TEXT, self::COMMENT, self::EOF => false,
        };
    }
}
