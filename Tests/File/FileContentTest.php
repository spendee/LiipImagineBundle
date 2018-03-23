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

use Liip\ImagineBundle\File\FileContent;
use Liip\ImagineBundle\File\FileInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\File\FileContent
 * @covers \Liip\ImagineBundle\File\FileTrait
 */
class FileContentTest extends TestCase
{
    public function testImplementsFileInterface()
    {
        $this->assertInstanceOf(FileInterface::class, new FileContent());
    }

    public function testGettersAndSetters()
    {
        $file = FileContent::create('the-content', 'image/jpeg', 'jpg');

        $this->assertSame('the-content', (string) $file);
        $this->assertSame('the-content', $file->contents());
        $this->assertTrue($file->contentType()->isEquivalent('image', 'jpeg'));
        $this->assertTrue($file->extension()->isExtension('jpg'));
        $this->assertNull($file->file());
        $this->assertFalse($file->hasFile());
        $this->assertFalse($file->exists());
        $this->assertTrue($file->isReadable());
        $this->assertTrue($file->isWritable());
        $this->assertTrue($file->hasContentType());
        $this->assertTrue($file->hasExtension());

        $file->setContents('foobar');
        $this->assertSame('foobar', $file->contents());
        $file->setContents('-baz', true);
        $this->assertSame('foobar-baz', $file->contents());

        $file = FileContent::create();

        $this->assertSame('', (string) $file);
        $this->assertNull($file->contents());
        $this->assertFalse($file->hasContentType());
        $this->assertFalse($file->hasExtension());
    }
}
