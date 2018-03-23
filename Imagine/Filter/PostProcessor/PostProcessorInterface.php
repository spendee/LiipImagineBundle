<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Filter\PostProcessor;

use Liip\ImagineBundle\File\FileInterface;

/**
 * Interface for PostProcessors - handlers which can operate on binaries prepared in FilterManager.
 */
interface PostProcessorInterface
{
    /**
     * @param FileInterface $file
     * @param array         $options
     *
     * @return FileInterface
     */
    public function process(FileInterface $file, array $options = []): FileInterface;
}
