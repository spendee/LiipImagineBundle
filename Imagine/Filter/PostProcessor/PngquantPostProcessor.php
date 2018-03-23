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
use Liip\ImagineBundle\File\FileContent;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * pngquant post-processor, for optimal, web-safe, lossy png compression
 * This requires a recent version of pngquant (so 2.3 or higher?)
 * See pngqaunt.org if you are unable to find a binary package for your distribution.
 *
 * @see https://pngquant.org/
 *
 * @author Alex Wilson <a@ax.gy>
 */
class PngquantPostProcessor implements PostProcessorInterface
{
    /**
     * @var string Path to pngquant binary
     */
    protected $pngquantBin;

    /**
     * @var string Quality to pass to pngquant
     */
    protected $quality;

    /**
     * Constructor.
     *
     * @param string $pngquantBin Path to the pngquant binary
     * @param string $quality
     */
    public function __construct($pngquantBin = '/usr/bin/pngquant', $quality = '80-100')
    {
        $this->pngquantBin = $pngquantBin;
        $this->setQuality($quality);
    }

    /**
     * @param string $quality
     *
     * @return PngquantPostProcessor
     */
    public function setQuality($quality)
    {
        $this->quality = $quality;

        return $this;
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
        if (!$file->contentType()->isEquivalent('image', 'png')) {
            return $file;
        }

        $arguments = [$this->pngquantBin];

        $arguments[] = '--quality';
        $arguments[] = $options['quality'] ?? $this->quality;

        // have process read file contents from STDIN
        $arguments[] = '-';

        $process = new Process($arguments);
        $process->setInput($file->contents());
        $process->run();

        // Both 98 and 99 mean the quality was too low to compress; they aren't throwable failures
        if (!in_array($process->getExitCode(), [0, 98, 99], true)) {
            throw new ProcessFailedException($process);
        }

        return new FileContent($process->getOutput(), $file->contentType(), $file->extension());
    }
}
