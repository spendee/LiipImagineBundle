<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Imagine\Filter\PostProcessor;

use Liip\ImagineBundle\File\FileInterface;
use Liip\ImagineBundle\File\Metadata\ContentTypeMetadata;
use Liip\ImagineBundle\File\Metadata\ExtensionMetadata;
use Liip\ImagineBundle\Imagine\Filter\PostProcessor\MozJpegPostProcessor;
use Liip\ImagineBundle\File\FileContent;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * @covers \Liip\ImagineBundle\Imagine\Filter\PostProcessor\MozJpegPostProcessor
 */
class MozJpegPostProcessorTest extends AbstractTest
{
    public function testMozJpegPostProcessor()
    {
        $mozJpegPostProcessor = new MozJpegPostProcessor(
            __DIR__.'/../../../Fixtures/bash/empty-command.sh'
        );

        $binary = FileContent::create('content', 'image/jpeg', 'jpeg');
        $result = $mozJpegPostProcessor->process($binary);

        $this->assertInstanceOf(FileInterface::class, $result);
    }
}
