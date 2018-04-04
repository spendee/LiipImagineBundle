<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Log;

/**
 * @author Rob Frawley 2nd <rmf@src.run>
 */
trait LoggerAwareTrait
{
    /**
     * @var Logger|null
     */
    protected $logger;

    /**
     * @return Logger|null
     */
    public function getLogger(): ?Logger
    {
        return $this->logger;
    }

    /**
     * @param Logger|null $logger
     */
    public function setLogger(Logger $logger = null): void
    {
        $this->logger = $logger;
    }

    /**
     * @param Logger|null $logger
     */
    public function requireSetLogger(Logger $logger = null): void
    {
        $this->logger = $logger ?: new Logger();
    }
}
