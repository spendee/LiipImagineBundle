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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FlysystemResolverFactory extends AbstractResolverFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, string $name, array $config): string
    {
        $definition = $this->createChildDefinition();
        $definition->replaceArgument(0, new Reference($config['filesystem_service']));
        $definition->replaceArgument(2, $config['root_url']);
        $definition->replaceArgument(3, $config['cache_prefix']);
        $definition->replaceArgument(4, $config['visibility']);

        return $this->registerFactoryDefinition($name, $definition, $container);
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder): void
    {
        $builder
            ->children()
                ->scalarNode('filesystem_service')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('cache_prefix')
                    ->defaultValue(null)
                ->end()
                ->scalarNode('root_url')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->enumNode('visibility')
                    ->values(['public', 'private'])
                    ->defaultValue('public')
                ->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'flysystem';
    }
}
