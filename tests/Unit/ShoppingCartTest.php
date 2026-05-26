<?php

declare(strict_types=1);

namespace Lombervid\ShoppingCart\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Lombervid\ShoppingCart\Component\Storage\StorageInterface;
use Lombervid\ShoppingCart\ShoppingCart;
use Lombervid\ShoppingCart\Item;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\MockObject\MockObject;

final class ShoppingCartTest extends TestCase
{
    private StorageInterface&MockObject $storage;

    private ShoppingCart $cart;

    public function testThereAreNoItemsWhenCartIsCreated(): void
    {
        $this->assertSame([], $this->cart->items());
        $this->assertSame(0, $this->cart->totalItems());
        $this->assertTrue($this->cart->isEmpty());
    }

    public function testNoShippingCostWhenCartIsEmpty(): ShoppingCart
    {
        $options = ['shipping' => ['amount' => 150]];
        $cart = new ShoppingCart($options, $this->storage);
        $this->assertEqualsWithDelta(0.0, $cart->total(), PHP_FLOAT_EPSILON);

        return $cart;
    }

    public function testAddItem(): ShoppingCart
    {
        $this->cart->add(new Item('15', 'Item', 50.5));
        $this->assertSame(1, $this->cart->totalItems());

        return $this->cart;
    }

    #[Depends('testAddItem')]
    public function testItemAlreadyInCartIsAddedCorrectly(ShoppingCart $cart): ShoppingCart
    {
        $cart->add(new Item('15', 'Item', 50.5));
        $this->assertSame(1, $cart->totalItems());
        $this->assertEqualsWithDelta(101.0, $cart->total(), PHP_FLOAT_EPSILON);

        return $cart;
    }

    #[Depends('testItemAlreadyInCartIsAddedCorrectly')]
    public function testNewItemIsAddedCorrectly(ShoppingCart $cart): ShoppingCart
    {
        $cart->add(new Item('25', 'Item 2', 100));
        $this->assertSame(2, $cart->totalItems());
        $this->assertEqualsWithDelta(201.0, $cart->total(), PHP_FLOAT_EPSILON);

        return $cart;
    }

    #[Depends('testNewItemIsAddedCorrectly')]
    public function testAddItemAlreadyInCartReplacingQuantity(ShoppingCart $cart): ShoppingCart
    {
        $cart->add(new Item('15', 'Item', 50.5), false);
        $this->assertEqualsWithDelta(150.5, $cart->total(), PHP_FLOAT_EPSILON);

        return $cart;
    }

    #[Depends('testAddItemAlreadyInCartReplacingQuantity')]
    public function testAddingItemAlreadyInCartKeepsOriginalPrice(ShoppingCart $cart): ShoppingCart
    {
        $cart->add(new Item('15', 'Item', 1500));
        $this->assertEqualsWithDelta(201.0, $cart->total(), PHP_FLOAT_EPSILON);

        return $cart;
    }

    #[Depends('testAddingItemAlreadyInCartKeepsOriginalPrice')]
    public function testRemoveItem(ShoppingCart $cart): ShoppingCart
    {
        $cart->remove('15');
        $this->assertSame(1, $cart->totalItems());
        $this->assertEqualsWithDelta(100.0, $cart->total(), PHP_FLOAT_EPSILON);

        return $cart;
    }

    #[Depends('testRemoveItem')]
    public function testClearCart(ShoppingCart $cart): void
    {
        $this->assertSame(1, $cart->totalItems());
        $this->assertEqualsWithDelta(100.0, $cart->total(), PHP_FLOAT_EPSILON);

        $cart->clear();
        $this->assertSame([], $cart->items());
        $this->assertSame(0, $cart->totalItems());
        $this->assertEqualsWithDelta(0.0, $cart->total(), PHP_FLOAT_EPSILON);
    }

    public function testTax(): void
    {
        $cart = new ShoppingCart(['tax' => 15], $this->storage);
        $cart->add(new Item('25', 'Item', 100));
        $this->assertEqualsWithDelta(115.0, $cart->total(), PHP_FLOAT_EPSILON);
    }

