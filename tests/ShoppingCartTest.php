<?php

declare(strict_types=1);

namespace Lombervid\ShoppingCart\Tests;

use PHPUnit\Framework\TestCase;
use Lombervid\ShoppingCart\Component\Session\Storage\SessionStorageInterface;
use Lombervid\ShoppingCart\ShoppingCart;
use Lombervid\ShoppingCart\Item;

class ShoppingCartTest extends TestCase
{
    protected function setUp(): void
    {
        $this->storage = $this->createMock(SessionStorageInterface::class);
        $this->cart = new ShoppingCart(storage: $this->storage);
    }

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
        $this->cart->add(new Item(15, 'Item', 50.5));

        $this->assertSame(1, $this->cart->totalItems());

        return $this->cart;
    }

    /**
     * @depends testAddItem
     */
    public function testItemAlreadyInCartIsAddedCorrectly(ShoppingCart $cart): ShoppingCart
    {
        $cart->add(new Item(15, 'Item', 50.5));

        $this->assertSame(1, $cart->totalItems());
        $this->assertSame(101.0, $cart->total());

        return $cart;
    }

    /**
     * @depends testItemAlreadyInCartIsAddedCorrectly
     */
    public function testNewItemIsAddedCorrectly(ShoppingCart $cart): ShoppingCart
    {
        $cart->add(new Item(25, 'Item 2', 100));

        $this->assertSame(2, $cart->totalItems());
        $this->assertSame(201.0, $cart->total());

        return $cart;
    }

    /**
     * @depends testNewItemIsAddedCorrectly
     */
    public function testAddItemAlreadyInCartReplacingQuantity(ShoppingCart $cart): ShoppingCart
    {
        $cart->add(new Item(15, 'Item', 50.5), false);

        $this->assertSame(150.5, $cart->total());

        return $cart;
    }

    /**
     * @depends testAddItemAlreadyInCartReplacingQuantity
     */
    public function testAddingItemAlreadyInCartKeepsOriginalPrice(ShoppingCart $cart): ShoppingCart
    {
        $cart->add(new Item(15, 'Item', 1500));

        $this->assertSame(201.0, $cart->total());

        return $cart;
    }

    /**
     * @depends testAddingItemAlreadyInCartKeepsOriginalPrice
     */
    public function testRemoveItem(ShoppingCart $cart): ShoppingCart
    {
        $cart->remove(15);

        $this->assertSame(1, $cart->totalItems());
        $this->assertSame(100.0, $cart->total());

        return $cart;
    }

    /**
     * @depends testRemoveItem
     */
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
        $cart->add(new Item(25, 'Item', 100));

        $this->assertSame(115.0, $cart->total());
    }

    /**
     * @depends testNoShippingCostWhenCartIsEmpty
     */
    public function testShipping($cart): void
    {
        $cart->add(new Item(25, 'Item', 100));

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
        $cart->add(new Item(25, 'Item', 100));

        $this->assertSame(250.0, $cart->total());

        $cart->add(new Item(15, 'Item', 399));

        $this->assertSame(649.0, $cart->total());

        $cart->add(new Item(13, 'Item', 1));

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
        $cart->add(new Item(25, 'Item', 100));

        $this->assertSame(287.5, $cart->total());

        $cart->add(new Item(15, 'Item', 600));

        $this->assertSame(805.00, $cart->total());
    }
}
