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

use Liip\ImagineBundle\DependencyInjection\Compiler\AttributeGuessersCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\FiltersCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\LoadersCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\LoggerAwareCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\MetadataReaderCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\PostProcessorsCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Compiler\ResolversCompilerPass;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\FileSystemLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\FlysystemLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Loader\StreamLoaderFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\AwsS3ResolverFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\FlysystemResolverFactory;
use Liip\ImagineBundle\DependencyInjection\Factory\Resolver\WebPathResolverFactory;
use Liip\ImagineBundle\DependencyInjection\LiipImagineExtension;
use Liip\ImagineBundle\LiipImagineBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @covers \Liip\ImagineBundle\LiipImagineBundle
 */
class LiipImagineBundleTest extends AbstractTest
{
    public function testInstanceOfBundle()
    {
        $this->assertInstanceOf(Bundle::class, new LiipImagineBundle());
    }

    /**
     * @return \Iterator
     */
    public function provideAddCompilerPassOnBuildData(): \Iterator
    {
        yield [0, LoggerAwareCompilerPass::class];
        yield [1, AttributeGuessersCompilerPass::class];
        yield [2, LoadersCompilerPass::class];
        yield [3, FiltersCompilerPass::class];
        yield [4, PostProcessorsCompilerPass::class];
        yield [5, ResolversCompilerPass::class];
        yield [6, MetadataReaderCompilerPass::class];
    }

    /**
     * @dataProvider provideAddCompilerPassOnBuildData
     *
     * @param int    $expectedPosition
     * @param string $expectedInstance
     */
    public function testAddCompilerPassOnBuild(int $expectedPosition, string $expectedInstance)
    {
        $container = $this->createContainerBuilderMock();

        $container
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->will($this->returnValue($this->createLiipImagineExtensionMock()));

        $container
            ->expects($this->at($expectedPosition))
            ->method('addCompilerPass')
            ->with($this->isInstanceOf($expectedInstance));

        $bundle = new LiipImagineBundle();
        $bundle->build($container);
    }

    /**
     * @return \Iterator
     */
    public function provideAddExtensionFactoryOnBuildData(): \Iterator
    {
        yield [0, 'addResolverFactory', WebPathResolverFactory::class];
        yield [1, 'addResolverFactory', AwsS3ResolverFactory::class];
        yield [2, 'addResolverFactory', FlysystemResolverFactory::class];
        yield [3, 'addLoaderFactory', StreamLoaderFactory::class];
        yield [4, 'addLoaderFactory', FileSystemLoaderFactory::class];
        yield [5, 'addLoaderFactory', FlysystemLoaderFactory::class];
    }

    /**
     * @dataProvider provideAddExtensionFactoryOnBuildData
     *
     * @param int    $expectedPosition
     * @param string $expectedInstance
     */
    public function testAddExtensionFactoryOnBuild(int $expectedPosition, string $expectedMethod, string $expectedInstance)
    {
        $extension = $this->createLiipImagineExtensionMock();
        $extension
            ->expects($this->at($expectedPosition))
            ->method($expectedMethod)
            ->with($this->isInstanceOf($expectedInstance));

        $container = $this->createContainerBuilderMock();
        $container
            ->expects($this->atLeastOnce())
            ->method('getExtension')
            ->with('liip_imagine')
            ->will($this->returnValue($extension));

        $bundle = new LiipImagineBundle();
        $bundle->build($container);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ContainerBuilder
     */
    private function createContainerBuilderMock()
    {
        return $this->createObjectMock(ContainerBuilder::class, [], false);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|LiipImagineExtension
     */
    private function createLiipImagineExtensionMock()
    {
        return $this->createObjectMock(LiipImagineExtension::class, [
            'getNamespace',
            'addResolverFactory',
            'addLoaderFactory',
        ], false);
    }
}
