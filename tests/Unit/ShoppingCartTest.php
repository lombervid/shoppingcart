<?php

declare(strict_types=1);

namespace Lombervid\ShoppingCart\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Lombervid\ShoppingCart\Component\Storage\StorageInterface;
use Lombervid\ShoppingCart\ShoppingCart;
use Lombervid\ShoppingCart\Item;
use PHPUnit\Framework\Attributes\Depends;
use PHPUnit\Framework\MockObject\MockObject;

class ShoppingCartTest extends TestCase
{
    private StorageInterface&MockObject $storage;
    private ShoppingCart $cart;

    public function testThereAreNoItemsWhenCartIsCreated(): void
    {
        self::assertSame([], $this->cart->items());
        self::assertSame(0, $this->cart->totalItems());
        self::assertTrue($this->cart->isEmpty());
    }

    public function testNoShippingCostWhenCartIsEmpty(): ShoppingCart
    {
        $options = ['shipping' => ['amount' => 150]];
        $cart = new ShoppingCart($options, $this->storage);
        self::assertSame(0.0, $cart->total());

        return $cart;
    }

    public function testAddItem(): ShoppingCart
    {
        $this->cart->add(new Item('15', 'Item', 50.5));
        self::assertSame(1, $this->cart->totalItems());

        return $this->cart;
    }

    #[Depends('testAddItem')]
    public function testItemAlreadyInCartIsAddedCorrectly(ShoppingCart $cart): ShoppingCart
    {
        $cart->add(new Item('15', 'Item', 50.5));
        self::assertSame(1, $cart->totalItems());
        self::assertSame(101.0, $cart->total());

        return $cart;
    }

    #[Depends('testItemAlreadyInCartIsAddedCorrectly')]
    public function testNewItemIsAddedCorrectly(ShoppingCart $cart): ShoppingCart
    {
        $cart->add(new Item('25', 'Item 2', 100));
        self::assertSame(2, $cart->totalItems());
        self::assertSame(201.0, $cart->total());

        return $cart;
    }

    #[Depends('testNewItemIsAddedCorrectly')]
    public function testAddItemAlreadyInCartReplacingQuantity(ShoppingCart $cart): ShoppingCart
    {
        $cart->add(new Item('15', 'Item', 50.5), false);
        self::assertSame(150.5, $cart->total());

        return $cart;
    }

    #[Depends('testAddItemAlreadyInCartReplacingQuantity')]
    public function testAddingItemAlreadyInCartKeepsOriginalPrice(ShoppingCart $cart): ShoppingCart
    {
        $cart->add(new Item('15', 'Item', 1500));
        self::assertSame(201.0, $cart->total());

        return $cart;
    }

    #[Depends('testAddingItemAlreadyInCartKeepsOriginalPrice')]
    public function testRemoveItem(ShoppingCart $cart): ShoppingCart
    {
        $cart->remove('15');
        self::assertSame(1, $cart->totalItems());
        self::assertSame(100.0, $cart->total());

        return $cart;
    }

    #[Depends('testRemoveItem')]
    public function testClearCart(ShoppingCart $cart): void
    {
        self::assertSame(1, $cart->totalItems());
        self::assertSame(100.0, $cart->total());

        $cart->clear();
        self::assertSame([], $cart->items());
        self::assertSame(0, $cart->totalItems());
        self::assertSame(0.0, $cart->total());
    }

    public function testTax(): void
    {
        $cart = new ShoppingCart(['tax' => 15], $this->storage);
        $cart->add(new Item('25', 'Item', 100));
        self::assertSame(115.0, $cart->total());
    }

    #[Depends('testNoShippingCostWhenCartIsEmpty')]
    public function testShipping(ShoppingCart $cart): void
    {
        $cart->add(new Item('25', 'Item', 100));
        self::assertSame(250.0, $cart->total());
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
        self::assertSame(250.0, $cart->total());

        $cart->add(new Item('15', 'Item', 399));
        self::assertSame(649.0, $cart->total());

        $cart->add(new Item('13', 'Item', 1));
        self::assertSame(500.0, $cart->total());
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
        self::assertSame(287.5, $cart->total());

        $cart->add(new Item('15', 'Item', 600));
        self::assertSame(805.00, $cart->total());
    }

    public function testToArray(): void
    {
        $items = $this->items();

        $cart = new ShoppingCart(storage: $this->storage);
        self::assertSame([], $cart->toArray());

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
        self::assertSame($items, $cart->toArray());
    }

    public function testLoadCartFromStorage(): void
    {
        $items = $this->items();
        $this
            ->storage
            ->expects(self::once())
            ->method('get')
            ->with(self::identicalTo('shopping_cart'))
            ->willReturn($items);

        $cart = new ShoppingCart(storage: $this->storage);
        self::assertSame($items, $cart->toArray());
    }

    /**
     * @phpstan-return array<TItemArray>
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
