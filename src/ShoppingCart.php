<?php

/**
 * This is a PHP library to create a simple shopping cart.
 *
 * @copyright Copyright (c) 2023, lombervid.
 * @link      https://github.com/lombervid/shoppingcart
 *
 * @package   ShoppingCart
 */

declare(strict_types=1);

namespace Lombervid\ShoppingCart;

use Lombervid\ShoppingCart\Component\Storage\NativeSessionStorage;
use Lombervid\ShoppingCart\Component\Storage\StorageInterface;
use Lombervid\ShoppingCart\Component\Support\Arr;

class ShoppingCart
{
    final public const VERSION = '3.0';

    /**
     * @var Item[] Array of items.
     */
    protected array $items;

    /**
     * @var StorageInterface Storage object.
     */
    protected StorageInterface $storage;

    /** @phpstan-var TCartOptions */
    protected array $options = [
        'name'     => 'shopping_cart',
        'autosave' => true,
        'tax'      => 0,
        'shipping' => [
            'amount' => 0,
            'free'   => 0,
        ],
    ];

    /**
     * Constructor.
     *
     * @phpstan-param TOptions $options
     * @param array $options Cart options.
     * @param StorageInterface $storage Cart storage.
     */
    public function __construct(array $options = [], StorageInterface $storage = null)
    {
        $this->items   = [];
        $this->storage = $storage ?? new NativeSessionStorage();
        $this->options = $this->filterOptions($options);
        $this->load();
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        if ($this->getOption('autosave') === true) {
            $this->save();
        }
    }

    /**
     * Add an item in the cart
     *
     * @param Item $item Item to be added.
     * @param bool $append If item exist: true => appends the new qty to current, false => replace the current with new
     */
    public function add(Item $item, bool $append = true): void
    {
        $id = $item->get('id');

        if (!$this->inCart($id)) {
            $this->items[$id] = $item;
            return;
        }

        if ($append) {
            $this->items[$id]->add($item->get('qty'));
        } else {
            $this->items[$id]->update($item->get('qty'));
        }
    }

    /**
     * Remove an item from the cart
     *
     * @param  string $id Item ID to delete
     */
    public function remove(string $id): bool
    {
        if (!$this->inCart($id)) {
            return false;
        }

        unset($this->items[$id]);

        return true;
    }

    /**
     * Return the total of the cart
     *
     * @return float Cart total
     */
    public function total(): float
    {
        return $this->subtotal() + $this->shipping() + $this->tax();
    }

    /**
     * Return the total price of all the items
     *
     * @return float Cart subtotal
     */
    public function subtotal(): float
    {
        $subtotal = 0;

        foreach ($this->items as $item) {
            $subtotal += $item->total();
        }

        return $subtotal;
    }

    /**
     * Return the shipping cost
     *
     * @return float Shipping cost
     */
    public function shipping(): float
    {
        $shipping = $this->getOption('shipping');
        $free = floatval($shipping['free']);

        if ($this->isEmpty()) {
            return 0;
        }

        if (0 < $free && $free <= $this->subtotal()) {
            return 0;
        }

        return floatval($shipping['amount']);
    }

    /**
     * Return the tax
     *
     * @return float Tax
     */
    public function tax(): float
    {
        $tax = floatval($this->getOption('tax'));

        if ($tax <= 0) {
            return 0;
        }

        return ($this->subtotal() + $this->shipping()) * $tax / 100;
    }

    /**
     * Checks if item exists in cart
     *
     * @param string $id Item ID to find
     */
    public function inCart(string $id): bool
    {
        return array_key_exists($id, $this->items);
    }

    /**
     * Return all the items
     *
     * @return Item[] Items in cart
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * Remove all items
     *
     * @return static
     */
    public function clear(): static
    {
        $this->items = [];

        return $this;
    }

    /**
     * Checks if the cart is empty
     *
     * @return  bool Return true if there are not items in the cart, otherwise false
     */
    public function isEmpty(): bool
    {
        return ($this->totalItems() <= 0);
    }

    /**
     * Return the total number of items in the cart
     *
     * @return int Total items in the cart
     */
    public function totalItems(): int
    {
        return count($this->items);
    }

    /**
     * Save the items in the Session.
     */
    public function save(): void
    {
        $this->storage->set($this->getOption('name'), $this->toArray());
    }

    /**
     * Return a list of items as array
     *
     * @phpstan-return array<TItemArray>
     * @return array List of items as array
     */
    public function toArray(): array
    {
        return array_map(fn($item) => $item->toArray(), $this->items);
    }

    /**
     * Filter the cart options
     *
     * @phpstan-param TOptions $options
     * @phpstan-return TCartOptions
     * @param array $options Cart options
     *
     * @return array Filtered options
     */
    protected function filterOptions(array $options): array
    {
        /** @phpstan-var TCartOptions */
        return array_replace_recursive(
            $this->options,
            Arr::intersectKeyRecursive($options, $this->options)
        );
    }

    /**
     * Get value of the option name
     *
     * @param string $name Option name
     *
     * @phpstan-return (
     *      $name is "name" ? string :
     *      ($name is "autosave" ? bool :
     *      ($name is "tax" ? float :
     *      ($name is "shipping" ? TShipping : mixed)))
     * )
     * @return mixed Option value
     */
    protected function getOption(string $name): mixed
    {
        if (!array_key_exists($name, $this->options)) {
            throw new \Exception('Invalid option', 1);
        }

        return $this->options[$name];
    }

    /**
     * Load the items from session
     */
    protected function load(): void
    {
        $items = $this->storage->get($this->getOption('name'));

        if (is_array($items)) {
            /** @phpstan-var TItemArray $item */
            foreach ($items as $item) {
                $this->add(new Item(
                    Arr::get($item, 'id'),
                    Arr::get($item, 'name'),
                    Arr::get($item, 'price', empty: false),
                    Arr::get($item, 'qty'),
                    Arr::get($item, 'fields', [], 'array'),
                    Arr::get($item, 'discount', empty: false)
                ));
            }
        }
    }
}
