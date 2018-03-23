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

/**
 * @internal
 */
final class Interpreter
{
    /**
     * @var string
     */
    private static $defaultResponse = 'An undefined error occured.';

    /**
     * @param string $defaultResponse
     */
    public static function setDefaultResponse(string $defaultResponse): void
    {
        self::$defaultResponse = $defaultResponse;
    }

    /**
     * @param string $index
     *
     * @return string|null
     */
    public static function lastErrorMessage(string $index = 'message')
    {
        return error_get_last()[$index] ?? self::$defaultResponse;
    }
}
