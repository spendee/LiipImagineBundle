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

use Liip\ImagineBundle\File\Metadata\ExtensionMetadata;
use Liip\ImagineBundle\Tests\Fixtures\Data\DataLoader;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\File\Metadata\ExtensionMetadata
 */
class ExtensionMetadataTest extends TestCase
{
    /**
     * @return \Iterator
     */
    public function provideExtensionsData(): \Iterator
    {
        return self::fetchFixtureData();
    }

    /**
     * @dataProvider provideExtensionsData
     *
     * @param string $provided
     */
    public function testExtensions(string $provided)
    {
        $meta = ExtensionMetadata::create($provided);

        $this->assertTrue($meta->hasExtension());
        $this->assertSame($provided, $meta->getExtension());
        $this->assertSame($provided, $meta->__toString());
        $this->assertTrue($meta->isValid());
        $this->assertTrue($meta->isMatch($provided));
        $this->assertFalse($meta->isMatch('foo-bar-baz'));
    }

    /**
     * @return \Iterator
     */
    public static function fetchFixtureData(): \Iterator
    {
        return (new DataLoader())(__CLASS__, 20);
    }
}
