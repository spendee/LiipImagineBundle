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
use Liip\ImagineBundle\File\FileContent;
use Liip\ImagineBundle\File\FileInterface;
use Liip\ImagineBundle\File\Guesser\GuesserManager;
use Liip\ImagineBundle\Imagine\Filter\FilterConfiguration;

class DataManager
{
    /**
     * @var GuesserManager
     */
    protected $guesserManager;

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
     * @param FilterConfiguration $filterConfig
     * @param GuesserManager      $guesserManager
     * @param string              $defaultLoader
     * @param string              $globalDefaultImage
     */
    public function __construct(
        FilterConfiguration $filterConfig,
        GuesserManager $guesserManager,
        $defaultLoader = null,
        $globalDefaultImage = null
    ) {
        $this->filterConfig = $filterConfig;
        $this->guesserManager = $guesserManager;
        $this->defaultLoader = $defaultLoader;
        $this->globalDefaultImage = $globalDefaultImage;
    }

    /**
     * Adds a loader to retrieve images for the given filter.
     *
     * @param string          $filter
     * @param LoaderInterface $loader
     */
    public function addLoader($filter, LoaderInterface $loader)
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
    public function getLoader($filter)
    {
        $config = $this->filterConfig->get($filter);

        $loaderName = empty($config['data_loader']) ? $this->defaultLoader : $config['data_loader'];

        if (!isset($this->loaders[$loaderName])) {
            throw new \InvalidArgumentException(sprintf(
                'Could not find data loader "%s" for "%s" filter type',
                $loaderName,
                $filter
            ));
        }

        return $this->loaders[$loaderName];
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
    public function find($filter, $path)
    {
        $loader = $this->getLoader($filter);

        $file = $loader->find($path);
        if (!$file instanceof FileInterface) {
            $meta = $this->guesserManager->guessUsingContent($file);
            $file = new FileContent($file, $meta->contentType(), $meta->extension());
        }

        if (!$file->hasContentType()) {
            throw new \LogicException(sprintf('The mime type of image %s was not guessed.', $path));
        }

        if (0 !== mb_strpos($file->contentType(), 'image/')) {
            throw new \LogicException(sprintf('The mime type of image %s must be image/xxx got %s.', $path, $file->contentType()));
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
    public function getDefaultImageUrl($filter)
    {
        $config = $this->filterConfig->get($filter);

        $defaultImage = null;
        if (false === empty($config['default_image'])) {
            $defaultImage = $config['default_image'];
        } elseif (!empty($this->globalDefaultImage)) {
            $defaultImage = $this->globalDefaultImage;
        }

        return $defaultImage;
    }
}
