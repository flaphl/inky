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

/**
 * Default escaper implementation with multiple strategies.
 */
class Escaper implements EscaperInterface
{
    private string $defaultStrategy = 'html';
    private array $strategies = [];

    public function __construct()
    {
        // Register default strategies
        $this->addStrategy('html', fn($value) => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
        $this->addStrategy('js', fn($value) => json_encode($value, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT));
        $this->addStrategy('css', fn($value) => preg_replace('/[^a-zA-Z0-9\-_]/', '\\\\$0', $value));
        $this->addStrategy('url', fn($value) => rawurlencode($value));
        $this->addStrategy('attr', fn($value) => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));
    }

    /**
     * {@inheritdoc}
     */
    public function escape(mixed $value, string $strategy = 'html'): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value);
        }

        $value = (string) $value;

        if (!$this->hasStrategy($strategy)) {
            throw new \InvalidArgumentException(sprintf('Unknown escaping strategy "%s"', $strategy));
        }

        return ($this->strategies[$strategy])($value);
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultStrategy(string $strategy): self
    {
        $this->defaultStrategy = $strategy;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultStrategy(): string
    {
        return $this->defaultStrategy;
    }

    /**
     * {@inheritdoc}
     */
    public function addStrategy(string $name, callable $escaper): self
    {
        $this->strategies[$name] = $escaper;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function hasStrategy(string $name): bool
    {
        return isset($this->strategies[$name]);
    }
}
