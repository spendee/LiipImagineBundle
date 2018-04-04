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
use Liip\ImagineBundle\File\FileTemp;
use Liip\ImagineBundle\Log\Logger;
use Liip\ImagineBundle\Log\LoggerAwareTrait;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * @author Rob Frawley 2nd <rmf@src.run>
 */
abstract class AbstractPostProcessor implements PostProcessorInterface
{
    use LoggerAwareTrait;

    /**
     * @var string
     */
    protected $executableBin;

    /**
     * @var string|null
     */
    protected $temporaryPath;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @param string      $executableBin
     * @param string|null $temporaryPath
     * @param Logger|null $logger
     */
    public function __construct(string $executableBin, string $temporaryPath = null, Logger $logger = null)
    {
        $this->executableBin = $executableBin;
        $this->temporaryPath = $temporaryPath;
        $this->filesystem = new Filesystem();

        $this->requireSetLogger($logger);
    }

    /**
     * @param array         $options
     * @param \Closure|null $configure
     * @param mixed         ...$configureArguments
     *
     * @return ProcessCreator
     */
    protected function createProcess(array $options = [], \Closure $configure = null, ...$configureArguments): ProcessCreator
    {
        $definition = new ProcessCreator($this->executableBin);
        $definition->mergeOptions($options['process'] ?? []);

        if (null !== $configure) {
            $configure($definition, $options, ...$configureArguments);
        }

        return $definition;
    }

    /**
     * @param FileInterface $file
     *
     * @return bool
     */
    protected function isFileTypeJpg(FileInterface $file): bool
    {
        return $file->getContentType()->isMatch('image', 'jpg')
            || $file->getContentType()->isMatch('image', 'jpeg');
    }

    /**
     * @param FileInterface $file
     *
     * @return bool
     */
    protected function isFileTypePng(FileInterface $file): bool
    {
        return $file->getContentType()->isMatch('image', 'png');
    }

    /**
     * @param FileInterface $file
     * @param array         $options
     *
     * @return FileInterface
     */
    protected function writeTemporary(FileInterface $file, array $options = []): FileInterface
    {
        return FileTemp::create(
            $options['work_file_prefix'] ?? 'liip-imagine-bundle-post-processor',
            $options['work_file_root'] ?? $this->temporaryPath,
            $file->getContents()
        );
    }

    /**
     * @param Process  $process
     * @param int[]    $okayReturnedCodes
     * @param string[] $failStdOutRegexps
     * @param bool     $requireAllRegexpsToFail
     *
     * @return bool
     */
    protected function isProcessSuccess(Process $process, array $okayReturnedCodes = [0], array $failStdOutRegexps = ['*ERROR*'], bool $requireAllRegexpsToFail = false): bool
    {
        if (0 < count($okayReturnedCodes) && false === in_array($process->getExitCode(), $okayReturnedCodes, true)) {
            return false;
        }

        $failFoundMatches = array_filter($failStdOutRegexps, function (string $search) use ($process) {
            return (new MultiplePcreMatcher($search))->isMatching($process->getOutput());
        });

        $inputLength = count($failStdOutRegexps);
        $matchLength = count($failFoundMatches);

        if (0 === $inputLength) {
            return true;
        }

        if (!$requireAllRegexpsToFail) {
            return !(0 < $matchLength);
        }

        return !($matchLength === $inputLength);
    }
}
