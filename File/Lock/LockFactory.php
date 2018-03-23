<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\File\Lock;

use Liip\ImagineBundle\File\FileInterface;
use Liip\ImagineBundle\File\FileReferenceTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Lock;
use Symfony\Component\Lock\Store\SemaphoreStore;
use Symfony\Component\Lock\StoreInterface;

/**
 * @internal
 *
 * @author Rob Frawley 2nd <rmf@src.run>
 */
final class LockFactory
{
    private static $logger;

    /**
     * @var StoreInterface|null
     */
    private static $store;

    /**
     * @var Factory|null
     */
    private static $factory;

    public static function reset(): void
    {
        self::setLogger();
        self::setStore();
        self::setFactory();
    }

    /**
     * @param LoggerInterface|null $logger
     */
    public static function setLogger(LoggerInterface $logger = null): void
    {
        self::$logger = $logger;
    }

    /**
     * @return LoggerInterface
     */
    public static function getLogger(): LoggerInterface
    {
        if (null === self::$logger) {
            self::$logger = new NullLogger();
        }

        return self::$logger;
    }

    /**
     * @param StoreInterface|null $factoryStore
     */
    public static function setStore(StoreInterface $factoryStore = null): void
    {
        self::$store = $factoryStore;
    }

    /**
     * @return StoreInterface
     */
    public static function getStore(): StoreInterface
    {
        if (null === self::$store) {
            self::$store = new SemaphoreStore();
        }

        return self::$store;
    }

    /**
     * @param Factory|null $factory
     */
    public static function setFactory(Factory $factory = null): void
    {
        self::$factory = $factory;
    }

    /**
     * @return Factory
     */
    public static function getFactory(): Factory
    {
        if (null === self::$factory) {
            self::$factory = new Factory(self::getStore());
            self::$factory->setLogger(self::getLogger());
        }

        return self::$factory;
    }

    /**
     * @param mixed $context
     * @param bool  $acquire
     * @param bool  $blocking
     *
     * @return null|Lock
     */
    public static function createLock($context, bool $acquire = false, bool $blocking = false): ?Lock
    {
        $lock = self::getFactory()->createLock(self::normalizeForContext($context));

        if (true === $acquire && false === $lock->acquire($blocking)) {
            return null;
        }

        return $lock;
    }

    /**
     * @param mixed $context
     *
     * @return null|Lock
     */
    public static function createAcquired($context): ?Lock
    {
        return self::createLock($context, true);
    }

    /**
     * @param mixed $context
     *
     * @return Lock
     */
    public static function createBlocked($context): Lock
    {
        return self::createLock($context, true, true);
    }

    /**
     * @param mixed $context
     *
     * @return string
     */
    private static function normalizeForContext($context): string
    {
        if (method_exists($context, '__toString') && !empty($string = $context->__toString())) {
            return sprintf('[%s]%s', get_class($context), $string);
        }

        if (is_object($context)) {
            return sprintf('[%s]%s', get_class($context), spl_object_hash($context));
        }

        return (string) $context;
    }
}
