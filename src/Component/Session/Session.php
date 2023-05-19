<?php

declare(strict_types=1);

namespace Lombervid\ShoppingCart\Component\Session;

use Lombervid\ShoppingCart\Component\Session\Storage\SessionStorageInterface;
use Lombervid\ShoppingCart\Component\Session\Storage\NativeSessionStorage;

class Session
{
    public function __construct(protected SessionStorageInterface $storage = new NativeSessionStorage())
    {
        $this->storage->start();
    }

    public function set(string $name, mixed $value): void
    {
        $this->storage->set($name, $value);
    }

    public function get(string $name): string
    {
        return $this->storage->get($name);
    }

    public function remove(string $name): void
    {
        $this->storage->remove($name);
    }

    public function clear(): void
    {
        $this->storage->clear();
    }
}
