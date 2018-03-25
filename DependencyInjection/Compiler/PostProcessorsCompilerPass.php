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

/**
 * Compiler pass to register post_processors tagged with liip_imagine.filter.post_processor.
 *
 * @author Konstantin Tjuterev <kostik.lv@gmail.com>
 */
class PostProcessorsCompilerPass extends AbstractCompilerPass
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
     * @param string $filterTagName
     * @param string $managerIdName
     */
    public function __construct(
        string $filterTagName = 'liip_imagine.filter.post_processor',
        string $managerIdName = 'liip_imagine.filter.manager'
    ) {
        $this->tagName = $filterTagName;
        $this->service = $managerIdName;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $filters = $container->findTaggedServiceIds($this->tagName);

        if (0 < count($filters) && $container->hasDefinition($this->service)) {
            $manager = $container->getDefinition($this->service);

            foreach ($filters as $id => $tag) {
                $manager->addMethodCall('addPostProcessor', [$tag[0]['post_processor'], new Reference($id)]);
                $this->log($container, 'Registered filter post-processor: %s', $id);
            }
        }
    }
}
