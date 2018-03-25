<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\File\Attributes;

use Liip\ImagineBundle\File\Attributes\ExtensionAttribute;
use Liip\ImagineBundle\Tests\Fixtures\Data\DataLoader;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\File\Attributes\AttributeTrait
 * @covers \Liip\ImagineBundle\File\Attributes\ExtensionAttribute
 */
class ExtensionAttributeTest extends TestCase
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
        $meta = ExtensionAttribute::create($provided);

        $this->assertTrue($meta->hasName());
        $this->assertSame($provided, $meta->getName());
        $this->assertSame($provided, $meta->stringify());
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
        return (new DataLoader())(__CLASS__, 30);
    }
}
