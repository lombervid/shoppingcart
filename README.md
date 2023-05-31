# ShoppingCart PHP Class

* Repository: https://github.com/lombervid/shoppingcart
* Version: 1.0.0

## Description

ShoppingCart is a free PHP class that provides you with a simple shopping cart implementation stored in `session`.

## Installation

### Direct download

You can use the links on the main project page to either clone the repo or download
the [ZIP file](https://github.com/lombervid/shoppingcart/archive/master.zip). For
convenience, an autoloader script is provided in `src/autoload.php` which you
can require into your script. For
example:

```php
require '/path/to/shoppingcart/src/autoload.php';
$shoppingCart = new \ShoppingCart\ShoppingCart();
```

The classes in the project are structured according to the
[PSR-4](http://www.php-fig.org/psr/psr-4/) standard, so you may of course also
use your own autoloader or require the needed files directly in your code.

## Usage

### Constructor

There are two ways to initialize the `ShoppingCart Class`. One of them is calling the `constructor` without parameters.

```php
$shoppingCart = new \ShoppingCart\ShoppingCart();
```

Which automaticly assigns the name of `shopping_cart` to your session shopping cart.

The other way is passing the name to the constructor.

```php
$shoppingCart = new \ShoppingCart\ShoppingCart('my_shopping_cart');
```

#### Add items

You can add items calling the method `add($id, $amount = 1)` which receive as parameters `$id` and `$amount` (optional). If `$amount` is not received then `$amount = 1`.

```php
$shoppingCart = new \ShoppingCart\ShoppingCart();

$shoppingCart->add(15);		// Item ID
$shoppingCart->add(58, 3);	// Item ID, Amount
$shoppingCart->add(15, 5);

foreach ($shoppingCart->items() as $item) {
	# $item['id']
	# $item['amount']
}
```
at this point your `$shoppingCart->items()` is like this:
```php
Array
(
    [9bf31c7ff062936a96d3c8bd1f8f2ff3] => Array
        (
            [id] => 15
            [amount] => 6
        )

    [66f041e16a60928b05a7e228a89c3799] => Array
        (
            [id] => 58
            [amount] => 3
        )

)
```

#### Add extra fields to your shopping cart

You can also add extra fields (such as price, name, etc) to your shopping cart. The `ShoppingCart constructor` receives another parameter which is an `Array` with the following structure:

```php
Array(
	'field_name'	=>	'field_value',
	'field_name2'	=>	'field_value2'
)
```

when you provide the `$fields` param, each field of the array is added to your item.

```php
$fields = array(
	'name'	=>	'Product 1',
	'price'	=>	50
);

$shoppingCart = new \ShoppingCart\ShoppingCart();
$shoppingCart->add(10, 2, $fields);
```

with the above code your `$shoppingCart->items()` will return:

```php
Array
(
    [d3d9446802a44259755d38e6d163e820] => Array
        (
            [id] => 10
            [amount] => 2
            [name] => Product 1
            [price] => 50
        )

)
```

#### Delete items

You can delete an item calling the method `delete($id)` which receive `$id` as parameter.

```php
$shoppingCart->delete(15);
```

#### Cleaning the cart

You can clean the shopping cart calling the method `clean()` which remove all the items from the cart.

```php
$shoppingCart->clean();
```

## Contributing

Refer to [CONTRIBUTING](./.github/CONTRIBUTING.md) for information.
