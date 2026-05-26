<?php

declare(strict_types=1);

namespace Lombervid\ShoppingCart\Tests\Unit\Component\Support;

use PHPUnit\Framework\TestCase;
use Lombervid\ShoppingCart\Component\Support\Arr;
use PHPUnit\Framework\Attributes\DataProvider;

final class ArrTest extends TestCase
{
    /** @var TCartOptions */
    protected static $defaultOptions = [
        'name'     => 'shopping_cart',
        'autosave' => true,
        'tax'      => 0,
        'shipping' => [
            'amount' => 0,
            'free'   => 0,
        ],
    ];

    /** @var TCartOptions */
    protected static $defaultArray = [
        'name'     => 'shopping_cart',
        'autosave' => true,
        'tax'      => 0,
        'total'    => 150.45,
        'fields'   => 'some_Fields',
        'shipping' => [
            'amount' => 50,
            'free'   => 500,
        ],
    ];

    /**
     * @return \Iterator<string, array{mixed[], TOptions}>
     */
    public static function intersectKeyRecursiveProvider(): \Iterator
    {
        yield 'empty values' => [[], []];
        yield 'root level' => [
            ['tax' => 12],
            ['tax' => 12.0],
        ];
        yield 'wrong value type' => [
            ['autosave' => 'true'],
            [],
        ];
        yield 'wrong value type 2' => [
            ['tax' => ['12']],
            [],
        ];
        yield 'sub level' => [
            ['shipping' => ['free' => 13.0]],
            ['shipping' => ['free' => 13.0]],
        ];
        yield 'sub level wrong value type' => [
            ['shipping' => ['free' => true, 'amount' => 50]],
            ['shipping' => ['amount' => 50.0]],
        ];
        yield 'numeric string value' => [
            ['tax' => '12', 'shipping' => ['amount' => '34.5']],
            ['tax' => 12.0, 'shipping' => ['amount' => 34.5]],
        ];
        yield 'invalid indexes' => [
            ['some' => 23, 'name' => 'my_cart', 'shipping' => ['amount' => '34.5', 'invalid' => 23]],
            ['name' => 'my_cart', 'shipping' => ['amount' => 34.5]],
        ];
    }

    /**
     * @param TOptions $options
     * @param TOptions $expected
     */
    #[DataProvider('intersectKeyRecursiveProvider')]
    public function testIntersectKeyRecursive(array $options, array $expected): void
    {
        $this->assertSame($expected, Arr::intersectKeyRecursive($options, self::$defaultOptions));
    }

    public function testGet(): void
    {
        $this->assertTrue(Arr::get(self::$defaultArray, 'autosave'));
    }

    public function testGetReturnsDefaultWhenZeroValue(): void
    {
        $this->assertNull(Arr::get(self::$defaultArray, 'tax'));
    }

    public function testGetCheckOnlyIfKeyExists(): void
    {
        $this->assertSame(0, Arr::get(self::$defaultArray, 'tax', empty: false));
    }

    public function testGetDefaultValue(): void
    {
        $this->assertSame('default_value', Arr::get(self::$defaultArray, 'missing', default: 'default_value'));
    }

    public function testGetCheckValueType(): void
    {
        $this->assertNull(Arr::get(self::$defaultArray, 'fields', type: 'array'));

        $this->assertSame(
            ['amount' => 50, 'free'   => 500],
            Arr::get(self::$defaultArray, 'shipping', type: 'array'),
        );
    }

    public function testGetCheckMultipleValueTypes(): void
    {
        $this->assertSame(
            0,
            Arr::get(self::$defaultArray, 'tax', type: ['double', 'integer'], empty: false),
        );
    }
}
