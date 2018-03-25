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

class FiltersCompilerPass extends AbstractCompilerPass
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
     * @param string $loadersTagName
     * @param string $managerService
     */
    public function __construct(
        string $loadersTagName = 'liip_imagine.filter.loader',
        string $managerService = 'liip_imagine.filter.manager'
    ) {
        $this->tagName = $loadersTagName;
        $this->service = $managerService;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $loaders = $container->findTaggedServiceIds($this->tagName);

        if (0 < count($loaders) && $container->hasDefinition($this->service)) {
            $manager = $container->getDefinition($this->service);

            foreach ($loaders as $id => $tag) {
                $manager->addMethodCall('addLoader', [$tag[0]['loader'], new Reference($id)]);
                $this->log($container, 'Registered filter loader: %s', $id);
            }
        }
    }
}
