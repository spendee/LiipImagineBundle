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

use Liip\ImagineBundle\File\Metadata\LocationMetadata;
use Liip\ImagineBundle\Tests\Fixtures\Data\DataLoader;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\File\Metadata\LocationMetadata
 */
class LocationMetadataTest extends TestCase
{
    /**
     * @return \Iterator
     */
    public static function provideLocationsData(): \Iterator
    {
        return self::fetchFixtureData();
    }

    /**
     * @dataProvider provideLocationsData
     *
     * @param string $provided
     */
    public function testLocations(string $provided)
    {
        $meta = LocationMetadata::create($provided);

        $this->assertSame($provided, $meta->getPathname());
        $this->assertSame($provided, $meta->__toString());
        $this->assertTrue($meta->isLocation($provided));
        $this->assertTrue($meta->isValid());
    }

    /**
     * @return \Iterator
     */
    public static function fetchFixtureData(): \Iterator
    {
        return (new DataLoader())(__CLASS__);
    }
}
