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
use Liip\ImagineBundle\File\FileReference;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStreamException;
use org\bovigo\vfs\vfsStreamFile;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;

/**
 * @covers \Liip\ImagineBundle\File\FileReference
 * @covers \Liip\ImagineBundle\File\FileReferenceTrait
 * @covers \Liip\ImagineBundle\File\FileTrait
 */
class FileReferenceTest extends TestCase
{
    /**
     * @var vfsStreamDirectory
     */
    private $filesystemRoot;

    /**
     * @var vfsStreamDirectory
     */
    private $filesystemWork;

    /**
     * Setup our virtual filesystem environment.
     */
    public function setUp()
    {
        parent::setUp();

        try {
            $this->filesystemRoot = vfsStream::setup('php-unit', 0700);
            $this->filesystemRoot->chown(getmyuid());
            $this->filesystemRoot->chgrp(getmygid());
            $this->filesystemWork = new vfsStreamDirectory(self::normalizeClassOrMethodName(__CLASS__), 0777);
            $this->filesystemWork->at($this->filesystemRoot);
        } catch (vfsStreamException $e) {
            $this->fail(sprintf('Failed creating virtual filesystem base: %s', $e->getMessage()));
        }
    }

    public function testInstanceOfFileInterface()
    {
        $this->assertInstanceOf(FileInterface::class, new FileReference());
    }

    /**
     * @return \Iterator
     */
    public static function provideContentsData(): \Iterator
    {
        $finder = (new Finder())
            ->in(__DIR__)
            ->name('*.php')
            ->ignoreUnreadableDirs(true);

        foreach ($finder->files() as $f) {
            yield [$f];
        }
    }

    /**
     * @dataProvider provideContentsData
     *
     * @param string $contents
     */
    public function testSettersAndAccesses(string $contents)
    {
        $path = $this->createFakeFileName(__METHOD__, $this->filesystemWork->url());
        $file = $this->createFileReference($path, 'type/sub-type', 'ext');

        $this->assertSame($path, $file->file()->getPathname());
        $this->assertSame($path, (string) $file);
        $this->assertTrue($file->hasFile());
        $this->assertFalse($file->exists());
        $this->assertFalse($file->isReadable());
        $this->assertTrue($file->isWritable());
        $this->assertNull($file->contents());
        $this->assertFalse($file->hasContents());
        $this->assertTrue($file->hasEmptyContents());

        $file->setContents($contents);

        $this->assertTrue($file->exists());
        $this->assertTrue($file->isReadable());
        $this->assertTrue($file->isWritable());
        $this->assertSame($contents, $file->contents());
        $this->assertTrue($file->hasContents());
        $this->assertFalse($file->hasEmptyContents());

        $file->setContents($contents);

        $this->assertSame($contents, $file->contents());

        $file->setContents('');

        for ($i = 1; $i < 8; ++$i) {
            $file->setContents($contents, true);
            $this->assertSame(str_repeat($contents, $i), $file->contents());
        }

        $file->remove();

        $this->assertFalse($file->exists());
    }

    /**
     * @group exceptions
     */
    public function testThrowsIfUnlinkFails()
    {
        $this->expectException(FileOperationException::class);
        $this->expectExceptionMessageRegExp('{Failed to remove file "vfs://php-unit/[^/]+/[^\.]+.ext": [^\.]+.}');

        $fake = $this->createFakeFile(__FUNCTION__, 'foobar', 0400, null, vfsStream::getCurrentGroup() + 1);
        $file = $this->createFileReference($fake);

        $file->remove();
    }

    /**
     * @group exceptions
     */
    public function testThrowsIfCreateFails()
    {
        $this->expectException(FileOperationException::class);
        $this->expectExceptionMessageRegExp('{Failed to write contents of "vfs://php-unit/[^/]+/[^\.]+.ext": [^\.]+.}');

        $fake = $this->createFakeFile(__FUNCTION__, 'foobar', 0400, null, vfsStream::getCurrentGroup() + 1);
        $file = $this->createFileReference($fake);

        $file->setContents('foobar');
    }

    /**
     * @param string|vfsStreamFile $file
     * @param string|null          $contentType
     * @param string|null          $extension
     *
     * @return FileReference
     */
    private function createFileReference($file, string $contentType = null, string $extension = null): FileReference
    {
        return FileReference::create(
            $file instanceof vfsStreamFile ? $file->url() : $file,
            $contentType ?? 'content-type/sub-type',
            $extension ?? 'ext'
        );
    }

    /**
     * @param string      $method
     * @param string|null $contents
     * @param int|null    $permissions
     * @param int|null    $u
     * @param int|null    $g
     *
     * @return vfsStreamFile
     */
    private function createFakeFile(string $method, string $contents = null, int $permissions = null, int $u = null, int $g = null): vfsStreamFile
    {
        $file = new vfsStreamFile(self::createFakeFileName($method), $permissions);

        if (null !== $contents) {
            $file->setContent('foobar');
        }

        if (-1 !== $u) {
            $file->chown($u ?: vfsStream::getCurrentUser());
        }

        if (-1 !== $g) {
            $file->chgrp($g ?: vfsStream::getCurrentGroup());
        }

        $this->filesystemWork->addChild($file);

        return $file;
    }

    /**
     * @param string      $method
     * @param string|null $root
     *
     * @return string
     */
    private static function createFakeFileName(string $method, string $root = null): string
    {
        $name = sprintf(
            '%s-%s.ext', self::normalizeClassOrMethodName($method), mt_rand(10000000000, 99999999999)
        );

        if (null !== $root) {
            $name = sprintf('%s/%s', $root, $name);
        }

        return $name;
    }

    /**
     * @param string $name
     *
     * @return string
     */
    private static function normalizeClassOrMethodName(string $name): string
    {
        return mb_strtolower(ltrim(preg_replace('{[A-Z]([a-z]+)}', '-$0',
                preg_replace('{^.+\\\}i', '', get_called_class())
        ), '-'));
    }
}
