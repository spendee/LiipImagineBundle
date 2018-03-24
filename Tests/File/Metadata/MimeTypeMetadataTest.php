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

use Liip\ImagineBundle\Exception\InvalidArgumentException;
use Liip\ImagineBundle\File\Metadata\MimeTypeMetadata;
use Liip\ImagineBundle\Tests\Fixtures\Data\DataLoader;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\File\Metadata\MimeTypeMetadata
 */
class MimeTypeMetadataTest extends TestCase
{
    /**
     * @return \Iterator|string[]
     */
    public static function provideContentTypeData(): \Iterator
    {
        foreach (self::fetchFixtureData() as $data) {
            yield $data;
        }
    }

    /**
     * @dataProvider provideContentTypeData
     *
     * @param string      $provided
     * @param string      $type
     * @param string      $subType
     * @param string|null $prefix
     * @param string|null $suffix
     * @param string|null $prefixDeliminator
     */
    public function testContentTypes(string $provided, string $type, string $subType, string $prefix = null, string $suffix = null, string $prefixDeliminator = null)
    {
        $this->assertTrue(MimeTypeMetadata::isValidMimeType($provided));

        $meta = MimeTypeMetadata::create($provided);

        $this->assertTrue($meta->isValid());
        $this->assertSame($provided, $meta->__toString());

        $this->assertTrue($meta->hasType());
        $this->assertSame($type, $meta->getType());
        $this->assertTrue($meta->isType($type));
        $this->assertFalse($meta->isType('foobar'));

        $this->assertTrue($meta->hasSubType());
        $this->assertSame($subType, $meta->getSubType());
        $this->assertTrue($meta->isSubType($subType));
        $this->assertFalse($meta->isSubType('foobar'));

        if (null !== $prefix) {
            $this->assertTrue($meta->hasPrefix());
            $this->assertSame($prefix, $meta->getPrefix());
            $this->assertTrue($meta->isPrefix($prefix));
            $this->assertFalse($meta->isPrefix('foobar'));
            $this->assertTrue($meta->hasDeliminator());
            $this->assertSame($prefixDeliminator, $meta->getDeliminator());
            $this->assertTrue($meta->isDeliminator($prefixDeliminator));
            $this->assertFalse($meta->isDeliminator('foobar'));
        } else {
            $this->assertFalse($meta->hasPrefix());
            $this->assertNull($meta->getPrefix());
            $this->assertTrue($meta->isPrefix(null));
            $this->assertFalse($meta->isPrefix('foobar'));
            $this->assertFalse($meta->hasDeliminator());
            $this->assertNull($meta->getDeliminator());
            $this->assertTrue($meta->isDeliminator(null));
            $this->assertFalse($meta->isDeliminator('foobar'));
        }

        if (null !== $suffix) {
            $this->assertTrue($meta->hasSuffix());
            $this->assertSame($suffix, $meta->getSuffix());
            $this->assertTrue($meta->isSuffix($suffix));
            $this->assertFalse($meta->isSuffix('foobar'));
        } else {
            $this->assertFalse($meta->hasSuffix());
            $this->assertNull($meta->getSuffix());
            $this->assertTrue($meta->isSuffix(null));
            $this->assertFalse($meta->isSuffix('foobar'));
        }
    }

    /**
     * @return \Iterator|string[]
     */
    public static function provideInvalidContentTypeData(): \Iterator
    {
        yield [null];
        yield ['foobar'];
    }

    /**
     * @dataProvider provideInvalidContentTypeData
     *
     * @param string|null $provided
     */
    public function testInvalidContentTypes(string $provided = null)
    {
        $this->assertNull(MimeTypeMetadata::getMimeTypeParts($provided));
        $this->assertFalse(MimeTypeMetadata::isValidMimeType($provided));

        $meta = MimeTypeMetadata::create($provided);

        $this->assertFalse($meta->hasType());
        $this->assertFalse($meta->hasSubType());
        $this->assertFalse($meta->hasPrefix());
        $this->assertFalse($meta->hasSuffix());
    }

