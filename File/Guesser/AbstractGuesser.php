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
abstract class AbstractGuesser implements GuesserInterface
{
    /**
     * @var GuesserInterface[]|MimeTypeGuesserInterface[]|ExtensionGuesserInterface[]
     */
    private $guessers = [];

    /**
     * @param GuesserInterface[]|MimeTypeGuesserInterface[]|ExtensionGuesserInterface[] ...$guessers
     */
    public function __construct(...$guessers)
    {
        foreach ($guessers as $g) {
            $this->register($g);
        }
    }

    /**
     * @param GuesserInterface|MimeTypeGuesserInterface|ExtensionGuesserInterface $guesser
     */
    public function register($guesser): void
    {
        if (static::isSupportedGuesser($guesser)) {
            array_unshift($this->guessers, $guesser);
        }
    }

    /**
     * @param string $subject
     *
     * @return string|null
     */
    public function guess($subject): ?string
    {
        foreach ($this->guessers as $g) {
            if (null !== $guess = $g->guess((string) $subject)) {
                return $guess;
            }
        }

        return null;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->guessers);
    }

    /**
     * @param mixed $guesser
     *
     * @return bool
     */
    protected static function isSupportedGuesser($guesser): bool
    {
        return is_callable([$guesser, 'guess']);
    }
}
