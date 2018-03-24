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

interface FilePathInterface extends FileInterface
{
    /**
     * @return bool
     */
    public function hasFile(): bool;

    /**
     * @return \SplFileInfo|null
     */
    public function getFile(): ?\SplFileInfo;

    /**
     * return bool
     */
    public function fileExists(): bool;

    /**
     * @return bool
     */
    public function isFileReadable(): bool;

    /**
     * @return bool
     */
    public function isFileWritable(): bool;
}
