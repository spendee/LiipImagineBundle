<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Log;

use Liip\ImagineBundle\Log\Logger;
use Liip\ImagineBundle\Log\LoggerAwareTrait;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\Log\LoggerAwareTrait
 */
class LoggerAwareTraitTest extends TestCase
{
    public function testLog(): void
    {
        $logger = new class() {
            use LoggerAwareTrait;
        };

        $logger->setLogger();
        $this->assertNull($logger->getLogger());

        $logger->requireSetLogger();
        $this->assertInstanceOf(Logger::class, $logger->getLogger());

        $logger->setLogger($l = new Logger());
        $this->assertSame($l, $logger->getLogger());
    }
}
