<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\File;

use Liip\ImagineBundle\File\Metadata\MimeTypeMetadata;
use Liip\ImagineBundle\File\Metadata\ExtensionMetadata;

interface FileInterface
{
    /**
     * @return string
     */
    public function __toString(): string;

    /**
     * @return string|null
     */
    public function getContents(): ?string;

    /**
     * @return bool
     */
    public function hasContents(): bool;

    /**
     * @param string $contents
     * @param bool   $append
     *
     * @return FileInterface
     */
    public function setContents(string $contents = '', bool $append = false): self;

    /**
     * @return int
     */
    public function getContentsLength(): int;

    /**
     * @return MimeTypeMetadata
     */
    public function getContentType(): MimeTypeMetadata;

    /**
     * @return bool
     */
    public function hasContentType(): bool;

    /**
     * @return ExtensionMetadata
     */
    public function getExtension(): ExtensionMetadata;

    /**
     * @return bool
     */
    public function hasExtension(): bool;

    /**
     * @return bool
     */
    public function hasFile(): bool;
}
