<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\File\Attributes\Guesser;

use Liip\ImagineBundle\Exception\InvalidArgumentException;
use Liip\ImagineBundle\File\Attributes\Guesser\ContentTypeGuesser;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;

/**
 * @covers \Liip\ImagineBundle\File\Attributes\Guesser\AbstractGuesser
 * @covers \Liip\ImagineBundle\File\Attributes\Guesser\ContentTypeGuesser
 */
class ContentTypeGuesserTest extends TestCase
{
    public function testGuess()
    {
        $mock = $this->createMimeTypeGuesserMock();
        $mock
            ->expects($this->once())
            ->method('guess')
            ->with('foobar');

        $g = new ContentTypeGuesser();
        $g->register($mock);
        $g->guess('foobar');

        $mock = $this->createMimeTypeGuesserMock();
        $mock
            ->expects($this->exactly(5))
            ->method('guess')
            ->with('foobar')
            ->willReturnOnConsecutiveCalls(null, null, null, null, 'baz');

        $g = new ContentTypeGuesser();
        $g->register($mock);
        $g->register($mock);
        $g->register($mock);

        $this->assertNull($g->guess('foobar'));
        $this->assertSame('baz', $g->guess('foobar'));
    }

    public function testThrowsOnUnsupportedRegister()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageRegExp('{Invalid guesser type "[^"]+" provided for "[^"]+"\.}');

        $g = new ContentTypeGuesser();
        $g->register(new class {});
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|MimeTypeGuesser
     */
    private function createMimeTypeGuesserMock()
    {
        return $this
            ->getMockBuilder(MimeTypeGuesser::class)
            ->setMethods(['guess'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
    }
}
