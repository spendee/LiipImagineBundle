<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Imagine\Cache\Resolver;

use Liip\ImagineBundle\Exception\Imagine\Cache\Resolver\NotStorableException;
use Liip\ImagineBundle\File\FileInterface;
use Psr\Log\LoggerInterface;

class AmazonS3Resolver extends AbstractResolver
{
    /**
     * @var \AmazonS3
     */
    protected $storage;

    /**
     * @var string
     */
    protected $bucket;

    /**
     * @var string
     */
    protected $acl;

    /**
     * @var array
     */
    protected $objUrlOptions;

    /**
     * @param \AmazonS3       $storage       The Amazon S3 storage API. It's required to know authentication information
     * @param string          $bucket        The bucket name to operate on
     * @param string          $acl           The ACL to use when storing new objects. Default: owner read/write, public read
     * @param array           $objUrlOptions A list of options to be passed when retrieving the object url from Amazon S3
     * @param LoggerInterface $logger
     */
    public function __construct(\AmazonS3 $storage, string $bucket, string $acl = \AmazonS3::ACL_PUBLIC, array $objUrlOptions = [], LoggerInterface $logger = null)
    {
        parent::__construct($logger);

        $this->storage = $storage;
        $this->bucket = $bucket;
        $this->acl = $acl;
        $this->objUrlOptions = $objUrlOptions;
    }

    /**
     * {@inheritdoc}
     */
    public function isStored(string $path, string $filter): bool
    {
        return $this->objectExists($this->getObjectPath($path, $filter));
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $path, string $filter): string
    {
        return $this->getObjectUrl($this->getObjectPath($path, $filter));
    }

    /**
     * {@inheritdoc}
     */
    public function store(FileInterface $file, string $path, string $filter): void
    {
        $object = $this->getObjectPath($path, $filter);
        $result = $this->storage->create_object($this->bucket, $object, [
            'body' => $file->getContents(),
            'contentType' => (string) $file->getContentType(),
            'length' => $file->getContentsLength(),
            'acl' => $this->acl,
        ]);

        if (!$result->isOK()) {
            $this->logger->error('The object could not be created on Amazon S3.', [
                'path' => $path,
                'filter' => $filter,
                'object' => $object,
                'bucket' => $this->bucket,
                'result' => $result,
            ]);

            throw new NotStorableException('The object could not be created on Amazon S3.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove(array $paths, array $filters): void
    {
        if (empty($paths) && empty($filters)) {
            return;
        }

        if (empty($paths)) {
            if (!$this->storage->delete_all_objects($this->bucket, sprintf('/%s/i', implode('|', $filters)))) {
                $this->logger->error('The objects could not be deleted from Amazon S3.', [
                    'filters' => implode(', ', $filters),
                    'bucket' => $this->bucket,
                ]);
            }

            return;
        }

        foreach ($filters as $filter) {
            foreach ($paths as $path) {
                $object = $this->getObjectPath($path, $filter);

                if (!$this->objectExists($object)) {
                    continue;
                }

                if (!$this->storage->delete_object($this->bucket, $object)->isOK()) {
                    $this->logger->error('The objects could not be deleted from Amazon S3.', [
                        'path' => $path,
                        'filter' => $filter,
                        'object' => $object,
                        'bucket' => $this->bucket,
                    ]);
                }
            }
        }
    }

    /**
     * Sets a single option to be passed when retrieving an objects URL.
     *
     * If the option is already set, it will be overwritten.
     *
     * @see \AmazonS3::get_object_url() for available options
     *
     * @param string $key   The name of the option
     * @param mixed  $value The value to be set
     *
     * @return $this
     */
    public function setObjectUrlOption(string $key, string $value): self
    {
        $this->objUrlOptions[$key] = $value;

        return $this;
    }

    /**
     * Returns the object path within the bucket.
     *
     * @param string $path   The base path of the resource
     * @param string $filter The name of the imagine filter in effect
     *
     * @return string The path of the object on S3
     */
    protected function getObjectPath(string $path, string $filter): string
    {
        return str_replace('//', '/', $filter.'/'.$path);
    }

    /**
     * Returns the URL for an object saved on Amazon S3.
     *
     * @param string $object
     *
     * @return string|null
     */
    protected function getObjectUrl(string $object): ?string
    {
        return $this
            ->storage
            ->get_object_url($this->bucket, $object, 0, $this->objUrlOptions);
    }

    /**
     * Checks whether an object exists.
     *
     * @param string $object
     *
     * @throws \S3_Exception
     *
     * @return bool
     */
    protected function objectExists(string $object): bool
    {
        return $this
            ->storage
            ->if_object_exists($this->bucket, $object);
    }
}
