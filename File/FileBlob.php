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
class FileBlob extends AbstractFile
{
    /**
     * @var string|null
     */
    private $contents;

    /**
     * @param string|null            $contents
     * @param MimeTypeMetadata|null  $contentType
     * @param ExtensionMetadata|null $extension
     */
    public function __construct(string $contents = null, MimeTypeMetadata $contentType = null, ExtensionMetadata $extension = null)
    {
        $this->contents = $contents;
        parent::__construct($contentType, $extension);
    }

    /**
     * @param string|null $contents
     * @param string|null $contentType
     * @param string|null $extension
     *
     * @return self
     */
    public static function create(string $contents = null, string $contentType = null, string $extension = null)
    {
        return new self($contents, MimeTypeMetadata::create($contentType), ExtensionMetadata::create($extension));
    }

    /**
     * @return string|null
     */
    protected function doGetContents(): ?string
    {
        return $this->contents;
    }

    /**
     * @param string $contents
     * @param bool   $append
     */
    protected function doSetContents(string $contents, bool $append): void
    {
        $this->contents = true === $append ? $this->contents.$contents : $contents;
    }
}
