<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotResolvableException;
use Liip\ImagineBundle\File\FileInterface;

interface ResolverInterface
{
    /**
     * Checks whether the given path is stored within this Resolver.
     *
     * @param string $path
     * @param string $filter
     *
     * @return bool
     */
    public function isStored(string $path, string $filter): bool;

    /**
     * Resolves filtered path for rendering in the browser.
     *
     * @param string $path   The path where the original file is expected to be
     * @param string $filter The name of the imagine filter in effect
     *
     * @throws NotResolvableException
     *
     * @return string The absolute URL of the cached image
     */
    public function resolve(string $path, string $filter): string;

    /**
     * Stores the content of the given binary.
     *
     * @param FileInterface $file   The image binary to store
     * @param string        $path   The path where the original file is expected to be
     * @param string        $filter The name of the imagine filter in effect
     */
    public function store(FileInterface $file, string $path, string $filter): void;

    /**
     * @param string[] $paths   The paths where the original files are expected to be
     * @param string[] $filters The imagine filters in effect
     */
    public function remove(array $paths, array $filters): void;
}
