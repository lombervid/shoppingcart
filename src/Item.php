<?php

declare(strict_types=1);

namespace Lombervid\ShoppingCart;

class Item
{
    public function __construct(
        private string $id,
        private string $name,
        private float $price,
        private int $qty = 1,
        private array $fields = [],
        private float $discount = 0,
    ) {
        if ($id === '') {
            throw new \InvalidArgumentException('ID must not be empty');
        }

        if ($name === '') {
            throw new \InvalidArgumentException('Name must not be empty');
        }

        if ($price < 0) {
            throw new \InvalidArgumentException('Price must to be greater than or equal to zero');
        }

        if ($qty <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero');
        }

        if ($discount < 0) {
            throw new \InvalidArgumentException('Discount must be greater than or equal to zero');
        }
    }

    public function add(int $qty): void
    {
        $this->qty += $qty;
    }

    public function update(int $qty): void
    {
        $this->qty = $qty;
    }

    public function get(string $name): mixed
    {
        if ('fields' != $name && property_exists($this, $name)) {
            return $this->{$name};
        }

        if (array_key_exists($name, $this->fields)) {
            return $this->fields[$name];
        }

        return '';
    }

    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    public function hasDiscount(): bool
    {
        return (0 < $this->discount);
    }

    public function price(): float
    {
        return $this->price - $this->discount();
    }

    public function total(): float
    {
        return $this->price() * $this->qty;
    }

    public function toArray(): array
    {
        return [
            'id'       => $this->id,
            'name'     => $this->name,
            'price'    => $this->price,
            'qty'      => $this->qty,
            'discount' => $this->discount,
            'fields'   => $this->fields,
        ];
    }

    private function discount(): float
    {
        return $this->discount;
    }
}
