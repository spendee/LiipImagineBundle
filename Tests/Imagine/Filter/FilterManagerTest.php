<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Filter;

use Liip\ImagineBundle\File\FileBlob;
use Liip\ImagineBundle\File\FileInterface;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\FilterManager
 */
class FilterManagerTest extends AbstractTest
{
    public function testThrowsIfNoLoadersAddedForFilterOnApplyFilter()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not find filter(s): "thumbnail"');

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'filters' => [
                    'thumbnail' => [
                        'size' => [180, 180],
                        'mode' => 'outbound',
                    ],
                ],
                'post_processors' => [],
            ]));

        $binary = FileBlob::create('aContent', 'image/png', 'png');

        $filterManager = new FilterManager(
            $config,
            $this->createImagineInterfaceMock(),
            $this->createFileGuesserManager()
        );

        $filterManager->applyFilter($binary, 'thumbnail');
    }

    public function testReturnFilteredBinaryWithExpectedContentOnApplyFilter()
    {
        $originalContent = 'aOriginalContent';
        $expectedFilteredContent = 'theFilteredContent';

        $binary = FileBlob::create($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'filters' => [
                    'thumbnail' => $thumbConfig,
                ],
                'post_processors' => [],
            ]));

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($expectedFilteredContent));

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->with($originalContent)
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createFileGuesserManager()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');

        $this->assertInstanceOf(FileBlob::class, $filteredBinary);
        $this->assertSame($expectedFilteredContent, $filteredBinary->getContents());
    }

    public function testReturnFilteredBinaryWithFormatOfOriginalBinaryOnApplyFilter()
    {
        $originalContent = 'aOriginalContent';
        $expectedFormat = 'png';

        $binary = FileBlob::create($originalContent, 'image/png', $expectedFormat);

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'filters' => [
                    'thumbnail' => $thumbConfig,
                ],
                'post_processors' => [],
            ]));

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('aFilteredContent'));

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createFileGuesserManager()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');

        $this->assertInstanceOf(FileBlob::class, $filteredBinary);
        $this->assertSame($expectedFormat, (string) $filteredBinary->getExtension());
    }

    public function testReturnFilteredBinaryWithCustomFormatIfSetOnApplyFilter()
    {
        $originalContent = 'aOriginalContent';
        $originalFormat = 'png';
        $expectedFormat = 'jpg';

        $binary = FileBlob::create($originalContent, 'image/png', $originalFormat);

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'format' => $expectedFormat,
                'filters' => [
                    'thumbnail' => $thumbConfig,
                ],
                'post_processors' => [],
            ]));

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('aFilteredContent'));

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createFileGuesserManager()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');

        $this->assertInstanceOf(FileBlob::class, $filteredBinary);
        $this->assertSame($expectedFormat, (string) $filteredBinary->getExtension());
    }

    public function testReturnFilteredBinaryWithMimeTypeOfOriginalBinaryOnApplyFilter()
    {
        $binary = FileBlob::create('foobar', 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'filters' => [
                    'thumbnail' => $thumbConfig,
                ],
                'post_processors' => [],
            ]));

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('aFilteredContent'));

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createFileGuesserManager([$mimeTypeGuesser])
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');

        $this->assertInstanceOf(FileBlob::class, $filteredBinary);
        $this->assertSame('image/png', (string) $filteredBinary->getContentType());
    }

    public function testReturnFilteredBinaryWithMimeTypeOfCustomFormatIfSetOnApplyFilter()
    {
        $originalContent = 'aOriginalContent';
        $originalMimeType = 'image/png';
        $expectedContent = 'aFilteredContent';
        $expectedMimeType = 'image/jpeg';

        $binary = FileBlob::create($originalContent, $originalMimeType, 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'format' => 'jpg',
                'filters' => [
                    'thumbnail' => $thumbConfig,
                ],
                'post_processors' => [],
            ]));

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($expectedContent));

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->will($this->returnValue($expectedMimeType));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createFileGuesserManager([$mimeTypeGuesser])
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');

        $this->assertInstanceOf(FileBlob::class, $filteredBinary);
        $this->assertSame($expectedMimeType, (string) $filteredBinary->getContentType());
    }

    public function testAltersQualityOnApplyFilter()
    {
        $originalContent = 'aOriginalContent';
        $expectedQuality = 80;

        $binary = FileBlob::create($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'quality' => $expectedQuality,
                'filters' => [
                    'thumbnail' => $thumbConfig,
                ],
                'post_processors' => [],
            ]));

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', ['quality' => $expectedQuality])
            ->will($this->returnValue('aFilteredContent'));

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createFileGuesserManager()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $this->assertInstanceOf(FileBlob::class, $filterManager->applyFilter($binary, 'thumbnail'));
    }

    public function testAlters100QualityIfNotSetOnApplyFilter()
    {
        $originalContent = 'aOriginalContent';
        $expectedQuality = 100;

        $binary = FileBlob::create($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'filters' => [
                    'thumbnail' => $thumbConfig,
                ],
                'post_processors' => [],
            ]));

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', ['quality' => $expectedQuality])
            ->will($this->returnValue('aFilteredContent'));

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createFileGuesserManager()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $this->assertInstanceOf(FileBlob::class, $filterManager->applyFilter($binary, 'thumbnail'));
    }

    public function testMergeRuntimeConfigWithOneFromFilterConfigurationOnApplyFilter()
    {
        $binary = FileBlob::create('aContent', 'image/png', 'png');

        $runtimeConfig = [
            'filters' => [
                'thumbnail' => [
                    'size' => [100, 100],
                ],
            ],
            'post_processors' => [],
        ];

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $thumbMergedConfig = [
            'size' => [100, 100],
            'mode' => 'outbound',
        ];

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'filters' => [
                    'thumbnail' => $thumbConfig,
                ],
                'post_processors' => [],
            ]));

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('aFilteredContent'));

        $imagine = $this->createImagineInterfaceMock();
        $imagine
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbMergedConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $config,
            $imagine,
            $this->createFileGuesserManager()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $this->assertInstanceOf(
            FileBlob::class,
            $filterManager->applyFilter($binary, 'thumbnail', $runtimeConfig)
        );
    }

    public function testThrowsIfNoLoadersAddedForFilterOnApply()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not find filter(s): "thumbnail"');

        $binary = FileBlob::create('aContent', 'image/png', 'png');

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $this->createImagineInterfaceMock(),
            $this->createFileGuesserManager()
        );

        $filterManager->apply($binary, [
            'filters' => [
                'thumbnail' => [
                    'size' => [180, 180],
                    'mode' => 'outbound',
                ],
            ],
        ]);
    }

    public function testReturnFilteredBinaryWithExpectedContentOnApply()
    {
        $originalContent = 'aOriginalContent';
        $expectedFilteredContent = 'theFilteredContent';

        $binary = FileBlob::create($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($expectedFilteredContent));

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->with($originalContent)
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $this->createFileGuesserManager()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, [
            'filters' => [
                'thumbnail' => $thumbConfig,
            ],
            'post_processors' => [],
        ]);

        $this->assertInstanceOf(FileBlob::class, $filteredBinary);
        $this->assertSame($expectedFilteredContent, $filteredBinary->getContents());
    }

    public function testReturnFilteredBinaryWithFormatOfOriginalBinaryOnApply()
    {
        $originalContent = 'aOriginalContent';
        $expectedFormat = 'png';

        $binary = FileBlob::create($originalContent, 'image/png', $expectedFormat);

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('aFilteredContent'));

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $this->createFileGuesserManager()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, [
            'filters' => [
                'thumbnail' => $thumbConfig,
            ],
            'post_processors' => [],
        ]);

        $this->assertInstanceOf(FileBlob::class, $filteredBinary);
        $this->assertSame($expectedFormat, (string) $filteredBinary->getExtension());
    }

    public function testReturnFilteredBinaryWithCustomFormatIfSetOnApply()
    {
        $originalContent = 'aOriginalContent';
        $originalFormat = 'png';
        $expectedFormat = 'jpg';

        $binary = FileBlob::create($originalContent, 'image/png', $originalFormat);

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('aFilteredContent'));

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $this->createFileGuesserManager()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, [
            'format' => $expectedFormat,
            'filters' => [
                'thumbnail' => $thumbConfig,
            ],
            'post_processors' => [],
        ]);

        $this->assertInstanceOf(FileBlob::class, $filteredBinary);
        $this->assertSame($expectedFormat, (string) $filteredBinary->getExtension());
    }

    public function testReturnFilteredBinaryWithMimeTypeOfOriginalBinaryOnApply()
    {
        $originalContent = 'aOriginalContent';
        $expectedMimeType = 'image/png';

        $binary = FileBlob::create($originalContent, $expectedMimeType, 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue('aFilteredContent'));

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $this->createFileGuesserManager([$mimeTypeGuesser])
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, [
            'filters' => [
                'thumbnail' => $thumbConfig,
            ],
            'post_processors' => [],
        ]);

        $this->assertInstanceOf(FileBlob::class, $filteredBinary);
        $this->assertSame($expectedMimeType, (string) $filteredBinary->getContentType());
    }

    public function testReturnFilteredBinaryWithMimeTypeOfCustomFormatIfSetOnApply()
    {
        $originalContent = 'aOriginalContent';
        $originalMimeType = 'image/png';
        $expectedContent = 'aFilteredContent';
        $expectedMimeType = 'image/jpeg';

        $binary = FileBlob::create($originalContent, $originalMimeType, 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($expectedContent));

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->once())
            ->method('guess')
            ->will($this->returnValue($expectedMimeType));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $this->createFileGuesserManager([$mimeTypeGuesser])
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, [
            'format' => 'jpg',
            'filters' => [
                'thumbnail' => $thumbConfig,
            ],
            'post_processors' => [],
        ]);

        $this->assertInstanceOf(FileBlob::class, $filteredBinary);
        $this->assertSame($expectedMimeType, (string) $filteredBinary->getContentType());
    }

    public function testAltersQualityOnApply()
    {
        $originalContent = 'aOriginalContent';
        $expectedQuality = 80;

        $binary = FileBlob::create($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', ['quality' => $expectedQuality])
            ->will($this->returnValue('aFilteredContent'));

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $this->createFileGuesserManager()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, [
            'quality' => $expectedQuality,
            'filters' => [
                'thumbnail' => $thumbConfig,
            ],
            'post_processors' => [],
        ]);

        $this->assertInstanceOf(FileBlob::class, $filteredBinary);
    }

    public function testAlters100QualityIfNotSetOnApply()
    {
        $originalContent = 'aOriginalContent';
        $expectedQuality = 100;

        $binary = FileBlob::create($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->with('png', ['quality' => $expectedQuality])
            ->will($this->returnValue('aFilteredContent'));

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $imagineMock,
            $this->createFileGuesserManager()
        );
        $filterManager->addLoader('thumbnail', $loader);

        $filteredBinary = $filterManager->apply($binary, [
            'filters' => [
                'thumbnail' => $thumbConfig,
            ],
            'post_processors' => [],
        ]);

        $this->assertInstanceOf(FileBlob::class, $filteredBinary);
    }

    public function testApplyPostProcessor()
    {
        $originalContent = 'aContent';
        $expectedPostProcessedContent = 'postProcessedContent';
        $binary = FileBlob::create($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'filters' => [
                    'thumbnail' => $thumbConfig,
                ],
                'post_processors' => [
                    'foo' => [],
                ],
            ]));

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($originalContent));

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->with($originalContent)
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $processedBinary = FileBlob::create($expectedPostProcessedContent, 'image/png', 'png');

        $postProcessor = $this->createPostProcessorInterfaceMock();
        $postProcessor
            ->expects($this->once())
            ->method('process')
            ->with($binary)
            ->will($this->returnValue($processedBinary));

        $filterManager = new FilterManager(
            $config,
            $imagineMock,
            $this->createFileGuesserManager()
        );
        $filterManager->addLoader('thumbnail', $loader);
        $filterManager->addPostProcessor('foo', $postProcessor);

        $filteredBinary = $filterManager->applyFilter($binary, 'thumbnail');
        $this->assertInstanceOf(FileBlob::class, $filteredBinary);
        $this->assertSame($expectedPostProcessedContent, $filteredBinary->getContents());
    }

    public function testThrowsIfNoPostProcessorAddedForFilterOnApplyFilter()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not find post processor(s): "foo"');

        $originalContent = 'aContent';
        $binary = FileBlob::create($originalContent, 'image/png', 'png');

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->atLeastOnce())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'filters' => [
                    'thumbnail' => $thumbConfig,
                ],
                'post_processors' => [
                    'foo' => [],
                ],
            ]));

        $thumbConfig = [
            'size' => [180, 180],
            'mode' => 'outbound',
        ];

        $image = $this->getImageInterfaceMock();
        $image
            ->expects($this->once())
            ->method('get')
            ->will($this->returnValue($originalContent));

        $imagineMock = $this->createImagineInterfaceMock();
        $imagineMock
            ->expects($this->once())
            ->method('load')
            ->with($originalContent)
            ->will($this->returnValue($image));

        $loader = $this->createFilterLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('load')
            ->with($this->identicalTo($image), $thumbConfig)
            ->will($this->returnArgument(0));

        $filterManager = new FilterManager(
            $config,
            $imagineMock,
            $this->createFileGuesserManager()
        );

        $filterManager->addLoader('thumbnail', $loader);
        $filterManager->applyFilter($binary, 'thumbnail');
    }

    public function testApplyPostProcessorsWhenNotDefined()
    {
        $binary = $this->getMockBuilder(FileInterface::class)->getMock();
        $filterManager = new FilterManager(
            $this->createFilterConfigurationMock(),
            $this->createImagineInterfaceMock(),
            $this->createFileGuesserManager()
        );

        $this->assertSame($binary, $filterManager->applyPostProcessors($binary, []));
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LoaderInterface
     */
    protected function createFilterLoaderInterfaceMock()
    {
        return $this->createObjectMock(LoaderInterface::class);
    }
}
