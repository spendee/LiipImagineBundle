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
final class Metadata implements MetadataInterface
{
    /**
     * @var LocationMetadata
     */
    private $location;

    /**
     * @var ContentTypeMetadata
     */
    private $contentType;

    /**
     * @var ExtensionMetadata
     */
    private $extension;

    /**
     * @param LocationMetadata|null    $location
     * @param ContentTypeMetadata|null $contentType
     * @param ExtensionMetadata|null   $extension
     */
    public function __construct(
        LocationMetadata $location = null,
        ContentTypeMetadata $contentType = null,
        ExtensionMetadata $extension = null
    ) {
        $this->location = self::sanitize($location);
        $this->contentType = self::sanitize($contentType);
        $this->extension = self::sanitize($extension);
    }

    /**
     * @param LocationMetadata|null    $location
     * @param ContentTypeMetadata|null $contentType
     * @param ExtensionMetadata|null   $extension
     *
     * @return self
     */
    public static function create(
        LocationMetadata $location = null,
        ContentTypeMetadata $contentType = null,
        ExtensionMetadata $extension = null
    ): self {
        return new self($location, $contentType, $extension);
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return vsprintf('(%s) [%s]: %s', [
            $this->extension(),
            $this->contentType(),
            $this->location(),
        ]);
    }

    /**
     * @return LocationMetadata
     */
    public function location(): LocationMetadata
    {
        return $this->location;
    }

    /**
     * @return bool
     */
    public function hasLocation(): bool
    {
        return $this->location()->isValid();
    }

    /**
     * @return ContentTypeMetadata
     */
    public function contentType(): ContentTypeMetadata
    {
        return $this->contentType;
    }

    /**
     * @return bool
     */
    public function hasContentType(): bool
    {
        return $this->contentType()->isValid();
    }

    /**
     * @return ExtensionMetadata
     */
    public function extension(): ExtensionMetadata
    {
        return $this->extension;
    }

    /**
     * @return bool
     */
    public function hasExtension(): bool
    {
        return $this->extension()->isValid();
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->location()->isValid() &&
            $this->contentType()->isValid() &&
            $this->extension()->isValid();
    }

    /**
     * @param MetadataInterface|null $metadata
     *
     * @return MetadataInterface
     */
    private static function sanitize(MetadataInterface $metadata = null): MetadataInterface
    {
        return null !== $metadata && $metadata->isValid()
            ? $metadata
            : call_user_func(sprintf('%s::create', get_class($metadata)));
    }
}
