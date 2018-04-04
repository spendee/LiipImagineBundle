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

use Liip\ImagineBundle\Log\Formatter\SpfFormat;

/**
 * @covers \Liip\ImagineBundle\Log\Formatter\AbstractFormat
 * @covers \Liip\ImagineBundle\Log\Formatter\SpfFormat
 */
class SpfFormatTest extends AbstractFormatTestCase
{
    /**
     * @return \Iterator
     */
    public static function provideFormatData(): \Iterator
    {
        foreach (parent::provideFormatData() as $data) {
            yield $data;
        }

        yield ['[imagine-bundle] [baz-qux] a message with 2 replacements', 'a message with %d %s', [2, 'replacements'], 'baz-qux'];
        yield ['[imagine-bundle] a message with a complex replacement', 'a message with a %s replacement', [new class() {
            public function __toString()
            {
                return 'complex';
            }
        }]];
    }

    /**
     * @return string
     */
    protected static function getFormatFqcn(): string
    {
        return SpfFormat::class;
    }
}
