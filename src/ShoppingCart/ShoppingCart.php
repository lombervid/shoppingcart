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
        'shipping' => array(
            'amount' => 0,
            'free'   => 0,
        ),
        'tax'      => 0,
        'optionas' => 'value',
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
     * @param ShoppingCart\Item Item to be added.
     */
    public function add(Item $item)
    {
        $itemID = md5($item->get('id'));

        if (array_key_exists($itemID, $this->items)) {
            $this->items[$itemID]->add($item->get('amount'));
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

        if (array_key_exists($itemID, $this->items)) {
            unset($this->items[$itemID]);
        }
    }

    public function total()
    {
        return $this->subtotal() + $this->shipping() + $this->tax();
    }

    public function subtotal()
    {
        $subtotal = 0;

        foreach ($this->items as $item) {
            $subtotal += $item->total();
        }

        return $subtotal;
    }

    public function shipping()
    {
        $shipping = $this->getOption('shipping');
        $free     = floatval($shipping['free']);

        if ($this->subtotal() > $free) {
            return 0;
        }

        return floatval($shipping['amount']);
    }

    public function tax()
    {
        $tax = floatval($this->getOption('tax'));

        if ($tax <= 0) {
            return 0;
        }

        return $this->subtotal() * $tax / 100;
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
        $this->session->set($this->getOption('name'), $this->items);
    }

    protected function filterOptions(array $options)
    {
        return array_replace_recursive(
            $this->options,
            array_intersect_key_recursive($options, $this->options)
        );
    }

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
            $this->items = $items;
        }
    }
}