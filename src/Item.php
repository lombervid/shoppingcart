<?php
namespace Lombervid\ShoppingCart;

class Item
{
    private $id;
    private $name;
    private $price;
    private $qty;
    private $fields;
    private $discount;

    public function __construct(
        $id,
        $name,
        $price,
        $qty = 1,
        array $fields = array(),
        $discount = 0
    ) {
        $this->id       = strval($id);
        $this->name     = strval($name);
        $this->price    = floatval($price);
        $this->qty      = intval($qty);
        $this->fields   = $fields;
        $this->discount = floatval($discount);
    }

    public function add($qty)
    {
        $this->qty += intval($qty);
    }

    public function update($qty)
    {
        $this->qty = intval($qty);
    }

    public function get($name)
    {
        if ('fields' != $name && property_exists($this, $name)) {
            return $this->{$name};
        } elseif (array_key_exists($name, $this->fields)) {
            return $this->fields[$name];
        }

        return '';
    }

    public function hasDiscount()
    {
        return (0 < $this->discount);
    }

    public function price()
    {
        return $this->price - $this->discount();
    }

    public function total()
    {
        return $this->price() * $this->qty;
    }

    public function toArray()
    {
        return array(
            'id'       => $this->id,
            'name'     => $this->name,
            'price'    => $this->price,
            'qty'      => $this->qty,
            'discount' => $this->discount,
            'fields'   => $this->fields,
        );
    }

    private function discount()
    {
        return $this->discount;
    }
}
