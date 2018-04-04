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

use Liip\ImagineBundle\Log\Formatter\PsrFormat;

/**
 * @covers \Liip\ImagineBundle\Log\Formatter\AbstractFormat
 * @covers \Liip\ImagineBundle\Log\Formatter\PsrFormat
 */
class PsrFormatTest extends AbstractFormatTestCase
{
    /**
     * @return \Iterator
     */
    public static function provideFormatData(): \Iterator
    {
        foreach (parent::provideFormatData() as $data) {
            yield $data;
        }

        yield ['[imagine-bundle] [baz-qux] a message with 2 replacements', 'a message with {count} {type}', ['count' => 2, 'type' => 'replacements'], 'baz-qux'];
        yield ['[imagine-bundle] a message with a complex replacement', 'a message with a {adjective} replacement', ['adjective' => new class() {
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
        return PsrFormat::class;
    }
}
