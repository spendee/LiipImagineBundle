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

/**
 * @author Rob Frawley 2nd <rmf@src.run>
 */
class FilePath extends AbstractFilePath
{
    /**
     * @param string|\SplFileInfo|null $file
     * @param MimeTypeMetadata|null    $contentType
     * @param ExtensionMetadata|null   $extension
     */
    public function __construct($file = null, MimeTypeMetadata $contentType = null, ExtensionMetadata $extension = null)
    {
        if (null !== $file) {
            $this->file = $file instanceof \SplFileInfo ? $file : new \SplFileInfo($file);
        }

        parent::__construct($contentType, $extension);
    }

    /**
     * @param string|null $file
     * @param string|null $contentType
     * @param string|null $extension
     *
     * @return self
     */
    public static function create(string $file = null, string $contentType = null, string $extension = null)
    {
        return new self($file, MimeTypeMetadata::create($contentType), ExtensionMetadata::create($extension));
    }
}
