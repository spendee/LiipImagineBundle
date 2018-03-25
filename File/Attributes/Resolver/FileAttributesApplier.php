<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\File\Attributes\Resolver;

use Liip\ImagineBundle\Exception\File\Attributes\Resolver\InvalidFileAttributesException;
use Liip\ImagineBundle\File\FileBlob;
use Liip\ImagineBundle\File\FileBlobInterface;
use Liip\ImagineBundle\File\FileInterface;
use Liip\ImagineBundle\File\FilePath;
use Liip\ImagineBundle\File\FilePathInterface;
use Liip\ImagineBundle\File\Attributes\ContentTypeAttribute;
use Liip\ImagineBundle\File\Attributes\ExtensionAttribute;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;

/**
 * @author Rob Frawley 2nd <rmf@src.run>
 */
final class FileAttributesApplier
{
    use LoggerAwareTrait;

    /**
     * @var FileAttributesResolver
     */
    private $resolver;

    /**
     * @param FileAttributesResolver $resolver
     */
    public function __construct(FileAttributesResolver $resolver)
    {
        $this->resolver = $resolver;
        $this->logger = new NullLogger();
    }

    /**
     * @param FileInterface|FilePathInterface $file
     *
     * @return FileInterface|FilePathInterface
     */
    public function apply(FileInterface $file): FileInterface
    {
        if (!$file->hasContentType()) {
            $attr = $this->resolver->resolve($file);

            return $this->assignFileAttributes(
                $file,
                $attr->getContentType(),
                $attr->getExtension()
            );
        }

        if (!$file->hasExtension()) {
            $attr = $this->resolver->resolve($file);

            return $this->assignFileAttributes(
                $file,
                $file->getContentType(),
                $attr->getExtension()
            );
        }

        return $file;
    }

    /**
     * @param FileInterface|FileBlobInterface|FilePathInterface $file
     * @param ContentTypeAttribute                              $contentType
     * @param ExtensionAttribute                                $extension
     *
     * @return FileInterface|FileBlobInterface|FilePathInterface
     */
    private function assignFileAttributes(FileInterface $file, ContentTypeAttribute $contentType, ExtensionAttribute $extension): FileInterface
    {
        if (false === $contentType->isValid()) {
            $this->logger->error($m = sprintf(
                'Unable to resolve content type attribute for file %s.',
                $file->hasFile() ? $file->getFile()->getPathname() : 'blob'
            ));

            throw new InvalidFileAttributesException($m);
        }

        if (false === $extension->isValid()) {
            $this->logger->error($m = sprintf(
                'Unable to resolve extension attribute for file %s.',
                $file->hasFile() ? $file->getFile()->getPathname() : 'blob'
            ));

            throw new InvalidFileAttributesException($m);
        }

        return $file instanceof FilePathInterface
            ? new FilePath($file->getFile(), $contentType, $extension)
            : new FileBlob($file->getContents(), $contentType, $extension);
    }
}
