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

use Liip\ImagineBundle\File\Attributes\Attributes;
use Liip\ImagineBundle\File\FileBlob;
use Liip\ImagineBundle\File\FilePath;
use Liip\ImagineBundle\File\Attributes\Guesser\ContentTypeGuesser;
use Liip\ImagineBundle\File\Attributes\Guesser\ExtensionGuesser;
use Liip\ImagineBundle\File\Attributes\Resolver\FileAttributesResolver;
use Liip\ImagineBundle\File\Attributes\ContentTypeAttribute;
use Liip\ImagineBundle\File\Attributes\ExtensionAttribute;
use Liip\ImagineBundle\File\FileTemp;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser as SymfonyExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser as SymfonyContentTypeGuesser;

/**
 * @covers \Liip\ImagineBundle\File\Attributes\Guesser\ContentTypeGuesser
 * @covers \Liip\ImagineBundle\File\Attributes\Guesser\ExtensionGuesser
 * @covers \Liip\ImagineBundle\File\Attributes\Resolver\FileAttributesResolver
 */
class FileAttributesResolverTest extends TestCase
{
    public static function providePathGuessData(): \Iterator
    {
        /** @var \SplFileInfo[] $finder */
        $finder = (new Finder())
            ->in(sprintf('%s/../../../Fixtures/assets', __DIR__))
            ->files();

        $c = function (string $extension): ?string {
            switch ($extension) {
                case 'jpg':
                case 'jpeg':
                    return 'image/jpeg';
                case 'gif':
                    return 'image/gif';
                case 'png':
                    return 'image/png';
                case 'webp':
                    return 'image/webp';
                case 'ico':
                    return 'image/x-icon';
                case 'tiff':
                    return 'image/tiff';
                case 'pdf':
                    return 'application/pdf';
                case 'txt':
                    return 'text/plain';
            }

            return null;
        };

        $e = function (string $extension): ?string {
            switch ($extension) {
                case 'jpg':
                    return 'jpeg';
            }

            return $extension;
        };

        foreach ($finder as $file) {
            yield [$file->getPathname(), $c($file->getExtension()), $e($file->getExtension())];
        }
    }

    /**
     * @dataProvider providePathGuessData
     *
     * @param string $file
     * @param string $contentType
     * @param string $extension
     */
    public function testPathGuess(string $file, string $contentType, string $extension)
    {
        $resolver = self::createPopulatedFileAttributesResolver();

        $attr = $resolver->resolve($file);
        $this->assertAttributesEqual($attr, $contentType, $extension);

        $attr = $resolver->resolve(FilePath::create($file));
        $this->assertAttributesEqual($attr, $contentType, $extension);

        $attr = $resolver->resolveFilePath(FilePath::create($file));
        $this->assertAttributesEqual($attr, $contentType, $extension);
    }

    public static function provideContentGuessData(): \Iterator
    {
        foreach (self::providePathGuessData() as [$p, $c, $e]) {
            yield [file_get_contents($p), $p, $c, $e];
        }
    }

    /**
     * @dataProvider provideContentGuessData
     *
     * @param string $content
     * @param string $file
     * @param string $contentType
     * @param string $extension
     */
    public function testContentGuess(string $content, string $file, string $contentType, string $extension)
    {
        $resolver = self::createPopulatedFileAttributesResolver();

        $attr = $resolver->resolveFileBlob(FileBlob::create($content));
        $this->assertAttributesEqual($attr, $contentType, $extension);

        $attr = $resolver->resolve(FileBlob::create($content));
        $this->assertAttributesEqual($attr, $contentType, $extension);
    }

    /**
     * @param Attributes $attributes
     * @param string     $cString
     * @param string     $eString
     */
    private function assertAttributesEqual(Attributes $attributes, string $cString, string $eString): void
    {
        $c = $attributes->getContentType();
        $e = $attributes->getExtension();

        $this->assertInstanceOf(ContentTypeAttribute::class, $c);
        $this->assertInstanceOf(ExtensionAttribute::class, $e);

        $this->assertTrue($c->isValid());
        $this->assertTrue($e->isValid());

        $this->assertTrue($c->isMatch(...array_values(ContentTypeAttribute::explodeParsable($cString))));
        $this->assertTrue($e->isMatch(...array_values(ExtensionAttribute::explodeParsable($eString))));

        $this->assertSame($cString, $c->stringify());
        $this->assertSame($eString, $e->stringify());
    }

    /**
     * @return FileAttributesResolver
     */
    private static function createPopulatedFileAttributesResolver(): FileAttributesResolver
    {
        return self::createFileAttributesResolver(
            [SymfonyContentTypeGuesser::getInstance()],
            [SymfonyExtensionGuesser::getInstance()]
        );
    }

    /**
     * @param array $contentTypeGuessers
     * @param array $extensionGuessers
     *
     * @return FileAttributesResolver
     */
    private static function createFileAttributesResolver(array $contentTypeGuessers = [], array $extensionGuessers = []): FileAttributesResolver
    {
        $c = new ContentTypeGuesser();

        foreach ($contentTypeGuessers as $g) {
            $c->register($g);
        }

        $e = new ExtensionGuesser();

        foreach ($extensionGuessers as $g) {
            $e->register($g);
        }

        return new FileAttributesResolver($c, $e);
    }
}
