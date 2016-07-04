<?php
namespace ShoppingCart;

class Item
{
	private $id;
	private $name;
	private $price;
	private $amount;
	private $fields;
	private $discount;
	private $coupon;

	public function __construct(
		$id,
		$name,
		$price,
		$amount = 1,
		array $fields = array(),
		$discount = 0
	) {
		$this->id       = $id;
		$this->name     = $name;
		$this->price    = floatval($price);
		$this->amount   = intval($amount);
		$this->fields   = $fields;
		$this->discount = floatval($discount);
		$this->coupon   = 0;
	}

	public function add($amount)
	{
		$this->amount += intval($amount);
	}

	public function update($amount)
	{
		$this->amount = intval($amount);
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
		return (0 < $this->discount || 0 < $this->coupon);
	}

	public function price()
	{
		return $this->price - $this->discount();
	}

	public function total()
	{
		return $this->price() * $this->amount;
	}

    public function toArray()
    {
        $item['id']       = $this->id;
        $item['name']     = $this->name;
        $item['price']    = $this->price;
        $item['amount']   = $this->amount;
        $item['discount'] = $this->discount;
        $item['coupon']   = $this->coupon;
        $item['fields']   = $this->fields;

        return $item;
    }

	private function discount()
	{
		if ($this->discount < $this->coupon) {
			return $this->coupon;
		}

		return $this->discount;
	}
}
