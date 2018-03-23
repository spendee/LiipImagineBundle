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

use Liip\ImagineBundle\File\Metadata\ContentTypeMetadata;
use Liip\ImagineBundle\File\Metadata\ExtensionMetadata;
use Liip\ImagineBundle\File\Metadata\LocationMetadata;
use Liip\ImagineBundle\File\Metadata\Metadata;
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
     * @param LocationMetadata    $l
     * @param ContentTypeMetadata $c
     * @param ExtensionMetadata   $e
     */
    public function testMetadata(LocationMetadata $l, ContentTypeMetadata $c, ExtensionMetadata $e)
    {
        $meta = Metadata::create($l, $c, $e);

        $this->assertTrue($meta->isValid());

        $this->assertTrue($meta->hasLocation());
        $this->assertSame($l, $meta->location());
        $this->assertTrue($meta->hasContentType());
        $this->assertSame($c, $meta->contentType());
        $this->assertTrue($meta->hasExtension());
        $this->assertSame($e, $meta->extension());

        $this->assertStringMatchesFormat('(%s) [%s]: %s', $meta->__toString());
    }

    /**
     * @return \Iterator
     */
    public static function fixtureMetadata(): \Iterator
    {
        $iterator = new \MultipleIterator(
            \MultipleIterator::MIT_KEYS_ASSOC | \MultipleIterator::MIT_NEED_ALL
        );
        $iterator->attachIterator(LocationMetadataTest::fetchFixtureData(), 'l');
        $iterator->attachIterator(ContentTypeMetadataTest::fetchFixtureData(), 'c');
        $iterator->attachIterator(ExtensionMetadataTest::fetchFixtureData(), 'e');

        foreach ($iterator as $item) {
            yield [
                LocationMetadata::create($item['l'][0]),
                ContentTypeMetadata::create($item['c'][0]),
                ExtensionMetadata::create($item['e'][0]),
            ];
        }
    }
}
