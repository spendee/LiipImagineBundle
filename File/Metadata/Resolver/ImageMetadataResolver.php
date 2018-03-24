<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\File\Metadata\Resolver;

use Liip\ImagineBundle\File\AbstractFilePath;
use Liip\ImagineBundle\File\FileBlob;
use Liip\ImagineBundle\File\FileInterface;
use Liip\ImagineBundle\File\FilePath;
use Liip\ImagineBundle\File\FilePathInterface;
use Liip\ImagineBundle\File\Guesser\GuesserManager;
use Liip\ImagineBundle\File\Metadata\MimeTypeMetadata;
use Liip\ImagineBundle\File\Metadata\ExtensionMetadata;
use Liip\ImagineBundle\File\Metadata\Metadata;

/**
 * @author Rob Frawley 2nd <rmf@src.run>
 */
final class ImageMetadataResolver
{
    /**
     * @var GuesserManager
     */
    private $guesserManager;

    /**
     * @param GuesserManager $guesserManager
     */
    public function __construct(GuesserManager $guesserManager)
    {
        $this->guesserManager = $guesserManager;
    }

    /**
     * @param FileInterface|FilePathInterface $file
     *
     * @return FileInterface|FilePathInterface
     */
    public function resolve(FileInterface $file): FileInterface
    {
        if (!$file->hasContentType()) {
            $meta = $this->resolveMetadata($file);

            return $this->replaceMetadata(
                $file,
                $meta->getContentType(),
                $meta->getExtension()
            );
        }

        if (!$file->hasExtension()) {
            return $this->replaceMetadata(
                $file,
                $file->getContentType(),
                $this->resolveMetadata($file)->getExtension()
            );
        }

        return $file;
    }

    /**
     * @param FileInterface|FilePathInterface $file
     *
     * @return Metadata
     */
    private function resolveMetadata(FileInterface $file): Metadata
    {
        if ($file instanceof AbstractFilePath) {
            return $this
                ->guesserManager
                ->guessUsingPath($file->getFile()->getPathname());
        }

        return $this
            ->guesserManager
            ->guessUsingContent($file->getContents());
    }

    /**
     * @param FileInterface|FilePathInterface $file
     * @param MimeTypeMetadata                $contentType
     * @param ExtensionMetadata               $extension
     *
     * @return FileInterface|FilePathInterface
     */
    private function replaceMetadata(FileInterface $file, MimeTypeMetadata $contentType, ExtensionMetadata $extension): FileInterface
    {
        if ($file instanceof AbstractFilePath) {
            return new FilePath($file->getFile(), $contentType, $extension);
        }

        return new FileBlob($file->getContents(), $contentType, $extension);
    }
}
