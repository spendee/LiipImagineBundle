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

    /**
     * Resets the lock state (including logger, store, and factory)
     */
    public static function reset(): void
    {
        self::$logger = null;
        self::$store = null;
        self::$factory = null;
    }

    /**
     * @param LoggerInterface|null $logger
     */
    public static function setLogger(LoggerInterface $logger = null): void
    {
        self::$factory = null;
        self::$logger = $logger;
    }

    /**
     * @return LoggerInterface
     */
    public static function getLogger(): LoggerInterface
    {
        return self::$logger = self::$logger ?: new NullLogger();
    }

    /**
     * @param StoreInterface|null $store
     */
    public static function setStore(StoreInterface $store = null): void
    {
        self::$factory = null;
        self::$store = $store;
    }

    /**
     * @return StoreInterface
     */
    public static function getStore(): StoreInterface
    {
        return self::$store = self::$store ?: new SemaphoreStore();
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
     *
     * @return null|Lock
     */
    public static function create($context): ?Lock
    {
        return self::getFactory()->createLock(self::stringifyContext($context));
    }

    /**
     * @param mixed $context
     *
     * @return null|Lock
     */
    public static function acquire($context): ?Lock
    {
        $lock = self::create($context);

        return $lock->acquire(false) ? $lock : null;
    }

    /**
     * @param mixed $context
     *
     * @return Lock
     */
    public static function blocking($context): Lock
    {
        ($lock = self::create($context))->acquire(true);

        return $lock;
    }

    /**
     * @param mixed $context
     *
     * @return string
     */
    private static function stringifyContext($context): string
    {
        return is_object($context) ? self::stringifyObjectContext($context) : (string) $context;
    }

    /**
     * @param object $object
     *
     * @return string
     */
    private static function stringifyObjectContext($object): string
    {
        if (method_exists($object, '__toString') && !empty($string = $object->__toString())) {
            return $string;
        }

        return sprintf('[%s]="%s"', get_class($object), $string ?? spl_object_hash($object));
    }
}
