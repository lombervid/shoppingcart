<?php

declare(strict_types=1);

namespace Lombervid\ShoppingCart\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Lombervid\ShoppingCart\Item;
use PHPUnit\Framework\Attributes\DependsUsingDeepClone;

final class ItemTest extends TestCase
{
    public function testSingleItem(): Item
    {
        $item = new Item('1', 'Item', 15);

        $this->assertEqualsWithDelta(15.0, $item->price(), PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(15.0, $item->total(), PHP_FLOAT_EPSILON);

        return $item;
    }

    public function testExceptionsAreThrownForNegativePrice(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Item('1', 'Item', -15);
    }

    public function testExceptionsAreThrownForZeroOrLowerQuantity(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Item('1', 'Item', 15, 0);
    }

    #[DependsUsingDeepClone('testSingleItem')]
    public function testAddQuantity(Item $item): void
    {
        $this->assertEqualsWithDelta(15.0, $item->total(), PHP_FLOAT_EPSILON);

        $item->add(2);
        $this->assertEqualsWithDelta(45.0, $item->total(), PHP_FLOAT_EPSILON);
    }

    #[DependsUsingDeepClone('testSingleItem')]
    public function testUpdateQuantity(Item $item): void
    {
        $this->assertEqualsWithDelta(15.0, $item->total(), PHP_FLOAT_EPSILON);

        $item->update(2);
        $this->assertEqualsWithDelta(30.0, $item->total(), PHP_FLOAT_EPSILON);
    }

    public function testAddingItemWithQuantity(): void
    {
        $item = new Item('1', 'Item', 15, 3);

        $this->assertEqualsWithDelta(15.0, $item->price(), PHP_FLOAT_EPSILON);
        $this->assertEqualsWithDelta(45.0, $item->total(), PHP_FLOAT_EPSILON);
    }

    public function testNoDiscount(): void
    {
        $item = new Item('1', 'Item', 15);
        $this->assertFalse($item->hasDiscount());
        $this->assertEqualsWithDelta(15.0, $item->total(), PHP_FLOAT_EPSILON);
    }

    public function testDiscountApplies(): void
    {
        $item = new Item('1', 'Item', 15, discount: 10);
        $this->assertTrue($item->hasDiscount());
        $this->assertEqualsWithDelta(5.0, $item->total(), PHP_FLOAT_EPSILON);
    }

    public function testDiscountAppliesWithQuantity(): void
    {
        $item = new Item('1', 'Item', 15, 3, discount: 10);
        $this->assertTrue($item->hasDiscount());
        $this->assertEqualsWithDelta(15.0, $item->total(), PHP_FLOAT_EPSILON);
    }

    public function testExceptionsAreThrownWithNegativeDiscount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Item('1', 'Item', 115, discount: -10);
    }

    public function testToArrayMethod(): void
    {
        $item = new Item('1', 'Item', 15);

        $this->assertSame([
            'id' => '1',
            'name' => 'Item',
            'price' => 15.0,
            'qty' => 1,
            'discount' => 0.0,
            'fields' => [],
        ], $item->toArray());
    }

    public function testToArrayMethodWithExtraArguments(): Item
    {
        $item = new Item('100', 'New Item', 68.5, 2, [
            'description' => 'New item from collection',
        ], 15);

        $this->assertSame([
            'id' => '100',
            'name' => 'New Item',
            'price' => 68.5,
            'qty' => 2,
            'discount' => 15.0,
            'fields' => [
                'description' => 'New item from collection',
            ],
        ], $item->toArray());

        return $item;
    }

    #[DependsUsingDeepClone('testToArrayMethodWithExtraArguments')]
    public function testGetProperties(Item $item): void
    {
        $this->assertSame('100', $item->get('id'));
        $this->assertSame('New Item', $item->get('name'));
        $this->assertEqualsWithDelta(68.5, $item->get('price'), PHP_FLOAT_EPSILON);
        $this->assertSame(2, $item->get('qty'));
        $this->assertEqualsWithDelta(15.0, $item->get('discount'), PHP_FLOAT_EPSILON);
    }

    #[DependsUsingDeepClone('testToArrayMethodWithExtraArguments')]
    public function testGetExtraFields(Item $item): void
    {
        $this->assertSame('New item from collection', $item->get('description'));
    }

    #[DependsUsingDeepClone('testToArrayMethodWithExtraArguments')]
    public function testDefaultValueOnInvalidField(Item $item): void
    {
        $this->assertNull($item->get('invalid'));
        $this->assertSame('some_Value', $item->get('invalid', 'some_Value'));
    }

    #[DependsUsingDeepClone('testToArrayMethodWithExtraArguments')]
    public function testAccessPropertiesAndFieldsAsPublicObjectProperties(Item $item): void
    {
        $this->assertSame('100', $item->id);
        $this->assertSame('New Item', $item->name);
        $this->assertEqualsWithDelta(68.5, $item->price, PHP_FLOAT_EPSILON);
        $this->assertSame(2, $item->qty);
        $this->assertEqualsWithDelta(15.0, $item->discount, PHP_FLOAT_EPSILON);
        $this->assertSame('New item from collection', $item->description);
        $this->assertNull($item->invalid);
    }

    public function testExceptionIsThrownForEmptyId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Item('', 'Item', 1125);
    }

    public function testExceptionIsThrownForEmptyName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Item('34', '', 1125);
    }
}
