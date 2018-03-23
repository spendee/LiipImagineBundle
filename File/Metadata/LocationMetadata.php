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
final class LocationMetadata extends \SplFileInfo implements MetadataInterface
{
    /**
     * @return string
     */
    public function __toString(): string
    {
        return parent::__toString();
    }

    /**
     * @param string $filePath
     *
     * @return self
     */
    public static function create(string $filePath): self
    {
        return new self($filePath);
    }

    /**
     * @param LocationMetadata|string $location
     *
     * @return bool
     */
    public function isLocation($location): bool
    {
        return (string) $this === (string) $location;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isFile();
    }
}
