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

use Liip\ImagineBundle\File\FileBlob;
use Liip\ImagineBundle\File\FileInterface;
use Liip\ImagineBundle\File\Metadata\MimeTypeMetadata;
use Liip\ImagineBundle\File\Metadata\ExtensionMetadata;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Liip\ImagineBundle\File\AbstractFile
 * @covers \Liip\ImagineBundle\File\FileBlob
 */
class FileBlobTest extends TestCase
{
    public function testImplementsFileInterface()
    {
        $this->assertInstanceOf(FileInterface::class, new FileBlob());
    }

    public function testGettersAndSetters()
    {
        $file = FileBlob::create('the-content', 'image/jpeg', 'jpg');

        $this->assertSame('the-content', (string) $file);
        $this->assertSame('the-content', $file->getContents());
        $this->assertFalse($file->hasFile());
        $this->assertTrue($file->getContentType()->isMatch('image', 'jpeg'));
        $this->assertTrue($file->hasContentType());
        $this->assertTrue($file->getExtension()->isMatch('jpg'));
        $this->assertTrue($file->hasExtension());

        $file->setContents('foobar');
        $this->assertSame('foobar', $file->getContents());
        $this->assertSame(6, $file->getContentsLength());
        $file->setContents('-baz', true);
        $this->assertSame('foobar-baz', $file->getContents());
        $this->assertSame(10, $file->getContentsLength());

        $file = FileBlob::create();

        $this->assertSame('', (string) $file);
        $this->assertNull($file->getContents());
        $this->assertSame(0, $file->getContentsLength());
        $this->assertFalse($file->hasContentType());
        $this->assertFalse($file->hasExtension());
    }
}
