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

use SR\Dumper\Transformer\StringTransformer;

/**
 * @internal
 *
 * @author Rob Frawley 2nd <rmf@src.run>
 */
abstract class AbstractFormat implements FormatInterface
{
    /**
     * @var string
     */
    private const BREAK_SEQUENCE = '[{abc012}]';

    /**
     * @var string
     */
    private $rootPackageName;

    /**
     * @var int
     */
    private $baseIndentLevel;

    /**
     * @param string|null $rootPackageName
     * @param int         $baseIndentLevel
     */
    public function __construct(string $rootPackageName = null, int $baseIndentLevel = null)
    {
        $this->rootPackageName = $rootPackageName ?: 'imagine-bundle';
        $this->baseIndentLevel = $baseIndentLevel ?: 0;
    }

    /**
     * @param string      $format
     * @param array       $replacements
     * @param string|null $context
     * @param int         $indent
     *
     * @return string
     */
    public function format(string $format, array $replacements = [], string $context = null, int $indent = 0): string
    {
        return $this->contextualize(
            $this->interpolate($format, $replacements), $indent, $context
        );
    }

    /**
     * @param string $message
     * @param int    $indentLevel
     * @param string $indentBullet
     *
     * @return string
     */
    public function indent(string $message, int $indentLevel = 0, string $indentBullet = '>'): string
    {
        $indent = str_repeat(' ', $indentLevel * 2);

        if ($indentLevel > 0) {
            $indent .= sprintf('%s ', $indentBullet);
        }

        return $indent.$message;
    }

    /**
     * @param string $format
     * @param array  $replacements
     *
     * @return string
     */
    abstract protected function interpolate(string $format, array $replacements): string;

    /**
     * @param string[] $replacements
     *
     * @return string[]
     */
    protected function normalizeReplacements(array $replacements): array
    {
        $transformer = new StringTransformer();

        return array_map(function ($value) use ($transformer) {
            return $transformer($value);
        }, $replacements);
    }

    /**
     * @param string      $message
     * @param int         $indent
     * @param string|null $context
     *
     * @return string
     */
    private function contextualize(string $message, int $indent, string $context = null): string
    {
        $contexts = [];
        $stripped = $this->removeContexts($this->removeRootPackage($message), $context, $contexts);

        return $this->prefixRootPackage($this->prefixContexts($this->indent($stripped, $indent), $contexts));
    }

    /**
     * @param string      $message
     * @param string|null $addition
     * @param array       $contexts
     *
     * @return string
     */
    private function removeContexts(string $message, string $addition = null, array &$contexts = []): string
    {
        while (1 === preg_match('{^\[(?<context>[^]]+)\]}', $message, $matched)) {
            $message = $this->removeBlock($message, $contexts[] = $matched['context']);
        }

        if (null !== $addition) {
            $contexts[] = $addition;
        }

        $contexts = array_unique($contexts);

        return $message;
    }

    /**
     * @param string $message
     * @param array  $contexts
     *
     * @return string
     */
    private function prefixContexts(string $message, array $contexts): string
    {
        if (empty($contexts)) {
            return $message;
        }

        return sprintf('%s %s', implode(' ', array_map(function (string $c) {
            return $this->formatBlock($c);
        }, $contexts)), $message);
    }

    /**
     * @param string $message
     *
     * @return string
     */
    private function removeRootPackage(string $message): string
    {
        return $this->removeBlock($message, $this->rootPackageName);
    }

    /**
     * @param string $message
     *
     * @return string
     */
    private function prefixRootPackage(string $message): string
    {
        return sprintf('%s %s', $this->formatBlock($this->rootPackageName), $message);
    }

    /**
     * @param string $message
     * @param string $block
     *
     * @return string
     */
    private function removeBlock(string $message, string $block): string
    {
        $block = $this->formatBlock($block);

        if (0 === mb_strpos($message, $block)) {
            $message = mb_substr($message, mb_strlen($block) + 1);
        }

        return $message;
    }

    /**
     * @param string $block
     *
     * @return string
     */
    private function formatBlock(string $block): string
    {
        return sprintf('[%s]', trim($block));
    }
}
