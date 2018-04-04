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
final class PsrFormat extends AbstractFormat
{
    /**
     * @param string $message
     * @param array  $replacements
     *
     * @return string
     */
    protected function interpolate(string $message, array $replacements): string
    {
        foreach ($this->normalizeReplacements($replacements) as $search => $replace) {
            $message = str_replace(sprintf('{%s}', $search), $replace, $message);
        }

        return $message;
    }
}
