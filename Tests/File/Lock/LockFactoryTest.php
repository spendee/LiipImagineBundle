<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\File\Lock;

use Liip\ImagineBundle\File\Lock\LockFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Lock;
use Symfony\Component\Lock\Store\SemaphoreStore;
use Symfony\Component\Lock\StoreInterface;

/**
 * @covers \Liip\ImagineBundle\File\Lock\LockFactory
 */
class LockFactoryTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        LockFactory::reset();
    }

    public function testCreateLock()
    {
        for ($i = 0; $i < 10; $i++) {
            $lock = LockFactory::create('foo');
            $this->assertInstanceOf(Lock::class, $lock);
            $this->assertFalse($lock->isAcquired());
            $lock->release();
        }
    }

    public function testAcquireLock()
    {
        for ($i = 0; $i < 10; $i++) {
            $lock = LockFactory::acquire('foo');
            $this->assertInstanceOf(Lock::class, $lock);
            $this->assertTrue($lock->isAcquired());
            $lock->release();

            $lockOne = LockFactory::acquire('foo');
            $this->assertInstanceOf(Lock::class, $lockOne);
            $this->assertTrue($lockOne->isAcquired());

            $lockTwo = LockFactory::acquire('foo');
            $this->assertNull($lockTwo);
            $this->assertTrue($lockOne->isAcquired());
            $lockOne->release();
        }
    }

    public function testBlockingLock()
    {
        for ($i = 0; $i < 10; ++$i) {
            $lockOne = LockFactory::blocking('foo');
            $this->assertInstanceOf(Lock::class, $lockOne);
            $this->assertTrue($lockOne->isAcquired());

            $lockTwo = LockFactory::acquire('foo');
            $this->assertNull($lockTwo);
            $this->assertTrue($lockOne->isAcquired());
            $lockOne->release();
        }
    }

    public function testSettersAndAccessors()
    {
        $l = new NullLogger();
        $s = new SemaphoreStore();

        $this->assertNotSame($l, LockFactory::getLogger());
        $this->assertInstanceOf(LoggerInterface::class, LockFactory::getLogger());
        $this->assertNotSame($s, LockFactory::getStore());
        $this->assertInstanceOf(StoreInterface::class, LockFactory::getStore());
        $this->assertInstanceOf(Factory::class, $f = LockFactory::getFactory());

        LockFactory::setLogger($l);
        $this->assertSame($l, LockFactory::getLogger());
        $this->assertNotSame($f, $f = LockFactory::getFactory());
        $this->assertInstanceOf(LoggerInterface::class, LockFactory::getLogger());
        $this->assertInstanceOf(StoreInterface::class, LockFactory::getStore());
        $this->assertInstanceOf(Factory::class, LockFactory::getFactory());

        LockFactory::setStore($s);
        $this->assertSame($s, LockFactory::getStore());
        $this->assertNotSame($f, $f = LockFactory::getFactory());
        $this->assertInstanceOf(LoggerInterface::class, LockFactory::getLogger());
        $this->assertInstanceOf(StoreInterface::class, LockFactory::getStore());
        $this->assertInstanceOf(Factory::class, LockFactory::getFactory());
    }

    public function testObjectContexts()
    {
        $object = new class() {};

        $lockOne = LockFactory::acquire($object);
        $lockTwo = LockFactory::acquire($object);
        $this->assertTrue($lockOne->isAcquired());
        $this->assertNull($lockTwo);
        $lockOne->release();

        $object = new class() {
            public static $i = 0;
            public function __toString(): string
            {
                return sprintf('class-string-%d', self::$i++);
            }
        };

        $lockOne = LockFactory::acquire($object);
        $lockTwo = LockFactory::acquire($object);
        $this->assertTrue($lockOne->isAcquired());
        $this->assertTrue($lockTwo->isAcquired());
        $lockOne->release();
        $lockTwo->release();
    }
}
