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

use Liip\ImagineBundle\File\FileInterface;
use Symfony\Component\Routing\RequestContext;

class NoCacheWebPathResolver implements ResolverInterface
{
    /**
     * @var RequestContext
     */
    private $requestContext;

    /**
     * @param RequestContext $requestContext
     */
    public function __construct(RequestContext $requestContext)
    {
        $this->requestContext = $requestContext;
    }

    /**
     * {@inheritdoc}
     */
    public function isStored(string $path, string $filter): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $path, string $filter): string
    {
        return sprintf('%s://%s/%s',
            $this->requestContext->getScheme(),
            $this->requestContext->getHost(),
            ltrim($path, '/')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function store(FileInterface $file, string $path, string $filter): void
    {
    }

    /**
     * {@inheritdoc}
     */
    public function remove(array $paths, array $filters): void
    {
    }
}
