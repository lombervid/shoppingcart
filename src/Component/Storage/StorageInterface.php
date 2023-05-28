<?php

namespace Lombervid\ShoppingCart\Component\Storage;

interface StorageInterface
{
    public function set(string $name, mixed $value): void;
    public function get(string $name, mixed $default = null): mixed;
    public function remove(string $name): void;
    public function clear(): void;
}
