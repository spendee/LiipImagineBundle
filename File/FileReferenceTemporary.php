<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\File;

use Liip\ImagineBundle\Exception\File\FileOperationException;
use Liip\ImagineBundle\File\Lock\LockAction;
use Liip\ImagineBundle\Utility\Interpreter\Interpreter;

/**
 * @author Rob Frawley 2nd <rmf@src.run>
 */
final class FileReferenceTemporary implements FileInterface
{
    use FileReferenceTrait {
        FileReferenceTrait::dumpContents as doDumpContents;
    }

    /**
     * @var string
     */
    private $tmpContext;

    /**
     * @var string
     */
    private $pathPrefix;

    /**
     * @param string|null $tmpContext
     * @param string|null $pathPrefix
     */
    public function __construct(string $tmpContext = null, string $pathPrefix = null)
    {
        $this->setTmpContext($tmpContext);
        $this->setPathPrefix($pathPrefix);
    }

    public function __destruct()
    {
        $this->release();
    }

    /**
     * @return string
     */
    public function tmpContext(): string
    {
        return $this->tmpContext;
    }

    /**
     * @param string|null $name
     *
     * @return self
     */
    public function setTmpContext(string $name = null): self
    {
        $this->requireReleasedState('failed to change context descriptor');
        $this->tmpContext = sprintf('imagine-bundle-temporary_%s', $name ?: 'general');

        return $this;
    }

    /**
     * @return string
     */
    public function pathPrefix(): string
    {
        return $this->pathPrefix;
    }

    /**
     * @param string|null $path
     *
     * @return FileReferenceTemporary
     */
    public function setPathPrefix(string $path = null): self
    {
        $this->requireReleasedState('failed to change path prefix');
        $this->pathPrefix = self::makePathIfNotExists($path ?? sys_get_temp_dir());

        return $this;
    }

    /**
     * @return bool
     */
    public function isAcquired(): bool
    {
        return $this->hasFile();
    }

    /**
     * @return self
     */
    public function acquire(): self
    {
        $this->requireReleasedState('failed to acquire a new one');

        $this->file = LockAction::blocking($this, function (): \SplFileInfo {
            if (false !== $file = @tempnam($this->pathPrefix(), $this->tmpContext())) {
                return new \SplFileInfo($file);
            }

            throw new FileOperationException(sprintf(
                'Failed to acquire temporary file in "%s": %s.', $this->pathPrefix(), Interpreter::lastErrorMessage()
            ));
        });

        return $this;
    }

    /**
     * @param bool $remove
     *
     * @return self
     */
    public function release(bool $remove = true): self
    {
        if (!$this->isAcquired()) {
            return $this;
        }

        if ($remove) {
            $this->remove();
        }

        $this->file = null;

        return $this;
    }

    /**
     * @param string $message
     */
    private function requireReleasedState(string $message): void
    {
        if ($this->isAcquired()) {
            throw new FileOperationException(sprintf('Temporary file must be released first: %s', $message));
        }
    }

    /**
     * @param string $contents
     * @param bool   $append
     *
     * @return FileInterface
     */
    protected function dumpContents(string $contents, bool $append): FileInterface
    {
        if (!$this->isAcquired()) {
            $this->acquire();
        }

        return $this->doDumpContents($contents, $append);
    }
}
