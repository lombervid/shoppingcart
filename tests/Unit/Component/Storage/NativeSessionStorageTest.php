<?php

declare(strict_types=1);

namespace Lombervid\ShoppingCart\Tests\Unit\Component\Storage;

use PHPUnit\Framework\TestCase;
use Lombervid\ShoppingCart\Component\Storage\NativeSessionStorage;
use PHPUnit\Framework\Attributes\Depends;

class NativeSessionStorageTest extends TestCase
{
    public function testInitialization(): NativeSessionStorage
    {
        self::assertContains(session_status(), [PHP_SESSION_NONE, PHP_SESSION_DISABLED ]);

        $storage = new NativeSessionStorage();
        self::assertSame(PHP_SESSION_ACTIVE, session_status());

        return $storage;
    }

    #[Depends('testInitialization')]
    public function testSetValue(NativeSessionStorage $storage): NativeSessionStorage
    {
        self::assertSame([], $_SESSION);

        $storage->set('name', 'my_cart');
        self::assertSame(['name' => 'my_cart'], $_SESSION);

        $storage->set('total', 34.56);
        self::assertSame(['name' => 'my_cart', 'total' => 34.56], $_SESSION);

        $storage->set('fields', [['id' => 16], ['id' => 1256]]);
        self::assertSame([
            'name' => 'my_cart',
            'total' => 34.56,
            'fields' => [
                ['id' => 16],
                ['id' => 1256],
            ],
        ], $_SESSION);

        return $storage;
    }

    #[Depends('testSetValue')]
    public function testGetValue(NativeSessionStorage $storage): NativeSessionStorage
    {
        self::assertSame('my_cart', $storage->get('name'));
        self::assertSame(34.56, $storage->get('total'));
        self::assertSame([['id' => 16], ['id' => 1256]], $storage->get('fields'));

        return $storage;
    }

    #[Depends('testGetValue')]
    public function testRemoveValue(NativeSessionStorage $storage): NativeSessionStorage
    {
        self::assertSame([
            'name' => 'my_cart',
            'total' => 34.56,
            'fields' => [
                ['id' => 16],
                ['id' => 1256],
            ],
        ], $_SESSION);

        $storage->remove('fields');
        self::assertSame(['name' => 'my_cart', 'total' => 34.56], $_SESSION);

        return $storage;
    }

    #[Depends('testRemoveValue')]
    public function testClearSession(NativeSessionStorage $storage): void
    {
        self::assertSame(['name' => 'my_cart', 'total' => 34.56], $_SESSION);

        $storage->clear();
        self::assertSame([], $_SESSION);
    }
}
