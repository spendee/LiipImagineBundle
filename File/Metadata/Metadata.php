<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\File\Metadata;

/**
 * @author Rob Frawley 2nd <rmf@src.run>
 */
final class Metadata
{
    /**
     * @var MimeTypeMetadata
     */
    private $contentType;

    /**
     * @var ExtensionMetadata
     */
    private $extension;

    /**
     * @param MimeTypeMetadata|null  $contentType
     * @param ExtensionMetadata|null $extension
     */
    public function __construct(MimeTypeMetadata $contentType = null, ExtensionMetadata $extension = null)
    {
        $this->contentType = $contentType->isValid() ? $contentType : MimeTypeMetadata::create();
        $this->extension = $extension->isValid() ? $extension : ExtensionMetadata::create();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return sprintf('%s => "%s"', $this->getExtension(), $this->getContentType());
    }

    /**
     * @param string|null $contentType
     * @param string|null $extension
     *
     * @return self
     */
    public static function create(string $contentType = null, string $extension = null): self
    {
        return new self(MimeTypeMetadata::create($contentType), ExtensionMetadata::create($extension));
    }

    /**
     * @return MimeTypeMetadata
     */
    public function getContentType(): MimeTypeMetadata
    {
        return $this->contentType;
    }

    /**
     * @return bool
     */
    public function hasContentType(): bool
    {
        return $this->getContentType()->isValid();
    }

    /**
     * @return ExtensionMetadata
     */
    public function getExtension(): ExtensionMetadata
    {
        return $this->extension;
    }

    /**
     * @return bool
     */
    public function hasExtension(): bool
    {
        return $this->getExtension()->isValid();
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->getContentType()->isValid() && $this->getExtension()->isValid();
    }
}
