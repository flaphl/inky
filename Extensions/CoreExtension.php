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
 * Core extension providing standard filters and functions.
 */
class CoreExtension implements ExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'core';
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {
        return [
            // String filters
            'upper' => fn($str) => strtoupper($str),
            'lower' => fn($str) => strtolower($str),
            'capitalize' => fn($str) => ucfirst(strtolower($str)),
            'title' => fn($str) => ucwords(strtolower($str)),
            'trim' => fn($str) => trim($str),
            'length' => fn($value) => is_string($value) ? strlen($value) : count($value),
            'reverse' => fn($str) => strrev($str),
            'replace' => fn($str, $replacements) => is_array($replacements) 
                ? str_replace(array_keys($replacements), array_values($replacements), $str)
                : $str,
            'slice' => fn($value, $start, $length = null) => is_array($value) 
                ? array_slice($value, $start, $length)
                : substr($value, $start, $length),
            
            // Array filters
            'first' => fn($array) => is_array($array) ? reset($array) : null,
            'last' => fn($array) => is_array($array) ? end($array) : null,
            'join' => fn($array, $glue = '') => implode($glue, $array),
            'sort' => function($array) {
                sort($array);
                return $array;
            },
            'keys' => fn($array) => array_keys($array),
            'values' => fn($array) => array_values($array),
            
            // Number filters
            'abs' => fn($num) => abs($num),
            'round' => fn($num, $precision = 0) => round($num, $precision),
            'number_format' => fn($num, $decimals = 0) => number_format($num, $decimals),
            
            // Date filters
            'date' => fn($date, $format = 'Y-m-d H:i:s') => date($format, is_numeric($date) ? $date : strtotime($date)),
            
            // Encoding filters
            'json' => fn($value) => json_encode($value),
            'url_encode' => fn($str) => urlencode($str),
            'base64' => fn($str) => base64_encode($str),
            
            // HTML filters
            'nl2br' => fn($str) => nl2br($str),
            'strip_tags' => fn($str) => strip_tags($str),
            
            // Default filter
            'default' => fn($value, $default = '') => $value ?: $default,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            // Utility functions
            'range' => fn($start, $end, $step = 1) => range($start, $end, $step),
            'cycle' => function($values, $index) {
                return $values[$index % count($values)];
            },
            'random' => fn($min = 0, $max = 100) => rand($min, $max),
            
            // Type checking
            'is_array' => fn($value) => is_array($value),
            'is_string' => fn($value) => is_string($value),
            'is_numeric' => fn($value) => is_numeric($value),
            'is_null' => fn($value) => is_null($value),
            'is_empty' => fn($value) => empty($value),
            
            // Array functions
            'min' => fn($array) => is_array($array) ? min($array) : $array,
            'max' => fn($array) => is_array($array) ? max($array) : $array,
            'sum' => fn($array) => array_sum($array),
            'count' => fn($value) => is_array($value) || $value instanceof \Countable ? count($value) : 0,
            
            // String functions
            'str_contains' => fn($haystack, $needle) => str_contains($haystack, $needle),
            'str_starts_with' => fn($haystack, $needle) => str_starts_with($haystack, $needle),
            'str_ends_with' => fn($haystack, $needle) => str_ends_with($haystack, $needle),
            
            // Debug function
            'dump' => function(...$values) {
                ob_start();
                var_dump(...$values);
                return ob_get_clean();
            },
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getTests(): array
    {
        return [
            'empty' => fn($value) => empty($value),
            'null' => fn($value) => $value === null,
            'array' => fn($value) => is_array($value),
            'string' => fn($value) => is_string($value),
            'numeric' => fn($value) => is_numeric($value),
            'even' => fn($value) => is_numeric($value) && $value % 2 === 0,
            'odd' => fn($value) => is_numeric($value) && $value % 2 !== 0,
            'divisible_by' => fn($value, $divisor) => is_numeric($value) && $value % $divisor === 0,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobals(): array
    {
        return [
            '_version' => '1.0.0',
            '_charset' => 'UTF-8',
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
        foreach ($this->getFilters() as $name => $filter) {
            // Engine would register filters here
        }
        
        foreach ($this->getFunctions() as $name => $function) {
            // Engine would register functions here
        }
        
        foreach ($this->getGlobals() as $name => $value) {
            $engine->addGlobal($name, $value);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function initialize(EngineInterface $engine): void
    {
        // No initialization needed for core extension
    }
}
