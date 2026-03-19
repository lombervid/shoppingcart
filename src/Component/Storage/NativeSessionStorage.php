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
        if (!$this->isStarted() && !session_start()) {
            throw new \RuntimeException('Failed to start the session');
        }
    }

    public function isStarted(): bool
    {
        return session_status() === PHP_SESSION_ACTIVE;
    }

    #[\Override]
    public function set(string $name, mixed $value): void
    {
        $_SESSION[$name] = $value;
    }

    #[\Override]
    public function get(string $name, mixed $default = ''): mixed
    {
        return $_SESSION[$name] ?? $default;
    }

    #[\Override]
    public function remove(string $name): void
    {
        if (isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
        }
    }

    #[\Override]
    public function clear(): void
    {
        $_SESSION = [];
    }
}
