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
 * @internal
 *
 * @author Rob Frawley 2nd <rmf@src.run>
 */
trait FileReferenceTrait
{
    use FileTrait;

    /**
     * @var \SplFileInfo|null
     */
    private $file;

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->hasFile() ? $this->file()->getPathname() : '';
    }

    /**
     * {@inheritdoc}
     */
    public function file(): ?\SplFileInfo
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
    public function exists(): bool
    {
        return $this->hasFile() && file_exists($this->file()->getPathname());
    }

    /**
     * {@inheritdoc}
     */
    public function isReadable(): bool
    {
        return $this->exists() && is_readable($this->file()->getPathname());
    }

    /**
     * {@inheritdoc}
     */
    public function isWritable(): bool
    {
        return ($this->exists() && is_writable($this->file()->getPathname()))
            || (!$this->exists() && $this->hasFile() && is_writable($this->file()->getPath()));
    }

    /**
     * @return self
     */
    public function remove(): self
    {
        LockAction::blocking($this, function (): void {
            if ($this->exists() && (false === $this->isWritable() || false === @unlink($this->file()->getPathname()))) {
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
    protected function readFileContents(): ?string
    {
        if (!$this->hasFile()) {
            return null;
        }

        return LockAction::blocking($this, function (): ?string {
            if (false !== $contents = @file_get_contents($this->file()->getPathname())) {
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
     *
     * @return FileInterface|FileReferenceTrait
     */
    protected function dumpContents(string $contents, bool $append): FileInterface
    {
        if (!$this->hasFile()) {
            throw new FileOperationException('Failed to dump file contents: no file assigned!');
        }

        LockAction::blocking($this, function () use ($contents, $append): void {
            self::makePathIfNotExists($this->file()->getPath());
            self::dumpContentsForFile($this->file()->getPathname(), $contents, $append);
        });

        return $this;
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
