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
use Psr\Log\NullLogger;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Lock;
use Symfony\Component\Lock\Store\SemaphoreStore;

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
        $lock = LockFactory::createLock('foobar', false, false);
        $this->assertInstanceOf(Lock::class, $lock);
        $this->assertFalse($lock->isAcquired());
        $lock->release();

        $lock = LockFactory::createLock('foobar', true, false);
        $this->assertInstanceOf(Lock::class, $lock);
        $this->assertTrue($lock->isAcquired());
        $lock->release();

        $lock = LockFactory::createAcquired('foobar');
        $this->assertInstanceOf(Lock::class, $lock);
        $this->assertTrue($lock->isAcquired());
        $lock->release();

        $lock = LockFactory::createLock('foobar', true, true);
        $this->assertInstanceOf(Lock::class, $lock);
        $this->assertTrue($lock->isAcquired());
        $lock->release();

        $lock = LockFactory::createBlocked('foobar');
        $this->assertInstanceOf(Lock::class, $lock);
        $this->assertTrue($lock->isAcquired());
        $lock->release();

        $lock1 = LockFactory::createAcquired('foobar');
        $lock2 = LockFactory::createAcquired('foobar');
        $this->assertInstanceOf(Lock::class, $lock1);
        $this->assertNull($lock2);
        $lock1->release();
    }

    public function testSetters()
    {
        $l = new NullLogger();
        $s = new SemaphoreStore();
        $f = new Factory($s);

        $this->assertNotSame($l, LockFactory::getLogger());
        $this->assertNotSame($s, LockFactory::getStore());
        $this->assertNotSame($f, LockFactory::getFactory());

        LockFactory::setLogger($l);
        LockFactory::setStore($s);
        LockFactory::setFactory($f);

        $this->assertSame($l, LockFactory::getLogger());
        $this->assertSame($s, LockFactory::getStore());
        $this->assertSame($f, LockFactory::getFactory());
    }
}
