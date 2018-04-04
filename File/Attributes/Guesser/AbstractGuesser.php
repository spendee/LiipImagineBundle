<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\File\Attributes\Guesser;

use Liip\ImagineBundle\Exception\InvalidArgumentException;
use Symfony\Component\HttpFoundation\File\MimeType\ExtensionGuesserInterface as BaseExtensionGuesserInterface;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;

/**
 * @internal
 *
 * @author Rob Frawley 2nd <rmf@src.run>
 */
abstract class AbstractGuesser
{
    /**
     * @var ContentTypeGuesserInterface[]|MimeTypeGuesserInterface[]|ExtensionGuesserInterface[]|BaseExtensionGuesserInterface[]
     */
    private $guessers = [];

    /**
     * @param ContentTypeGuesserInterface|MimeTypeGuesserInterface|ExtensionGuesserInterface|BaseExtensionGuesserInterface $guesser
     */
    public function register($guesser): void
    {
        if (!static::isSupportedGuesser($guesser)) {
            throw new InvalidArgumentException(
                'Invalid guesser type "%s" provided for "%s".', get_class($guesser), get_called_class()
            );
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
        foreach ($this->guessers as $guesser) {
            if (null !== $result = $guesser->guess($subject)) {
                return $result;
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
