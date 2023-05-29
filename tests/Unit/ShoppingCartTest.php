<?php

declare(strict_types=1);

namespace Lombervid\ShoppingCart\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Lombervid\ShoppingCart\Component\Storage\StorageInterface;
use Lombervid\ShoppingCart\ShoppingCart;
use Lombervid\ShoppingCart\Item;
use PHPUnit\Framework\Attributes\Depends;

class ShoppingCartTest extends TestCase
{
    private StorageInterface $storage;
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
        $this->assertSame(0.0, $cart->total());

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
        $this->assertSame(101.0, $cart->total());

        return $cart;
    }

    #[Depends('testItemAlreadyInCartIsAddedCorrectly')]
    public function testNewItemIsAddedCorrectly(ShoppingCart $cart): ShoppingCart
    {
        $cart->add(new Item('25', 'Item 2', 100));
        $this->assertSame(2, $cart->totalItems());
        $this->assertSame(201.0, $cart->total());

        return $cart;
    }

    #[Depends('testNewItemIsAddedCorrectly')]
    public function testAddItemAlreadyInCartReplacingQuantity(ShoppingCart $cart): ShoppingCart
    {
        $cart->add(new Item('15', 'Item', 50.5), false);
        $this->assertSame(150.5, $cart->total());

        return $cart;
    }

    #[Depends('testAddItemAlreadyInCartReplacingQuantity')]
    public function testAddingItemAlreadyInCartKeepsOriginalPrice(ShoppingCart $cart): ShoppingCart
    {
        $cart->add(new Item('15', 'Item', 1500));
        $this->assertSame(201.0, $cart->total());

        return $cart;
    }

    #[Depends('testAddingItemAlreadyInCartKeepsOriginalPrice')]
    public function testRemoveItem(ShoppingCart $cart): ShoppingCart
    {
        $cart->remove('15');
        $this->assertSame(1, $cart->totalItems());
        $this->assertSame(100.0, $cart->total());

        return $cart;
    }

    #[Depends('testRemoveItem')]
    public function testClearCart(ShoppingCart $cart): void
    {
        $this->assertSame(1, $cart->totalItems());
        $this->assertSame(100.0, $cart->total());

        $cart->clear();
        $this->assertSame([], $cart->items());
        $this->assertSame(0, $cart->totalItems());
        $this->assertSame(0.0, $cart->total());
    }

    public function testTax(): void
    {
        $cart = new ShoppingCart(['tax' => 15], $this->storage);
        $cart->add(new Item('25', 'Item', 100));
        $this->assertSame(115.0, $cart->total());
    }

    #[Depends('testNoShippingCostWhenCartIsEmpty')]
    public function testShipping(ShoppingCart $cart): void
    {
        $cart->add(new Item('25', 'Item', 100));
        $this->assertSame(250.0, $cart->total());
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
        $this->assertSame(250.0, $cart->total());

        $cart->add(new Item('15', 'Item', 399));
        $this->assertSame(649.0, $cart->total());

        $cart->add(new Item('13', 'Item', 1));
        $this->assertSame(500.0, $cart->total());
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
        $this->assertSame(287.5, $cart->total());

        $cart->add(new Item('15', 'Item', 600));
        $this->assertSame(805.00, $cart->total());
    }

    public function testToArray(): void
    {
        $items = [
            md5('15') => [
                'id'       => '15',
                'name'     => 'My Item',
                'price'    => 50,
                'qty'      => 3,
                'discount' => 0,
                'fields'   => [],
            ],
            md5('3456') => [
                'id'       => '3456',
                'name'     => 'My Item 2',
                'price'    => 150.25,
                'qty'      => 1,
                'discount' => 0,
                'fields'   => [],
            ],
            md5('2456') => [
                'id'       => '2456',
                'name'     => 'My Item 3',
                'price'    => 75.0,
                'qty'      => 2,
                'discount' => 0,
                'fields'   => ['size' => 'M'],
            ],
            md5('8906') => [
                'id'       => '8906',
                'name'     => 'My Item 4',
                'price'    => 10.36,
                'qty'      => 1,
                'discount' => 5.12,
                'fields'   => [],
            ],
        ];

        $cart = new ShoppingCart(storage: $this->storage);
        $this->assertEquals([], $cart->toArray());

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
        $this->assertEquals($items, $cart->toArray());
    }

    protected function setUp(): void
    {
        $this->storage = $this->createMock(StorageInterface::class);
        $this->cart = new ShoppingCart(storage: $this->storage);
    }
}
