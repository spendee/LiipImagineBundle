<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\File\Loader;

use Liip\ImagineBundle\File\Loader\FileSystemLoader;
use Liip\ImagineBundle\File\Loader\LoaderInterface;
use Liip\ImagineBundle\File\Loader\Locator\FileSystemLocator;
use Liip\ImagineBundle\File\Loader\Locator\LocatorInterface;
use Liip\ImagineBundle\Exception\File\Loader\NotLoadableException;
use Liip\ImagineBundle\Exception\InvalidArgumentException;
use Liip\ImagineBundle\File\FilePath;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\File\Loader\FileSystemLoader
 */
class FileSystemLoaderTest extends AbstractTest
{
    public function testConstruction()
    {
        $loader = $this->getFileSystemLoader();

        $this->assertInstanceOf(FileSystemLoader::class, $loader);
    }

    public function testImplementsLoaderInterface()
    {
        $this->assertInstanceOf(LoaderInterface::class, $this->getFileSystemLoader());
    }

    /**
     * @return array[]
     */
    public static function provideLoadCases()
    {
        $file = pathinfo(__FILE__, PATHINFO_BASENAME);

        return [
            [
                __DIR__,
                $file,
            ],
            [
                __DIR__.'/',
                $file,
            ],
            [
                __DIR__, '/'.
                $file,
            ],
            [
                __DIR__.'/../../File/Loader',
                '/'.$file,
            ],
            [
                realpath(__DIR__.'/..'),
                'Loader/'.$file,
            ],
            [
                __DIR__.'/../',
                '/Loader/../../File/Loader/'.$file,
            ],
        ];
    }

    /**
     * @dataProvider provideLoadCases
     *
     * @param string $root
     * @param string $path
     */
    public function testLoad($root, $path)
    {
        $this->assertValidLoaderFindReturn($this->getFileSystemLoader([$root])->find($path));
    }

    /**
     * @return string[][]
     */
    public static function provideMultipleRootLoadCases()
    {
        $pathsPrepended = [
            realpath(__DIR__.'/../'),
            realpath(__DIR__.'/../../'),
            realpath(__DIR__.'/../../../'),
        ];

        return array_map(function ($parameters) use ($pathsPrepended) {
            return [[$pathsPrepended[mt_rand(0, count($pathsPrepended) - 1)], $parameters[0]], $parameters[1]];
        }, static::provideLoadCases());
    }

    /**
     * @dataProvider provideMultipleRootLoadCases
     *
     * @param string[] $roots
     * @param string   $path
     */
    public function testMultipleRootLoadCases($roots, $path)
    {
        $this->assertValidLoaderFindReturn($this->getFileSystemLoader($roots)->find($path));
    }

    public function testAllowsEmptyRootPath()
    {
        $loader = $this->getFileSystemLoader([]);

        $this->assertInstanceOf(FileSystemLoader::class, $loader);
    }

    public function testThrowsIfRootPathDoesNotExist()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Root image path not resolvable');

        $loader = $this->getFileSystemLoader(['/a/bad/root/path']);

        $this->assertInstanceOf(FileSystemLoader::class, $loader);
    }

    /**
     * @return array[]
     */
    public function provideOutsideRootPathsData()
    {
        return [
            ['../Loader/../../File/Loader/../../../Resources/config/routing.yaml'],
            ['../../File/'],
        ];
    }

    /**
     * @dataProvider provideOutsideRootPathsData
     *
     * @param string $path
     */
    public function testThrowsIfRealPathOutsideRootPath($path)
    {
        $this->expectException(NotLoadableException::class);
        $this->expectExceptionMessage('Source image invalid');

        $loader = $this->getFileSystemLoader()->find($path);

        $this->assertInstanceOf(FileSystemLoader::class, $loader);
    }

    public function testPathWithDoublePeriodBackStep()
    {
        $this->assertValidLoaderFindReturn($this->getFileSystemLoader()->find('/../../File/Loader/'.pathinfo(__FILE__, PATHINFO_BASENAME)));
    }

    public function testThrowsIfFileDoesNotExist()
    {
        $this->expectException(NotLoadableException::class);
        $this->expectExceptionMessage('Source image not resolvable');

        $loader = $this->getFileSystemLoader()->find('fileNotExist');

        $this->assertInstanceOf(FileSystemLoader::class, $loader);
    }

    /**
     * @param string[] $roots
     *
     * @return FileSystemLocator
     */
    private function getFileSystemLocator(array $roots)
    {
        return new FileSystemLocator($roots);
    }

    /**
     * @return string[]
     */
    private function getDefaultDataRoots()
    {
        return [__DIR__];
    }

    /**
     * @param array                 $roots
     * @param LocatorInterface|null $locator
     *
     * @return FileSystemLoader
     */
    private function getFileSystemLoader(array $roots = [], LocatorInterface $locator = null)
    {
        return new FileSystemLoader(
            null !== $locator ? $locator : $this->getFileSystemLocator(count($roots) ? $roots : $this->getDefaultDataRoots())
        );
    }

    /**
     * @param FilePath|mixed $return
     * @param string|null    $message
     */
    private function assertValidLoaderFindReturn($return, $message = null)
    {
        $this->assertInstanceOf(FilePath::class, $return, $message);
        $this->assertTrue($return->hasFile());
    }
}
