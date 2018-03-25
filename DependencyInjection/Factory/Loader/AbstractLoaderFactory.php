<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\DependencyInjection\Factory\Loader;

use Liip\ImagineBundle\DependencyInjection\Factory\AbstractFactory;

abstract class AbstractLoaderFactory extends AbstractFactory implements LoaderFactoryInterface
{
    /**
     * @return string
     */
    protected static function getDefinitionNamePrefix(): string
    {
        return 'liip_imagine.binary.loader';
    }

    /**
     * @return string
     */
    protected static function getDefinitionTagContext(): string
    {
        return 'loader';
    }
}
