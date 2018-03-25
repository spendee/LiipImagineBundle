<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Utility\Interpreter;

use Liip\ImagineBundle\Utility\Interpreter\Interpreter;
use Liip\ImagineBundle\Utility\Interpreter\State\AbstractErrorState;
use Liip\ImagineBundle\Utility\Interpreter\State\ErrorStateDefault;
use Liip\ImagineBundle\Utility\Interpreter\State\ErrorStateDefined;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\Utility\Interpreter\Interpreter
 * @covers \Liip\ImagineBundle\Utility\Interpreter\State\AbstractErrorState
 * @covers \Liip\ImagineBundle\Utility\Interpreter\State\ErrorStateDefault
 * @covers \Liip\ImagineBundle\Utility\Interpreter\State\ErrorStateDefault
 */
class InterpreterTest extends TestCase
{
    /**
     * @return \Iterator
     */
    public function provideLastErrorMessageData(): \Iterator
    {
        yield [
            function () {
                @file_get_contents(sprintf('%s/foo/bar/baz.ext', sys_get_temp_dir()));
            },
            'file_get_contents(%s/foo/bar/baz.ext): failed to open stream: No such file or directory'
        ];

        yield [
            function () {
                @unlink(sprintf('%s/foo/bar/baz.ext', sys_get_temp_dir()));
            },
            'unlink(%s/foo/bar/baz.ext): No such file or directory'
        ];
    }

    /**
     * @dataProvider provideLastErrorMessageData
     *
     * @param \Closure $error
     * @param string   $expectedMessageFormat
     */
    public function testLastErrorMessage(\Closure $error, string $expectedMessageFormat)
    {
        $error();
        $this->assertErrorStateDefined(Interpreter::error(), $expectedMessageFormat);
        $this->assertErrorStateDefault(Interpreter::error(), $expectedMessageFormat);

        $error();
        $this->assertErrorStateDefined(Interpreter::error(true), $expectedMessageFormat);
        $this->assertErrorStateDefault(Interpreter::error(), $expectedMessageFormat);

        $error();
        $this->assertErrorStateDefined(Interpreter::error(false), $expectedMessageFormat);
        $this->assertErrorStateDefined(Interpreter::error(), $expectedMessageFormat);
        $this->assertErrorStateDefault(Interpreter::error(), $expectedMessageFormat);
    }

    /**
     * @param AbstractErrorState $state
     * @param string             $expectedMessageFormat
     */
    private function assertErrorStateDefined(AbstractErrorState $state, string $expectedMessageFormat): void
    {
        $this->assertInstanceOf(ErrorStateDefined::class, $state);

        $this->assertTrue($state->isDefined());
        $this->assertFalse($state->isDefault());

        $this->assertStringMatchesFormat($expectedMessageFormat, $state->message());

        $this->assertGreaterThan(0, $state->type());

        $this->assertTrue($state->hasFile());
        $this->assertSame(__FILE__, $state->file()->getPathname());

        $this->assertTrue($state->hasLine());
        $this->assertGreaterThan(0, $state->line());
    }

    /**
     * @param AbstractErrorState $state
     * @param string             $expectedMessageFormat
     */
    private function assertErrorStateDefault(AbstractErrorState $state, string $expectedMessageFormat): void
    {
        $this->assertInstanceOf(ErrorStateDefault::class, $state);

        $this->assertFalse($state->isDefined());
        $this->assertTrue($state->isDefault());

        $this->assertStringNotMatchesFormat($expectedMessageFormat, $state->message());

        $this->assertSame(-1000, $state->type());

        $this->assertFalse($state->hasFile());
        $this->assertNull($state->file());

        $this->assertFalse($state->hasLine());
        $this->assertNull($state->line());
    }
}
