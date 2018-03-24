<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Data;

use Liip\ImagineBundle\Binary\Loader\LoaderInterface;
use Liip\ImagineBundle\File\FileBlob;
use Liip\ImagineBundle\File\FileInterface;
use Liip\ImagineBundle\File\Guesser\GuesserManager;
use Liip\ImagineBundle\File\Metadata\Metadata;
use Liip\ImagineBundle\File\Metadata\Resolver\ImageMetadataResolver;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;

class DataManager
{
    /**
     * @var ImageMetadataResolver
     */
    protected $metadataResolver;

    /**
     * @var FilterConfiguration
     */
    protected $filterConfig;

    /**
     * @var string|null
     */
    protected $defaultLoader;

    /**
     * @var string|null
     */
    protected $globalDefaultImage;

    /**
     * @var LoaderInterface[]
     */
    protected $loaders = [];

    /**
     * @param FilterConfiguration   $filterConfig
     * @param ImageMetadataResolver $metadataResolver
     * @param string                $defaultLoader
     * @param string                $globalDefaultImage
     */
    public function __construct(
        FilterConfiguration $filterConfig,
        ImageMetadataResolver $metadataResolver,
        string $defaultLoader = null,
        string $globalDefaultImage = null
    ) {
        $this->filterConfig = $filterConfig;
        $this->metadataResolver = $metadataResolver;
        $this->defaultLoader = $defaultLoader;
        $this->globalDefaultImage = $globalDefaultImage;
    }

    /**
     * Adds a loader to retrieve images for the given filter.
     *
     * @param string          $filter
     * @param LoaderInterface $loader
     */
    public function addLoader(string $filter, LoaderInterface $loader): void
    {
        $this->loaders[$filter] = $loader;
    }

    /**
     * Returns a loader previously attached to the given filter.
     *
     * @param string $filter
     *
     * @throws \InvalidArgumentException
     *
     * @return LoaderInterface
     */
    public function getLoader(string $filter): LoaderInterface
    {
        $config = $this->filterConfig->get($filter);
        $loader = empty($config['data_loader']) ? $this->defaultLoader : $config['data_loader'];

        if (!isset($this->loaders[$loader])) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find data loader "%s" for "%s" filter type',
                $loader, $filter
            ));
        }

        return $this->loaders[$loader];
    }

    /**
     * Retrieves an image with the given filter applied.
     *
     * @param string $filter
     * @param string $path
     *
     * @throws \LogicException
     *
     * @return FileInterface
     */
    public function find(string $filter, string $path): FileInterface
    {
        $file = $this
            ->metadataResolver
            ->resolve(
                $this->getLoader($filter)->find($path)
            );

        if (!$file->hasContentType()) {
            throw new \LogicException(sprintf(
                'Failed to resolve the content type of "%s".', $path
            ));
        }

        if (!$file->getContentType()->isMatch('image')) {
            throw new \LogicException(sprintf(
                'Invalid content type "%s" resolved for "%s" (expected primary type "image").',
                (string) $file->getContentType(), $path
            ));
        }

        return $file;
    }

    /**
     * Get default image url with the given filter applied.
     *
     * @param string $filter
     *
     * @return string|null
     */
    public function getDefaultImageUrl(string $filter): ?string
    {
        $config = $this
            ->filterConfig
            ->get($filter);

        if (!empty($config['default_image'])) {
            return $config['default_image'];
        }

        if (!empty($this->globalDefaultImage)) {
            return $this->globalDefaultImage;
        }

        return null;
    }
}
