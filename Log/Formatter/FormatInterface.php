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
interface FormatInterface
{
    /**
     * @param string      $format
     * @param array       $replacements
     * @param string|null $context
     *
     * @return string
     */
    public function format(string $format, array $replacements = [], string $context = null): string;

    /**
     * @param string $message
     * @param int    $indentLevel
     * @param string $indentBullet
     *
     * @return string
     */
    public function indent(string $message, int $indentLevel = 0, string $indentBullet = '>'): string;
}
