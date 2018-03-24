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
 * @author Rob Frawley 2nd <rmf@src.run>
 */
final class FileTemp extends AbstractFilePath
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $root;

    /**
     * @param string|null $name
     * @param string|null $root
     */
    public function __construct(string $name = null, string $root = null)
    {
        $this->setName($name);
        $this->setRoot($root);
    }

    /**
     * Automatically release the temporary file.
     */
    public function __destruct()
    {
        $this->release();
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     *
     * @return self
     */
    public function setName(string $name = null): self
    {
        $this->requireReleasedState('failed to change context descriptor');
        $this->name = sprintf('imagine-bundle-temporary_%s', $name ?: 'general');

        return $this;
    }

    /**
     * @return string
     */
    public function getRoot(): string
    {
        return $this->root;
    }

    /**
     * @param string|null $path
     *
     * @return FileTemp
     */
    public function setRoot(string $path = null): self
    {
        $this->requireReleasedState('failed to change path prefix');
        $this->root = self::makePathIfNotExists($path ?? sys_get_temp_dir());

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

        $this->file = LockInvokable::blocking($this, function (): \SplFileInfo {
            if (false !== $file = @tempnam($this->getRoot(), $this->getName())) {
                return new \SplFileInfo($file);
            }

            throw new FileOperationException(sprintf(
                'Failed to acquire temporary file in "%s": %s.', $this->getRoot(), Interpreter::lastErrorMessage()
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
     * @param string $contents
     * @param bool   $append
     */
    protected function doSetContents(string $contents, bool $append): void
    {
        if (!$this->isAcquired()) {
            $this->acquire();
        }

        parent::doSetContents($contents, $append);
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
}
