<?php

declare(strict_types=1);

namespace Lombervid\ShoppingCart\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Lombervid\ShoppingCart\Item;
use PHPUnit\Framework\Attributes\DependsUsingDeepClone;

class ItemTest extends TestCase
{
    public function testSingleItem(): Item
    {
        $item = new Item('1', 'Item', 15);

        self::assertSame(15.0, $item->price());
        self::assertSame(15.0, $item->total());

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
        self::assertSame(15.0, $item->total());

        $item->add(2);
        self::assertSame(45.0, $item->total());
    }

    #[DependsUsingDeepClone('testSingleItem')]
    public function testUpdateQuantity(Item $item): void
    {
        self::assertSame(15.0, $item->total());

        $item->update(2);
        self::assertSame(30.0, $item->total());
    }

    public function testAddingItemWithQuantity(): void
    {
        $item = new Item('1', 'Item', 15, 3);

        self::assertSame(15.0, $item->price());
        self::assertSame(45.0, $item->total());
    }

    public function testNoDiscount(): void
    {
        $item = new Item('1', 'Item', 15);
        self::assertFalse($item->hasDiscount());
        self::assertSame(15.0, $item->total());
    }

    public function testDiscountApplies(): void
    {
        $item = new Item('1', 'Item', 15, discount: 10);
        self::assertTrue($item->hasDiscount());
        self::assertSame(5.0, $item->total());
    }

    public function testDiscountAppliesWithQuantity(): void
    {
        $item = new Item('1', 'Item', 15, 3, discount: 10);
        self::assertTrue($item->hasDiscount());
        self::assertSame(15.0, $item->total());
    }

    public function testExceptionsAreThrownWithNegativeDiscount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Item('1', 'Item', 115, discount: -10);
    }

    public function testToArrayMethod(): void
    {
        $item = new Item('1', 'Item', 15);

        self::assertSame([
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

        self::assertSame([
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
        self::assertSame('100', $item->get('id'));
        self::assertSame('New Item', $item->get('name'));
        self::assertSame(68.5, $item->get('price'));
        self::assertSame(2, $item->get('qty'));
        self::assertSame(15.0, $item->get('discount'));
    }

    #[DependsUsingDeepClone('testToArrayMethodWithExtraArguments')]
    public function testGetExtraFields(Item $item): void
    {
        self::assertSame('New item from collection', $item->get('description'));
    }

    #[DependsUsingDeepClone('testToArrayMethodWithExtraArguments')]
    public function testDefaultValueOnInvalidField(Item $item): void
    {
        self::assertNull($item->get('invalid'));
        self::assertSame('some_Value', $item->get('invalid', 'some_Value'));
    }

    #[DependsUsingDeepClone('testToArrayMethodWithExtraArguments')]
    public function testAccessPropertiesAndFieldsAsPublicObjectProperties(Item $item): void
    {
        self::assertSame('100', $item->id);
        self::assertSame('New Item', $item->name);
        self::assertSame(68.5, $item->price);
        self::assertSame(2, $item->qty);
        self::assertSame(15.0, $item->discount);
        self::assertSame('New item from collection', $item->description);
        self::assertNull($item->invalid);
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
