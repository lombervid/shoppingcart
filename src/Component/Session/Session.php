<?php
namespace Lombervid\ShoppingCart\Component\Session;

use Lombervid\ShoppingCart\Component\Session\Storage\SessionStorageInterface;
use Lombervid\ShoppingCart\Component\Session\Storage\NativeSessionStorage;

class Session
{
	protected $storage;

	public function __construct(SessionStorageInterface $storage = null)
	{
		$this->storage = $storage ?: new NativeSessionStorage();
		$this->storage->start();
	}

	public function set($name, $value)
	{
		$this->storage->set($name, $value);
	}

	public function get($name)
	{
		return $this->storage->get($name);
	}

	public function remove($name)
	{
		$this->storage->remove($name);
	}

	public function clear()
	{
		$this->storage->clear();
	}
}
