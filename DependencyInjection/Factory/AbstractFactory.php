<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\DependencyInjection\Factory;

use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

abstract class AbstractFactory implements FactoryInterface
{
    /**
     * @param string|null $name
     * @param string      $type
     *
     * @return Reference
     */
    final protected function createReference(string $name = null, string $type = 'prototype'): Reference
    {
        return new Reference($this->getFactoryServiceName($name, $type));
    }

    /**
     * @param string|null $name
     *
     * @return Reference
     */
    final protected function createChildReference(string $name = null): Reference
    {
        return new Reference($this->getPrototypeFactoryServiceName($name));
    }

    /**
     * @param string|null $name
     *
     * @return ChildDefinition
     */
    final protected function createChildDefinition(string $name = null): ChildDefinition
    {
        return new ChildDefinition($this->getPrototypeFactoryServiceName($name));
    }

    /**
     * @param string|null $name
     * @param string|null $type
     * @param bool        $prefix
     *
     * @return string
     */
    final protected function getFactoryServiceName(string $name = null, string $type = null, bool $prefix = true): string
    {
        return vsprintf('%s%s%s%s', [
            static::getDefinitionNamePrefix(),
            $prefix ? sprintf('.%s', $this->getName()) : '',
            $name ? sprintf('.%s', $name) : '',
            $type ? sprintf('.%s', $type) : '',
        ]);
    }

    /**
     * @param string|null $name
     *
     * @return string
     */
    final protected function getPrototypeFactoryServiceName(string $name = null): string
    {
        return sprintf('%s.%s.prototype', static::getDefinitionNamePrefix(), $name ?: $this->getName());
    }

    /**
     * @param string           $name
     * @param Definition       $def
     * @param ContainerBuilder $container
     *
     * @return string
     */
    final protected function registerFactoryDefinition(string $name, Definition $def, ContainerBuilder $container): string
    {
        $def->addTag(static::getDefinitionNamePrefix(), [
            static::getDefinitionTagContext() => $name,
        ]);

        $def->setPublic(true);

        $container->setDefinition(
            $key = $this->getFactoryServiceName($name, null, false),
            $def
        );

        return $key;
    }

    /**
     * @return string
     */
    abstract protected static function getDefinitionNamePrefix(): string;

    /**
     * @return string
     */
    abstract protected static function getDefinitionTagContext(): string;
}
