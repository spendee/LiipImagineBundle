<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\File\Metadata;

use Liip\ImagineBundle\File\Metadata\MimeTypeMetadata;
use Liip\ImagineBundle\File\Metadata\ExtensionMetadata;
use Liip\ImagineBundle\File\Metadata\LocationMetadata;
use Liip\ImagineBundle\File\Metadata\Metadata;
use Liip\ImagineBundle\Tests\Fixtures\Data\DataLoader;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\File\Metadata\Metadata
 */
class MetadataTest extends TestCase
{
    /**
     * @return \Iterator
     */
    public static function provideMetadataData(): \Iterator
    {
        return self::fixtureMetadata();
    }

    /**
     * @dataProvider provideMetadataData
     *
     * @param string $c
     * @param string $e
     */
    public function testMetadata(string $c, string $e)
    {
        $meta = Metadata::create($c, $e);

        $this->assertTrue($meta->isValid());

        $this->assertTrue($meta->hasContentType());
        $this->assertSame($c, $meta->getContentType()->__toString());
        $this->assertTrue($meta->hasExtension());
        $this->assertSame($e, $meta->getExtension()->__toString());

        $this->assertStringMatchesFormat('%s => "%s/%s"', $meta->__toString());
    }

    /**
     * @return \Iterator
     */
    public static function fixtureMetadata(): \Iterator
    {
        foreach ((new DataLoader())(MimeTypeMetadataTest::class, 10) as $d) {
            yield [$d[0], $d[count($d) - 1]];
        }
    }
}
