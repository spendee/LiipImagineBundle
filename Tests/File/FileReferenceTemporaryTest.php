<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\File;

use Liip\ImagineBundle\Exception\File\FileOperationException;
use Liip\ImagineBundle\File\FileInterface;
use Liip\ImagineBundle\File\FileReferenceTemporary;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\File\FileReferenceTemporary
 * @covers \Liip\ImagineBundle\File\FileReferenceTrait
 * @covers \Liip\ImagineBundle\File\FileTrait
 */
class FileReferenceTemporaryTest extends TestCase
{
    public function testInstanceOfFileInterface()
    {
        $this->assertInstanceOf(FileInterface::class, new FileReferenceTemporary());
    }

    public function testAcquireAndRelease()
    {
        $temporary = new FileReferenceTemporary();

        $this->assertSame(sys_get_temp_dir(), $temporary->pathPrefix());
        $this->assertStringStartsWith('imagine-bundle', $temporary->tmpContext());
        $this->assertFalse($temporary->hasFile());
        $this->assertFalse($temporary->exists());
        $this->assertFalse($temporary->isReadable());
        $this->assertFalse($temporary->isWritable());
        $this->assertFalse($temporary->hasContents());
        $this->assertNull($temporary->contents());

        $temporary->acquire();

        $this->assertTrue($temporary->hasFile());
        $this->assertStringStartsWith(sys_get_temp_dir(), $temporary->file()->getPathname());
        $this->assertStringStartsWith('imagine-bundle', $temporary->file()->getFilename());
        $this->assertTrue($temporary->exists());
        $this->assertTrue($temporary->isReadable());
        $this->assertTrue($temporary->isWritable());
        $this->assertTrue($temporary->hasContents());
        $this->assertTrue($temporary->hasEmptyContents());
        $this->assertSame('', $temporary->contents());

        $temporary->setContents('foobar');

        $this->assertTrue($temporary->hasFile());
        $this->assertTrue($temporary->exists());
        $this->assertTrue($temporary->isReadable());
        $this->assertTrue($temporary->isWritable());
        $this->assertSame('foobar', $temporary->contents());
        $this->assertTrue($temporary->hasContents());
        $this->assertFileExists($file = $temporary->file()->getPathname());

        $temporary->release();

        $this->assertFalse($temporary->hasFile());
        $this->assertFalse($temporary->exists());
        $this->assertFalse($temporary->isReadable());
        $this->assertFalse($temporary->isWritable());
        $this->assertFalse($temporary->hasContents());
        $this->assertNull($temporary->contents());
        $this->assertFileNotExists($file);
    }

    public function testAutomaticallyAcquiredOnSetContents()
    {
        $temporary = new FileReferenceTemporary();

        $this->assertFalse($temporary->isAcquired());
        $temporary->setContents('foobar');
        $this->assertTrue($temporary->isAcquired());
        $this->assertSame('foobar', $temporary->contents());
        $temporary->release();
    }

    public function testThrowsOnSetTmpContextWhileAcquired()
    {
        $this->expectException(FileOperationException::class);
        $this->expectExceptionMessage('failed to change context descriptor');

        $temporary = new FileReferenceTemporary();
        $temporary->acquire();
        $temporary->setTmpContext('foobar');
    }

    public function testThrowsOnSetPathPrefixWhileAcquired()
    {
        $this->expectException(FileOperationException::class);
        $this->expectExceptionMessage('failed to change path prefix');

        $temporary = new FileReferenceTemporary();
        $temporary->acquire();
        $temporary->setPathPrefix('foobar');
    }
}
