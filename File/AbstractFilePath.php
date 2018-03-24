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
use Liip\ImagineBundle\File\Lock\LockInvokable;
use Liip\ImagineBundle\Utility\Interpreter\Interpreter;

/**
 * @internal
 *
 * @author Rob Frawley 2nd <rmf@src.run>
 */
abstract class AbstractFilePath extends AbstractFile implements FilePathInterface
{
    /**
     * @var \SplFileInfo|null
     */
    protected $file;

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->hasFile() ? $this->getFile()->getPathname() : '';
    }

    /**
     * {@inheritdoc}
     */
    public function getFile(): ?\SplFileInfo
    {
        return $this->file;
    }

    /**
     * {@inheritdoc}
     */
    public function hasFile(): bool
    {
        return null !== $this->file;
    }

    /**
     * {@inheritdoc}
     */
    public function fileExists(): bool
    {
        return $this->hasFile() && file_exists($this->getFile()->getPathname());
    }

    /**
     * {@inheritdoc}
     */
    public function isFileReadable(): bool
    {
        return $this->fileExists() && is_readable($this->getFile()->getPathname());
    }

    /**
     * {@inheritdoc}
     */
    public function isFileWritable(): bool
    {
        return ($this->fileExists() && is_writable($this->getFile()->getPathname()))
            || (!$this->fileExists() && $this->hasFile() && is_writable($this->getFile()->getPath()));
    }

    /**
     * @return self
     */
    public function remove(): self
    {
        LockInvokable::blocking($this, function (): void {
            if ($this->fileExists() && (false === $this->isFileWritable() || false === @unlink($this->getFile()->getPathname()))) {
                throw new FileOperationException(sprintf(
                    'Failed to remove file "%s": %s', $this->file->getPathname(), Interpreter::lastErrorMessage()
                ));
            }
        });

        return $this;
    }

    /**
     * @return string|null
     */
    protected function doGetContents(): ?string
    {
        if (!$this->hasFile()) {
            return null;
        }

        return LockInvokable::blocking($this, function (): ?string {
            if (false !== $contents = @file_get_contents($this->getFile()->getPathname())) {
                return $contents;
            }

            return null;
        });
    }

    /**
     * @param string $contents
     * @param bool   $append
     *
     * @throws FileOperationException
     */
    protected function doSetContents(string $contents, bool $append): void
    {
        LockInvokable::blocking($this, function () use ($contents, $append): void {
            self::makePathIfNotExists($this->getFile()->getPath());
            self::dumpContentsForFile($this->getFile()->getPathname(), $contents, $append);
        });
    }

    /**
     * @param string $path
     *
     * @return string
     */
    protected static function makePathIfNotExists(string $path): string
    {
        if (false === is_dir($path) && false === @mkdir($path, 0777, true) && false === is_dir($path)) {
            throw new FileOperationException(sprintf(
                'Failed to create file "%s": %s', $path, Interpreter::lastErrorMessage()
            ));
        }

        if (false !== $real = @realpath($path)) {
            return $real;
        }

        return $path;
    }

    /**
     * @param string $file
     * @param string $contents
     * @param bool   $append
     */
    private static function dumpContentsForFile(string $file, string $contents, bool $append): void
    {
        if (false === @file_put_contents($file, $contents, $append ? FILE_APPEND : 0)) {
            throw new FileOperationException(sprintf(
                'Failed to write contents of "%s": %s.', $file, Interpreter::lastErrorMessage()
            ));
        }
    }
}
