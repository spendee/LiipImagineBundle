<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Data\Loader;

use Doctrine\Common\Persistence\ObjectManager;
use Liip\ImagineBundle\Exception\File\Loader\NotLoadableException;
use Liip\ImagineBundle\File\FileBlob;
use Liip\ImagineBundle\File\FileInterface;

abstract class AbstractDoctrineLoader implements LoaderInterface
{
    /**
     * @var ObjectManager
     */
    protected $manager;

    /**
     * @var string
     */
    protected $class;

    /**
     * @param ObjectManager $manager
     * @param string        $class
     */
    public function __construct(ObjectManager $manager, string $class = null)
    {
        $this->manager = $manager;
        $this->class = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $identity): FileInterface
    {
        $image = $this->manager->find($this->class, $this->mapPathToId($identity));

        if (!$image) {
            // try to find the image without extension
            $info = pathinfo($identity);
            $name = $info['dirname'].'/'.$info['filename'];

            $image = $this->manager->find($this->class, $this->mapPathToId($name));
        }

        if (!$image) {
            throw new NotLoadableException('Source image was not found with id "%s"', $identity);
        }

        return FileBlob::create(stream_get_contents($this->getStreamFromImage($image)));
    }

    /**
     * Map the requested path (ie. subpath in the URL) to an id that can be used to lookup the image in the Doctrine store.
     *
     * @param string $path
     *
     * @return string
     */
    abstract protected function mapPathToId($path);

    /**
     * Return a stream resource from the Doctrine entity/document with the image content.
     *
     * @param object $image
     *
     * @return resource
     */
    abstract protected function getStreamFromImage($image);
}
