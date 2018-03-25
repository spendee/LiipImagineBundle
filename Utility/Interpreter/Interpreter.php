<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Utility\Interpreter;

use Liip\ImagineBundle\Utility\Interpreter\State\AbstractErrorState;
use Liip\ImagineBundle\Utility\Interpreter\State\ErrorStateDefault;
use Liip\ImagineBundle\Utility\Interpreter\State\ErrorStateDefined;

/**
 * @author Rob Frawley 2nd <rmf@src.run>
 */
final class Interpreter
{
    /**
     * @param bool $clear
     *
     * @return AbstractErrorState|ErrorStateDefault|ErrorStateDefined
     */
    public static function error(bool $clear = true)
    {
        try {
            return AbstractErrorState::create(error_get_last());
        } finally {
            if (true === $clear) {
                self::errorClear();
            }
        }
    }

    /**
     * Clear the last error
     */
    public static function errorClear(): void
    {
        error_clear_last();
    }
}
