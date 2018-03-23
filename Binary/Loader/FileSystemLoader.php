<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Binary\Loader;

use Liip\ImagineBundle\Binary\Locator\LocatorInterface;
use Liip\ImagineBundle\File\Guesser\GuesserManager;
use Liip\ImagineBundle\File\FileReference;

class FileSystemLoader implements LoaderInterface
{
    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @var GuesserManager
     */
    private $guesserManager;

    /**
     * @param LocatorInterface $locator
     * @param GuesserManager   $guesserManager
     */
    public function __construct(
        LocatorInterface $locator,
        GuesserManager   $guesserManager
    ) {
        $this->locator = $locator;
        $this->guesserManager = $guesserManager;
    }

    /**
     * {@inheritdoc}
     */
    public function find($path)
    {
        $path = $this->locator->locate($path);
        $meta = $this->guesserManager->guessUsingPath($path);

        return new FileReference($path, $meta->contentType(), $meta->extension());
    }
}
