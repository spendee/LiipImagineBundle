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

use Liip\ImagineBundle\Utility\Framework\SymfonyFramework;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class WebPathResolverFactory extends AbstractResolverFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, string $name, array $config): string
    {
        $definition = $this->createChildDefinition();
        $definition->replaceArgument(2, $config['web_root']);
        $definition->replaceArgument(3, $config['cache_prefix']);

        return $this->registerFactoryDefinition($name, $definition, $container);
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder): void
    {
        $builder
            ->children()
                ->scalarNode('web_root')
                    ->defaultValue(SymfonyFramework::getContainerResolvableRootWebPath())
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('cache_prefix')
                    ->defaultValue('media/cache')
                    ->cannotBeEmpty()
                ->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'web_path';
    }
}
