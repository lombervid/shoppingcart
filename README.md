<div align="center">

[![GitHub release (latest SemVer)](https://img.shields.io/github/v/release/lombervid/shoppingcart?display_name=tag&sort=semver)](https://github.com/lombervid/shoppingcart/releases/latest)
[![Packagist](https://img.shields.io/packagist/v/lombervid/shoppingcart)](https://packagist.org/packages/lombervid/shoppingcart)
![PHP Version](https://poser.pugx.org/lombervid/shoppingcart/require/php)
[![tests](https://github.com/lombervid/shoppingcart/actions/workflows/tests.yml/badge.svg)](https://github.com/lombervid/shoppingcart/actions/workflows/tests.yml?query=branch%3Amain)
[![GitHub](https://img.shields.io/github/license/lombervid/shoppingcart)](https://github.com/lombervid/shoppingcart/blob/main/LICENSE)

</div>

# ShoppingCart PHP Class

**_ShoppingCart_** is a simple _PHP_ package that provides you with a simple shopping cart implementation stored in `session`.

## Installation

### Composer

You can install it using [composer](https://getcomposer.org/):

```bash
composer require lombervid/shoppingcart
```

## Usage

Create an instance of `ShoppingCart` class.

```php
use Lombervid\ShoppingCart\ShoppingCart;

$shoppingCart = new ShoppingCart();
```

### Add items

You can add items calling the method `add()` passing an `Item` instance as parameter.

```php
use Lombervid\ShoppingCart\Item;
use Lombervid\ShoppingCart\ShoppingCart;

$cart = new ShoppingCart();
$cart->add(new Item('1', 'Cake', 15.56));
$cart->add(new Item('15', 'Frappe', 5));

foreach ($cart->items() as $item) {
	// $item->id
	// $item->name
}
```

at this point your `$cart->items()` will look like this:

```php
array:2 [▼
  1 => Lombervid\ShoppingCart\Item {#5 ▼
    -id: "1"
    -name: "Cake"
    -price: 15.56
  }
  15 => Lombervid\ShoppingCart\Item {#6 ▼
    -id: "15"
    -name: "Frappe"
    -price: 5.0
  }
]
```

### Add extra fields to your item

You can also add extra fields (such as price, name, etc) to your item. The `Item` constructor receives a parameter `fields` which is an `Array` with the following structure:

```php
[
    'field_name'   => 'field_value',
    'field_2_name' => 'field_2_value'
]
```

when you provide the `$fields` param, each field of the array is added to your item.

```php
$fields = [
	'size'  => 'XL',
	'color' => 'blue'
];

$item = new Item('23', 'My Shirt', 2.5, fields: $fields);
$cart->add($item);
```

with the above code your `$cart->items()` will look line:

```php
array:1 [▼
  23 => Lombervid\ShoppingCart\Item {#5 ▼
    -id: "23"
    -name: "My Shirt"
    -price: 2.5
    -qty: 1
    -fields: array:2 [▼
      "size" => "XL"
      "color" => "blue"
    ]
  }
]
```

Then you can access any extra field as if they were properties:

```php
foreach ($cart->items() as $item) {
    // $item->size
    // $item->color
}
```

### Remove items

You can remove an item from the cart calling the method `remove($id)` which receive item's `$id` as parameter.

```php
$cart->remove(23);
```

### Clear the cart

You can clear the cart calling the method `clear()` which removes all the items from the cart.

```php
$shoppingCart->clear();
```

## Advanced options

### ShoppingCart

#### Cart options

It is an `array` of options. The default value is:

```php
[
    'name'     => 'shopping_cart',
    'autosave' => true,
    'tax'      => 0,
    'shipping' => [
        'amount' => 0,
        'free'   => 0,
    ],
]
```

| Option            | Type         | Default         | Description                                              |
| :---------------- | ------------ | --------------- | -------------------------------------------------------- |
| `name`            | `string`     | `shopping_cart` | Cart's name. Used to save the cart in storage            |
| `autosave`        | `bool`       | `true`          | If set to `true`, cart is saved when object is destroyed |
| `tax`             | `int\|float` | `0`             | Porcentaje to be used as tax (0 - 100)                   |
| `shipping.amount` | `int\|float` | `0`             | Shipping cost                                            |
| `shipping.free`   | `int\|float` | `0`             | Value after which shipping will be free. `0` to disable  |

#### Constructor

| Parameter  | Type               | Default                           | Required | Description    |
| :--------- | ------------------ | --------------------------------- | -------- | -------------- |
| `$options` | `array`            | See [Cart options](#cart-options) | `false`  | Cart options   |
| `$storage` | `StorageInterface` | `NativeSessionStorage`            | `false`  | Storage driver |

#### Methods

| Name                                         | Description                                   |
| :------------------------------------------- | --------------------------------------------- |
| `add(Item $item, bool $append = true): void` | Add an item to the cart                       |
| `remove(string $id): bool`                   | Remove an item from the cart                  |
| `subtotal(): float`                          | Get subtotal                                  |
| `shipping(): float`                          | Get shipping cost                             |
| `tax(): float`                               | Get tax                                       |
| `total(): float`                             | Get total                                     |
| `inCart(string $id): bool`                   | Check if an item is in the cart               |
| `items(): array`                             | Return the items in the cart                  |
| `clear(): static`                            | Remove all items from the cart                |
| `isEmpty(): bool`                            | Check if the cart is empty                    |
| `totalItems(): int`                          | Return the total (distinct) items in the cart |
| `save(): void`                               | Save items in the storage                     |
| `toArray(): array`                           | Return items as `array`                       |

### Item

#### Constructor

| Parameter   | Type     | Default | Required | Description        |
| :---------- | -------- | ------- | -------- | ------------------ |
| `$id`       | `string` | `N/A`   | `true`   | Identifier         |
| `$name`     | `string` | `N/A`   | `true`   | Name / description |
| `$price`    | `float`  | `N/A`   | `true`   | Price (`>= 0`)     |
| `$qty`      | `int`    | `1`     | `false`  | Quantity (`> 0`)   |
| `$fields`   | `array`  | `[]`    | `false`  | Extra fields       |
| `$discount` | `float`  | `0`     | `false`  | Discount (`>= 0`)  |

#### Methods

| Name                                              | Description                        |
| :------------------------------------------------ | ---------------------------------- |
| `add(int $qty): void`                             | Increase item's quantity by `$qty` |
| `update(int $qty): void`                          | Update item's quantity to `$qty`   |
| `get(string $name, mixed $default = null): mixed` | Get `$name` property/field         |
| `hasDiscount(): bool`                             | Check if has a discount            |
| `price(): float`                                  | Get item's price                   |
| `total(): float`                                  | Get total                          |
| `toArray(): array`                                | Return item as `array`             |

## Contributing

Refer to [CONTRIBUTING](./.github/CONTRIBUTING.md) for information.

## License

[MIT](https://github.com/lombervid/shoppingcart/blob/main/LICENSE)
