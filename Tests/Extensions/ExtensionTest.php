<?php

/**
 * This file is part of the Flaphl package.
 *
 * (c) Jade Phyressi <jade@flaphl.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flaphl\Fridge\Inky\Tests\Extensions;

use PHPUnit\Framework\TestCase;
use Flaphl\Fridge\Inky\Extensions\{CoreExtension, ExampleExtension};
use Flaphl\Fridge\Inky\Engine\{Engine, Loader};

class ExtensionTest extends TestCase
{
    private CoreExtension $coreExtension;
    private ExampleExtension $exampleExtension;
    private Engine $engine;

    protected function setUp(): void
    {
        $this->coreExtension = new CoreExtension();
        $this->exampleExtension = new ExampleExtension();
        
        $tempDir = sys_get_temp_dir() . '/inky_ext_test_' . uniqid();
        mkdir($tempDir);
        $loader = new Loader([$tempDir]);
        $this->engine = new Engine($loader);
    }

    public function testCoreExtensionConstruction(): void
    {
        $this->assertInstanceOf(CoreExtension::class, $this->coreExtension);
    }

    public function testCoreExtensionGetFilters(): void
    {
        $filters = $this->coreExtension->getFilters();
        
        $this->assertIsArray($filters);
        $this->assertNotEmpty($filters);
        $this->assertArrayHasKey('upper', $filters);
        $this->assertArrayHasKey('lower', $filters);
        $this->assertArrayHasKey('capitalize', $filters);
    }

    public function testCoreExtensionGetFunctions(): void
    {
        $functions = $this->coreExtension->getFunctions();
        
        $this->assertIsArray($functions);
        $this->assertNotEmpty($functions);
        $this->assertArrayHasKey('range', $functions);
        $this->assertArrayHasKey('max', $functions);
        $this->assertArrayHasKey('min', $functions);
    }

    public function testCoreExtensionGetDirectives(): void
    {
        $directives = $this->coreExtension->getDirectives();
        
        $this->assertIsArray($directives);
    }

    public function testUpperFilter(): void
    {
        $filters = $this->coreExtension->getFilters();
        $upper = $filters['upper'];
        
        $this->assertEquals('HELLO', $upper('hello'));
    }

    public function testLowerFilter(): void
    {
        $filters = $this->coreExtension->getFilters();
        $lower = $filters['lower'];
        
        $this->assertEquals('hello', $lower('HELLO'));
    }

    public function testCapitalizeFilter(): void
    {
        $filters = $this->coreExtension->getFilters();
        $capitalize = $filters['capitalize'];
        
        $this->assertEquals('Hello world', $capitalize('hello world'));
    }

    public function testTrimFilter(): void
    {
        $filters = $this->coreExtension->getFilters();
        $trim = $filters['trim'];
        
        $this->assertEquals('hello', $trim('  hello  '));
    }

    public function testLengthFilter(): void
    {
        $filters = $this->coreExtension->getFilters();
        $length = $filters['length'];
        
        $this->assertEquals(5, $length('hello'));
        $this->assertEquals(3, $length([1, 2, 3]));
    }

    public function testDefaultFilter(): void
    {
        $filters = $this->coreExtension->getFilters();
        $default = $filters['default'];
        
        $this->assertEquals('fallback', $default(null, 'fallback'));
        $this->assertEquals('value', $default('value', 'fallback'));
    }

    public function testDateFilter(): void
    {
        $filters = $this->coreExtension->getFilters();
        $date = $filters['date'];
        
        $timestamp = strtotime('2024-01-01');
        $this->assertEquals('2024-01-01', $date($timestamp, 'Y-m-d'));
    }

    public function testJsonFilter(): void
    {
        $filters = $this->coreExtension->getFilters();
        $json = $filters['json'];
        
        $this->assertEquals('{"name":"test"}', $json(['name' => 'test']));
    }

    public function testReplaceFilter(): void
    {
        $filters = $this->coreExtension->getFilters();
        $replace = $filters['replace'];
        
        $this->assertEquals('hello world', $replace('hello NAME', ['NAME' => 'world']));
    }

    public function testSliceFilter(): void
    {
        $filters = $this->coreExtension->getFilters();
        $slice = $filters['slice'];
        
        $this->assertEquals('ell', $slice('hello', 1, 3));
        $this->assertEquals([2, 3], $slice([1, 2, 3, 4], 1, 2));
    }

    public function testRangeFunction(): void
    {
        $functions = $this->coreExtension->getFunctions();
        $range = $functions['range'];
        
        $this->assertEquals([1, 2, 3, 4, 5], $range(1, 5));
    }

    public function testMaxFunction(): void
    {
        $functions = $this->coreExtension->getFunctions();
        $max = $functions['max'];
        
        $this->assertEquals(5, $max([1, 2, 3, 4, 5]));
    }

    public function testMinFunction(): void
    {
        $functions = $this->coreExtension->getFunctions();
        $min = $functions['min'];
        
        $this->assertEquals(1, $min([1, 2, 3, 4, 5]));
    }

    public function testExampleExtensionConstruction(): void
    {
        $this->assertInstanceOf(ExampleExtension::class, $this->exampleExtension);
    }

    public function testExampleExtensionGetFilters(): void
    {
        $filters = $this->exampleExtension->getFilters();
        
        $this->assertIsArray($filters);
        $this->assertArrayHasKey('reverse', $filters);
    }

    public function testReverseFilter(): void
    {
        $filters = $this->exampleExtension->getFilters();
        $reverse = $filters['reverse'];
        
        $this->assertEquals('olleh', $reverse('hello'));
    }

    public function testExampleExtensionGetFunctions(): void
    {
        $functions = $this->exampleExtension->getFunctions();
        
        $this->assertIsArray($functions);
        $this->assertArrayHasKey('greet', $functions);
    }

    public function testGreetFunction(): void
    {
        $functions = $this->exampleExtension->getFunctions();
        $greet = $functions['greet'];
        
        $this->assertEquals('Hello, World!', $greet('World'));
    }

    public function testRegisterExtensionWithEngine(): void
    {
        $this->engine->registerExtension($this->coreExtension);
        
        $this->assertTrue($this->engine->hasExtension(CoreExtension::class));
    }

    public function testExtensionFiltersAvailableInEngine(): void
    {
        $this->engine->registerExtension($this->coreExtension);
        
        $this->assertTrue($this->engine->hasFilter('upper'));
        $this->assertTrue($this->engine->hasFilter('lower'));
    }

    public function testExtensionFunctionsAvailableInEngine(): void
    {
        $this->engine->registerExtension($this->coreExtension);
        
        $this->assertTrue($this->engine->hasFunction('range'));
        $this->assertTrue($this->engine->hasFunction('max'));
    }
}
