<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Log\Formatter;

/**
 * @author Rob Frawley 2nd <rmf@src.run>
 */
final class SpfFormat extends AbstractFormat
{
    /**
     * @param string $format
     * @param array  $replacements
     *
     * @return string
     */
    protected function interpolate(string $format, array $replacements): string
    {
        return vsprintf($format, array_values($this->normalizeReplacements($replacements))) ?? $format;
    }
}
