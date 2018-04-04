<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use League\Flysystem\FilesystemInterface;
use Liip\ImagineBundle\Exception\File\Loader\NotLoadableException;
use Liip\ImagineBundle\File\FileBlob;
use Liip\ImagineBundle\File\FileInterface;

class FlysystemLoader implements LoaderInterface
{
    /**
     * @var FilesystemInterface
     */
    private $filesystem;

    /**
     * @param FilesystemInterface $filesystem
     */
    public function __construct(FilesystemInterface $filesystem)
    {
        $this->filesystem = $filesystem;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $identity): FileInterface
    {
        if (false === $this->filesystem->has($identity)) {
            throw new NotLoadableException('Source image "%s" not found.', $identity);
        }

        try {
            $file = $this->filesystem->read($identity);
            $type = $this->filesystem->getMimetype($identity);
        } catch (\Exception $e) {
            throw new NotLoadableException('Failed to load "%s" from flysystem service!', $identity, $e);
        }

        return FileBlob::create($file, $type);
    }
}
