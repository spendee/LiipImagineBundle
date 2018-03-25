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

use Liip\ImagineBundle\File\FileInterface;

interface LoaderInterface
{
    /**
     * Retrieves the image file using the passed identity string, which can be a file name, database id, or an other
     * identifier supported by the specific loader implementation.
     *
     * @param string $identity
     *
     * @return FileInterface
     */
    public function find(string $identity): FileInterface;
}
