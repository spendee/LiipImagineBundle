<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests;

use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Metadata\MetadataBag;
use Liip\ImagineBundle\File\Loader\LoaderInterface;
use Liip\ImagineBundle\File\Attributes\Guesser\ContentTypeGuesser;
use Liip\ImagineBundle\File\Attributes\Guesser\ContentTypeGuesserInterface;
use Liip\ImagineBundle\File\Attributes\Guesser\ExtensionGuesser;
use Liip\ImagineBundle\File\Attributes\Resolver\FileAttributesResolver;
use Liip\ImagineBundle\File\Attributes\Resolver\FileAttributesApplier;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Liip\ImagineBundle\Imagine\Cache\Resolver\ResolverInterface;
use Liip\ImagineBundle\Imagine\Cache\SignerInterface;
use Liip\ImagineBundle\Imagine\Data\DataManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;
use Liip\ImagineBundle\Imagine\Filter\FilterManager;
use Liip\ImagineBundle\Imagine\Filter\PostProcessor\PostProcessorInterface;
use Liip\ImagineBundle\Log\Logger;
use Liip\ImagineBundle\Service\FilterService;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesser as SymfonyExtensionGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractTest extends TestCase
{
    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var string
     */
    protected $fixturesPath;

    /**
     * @var string
     */
    protected $temporaryPath;

    protected function setUp()
    {
        $this->fixturesPath = realpath(__DIR__.DIRECTORY_SEPARATOR.'Fixtures');
        $this->temporaryPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'liip_imagine_test';
        $this->filesystem = new Filesystem();

        if ($this->filesystem->exists($this->temporaryPath)) {
            $this->filesystem->remove($this->temporaryPath);
        }

        $this->filesystem->mkdir($this->temporaryPath);
    }

    protected function tearDown()
    {
        if (!$this->filesystem) {
            return;
        }

        if ($this->filesystem->exists($this->temporaryPath)) {
            $this->filesystem->remove($this->temporaryPath);
        }
    }

    /**
     * @return string[]
     */
    public function invalidPathProvider()
    {
        return [
            [$this->fixturesPath.'/assets/../../foobar.png'],
            [$this->fixturesPath.'/assets/some_folder/../foobar.png'],
            ['../../outside/foobar.jpg'],
        ];
    }

    /**
     * @return FilterConfiguration
     */
    protected function createFilterConfiguration()
    {
        $config = new FilterConfiguration();
        $config->set('thumbnail', [
            'size' => [180, 180],
            'mode' => 'outbound',
        ]);

        return $config;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|CacheManager
     */
    protected function createCacheManagerMock()
    {
        return $this
            ->getMockBuilder(CacheManager::class)
            ->setConstructorArgs([
                $this->createFilterConfiguration(),
                $this->createRouterInterfaceMock(),
                $this->createSignerInterfaceMock(),
                $this->createEventDispatcherInterfaceMock(),
            ])
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FilterConfiguration
     */
    protected function createFilterConfigurationMock()
    {
        return $this->createObjectMock(FilterConfiguration::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SignerInterface
     */
    protected function createSignerInterfaceMock()
    {
        return $this->createObjectMock(SignerInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RouterInterface
     */
    protected function createRouterInterfaceMock()
    {
        return $this->createObjectMock(RouterInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ResolverInterface
     */
    protected function createCacheResolverInterfaceMock()
    {
        return $this->createObjectMock(ResolverInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|EventDispatcherInterface
     */
    protected function createEventDispatcherInterfaceMock()
    {
        return $this->createObjectMock(EventDispatcherInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ImageInterface
     */
    protected function getImageInterfaceMock()
    {
        return $this->createObjectMock(ImageInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MetadataBag
     */
    protected function getMetadataBagMock()
    {
        return $this->createObjectMock(MetadataBag::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ImagineInterface
     */
    protected function createImagineInterfaceMock()
    {
        return $this->createObjectMock(ImagineInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|Logger
     */
    protected function createLoggerMock()
    {
        return $this->createObjectMock(Logger::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LoaderInterface
     */
    protected function createBinaryLoaderInterfaceMock()
    {
        return $this->createObjectMock(LoaderInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MimeTypeGuesserInterface
     */
    protected function createMimeTypeGuesserInterfaceMock()
    {
        return $this->createObjectMock(MimeTypeGuesserInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ExtensionGuesserInterface
     */
    protected function createExtensionGuesserInterfaceMock()
    {
        return $this->createObjectMock(ExtensionGuesserInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|PostProcessorInterface
     */
    protected function createPostProcessorInterfaceMock()
    {
        return $this->createObjectMock(PostProcessorInterface::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FilterManager
     */
    protected function createFilterManagerMock()
    {
        return $this->createObjectMock(FilterManager::class, [], false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|FilterService
     */
    protected function createFilterServiceMock()
    {
        return $this->createObjectMock(FilterService::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|DataManager
     */
    protected function createDataManagerMock()
    {
        return $this->createObjectMock(DataManager::class, [], false);
    }

    /**
     * @param string|null $willReturn
     * @param string|null $willExpect
     *
     * @return ContentTypeGuesser
     */
    protected function createContentTypeGuesserMock(string $willReturn = null, string $willExpect = null): ContentTypeGuesser
    {
        /** @var MimeTypeGuesserInterface|\PHPUnit_Framework_MockObject_MockObject $guesser */
        $guesser = $this->getMockBuilder(MimeTypeGuesserInterface::class)
            ->setMethods(['guess'])
            ->getMock();

        $builder = $guesser
            ->expects($this->atLeastOnce())
            ->method('guess');

        if ($willExpect) {
            $builder->with($willExpect);
        } else {
            $builder->withAnyParameters();
        }

        $builder->willReturn($willReturn);

        return $this->createContentTypeGuesserInstance($guesser);
    }

    /**
     * @param string|null $willReturn
     * @param string|null $willExpect
     *
     * @return ExtensionGuesser
     */
    protected function createExtensionGuesserMock(string $willReturn = null, string $willExpect = null): ExtensionGuesser
    {
        /** @var ExtensionGuesserInterface|\PHPUnit_Framework_MockObject_MockObject $guesser */
        $guesser = $this->getMockBuilder(ExtensionGuesserInterface::class)
            ->setMethods(['guess'])
            ->getMock();

        $builder = $guesser
            ->expects($this->atLeastOnce())
            ->method('guess');

        if ($willExpect) {
            $builder->with($willExpect);
        } else {
            $builder->withAnyParameters();
        }

        $builder->willReturn($willReturn);

        return $this->createExtensionGuesserInstance($guesser);
    }

    /**
     * @param array ...$registrations
     *
     * @return ContentTypeGuesser
     */
    protected function createContentTypeGuesserInstance(...$registrations): ContentTypeGuesser
    {
        $guesser = new ContentTypeGuesser();

        foreach ($registrations ?: [MimeTypeGuesser::getInstance()] as $g) {
            $guesser->register($g);
        }

        return $guesser;
    }

    /**
     * @param array ...$registrations
     *
     * @return ExtensionGuesser
     */
    protected function createExtensionGuesserInstance(...$registrations): ExtensionGuesser
    {
        $guesser = new ExtensionGuesser();

        foreach ($registrations ?: [SymfonyExtensionGuesser::getInstance()] as $g) {
            $guesser->register($g);
        }

        return $guesser;
    }

    /**
     * @param ContentTypeGuesserInterface|ContentTypeGuesserInterface[] $cGuessers
     * @param ExtensionGuesserInterface|ExtensionGuesserInterface[]     $eGuessers
     *
     * @return FileAttributesResolver
     */
    protected function createFileAttributeResolverInstance($cGuessers = [], $eGuessers = []): FileAttributesResolver
    {
        return new FileAttributesResolver(
            $this->createContentTypeGuesserInstance(...array_values(is_array($cGuessers) ? $cGuessers : [$cGuessers])),
            $this->createExtensionGuesserInstance(...array_values(is_array($eGuessers) ? $eGuessers : [$eGuessers]))
        );
    }

    /**
     * @param ContentTypeGuesserInterface|ContentTypeGuesserInterface[] $cGuessers
     * @param ExtensionGuesserInterface|ExtensionGuesserInterface[]     $eGuessers
     *
     * @return FileAttributesApplier
     */
    protected function createFileAttributesApplierInstance($cGuessers = [], $eGuessers = []): FileAttributesApplier
    {
        return new FileAttributesApplier(
            $this->createFileAttributeResolverInstance(
                array_values(is_array($cGuessers) ? $cGuessers : [$cGuessers]),
                array_values(is_array($eGuessers) ? $eGuessers : [$eGuessers])
            )
        );
    }

    /**
     * @param string   $object
     * @param string[] $methods
     * @param bool     $constructorInvoke
     * @param mixed[]  $constructorParams
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function createObjectMock($object, array $methods = [], $constructorInvoke = false, array $constructorParams = []): \PHPUnit_Framework_MockObject_MockObject
    {
        $builder = $this->getMockBuilder($object);

        if (count($methods) > 0) {
            $builder->setMethods($methods);
        }

        if ($constructorInvoke) {
            $builder->enableOriginalConstructor();
        } else {
            $builder->disableOriginalConstructor();
        }

        if (count($constructorParams) > 0) {
            $builder->setConstructorArgs($constructorParams);
        }

        return $builder->getMock();
    }

    /**
     * @param object $object
     * @param string $name
     *
     * @return \ReflectionMethod
     */
    protected function getVisibilityRestrictedMethod($object, $name)
    {
        $r = new \ReflectionObject($object);

        $m = $r->getMethod($name);
        $m->setAccessible(true);

        return $m;
    }
}
