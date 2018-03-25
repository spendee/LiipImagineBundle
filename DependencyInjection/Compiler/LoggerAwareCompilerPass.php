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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Reference;

class LoggerAwareCompilerPass extends AbstractCompilerPass
{
    /**
     * @var string
     */
    private $tagName;

    /**
     * @param string $loggerAwareTagName
     */
    public function __construct(string $loggerAwareTagName = 'liip_imagine.logger_aware')
    {
        $this->tagName = $loggerAwareTagName;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $aware = $container->findTaggedServiceIds($this->tagName);

        foreach ($aware as $id => $tag) {
            $container->getDefinition($id)->addMethodCall('setLogger', [new Reference(
                'logger', ContainerInterface::IGNORE_ON_INVALID_REFERENCE
            )]);
            $this->log($container, 'Setting logger for service: %s', $id);
        }
    }
}
