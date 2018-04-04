<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Imagine\Data\Loader;

use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Liip\ImagineBundle\Exception\File\Loader\NotLoadableException;
use Liip\ImagineBundle\Imagine\Data\Loader\FlysystemLoader;
use Liip\ImagineBundle\Imagine\Data\Loader\LoaderInterface;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @requires PHP 5.4
 *
 * @covers \Liip\ImagineBundle\Imagine\Data\Loader\FlysystemLoader
 */
class FlysystemLoaderTest extends AbstractTest
{
    private $flyFilesystem;

    public function setUp()
    {
        parent::setUp();

        if (!class_exists(Filesystem::class)) {
            $this->markTestSkipped('Requires the league/flysystem package.');
        }

        $this->flyFilesystem = new Filesystem(new Local($this->fixturesPath));
    }

    /**
     * @return FlysystemLoader
     */
    public function getFlysystemLoader()
    {
        return new FlysystemLoader($this->flyFilesystem, $this->createFileAttributeResolverInstance());
    }

    public function testShouldImplementLoaderInterface()
    {
        $this->assertInstanceOf(LoaderInterface::class, $this->getFlysystemLoader());
    }

    public function testReturnImageContentOnFind()
    {
        $loader = $this->getFlysystemLoader();

        $this->assertSame(
            file_get_contents($this->fixturesPath.'/assets/cats.jpeg'),
            $loader->find('assets/cats.jpeg')->getContents()
        );
    }

    public function testThrowsIfInvalidPathGivenOnFind()
    {
        $this->expectException(NotLoadableException::class);
        $this->expectExceptionMessageRegExp('{Source image .+ not found}');

        $loader = $this->getFlysystemLoader();

        $loader->find('invalid.jpeg');
    }
}
