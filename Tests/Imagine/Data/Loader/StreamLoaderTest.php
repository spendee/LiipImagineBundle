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

use Liip\ImagineBundle\Imagine\Data\Loader\StreamLoader;
use Liip\ImagineBundle\Exception\File\Loader\NotLoadableException;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Data\Loader\StreamLoader<extended>
 */
class StreamLoaderTest extends AbstractTest
{
    public function testThrowsIfInvalidPathGivenOnFind()
    {
        $this->expectException(NotLoadableException::class);
        $this->expectExceptionMessageRegExp('{Source image file://.+ not found.}');

        $loader = new StreamLoader('file://');
        $loader->find($this->temporaryPath.'/invalid.jpeg');
    }

    public function testReturnImageContentOnFind()
    {
        $loader = new StreamLoader('file://');

        $this->assertSame(
            file_get_contents($this->fixturesPath.'/assets/cats.jpeg'),
            $loader->find($this->fixturesPath.'/assets/cats.jpeg')->getContents()
        );
    }

    public function testReturnImageContentWhenStreamContextProvidedOnFind()
    {
        $loader = new StreamLoader('file://', stream_context_create());

        $this->assertSame(
            file_get_contents($this->fixturesPath.'/assets/cats.jpeg'),
            $loader->find($this->fixturesPath.'/assets/cats.jpeg')->getContents()
        );
    }

    public function testThrowsIfInvalidResourceGivenInConstructor()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The given context is no valid resource');

        new StreamLoader('an-invalid-resource-name', true);
    }
}
