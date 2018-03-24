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
use Liip\ImagineBundle\File\FileTemp;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\File\AbstractFile
 * @covers \Liip\ImagineBundle\File\AbstractFilePath
 * @covers \Liip\ImagineBundle\File\FileTemp
 */
class FileReferenceTemporaryTest extends TestCase
{
    public function testInstanceOfFileInterface()
    {
        $this->assertInstanceOf(FileInterface::class, new FileTemp());
    }

    public function testAcquireAndRelease()
    {
        $temporary = new FileTemp();

        $this->assertSame(sys_get_temp_dir(), $temporary->getRoot());
        $this->assertStringStartsWith('imagine-bundle', $temporary->getName());
        $this->assertFalse($temporary->hasFile());
        $this->assertFalse($temporary->fileExists());
        $this->assertFalse($temporary->isFileReadable());
        $this->assertFalse($temporary->isFileWritable());
        $this->assertFalse($temporary->hasContents());
        $this->assertNull($temporary->getContents());

        $temporary->acquire();

        $this->assertTrue($temporary->hasFile());
        $this->assertStringStartsWith(sys_get_temp_dir(), $temporary->getFile()->getPathname());
        $this->assertStringStartsWith('imagine-bundle', $temporary->getFile()->getFilename());
        $this->assertTrue($temporary->fileExists());
        $this->assertTrue($temporary->isFileReadable());
        $this->assertTrue($temporary->isFileWritable());
        $this->assertTrue($temporary->hasContents());
        $this->assertSame('', $temporary->getContents());

        $temporary->setContents('foobar');

        $this->assertTrue($temporary->hasFile());
        $this->assertTrue($temporary->fileExists());
        $this->assertTrue($temporary->isFileReadable());
        $this->assertTrue($temporary->isFileWritable());
        $this->assertSame('foobar', $temporary->getContents());
        $this->assertTrue($temporary->hasContents());
        $this->assertFileExists($file = $temporary->getFile()->getPathname());

        $temporary->release();

        $this->assertFalse($temporary->hasFile());
        $this->assertFalse($temporary->fileExists());
        $this->assertFalse($temporary->isFileReadable());
        $this->assertFalse($temporary->isFileWritable());
        $this->assertFalse($temporary->hasContents());
        $this->assertNull($temporary->getContents());
        $this->assertFileNotExists($file);
    }

    public function testAutomaticallyAcquiredOnSetContents()
    {
        $temporary = new FileTemp();

        $this->assertFalse($temporary->isAcquired());
        $temporary->setContents('foobar');
        $this->assertTrue($temporary->isAcquired());
        $this->assertSame('foobar', $temporary->getContents());
        $temporary->release();
    }

    public function testThrowsOnSetTmpContextWhileAcquired()
    {
        $this->expectException(FileOperationException::class);
        $this->expectExceptionMessage('failed to change context descriptor');

        $temporary = new FileTemp();
        $temporary->acquire();
        $temporary->setName('foobar');
    }

    public function testThrowsOnSetPathPrefixWhileAcquired()
    {
        $this->expectException(FileOperationException::class);
        $this->expectExceptionMessage('failed to change path prefix');

        $temporary = new FileTemp();
        $temporary->acquire();
        $temporary->setRoot('foobar');
    }
}
