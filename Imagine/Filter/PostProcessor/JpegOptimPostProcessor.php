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

use Liip\ImagineBundle\File\FileBlob;
use Liip\ImagineBundle\File\FileInterface;
use Liip\ImagineBundle\File\FileTemp;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class JpegOptimPostProcessor implements PostProcessorInterface
{
    /**
     * @var string Path to jpegoptim binary
     */
    protected $jpegoptimBin;

    /**
     * If set --strip-all will be passed to jpegoptim.
     *
     * @var bool
     */
    protected $stripAll;

    /**
     * If set, --max=$value will be passed to jpegoptim.
     *
     * @var int
     */
    protected $max;

    /**
     * If set to true --all-progressive will be passed to jpegoptim, otherwise --all-normal will be passed.
     *
     * @var bool
     */
    protected $progressive;

    /**
     * Directory where temporary file will be written.
     *
     * @var string
     */
    protected $tempDir;

    /**
     * Constructor.
     *
     * @param string $jpegoptimBin Path to the jpegoptim binary
     * @param bool   $stripAll     Strip all markers from output
     * @param int    $max          Set maximum image quality factor
     * @param bool   $progressive  Force output to be progressive
     * @param string $tempDir      Directory where temporary file will be written
     */
    public function __construct(
        $jpegoptimBin = '/usr/bin/jpegoptim',
        $stripAll = true,
        $max = null,
        $progressive = true,
        $tempDir = ''
    ) {
        $this->jpegoptimBin = $jpegoptimBin;
        $this->stripAll = $stripAll;
        $this->max = $max;
        $this->progressive = $progressive;
        $this->tempDir = $tempDir ?: sys_get_temp_dir();
    }

    /**
     * @param FileInterface $file
     * @param array         $options
     *
     * @throws ProcessFailedException
     *
     * @return FileInterface
     */
    public function process(FileInterface $file, array $options = []): FileInterface
    {
        if (!$file->getContentType()->isMatch('image', 'jpeg') &&
            !$file->getContentType()->isMatch('image', 'jpg')
        ) {
            return $file;
        }

        $temporary = new FileTemp(
            'post-processor-jpegoptim', $options['temp_dir'] ?? $this->tempDir
        );
        $temporary->acquire();
        $arguments = [$this->jpegoptimBin];

        if (true === ($options['strip_all'] ?? $this->stripAll)) {
            $arguments[] = '--strip-all';
        }

        if (null !== $quality = ($options['max'] ?? $this->max)) {
            $arguments[] = '--max='.$quality;
        }

        if (true === ($options['progressive'] ?? $this->progressive)) {
            $arguments[] = '--all-progressive';
        } else {
            $arguments[] = '--all-normal';
        }

        $arguments[] = $temporary->getFile()->getPathname();
        $temporary->setContents($file->getContents());

        $process = new Process($arguments);
        $process->run();

        $processed = $temporary->getContents();
        $temporary->release();

        if (false !== mb_strpos($process->getOutput(), 'ERROR') || 0 !== $process->getExitCode()) {
            throw new ProcessFailedException($process);
        }

        return new FileBlob($processed, $file->getContentType(), $file->getExtension());
    }
}
