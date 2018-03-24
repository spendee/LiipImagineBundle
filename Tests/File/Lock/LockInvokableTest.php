<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\File\Lock;

use Liip\ImagineBundle\File\Lock\LockInvokable;
use Liip\ImagineBundle\File\Lock\LockFactory;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\File\Lock\LockInvokable
 */
class LockInvokableTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        LockFactory::reset();
    }

    public function testAction()
    {
        $result = LockInvokable::blocking($this, function (): string {
            return 'foobar';
        });

        $this->assertSame('foobar', $result);
    }
}
