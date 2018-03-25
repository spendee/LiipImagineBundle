<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Fixtures\Data\File\Attributes;

use Liip\ImagineBundle\Tests\File\Attributes\ContentTypeAttributeTest;
use Liip\ImagineBundle\Tests\Fixtures\Data\DataLoader;

return function (): array {
    return [
        'default' => array_map(function (array $data): array {
            return [array_pop($data)];
        }, (iterator_to_array((new DataLoader())(ContentTypeAttributeTest::class)))),
    ];
};
