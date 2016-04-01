<?php
namespace Component\Session;

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

	public static function get($name)
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