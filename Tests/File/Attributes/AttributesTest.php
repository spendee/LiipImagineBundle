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

use Liip\ImagineBundle\File\Attributes\Attributes;
use Liip\ImagineBundle\File\Attributes\ContentTypeAttribute;
use Liip\ImagineBundle\File\Attributes\ExtensionAttribute;
use Liip\ImagineBundle\Tests\Fixtures\Data\DataLoader;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\File\Attributes\Attributes
 */
class AttributesTest extends TestCase
{
    /**
     * @return \Iterator
     */
    public static function provideTestConstructionAndAccessorsData(): \Iterator
    {
        return self::yieldFixtures();
    }

    /**
     * @dataProvider provideTestConstructionAndAccessorsData
     *
     * @param string $contentType
     * @param string $extension
     */
    public function testConstructionAndAccessors(string $contentType, string $extension)
    {
        $bag = new Attributes(ContentTypeAttribute::create($contentType), ExtensionAttribute::create($extension));

        $this->assertTrue($bag->isValid());
        $this->assertSame($contentType, $bag->getContentType()->stringify());
        $this->assertSame($extension, $bag->getExtension()->stringify());
    }

    /**
     * @return \Iterator
     */
    public static function yieldFixtures(): \Iterator
    {
        foreach ((new DataLoader())(ContentTypeAttributeTest::class, 20) as $data) {
            yield [array_shift($data), array_pop($data)];
        }
    }
}
