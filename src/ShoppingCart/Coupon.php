<?php
namespace ShoppingCart;

class Coupon
{
	private $code;
	private $price;
	private $order;
	private $items;

	public function __construct(
		$code,
		$price,
		$inCart = 1,
		array $items = array()
	) {
		$this->code   = $code;
		$this->price  = $price;
		$this->inCart = $inCart;
		$this->items  = $items;
	}

	public function set(
		$code,
		$price,
		$inCart = 1,
		array $items = array()
	) {
		$this->code   = $code;
		$this->price  = $price;
		$this->inCart = $inCart;
		$this->items  = $items;
	}

	public function inCart()
	{
		return $this->inCart;
	}
}