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

use Liip\ImagineBundle\File\FileTemp;
use Liip\ImagineBundle\File\Guesser\Handler\ContentTypeGuesser;
use Liip\ImagineBundle\File\Guesser\Handler\ExtensionGuesser;
use Liip\ImagineBundle\File\Metadata\MimeTypeMetadata;
use Liip\ImagineBundle\File\Metadata\ExtensionMetadata;
use Liip\ImagineBundle\File\Metadata\LocationMetadata;
use Liip\ImagineBundle\File\Metadata\Metadata;

/**
 * @internal
 *
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
        $temporary = (new FileTemp('guesser-manager'))->acquire();

        if (null !== $contents) {
            $temporary->setContents($contents);
        }

        return $this->guessUsingPath($temporary->getFile()->getPathname());
    }

    /**
     * @param string|null $path
     *
     * @return MimeTypeMetadata
     */
    public function guessContentType(string $path = null): MimeTypeMetadata
    {
        return MimeTypeMetadata::create($this->contentTypeGuesser->guess($path));
    }

    /**
     * @param MimeTypeMetadata $contentType
     *
     * @return ExtensionMetadata
     */
    public function guessExtension(MimeTypeMetadata $contentType): ExtensionMetadata
    {
        return ExtensionMetadata::create($this->extensionGuesser->guess($contentType));
    }
}
