<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Binary\Loader;

use League\Flysystem\FilesystemInterface;
use Liip\ImagineBundle\Exception\Binary\Loader\NotLoadableException;
use Liip\ImagineBundle\File\FileContent;
use Liip\ImagineBundle\File\Guesser\GuesserManager;
use Liip\ImagineBundle\File\Metadata\ContentTypeMetadata;

class FlysystemLoader implements LoaderInterface
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @var GuesserManager
     */
    private $guesserManager;

    /**
     * @param FilesystemInterface $filesystem
     * @param GuesserManager      $guesserManager
     */
    public function __construct(FilesystemInterface $filesystem, GuesserManager $guesserManager)
    {
        $this->filesystem = $filesystem;
        $this->guesserManager = $guesserManager;
    }

    /**
     * {@inheritdoc}
     */
    public function find($path)
    {
        if (false === $this->filesystem->has($path)) {
            throw new NotLoadableException(sprintf('Source image "%s" not found.', $path));
        }

        try {
            $file = $this->filesystem->read($path);
            $type = ContentTypeMetadata::create($this->filesystem->getMimetype($path));
        } catch (\Exception $e) {
            throw new NotLoadableException(sprintf('Failed to load "%s" from flysystem service!', $path), 0, $e);
        }

        return new FileContent($file, ContentTypeMetadata::create($type), $this->guesserManager->guessExtension($type));
    }
}
