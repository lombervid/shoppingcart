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
        $item = new Item(1, 'Item', 15);

        $this->assertSame(15.0, $item->price());
        $this->assertSame(15.0, $item->total());

        return $item;
    }

    public function testExceptionsAreThrownForNegativePrice(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Item(1, 'Item', -15);
    }

    public function testExceptionsAreThrownForZeroOrLowerQuantity(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Item(1, 'Item', 15, 0);
    }

    #[DependsUsingDeepClone('testSingleItem')]
    public function testAddQuantity(Item $item): void
    {
        $this->assertSame(15.0, $item->total());

        $item->add(2);
        $this->assertSame(45.0, $item->total());
    }

    #[DependsUsingDeepClone('testSingleItem')]
    public function testUpdateQuantity(Item $item): void
    {
        $this->assertSame(15.0, $item->total());

        $item->update(2);
        $this->assertSame(30.0, $item->total());
    }

    public function testAddingItemWithQuantity(): void
    {
        $item = new Item(1, 'Item', 15, 3);

        $this->assertSame(15.0, $item->price());
        $this->assertSame(45.0, $item->total());
    }

    public function testNoDiscount(): void
    {
        $item = new Item(1, 'Item', 15);
        $this->assertSame(false, $item->hasDiscount());
        $this->assertSame(15.0, $item->total());
    }

    public function testDiscountApplies(): void
    {
        $item = new Item(1, 'Item', 15, discount: 10);
        $this->assertSame(true, $item->hasDiscount());
        $this->assertSame(5.0, $item->total());
    }

    public function testDiscountAppliesWithQuantity(): void
    {
        $item = new Item(1, 'Item', 15, 3, discount: 10);
        $this->assertSame(true, $item->hasDiscount());
        $this->assertSame(15.0, $item->total());
    }

    public function testExceptionsAreThrownWithNegativeDiscount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Item(1, 'Item', 115, discount: -10);
    }

    public function testToArrayMethod(): void
    {
        $item = new Item(1, 'Item', 15);

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
        $item = new Item(100, 'New Item', 68.5, 2, [
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
        $this->assertSame(68.5, $item->get('price'));
        $this->assertSame(2, $item->get('qty'));
        $this->assertSame(15.0, $item->get('discount'));
    }

    #[DependsUsingDeepClone('testToArrayMethodWithExtraArguments')]
    public function testGetExtraFields(Item $item): void
    {
        $this->assertSame('New item from collection', $item->get('description'));
    }

    #[DependsUsingDeepClone('testToArrayMethodWithExtraArguments')]
    public function testEmptyStringOnInvalidField(Item $item): void
    {
        $this->assertSame('', $item->get('invalid'));
    }
}
