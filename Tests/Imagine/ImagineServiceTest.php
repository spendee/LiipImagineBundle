<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Imagine;

use Liip\ImagineBundle\Exception\Imagine\Filter\NonExistingFilterException;
use Liip\ImagineBundle\File\FileBlob;
use Liip\ImagineBundle\Imagine\ImagineService;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\ImagineService
 */
class ImagineServiceTest extends AbstractTest
{
    public function testBustCache(): void
    {
        $service = new \Liip\ImagineBundle\Imagine\ImagineService(
            $this->createDataManagerMock(),
            $this->createFilterManagerMock(),
            $cache = $this->createCacheManagerMock()
        );

        $cache
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->with('path', 'filter')
            ->willReturn(false);

        $cache
            ->expects($this->never())
            ->method('remove');

        $service->bustCache('path', 'filter');

        $service = new ImagineService(
            $this->createDataManagerMock(),
            $this->createFilterManagerMock(),
            $cache = $this->createCacheManagerMock()
        );

        $cache
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->with('path', 'filter')
            ->willReturn(true);

        $cache
            ->expects($this->atLeastOnce())
            ->method('remove')
            ->with('path', 'filter');

        $service->bustCache('path', 'filter');
    }

    public function testGetUrlOfFilteredImage(): void
    {
        $service = new ImagineService(
            $this->createDataManagerMock(),
            $this->createFilterManagerMock(),
            $cache = $this->createCacheManagerMock()
        );

        $cache
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->with('path', 'filter', null)
            ->willReturn(true);

        $cache
            ->expects($this->atLeastOnce())
            ->method('resolve')
            ->with('path', 'filter', null)
            ->willReturn('resolved');

        $this->assertSame('resolved', $service->getUrlOfFilteredImage('path', 'filter'));

        $service = new ImagineService(
            $this->createDataManagerMock(),
            $filter = $this->createFilterManagerMock(),
            $cache = $this->createCacheManagerMock()
        );

        $cache
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->with('path', 'filter', null)
            ->willReturn(false);

        $cache
            ->expects($this->atLeastOnce())
            ->method('resolve')
            ->with('path', 'filter', null)
            ->willReturn('resolved');

        $cache
            ->expects($this->atLeastOnce())
            ->method('store')
            ->withAnyParameters();

        $filter
            ->expects($this->atLeastOnce())
            ->method('applyFilter')
            ->withAnyParameters()
            ->willReturn(FileBlob::create());

        $this->assertSame('resolved', $service->getUrlOfFilteredImage('path', 'filter'));

        $service = new ImagineService(
            $this->createDataManagerMock(),
            $filter = $this->createFilterManagerMock(),
            $cache = $this->createCacheManagerMock(),
            $logger = $this->createLoggerMock()
        );

        $cache
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->with('path', 'filter', null)
            ->willReturn(false);

        $cache
            ->expects($this->never())
            ->method('resolve');

        $cache
            ->expects($this->never())
            ->method('store');

        $filter
            ->expects($this->atLeastOnce())
            ->method('applyFilter')
            ->withAnyParameters()
            ->willThrowException(new NonExistingFilterException('Filter does not exist'));

        $logger
            ->expects($this->atLeastOnce())
            ->method('debug')
            ->with('Could not locate filter "filter" for path "path". Message was "Filter does not exist"');

        $this->expectException(NonExistingFilterException::class);
        $this->expectExceptionMessage('Filter does not exist');
        $service->getUrlOfFilteredImage('path', 'filter');
    }

    public function testGetUrlOfFilteredImageWithRuntimeFilters(): void
    {
        $service = new ImagineService(
            $this->createDataManagerMock(),
            $this->createFilterManagerMock(),
            $cache = $this->createCacheManagerMock()
        );

        $cache
            ->expects($this->atLeastOnce())
            ->method('getRuntimePath')
            ->with('path', ['f1', 'f2'])
            ->willReturn('rc/path');

        $cache
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->with('rc/path', 'filter', null)
            ->willReturn(true);

        $cache
            ->expects($this->atLeastOnce())
            ->method('resolve')
            ->with('rc/path', 'filter', null)
            ->willReturn('resolved');

        $this->assertSame('resolved', $service->getUrlOfFilteredImageWithRuntimeFilters('path', 'filter', ['f1', 'f2']));

        $service = new ImagineService(
            $this->createDataManagerMock(),
            $filter = $this->createFilterManagerMock(),
            $cache = $this->createCacheManagerMock()
        );

        $cache
            ->expects($this->atLeastOnce())
            ->method('getRuntimePath')
            ->with('path', ['f1', 'f2'])
            ->willReturn('rc/path');

        $cache
            ->expects($this->atLeastOnce())
            ->method('isStored')
            ->with('rc/path', 'filter', null)
            ->willReturn(false);

        $cache
            ->expects($this->atLeastOnce())
            ->method('resolve')
            ->with('rc/path', 'filter', null)
            ->willReturn('resolved');

        $cache
            ->expects($this->atLeastOnce())
            ->method('store')
            ->withAnyParameters();

        $filter
            ->expects($this->atLeastOnce())
            ->method('applyFilter')
            ->withAnyParameters()
            ->willReturn(FileBlob::create());

        $this->assertSame('resolved', $service->getUrlOfFilteredImageWithRuntimeFilters('path', 'filter', ['f1', 'f2']));
    }
}
