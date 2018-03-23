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

use Liip\ImagineBundle\File\Metadata\ContentTypeMetadata;
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
    public function contents(): ?string;

    /**
     * @return bool
     */
    public function hasContents(): bool;

    /**
     * @return bool
     */
    public function hasEmptyContents(): bool;

    /**
     * @param string $contents
     * @param bool   $append
     *
     * @return FileInterface
     */
    public function setContents(string $contents = '', bool $append = false): self;

    /**
     * @return ContentTypeMetadata
     */
    public function contentType(): ContentTypeMetadata;

    /**
     * @return bool
     */
    public function hasContentType(): bool;

    /**
     * @return ExtensionMetadata
     */
    public function extension(): ExtensionMetadata;

    /**
     * @return bool
     */
    public function hasExtension(): bool;

    /**
     * @return \SplFileInfo|null
     */
    public function file(): ?\SplFileInfo;

    /**
     * @return bool
     */
    public function hasFile(): bool;

    /**
     * return bool
     */
    public function exists(): bool;

    /**
     * @return bool
     */
    public function isReadable(): bool;

    /**
     * @return bool
     */
    public function isWritable(): bool;
}
