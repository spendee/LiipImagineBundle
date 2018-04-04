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

use Liip\ImagineBundle\Exception\InvalidArgumentException;
use Liip\ImagineBundle\File\Attributes\Guesser\ContentTypeGuesserInterface;
use Liip\ImagineBundle\File\Attributes\Guesser\ExtensionGuesserInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface as SymfonyExtensionGuesserInterface;

/**
 * @author Rob Frawley 2nd <rmf@src.run>
 */
class AttributeGuessersCompilerPass extends AbstractCompilerPass
{
    use PriorityTaggedServiceTrait;

    /**
     * @var string
     */
    private $tagNameFormat;

    /**
     * @var string
     */
    private $serviceFormat;

    /**
     * @param string $guesserTagNameFormat
     * @param string $proxyServiceIdFormat
     */
    public function __construct(
        string $guesserTagNameFormat = 'liip_imagine.file_attributes.guesser.%s',
        string $proxyServiceIdFormat = 'liip_imagine.file_attributes.guesser_proxy.%s'
    ) {
        $this->tagNameFormat = $guesserTagNameFormat;
        $this->serviceFormat = $proxyServiceIdFormat;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $this->processGuesserProxyContext(
            $container,
            'content_type',
            [ContentTypeGuesserInterface::class, MimeTypeGuesserInterface::class]
        );

        $this->processGuesserProxyContext(
            $container,
            'extension',
            [ExtensionGuesserInterface::class, SymfonyExtensionGuesserInterface::class]
        );
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $context
     * @param string[]         $interfaces
     */
    private function processGuesserProxyContext(ContainerBuilder $container, string $context, array $interfaces): void
    {
        if ($container->hasDefinition($id = sprintf($this->serviceFormat, $context))) {
            $proxy = $container->getDefinition($id);

            foreach ($this->findAndSortTaggedServices(sprintf($this->tagNameFormat, $context), $container) as $r) {
                $this->registerGuesserWithProxy(
                    $container,
                    $context,
                    $proxy,
                    $r,
                    $interfaces
                );
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $context
     * @param Definition       $proxy
     * @param Reference        $guesser
     * @param string[]         $interfaces
     */
    private function registerGuesserWithProxy(ContainerBuilder $container, string $context, Definition $proxy, Reference $guesser, array $interfaces): void
    {
        $reflection = $this->getReflection($container, $guesser);
        $interfaces = array_filter($interfaces, function (string $interface) use ($reflection) {
            return $reflection->implementsInterface($interface);
        });

        if (empty($interfaces)) {
            throw new InvalidArgumentException(
                'Class "%s" used for %s guesser service "%s" must implement one of "%s".',
                $reflection->getName(), $context, (string) $guesser, implode(', ', $interfaces)
            );
        }

        $proxy->addMethodCall('register', [$guesser]);
        $this->log($container, 'Registered %s attribute guesser: %s', $context, (string) $guesser);
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $id
     *
     * @return string
     */
    private function getClassName(ContainerBuilder $container, string $id): string
    {
        return $container
            ->getParameterBag()
            ->resolveValue(
                $container->getDefinition($id)->getClass()
            );
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $id
     *
     * @return \ReflectionClass
     */
    private function getReflection(ContainerBuilder $container, string $id): \ReflectionClass
    {
        $class = $this->getClassName($container, $id);

        if (null !== $reflection = $container->getReflectionClass($class, false)) {
            return $reflection;
        }

        throw new InvalidArgumentException('Class "%s" used for service "%s" cannot be found.', $class, $id);
    }
}
