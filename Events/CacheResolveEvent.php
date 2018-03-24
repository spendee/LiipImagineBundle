<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Events;

use Symfony\Component\EventDispatcher\Event;

class CacheResolveEvent extends Event
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $filter;

    /**
     * @var string|null
     */
    protected $url;

    /**
     * @param string      $path
     * @param string      $filter
     * @param string|null $url
     */
    public function __construct(string $path, string $filter, string $url = null)
    {
        $this->path = $path;
        $this->filter = $filter;
        $this->url = $url;
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $filter
     *
     * @return $this
     */
    public function setFilter(string $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * @return string
     */
    public function getFilter(): string
    {
        return $this->filter;
    }

    /**
     * @param string $url
     *
     * @return $this
     */
    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl(): ?string
    {
        return $this->url;
    }

    /**
     * @return bool
     */
    public function hasUrl(): bool
    {
        return null !== $this->url;
    }
}
