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
final class ExtensionMetadata implements MetadataInterface
{
    /**
     * @var string|null
     */
    private $extension;

    /**
     * @param string|null $extension
     */
    public function __construct(string $extension = null)
    {
        $this->extension = $extension;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->hasExtension() ? $this->extension : '';
    }

    /**
     * @param string|null $extension
     *
     * @return self
     */
    public static function create(string $extension = null): self
    {
        return new self($extension);
    }

    /**
     * @return null|string
     */
    public function extension(): ?string
    {
        return $this->extension;
    }

    /**
     * @return bool
     */
    public function hasExtension(): bool
    {
        return null !== $this->extension;
    }

    /**
     * @param string|null $extension
     *
     * @return bool
     */
    public function isExtension(string $extension = null): bool
    {
        return $extension === $this->extension;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->hasExtension();
    }
}
