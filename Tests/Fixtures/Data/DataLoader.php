<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Fixtures\Data;

use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
final class DataLoader
{
    /**
     * @var array[]
     */
    private static $data = [];

    /**
     * @var string
     */
    private $dataRootPath;

    /**
     * @param string|null $dataRootPath
     */
    public function __construct(string $dataRootPath = null)
    {
        $this->dataRootPath = $dataRootPath ?: __DIR__;
    }

    /**
     * @param string      $testClassName
     * @param string|null $context
     * @param bool        $iterator
     *
     * @throws \ReflectionException
     *
     * @return array|\Iterator
     */
    public function __invoke(string $testClassName, string $context = null, bool $iterator = true)
    {
        $r = new \ReflectionClass($testClassName);

        if (1 !== preg_match('{^.+?\\\Tests\\\(?<path>.+)\\\(?<file>[^\\\]+)$}', $r->getName(), $m)) {
            throw new \RuntimeException(sprintf('Failed to load data for "%s" test class!', $r->getName()));
        }

        $dataFile = str_replace(
            '\\', '/', sprintf('%s/%s/%sData.php', $this->dataRootPath, $m['path'], $m['file'])
        );

        if (!isset(self::$data[$dataFile])) {
            if (!file_exists($dataFile)) {
                throw new \RuntimeException(sprintf('Test fixture data file "%s" does not exist!', $dataFile));
            }

            self::$data[$dataFile] = (include_once $dataFile)();
        }

        $d = self::$data[$dataFile];

        if (null !== $context) {
            if (!isset($d[$context])) {
                throw new \RuntimeException(sprintf('Test fixture data file "%s" context "%s" not found!', $dataFile, $context));
            }

            $d = $d[$context];
        }

        if (false === $iterator) {
            return $d;
        }

        foreach ($d as $i => $v) {
            yield $i => $v;
        }
    }
}
