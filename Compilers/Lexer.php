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
 * Lexer implementation for tokenizing templates.
 */
class Lexer implements LexerInterface
{
    private string $openDelimiter = '{{';
    private string $closeDelimiter = '}}';

    /**
     * {@inheritdoc}
     */
    public function tokenize(string $source, string $name = 'unknown'): array
    {
        $tokens = [];
        $line = 1;
        $column = 1;
        $position = 0;
        $length = strlen($source);

        while ($position < $length) {
            // Find next delimiter
            $openPos = strpos($source, $this->openDelimiter, $position);
            
            if ($openPos === false) {
                // No more delimiters, rest is text
                $value = substr($source, $position);
                if ($value !== '') {
                    $tokens[] = new Token(TokenType::TEXT, $value, $line, $column);
                }
                break;
            }

            // Add text before delimiter
            if ($openPos > $position) {
                $value = substr($source, $position, $openPos - $position);
                $tokens[] = new Token(TokenType::TEXT, $value, $line, $column);
                
                // Update position tracking
                $lines = substr_count($value, "\n");
                $line += $lines;
                if ($lines > 0) {
                    $column = strlen($value) - strrpos($value, "\n");
                } else {
                    $column += strlen($value);
                }
            }

            // Find closing delimiter
            $closePos = strpos($source, $this->closeDelimiter, $openPos + strlen($this->openDelimiter));
            
            if ($closePos === false) {
                // Unclosed delimiter - treat as text
                $tokens[] = new Token(TokenType::TEXT, substr($source, $openPos), $line, $column);
                break;
            }

            // Extract content between delimiters
            $contentStart = $openPos + strlen($this->openDelimiter);
            $content = substr($source, $contentStart, $closePos - $contentStart);
            $content = trim($content);

            // Determine token type based on content
            $tokenType = $this->determineTokenType($content);
            $tokens[] = new Token($tokenType, $content, $line, $column);

            // Move position past closing delimiter
            $position = $closePos + strlen($this->closeDelimiter);
            $column += ($position - $openPos);
        }

        // Add EOF token
        $tokens[] = new Token(TokenType::EOF, '', $line, $column);

        return $tokens;
    }

    /**
     * {@inheritdoc}
     */
    public function setDelimiters(string $open, string $close): self
    {
        $this->openDelimiter = $open;
        $this->closeDelimiter = $close;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOpenDelimiter(): string
    {
        return $this->openDelimiter;
    }

    /**
     * {@inheritdoc}
     */
    public function getCloseDelimiter(): string
    {
        return $this->closeDelimiter;
    }

    /**
     * Determine token type from content.
     *
     * @param string $content Token content
     *
     * @return TokenType Token type
     */
    private function determineTokenType(string $content): TokenType
    {
        if (str_starts_with($content, '#')) {
            return TokenType::COMMENT;
        }

        if (str_starts_with($content, '@')) {
            return TokenType::DIRECTIVE;
        }

        if (str_starts_with($content, '{%') || str_starts_with($content, '%}')) {
            return str_starts_with($content, '{%') ? TokenType::BLOCK_START : TokenType::BLOCK_END;
        }

        if (str_starts_with($content, '!')) {
            return TokenType::RAW;
        }

        return TokenType::VAR;
    }
}