    /**
     * @return \Iterator|string[]|bool[]
     */
    public static function provideVendorTypeData(): \Iterator
    {
        yield ['foo/bar'];
        yield ['foo/bar+baz'];
        yield ['foo/x.bar', 'x'];
        yield ['foo/x.bar+baz', 'x'];
        yield ['foo/vnd.bar', 'vnd'];
        yield ['foo/vnd.bar+baz', 'vnd'];
        yield ['foo/prs.bar', 'prs'];
        yield ['foo/prs.bar+baz', 'prs'];
    }

    /**
     * @dataProvider provideVendorTypeData
     *
     * @param string      $provided
     * @param string|null $vendor
     */
    public function testVendorTypes(string $provided, string $vendor = null)
    {
        $mime = MimeTypeMetadata::create($provided);

        $this->assertSame($vendor, $mime->getPrefix());

        switch ($vendor) {
            case 'x':
                $this->assertFalse($mime->isPrefixStandard());
                $this->assertTrue($mime->isPrefixUnregistered());
                $this->assertFalse($mime->isPrefixVendor());
                $this->assertFalse($mime->isPrefixPersonal());
                break;

            case 'vnd':
                $this->assertFalse($mime->isPrefixStandard());
                $this->assertFalse($mime->isPrefixUnregistered());
                $this->assertTrue($mime->isPrefixVendor());
                $this->assertFalse($mime->isPrefixPersonal());
                break;

            case 'prs':
                $this->assertFalse($mime->isPrefixStandard());
                $this->assertFalse($mime->isPrefixUnregistered());
                $this->assertFalse($mime->isPrefixVendor());
                $this->assertTrue($mime->isPrefixPersonal());
                break;

            default:
                $this->assertTrue($mime->isPrefixStandard());
                $this->assertFalse($mime->isPrefixUnregistered());
                $this->assertFalse($mime->isPrefixVendor());
                $this->assertFalse($mime->isPrefixPersonal());
        }
    }

    public static function provideEquivalenceData(): \Iterator
    {
        return self::fetchFixtureData();
    }

    /**
     * @dataProvider provideEquivalenceData
     *
     * @param string|null $provided
     * @param string      $type
     * @param string      $subType
     * @param string|null $prefix
     * @param string|null $suffix
     */
    public function testIsEquivalent(string $provided = null, string $type, string $subType, string $prefix = null, string $suffix = null)
    {
        $meta = MimeTypeMetadata::create($provided);

        $this->assertTrue($meta->isMatch($type, $subType, $prefix, $suffix));

        $this->assertTrue($meta->isMatch($type));
        $this->assertTrue($meta->isMatch($type, $subType));
        $this->assertTrue($meta->isMatch($type, $subType, $prefix));
        $this->assertTrue($meta->isMatch($type, $subType, $prefix, $suffix));
        $this->assertTrue($meta->isMatch($type, null, $prefix));
        $this->assertTrue($meta->isMatch($type, null, null, $suffix));
    }

    public function testToStringOnNullFileType()
    {
        $this->assertEmpty(MimeTypeMetadata::create()->__toString());
    }

    /**
     * @return \Iterator
     */
    public function provideThrowsOnInvalidMimeTypesData(): \Iterator
    {
        yield ['foo$'];
        yield ['foo', 'bar$'];
        yield ['foo', 'bar', 'baz'];
        yield ['foo', 'bar', 'x', 'baz$'];
        yield ['foo', 'bar', 'x', 'baz', 'qux'];
    }

    /**
     * @dataProvider provideThrowsOnInvalidMimeTypesData
     *
     * @param string      $type
     * @param string|null $subType
     * @param string|null $prefix
     * @param string|null $suffix
     * @param string|null $deliminator
     */
    public function testThrowsOnInvalidMimeTypes(string $type, string $subType = null, string $prefix = null, string $suffix = null, string $deliminator = null)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('{Invalid mime type (prefix|deliminator|character\(s\)) "[^"]+" provided (in "[^"]+" )?\(accepted values are ".+"\)\.}');

        new MimeTypeMetadata($type, $subType, $prefix, $suffix, $deliminator);
    }

    /**
     * @return \Iterator
     */
    public static function fetchFixtureData(): \Iterator
    {
        return (new DataLoader())(__CLASS__, 20);
    }
}
