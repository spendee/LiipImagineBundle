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

use Liip\ImagineBundle\File\Attributes\ContentTypeAttribute;
use Liip\ImagineBundle\File\Attributes\ExtensionAttribute;

/**
 * @author Rob Frawley 2nd <rmf@src.run>
 */
class FilePath extends AbstractFilePath implements FilePathInterface
{
    /**
     * @param string|\SplFileInfo|null  $file
     * @param ContentTypeAttribute|null $contentType
     * @param ExtensionAttribute|null   $extension
     */
    public function __construct($file = null, ContentTypeAttribute $contentType = null, ExtensionAttribute $extension = null)
    {
        parent::__construct($contentType, $extension);

        if (null !== $file) {
            $this->file = $file instanceof \SplFileInfo ? $file : new \SplFileInfo($file);
        }
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
        return new self($file, ContentTypeAttribute::create($contentType), ExtensionAttribute::create($extension));
    }
}
