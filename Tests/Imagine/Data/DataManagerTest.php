<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Imagine\Data;

use Liip\ImagineBundle\File\FileBlob;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Data\DataManager
 */
class DataManagerTest extends AbstractTest
{
    public function testUseDefaultLoaderUsedIfNoneSet()
    {
        $loader = $this->createBinaryLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg');

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => null,
            ]));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->atLeastOnce())
            ->method('guess')
            ->withAnyParameters()
            ->will($this->returnValue('image/png'));

        $dataManager = new DataManager($config, $this->createFileMetadataResolver([$mimeTypeGuesser]), 'default');
        $dataManager->addLoader('default', $loader);

        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testUseLoaderRegisteredForFilterOnFind()
    {
        $loader = $this->createBinaryLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg');

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            ]));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->atLeastOnce())
            ->method('guess')
            ->will($this->returnValue('image/png'));

        $dataManager = new DataManager($config, $this->createFileMetadataResolver([$mimeTypeGuesser]));
        $dataManager->addLoader('the_loader', $loader);

        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testThrowsIfMimeTypeWasNotGuessedOnFind()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Failed to resolve the content type of "cats.jpeg"');

        $loader = $this->createBinaryLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg');

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            ]));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->atLeastOnce())
            ->method('guess')
            ->will($this->returnValue(null));

        $dataManager = new DataManager($config, $this->createFileMetadataResolver([$mimeTypeGuesser]));
        $dataManager->addLoader('the_loader', $loader);
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testThrowsIfMimeTypeNotImageOneOnFind()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Invalid content type "text/plain" resolved for "cats.jpeg" (expected primary type "image").');

        $loader = $this->createBinaryLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
            ->willReturn(FileBlob::create('content'));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            ]));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->atLeastOnce())
            ->method('guess')
            ->will($this->returnValue('text/plain'));

        $dataManager = new DataManager($config, $this->createFileMetadataResolver([$mimeTypeGuesser]));
        $dataManager->addLoader('the_loader', $loader);
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testThrowsIfLoaderReturnBinaryWithEmtptyMimeTypeOnFind()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Failed to resolve the content type of "cats.jpeg".');

        $loader = $this->createBinaryLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
            ->will($this->returnValue(FileBlob::create('content', null, 'jpeg')));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            ]));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->atLeastOnce())
            ->method('guess');

        $dataManager = new DataManager($config, $this->createFileMetadataResolver([$mimeTypeGuesser]));
        $dataManager->addLoader('the_loader', $loader);
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testThrowsIfLoaderReturnBinaryWithMimeTypeNotImageOneOnFind()
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Invalid content type "text/plain" resolved for "cats.jpeg" (expected primary type "image").');

        $binary = FileBlob::create('content', 'text/plain', 'txt');

        $loader = $this->createBinaryLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('find')
            ->with('cats.jpeg')
            ->will($this->returnValue($binary));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => 'the_loader',
            ]));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $dataManager = new DataManager($config, $this->createFileMetadataResolver([$mimeTypeGuesser]));
        $dataManager->addLoader('the_loader', $loader);
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testThrowIfLoaderNotRegisteredForGivenFilterOnFind()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Could not find data loader "" for "thumbnail" filter type');

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => null,
            ]));

        $dataManager = new DataManager($config, $this->createFileMetadataResolver());
        $dataManager->find('thumbnail', 'cats.jpeg');
    }

    public function testShouldReturnBinaryWithLoaderContentAndGuessedMimeTypeOnFind()
    {
        $expectedContent = 'theImageBinaryContent';
        $expectedMimeType = 'image/png';

        $loader = $this->createBinaryLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('find')
            ->willReturn(FileBlob::create($expectedContent));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->atLeastOnce())
            ->method('guess')
            ->will($this->returnValue($expectedMimeType));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => null,
            ]));

        $dataManager = new DataManager($config, $this->createFileMetadataResolver([$mimeTypeGuesser]), 'default');
        $dataManager->addLoader('default', $loader);

        $binary = $dataManager->find('thumbnail', 'cats.jpeg');

        $this->assertInstanceOf(FileBlob::class, $binary);
        $this->assertSame($expectedContent, $binary->getContents());
        $this->assertSame($expectedMimeType, (string) $binary->getContentType());
    }

    public function testShouldReturnBinaryWithLoaderContentAndGuessedFormatOnFind()
    {
        $content = 'theImageBinaryContent';
        $mimeType = 'image/png';
        $expectedFormat = 'png';

        $loader = $this->createBinaryLoaderInterfaceMock();
        $loader
            ->expects($this->once())
            ->method('find')
            ->willReturn(FileBlob::create($content));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->atLeastOnce())
            ->method('guess')
            ->will($this->returnValue($mimeType));

        $extensionGuesser = $this->createExtensionGuesserInterfaceMock();
        $extensionGuesser
            ->expects($this->atLeastOnce())
            ->method('guess')
            ->with($mimeType)
            ->will($this->returnValue($expectedFormat));

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'size' => [180, 180],
                'mode' => 'outbound',
                'data_loader' => null,
            ]));

        $dataManager = new DataManager($config, $this->createFileMetadataResolver([$mimeTypeGuesser], [$extensionGuesser]), 'default');
        $dataManager->addLoader('default', $loader);

        $binary = $dataManager->find('thumbnail', 'cats.jpeg');

        $this->assertInstanceOf(FileBlob::class, $binary);
        $this->assertSame($content, $binary->getContents());
        $this->assertSame($expectedFormat, (string) $binary->getExtension());
    }

    public function testUseDefaultGlobalImageUsedIfImageNotFound()
    {
        $loader = $this->createBinaryLoaderInterfaceMock();

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'default_image' => null,
            ]));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $defaultGlobalImage = 'cats.jpeg';
        $dataManager = new DataManager($config, $this->createFileMetadataResolver([$mimeTypeGuesser]), 'default', 'cats.jpeg');
        $dataManager->addLoader('default', $loader);

        $defaultImage = $dataManager->getDefaultImageUrl('thumbnail');
        $this->assertSame($defaultImage, $defaultGlobalImage);
    }

    public function testUseDefaultFilterImageUsedIfImageNotFound()
    {
        $loader = $this->createBinaryLoaderInterfaceMock();

        $defaultFilterImage = 'cats.jpeg';

        $config = $this->createFilterConfigurationMock();
        $config
            ->expects($this->once())
            ->method('get')
            ->with('thumbnail')
            ->will($this->returnValue([
                'default_image' => $defaultFilterImage,
            ]));

        $mimeTypeGuesser = $this->createMimeTypeGuesserInterfaceMock();
        $mimeTypeGuesser
            ->expects($this->never())
            ->method('guess');

        $dataManager = new DataManager($config, $this->createFileMetadataResolver([$mimeTypeGuesser]), 'default', null);
        $dataManager->addLoader('default', $loader);

        $defaultImage = $dataManager->getDefaultImageUrl('thumbnail');
        $this->assertSame($defaultImage, $defaultFilterImage);
    }
}
