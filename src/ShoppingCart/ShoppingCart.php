<?php
/**
 * This is a PHP library to create a simple shopping cart.
 *
 * @copyright Copyright (c) 2016, Codelutions.
 * @link      https://github.com/lombervid/shoppingcart
 *
 * @package   ShoppingCart
 */
namespace ShoppingCart;

use ShoppingCart\Component\Session\Session;
use ShoppingCart\Component\Session\Storage\SessionStorageInterface;
use ShoppingCart\Item;

/**
 * ShoppingCart Class
 * @version 2.0
 */
class ShoppingCart
{
    use ArrayFunctionsTrait;

    /**
     * @const string Version of this library.
     */
    const VERSION = '2.0';

    /**
     * @var array Array of items.
     */
    protected $items;

    /**
     * @var ShoppingCart\Component\Session Session object.
     */
    protected $session;

    /**
     * @var array Cart options.
     */
    protected $options = array(
        'name'     => 'shopping_cart',
        'autosave' => true,
        'tax'      => 0,
        'shipping' => array(
            'amount' => 0,
            'free'   => 0,
        ),
    );

    /**
     * Constructor.
     *
     * @param string $name Name of the Session.
     */
    public function __construct(array $options = array(), SessionStorageInterface $storage = null)
    {
        $this->items   = array();
        $this->session = new Session($storage);
        $this->options = $this->filterOptions($options);
        $this->load();
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        if ($this->getOption('autosave')) {
            $this->save();
        }
    }

    /**
     * Add an item in the cart
     *
     * @param Item $item Item to be added.
     * @param bool $addQty If the items exists; true => add the new qty to current, false => replace the current with new
     */
    public function add(Item $item, $addQty = true)
    {
        $itemID = md5($item->get('id'));

        if ($this->inCart($itemID)) {
            if ($addQty) {
                $this->items[$itemID]->add($item->get('qty'));
            } else {
                $this->items[$itemID]->update($item->get('qty'));
            }
        } else {
            $this->items[$itemID] = $item;
        }
    }

    /**
     * Remove an item from the cart
     *
     * @param  integer $id Item ID to delete
     */
    public function remove($id)
    {
        $itemID = md5($id);

        if ($this->inCart($itemID)) {
            unset($this->items[$itemID]);
            return true;
        }

        return false;
    }

    /**
     * Return the total of the cart
     *
     * @return float Cart total
     */
    public function total()
    {
        return $this->subtotal() + $this->shipping() + $this->tax();
    }

    /**
     * Return the total price of all the items
     *
     * @return float Cart subtotal
     */
    public function subtotal()
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
    public function shipping()
    {
        $shipping = $this->getOption('shipping');
        $free     = floatval($shipping['free']);

        if ($this->subtotal() > $free) {
            return 0;
        }

        return floatval($shipping['amount']);
    }

    /**
     * Return the tax
     *
     * @return float Tax
     */
    public function tax()
    {
        $tax = floatval($this->getOption('tax'));

        if ($tax <= 0) {
            return 0;
        }

        return $this->subtotal() * $tax / 100;
    }

    /**
     * Description
     *
     * @param type $itemID
     * @param type|bool $encrypted
     * @return type
     */
    public function inCart($itemID, $encrypted = true)
    {
        $itemID = strval($itemID);

        if (!$encrypted) {
            $itemID = md5($itemID);
        }

        return array_key_exists($itemID, $this->items);
    }

    /**
     * Return the items
     *
     * @return array Items in the shopping cart
     */
    public function items()
    {
        return $this->items;
    }

    /**
     * Remove all items in the shopping cart
     */
    public function clear()
    {
        $this->items = array();

        return $this;
    }

    /**
     * Checks if the cart is empty
     *
     * @return  boolean Return true if there are not items in the cart, otherwise false
     */
    public function isEmpty()
    {
        return ($this->totalItems() <= 0);
    }

    /**
     * Checks the total items in the cart
     *
     * @return integer Total items in the cart
     */
    public function totalItems()
    {
        return count($this->items);
    }

    /**
     * Save the items in the Session.
     */
    public function save()
    {
        $this->session->set($this->getOption('name'), $this->itemsToArray());
    }

    /**
     * Return a list of items as array
     *
     * @return array List of items as array
     */
    protected function itemsToArray()
    {
        return array_map(function ($item) {
            return $item->toArray();
        }, $this->items);
    }

    /**
     * Filter the cart options
     *
     * @param array $options Cart options
     * @return array Filtered options
     */
    protected function filterOptions(array $options)
    {
        return array_replace_recursive(
            $this->options,
            $this->arrayIntersectKeyRecursive($options, $this->options)
        );
    }

    /**
     * Get value of the option name
     *
     * @param type $name Option name
     * @return type Option value
     */
    protected function getOption($name)
    {
        if (!array_key_exists($name, $this->options)) {
            throw new Exception('Invalid option.', 1);
        }

        return $this->options[$name];
    }

    /**
     * Load the items from session
     */
    protected function load()
    {
        $items = $this->session->get($this->getOption('name'));

        if (is_array($items)) {
            foreach ($items as $item) {
                $this->add(new Item(
                    $this->_array($item, 'id'),
                    $this->_array($item, 'name'),
                    $this->_array($item, 'price'),
                    $this->_array($item, 'qty'),
                    $this->_array($item, 'fields', array(), 'array'),
                    $this->_array($item, 'discount'),
                    $this->_array($item, 'coupon')
                ));
            }
        }
    }
}
