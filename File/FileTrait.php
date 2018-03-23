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
 * @internal
 *
 * @author Rob Frawley 2nd <rmf@src.run>
 */
trait FileTrait
{
    /**
     * @var ContentTypeMetadata
     */
    private $contentType;

    /**
     * @var ExtensionMetadata
     */
    private $extension;

    /**
     * {@inheritdoc}
     */
    public function contentType(): ContentTypeMetadata
    {
        return $this->contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function hasContentType(): bool
    {
        return $this->contentType()->isValid();
    }

    /**
     * {@inheritdoc}
     */
    public function extension(): ExtensionMetadata
    {
        return $this->extension;
    }

    /**
     * {@inheritdoc}
     */
    public function hasExtension(): bool
    {
        return $this->extension()->isValid();
    }

    /**
     * {@inheritdoc}
     */
    public function contents(): ?string
    {
        return $this->readFileContents();
    }

    /**
     * {@inheritdoc}
     */
    public function hasContents(): bool
    {
        return null !== $this->readFileContents();
    }

    /**
     * {@inheritdoc}
     */
    public function hasEmptyContents(): bool
    {
        return empty($this->readFileContents());
    }

    /**
     * @param string $contents
     * @param bool   $append
     *
     * @return FileInterface
     */
    public function setContents(string $contents = '', bool $append = false): FileInterface
    {
        return $this->dumpContents($contents, $append);
    }

    /**
     * @return null|string
     */
    abstract protected function readFileContents(): ?string;

    /**
     * @param string $contents
     * @param bool   $append
     *
     * @return FileInterface
     */
    abstract protected function dumpContents(string $contents, bool $append): FileInterface;
}
