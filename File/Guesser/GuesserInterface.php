<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\File\Guesser;

use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;

/**
 * @author Rob Frawley 2nd <rmf@src.run>
 */
interface GuesserInterface extends \Countable
{
    /**
     * @param GuesserInterface|MimeTypeGuesserInterface|ExtensionGuesserInterface $guesser
     */
    public function register($guesser): void;

    /**
     * @param string $subject
     *
     * @return string|null
     */
    public function guess($subject): ?string;
}
