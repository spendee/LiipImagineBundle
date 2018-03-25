<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ResolversCompilerPass extends AbstractCompilerPass
{
    /**
     * @var string
     */
    private $tagName;

    /**
     * @var string
     */
    private $service;

    /**
     * @param string $resolverTagName
     * @param string $managerServiceId
     */
    public function __construct(
        string $resolverTagName = 'liip_imagine.cache.resolver',
        string $managerServiceId = 'liip_imagine.cache.manager'
    ) {
        $this->tagName = $resolverTagName;
        $this->service = $managerServiceId;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $resolvers = $container->findTaggedServiceIds($this->tagName);

        if (0 < count($resolvers) && $container->hasDefinition($this->service)) {
            $manager = $container->getDefinition($this->service);

            foreach ($resolvers as $id => $tag) {
                $manager->addMethodCall('addResolver', [$tag[0]['resolver'], new Reference($id)]);
                $this->log($container, 'Registered cache resolver: %s', $id);
            }
        }
    }
}
