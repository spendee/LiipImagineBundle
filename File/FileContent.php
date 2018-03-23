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

/**
 * @author Rob Frawley 2nd <rmf@src.run>
 */
class FileContent implements FileInterface
{
    use FileTrait;

    /**
     * @var string|null
     */
    private $contents;

    /**
     * @param string|null              $contents
     * @param ContentTypeMetadata|null $contentType
     * @param ExtensionMetadata|null   $extension
     */
    public function __construct(string $contents = null, ContentTypeMetadata $contentType = null, ExtensionMetadata $extension = null)
    {
        $this->contents = $contents;
        $this->contentType = $contentType ?: null;
        $this->extension = $extension ?: null;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->contents() ?: '';
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
        return new self($contents, ContentTypeMetadata::create($contentType), ExtensionMetadata::create($extension));
    }

    /**
     * {@inheritdoc}
     */
    public function file(): ?\SplFileInfo
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function hasFile(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function exists(): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        return true;
    }

    /**
     * @return string|null
     */
    protected function readFileContents(): ?string
    {
        return $this->contents;
    }

    /**
     * @param string $contents
     * @param bool   $append
     *
     * @return FileInterface
     */
    protected function dumpContents(string $contents, bool $append): FileInterface
    {
        $this->contents = true === $append ? $this->contents.$contents : $contents;

        return $this;
    }
}
