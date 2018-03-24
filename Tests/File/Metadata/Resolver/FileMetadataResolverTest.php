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

use Liip\ImagineBundle\File\FileBlob;
use Liip\ImagineBundle\File\FilePath;
use Liip\ImagineBundle\File\Guesser\Handler\ContentTypeGuesser;
use Liip\ImagineBundle\File\Guesser\GuesserManager;
use Liip\ImagineBundle\File\Guesser\Handler\ExtensionGuesser;
use Liip\ImagineBundle\File\Metadata\Resolver\ImageMetadataResolver;
use Liip\ImagineBundle\Tests\Fixtures\Data\DataLoader;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;

/**
 * @covers \Liip\ImagineBundle\File\Metadata\Resolver\ImageMetadataResolver
 */
class FileMetadataResolverTest extends TestCase
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
        /** @var MimeTypeGuesserInterface|\PHPUnit_Framework_MockObject_MockObject $symfonyContentTypeGuesser */
        $symfonyContentTypeGuesser = $this->getMockBuilder(MimeTypeGuesserInterface::class)
            ->setMethods(['guess'])
            ->getMock();

        $symfonyContentTypeGuesser
            ->expects($this->atLeastOnce())
            ->method('guess')
            ->withAnyParameters()
            ->willReturn($contentType);

        $contentTypeGuesser = new ContentTypeGuesser($symfonyContentTypeGuesser);

        /** @var ExtensionGuesserInterface|\PHPUnit_Framework_MockObject_MockObject $symfonyExtensionGuesser */
        $symfonyExtensionGuesser = $this->getMockBuilder(ExtensionGuesserInterface::class)
            ->setMethods(['guess'])
            ->getMock();

        $symfonyExtensionGuesser
            ->expects($this->atLeastOnce())
            ->method('guess')
            ->with($contentType)
            ->willReturn($extension);

        $extensionGuesser = new ExtensionGuesser($symfonyExtensionGuesser);

        $resolver = new ImageMetadataResolver(new GuesserManager($contentTypeGuesser, $extensionGuesser));
        $fileBlob = $resolver->resolve($fb = FileBlob::create('content'));
        $filePath = $resolver->resolve($fp = FilePath::create('file.ext'));

        $this->assertNotSame($fb, $fileBlob);
        $this->assertSame($contentType, (string) $fileBlob->getContentType());
        $this->assertSame($extension, (string) $fileBlob->getExtension());
        $this->assertSame('content', $fileBlob->getContents());
        $this->assertNotSame($fp, $filePath);
        $this->assertSame($contentType, (string) $filePath->getContentType());
        $this->assertSame($extension, (string) $filePath->getExtension());
        $this->assertSame('file.ext', $filePath->getFile()->getPathname());

        $fileBlob = $resolver->resolve($fb = FileBlob::create('content', $contentType));
        $filePath = $resolver->resolve($fp = FilePath::create('file.ext', $contentType));

        $this->assertNotSame($fb, $fileBlob);
        $this->assertSame($contentType, (string) $fileBlob->getContentType());
        $this->assertSame($extension, (string) $fileBlob->getExtension());
        $this->assertSame('content', $fileBlob->getContents());
        $this->assertNotSame($fp, $filePath);
        $this->assertSame($contentType, (string) $filePath->getContentType());
        $this->assertSame($extension, (string) $filePath->getExtension());
        $this->assertSame('file.ext', $filePath->getFile()->getPathname());

        $fileBlob = $resolver->resolve($fb = FileBlob::create('content', $contentType, $extension));
        $filePath = $resolver->resolve($fp = FilePath::create('file.ext', $contentType, $extension));

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
     * @return \Iterator
     */
    public static function fetchFixtureData(): \Iterator
    {
        foreach ((new DataLoader())(MimeTypeMetadataTest::class, 10) as $d) {
            yield [$d[0], $d[count($d) - 1]];
        }
    }
}
