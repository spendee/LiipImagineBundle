<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\File\Attributes;

/**
 * @author Rob Frawley 2nd <rmf@src.run>
 */
final class Attributes
{
    /**
     * @var ContentTypeAttribute
     */
    private $contentType;

    /**
     * @var ExtensionAttribute
     */
    private $extension;

    /**
     * @param ContentTypeAttribute|null $contentType
     * @param ExtensionAttribute|null   $extension
     */
    public function __construct(ContentTypeAttribute $contentType = null, ExtensionAttribute $extension = null)
    {
        $this->contentType = $contentType ?: new ContentTypeAttribute();
        $this->extension = $extension ?: new ExtensionAttribute();
    }

    /**
     * @return ContentTypeAttribute
     */
    public function getContentType(): ContentTypeAttribute
    {
        return $this->contentType;
    }

    /**
     * @return ExtensionAttribute
     */
    public function getExtension(): ExtensionAttribute
    {
        return $this->extension;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return true === $this->contentType->isValid()
            && true === $this->extension->isValid();
    }
}
