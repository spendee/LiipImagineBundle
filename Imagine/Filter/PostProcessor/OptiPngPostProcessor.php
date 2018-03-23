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

use Liip\ImagineBundle\File\FileContent;
use Liip\ImagineBundle\File\FileInterface;
use Liip\ImagineBundle\File\FileReferenceTemporary;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class OptiPngPostProcessor implements PostProcessorInterface
{
    /**
     * @var string Path to optipng binary
     */
    protected $optipngBin;

    /**
     * If set --oN will be passed to optipng.
     *
     * @var int
     */
    protected $level;

    /**
     * If set --strip=all will be passed to optipng.
     *
     * @var bool
     */
    protected $stripAll;

    /**
     * Directory where temporary file will be written.
     *
     * @var string
     */
    protected $tempDir;

    /**
     * Constructor.
     *
     * @param string $optipngBin Path to the optipng binary
     * @param int    $level      Optimization level
     * @param bool   $stripAll   Strip metadata objects
     * @param string $tempDir    Directory where temporary file will be written
     */
    public function __construct($optipngBin = '/usr/bin/optipng', $level = 7, $stripAll = true, $tempDir = '')
    {
        $this->optipngBin = $optipngBin;
        $this->level = $level;
        $this->stripAll = $stripAll;
        $this->tempDir = $tempDir ?: sys_get_temp_dir();
    }

    /**
     * @param FileInterface $file
     * @param array         $options
     *
     * @throws ProcessFailedException
     *
     * @return FileInterface|FileContent
     */
    public function process(FileInterface $file, array $options = []): FileInterface
    {
        if (!$file->contentType()->isEquivalent('image', 'png')) {
            return $file;
        }

        $temporary = new FileReferenceTemporary(
            'post-processor-optipng', $options['temp_dir'] ?? $this->tempDir
        );
        $temporary->acquire();
        $arguments = [$this->optipngBin];

        if (null !== $level = ($options['level'] ?? $this->level)) {
            $arguments[] = sprintf('--o%d', $level);
        }

        if (true === ($options['strip_all'] ?? $this->stripAll)) {
            $arguments[] = '--strip=all';
        }

        $arguments[] = $temporary->file()->getPathname();
        $temporary->setContents($file->contents());

        $process = new Process($arguments);
        $process->run();

        $processed = $temporary->contents();
        $temporary->release();

        if (false !== mb_strpos($process->getOutput(), 'ERROR') || 0 !== $process->getExitCode()) {
            throw new ProcessFailedException($process);
        }

        return new FileContent($processed, $file->contentType(), $file->extension());
    }
}
