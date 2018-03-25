<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\DependencyInjection\Factory\Resolver;

use Liip\ImagineBundle\DependencyInjection\Factory\AbstractFactory;

abstract class AbstractResolverFactory extends AbstractFactory implements ResolverFactoryInterface
{
    /**
     * @return string
     */
    protected static function getDefinitionNamePrefix(): string
    {
        return 'liip_imagine.cache.resolver';
    }

    /**
     * @return string
     */
    protected static function getDefinitionTagContext(): string
    {
        return 'resolver';
    }
}
