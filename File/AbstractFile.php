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
 * @internal
 *
 * @author Rob Frawley 2nd <rmf@src.run>
 */
abstract class AbstractFile implements FileInterface
{
    /**
     * @var MimeTypeMetadata
     */
    protected $contentType;

    /**
     * @var ExtensionMetadata
     */
    protected $extension;

    /**
     * @param MimeTypeMetadata|null  $contentType
     * @param ExtensionMetadata|null $extension
     */
    public function __construct(MimeTypeMetadata $contentType = null, ExtensionMetadata $extension = null)
    {
        $this->contentType = $contentType ?: new MimeTypeMetadata();
        $this->extension = $extension ?: new ExtensionMetadata();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getContents() ?: '';
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType(): MimeTypeMetadata
    {
        return $this->contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function hasContentType(): bool
    {
        return $this->getContentType()->isValid();
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension(): ExtensionMetadata
    {
        return $this->extension;
    }

    /**
     * {@inheritdoc}
     */
    public function hasExtension(): bool
    {
        return $this->getExtension()->isValid();
    }

    /**
     * {@inheritdoc}
     */
    public function getContents(): ?string
    {
        return $this->doGetContents();
    }

    /**
     * {@inheritdoc}
     */
    public function hasContents(): bool
    {
        return null !== $this->doGetContents();
    }

    /**
     * @param string $contents
     * @param bool   $append
     *
     * @return FileInterface
     */
    public function setContents(string $contents = '', bool $append = false): FileInterface
    {
        $this->doSetContents($contents, $append);

        return $this;
    }

    /**
     * @return int
     */
    public function getContentsLength(): int
    {
        return mb_strlen($this->getContents());
    }

    /**
     * {@inheritdoc}
     */
    public function hasFile(): bool
    {
        return false;
    }

    /**
     * @return null|string
     */
    abstract protected function doGetContents(): ?string;

    /**
     * @param string $contents
     * @param bool   $append
     */
    abstract protected function doSetContents(string $contents, bool $append): void;
}
