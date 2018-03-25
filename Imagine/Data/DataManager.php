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

use Liip\ImagineBundle\File\Loader\LoaderInterface;
use Liip\ImagineBundle\Exception\File\Attributes\Resolver\InvalidFileAttributesException;
use Liip\ImagineBundle\Exception\Imagine\Data\InvalidFileFoundException;
use Liip\ImagineBundle\File\FileBlob;
use Liip\ImagineBundle\File\FileInterface;
use Liip\ImagineBundle\File\Attributes\Resolver\FileAttributesResolver;
use Liip\ImagineBundle\File\Attributes\Attributes;
use Liip\ImagineBundle\File\Attributes\Resolver\FileAttributesApplier;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;

class DataManager
{
    /**
     * @var FileAttributesApplier
     */
    protected $fileAttributes;

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
     * @param FileAttributesApplier $fileAttributesApplier
     * @param string                $defaultLoader
     * @param string                $globalDefaultImage
     */
    public function __construct(
        FilterConfiguration $filterConfig,
        FileAttributesApplier $fileAttributesApplier,
        string $defaultLoader = null,
        string $globalDefaultImage = null
    ) {
        $this->filterConfig = $filterConfig;
        $this->fileAttributes = $fileAttributesApplier;
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
        $file = $this->getLoader($filter)->find($path);

        try {
            $file = $this->fileAttributes->apply($file);
        } catch (InvalidFileAttributesException $e) {
            throw new InvalidFileFoundException(sprintf('Invalid attributes resolved for "%s".', $path), 0, $e);
        }

        if (!$file->getContentType()->isMatch('image')) {
            throw new InvalidFileFoundException(sprintf(
                'Invalid content type attribute "%s" for "%s" (expected primary content type "image" but got "%s").',
                $file->getContentType()->stringify(), $path, $file->getContentType()->getType() ?: 'null'
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
