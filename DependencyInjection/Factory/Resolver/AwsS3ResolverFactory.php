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

use Aws\S3\S3Client;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class AwsS3ResolverFactory extends AbstractResolverFactory
{
    /**
     * {@inheritdoc}
     */
    public function create(ContainerBuilder $container, string $name, array $config): string
    {
        $clientRef = $this->createReference($name, 'client');
        $clientDef = (new Definition(S3Client::class))
            ->setFactory([S3Client::class, 'factory'])
            ->addArgument($this->createClientConfigArguments($config['client_config']))
            ->setShared(false);

        $container->setDefinition($clientRef, $clientDef);

        $resolverKey = $this->getFactoryServiceName($name, null);
        $resolverDef = $this->createChildDefinition();
        $resolverDef->replaceArgument(0, $clientRef);
        $resolverDef->replaceArgument(1, $config['bucket']);
        $resolverDef->replaceArgument(2, $config['acl']);
        $resolverDef->replaceArgument(3, $config['get_options']);
        $resolverDef->replaceArgument(4, $config['put_options']);
        $resolverDef->replaceArgument(5, $config['del_options']);

        if (isset($config['cache_prefix'])) {
            $resolverDef->replaceArgument(6, $config['cache_prefix']);
        }

        $container->setDefinition($resolverKey, $resolverDef);

        if ($config['proxies']) {
            $proxyRef = $this->createReference($name, 'proxy');
            $container->setDefinition($proxyRef, $resolverDef);

            $proxyDef = $this->createChildDefinition('proxy');
            $proxyDef->replaceArgument(0, $proxyRef);
            $proxyDef->replaceArgument(1, $config['proxies']);
            $container->setDefinition($resolverKey, $proxyDef);
        }

        if ($config['cache']) {
            $cacheRef = $this->createReference($name, 'cache');
            $container->setDefinition($cacheRef, $container->getDefinition($resolverKey));

            $cacheDef = $this->createChildDefinition('cache');
            $cacheDef->replaceArgument(0, new Reference($config['cache']));
            $cacheDef->replaceArgument(1, new Reference($cacheRef));
            $container->setDefinition($resolverKey, $cacheDef);
        }

        $container->getDefinition($resolverKey)->addTag('liip_imagine.cache.resolver', [
            'resolver' => $name,
        ]);

        return $resolverKey;
    }

    /**
     * {@inheritdoc}
     */
    public function addConfiguration(ArrayNodeDefinition $builder): void
    {
        $builder
            ->children()
                ->scalarNode('bucket')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('client_config')
                    ->isRequired()
                    ->children()
                        ->arrayNode('credentials')
                            ->variablePrototype()
                                ->isRequired()
                            ->end()
                        ->end()
                        ->scalarNode('version')
                            ->defaultValue('2016-03-01')
                        ->end()
                        ->scalarNode('region')->end()
                        ->arrayNode('extras')
                            ->arrayPrototype()->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('cache')
                    ->defaultValue(false)
                ->end()
                ->scalarNode('acl')
                    ->defaultValue('public-read')
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('cache_prefix')
                    ->defaultValue(null)
                ->end()
                ->arrayNode('get_options')
                    ->useAttributeAsKey('key')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('put_options')
                    ->useAttributeAsKey('key')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('del_options')
                    ->useAttributeAsKey('key')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('proxies')
                    ->defaultValue([])
                    ->useAttributeAsKey('name')
                    ->prototype('scalar')->end()
                ->end()
            ->end();
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'aws_s3';
    }

    /**
     * @param array $config
     *
     * @return array
     */
    private function createClientConfigArguments(array $config): array
    {
        $arguments = [
            'credentials' => $config['credentials'],
        ];

        if (isset($config['version'])) {
            $arguments['version'] = $config['version'];
        }

        if (isset($config['region'])) {
            $arguments['region'] = $config['region'];
        }

        if (isset($config['extras']) && 0 < count($config['extras'])) {
            $arguments = array_merge($arguments, $config['extras']);
        }

        return $arguments;
    }
}
