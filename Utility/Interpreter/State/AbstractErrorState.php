<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Utility\Interpreter\State;

/**
 * @internal
 */
abstract class AbstractErrorState
{
    /**
     * @var int
     */
    private const DEFAULT_TYPE = -1000;

    /**
     * @var string
     */
    private const DEFAULT_MESSAGE = 'An undefined error occurred.';

    /**
     * @var int
     */
    private $type;

    /**
     * @var string
     */
    private $message;

    /**
     * @var \SplFileInfo|null
     */
    private $file;

    /**
     * @var int|null
     */
    private $line;

    /**
     * @param int|null    $type
     * @param string|null $message
     * @param string|null $file
     * @param int|null    $line
     */
    public function __construct(int $type = null, string $message = null, string $file = null, int $line = null)
    {
        $this->type = $type ?: self::DEFAULT_TYPE;
        $this->message = $message ?: self::DEFAULT_MESSAGE;
        $this->file = $file ? new \SplFileInfo($file) : null;
        $this->line = $line;
    }

    /**
     * @param array|null $array
     *
     * @return self
     */
    public static function create(array $array = null): self
    {
        return null !== $array
            ? new ErrorStateDefined(...array_values($array))
            : new ErrorStateDefault();
    }

    /**
     * @return bool
     */
    public function isDefined(): bool
    {
        return get_called_class() === ErrorStateDefined::class;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return get_called_class() === ErrorStateDefault::class;
    }

    /**
     * @return int
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function message(): string
    {
        return $this->message;
    }

    /**
     * @return null|\SplFileInfo
     */
    public function file(): ?\SplFileInfo
    {
        return $this->file;
    }

    /**
     * @return bool
     */
    public function hasFile(): bool
    {
        return null !== $this->file;
    }

    /**
     * @return int|null
     */
    public function line(): ?int
    {
        return $this->line;
    }

    /**
     * @return bool
     */
    public function hasLine(): bool
    {
        return null !== $this->line;
    }
}
