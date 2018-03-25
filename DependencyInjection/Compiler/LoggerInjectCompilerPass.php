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
 * @author Rob Frawley 2nd <rmf@src.run>
 */
class LoggerInjectCompilerPass extends AbstractCompilerPass
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
     * @param string $loggerTagName
     * @param string $loggerService
     */
    public function __construct(
        string $loggerTagName = 'liip_imagine.logger_inject',
        string $loggerService = 'liip_imagine.logger'
    ) {
        $this->tagName = $loggerTagName;
        $this->service = $loggerService;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        foreach ($container->findTaggedServiceIds($this->tagName) as $id => $tag) {
            $container->getDefinition($id)->addMethodCall('setLogger', [new Reference($this->service)]);
            $this->log($container, 'Injecting logger for service: %s', $id);
        }
    }
}
