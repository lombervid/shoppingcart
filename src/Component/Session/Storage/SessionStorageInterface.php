<?php

namespace Lombervid\ShoppingCart\Component\Session\Storage;

interface SessionStorageInterface
{
    public function start(): void;
    public function isStarted(): bool;
    public function set(string $name, mixed $value): void;
    public function get(string $name): string;
    public function remove(string $name): void;
    public function clear(): void;
}
