<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Fixtures\Data\File\Metadata;

use Symfony\Component\Finder\Finder;

return function (): array {
    $finder = (new Finder())
        ->in(sprintf('%s/../../../../', __DIR__))
        ->name('*.php')
        ->ignoreUnreadableDirs(true)
        ->files();

    return array_map(function (\SplFileInfo $file) {
        return [$file];
    }, iterator_to_array($finder));
};
