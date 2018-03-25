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

use Liip\ImagineBundle\File\FileBlobInterface;
use Liip\ImagineBundle\File\FileInterface;
use Liip\ImagineBundle\File\FilePathInterface;
use Liip\ImagineBundle\File\FileTemp;
use Liip\ImagineBundle\File\Attributes\Guesser\ContentTypeGuesserInterface;
use Liip\ImagineBundle\File\Attributes\Attributes;
use Liip\ImagineBundle\File\Attributes\ContentTypeAttribute;
use Liip\ImagineBundle\File\Attributes\ExtensionAttribute;
use Liip\ImagineBundle\File\Attributes\Guesser\ExtensionGuesserInterface;

/**
 * @author Rob Frawley 2nd <rmf@src.run>
 */
final class FileAttributesResolver
{
    /**
     * @var ContentTypeGuesserInterface
     */
    private $contentTypeGuesser;

    /**
     * @var ExtensionGuesserInterface
     */
    private $extensionGuesser;

    /**
     * @param ContentTypeGuesserInterface $contentTypeGuesser
     * @param ExtensionGuesserInterface   $extensionGuesser
     */
    public function __construct(ContentTypeGuesserInterface $contentTypeGuesser, ExtensionGuesserInterface $extensionGuesser)
    {
        $this->contentTypeGuesser = $contentTypeGuesser;
        $this->extensionGuesser = $extensionGuesser;
    }

    /**
     * @param FileInterface|string $file
     *
     * @return Attributes
     */
    public function resolve($file): Attributes
    {
        if ($file instanceof FileBlobInterface) {
            return $this->resolveFileBlob($file);
        }

        if ($file instanceof FilePathInterface) {
            return $this->resolveFilePath($file);
        }

        return $this->guess($file);
    }

    /**
     * @param FileInterface $file
     *
     * @return Attributes
     */
    public function resolveFileBlob(FileInterface $file): Attributes
    {
        $temporary = new FileTemp('file-attributes-resolver');
        $temporary->acquire();

        if ($file->hasContents()) {
            $temporary->setContents($file->getContents());
        }

        try {
            return $this->resolveFilePath($temporary);
        } finally {
            $temporary->release();
        }
    }

    /**
     * @param FilePathInterface $file
     *
     * @return Attributes
     */
    public function resolveFilePath(FilePathInterface $file): Attributes
    {
        return $this->guess($file->getFile()->getPathname());
    }

    /**
     * @param string $path
     *
     * @return Attributes
     */
    private function guess(string $path): Attributes
    {
        $c = ContentTypeAttribute::create(
            $this->contentTypeGuesser->guess($path)
        );

        $e = ExtensionAttribute::create(
            $this->extensionGuesser->guess($c->stringify())
        );

        return new Attributes($c, $e);
    }
}
