<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Log\Formatter;

use Liip\ImagineBundle\Tests\AbstractTest;

abstract class AbstractFormatTestCase extends AbstractTest
{
    /**
     * @return \Iterator
     */
    public static function provideFormatData(): \Iterator
    {
        yield ['[imagine-bundle] a message with no replacements', 'a message with no replacements', []];
        yield ['[imagine-bundle] [foo-bar] a message with no replacements', 'a message with no replacements', [], 'foo-bar'];
        yield ['[imagine-bundle] [foo-bar] a message with no replacements', '[imagine-bundle] [foo-bar] [foo-bar] a message with no replacements', [], 'foo-bar'];
        yield ['[imagine-bundle] [bar-baz] a message with context', '[bar-baz] a message with context', []];
        yield ['[imagine-bundle] [bar-baz] [baz-qux] a message with context', '[bar-baz] a message with context', [], 'baz-qux'];
        yield ['[imagine-bundle] [bar-baz] [baz-qux] a message with context', '[bar-baz] [baz-qux] a message with context', []];
        yield ['[imagine-bundle]   > an indented line', 'an indented line', [], null, 1];
    }

    /**
     * @dataProvider provideFormatData
     *
     * @param string      $expected
     * @param string      $format
     * @param array       $replacements
     * @param string|null $context
     * @param int         $indent
     */
    public function testFormat(string $expected, string $format, array $replacements, string $context = null, int $indent = 0): void
    {
        $this->assertSame($expected, static::invokeFormatMethod('format', $format, $replacements, $context, $indent));
    }

    /**
     * @return \Iterator
     */
    public static function provideIndentData(): \Iterator
    {
        yield ['an indented line', 'an indented line', 0];
        yield ['  > an indented line', 'an indented line', 1];
        yield ['    > an indented line', 'an indented line', 2];
        yield ['      > an indented line', 'an indented line', 3];
        yield ['        > an indented line', 'an indented line', 4];
        yield ['          > an indented line', 'an indented line', 5];
    }

    /**
     * @dataProvider provideIndentData
     *
     * @param string      $expected
     * @param string      $message
     * @param int         $indent
     */
    public function testIndent(string $expected, string $message, int $indent = 0): void
    {
        $this->assertSame($expected, static::invokeFormatMethod('indent', $message, $indent));
    }

    /**
     * @param string $method
     * @param mixed  ...$arguments
     *
     * @return string
     */
    private static function invokeFormatMethod(string $method, ...$arguments): string
    {
        $class = static::getFormatFqcn();
        return (new $class())->{$method}(...$arguments);
    }

    /**
     * @return string
     */
    abstract protected static function getFormatFqcn(): string;
}
