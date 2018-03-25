<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\File\Loader;

use Liip\ImagineBundle\File\Loader\Locator\LocatorInterface;
use Liip\ImagineBundle\File\FileInterface;
use Liip\ImagineBundle\File\FilePath;

class FileSystemLoader implements LoaderInterface
{
    /**
     * @var LocatorInterface
     */
    private $locator;

    /**
     * @param LocatorInterface $locator
     */
    public function __construct(LocatorInterface $locator)
    {
        $this->locator = $locator;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $identity): FileInterface
    {
        return FilePath::create($this->locator->locate($identity));
    }
}
