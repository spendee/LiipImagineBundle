<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Imagine\Data\Loader\Locator;

use Liip\ImagineBundle\Imagine\Data\Loader\Locator\FileSystemLocator;
use Liip\ImagineBundle\Imagine\Data\Loader\Locator\LocatorInterface;
use Liip\ImagineBundle\Exception\File\Loader\NotLoadableException;
use Liip\ImagineBundle\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;

abstract class AbstractFileSystemLocatorTest extends TestCase
{
    public function testImplementsLocatorInterface()
    {
        $this->assertInstanceOf(LocatorInterface::class, new FileSystemLocator());
    }

    public function testThrowsIfEmptyRootPath()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Root image path not resolvable');

        $this->getFileSystemLocator('');
    }

    public function testThrowsIfRootPathDoesNotExist()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Root image path not resolvable');

        $this->getFileSystemLocator('/a/bad/root/path');
    }

    public function testThrowsIfFileDoesNotExist()
    {
        $this->expectException(NotLoadableException::class);
        $this->expectExceptionMessage('Source image not resolvable');

        $this->getFileSystemLocator(__DIR__)->locate('fileNotExist');
    }

    public function testThrowsIfRootPlaceholderInvalid()
    {
        $this->expectException(NotLoadableException::class);
        $this->expectExceptionMessage('Invalid root placeholder "@invalid-placeholder" for path');

        $this->getFileSystemLocator(__DIR__)->locate('@invalid-placeholder:file.ext');
    }

    /**
     * @return array[]
     */
    public static function provideLoadCases()
    {
        return [];
    }

    /**
     * @dataProvider provideLoadCases
     *
     * @param string $root
     * @param string $path
     */
    public function testLoad($root, $path)
    {
        $this->assertStringStartsWith(realpath($root.'/../'), $this->getFileSystemLocator($root)->locate($path));
    }

    /**
     * @return array[]
     */
    public static function provideMultipleRootLoadCases()
    {
        return [];
    }

    /**
     * @dataProvider provideMultipleRootLoadCases
     *
     * @param string $root
     * @param string $path
     */
    public function testMultipleRootLoadCases($root, $path)
    {
        $this->assertNotNull($this->getFileSystemLocator($root)->locate($path));
    }

    /**
     * @return array[]
     */
    public function provideOutsideRootPathsData()
    {
        return [
            ['../Loader/../../Data/Loader/../../../Resources/config/routing.yaml'],
            ['../../Data/'],
        ];
    }

    /**
     * @dataProvider provideOutsideRootPathsData
     *
     * @param string $path
     */
    public function testThrowsIfRealPathOutsideRootPath($path)
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Root image path not resolvable');

        $this->getFileSystemLocator($path)->locate($path);
    }

    /**
     * @param string[]|string $paths
     *
     * @return LocatorInterface
     */
    abstract protected function getFileSystemLocator($paths);
}
