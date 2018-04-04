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
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

/**
 * @covers \Liip\ImagineBundle\Log\Logger
 */
class LoggerTest extends TestCase
{
    /**
     * @return \Iterator
     */
    public function provideLogData(): \Iterator
    {
        yield [LogLevel::ALERT];
        yield [LogLevel::CRITICAL];
        yield [LogLevel::DEBUG];
        yield [LogLevel::EMERGENCY];
        yield [LogLevel::ERROR];
        yield [LogLevel::INFO];
        yield [LogLevel::NOTICE];
        yield [LogLevel::WARNING];
    }

    /**
     * @dataProvider provideLogData
     *
     * @param string $level
     */
    public function testLog(string $level): void
    {
        $internal = $this
            ->getMockBuilder(AbstractLogger::class)
            ->setMethods(['log'])
            ->getMockForAbstractClass();

        $internal
            ->expects($this->once())
            ->method('log')
            ->with($level, 'foobar', ['foo' => 'bar']);

        $logger = new Logger($internal);
        $logger->{$level}('foobar', ['foo' => 'bar']);
    }
}
