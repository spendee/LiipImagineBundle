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

use Liip\ImagineBundle\File\Guesser\Handler\ContentTypeGuesser;
use Liip\ImagineBundle\File\Guesser\Handler\ExtensionGuesser;
use Liip\ImagineBundle\File\Guesser\GuesserManager;
use Liip\ImagineBundle\File\Metadata\MimeTypeMetadata;
use Liip\ImagineBundle\File\Metadata\ExtensionMetadata;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser as SymfonyExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser as SymfonyContentTypeGuesser;

/**
 * @covers \Liip\ImagineBundle\File\Guesser\Handler\ContentTypeGuesser
 * @covers \Liip\ImagineBundle\File\Guesser\Handler\ExtensionGuesser
 * @covers \Liip\ImagineBundle\File\Guesser\GuesserManager
 */
class GuesserManagerTest extends TestCase
{
    public static function providePathGuessData(): \Iterator
    {
        /** @var \SplFileInfo[] $finder */
        $finder = (new Finder())
            ->in(sprintf('%s/../../Fixtures/assets', __DIR__))
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
        $manager = self::createSymfonyBasedGuesserManager();
        $meta = $manager->guessUsingPath($file);

        $this->assertInstanceOf(MimeTypeMetadata::class, $meta->getContentType());
        $this->assertInstanceOf(ExtensionMetadata::class, $meta->getExtension());
        $this->assertSame($contentType, $meta->getContentType()->__toString());
        $this->assertSame($extension, $meta->getExtension()->getExtension());
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
        $manager = self::createSymfonyBasedGuesserManager();
        $meta = $manager->guessUsingContent($content);

        $this->assertInstanceOf(MimeTypeMetadata::class, $meta->getContentType());
        $this->assertInstanceOf(ExtensionMetadata::class, $meta->getExtension());
        $this->assertSame($contentType, $meta->getContentType()->__toString());
        $this->assertSame($extension, $meta->getExtension()->getExtension());
    }

    /**
     * @return GuesserManager
     */
    private static function createSymfonyBasedGuesserManager(): GuesserManager
    {
        return self::createGuesserManager([
            SymfonyContentTypeGuesser::getInstance(),
        ], [
            SymfonyExtensionGuesser::getInstance(),
        ]);
    }

    /**
     * @return GuesserManager
     */
    private static function createEmptyGuesserManager(): GuesserManager
    {
        return self::createGuesserManager();
    }

    /**
     * @param array $contentTypeGuessers
     * @param array $extensionGuessers
     *
     * @return GuesserManager
     */
    private static function createGuesserManager(array $contentTypeGuessers = [], array $extensionGuessers = []): GuesserManager
    {
        $c = new ContentTypeGuesser(...$contentTypeGuessers);
        $e = new ExtensionGuesser(...$extensionGuessers);

        return new GuesserManager($c, $e);
    }
}
