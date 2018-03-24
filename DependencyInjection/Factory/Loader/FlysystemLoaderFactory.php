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

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class FlysystemLoaderFactory extends AbstractLoaderFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, string $name, array $config): string
    {
        $definition = $this->getChildLoaderDefinition();
        $definition->replaceArgument(0, new Reference($config['filesystem_service']));

        return $this->setTaggedLoaderDefinition($name, $definition, $container);
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'flysystem';
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
            ->end();
    }
}
