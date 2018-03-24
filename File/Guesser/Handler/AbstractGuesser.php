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

use Liip\ImagineBundle\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;

/**
 * @internal
 *
 * @author Rob Frawley 2nd <rmf@src.run>
 */
abstract class AbstractGuesser
{
    /**
     * @var MimeTypeGuesserInterface[]|ExtensionGuesserInterface[]
     */
    private $guessers = [];

    /**
     * @param MimeTypeGuesserInterface[]|ExtensionGuesserInterface[] ...$guessers
     */
    public function __construct(...$guessers)
    {
        foreach ($guessers as $g) {
            $this->register($g);
        }
    }

    /**
     * @param MimeTypeGuesserInterface|ExtensionGuesserInterface $guesser
     */
    public function register($guesser): void
    {
        if (!static::isSupportedGuesser($guesser)) {
            throw new InvalidArgumentException(sprintf(
                'Unsupported guesser registered of type "%s".', get_class($guesser)
            ));
        }

        array_unshift($this->guessers, $guesser);
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
     * @param mixed $guesser
     *
     * @return bool
     */
    abstract protected static function isSupportedGuesser($guesser): bool;
}
