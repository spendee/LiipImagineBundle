<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\File\Guesser;

use Liip\ImagineBundle\File\Metadata\ContentTypeMetadata;
use Liip\ImagineBundle\File\Metadata\ExtensionMetadata;
use Liip\ImagineBundle\File\Metadata\LocationMetadata;
use Liip\ImagineBundle\File\Metadata\Metadata;
use Liip\ImagineBundle\File\FileReferenceTemporary;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * @author Rob Frawley 2nd <rmf@src.run>
 */
final class GuesserManager
{
    /**
     * @var ContentTypeGuesser
     */
    private $contentTypeGuesser;

    /**
     * @var ExtensionGuesser
     */
    private $extensionGuesser;

    /**
     * @param ContentTypeGuesser $contentTypeGuesser
     * @param ExtensionGuesser   $extensionGuesser
     */
    public function __construct(
        ContentTypeGuesser $contentTypeGuesser,
        ExtensionGuesser $extensionGuesser
    ) {
        $this->contentTypeGuesser = $contentTypeGuesser;
        $this->extensionGuesser = $extensionGuesser;
    }

    /**
     * @param string $path
     *
     * @return Metadata
     */
    public function guessUsingPath(string $path): Metadata
    {
        return new Metadata(
            LocationMetadata::create($path),
            $type = $this->guessContentType($path),
            $this->guessExtension($type)
        );
    }

    /**
     * @param string|null $contents
     *
     * @return Metadata
     */
    public function guessUsingContent(string $contents = null): Metadata
    {
        $temporary = (new FileReferenceTemporary('guesser-manager'))->acquire();

        if (null !== $contents) {
            $temporary->setContents($contents);
        }

        try {
            return $this->guessUsingPath($temporary->file()->getPathname());
        } finally {
            $temporary->release();
        }
    }

    /**
     * @param string|null $path
     *
     * @return ContentTypeMetadata
     */
    public function guessContentType(string $path = null): ContentTypeMetadata
    {
        return ContentTypeMetadata::create($this->contentTypeGuesser->guess($path));
    }

    /**
     * @param ContentTypeMetadata $contentType
     *
     * @return ExtensionMetadata
     */
    public function guessExtension(ContentTypeMetadata $contentType): ExtensionMetadata
    {
        return ExtensionMetadata::create($this->extensionGuesser->guess($contentType));
    }
}