    #[Depends('testNoShippingCostWhenCartIsEmpty')]
    public function testShipping(ShoppingCart $cart): void
    {
        $cart->add(new Item('25', 'Item', 100));
        $this->assertEqualsWithDelta(250.0, $cart->total(), PHP_FLOAT_EPSILON);
    }

    public function testFreeShippingAfterCertainAmount(): void
    {
        $options = [
            'shipping' => [
                'amount' => 150,
                'free' => 500,
            ]
        ];

        $cart = new ShoppingCart($options, $this->storage);
        $cart->add(new Item('25', 'Item', 100));
        $this->assertEqualsWithDelta(250.0, $cart->total(), PHP_FLOAT_EPSILON);

        $cart->add(new Item('15', 'Item', 399));
        $this->assertEqualsWithDelta(649.0, $cart->total(), PHP_FLOAT_EPSILON);

        $cart->add(new Item('13', 'Item', 1));
        $this->assertEqualsWithDelta(500.0, $cart->total(), PHP_FLOAT_EPSILON);
    }

    public function testShippingAndTaxt(): void
    {
        $options = [
            'tax' => 15,
            'shipping' => [
                'amount' => 150,
                'free' => 700,
            ],
        ];

        $cart = new ShoppingCart($options, $this->storage);
        $cart->add(new Item('25', 'Item', 100));
        $this->assertEqualsWithDelta(287.5, $cart->total(), PHP_FLOAT_EPSILON);

        $cart->add(new Item('15', 'Item', 600));
        $this->assertEqualsWithDelta(805.00, $cart->total(), PHP_FLOAT_EPSILON);
    }

    public function testToArray(): void
    {
        $items = $this->items();

        $cart = new ShoppingCart(storage: $this->storage);
        $this->assertSame([], $cart->toArray());

        foreach (
            $items as [
                'id' => $id,
                'name' => $name,
                'price' => $price,
                'qty' => $qty,
                'discount' => $discount,
                'fields' => $fields
            ]
        ) {
            $cart->add(new Item($id, $name, $price, $qty, $fields, $discount));
        }

        $this->assertSame($items, $cart->toArray());
    }

    public function testLoadCartFromStorage(): void
    {
        $items = $this->items();
        $this
            ->storage
            ->expects($this->once())
            ->method('get')
            ->with(self::identicalTo('shopping_cart'))
            ->willReturn($items);

        $cart = new ShoppingCart(storage: $this->storage);
        $this->assertSame($items, $cart->toArray());
    }

    /**
     * @return array<TItemArray>
     */
    protected function items(): array
    {
        return [
            '15' => [
                'id'       => '15',
                'name'     => 'My Item',
                'price'    => 50.0,
                'qty'      => 3,
                'discount' => 0.0,
                'fields'   => [],
            ],
            '3456' => [
                'id'       => '3456',
                'name'     => 'My Item 2',
                'price'    => 150.25,
                'qty'      => 1,
                'discount' => 0.0,
                'fields'   => [],
            ],
            '2456' => [
                'id'       => '2456',
                'name'     => 'My Item 3',
                'price'    => 75.0,
                'qty'      => 2,
                'discount' => 0.0,
                'fields'   => ['size' => 'M'],
            ],
            '8906' => [
                'id'       => '8906',
                'name'     => 'My Item 4',
                'price'    => 10.36,
                'qty'      => 1,
                'discount' => 5.12,
                'fields'   => [],
            ],
            '4567' => [
                'id'       => '4567',
                'name'     => 'My Item 5',
                'price'    => 0.0,
                'qty'      => 1,
                'discount' => 0.0,
                'fields'   => [],
            ],
        ];
    }

    protected function setUp(): void
    {
        $this->storage = $this->createMock(StorageInterface::class);
        $this->cart = new ShoppingCart(storage: $this->storage);
    }
}
