<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\File\Attributes\Resolver;

use Liip\ImagineBundle\Exception\File\Attributes\Resolver\InvalidFileAttributesException;
use Liip\ImagineBundle\File\FileBlob;
use Liip\ImagineBundle\File\FilePath;
use Liip\ImagineBundle\File\Attributes\Guesser\ContentTypeGuesser;
use Liip\ImagineBundle\File\Attributes\Resolver\FileAttributesResolver;
use Liip\ImagineBundle\File\Attributes\Guesser\ExtensionGuesser;
use Liip\ImagineBundle\File\Attributes\Resolver\FileAttributesApplier;
use Liip\ImagineBundle\Tests\AbstractTest;
use Liip\ImagineBundle\Tests\File\Attributes\ContentTypeAttributeTest;
use Liip\ImagineBundle\Tests\Fixtures\Data\DataLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;

/**
 * @covers \Liip\ImagineBundle\File\Attributes\Resolver\FileAttributesApplier
 */
class FileAttributesApplierTest extends AbstractTest
{
    /**
     * @return \Iterator|string[]
     */
    public static function provideResolveData(): \Iterator
    {
        foreach (self::fetchFixtureData() as $data) {
            yield $data;
        }
    }

    /**
     * @dataProvider provideResolveData
     *
     * @param string $contentType
     * @param string $extension
     */
    public function testResolve(string $contentType, string $extension)
    {
        $resolver = $this->createFileAttributesApplierInstance(
            $this->createContentTypeGuesserMock($contentType),
            $this->createExtensionGuesserMock($extension, $contentType)
        );

        $fileBlob = $resolver->apply($fb = FileBlob::create('content'));
        $filePath = $resolver->apply($fp = FilePath::create('file.ext'));

        $this->assertNotSame($fb, $fileBlob);
        $this->assertSame($contentType, (string) $fileBlob->getContentType());
        $this->assertSame($extension, (string) $fileBlob->getExtension());
        $this->assertSame('content', $fileBlob->getContents());
        $this->assertNotSame($fp, $filePath);
        $this->assertSame($contentType, (string) $filePath->getContentType());
        $this->assertSame($extension, (string) $filePath->getExtension());
        $this->assertSame('file.ext', $filePath->getFile()->getPathname());

        $fileBlob = $resolver->apply($fb = FileBlob::create('content', $contentType));
        $filePath = $resolver->apply($fp = FilePath::create('file.ext', $contentType));

        $this->assertNotSame($fb, $fileBlob);
        $this->assertSame($contentType, (string) $fileBlob->getContentType());
        $this->assertSame($extension, (string) $fileBlob->getExtension());
        $this->assertSame('content', $fileBlob->getContents());
        $this->assertNotSame($fp, $filePath);
        $this->assertSame($contentType, (string) $filePath->getContentType());
        $this->assertSame($extension, (string) $filePath->getExtension());
        $this->assertSame('file.ext', $filePath->getFile()->getPathname());

        $fileBlob = $resolver->apply($fb = FileBlob::create('content', $contentType, $extension));
        $filePath = $resolver->apply($fp = FilePath::create('file.ext', $contentType, $extension));

        $this->assertSame($fb, $fileBlob);
        $this->assertSame($contentType, (string) $fileBlob->getContentType());
        $this->assertSame($extension, (string) $fileBlob->getExtension());
        $this->assertSame('content', $fileBlob->getContents());
        $this->assertSame($fp, $filePath);
        $this->assertSame($contentType, (string) $filePath->getContentType());
        $this->assertSame($extension, (string) $filePath->getExtension());
        $this->assertSame('file.ext', $filePath->getFile()->getPathname());
    }

    /**
     * @dataProvider provideResolveData
     *
     * @param string $contentType
     * @param string $extension
     */
    public function testResolveThrowsOnInvalidContentTypeAttribute(string $contentType, string $extension)
    {
        $this->expectException(InvalidFileAttributesException::class);
        $this->expectExceptionMessage('Unable to resolve content type attribute for file blob.');

        $resolver = $this->createFileAttributesApplierInstance(
            $this->createContentTypeGuesserMock('foobar'),
            $this->createExtensionGuesserMock($extension, null)
        );

        $resolver->apply(FileBlob::create());
    }

    /**
     * @dataProvider provideResolveData
     *
     * @param string $contentType
     * @param string $extension
     */
    public function testResolveThrowsOnInvalidExtensionAttribute(string $contentType, string $extension)
    {
        $this->expectException(InvalidFileAttributesException::class);
        $this->expectExceptionMessage('Unable to resolve extension attribute for file blob.');

        $resolver = $this->createFileAttributesApplierInstance(
            $this->createContentTypeGuesserMock($contentType),
            $this->createExtensionGuesserMock(null, $contentType)
        );

        $resolver->apply(FileBlob::create());
    }

    /**
     * @return \Iterator
     */
    public static function fetchFixtureData(): \Iterator
    {
        foreach ((new DataLoader())(ContentTypeAttributeTest::class, 10) as $d) {
            yield [$d[0], $d[count($d) - 1]];
        }
    }
}
