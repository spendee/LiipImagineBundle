<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Functional\app;

use Liip\ImagineBundle\LiipImagineBundle;
use Liip\ImagineBundle\Tests\Functional\Fixtures\BarBundle\LiipBarBundle;
use Liip\ImagineBundle\Tests\Functional\Fixtures\FooBundle\LiipFooBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    /**
     * @return array
     */
    public function registerBundles()
    {
        $bundles = [
            new FrameworkBundle(),
            new LiipImagineBundle(),
            new LiipFooBundle(),
            new LiipBarBundle(),
        ];

        return $bundles;
    }

    /**
     * @return string
     */
    public function getCacheDir()
    {
        return sys_get_temp_dir().'/liip_imagine_test/cache';
    }

    /**
     * @return string
     */
    public function getLogDir()
    {
        return sys_get_temp_dir().'/liip_imagine_test/cache/logs';
    }

    /**
     * @param \Symfony\Component\Config\Loader\LoaderInterface $loader
     *
     * @throws \Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config.yml');
    }
}
