<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\File\Guesser\Handler;

use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;

/**
 * @internal
 *
 * @author Rob Frawley 2nd <rmf@src.run>
 */
final class ExtensionGuesser extends AbstractGuesser implements ExtensionGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    protected static function isSupportedGuesser($guesser): bool
    {
        return $guesser instanceof ExtensionGuesserInterface;
    }
}
