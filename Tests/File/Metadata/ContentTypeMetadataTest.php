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
use Liip\ImagineBundle\Tests\Fixtures\Data\DataLoader;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\File\Metadata\ContentTypeMetadata
 */
class ContentTypeMetadataTest extends TestCase
{
    /**
     * @return \Iterator|string[]
     */
    public static function provideContentTypeData(): \Iterator
    {
        foreach (self::fetchFixtureData() as $data) {
            yield $data;
        }

        foreach (self::fetchExtendedFixtureData() as $data) {
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
        $this->assertTrue(ContentTypeMetadata::isValidContentType($provided));

        $meta = ContentTypeMetadata::create($provided);

        $this->assertTrue($meta->isValid());
        $this->assertSame($provided, $meta->__toString());

        $this->assertTrue($meta->hasType());
        $this->assertSame($type, $meta->type());
        $this->assertTrue($meta->isType($type));
        $this->assertFalse($meta->isType('foobar'));

        $this->assertTrue($meta->hasSubType());
        $this->assertSame($subType, $meta->subType());
        $this->assertTrue($meta->isSubType($subType));
        $this->assertFalse($meta->isSubType('foobar'));

        if (null !== $prefix) {
            $this->assertTrue($meta->hasPrefix());
            $this->assertSame($prefix, $meta->prefix());
            $this->assertTrue($meta->isPrefix($prefix));
            $this->assertFalse($meta->isPrefix('foobar'));
            $this->assertTrue($meta->hasPrefixDeliminator());
            $this->assertSame($prefixDeliminator, $meta->prefixDeliminator());
            $this->assertTrue($meta->isPrefixDeliminator($prefixDeliminator));
            $this->assertFalse($meta->isPrefixDeliminator('foobar'));
        } else {
            $this->assertFalse($meta->hasPrefix());
            $this->assertNull($meta->prefix());
            $this->assertTrue($meta->isPrefix(null));
            $this->assertFalse($meta->isPrefix('foobar'));
            $this->assertFalse($meta->hasPrefixDeliminator());
            $this->assertNull($meta->prefixDeliminator());
            $this->assertTrue($meta->isPrefixDeliminator(null));
            $this->assertFalse($meta->isPrefixDeliminator('foobar'));
        }

        if (null !== $suffix) {
            $this->assertTrue($meta->hasSuffix());
            $this->assertSame($suffix, $meta->suffix());
            $this->assertTrue($meta->isSuffix($suffix));
            $this->assertFalse($meta->isSuffix('foobar'));
        } else {
            $this->assertFalse($meta->hasSuffix());
            $this->assertNull($meta->suffix());
            $this->assertTrue($meta->isSuffix( null));
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
        $this->assertNull(ContentTypeMetadata::explodeContentType($provided));
        $this->assertFalse(ContentTypeMetadata::isValidContentType($provided));

        $meta = ContentTypeMetadata::create($provided);

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
        yield ['foo/x.bar', false, true];
        yield ['foo/x.bar+baz', false, true];
        yield ['foo/vnd.bar', false, false, true];
        yield ['foo/vnd.bar+baz', false, false, true];
        yield ['foo/prs.bar', false, false, false, true];
        yield ['foo/prs.bar+baz', false, false, false, true];
    }

    /**
     * @dataProvider provideVendorTypeData
     *
     * @param string $provided
     * @param bool   $isStandard
     * @param bool   $isUnregistered
     * @param bool   $isVendor
     * @param bool   $isPersonal
     */
    public function testVendorTypes(string $provided, bool $isStandard = true, bool $isUnregistered = false, bool $isVendor = false, bool $isPersonal = false)
    {
        $meta = ContentTypeMetadata::create($provided);

        $this->assertSame($isStandard, $meta->isPrefixStandard());
        $this->assertSame($isUnregistered, $meta->isPrefixUnregistered());
        $this->assertSame($isVendor, $meta->isPrefixVendor());
        $this->assertSame($isPersonal, $meta->isPrefixPersonal());
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
        $meta = ContentTypeMetadata::create($provided);

        $this->assertTrue($meta->isEquivalent($type, $subType, $prefix, $suffix));
        $this->assertTrue($meta->isSame($type, $subType, $prefix, $suffix));

        $this->assertTrue($meta->isEquivalent($type));
        $this->assertTrue($meta->isEquivalent($type, $subType));
        $this->assertTrue($meta->isEquivalent($type, $subType, $prefix));
        $this->assertTrue($meta->isEquivalent($type, $subType, $prefix, $suffix));
        $this->assertTrue($meta->isEquivalent($type, null, $prefix));
        $this->assertTrue($meta->isEquivalent($type, null, null, $suffix));

        $this->assertFalse($meta->isSame($type, null));
        $this->assertFalse($meta->isSame(null, $subType));

        if (null !== $prefix || null !== $suffix) {
            $this->assertFalse($meta->isSame($type));
            $this->assertFalse($meta->isSame($type, $subType));
        }

        if (null !== $prefix) {
            $this->assertFalse($meta->isSame($type, $subType, null, $suffix));
        }

        if (null !== $suffix) {
            $this->assertFalse($meta->isSame($type, $subType, $prefix, null));
        }

        if (null !== $prefix && null === $suffix) {
            $this->assertFalse($meta->isSame($type, $subType));
            $this->assertFalse($meta->isSame($type, $subType));
        }
    }

    public function testToStringOnNullFileType()
    {
        $this->assertEmpty(ContentTypeMetadata::create()->__toString());
    }

    /**
     * @return \Iterator
     */
    public static function fetchFixtureData(): \Iterator
    {
        return (new DataLoader())(__CLASS__, 'basic');
    }

    /**
     * @return \Iterator
     */
    public static function fetchExtendedFixtureData(): \Iterator
    {
        return (new DataLoader())(__CLASS__, 'extended');
    }
}
