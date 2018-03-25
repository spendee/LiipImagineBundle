<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Filter;

use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Liip\ImagineBundle\File\FileBlob;
use Liip\ImagineBundle\File\FileInterface;
use Liip\ImagineBundle\File\Attributes\Resolver\FileAttributesResolver;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;
use Liip\ImagineBundle\Imagine\Filter\PostProcessor\PostProcessorInterface;

class FilterManager
{
    /**
     * @var FilterConfiguration
     */
    protected $filterConfig;

    /**
     * @var ImagineInterface
     */
    protected $imagine;

    /**
     * @var FileAttributesResolver
     */
    protected $guesserManager;

    /**
     * @var LoaderInterface[]
     */
    protected $loaders = [];

    /**
     * @var PostProcessorInterface[]
     */
    protected $postProcessors = [];

    /**
     * @param FilterConfiguration    $filterConfig
     * @param ImagineInterface       $imagine
     * @param FileAttributesResolver $guesserManager
     */
    public function __construct(
        FilterConfiguration $filterConfig,
        ImagineInterface $imagine,
        FileAttributesResolver $guesserManager
    ) {
        $this->filterConfig = $filterConfig;
        $this->imagine = $imagine;
        $this->guesserManager = $guesserManager;
    }

    /**
     * Adds a loader to handle the given filter.
     *
     * @param string          $filter
     * @param LoaderInterface $loader
     */
    public function addLoader(string $filter, LoaderInterface $loader): void
    {
        $this->loaders[$filter] = $loader;
    }

    /**
     * Adds a post-processor to handle binaries.
     *
     * @param string                 $name
     * @param PostProcessorInterface $postProcessor
     */
    public function addPostProcessor(string $name, PostProcessorInterface $postProcessor): void
    {
        $this->postProcessors[$name] = $postProcessor;
    }

    /**
     * @return FilterConfiguration
     */
    public function getFilterConfiguration(): FilterConfiguration
    {
        return $this->filterConfig;
    }

    /**
     * @param FileInterface $file
     * @param array         $config
     *
     * @throws \InvalidArgumentException
     *
     * @return FileInterface
     */
    public function apply(FileInterface $file, array $config): FileInterface
    {
        $config += [
            'quality' => 100,
            'animated' => false,
        ];

        return $this->applyPostProcessors($this->applyFilters($file, $config), $config);
    }

    /**
     * @param FileInterface $file
     * @param array         $config
     *
     * @return FileInterface
     */
    public function applyFilters(FileInterface $file, array $config): FileInterface
    {
        $image = $this->imagine->load($file->getContents());

        foreach ($this->sanitizeFilters($config['filters'] ?? []) as $name => $options) {
            $prior = $image;
            $image = $this->loaders[$name]->load($image, $options);

            if ($prior !== $image) {
                $this->destroyImage($prior);
            }
        }

        return $this->exportConfiguredImageBinary($file, $image, $config);
    }

    /**
     * Apply the provided filter set on the given binary.
     *
     * @param FileInterface $file
     * @param string        $filter
     * @param array         $runtimeConfig
     *
     * @throws \InvalidArgumentException
     *
     * @return FileInterface
     */
    public function applyFilter(FileInterface $file, $filter, array $runtimeConfig = [])
    {
        $config = array_replace_recursive(
            $this->getFilterConfiguration()->get($filter),
            $runtimeConfig
        );

        return $this->apply($file, $config);
    }

    /**
     * @param FileInterface $file
     * @param array         $config
     *
     * @throws \InvalidArgumentException
     *
     * @return FileInterface
     */
    public function applyPostProcessors(FileInterface $file, array $config): FileInterface
    {
        foreach ($this->sanitizePostProcessors($config['post_processors'] ?? []) as $name => $options) {
            $file = $this->postProcessors[$name]->process($file, $options);
        }

        return $file;
    }

    /**
     * @param FileInterface  $file
     * @param ImageInterface $image
     * @param array          $config
     *
     * @return FileInterface
     */
    private function exportConfiguredImageBinary(FileInterface $file, ImageInterface $image, array $config): FileInterface
    {
        $options = ['quality' => $config['quality']];

        if (isset($config['jpeg_quality'])) {
            $options['jpeg_quality'] = $config['jpeg_quality'];
        }

        if (isset($config['png_compression_level'])) {
            $options['png_compression_level'] = $config['png_compression_level'];
        }

        if (isset($config['png_compression_filter'])) {
            $options['png_compression_filter'] = $config['png_compression_filter'];
        }

        if ($file->getExtension()->isMatch('gif') && $config['animated'] ?? false) {
            $options['animated'] = $config['animated'];
        }

        $filterDataTyped = $file->getContentType();
        $filterExtension = $config['format'] ?? (string) $file->getExtension();
        $filterImageBlob = $image->get($filterExtension, $options);

        if ($filterExtension !== (string) $file->getExtension()) {
            $filterDataTyped = $this
                ->guesserManager
                ->resolveFileBlob(FileBlob::create($filterImageBlob))
                ->getContentType();
        }

        $this->destroyImage($image);

        return FileBlob::create($filterImageBlob, $filterDataTyped, $filterExtension);
    }

    /**
     * @param array $filters
     *
     * @return array
     */
    private function sanitizeFilters(array $filters): array
    {
        $sanitized = array_filter($filters, function (string $name): bool {
            return isset($this->loaders[$name]);
        }, ARRAY_FILTER_USE_KEY);

        if (count($filters) !== count($sanitized)) {
            throw new \InvalidArgumentException(sprintf('Could not find filter(s): %s', implode(', ', array_map(function (string $name): string {
                return sprintf('"%s"', $name);
            }, array_diff(array_keys($filters), array_keys($sanitized))))));
        }

        return $sanitized;
    }

    /**
     * @param array $processors
     *
     * @return array
     */
    private function sanitizePostProcessors(array $processors): array
    {
        $sanitized = array_filter($processors, function (string $name): bool {
            return isset($this->postProcessors[$name]);
        }, ARRAY_FILTER_USE_KEY);

        if (count($processors) !== count($sanitized)) {
            throw new \InvalidArgumentException(sprintf('Could not find post processor(s): %s', implode(', ', array_map(function (string $name): string {
                return sprintf('"%s"', $name);
            }, array_diff(array_keys($processors), array_keys($sanitized))))));
        }

        return $sanitized;
    }

    /**
     * We are done with the image object so we can destruct the this because imagick keeps consuming memory if we don't.
     * See https://github.com/liip/LiipImagineBundle/pull/682
     *
     * @param ImageInterface $image
     */
    private function destroyImage(ImageInterface $image): void
    {
        if (method_exists($image, '__destruct')) {
            $image->__destruct();
        }
    }
}
