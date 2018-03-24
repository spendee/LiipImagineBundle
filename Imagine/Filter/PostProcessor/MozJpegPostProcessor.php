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
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * mozjpeg post-processor, for noticably better jpeg compression.
 *
 * @see http://calendar.perfplanet.com/2014/mozjpeg-3-0/
 * @see https://mozjpeg.codelove.de/binaries.html
 *
 * @author Alex Wilson <a@ax.gy>
 */
class MozJpegPostProcessor implements PostProcessorInterface
{
    /**
     * @var string Path to the mozjpeg cjpeg binary
     */
    protected $mozjpegBin;

    /**
     * @var null|int Quality factor
     */
    protected $quality;

    /**
     * Constructor.
     *
     * @param string   $mozjpegBin Path to the mozjpeg cjpeg binary
     * @param int|null $quality    Quality factor
     */
    public function __construct(
        $mozjpegBin = '/opt/mozjpeg/bin/cjpeg',
        $quality = null
    ) {
        $this->mozjpegBin = $mozjpegBin;
        $this->setQuality($quality);
    }

    /**
     * @param int $quality
     *
     * @return MozJpegPostProcessor
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
        if (!$file->getContentType()->isMatch('image', 'jpg') &&
            !$file->getContentType()->isMatch('image', 'jpeg')
        ) {
            return $file;
        }

        $arguments = [$this->mozjpegBin];

        // Places emphasis on DC
        $arguments[] = '-quant-table';
        $arguments[] = 2;

        if (null !== $quality = ($options['quality'] ?? $this->quality)) {
            $arguments[] = '-quality';
            $arguments[] = $quality;
        }

        $arguments[] = '-optimise';

        // Favor stdin/stdout so we don't waste time creating a new file.
        $process = new Process($arguments);
        $process->setInput($file->getContents());
        $process->run();

        if (false !== mb_strpos($process->getOutput(), 'ERROR') || 0 !== $process->getExitCode()) {
            throw new ProcessFailedException($process);
        }

        return new FileBlob($process->getOutput(), $file->getContentType(), $file->getExtension());
    }
}
