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

/**
 * @author Rob Frawley 2nd <rmf@src.run>
 */
interface GuesserInterface
{
    /**
     * @param GuesserInterface|mixed $guesser
     */
    public function register($guesser): void;

    /**
     * @param string $subject
     *
     * @return string|null
     */
    public function guess($subject): ?string;
}
