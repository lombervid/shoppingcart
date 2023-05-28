<?php

declare(strict_types=1);

namespace Lombervid\ShoppingCart\Component\Storage;

class NativeSessionStorage implements StorageInterface
{
    public function __construct()
    {
        $this->start();
    }

    public function start(): void
    {
        if (!$this->isStarted()) {
            if (!session_start()) {
                throw new \RuntimeException('Failed to start the session');
            }
        }
    }

    public function isStarted(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    public function set(string $name, mixed $value): void
    {
        $_SESSION[$name] = $value;
    }

    public function get(string $name, mixed $default = ''): mixed
    {
        return $_SESSION[$name] ?? $default;
    }

    public function remove(string $name): void
    {
        if (isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
        }
    }

    public function clear(): void
    {
        unset($_SESSION);
    }
}
