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

/**
 * @internal
 *
 * @author Rob Frawley 2nd <rmf@src.run>
 */
final class LockInvokable
{
    /**
     * @param mixed    $context
     * @param \Closure $action
     *
     * @return mixed
     */
    public static function blocking($context, \Closure $action)
    {
        $lock = LockFactory::blocking($context);

        try {
            return $action();
        } finally {
            $lock->release();
        }
    }
}
